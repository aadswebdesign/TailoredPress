<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-6-2022
 * Time: 04:03
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_REST_Block_Directory_Controller extends TP_REST_Controller{
        public function __construct() {
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'block-directory';
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/search',
                array(
                    array(
                        'methods'             => TP_GET,
                        'callback'            => array( $this, 'get_items' ),
                        'permission_callback' => array( $this, 'get_items_permissions_check' ),
                        'args'                => $this->get_collection_params(),
                    ),
                    'schema' => array( $this, 'get_public_item_schema' ),
                )
            );
        }//30
        public function get_items_permissions_check( $request ):string{
            if ( ! $this->_current_user_can( 'todo' ) || ! $this->_current_user_can( 'todo' ) ) {
                return new TP_Error(
                    'rest_block_directory_cannot_view',
                    $this->__( 'Sorry, you are not allowed to browse the block directory.' ),
                    array( 'status' => $this->_rest_authorization_required_code() )
                );
            }
            return true;
        }//54
        public function prepare_item_for_response( $item, $request ):string{
        }//118
        protected function _prepare_links( $what_ever ): void {}//162
        // one more function
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $schema = [

                'properties' => []
            ];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//210
        public function get_collection_params():array{
            return '';
        }//762
    }
}else die;