<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\Post\TP_Post_Type;
use TP_Core\Libs\RestApi\TP_REST_Request;
if(ABSPATH){
    class TP_REST_Auto_Saves_Controller extends TP_REST_Revisions_Controller{
        public function __construct($parent_post_type ){
            parent::__construct($parent_post_type);
            $this->_parent_post_type  = $parent_post_type;
            $post_type_object        = $this->_get_post_type_object( $parent_post_type );
            $parent_controller = null;
            if( $post_type_object instanceof TP_Post_Type ){
                $parent_controller = $post_type_object->get_rest_controller();
            }
            if ( ! $parent_controller )$parent_controller = new TP_REST_Posts_Controller($parent_post_type);
            $this->_parent_controller = $parent_controller;
            $this->_revisions_controller = new TP_REST_Revisions_Controller( $parent_post_type );
            $this->_rest_base         = 'autosaves';
            $this->_namespace         = ! empty( $post_type_object->rest_namespace ) ? $post_type_object->rest_namespace : 'tp/v1';
            $this->_parent_base       = ! empty( $post_type_object->rest_base ) ? $post_type_object->rest_base : $post_type_object->name;
        }//50
        public function register_routes(): void{
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_parent_base . '/(?P<id>[\d]+)/' . $this->_rest_base,
                ['args' => ['parent' => ['description' => $this->__( 'The ID for the parent of the autosave.' ),
                            'type' => 'integer',],],
                    ['methods' => TP_GET,
                        'callback' => [$this, 'get_items'],'permission_callback' => [$this, 'get_items_permissions_check'],
                        'args' => $this->get_collection_params(),],
                    ['methods' => TP_GET,'callback' => [$this, 'create_item'],
                        'permission_callback' => [$this, 'create_item_permissions_check'],
                        'args' => $this->_parent_controller->get_endpoint_args_for_item_schema( TP_EDITABLE ),
                    ],'schema' => [$this, 'get_public_item_schema'],
                ]
            );
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_parent_base . '/(?P<parent>[\d]+)/' . $this->_rest_base . '/(?P<id>[\d]+)',
                ['args'=> ['parent' => ['description' => $this->__( 'The ID for the parent of the autosave.' ),'type' => 'integer',],
                        'id'=> ['description' => $this->__( 'The ID for the autosave.' ),'type'=> 'integer', ],
                    ],['methods' => TP_GET,
                        'callback' => [$this, 'get_item'],
                        'permission_callback' => [$this->_revisions_controller, 'get_item_permissions_check'],
                        'args' => ['context' => $this->get_context_param( ['default'=> 'view'] ),],
                    ],'schema' => [$this, 'get_public_item_schema'], ]
            );
        }//70
        protected function _get_parent( $parent_id ){
            return $this->_revisions_controller->_get_parent( $parent_id );
        }//140
        public function get_items_permissions_check( $request ):string{
            $parent = $this->_get_parent( $request['id'] );
            if ( $this->_init_error( $parent ) ) return $parent;
            if ( ! $this->_current_user_can( 'edit_post', $parent->ID ) )
                return new TP_Error('rest_cannot_read',
                    $this->__( 'Sorry, you are not allowed to view autosaves of this post.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }
        public function create_item_permissions_check(TP_REST_Request $request ):string {
            $id = $request->get_param( 'id' );
            if ( empty( $id ) )
                return new TP_Error('rest_post_invalid_id', $this->__( 'Invalid item ID.' ), ['status' => NOT_FOUND]);
            return $this->_parent_controller->update_item_permissions_check( $request );
        }
        public function create_item(TP_REST_Request $request ):string {
            if ( ! defined( 'DOING_AUTOSAVE' ) ) define( 'DOING_AUTOSAVE', true );
            $post = $this->_get_post( $request['id'] );
            if($post  instanceof \stdClass ){}//todo
            if ( $this->_init_error( $post ) ) return $post;
            $prepared_post     = $this->_parent_controller->_prepare_item_for_database( $request );
            $prepared_post->ID = $post->ID;
            $user_id           = $this->_get_current_user_id();
            if ( ( 'draft' === $post->post_status || 'auto-draft' === $post->post_status ) && $post->post_author === $user_id )
                $autosave_id = $this->_tp_update_post( $this->_tp_slash( (array) $prepared_post ), true );
            else $autosave_id = $this->create_post_autosave( (array) $prepared_post );
            if ( $this->_init_error( $autosave_id ) ) return $autosave_id;
            $autosave = $this->_get_post( $autosave_id );
            $request->set_param( 'context', 'edit' );
            $response = $this->prepare_item_for_response( $autosave, $request );
            $response = $this->_rest_ensure_response( $response );
            return $response;
        }//207
        public function get_item(TP_REST_Request $request ):string{
            $parent_id = (int) $request->get_param( 'parent' );
            if ( $parent_id <= 0 )
                return new TP_Error('rest_post_invalid_id',$this->__( 'Invalid post parent ID.' ), ['status' => NOT_FOUND]);
            $autosave = $this->_tp_get_post_autosave( $parent_id );
            if ( ! $autosave )
                return new TP_Error('rest_post_no_autosave', $this->__( 'There is no autosave revision for this post.' ), ['status' => NOT_FOUND]);
            return $this->prepare_item_for_response( $autosave, $request );
        }//253
        public function get_items($request ):string{
            $parent = $this->_get_parent( $request['id'] );
            if ( $this->_init_error( $parent ) ) return $parent;
            $response  = [];
            $parent_id = $parent->ID;
            $revisions = $this->_tp_get_post_revisions( $parent_id, array( 'check_enabled' => false ) );
            foreach ($revisions as $revision ) {
                if ( false !== strpos( $revision->post_name, "{$parent_id}-autosave" ) ) {
                    $data       = $this->prepare_item_for_response( $revision, $request );
                    $response[] = $this->prepare_response_for_collection( $data );
                }
            }
            return $this->_rest_ensure_response( $response );
        }//288
        public function get_item_schema(){
            if ( $this->_schema ) return $this->_add_additional_fields_schema( $this->_schema );
            $schema = $this->_revisions_controller->get_item_schema();
            $schema['properties']['preview_link'] = ['description' => $this->__( 'Preview link for the post.' ),
                'type' => 'string','format' => 'uri','context' => ['edit'],'readonly' => true,];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//316
        public function create_post_autosave( $post_data ){
            $post_id = (int) $post_data['ID'];
            $post    = $this->_get_post( $post_id );
            if ( $this->_init_error( $post ) ) return $post;
            $user_id = $this->_get_current_user_id();
            $old_autosave = $this->_tp_get_post_autosave( $post_id, $user_id );
            if($old_autosave  instanceof TP_Post ){}//todo
            if ( $old_autosave ) {
                $new_autosave  = $this->_tp_post_revision_data( $post_data, true );
                $new_autosave['ID'] = $old_autosave->ID;
                $new_autosave['post_author'] = $user_id;
                $autosave_is_different = false;
                foreach ( array_intersect( array_keys( $new_autosave ), array_keys( $this->_tp_post_revision_fields( $post ) ) ) as $field ) {
                    if ( $this->_normalize_whitespace( $new_autosave[ $field ] ) !== $this->_normalize_whitespace( $post->$field ) ) {
                        $autosave_is_different = true;
                        break;
                    }
                }
                if ( ! $autosave_is_different ) {
                    $this->_tp_delete_post_revision( $old_autosave->ID );
                    return new TP_Error('rest_autosave_no_changes',
                        $this->__( 'There is nothing to save. The autosave and the post content are the same.' ),
                        ['status' => BAD_REQUEST]);
                }
                $this->_do_action( 'tp_creating_autosave', $new_autosave );
                return $this->_tp_update_post( $this->_tp_slash( $new_autosave ) );
            }
            return $this->_tp_put_post_revision( $post_data, true );
        }//346
        public function prepare_item_for_response( $item, $request ):string{
            $post     = $item;
            $response = $this->_revisions_controller->prepare_item_for_response( $post, $request );
            $fields = $this->get_fields_for_response( $request );
            if ( in_array( 'preview_link', $fields, true ) ) {
                $parent_id          = $this->_tp_is_post_autosave( $post );
                $preview_post_id    = false === $parent_id ? $post->ID : $parent_id;
                $preview_query_args = [];
                if ( false !== $parent_id ) {
                    $preview_query_args['preview_id']    = $parent_id;
                    $preview_query_args['preview_nonce'] = $this->_tp_create_nonce( 'post_preview_' . $parent_id );
                }
                $response->data['preview_link'] = $this->_get_preview_post_link( $preview_post_id, $preview_query_args );
            }
            $context        = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $response->data = $this->_add_additional_fields_to_object( $response->data, $request );
            $response->data = $this->filter_response_by_context( $response->data, $context );
            return $this->_apply_filters( 'rest_prepare_autosave', $response, $post, $request );
        }//405
        public function get_collection_params():array{
            return ['context' => $this->get_context_param(['default' => 'view']),];
        }//450
    }
}else die;