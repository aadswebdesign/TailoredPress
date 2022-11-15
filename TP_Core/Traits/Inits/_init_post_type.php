<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-4-2022
 * Time: 19:13
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\Post\TP_Post_Type;
if(ABSPATH){
    trait _init_post_type{
        protected $_tp_post_type;
        protected $_tp_post_types;
        protected $_tp_post_type_features;
        protected function _init_post_types($post_type= null,...$args):TP_Post_Type{
            if(!($this->_tp_post_type instanceof TP_Post_Type))
                $this->_tp_post_type = new TP_Post_Type($post_type,$args);
            return $this->_tp_post_type;
        }
    }
}else die;