<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 04:03
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Cookie;
if(ABSPATH){
    /** @noinspection PhpInconsistentReturnPointsInspection */
    class SetCookie{
        private $__data;
        private static $__defaults = [
            'Name'     => null,
            'Value'    => null,
            'Domain'   => null,
            'Path'     => '/',
            'Max-Age'  => null,
            'Expires'  => null,
            'Secure'   => false,
            'Discard'  => false,
            'HttpOnly' => false
        ];
        public static function fromString(string $cookie): self{
            $data = self::$__defaults;
            $pieces = \array_filter(\array_map('trim', \explode(';', $cookie)));
            if (!isset($pieces[0]) || \strpos($pieces[0], '=') === false){return new self($data);}
            foreach ($pieces as $part) {
                $cookieParts = \explode('=', $part, 2);
                $key = \trim($cookieParts[0]);
                $value = isset($cookieParts[1])
                    ? \trim($cookieParts[1], " \n\r\t\0\x0B")
                    : true;
                if (!isset($data['Name'])) {
                    $data['Name'] = $key;
                    $data['Value'] = $value;
                } else {
                    foreach (\array_keys(self::$__defaults) as $search) {
                        if (!\strcasecmp($search, $key)) {
                            $data[$search] = $value;
                            continue 2;
                        }
                    }
                    $data[$key] = $value;
                }
            }
            return new self($data);
        }
        public function __construct(array $data = []){
            /** @var array|null $replaced will be null in case of replace error */
            $replaced = \array_replace(self::$__defaults, $data);
            if ($replaced === null) {
                throw new \InvalidArgumentException('Unable to replace the default values for the Cookie.');
            }
            $this->__data = $replaced;
            if (!$this->getExpires() && $this->getMaxAge()) {
                $this->setExpires(\time() + $this->getMaxAge());
            } elseif (null !== ($expires = $this->getExpires()) && !\is_numeric($expires)) {
                $this->setExpires($expires);
            }
        }
        public function __toString(){
            $str = $this->__data['Name'] . '=' . ($this->__data['Value'] ?? '') . '; ';
            foreach ($this->__data as $k => $v) {
                if ($k !== 'Name' && $k !== 'Value' && $v !== null && $v !== false) {
                    if ($k === 'Expires') {
                        $str .= 'Expires=' . \gmdate('D, d M Y H:i:s \G\M\T', $v) . '; ';
                    } else {
                        $str .= ($v === true ? $k : "{$k}={$v}") . '; ';
                    }
                }
            }
            return \rtrim($str, '; ');
        }
        public function toArray(): array {
            return $this->__data;
        }
        public function getName(){
            return $this->__data['Name'];
        }
        public function setName($name): void{
            if (!is_string($name)) {
                /** @noinspection PhpUndefinedFunctionInspection */
                trigger_deprecation('guzzlehttp/guzzle', '7.4', 'Not passing a string to %s::%s() is deprecated and will cause an error in 8.0.', __CLASS__, __FUNCTION__);
            }
            $this->__data['Name'] = (string) $name;
            return null;
        }
        public function getValue(){
            return $this->__data['Value'];
        }
        public function setValue($value): void{
            if (!is_string($value)) {
                /** @noinspection PhpUndefinedFunctionInspection */
                trigger_deprecation('guzzlehttp/guzzle', '7.4', 'Not passing a string to %s::%s() is deprecated and will cause an error in 8.0.', __CLASS__, __FUNCTION__);
            }
            $this->__data['Value'] = (string) $value;
            return null;
        }
        public function getDomain(){
            return $this->__data['Domain'];
        }
        public function setDomain($domain): void{
            if (!is_string($domain) && null !== $domain) {
                /** @noinspection PhpUndefinedFunctionInspection */
                trigger_deprecation('guzzlehttp/guzzle', '7.4', 'Not passing a string or null to %s::%s() is deprecated and will cause an error in 8.0.', __CLASS__, __FUNCTION__);
            }
            $this->__data['Domain'] = null === $domain ? null : (string) $domain;
            return null;
        }
        public function getPath(){
            return $this->__data['Path'];
        }
        public function setPath($path): void{
            if (!is_string($path)) {
                /** @noinspection PhpUndefinedFunctionInspection */
                trigger_deprecation('guzzlehttp/guzzle', '7.4', 'Not passing a string to %s::%s() is deprecated and will cause an error in 8.0.', __CLASS__, __FUNCTION__);
            }
            $this->__data['Path'] = (string) $path;
            return null;
        }

        /** @noinspection ReturnTypeCanBeDeclaredInspection */
        public function getMaxAge(){
            return null === $this->__data['Max-Age'] ? null : (int) $this->__data['Max-Age'];
        }
        public function setMaxAge($maxAge): void{
            if (!is_int($maxAge) && null !== $maxAge) {
                /** @noinspection PhpUndefinedFunctionInspection */
                trigger_deprecation('guzzlehttp/guzzle', '7.4', 'Not passing an int or null to %s::%s() is deprecated and will cause an error in 8.0.', __CLASS__, __FUNCTION__);
            }
            $this->__data['Max-Age'] = $maxAge === null ? null : (int) $maxAge;
            return null;
        }
        public function getExpires(){
            return $this->__data['Expires'];
        }
        public function setExpires($timestamp): void{
            if (!is_int($timestamp) && !is_string($timestamp) && null !== $timestamp) {
                /** @noinspection PhpUndefinedFunctionInspection */
                trigger_deprecation('guzzlehttp/guzzle', '7.4', 'Not passing an int, string or null to %s::%s() is deprecated and will cause an error in 8.0.', __CLASS__, __FUNCTION__);
            }
            /** @noinspection PhpUndefinedFunctionInspection */
            if (\is_numeric($timestamp)) {
                $this->__data['Expires'] = null === $timestamp ? null : ((int)$timestamp);
            } else {
                $this->__data['Expires'] = null === $timestamp ? null : (\strtotime((string)$timestamp));
            }
            return null;
        }
        public function getSecure(){
            return $this->__data['Secure'];
        }
        public function setSecure($secure): void {
            if (!is_bool($secure)) {
                /** @noinspection PhpUndefinedFunctionInspection */
                trigger_deprecation('guzzlehttp/guzzle', '7.4', 'Not passing a bool to %s::%s() is deprecated and will cause an error in 8.0.', __CLASS__, __FUNCTION__);
            }
            $this->__data['Secure'] = (bool) $secure;
            return null;
        }
        public function getDiscard(){
            return $this->__data['Discard'];
        }
        public function setDiscard($discard): void{
            if (!is_bool($discard)) {
                /** @noinspection PhpUndefinedFunctionInspection */
                trigger_deprecation('guzzlehttp/guzzle', '7.4', 'Not passing a bool to %s::%s() is deprecated and will cause an error in 8.0.', __CLASS__, __FUNCTION__);
            }
            $this->__data['Discard'] = (bool) $discard;
            return null;
        }
        public function getHttpOnly(){
            return $this->__data['HttpOnly'];
        }
        public function setHttpOnly($httpOnly): void{
            if (!is_bool($httpOnly)) {
                /** @noinspection PhpUndefinedFunctionInspection */
                trigger_deprecation('guzzlehttp/guzzle', '7.4', 'Not passing a bool to %s::%s() is deprecated and will cause an error in 8.0.', __CLASS__, __FUNCTION__);
            }
            $this->__data['HttpOnly'] = (bool) $httpOnly;
            return null;
        }
        public function matchesPath(string $requestPath): bool {
            $cookiePath = $this->getPath();
            // Match on exact matches or when path is the default empty "/"
            if ($cookiePath === '/' || $cookiePath === $requestPath) {
                return true;
            }
            // Ensure that the cookie-path is a prefix of the request path.
            if (0 !== \strpos($requestPath, $cookiePath)) {
                return false;
            }
            if (\substr($cookiePath, -1, 1) === '/') {
                return true;
            }
            return \substr((array) $requestPath, \strlen($cookiePath), 1) === '/';
        }
        public function matchesDomain(string $domain): bool{
            $cookieDomain = $this->getDomain();
            if (null === $cookieDomain) {
                return true;
            }
            $cookieDomain = \ltrim($cookieDomain, '.');
            if (!$cookieDomain || !\strcasecmp($domain, $cookieDomain)) {
                return true;
            }
            if (\filter_var($domain, \FILTER_VALIDATE_IP)) {
                return false;
            }
            return (bool) \preg_match('/\.' . \preg_quote($cookieDomain, '/') . '$/', $domain);
        }
        public function isExpired(): bool{
            return $this->getExpires() !== null && \time() > $this->getExpires();
        }
        public function validate(){
            $name = $this->getName();
            if ($name === '') {
                return 'The cookie name must not be empty';
            }
            if (\preg_match(
                '/[\x00-\x20\x22\x28-\x29\x2c\x2f\x3a-\x40\x5c\x7b\x7d\x7f]/',
                $name
            )) {
                return 'Cookie name must not contain invalid characters: ASCII '
                . 'Control characters (0-31;127), space, tab and the '
                . 'following characters: ()<>@,;:\"/?={}';
            }
            $value = $this->getValue();
            if ($value === null) {
                return 'The cookie value must not be empty';
            }
            $domain = $this->getDomain();
            if ($domain === null || $domain === '') {
                return 'The cookie domain must not be empty';
            }
            return true;
        }
    }
}else {die;}