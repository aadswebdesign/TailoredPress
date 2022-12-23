<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-12-2022
 * Time: 08:22
 */
namespace TP_Content\Themes;
use TP_Core\Traits\TP_Template_Loader;
use TP_Core\Traits\User\_user_02;
use TP_Core\Traits\User\_user_03;
//for now
//use TP_Content\Themes\Tailored_One\Theme_Index;
if(ABSPATH){
    class ThemePanel{
        use TP_Template_Loader;
        use _user_02,_user_03;
        protected $_args;
        protected $_theme;
        public function __construct($args = null){
            $this->__tpl_construct($args);
            //$this->_theme = new Theme_Index();
        }
        private function __to_string():string{
            return $this->__tpl_to_string(); //todo This has to become a theme loader method
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}