<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-5-2022
 * Time: 12:34
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_Metadata_Lazyloader;
if(ABSPATH){
    trait _init_meta{
        protected $_tp_meta_data_lazy_loader;
        protected function _init_meta_data_lazy_loader():TP_Metadata_Lazyloader{
            if(!($this->_tp_meta_data_lazy_loader instanceof TP_Metadata_Lazyloader)){
                $this->_tp_meta_data_lazy_loader = new TP_Metadata_Lazyloader();
            }
            return $this->_tp_meta_data_lazy_loader;
        }
    }
}else die;