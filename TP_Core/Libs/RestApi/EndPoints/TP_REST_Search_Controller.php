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
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Libs\RestApi\Search\TP_REST_Search_Handler;
if(ABSPATH){
    class TP_REST_Search_Controller extends TP_REST_Controller {
        public const PROP_ID = 'id';
        public const PROP_TITLE = 'title';
        public const PROP_URL = 'url';
        public const PROP_TYPE = 'type';
        public const PROP_SUBTYPE = 'subtype';
        public const TYPE_ANY = 'any';
        protected $_search_handlers = [];
        public function __construct( array $search_handlers ){
            $this->_namespace = 'tp/v1';
            $this->_rest_base = 'search';
            foreach ( $search_handlers as $search_handler ) {
                if ( ! $search_handler instanceof TP_REST_Search_Handler ) {
                    $this->_doing_it_wrong( __METHOD__,
                        sprintf( $this->__( 'REST search handlers must extend the %s class.' ), 'TP_REST_Search_Handler' ),
                        '0.0.1');
                    continue;
                }
                $this->_search_handlers[ $search_handler->get_type() ] = $search_handler;
            }
        }//66
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base,
                [['methods' => TP_GET,'callback' => [$this, 'get_items'],'permission_callback' => [$this, 'get_items_permission_check'],
                    'args' => $this->get_collection_params(),], 'schema' => [$this, 'get_public_item_schema'],]
            );
        }//91
        public function get_items_permissions_check( $request ):string{
            return true;
        }//115
        public function get_items(TP_REST_Request $request ):string{
            $handler = $this->_get_search_handler( $request );
            if ( $this->_init_error( $handler ) ) return $handler;
            $result = $handler->search_items($request );
            if ( ! isset( $result[ TP_REST_Search_Handler::RESULT_IDS ],$result[ TP_REST_Search_Handler::RESULT_TOTAL ] ) || ! is_array( $result[ TP_REST_Search_Handler::RESULT_IDS ] ))
                return new TP_Error('rest_search_handler_error',
                    $this->__( 'Internal search handler error.' ),
                    ['status' => INTERNAL_SERVER_ERROR]);
            $ids = $result[ TP_REST_Search_Handler::RESULT_IDS ];
            $results = [];
            foreach ( $ids as $id ) {
                $data      = $this->prepare_item_for_response( $id, $request );
                $results[] = $this->prepare_response_for_collection( $data );
            }
            $total     = (int) $result[ TP_REST_Search_Handler::RESULT_TOTAL ];
            $page      = (int) $request['page'];
            $per_page  = (int) $request['per_page'];
            $max_pages = ceil( $total / $per_page );
            if ( $page > $max_pages && $total > 0 )
                return new TP_Error('rest_search_invalid_page_number',
                    $this->__( 'The page number requested is larger than the number of pages available.' ),
                    ['status' => BAD_REQUEST]
                );
            $_response = $this->_rest_ensure_response( $results );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $response->header( 'X-TP-Total', $total );
            $response->header( 'X-TP-TotalPages', $max_pages );
            $request_params = $request->get_query_params();
            $base           = $this->_add_query_arg( $this->_url_encode_deep( $request_params ), $this->_rest_url( sprintf( '%s/%s', $this->_namespace, $this->_rest_base ) ) );
            if ( $page > 1 ) {
                $prev_link = $this->_add_query_arg( 'page', $page - 1, $base );
                $response->link_header( 'prev', $prev_link );
            }
            if ( $page < $max_pages ) {
                $next_link = $this->_add_query_arg( 'page', $page + 1, $base );
                $response->link_header( 'next', $next_link );
            }
            return $response;
        }//127
        public function prepare_item_for_response( $item, $request ):string{
            $item_id = $item;
            $handler = $this->_get_search_handler( $request );
            if ( $this->_init_error( $handler ) ) return new TP_REST_Response();
            $fields = $this->get_fields_for_response( $request );
            $data = $handler->prepare_item( $item_id, $fields );
            $data = $this->_add_additional_fields_to_object( $data, $request );
            $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
            $data    = $this->filter_response_by_context( $data, $context );
            $_response = $this->_rest_ensure_response( $data );
            $response = null;
            if( $_response instanceof TP_REST_Response ){
                $response = $_response;
            }
            $links = $handler->prepare_item_links( $item_id );
            $links['collection'] = ['href' => $this->_rest_url( sprintf( '%s/%s', $this->_namespace, $this->_rest_base ) ),];
            $response->add_links( $links );
            return $response;
        }//195
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $types    = [];
            $subtypes = [];
            foreach ( $this->_search_handlers as $search_handler ) {
                $types[]  = $search_handler->get_type();
                $subtypes = $this->_tp_array_merge($subtypes, $search_handler->get_subtypes());
            }
            $types    = array_unique( $types );
            $subtypes = array_unique( $subtypes );
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => 'search-result','type' => 'object',
                'properties' => [
                    self::PROP_ID => ['description' => $this->__( 'Unique identifier for the object.' ),
                        'type' => ['integer','string'],'context' => ['view','embed'],'readonly' => true,
                    ],
                    self::PROP_TITLE => ['description' => $this->__( 'The title for the object.' ),
                        'type' => 'string','context' => ['view','embed'],'readonly' => true,
                    ],
                    self::PROP_URL => ['description' => $this->__( 'URL to the object.' ),
                        'type' => 'string','format' => 'uri','context' => ['view','embed'],'readonly' => true,
                    ],
                    self::PROP_TYPE => ['description' => $this->__( 'Object type.' ),
                        'type' => 'string','enum' => $types,'context' => ['view','embed'],'readonly' => true,
                    ],
                    self::PROP_SUBTYPE => ['description' => $this->__( 'Object subtype.' ),
                        'type' => 'string','enum' => $subtypes,'context' => ['view','embed'],'readonly' => true,
                    ],
                ]
            ];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//229
        public function get_collection_params():array{
            $types    = [];
            $subtypes = [];
            foreach ( $this->_search_handlers as $search_handler ) {
                $types[]  = $search_handler->get_type();
                $subtypes = $this->_tp_array_merge($subtypes, $search_handler->get_subtypes());
            }
            $types    = array_unique( $types );
            $subtypes = array_unique( $subtypes );
            $query_params = parent::get_collection_params();
            $query_params['context']['default'] = 'view';
            $query_params[ self::PROP_TYPE ] = ['default' => $types[0],
                'description' => $this->__( 'Limit results to items of an object type.' ),
                'type' => 'string','enum' => $types,];
            $query_params[ self::PROP_SUBTYPE ] = ['default' => self::TYPE_ANY,
                'description' => $this->__( 'Limit results to items of one or more object subtypes.' ),
                'type' => 'array','items' => ['enum' => array_merge( $subtypes, array( self::TYPE_ANY ) ),'type' => 'string',],
                'sanitize_callback' => [$this, 'sanitize_subtypes'],];
            return $query_params;
        }//298
        public function sanitize_subtypes( $subtypes, $request, $parameter ){
            $subtypes = $this->_tp_parse_slug_list( $subtypes );
            $subtypes = $this->_rest_parse_request_arg( $subtypes, $request, $parameter );
            if ( $this->_init_error( $subtypes ) ) return $subtypes;
            if ( in_array( self::TYPE_ANY, $subtypes, true ) ) return array( self::TYPE_ANY );
            $handler = $this->_get_search_handler( $request );
            if ( $this->_init_error( $handler ) ) return $handler;
            return array_intersect( $subtypes, $handler->get_subtypes() );
        }//345
        protected function _get_search_handler(TP_REST_Request $request ){
            $type = $request->get_param( self::PROP_TYPE );
            if ( ! $type || ! isset( $this->_search_handlers[ $type ] ) )
                return new TP_Error('rest_search_invalid_type',
                    $this->__( 'Invalid type parameter.' ),
                    ['status' => BAD_REQUEST]);
            return $this->_search_handlers[ $type ];
        }//374
    }
}else die;