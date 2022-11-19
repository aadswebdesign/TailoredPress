<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:23
 */
namespace TP_Core\Traits\Templates;
if(ABSPATH){
    trait _link_template_09 {
        /**
         * @description Retrieves a paginated navigation to next/previous set of comments, when applicable.
         * @param \array[] ...$args
         * @return string
         */
        protected function _get_the_comments_pagination(array ...$args):string{
            $navigation = '';
            if ( ! empty( $args['screen_reader_text'] ) && empty( $args['aria_label'] ) )
                $args['aria_label'] = $args['screen_reader_text'];
            $args = $this->_tp_parse_args( $args,['screen_reader_text' => $this->__( 'Comments navigation' ),'aria_label' => $this->__( 'Comments' ),'class' => 'comments-pagination',]);
            $args['echo'] = false;
            if ( isset( $args['type'] ) && 'array' === $args['type'] ) $args['type'] = 'plain';
            $links = $this->_paginate_comments_links( $args );
            if ( $links ) $navigation = $this->_navigation_markup( $links, $args['class'], $args['screen_reader_text'], $args['aria_label'] );
            return $navigation;
        }//3229 from link-template
        /**
         * @description Retrieves the URL for the current site where the front end is accessible.
         * @param string $path
         * @param null $scheme
         * @return mixed
         */
        protected function _home_url( $path = '', $scheme = null ){
            return $this->_get_home_url(null, $path, $scheme);
        }//3286 //todo
        /**
         * @description Retrieves the URL for a given site where the front end is accessible.
         * @param null $blog_id
         * @param string $path
         * @param null $scheme
         * @return mixed
         */
        protected function _get_home_url( $blog_id = null, $path = '', $scheme = null ){
            $original_scheme = $scheme;
            if ( empty( $blog_id ) || ! $this->_is_multisite() ) {
                $url = $this->_get_option( 'home' );
            } else {
                $this->_switch_to_blog( $blog_id );
                $url = $this->_get_option( 'home' );
                $this->_restore_current_blog();
            }
            if ( ! in_array( $scheme, array( 'http', 'https', 'relative' ), true ) ) {
                if ( $this->_is_ssl() )  $scheme = 'https';
                 else  $scheme = parse_url( $url, PHP_URL_SCHEME );
            }
            $url = $this->_set_url_scheme( $url, $scheme );
            if ( $path && is_string( $path ) )
                $url .= '/' . ltrim( $path, '/' );
            return $this->_apply_filters( 'home_url', $url, $path, $original_scheme, $blog_id );
        }//3350 todo
        /**
         * @description Retrieves the URL for the current site where TailoredPress application files
         * @param string $path
         * @param null $scheme
         * @return mixed
         */
        protected function _site_url( $path = '', $scheme = null ){
            return $this->_get_site_url( null, $path, $scheme );
        }//3358
        /**
         * @description Retrieves the URL for a given site where TailoredPress application files
         * @param null $blog_id
         * @param string $path
         * @param null $scheme
         * @return mixed
         */
        protected function _get_site_url( $blog_id = null, $path = '', $scheme = null ){
            if ( empty( $blog_id ) || ! $this->_is_multisite() ) $url = $this->_get_option( 'siteurl' );
            else {
                $this->_switch_to_blog( $blog_id );
                $url = $this->_get_option( 'siteurl' );
                $this->_restore_current_blog();
            }
            $url = $this->_set_url_scheme( $url, $scheme );
            if ( $path && is_string( $path ) ) $url .= '/' . ltrim( $path, '/' );
            return $this->_apply_filters( 'site_url', $url, $path, $scheme, $blog_id );
        }//3379 from link-template
        /**
         * @description Retrieves the URL to the admin area for the current site.
         * @param string $path
         * @param string $scheme
         * @return mixed
         */
        protected function _admin_url($path='', $scheme = 'admin'){
            return $this->_get_admin_url( null, $path, $scheme );
        }//3418 from link-template
        /**
         * @description Retrieves the URL to the admin area for a given site.
         * @param null $blog_id
         * @param string $path
         * @param string $scheme
         * @return mixed
         */
        protected function _get_admin_url( $blog_id = null, $path = '', $scheme = 'admin' ){
            $url = $this->_get_site_url( $blog_id, 'TailoredPress/TP_Admin/', $scheme );//todo take TailoredPress out
            if ( $path && is_string( $path ) ) $url .= ltrim( $path, '/' );
            return $this->_apply_filters( 'admin_url', $url, $path, $blog_id, $scheme );
        }//3434 from link-template
        /**
         * @description Retrieves the URL to the includes directory.
         * @param string $path
         * @param null $scheme
         * @return mixed
         */
        protected function _includes_url( $path = '', $scheme = null ){
            $url = $this->_site_url( TP_CORE_ASSETS . '/', $scheme );
            if ( $path && is_string( $path ) )  $url .= ltrim( $path, '/' );

            return $this->_apply_filters( 'includes_url', $url, $path, $scheme );
        }//3466 from link-template
        /**
         * @description Retrieves the URL to the content directory.
         * @param string $path
         * @return mixed
         */
        protected function _content_url( $path = '' ){
            $url = $this->_set_url_scheme( TP_CONTENT_URL );
            if ( $path && is_string( $path ) ) $url .= '/' . ltrim( $path, '/' );
            return $this->_apply_filters( 'content_url', $url, $path );
        }//3496 from link-template
        //todo @description alternative for plugin url
        //protected function _libs_url( $path = '', $lib = ''){
            //$path = $this->_tp_normalize_path( $path );
            //$lib = $this->_tp_normalize_path( $lib );
            //$url = set_url_scheme( $url );
            //todo TP_LIBS_URL
        //}
        /**
         * @description Retrieves the site URL for the current network.
         * @param string $path
         * @param null $scheme
         * @return mixed
         */
        protected function _network_site_url( $path = '', $scheme = null ){
            if ( ! $this->_is_multisite() ) return $this->_site_url( $path, $scheme );
            $current_network = $this->_get_network();
            if ( 'relative' === $scheme ) $url = $current_network->path;
            else $url = $this->_set_url_scheme( 'http://' . $current_network->domain . $current_network->path, $scheme );
            if ( $path && is_string( $path ) ) $url .= ltrim( $path, '/' );
            return $this->_apply_filters( 'network_site_url', $url, $path, $scheme );
        }//3584 from link-template
    }
}else die;