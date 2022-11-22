<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 03:16
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Tool;
if(ABSPATH){
    trait QueryBuilderTrait{
        protected function _buildQueryString(array $params): string
        {
            return http_build_query($params, '', '&', \PHP_QUERY_RFC3986);
        }
    }
}else die;