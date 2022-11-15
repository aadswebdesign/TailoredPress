<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-8-2022
 * Time: 14:22
 */
declare(strict_types=1);
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\AssetsTools\TP_Scripts;
use TP_Core\Libs\AssetsTools\TP_LinkStyle;
if(ABSPATH){
    trait _init_assets{
        protected $_tp_scripts;
        protected $_tp_styles;
        protected function _init_scripts(): TP_Scripts{
            if(!($this->_tp_scripts instanceof TP_Scripts))
                $this->_tp_scripts = new TP_Scripts();
            return $this->_tp_scripts;
        }
        protected function _init_styles(): TP_LinkStyle{
            if(!($this->_tp_styles instanceof TP_LinkStyle))
                $this->_tp_styles = new TP_LinkStyle();
            return $this->_tp_styles;
        }

    }
}else{die;}