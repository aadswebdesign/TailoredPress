<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-6-2022
 * Time: 17:59
 */
namespace TP_Core\Libs\RestApi\Fields;
if(ABSPATH){
    class TP_REST_Post_Meta_Fields extends TP_REST_Meta_Fields{
        protected $_post_type;
        public function __construct( $post_type ) {
            $this->_post_type = $post_type;
        }
        protected function _get_meta_type(): string{
            return 'post';
        }
        protected function _get_meta_subtype() {
            return $this->_post_type;
        }
        protected function _get_rest_field_type(){
            return $this->_post_type;
        }
    }
}else die;