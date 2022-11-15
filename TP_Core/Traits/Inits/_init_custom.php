<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 06:11
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\Customs\TP_Customize_Manager;
use TP_Core\Libs\Customs\TP_Customize_Setting;
if(ABSPATH){
    trait _init_custom{
        protected $_tp_customize_manager;
        protected $_tp_customize_setting;
        protected function _init_customize_manager(...$args):TP_Customize_Manager{
            if(!($this->_tp_customize_manager instanceof TP_Customize_Manager)){
                $this->_tp_customize_manager = new TP_Customize_Manager($args);
            }
            return $this->_tp_customize_manager;
        }
        protected function _init_customize_setting($manager = null,$id= null):TP_Customize_Setting{
            if(!($this->_tp_customize_setting instanceof TP_Customize_Setting)){
                $this->_tp_customize_setting = new TP_Customize_Setting($manager,$id);
            }
            return $this->_tp_customize_setting;
        }
    }
}else die;