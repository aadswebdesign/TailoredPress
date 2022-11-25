<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-6-2022
 * Time: 20:26
 */
namespace TP_Core\Libs\RestApi\Search;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_format_post_01;
use TP_Core\Traits\Methods\_methods_11;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\RestApi\_rest_api_01;
use TP_Core\Traits\RestApi\_rest_api_08;
use TP_Core\Traits\Taxonomy\_taxonomy_01;
use TP_Core\Traits\Taxonomy\_taxonomy_02;
use TP_Core\Traits\Taxonomy\_taxonomy_04;
use TP_Core\Traits\Taxonomy\_taxonomy_07;
use TP_Core\Traits\Templates\_link_template_01;
use TP_Core\Traits\Templates\_post_template_01;
use TP_Core\Libs\RestApi\TP_REST_Request;
if(ABSPATH){
    abstract class TP_REST_Search_Handler{
        use _filter_01;
        use _format_post_01;
        use _post_01;
        use _post_03;
        use _post_04;
        use _post_template_01;
        use _link_template_01;
        use _rest_api_01;
        use _rest_api_08;
        use _taxonomy_01;
        use _taxonomy_02;
        use _taxonomy_04;
        use _taxonomy_07;
        use _methods_11;
        public const RESULT_IDS = 'ids';
        public const RESULT_TOTAL = 'total';
        protected $_type = '';
        protected $_subtypes = [];
        public function get_type():string {
            return $this->_type;
        }//52
        public function get_subtypes():array {
            return $this->_subtypes;
        }//61
        abstract public function search_items( TP_REST_Request $request );
        abstract public function prepare_item( $id, array $fields );
        abstract public function prepare_item_links( $id );
    }
}else die;