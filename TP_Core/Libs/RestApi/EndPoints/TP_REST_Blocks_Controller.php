<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
if(ABSPATH){
    class TP_REST_Blocks_Controller extends TP_REST_Posts_Controller{
        public function check_read_permission( $post ):bool {
            if ( ! $this->_current_user_can( 'read_post', $post->ID ) )
                return false;
            return parent::check_read_permission( $post );
        }//30
        public function filter_response_by_context( $data, $context ) {
            $data = parent::filter_response_by_context( $data, $context );
            unset( $data['title']['rendered'], $data['content']['rendered'] );
            return $data;
        }//48
        public function get_item_schema() {
            $schema = parent::get_item_schema();
            $schema['properties']['title']['properties']['raw']['context']   = ['view', 'edit'];
            $schema['properties']['content']['properties']['raw']['context'] = ['view', 'edit'];
            unset( $schema['properties']['title']['properties']['rendered'], $schema['properties']['content']['properties']['rendered'] );
            return $schema;
        }
    }
}else die;