<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 14:42
 */
namespace TP_Core\Traits\Multisite\Blog;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\TP_Site;
if(ABSPATH){
    trait _ms_blog_01 {
        use _init_db;
        /**
         * @description Update the last_updated field for the current site.
         */
        protected function _tp_mu_update_blogs_date():void{
            $site_id = $this->_get_current_blog_id();
            $this->_update_blog_details( $site_id, array( 'last_updated' => $this->_current_time( 'mysql', true ) ) );
            $this->_do_action( 'tpmu_blog_updated', $site_id );
        }//19
        /**
         * @description Get a full blog URL, given a blog ID.
         * @param $blog_id
         * @return string
         */
        protected function _get_blog_address_by_id( $blog_id ):string{
            $bloginfo = $this->_get_site( (int) $blog_id );
            if ( empty( $bloginfo ) ) return '';
            $scheme = parse_url( $bloginfo->home, PHP_URL_SCHEME );
            $scheme = empty( $scheme ) ? 'http' : $scheme;
            return $this->_esc_url( $scheme . '://' . $bloginfo->domain . $bloginfo->path );
        }//41
        /**
         * @description Get a full blog URL, given a blog name.
         * @param $blogname
         * @return mixed
         */
        protected function _get_blog_address_by_name( $blogname ){
            if ( $this->_is_subdomain_install() ) {
                if ( 'main' === $blogname ) $blogname = 'www';
                $url = rtrim( $this->_network_home_url(), '/' );
                if ( ! empty( $blogname ) ) $url = preg_replace( '|^([^\.]+://)|', '${1}' . $blogname . '.', $url );
            } else $url = $this->_network_home_url( $blogname );
            return $this->_esc_url( $url . '/' );
        }//62
        /**
         * @description Retrieves a sites ID given its (sub domain or directory) slug.
         * @param $slug
         * @return mixed|null
         */
        protected function _get_id_from_blogname( $slug ){
            $current_network = $this->_get_network();
            $slug            = trim( $slug, '/' );
            if ( $this->_is_subdomain_install() ) {
                $domain = $slug . '.' . preg_replace( '|^www\.|', '', $current_network->domain );
                $path   = $current_network->path;
            } else {
                $domain = $current_network->domain;
                $path   = $current_network->path . $slug . '/';
            }
           $site_ids = $this->_get_sites(
                ['number' => 1, 'fields' => 'ids', 'domain' => $domain, 'path' => $path, 'update_site_meta_cache' => false,]
            );
            if ( empty( $site_ids ) ) return null;
            return array_shift( $site_ids );
        }//86
        /**
         * @description Retrieve the details for a blog from the blogs table and blog options.
         * @param null $fields
         * @param bool $get_all
         * @return bool|TP_Site
         */
        protected function _get_blog_details($fields = null, $get_all = true ){
            $this->tpdb = $this->_init_db();
            if ( is_array( $fields ) ) {
                if ( isset( $fields['blog_id'] ) ) {
                    $blog_id = $fields['blog_id'];
                } elseif ( isset( $fields['domain'], $fields['path'] ) ) {
                    $key  = md5( $fields['domain'] . $fields['path'] );
                    $blog = $this->_tp_cache_get( $key, 'blog-lookup' );
                    if ( false !== $blog ) return $blog;
                    if (strpos($fields['domain'], 'www.') === 0) {
                        $nowww = substr( $fields['domain'], 4 );
                        $blog  = $this->tpdb->get_row( $this->tpdb->prepare( TP_SELECT . " * FROM $this->tpdb->blogs WHERE domain IN (%s,%s) AND path = %s ORDER BY CHAR_LENGTH(domain) DESC", $nowww, $fields['domain'], $fields['path'] ) );
                    } else $blog = $this->tpdb->get_row( $this->tpdb->prepare( TP_SELECT . " * FROM $this->tpdb->blogs WHERE domain = %s AND path = %s", $fields['domain'], $fields['path'] ) );
                    if ($blog instanceof TP_Site && $blog ) {
                        $this->_tp_cache_set( $blog->blog_id . 'short', $blog, 'blog-details' );
                        $blog_id = $blog->blog_id;
                    } else return false;
                } elseif ( isset( $fields['domain'] ) && $this->_is_subdomain_install() ) {
                    $key  = md5( $fields['domain'] );
                    $blog = $this->_tp_cache_get( $key, 'blog-lookup' );
                    if ( false !== $blog ) return $blog;
                    if (strpos($fields['domain'], 'www.') === 0) {
                        $nowww = substr( $fields['domain'], 4 );
                        $blog  = $this->tpdb->get_row( $this->tpdb->prepare( TP_SELECT . " * FROM $this->tpdb->blogs WHERE domain IN (%s,%s) ORDER BY CHAR_LENGTH(domain) DESC", $nowww, $fields['domain'] ) );
                    } else $blog = $this->tpdb->get_row( $this->tpdb->prepare( TP_SELECT . " * FROM $this->tpdb->blogs WHERE domain = %s", $fields['domain'] ) );
                     if ($blog instanceof TP_Site && $blog ) {
                        $this->_tp_cache_set( $blog->blog_id . 'short', $blog, 'blog-details' );
                        $blog_id = $blog->blog_id;
                    } else return false;
                } else {
                    return false;
                }
            } else if ( ! $fields ) $blog_id = $this->_get_current_blog_id();
            elseif ( ! is_numeric( $fields ) ) $blog_id = $this->_get_id_from_blogname( $fields );
            else $blog_id = $fields;
            $blog_id = (int) $blog_id;
            $all     = $get_all ? '' : 'short';
            $details = $this->_tp_cache_get( $blog_id . $all, 'blog-details' );
            if ( $details ) {
                if ( ! is_object( $details ) ) {
                    if ( -1 === $details ) return false;
                    else {
                        $this->_tp_cache_delete( $blog_id . $all, 'blog-details' );
                        unset( $details );
                    }
                } else return $details;
            }
            if ( $get_all ) $details = $this->_tp_cache_get( $blog_id . 'short', 'blog-details' );
            else{ $details = $this->_tp_cache_get( $blog_id, 'blog-details' );
                 // If short was requested and full cache is set, we can return.
                 if ( $details ) {
                    if ( ! is_object( $details ) ) {
                         if ( -1 === $details ) return false;
                         else {
                            $this->_tp_cache_delete( $blog_id, 'blog-details' );
                            unset( $details );
                         }
                     } else return $details;
                 }
            }
            if ( empty( $details ) ) {
                $details = TP_Site::get_instance( $blog_id );
                if ( ! $details ) {
                    $this->_tp_cache_set( $blog_id, -1, 'blog-details' );
                    return false;
                }
            }
            if ( ! $details instanceof TP_Site ) $details = new TP_Site( $details );
            if ( ! $get_all ) {
                $this->_tp_cache_set( $blog_id . $all, $details, 'blog-details' );
                return $details;
            }
            $switched_blog = false;
            if ( $this->_get_current_blog_id() !== $blog_id ) {
                $this->_switch_to_blog( $blog_id );
                $switched_blog = true;
            }
            $details->blogname   = $this->_get_option( 'blogname' );
            $details->siteurl    = $this->_get_option( 'siteurl' );
            $details->post_count = $this->_get_option( 'post_count' );
            $details->home       = $this->_get_option( 'home' );
            if ( $switched_blog ) $this->_restore_current_blog();
            $details = $this->_apply_filters_deprecated( 'blog_details', array( $details ), '4.7.0', 'site_details' );
            $this->_tp_cache_set( $blog_id . $all, $details, 'blog-details' );
            $key = md5( $details->domain . $details->path );
            $this->_tp_cache_set( $key, $details, 'blog-lookup' );
            return $details;
        }//128
        /**
         * @description Clear the blog details cache.
         * @param int $blog_id
         */
        protected function _refresh_blog_details( $blog_id = 0 ):void{
            $blog_id = (int) $blog_id;
            if ( ! $blog_id )
                $blog_id = $this->_get_current_blog_id();
            $this->_clean_blog_cache( $blog_id );
        }//282
        /**
         * @description Update the details for a blog. Updates the blogs table for a given blog ID.
         * @param $blog_id
         * @param array $details
         * @return bool
         */
        protected function _update_blog_details( $blog_id,array $details):bool{
            if ( empty( $details ) ) return false;
            if ( is_object((object) $details ) ) $details = get_object_vars((object) $details );
            $site = $this->_tp_update_site( $blog_id, $details );
            if ( $this->_init_error( $site ) ) return false;
            return true;
        }//302
        /**
         * @description Cleans the site details cache for a site.
         * @param int $site_id
         */
        protected function _clean_site_details_cache( $site_id = 0 ):void{
            $site_id = (int) $site_id;
            if ( ! $site_id ) $site_id = $this->_get_current_blog_id();
            $this->_tp_cache_delete( $site_id, 'site-details' );
            $this->_tp_cache_delete( $site_id, 'blog-details' );
        }//329
        /**
         * @description Retrieve option value for a given blog id based on name of option.
         * @param $id
         * @param $option
         * @param bool $default
         * @return mixed
         */
        protected function _get_blog_option( $id, $option, $default = false ){
            $id = (int) $id;
            if ( empty( $id ) ) $id = $this->_get_current_blog_id();
            if ( $this->_get_current_blog_id() === $id ) return $this->_get_option( $option, $default );
            $this->_switch_to_blog( $id );
            $value = $this->_get_option( $option, $default );
            $this->_restore_current_blog();
            return $this->_apply_filters( "blog_option_{$option}", $value, $id );
        }//356
        /**
         * @description Add a new option for a given blog ID.
         * @param $id
         * @param $option
         * @param $value
         * @return mixed
         */
        protected function _add_blog_option( $id, $option, $value ){
            $id = (int) $id;
            if ( empty( $id ) ) $id = $this->_get_current_blog_id();
            if ( $this->_get_current_blog_id() === $id ) return $this->_add_option( $option, $value );
            $this->_switch_to_blog( $id );
            $return = $this->_add_option( $option, $value );
            $this->_restore_current_blog();
            return $return;
        }//403
    }
}else die;