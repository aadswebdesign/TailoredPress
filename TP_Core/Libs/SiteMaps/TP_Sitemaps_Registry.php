<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-8-2022
 * Time: 22:48
 */
namespace TP_Core\Libs\SiteMaps;
use TP_Core\Traits\Filters\_filter_01;
if(ABSPATH){
    class TP_Sitemaps_Registry{
        use _filter_01;
        private $__providers = [];
        public function add_provider( $name, TP_Sitemaps_Provider $provider ):bool{
            if(isset($this->__providers[ $name ])){return false;}
            $provider = $this->_apply_filters( 'tp_sitemaps_add_provider', $provider, $name );
            if ( ! $provider instanceof TP_Sitemaps_Provider ){return false;}
            $this->__providers[ $name ] = $provider;
            return true;
        }//36
        public function get_provider( $name ){
            return $this->__providers[$name] ?? null;
        }//67
        public function get_providers():array {
            return $this->__providers;
        }//82
    }
}else{die;}