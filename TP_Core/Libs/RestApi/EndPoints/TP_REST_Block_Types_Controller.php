<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\Block\TP_Block_Type;
use TP_Core\Libs\Block\TP_Block_Type_Registry;
use TP_Core\Libs\Block\TP_Block_Styles_Registry;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Libs\RestApi\TP_REST_Request;
if(ABSPATH){
    class TP_REST_Block_Types_Controller extends TP_REST_Controller{
        protected $_block_registry;
        protected $_style_registry;
        public function __construct() {
            $this->_namespace      = 'tp/v1';
            $this->_rest_base      = 'block-types';
            $this->_block_registry = TP_Block_Type_Registry::get_instance();
            $this->_style_registry = TP_Block_Styles_Registry::get_instance();
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base,
                [['methods' => TP_GET,'callback' => [$this, 'get_items'],
                        'permission_callback' => [$this, 'get_items_permissions_check'],
                        'args' => $this->get_collection_params(),
                    ],'schema' => [$this, 'get_public_item_schema'],]
            );
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/(?P<namespace>[a-zA-Z0-9_-]+)',
                [['methods' => TP_GET,'callback' => [$this, 'get_items'],
                        'permission_callback' => [ $this, 'get_items_permissions_check'],
                        'args' => $this->get_collection_params(),],
                    'schema' => [$this, 'get_public_item_schema'],]
            );
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/(?P<namespace>[a-zA-Z0-9_-]+)/(?P<name>[a-zA-Z0-9_-]+)',//todo lookup
                ['args' => [
                        'name' => ['description' => $this->__( 'Block name.' ),'type' => 'string',],
                        'namespace' => ['description' => $this->__( 'Block namespace.' ),'type' => 'string',],
                    ],
                    ['methods'=> TP_GET,'callback'=> [$this, 'get_items'],
                        'permission_callback' => [ $this, 'get_items_permissions_check'],
                        'args'=> ['context' => $this->get_context_param( array( 'default' => 'view' ) ),],
                    ],
                    'schema' => [$this, 'get_public_item_schema'],
                ]
            );
        }//54
        public function get_items_permissions_check( $request ):string{
            return $this->_check_read_permission();
        }//119
        protected function _check_read_permission(){
            if ( $this->_current_user_can( 'edit_posts' ) ) return true;
            foreach ( $this->_get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
                if ( $this->_current_user_can( $post_type->cap->edit_posts ) ) return true;
            }
            return new TP_Error( 'rest_block_type_cannot_view', $this->__( 'Sorry, you are not allowed to manage block types.' ), array( 'status' => $this->_rest_authorization_required_code() ) );
        }//186
        protected function _get_block( $name ) {
            if( $this->_block_registry instanceof TP_Block_Type_Registry ){
                $block_type = $this->_block_registry->registered( $name );
            }
            if ( empty( $block_type ) )
                return new TP_Error( 'rest_block_type_invalid', $this->__( 'Invalid block type.' ), array( 'status' => 404 ) );
            return $block_type;
        }//207
        public function get_item( $request ):string{
            $block_name = sprintf( '%s/%s', $request['namespace'], $request['name'] );
            $block_type = $this->_get_block( $block_name );
            if ( $this->_init_error( $block_type ) ) return $block_type;
            $data = $this->prepare_item_for_response( $block_type, $request );
            return $this->_rest_ensure_response( $data );
        }//224
        public function prepare_item_for_response(TP_Block_Type $item, $request ):string{
            $block_type = $item;
            $fields     = $this->get_fields_for_response( $request );
            $data       = [];
            if ($block_type instanceof TP_REST_Request && $this->_rest_is_field_included('attributes', $fields)) {
                $data['attributes'] = $block_type->get_attributes();
            }
            if ($block_type instanceof TP_Block_Type && $this->_rest_is_field_included( 'is_dynamic', $fields ) ){
                $data['is_dynamic'] = $block_type->is_dynamic();
            }
            $schema       = $this->get_item_schema();
            $extra_fields = ['api_version','name','title','description','icon','category',
                'keywords','parent','provides_context','uses_context','supports','styles',
                'textdomain','example','editor_script','script','view_script','editor_style',
                'style','variations',];
            foreach ( $extra_fields as $extra_field ) {
                if ( $this->_rest_is_field_included( $extra_field, $fields ) ) {
                    if ( isset( $block_type->$extra_field ) )
                        $field = $block_type->$extra_field;
                    elseif ( array_key_exists( 'default', $schema['properties'][ $extra_field ] ) )
                        $field = $schema['properties'][ $extra_field ]['default'];
                    else $field = '';
                    $data[ $extra_field ] = $this->_rest_sanitize_value_from_schema( $field, $schema['properties'][ $extra_field ] );
                }
            }
            if ( $this->_rest_is_field_included( 'styles', $fields ) ) {
                $styles         = $this->_style_registry->get_registered_styles_for_block( $block_type->name );
                $styles         = array_values( $styles );
                $data['styles'] = $this->_tp_parse_args( $styles, $data['styles'] );
                $data['styles'] = array_filter( $data['styles'] );
            }
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $response = $this->_rest_ensure_response( $data );
            if( $response instanceof  TP_REST_Response){}
            $response->add_links( $this->_prepare_links( $block_type ) );
            return $this->_apply_filters( 'rest_prepare_block_type', $response, $block_type, $request );
        }//245
        protected function _prepare_links(TP_Block_Type $block_type ): array{
            @list( $namespace ) = explode( '/', $block_type->name );
            $links = [
                'collection' => ['href' => $this->_rest_url( sprintf( '%s/%s', $this->_namespace, $this->_rest_base ) ),],
                'self' => ['href' => $this->_rest_url( sprintf( '%s/%s/%s', $this->_namespace, $this->_rest_base, $block_type->name ) ),],
                'up' => ['href' => $this->_rest_url( sprintf( '%s/%s/%s', $this->_namespace, $this->_rest_base, $namespace ) ),],
            ];
            if ( $block_type->is_dynamic() )
                $links['https://api.w.org/render-block'] = ['href' => $this->_add_query_arg( 'context', 'edit', $this->_rest_url( sprintf( '%s/%s/%s', 'wp/v2', 'block-renderer', $block_type->name ) ) ),];
            return $links;
        }
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $inner_blocks_definition = [
                'description' => $this->__( 'The list of inner blocks used in the example.' ),
                'type'=> 'array',
                'items'=> ['type' => 'object',
                    'properties' => [
                        'name' => ['description' => $this->__( 'The name of the inner block.' ), 'type' => 'string',],
                        'attributes' => ['description' => $this->__( 'The attributes of the inner block.' ),'type' => 'object',],
                        'innerBlocks' => ['description' => $this->__( "A list of the inner block's own inner blocks. This is a recursive definition following the parent innerBlocks schema." ),'type' => 'array',],
                    ],
                ],
            ];
            $example_definition = [
                'description' => $this->__( 'Block example.' ),
                'type' => ['object','null'],'default' => null,
                'properties'  => [
                    'attributes'  => ['description' => $this->__( 'The attributes used in the example.' ),'type' => 'object',],
                    'innerBlocks' => $inner_blocks_definition,
                ],
                'context' => ['embed', 'view', 'edit'],
                'readonly' => true,
            ];
            $keywords_definition = [
                'description' => $this->__( 'Block keywords.' ),'type'=> 'array','items' => ['type' => 'string',],
                'default' => [],'context' => ['embed', 'view', 'edit'],'readonly' => true,
            ];
            $icon_definition = [
                'description' => $this->__( 'Icon of block type.' ),'type' => ['string', 'null'],
                'default' => null,'context' => ['embed', 'view', 'edit'],'readonly' => true,
            ];
            $category_definition = [
                'description' => $this->__( 'Block category.' ),'type' => ['string', 'null'],
                'default' => null,'context' => ['embed', 'view', 'edit'],'readonly' => true,
            ];
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => 'block-type','type' => 'object',
                'properties' => [
                    'api_version'      => [
                        'description' => $this->__( 'Version of block API.' ),
                        'type'        => 'integer',
                        'default'     => 1,
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ],
                    'title'            => [
                        'description' => $this->__( 'Title of block type.' ),
                        'type'        => 'string',
                        'default'     => '',
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ],
                    'name'             => [
                        'description' => $this->__( 'Unique name identifying the block type.' ),
                        'type'        => 'string',
                        'default'     => '',
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ],
                    'description'      => [
                        'description' => $this->__( 'Description of block type.' ),
                        'type'        => 'string',
                        'default'     => '',
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ],
                    'icon'             => $icon_definition,
                    'attributes'       => array(
                        'description'          => $this->__( 'Block attributes.' ),
                        'type'                 => array( 'object', 'null' ),
                        'properties'           => array(),
                        'default'              => null,
                        'additionalProperties' => array(
                            'type' => 'object',
                        ),
                        'context'              => ['embed', 'view', 'edit'],
                        'readonly'             => true,
                    ),
                    'provides_context' => array(
                        'description'          => $this->__( 'Context provided by blocks of this type.' ),
                        'type'                 => 'object',
                        'properties'           => array(),
                        'additionalProperties' => array(
                            'type' => 'string',
                        ),
                        'default'              => array(),
                        'context'              => ['embed', 'view', 'edit'],
                        'readonly'             => true,
                    ),
                    'uses_context'     => array(
                        'description' => $this->__( 'Context values inherited by blocks of this type.' ),
                        'type'        => 'array',
                        'default'     => array(),
                        'items'       => array(
                            'type' => 'string',
                        ),
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ),
                    'supports'         => array(
                        'description' => $this->__( 'Block supports.' ),
                        'type'        => 'object',
                        'default'     => array(),
                        'properties'  => array(),
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ),
                    'category'         => $category_definition,
                    'is_dynamic'       => array(
                        'description' => $this->__( 'Is the block dynamically rendered.' ),
                        'type'        => 'boolean',
                        'default'     => false,
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ),
                    'editor_script'    => array(
                        'description' => $this->__( 'Editor script handle.' ),
                        'type'        => array( 'string', 'null' ),
                        'default'     => null,
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ),
                    'script'           => array(
                        'description' => $this->__( 'Public facing and editor script handle.' ),
                        'type'        => array( 'string', 'null' ),
                        'default'     => null,
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ),
                    'view_script'      => array(
                        'description' => $this->__( 'Public facing script handle.' ),
                        'type'        => array( 'string', 'null' ),
                        'default'     => null,
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ),
                    'editor_style'     => array(
                        'description' => $this->__( 'Editor style handle.' ),
                        'type'        => array( 'string', 'null' ),
                        'default'     => null,
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ),
                    'style'            => array(
                        'description' => $this->__( 'Public facing and editor style handle.' ),
                        'type'        => array( 'string', 'null' ),
                        'default'     => null,
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ),
                    'styles'           => array(
                        'description' => $this->__( 'Block style variations.' ),
                        'type'        => 'array',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'name'         => array(
                                    'description' => $this->__( 'Unique name identifying the style.' ),
                                    'type'        => 'string',
                                    'required'    => true,
                                ),
                                'label'        => array(
                                    'description' => $this->__( 'The human-readable label for the style.' ),
                                    'type'        => 'string',
                                ),
                                'inline_style' => array(
                                    'description' => $this->__( 'Inline CSS code that registers the CSS class required for the style.' ),
                                    'type'        => 'string',
                                ),
                                'style_handle' => array(
                                    'description' => $this->__( 'Contains the handle that defines the block style.' ),
                                    'type'        => 'string',
                                ),
                            ),
                        ),
                        'default'     => array(),
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ),
                    'variations'       => array(
                        'description' => $this->__( 'Block variations.' ),
                        'type'        => 'array',
                        'items'       => array(
                            'type'       => 'object',
                            'properties' => array(
                                'name'        => array(
                                    'description' => $this->__( 'The unique and machine-readable name.' ),
                                    'type'        => 'string',
                                    'required'    => true,
                                ),
                                'title'       => array(
                                    'description' => $this->__( 'A human-readable variation title.' ),
                                    'type'        => 'string',
                                    'required'    => true,
                                ),
                                'description' => array(
                                    'description' => $this->__( 'A detailed variation description.' ),
                                    'type'        => 'string',
                                    'required'    => false,
                                ),
                                'category'    => $category_definition,
                                'icon'        => $icon_definition,
                                'isDefault'   => array(
                                    'description' => $this->__( 'Indicates whether the current variation is the default one.' ),
                                    'type'        => 'boolean',
                                    'required'    => false,
                                    'default'     => false,
                                ),
                                'attributes'  => array(
                                    'description' => $this->__( 'The initial values for attributes.' ),
                                    'type'        => 'object',
                                ),
                                'innerBlocks' => $inner_blocks_definition,
                                'example'     => $example_definition,
                                'scope'       => array(
                                    'description' => $this->__( 'The list of scopes where the variation is applicable. When not provided, it assumes all available scopes.' ),
                                    'type'        => array( 'array', 'null' ),
                                    'default'     => null,
                                    'items'       => array(
                                        'type' => 'string',
                                        'enum' => array( 'block', 'inserter', 'transform' ),
                                    ),
                                    'readonly'    => true,
                                ),
                                'keywords'    => $keywords_definition,
                            ),
                        ),
                        'readonly'    => true,
                        'context'     => ['embed', 'view', 'edit'],
                        'default'     => null,
                    ),
                    'textdomain'       => array(
                        'description' => $this->__( 'Public text domain.' ),
                        'type'        => array( 'string', 'null' ),
                        'default'     => null,
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ),
                    'parent'           => array(
                        'description' => $this->__( 'Parent blocks.' ),
                        'type'        => array( 'array', 'null' ),
                        'items'       => ['type' => 'string',],
                        'default'     => null,
                        'context'     => ['embed', 'view', 'edit'],
                        'readonly'    => true,
                    ),
                    'keywords'         => $keywords_definition,
                    'example'          => $example_definition,
                ],
            ];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//363 //todo shrink
        public function get_collection_params():array{
            return ['context' => $this->get_context_param( ['default' => 'view']),
                'namespace' => ['description' => $this->__( 'Block namespace.' ),'type' => 'string',],
            ];
        }//762
    }
}else die;