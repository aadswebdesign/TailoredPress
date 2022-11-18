<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 08:35
 */
namespace TP_Core\Traits\RestApi;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_rest;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Libs\HTTP\TP_HTTP_Response;
use TP_Core\Libs\RestApi\TP_REST_Server;
if(ABSPATH){
    trait _rest_api_02{
        use _init_rest;
        use _init_error;
        /**
         * @description Do a REST request.
         * @param $request
         * @return mixed
         */
        protected function _rest_do_request( $request ){
            $request = $this->_rest_ensure_request( $request );
            if($request instanceof TP_REST_Request)
            return $this->_rest_get_server()->dispatch( $request );
        }//509
        /**
         * @description Retrieves the current REST server instance.
         * @return \TP_Core\Libs\RestApi\TP_REST_Server
         */
        protected function _rest_get_server():TP_REST_Server{
            if ( empty( $this->tp_rest_server ) ){
                $tp_rest_server_class = $this->_apply_filters( 'tp_rest_server_class', TP_REST_Server::class);
                $this->tp_rest_server       = new $tp_rest_server_class;
                $this->_do_action( 'rest_api_init', $this->tp_rest_server );
            }
                return $this->tp_rest_server;
        }//525
        /**
         * @description Ensures request arguments are a request object (for consistency).
         * @param $request
         * @return \TP_Core\Libs\RestApi\TP_REST_Request
         */
        protected function _rest_ensure_request( $request ): TP_REST_Request{
            return $this->_init_rest_request($request);
        }//568
        /**
         * @description Ensures a REST response is a response object (for consistency).
         * @param $response
         * @return TP_REST_Response
         */
        protected function _rest_ensure_response( $response ):TP_REST_Response{
            if ( $this->_init_error( $response ) ) return $response;
            if ( $response instanceof TP_REST_Response ) return $response;
            if ( $response instanceof TP_HTTP_Response )
                return new TP_REST_Response($response->get_data(), $response->get_status(), $response->get_headers());
            return new TP_REST_Response( $response );
        }//594
        /**
         * @description Sends Cross-Origin Resource Sharing headers with API requests.
         * @param $value
         * @return mixed
         */
        protected function _rest_send_cors_headers( $value ){
            $origin = $this->_get_http_origin();
            if ( $origin ) {
                if ( 'null' !== $origin ) $origin = $this->_esc_url_raw( $origin );
                header( 'Access-Control-Allow-Origin: ' . $origin );
                header( 'Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, PATCH, DELETE' );
                header( 'Access-Control-Allow-Credentials: true' );
                header( 'Vary: Origin', false );
            } elseif ( 'GET' === $_SERVER['REQUEST_METHOD'] && ! headers_sent() && ! $this->_is_user_logged_in() )
                header( 'Vary: Origin', false );
            return $value;
        }//699
        /**
         * @description Handles OPTIONS requests for the server.
         * @param $response
         * @param TP_REST_Server $handler
         * @param TP_REST_Request $request
         * @return TP_REST_Response
         */
        protected function _rest_handle_options_request( $response,TP_REST_Server $handler,TP_REST_Request $request ):TP_REST_Response{
            if ( ! empty( $response ) || $request->get_method() !== 'OPTIONS' )
                return $response;
            $response = new TP_REST_Response();
            $data     = [];
            foreach ( $handler->get_routes() as $route => $endpoints ) {
                $match = preg_match( '@^' . $route . '$@i', $request->get_route(), $matches );
                if ( ! $match ) continue;
                $args = [];
                foreach ( $matches as $param => $value ) {
                    if ( ! is_int( $param ) ) $args[ $param ] = $value;
                }
                foreach ( $endpoints as $endpoint ) {
                    unset( $args[0] );
                    $request->set_url_params( $args );
                    $request->set_attributes( $endpoint );
                }
                $data = $handler->get_data_for_route( $route, $endpoints, 'help' );
                $response->set_matched_route( $route );
                break;
            }
            $response->set_data( $data );
            return $response;
        }//731
        /**
         * @description Sends the "Allow" header to state all methods that can be sent to the current route.
         * @param TP_REST_Response $response
         * @param TP_REST_Server $server
         * @param TP_REST_Request $request
         * @return TP_REST_Response
         */
        protected function _rest_send_allow_header(TP_REST_Response $response,TP_REST_Server $server,TP_REST_Request $request ):TP_REST_Response{
            $matched_route = $response->get_matched_route();
            if ( ! $matched_route ) return $response;
            $routes = $server->get_routes();
            $allowed_methods = [];
            foreach ( $routes[ $matched_route ] as $_handler ) {
                foreach ( $_handler['methods'] as $handler_method => $value ) {
                    if ( ! empty( $_handler['permission_callback'] ) ) {
                        $permission = call_user_func( $_handler['permission_callback'], $request );
                        $allowed_methods[ $handler_method ] = true === $permission;
                    } else $allowed_methods[ $handler_method ] = true;
                }
            }
            $allowed_methods = array_filter( $allowed_methods );
            if ( $allowed_methods )
                $response->header( 'Allow', implode( ', ', array_map( 'strtoupper', array_keys( $allowed_methods ) ) ) );
            return $response;
        }//780
        /**
         * @description Recursively computes the intersection of arrays using keys for comparison.
         * @param $array1
         * @param $array2
         * @return array
         */
        protected function _rest_array_intersect_key_recursive( $array1, $array2 ):array{
            $array1 = array_intersect_key( $array1, $array2 );
            foreach ( $array1 as $key => $value ) {
                if ( is_array( $value ) && is_array( $array2[ $key ] ) )
                    $array1[ $key ] = $this->_rest_array_intersect_key_recursive( $value, $array2[ $key ] );
            }
            return $array1;
        }//826
        /**
         * @description Filters the REST API response to include only a white-listed set of response object fields.
         * @param TP_REST_Response $response
         * @param $request
         * @return TP_REST_Response
         */
        protected function _rest_filter_response_fields(TP_REST_Response $response, $request ):TP_REST_Response{//not used , $server
            if ( ! isset( $request['_fields'] ) || $response->is_error() )
                return $response;
            $data = $response->get_data();
            $fields = $this->_tp_parse_list( $request['_fields'] );
            if ( 0 === count( $fields ) ) return $response;
            $fields = array_map( 'trim', $fields );
            $fields_as_keyed = [];
            foreach ( $fields as $field ) {
                $parts = explode( '.', $field );
                $ref   = &$fields_as_keyed;
                while ( count( $parts ) > 1 ) {
                    $next = array_shift( $parts );
                    if ( isset( $ref[ $next ] ) && true === $ref[ $next ] )
                        break 2;
                    $ref[ $next ] = $ref[ $next ] ?? [];
                    $ref          = &$ref[ $next ];
                }
                $last         = array_shift( $parts );
                $ref[ $last ] = true;
            }
            if ( $this->_tp_is_numeric_array( $data ) ) {
                $new_data = [];
                foreach ( $data as $item ) $new_data[] = $this->_rest_array_intersect_key_recursive( $item, $fields_as_keyed );
            } else $new_data = $this->_rest_array_intersect_key_recursive( $data, $fields_as_keyed );
            $response->set_data( $new_data );
            return $response;
        }//846
        /**
         * @description Given an array of fields to include in a response, some of which may be `nested.fields`,
         * @description . determine whether the provided field should be included in the response body.
         * @param $field
         * @param $fields
         * @return bool
         */
        protected function _rest_is_field_included( $field, $fields ):bool{
            if ( in_array( $field, $fields, true ) ) return true;
            foreach ( $fields as $accepted_field ) {
                if ( strpos( $accepted_field, "$field." ) === 0 ) return true;
                if ( strpos( $field, "$accepted_field." ) === 0 ) return true;
            }
            return false;
        }//910
    }
}else die;