<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-11-2022
 * Time: 16:21
 */
namespace TP_Admin\Traits\AdminInits;
use TP_Admin\Libs\AdmFilesystem\Adm_Filesystem_Base;
if(ABSPATH){
    trait _adm_init_files {
        protected $_adm_file_system;
        protected function _init_files($arg = null):Adm_Filesystem_Base{
            if(!($this->_adm_file_system instanceof Adm_Filesystem_Base) ){
                $this->_adm_file_system = new Adm_Filesystem_Base($arg);
            }
            return $this->_adm_file_system;
        }
    }
}else{die;}

