<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-3-2022
 * Time: 05:20
 */
namespace TP_Core\Traits\Templates\Modules;
use TP_Core\Traits\Formats\_formats_08;
if(ABSPATH){
    class _link_module {
        use _formats_08;
        private $__html;
        public function __construct()
        {
        }
        private function __to_string():string{

            $this->__html = $this->_esc_html("<link ");
            $this->__html .= $this->_esc_html("");
            $this->__html .= $this->_esc_html("/>");
            return (string) $this->__html;
        }

        public function __toString()
        {
            return $this->__to_string();
        }

    }
}else die;
