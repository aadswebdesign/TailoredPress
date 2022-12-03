<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 15:29
 */
namespace TP_Core\Libs\IXR;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\I10n\_I10n_01;
if(ABSPATH){
    class IXR_Error {
        use _formats_08;
        use _I10n_01;
        private $__html;
        public $code;
        public $message;
        public function __construct( $code, $message ){
            $this->code = $code;
            $this->message = htmlspecialchars($message);
        }
        private function __to_string(): string{
            $this->__html  = $this->_esc_html("<?xml version='1.0'>");
            $this->__html .= $this->_esc_html("<methodResponse>");
            $this->__html .= $this->_esc_html("<fault><value><structure>");
            $this->__html .= $this->_esc_html("<member>");
            $this->__html .= $this->_esc_html("<name>");
            $this->__html .= $this->__('faultCode');
            $this->__html .= $this->_esc_html("</name>");
            $this->__html .= $this->_esc_html("<value><int>{$this->code}</int></value>");
            $this->__html .= $this->_esc_html("</member>");
            $this->__html .= $this->_esc_html("<member>");
            $this->__html .= $this->_esc_html("<name>");
            $this->__html .= $this->__('faultString');
            $this->__html .= $this->_esc_html("</name>");
            $this->__html .= $this->_esc_html("<value><string>{$this->message}</string></value>");
            $this->__html .= $this->_esc_html("</member>");
            $this->__html .= $this->_esc_html("</structure></value></fault>");
            $this->__html .= $this->_esc_html("</methodResponse>");
            return (string) $this->__html;
        }
        public function __toString(){
            return $this->__to_string();
        }
        public function getXml(): string
        {
            return $this->__toString();
        }
    }
}else{die;}