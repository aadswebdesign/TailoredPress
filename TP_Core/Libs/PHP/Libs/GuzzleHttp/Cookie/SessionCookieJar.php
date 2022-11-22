<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-4-2022
 * Time: 14:31
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Cookie;
if(ABSPATH){
    class SessionCookieJar extends CookieJar{
        private $__sessionKey;
        private $__storeSessionCookies;
        public function __construct(string $sessionKey, bool $storeSessionCookies = false){
            parent::__construct();
            $this->__sessionKey = $sessionKey;
            $this->__storeSessionCookies = $storeSessionCookies;
            $this->_load();
        }
        public function __destruct(){
            $this->save();
        }
        public function save(): void{
            $json = [];
            foreach ($this as $cookie) {
                if (CookieJar::shouldPersist($cookie, $this->__storeSessionCookies)){$json[] = $cookie->toArray();}
            }
            $_SESSION[$this->__sessionKey] = \json_encode($json);
            return null;
        }
        protected function _load(): void{
            if (!isset($_SESSION[$this->__sessionKey])){return;}
            $data = \json_decode($_SESSION[$this->__sessionKey], true);
            if (\is_array($data)) {
                foreach ($data as $cookie) {$this->setCookie(new SetCookie($cookie));}
            } elseif (\$data === ''){ throw new \RuntimeException("Invalid cookie data");}
            return null;
        }
    }
}else{die;}