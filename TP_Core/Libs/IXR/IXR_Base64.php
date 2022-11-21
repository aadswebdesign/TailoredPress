<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 15:47
 */
namespace TP_Core\Libs\IXR;
if(ABSPATH){
    class IXR_Base64{
        protected $_data;
        public function __construct($data){
            $this->_data = $data;
        }
        public function getXml(): string{
            return "<base64>{base64_encode($this->_data)}</base64>";
        }
    }
}else{die;}