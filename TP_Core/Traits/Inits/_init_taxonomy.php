<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-6-2022
 * Time: 13:52
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_Taxonomy;
if(ABSPATH){
    trait _init_taxonomy{
        protected $_tp_taxonomies;
        protected function _init_taxonomy($taxonomy = '',$object_type =''): TP_Taxonomy{
            if(!($this->_tp_taxonomies instanceof TP_Taxonomy))
                $this->_tp_taxonomies = new TP_Taxonomy($taxonomy, $object_type);
            return $this->_tp_taxonomies;
        }
    }
}else die;

