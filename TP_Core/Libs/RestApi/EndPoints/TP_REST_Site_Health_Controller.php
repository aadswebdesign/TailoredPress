<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Admin\Libs\Adm_Debug_Data;
use TP_Admin\Libs\Adm_Site_Health;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_REST_Site_Health_Controller extends TP_REST_Controller {
        private $__site_health;
        public function __construct( $site_health ) {
            $this->_namespace = 'tp-site-health/v1';
            $this->_rest_base = 'tests';
            $this->__site_health = $site_health;
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,
                sprintf('/%s/%s',$this->_rest_base,'background-updates'),
                [['methods'=> TP_GET,'callback'=> [$this, 'test_background_updates'],
                    'permission_callback' => function () {
                        return $this->_validate_request_permission( 'background_updates' );
                    },],'schema' => [$this, 'get_public_item_schema'],]
            );
            $this->_register_rest_route(
                $this->_namespace,sprintf('/%s/%s',$this->_rest_base,'loopback-requests'),
                [['methods' => TP_GET,'callback' => [$this, 'test_loopback_requests'],
                        'permission_callback' => function () {
                            return $this->_validate_request_permission( 'loopback_requests' );
                        },],'schema' => [$this, 'get_public_item_schema'],]
            );
            $this->_register_rest_route(
                $this->_namespace,sprintf('/%s/%s',$this->_rest_base,'https-status'),
                [['methods' => TP_GET,'callback' => [$this, 'test_https_status'],
                    'permission_callback' => function () {
                        return $this->_validate_request_permission( 'https_status' );
                    },],'schema' => [$this, 'get_public_item_schema'],]
            );
            $this->_register_rest_route(
                $this->_namespace,sprintf('/%s/%s', $this->_rest_base,'dotorg-communication'),
                [['methods'=> TP_GET,'callback' => [$this, 'test_dot_org_communication'],
                    'permission_callback' => function () {
                        return $this->_validate_request_permission( 'dotorg_communication' );
                    },],'schema' => [$this, 'get_public_item_schema'],]
            );
            $this->_register_rest_route(
                $this->_namespace,sprintf('/%s/%s',$this->_rest_base,'authorization-header'),
                [['methods'=> TP_GET,'callback'=> [$this, 'test_authorization_header'],
                    'permission_callback' => function () {
                        return $this->_validate_request_permission( 'authorization_header' );
                    },],'schema' => [$this, 'get_public_item_schema'],]
            );
            $this->_register_rest_route(
                $this->_namespace,sprintf('/%s','directory-sizes'),
                ['methods' => TP_GET,'callback' => [$this, 'get_directory_sizes'],
                    'permission_callback' => function() {
                        return $this->_validate_request_permission( 'debug_enabled' ) && ! $this->_is_multisite();},]
            );
        }//49
        protected function _validate_request_permission( $check ):string{
            $default_capability = 'view_site_health_checks';
            $capability = $this->_apply_filters( "site_health_test_rest_capability_{$check}", $default_capability, $check );
            return $this->_current_user_can( $capability );
        }//169
        public function test_background_updates():string{
            $this->_load_admin_textdomain();
            if( $this->__site_health instanceof Adm_Site_Health ){}//todo
            return $this->__site_health->get_test_background_updates();
        }//192
        public function test_dot_org_communication(){
            $this->_load_admin_textdomain();
            if( $this->__site_health instanceof Adm_Site_Health ){}
            return $this->__site_health->get_test_dot_org_communication();
        }//204
        public function test_loopback_requests(){
            $this->_load_admin_textdomain();
            if( $this->__site_health instanceof Adm_Site_Health ){}
            return $this->__site_health->get_test_loopback_requests();
        }//216
        public function test_https_status(){
            $this->_load_admin_textdomain();
            if( $this->__site_health instanceof Adm_Site_Health ){}
            return $this->__site_health->get_test_https_status();
        }//228
        public function test_authorization_header(){
            $this->_load_admin_textdomain();
            if( $this->__site_health instanceof Adm_Site_Health ){}
            return $this->__site_health->get_test_authorization_header();
        }//240
        public function get_directory_sizes(){
            $this->_load_admin_textdomain();
            $sizes_data = Adm_Debug_Data::get_sizes();
            $all_sizes  = array( 'raw' => 0 );
            foreach ((array) $sizes_data as $name => $value ) {
                $name = $this->_sanitize_text_field( $name );
                $data = [];
                if ( isset( $value['size'] ) ) {
                    if ( is_string( $value['size'] ) ) $data['size'] = $this->_sanitize_text_field( $value['size'] );
                    else $data['size'] = (int) $value['size'];
                }
                if ( isset( $value['debug'] ) ) {
                    if ( is_string( $value['debug'] ) ) $data['debug'] = $this->_sanitize_text_field( $value['debug'] );
                    else $data['debug'] = (int) $value['debug'];
                }
                if ( ! empty( $value['raw'] ) ) $data['raw'] = (int) $value['raw'];
                $all_sizes[ $name ] = $data;
            }
            if ( isset( $all_sizes['total_size']['debug'] ) && 'not available' === $all_sizes['total_size']['debug'] )
                return new TP_Error( 'not_available', $this->__( 'Directory sizes could not be returned.' ), array( 'status' => 500 ) );
            return $all_sizes;
        }//252
        protected function _load_admin_textdomain(): void{
            if ( ! $this->_is_admin() ) {
                $locale = $this->_determine_locale();
                $this->_load_textdomain( 'default', TP_ADMIN_LANG . "/admin-$locale.mo" );//todo
            }
        }//304
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $schema = [

                'properties' => []
            ];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//319
    }
}else die;