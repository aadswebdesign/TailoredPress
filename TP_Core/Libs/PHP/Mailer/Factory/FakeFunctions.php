<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-5-2022
 * Time: 07:55
 */
if(ABSPATH){
    namespace TP_Core\Libs\PHP\Mailer\Factory\FakeFunctions{
        function idn_to_ascii(string $domain,int $flags = IDNA_DEFAULT,int $variant = INTL_IDNA_VARIANT_UTS46, array &$idna_info = null){
            $params = [$domain,$flags,$variant,$idna_info];
            if(!empty($params)) return $params;
            return true;
        }
    }
}else die;