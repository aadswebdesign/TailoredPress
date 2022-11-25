<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-6-2022
 * Time: 04:17
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Traits\Menus\_nav_menu_01;
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_REST_Menu_Locations_Controller extends TP_REST_Controller{
        use _nav_menu_01;
        public function __construct() {
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'menu-locations';
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base . '/(?P<location>[\w-]+)',
                ['args'=> ['location' => ['description' => $this->__( 'An alphanumeric identifier for the menu location.' ),'type' => 'string',],],
                    ['methods' => TP_GET,'callback' => [$this, 'get_item'],'permission_callback' => [$this, 'get_item_permissions_check'],
                        'args' => ['context' => $this->get_context_param(['default' => 'view']),],
                    ],'schema' => [$this, 'get_public_item_schema'],
                ]
            );
        }//36
        public function get_items_permissions_check( $request ):string{
            if ( ! $this->_current_user_can( 'edit_theme_options' ) )
                return new TP_Error(
                    'rest_cannot_view',
                    $this->__( 'Sorry, you are not allowed to view menu locations.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }//82
        public function get_items($request ):string{
            $data = [];
            foreach ( (array)$this->_get_registered_nav_menus() as $name => $description ) {
                $location = new \stdClass();
                $location->name        = $name;
                $location->description = $description;
                $location      = $this->prepare_item_for_response( $location, $request );
                $data[ $name ] = $this->prepare_response_for_collection( $location );
            }
            return $this->_rest_ensure_response( $data );
        }//102
        public function get_item_permissions_check( $request ):string{
            if ( ! $this->_current_user_can( 'edit_theme_options' ) )
                return new TP_Error('rest_cannot_view',
                    $this->__( 'Sorry, you are not allowed to view menu locations.' ),
                    ['status' => $this->_rest_authorization_required_code()]);
            return true;
        }//125
        public function get_item( $request ):string{
            $registered_menus = $this->_get_registered_nav_menus();
            if ( ! array_key_exists( $request['location'], $registered_menus ) )
                return new TP_Error( 'rest_menu_location_invalid', $this->__( 'Invalid menu location.' ), array( 'status' => 404 ) );
            $location              = new \stdClass();
            $location->name        = $request['location'];
            $location->description = $registered_menus[ $location->name ];
            $data = $this->prepare_item_for_response( $location, $request );
            return $this->_rest_ensure_response( $data );
        }//145
        public function prepare_item_for_response( $item, $request ):string{
            $location  = $item;
            $locations = $this->_get_nav_menu_locations();
            $menu      = $locations[ $location->name ] ?? 0;
            $fields = $this->get_fields_for_response( $request );
            $data   = [];
            if ( $this->_rest_is_field_included( 'name', $fields ) ) $data['name'] = $location->name;
            if ( $this->_rest_is_field_included( 'description', $fields ) ) $data['description'] = $location->description;
            if ( $this->_rest_is_field_included( 'menu', $fields ) ) $data['menu'] = $menu;
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->add_links( $this->_prepare_links( $location ) );
            return $this->_apply_filters( 'rest_prepare_menu_location', $response, $location, $request );
        }//169
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $schema = ['$schema'=> 'http://json-schema.org/draft-04/schema#',
                'title'=> 'menu-location','type'=> 'object',
                'properties' => [
                    'name' => ['description' => $this->__( 'The name of the menu location.' ),
                        'type' => 'string','context' => ['embed','view','edit'],'readonly' => true,
                    ],
                    'description' => ['description' => $this->__( 'The description of the menu location.' ),
                        'type' => 'string','context' => ['embed','view','edit'],'readonly' => true,
                    ],
                    'menu' => ['description' => $this->__( 'The ID of the assigned menu.' ),
                        'type' => 'integer','context' => ['embed','view','edit'],'readonly' => true,
                    ],
                ]
            ];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//217
        public function get_collection_params():array{
            return ['context' => $this->get_context_param( array( 'default' => 'view' ) ),];
        }//258
        protected function _prepare_links( $location  ): array{
            $base = sprintf( '%s/%s', $this->_namespace, $this->_rest_base );
            $links = [
                'self' => ['href' => $this->_rest_url( $this->_trailingslashit( $base ) . $location->name )],
                'collection' => ['href' => $this->_rest_url( $base )],
            ];
            $locations = $this->_get_nav_menu_locations();
            $menu      = $locations[ $location->name ] ?? 0;
            if ( $menu ) {
                $path = $this->_rest_get_route_for_term( $menu );
                if ( $path ) {
                    $url = $this->_rest_url( $path );
                    $links['https://api.w.org/menu'][] = ['href' => $url,'embeddable' => true,];
                }
            }
            return $links;
        }//272
    }
}else die;