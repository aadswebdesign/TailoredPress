<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-6-2022
 * Time: 17:59
 */
namespace TP_Core\Libs\RestApi\Fields;
if(ABSPATH){
    class TP_REST_User_Meta_Fields extends TP_REST_Meta_Fields{
        protected function _get_meta_type(): string{
            return 'user';
        }
        protected function _get_meta_subtype(): string{
            return 'user';
        }
        protected function _get_rest_field_type(): string{
            return 'user';
        }
    }
}else die;