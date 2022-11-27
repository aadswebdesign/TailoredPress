<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-6-2022
 * Time: 06:42
 */
namespace TP_Admin\Traits;
if(ABSPATH){
    trait _adm_taxonomy{
        //@description Check whether a category exists.
        protected function _category_exists( $cat_name, $parent = null ){return '';}//24
        //@description Get category object for given ID and 'edit' filter context.
        protected function _get_category_to_edit( $id ){return '';}//40
        //@description Add a new category to the database if it does not already exist.
        protected function _tp_create_category( $cat_name, $parent = 0 ){return '';}//55
        //@description Create categories for the given post.
        protected function _tp_create_categories( $categories, $post_id = '' ){return '';}//78
        //@description Updates an existing Category or creates a new Category.
        protected function _tp_insert_category( $cat_arr, $tp_error = false ){return '';}//121
        //@description Aliases wp_insert_category() with minimal args.
        protected function _tp_update_category( $cat_arr ){return '';}//188
        //@description Check whether a post tag with a given name exists.
        protected function _tag_exists( $tag_name ){return '';}//222
        //@description Add a new tag to the database if it does not already exist.
        protected function _tp_create_tag( $tag_name ){return '';}//234
        //@description Get comma-separated list of tags available to edit.
        protected function _get_tags_to_edit( $post_id, $taxonomy = 'post_tag' ){return '';}//247
        //@description Get comma-separated list of terms available to edit for the given post ID.
        protected function _get_terms_to_edit( $post_id, $taxonomy = 'post_tag' ){return '';}//260
        //@description Add a new term to the database if it does not already exist.
        protected function _tp_create_term( $tag_name, $taxonomy = 'post_tag' ){return '';}//309
    }
}else die;