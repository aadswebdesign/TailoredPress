<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-3-2022
 * Time: 04:24
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_pages;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    trait _post_template_01 {
        use _init_pages;
        /**
         * @description Display the ID of the current item in the WordPress Loop.
         */
        protected function _the_ID():void{
            echo $this->_get_the_ID();
        }//16 from post-template
        /**
         * @description Retrieve the ID of the current item in the WordPress Loop.
         * @return bool
         */
        protected function _get_the_ID():bool{
            $post = $this->_get_post();
            return $post->ID ?? false;
        }//27 from post-template
        /**
         * @description Display or retrieve the current post title with optional markup.
         * @param string $before
         * @param string $after
         * @return string
         */
        protected function _get_title( $before = '', $after = ''):string{
            $title = $this->_get_the_title();
            if ($title === '') return '';
            return $before . $title . $after;
        }//added
        protected function _the_title( $before = '', $after = ''):void{
            echo $this->_get_title( $before, $after);

        }//42 from post-template
        /**
         * @description Sanitize the current title when retrieving or displaying.
         * @param \array[] ...$args
         * @return bool|string
         */
        protected function _get_the_title_attribute(array ...$args){
            $defaults = ['before' => '','after' => '','echo' => true,'post' => $this->_get_post(),];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $title = $this->_get_the_title( $parsed_args['post'] );
            if ($title === '') return false;
            $title = $parsed_args['before'] . $title . $parsed_args['after'];
            $title = $this->_esc_attr( strip_tags( $title ) );
            return $title;
        }//80 from post-template
        protected function _the_title_attribute( array ...$args):void{
            echo $this->_get_the_title_attribute($args);
        }
        /**
         * @description Retrieve post title.
         * @param string|int $post
         * @return mixed
         */
        protected function _get_the_title( $post = 0 ){
            $post = (string)$this->_get_post( $post );
            $title = $post->post_title ?? '';
            $id    = $post->ID ?? 0;
            if ( ! $this->_is_admin() ) {
                if ( ! empty( $post->post_password ) ) {
                   $prepend = $this->__( 'Protected: %s' );
                   $protected_title_format = $this->_apply_filters( 'protected_title_format', $prepend, $post );
                    $title                  = sprintf( $protected_title_format, $title );
                } elseif ( isset( $post->post_status ) && 'private' === $post->post_status ) {
                    $prepend = $this->__( 'Private: %s' );
                    $private_title_format = $this->_apply_filters( 'private_title_format', $prepend, $post );
                    $title                = sprintf( $private_title_format, $title );
                }
            }
            return $this->_apply_filters( 'the_title', $title, $id );
        }//117 from post-template
        /**
         * @description Display the Post Global Unique Identifier (guid).
         * @param string|int $post
         */
        protected function _the_guid( $post = 0 ):void{
            $post = (string)$this->_get_post( $post );
            $guid = isset( $post->guid ) ? $this->_get_the_guid( $post ) : '';
            $id   = $post->ID ?? 0;
            echo $this->_apply_filters( 'the_guid', $guid, $id );
        }//187 from post-template
        /**
         * @description Retrieve the Post Global Unique Identifier (guid).
         * @param string|int $post
         * @return mixed
         */
        protected function _get_the_guid( $post = 0 ){
            $post = (string)$this->_get_post( $post );
            $guid = $post->guid ?? '';
            $id   = $post->ID ?? 0;
            return $this->_apply_filters( 'get_the_guid', $guid, $id );
        }//218 from post-template
        /**
         * @description Display the post content.
         * @param null $more_link_text
         * @param bool $strip_teaser
         */
        protected function _the_content( $more_link_text = null, $strip_teaser = false ):void{
            $content = $this->_get_the_content( $more_link_text, $strip_teaser );
            $content = $this->_apply_filters( 'the_content', $content );
            $content = str_replace( ']]>', ']]&gt;', $content );
            echo $content;
        }//243 from post-template
        /**
         * @description Retrieve the post content.
         * @param null $more_link_text
         * @param bool $strip_teaser
         * @param null $post
         * @return string
         */
        protected function _get_the_content( $more_link_text = null, $strip_teaser = false, $post = null ):string{
            $page = $this->tp_page;
            $more = $this->tp_more;
            $preview = $this->tp_preview;
            $pages = $this->tp_pages;
            $multipage = $this->tp_multi_page;
            $_post = $this->_get_post( $post );
            if ( ! ( $_post instanceof TP_Post ) ) return '';
            if ( null === $post && $this->_did_action( 'the_post' ) )
                $elements = compact( 'page', 'more', 'preview', 'pages', 'multipage' );
            else $elements = $this->_generate_postdata( $_post );
            if ( null === $more_link_text ) {
                $more_link_text = sprintf("<span aria-label='%1\$s'>%2\$s</span>",sprintf( $this->__( 'Continue reading %s' ),
                    $this->_get_the_title_attribute(['echo' => false,'post' => $_post,]) ), $this->__( '(more&hellip;)' ));
            }
            $output     = '';
            $has_teaser = false;
            if ( $this->_post_password_required( $_post ) ) return $this->_get_the_password_form( $_post );
            if ( $elements['page'] > count( $elements['pages'] ) ) $elements['page'] = count( $elements['pages'] );
            $page_no = $elements['page'];
            $content = $elements['pages'][ $page_no - 1 ];
            if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
                if ( $this->_has_block( 'more', $content ) ) $content = preg_replace( '/<!-- \/?tp:more(.*?) -->/', '', $content );
                $content = explode( $matches[0], $content, 2 );
                if ( ! empty( $matches[1] ) && ! empty( $more_link_text ) ) $more_link_text = strip_tags( $this->_tp_kses_no_null( trim( $matches[1] ) ) );
                $has_teaser = true;
            } else $content = [$content];
            if (( ! $elements['multipage'] || 1 === $elements['page'] ) && false !== strpos( $_post->post_content, '<!--noteaser-->' ))
                $strip_teaser = true;
            $teaser = $content[0];
            if ( $elements['more'] && $strip_teaser && $has_teaser ) $teaser = '';
            $output .= $teaser;
            if ( count( $content ) > 1 ) {
                if ( $elements['more'] )  $output .= "<span id='more_{$_post->ID}'></span>" . $content[1];
                else {
                    if ( ! empty( $more_link_text ) )
                        $output .= $this->_apply_filters( 'the_content_more_link', "<a href='{$this->_get_permalink( $_post )}#more_{$_post->ID}' class='more-link'>$more_link_text</a>", $more_link_text );
                    $output = $this->_force_balance_tags( $output );
                }
            }
            return $output;
        }//276 from post-template
        /**
         * @description Display the post excerpt.
         */
        protected function _the_excerpt():void{
            echo $this->_apply_filters( 'the_excerpt', $this->_get_the_excerpt() );
        }//383 from post-template
    }
}else die;