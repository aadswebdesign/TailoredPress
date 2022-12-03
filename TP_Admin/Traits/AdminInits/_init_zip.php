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
        protected $_zip;
        protected function _init_zip($p_zipname = null):Adm_Zip{
            if(!($this->_zip instanceof Adm_Zip)){
                $this->_zip = new Adm_Zip($p_zipname);
            }
            return $this->_zip;
        }
    }
}else die;