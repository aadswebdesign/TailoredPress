<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-5-2022
 * Time: 19:18
 */
namespace TP_Core\Libs;
use TP_Core\Libs\DB\TP_Db;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Multisite\Blog\_ms_blog_02;
use TP_Core\Traits\Options\_option_01;
if(ABSPATH){
    final class TP_Site{
        use _action_01, _filter_01, _option_01, _cache_01;
        use _init_db, _init_error, _ms_blog_02;
        public $blog_id;
        public $domain = '';
        public $path = '';
        public $site_id = '0';
        public $registered = '0000-00-00 00:00:00';
        public $last_updated = '0000-00-00 00:00:00';
        public $public = '1';
        public $archived = '0';
        public $mature = '0';
        public $spam = '0';
        public $deleted = '0';
        public $lang_id = '0';
        public static function get_instance( $site_id ){
            $tpdb = (new static($site_id))->_init_db();
            $site_id = (int) $site_id;
            if ( ! $site_id ) return false;
            $_site = (new static($site_id))->_tp_cache_get( $site_id, 'sites' );
            if ( false === $_site && $tpdb instanceof TP_Db) {
                $_site = $tpdb->get_row( $tpdb->prepare( TP_SELECT . " * FROM {$tpdb->blogs} WHERE blog_id = %d LIMIT 1", $site_id ) );
                if ( empty( $_site ) || (new static($site_id))->_init_error( $_site ) )
                    $_site = -1;
                (new static($site_id))->_tp_cache_add( $site_id, $_site, 'sites' );
            }
            if ( is_numeric( $_site ) ) return false;
            return new TP_Site( $_site );
        }//157
        public function __construct( $site ) {
            foreach ( get_object_vars((object) $site ) as $key => $value )
                $this->$key = $value;
        }//194
        public function to_array(): array{
            return get_object_vars( $this );
        }//207
        public function __get( $key ){}//222
        public function __isset( $key ){
            switch ( $key ) {
                case 'id':
                case 'network_id':
                    return true;
                case 'blogname':
                case 'siteurl':
                case 'post_count':
                case 'home':
                    if ( ! $this->_did_action( 'ms_loaded' ) ) return false;
                    return true;
                default: // Custom properties added by 'site_details' filter.
                    if ( ! $this->_did_action( 'ms_loaded' ) ) return false;
                    $details = $this->__get_details();
                    if ( isset( $details->$key ) ) return true;
            }
            return false;
        }//257
        public function __set( $key, $value ){
            switch ( $key ) {
                case 'id':
                    $this->blog_id = (string) $value;
                    break;
                case 'network_id':
                    $this->site_id = (string) $value;
                    break;
                default:
                    $this->$key = $value;
            }
        }//294
        private function __get_details(){
            $details = $this->_tp_cache_get( $this->blog_id, 'site-details' );
            if ( false === $details ) {
                $this->_switch_to_blog( $this->blog_id );
                // Create a raw copy of the object for backward compatibility with the filter below.
                $details = new \stdClass();
                foreach ( get_object_vars( $this ) as $key => $value )
                    $details->$key = $value;
                $details->blogname   = $this->_get_option( 'blogname' );
                $details->siteurl    = $this->_get_option( 'siteurl' );
                $details->post_count = $this->_get_option( 'post_count' );
                $details->home       = $this->_get_option( 'home' );
                $this->_restore_current_blog();
                $this->_tp_cache_set( $this->blog_id, $details, 'site-details' );
            }
            $details = $this->_apply_filters_deprecated( 'blog_details', array( $details ), '4.7.0', 'site_details' );
            $details = $this->_apply_filters( 'site_details', $details );
            return $details;
        }//318
    }
}else die;