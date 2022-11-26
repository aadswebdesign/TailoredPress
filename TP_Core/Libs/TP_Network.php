<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-5-2022
 * Time: 14:22
 */
namespace TP_Core\Libs;
//use TP_Core\Libs\DB\TP_Db;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Load\_load_02;
use TP_Core\Traits\Multisite\_ms_network;
use TP_Core\Traits\Multisite\Site\_ms_site_01;
use TP_Core\Traits\Options\_option_03;
if(ABSPATH){
    class TP_Network{
        use _init_db, _init_error, _cache_01, _filter_01;
        use _option_03, _ms_site_01, _ms_network, _load_02;
        private $__id;
        private $__blog_id = '0';
        public $domain = '';
        public $path = '';
        //todo added
        public $network_id= '0';
        public $cookie_domain = '';
        public $site_name = '';
        public static function get_instance( $network_id ){
            $tpdb = (new self($network_id))->_init_db();
            $network_id = (int) $network_id;
            if ( ! $network_id ) return false;
            $_network = (new self($network_id))->_tp_cache_get( $network_id, 'networks' );//todo lookup
            if ( false === $_network) {
                $_network = $tpdb->get_row( $tpdb->prepare( TP_SELECT . " * FROM {$tpdb->site} WHERE id = %d LIMIT 1", $network_id ) );
                if ( empty( $_network ) || (new static($network_id))->_init_error( $_network ) )  $_network = -1;
                $_network = (new self($network_id))->_tp_cache_add( $network_id, $_network, 'networks' );
            }
            if ( is_numeric( $_network ) ) return false;
            return new TP_Network( $_network );
        }//94
        public function __construct( $network ) {
            foreach ( get_object_vars( $network ) as $key => $value )
                $this->$key = $value;
            $this->__set_site_name();
            $this->__set_cookie_domain();
        }//138
        public function __get( $key ) {
            switch ( $key ) {
                case 'id':
                    return (int) $this->__id;
                case 'blog_id':
                    return (string) $this->__get_main_site_id();
                case 'site_id':
                    return $this->__get_main_site_id();
            }
            return null;
        }//150
        public function __isset( $key ) {
            switch ( $key ) {
                case 'id':
                case 'blog_id':
                case 'site_id':
                    return true;
            }
            return false;
        }//173
        public function __set( $key, $value ) {
            switch ( $key ) {
                case 'id':
                    $this->__id = (int) $value;
                    break;
                case 'blog_id':
                case 'site_id':
                    $this->__blog_id = (string) $value;
                    break;
                default:
                    $this->$key = $value;
            }
        }//194
        private function __get_main_site_id(): int{
            $main_site_id = (int) $this->_apply_filters( 'pre_get_main_site_id', null, $this );
            if ( 0 < $main_site_id ) return $main_site_id;
            if ( 0 < (int) $this->__blog_id ) return (int) $this->__blog_id;
            if ( ( defined( 'DOMAIN_CURRENT_SITE' ) && defined( 'PATH_CURRENT_SITE' ) && DOMAIN_CURRENT_SITE === $this->domain && PATH_CURRENT_SITE === $this->path )
                || ( defined( 'SITE_ID_CURRENT_SITE' ) && SITE_ID_CURRENT_SITE === $this->__id ) ) {
                if ( defined( 'BLOG_ID_CURRENT_SITE' ) ) {
                    $this->__blog_id = (string) BLOG_ID_CURRENT_SITE;
                    return (int) $this->__blog_id;
                }
                if ( defined( 'BLOGID_CURRENT_SITE' ) ) { // Deprecated.
                    $this->__blog_id = (string) BLOGID_CURRENT_SITE;
                    return (int) $this->__blog_id;
                }
            }
            $site = $this->_get_site();
            if (($site instanceof TP_Site) && $site->domain === $this->domain && $site->path === $this->path )
                $main_site_id = (int) $site->site_id;//todo
            else {
                $cache_key = 'network:' . $this->__id . ':main_site';
                $main_site_id = $this->_tp_cache_get( $cache_key, 'site-options' );
                if ( false === $main_site_id ) {
                    $_sites = $this->_get_sites(
                        ['fields' => 'ids','number' => 1,'domain' => $this->domain,'path' => $this->path,'network_id' => $this->__id,]
                    );
                    $main_site_id = ! empty( $_sites ) ? array_shift( $_sites ) : 0;
                    $this->_tp_cache_add( $cache_key, $main_site_id, 'site-options' );
                }
            }
            $this->__blog_id = (string) $main_site_id;
            return (int) $this->__blog_id;
        }//218
        private function __set_site_name(): void {
            if ( ! empty( $this->site_name ) ) return;
            $default         = ucfirst( $this->domain );
            $this->site_name = $this->_get_network_option( $this->__id, 'site_name', $default );
        }//286
        private function __set_cookie_domain(): void{
            if ( ! empty( $this->cookie_domain ) ) return;
            $this->cookie_domain = $this->domain;
            if (strpos($this->cookie_domain, 'www.') === 0)
                $this->cookie_domain = substr( $this->cookie_domain, 4 );
        }//304
        public static function get_by_path( $domain = '', $path = '', $segments = null ){
            static $_network;
            $domains = array( $domain );
            $pieces  = explode( '.', $domain );
            while ( array_shift( $pieces ) ) {
                if ( ! empty( $pieces ) ) $domains[] = implode( '.', $pieces );
            }
            $using_paths = true;
            if ( (new static($domain))->_tp_using_ext_object_cache() ) {
                $using_paths = (new static($domain))->_tp_cache_get( 'networks_have_paths', 'site-options' );
                if ( false === $using_paths ) {
                    $using_paths = (new static($domain))->_get_networks(
                        array(
                            'number'       => 1,
                            'count'        => true,
                            'path__not_in' => '/',
                        )
                    );
                    (new static($domain))->_tp_cache_add( 'networks_have_paths', $using_paths, 'site-options' );
                }
            }
            $paths = [];
            if ( $using_paths ) {
                $path_segments = array_filter( explode( '/', trim( $path, '/' ) ) );
                $segments = (new static($domain))->_apply_filters( 'network_by_path_segments_count', $segments, $domain, $path );
                if ( ( null !== $segments ) && count( $path_segments ) > $segments )
                    $path_segments = array_slice( $path_segments, 0, $segments );
                while ( count( $path_segments ) ) {
                    $paths[] = '/' . implode( '/', $path_segments ) . '/';
                    array_pop( $path_segments );
                }
                $paths[] = '/';
            }
            $pre = (new static($domain))->_apply_filters( 'pre_get_network_by_path', null, $domain, $path, $segments, $paths );
            if ( null !== $pre ) return $pre;
            if ( ! $using_paths ) {
                $networks = (new static($domain))->_get_networks(
                    ['number' => 1,'orderby' => ['domain_length' => 'DESC',], 'domain__in' => $domains,]
                );
                if ( ! empty( $networks ) ) return array_shift( $networks );
                return false;
            }
            $networks = (new static($domain))->_get_networks(
                ['orderby' => ['domain_length' => 'DESC','path_length' => 'DESC',],
                    'domain__in' => $domains, 'path__in' => $paths,]
            );
            $found = false;
            foreach ( (array)$networks as $network ) {
                if ( ( $network->domain === $domain ) || ( "www.{$network->domain}" === $domain ) ) {
                    if ( in_array( $network->path, $paths, true ) ) {
                        $found = true;
                        break;
                    }
                }
                if ( '/' === $network->path ) {
                    $found = true;
                    break;
                }
                $_network = $network;
            }
            if ( true === $found ) return $_network;
            return false;
        }//331
    }
}else die;