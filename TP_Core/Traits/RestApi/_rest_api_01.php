<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 08:35
 */
namespace TP_Core\Traits\RestApi;
use TP_Core\Traits\Inits\_init_core;
use TP_Core\Traits\Inits\_init_rewrite;
use TP_Core\Libs\TP_Core;
use TP_Core\Libs\Post\TP_Post_Type;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Application_Passwords_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Comments_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Users_Controller;
use TP_Core\Libs\RestApi\TP_REST_Server;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Revisions_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Auto_Saves_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Post_Types_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Post_Statuses_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Taxonomies_Controller;
use TP_Core\Libs\TP_Taxonomy;
use TP_Core\Libs\RestApi\Search\TP_REST_Post_Search_Handler;
use TP_Core\Libs\RestApi\Search\TP_REST_Term_Search_Handler;
use TP_Core\Libs\RestApi\Search\TP_REST_Post_Format_Search_Handler;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Search_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Block_Renderer_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Block_Types_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Global_Styles_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Settings_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Themes_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Pattern_Directory_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Site_Health_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_URL_Details_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Menu_Locations_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Edit_Site_Export_Controller;
use TP_Admin\Libs\Adm_Site_Health;
if(ABSPATH){
    trait _rest_api_01{
        use _init_rewrite;
        use _init_core;
        /**
         * @description Registers a REST API route.
         * @param $namespace
         * @param $route
         * @param array $args
         * @param bool $override
         * @return bool
         */
        protected function _register_rest_route( $namespace, $route, $args = [], $override = false ):bool{
            if (empty( $namespace )) {
                $this->_doing_it_wrong( 'register_rest_route', $this->__( 'Routes must be name spaced with theme name and version.' ), '0.0.1' );
                return false;
            }

            if (empty( $route )) {
                $this->_doing_it_wrong( 'register_rest_route', $this->__( 'Route must be specified.' ), '0.0.1' );
                return false;
            }
            $clean_namespace = trim( $namespace, '/' );
            if ( $clean_namespace !== $namespace )
                $this->_doing_it_wrong( __FUNCTION__, $this->__( 'Namespace must not start or end with a slash.' ), '0.0.1' );
            if ( ! $this->_did_action( 'rest_api_init' ) ) {
                $this->_doing_it_wrong(
                    'register_rest_route',
                    sprintf($this->__( 'REST API routes must be registered on the %s action.' ),
                        '<code>rest_api_init</code>'),'0.0.1');
            }
            if ( isset( $args['args'] ) ) {
                $common_args = $args['args'];
                unset( $args['args'] );
            } else  $common_args = [];
            if ( isset( $args['callback'] ) )$args = array( $args );
            $defaults = ['methods'  => 'GET','callback' => null,'args' => [],];
            foreach ( $args as $key => &$arg_group ) {
                if ( ! is_numeric( $key)) continue;
                $arg_group         = $this->_tp_array_merge( $defaults, $arg_group );
                $arg_group['args'] = $this->_tp_array_merge( $common_args, $arg_group['args'] );
                if ( ! isset( $arg_group['permission_callback'] ) ) {
                    $this->_doing_it_wrong(
                        __FUNCTION__,
                        sprintf(
                        /* translators: 1: The REST API route being registered, 2: The argument name, 3: The suggested function name. */
                            $this->__( 'The REST API route definition for %1$s is missing the required %2$s argument. For REST API routes that are intended to be public, use %3$s as the permission callback.' ),
                            '<code>' . $clean_namespace . '/' . trim( $route, '/' ) . '</code>',
                            '<code>permission_callback</code>',
                            '<code>__return_true</code>'
                        ),
                        '0.0.0'
                    );
                }
            }
            unset($arg_group);
            $full_route = '/' . $clean_namespace . '/' . trim( $route, '/' );
            $rest_server = $this->_rest_get_server();
            if($rest_server  instanceof  TP_REST_Server){} //todo
            $rest_server->register_route( $clean_namespace, $full_route, $args, $override );
            return true;
        }//34
        /**
         * @description Registers a new field on an existing TailoredPress object type.
         * @param $object_type
         * @param $attribute
         * @param array $args
         */
        protected function _register_rest_field( $object_type, $attribute, $args = [] ):void{
            $defaults = ['get_callback' => null,'update_callback' => null,'schema' => null,];
            $args = $this->_tp_parse_args( $args, $defaults );
            $object_types = (array) $object_type;
            foreach ( $object_types as $object_type_field )
                $this->tp_rest_additional_fields[ $object_type_field ][ $attribute ] = $args;
        }//137
        /**
         * @description Registers rewrite rules for the REST API.
         */
        protected function _rest_api_init():void{
            $this->_rest_api_register_rewrites();
            if($this->_tp_core instanceof TP_Core)
            $this->_tp_core->add_query_var( 'rest_route' );
        }//163
        /**
         * todo adjusting the routes
         * @description Adds REST rewrite rules.
         */
        protected function _rest_api_register_rewrites():void{
            $tp_rewrite = $this->_init_rewrite();
            $this->_add_rewrite_rule( '^' . $this->_rest_get_url_prefix() . '/?$', 'index.php?rest_route=/', 'top' );
            $this->_add_rewrite_rule( '^' . $this->_rest_get_url_prefix() . '/(.*)?', 'index.php?rest_route=/$matches[1]', 'top' );
            $this->_add_rewrite_rule( '^' . $tp_rewrite->index . '/' . $this->_rest_get_url_prefix() . '/?$', 'index.php?rest_route=/', 'top' );
            $this->_add_rewrite_rule( '^' . $tp_rewrite->index . '/' . $this->_rest_get_url_prefix() . '/(.*)?', 'index.php?rest_route=/$matches[1]', 'top' );
        }//178
        /**
         * @description Registers the default REST API filters.
         */
        protected function _rest_api_default_filters():void{
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                // Deprecated reporting.
                $this->_add_action( 'deprecated_function_run', 'rest_handle_deprecated_function', 10, 3 );
                $this->_add_filter( 'deprecated_function_trigger_error', '__return_false' );
                $this->_add_action( 'deprecated_argument_run', 'rest_handle_deprecated_argument', 10, 3 );
                $this->_add_filter( 'deprecated_argument_trigger_error', '__return_false' );
                $this->_add_action( 'doing_it_wrong_run', 'rest_handle_doing_it_wrong', 10, 3 );
                $this->_add_filter( 'doing_it_wrong_trigger_error', '__return_false' );
            }
            $this->_add_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
            $this->_add_filter( 'rest_post_dispatch', 'rest_send_allow_header', 10, 3 );
            $this->_add_filter( 'rest_post_dispatch', 'rest_filter_response_fields', 10, 3 );
            $this->_add_filter( 'rest_pre_dispatch', 'rest_handle_options_request', 10, 3 );
            $this->_add_filter( 'rest_index', 'rest_add_application_passwords_to_index' );
        }//195
        /**
         * @description Registers default REST API routes.
         */
        protected function _create_initial_rest_routes():void{
            foreach ( $this->_get_post_types(['show_in_rest' => true], 'objects' ) as $post_type ) {
                $controller = null;
                if($post_type instanceof TP_Post_Type ){$controller = $post_type->get_rest_controller();}
                if ( ! $controller ) continue;
                if( $controller instanceof TP_REST_Controller ){ $controller->register_routes();}
                if ( $this->_post_type_supports( $post_type->name, 'revisions' ) ) {
                    $revisions_controller = new TP_REST_Revisions_Controller( $post_type->name );
                    $revisions_controller->register_routes();
                }
                if ( 'attachment' !== $post_type->name ) {
                    $autosaves_controller = new TP_REST_Auto_Saves_Controller( $post_type->name );
                    $autosaves_controller->register_routes();
                }
            }
            $controller = new TP_REST_Post_Types_Controller;
            $controller->register_routes();
            $controller = new TP_REST_Post_Statuses_Controller;
            $controller->register_routes();
            $controller = new TP_REST_Taxonomies_Controller;
            $controller->register_routes();
            foreach ( $this->_get_taxonomies( array( 'show_in_rest' => true ), 'object' ) as $taxonomy ) {
                if($taxonomy instanceof TP_Taxonomy ){$controller = $taxonomy->get_rest_controller();}
                if ( ! $controller ) continue;
                if( $controller instanceof TP_REST_Controller ){$controller->register_routes();}
            }
            $controller = new TP_REST_Users_Controller();
            $controller->register_routes();
            $controller = new TP_REST_Application_Passwords_Controller();
            $controller->register_routes();
            $controller = new TP_REST_Comments_Controller();
            $controller->register_routes();
            $search_handlers = [
                new TP_REST_Post_Search_Handler(),
                new TP_REST_Term_Search_Handler(),
                new TP_REST_Post_Format_Search_Handler(),
            ];
            $search_handlers = $this->_apply_filters( 'tp_rest_search_handlers', $search_handlers );
            $controller = new TP_REST_Search_Controller( $search_handlers );
            $controller->register_routes();
            $controller = new TP_REST_Block_Renderer_Controller();
            $controller->register_routes();
            $controller = new TP_REST_Block_Types_Controller();
            $controller->register_routes();
            $controller = new TP_REST_Global_Styles_Controller;
            $controller->register_routes();
            $controller = new TP_REST_Settings_Controller;
            $controller->register_routes();
            $controller = new TP_REST_Themes_Controller;
            $controller->register_routes();
            //$controller = new TP_REST_Block_Directory_Controller(); todo is for later
            //$controller->register_routes();
            $controller = new TP_REST_Pattern_Directory_Controller();
            $controller->register_routes();
            $site_health = Adm_Site_Health::get_instance();
            $controller  = new TP_REST_Site_Health_Controller( $site_health );
            $controller->register_routes();
            $controller = new TP_REST_URL_Details_Controller();
            $controller->register_routes();
            $controller = new TP_REST_Menu_Locations_Controller();
            $controller->register_routes();
            $controller = new TP_REST_Edit_Site_Export_Controller();
            $controller->register_routes();
            //$controller = new TP_REST_Sidebars_Controller(); todo is for later
            //$controller->register_routes();
        }//220
        /**
         * @description Loads the REST API.
         * @return bool
         */
        protected function _rest_api_loaded():bool{
            if ( empty( $this->_tp_core->query_vars['rest_route'] ) )
                return false;
            define( 'REST_REQUEST', true );
            $_server = $this->_rest_get_server();
            $server = null;
            if($_server instanceof TP_REST_Server ){ $server = $_server;}
            $route = $this->_untrailingslashit( $this->_tp_core->query_vars['rest_route'] );
            if ( empty( $route ) ) $route = '/';
            $server->serve_request( $route );
            die();
        }//365
        /**
         * @description Retrieves the URL prefix for any API resource.
         * @return mixed
         */
        protected function _rest_get_url_prefix(){
            return $this->_apply_filters( 'rest_url_prefix', 'tp-json' );
        }//399
        /**
         * @description Retrieves the URL to a REST endpoint on a site.
         * @param null $blog_id
         * @param string $path
         * @param string $scheme
         * @return mixed
         */
        protected function _get_rest_url( $blog_id = null, $path = '/', $scheme = 'rest' ){
            if ( empty( $path ) ) $path = '/';
            $path = '/' . ltrim( $path, '/' );
            if ($this->_get_option( 'permalink_structure' ) || ($this->_is_multisite() && $this->_get_blog_option($blog_id, 'permalink_structure'))) {
                $tp_rewrite = $this->_init_rewrite();
                if ( $tp_rewrite->using_index_permalinks() )
                    $url = $this->_get_home_url( $blog_id, $tp_rewrite->index . '/' . $this->_rest_get_url_prefix(), $scheme );
                else $url = $this->_get_home_url( $blog_id, $this->_rest_get_url_prefix(), $scheme );
                $url .= $path;
            } else {
                $url = $this->_trailingslashit( $this->_get_home_url( $blog_id, '', $scheme ) );
                if ( 'index.php' !== substr( $url, 9 ) ) $url .= 'index.php';
                $url = $this->_add_query_arg( 'rest_route', $path, $url );
            }
            if (isset($_SERVER['SERVER_NAME']) && $this->_is_ssl() && parse_url($this->_get_home_url($blog_id), PHP_URL_HOST) === $_SERVER['SERVER_NAME']) $url = $this->_set_url_scheme( $url, 'https' );
            if ( $this->_is_admin() && $this->_force_ssl_admin() )
                $url = $this->_set_url_scheme( $url, 'https' );
            return $this->_apply_filters( 'rest_url', $url, $path, $blog_id, $scheme );
        }//425
        /**
         * @description Retrieves the URL to a REST endpoint.
         * @param string $path
         * @param string $scheme
         * @return mixed
         */
        protected function _rest_url( $path = '', $scheme = 'rest' ){
            return $this->_get_rest_url( null, $path, $scheme );
        }//495
    }
}else die;