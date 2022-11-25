<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-6-2022
 * Time: 20:26
 */
namespace TP_Core\Libs\RestApi\Search;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Search_Controller;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_format_post_01;
if(ABSPATH){
    class TP_REST_Post_Format_Search_Handler extends TP_REST_Search_Handler{
        use _filter_01, _format_post_01;
        public function __construct() {
            $this->_type = 'post-format';
        }
        public function search_items( TP_REST_Request $request ):array {
            $format_strings = $this->_get_post_format_strings();
            $format_slugs   = array_keys( $format_strings );
            $query_args = [];
            if ( ! empty( $request['search'] ) ) $query_args['search'] = $request['search'];
            $query_args = $this->_apply_filters( 'rest_post_format_search_query', $query_args, $request );
            $found_ids = [];
            foreach ( $format_slugs as $index => $format_slug ) {
                if ( ! empty( $query_args['search'] ) ) {
                    $format_string       = $this->_get_post_format_string( $format_slug );
                    $format_slug_match   = stripos( $format_slug, $query_args['search'] ) !== false;
                    $format_string_match = stripos( $format_string, $query_args['search'] ) !== false;
                    if ( ! $format_slug_match && ! $format_string_match ) continue;
                }
                $format_link = $this->_get_post_format_link( $format_slug );
                if ( $format_link ) $found_ids[] = $format_slug;
            }
            $page     = (int) $request['page'];
            $per_page = (int) $request['per_page'];
            return [
                self::RESULT_IDS   => array_slice( $found_ids, ( $page - 1 ) * $per_page, $per_page ),
                self::RESULT_TOTAL => count( $found_ids ),
            ];
        }
        public function prepare_item( $id, array $fields ):array {
            $data = [];
            if ( in_array( TP_REST_Search_Controller::PROP_ID, $fields, true ) )
                $data[ TP_REST_Search_Controller::PROP_ID ] = $id;
            if ( in_array( TP_REST_Search_Controller::PROP_TITLE, $fields, true ) )
                $data[ TP_REST_Search_Controller::PROP_TITLE ] = $this->_get_post_format_string( $id );
            if ( in_array( TP_REST_Search_Controller::PROP_URL, $fields, true ) )
                $data[ TP_REST_Search_Controller::PROP_URL ] = $this->_get_post_format_link( $id );
            if ( in_array( TP_REST_Search_Controller::PROP_TYPE, $fields, true ) )
                $data[ TP_REST_Search_Controller::PROP_TYPE ] = $this->_type;
            return $data;
        }
        public function prepare_item_links( $id ):array {
            return [];
        }
    }
}else die;