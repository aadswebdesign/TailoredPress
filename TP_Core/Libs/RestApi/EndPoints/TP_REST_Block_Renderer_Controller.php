<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\Block\TP_Block_Type_Registry;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\RestApi\TP_REST_Request;
if(ABSPATH){
    class TP_REST_Block_Renderer_Controller extends TP_REST_Controller{
        public function __construct() {
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'block-renderer';
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/(?P<name>[a-z0-9-]+/[a-z0-9-]+)',
                ['args' => ['name' => ['description' => $this->__( 'Unique registered name for the block.' ),'type' => 'string',],],
                    ['methods' => [TP_GET, TP_POST],'callback' => array( $this, 'get_item' ),
                        'permission_callback' => [$this, 'get_item_permissions_check'],
                        'args' => ['context'  => $this->get_context_param( [ 'default' => 'view'] ),
                            'attributes' => [
                                'description' => $this->__( 'Attributes for the block.' ),'type' => 'object','default' => [],
                                'validate_callback' => static function ( $value, $request ) {
                                    $block = TP_Block_Type_Registry::get_instance()->registered( $request['name'] );
                                    if ( ! $block ) return true;
                                    $block_attributes = null;
                                    if( $block instanceof TP_REST_Request ){
                                        $block_attributes =  $block->get_attributes();
                                    }
                                    $schema = ['type' => 'object','properties' => $block_attributes,'additionalProperties' => false,];
                                    return (new static)->_rest_validate_value_from_schema( $value, $schema );
                                },
                                'sanitize_callback' => static function ( $value, $request ) {
                                    $block = TP_Block_Type_Registry::get_instance()->registered( $request['name'] );
                                    if ( ! $block ) return true;
                                    $block_attributes = null;
                                    if( $block instanceof TP_REST_Request ){
                                        $block_attributes =  $block->get_attributes();
                                    }
                                    $schema = ['type' => 'object','properties' => $block_attributes,'additionalProperties' => false,];
                                    return (new static)->_rest_sanitize_value_from_schema( $value, $schema );
                                },
                            ],
                            'post_id' => ['description' => $this->__( 'ID of the post context.' ),'type' => 'integer',],
                        ],
                    ],
                    'schema' => [$this, 'get_public_item_schema'],
                ]
            );
        }//70
        public function get_items_permissions_check( $request ):string{
            $post = $this->_init_post();
            $_post_id = $post->ID;
            $post_id = isset( $request['post_id'] ) ? (int) $request['post_id'] : 0;
            if ( $post_id > 0 ) {
                $post = $this->_get_post( $post_id );
                if ( ! $post || ! $this->_current_user_can( 'edit_post', $_post_id ) ) {
                    return new TP_Error('block_cannot_read',
                        $this->__( 'Sorry, you are not allowed to read blocks of this post.' ),
                        ['status' => $this->_rest_authorization_required_code(),]
                    );
                }
            } else if ( ! $this->_current_user_can( 'edit_posts' ) )
                return new TP_Error('block_cannot_read',
                    $this->__( 'Sorry, you are not allowed to read blocks as this user.' ),
                    ['status' => $this->_rest_authorization_required_code(),]);
            return true;
        }//111
        public function get_item(TP_REST_Request $request ):string{
            $this->_tp_post;
            $post_id = isset( $request['post_id'] ) ? (int) $request['post_id'] : 0;
            if ( $post_id > 0 ) {
                $this->_tp_post = $this->_get_post( $post_id );
                $this->_setup_postdata( $this->_tp_post );
            }
            $registry   = TP_Block_Type_Registry::get_instance();
            $registry_registered = null;
            if( $registry instanceof TP_Block_Type_Registry ){
                $registry_registered =  $registry->registered( $request['name'] );
            }
            $registered = $registry_registered;
            if ( null === $registered || ! $registered->is_dynamic() )
                return new TP_Error('block_invalid',$this->__( 'Invalid block.' ),['status' => NOT_FOUND,]);
            $attributes = $request->get_param( 'attributes' );
            $block = ['blockName' => $request['name'],'attrs' => $attributes,'innerHTML' => '', 'innerContent' => [],];
            $data = ['rendered' => $this->_render_block( $block ),];
            return $this->_rest_ensure_response( $data );
        }//153
        public function get_item_schema(){
            if ( $this->_schema ) return $this->_schema;
            $this->_schema = ['$schema' => 'http://json-schema.org/schema#','title' => 'rendered-block','type' => 'object',
                'properties' => [
                    'rendered' => ['description' => $this->__( 'The rendered block.' ),'type' => 'string','required' => true,'context' => ['edit'],],
                ],
            ];
            return $this->_schema;
        }//203
    }
}else die;