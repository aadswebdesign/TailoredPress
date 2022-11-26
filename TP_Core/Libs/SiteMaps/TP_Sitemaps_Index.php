<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-8-2022
 * Time: 22:48
 */
namespace TP_Core\Libs\SiteMaps;
if(ABSPATH){
    class TP_Sitemaps_Index extends Sitemaps_Base{
        private $__max_sitemaps = 50000;
        protected $_registry;
        public function __construct( TP_Sitemaps_Registry $registry ){
            $this->_registry = $registry;
        }//43
        public function get_sitemap_list():array{
            $sitemaps = [];
            $providers = $this->_registry->get_providers();
            /* @var TP_Sitemaps_Provider $provider */
            foreach ( $providers as $name => $provider ) {
                $sitemap_entries = $provider->get_sitemap_entries();
                if ( ! $sitemap_entries ){continue;}
                array_push( $sitemaps, ...$sitemap_entries );
                if(count( $sitemaps ) >= $this->__max_sitemaps){break;}
            }
            return array_slice( $sitemaps, 0, $this->__max_sitemaps, true );
        }//54
        public function get_index_url(){
            $tp_rewrite = $this->_init_rewrite();
            if ( ! $tp_rewrite->using_permalinks() ) {
                return $this->_home_url( '/?sitemap=index' );
            }
            return $this->_home_url( '/tp-sitemap.xml' );
        }//86
    }
}else{die;}