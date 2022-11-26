<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 09:44
 */
namespace TP_Core\Libs\SimplePie\SP_Components\Cache;
if(ABSPATH){
    interface SP_Cache_Base{
        public const TYPE_FEED = 'spc';
        public const TYPE_IMAGE = 'spi';
        public function __construct($location, $name, $type);
        public function save($data);
        public function load();
        public function micro_time();
        public function touch();
        public function unlink();
    }
}else die;