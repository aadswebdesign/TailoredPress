<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-4-2022
 * Time: 15:36
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Methods\_methods_13;
use TP_Core\Traits\Load\_load_04;
if(ABSPATH){
    class TP_Object_Cache{
        use _formats_08;
        use _load_04;
        use _methods_13;
        private $__blog_prefix;
        private $__cache = [];
        private $__multisite;
        private $__expire;
        private $__force;
        public $global_groups = [];
        public $cache_hits = 0;
        public $cache_misses = 0;
        public function __construct() {
            $this->__multisite   = $this->_is_multisite();
            $this->__blog_prefix = $this->__multisite ? $this->_get_current_blog_id() . ':' : '';
        }//79
        public function add( $key, $data, $group = 'default', $expire = 0 ): bool{
            if ( $this->_tp_suspend_cache_addition()) return false;
            if ( empty( $group ) ) $group = 'default';
            $id = $key;
            if ( $this->__multisite && ! isset( $this->global_groups[ $group ] ) )
                $id = $this->__blog_prefix . $key;
            if ( $this->_exists( $id, $group ) )
                return false;
            return $this->set( $key, $data, $group, (int) $expire );
        }//161
        public function add_multiple( array $data, $group = '', $expire = 0 ): array{
            $values = [];
            foreach ( $data as $key => $value )
                $values[ $key ] = $this->add( $key, $value, $group, $expire );
            return $values;
        }//194
        public function replace( $key, $data, $group = 'default', $expire = 0 ): bool{
            if ( empty( $group ) ) $group = 'default';
            $id = $key;
            if ( $this->__multisite && ! isset( $this->global_groups[ $group ] ) )
                $id = $this->__blog_prefix . $key;
            if ( ! $this->_exists( $id, $group ) )return false;
            return $this->set( $key, $data, $group, (int) $expire );
        }//218
        public function set( $key, $data, $group = 'default', $expire = 0 ): bool{
            $this->__expire = $expire;//todo fake it for now
            if ( empty( $group ) ) $group = 'default';
            if ( $this->__multisite && ! isset( $this->global_groups[ $group ] ) )
                $key = $this->__blog_prefix . $key;
            if ( is_object( $data ) )
                $data = clone $data;
            $this->__cache[ $group ][ $key ] = $data;
            return true;
        }//255 set
        public function set_multiple( array $data, $group = '', $expire = 0 ): array{
            $values = [];
            foreach ( $data as $key => $value )
                $values[ $key ] = $this->set( $key, $value, $group, $expire );
            return $values;
        }//283

        /**
         * @param $key
         * @param string $group
         * @param null $found
         * @param bool $force
         * @return mixed
         */
        public function get( $key, $group = 'default', &$found = null, $force = false ){
            $this->__force = $force; //todo fake it for now
            if ( empty( $group ) ) $group = 'default';
            if ( $this->__multisite && ! isset( $this->global_groups[ $group ] ) )
                $key = $this->__blog_prefix . $key;
            if ( $this->_exists( $key, $group ) ) {
                $found             = true;
                ++$this->cache_hits;
                if ( is_object( $this->__cache[ $group ][ $key ] ) )
                    return clone $this->__cache[ $group ][ $key ];
                else return $this->__cache[ $group ][ $key ];
            }else{$found = false;}
            ++$this->cache_misses;
            return false;
        }//312
        public function get_multiple( $keys, $group = 'default', $force = false ): array{
            $values = [];
            foreach ( $keys as $key )
                $values[ $key ] = $this->get( $key, $group, $force );
            return $values;
        }//348
        public function delete( $key, $group = 'default' ): bool {//, $deprecated = false
            if ( empty( $group ) ) $group = 'default';
            if ( $this->__multisite && ! isset( $this->global_groups[ $group ] ) )
                $key = $this->__blog_prefix . $key;
            if ( ! $this->_exists( $key, $group ) ) return false;
            unset( $this->__cache[ $group ][ $key ] );
            return true;
        }//370
        public function delete_multiple( array $keys, $group = '' ): array{
            $values = [];
            foreach ( $keys as $key ) $values[ $key ] = $this->delete( $key, $group );
            return $values;
        }//397
        public function increase( $key, $offset = 1, $group = 'default' ): bool{
            if ( empty( $group ) ) $group = 'default';
            if ( $this->__multisite && ! isset( $this->global_groups[ $group ] ) )
                $key = $this->__blog_prefix . $key;
            if ( ! $this->_exists( $key, $group ) ) return false;
            if ( ! is_numeric( $this->__cache[ $group ][ $key ] ) )
                $this->__cache[ $group ][ $key ] = 0;
            $offset = (int) $offset;
            $this->__cache[ $group ][ $key ] += $offset;
            if ( $this->__cache[ $group ][ $key ] < 0 )
                $this->__cache[ $group ][ $key ] = 0;
            return $this->__cache[ $group ][ $key ];
        }//418
        public function decrease( $key, $offset = 1, $group = 'default' ): bool{
            if ( empty( $group ) ) $group = 'default';
            if ( $this->__multisite && ! isset( $this->global_groups[ $group ] ) )
                $key = $this->__blog_prefix . $key;
            if ( ! $this->_exists( $key, $group ) ) return false;
            if ( ! is_numeric( $this->__cache[ $group ][ $key ] ) )
                $this->__cache[ $group ][ $key ] = 0;
            $offset = (int) $offset;
            $this->__cache[ $group ][ $key ] -= $offset;
            if ( $this->__cache[ $group ][ $key ] < 0 ) $this->__cache[ $group ][ $key ] = 0;
            return $this->__cache[ $group ][ $key ];
        }//457
        public function flush(): bool{
            $this->__cache = [];
            return true;
        }//492
        public function add_global_groups( $groups ): void{
            $groups = (array) $groups;
            $groups = array_fill_keys( $groups, true );
            $this->global_groups = array_merge( $this->global_groups, $groups );
        }//505
        public function switch_to_blog( $blog_id ): void{
            $blog_id           = (int) $blog_id;
            $this->__blog_prefix = $this->__multisite ? $blog_id . ':' : '';
        }//521
        public function stats(): void{
            //todo
            echo '<p>';
            echo "<strong>Cache Hits:</strong> {$this->cache_hits}<br />";
            echo "<strong>Cache Misses:</strong> {$this->cache_misses}<br />";
            echo '</p>';
            echo '<ul>';
            foreach ( $this->__cache as $group => $cache ) {
                echo '<li><strong>Group:</strong> ' . $this->_esc_html( $group ) . ' - ( ' . number_format( strlen( serialize( $cache ) ) / KB_IN_BYTES, 2 ) . 'k )</li>';
            }
            echo '</ul>';
        }//553
        protected function _exists( $key, $group ): bool{
            return isset( $this->__cache[ $group ] ) && ( isset( $this->__cache[ $group ][ $key ] ) || array_key_exists( $key, $this->__cache[ $group ] ) );
        }//143
    }
}else die;

