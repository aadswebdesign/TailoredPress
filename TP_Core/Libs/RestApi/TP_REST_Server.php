<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-5-2022
 * Time: 16:18
 */
namespace TP_Core\Libs\RestApi;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\Inits\_init_user;
use TP_Core\Traits\Pluggables\_pluggable_02;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_09;
use TP_Core\Traits\Methods\_methods_11;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\RestApi\_rest_api_01;
use TP_Core\Traits\RestApi\_rest_api_02;
use TP_Core\Traits\RestApi\_rest_api_03;
use TP_Core\Traits\RestApi\_rest_api_04;
use TP_Core\Traits\RestApi\_rest_api_06;
use TP_Core\Traits\RestApi\_rest_api_07;
use TP_Core\Traits\RestApi\_rest_api_08;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Theme\_theme_01;
use TP_Core\Traits\Theme\_theme_03;
use TP_Core\Traits\Formats\_formats_09;
use TP_Core\Traits\HTTP\_http_03;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\I10n\_I10n_01;
if(ABSPATH){
    class TP_REST_Server{
        use _filter_01;
        use _option_01;
        use _methods_04, _methods_09, _methods_10, _methods_11;
        use _I10n_01;
        use _http_03;
        use _formats_04, _formats_08, _formats_09, _formats_11;
        use _rest_api_01, _rest_api_02, _rest_api_03, _rest_api_04;
        use _rest_api_06, _rest_api_07, _rest_api_08;
        use _theme_01, _theme_03;
        use _capability_01;
        use _pluggable_02;
        use _link_template_09;
        use _post_03;
        use _init_error,_init_user;
        protected $_namespaces = [];
        protected $_endpoints = [];
        protected $_route_options = [];
        protected $_embed_cache = [];
        protected static $_HTTP_RAW_POST_DATA;
        public function __construct() {
            $this->_endpoints = [
                '/' =>[
                    'callback' => [$this, 'get_index'],
                    'methods'  => 'GET',
                    'args'     => ['context' => ['default' => 'view',],],
                ],
                '/batch/v1' => [
                    'callback' => [$this, 'serve_batch_request_v1'],
                    'methods'  => 'POST',
                    'args'     => [
                        'validation' => [
                            'type'    => 'string',
                            'enum'    => ['require-all-validate', 'normal'],
                            'default' => 'normal',
                        ],
                        'requests'   => [
                            'type'       => 'object',
                            'properties' => [
                                'method'  => [
                                    'type'    => 'string',
                                    'enum'    => ['POST', 'PUT', 'PATCH', 'DELETE'],
                                    'default' => 'POST',
                                ],
                                'path'    => [
                                    'type'     => 'string',
                                    'required' => true,
                                ],
                                'body'    => [
                                    'type'                 => 'object',
                                    'properties'           => [],
                                    'additionalProperties' => true,
                                ],
                                'headers' => [
                                    'type'                 => 'object',
                                    'properties'           => [],
                                    'additionalProperties' => [
                                        'type'  => [ 'string', 'array' ],
                                        'items' => ['type' => 'string',],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }//99
        public function check_authentication(){
            return $this->_apply_filters( 'rest_authentication_errors', null );
        }//163
        protected function _error_to_response($error ): TP_REST_Response{
            return $this->_rest_convert_error_to_response( $error );
        }//204
        protected function _json_error( $code, $message, $status = null ){
            if ( $status ) $this->_set_status( $status );
            $error = compact( 'code', 'message' );
            return $this->_tp_json_encode( $error );
        }//223
        public function serve_request( $path = null ){
            $tp_user = $this->_init_user();
            if (! $tp_user instanceof TP_User && ! $tp_user->exists())//todo
                $current_user = null;
            $jsonp_enabled = $this->_apply_filters( 'rest_jsonp_enabled', true );
            $jsonp_callback = false;
            if ( isset( $_GET['_jsonp'] ) ) $jsonp_callback = $_GET['_jsonp'];
            $content_type = ( $jsonp_callback && $jsonp_enabled ) ? 'application/javascript' : 'application/json';
            $this->send_header( 'Content-Type', $content_type . '; charset=' . $this->_get_option( 'blog_charset' ) );
            $this->send_header( 'X-Robots-Tag', 'noindex' );
            $api_root = $this->_get_rest_url();
            if ( ! empty( $api_root ) ) $this->send_header( 'Link', '<' . $this->_esc_url_raw( $api_root ) . '>; rel="https://api.w.org/"' );
            $this->send_header( 'X-Content-Type-Options', 'nosniff' );
            $expose_headers = array( 'X-TP-Total', 'X-TP-TotalPages', 'Link' );
            $expose_headers = $this->_apply_filters( 'rest_exposed_cors_headers', $expose_headers );
            $this->send_header( 'Access-Control-Expose-Headers', implode( ', ', $expose_headers ) );
            $allow_headers = ['Authorization','X-TP-Nonce', 'Content-Disposition','Content-MD5','Content-Type',];
            $allow_headers = $this->_apply_filters( 'rest_allowed_cors_headers', $allow_headers );
            $this->send_header( 'Access-Control-Allow-Headers', implode( ', ', $allow_headers ) );
            $send_no_cache_headers = $this->_apply_filters( 'rest_send_nocache_headers', $this->_is_user_logged_in() );
            if ( $send_no_cache_headers ) {
                foreach ( $this->_tp_get_nocache_headers() as $header => $header_value ) {
                    if ( empty( $header_value ) ) $this->remove_header( $header );
                    else $this->send_header( $header, $header_value );
                }
            }
            if ( $jsonp_callback ) {
                if ( ! $jsonp_enabled ) {
                    echo $this->_json_error( 'rest_callback_disabled', $this->__( 'JSONP support is disabled on this site.' ), 400 );
                    return false;
                }
                if ( ! $this->_tp_jsonp_check_callback( $jsonp_callback ) ) {
                    echo $this->_json_error( 'rest_callback_invalid', $this->__( 'Invalid JSONP callback function.' ), 400 );
                    return false;
                }
            }
            if ( empty( $path ) ) {
                if ( isset( $_SERVER['PATH_INFO'])) $path = $_SERVER['PATH_INFO'];
                else $path = '/';
            }
            $request = new TP_REST_Request( $_SERVER['REQUEST_METHOD'], $path );
            $request->set_query_params( $this->_tp_unslash( $_GET ) );
            $request->set_body_params( $this->_tp_unslash( $_POST ) );
            $request->set_file_params( $_FILES );
            $request->set_headers( $this->get_headers( $this->_tp_unslash( $_SERVER ) ) );
            $request->set_body( self::get_raw_data() );
            if ( isset( $_GET['_method'] ) )
                $request->set_method( $_GET['_method'] );
            elseif ( isset( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) )
                $request->set_method( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] );
            $result = $this->check_authentication();
            if ( ! $this->_init_error( $result ) ) $result = $this->dispatch( $request );
            $result = $this->_rest_ensure_response( $result );
            if($result instanceof TP_REST_Response){}
            if ( $this->_init_error( $result ) )
                $result = $this->_error_to_response( $result );
            $result = $this->_apply_filters( 'rest_post_dispatch', $this->_rest_ensure_response( $result ), $this, $request );
            if ( isset( $_GET['_envelope'] ) ) {
                $embed  = isset( $_GET['_embed'] ) ? $this->_rest_parse_embed_param( $_GET['_embed'] ) : false;
                $result = $this->envelope_response( $result, $embed );
            }
            $headers = $result->get_headers();
            $this->send_headers( $headers );
            $code = $result->get_status();
            $this->_set_status( $code );
            $served = $this->_apply_filters( 'rest_pre_serve_request', false, $result, $request, $this );
            if ( ! $served ) {
                if ( 'HEAD' === $request->get_method()) return null;
                $embed  = isset( $_GET['_embed'] ) ? $this->_rest_parse_embed_param( $_GET['_embed'] ) : false;
                $result = $this->response_to_data( $result, $embed );
                $result = $this->_apply_filters( 'rest_pre_echo_response', $result, $this, $request );
                if ( 204 === $code || null === $result ) return null;
                $result = $this->_tp_json_encode( $result );
                $json_error_message = $this->_get_json_last_error();
                if ( $json_error_message ) {
                    $this->_set_status( 500 );
                    $json_error_obj = new TP_Error('rest_encode_error',$json_error_message,['status' => 500]);
                    if($json_error_obj instanceof TP_REST_Response)
                    $result = $this->_error_to_response( $json_error_obj );
                    $result = $this->_tp_json_encode( $result->data );
                }
                if ( $jsonp_callback ) echo '/**/' . $jsonp_callback . '(' . $result . ')';
                else  echo $result;
            }
            return null;
        }//249
        public function response_to_data(TP_REST_Response $response, $embed ){
            $data  = $response->get_data();
            $links = self::get_compact_response_links( $response );
            if ( ! empty( $links ) ) $data['_links'] = $links;
            if ( $embed ) {
                $this->_embed_cache = array();
                if ( $this->_tp_is_numeric_array( $data ) ) {
                    foreach ( $data as $key => $item ) $data[ $key ] = $this->_embed_links( $item, $embed );
                } else $data = $this->_embed_links( $data, $embed );
                $this->_embed_cache = [];
            }
            return $data;
        }//539
        public static function get_response_links(TP_REST_Response  $response ): array{
            $links = $response->get_links();
            if ( empty( $links ) ) return [];
            $data = [];
            foreach ( $links as $rel => $items ) {
                $data[ $rel ] = [];
                foreach ( $items as $item ) {
                    $attributes         = $item['attributes'];
                    $attributes['href'] = $item['href'];
                    $data[ $rel ][]     = $attributes;
                }
            }
            return $data;
        }//575
        public static function get_compact_response_links(TP_REST_Response $response ): array
        {
            $links = self::get_response_links( $response );
            if ( empty( $links ) ) return [];
            $curies      = $response->get_curies();
            $used_curies = [];
            foreach ($links as $rel => $items ) {
                foreach ( $curies as $curie ) {
                    $href_prefix = substr( $curie['href'], 0, strpos( $curie['href'], '{rel}' ) );
                    if ( strpos( $rel, $href_prefix ) !== 0 ) continue;
                    $rel_regex = str_replace( '\{rel\}', '(.+)', preg_quote( $curie['href'], '!' ) );
                    preg_match( '!' . $rel_regex . '!', $rel, $matches );
                    if ( $matches ) {
                        $new_rel                       = $curie['name'] . ':' . $matches[1];
                        $used_curies[ $curie['name'] ] = $curie;
                        $links[ $new_rel ]             = $items;
                        unset( $links[ $rel ] );
                        break;
                    }
                }
            }
            if ( $used_curies ) $links['curies'] = array_values( $used_curies );
            return $links;
        }//608
        protected function _embed_links( $data, $embed = true ){
            if ( empty( $data['_links'] ) )return $data;
            $embedded = [];
            foreach ( $data['_links'] as $rel => $links ) {
                if (! in_array( $rel, (array)$embed, true ) ) continue;//todo
                $embeds = array();
                foreach ( $links as $item ) {
                    if ( empty( $item['embeddable'] ) ) {
                        $embeds[] = [];
                        continue;
                    }
                    if ( ! array_key_exists( $item['href'], $this->_embed_cache ) ) {
                        $request = TP_REST_Request::from_url( $item['href'] );
                        if ( ! $request ) {
                            $embeds[] = [];
                            continue;
                        }
                        if ( empty( $request['context'] ) )
                            $request['context'] = 'embed';
                        $response = $this->dispatch( $request );
                        $response = $this->_apply_filters( 'rest_post_dispatch', $this->_rest_ensure_response( $response ), $this, $request );
                        $this->_embed_cache[ $item['href'] ] = $this->response_to_data( $response, false );
                    }
                $embeds[] = $this->_embed_cache[ $item['href'] ];
                }
                $has_links = count( array_filter( $embeds ) );
                if ( $has_links )  $embedded[ $rel ] = $embeds;
            }
            if ( ! empty( $embedded ) ) $data['_embedded'] = $embedded;
            return $data;
        }//663
        public function envelope_response(TP_REST_Response $response, $embed ): TP_REST_Response{
            $envelope = [
                'body'    => $this->response_to_data( $response, $embed ),
                'status'  => $response->get_status(),
                'headers' => $response->get_headers(),
            ];
            $envelope = $this->_apply_filters( 'rest_envelope_response', $envelope, $response );
            return $this->_rest_ensure_response( $envelope );
        }//740
        public function register_route( $namespace, $route, $route_args, $override = false ): void{
            if ( ! isset( $this->_namespaces[ $namespace ] ) ) {
                $this->_namespaces[ $namespace ] = [];
                $this->register_route(
                    $namespace,
                    '/' . $namespace,
                    [[
                            'methods'  => TP_GET,
                            'callback' => [$this, 'get_namespace_index'],
                            'args'     => ['namespace' => ['default' => $namespace,],'context' => ['default' => 'view',],],
                    ],]
                );
            }
            $this->_namespaces[ $namespace ][ $route ] = true;
            $route_args['namespace']                  = $namespace;
            if ( $override || empty( $this->_endpoints[ $route ] ) )
                $this->_endpoints[ $route ] = $route_args;
            else $this->_endpoints[ $route ] = array_merge( $this->_endpoints[ $route ], $route_args );

        }//778
        public function get_routes( $namespace = '' ){
            $endpoints = $this->_endpoints;
            if ( $namespace ) $endpoints = $this->_tp_list_filter( $endpoints, array( 'namespace' => $namespace ) );
            $endpoints = $this->_apply_filters( 'rest_endpoints', $endpoints );
            $defaults = ['methods'=> '','accept_json'=> false,'accept_raw'=> false,
                'show_in_index' => true, 'args' => [],];
            foreach ( $endpoints as $route => &$handlers ) {
                if ( isset( $handlers['callback'] ) ) $handlers = array( $handlers );
                if ( ! isset( $this->_route_options[ $route ] ) )
                    $this->_route_options[ $route ] = array();
                foreach ( $handlers as $key => &$handler ) {
                    if ( ! is_numeric( $key ) ) {
                        $this->_route_options[ $route ][ $key ] = $handler;
                        unset( $handlers[ $key ] );
                        continue;
                    }
                    $handler = $this->_tp_parse_args( $handler, $defaults );
                    if ( is_string( $handler['methods'] ) )
                        $methods = explode( ',', $handler['methods'] );
                    elseif ( is_array( $handler['methods'] ) )
                        $methods = $handler['methods'];
                    else $methods = [];
                    $handler['methods'] = [];
                    foreach ( $methods as $method ) {
                        $method                        = strtoupper( trim( $method ) );
                        $handler['methods'][ $method ] = true;
                    }
                }
            }
            return $endpoints;
        }//835
        public function get_namespaces(): array{
            return array_keys( $this->_namespaces );
        }//913
        public function get_route_options( $route ){
            if ( ! isset( $this->_route_options[ $route ] ) )
                return null;
            return $this->_route_options[ $route ];
        }//925
        public function dispatch(TP_REST_Request $request ){
            $result = $this->_apply_filters( 'rest_pre_dispatch', null, $this, $request );
            if ( ! empty( $result ) ) return $result;
            $error   = null;
            $matched = $this->_match_request_to_handler( $request );
            if ( $this->_init_error( $matched ) )
                return $this->_error_to_response( $matched );
            @list( $route, $handler ) = $matched;
            if ( ! is_callable( $handler['callback'] ) )
                $error = new TP_Error('rest_invalid_handler', $this->__( 'The handler for the route is invalid.' ), array( 'status' => 500 ));
            if ( ! $this->_init_error( $error ) ) {
                $check_required = $request->has_valid_params();
                if ( $this->_init_error( $check_required ) )
                    $error = $check_required;
                else {
                    $check_sanitized = $request->sanitize_params();
                    if ( $this->_init_error( $check_sanitized ) )
                        $error = $check_sanitized;
                }
            }
            return $this->_respond_to_request( $request, $route, $handler, $error );
        }//941
        protected function _match_request_to_handler(TP_REST_Request $request ){
            $method = $request->get_method();
            $path   = $request->get_route();
            $with_namespace = array();
            foreach ( $this->get_namespaces() as $namespace ) {
                if ( 0 === strpos( $this->_trailingslashit( ltrim( $path, '/' ) ), $namespace ) )
                    $with_namespace[] = $this->get_routes( $namespace );
            }
            if ( $with_namespace ) $routes = array_merge( ...$with_namespace );
            else $routes = $this->get_routes();
            foreach ( $routes as $route => $handlers ) {
                $match = preg_match( '@^' . $route . '$@i', $path, $matches );
                if ( ! $match ) continue;
                $args = [];
                foreach ( $matches as $param => $value ) {
                    if ( ! is_int( $param ) ) $args[ $param ] = $value;
                }
                foreach ( $handlers as $handler ) {
                    $callback = $handler['callback'];
                    $response = null;
                    $checked_method = $method;
                    if ( 'HEAD' === $method && empty( $handler['methods']['HEAD'] ) )
                        $checked_method = 'GET';
                    if ( empty( $handler['methods'][ $checked_method ] ) )
                        continue;
                    if ( ! is_callable( $callback ) ) return array( $route, $handler );
                    $request->set_url_params( $args );
                    $request->set_attributes( $handler );
                    $defaults = array();
                    foreach ( $handler['args'] as $arg => $options ) {
                        if ( isset( $options['default'] ) )
                            $defaults[ $arg ] = $options['default'];
                    }
                    $request->set_default_params( $defaults );
                    return array( $route, $handler );
                }
            }
            return new TP_Error(
                'rest_no_route',
                $this->__( 'No route was found matching the URL and request method.' ),
                array( 'status' => 404 )
            );
        }//1002
        protected function _respond_to_request( $request, $route, $handler, $response ){
            $response = $this->_apply_filters( 'rest_request_before_callbacks', $response, $handler, $request );
            if ( ! empty( $handler['permission_callback'] ) && ! $this->_init_error( $response )) {
                $permission = call_user_func( $handler['permission_callback'], $request );
                if ( $this->_init_error( $permission ) ) {
                    $response = $permission;
                } elseif ( false === $permission || null === $permission ) {
                    $response = new TP_Error(
                        'rest_forbidden',
                        $this->__( 'Sorry, you are not allowed to do that.' ),
                        array( 'status' => $this->_rest_authorization_required_code() )
                    );
                }
            }
            if ( ! $this->_init_error( $response ) ) {
                $dispatch_result = $this->_apply_filters( 'rest_dispatch_request', null, $request, $route, $handler );
                if ( null !== $dispatch_result ) $response = $dispatch_result;
                else $response = call_user_func( $handler['callback'], $request );
            }
            $response = $this->_apply_filters( 'rest_request_after_callbacks', $response, $handler, $request );
            if ( $this->_init_error( $response ) )
                $response = $this->_error_to_response( $response );
            else $response = $this->_rest_ensure_response( $response );
            if($response instanceof TP_REST_Response){}//todo
            $response->set_matched_route( $route );
            $response->set_matched_handler( $handler );
            return $response;
        }//1088
        protected function _get_json_last_error(){
            $last_error_code = json_last_error();
            if ( JSON_ERROR_NONE === $last_error_code || empty( $last_error_code ) )
                return false;
            return json_last_error_msg();
        }//1192
        public function get_index( $request ){
            $available = array(
                'name'            => $this->_get_option( 'blogname' ),
                'description'     => $this->_get_option( 'blogdescription' ),
                'url'             => $this->_get_option( 'siteurl' ),
                'home'            => $this->_home_url(),
                'gmt_offset'      => $this->_get_option( 'gmt_offset' ),
                'timezone_string' => $this->_get_option( 'timezone_string' ),
                'namespaces'      => array_keys( $this->_namespaces ),
                'authentication'  => array(),
                'routes'          => $this->get_data_for_routes( $this->get_routes(), $request['context'] ),
            );
            $response = new TP_REST_Response( $available );
            $response->add_link( 'help', 'https://developer.wordpress.org/rest-api/' );
            $this->_add_active_theme_link_to_index( $response );
            $this->_add_site_logo_to_index( $response );
            $this->_add_site_icon_to_index( $response );
            return $this->_apply_filters( 'rest_index', $response, $request );
        }//1216
        protected function _add_active_theme_link_to_index( TP_REST_Response $response ): void{
            $should_add = $this->_current_user_can( 'switch_themes' ) || $this->_current_user_can( 'manage_network_themes' );
            if ( ! $should_add && $this->_current_user_can( 'edit_posts' ) )
                $should_add = true;
            if ( ! $should_add ) {
                foreach ( $this->_get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
                    if ( $this->_current_user_can( $post_type->cap->edit_posts ) ) {
                        $should_add = true;
                        break;
                    }
                }
            }
            if ( $should_add ) {
                $theme = $this->_tp_get_theme();
                $response->add_link( 'https://api.w.org/active-theme', $this->_rest_url( 'tp/v1/themes/' . $theme->get_stylesheet() ) );
            }
        }//1259
        protected function _add_site_logo_to_index( TP_REST_Response $response ): void {
            $site_logo_id = $this->_get_theme_mod( 'custom_logo', 0 );
            $this->_add_image_to_index( $response, $site_logo_id, 'site_logo' );
        }//1291
        protected function _add_site_icon_to_index( TP_REST_Response $response ): void{
            $site_icon_id = $this->_get_option( 'site_icon', 0 );
            $this->_add_image_to_index( $response, $site_icon_id, 'site_icon' );
        }//1307
        protected function _add_image_to_index( TP_REST_Response $response, $image_id, $type ): void{
            $response->data[ $type ] = (int) $image_id;
            if ( $image_id ) {
                $response->add_link(
                    'https://api.w.org/featuredmedia',
                    $this->_rest_url( $this->_rest_get_route_for_post( $image_id ) ),
                    ['embeddable' => true,'type' => $type,]
                );
            }
        }//1324
        public function get_namespace_index( $request ){
            $namespace = $request['namespace'];
            if ( ! isset( $this->_namespaces[ $namespace ] ) ) {
                return new TP_Error(
                    'rest_invalid_namespace',
                    $this->__( 'The specified namespace could not be found.' ),
                    array( 'status' => 404 )
                );
            }
            $routes    = $this->_namespaces[ $namespace ];
            $endpoints = array_intersect_key( $this->get_routes(), $routes );
            $data     = [
                'namespace' => $namespace,
                'routes'    => $this->get_data_for_routes( $endpoints, $request['context'] ),
            ];
            $response = $this->_rest_ensure_response( $data );
            if($response instanceof TP_REST_Response)
            $response->add_link( 'up', $this->_rest_url( '/' ) );
            return $this->_apply_filters( 'rest_namespace_index', $response, $request );
        }//1347
        public function get_data_for_routes( $routes, $context = 'view' ){
            $available = [];
            foreach ( $routes as $route => $callbacks ) {
                $data = $this->get_data_for_route( $route, $callbacks, $context );
                if ( empty( $data ) ) continue;
                $available[ $route ] = $this->_apply_filters( 'rest_endpoints_description', $data );
            }
            return $this->_apply_filters( 'rest_route_data', $available, $routes );
        } //1393
        public function get_data_for_route( $route, $callbacks, $context = 'view' ): ?array{
            $data = ['namespace' => '','methods'   => [],'endpoints' => [],];
            $allow_batch = false;
            if ( isset( $this->_route_options[ $route ] ) ) {
                $options = $this->_route_options[ $route ];
                if ( isset( $options['namespace'] ) ) $data['namespace'] = $options['namespace'];
                $allow_batch = $options['allow_batch'] ?? false;
                if ( isset( $options['schema'] ) && 'help' === $context )
                    $data['schema'] = call_user_func( $options['schema'] );
            }
            $allowed_schema_keywords = array_flip( $this->_rest_get_allowed_schema_keywords() );
            $route = preg_replace( '#\(\?P<(\w+?)>.*?\)#', '{$1}', $route );
            foreach ( $callbacks as $callback ) {
                if ( empty( $callback['show_in_index'] ) )continue;
                $data['methods'] = array_merge( $data['methods'], array_keys( $callback['methods'] ) );
                $endpoint_data   = ['methods' => array_keys( $callback['methods'] ),];
                $callback_batch = $callback['allow_batch'] ?? $allow_batch;
                if ( $callback_batch ) $endpoint_data['allow_batch'] = $callback_batch;
                if ( isset( $callback['args'] ) ) {
                    $endpoint_data['args'] = array();
                    foreach ( $callback['args'] as $key => $opts ) {
                        $arg_data             = array_intersect_key( $opts, $allowed_schema_keywords );
                        $arg_data['required'] = ! empty( $opts['required'] );
                        $endpoint_data['args'][ $key ] = $arg_data;
                    }
                }
                $data['endpoints'][] = $endpoint_data;
                if ( strpos( $route, '{' ) === false )
                    $data['_links'] = ['self' => [['href' => $this->_rest_url( $route ),],],];
            }
            if ( empty( $data['methods'] ) ) return null;
            return $data;
        }//1438
        protected function _get_max_batch_size(){
            return $this->_apply_filters( 'rest_get_max_batch_size', 25 );
        }//1522
        public function serve_batch_request_v1( TP_REST_Request $batch_request ): TP_REST_Response{
            $requests = [];
            $args = [];
            static $parsed_url;
            foreach ( $batch_request['requests'] as $_args ) {
                $parsed_url = $this->_tp_parse_url( $_args['path'] );
                if ( false === $parsed_url ) {
                    $requests[] = new TP_Error( 'parse_path_failed', $this->__( 'Could not parse the path.' ), array( 'status' => 400 ) );
                    continue;
                }
                $args[] = $_args;
            }
            $single_request = new TP_REST_Request( $args['method'] ?? 'POST', $parsed_url['path'] );
            if ( ! empty( $parsed_url['query'] ) ) {
                $query_args = null;
                $this->_tp_parse_str( $parsed_url['query'], $query_args );
                $single_request->set_query_params( $query_args );
            }
            if ( ! empty( $args['body'] ) )
                $single_request->set_body_params( $args['body'] );
            if ( ! empty( $args['headers'] ) )
                $single_request->set_headers( $args['headers'] );
            $requests[] = $single_request;
            $matches    = [];
            $validation = [];
            $has_error  = false;
            foreach ( $requests as $single_request ){
                $match     = $this->_match_request_to_handler( $single_request );
                $matches[] = $match;
                $error     = null;
                if ( $this->_init_error( $match ) ) $error = $match;
                if ( ! $error ) {
                    @list( $route, $handler ) = $match;
                    if ( isset( $handler['allow_batch'] ) )
                        $allow_batch = $handler['allow_batch'];
                    else {
                        $route_options = $this->get_route_options( $route );
                        $allow_batch   = $route_options['allow_batch'] ?? false;
                    }
                    if ( ! is_array( $allow_batch ) || empty( $allow_batch['v1'] ) ) {
                        $error = new TP_Error(
                            'rest_batch_not_allowed',
                            $this->__( 'The requested route does not support batch requests.' ),
                            array( 'status' => 400 )
                        );
                    }
                }
                if ( ! $error ) {
                    $check_required = $single_request->has_valid_params();
                    if ( $this->_init_error( $check_required ) ) $error = $check_required;
                }
                if ( ! $error ) {
                    $check_sanitized = $single_request->sanitize_params();
                    if ( $this->_init_error( $check_sanitized)) $error = $check_sanitized;
                }
                if ( $error ) {
                    $has_error    = true;
                    $validation[] = $error;
                } else $validation[] = true;
            }
            $responses = [];
            if ( $has_error && 'require-all-validate' === $batch_request['validation'] ) {
                foreach ( $validation as $valid ) {
                    if ( $this->_init_error( $valid ) )
                        /** @noinspection PhpUndefinedMethodInspection *///todo
                        $responses[] = $this->envelope_response( $this->_error_to_response($valid ), false )->get_data();
                    else $responses[] = null;
                }
                return new TP_REST_Response(
                    ['failed' => 'validation','responses' => $responses,],
                    MULTI_STATUS
                );
            }
            foreach ( $requests as $i => $single_request ) {
                $clean_request = clone $single_request;
                $clean_request->set_url_params( array() );
                $clean_request->set_attributes( array() );
                $clean_request->set_default_params( array() );
                $result = $this->_apply_filters( 'rest_pre_dispatch', null, $this, $clean_request );
                if ( empty( $result ) ) {
                    $match = $matches[ $i ];
                    $error = null;
                    if ( $this->_init_error( $validation[ $i ] ) )
                        $error = $validation[ $i ];
                    if ( $this->_init_error( $match ) )
                        $result = $this->_error_to_response( $match );
                    else {
                        @list( $route, $handler ) = $match;
                        if ( ! $error && ! is_callable( $handler['callback'] ) ) {
                            $error = new TP_Error(
                                'rest_invalid_handler',
                                $this->__( 'The handler for the route is invalid' ),
                                array( 'status' => 500 )
                            );
                        }
                        $result = $this->_respond_to_request( $single_request, $route, $handler, $error );
                    }
                }
                $result = $this->_apply_filters( 'rest_post_dispatch', $this->_rest_ensure_response( $result ), $this, $single_request );
                /** @noinspection PhpUndefinedMethodInspection *///todo
                $responses[] = $this->envelope_response( $result, false )->get_data();
            }
            return new TP_REST_Response( array( 'responses' => $responses ), MULTI_STATUS );
        }//1541
        protected function _set_status( $code ): void{
            $this->_status_header( $code );
        }//1696
        public function send_header( $key, $value ): void{
            $value = preg_replace( '/\s+/', ' ', $value );
            header( sprintf( '%s: %s', $key, $value ) );
        }//1708
        public function send_headers( $headers ): void{
            foreach ( $headers as $key => $value ) $this->send_header( $key, $value );
        }//1727
        public function remove_header( $key ): void{
            header_remove( $key );
        }//1740
        public static function get_raw_data(): string{
            self::$_HTTP_RAW_POST_DATA;
            if ( ! isset( self::$_HTTP_RAW_POST_DATA ) )
                self::$_HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
            return self::$_HTTP_RAW_POST_DATA;
        }//1753
        public function get_headers(array $server ): array {
            $headers = [];
            $additional = ['CONTENT_LENGTH' => true,'CONTENT_MD5' => true,'CONTENT_TYPE' => true,];
            foreach ( $server as $key => $value ) {
                if ( strpos( $key, 'HTTP_' ) === 0 ) $headers[ substr( $key, 5 ) ] = $value;
                elseif ( 'REDIRECT_HTTP_AUTHORIZATION' === $key && empty( $server['HTTP_AUTHORIZATION'] ) ) {
                    $headers['AUTHORIZATION'] = $value;
                } elseif (isset($additional[ $key ])) $headers[ $key ] = $value;
            }
            return $headers;
        }//1774
    }
}else die;