<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Traits\Templates\_block_utils_template_01;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Libs\RestApi\TP_REST_Request;
if(ABSPATH){
    class TP_REST_Templates_Controller extends TP_REST_Controller{
        use _block_utils_template_01;
        protected $_post_type;
        public function __construct( $post_type ) {
            $this->_post_type = $post_type;
            $obj             = $this->_get_post_type_object( $post_type );
            $this->_rest_base = ! empty( $obj->rest_base ) ? $obj->rest_base : $obj->name;
            $this->_namespace = ! empty( $obj->rest_namespace ) ? $obj->rest_namespace : 'tp/v1';
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base,[
                    ['methods' => TP_GET,'callback' => [$this, 'get_items'],
                        'permission_callback' => [$this, 'get_items_permissions_check'],
                        'args' => $this->get_collection_params(),],
                    ['methods'=> TP_POST,'callback'=> [$this, 'create_item'],
                        'permission_callback' => [$this, 'create_item_permissions_check'],
                        'args'=> $this->get_endpoint_args_for_item_schema( TP_POST ),
                    ],'schema' => [$this, 'get_public_item_schema'],
                ]
            );
            $this->_register_rest_route(
                $this->_namespace,sprintf(
                    '/%s/(?P<id>%s%s)',$this->_rest_base,'([^\/:<>\*\?"\|]+(?:\/[^\/:<>\*\?"\|]+)?)','[\/\w-]+'),[
                    'args' => ['id'=>['description' => $this->__( 'The id of a template' ),
                        'type' => 'string','sanitize_callback' => [$this, '_sanitize_template_id'],]
                    ],
                    ['methods' => TP_GET,'callback' => [$this, 'get_item'],
                        'permission_callback' => [$this, 'get_item_permissions_check'],
                        'args' => ['context' => $this->get_context_param(['default' => 'view']),],
                    ],
                    ['methods' => TP_EDITABLE,
                        'callback' => [$this, 'update_item'],
                        'permission_callback' => [$this, 'update_item_permissions_check'],
                        'args' => $this->get_endpoint_args_for_item_schema( TP_EDITABLE ),
                    ],
                    ['methods' => TP_DELETE,'callback' => [$this, 'delete_item'],
                        'permission_callback' => [$this, 'delete_item_permissions_check'],
                        'args' =>['force' =>['type' => 'boolean','default'=> false,
                            'description' => $this->__( 'Whether to bypass Trash and force deletion.' ),]],
                    ],'schema' => [$this, 'get_public_item_schema'],
                ]
            );
        }//70
        protected function _permissions_check( $request ){
            if (!empty($request) && ! $this->_current_user_can( 'edit_theme_options' ))
                return new TP_Error( 'rest_cannot_manage_templates',
                    $this->__( 'Sorry, you are not allowed to access the templates on this site.' ),
                    ['status' => $this->_rest_authorization_required_code(),]);
            return true;
        }//128
        public function sanitize_template_id( $id ){
            $id = urldecode( $id );
            $last_slash_pos = strrpos( $id, '/' );
            if ( false === $last_slash_pos ) return $id;
            $is_double_slashed = $id[$last_slash_pos - 1] === '/';
            if ( $is_double_slashed ) return $id;
            return (substr( $id, 0, $last_slash_pos ) . '/' . substr( $id, $last_slash_pos ));
        }//160
        public function get_items_permissions_check( $request ):string{
            return $this->_permissions_check( $request );
        }//187
        public function get_items($request ):string{
            $query = array();
            if ( isset( $request['tp_id'] ) ) $query['tp_id'] = $request['tp_id'];
            if ( isset( $request['area'] ) ) $query['area'] = $request['area'];
            if ( isset( $request['post_type'] ) ) $query['post_type'] = $request['post_type'];
            $templates = [];
            foreach ((array) $this->_get_block_templates( $query, $this->_post_type ) as $template ) {
                $data = $this->prepare_item_for_response( $template, $request );
                $templates[] = $this->prepare_response_for_collection( $data );
            }
            return $this->_rest_ensure_response( $templates );
        }//199
        public function get_item_permissions_check( $request ):string{
            return $this->_permissions_check( $request );
        }//228
        public function get_item( $request ):string{
            if ( isset( $request['source'] ) && 'theme' === $request['source'] )
                $template = $this->_get_block_file_template( $request['id'], $this->_post_type );
            else $template = $this->_get_block_template( $request['id'], $this->_post_type );
            if ( ! $template )
                return new TP_Error( 'rest_template_not_found', $this->__( 'No templates exist with that id.' ),['status' => NOT_FOUND]);
            return $this->prepare_item_for_response( $template, $request );
        }//240
        public function update_item_permissions_check( $request ):string {
            return $this->_permissions_check( $request );
        }//262
        public function update_item(TP_REST_Request $request ):string{
            $_template = $this->_get_block_template( $request['id'], $this->_post_type );
            $template = null;
            if( $_template instanceof \stdClass ){
                $template = $_template;
            }
            if ( ! $template )
                return new TP_Error( 'rest_template_not_found', $this->__( 'No templates exist with that id.' ),['status' => NOT_FOUND]);
            $post_before = $this->_get_post( $template->tp_id );
            if ( isset( $request['source'] ) && 'theme' === $request['source'] ) {
                $this->_tp_delete_post( $template->tp_id, true );
                $request->set_param( 'context', 'edit' );
                $template = $this->_get_block_template( $request['id'], $this->_post_type );
                $response = $this->prepare_item_for_response( $template, $request );
                return $this->_rest_ensure_response( $response );
            }
            $changes = $this->_prepare_item_for_database( $request );
            if ( $this->_init_error( $changes ) ) return $changes;
            if ( 'custom' === $template->source ) {
                $update = true;
                $result = $this->_tp_update_post( $this->_tp_slash( (array) $changes ), false );
            } else {
                $update      = false;
                $post_before = null;
                $result      = $this->_tp_insert_post( $this->_tp_slash( (array) $changes ), false );
            }
            if ($result  instanceof TP_Error && $this->_init_error( $result ) ) {
                if ( 'db_update_error' === $result->get_error_code() ) $result->add_data(['status' => INTERNAL_SERVER_ERROR]);
                else  $result->add_data( ['status' => BAD_REQUEST]);
                return $result;
            }
            $_template      = $this->_get_block_template( $request['id'], $this->_post_type );
            $template = null;
            if( $_template instanceof \stdClass ){
                $template = $_template;
            }
            $fields_update = $this->_update_additional_fields_for_object( $template, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'edit' );
            $post = $this->_get_post( $template->tp_id );
            $this->_do_action( "rest_after_insert_{$this->_post_type}", $post, $request, false );
            $this->_tp_after_insert_post( $post, $update, $post_before );
            $response = $this->prepare_item_for_response( $template, $request );
            return $this->_rest_ensure_response( $response );
        }//274
        public function create_item_permissions_check( $request ):string {
            return $this->_permissions_check( $request );
        }//343
        public function create_item( $request ):string{
            $prepared_post = $this->_prepare_item_for_database( $request );
            if ( $this->_init_error( $prepared_post ) ) return $prepared_post;
            $prepared_post->post_name = $request['slug'];
            $_post_id= $this->_tp_insert_post( $this->_tp_slash( (array) $prepared_post ), true );
            $post_id = null;
            if($_post_id  instanceof TP_Error ){
                $post_id = $_post_id;
            }
            if ( $this->_init_error( $post_id ) ) {
                if ( 'db_insert_error' === $post_id->get_error_code() )
                    $post_id->add_data(['status' => INTERNAL_SERVER_ERROR]);
                else $post_id->add_data(['status' => BAD_REQUEST]);
                return $post_id;
            }
            $posts = $this->_get_block_templates( array( 'tp_id' => $post_id ), $this->_post_type );
            if ( ! count( $posts ) )
                return new TP_Error( 'rest_template_insert_error', $this->__( 'No templates exist with that id.' ),['status' => NOT_FOUND] );
            $id            = $posts[0]->id;
            $post          = $this->_get_post( $post_id );
            $_template      = $this->_get_block_template( $id, $this->_post_type );
            $template = null;
            if( $_template instanceof \stdClass ){
                $template = $_template;
            }
            $fields_update = $this->_update_additional_fields_for_object( $template, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            /** This action is documented in wp-includes/rest-api/endpoints/class-wp-rest-posts-controller.php */
            $this->_do_action( "rest_after_insert_{$this->_post_type}", $post, $request, true );
            $this->_tp_after_insert_post( $post, false, null );
            $response = $this->prepare_item_for_response( $template, $request );
            $_response = $this->_rest_ensure_response( $response );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->set_status( CREATED );
            $response->header( 'Location', $this->_rest_url( sprintf( '%s/%s/%s', $this->_namespace, $this->_rest_base, $template->id ) ) );
            return $response;
        }//355
        public function delete_item_permissions_check( $request ):string {
            return $this->_permissions_check( $request );
        }//407
        public function delete_item(TP_REST_Request $request ):string{
            $_template = $this->_get_block_template( $request['id'], $this->_post_type );
            $template = null;
            if( $_template instanceof \stdClass ){
                $template = $_template;
            }
            if ( ! $template )
                return new TP_Error( 'rest_template_not_found', $this->__( 'No templates exist with that id.' ),['status' => NOT_FOUND]);
            if ( 'custom' !== $template->source )
                return new TP_Error( 'rest_invalid_template', $this->__( 'Templates based on theme files can\'t be removed.' ),['status' => BAD_REQUEST]);
            $id    = $template->tp_id;
            $force = (bool) $request['force'];
            $request->set_param( 'context', 'edit' );
            if ( $force ) {
                $previous = $this->prepare_item_for_response( $template, $request );
                $previous_data = null;
                if( $previous instanceof TP_REST_Response ){
                    $previous_data = $previous->get_data();
                }
                $result   = $this->_tp_delete_post( $id, true );
                $response = new TP_REST_Response();
                $response->set_data(['deleted'  => true,'previous' => $previous_data,]);
            } else {
                if ( 'trash' === $template->status )
                    return new TP_Error( 'rest_template_already_trashed',
                        $this->__( 'The template has already been deleted.' ),
                        ['status' => GONE]);
                $result           = $this->_tp_trash_post( $id );
                $template->status = 'trash';
                $response         = $this->prepare_item_for_response( $template, $request );
            }
            if ( ! $result )
                return new TP_Error('rest_cannot_delete',
                    $this->__( 'The template cannot be deleted.' ),
                    ['status' => INTERNAL_SERVER_ERROR]
                );
            return $response;
        }//419
        protected function _prepare_item_for_database( $request ):string{
            $_template = $request['id'] ? $this->_get_block_template( $request['id'], $this->_post_type ) : null;
            $template = null;
            if( $_template instanceof \stdClass ){
                $template = $_template;
            }
            $changes  = new \stdClass();
            if ( null === $template ) {
                $changes->post_type   = $this->_post_type;
                $changes->post_status = 'publish';
                $changes->tax_input   = ['tp_theme' => $request['theme'] ?? $this->_tp_get_theme()->get_stylesheet()];
            } elseif ( 'custom' !== $template->source ) {
                $changes->post_name   = $template->slug;
                $changes->post_type   = $this->_post_type;
                $changes->post_status = 'publish';
                $changes->tax_input   = ['tp_theme' => $template->theme,];
                $changes->meta_input  = ['origin' => $template->source,];
            } else {
                $changes->post_name   = $template->slug;
                $changes->ID          = $template->tp_id;
                $changes->post_status = 'publish';
            }
            if ( isset( $request['content'] ) ) {
                if ( is_string( $request['content'] ) ) $changes->post_content = $request['content'];
                elseif ( isset( $request['content']['raw'] ) ) $changes->post_content = $request['content']['raw'];
            } elseif ( null !== $template && 'custom' !== $template->source ) $changes->post_content = $template->content;
            if ( isset( $request['title'] ) ) {
                if ( is_string( $request['title'] ) ) $changes->post_title = $request['title'];
                elseif ( ! empty( $request['title']['raw'] ) ) $changes->post_title = $request['title']['raw'];
            } elseif ( null !== $template && 'custom' !== $template->source ) $changes->post_title = $template->title;
            if ( isset( $request['description'] ) ) $changes->post_excerpt = $request['description'];
            elseif ( null !== $template && 'custom' !== $template->source ) $changes->post_excerpt = $template->description;
            if ( 'wp_template_part' === $this->_post_type ) {
                if ( isset( $request['area'] ) )
                    $changes->tax_input['tp_template_part_area'] = $this->_filter_block_template_part_area( $request['area'] );
                elseif ( null !== $template && 'custom' !== $template->source && $template->area )
                    $changes->tax_input['tp_template_part_area'] = $this->_filter_block_template_part_area( $template->area );
                elseif ( ! $template->area ) $changes->tax_input['tp_template_part_area'] = TP_NS_CORE_UNCATEGORIZED;
            }
            if ( ! empty( $request['author'] ) ) {
                $post_author = (int) $request['author'];
                if ( $this->_get_current_user_id() !== $post_author ) {
                    $user_obj = $this->_get_user_data( $post_author );
                    if ( ! $user_obj )
                        return new TP_Error('rest_invalid_author',
                            $this->__( 'Invalid author ID.' ), ['status' => BAD_REQUEST]
                        );
                }
                $changes->post_author = $post_author;
            }
            return $changes;
        }//480
        public function prepare_item_for_response( $item, $request ):string{
            $template = $item;
            $fields = $this->get_fields_for_response( $request );
            $data = [];
            if ( $this->_rest_is_field_included( 'id', $fields ) ) $data['id'] = $template->id;
            if ( $this->_rest_is_field_included( 'theme', $fields ) ) $data['theme'] = $template->theme;
            if ( $this->_rest_is_field_included( 'content', $fields ) ) $data['content'] = array();
            if ( $this->_rest_is_field_included( 'content.raw', $fields ) ) $data['content']['raw'] = $template->content;
            if ( $this->_rest_is_field_included( 'content.block_version', $fields ) ) $data['content']['block_version'] = $this->_block_version( $template->content );
            if ( $this->_rest_is_field_included( 'slug', $fields ) ) $data['slug'] = $template->slug;
            if ( $this->_rest_is_field_included( 'source', $fields ) ) $data['source'] = $template->source;
            if ( $this->_rest_is_field_included( 'origin', $fields ) ) $data['origin'] = $template->origin;
            if ( $this->_rest_is_field_included( 'type', $fields ) ) $data['type'] = $template->type;
            if ( $this->_rest_is_field_included( 'description', $fields ) ) $data['description'] = $template->description;
            if ( $this->_rest_is_field_included( 'title', $fields ) ) $data['title'] = [];
            if ( $this->_rest_is_field_included( 'title.raw', $fields ) ) $data['title']['raw'] = $template->title;
            if ( $this->_rest_is_field_included( 'title.rendered', $fields ) ) {
                if ( $template->tp_id ) $data['title']['rendered'] = $this->_apply_filters( 'the_title', $template->title, $template->tp_id );
                else $data['title']['rendered'] = $template->title;
            }
            if ( $this->_rest_is_field_included( 'status', $fields ) ) $data['status'] = $template->status;
            if ( $this->_rest_is_field_included( 'tp_id', $fields ) ) $data['tp_id'] = (int) $template->tp_id;
            if ( $this->_rest_is_field_included( 'has_theme_file', $fields ) ) $data['has_theme_file'] = (bool) $template->has_theme_file;
            if ( 'tp_template' === $template->type && $this->_rest_is_field_included( 'is_custom', $fields ) ) $data['is_custom'] = $template->is_custom;
            if ( $this->_rest_is_field_included( 'author', $fields ) ) $data['author'] = (int) $template->author;
            if ( 'tp_template_part' === $template->type && $this->_rest_is_field_included( 'area', $fields )) $data['area'] = $template->area;
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $links = $this->_prepare_links( $template->id );
            $response->add_links( $links );
            if ( ! empty( $links['self']['href'] ) ) {
                $actions = $this->_get_available_actions();
                $self    = $links['self']['href'];
                foreach ( $actions as $rel ) $response->add_link( $rel, $self );
            }
            return $response;
        }//569
        protected function _prepare_links( $id ): array{
            $base = sprintf( '%s/%s', $this->_namespace, $this->_rest_base );
            $links = [
                'self' => ['href' => $this->_rest_url( $this->_trailingslashit( $base ) . $id ),],
                'collection' => ['href' => $this->_rest_url( $base ),],
                'about' => ['href' => $this->_rest_url( 'tp/v1/types/' . $this->_post_type ),],
            ];
            return $links;
        }//687
        protected function _get_available_actions(): array{
            $rels = [];
            $post_type = $this->_get_post_type_object( $this->_post_type );
            if ( $this->_current_user_can( $post_type->cap->publish_posts ) )
                $rels[] = 'https://api.w.org/action-publish';
            if ( $this->_current_user_can( 'unfiltered_html' ) )
                $rels[] = 'https://api.w.org/action-unfiltered-html';
            return $rels;
        }//712
        public function get_collection_params():array{
            return [
                'context'   => $this->get_context_param( array( 'default' => 'view' ) ),
                'tp_id' => ['description' => $this->__( 'Limit to the specified post id.' ),'type' => 'integer',],
                'area' => ['description' => $this->__( 'Limit to the specified template part area.' ),'type' => 'string',],
                'post_type' => ['description' => $this->__( 'Post type to get the templates for.' ),'type' => 'string',],
            ];
        }//736
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => $this->_post_type,'type' => 'object',
                'properties' => [
                    'id' => ['description' => $this->__( 'ID of template.' ),
                        'type'  => 'string','context' => ['embed','view','edit'],'readonly' => true,
                    ],
                    'slug' => ['description' => $this->__( 'Unique slug identifying the template.' ),
                        'type' => 'string','context' => ['embed','view','edit'],'required' => true,
                        'minLength' => 1,'pattern' => '[a-zA-Z0-9_\-]+',
                    ],
                    'theme' => ['description' => $this->__( 'Theme identifier for the template.' ),
                        'type' => 'string','context' => ['embed','view','edit'],
                    ],
                    'type' => ['description' => $this->__( 'Type of template.' ),
                        'type' => 'string','context' => ['embed','view','edit'],
                    ],
                    'source' => ['description' => $this->__( 'Source of template' ),
                        'type' => 'string','context' => ['embed','view','edit'],'readonly' => true,
                    ],
                    'origin' => ['description' => $this->__( 'Source of a customized template' ),
                        'type' => 'string','context' => ['embed','view','edit'], 'readonly' => true,
                    ],
                    'content' => [
                        'description' => $this->__( 'Content of template.' ),
                        'type' => ['object','string'],'default' => '','context' => ['embed','view','edit'],
                        'properties'  =>[
                            'raw' =>['description' => $this->__( 'Content for the template, as it exists in the database.' ),
                                'type' => 'string','context' => ['view','edit'],
                            ],
                            'block_version' =>['description' => $this->__( 'Version of the content block format used by the template.' ),
                                'type' => 'integer','context' => ['edit'],'readonly' => true,
                            ]
                        ]
                    ],
                    'title' => ['description' => $this->__( 'Title of template.' ),
                        'type' => ['object','string'],'default' => '','context' => ['embed','view','edit'],
                        'properties'  =>[
                            'raw' =>['description' => $this->__( 'Title for the template, as it exists in the database.' ),
                                'type' => 'string','context' => ['embed','view','edit'],
                            ],
                            'rendered' =>['description' => $this->__( 'HTML title for the template, transformed for display.' ),
                                'type' => 'string','context' => ['embed','view','edit'],'readonly' => true,
                            ]
                        ]
                    ],
                    'description' => [
                        'description' => $this->__( 'Description of template.' ),
                        'type' => 'string','default' => '','context' => ['embed','view','edit'],
                    ],
                    'status' => ['description' => $this->__( 'Status of template.' ),
                        'type' => 'string','enum' => array_keys( $this->_get_post_stati(['internal' => false]) ),
                        'default' => 'publish','context' => ['embed','view','edit'],
                    ],
                    'tp_id' => ['description' => $this->__( 'Post ID.' ),
                        'type' => 'integer','context' => ['embed','view','edit'],'readonly' => true,
                    ],
                    'has_theme_file' => ['description' => $this->__( 'Theme file exists.' ),
                        'type' => 'bool','context' => ['embed','view','edit'],'readonly' => true,
                    ],
                    'author' => ['description' => $this->__( 'The ID for the author of the template.' ),
                        'type' => 'integer','context' => ['view','edit','embed'],
                    ],
                ]
            ];
            if ( 'tp_template' === $this->_post_type )
                $schema['properties']['is_custom'] = ['description' => $this->__( 'Whether a template is a custom template.' ),
                    'type' => 'bool', 'context' => ['embed','view','edit'],'readonly' => true,];
            if ( 'tp_template_part' === $this->_post_type )
                $schema['properties']['area'] = ['description' => $this->__( 'Where the template part is intended for use (header, footer, etc.)' ),
                    'type' => 'string','context' => ['embed','view','edit'],];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//762
    }
}else die;