<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 16:16
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Cookie;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Utils;
if(ABSPATH){
    class FileCookieJar extends CookieJar{
        private $__filename;
        private $__storeSessionCookies;
        public function __construct(string $cookieFile, bool $storeSessionCookies = false){
            parent::__construct();
            $this->__filename = $cookieFile;
            $this->__storeSessionCookies = $storeSessionCookies;
            if (\file_exists($cookieFile)){$this->load($cookieFile);}
        }
        public function __destruct(){
            $this->save($this->__filename);
        }
        public function save(string $filename): void{
            $json = [];
            foreach ($this as $cookie) {
                if (CookieJar::shouldPersist($cookie, $this->__storeSessionCookies)){$json[] = $cookie->toArray();}
            }
            $jsonStr = Utils::jsonEncode($json);
            if (false === \file_put_contents($filename, $jsonStr, \LOCK_EX)){throw new \RuntimeException("Unable to save file {$filename}");}
            return null;
        }
        public function load(string $filename): void{
            $json = \file_get_contents($filename);
            if (false === $json){throw new \RuntimeException("Unable to load file {$filename}");}
            if ($json === ''){ return;}
            $data = Utils::jsonDecode($json, true);
            if (\is_array($data)) {
                foreach ($data as $cookie){$this->setCookie(new SetCookie($cookie));}
            } elseif (\is_scalar($data) && !empty($data)){throw new \RuntimeException("Invalid cookie file: {$filename}");}
            return null;
        }
    }
}else{die;}