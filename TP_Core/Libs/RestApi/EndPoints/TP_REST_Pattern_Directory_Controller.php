<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Libs\RestApi\TP_REST_Response;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_REST_Pattern_Directory_Controller extends TP_REST_Controller {
        public function __construct() {
            $this->_namespace     = 'tp/v1';
            $this->_rest_base = 'pattern-directory';
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,
                '/' . $this->_rest_base . '/patterns',
                [['methods' => TP_GET,'callback' => [ $this, 'get_items'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params(),],'schema' => [$this, 'get_public_item_schema'],
                ]
            );
        }//37
        public function get_items_permissions_check( $request ):string{
            if ( $this->_current_user_can( 'edit_posts' ) ) return true;
            foreach ( $this->_get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
                if ( $this->_current_user_can( $post_type->cap->edit_posts ) ) return true;
            }
            return new TP_Error('rest_pattern_directory_cannot_view',
                $this->__( 'Sorry, you are not allowed to browse the local block pattern directory.' ),
                ['status' => $this->_rest_authorization_required_code()]
            );
        }//61
        public function get_items($request ):string{
            $query_args = ['locale' => $this->_get_user_locale(),'tp-version' => TP_VERSION,];
            $category_id = $request['category'];
            $keyword_id  = $request['keyword'];
            $search_term = $request['search'];
            if ( $category_id ) $query_args['pattern-categories'] = $category_id;
            if ( $keyword_id ) $query_args['pattern-keywords'] = $keyword_id;
            if ( $search_term ) $query_args['search'] = $search_term;
            $transient_key = 'tp_remote_block_patterns_' . md5( implode( '-', $query_args ) );
            $raw_patterns = $this->_get_site_transient( $transient_key );
            if ( ! $raw_patterns ) {
                $api_url = $this->_add_query_arg(array_map( 'rawurlencode', $query_args ),'http://api.wordpress.org/patterns/1.0/');
                if ( $this->_tp_http_supports( array( 'ssl' ) ) )
                    $api_url = $this->_set_url_scheme( $api_url, 'https' );
                $cache_ttl      = 5;
                $tp_org_response = $this->_tp_remote_get( $api_url );
                $raw_patterns   = json_decode( $this->_tp_remote_retrieve_body( $tp_org_response ),[]);
                if ( $this->_init_error( $tp_org_response ) ) {
                    $raw_patterns = $tp_org_response;
                } elseif ( ! is_array( $raw_patterns ) ) {
                    $raw_patterns = new TP_Error(
                        'pattern_api_failed',
                        sprintf(
                            $this->__( 'An unexpected error occurred. Something may be wrong with todo or this server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.' ),
                            $this->__( 'todo' )
                        ),
                        ['response' => $this->_tp_remote_retrieve_body( $tp_org_response ),]
                    );
                } else  $cache_ttl = HOUR_IN_SECONDS;
                $this->_set_site_transient( $transient_key, $raw_patterns, $cache_ttl );
            }
            if ( $this->_init_error( $raw_patterns ) ) {
                $raw_patterns->add_data( ['status' => INTERNAL_SERVER_ERROR] );
                return $raw_patterns;
            }
            $response = [];
            if ( $raw_patterns ) {
                foreach ( $raw_patterns as $pattern )
                    $response[] = $this->prepare_response_for_collection($this->prepare_item_for_response( $pattern, $request ));
            }
            return new TP_REST_Response( $response );
        }//87
        public function prepare_item_for_response( $item, $request ):string{
            $raw_pattern      = $item;
            $prepared_pattern = [
                'id'             => $this->_abs_int( $raw_pattern->id ),
                'title'          => $this->_sanitize_text_field( $raw_pattern->title->rendered ),
                'content'        => $this->_tp_kses_post( $raw_pattern->pattern_content ),
                'categories'     => array_map( 'sanitize_title', $raw_pattern->category_slugs ),
                'keywords'       => array_map( 'sanitize_title', $raw_pattern->keyword_slugs ),
                'description'    => $this->_sanitize_text_field( $raw_pattern->meta->wpop_description ),
                'viewport_width' => $this->_abs_int( $raw_pattern->meta->tpop_viewport_width ),
            ];
            $prepared_pattern = $this->_add_additional_fields_to_object( $prepared_pattern, $request );
            $response = new TP_REST_Response( $prepared_pattern );
            return $this->_apply_filters( 'rest_prepare_block_pattern', $response, $raw_pattern, $request );
        }//208
        public function get_item_schema(){
            if ( $this->_schema ) return $this->_add_additional_fields_schema( $this->_schema );
            $this->_schema = ['$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => 'pattern-directory-item','type' => 'object',
                'properties' => [
                    'id' => ['description' => $this->__( 'The pattern ID.' ),'type' => 'integer',
                        'minimum' => 1,'context' => ['view', 'embed'],
                    ],
                    'title' => ['description' => $this->__( 'The pattern title, in human readable format.' ),
                        'type' => 'string','minLength' => 1,'context' => ['view', 'embed'],
                    ],
                    'content' => ['description' => $this->__( 'The pattern content.' ),'type' => 'string',
                        'minLength' => 1,'context' => ['view', 'embed'],
                    ],
                    'categories' => ['description' => $this->__( "The pattern's category slugs." ),'type' => 'array',
                        'uniqueItems' => true,'items' => ['type' => 'string'],'context' => ['view', 'embed'],
                    ],
                    'keywords' => ['description' => $this->__( "The pattern's keyword slugs." ),'type' => 'array',
                        'uniqueItems' => true,'items' => ['type' => 'string'],'context' => ['view', 'embed'],
                    ],
                    'description' => ['description' => $this->__( 'A description of the pattern.' ),
                        'type' => 'string','minLength' => 1,'context' => ['view', 'embed'],
                    ],
                    'viewport_width' => ['description' => $this->__( 'The preferred width of the viewport when previewing a pattern, in pixels.' ),
                        'type' => 'integer','context' => ['view', 'embed'],
                    ],
                ],
            ];
            return $this->_add_additional_fields_schema( $this->_schema );
        }//244
        public function get_collection_params():array{
            $query_params = parent::get_collection_params();
            unset( $query_params['page'], $query_params['per_page'] );
            $query_params['search']['minLength'] = 1;
            $query_params['context']['default']  = 'view';
            $query_params['category'] = ['description' => $this->__( 'Limit results to those matching a category ID.' ),
                'type' => 'integer','minimum' => 1,];
            $query_params['keyword'] = ['description' => $this->__( 'Limit results to those matching a keyword ID.' ),
                'type' => 'integer','minimum' => 1,];
            return $this->_apply_filters( 'rest_pattern_directory_collection_params', $query_params );
        }//316
    }
}else die;