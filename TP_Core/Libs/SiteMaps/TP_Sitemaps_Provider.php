<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-8-2022
 * Time: 22:24
 */
namespace TP_Core\Libs\SiteMaps;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Misc\_sitemap;
use TP_Core\Traits\Post\_post_03;
if(ABSPATH){
    abstract class TP_Sitemaps_Provider extends Sitemaps_Base {
        use _init_error;
        use _post_03;
        use _sitemap;
        protected $_name = '';
        protected $_object_type = '';
        abstract public function get_url_list( $page_num, $object_subtype = '' );//47
        abstract public function get_max_num_pages( $object_subtype = '' );//57
        public function get_sitemap_type_data():string{
            $sitemap_data = [];
            $object_subtypes = $this->get_object_subtypes();
            if ( empty( $object_subtypes ) ) {
                $sitemap_data[] = ['name'  => '','pages' => $this->get_max_num_pages(),];
                return $sitemap_data;
            }
            foreach ( $object_subtypes as $object_subtype_name => $data ) {
                $object_subtype_name = (string) $object_subtype_name;
                $sitemap_data[] = array(
                    'name'  => $object_subtype_name,
                    'pages' => $this->get_max_num_pages( $object_subtype_name ),
                );
            }
            return $sitemap_data;
        }//66
        public function get_sitemap_entries():array{
            $sitemaps = [];
            $sitemap_types = $this->get_sitemap_type_data();
            foreach ((array) $sitemap_types as $type ) {
                for ( $page = 1; $page <= $type['pages']; $page ++ ) {
                    $sitemap_entry = array(
                        'loc' => $this->get_sitemap_url( $type['name'], $page ),
                    );
                    $sitemap_entry = $this->_apply_filters( 'tp_sitemaps_index_entry', $sitemap_entry, $this->_object_type, $type['name'], $page );
                    $sitemaps[] = $sitemap_entry;
                }
            }
            return $sitemaps;
        }//103
        public function get_sitemap_url( $name, $page ){
            $tp_rewrite = $this->_init_rewrite();
            $params = array_filter(['sitemap' => $this->_name,'sitemap-subtype' => $name,'paged' => $page,]);
            $basename = sprintf('/tp-sitemap-%1$s.xml',implode( '-', $params ));
            if ( ! $tp_rewrite->using_permalinks() ) {
                $basename = '/?' . http_build_query( $params, '', '&' );
            }
            return $this->_home_url( $basename );
        }//145
        public function get_object_subtypes():array {
            return [];
        }//176
    }
}else{die;}