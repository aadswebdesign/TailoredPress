<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-8-2022
 * Time: 18:24
 */
namespace TP_Core\Traits\Misc;
use TP_Core\Libs\SiteMaps\TP_Sitemaps;
use TP_Core\Libs\SiteMaps\TP_Sitemaps_Provider;
use TP_Core\Traits\Inits\_init_sitemap;
if(ABSPATH){
    trait _sitemap{
        use _init_sitemap;
        /**
         * @description Retrieves the current Sitemaps server instance.
         * @return TP_Sitemaps
         */
        protected function _tp_sitemaps_get_server():TP_Sitemaps{
            $this->tp_sitemaps = $this->_init_sitemap();
            return  $this->tp_sitemaps;
        }//22
        /**
         * @description Gets an array of sitemap providers.
         * @return TP_Sitemaps
         */
        protected function _tp_get_sitemap_providers():TP_Sitemaps{
            $this->tp_sitemaps = $this->_tp_sitemaps_get_server();
            return $this->tp_sitemaps->registry->get_providers();
        }//52
        /**
         * @description Registers a new sitemap provider.
         * @param $name
         * @param TP_Sitemaps_Provider $provider
         * @return bool
         */
        protected function _tp_register_sitemap_provider( $name, TP_Sitemaps_Provider $provider ):bool{
            $this->tp_sitemaps = $this->_tp_sitemaps_get_server();
            return $this->tp_sitemaps->registry->add_provider( $name, $provider );
        }//67
        /**
         * @description Gets the maximum number of URLs for a sitemap.
         * @param $object_type
         * @return mixed
         */
        protected function _tp_sitemaps_get_max_urls( $object_type ){
            return $this->_apply_filters( 'tp_sitemaps_max_urls', 2000, $object_type );
        }//81
        /**
         * @description Retrieves the full URL for a sitemap
         * @param $name
         * @param string $subtype_name
         * @param mixed $page
         * @return mixed
         */
        protected function _get_sitemap_url( $name, $subtype_name = '',$page = 1 ) {
            $this->tp_sitemaps = $this->_tp_sitemaps_get_server();
            if ( ! $this->tp_sitemaps ) { return false;}
            if ( 'index' === $name ) {return $this->tp_sitemaps->index->get_index_url();}
            $provider = $this->tp_sitemaps->registry->get_provider( $name );
            if ( ! $provider ) {return false;}
            if ( $subtype_name && !array_key_exists($subtype_name, $provider->get_object_subtypes())) { return false;}
            $page = $this->_abs_int($page );
            if ( 0 >= $page ){$page = 1;}
            return $provider->get_sitemap_url( $subtype_name, $page );
        }//103
    }
}else{die;}