<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-6-2022
 * Time: 20:26
 */
namespace TP_Core\Libs\RestApi\Search;
use TP_Core\Libs\Queries\TP_Term_Query;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Search_Controller;
use TP_Core\Traits\Filters\_filter_01;

if(ABSPATH){
    class TP_REST_Term_Search_Handler extends TP_REST_Search_Handler{
        use _filter_01;
        public function __construct() {
            $this->_type = 'term';
            $this->_subtypes = array_values(
                $this->_get_taxonomies(['public' => true,'show_in_rest' => true,],'names')
            );
        }
        public function search_items( TP_REST_Request $request ):array {
            $taxonomies = $request[ TP_REST_Search_Controller::PROP_SUBTYPE ];
            if ( in_array( TP_REST_Search_Controller::TYPE_ANY, $taxonomies, true ) ) $taxonomies = $this->_subtypes;
            $page     = (int) $request['page'];
            $per_page = (int) $request['per_page'];
            $query_args = ['taxonomy' => $taxonomies,'hide_empty' => false,
                'offset' => ( $page - 1 ) * $per_page,'number' => $per_page,];
            if ( ! empty( $request['search'] ) )  $query_args['search'] = $request['search'];
            $query_args = $this->_apply_filters( 'rest_term_search_query', $query_args, $request );
            $query       = new TP_Term_Query();
            $found_terms = $query->query_term( $query_args );
            $found_ids   = $this->_tp_list_pluck( $found_terms, 'term_id' );
            unset( $query_args['offset'], $query_args['number'] );
            $total = $this->_tp_count_terms( $query_args );
            if ( ! $total ) $total = 0;
            return [self::RESULT_IDS => $found_ids,self::RESULT_TOTAL => $total,];
        }
        public function prepare_item( $id, array $fields ):array {
            $term = $this->_get_term( $id );
            $data = [];
            if ( in_array( TP_REST_Search_Controller::PROP_ID, $fields, true ) )
                $data[ TP_REST_Search_Controller::PROP_ID ] = (int) $id;
            if ( in_array( TP_REST_Search_Controller::PROP_TITLE, $fields, true ) )
                $data[ TP_REST_Search_Controller::PROP_TITLE ] = $term->name;
            if ( in_array( TP_REST_Search_Controller::PROP_URL, $fields, true ) )
                $data[ TP_REST_Search_Controller::PROP_URL ] = $this->_get_term_link( $id );
            if ( in_array( TP_REST_Search_Controller::PROP_TYPE, $fields, true ) )
                $data[ TP_REST_Search_Controller::PROP_TYPE ] = $term->taxonomy;
            return $data;
        }
        public function prepare_item_links( $id ):array {
            $term = $this->_get_term( $id );
            $links = [];
            $item_route = $this->_rest_get_route_for_term( $term );
            if ( $item_route )
                $links['self'] = ['href' => $this->_rest_url( $item_route ), 'embeddable' => true,];
            $links['about'] = ['href' => $this->_rest_url( sprintf( 'tp/v1/taxonomies/%s', $term->taxonomy ) ),];
            return $links;
        }
    }
}else die;