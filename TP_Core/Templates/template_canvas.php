<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-5-2022
 * Time: 09:09
 */

namespace TP_Core\Assets\Templates;
if(ABSPATH){
    class template_canvas{
        private $__html;
        public function __construct()
        {
        }
        private function __to_string():string{
            $this->__html = 'template_canvas';
            return (string) $this->__html;
        }
        public function __toString(){
            return $this->__to_string();
        }

    }
}else die;

