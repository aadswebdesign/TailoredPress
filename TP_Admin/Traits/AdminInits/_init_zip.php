<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-7-2022
 * Time: 10:01
 */
namespace TP_Admin\Traits\AdminInits;
use TP_Admin\Libs\Adm_Zip;
if(ABSPATH){
    trait _init_zip{
        protected $_adm_zip;
        protected function _init_zip($p_zipname = null):Adm_Zip{
            if(!($this->_adm_zip instanceof Adm_Zip)){
                $this->_adm_zip = new Adm_Zip($p_zipname);
            }
            return $this->_adm_zip;
        }
    }
}else die;