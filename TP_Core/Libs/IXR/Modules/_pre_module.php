<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 13:34
 */
namespace TP_Core\Libs\IXR\Modules;
use TP_Core\Traits\Formats\_formats_08;
if(ABSPATH){
    class _pre_module{
        use _formats_08;
        private $__html;
        private $__char_value;
        private $__pre_class;
        public function __construct($pre_class=null,$char_value = null){
            $this->__pre_class = $pre_class ?: 'ixr';
            if(!is_null($char_value)) $this->__char_value = htmlspecialchars($char_value);
        }
        private function __to_string(): string{
            $this->__html = $this->_esc_html("<pre class='{$this->__pre_class}'>{$this->__char_value}\n</pre>\n\n");
            return (string) $this->__html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}