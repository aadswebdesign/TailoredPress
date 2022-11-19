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
        protected function _get_query_template($type,...$templates):string{ }//23
        //protected function _get_index_template(){}//118
        //protected function _get_404_template(){}//134 uses __get_query_template
        //protected function _get_archive_template(){}//150 todo
        //protected function _get_post_type_archive_template(){}//176
        //protected function _get_author_template(){}//214 todo
        //protected function _get_category_template() {}//254 todo
        //protected function _get_tag_template(){}//318 todo
        //protected function _get_taxonomy_template(){}//346 todo
        //protected function _get_date_template(){}//379
        //protected function _get_home_template(){}//395 uses __get_query_template
    }
}else die;