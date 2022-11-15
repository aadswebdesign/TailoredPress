<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-5-2022
 * Time: 10:03
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\Embed\TP_Embed;
use TP_Core\Libs\Embed\TP_ObjEmbed;
if(ABSPATH){
    trait _init_embed{
        protected $_content_width;
        protected $_tp_embed;
        protected $_tp_obj_embed;
        protected function _init_embed():TP_Embed{
            if(!($this->_tp_embed instanceof TP_Embed))
                $this->_tp_embed = new TP_Embed();
            return $this->_tp_embed;
        }
        protected function _init_obj_embed():TP_ObjEmbed{
            if(!($this->_tp_obj_embed instanceof TP_ObjEmbed))
                $this->_tp_obj_embed = new TP_ObjEmbed();
            return $this->_tp_obj_embed;
        }
    }
}else die;