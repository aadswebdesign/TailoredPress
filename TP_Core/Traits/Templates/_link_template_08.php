<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:23
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_rewrite;
if(ABSPATH){
    trait _link_template_08 {
        use _init_rewrite, _init_queries;
        /**
         * @description Displays a paginated navigation to next/previous set of posts, when applicable.
         * @param \array[] ...$args
         */
        protected function _the_posts_pagination(array ...$args):void{
            echo $this->_get_the_posts_pagination( $args );
        }//2880 from link-template
        /**
         * @description Wraps passed links in navigational markup.
         * @param $links
         * @param string $class
         * @param string $screen_reader_text
         * @param string $aria_label
         * @return string
         */
        protected function _navigation_markup( $links, $class = 'posts-navigation', $screen_reader_text = '', $aria_label = '' ):string{
            if ( empty( $screen_reader_text ) ) $screen_reader_text = $this->__( 'Posts navigation' );
            if ( empty( $aria_label ) ) $aria_label = $screen_reader_text;
            $template = "<nav class='navigation %1\$s'>";
            $template .= "<h2 class='screen-reader-text'>%2\$s</h2>";
            $template .= "<div class='nav-links'>%3\$s</div>";
            $template .= "</nav>";
            $template = $this->_apply_filters( 'navigation_markup_template', $template, $class );
            return sprintf( $template, $this->_sanitize_html_class( $class ), $this->_esc_html( $screen_reader_text ), $links, $this->_esc_html( $aria_label ) );
        }//2900 from link-template
        /**
         * @description Retrieves the comments page number link.
         * @param int $pagenum
         * @param int $max_page
         * @return mixed
         */
        protected function _get_comments_page_num_link( $pagenum = 1, $max_page = 0 ){
            $tp_rewrite = $this->_init_rewrite();
            $pagenum = (int) $pagenum;
            $result = $this->_get_permalink();
            if ( 'newest' === $this->_get_option( 'default_comments_page' ) ) {
                if ( $pagenum !== $max_page ) {
                    if ( $tp_rewrite->using_permalinks() )
                        $result = $this->_user_trailingslashit( $this->_trailingslashit( $result ) . $tp_rewrite->comments_pagination_base . '-' . $pagenum, 'commentpaged' );
                    else $result = $this->_add_query_arg( 'cpage', $pagenum, $result );
                }
            } elseif ( $pagenum > 1 ) {
                if ( $tp_rewrite->using_permalinks() )
                    $result = $this->_user_trailingslashit( $this->_trailingslashit( $result ) . $tp_rewrite->comments_pagination_base . '-' . $pagenum, 'commentpaged' );
                else $result = $this->_add_query_arg( 'cpage', $pagenum, $result );
            }
            $result .= '#comments';
            return $this->_apply_filters( 'get_comments_pagenum_link', $result );

        }//2948 from link-template
        /**
         * @description Retrieves the link to the next comments page.
         * @param string $label
         * @param int|float $max_page
         * @return bool|string
         */
        protected function _get_next_comments_link( $label = '', $max_page = 0 ){
            $tp_query = $this->_init_query();
            if ( ! $this->_is_singular() ) return false;
            $page = $this->_get_query_var( 'cpage' );
            if ( ! $page ) $page = 1;
            $nextpage = $page + 1;
            if ( empty( $max_page ) ) $max_page = $tp_query->max_num_comment_pages;
            if ( empty( $max_page ) ) $max_page = $this->_get_comment_pages_count();
            if ( $nextpage > $max_page )return false;
            if ( empty( $label ) ) $label = $this->__( 'Newer Comments &raquo;' );
            return "<a href='{$this->_esc_url($this->_get_comments_page_num_link( $nextpage, $max_page ))}' {$this->_apply_filters( 'next_comments_link_attributes', '' )}>". preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) ."</a>";
        }//2994 from link-template
        /**
         * @description Displays the link to the next comments page.
         * @param string $label
         * @param int $max_page
         */
        protected function _next_comments_link( $label = '', $max_page = 0 ):void{
            echo $this->_get_next_comments_link( $label, $max_page );
        }// from link-template
        /**
         * @description Retrieves the link to the previous comments page.
         * @param string $label
         * @return bool|string
         */
        protected function _get_previous_comments_link( $label = '' ){
            if ( ! $this->_is_singular() ) return false;
            $page = $this->_get_query_var( 'cpage' );
            if ( (int) $page <= 1 ) return false;
            $prevpage = (int) $page - 1;
            if ( empty( $label ) ) $label = $this->__( '&laquo; Older Comments' );
            return "<a href='{$this->_esc_url($this->_get_comments_page_num_link( $prevpage ))}' {$this->_apply_filters( 'previous_comments_link_attributes', '' )}>" . preg_replace( '/&([^#])(?![a-z]{1,8};)/i', '&#038;$1', $label ) . "</a>";
        }//3055 from link-template
        /**
         * @description Displays the link to the previous comments page.
         * @param string $label
         */
        protected function _previous_comments_link( $label = '' ):void{
            echo $this->_get_previous_comments_link( $label );
        }//3089 from link-template
        /**
         * @description Displays or retrieves pagination links for the comments on the current post.
         * @param \array[] ...$args
         * @return bool
         */
        protected function _get_paginate_comments_links(array ...$args):bool{
            $tp_rewrite = $this->_init_rewrite();
            if ( ! $this->_is_singular() ) return false;
            $page = $this->_get_query_var( 'cpage' );
            if ( ! $page ) $page = 1;
            $max_page = $this->_get_comment_pages_count();
            $defaults = ['base' => $this->_add_query_arg( 'cpage', '%#%' ),'format' => '','total' => $max_page,
                'current' => $page,'echo' => true,'type' => 'plain','add_fragment' => '#comments',];
            if ( $tp_rewrite->using_permalinks() )
                $defaults['base'] = $this->_user_trailingslashit( $this->_trailingslashit( $this->_get_permalink() ) . $tp_rewrite->comments_pagination_base . '-%#%', 'commentpaged' );
            $args       = $this->_tp_parse_args( $args, $defaults );
            return $this->_paginate_links( $args );
        }//3107 from link-template
        protected function _paginate_comments_links(array ...$args):void{
            $link = null;
            if('array' !== $args['type'] ) $link = $this->_get_paginate_comments_links($args);
            echo $link;
        }
        /**
         * @description Retrieves navigation to next/previous set of comments, when applicable.
         * @param \array[] ...$args
         * @return string
         */
        protected function _get_the_comments_navigation(array ...$args):string{
            $navigation = '';
            if ( $this->_get_comment_pages_count() > 1 ) {
                if ( ! empty( $args['screen_reader_text'] ) && empty( $args['aria_label'] ) )
                    $args['aria_label'] = $args['screen_reader_text'];
                $args = $this->_tp_parse_args($args,['prev_text' => $this->__( 'Older comments' ),'next_text' => $this->__( 'Newer comments' ),
                        'screen_reader_text' => $this->__( 'Comments navigation' ),'aria_label' => $this->__( 'Comments' ), 'class' => 'comment-navigation',]);
                $prev_link = $this->_get_previous_comments_link( $args['prev_text'] );
                $next_link = $this->_get_next_comments_link( $args['next_text'] );
                if ( $prev_link ) $navigation .= "<div class='nav-previous'>$prev_link</div>";
                if ( $next_link ) $navigation .= "<div class='nav-next'>$next_link</div>";
                $navigation = $this->_navigation_markup( $navigation, $args['class'], $args['screen_reader_text'], $args['aria_label'] );
            }
            return $navigation;
        }//3162 from link-template
        /**
         * @description Displays navigation to next/previous set of comments, when applicable.
         * @param \array[] ...$args
         */
        protected function _the_comments_navigation(array ...$args):void{
            echo $this->_get_the_comments_navigation( $args );
        }//3207 from link-template
    }
}else die;