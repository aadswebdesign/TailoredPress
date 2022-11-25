<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-6-2022
 * Time: 17:59
 */
namespace TP_Core\Libs\RestApi\Fields;
if(ABSPATH){
    class TP_REST_Term_Meta_Fields extends TP_REST_Meta_Fields{
        protected $_taxonomy;
        public function __construct( $taxonomy ) {
            $this->_taxonomy = $taxonomy;
        }
        protected function _get_meta_type(): string{
            return 'term';
        }
        protected function _get_meta_subtype() {
            return $this->_taxonomy;
        }
        protected function _get_rest_field_type():string{
            return 'post_tag' === $this->_taxonomy ? 'tag' : $this->_taxonomy;
        }
    }
}else die;