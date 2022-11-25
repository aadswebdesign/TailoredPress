<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-5-2022
 * Time: 13:09
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Libs\RestApi\TP_REST_Server;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_REST_Controller extends Endpoints_Base {
        public function register_routes():void{
            $this->_doing_it_wrong(
                'TP_REST_Controller::register_routes',
                /* translators: %s: register_routes() */
                sprintf( $this->__( "Method '%s' must be overridden." ), __METHOD__ ),
                '0.0.1'
            );
        }//48
        public function get_items_permissions_check($request):string{
            $this->_fake = $request;
            return new TP_Error(
                'invalid-method',
                /* translators: %s: Method name. */
                sprintf( $this->__( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
                array( 'status' => 405 )
            );
        }//65
        public function get_items( $request ):string{
            $this->_fake = $request;
            return new TP_Error(
                'invalid-method',
                /* translators: %s: Method name. */
                sprintf( $this->__( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
                array( 'status' => 405 )
            );
        }//82
        public function get_item_permissions_check( $request ):string{
            $this->_fake = $request;
            return new TP_Error(
                'invalid-method',
                /* translators: %s: Method name. */
                sprintf( $this->__( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
                array( 'status' => 405 )
            );
        }//99
        public function get_item( $request ):string{
            $this->_fake = $request;
            return new TP_Error(
                'invalid-method',
                /* translators: %s: Method name. */
                sprintf( $this->__( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
                array( 'status' => 405 )
            );
        }//116
        public function create_item_permissions_check( $request ):string{
            $this->_fake = $request;
            return new TP_Error(
                'invalid-method',
                /* translators: %s: Method name. */
                sprintf( $this->__( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
                array( 'status' => 405 )
            );
        }//133
        public function create_item( $request ):string{
            $this->_fake = $request;
            return new TP_Error(
                'invalid-method',
                /* translators: %s: Method name. */
                sprintf( $this->__( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
                array( 'status' => 405 )
            );
        }//150
        public function update_item_permissions_check( $request ):string{
            $this->_fake = $request;
            return new TP_Error(
                'invalid-method',
                /* translators: %s: Method name. */
                sprintf( $this->__( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
                array( 'status' => 405 )
            );}//167
        public function update_item( $request ):string{
            $this->_fake = $request;
            return new TP_Error(
                'invalid-method',
                /* translators: %s: Method name. */
                sprintf( $this->__( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
                array( 'status' => 405 )
            );}//184
        public function delete_item_permissions_check( $request ):string{
            $this->_fake = $request;
            return new TP_Error(
                'invalid-method',
                /* translators: %s: Method name. */
                sprintf( $this->__( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
                array( 'status' => 405 )
            );}//201
        public function delete_item( $request ):string{
            $this->_fake = $request;
            return new TP_Error(
                'invalid-method',
                /* translators: %s: Method name. */
                sprintf( $this->__( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
                array( 'status' => 405 )
            );}//218
        protected function _prepare_item_for_database( $request):string{
            $this->_fake = $request;
            return new TP_Error(
                'invalid-method',
                /* translators: %s: Method name. */
                sprintf( $this->__( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
                array( 'status' => 405 )
            );
        }//235
        public function prepare_item_for_response( $item, $request ):string{
            $this->_fake = $item.$request;
            return new TP_Error(
                'invalid-method',
                /* translators: %s: Method name. */
                sprintf( $this->__( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ),
                array( 'status' => 405 )
            );}//253
        public function prepare_response_for_collection( $response ){
            if ( ! ( $response instanceof TP_REST_Response ) )  return $response;
            $data   = (array) $response->get_data();
            $server = $this->_rest_get_server();
            if ($server instanceof TP_REST_Server){}
            $links  = $server::get_compact_response_links( $response );
            if ( ! empty( $links ) ) $data['_links'] = $links;
            return $data;
        }//270
        public function filter_response_by_context( $data, $context ){
            $schema = $this->get_item_schema();
            return $this->_rest_filter_response_by_context( $data, $schema, $context );
        }//295
        public function get_item_schema(){
            return $this->_add_additional_fields_schema([]);
        }//309
        public function get_public_item_schema(){
            $schema = $this->get_item_schema();
            if ( ! empty( $schema['properties'] ) ) {
                foreach ( $schema['properties'] as &$property )
                    unset( $property['arg_options'] );
            }
            return $schema;
        }//320
        public function get_collection_params():array{
            return [
                'context'  => $this->get_context_param(),
                'page'     => [
                    'description' => $this->__( 'Current page of the collection.' ),
                    'type' => 'integer','default' => 1,'sanitize_callback' => 'absint',
                    'validate_callback' => 'rest_validate_request_arg','minimum' => 1,
                ],
                'per_page' => [
                    'description' => $this->__( 'Maximum number of items to be returned in result set.' ),
                    'type' => 'integer','default' => 10,'minimum' => 1,'maximum' => 100,
                    'sanitize_callback' => 'absint','validate_callback' => 'rest_validate_request_arg',
                ],
                'search' => [
                    'description' => $this->__( 'Limit results to those matching a string.' ),
                    'type' => 'string','sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ]
            ];
        }//340
        public function get_context_param( ...$args){
            $param_details = [
                'description'       => $this->__( 'Scope under which the request is made; determines fields present in response.' ),
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_key',
                'validate_callback' => 'rest_validate_request_arg',
            ];
            $schema = $this->get_item_schema();
            if ( empty( $schema['properties'] ) )
                return array_merge( $param_details, $args );
            $contexts = [];
            foreach ( $schema['properties'] as $attributes ) {
                if ( ! empty( $attributes['context'] ) )
                    $contexts = $this->_tp_array_merge($contexts, $attributes['context']);
            }
            if ( ! empty( $contexts ) ) {
                $param_details['enum'] = array_unique( $contexts );
                rsort( $param_details['enum'] );
            }
            return array_merge( $param_details, $args );
        }//379
        public function get_fields_for_response( $request ){
            $schema     = $this->get_item_schema();
            $properties = $schema['properties'] ?? [];
            $context = $request['context'];
            if ( $context ) {
                foreach ( $properties as $name => $options ) {
                    if ( ! empty( $options['context'] ) && ! in_array( $context, $options['context'], true ) )
                        unset( $properties[ $name ] );
                }
            }
            $fields = array_keys( $properties );
            if ( ! isset( $request['_fields'] ) ) return $fields;
            $requested_fields = $this->_tp_parse_list( $request['_fields'] );
            if ( 0 === count( $requested_fields ) ) return $fields;
            $requested_fields = array_map( 'trim', $requested_fields );
            if ( in_array( 'id', $fields, true ) ) $requested_fields[] = 'id';
            return array_reduce(
                $requested_fields,
                static function( $response_fields, $field ) use ( $fields ) {
                    if ( in_array( $field, $fields, true ) ) {
                        $response_fields[] = $field;
                        return $response_fields;
                    }
                    $nested_fields = explode( '.', $field );
                    if ( in_array( $nested_fields[0], $fields, true ) )
                        $response_fields[] = $field;
                    return $response_fields;
                },
                []
            );
        }//558
        public function get_endpoint_args_for_item_schema( $method = TP_POST ): array{
            return $this->_rest_get_endpoint_args_for_schema( $this->get_item_schema(), $method );
        }//628
        public function sanitize_slug( $slug ){
            return $this->_sanitize_title( $slug );
        }//651
        protected function _add_additional_fields_to_object( $prepared, $request ){
            $additional_fields = $this->_get_additional_fields();
            $requested_fields = $this->get_fields_for_response( $request );
            foreach ( $additional_fields as $field_name => $field_options ) {
                if ( ! $field_options['get_callback'] )continue;
                if ( ! $this->_rest_is_field_included( $field_name, $requested_fields ) )
                    continue;
                $prepared[ $field_name ] = call_user_func( $field_options['get_callback'], $prepared, $field_name, $request, $this->_get_object_type() );
            }
            return $prepared;
        }//418
        protected function _update_additional_fields_for_object( $object, $request ){
            $additional_fields = $this->_get_additional_fields();
            foreach ( $additional_fields as $field_name => $field_options ) {
                if ( ! $field_options['update_callback'] ) continue;
                if ( ! isset( $request[ $field_name ] ) ) continue;
                $result = call_user_func( $field_options['update_callback'], $request[ $field_name ], $object, $field_name, $request, $this->_get_object_type() );
                if ( $this->_init_error( $result ) ) return $result;
            }
            return true;
        }//448
        protected function _add_additional_fields_schema( $schema ){
            if ( empty( $schema['title'] ) ) return $schema;
            $object_type = $schema['title'];
            $additional_fields = $this->_get_additional_fields( $object_type );
            foreach ( $additional_fields as $field_name => $field_options ) {
                if ( ! $field_options['schema'] ) continue;
                $schema['properties'][ $field_name ] = $field_options['schema'];
            }
            return $schema;
        }//481
        protected function _get_additional_fields( $object_type = null ){
            if ( ! $object_type ) $object_type = $this->_get_object_type();
            if ( ! $object_type ) return [];
            if ( ! $this->_tp_rest_additional_fields || ! isset( $this->_tp_rest_additional_fields[ $object_type ] ) )
                return [];
            return $this->_tp_rest_additional_fields[ $object_type ];
        }//513
        protected function _get_object_type(){
            $schema = $this->get_item_schema();
            if ( ! $schema || ! isset( $schema['title'] ) ) return null;
            return $schema['title'];
        }//538
    }
}else die;