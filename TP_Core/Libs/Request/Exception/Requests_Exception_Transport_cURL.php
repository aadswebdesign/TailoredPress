<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 18:12
 */
namespace TP_Core\Libs\Request\Exception;
if(ABSPATH){
    class Requests_Exception_Transport_cURL extends Requests_Exception_Transport{
        public const EASY  = 'cURLEasy';
        public const MULTI = 'cURLMulti';
        public const SHARE = 'cURLShare';
        protected $_code = -1;
        protected $_reason = 'Unknown';
        protected $_type = 'Unknown';
        public function __construct($message, $type, $data = null, $code = 0) {
            if ($type !== null) $this->_type = $type;
            if ($code !== null) $this->_code = $code;
            if ($message !== null) $this->_reason = $message;
            $message = sprintf('%d %s', $this->_code, $this->_reason);
            parent::__construct($message, $this->_type, $data, $this->_code);
        }
        public function getReason(): string{
            return $this->_reason;
        }
    }
}else die;