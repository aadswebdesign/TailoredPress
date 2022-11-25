<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\RestApi\TP_REST_Request;
if(ABSPATH){
    class TP_REST_Settings_Controller extends TP_REST_Controller{
        public function __construct() {
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'settings';
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base,
                [
                    [
                        'methods' => TP_GET,'callback' => [$this, 'get_item'],'args' => [],
                        'permission_callback' => [$this, 'get_item_permissions_check'],
                    ],
                    [
                        'methods' => TP_EDITABLE,'callback' => [$this, 'update_item'],
                        'args' => $this->get_endpoint_args_for_item_schema( TP_EDITABLE ),
                        'permission_callback' => [$this, 'get_item_permissions_check'],
                    ],
                    'schema' => [$this, 'get_public_item_schema'],
                ]
            );
        }//36
        public function get_items_permissions_check( $request ):string{
            return $this->_current_user_can( 'manage_options' );
        }//68
        public function get_item( $request ):string{
            $options  = $this->_get_registered_options();
            $response = [];
            foreach ( $options as $name => $args ) {
                $response[ $name ] = $this->_apply_filters( 'rest_pre_get_setting', null, $name, $args );
                if ( is_null( $response[ $name ] ) )
                    $response[ $name ] = $this->_get_option( $args['option_name'], $args['schema']['default'] );
                $response[ $name ] = $this->_prepare_value( $response[ $name ], $args['schema'] );
            }
            return $response;
        }//80
        protected function _prepare_value( $value, $schema ){
            if ( $this->_init_error( $this->_rest_validate_value_from_schema( $value, $schema ) ) )
                return null;
            return $this->_rest_sanitize_value_from_schema( $value, $schema );
        }//125
        public function update_item(TP_REST_Request $request ):string{
            $options = $this->_get_registered_options();
            $params = $request->get_params();
            foreach ( $options as $name => $args ) {
                if ( ! array_key_exists( $name, $params ) ) continue;
                $updated = $this->_apply_filters( 'rest_pre_update_setting', false, $name, $request[ $name ], $args );
                if ( $updated ) continue;
                if ( is_null( $request[ $name ] ) ) {
                    if ( $this->_init_error( $this->_rest_validate_value_from_schema( $this->_get_option( $args['option_name'], false ), $args['schema'] ) ) )
                        return new TP_Error('rest_invalid_stored_value',
                            sprintf( $this->__( 'The %s property has an invalid stored value, and cannot be updated to null.' ), $name ),
                            ['status' => INTERNAL_SERVER_ERROR]);
                    $this->_delete_option( $args['option_name'] );
                } else $this->_update_option( $args['option_name'], $request[ $name ] );
            }
            return $this->get_item( $request );
        }//146
        protected function _get_registered_options():string{
            $rest_options = [];
            foreach ((array) $this->_get_registered_settings() as $name => $args ) {
                if ( empty( $args['show_in_rest'] ) ) continue;
                $rest_args = [];
                if ( is_array( $args['show_in_rest'] ) ) $rest_args = $args['show_in_rest'];
                $defaults = ['name' => ! empty( $rest_args['name'] ) ? $rest_args['name'] : $name, 'schema' => [],];
                $rest_args = $this->_tp_array_merge($defaults, $rest_args);
                $default_schema = [
                    'type' => empty( $args['type'] ) ? null : $args['type'],
                    'description' => empty( $args['description'] ) ? '' : $args['description'],
                    'default' => $args['default'] ?? null,
                ];
                $rest_args['schema']= $this->_tp_array_merge($default_schema, $rest_args['schema']);
                $rest_args['option_name'] = $name;
                if ( empty( $rest_args['schema']['type'] ) ) continue;
                if ( ! in_array( $rest_args['schema']['type'], array( 'number', 'integer', 'string', 'boolean', 'array', 'object' ), true ) )
                    continue;
                $rest_args['schema'] = $this->_set_additional_properties_to_false( $rest_args['schema'] );
                $rest_options[ $rest_args['name'] ] = $rest_args;
            }
            return $rest_options;
        }//218
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $options = $this->_get_registered_options();
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => 'settings','type' => 'object','properties' => []
            ];
            foreach ( $options as $option_name => $option ) {
                $schema['properties'][ $option_name ] = $option['schema'];
                $schema['properties'][ $option_name ]['arg_options'] = [
                    'sanitize_callback' => [$this, 'sanitize_callback'],
                ];
            }
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//276
        public function sanitize_callback( $value, $request, $param ){
            if ( is_null( $value ) ) return $value;
            return $this->_rest_parse_request_arg( $value, $request, $param );
        }//316
        protected function _set_additional_properties_to_false( $schema ){
            switch ( $schema['type'] ) {
                case 'object':
                    foreach ( $schema['properties'] as $key => $child_schema )
                        $schema['properties'][ $key ] = $this->_set_additional_properties_to_false( $child_schema );
                    $schema['additionalProperties'] = false;
                    break;
                case 'array':
                    $schema['items'] = $this->_set_additional_properties_to_false( $schema['items'] );
                    break;
            }
            return $schema;
        }//336
    }
}else die;
