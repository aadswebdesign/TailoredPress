<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 19:37
 */
namespace TP_Core\Traits\Cache;
use TP_Core\Traits\Inits\_init_cache;
if(ABSPATH){
    trait _cache_02 {
        use _init_cache;
        protected function _tp_cache_increase( $key, $offset = 1, $group = '' ): bool{
            return $this->_init_object_cache()->increase( $key, $offset, $group );
        }//229
        protected function _tp_cache_decrease( $key, $offset = 1, $group = '' ): bool{
            return $this->_init_object_cache()->decrease( $key, $offset, $group);
        }//253
        protected function _tp_cache_flush(): bool{
            return $this->_init_object_cache()->flush();
        }//265
        /**
         * @description  Removes all cache items from the in-memory runtime cache.
         * @return bool
         */
        protected function _tp_cache_flush_runtime(): bool{
            return $this->_tp_cache_flush();
        }//280
        protected function _tp_cache_close():bool{
            return true;
        }//297
        protected function _tp_cache_add_global_groups( $groups ): void{
            $this->_init_object_cache()->add_global_groups( $groups );
        }//311
        protected function _tp_cache_add_non_persistent_groups( $groups ): void{}//324
        protected function _tp_cache_switch_to_blog( $blog_id ): void{
            $this->_init_object_cache()->switch_to_blog( $blog_id );
        }//340
    }
}else die;