<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 12-5-2022
 * Time: 19:14
 */
namespace TP_Core\Traits\Templates\Components;
if(ABSPATH){
    class comments_popup_template_view{
        private $__html;
        private $__args;
        private $__zero;
        private $__one;
        private $__more;
        private $__empty;
        private $__enter_password;
        public function __construct(...$args){
            $this->__args = $args;
            $this->__zero = $this->__args['zero'] ?? '';
            $this->__one = $this->__args['one'] ?? '';
            $this->__more = $this->__args['more'] ?? '';
            $this->__empty = $this->__args['empty'] ?? '';
            $this->__enter_password = $this->__args['enter_password'] ?? '';
        }
        private function __to_string():string{
            ob_start();
            $this->__zero;
            $this->__one;
            $this->__more;
            $this->__empty;
            $this->__enter_password;
            $this->__html = "<a href='{$this->__args['respond_link']}' {$this->__args['respond_css']} {$this->__args['attributes']}>{$this->__args['comments_number']}</a>";
            return (string) $this->__html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;

