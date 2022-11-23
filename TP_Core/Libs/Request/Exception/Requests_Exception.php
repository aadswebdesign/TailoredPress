<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 12:48
 */
namespace TP_Core\Libs\Request\Exception;
if(ABSPATH){
    class Requests_Exception extends \Exception{
        protected $_type;
        protected $_data;
        public function __construct($message, $type, $data = null, $code = 0) {
            parent::__construct($message, $code);
            $this->_type = $type;
            $this->_data = $data;
        }
        public function getType(): int{
            return $this->_type;
        }
        public function getData() {
            return $this->_data;
        }
    }
}else die;