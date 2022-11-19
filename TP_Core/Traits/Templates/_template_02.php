<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 15:46
 */
namespace TP_Core\Traits\Templates;
if(ABSPATH){
    trait _template_02 {
        protected function _get_front_page_template(){
        }//413 uses __get_query_template
        protected function _get_privacy_policy_template(){
        }//431
        protected function _get_page_template(){
        }//465 todo
        protected function _get_search_template(){
        }//510 todo
        protected function _get_single_template(){
        }//545 todo
        protected function _get_embed_template(){
        }//594
        protected function _get_singular_template(){
        }//624
        protected function _get_attachment_template() {
        }//657 todo
        protected function _locate_template($path=null,$template_classes =null, ...$args):string{return '';}//697
        //protected function _locate_theme($theme_class):?string{}//added
    }
}else die;