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
        protected function _get_front_page_template():string{
            $output  = "";
            $output .= "";
            $output .= "";
            $output .= "</br>_get_front_page_template";
            $output .= "";
            $output .= "";
            return $output;
        }//413 uses __get_query_template
        protected function _get_privacy_policy_template():string{
            $output  = "";
            $output .= "";
            $output .= "";
            $output .= "</br>_get_privacy_policy_template";
            $output .= "";
            $output .= "";
            return $output;
        }//431
        protected function _get_page_template():string{
            $output  = "";
            $output .= "";
            $output .= "";
            $output .= "</br>_get_page_template";
            $output .= "";
            $output .= "";
            return $output;
        }//465 todo
        protected function _get_search_template():string{
            $output  = "";
            $output .= "";
            $output .= "";
            $output .= "</br>_get_search_template";
            $output .= "";
            $output .= "";
            return $output;
        }//510 todo
        protected function _get_single_template():string{
            $output  = "";
            $output .= "";
            $output .= "";
            $output .= "</br>_get_single_template";
            $output .= "";
            $output .= "";
            return $output;
        }//545 todo
        protected function _get_embed_template():string{
            //$post_formats; standard (default), aside, gallery, link, image, quote, status, video, audio and chat.
            //seven post types; posts, pages, attachments, revisions, navigation menus, custom CSS, and changesets
            $output  = "";
            $output .= "</br>_get_embed_template</br>";
            $output .= "";
            return $output;
        }//594
        protected function _get_singular_template():string{
            $output  = "";
            $output .= "";
            $output .= "";
            $output .= "</br>_get_singular_template";
            $output .= "";
            $output .= "";
            return $output;
        }//624
        protected function _get_attachment_template():string {
            $output  = "";
            $output .= "";
            $output .= "";
            $output .= "</br>_get_attachment_template";
            $output .= "";
            $output .= "";
            return $output;
        }//657 todo
        protected function _locate_template($template_classes, $load = false, ...$args):string{//$path=null,$template_classes =null, ...$args
            $located = '';
            foreach ( (array) $template_classes as $template_class ) {
                if ( ! $template_class ) continue;



            }

            $output  = "";
            $output .= "_locate_template";
            return $output;
        }//697
        //protected function _locate_theme($theme_class):?string{}//added
    }
}else die;