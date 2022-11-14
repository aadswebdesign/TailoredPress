<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-5-2022
 * Time: 11:06
 */
namespace TP_Core\Traits\Categories;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _category_01{
        use _init_error;
        /**
         * @description Retrieves a list of category objects.
         * @param array ...$args
         * @return array
         */
        protected function _get_categories( ...$args):array {
            $defaults = array( 'taxonomy' => 'category' );
            $args     = $this->_tp_parse_args( $args, $defaults );
            $args['taxonomy'] = $this->_apply_filters( 'get_categories_taxonomy', $args['taxonomy'], $args );
            $categories = $this->_get_terms( $args );
            if ( $this->_init_error( $categories ) ) $categories = [];
             else {
                $categories = (array) $categories;
                foreach ( array_keys( $categories ) as $k )
                    $this->_make_cat_compat( $categories[ $k ] );
            }
            return $categories;
        }//26
        /**
         * @description Retrieves category data given a category ID or category object.
         * @param $category
         * @param string $output
         * @param string $filter
         * @return mixed
         */
        protected function _get_category( $category, $output = OBJECT, $filter = 'raw' ) {
            $category = $this->_get_term( $category, 'category', $output, $filter );
            if ( $this->_init_error( $category ) )
                return $category;
            $this->_make_cat_compat( $category );
            return $category;
        }//70
        /**
         * @description Retrieves a category based on URL containing the category slug.
         * @param $category_path
         * @param bool $full_match
         * @param string $output
         * @return mixed
         */
        protected function _get_category_by_path( $category_path, $full_match = true, $output = OBJECT ) {
            $category_path  = rawurlencode( urldecode( $category_path ) );
            $category_path  = str_replace(array('%2F', '%20'), array('/', ' '), $category_path);
            $category_paths = '/' . trim( $category_path, '/' );
            $leaf_path      = $this->_sanitize_title( basename( $category_paths ) );
            $category_paths = explode( '/', $category_paths );
            $full_path      = '';
            foreach ($category_paths as $pathdir )
                $full_path .= ( '' !== $pathdir ? '/' : '' ) . $this->_sanitize_title( $pathdir );
            $categories = $this->_get_terms(['taxonomy' => 'category','get' => 'all','slug' => $leaf_path,]);
            if ( empty( $categories ) ) return null;
            foreach ( $categories as $category ) {
                $path        = '/' . $leaf_path;
                $current_category = $category;
                while ( ( 0 !== $current_category->parent ) && ( $current_category->parent !== $current_category->term_id ) ) {
                    $current_category = $this->_get_term( $current_category->parent, 'category' );
                    if ( $this->_init_error( $current_category ) )
                        return $current_category;
                    $path = '/' . $current_category->slug . $path;
                }
                if ( $path === $full_path ) {
                    $category = $this->_get_term( $category->term_id, 'category', $output );
                    $this->_make_cat_compat( $category );
                    return $category;
                }
            }
            if ( ! $full_match ) {
                $category = $this->_get_term( reset( $categories )->term_id, 'category', $output );
                $this->_make_cat_compat( $category );
                return $category;
            }
            return null;
        }//124
        /**
         * @description Retrieves a category object by category slug.
         * @param $slug
         * @return mixed
         */
        protected function _get_category_by_slug( $slug ) {
            $category = $this->_get_term_by( 'slug', $slug, 'category' );
            if ( $category ) $this->_make_cat_compat( $category );
            return $category;
        }//187
        /**
         * @description Retrieves the ID of a category from its name.
         * @param $cat_name
         * @return int
         */
        protected function _get_cat_ID( $cat_name ):int {
            $cat = $this->_get_term_by( 'name', $cat_name, 'category' );
            if ( $cat ) return $cat->term_id;
            return 0;
        }//205
        /**
         * @description Retrieves the name of a category from its ID.
         * @param $cat_id
         * @return mixed
         */
        protected function _get_cat_name( $cat_id ) {
            $cat_id   = (int) $cat_id;
            $category = $this->_get_term( $cat_id, 'category' );
            if ( ! $category || $this->_init_error( $category ) )
                return '';
            return $category->name;
        }//223
        /**
         * @description Checks if a category is an ancestor of another category.
         * @param $cat1
         * @param $cat2
         * @return mixed
         */
        protected function _cat_is_ancestor_of( $cat1, $cat2 ) {
            return $this->_term_is_ancestor_of( $cat1, $cat2, 'category' );
        }//246
        /**
         * @description Sanitizes category data based on context.
         * @param $category
         * @param string $context
         * @return mixed
         */
        protected function _sanitize_category( $category, $context = 'display' ) {
            return $this->_sanitize_term( $category, 'category', $context );
        }//259
        /**
         * @description Sanitizes data in single category key field.
         * @param $field
         * @param $value
         * @param $cat_id
         * @param $context
         * @return mixed
         */
        protected function _sanitize_category_field( $field, $value, $cat_id, $context ) {
            return $this->_sanitize_term_field( $field, $value, $cat_id, 'category', $context );
        }//274
        /**
         * @description Retrieves all post tags.
         * @param array $args
         * @return array
         */
        protected function _get_tags( ...$args):array {
            $defaults = array( 'taxonomy' => 'post_tag' );
            $args     = $this->_tp_parse_args( $args, $defaults );
            $tags = $this->_get_terms( $args );
            if ( empty( $tags ) ) $tags = array();
            else $tags = $this->_apply_filters( 'get_tags', $tags, $args );
            return $tags;
        }//293
    }
}else die;