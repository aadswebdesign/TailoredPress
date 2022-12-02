<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-3-2022
 * Time: 04:24
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_error;
//use TP_Core\Traits\Inits\_init_pages;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_rewrite;
use TP_Core\Libs\TP_PasswordHash;
if(ABSPATH){
    trait _post_template_02 {
        use _init_error, _init_queries,_init_rewrite;//,_init_pages
        /**
         * @description Retrieves the post excerpt.
         * @param null $post
         * @return string
         */
        protected function _get_the_excerpt( $post = null ):string{
            $post = $this->_get_post( $post );
            if ( empty( $post ) ) return '';
            if ( $this->_post_password_required( $post ) )
                return $this->__( 'There is no excerpt because this is a protected post.' );
            return $this->_apply_filters( 'get_the_excerpt', $post->post_excerpt, $post );
        }//406 from post-template
        /**
         * @description Determines whether the post has a custom excerpt.
         * @param string|int $post
         * @return bool
         */
        protected function _has_excerpt( $post = 0 ):bool{
            $post = (string) $this->_get_post( $post );
            return ( ! empty( $post->post_excerpt ) );
        }//444 from post-template
        /**
         * @description Displays the classes for the post container element.
         * @param string $class
         * @param null $post_id
         * @return string
         */
        protected function _get_the_post_class( $class = '', $post_id = null ):string{
            return " class='{$this->_esc_attr( implode( ' ', $this->_get_post_class( $class, $post_id ) ) )}'";
        }//457 from post-template
        protected function _post_class( $class = '', $post_id = null ):void{
            echo $this->_get_the_post_class( $class, $post_id);
        }
        /**
         * @description Retrieves an array of the class names for the post container element.
         * @param array|string $class
         * @param null $post_id
         * @return array
         */
        protected function _get_post_class( $class = '', $post_id = null ):array{
            $post = $this->_get_post( $post_id );
            $classes = [];
            if ( $class ) {
                if ( ! is_array( $class ) ) $class = preg_split( '#\s+#', $class );
                $classes = array_map( 'esc_attr', $class );
            } else $class = [];
            if ( ! $post ) return $classes;
            $classes[] = 'post-' . $post->ID;
            if ( ! $this->_is_admin() ) $classes[] = $post->post_type;
            $classes[] = 'type-' . $post->post_type;
            $classes[] = 'status-' . $post->post_status;
            if ( $this->_post_type_supports( $post->post_type, 'post-formats' ) ) {
                $post_format = $this->_get_post_format( $post->ID );
                if ( $post_format && ! $this->_init_error( $post_format ) )
                    $classes[] = 'format-' . $this->_sanitize_html_class( $post_format );
                else $classes[] = 'format-standard';
            }
            $post_password_required = $this->_post_password_required( $post->ID );
            if ( $post_password_required ) $classes[] = 'post-password-required';
            elseif ( ! empty( $post->post_password ) ) $classes[] = 'post-password-protected';
            if (! $post_password_required && $this->_current_theme_supports( 'post-thumbnails' ) && $this->_has_post_thumbnail( $post->ID ) && ! $this->_is_attachment( $post ))
                $classes[] = 'has-post-thumbnail';
            if ( $this->_is_sticky( $post->ID ) ) {
                if ( $this->_is_home() && ! $this->_is_paged() )  $classes[] = 'sticky';
                elseif ( $this->_is_admin() ) $classes[] = 'status-sticky';
            }
            $classes[] = 'hentry';
            $taxonomies = $this->_get_taxonomies( array( 'public' => true ) );
            foreach ( (array) $taxonomies as $taxonomy ) {
                if ( $this->_is_object_in_taxonomy( $post->post_type, $taxonomy ) ) {
                    foreach ( (array) $this->_get_the_terms( $post->ID, $taxonomy ) as $term ) {
                        if ( empty( $term->slug ) ) continue;
                        $term_class = $this->_sanitize_html_class( $term->slug, $term->term_id );
                        if ( is_numeric( $term_class ) || ! trim( $term_class, '-' ) )
                            $term_class = $term->term_id;
                        if ( 'post_tag' === $taxonomy ) $classes[] = 'tag-' . $term_class;
                        else $classes[] = $this->_sanitize_html_class( $taxonomy . '-' . $term_class, $taxonomy . '-' . $term->term_id );
                    }
                }
            }
            $classes = array_map( 'esc_attr', $classes );
            $classes = $this->_apply_filters( 'post_class', $classes, $class, $post->ID );
            return array_unique( $classes );
        }//483 from post-template
        /**
         * @description Displays the class names for the body element.
         * @param string $class
         * @return string
         */
        protected function _get_the_body_class( $class = '' ):string{
            return " class='{$this->_esc_attr( implode( ' ', $this->_get_body_class( $class ) ) )}'";
        }//593 from post-template
        protected function _body_class( $class = '' ):void{
            echo $this->_get_the_body_class( $class);
        }//593 from post-template
        /**
         * @description Retrieves an array of the class names for the body element.
         * @param array|string $class
         * @return array
         */
        protected function _get_body_class( $class = '' ):array{
            $tp_query = $this->_init_query();
            $classes = [];
            if ( $this->_is_rtl() ) $classes[] = 'rtl';
            if ( $this->_is_front_page() ) $classes[] = 'home';
            if ( $this->_is_home() ) $classes[] = 'blog';
            if ( $this->_is_privacy_policy() ) $classes[] = 'privacy-policy';
            if ( $this->_is_archive() ) $classes[] = 'archive';
            if ( $this->_is_date() ) $classes[] = 'date';
            if ( $this->_is_search() ) {
                $classes[] = 'search';
                $classes[] = $tp_query->posts ? 'search-results' : 'search-no-results';
            }
            if ( $this->_is_paged() ) $classes[] = 'paged';
            if ( $this->_is_attachment() ) $classes[] = 'attachment';
            if ( $this->_is_404() ) $classes[] = 'error404';
            if ( $this->_is_singular() ) {
                $post_id   = $tp_query->get_queried_object_id();
                $post      = $tp_query->get_queried_object();
                $post_type = $post->post_type;
                if ( $this->_is_page_template() ) {
                    $classes[] = "{$post_type}-template";
                    $template_slug  = $this->_get_page_template_slug( $post_id );
                    $template_parts = explode( '/', $template_slug );
                    foreach ( $template_parts as $part )
                        $classes[] = "{$post_type}-template-" . $this->_sanitize_html_class( str_replace( array( '.', '/' ), '-', basename( $part, '.php' ) ) );
                    $classes[] = "{$post_type}-template-" . $this->_sanitize_html_class( str_replace( '.', '-', $template_slug ) );
                } else $classes[] = "{$post_type}-template-default";
                if ( $this->_is_single() ) {
                    $classes[] = 'single';
                    if ( isset( $post->post_type ) ) {
                        $classes[] = 'single-' . $this->_sanitize_html_class( $post->post_type, $post_id );
                        $classes[] = 'postid-' . $post_id;
                        if ( $this->_post_type_supports( $post->post_type, 'post-formats' ) ) {
                            $post_format = $this->_get_post_format( $post->ID );
                            if ( $post_format && ! $this->_init_error( $post_format ) )
                                $classes[] = 'single-format-' . $this->_sanitize_html_class( $post_format );
                            else $classes[] = 'single-format-standard';
                        }
                    }
                }
                if ( $this->_is_attachment() ) {
                    $mime_type   = $this->_get_post_mime_type( $post_id );
                    $mime_prefix = array( 'application/', 'image/', 'text/', 'audio/', 'video/', 'music/' );
                    $classes[]   = 'attachment-id-' . $post_id;
                    $classes[]   = 'attachment-' . str_replace( $mime_prefix, '', $mime_type );
                } elseif ( $this->_is_page() ) {
                    $classes[] = 'page';
                    $page_id = $tp_query->get_queried_object_id();
                    $post = $this->_get_post( $page_id );
                    $classes[] = 'page-id-' . $page_id;
                    if ( $this->_get_pages(['parent' => $page_id, 'number' => 1,]) )
                        $classes[] = 'page-parent';
                    if ( $post->post_parent ) {
                        $classes[] = 'page-child';
                        $classes[] = 'parent-pageid-' . $post->post_parent;
                    }
                }
            } elseif ( $this->_is_archive() ) {
                if ( $this->_is_post_type_archive() ) {
                    $classes[] = 'post-type-archive';
                    $post_type = $this->_get_query_var( 'post_type' );
                    if ( is_array( $post_type ) ) {
                        $post_type = reset( $post_type );
                    }
                    $classes[] = 'post-type-archive-' . $this->_sanitize_html_class( $post_type );
                } elseif ( $this->_is_author() ) {
                    $author    = $tp_query->get_queried_object();
                    $classes[] = 'author';
                    if ( isset( $author->user_nicename ) ) {
                        $classes[] = 'author-' . $this->_sanitize_html_class( $author->user_nicename, $author->ID );
                        $classes[] = 'author-' . $author->ID;
                    }
                } elseif ( $this->_is_category() ) {
                    $cat       = $tp_query->get_queried_object();
                    $classes[] = 'category';
                    if ( isset( $cat->term_id ) ) {
                        $cat_class = $this->_sanitize_html_class( $cat->slug, $cat->term_id );
                        if ( is_numeric( $cat_class ) || ! trim( $cat_class, '-' ) )
                            $cat_class = $cat->term_id;
                        $classes[] = 'category-' . $cat_class;
                        $classes[] = 'category-' . $cat->term_id;
                    }
                } elseif ( $this->_is_tag() ) {
                    $tag       = $tp_query->get_queried_object();
                    $classes[] = 'tag';
                    if ( isset( $tag->term_id ) ) {
                        $tag_class = $this->_sanitize_html_class( $tag->slug, $tag->term_id );
                        if ( is_numeric( $tag_class ) || ! trim( $tag_class, '-' ) ) $tag_class = $tag->term_id;
                        $classes[] = 'tag-' . $tag_class;
                        $classes[] = 'tag-' . $tag->term_id;
                    }
                } elseif ( $this->_is_tax() ) {
                    $term = $tp_query->get_queried_object();
                    if ( isset( $term->term_id ) ) {
                        $term_class = $this->_sanitize_html_class( $term->slug, $term->term_id );
                        if ( is_numeric( $term_class ) || ! trim( $term_class, '-' ) )  $term_class = $term->term_id;
                        $classes[] = 'tax-' . $this->_sanitize_html_class( $term->taxonomy );
                        $classes[] = 'term-' . $term_class;
                        $classes[] = 'term-' . $term->term_id;
                    }
                }
            }
            if ( $this->_is_user_logged_in() ) $classes[] = 'logged-in';
            if ( $this->_is_admin_bar_showing() ) {
                $classes[] = 'admin-bar';
                $classes[] = 'no-customize-support';
            }
            if ( $this->_current_theme_supports( 'custom-background' ) && ( $this->_get_background_color() !== $this->_get_theme_support( 'custom-background', 'default-color' ) || $this->_get_background_image() ) )
                $classes[] = 'custom-background';
            if ( $this->_has_custom_logo() ) $classes[] = 'tp-custom-logo';
            if ( $this->_current_theme_supports( 'responsive-embeds' ) ) $classes[] = 'tp-embed-responsive';
            $page = $tp_query->get( 'page' );
            if ( ! $page || $page < 2 ) $page = $tp_query->get( 'paged' );
            if ( $page && $page > 1 && ! $this->_is_404() ) {
                $classes[] = 'paged-' . $page;
                if ( $this->_is_single() ) $classes[] = 'single-paged-' . $page;
                elseif ( $this->_is_page() ) $classes[] = 'page-paged-' . $page;
                elseif ( $this->_is_category() ) $classes[] = 'category-paged-' . $page;
                elseif ( $this->_is_tag() ) $classes[] = 'tag-paged-' . $page;
                elseif ( $this->_is_date() ) $classes[] = 'date-paged-' . $page;
                elseif ( $this->_is_author() ) $classes[] = 'author-paged-' . $page;
                elseif ( $this->_is_search() ) $classes[] = 'search-paged-' . $page;
                elseif ( $this->_is_post_type_archive() ) $classes[] = 'post-type-paged-' . $page;
            }
            if ( ! empty( $class ) ) {
                if ( ! is_array( $class ) ) $class = preg_split( '#\s+#', $class );
                $classes = array_merge( $classes, $class );
            } else $class = [];
            $classes = array_map( 'esc_attr', $classes );
            $classes = $this->_apply_filters( 'body_class', $classes, $class );
            return array_unique( $classes );
        }//608 from post-template
        /**
         * @description Whether post requires password and correct password has been provided.
         * @param null $post
         * @return mixed
         */
        protected function _post_password_required( $post = null ){
            $post = $this->_get_post( $post );
            if ( empty( $post->post_password ) )
                return $this->_apply_filters( 'post_password_required', false, $post );
            $cookie_hash = $_COOKIE[ 'tp-postpass_' . COOKIE_HASH ];
            if ( ! isset( $cookie_hash) )
                return $this->_apply_filters( 'post_password_required', true, $post );
            $hasher = new TP_PasswordHash( 8, true );
            $hash = $this->_tp_unslash( $_COOKIE[ 'tp-postpass_' . COOKIE_HASH ] );
            if ( 0 !== strpos( $hash, '$P$B' ) ) $required = true;
            else $required = ! $hasher->CheckPassword( $post->post_password, $hash );
            return $this->_apply_filters( 'post_password_required', $required, $post );
        }//849 from post-template
        /**
         * @description The formatted output of a list of pages.
         * @param string $args
         * @return mixed
         */
        protected function _tp_get_link_pages( $args = '' ){
            $page = $this->tp_page;
            $numpages = $this->tp_num_pages;
            $multipage = $this->tp_multi_page;
            $more = $this->tp_more;
            $defaults = ['before' => "<p class='post-nav-links'>" . $this->__( 'Pages:' ),'after' => '</p>',
                'link_before' => '','link_after' => '','aria_current' => 'page','next_or_number' => 'number',
                'separator' => ' ','nextpage_link' => $this->__( 'Next page' ),'previouspage_link' => $this->__( 'Previous page' ),
                'pagelink' => '%','echo' => 1,];
            $parsed_args = $this->_tp_parse_args( $args, $defaults );
            $parsed_args = $this->_apply_filters( 'tp_link_pages_args', $parsed_args );
            $output = '';
            if ( $multipage ) {
                if ( 'number' === $parsed_args['next_or_number'] ) {
                    $output .= $parsed_args['before'];
                    for ( $i = 1; $i <= $numpages; $i++ ) {
                        $link = $parsed_args['link_before'] . str_replace( '%', $i, $parsed_args['pagelink'] ) . $parsed_args['link_after'];
                        if ( $i !== $page || (!$more && 1 === $page)) $link = $this->_tp_link_page( $i ) . $link . '</a>';
                        elseif ( $i === $page )
                            $link = "<span class='post-page-numbers current' aria-current='{$this->_esc_attr( $parsed_args['aria_current'] )}'>$link</span>";
                        $link = $this->_apply_filters( 'tp_link_pages_link', $link, $i );
                        $output .= ( 1 === $i ) ? ' ' : $parsed_args['separator'];
                        $output .= $link;
                    }
                    $output .= $parsed_args['after'];
                } elseif ( $more ) {
                    $output .= $parsed_args['before'];
                    $prev    = $page - 1;
                    if ( $prev > 0 ) {
                        $link = $this->_tp_link_page( $prev ) . $parsed_args['link_before'] . $parsed_args['previouspagelink'] . $parsed_args['link_after'] . '</a>';
                        $output .= $this->_apply_filters( 'tp_link_pages_link', $link, $prev );
                    }
                    $next = $page + 1;
                    if ( $next <= $numpages ) {
                        if ( $prev ) $output .= $parsed_args['separator'];
                        $link = $this->_tp_link_page( $next ) . $parsed_args['link_before'] . $parsed_args['nextpagelink'] . $parsed_args['link_after'] . '</a>';
                        $output .= $this->_apply_filters( 'tp_link_pages_link', $link, $next );
                    }
                    $output .= $parsed_args['after'];
                }
            }
            return $this->_apply_filters( 'tp_link_pages', $output, $args );
        }//added
        protected function _tp_link_pages( $args = '' ):void{
            echo $this->_tp_get_link_pages( $args);
        }//925 from post-template
        /**
         * @description Helper function for tp_link_pages().
         * @param $i
         * @return string
         */
        protected function _tp_link_page( $i ):string{
            $tp_rewrite = $this->_init_rewrite();
            $post       = $this->_get_post();
            $query_args = [];
            if ( 1 === $i ) $url = $this->_get_permalink();
            else if ( ! $this->_get_option( 'permalink_structure' ) || in_array( $post->post_status, array( 'draft', 'pending' ), true ) )
                $url = $this->_add_query_arg( 'page', $i, $this->_get_permalink() );
            elseif ( 'page' === $this->_get_option( 'show_on_front' ) && $this->_get_option( 'page_on_front' ) === $post->ID )
                $url = $this->_trailingslashit( $this->_get_permalink() ) . $this->_user_trailingslashit( "$tp_rewrite->pagination_base/" . $i, 'single_paged' );
            else $url = $this->_trailingslashit( $this->_get_permalink() ) . $this->_user_trailingslashit( $i, 'single_paged' );
            if ( $this->_is_preview() ) {
                if ( ( 'draft' !== $post->post_status ) && isset( $_GET['preview_id'], $_GET['preview_nonce'] ) ) {
                    $query_args['preview_id']    = $this->_tp_unslash( $_GET['preview_id'] );
                    $query_args['preview_nonce'] = $this->_tp_unslash( $_GET['preview_nonce'] );
                }
                $url = $this->_get_preview_post_link( $post, $query_args, $url );
            }
            return "<a href='{$this->_esc_url($url)}' class='post-page-numbers'>";
        }//1031 from post-template
        /**
         * @description Retrieve post custom meta data field.
         * @param string $key
         * @return bool
         */
        protected function _post_custom( $key = '' ):bool{
            $custom = $this->_get_post_custom();
            if ( ! isset( $custom[ $key ] ) )  return false;
            elseif ( 1 === count( $custom[ $key ] ) )  return $custom[ $key ][0];
            else return $custom[ $key ];
        }//1074 from post-template
    }
}else die;