<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 05:07
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Cookie;
use TP_Core\Libs\PHP\Libs\HTTP_Message\RequestInterface;
use TP_Core\Libs\PHP\Libs\HTTP_Message\ResponseInterface;
if(ABSPATH){
    class CookieJar implements CookieJarInterface{
        private $__cookies = [];
        private $__strictMode;
        public function __construct(bool $strictMode = false, array $cookieArray = []){
            $this->__strictMode = $strictMode;
            foreach ($cookieArray as $cookie) {
                if (!($cookie instanceof SetCookie)) {
                    $cookie = new SetCookie($cookie);
                }
                $this->setCookie($cookie);
            }
        }
        public static function fromArray(array $cookies, string $domain): self{
            $cookieJar = new self();
            foreach ($cookies as $name => $value) {
                $cookieJar->setCookie(new SetCookie([
                    'Domain'  => $domain,
                    'Name'    => $name,
                    'Value'   => $value,
                    'Discard' => true
                ]));
            }
            return $cookieJar;
        }
        public static function shouldPersist(SetCookie $cookie, bool $allowSessionCookies = false): bool{
            if ($allowSessionCookies || $cookie->getExpires()) {
                if (!$cookie->getDiscard()) {
                    return true;
                }
            }
            return false;
        }
        public function getCookieByName(string $name): ?SetCookie{
            foreach ($this->__cookies as $cookie) {
                if ($cookie->getName() !== null && \strcasecmp($cookie->getName(), $name) === 0) {
                    return $cookie;
                }
            }
            return null;
        }
        public function toArray(): array{
            return \array_map(static function (SetCookie $cookie): array {
                return $cookie->toArray();
            }, $this->getIterator()->getArrayCopy());
        }
        public function clear(?string $domain = null, ?string $path = null, ?string $name = null): void{
            if (!$domain) {
                $this->__cookies = [];
                return;
            }
            if (!$path) {
                $this->__cookies = \array_filter(
                    $this->__cookies,
                    static function (SetCookie $cookie) use ($domain): bool {
                        return !$cookie->matchesDomain($domain);
                    }
                );
            } elseif (!$name) {
                $this->__cookies = \array_filter(
                    $this->__cookies,
                    static function (SetCookie $cookie) use ($path, $domain): bool {
                        return !($cookie->matchesPath($path) &&
                            $cookie->matchesDomain($domain));
                    }
                );
            } else {
                $this->__cookies = \array_filter(
                    $this->__cookies,
                    static function (SetCookie $cookie) use ($path, $domain, $name) {
                        return !($cookie->getName() === $name &&
                            $cookie->matchesPath($path) &&
                            $cookie->matchesDomain($domain));
                    }
                );
            }
            return null;
        }
        public function clearSessionCookies(): void{
            $this->__cookies = \array_filter(
                $this->__cookies,
                static function (SetCookie $cookie): bool {
                    return !$cookie->getDiscard() && $cookie->getExpires();
                }
            );
            return null;
        }
        public function setCookie(SetCookie $cookie): bool
        {
            // If the name string is empty (but not 0), ignore the set-cookie
            // string entirely.
            $name = $cookie->getName();
            if (!$name && $name !== '0') {
                return false;
            }

            // Only allow cookies with set and valid domain, name, value
            $result = $cookie->validate();
            if ($result !== true) {
                if ($this->__strictMode) {
                    throw new \RuntimeException('Invalid cookie: ' . $result);
                }
                $this->__removeCookieIfEmpty($cookie);
                return false;
            }

            // Resolve conflicts with previously set cookies
            foreach ($this->__cookies as $i => $c) {
                if ($c->getPath() !== $cookie->getPath() ||
                    $c->getDomain() !== $cookie->getDomain() ||
                    $c->getName() !== $cookie->getName()
                ) {
                    continue;
                }

                // The previously set cookie is a discard cookie and this one is
                // not so allow the new cookie to be set
                if (!$cookie->getDiscard() && $c->getDiscard()) {
                    unset($this->__cookies[$i]);
                    continue;
                }

                // If the new cookie's expiration is further into the future, then
                // replace the old cookie
                if ($cookie->getExpires() > $c->getExpires()) {
                    unset($this->__cookies[$i]);
                    continue;
                }

                // If the value has changed, we better change it
                if ($cookie->getValue() !== $c->getValue()) {
                    unset($this->__cookies[$i]);
                    continue;
                }

                // The cookie exists, so no need to continue
                return false;
            }
            $this->__cookies[] = $cookie;
            return true;
        }
        public function count(): int
        {
            return \count($this->__cookies);
        }
        public function getIterator(): \ArrayIterator{
            return new \ArrayIterator(\array_values($this->__cookies));
        }
        public function extractCookies(RequestInterface $request, ResponseInterface $response): void {
            if ($cookieHeader = $response->getHeader('Set-Cookie')) {
                foreach ($cookieHeader as $cookie) {
                    $sc = SetCookie::fromString($cookie);
                    if (!$sc->getDomain()) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $sc->setDomain($request->getUri()->getHost());
                    }
                    if (0 !== \strpos($sc->getPath(), '/')) {
                        $sc->setPath($this->__getCookiePathFromRequest($request));
                    }
                    $this->setCookie($sc);
                }
            }
            return null;
        }
        private function __getCookiePathFromRequest(RequestInterface $request): string{
            /** @noinspection PhpUndefinedMethodInspection *///todo
            $uriPath = $request->getUri()->getPath();
            if ('' === $uriPath) {
                return '/';
            }
            if (0 !== \strpos($uriPath, '/')) {
                return '/';
            }
            if ('/' === $uriPath) {
                return '/';
            }
            $lastSlashPos = \strrpos($uriPath, '/');
            if (0 === $lastSlashPos || false === $lastSlashPos) {
                return '/';
            }

            return \substr($uriPath, 0, $lastSlashPos);
        }
        public function withCookieHeader(RequestInterface $request): RequestInterface{
            $values = [];
            $uri = $request->getUri();
            /** @noinspection PhpUndefinedMethodInspection */
            $scheme = $uri->getScheme();
            /** @noinspection PhpUndefinedMethodInspection */
            $host = $uri->getHost();
            /** @noinspection PhpUndefinedMethodInspection */
            $path = $uri->getPath() ?: '/';

            foreach ($this->__cookies as $cookie) {
                if ($cookie->matchesPath($path) &&
                    $cookie->matchesDomain($host) &&
                    !$cookie->isExpired() &&
                    (!$cookie->getSecure() || $scheme === 'https')
                ) {
                    $values[] = $cookie->getName() . '='
                        . $cookie->getValue();
                }
            }
            return $values
                ? $request->withHeader('Cookie', \implode('; ', $values))
                : $request;
        }
        private function __removeCookieIfEmpty(SetCookie $cookie): void{
            $cookieValue = $cookie->getValue();
            if ($cookieValue === null || $cookieValue === '') {
                $this->clear(
                    $cookie->getDomain(),
                    $cookie->getPath(),
                    $cookie->getName()
                );
            }
            return null;
        }
    }
}else{die;}