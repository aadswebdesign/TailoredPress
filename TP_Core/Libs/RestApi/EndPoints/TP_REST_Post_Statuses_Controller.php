<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\RestApi\TP_REST_Response;
if(ABSPATH){
    class TP_REST_Post_Statuses_Controller extends TP_REST_Controller{
        public function __construct() {
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'statuses';
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace, '/' . $this->_rest_base,
                [['methods' => TP_GET,'callback' => [$this, 'get_items'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params(),],'schema' => [$this, 'get_public_item_schema'],
                ]
            );
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base . '/(?P<status>[\w-]+)',
                ['args' => ['status' => ['description' => $this->__( 'An alphanumeric identifier for the status.' ),'type' => 'string',],],
                    ['methods' => TP_GET,'callback' => [$this, 'get_item'],
                        'permission_callback' => [$this, 'get_item_permissions_check'],
                        'args' => [ 'context' => $this->get_context_param(['default' => 'view']),],
                    ],'schema' =>[$this, 'get_public_item_schema'],
                ]
            );
        }//36
        public function get_items_permissions_check( $request ):string{
            if ( 'edit' === $request['context'] ) {
                $types = $this->_get_post_types( array( 'show_in_rest' => true ), 'objects' );
                foreach ( $types as $type ) {
                    if ($this->_current_user_can($type->cap->edit_posts)) return true;
                }
                return new TP_Error('rest_cannot_view',
                    $this->__( 'Sorry, you are not allowed to manage post statuses.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            }
            return true;
        }//83
        public function get_items($request ):string{
            $data              = [];
            $statuses          = $this->_get_post_stati(['internal'=>false], 'object' );
            $statuses['trash'] = $this->_get_post_status_object( 'trash' );
            foreach ( $statuses as $slug => $obj ) {
                $ret = $this->_check_read_permission( $obj );
                if ( ! $ret ) continue;
                $status             = $this->prepare_item_for_response( $obj, $request );
                $data[ $obj->name ] = $this->prepare_response_for_collection( $status );
            }
            return $this->_rest_ensure_response( $data );
        }//111
        public function get_item_permissions_check( $request ):string{
            $status = $this->_get_post_status_object( $request['status'] );
            if ( empty( $status ) )
                return new TP_Error('rest_status_invalid',
                    $this->__( 'Invalid status.' ),['status' => NOT_FOUND]);
            $check = $this->_check_read_permission( $status );
            if ( ! $check )
                return new TP_Error('rest_cannot_read_status',
                    $this->__( 'Cannot view status.' ),
                    ['status' => $this->_rest_authorization_required_code()]
                );
            return true;
        }//138
        protected function _check_read_permission( $status ): bool{
            if ( true === $status->public ) return true;
            if ( false === $status->internal || 'trash' === $status->name ) {
                $types = $this->_get_post_types( array( 'show_in_rest' => true ), 'objects' );
                foreach ( $types as $type ) {
                    if ( $this->_current_user_can( $type->cap->edit_posts ) ) return true;
                }
            }
            return false;
        }//170
        public function get_item( $request ):string{
            $obj = $this->_get_post_status_object( $request['status'] );
            if ( empty( $obj ) )
                return new TP_Error('rest_status_invalid',
                    $this->__( 'Invalid status.' ),['status' => NOT_FOUND]);
            $data = $this->prepare_item_for_response( $obj, $request );
            return $this->_rest_ensure_response( $data );
        }//196
        public function prepare_item_for_response( $item, $request ):string{
            $status = $item;
            $fields = $this->get_fields_for_response( $request );
            $data   = [];
            if ( in_array( 'name', $fields, true ) ) $data['name'] = $status->label;
            if ( in_array( 'private', $fields, true ) ) $data['private'] = (bool) $status->private;
            if ( in_array( 'protected', $fields, true ) ) $data['protected'] = (bool) $status->protected;
            if ( in_array( 'public', $fields, true ) ) $data['public'] = (bool) $status->public;
            if ( in_array( 'queryable', $fields, true ) ) $data['queryable'] = (bool) $status->publicly_queryable;
            if ( in_array( 'show_in_list', $fields, true ) ) $data['show_in_list'] = (bool) $status->show_in_admin_all_list;
            if ( in_array( 'slug', $fields, true ) ) $data['slug'] = $status->name;
            if ( in_array( 'date_floating', $fields, true ) ) $data['date_floating'] = $status->date_floating;
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->_add_additional_fields_to_object( $data, $request );
            $data    = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }            $rest_url = $this->_rest_url( $this->_rest_get_route_for_post_type_items( 'post' ) );
            if ( 'publish' === $status->name ) $response->add_link( 'archives', $rest_url );
            else $response->add_link( 'archives', $this->_add_query_arg( 'status', $status->name, $rest_url ) );
            return $this->_apply_filters( 'rest_prepare_status', $response, $status, $request );
        }//222
        public function get_item_schema(){
            if ( $this->_schema ) return $this->_add_additional_fields_schema( $this->_schema );
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => 'status','type' => 'object',
                'properties' => [
                    'name' => ['description' => $this->__( 'The title for the status.' ),
                        'type' => 'string','context' => ['embed','view','edit'],'readonly' => true,
                    ],
                    'private' => ['description' => $this->__( 'Whether posts with this status should be private.' ),
                        'type' => 'boolean','context' => ['edit'],'readonly' => true,
                    ],
                    'protected' => ['description' => $this->__( 'Whether posts with this status should be protected.' ),
                        'type' => 'boolean','context' => ['edit'],'readonly' => true,
                    ],
                    'public' => ['description' => $this->__( 'Whether posts of this status should be shown in the front end of the site.' ),
                        'type' => 'boolean','context' => ['view','edit'],'readonly' => true,
                    ],
                    'queryable' => ['description' => $this->__( 'Whether posts with this status should be publicly-queryable.' ),
                        'type' => 'boolean','context' => ['view','edit'],'readonly' => true,
                    ],
                    'show_in_list'  => ['description' => $this->__( 'Whether to include posts in the edit listing for their post type.' ),
                        'type' => 'boolean','context' => ['edit'],'readonly' => true,
                    ],
                    'slug' => ['description' => $this->__( 'An alphanumeric identifier for the status.' ),
                        'type' => 'string','context' => ['embed','view','edit'],'readonly' => true,
                    ],
                    'date_floating' => ['description' => $this->__( 'Whether posts of this status may have floating published dates.' ),
                        'type' => 'boolean','context' => ['view','edit'],'readonly' => true,
                    ],
                ]
            ];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//294
        public function get_collection_params():array{
            return ['context' => $this->get_context_param( array( 'default' => 'view' ) )];
        }//367
    }
}else die;