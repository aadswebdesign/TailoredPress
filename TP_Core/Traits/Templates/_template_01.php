<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 15:46
 */
namespace TP_Core\Traits\Templates;
//use TP_Core\Libs\Post\TP_Post_Type;
//use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _template_01 {
        protected function _get_query_template($type,...$templates):string{
            $output  = "";
            $output .= "";
            $output .= "";
            $output .= "</br>_get_query_template";
            $output .= "";
            $output .= "";
            return $output;
        }//23
        protected function _get_index_template():string{
            $output  = "";
            $output .= "";
            $output .= "";
            $output .= "</br>_get_index_template";
            $output .= "";
            $output .= "";
            return $output;
        }//118
        protected function _get_404_template():string{
            $output  = "";
            $output .= "";
            $output .= "</br>_get_404_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//134 uses __get_query_template
        protected function _get_archive_template():string{
            $output  = "";
            $output .= "";
            $output .= "";
            $output .= "</br>_get_archive_template";
            $output .= "";
            $output .= "";
            return $output;
        }//150 todo
        protected function _get_post_type_archive_template():string{
            $output  = "";
            $output .= "";
            $output .= "</br>_get_post_type_archive_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//176
        protected function _get_author_template():string{
            $output  = "";
            $output .= "";
            $output .= "</br>_get_author_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//214 todo
        protected function _get_category_template():string {
            $output  = "";
            $output .= "";
            $output .= "</br>_get_category_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//254 todo
        protected function _get_tag_template():string{
            $output  = "";
            $output .= "";
            $output .= "</br>_get_tag_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//318 todo
        protected function _get_taxonomy_template():string{
            $output  = "";
            $output .= "";
            $output .= "</br>_get_taxonomy_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//346 todo
        protected function _get_date_template():string{
            $output  = "";
            $output .= "";
            $output .= "";
            $output .= "</br>_get_date_template";
            $output .= "";
            $output .= "";
            return $output;
        }//379
        protected function _get_home_template():string{
            $output  = "";
            $output .= "";
            $output .= "</br>_get_home_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//395 uses __get_query_template
    }
}else die;