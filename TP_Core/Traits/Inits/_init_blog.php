<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-5-2022
 * Time: 22:18
 */
namespace TP_Core\Traits\Inits;
if(ABSPATH){
    trait _init_blog{
        //todo, for now has to be worked out
        protected $_tp_blog_id = [];
        protected function _init_blog_id($id=null):array{
            if(!empty($id)) $this->_tp_blog_id[$id];
            return $this->_tp_blog_id;
        }
    }
}else die;