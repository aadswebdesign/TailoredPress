<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-5-2022
 * Time: 06:59
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Options\_option_02;
use TP_Core\Traits\Options\_option_03;
use TP_Core\Libs\SimplePie\SimplePie;
if(ABSPATH){
    class TP_Feed_Cache_Transient{
        use _filter_01;
        use _option_01, _option_02, _option_03;
        public $name;
        public $mod_name;
        public $lifetime = 43200;
        public function __construct( $location, $filename, $extension ) {
            $this->name     = 'feed_' . $filename;
            $this->mod_name = 'feed_mod_' . $filename;
            $lifetime = $this->lifetime;
            $this->lifetime = $this->_apply_filters( 'tp_feed_cache_transient_lifetime', $lifetime, $filename );
        }
        public function save( $data ): bool {
            if ( $data instanceof SimplePie )
                $data = $data->sp_data;
            $this->_set_transient( $this->name, $data, $this->lifetime );
            $this->_set_transient( $this->mod_name, time(), $this->lifetime );
            return true;
        }
        public function load(): string{
            return $this->_get_transient( $this->name );
        }
        public function mtime(): string {
            return $this->_get_transient( $this->mod_name );
        }
        public function touch(): string {
            return $this->_set_transient( $this->mod_name, time(), $this->lifetime );
        }
        public function unlink(): string {
            $this->_delete_transient( $this->name );
            $this->_delete_transient( $this->mod_name );
            return true;
        }
    }
}else die;