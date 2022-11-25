<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\RestApi\Fields\TP_REST_Meta_Fields;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Traits\Menus\_nav_menu_02;
if(ABSPATH){
    class TP_REST_Menu_Items_Controller extends TP_REST_Posts_Controller{
        use _nav_menu_02;
        protected function _get_nav_menu_item( $id ) {
            $post = $this->_get_post( $id );
            if ( $this->_init_error( $post ) ) return $post;
            return $this->_tp_setup_nav_menu_item( $post );
        }//27
        public function get_items_permissions_check( $request ):string{
            $has_permission = parent::get_items_permissions_check( $request );
            if ( true !== $has_permission ) return $has_permission;
            return $this->_check_has_read_only_access( $request );
        }//44
        public function get_item_permissions_check( $request ):string{
            $permission_check = parent::get_item_permissions_check( $request );
            if ( true !== $permission_check ) return $permission_check;
            return $this->_check_has_read_only_access( $request );
        }//62
        protected function _check_has_read_only_access( $request ): mixed {
            if ( $this->_current_user_can( 'edit_theme_options' ) ) return true;
            if ( $this->_current_user_can( 'edit_posts' ) ) return true;
            foreach ( $this->_get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
                if ( $this->_current_user_can( $post_type->cap->edit_posts ) ) return true;
            }
            $return = null;
            if($request !== null){
                $return = new TP_Error('rest_cannot_view',
                    $this->__( 'Sorry, you are not allowed to view menu items.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            }//todo
            return $return ?: true;
        }//82
        public function create_item(TP_REST_Request $request ):string {
            if ( ! empty( $request['id'] ) )
                return new TP_Error( 'rest_post_exists', $this->__( 'Cannot create existing post.' ),['status' => BAD_REQUEST]);
            $prepared_nav_item = $this->_prepare_item_for_database( $request );
            if ( $this->_init_error( $prepared_nav_item ) ) return $prepared_nav_item;
            $prepared_nav_item = (array) $prepared_nav_item;
            $nav_menu_item_id = $this->_tp_update_nav_menu_item( $prepared_nav_item['menu-id'], $prepared_nav_item['menu-item-db-id'], $this->_tp_slash( $prepared_nav_item ), false );
            if ($nav_menu_item_id instanceof TP_Error &&  $this->_init_error( $nav_menu_item_id ) ) {
                if ( 'db_insert_error' === $nav_menu_item_id->get_error_code() )
                    $nav_menu_item_id->add_data(['status' => INTERNAL_SERVER_ERROR]);
                else $nav_menu_item_id->add_data(['status' => BAD_REQUEST]);
                return $nav_menu_item_id;
            }
            $nav_menu_item = $this->_get_nav_menu_item( $nav_menu_item_id );
            if ($nav_menu_item instanceof TP_Error && $this->_init_error( $nav_menu_item )) {
                $nav_menu_item->add_data( ['status' => NOT_FOUND] );
                return $nav_menu_item;
            }
            $this->_do_action( 'rest_insert_nav_menu_item', $nav_menu_item, $request, true );
            $schema = $this->get_item_schema();
            if ( $this->_meta instanceof TP_REST_Meta_Fields && ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                $meta_update = $this->_meta->update_value( $request['meta'], $nav_menu_item_id );
                if ( $this->_init_error( $meta_update ) ) return $meta_update;
            }
            $nav_menu_item = $this->_get_nav_menu_item( $nav_menu_item_id );
            $fields_update = $this->_update_additional_fields_for_object( $nav_menu_item, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'edit' );
            $this->_do_action( 'rest_after_insert_nav_menu_item', $nav_menu_item, $request, true );
            $post = $this->_get_post( $nav_menu_item_id );
            $this->_tp_after_insert_post( $post, false, null );
            $response = $this->prepare_item_for_response( $post, $request );
            $_response = $this->_rest_ensure_response( $response );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->set_status( CREATED );
            $response->header( 'Location', $this->_rest_url( sprintf( '%s/%s/%d', $this->_namespace, $this->_rest_base, $nav_menu_item_id ) ) );
            return $response;
        }//113
        public function update_item(TP_REST_Request $request ):string{
            $valid_check = $this->_get_nav_menu_item( $request['id'] );
            if ( $this->_init_error( $valid_check ) ) return $valid_check;
            $post_before       = $this->_get_post( $request['id'] );
            $prepared_nav_item = $this->_prepare_item_for_database( $request );
            if ( $this->_init_error( $prepared_nav_item ) ) return $prepared_nav_item;
            $prepared_nav_item = (array) $prepared_nav_item;
            $_nav_menu_item_id = $this->_tp_update_nav_menu_item( $prepared_nav_item['menu-id'], $prepared_nav_item['menu-item-db-id'], $this->_tp_slash( $prepared_nav_item ), false );
            $nav_menu_item_id = null;
            if($_nav_menu_item_id instanceof TP_Error ){
                $nav_menu_item_id = $_nav_menu_item_id;
            }
            if ( $this->_init_error( $nav_menu_item_id ) ) {
                if ( 'db_update_error' === $nav_menu_item_id->get_error_code() )
                    $nav_menu_item_id->add_data( array( 'status' => INTERNAL_SERVER_ERROR ) );
                else $nav_menu_item_id->add_data( array( 'status' => BAD_REQUEST ) );
                return $nav_menu_item_id;
            }
            $nav_menu_item = $this->_get_nav_menu_item( $nav_menu_item_id );
            if ($nav_menu_item instanceof TP_Error && $this->_init_error( $nav_menu_item ) ) {
                $nav_menu_item->add_data( array( 'status' => NOT_FOUND ) );
                return $nav_menu_item;
            }
            $this->_do_action( 'rest_insert_nav_menu_item', $nav_menu_item, $request, false );
            $schema = $this->get_item_schema();
            if ($this->_meta instanceof TP_REST_Meta_Fields && ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                $meta_update = $this->_meta->update_value( $request['meta'], $nav_menu_item->ID );
                if ( $this->_init_error( $meta_update ) )return $meta_update;
            }
            $post          = $this->_get_post( $nav_menu_item_id );
            $nav_menu_item = $this->_get_nav_menu_item( $nav_menu_item_id );
            $fields_update = $this->_update_additional_fields_for_object( $nav_menu_item, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'edit' );
            $this->_do_action( 'rest_after_insert_nav_menu_item', $nav_menu_item, $request, false );
            $this->_tp_after_insert_post( $post, true, $post_before );
            $response = $this->prepare_item_for_response( $this->_get_post( $nav_menu_item_id ), $request );
            return $this->_rest_ensure_response( $response );
        }//205
        public function delete_item( $request ):string{
            $menu_item = $this->_get_nav_menu_item( $request['id'] );
            if ( $this->_init_error( $menu_item ) ) return $menu_item;
            if ( ! $request['force'] )
                return new TP_Error( 'rest_trash_not_supported', sprintf( $this->__( "Menu items do not support trashing. Set '%s' to delete." ), 'force=true' ), array( 'status' => 501 ) );
            $_previous = $this->prepare_item_for_response( $this->_get_post( $request['id'] ), $request );
            $previous = null;
            if($_previous  instanceof TP_REST_Response ){
                $previous = $_previous;
            }
            $result = $this->_tp_delete_post( $request['id'], true );
            if ( ! $result )
                return new TP_Error( 'rest_cannot_delete', $this->__( 'The post cannot be deleted.' ), array( 'status' => 500 ) );
            $response = new TP_REST_Response();
            $response->set_data(['deleted'  => true, 'previous' => $previous->get_data(),]);
            $this->_do_action( 'rest_delete_nav_menu_item', $menu_item, $response, $request );
            return $response;
        }//279
        protected function _prepare_item_for_database( $request ):string{
            $menu_item_db_id = $request['id'];
            $menu_item_obj   = $this->_get_nav_menu_item( $menu_item_db_id );
            if ( ! $this->_init_error( $menu_item_obj ) ) {
                $position = ( 0 === $menu_item_obj->menu_order ) ? 1 : $menu_item_obj->menu_order;
                $prepared_nav_item = [
                    'menu-item-db-id'       => $menu_item_db_id,
                    'menu-item-object-id'   => $menu_item_obj->object_id,
                    'menu-item-object'      => $menu_item_obj->object,
                    'menu-item-parent-id'   => $menu_item_obj->menu_item_parent,
                    'menu-item-position'    => $position,
                    'menu-item-type'        => $menu_item_obj->type,
                    'menu-item-title'       => $menu_item_obj->title,
                    'menu-item-url'         => $menu_item_obj->url,
                    'menu-item-description' => $menu_item_obj->description,
                    'menu-item-attr-title'  => $menu_item_obj->attr_title,
                    'menu-item-target'      => $menu_item_obj->target,
                    'menu-item-classes'     => $menu_item_obj->classes,
                    // Stored in the database as a string.
                    'menu-item-xfn'         => explode( ' ', $menu_item_obj->xfn ),
                    'menu-item-status'      => $menu_item_obj->post_status,
                    'menu-id'               => $this->_get_menu_id( $menu_item_db_id ),
                ];
            } else {
                $prepared_nav_item = ['menu-id' => 0,'menu-item-db-id' => 0,'menu-item-object-id' => 0,
                    'menu-item-object' => '','menu-item-parent-id' => 0,'menu-item-position' => 1,
                    'menu-item-type' => 'custom','menu-item-title' => '','menu-item-url' => '',
                    'menu-item-description' => '','menu-item-attr-title' => '','menu-item-target' => '',
                    'menu-item-classes' => [],'menu-item-xfn' => [],'menu-item-status' => 'publish',];
            }
            $mapping = ['menu-item-db-id' => 'id', 'menu-item-object-id' => 'object_id',
                'menu-item-object' => 'object','menu-item-parent-id' => 'parent',
                'menu-item-position' => 'menu_order','menu-item-type' => 'type',
                'menu-item-url' => 'url','menu-item-description' => 'description',
                'menu-item-attr-title' => 'attr_title','menu-item-target' => 'target',
                'menu-item-classes' => 'classes','menu-item-xfn' => 'xfn','menu-item-status' => 'status',];
            $schema = $this->get_item_schema();
            foreach ( $mapping as $original => $api_request ) {
                if ( isset( $request[ $api_request ] ) ) $prepared_nav_item[ $original ] = $request[ $api_request ];
            }
            $taxonomy = $this->_get_taxonomy( 'nav_menu' );
            $base= ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
            if ( ! empty( $request[ $base ] ) )
                $prepared_nav_item['menu-id'] = $this->_abs_int( $request[ $base ] );
            if ( ! empty( $schema['properties']['title'] ) && isset( $request['title'] ) ) {
                if ( is_string( $request['title'] ) ) $prepared_nav_item['menu-item-title'] = $request['title'];
                elseif ( ! empty( $request['title']['raw'] ) ) $prepared_nav_item['menu-item-title'] = $request['title']['raw'];
            }
            $error = new TP_Error();
            if ( ! $prepared_nav_item['menu-item-object'] ) {
                if ( 'taxonomy' === $prepared_nav_item['menu-item-type'] ) {
                    $original = $this->_get_term( $this->_abs_int( $prepared_nav_item['menu-item-object-id'] ) );
                    if ( empty( $original ) || $this->_init_error( $original ) )
                        $error->add( 'rest_term_invalid_id', $this->__( 'Invalid term ID.' ),['status' => BAD_REQUEST] );
                    else $prepared_nav_item['menu-item-object'] = $this->_get_term_field( 'taxonomy', $original );
                } elseif ( 'post_type' === $prepared_nav_item['menu-item-type'] ) {
                    $original = $this->_get_post( $this->_abs_int( $prepared_nav_item['menu-item-object-id'] ) );
                    if ( empty( $original ) )  $error->add( 'rest_post_invalid_id', $this->__( 'Invalid post ID.' ),['status' => BAD_REQUEST]);
                     else  $prepared_nav_item['menu-item-object'] = $this->_get_post_type( $original );
                }
            }
            if ( 'post_type_archive' === $prepared_nav_item['menu-item-type'] ) {
                $post_type = $prepared_nav_item['menu-item-object'] ?: false;
                $original  = $this->_get_post_type_object( $post_type );
                if ( ! $original ) $error->add( 'rest_post_invalid_type', $this->__( 'Invalid post type.' ),['status' => BAD_REQUEST]);
            }
            if ( 'custom' === $prepared_nav_item['menu-item-type'] ) {
                if ( '' === $prepared_nav_item['menu-item-title'] )
                    $error->add( 'rest_title_required', $this->__( 'The title is required when using a custom menu item type.' ),['status' => BAD_REQUEST]);
                if ( empty( $prepared_nav_item['menu-item-url'] ) )
                    $error->add( 'rest_url_required', $this->__( 'The url is required when using a custom menu item type.' ),['status' => BAD_REQUEST]);
            }
            if ( $error->has_errors() ) return $error;
            foreach ( array( 'menu-item-xfn', 'menu-item-classes' ) as $key )
                $prepared_nav_item[ $key ] = implode( ' ', $prepared_nav_item[ $key ] );
            if ( 'publish' !== $prepared_nav_item['menu-item-status'] ) $prepared_nav_item['menu-item-status'] = 'draft';
            $prepared_nav_item = (object) $prepared_nav_item;
            return $this->_apply_filters( 'rest_pre_insert_nav_menu_item', $prepared_nav_item, $request );
        }//330
        public function prepare_item_for_response( $item, $request ):string{
            $fields    = $this->get_fields_for_response( $request );
            $menu_item = $this->_get_nav_menu_item( $item->ID );
            $data      = [];
            if ( $this->_rest_is_field_included( 'id', $fields ) ) $data['id'] = $menu_item->ID;
            if ( $this->_rest_is_field_included( 'title', $fields ) ) $data['title'] = [];
            if ( $this->_rest_is_field_included( 'title.raw', $fields ) ) $data['title']['raw'] = $menu_item->title;
            if ( $this->_rest_is_field_included( 'title.rendered', $fields ) ) {
                $this->_add_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
                $title = $this->_apply_filters( 'the_title', $menu_item->title, $menu_item->ID );
                $data['title']['rendered'] = $title;
                $this->_remove_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
            }
            if ( $this->_rest_is_field_included( 'status', $fields ) ) $data['status'] = $menu_item->post_status;
            if ( $this->_rest_is_field_included( 'url', $fields ) ) $data['url'] = $menu_item->url;
            if ( $this->_rest_is_field_included( 'attr_title', $fields ) ) $data['attr_title'] = $menu_item->attr_title;
            if ( $this->_rest_is_field_included( 'description', $fields ) ) $data['description'] = $menu_item->description;
            if ( $this->_rest_is_field_included( 'type', $fields ) ) $data['type'] = $menu_item->type;
            if ( $this->_rest_is_field_included( 'type_label', $fields ) ) $data['type_label'] = $menu_item->type_label;
            if ( $this->_rest_is_field_included( 'object', $fields ) ) $data['object'] = $menu_item->object;
            if ( $this->_rest_is_field_included( 'object_id', $fields ) ) $data['object_id'] = $this->_abs_int( $menu_item->object_id );
            if ( $this->_rest_is_field_included( 'parent', $fields ) ) $data['parent'] = (int) $menu_item->menu_item_parent;
            if ( $this->_rest_is_field_included( 'menu_order', $fields ) ) $data['menu_order'] = (int) $menu_item->menu_order;
            if ( $this->_rest_is_field_included( 'target', $fields ) ) $data['target'] = $menu_item->target;
            if ( $this->_rest_is_field_included( 'classes', $fields ) ) $data['classes'] = (array) $menu_item->classes;
            if ( $this->_rest_is_field_included( 'xfn', $fields ) ) $data['xfn'] = array_map( 'sanitize_html_class', explode( ' ', $menu_item->xfn ) );
            if ( $this->_rest_is_field_included( 'invalid', $fields ) ) $data['invalid'] = (bool) $menu_item->_invalid;
            if ($this->_meta instanceof TP_REST_Meta_Fields && $this->_rest_is_field_included( 'meta', $fields ) ) $data['meta'] = $this->_meta->get_value( $menu_item->ID, $request );
            $taxonomies = $this->_tp_list_filter( $this->_get_object_taxonomies( $this->_post_type, 'objects' ), array( 'show_in_rest' => true ) );
            foreach ( $taxonomies as $taxonomy ) {
                $base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
                if ( $this->_rest_is_field_included( $base, $fields ) ) {
                    $terms = $this->_get_the_terms( $item, $taxonomy->name );
                    if ( ! is_array( $terms ) ) continue;
                    $term_ids = $terms ? array_values( $this->_tp_list_pluck( $terms, 'term_id' ) ) : array();
                    if ( 'nav_menu' === $taxonomy->name ) $data[ $base ] = $term_ids ? array_shift( $term_ids ) : 0;
                    else $data[ $base ] = $term_ids;
                }
            }
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }            $links = $this->_prepare_links( $item );
            $response->add_links( $links );
            if ( ! empty( $links['self']['href'] ) ) {
                $actions = $this->_get_available_actions( $item, $request );
                $self = $links['self']['href'];
                foreach ( $actions as $rel ) $response->add_link( $rel, $self );
            }
            return $this->_apply_filters( 'rest_prepare_nav_menu_item', $response, $menu_item, $request );
        }//495
        protected function _prepare_links( $post ):string{
            $links[]     = parent::_prepare_links( $post );
            $menu_item = $this->_get_nav_menu_item( $post->ID );
            if ( empty( $menu_item->object_id ) ) return $links;
            $path = '';
            $type = '';
            $key  = $menu_item->type;
            if ( 'post_type' === $menu_item->type ) {
                $path = $this->_rest_get_route_for_post( $menu_item->object_id );
                $type = $this->_get_post_type( $menu_item->object_id );
            } elseif ( 'taxonomy' === $menu_item->type ) {
                $path = $this->_rest_get_route_for_term( $menu_item->object_id );
                $type = $this->_get_term_field( 'taxonomy', $menu_item->object_id );
            }
            if ( $path && $type )
                $links['https://api.w.org/menu-item-object'][] = ['href' => $this->_rest_url( $path ),
                    $key => $type,'embeddable' => true,];
            return $links;
        }//648
        protected function _get_schema_links():string{
            $links[]   = parent::_get_schema_links();
            $href    = $this->_rest_url( "{$this->_namespace}/{$this->_rest_base}/{id}" );
            $links[] = ['rel' => 'https://api.w.org/menu-item-object',
                'title' => $this->__( 'Get linked object.' ),'href' => $href,
                'targetSchema' => ['type' => 'object','properties' => ['object' => ['type' => 'integer',],],],
            ];
            return $links;
        }//685
        public function get_item_schema(){
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#','title' => $this->_post_type,'type' => 'object',];
            $schema['properties']['title'] = ['description' => $this->__( 'The title for the object.' ),
                'type' => ['string', 'object'],'context' => ['view', 'edit', 'embed'],
                'properties'  => [
                    'raw'      => ['description' => $this->__( 'Title for the object, as it exists in the database.' ),
                        'type' => 'string','context' => ['edit'],],
                    'rendered' => ['description' => $this->__( 'HTML title for the object, transformed for display.' ),
                        'type' => 'string','context' => ['view', 'edit', 'embed'],'readonly' => true,],
                ],
            ];
            $schema['properties']['id'] = ['description' => $this->__( 'Unique identifier for the object.' ),
                'type' => 'integer','default' => 0,'minimum' => 0,'context' => ['view', 'edit', 'embed'],'readonly' => true,
            ];
            $schema['properties']['type_label'] = ['description' => $this->__( 'Name of type.' ),
                'type'=> 'string','context'=> ['view', 'edit', 'embed'],'readonly'=> true,
            ];
            $schema['properties']['type'] = [
                'description' => $this->__( 'The family of objects originally represented, such as "post_type" or "taxonomy".' ),
                'type' => 'string','enum' => ['taxonomy', 'post_type', 'post_type_archive', 'custom'],
                'context' => ['view', 'edit', 'embed'],'default' => 'custom',
            ];
            $schema['properties']['status'] = ['description' => $this->__( 'A named status for the object.' ),
                'type' => 'string','enum' => array_keys( $this->_get_post_stati( ['internal' => false] ) ),
                'default' => 'publish', 'context' => ['view', 'edit', 'embed'],
            ];
            $schema['properties']['parent'] = ['description' => $this->__( 'The ID for the parent of the object.' ),
                'type' => 'integer','minimum' => 0,'default' => 0,'context' => ['view', 'edit', 'embed'],
            ];
            $schema['properties']['attr_title'] = ['description' => $this->__( 'Text for the title attribute of the link element for this menu item.' ),
                'type' => 'string','context' => ['view', 'edit', 'embed'],'arg_options' => ['sanitize_callback' => 'sanitize_text_field',],
            ];
            $schema['properties']['classes'] = ['description' => $this->__( 'Class names for the link element of this menu item.' ),
                'type' => 'array','items' => ['type' => 'string',],'context' => ['view', 'edit', 'embed'],
                'arg_options' => ['sanitize_callback' => function ( $value ) {
                    return array_map( 'sanitize_html_class', $this->_tp_parse_list( $value ) );
                },],
            ];
            $schema['properties']['description'] = ['description' => $this->__( 'The description of this menu item.' ),
                'type' => 'string','context' => ['view', 'edit', 'embed'],'arg_options' => ['sanitize_callback' => 'sanitize_text_field',],
            ];
            $schema['properties']['menu_order'] = [
                'description' => $this->__( 'The DB ID of the nav_menu_item that is this item\'s menu parent, if any, otherwise 0.' ),
                'context' => ['view', 'edit', 'embed'],'type' => 'integer','minimum' => 1,'default' => 1,
            ];
            $schema['properties']['object'] = ['description' => $this->__( 'The type of object originally represented, such as "category", "post", or "attachment".' ),
                'context' => ['view', 'edit', 'embed'],'type' => 'string','arg_options' => ['sanitize_callback' => 'sanitize_key',],
            ];
            $schema['properties']['object_id'] = [
                'description' => $this->__( 'The database ID of the original object this menu item represents, for example the ID for posts or the term_id for categories.' ),
                'context' => ['view', 'edit', 'embed'],'type' => 'integer','minimum' => 0,'default' => 0,
            ];
            $schema['properties']['target'] = [
                'description' => $this->__( 'The target attribute of the link element for this menu item.' ),
                'type' => 'string','context' => ['view', 'edit', 'embed'],'enum' => ['_blank','',],
            ];
            $schema['properties']['type_label'] = ['description' => $this->__( 'The singular label used to describe this type of menu item.' ),
                'context' => ['view', 'edit', 'embed'],'type' => 'string','readonly' => true,
            ];
            $schema['properties']['url'] = [
                'description' => $this->__( 'The URL to which this menu item points.' ),
                'type' => 'string','format' => 'uri','context' => ['view', 'edit', 'embed'],
                'arg_options' => [
                    'validate_callback' => static function ( $url ) {
                        if ( '' === $url ) return true;
                        if ( (new self('url'))->_esc_url_raw( $url ) ) return true;
                        return new TP_Error('rest_invalid_url',(new self('url'))->__( 'Invalid URL.' ));//todo
                    },
                ],
            ];
            $schema['properties']['xfn'] = [
                'description' => $this->__( 'The XFN relationship expressed in the link of this menu item.' ),
                'type' => 'array','items' => ['type' => 'string',],'context' => ['view', 'edit', 'embed'],
                'arg_options' => ['sanitize_callback' => function ( $value ) {
                    return array_map( 'sanitize_html_class', $this->_tp_parse_list( $value ) );
                },],
            ];
            $schema['properties']['invalid'] = [
                'description' => $this->__( 'Whether the menu item represents an object that no longer exists.' ),
                'context'=> ['view', 'edit', 'embed'],'type'=> 'boolean','readonly'=> true,
            ];
            $taxonomies = $this->_tp_list_filter( $this->_get_object_taxonomies( $this->_post_type, 'objects' ), array( 'show_in_rest' => true ) );
            foreach ( $taxonomies as $taxonomy ) {
                $base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
                $schema['properties'][ $base ] = [
                    'description' => sprintf( $this->__( 'The terms assigned to the object in the %s taxonomy.' ), $taxonomy->name ),
                    'type' => 'array','items' => ['type' => 'integer',],'context' => ['view', 'edit'],
                ];
                if ( 'nav_menu' === $taxonomy->name ) {
                    $schema['properties'][ $base ]['type'] = 'integer';
                    unset( $schema['properties'][ $base ]['items'] );
                }
            }
            if($this->_meta instanceof TP_REST_Meta_Fields ){
                $schema['properties']['meta'] = $this->_meta->get_field_schema();
            }
            $schema_links = $this->_get_schema_links();
            if ( $schema_links ) $schema['links'] = $schema_links;
            return $this->_add_additional_fields_schema( $schema );
        }//712
        public function get_collection_params():array{
            $query_params = parent::get_collection_params();
            $query_params['menu_order'] = ['description' => $this->__( 'Limit result set to posts with a specific menu_order value.' ),
                'type' => 'integer',];
            $query_params['order'] = ['description' => $this->__( 'Order sort attribute ascending or descending.' ),
                'type' => 'string','default' => 'asc','enum' => ['asc', 'desc'],];
            $query_params['orderby'] = ['description' => $this->__( 'Sort collection by object attribute.' ),
                'type' => 'string','default' => 'menu_order','enum' => ['author','date','id','include',
                    'modified','parent','relevance','slug','include_slugs','title','menu_order',],
            ];
            $query_params['per_page']['default'] = 100;
            return $query_params;
        }//934
        protected function _prepare_items_query( $prepared_args = array(), $request = null ):string{
            $query_args = parent::_prepare_items_query( $prepared_args, $request );
            if ( isset( $query_args['orderby'], $request['orderby'] ) ) {
                $orderby_mappings = ['id' => 'ID','include' => 'post__in','slug' => 'post_name',
                    'include_slugs' => 'post_name__in', 'menu_order' => 'menu_order',];
                if ( isset( $orderby_mappings[ $request['orderby'] ] ) )
                    $query_args['orderby'] = $orderby_mappings[ $request['orderby'] ];
            }
            return $query_args;
        }//983
        protected function _get_menu_id( $menu_item_id ){
            $menu_ids = $this->_tp_get_post_terms( $menu_item_id, 'nav_menu', array( 'fields' => 'ids' ) );
            $menu_id  = 0;
            if ( $menu_ids && ! $this->_init_error( $menu_ids ) )
                $menu_id = array_shift( $menu_ids );
            return $menu_id;
        }//1012
    }
}else die;