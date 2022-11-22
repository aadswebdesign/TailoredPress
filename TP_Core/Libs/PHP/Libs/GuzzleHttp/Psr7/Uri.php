<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 06:01
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
use TP_Core\Libs\PHP\Libs\HTTP_Message\UriInterface;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7\Exception\MalformedUriException;
if(ABSPATH){
    class Uri implements UriInterface, \JsonSerializable{
        public const HTTP_DEFAULT_HOST = 'localhost';
        public const DEFAULT_PORTS = [
            'http'  => 80,'https' => 443,'ftp' => 21,'gopher' => 70,
            'nntp' => 119,'news' => 119,'telnet' => 23,'tn3270' => 23,
            'imap' => 143,'pop' => 110,'ldap' => 389,
        ];
        public const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';
        public const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';
        public const QUERY_SEPARATORS_REPLACEMENT = ['=' => '%3D', '&' => '%26'];
        private $__scheme = '';
        private $__userInfo = '';
        private $__host = '';
        private $__port;
        private $__path = '';
        private $__query = '';
        private $__fragment = '';
        private $__composedComponents;
        public function __construct(string $uri = ''){
            if ($uri !== '') {
                $parts = self::parse($uri);
                if ($parts === false)
                    throw new MalformedUriException("Unable to parse URI: $uri");
                $this->__applyParts($parts);
            }
        }
        private static function parse(string $url) {
            $prefix = '';
            if (preg_match('%^(.*://\[[0-9:a-f]+\])(.*?)$%', $url, $matches)) {
                /** @var array{0:string, 1:string, 2:string} $matches */
                $prefix = $matches[1];
                /** @noinspection MultiAssignmentUsageInspection */
                $url = $matches[2];
            }
            /** @noinspection NotOptimalRegularExpressionsInspection */
            $encodedUrl = preg_replace_callback(
                '%[^:/@?&=#]+%usD',
                static function ($matches) {
                    return urlencode($matches[0]);
                },
                $url
            );
            $result = parse_url($prefix . $encodedUrl);
            if ($result === false) return false;
            return array_map('urldecode', $result);
        }
        public function __toString(): string{
            if ($this->__composedComponents === null) {
                $this->__composedComponents = self::composeComponents(
                    $this->__scheme,
                    $this->getAuthority(),
                    $this->__path,
                    $this->__query,
                    $this->__fragment
                );
            }
            return $this->__composedComponents;
        }
        public static function composeComponents(?string $scheme, ?string $authority, string $path, ?string $query, ?string $fragment): string{
            $uri = '';
            if ($scheme !== '') $uri .= $scheme . ':';
            if ($authority !== ''|| $scheme === 'file') $uri .= '//' . $authority;
            $uri .= $path;
            if ($query !== '') $uri .= '?' . $query;
            if ($fragment !== '') $uri .= '#' . $fragment;
            return $uri;
        }
        public static function isDefaultPort(UriInterface $uri): bool{
            $defaults = self::DEFAULT_PORTS[$uri->getScheme()];
            return $uri->getPort() === null
            || (isset($defaults) && $uri->getPort() === self::DEFAULT_PORTS[$uri->getScheme()]);
        }
        public static function isAbsolute(UriInterface $uri): bool{
            return $uri->getScheme() !== '';
        }
        public static function isNetworkPathReference(UriInterface $uri): bool {
            return $uri->getScheme() === '' && $uri->getAuthority() !== '';
        }
        public static function isAbsolutePathReference(UriInterface $uri): bool{
            return $uri->getScheme() === ''
            && $uri->getAuthority() === ''
            && isset($uri->getPath()[0])
            && $uri->getPath()[0] === '/';
        }
        public static function isRelativePathReference(UriInterface $uri): bool{
            return $uri->getScheme() === ''
            && $uri->getAuthority() === ''
            && (!isset($uri->getPath()[0]) || $uri->getPath()[0] !== '/');
        }
        public static function isSameDocumentReference(UriInterface $uri, UriInterface $base = null): bool{
            if ($base !== null) {
                $uri = UriResolver::resolve($base, $uri);
                return ($uri->getScheme() === $base->getScheme())
                && ($uri->getAuthority() === $base->getAuthority())
                && ($uri->getPath() === $base->getPath())
                && ($uri->getQuery() === $base->getQuery());
            }
            return $uri->getScheme() === '' && $uri->getAuthority() === '' && $uri->getPath() === '' && $uri->getQuery() === '';
        }
        public static function withoutQueryValue(UriInterface $uri, string $key): UriInterface{
            $result = self::__getFilteredQueryString($uri, [$key]);
            return $uri->withQuery(implode('&', $result));
        }
        public static function withQueryValue(UriInterface $uri, string $key, ?string $value): UriInterface{
            $result = self::__getFilteredQueryString($uri, [$key]);
            $result[] = self::__generateQueryString($key, $value);
            return $uri->withQuery(implode('&', $result));
        }
        public static function withQueryValues(UriInterface $uri, array $keyValueArray): UriInterface{
            $result = self::__getFilteredQueryString($uri, array_keys($keyValueArray));
            foreach ($keyValueArray as $key => $value)
                $result[] = self::__generateQueryString((string) $key, $value !== null ? (string) $value : null);
            return $uri->withQuery(implode('&', $result));
        }
        public static function fromParts(array $parts): UriInterface{
            $uri = new self();
            $uri->__applyParts($parts);
            $uri->__validateState();
            return $uri;
        }
        public function getScheme(): string{
            return $this->__scheme;
        }
        public function getAuthority(): string{
            $authority = $this->__host;
            if ($this->__userInfo !== '')
                $authority = $this->__userInfo . '@' . $authority;
            if ($this->__port !== null) $authority .= ':' . $this->__port;
            return $authority;
        }
        public function getUserInfo(): string{
            return $this->__userInfo;
        }
        public function getHost(): string{
            return $this->__host;
        }
        public function getPort(): ?int{
            return $this->__port;
        }
        public function getPath(): string{
            return $this->__path;
        }
        public function getQuery(): string{
            return $this->__query;
        }
        public function getFragment(): string{
            return $this->__fragment;
        }
        public function withScheme($scheme): UriInterface{
            $scheme = $this->__filterScheme($scheme);
            if ($this->__scheme === $scheme) return $this;
            $new = clone $this;
            $new->__scheme = $scheme;
            $new->__composedComponents = null;
            $new->__removeDefaultPort();
            $new->__validateState();
            return $new;
        }
        public function withUserInfo($user, $password = null): UriInterface{
            $info = $this->__filterUserInfoComponent($user);
            if ($password !== null)
                $info .= ':' . $this->__filterUserInfoComponent($password);
            if ($this->__userInfo === $info)return $this;
            $new = clone $this;
            $new->__userInfo = $info;
            $new->__composedComponents = null;
            $new->__validateState();
            return $new;
        }
        public function withHost($host): UriInterface {
            $host = $this->__filterHost($host);
            if ($this->__host === $host) return $this;
            $new = clone $this;
            $new->__host = $host;
            $new->__composedComponents = null;
            $new->__validateState();
            return $new;
        }
        public function withPort($port): UriInterface{
            $port = $this->__filterPort($port);
            if ($this->__port === $port) return $this;
            $new = clone $this;
            $new->__port = $port;
            $new->__composedComponents = null;
            $new->__removeDefaultPort();
            $new->__validateState();
            return $new;
        }
        public function withPath($path): UriInterface{
            $path = $this->__filterPath($path);
            if ($this->__path === $path) return $this;
            $new = clone $this;
            $new->__path = $path;
            $new->__composedComponents = null;
            $new->__validateState();
            return $new;
        }
        public function withQuery($query): UriInterface{
            $query = $this->__filterQueryAndFragment($query);
            if ($this->__query === $query) return $this;
            $new = clone $this;
            $new->__query = $query;
            $new->__composedComponents = null;
            return $new;
        }
        public function withFragment($fragment): UriInterface{
            $fragment = $this->__filterQueryAndFragment($fragment);
            if ($this->__fragment === $fragment) return $this;
            $new = clone $this;
            $new->__fragment = $fragment;
            $new->__composedComponents = null;
            return $new;
        }
        public function jsonSerialize(): string{
            return $this->__toString();
        }
        private function __applyParts(array $parts): void{
            $this->__scheme = isset($parts['scheme'])
                ? $this->__filterScheme($parts['scheme'])
                : '';
            $this->__userInfo = isset($parts['user'])
                ? $this->__filterUserInfoComponent($parts['user'])
                : '';
            $this->__host = isset($parts['host'])
                ? $this->__filterHost($parts['host'])
                : '';
            $this->__port = isset($parts['port'])
                ? $this->__filterPort($parts['port'])
                : null;
            $this->__path = isset($parts['path'])
                ? $this->__filterPath($parts['path'])
                : '';
            $this->__query = isset($parts['query'])
                ? $this->__filterQueryAndFragment($parts['query'])
                : '';
            $this->__fragment = isset($parts['fragment'])
                ? $this->__filterQueryAndFragment($parts['fragment'])
                : '';
            if (isset($parts['pass']))
                $this->__userInfo .= ':' . $this->__filterUserInfoComponent($parts['pass']);
            $this->__removeDefaultPort();
            return null;
        }
        private function __filterScheme($scheme): string{
            if (!is_string($scheme))
                throw new \InvalidArgumentException('Scheme must be a string');
            return \strtr($scheme, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
        }
        private function __filterUserInfoComponent($component): string{
            if (!is_string($component))
                throw new \InvalidArgumentException('User info must be a string');
            return preg_replace_callback(
                '/(?:[^%' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . ']+|%(?![A-Fa-f0-9]{2}))/',
                [$this, '__raw_url_encodeMatchZero'],
                $component
            );
        }
        private function __filterHost($host): string{
            if (!is_string($host))
                throw new \InvalidArgumentException('Host must be a string');
            return \strtr($host, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
        }
        private function __filterPort($port): ?int{
            if ($port === null) return null;
            $port = (int) $port;
            if (0 > $port || 0xffff < $port)
                throw new \InvalidArgumentException(
                    sprintf('Invalid port: %d. Must be between 0 and 65535', $port)
                );
            return $port;
        }
        private static function __getFilteredQueryString(UriInterface $uri, array $keys): array{
            $current = $uri->getQuery();
            if ($current === '')return [];
            $decodedKeys = array_map('rawurldecode', $keys);
            return array_filter(explode('&', $current), static function ($part) use ($decodedKeys) {
                return !in_array(rawurldecode(explode('=', $part)[0]), $decodedKeys, true);
            });
        }
        private static function __generateQueryString(string $key, ?string $value): string{
            $queryString = strtr($key, self::QUERY_SEPARATORS_REPLACEMENT);
            if ($value !== null)
                $queryString .= '=' . strtr($value, self::QUERY_SEPARATORS_REPLACEMENT);
            return $queryString;
        }
        private function __removeDefaultPort(): void{
            if ($this->__port !== null && self::isDefaultPort($this))
                $this->__port = null;
            return null;
        }
        private function __filterPath($path): string{
            if (!is_string($path))
                throw new \InvalidArgumentException('Path must be a string');
            return preg_replace_callback(
                '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . '%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
                [$this, '__raw_url_encodeMatchZero'],
                $path
            );
        }
        private function __filterQueryAndFragment($str): string{
            if (!is_string($str))
                throw new \InvalidArgumentException('Query and fragment must be a string');
            return preg_replace_callback(
                '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
                [$this, '__raw_url_encodeMatchZero'],
                $str
            );
        }
        private function __raw_url_encodeMatchZero(array $match): string{
            return rawurlencode($match[0]);
        }
        private function __validateState(): void{
            if ($this->__host === '' && ($this->__scheme === 'http' || $this->__scheme === 'https'))
                $this->__host = self::HTTP_DEFAULT_HOST;
            if ($this->getAuthority() === '') {
                if (0 === strpos($this->__path, '//'))
                    throw new MalformedUriException('The path of a URI without an authority must not start with two slashes "//"');
                if ($this->__scheme === '' && false !== strpos(explode('/', $this->__path, 2)[0], ':'))
                    throw new MalformedUriException('A relative URI must not have a path beginning with a segment containing a colon');
            } elseif (isset($this->__path[0]) && $this->__path[0] !== '/')
                throw new MalformedUriException('The path of a URI with an authority must start with a slash "/" or be empty');
            return null;
        }
    }
}else die;