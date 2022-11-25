<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-5-2022
 * Time: 09:29
 */
namespace TP_Core\Libs\RestApi;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\Formats\_formats_09;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Load\_load_05;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\RestApi\_rest_api_01;
use TP_Core\Traits\RestApi\_rest_api_08;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\HTTP\TP_HTTP_Response;
if(ABSPATH){
    class TP_REST_Request implements \ArrayAccess{
        use _filter_01;
        use _I10n_01;
        use _init_error;
        use _rest_api_01;
        use _rest_api_08;
        use _formats_04;
        use _formats_09;
        use _option_01;
        use _load_05;
        protected $_params;
        protected $_headers = [];
        protected $_body;
        protected $_route;
        protected $_attributes = [];
        protected $_parsed_json = false;
        protected $_parsed_body = false;
        protected $_method;
        public function __construct( $method = '', $route = '', $attributes = array() ){}//112
        public function get_method(){
            return $this->_method;
        }//137
        public function set_method( $method ): void{
            $this->_method = strtoupper( $method );
        }//148
        public function get_headers(): array{
            return $this->_headers;
        }//159
        public static function canonicalize_header_name(string $key ) {
            $key = strtolower( $key );
            $key = (string) str_replace( '-', '_', $key );
            return $key;
        }//181
        public function get_header(string $key ) {
            $key = self::canonicalize_header_name( $key );
            if ( ! isset( $this->_headers[ $key ] ) )
                return null;
            return implode( ',', $this->_headers[ $key ] );
        }//200
        public function get_header_as_array(string $key ) {
            $key = self::canonicalize_header_name( $key );
            if ( ! isset( $this->_headers[$key])) return null;
            return $this->_headers[ $key ];
        }//218
        public function set_header(string $key, array $value ): void{
            $key   = self::canonicalize_header_name( $key );
            $this->_headers[ $key ] = $value;
        }//236
        public function add_header(string $key, array $value ): void{
            $key   = self::canonicalize_header_name( $key );
            if ( ! isset( $this->_headers[ $key ] ) )
                $this->_headers[ $key ] = [];
            $this->_headers[ $key ] = array_merge( $this->_headers[ $key ], $value );
        }//251
        public function remove_header(string $key ): void{
            $key = self::canonicalize_header_name( $key );
            unset( $this->_headers[ $key ] );
        }//269
        public function set_headers(array $headers, $override = true ): void{
            if ( true === $override ) $this->_headers = [];
            foreach ( $headers as $key => $value )
                $this->set_header( $key, $value );
        }//282
        public function get_content_type() {
            $value = $this->get_header( 'content-type' );
            if ( empty( $value )) return null;
            $parameters = '';
            if ( strpos( $value, ';' ) )
                @list( $value, $parameters ) = explode( ';', $value, 2 );
            $value = strtolower( $value );
            if ( false === strpos( $value,'/')) return null;
            @list( $type, $subtype ) = explode( '/', $value, 2 );
            $data = compact( 'value', 'type', 'subtype', 'parameters' );
            $data = array_map( 'trim', $data );
            return $data;
        }//301
        public function is_json_content_type(): bool{
            $content_type = $this->get_content_type();
            return isset( $content_type['value'] ) && $this->_tp_is_json_media_type( $content_type['value'] );
        }//333
        public function get_param(string $key ) {
            $order = $this->_get_parameter_order();
            foreach ( $order as $type ) {
                // Determine if we have the parameter for this type.
                if ( isset( $this->_params[ $type ][ $key ] ) )
                    return $this->_params[ $type ][ $key ];
            }
            return null;
        }//395
        public function has_param(string $key ): bool{
            $order = $this->_get_parameter_order();
            foreach ( $order as $type ) {
                if ( is_array( $this->_params[ $type ] ) && array_key_exists( $key, $this->_params[ $type ] ) )
                    return true;
            }
            return false;
        }//419
        public function set_param(string $key, $value): void{
            $order     = $this->_get_parameter_order();
            $found_key = false;
            foreach ( $order as $type ) {
                if ( 'defaults' !== $type && is_array( $this->_params[ $type ] ) && array_key_exists( $key, $this->_params[ $type ] ) ) {
                    $this->_params[ $type ][ $key ] = $value;
                    $found_key = true;
                }
            }
            if ( ! $found_key ) $this->_params[ $order[0] ][ $key ] = $value;
        }//443
        public function get_params(): array{
            $order = $this->_get_parameter_order();
            $order = array_reverse( $order, true );
            $params = array();
            foreach ( $order as $type ) {
                foreach ( (array) $this->_params[ $type ] as $key => $value )
                    $params[ $key ] = $value;
            }
            return $params;
        }//469
        public function get_url_params() {
            return $this->_params['URL'];
        }//494
        public function set_url_params( $params ): void{
            $this->_params['URL'] = $params;
        }//507
        public function get_query_params() {
            return $this->_params['GET'];
        }//520
        public function set_query_params( $params ): void{
            $this->_params['GET'] = $params;
        }//533
        public function get_body_params() {
            return $this->_params['POST'];
        }//546
        public function set_body_params( $params ): void{
            $this->_params['POST'] = $params;
        }//559
        public function get_file_params() {
            return $this->_params['FILES'];
        }//572
        public function set_file_params( $params ): void{
            $this->_params['FILES'] = $params;
        }//585
        public function get_default_params() {
            return $this->_params['defaults'];
        }//598
        public function set_default_params( $params ): void{
            $this->_params['defaults'] = $params;
        }//611
        public function get_body() {
            return $this->_body;
        }//622
        public function set_body( $data ): void{
            $this->_body = $data;
            $this->_parsed_json    = false;
            $this->_parsed_body    = false;
            $this->_params['JSON'] = null;
        }//633
        public function get_json_params() {
            $this->_parse_json_params();
            return $this->_params['JSON'];
        }//649
        public function get_route() {
            return $this->_route;
        }//746
        public function set_route( $route ): void{
            $this->_route = $route;
        }//757
        public function get_attributes(): array{
            return $this->_attributes;
        }//770
        public function set_attributes( $attributes ): void{
            $this->_attributes = $attributes;
        }//781
        public function sanitize_params() {
            $attributes = $this->get_attributes();
            if (empty( $attributes['args'])) return true;
            $order = $this->_get_parameter_order();
            $invalid_params  =[];
            $invalid_details = [];
            foreach ( $order as $type ) {
                if ( empty( $this->_params[ $type ] )) continue;
                foreach ( $this->_params[ $type ] as $key => $value ) {
                    if ( ! isset( $attributes['args'][ $key ])) continue;
                    $param_args = $attributes['args'][ $key ];
                    if ( ! array_key_exists( 'sanitize_callback', $param_args ) && ! empty( $param_args['type'] ) )
                        $param_args['sanitize_callback'] = 'rest_parse_request_arg';
                    if ( empty( $param_args['sanitize_callback'] ) ) continue;
                    $sanitized_value = call_user_func( $param_args['sanitize_callback'], $value, $this, $key );
                    if ( $this->_init_error( $sanitized_value ) ) {
                        $invalid_params[ $key ]  = implode( ' ', $sanitized_value->get_error_messages() );
                        $convert_error = $this->_rest_convert_error_to_response( $sanitized_value );
                        if($convert_error instanceof TP_HTTP_Response){
                            $invalid_details[ $key ] = $convert_error->get_data();
                        }
                    } else $this->_params[ $type ][ $key ] = $sanitized_value;
                }
            }
            if ( $invalid_params ) {
                return new TP_Error(
                    'rest_invalid_param',
                    /* translators: %s: List of invalid parameters. */
                    sprintf( $this->__( 'Invalid parameter(s): %s' ), implode( ', ', array_keys( $invalid_params ) ) ),
                    ['status'  => BAD_REQUEST,'params'  => $invalid_params,'details' => $invalid_details,]
                );
            }
            return true;
        }//795
        public function has_valid_params() {
            $json_error = $this->_parse_json_params();
            if ( $this->_init_error( $json_error ) ) return $json_error;
            $attributes = $this->get_attributes();
            $required   = [];
            $args = empty( $attributes['args'] ) ? [] : $attributes['args'];
            foreach ( $args as $key => $arg ) {
                $param = $this->get_param( $key );
                if ( isset( $arg['required'] ) && true === $arg['required'] && null === $param )
                    $required[] = $key;
            }
            if ( ! empty( $required ) ) {
                return new TP_Error(
                    'rest_missing_callback_param',
                    /* translators: %s: List of required parameters. */
                    sprintf( $this->__( 'Missing parameter(s): %s' ), implode( ', ', $required ) ),
                    ['status' => BAD_REQUEST,'params' => $required,]
                );
            }
            $invalid_params  = [];
            $invalid_details = [];
            foreach ( $args as $key => $arg ) {
                $param = $this->get_param( $key );
                if ( null !== $param && ! empty( $arg['validate_callback'] ) ) {
                    $valid_check = call_user_func( $arg['validate_callback'], $param, $this, $key );
                    if ( false === $valid_check )
                        $invalid_params[ $key ] = $this->__( 'Invalid parameter.' );
                   if ( $this->_init_error( $valid_check ) ) {
                        $invalid_params[ $key ]  = implode( ' ', $valid_check->get_error_messages() );
                        $convert_error = $this->_rest_convert_error_to_response( $valid_check );
                        if($convert_error instanceof TP_HTTP_Response){
                            $invalid_details[ $key ] = $convert_error->get_data();
                        }
                    }
                }
            }
            if ( $invalid_params ) {
                return new TP_Error(
                    'rest_invalid_param',
                    /* translators: %s: List of invalid parameters. */
                    sprintf( $this->__( 'Invalid parameter(s): %s' ), implode( ', ', array_keys( $invalid_params ) ) ),
                    ['status'  => BAD_REQUEST,'params'  => $invalid_params,'details' => $invalid_details,]
                );
            }
            if ( isset( $attributes['validate_callback'] ) ) {
                $valid_check = call_user_func( $attributes['validate_callback'], $this );
                if ( $this->_init_error( $valid_check ) ) return $valid_check;
                if ( false === $valid_check )
                    return new TP_Error( 'rest_invalid_params', $this->__( 'Invalid parameters.' ), array( 'status' => 400 ) );
            }
            return true;
        }//865
        public function offsetExists( $offset ):bool {
            $order = $this->_get_parameter_order();
            foreach ( $order as $type ) {
                if ( isset( $this->_params[ $type ][ $offset ] ) )
                    return true;
            }
            return false;
        }//961
        public function offsetGet( $offset ) {
            return $this->get_param( $offset );
        }//982
        public function offsetSet( $offset, $value ):string {
            $this->set_param( $offset, $value );
        }//995
        public function offsetUnset( $offset ):string {
            $order = $this->_get_parameter_order();
            foreach ( $order as $type )
                unset( $this->_params[ $type ][ $offset ] );
        }//1007
        public static function from_url( $url ) {
            $bits         = parse_url( $url );
            $query_params = array();
            if ( ! empty( $bits['query'] ) )
                (new static())->_tp_parse_str( $bits['query'], $query_params );
            $api_root = (new static())->_rest_url();
            if ((0 === strpos( $url, $api_root ))&& (new static())->_get_option( 'permalink_structure' )) {
                $api_url_part = substr( $url, strlen( (new static())->_untrailingslashit( $api_root ) ) );
                $route        = parse_url( $api_url_part, PHP_URL_PATH );
            } elseif ( ! empty( $query_params['rest_route'] ) ) {
                $route = $query_params['rest_route'];
                unset( $query_params['rest_route'] );
            }
            $request = false;
            if ( ! empty( $route ) ) {
                $request = new TP_REST_Request( 'GET', $route );
                $request->set_query_params( $query_params );
            }
            return (new static())->_apply_filters( 'rest_request_from_url', $request, $url );
        }
        protected function _get_parameter_order() {
            $order = [];
            if ( $this->is_json_content_type() ) $order[] = 'JSON';
            $body = $this->get_body();
            if ( 'POST' !== $this->_method && ! empty( $body ) ) $this->_parse_body_params();
            $accepts_body_data = array( 'POST', 'PUT', 'PATCH', 'DELETE' );
            if ( in_array( $this->_method, $accepts_body_data, true ) ) $order[] = 'POST';
            $order[] = 'GET';
            $order[] = 'URL';
            $order[] = 'defaults';
            return $this->_apply_filters( 'rest_request_parameter_order', $order, $this );
        }//348
        protected function _parse_json_params() {
            if ( $this->_parsed_json ) return true;
            $this->_parsed_json = true;
            if ( ! $this->is_json_content_type() ) return true;
            $body = $this->get_body();
            if ( empty( $body ) ) return true;
            $params = json_decode( $body, true );
            if ( null === $params && JSON_ERROR_NONE !== json_last_error() ) {
                $this->_parsed_json = false;
                $error_data = [
                    'status'             => BAD_REQUEST,
                    'json_error_code'    => json_last_error(),
                    'json_error_message' => json_last_error_msg(),
                ];
                return new TP_Error( 'rest_invalid_json', $this->__( 'Invalid JSON body passed.' ), $error_data );
            }
            $this->_params['JSON'] = $params;
            return true;
        }//665
        protected function _parse_body_params(): void{
            if ( $this->_parsed_body ) return;
            $this->_parsed_body = true;
            $content_type = $this->get_content_type();
            if ( ! empty( $content_type ) && 'application/x-www-form-urlencoded' !== $content_type['value'] )
                return;
            parse_str( $this->get_body(), $params );
            $this->_params['POST'] = array_merge( $params, $this->_params['POST'] );
        }//713
    }
}else die;