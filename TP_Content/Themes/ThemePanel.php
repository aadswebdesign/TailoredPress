<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-12-2022
 * Time: 08:22
 */

namespace TP_Content\Themes;

use TP_Core\Traits\Methods\_methods_21;

if(ABSPATH){
    class ThemePanel{
        use _methods_21;
        protected $_args;
        public function __construct($args = null){
            if (! defined( 'TP_NS_CONTENT' ) ) define('TP_NS_CONTENT','TP_Content\\');
            if (! defined( 'TP_NS_THEMES' ) ) define('TP_NS_THEMES',TP_NS_CONTENT.'Themes\\');
            $this->_args = $args;
        }
        public function __toString():string{
            //todo this is what it is for now
            return $this->_tp_load_class('Theme_Index',TP_NS_THEMES,'Tailored_One\\Theme_Index');
        }
    }
}else{die;}
