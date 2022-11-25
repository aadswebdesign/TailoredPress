<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-6-2022
 * Time: 17:59
 */
namespace TP_Core\Libs\RestApi\Fields;
if(ABSPATH){
    class TP_REST_Comment_Meta_Fields extends TP_REST_Meta_Fields{
        protected function _get_meta_type():string{
            return 'comment';
        }
        protected function _get_meta_subtype():string {
            return 'comment';
        }
        protected function _get_rest_field_type():string{
            return 'comment';
        }
    }
}else die;