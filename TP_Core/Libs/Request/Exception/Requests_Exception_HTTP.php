<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 16:01
 */
namespace TP_Core\Libs\Request\Exception;
if(ABSPATH){
    class Requests_Exception_HTTP extends Requests_Exception{
        protected $_code = 0;
        protected $_reason = 'Unknown';
        public function __construct($reason = null, $data = null) {
            if ($reason !== null) $this->_reason = $reason;
            $message = sprintf('%d %s', $this->_code, $this->_reason);
            parent::__construct($message, 'httpresponse', $data, $this->_code);
        }
        public function getReason() {
            return $this->_reason;
        }
        public static function get_class($code) {
            if (!$code) return 'Requests_Exception_HTTP_Unknown';
            $class = sprintf('Requests_Exception_HTTP_%d', $code);
            if (class_exists($class)) return $class;
            return 'Requests_Exception_HTTP_Unknown';
        }
    }
}else die;