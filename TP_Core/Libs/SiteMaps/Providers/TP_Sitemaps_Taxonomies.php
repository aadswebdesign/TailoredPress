<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-8-2022
 * Time: 22:48
 */
namespace TP_Core\Libs\SiteMaps\Providers;
use TP_Core\Libs\SiteMaps\TP_Sitemaps_Provider;
use TP_Core\Libs\Queries\TP_Term_Query;
use TP_Core\Traits\Taxonomy\_taxonomy_01;
use TP_Core\Traits\Taxonomy\_taxonomy_04;
use TP_Core\Traits\Taxonomy\_taxonomy_07;
if(ABSPATH){
    class TP_Sitemaps_Taxonomies extends TP_Sitemaps_Provider {
        use _taxonomy_01,_taxonomy_04,_taxonomy_07;
        public function __construct() {
            $this->_name        = 'users';
            $this->_object_type = 'user';
        }//26
        public function get_object_subtypes():array {
            $taxonomies = $this->_get_taxonomies( array( 'public' => true ), 'objects' );
            $taxonomies = array_filter( $taxonomies, 'is_taxonomy_viewable' );
            return $this->_apply_filters( 'tp_sitemaps_taxonomies', $taxonomies );
        }//35
        public function get_url_list( $page_num, $object_subtype = '' ){
            $taxonomy        = $object_subtype;
            $supported_types = $this->get_object_subtypes();
            if ( ! isset( $supported_types[ $taxonomy ])){return array();}
            $url_list = $this->_apply_filters('tp_sitemaps_taxonomies_pre_url_list',null,$taxonomy,$page_num);
            if ( null !== $url_list ) {return $url_list;}
            $url_list = array();
            $offset = ( $page_num - 1 ) * $this->_tp_sitemaps_get_max_urls( $this->_object_type );
            $args           = $this->_get_taxonomies_query_args( $taxonomy );
            $args['offset'] = $offset;
            $taxonomy_terms = new TP_Term_Query( $args );
            if ( ! empty( $taxonomy_terms->terms ) ) {
                foreach ( $taxonomy_terms->terms as $term ) {
                    $term_link = $this->_get_term_link( $term, $taxonomy );
                    if ( $this->_init_error( $term_link ) ) { continue;}
                    $sitemap_entry = ['loc' => $term_link,];
                    $sitemap_entry = $this->_apply_filters( 'tp_sitemaps_taxonomies_entry', $sitemap_entry, $term, $taxonomy );
                    $url_list[]    = $sitemap_entry;
                }
            }
            return $url_list;
        }//61
        public function get_max_num_pages( $object_subtype = '' ){
            if ( empty( $object_subtype )){return 0;}
            $taxonomy = $object_subtype;
            $max_num_pages = $this->_apply_filters( 'tp_sitemaps_taxonomies_pre_max_num_pages', null, $taxonomy );
            if ( null !== $max_num_pages ){ return $max_num_pages;}
            $term_count = $this->_tp_count_terms( $this->_get_taxonomies_query_args( $taxonomy ) );
            return (int) ceil( $term_count / $this->_tp_sitemaps_get_max_urls( $this->_object_type ) );
        }//143
        protected function _get_taxonomies_query_args( $taxonomy ){
            $_sitemaps_taxonomies = ['fields' => 'ids','taxonomy' => $taxonomy,'orderby' => 'term_order',
                'number' => $this->_tp_sitemaps_get_max_urls( $this->_object_type ),
                'hide_empty' => true,'hierarchical' => false,'update_term_meta_cache' => false,];
            return $this->_apply_filters('tp_sitemaps_taxonomies_query_args',$_sitemaps_taxonomies,$taxonomy);
        }//181
    }
}else{die;}