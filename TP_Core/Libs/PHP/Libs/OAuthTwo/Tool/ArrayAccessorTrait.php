<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 04:13
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Tool;
if(ABSPATH){
    trait ArrayAccessorTrait{
        private function __getValueByKey(array $data, $key, $default = null){
            if (!is_string($key) || empty($key) || !count($data)) return $default;
            if (strpos($key, '.') !== false) {
                $keys = explode('.', $key);
                foreach ($keys as $innerKey) {
                    if (!is_array($data) || !array_key_exists($innerKey, $data)) return $default;
                    $data = $data[$innerKey];
                }
                return $data;
            }
            return array_key_exists($key, $data) ? $data[$key] : $default;
        }
    }
}else die;