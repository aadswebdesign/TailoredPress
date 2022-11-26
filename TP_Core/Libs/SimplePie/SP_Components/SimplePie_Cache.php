<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 10:29
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\SP_Components\Cache\SP_Cache_File;
if(ABSPATH){
    class SimplePie_Cache{
        protected static $_handlers = array(
            'mysql'     => 'SP_Cache_MySQL',
            'memcache'  => 'SP_Cache_Memcache',
            'memcached' => 'SP_Cache_Memcached',
            // todo 'redis'     => 'SP_Cache_Redis'
        );
        public static function get_handler($location, $filename, $extension): SP_Cache_File{
            $type = explode(':', $location, 2);
            $type = $type[0];
            if (!empty(self::$_handlers[$type])){
                $class = self::$_handlers[$type];
                return new $class($location, $filename, $extension);
            }
            return new SP_Cache_File($location, $filename, $extension);
        }
        public static function register($type, $class):void{
            self::$_handlers[$type] = $class;
        }
        public static function parse_URL($url){
            $params = parse_url($url);
            $params['extras'] = array();
            if (isset($params['query'])) parse_str($params['query'], $params['extras']);
            return $params;
        }
    }
}else die;

