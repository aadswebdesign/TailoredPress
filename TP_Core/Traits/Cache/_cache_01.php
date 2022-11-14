<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 05:20
 */
namespace TP_Core\Traits\Cache;
use TP_Core\Traits\Inits\_init_cache;
if(ABSPATH){
    trait _cache_01 {
        use _init_cache;
        protected function _tp_cache_init():void {
            $this->_init_object_cache();
        }
        protected function _tp_cache_add( $key, $data, $group = '', $expire = 0 ): bool{
            return $this->_init_object_cache()->add( $key, $data, $group, (int) $expire );
        }//41
        /**
         * @description Adds multiple values to the cache in one call.
         * @param array $data
         * @param string $group
         * @param int $expire
         * @return array
         */
        protected function _tp_cache_add_multiple( array $data, $group = '', $expire = 0 ): array{
            return $this->_init_object_cache()->add_multiple( $data, $group, $expire );
        }//62
        /**
         * @param $key
         * @param $data
         * @param string $group
         * @param int $expire
         * @return bool
         */
        protected function _tp_cache_replace( $key, $data, $group = '', $expire = 0 ): bool{
            return $this->_init_object_cache()->replace( $key, $data, $group, (int) $expire );
        }//84
        /**
         * @param $key
         * @param $data
         * @param string $group
         * @param int $expire
         * @return bool
         */
        protected function _tp_cache_set( $key, $data, $group = '', $expire = 0 ): bool{
            return $this->_init_object_cache()->set( $key, $data, $group, (int) $expire );
        }//108
        protected function _tp_cache_set_multiple( array $data, $group = '', $expire = 0 ): array{
            return $this->_init_object_cache()->set_multiple( $data, $group, $expire );
        }//129

        /**
         * @param $key
         * @param string $group
         * @param bool $force
         * @param null $found
         * @return mixed
         */
        protected function _tp_cache_get( $key, $group = '', $force = false, &$found = null ){
            return $this->_init_object_cache()->get( $key, $group, $force, $found );
        }//151
        protected function _tp_cache_get_multiple( $keys, $group = '', $force = false ): array{
            return $this->_init_object_cache()->get_multiple( $keys, $group, $force );
        }//172
        protected function _tp_cache_delete( $key, $group = '' ): bool{
            return $this->_init_object_cache()->delete( $key, $group );
        }//190
        /**
         * @param array $keys
         * @param string $group
         * @return array
         */
        protected function _tp_cache_delete_multiple( array $keys, $group = '' ): array{
            return $this->_init_object_cache()->delete_multiple( $keys, $group );
        }//209
    }
}else die;