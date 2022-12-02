<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:23
 */
namespace TP_Core\Traits\Templates;
//use TP_Core\Traits\Inits\_init_pages;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _link_template_07 {
        use _init_queries;//_init_pages, 
        /**
         * @description Displays or retrieves the previous posts page link.
         * @return mixed
         */
        protected function _get_previous_posts(){
            return $this->_esc_url( $this->_get_previous_posts_page_link() );
        }//2550 from link-template
        protected function _previous_posts():void{
            echo $this->_get_previous_posts();
        }
        /**
         * @description Retrieves the previous posts page link.
         * @param null $label
         * @return string
         */
        protected function _get_previous_posts_link( $label = null ):string{
            $paged = $this->tp_paged;
            $link = '';
            if ( null === $label ) $label = $this->__( '&laquo; Previous Page' );
            if ($paged > 1 && ! $this->_is_single()) {
                $attr = $this->_apply_filters( 'previous_posts_link_attributes', '' );
                $link = "<a href='{$this->_get_previous_posts()}\{$attr}'>" . preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) ."</a>";
            }
            return $link;
        }//2570 from link-template
        /**
         * @description Displays the previous posts page link.
         * @param null $label
         */
        protected function _previous_posts_link( $label = null ):void{
            echo $this->_get_previous_posts_link( $label );
        }//2597 from link-template
        /**
         * @description Retrieves the post pages link navigation for previous and next pages.
         * @param \array[] ...$args
         * @return string
         */
        protected function _get_posts_nav_link(array ...$args):string{
            $tp_query = $this->_init_query();
            $return = '';
            if ( ! $this->_is_singular() ) {
                $defaults = ['sep' => ' &#8212; ','prelabel' => $this->__( '&laquo; Previous Page' ),'nxtlabel' => $this->__( 'Next Page &raquo;' ),];
                $args     = $this->_tp_parse_args( $args, $defaults );
                $max_num_pages = $tp_query->max_num_pages;
                $paged         = $this->_get_query_var( 'paged' );
                if ( $paged < 2 || $paged >= $max_num_pages ) $args['sep'] = '';
                if ( $max_num_pages > 1 ) {
                    $return  = $this->_get_previous_posts_link( $args['pre_label'] );
                    $return .= preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $args['sep'] );
                    $return .= $this->_get_next_posts_link( $args['next_label'] );
                }
            }
            return $return;
        }//2619 from link-template
        /**
         * @description Displays the post pages link navigation for previous and next pages.
         * @param string $sep
         * @param string $pre_label
         * @param string $nxt_label
         */
        protected function _posts_nav_link( $sep = '', $pre_label = '', $nxt_label = '' ):void{
            $args = array_filter( compact( 'sep', 'pre_label', 'nxt_label' ) );
            echo $this->_get_posts_nav_link( $args );
        }//2659 from link-template
        /**
         * @description Retrieves the navigation to next/previous post, when applicable.
         * @param \array[] ...$args
         * @return string
         */
        protected function _get_the_post_navigation(array ...$args):string{
            if ( ! empty( $args['screen_reader_text'] ) && empty( $args['aria_label'] ) )
                $args['aria_label'] = $args['screen_reader_text'];
            $args = $this->_tp_parse_args( $args,['prev_text' => '%title','next_text' => '%title','in_same_term' => false,
                    'excluded_terms' => '','taxonomy' => 'category','screen_reader_text' => $this->__( 'Post navigation' ),
                    'aria_label' => $this->__( 'Posts' ),'class' => 'post-navigation',]);
            $navigation = '';
            $previous = $this->_get_previous_post_link(
                "<div class='nav-previous'>%link</div>",$args['prev_text'],
                $args['in_same_term'],$args['excluded_terms'],$args['taxonomy']);
            $next = $this->_get_next_post_link(
                "<div class='nav-next'>%link</div>",
                $args['next_text'],$args['in_same_term'],$args['excluded_terms'],
                $args['taxonomy']);
            if ( $previous || $next )
                $navigation = $this->_navigation_markup( $previous . $next, $args['class'], $args['screen_reader_text'], $args['aria_label'] );
            return $navigation;
        }//2686 from link-template
        /**
         * @description Displays the navigation to next/previous post, when applicable.
         * @param \array[] ...$args
         */
        protected function _the_post_navigation(array ...$args):void{
            echo $this->_get_the_post_navigation( $args );
        }//2740 from link-template
        /**
         * @description Returns the navigation to next/previous set of posts, when applicable.
         * @param \array[] ...$args
         * @return string
         */
        protected function _get_the_posts_navigation(array ...$args):string{
            $navigation = '';
            if ( $this->_tp_query->max_num_pages > 1 ) {
                if ( ! empty( $args['screen_reader_text'] ) && empty( $args['aria_label'] ) )
                    $args['aria_label'] = $args['screen_reader_text'];
                $args = $this->_tp_parse_args($args,['prev_text' => $this->__( 'Older posts' ),'next_text' => $this->__( 'Newer posts' ),
                        'screen_reader_text' => $this->__( 'Posts navigation' ),'aria_label' => $this->__( 'Posts' ),'class' => 'posts-navigation',]);
                $next_link = $this->_get_previous_posts_link( $args['next_text'] );
                $prev_link = $this->_get_next_posts_link( $args['prev_text'] );
                if ( $prev_link ) $navigation .= "<div class='nav-previous'>$prev_link</div>";
                if ( $next_link ) $navigation .= "<div class='nav-next'>$next_link</div>";
                $navigation = $this->_navigation_markup( $navigation, $args['class'], $args['screen_reader_text'], $args['aria_label'] );
            }
            return $navigation;
        }//2767 from link-template
        /**
         * @description Displays the navigation to next/previous set of posts, when applicable.
         * @param \array[] ...$args
         */
        protected function _the_posts_navigation(array ...$args):void{
            echo $this->_get_the_posts_navigation( $args );
        }//2813 from link-template
        /**
         * @description Retrieves a paginated navigation to next/previous set of posts, when applicable.
         * @param \array[] ...$args
         * @return string
         */
        protected function _get_the_posts_pagination(array ...$args):string{
            $navigation = '';
            if ( $this->_tp_query->max_num_pages > 1 ) {
                if ( ! empty( $args['screen_reader_text'] ) && empty( $args['aria_label'] ) )
                    $args['aria_label'] = $args['screen_reader_text'];
                $args = $this->_tp_parse_args($args,['mid_size' => 1,
                        'prev_text' => $this->_x( 'Previous', 'previous set of posts' ),'next_text' => $this->_x( 'Next', 'next set of posts' ),
                        'screen_reader_text' => $this->__( 'Posts navigation' ),'aria_label' => $this->__( 'Posts' ),'class' => 'pagination',]);
                if ( isset( $args['type'] ) && 'array' === $args['type'] ) $args['type'] = 'plain';
                $links = $this->_paginate_links( $args );
                if ( $links ) $navigation = $this->_navigation_markup( $links, $args['class'], $args['screen_reader_text'], $args['aria_label'] );
            }
            return $navigation;
        }//2834 from link-template
    }
}else die;