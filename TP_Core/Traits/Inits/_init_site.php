<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-5-2022
 * Time: 04:20
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_Site;
if(ABSPATH){
    trait _init_site{
        protected $_tp_site;
        protected function _init_site($site=''):TP_Site{
            if(!($this->_tp_site instanceof TP_Site))
                $this->_tp_site = new TP_Site($site);
            return $this->_tp_site;
        }
        protected function _init_current_site(){
            return $this->_init_site()->blog_id;
        }
    }
}else die;