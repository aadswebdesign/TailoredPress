<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-7-2022
 * Time: 10:01
 */
namespace TP_Admin\Traits\AdminInits;
use TP_Admin\Libs\AdmPartials\Adm_Partial_Compats;
use TP_Admin\Libs\AdmPartials\Adm_Partial_Themes_Install_Block;
if(ABSPATH){
    trait _init_list_blocks{
        protected $_adm_list_block_compat;
        protected $_adm_theme_list_block_install;
        protected function _init_block_compat( $screen, $with_id = true ):Adm_Partial_Compats{
            if(!($this->_adm_list_block_compat instanceof Adm_Partial_Compats)){
                $this->_adm_list_block_compat = new Adm_Partial_Compats( $screen, $with_id);
            }
            return $this->_adm_list_block_compat;
        }
        protected function _init_theme_block_install():Adm_Partial_Themes_Install_Block{
            if(!($this->_adm_theme_list_block_install instanceof Adm_Partial_Themes_Install_Block)){
                $this->_adm_theme_list_block_install = new Adm_Partial_Themes_Install_Block();
            }
            return $this->_adm_theme_list_block_install;
        }
    }
}else die;