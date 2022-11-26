<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-8-2022
 * Time: 22:48
 */
namespace TP_Core\Libs\SiteMaps\Providers;
use TP_Core\Libs\SiteMaps\TP_Sitemaps_Provider;
use TP_Core\Libs\Queries\TP_Query;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Templates\_link_template_01;
if(ABSPATH){
    class TP_Sitemaps_Posts extends TP_Sitemaps_Provider {
        use _option_01;
        use _link_template_01;
        public function __construct() {
            $this->_name        = 'users';
            $this->_object_type = 'user';
        }//26
        public function get_object_subtypes():array{
            $post_types = $this->_get_post_types( array( 'public' => true ), 'objects' );
            unset( $post_types['attachment'] );
            $post_types = array_filter( $post_types, 'is_post_type_viewable' );
            return $this->_apply_filters( 'tp_sitemaps_post_types', $post_types );

        }//36
        public function get_url_list( $page_num, $object_subtype = '' ){
            $post_type = $object_subtype;
            $supported_types = $this->get_object_subtypes();
            if(!isset( $supported_types[$post_type])){return [];}
            $url_list = $this->_apply_filters('tp_sitemaps_posts_pre_url_list',null,$post_type,$page_num);
            if(null !== $url_list){return $url_list;}
            $args          = $this->_get_posts_query_args( $post_type );
            $args['paged'] = $page_num;
            $query = new TP_Query( $args );
            $url_list = [];
            if ( 'page' === $post_type && 1 === $page_num && 'posts' === $this->_get_option( 'show_on_front' ) ) {
                $sitemap_entry = ['loc' => $this->_home_url( '/' ),];
                $sitemap_entry = $this->_apply_filters( 'tp_sitemaps_posts_show_on_front_entry', $sitemap_entry );
                $url_list[]    = $sitemap_entry;
            }
            foreach ( $query->posts as $post ) {
                $sitemap_entry = ['loc' => $this->_get_permalink( $post ),];
                $sitemap_entry = $this->_apply_filters( 'tp_sitemaps_posts_entry', $sitemap_entry, $post, $post_type );
                $url_list[]    = $sitemap_entry;
            }
            return $url_list;
        }//64
        public function get_max_num_pages( $object_subtype = '' ){
            if ( empty( $object_subtype)){return 0;}
            $post_type = $object_subtype;
            $max_num_pages = $this->_apply_filters( 'tp_sitemaps_posts_pre_max_num_pages', null, $post_type );
            if(null !== $max_num_pages){return $max_num_pages;}
            $args                  = $this->_get_posts_query_args( $post_type );
            $args['fields']        = 'ids';
            $args['no_found_rows'] = false;
            $query = new TP_Query( $args );
            $min_num_pages = ( 'page' === $post_type && 'posts' === $this->_get_option( 'show_on_front' ) ) ? 1 : 0;
            return isset( $query->max_num_pages ) ? max( $min_num_pages, $query->max_num_pages ) : 1;
        }//157
        protected function _get_posts_query_args( $post_type ){
            $args= ['orderby' => 'ID','order' => 'ASC','post_type' => $post_type,
                'posts_per_page' => $this->_tp_sitemaps_get_max_urls( $this->_object_type ),
                'post_status' => array( 'publish' ),'no_found_rows' => true,
                'update_post_term_cache' => false,'update_post_meta_cache' => false,];
            return $this->_apply_filters('tp_sitemaps_posts_query_args',$args,$post_type);
        }
    }
}else{die;}