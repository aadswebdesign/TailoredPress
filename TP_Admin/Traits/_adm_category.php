<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-5-2022
 * Time: 07:23
 */
namespace TP_Admin\Traits;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _adm_category{
        use _init_error;
        /**
         * @description Check whether a category exists.
         * @param $cat_name
         * @param null $parent
         * @return string
         */
        protected function _category_exists( $cat_name, $parent = null ):string{
            $id = $this->_term_exists( $cat_name, 'category', $parent );
            if ( is_array( $id ) ) { $id = $id['term_id'];}
            return $id;
        }//24
        /**
         * @description Get category object for given ID and 'edit' filter context.
         * @param $id
         * @return string
         */
        protected function _get_category_to_edit( $id ):string{
            $category = $this->_get_term( $id, 'category', OBJECT, 'edit' );
            $this->_make_cat_compat( $category );
            return $category;
        }//40
        /**
         * @description Add a new category to the database if it does not already exist.
         * @param $cat_name
         * @param int $parent
         * @return string
         */
        protected function _tp_create_category( $cat_name, $parent = 0 ):string{
            $id = $this->_category_exists( $cat_name, $parent );
            if ( $id ) { return $id;}
            return $this->_tp_insert_category(['cat_name' => $cat_name,'category_parent' => $parent,]);
        }//55
        /**
         * @description Create categories for the given post.
         * @param $categories
         * @param string $post_id
         * @return string
         */
        protected function _tp_create_categories( $categories, $post_id = '' ):string{
            $cat_ids = [];
            foreach ( $categories as $category ) {
                $id = $this->_category_exists( $category );
                if ( $id ) { $cat_ids[] = $id;}
                else {
                    $id = $this->_tp_create_category( $category );
                    if ( $id ) { $cat_ids[] = $id;}
                }
            }
            if ( $post_id ) { $this->_tp_set_post_categories( $post_id, $cat_ids );}
            return $cat_ids;
        }//78
        /**
         * @description Updates an existing Category or creates a new Category.
         * @param $cat_arr
         * @param bool $tp_error
         * @return string
         */
        protected function _tp_insert_category( $cat_arr, $tp_error = false ):string{
            $cat_defaults = ['cat_ID' => 0,'taxonomy' => 'category','cat_name' => '',
                'category_description' => '','category_nicename' => '','category_parent' => '',];
            $cat_arr = $this->_tp_parse_args( $cat_arr, $cat_defaults );
            if ( '' === trim( $cat_arr['cat_name'] ) ) {
                if ( ! $tp_error ) { return 0;}
                return new TP_Error( 'cat_name', $this->__( 'You did not enter a category name.' ) );
            }
            $cat_arr['cat_ID'] = (int) $cat_arr['cat_ID'];
            $update = ! empty( $cat_arr['cat_ID'] );
            $name  = $cat_arr['cat_name'];
            $description = $cat_arr['category_description'];
            $slug = $cat_arr['category_nicename'];
            $parent = (int) $cat_arr['category_parent'];
            if ( $parent < 0 ) { $parent = 0;}
            if ( empty( $parent )
                || ! $this->_term_exists( $parent, $cat_arr['taxonomy'] )
                || ( $cat_arr['cat_ID'] && $this->_term_is_ancestor_of( $cat_arr['cat_ID'], $parent, $cat_arr['taxonomy'] ) ) ) {
                $parent = 0;
            }
            $args = compact( 'name', 'slug', 'parent', 'description' );
            if ( $update ) { $cat_arr['cat_ID'] = $this->_tp_update_term( $cat_arr['cat_ID'], $cat_arr['taxonomy'], $args );}
            else { $cat_arr['cat_ID'] = $this->_tp_insert_term( $cat_arr['cat_name'], $cat_arr['taxonomy'], $args );}
            if ( $this->_init_error( $cat_arr['cat_ID'] ) ) {
                if ( $tp_error ) { return $cat_arr['cat_ID'];}
                return 0;
            }
            return $cat_arr['cat_ID']['term_id'];
        }//121
        /**
         * @description Aliases tp_insert_category() with minimal args.
         * @param $cat_arr
         * @return string
         */
        protected function _tp_update_category( $cat_arr ):string{
            $cat_ID = (int) $cat_arr['cat_ID'];
            if ( isset( $cat_arr['category_parent'] ) && ( $cat_ID === $cat_arr['category_parent'] ) ) {
                return false;}
            $category = $this->_get_term( $cat_ID, 'category', ARRAY_A );
            $this->_make_cat_compat( $category );
            $category = $this->_tp_slash( $category );
            $cat_arr = array_merge( $category, $cat_arr );
            return $this->_tp_insert_category( $cat_arr );
        }//188
        /**
         * @description Check whether a post tag with a given name exists.
         * @param $tag_name
         * @return string
         */
        protected function _tag_exists( $tag_name ):string{
            return $this->_term_exists( $tag_name, 'post_tag' );
        }//222
        /**
         * @description Add a new tag to the database if it does not already exist.
         * @param $tag_name
         * @return string
         */
        protected function _tp_create_tag( $tag_name ):string{
            return $this->_tp_create_term( $tag_name, 'post_tag' );
        }//234
        /**
         * @description Get comma-separated list of tags available to edit.
         * @param $post_id
         * @param string $taxonomy
         * @return string
         */
        protected function _get_tags_to_edit( $post_id, $taxonomy = 'post_tag' ):string{
            return $this->_get_terms_to_edit( $post_id, $taxonomy );
        }//249
        /**
         * @description Get comma-separated list of terms available to edit for the given post ID.
         * @param $post_id
         * @param string $taxonomy
         * @return string
         */
        protected function _get_terms_to_edit( $post_id, $taxonomy = 'post_tag' ):string{
            $post_id = (int) $post_id;
            if ( ! $post_id ) { return false;}
            $terms = $this->_get_object_term_cache( $post_id, $taxonomy );
            if ( false === $terms ) {
                $terms = $this->_tp_get_object_terms( $post_id, $taxonomy );
                $this->_tp_cache_add( $post_id, $this->_tp_list_pluck( $terms, 'term_id' ), $taxonomy . '_relationships' );
            }
            if ( ! $terms ) {return false;}
            if ( $this->_init_error( $terms )){ return $terms;}
            $term_names = [];
            foreach ( $terms as $term ) {$term_names[] = $term->name;}
            $terms_to_edit = $this->_esc_attr( implode( ',', $term_names ) );
            $terms_to_edit = $this->_apply_filters( 'terms_to_edit', $terms_to_edit, $taxonomy );
            return $terms_to_edit;
        }//260
        /**
         * @description Add a new term to the database if it does not already exist.
         * @param $tag_name
         * @param string $taxonomy
         * @return string
         */
        protected function _tp_create_term( $tag_name, $taxonomy = 'post_tag' ):string{
            $id = $this->_term_exists( $tag_name, $taxonomy );
            if ( $id ) {return $id;}
            return $this->_tp_insert_term( $tag_name, $taxonomy );
        }//309
    }
}else die;