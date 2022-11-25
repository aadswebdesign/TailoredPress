<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-6-2022
 * Time: 20:26
 */
namespace TP_Core\Libs\RestApi\Search;
use TP_Core\Libs\Queries\TP_Query;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Search_Controller;
use TP_Core\Traits\Filters\_filter_01;

if(ABSPATH){
    class TP_REST_Post_Search_Handler extends TP_REST_Search_Handler{
        use _filter_01;
        public function __construct() {
            $this->_type = 'post';
            $this->_subtypes = array_diff(
                array_values( $this->_get_post_types(['public' => true,'show_in_rest' => true,],'names')),
                ['attachment']
            );
        }
        public function search_items( TP_REST_Request $request ):array {
            $post_types = $request[ TP_REST_Search_Controller::PROP_SUBTYPE ];
            if ( in_array( TP_REST_Search_Controller::TYPE_ANY, $post_types, true ) )
                $post_types = $this->_subtypes;
            $query_args = ['post_type' => $post_types,'post_status' => 'publish','paged' => (int) $request['page'],
                'posts_per_page' => (int) $request['per_page'],'ignore_sticky_posts' => true,'fields' => 'ids',];
            if ( ! empty( $request['search'] ) ) $query_args['s'] = $request['search'];
            $query_args = $this->_apply_filters( 'rest_post_search_query', $query_args, $request );
            $query     = new TP_Query();
            $found_ids = $query->query_main( $query_args );
            $total     = $query->found_posts;
            return [self::RESULT_IDS   => $found_ids, self::RESULT_TOTAL => $total,];
        }
        public function prepare_item( $id, array $fields ):array {
            $post = $this->_get_post( $id );
            $data = [];
            if ( in_array( TP_REST_Search_Controller::PROP_ID, $fields, true ) )
                $data[ TP_REST_Search_Controller::PROP_ID ] = (int) $post->ID;
            if ( in_array( TP_REST_Search_Controller::PROP_TITLE, $fields, true ) ) {
                if ( $this->_post_type_supports( $post->post_type, 'title' ) ) {
                    $this->_add_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
                    $data[ TP_REST_Search_Controller::PROP_TITLE ] = $this->_get_the_title( $post->ID );
                    $this->_remove_filter( 'protected_title_format', array( $this, 'protected_title_format' ) );
                } else $data[ TP_REST_Search_Controller::PROP_TITLE ] = '';
            }
            if ( in_array( TP_REST_Search_Controller::PROP_URL, $fields, true ) )
                $data[ TP_REST_Search_Controller::PROP_URL ] = $this->_get_permalink( $post->ID );
            if ( in_array( TP_REST_Search_Controller::PROP_TYPE, $fields, true ) )
                $data[ TP_REST_Search_Controller::PROP_TYPE ] = $this->_type;
            if ( in_array( TP_REST_Search_Controller::PROP_SUBTYPE, $fields, true ) )
                $data[ TP_REST_Search_Controller::PROP_SUBTYPE ] = $post->post_type;
            return $data;
        }
        public function prepare_item_links( $id ):array {
            $post = $this->_get_post( $id );
            $links = [];
            $item_route = $this->_rest_get_route_for_post( $post );
            if ( ! empty( $item_route ) )
                $links['self'] = ['href' => $this->_rest_url( $item_route ),'embeddable' => true,];
            $links['about'] = ['href' => $this->_rest_url( 'tp/v1/types/' . $post->post_type ),];
            return $links;
        }
        public function protected_title_format():string {
            return '%s';
        }
    }
}else die;