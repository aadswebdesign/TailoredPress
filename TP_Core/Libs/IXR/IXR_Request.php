<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 15:14
 */
namespace TP_Core\Libs\IXR;
use TP_Core\Traits\Formats\_formats_08;
if(ABSPATH){
    class IXR_Request{
        use _formats_08;
        private $__html;
        protected $_method;
        protected $_args;
        protected $_xml;
        public function __construct($method, $args){
            $this->_method = $method;
            $this->_args = $args;
            $this->_xml = $this->__toString();
        }
        private function __to_string(): string{
            $this->__html = $this->_esc_html("<?xml version='1.0'>");
            $this->__html .= $this->_esc_html("<methodCall>");
            $this->__html .= $this->_esc_html("<methodName>{$this->_method}</methodName>");
            $this->__html .= $this->_esc_html("<params>");
            foreach ($this->_args as $arg) {
                $ixr_value = new IXR_Value($arg);
                $this->__html .= $this->_esc_html("<param><value>");
                $this->__html .= $this->_esc_html($ixr_value->getXml());
                $this->__html .= $this->_esc_html("</value></param>\n");
            }
            $this->__html .= $this->_esc_html("</params>");
            $this->__html .= $this->_esc_html("</methodCall>");
            return (string) $this->__html;
        }
        public function __toString(){
            return $this->__to_string();
        }
        public function getLength(): string{
            return strlen($this->_xml);
        }
        public function getXml(): string
        {
            return $this->_xml;
        }
    }
}else{die;}