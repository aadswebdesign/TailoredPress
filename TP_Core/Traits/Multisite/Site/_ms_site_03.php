<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-5-2022
 * Time: 10:39
 */
namespace TP_Core\Traits\Multisite\Site;
use TP_Core\Libs\TP_Site;
if(ABSPATH){
    trait _ms_site_03{
        /**
         * @description Triggers actions on site status updates.
         * @param $new_site
         * @param null $old_site
         */
        protected function _tp_maybe_transition_site_statuses_on_update( $new_site,$old_site = null ):void{
            $site_id = $new_site->site_id;
            if ( ! $old_site )  $old_site = new TP_Site( new \stdClass() );
            if ( $new_site->spam !== $old_site->spam ) {
                if ( 1 === $new_site->spam ) $this->_do_action( 'make_spam_blog', $site_id );
                else  $this->_do_action( 'make_ham_blog', $site_id );
            }
            if ( $new_site->mature !== $old_site->mature ) {
                if ( 1 === $new_site->mature ) $this->_do_action( 'mature_blog', $site_id );
                else  $this->_do_action( 'un_mature_blog', $site_id );
            }
            if ( $new_site->archived !== $old_site->archived ) {
                if ( 1 === $new_site->archived ) $this->_do_action( 'archive_blog', $site_id );
                else $this->_do_action( 'un_archive_blog', $site_id );
            }
            if ( $new_site->deleted !== $old_site->deleted ) {
                if ( 1 === $new_site->deleted ) $this->_do_action( 'make_delete_blog', $site_id );
                else $this->_do_action( 'make_un_delete_blog', $site_id );
            }
            if ( $new_site->public !== $old_site->public )
                $this->_do_action( 'update_blog_public', $site_id, $new_site->public );
        }//1121
        /**
         * @description Cleans the necessary caches after specific site data has been updated.
         * @param $new_site
         * @param $old_site
         */
        protected function _tp_maybe_clean_new_site_cache_on_update( $new_site, $old_site ):void{
            if ( $old_site->domain !== $new_site->domain || $old_site->path !== $new_site->path )
                $this->_clean_blog_cache( $new_site );
        }//1247
        /**
         * @description Updates the `blog_public` option for a given site ID.
         * @param $site_id
         * @param $public
         */
        protected function _tp_update_blog_public_option_on_site_update( $site_id, $public ):void{
            if ( ! $this->_tp_is_site_initialized( $site_id ) ) return;
            $this->_update_blog_option( $site_id, 'blog_public', $public );
        }//1261
        /**
         * @description Sets the last changed time for the 'sites' cache group.
         */
        protected function _tp_cache_set_sites_last_changed():void{
            $this->_tp_cache_set( 'last_changed', microtime(), 'sites' );
        }//1276
        /**
         * @description Aborts calls to site meta if it is not supported.
         * @param $check
         * @return bool
         */
        protected function _tp_check_site_meta_support_prefilter( $check ):bool{
            if ( ! $this->_is_site_meta_supported() ) {
                /* translators: %s: Database table name. */
                $this->_doing_it_wrong( __FUNCTION__, sprintf( $this->__( 'The %s table is not installed. Please run the network database upgrade.' ), $GLOBALS['wpdb']->blogmeta ), '5.1.0' );
                return false;
            }
            return $check;
        }//1290
    }
}else die;