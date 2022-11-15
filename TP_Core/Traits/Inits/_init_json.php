<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-8-2022
 * Time: 23:48
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\JSON\TP_Theme_JSON;
if(ABSPATH){
    trait _init_json{
        protected $_tp_theme_json;
        protected function _init_theme_json( $theme_json = [], $origin = 'theme'):TP_Theme_JSON{
            if(!($this->_tp_theme_json instanceof TP_Theme_JSON))
                $this->_tp_theme_json = new TP_Theme_JSON( $theme_json, $origin);
            return $this->_tp_theme_json;
        }
    }
}else{die;}