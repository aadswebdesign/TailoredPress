<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-3-2022
 * Time: 21:51
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_rewrite;
if(ABSPATH){
    trait _category_template_01 {
        use _init_error;
        use _init_rewrite;
        /**
         * @description Retrieves category link URL.
         * @param $category
         * @return int|string
         */
        protected function _get_category_link( $category ){
            if ( ! is_object( $category ) ) $category = (int) $category;
            $category = $this->_get_term_link( $category );
            if ( $this->_init_error( $category ) ) return '';
            return $category;
        }//20 from category-template
        /**
         * @description Retrieves category parents with separator.
         * @param $category_id
         * @param bool $link
         * @param string $separator
         * @param bool $nice_name
         * @return mixed
         */
        protected function _get_category_parents( $category_id, $link = false, $separator = '/', $nice_name = false){
            $format = $nice_name ? 'slug' : 'name';
            $args = ['separator' => $separator,'link' => $link,'format' => $format,];
            return $this->_get_term_parents_list( $category_id, 'category', $args );
        }//47 from category-template
        /**
         * @description Retrieves post categories.
         * @param bool $post_id
         * @return mixed
         */
        protected function _get_the_category( $post_id = false ){
            $categories = $this->_get_the_terms( $post_id, 'category' );
            if ( ! $categories || $this->_init_error( $categories ) )
                $categories = [];
            $categories = array_values( $categories );
            foreach ( array_keys( $categories ) as $key )
                $this->_make_cat_compat( $categories[ $key ] );
            return $this->_apply_filters( 'get_the_categories', $categories, $post_id );
        }//77 from category-template
        /**
         * @description Retrieves category name based on category ID.
         * @param $cat_ID
         * @return string
         */
        protected function _get_the_category_by_ID( $cat_ID ):string{
            $cat_id   = (int) $cat_ID;
            $category = $this->_get_term( $cat_id );
            if ( $this->_init_error( $category ) )
                return $category;
            return ( $category ) ? $category->name : '';
        }//109 from category-template
        /**
         * @description Retrieves category list for a post in either HTML list or custom format.
         * @param string $separator
         * @param string $parents
         * @param bool $post_id
         * @return mixed
         */
        protected function _get_the_category_list( $separator = '', $parents = '', $post_id = false ){
            $rewrite = $this->_init_rewrite();
            if ( ! $this->_is_object_in_taxonomy( $this->_get_post_type( $post_id ), 'category' ) )
                return $this->_apply_filters( 'the_category', '', $separator, $parents );
            $categories = $this->_apply_filters( 'the_category_list', $this->_get_the_category( $post_id ), $post_id );
            if ( empty( $categories ) )
                return $this->_apply_filters( 'the_category', $this->__( 'Uncategorized' ), $separator, $parents );
            $rel = ( is_object( $rewrite ) && $rewrite->using_permalinks() ) ? 'rel="category tag"' : 'rel="category"';
            $the_list = "";
            if ( '' === $separator ) {
                $the_list .= "<ul class='post-categories'>";
                foreach ( $categories as $category ){
                    $the_list .= "\n\t<li>";
                    switch ( strtolower( $parents ) ){
                        case 'multiple':
                            if ( $category->parent )$the_list .= $this->_get_category_parents( $category->parent, true, $separator );
                            $the_list .= "<a href='{$this->_esc_url( $this->_get_category_link( $category->term_id ) )}' $rel>$category->name</a>";
                            break;
                        case 'single':
                            $the_list .= "<a href='{$this->_esc_url( $this->_get_category_link( $category->term_id ) )}' $rel>";
                            if ( $category->parent ) $the_list .= $this->_get_category_parents( $category->parent, false, $separator );
                            $the_list .= "$category->name</a>";
                            break;
                        case '':
                        default:
                        $the_list .= "<a href='{$this->_esc_url( $this->_get_category_link( $category->term_id ) )}' $rel>$category->name</a></li>";
                    }
                }
                $the_list .= "</ul>";
            }else{
                $i = 0;
                foreach ( $categories as $category ){
                    if ( 0 < $i ) $the_list .= $separator;
                    switch ( strtolower( $parents ) ) {
                        case 'multiple':
                            if ( $category->parent ) $the_list .= $this->_get_category_parents( $category->parent, true, $separator );
                            $the_list .= "<a href='{$this->_esc_url( $this->_get_category_link( $category->term_id ) )}' $rel>$category->name</a>";
                            break;
                        case 'single':
                            $the_list .= "<a href='{$this->_esc_url( $this->_get_category_link( $category->term_id ) )}' $rel>";
                            if ( $category->parent ) $the_list .= $this->_get_category_parents( $category->parent, false, $separator );
                            $the_list .= "$category->name</a>";
                            break;
                        case '':
                        default:
                        $the_list .= "<a href='{$this->_esc_url( $this->_get_category_link( $category->term_id ) )}' $rel>$category->name</a>";
                    }
                    ++$i;
                }
            }
            return $this->_apply_filters( 'the_category', $the_list, $separator, $parents );
        }//140 from category-template
        /**
         * @description Checks if the current post is within any of the given categories.
         * @param $category
         * @param null $post
         * @return bool
         */
        protected function _in_category( $category, $post = null ):bool{
            if ( empty( $category ) ) return false;
            return $this->_has_category( $category, $post );
        }//256 from category-template
        /**
         * @description Displays category list for a post in either HTML list or custom format.
         * @param string $separator
         * @param string $parents
         * @param bool $post_id
         */
        public function the_category( $separator = '', $parents = '', $post_id = false ):void{
            echo $this->_get_the_category_list( $separator, $parents, $post_id );
        }//274 from category-template
        /**
         * @description Retrieves category description.
         * @param int $category
         * @return mixed
         */
        protected function _category_description( $category = 0 ){
            return $this->_term_description( $category );
        }//286 from category-template
        /**
         * @description Displays or retrieves the HTML dropdown list of categories.
         * @param array ...$args
         * @return string
         */
        protected function _tp_get_dropdown_categories(...$args):string{
            $defaults = [
                'show_option_all' => '','show_option_none' => '','orderby' => 'id','order' => 'ASC','show_count' => 0,
                'hide_empty' => 1,'child_of' => 0,'exclude' => '','echo' => 1,'selected' => 0,'hierarchical' => 0,
                'name' => 'cat','id' => '','class' => 'postform','depth' => 0,'tab_index' => 0,'taxonomy' => 'category',
                'hide_if_empty' => false, 'option_none_value' => -1,'value_field' => 'term_id','required' => false,
            ];
            $defaults['selected'] = ( $this->_is_category() ) ? $this->_get_query_var( 'cat' ) : 0;
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $option_none_value = $parsed_args['option_none_value'];
            if ( ! isset( $parsed_args['pad_counts'] ) && $parsed_args['show_count'] && $parsed_args['hierarchical'] )
                $parsed_args['pad_counts'] = true;
            $tab_index = $parsed_args['tab_index'];
            $tab_index_attribute = '';
            if ( (int) $tab_index > 0 )
                $tab_index_attribute = " tabindex=\"$tab_index\"";
            $get_terms_args = $parsed_args;
            unset( $get_terms_args['name'] );
            $categories = $this->_get_terms( $get_terms_args );
            $name     = $this->_esc_attr( $parsed_args['name'] );
            $class    = $this->_esc_attr( $parsed_args['class'] );
            $id       = $parsed_args['id'] ? $this->_esc_attr( $parsed_args['id'] ) : $name;
            $required = $parsed_args['required'] ? 'required' : '';
            if ( ! $parsed_args['hide_if_empty'] || ! empty( $categories ) )
                $output = "<select $required name='$name' id='$id' class='$class' $tab_index_attribute>\n";
            else
                $output = '';
            if ( empty( $categories ) && ! $parsed_args['hide_if_empty'] && ! empty( $parsed_args['show_option_none'] ) ) {
                $show_option_none = $this->_apply_filters( 'list_cats', $parsed_args['show_option_none'], null );
                $output          .= "\t<option value='{$this->_esc_attr( $option_none_value )}' selected='selected'>$show_option_none</option>\n";
            }
            if ( ! empty( $categories ) ) {
                if ( $parsed_args['show_option_all'] ) {
                    $show_option_all = $this->_apply_filters( 'list_cats', $parsed_args['show_option_all'], null );
                    $selected        = ( '0' === (string) $parsed_args['selected'] ) ? " selected='selected'" : '';
                    $output         .= "\t<option value='0' $selected>$show_option_all</option>\n";
                }
                if ( $parsed_args['show_option_none'] ) {
                    $show_option_none = $this->_apply_filters( 'list_cats', $parsed_args['show_option_none'], null );
                    $selected         = $this->_get_selected( $option_none_value, $parsed_args['selected']);
                    $output          .= "\t<option value='{$this->_esc_attr( $option_none_value )}' $selected>$show_option_none</option>\n";
                }
                if ( $parsed_args['hierarchical'] ) $depth = $parsed_args['depth'];  // Walk the full depth.
                else  $depth = -1; // Flat.
                $output .= $this->_walk_category_dropdown_tree( $categories, $depth, $parsed_args );
            }
            if ( ! $parsed_args['hide_if_empty'] || ! empty( $categories ) )
                $output .= "</select>\n";
            $output = $this->_apply_filters( 'tp_dropdown_cats', $output, $parsed_args );

            return $output;
        }//337 from category-template
        protected function _tp_dropdown_categories(...$args):void{
            echo $this->_tp_get_dropdown_categories($args);
        }
        /**
         * @description Displays or retrieves the HTML list of categories.
         * @param array ...$args
         * @return string
         */
        protected function _tp_get_list_categories( ...$args):string{
            $defaults = [
                'child_of' => 0,'current_category' => 0,'depth' => 0,'exclude' => '','exclude_tree' => '',
                'feed' => '','feed_image' => '','feed_type' => '','hide_empty' => 1,'hide_title_if_empty' => false,
                'hierarchical' => true,'order' => 'ASC','orderby' => 'name','separator' => '<br />','show_count' => 0,'show_option_all' => '',
                'show_option_none' => $this->__( 'No categories' ),'style' => 'list','taxonomy' => 'category','title_li' => $this->__( 'Categories' ),'use_desc_for_title' => 1,
            ];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            if ( ! isset( $parsed_args['pad_counts'] ) && $parsed_args['show_count'] && $parsed_args['hierarchical'] )
                $parsed_args['pad_counts'] = true;
            if ( true === $parsed_args['hierarchical'] ) {
                $exclude_tree = array();
                if ( $parsed_args['exclude_tree'] )
                    $exclude_tree = array_merge( $exclude_tree, $this->_tp_parse_id_list( $parsed_args['exclude_tree'] ) );
                if ( $parsed_args['exclude'] )
                    $exclude_tree = array_merge( $exclude_tree, $this->_tp_parse_id_list( $parsed_args['exclude'] ) );
                $parsed_args['exclude_tree'] = $exclude_tree;
                $parsed_args['exclude']      = '';
            }
            if ( ! isset( $parsed_args['class'] ) )
                $parsed_args['class'] = ( 'category' === $parsed_args['taxonomy'] ) ? 'categories' : $parsed_args['taxonomy'];
            if ( ! $this->_taxonomy_exists( $parsed_args['taxonomy'] ) )
                return false;
            $show_option_all  = $parsed_args['show_option_all'];
            $show_option_none = $parsed_args['show_option_none'];
            $categories = $this->_get_categories( $parsed_args );
            $output = '';
            if ( $parsed_args['title_li'] && 'list' === $parsed_args['style']
                && ( ! empty( $categories ) || ! $parsed_args['hide_title_if_empty'] )
            )   $output = "<li class='{$this->_esc_attr( $parsed_args['class'] )}'>{$parsed_args['title_li']}</li><ul>";
            if ( empty( $categories ) ) {
                if ( ! empty( $show_option_none ) ) {
                    if ( 'list' === $parsed_args['style'] )
                        $output .= "<li class='cat-item-none'>$show_option_none</li>";
                    else $output .= $show_option_none;
                }
            }else{
                if ( ! empty( $show_option_all ) ) {
                    $posts_page = '';
                    $taxonomy_object = $this->_get_taxonomy( $parsed_args['taxonomy'] );
                    if ( ! in_array( 'post', $taxonomy_object->object_type, true ) && ! in_array( 'page', $taxonomy_object->object_type, true ) ) {
                        foreach ( $taxonomy_object->object_type as $object_type ) {
                            $_object_type = $this->_get_post_type_object( $object_type );
                            if ( ! empty( $_object_type->has_archive ) ) {
                                $posts_page = $this->_get_post_type_archive_link( $object_type );
                                break;
                            }
                        }
                    }
                    // Fallback for the 'All' link is the posts page.
                    if ( ! $posts_page ) {
                        if ( 'page' === $this->_get_option( 'show_on_front' ) && $this->_get_option( 'page_for_posts' ) )
                            $posts_page = $this->_get_permalink( $this->_get_option( 'page_for_posts' ) );
                        else $posts_page = $this->_home_url( '/' );
                    }
                    $posts_page = $this->_esc_url( $posts_page );
                    if ( 'list' === $parsed_args['style'] )
                        $output .= "<li class='cat-item-all'><a href='$posts_page'>$show_option_all</a></li>";
                    else $output .= "<a href='$posts_page'>$show_option_all</a>";
                }
                if ( empty( $parsed_args['current_category'] ) && ( $this->_is_category() || $this->_is_tax() || $this->_is_tag() ) ) {
                    $current_term_object = $this->_get_queried_object();
                    if ( $current_term_object && $parsed_args['taxonomy'] === $current_term_object->taxonomy )
                        $parsed_args['current_category'] = $this->_get_queried_object_id();
                }
                if ( $parsed_args['hierarchical'] ) $depth = $parsed_args['depth'];
                else $depth = -1; // Flat.
                $output .= $this->_walk_category_tree( $categories, $depth, $parsed_args );
            }
            if ( $parsed_args['title_li'] && 'list' === $parsed_args['style']
                && ( ! empty( $categories ) || ! $parsed_args['hide_title_if_empty'] )
            ) $output .= '</ul></li>';
            return $this->_apply_filters( 'tp_list_categories', $output, $args );
        }//525 from category-template
        protected function _tp_list_categories( ...$args):void{
			echo $this->_tp_get_list_categories($args);
		}
    }
}else die;