<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-3-2022
 * Time: 17:52
 */
namespace TP_Core\Traits\Compat;
if(ABSPATH){
    trait _cache_compat {
        /**
         * @description Adds multiple values to the cache in one call, if the cache keys don't already exist.
         * @param array $data
         * @param string $group
         * @param int $expire
         * @return mixed
         */
        protected function _tp_cache_compat_add_multiple( array $data, $group = '', $expire = 0 ){
            static $values;
            foreach ( $data as $key => $value )
                $values[ $key ] = $this->_tp_cache_add( $key, $value, $group, $expire );
            return $values;
        }//28
        /**
         * @description Sets multiple values to the cache in one call.
         * @param array $data
         * @param string $group
         * @param int $expire
         * @return array
         */
        protected function _tp_cache_compat_set_multiple( array $data, $group = '', $expire = 0 ):array{
            $values = [];
            foreach ( $data as $key => $value )
                $values[ $key ] = $this->_tp_cache_set( $key, $value, $group, $expire );
            return $values;
        }//60
        /**
         * @description Retrieves multiple values from the cache in one call.
         * @param $keys
         * @param string $group
         * @param bool $force
         * @return array
         */
        protected function _cache_compat_get_multiple( $keys, $group = '', $force = false ):array{
            $values = array();
            foreach ( $keys as $key )
                $values[ $key ] = $this->_tp_cache_get( $key, $group, $force );
            return $values;
        }//89
        /**
         * @description Deletes multiple values from the cache in one call.
         * @param array $keys
         * @param string $group
         * @return array
         */
        protected function _tp_cache_compat_delete_multiple( array $keys, $group = '' ):array{
            $values = [];
            foreach ( $keys as $key )
                $values[ $key ] = $this->_tp_cache_delete( $key, $group );
            return $values;
        }//116
        /**
         * @description Removes all cache items from the in-memory runtime cache.
         * @return bool
         */
        protected function _tp_cache_compat_flush_runtime():bool{
            return $this->_tp_using_ext_object_cache() ? false : $this->_tp_cache_flush();
        }//127
    }
}else die;