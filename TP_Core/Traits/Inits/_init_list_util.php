<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-4-2022
 * Time: 16:09
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_List_Util;
if(ABSPATH){
    trait _init_list_util{
        protected $_tp_list_util;
        protected function _init_list_util($input):TP_List_Util{
            if(!($this->_tp_list_util instanceof TP_List_Util))
                $this->_tp_list_util = new TP_List_Util($input);
            return $this->_tp_list_util;
        }
    }
}else die;