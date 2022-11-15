<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-5-2022
 * Time: 15:52
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_Theme;
if(ABSPATH){
    trait _init_theme{
        protected $_tp_theme;
        protected $_tp_theme_directories;
        /**
         * @param string $theme_dir
         * @param string $theme_root
         * @return TP_Theme
         */
        protected function _init_theme($theme_dir = '',$theme_root = ''):TP_Theme{
            if(!($this->_tp_theme instanceof TP_Theme))
                $this->_tp_theme = new TP_Theme($theme_dir,$theme_root );
            return $this->_tp_theme;
        }
    }
}else die;