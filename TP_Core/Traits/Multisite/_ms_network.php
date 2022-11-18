<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 14:42
 */
namespace TP_Core\Traits\Multisite;
use TP_Core\Traits\Inits\_init_cache;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_site;
use TP_Core\Libs\TP_Network;
if(ABSPATH){
    trait _ms_network{
        use _init_db;
        use _init_site;
        use _init_queries;
        use _init_cache;
        /**
         * @description Retrieves network data given a network ID or network object.
         * @param null $network
         * @return bool|null|TP_Network
         */
        protected function _get_network( $network = null ){
            $current_site = $this->_init_current_site();
            if ( empty( $network ) &&  $network instanceof TP_Network && isset( $current_site ) )
                $network = $current_site;
            elseif ( is_object( $network ) ) $network = new TP_Network( $network );
            else $network = TP_Network::get_instance( $network );
            if ( ! $network ) return null;
            $network = $this->_apply_filters( 'get_network', $network );
            return $network;
        }//23
        /**
         * @description Retrieves a list of networks.
         * @param \array[] ...$args
         * @return array|int
         */
        protected function _get_networks(array ...$args){
            $this->tp_query =  $this->_init_network_query();
            return $this->tp_query->query_network( $args );
        }//63
        /**
         * @description Removes a network from the object cache.
         * @param $ids
         */
        protected function _clean_network_cache( $ids ):void{
            if ( ! empty( $this->__tp_suspend_cache_invalidation ) ) return;
            foreach ( (array) $ids as $id ) {
                $this->_tp_cache_delete( $id, 'networks' );
                $this->_do_action( 'clean_network_cache', $id );
            }
            $this->_tp_cache_set( 'last_changed', microtime(), 'networks' );
        }//78
        /**
         * @description Updates the network cache of given networks.
         * @param $networks
         */
        protected function _update_network_cache( $networks ):void{
            foreach ( (array) $networks as $network )
                $this->_tp_cache_add( $network->id, $network, 'networks' );
        }//112
        /**
         * @description Adds any networks from the given IDs to the cache that do not already exist in cache.
         * @param $network_ids
         */
        protected function _prime_network_caches( $network_ids ):void{
            $this->tpdb = $this->_init_db();
            $non_cached_ids = $this->_get_non_cached_ids( $network_ids, 'networks' );
            if ( ! empty( $non_cached_ids ) ) {
                $fresh_networks = $this->tpdb->get_results( sprintf( TP_SELECT . " $this->tpdb->site.* FROM $this->tpdb->site WHERE id IN (%s)", implode( ',', array_map( 'intval', $non_cached_ids ) ) ) );
                $this->_update_network_cache( $fresh_networks );
            }
        }//129
    }
}else die;