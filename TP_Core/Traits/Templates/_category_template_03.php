<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-3-2022
 * Time: 21:51
 */
namespace TP_Core\Traits\Templates;
//use TP_Core\Libs\TP_Term;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _category_template_03 {
        use _init_error;
        /**
         * @description Displays the tags for a post.
         * @param null $before
         * @param string $sep
         * @param string $after
         */
        protected function _the_tags( $before = null, $sep = ', ', $after = '' ):void{
            if ( null === $before ) $before = $this->__( 'Tags: ' );
            $the_tags = $this->_get_the_tag_list( $before, $sep, $after );
            if ( ! $this->_init_error( $the_tags ) ) echo $the_tags;
        }//1221 from category-template
        /**
         * @description Retrieves tag description.
         * @param int $tag
         * @return string
         */
        protected function _tag_description( $tag = 0 ):string{
            return $this->_term_description( $tag );
        }//1241 from category-template
        /**
         * @description Retrieves term description.
         * @param mixed $term
         * @return string
         */
        protected function _term_description($term = 0):string{
            if ( ! $term && ( $this->_is_tax() || $this->_is_tag() || $this->_is_category() ) ) {
                $term = $this->_get_queried_object();
                if ( $term ) $term = $term->term_id;
            }
            $description = $this->_get_term_field( 'description', $term );
            return $this->_init_error( $description ) ? '' : $description;
        }//1255 from category-template
        /**
         * @description Retrieves the terms of the taxonomy that are attached to the post.
         * @param $post
         * @param $taxonomy
         * @return bool
         */
        protected function _get_the_terms( $post, $taxonomy ):bool{
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $terms = $this->_get_object_term_cache( $post->ID, $taxonomy );
            if ( false === $terms ) {
                $terms = $this->_tp_get_object_terms( $post->ID, $taxonomy );
                if ( ! $this->_init_error( $terms ) ) {
                    $term_ids = $this->_tp_list_pluck( $terms, 'term_id' );
                    $this->_tp_cache_add( $post->ID, $term_ids, $taxonomy . '_relationships' );
                }
            }
            $terms = $this->_apply_filters( 'get_the_terms', $terms, $post->ID, $taxonomy );
            if ( empty( $terms ) )return false;
            return $terms;
        }//1278 from category-template
        /**
         * @description Retrieves a post's terms as a list with specified format.
         * @param $post_id
         * @param $taxonomy
         * @param string $before
         * @param string $sep
         * @param string $after
         * @return bool|string
         */
        protected function _get_the_term_list( $post_id, $taxonomy, $before = '', $sep = '', $after = '' ):bool{
            $terms = $this->_get_the_terms( $post_id, $taxonomy );
            if ( $this->_init_error( $terms ) ) return $terms;
            if ( empty( $terms ) ) return false;
            $links = [];
            foreach ((array) $terms as $term ) {
                $link = $this->_get_term_link( $term, $taxonomy );
                if ( $this->_init_error( $link ) ) return $link;
                $links[] = "<a href='{$this->_esc_url( $link )}' rel='tag'>$term->name</a>";
            }
            $term_links = $this->_apply_filters( "term_links-{$taxonomy}", $links );
            return $before . implode( $sep, $term_links ) . $after;
        }//1326 from category-template
        /**
         * @description Retrieves term parents with separator.
         * @param $term_id
         * @param $taxonomy
         * @param array $args
         * @return string
         */
        protected function _get_term_parents_list( $term_id, $taxonomy, $args = [] ):string{
            $list = '';
            $term = $this->_get_term( $term_id, $taxonomy );
            if ( $this->_init_error( $term ) ) return $term;
            if ( ! $term ) return $list;
            $term_id = $term->term_id;
            $defaults = ['format' => 'name','separator' => '/','link' => true,'inclusive' => true,];
            $args = $this->_tp_parse_args( $args, $defaults );
            foreach ( array( 'link', 'inclusive' ) as $bool )
                $args[ $bool ] = $this->_tp_validate_boolean( $args[ $bool ] );
            $parents = $this->_get_ancestors( $term_id, $taxonomy, 'taxonomy' );
            if ( $args['inclusive'] ) array_unshift( $parents, $term_id );
            foreach ( array_reverse( $parents ) as $sub_term_id ) {
                $parent = $this->_get_term( $sub_term_id, $taxonomy );
                $name   = ( 'slug' === $args['format'] ) ? $parent->slug : $parent->name;
                if ( $args['link'] )
                    $list .= "<a href='{$this->_esc_url( $this->_get_term_link( $parent->term_id, $taxonomy ) )}'>$name</a>" . $args['separator'];
                else $list .= $name . $args['separator'];
            }
            return $list;
        }//1386 from category-template
        /**
         * @description Displays the terms for a post in a list.
         * @param $post_id
         * @param $taxonomy
         * @param string $before
         * @param string $sep
         * @param string $after
         * @return bool
         */
        protected function _get_cat_terms( $post_id, $taxonomy, $before = '', $sep = ', ', $after = '' ):bool{
            $term_list = $this->_get_the_term_list( $post_id, $taxonomy, $before, $sep, $after );
            if ($this->_init_error( $term_list )) return false;
            return $this->_apply_filters( 'the_terms', $term_list, $taxonomy, $before, $sep, $after );
        }//1445 from category-template
        public function the_cat_terms( $post_id, $taxonomy, $before = '', $sep = ', ', $after = '' ):void{
            echo $this->_get_cat_terms( $post_id, $taxonomy, $before, $sep, $after);
        }
        /**
         * @description Checks if the current post has any of given category.
         * @param string $category
         * @param null $post
         * @return mixed
         */
        protected function _has_category( $category = '', $post = null ){
            return $this->_has_term( $category, 'category', $post );
        }//1482 from category-template
        /**
         * @description Checks if the current post has any of given tags.
         * @param string $tag
         * @param null $post
         * @return mixed
         */
        protected function _has_tag( $tag = '', $post = null ){
            return $this->_has_term( $tag, 'category', $post );
        }//1509 from category-template
        /**
         * @description Checks if the current post has any of given terms.
         * @param string $term
         * @param string $taxonomy
         * @param null $post
         * @return bool
         */
        protected function _has_term( $term = '', $taxonomy = '', $post = null ):bool{
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $r = $this->_is_object_in_term( $post->ID, $taxonomy, $term );
            if ( $this->_init_error( $r ) ) return false;
            return $r;
        }//1531 from category-template
    }
}else die;