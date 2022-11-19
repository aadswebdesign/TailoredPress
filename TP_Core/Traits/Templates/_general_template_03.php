<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 17:17
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_locale;
if(ABSPATH){
    trait _general_template_03 {
        use _init_locale;
        /**
         * @description Whether the site has a Site Icon.
         * @param int $blog_id
         * @return bool
         */
        protected function _has_site_icon( $blog_id = 0 ):bool{
            return (bool) $this->_get_site_icon_url( 512, '', $blog_id );
        }//1001 from general-template
        /**
         * @description Determines whether the site has a custom logo.
         * @param int $blog_id
         * @return bool
         */
        protected function _has_custom_logo( $blog_id = 0 ):bool{
            $switched_blog = false;
            if (! empty( $blog_id ) &&  $this->_is_multisite() && $this->_get_current_blog_id() !== (int) $blog_id ) {
                $this->_switch_to_blog( $blog_id );
                $switched_blog = true;
            }
            $custom_logo_id = $this->_get_theme_mod( 'custom_logo' );
            if ( $switched_blog ) $this->_restore_current_blog();
            return (bool) $custom_logo_id;
        }//1013 from general-template
        /**
         * @description Returns a custom logo, linked to home unless the theme supports removing the link on the home page.
         * @param int $blog_id
         * @return mixed
         */
        protected function _get_custom_logo( $blog_id = 0 ){
            $html          = '';
            $switched_blog = false;
            if (! empty( $blog_id ) &&  $this->_is_multisite() && $this->_get_current_blog_id() !== (int) $blog_id ) {
                $this->_switch_to_blog( $blog_id );
                $switched_blog = true;
            }
            $custom_logo_id = $this->_get_theme_mod( 'custom_logo' );
            if ( $custom_logo_id ) {
                $custom_logo_attr = ['class' => 'custom-logo','loading' => false,];
                $unlink_homepage_logo = (bool) $this->_get_theme_support( 'custom-logo', 'unlink-homepage-logo' );
                if ( $unlink_homepage_logo && $this->_is_front_page() && ! $this->_is_paged() ) {
                    $custom_logo_attr['alt'] = '';
                } else {
                    $image_alt = $this->_get_post_meta( $custom_logo_id, '_tp_attachment_image_alt', true );
                    if ( empty( $image_alt ) ) { $custom_logo_attr['alt'] = $this->_get_bloginfo( 'name', 'display' );}
                }
                $custom_logo_attr = $this->_apply_filters( 'get_custom_logo_image_attributes', $custom_logo_attr, $custom_logo_id, $blog_id );
                $image = $this->_tp_get_attachment_image( $custom_logo_id, 'full', false, $custom_logo_attr );
                if ( $unlink_homepage_logo && $this->_is_front_page() && ! $this->_is_paged() ) {
                    $html = sprintf( "<span class='custom-logo-link'>%1\$s</span>", $image);
                } else {
                    $aria_current = $this->_is_front_page() && ! $this->_is_paged() ? ' aria-current="page"' : '';
                    $html = sprintf("<a href='%1\$s' class='custom-logo-link' rel='home' %2\$s>%3\$s</a>",
                        $this->_esc_url( $this->_home_url('/')), $aria_current,$image);
                }
            }
            if ( $switched_blog ) { $this->_restore_current_blog();}
            return $this->_apply_filters( 'get_custom_logo', $html, $blog_id );
        }//1041 from general-template
        /**
         * @description Displays a custom logo, linked to home unless the theme supports removing the link on the home page.
         * @param int $blog_id
         */
        protected function _the_custom_logo( $blog_id = 0 ):void{
            echo $this->_get_custom_logo( $blog_id );
        }//1142 from general-template
        /**
         * @description Returns document title for the current page.
         * @return array
         */
        protected function _tp_get_document_title():array{
            $title = $this->_apply_filters( 'pre_get_document_title', '' );
            if ( ! empty( $title ) ) return $title;
            $title = ['title' => '',];
            //todo from here
            if ( $this->_is_404() ) $title['title'] = $this->__( 'Page not found' );
            elseif ( $this->_is_front_page() )
                $title['title'] = $this->_get_bloginfo( 'name', 'display' );
            elseif ( $this->_is_post_type_archive() )
                $title['title'] = $this->_get_post_type_archive_title( '');
            elseif ( $this->_is_tax() )
                $title['title'] = $this->_get_single_term_title( '');
            elseif ( $this->_is_home() || $this->_is_singular() )
                $title['title'] = $this->_get_single_post_title( '');
            elseif ( $this->_is_category() || $this->_is_tag() )
                $title['title'] = $this->_get_single_term_title( '');
            elseif ( $this->_is_author() && $this->_get_queried_object() ) {
                $author         = $this->_get_queried_object();
                $title['title'] = $author->display_name;
            } elseif ( $this->_is_year() )
                $title['title'] = $this->_get_the_date( $this->_x( 'Y', 'yearly archives date format' ) );
            elseif ( $this->_is_month() )
                $title['title'] = $this->_get_the_date( $this->_x( 'F Y', 'monthly archives date format' ) );
            elseif ( $this->_is_day() )
                $title['title'] = $this->_get_the_date();
            if ( ( $this->tp_paged >= 2 || $this->tp_page >= 2 ) && ! $this->_is_404() )
                $title['page'] = sprintf( $this->__( 'Page %s' ), max( $this->tp_paged, $this->tp_page ) );
            if ( $this->_is_front_page() )
                $title['tag_line'] = $this->_get_bloginfo( 'description', 'display' );
            else $title['site'] = $this->_get_bloginfo( 'name', 'display' );
            $sep = $this->_apply_filters( 'document_title_separator', '-' );
            $title = $this->_apply_filters( 'document_title_parts', $title );
            $title = implode( " $sep ", array_filter( $title ) );
            $title = $this->_apply_filters( 'document_title', $title );
            return $title;
        }//1156 from general-template
        /**
         * @description Displays title tag with content.
         * @return string
         */
        protected function _tp_get_render_title_tag():string{
            if (!$this->_current_theme_supports( 'title-tag' )) return false;
            return "<title>{$this->_tp_get_document_title()}</title>\n";
        }//1287 from general-template
        protected function _tp_render_title_tag():void{
            echo $this->_tp_get_render_title_tag();
        }
        /**
         * @description Display or retrieve page title for all areas of blog.
         * @param string $sep
         * @param string $sep_location
         * @return bool|null|string
         */
        protected function _tp_get_title( $sep = '&raquo;', $sep_location = '' ){
            $tp_locale = $this->_init_locale();
            $m        = $this->_get_query_var( 'm' );
            $year     = $this->_get_query_var( 'year' );
            $monthnum = $this->_get_query_var( 'monthnum' );
            $day      = $this->_get_query_var( 'day' );
            //$search   = $this->_get_query_var( 's' );//todo
            $title    = '';
            $t_sep = '%TP_TITLE_SEP%';
            static $post_type_object;
            if ( $this->_is_single() || ( $this->_is_home() && ! $this->_is_front_page() ) || ( $this->_is_page() && ! $this->_is_front_page() ) )
                $title = $this->_get_single_post_title( '' );
            if ( $this->_is_post_type_archive() ) {
                $post_type = $this->_get_query_var( 'post_type' );
                if ( is_array( $post_type ) ) $post_type = reset( $post_type );
                $post_type_object = $this->_get_post_type_object( $post_type );
                if ( ! $post_type_object->has_archive ) $title = $this->_get_post_type_archive_title( '');
            }
            if ( $this->_is_category() || $this->_is_tag() )
                $title = $this->_get_single_term_title( '');
            if ( $this->_is_tax() ) {
                $term = $this->_get_queried_object();
                if ( $term ) {
                    $tax   = $this->_get_taxonomy( $term->taxonomy );
                    $title = $this->_get_single_term_title( $tax->labels->name . $t_sep);
                }
            }
            if ( $this->_is_author() && ! $this->_is_post_type_archive() ) {
                $author = $this->_get_queried_object();
                if ( $author ) $title = $author->display_name;
            }
            if ($post_type_object->has_archive && $this->_is_post_type_archive())//todo
                $title = $this->_get_post_type_archive_title( '');
            if (! empty( $m ) && $this->_is_archive()) {
                $my_year  = substr( $m, 0, 4 );
                $my_month = substr( $m, 4, 2 );
                $my_day   = (int) substr( $m, 6, 2 );
                $title    = $my_year .
                    ( $my_month ? $t_sep . $tp_locale->get_month( $my_month ) : '' ) .
                    ( $my_day ? $t_sep . $my_day : '' );
            }
            if (! empty( $year ) && $this->_is_archive()) {
                $title = $year;
                if ( ! empty( $monthnum ) ) $title .= $t_sep . $tp_locale->get_month( $monthnum );
                if ( ! empty( $day ) ) $title .= $t_sep . $this->_zero_ise( $day, 2 );
            }
            if ( $this->_is_404() ) $title = $this->__( 'Page not found' );
            $prefix = '';
            if ( ! empty( $title ) ) $prefix = " $sep ";
            $title_array = $this->_apply_filters( 'tp_title_parts', explode( $t_sep, $title ) );
            if ( 'right' === $sep_location ) { // Separator on right, so reverse the order.
                $title_array = array_reverse( $title_array );
                $title       = implode( " $sep ", $title_array ) . $prefix;
            } else $title = $prefix . implode( " $sep ", $title_array );
            $title = $this->_apply_filters( 'tp_title', $title, $sep, $sep_location );
            if ( !$title ) { return false;}
            return $title;
        }//1320 from general-template
        protected function _tp_title( $sep = '&raquo;', $sep_location = '' ):void{
            echo $this->_tp_get_title( $sep, $sep_location);
        }//1320
        /**
         * @description Display or retrieve page title for post.
         * @param string $prefix
         * @return bool|string
         */
        protected function _get_single_post_title( $prefix = ''){
            $_post = $this->_get_queried_object();
            if ( ! isset( $_post->post_title ) )  return false;
            $title = $this->_apply_filters( 'single_post_title', $_post->post_title, $_post );
            if (! $title ) {return false;}
            return $prefix . $title;
        }//1465 from general-template
        protected function _single_post_title( $prefix = ''):void{
            echo $this->_get_single_post_title( $prefix);
        }
        /**
         * @description Display or retrieve title for a post type archive.
         * @param string $prefix
         * @return bool|string
         */
        protected function _get_post_type_archive_title( $prefix = ''){
            if ( ! $this->_is_post_type_archive() ) return false;
            $post_type = $this->_get_query_var( 'post_type' );
            if ( is_array( $post_type ) ) $post_type = reset( $post_type );
            $post_type_obj = $this->_get_post_type_object( $post_type );
            $title = $this->_apply_filters( 'post_type_archive_title', $post_type_obj->labels->name, $post_type );
            if (! $title ) {return false;}
            return $prefix . $title;
        }//1500 from general-template
        protected function _post_type_archive_title( $prefix = ''):void{
            echo $this->_get_post_type_archive_title( $prefix);
        }
        /**
         * @description Display or retrieve page title for category archive.
         * @param string $prefix
         * @return string
         */
        protected function _single_cat_title( $prefix = ''):string{
            return $this->_get_single_term_title( $prefix);
        }//1542 from general-template
    }
}else die;