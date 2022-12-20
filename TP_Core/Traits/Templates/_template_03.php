<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 15:46
 */
namespace TP_Core\Traits\Templates;
//use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _template_03 {
        //not needed
        //protected function _load_template($_template_file, $require_once = true, array ...$args){}//749
        //protected function _locate_library_class($classes = null, $lib_path = null, ...$args){}
        //protected function _get_library_class($class){}
        //protected function _locate_admin_class($classes = null, $lib_path = null, ...$args){return '';}
        public function get_post_type_class():string{
            return '';
        }
    }
}else die;