<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-6-2022
 * Time: 04:17
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\RestApi\Fields\TP_REST_Meta_Fields;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Traits\Menus\_nav_menu_01;
use TP_Core\Traits\Menus\_nav_menu_02;
use TP_Core\Traits\Theme\_theme_03;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Term;
use TP_Core\Libs\RestApi\TP_REST_Response;
if(ABSPATH){
    class TP_REST_Menus_Controller extends TP_REST_Terms_Controller {
        use _nav_menu_01;
        use _nav_menu_02;
        use _theme_03;
        public function get_items_permissions_check( $request ):string{
            $has_permission = parent::get_items_permissions_check( $request );
            if ( true !== $has_permission ) return $has_permission;
            return $this->_check_has_read_only_access( $request );
        }//35
        public function get_item_permissions_check( $request ):string{
            $has_permission = parent::get_item_permissions_check( $request );
            if ( true !== $has_permission ) return $has_permission;
            return $this->_check_has_read_only_access( $request );
        }//45
        protected function _get_menu_term( $id ){
            $term = $this->_get_term_term($id);
            if ( $this->_init_error( $term ) ) return $term;
            $nav_term = $this->_tp_get_nav_menu_object( $term );
            //if($nav_term instanceof \stdClass ){}//todo
            $nav_term->auto_add = $this->_get_menu_auto_add( $nav_term->term_id );
            return $nav_term;
        }//63
        protected function _check_has_read_only_access( $request ){
            if(!$request) return false;
            if ( $this->_current_user_can( 'edit_theme_options' ) ) return true;
            if ( $this->_current_user_can( 'edit_posts' ) ) return true;
            foreach ( $this->_get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
                if ( $this->_current_user_can( $post_type->cap->edit_posts ) ) return true;
            }
            return new TP_Error('rest_cannot_view',
                $this->__( 'Sorry, you are not allowed to view menus.' ),
                ['status' => $this->_rest_authorization_required_code()]
            );
        }//86
        public function prepare_item_for_response( $term, $request ):string{
            $nav_menu = $this->_tp_get_nav_menu_object( $term );
            $_response = parent::prepare_item_for_response( $nav_menu, $request );
            $response = null;
            if($_response  instanceof TP_REST_Response ){
                $response = $_response;
            }
            $fields = $this->get_fields_for_response( $request );
            $data   = $response->get_data();
            if ( $this->_rest_is_field_included( 'locations', $fields ) )
                $data['locations'] = $this->_get_menu_locations( $nav_menu->term_id );
            if ( $this->_rest_is_field_included( 'auto_add', $fields ) )
                $data['auto_add'] = $this->_get_menu_auto_add( $nav_menu->term_id );
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->add_links( $this->_prepare_links( $term ) );
            return $this->_apply_filters( "rest_prepare_{$this->_taxonomy}", $response, $term, $request );
        }//117
        protected function _prepare_links( $term ):string{
            $links[] = parent::_prepare_links( $term );
            $locations = $this->_get_menu_locations( $term->term_id );
            foreach ( $locations as $location ) {
                $url = $this->_rest_url( sprintf( 'tp/v1/menu-locations/%s', $location ) );
                $links['https://api.w.org/menu-location'][] = ['href' => $url,'embeddable' => true,];
            }
            return $links;
        }//151
        protected function _prepare_item_for_database( $request ):string{
            $prepared_term = parent::_prepare_item_for_database( $request );
            $schema = $this->get_item_schema();
            if ( isset( $request['name'] ) && ! empty( $schema['properties']['name'] ) )
                $prepared_term->{'menu-name'} = $request['name'];
            return $prepared_term;
        }//175
        public function create_item(TP_REST_Request $request ):string{
            if ( isset( $request['parent'] ) ) {
                if ( ! $this->_is_taxonomy_hierarchical( $this->_taxonomy ) )
                    return new TP_Error( 'rest_taxonomy_not_hierarchical', $this->__( 'Cannot set parent term, taxonomy is not hierarchical.' ), array( 'status' => 400 ) );
                $parent = $this->_tp_get_nav_menu_object( (int) $request['parent'] );
                if ( ! $parent )
                    return new TP_Error( 'rest_term_invalid', $this->__( 'Parent term does not exist.' ), array( 'status' => 400 ) );
            }
            $prepared_term = $this->_prepare_item_for_database( $request );
            $_term = $this->_tp_update_nav_menu_object( 0, $this->_tp_slash( (array) $prepared_term ) );
            $term = null;
            if($_term instanceof TP_Error ){
                $term = $_term;
            }//todo
            if ( $this->_init_error( $term ) ) {
                if ( in_array( 'menu_exists', $term->get_error_codes(), true ) ) {
                    $existing_term = $this->_get_term_by( 'name', $prepared_term->{'menu-name'}, $this->_taxonomy );
                    $term->add_data( $existing_term->term_id, 'menu_exists' );
                    $term->add_data(['status'=> 400,'term_id' => $existing_term->term_id,]);
                } else  $term->add_data( array( 'status' => 400 ) );
                return $term;
            }
            $_term = $this->_get_term( $term );
            $term = null;
            if($_term instanceof TP_Term ){
                $term = $_term;
            }
            $this->_do_action( "rest_insert_{$this->_taxonomy}", $term, $request, true );
            $schema = $this->get_item_schema();
            if ( $this->_meta instanceof TP_REST_Meta_Fields && ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                $meta_update = $this->_meta->update_value( $request['meta'], $term->term_id );
                if ( $this->_init_error( $meta_update ) ) return $meta_update;
            }
            $locations_update = $this->_handle_locations( $term->term_id, $request );
            if ( $this->_init_error( $locations_update ) ) return $locations_update;
            $this->_handle_auto_add( $term->term_id, $request );
            $fields_update = $this->_update_additional_fields_for_object( $term, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'view' );
            $this->_do_action( "rest_after_insert_{$this->_taxonomy}", $term, $request, true );
            $response = $this->prepare_item_for_response( $term, $request );
            $_response = $this->_rest_ensure_response( $response );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->set_status( 201 );
            $response->header( 'Location', $this->_rest_url( $this->_namespace . '/' . $this->_rest_base . '/' . $term->term_id ) );
            return $response;
        }//195
        public function update_item(TP_REST_Request $request ):string{
            $term = $this->_get_menu_term( $request['id'] );
            if ( $this->_init_error( $term ) ) return $term;
            if ( isset( $request['parent'] ) ) {
                if ( ! $this->_is_taxonomy_hierarchical( $this->_taxonomy ) )
                    return new TP_Error( 'rest_taxonomy_not_hierarchical', $this->__( 'Cannot set parent term, taxonomy is not hierarchical.' ), array( 'status' => 400 ) );
                $parent = $this->_get_term( (int) $request['parent'], $this->_taxonomy );
                if ( ! $parent ) return new TP_Error( 'rest_term_invalid', $this->__( 'Parent term does not exist.' ), array( 'status' => 400 ) );
            }
            $prepared_term = $this->_prepare_item_for_database( $request );
            if ( ! empty( $prepared_term ) ) {
                if ( ! isset( $prepared_term->{'menu-name'} ) ) $prepared_term->{'menu-name'} = $term->name;
                $update = $this->_tp_update_nav_menu_object( $term->term_id, $this->_tp_slash( (array) $prepared_term ) );
                if ( $this->_init_error( $update ) ) return $update;
            }
            $_term = $this->_get_term( $term->term_id, $this->_taxonomy );
            $term = null;
            if($_term instanceof TP_Term ){
                $term = $_term;
            }
            $this->_do_action( "rest_insert_{$this->_taxonomy}", $term, $request, false );
            $schema = $this->get_item_schema();
            if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
                $meta_update = null;
                if( $this->_meta instanceof TP_REST_Meta_Fields ){
                    $meta_update = $this->_meta->update_value( $request['meta'], $term->term_id );
                }
                if ( $this->_init_error( $meta_update ) ) return $meta_update;
            }
            $locations_update = $this->_handle_locations( $term->term_id, $request );
            if ( $this->_init_error( $locations_update ) ) return $locations_update;
            $this->_handle_auto_add( $term->term_id, $request );
            $fields_update = $this->_update_additional_fields_for_object( $term, $request );
            if ( $this->_init_error( $fields_update ) ) return $fields_update;
            $request->set_param( 'context', 'view' );
            $this->_do_action( "rest_after_insert_{$this->_taxonomy}", $term, $request, false );
            $response = $this->prepare_item_for_response( $term, $request );
            return $this->_rest_ensure_response( $response );
        }//284
        public function delete_item(TP_REST_Request $request ):string{
            $term = $this->_get_menu_term( $request['id'] );
            if ( $this->_init_error( $term ) ) return $term;
            if ( ! $request['force'] )
                return new TP_Error( 'rest_trash_not_supported', sprintf( $this->__( "Menus do not support trashing. Set '%s' to delete." ), 'force=true' ), array( 'status' => 501 ) );
            $request->set_param( 'context', 'view' );
            $_previous = $this->prepare_item_for_response( $term, $request );
            $previous = null;
            if($_previous  instanceof TP_REST_Response ){
                $previous = $_previous;
            }
            $result = $this->_tp_delete_nav_menu( $term );
            if ( ! $result || $this->_init_error( $result ) )
                return new TP_Error( 'rest_cannot_delete', $this->__( 'The menu cannot be deleted.' ), array( 'status' => 500 ) );
            $response = new TP_REST_Response();
            $response->set_data(['deleted' => true, 'previous' => $previous->get_data(),]);
            $this->_do_action( "rest_delete_{$this->_taxonomy}", $term, $response, $request );
            return $response;
        }//363
        protected function _get_menu_auto_add( $menu_id ){
            $nav_menu_option = (array) $this->_get_option( 'nav_menu_options',['auto_add'=> []]);
            return in_array( $menu_id, $nav_menu_option['auto_add'], true );
        }//408
        protected function _handle_auto_add( $menu_id, $request ){
            if ( ! isset( $request['auto_add'] ) ) return true;
            $nav_menu_option = (array) $this->_get_option( 'nav_menu_options', array( 'auto_add' => array() ) );
            if ( ! isset( $nav_menu_option['auto_add'] ) ) $nav_menu_option['auto_add'] = array();
            $auto_add = $request['auto_add'];
            $i = array_search( $menu_id, $nav_menu_option['auto_add'], true );
            if ( $auto_add && false === $i )  $nav_menu_option['auto_add'][] = $menu_id;
            elseif ( ! $auto_add && false !== $i ) array_splice( $nav_menu_option['auto_add'], $i, 1 );
            $update = $this->_update_option( 'nav_menu_options', $nav_menu_option );
            $this->_do_action( 'tp_update_nav_menu', $menu_id );
            return $update;
        }//423
        protected function _get_menu_locations( $menu_id ): array{
            $locations = $this->_get_nav_menu_locations();
            $menu_locations = [];
            foreach ($locations as $location => $assigned_menu_id ) {
                if ( $menu_id === $assigned_menu_id ) $menu_locations[] = $location;
            }
            return $menu_locations;
        }//460
        protected function _handle_locations( $menu_id, $request ){
            if ( ! isset( $request['locations'] ) ) return true;
            $menu_locations = $this->_get_registered_nav_menus();
            $menu_locations = array_keys( $menu_locations );
            $new_locations  = [];
            foreach ( $request['locations'] as $location ) {
                if ( ! in_array( $location, $menu_locations, true ) )
                    return new TP_Error('rest_invalid_menu_location',
                        $this->__( 'Invalid menu location.' ),
                        ['status' => 400, 'location' => $location,] );
                $new_locations[ $location ] = $menu_id;
            }
            $assigned_menu = $this->_get_nav_menu_locations();
            foreach ($assigned_menu as $location => $term_id ) {
                if ( $term_id === $menu_id ) {
                    unset( $assigned_menu[ $location ] );
                }
            }
            $new_assignments = array_merge( $assigned_menu, $new_locations );
            $this->_set_theme_mod( 'nav_menu_locations', $new_assignments );

            return true;
        }//482
        public function get_item_schema(){
            $schema = parent::get_item_schema();
            unset( $schema['properties']['count'], $schema['properties']['link'], $schema['properties']['taxonomy'] );
            $schema['properties']['locations'] = [
                'description' => $this->__( 'The locations assigned to the menu.' ),
                'type' => 'array', 'items' => ['type'=> 'string',], 'context' => ['view', 'edit'],
                'arg_options' => [
                    'validate_callback' => function ( $locations, $request, $param ){
                        $valid = $this->_rest_validate_request_arg( $locations, $request, $param );
                        if ( true !== $valid ) return $valid;
                        $locations = $this->_rest_sanitize_request_arg( $locations, $request, $param );
                        foreach ((array) $locations as $location ) {
                            if ( ! array_key_exists( $location, $this->_get_registered_nav_menus() ) )
                                return new TP_Error('rest_invalid_menu_location',$this->__( 'Invalid menu location.' ),['location' => $location]);
                        }
                        return true;
                    },
                ]
            ];
            $schema['properties']['auto_add'] = [
                'description' => $this->__( 'Whether to automatically add top level pages to this menu.' ),
                'context' => ['view', 'edit'], 'type' => 'boolean',
            ];
            return $schema;
        }//669
    }
}else die;