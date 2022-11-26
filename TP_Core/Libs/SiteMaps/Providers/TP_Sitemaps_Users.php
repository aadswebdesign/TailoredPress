<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-8-2022
 * Time: 22:48
 */
namespace TP_Core\Libs\SiteMaps\Providers;
use TP_Core\Libs\SiteMaps\TP_Sitemaps_Provider;
use TP_Core\Libs\Queries\TP_User_Query;
use TP_Core\Traits\Templates\_author_template_02;
if(ABSPATH){
    class TP_Sitemaps_Users extends TP_Sitemaps_Provider {
        use _author_template_02;
        public function __construct() {
            $this->_name        = 'users';
            $this->_object_type = 'user';
        }//26
        public function get_url_list( $page_num, $object_subtype = '' ){
            $url_list = $this->_apply_filters('tp_sitemaps_users_pre_url_list',null,$page_num);
            if ( null !== $url_list ) {return $url_list;}
            $args          = $this->_get_users_query_args();
            $args['paged'] = $page_num;
            $query    = new TP_User_Query( $args );
            $users    = $query->get_results();
            $url_list = [];
            foreach ( $users as $user ) {
                $sitemap_entry = ['loc' => $this->_get_author_posts_url( $user->ID ),];
                $sitemap_entry = $this->_apply_filters( 'tp_sitemaps_users_entry', $sitemap_entry, $user );
                $url_list[]    = $sitemap_entry;
            }
            return $url_list;
        }//39
        public function get_max_num_pages( $object_subtype = '' ){
            $max_num_pages = $this->_apply_filters( 'tp_sitemaps_users_pre_max_num_pages', null );
            if ( null !== $max_num_pages ){return $max_num_pages;}
            $args  = $this->_get_users_query_args();
            $query = new TP_User_Query( $args );
            $total_users = $query->get_total();
            return (int) ceil( $total_users / $this->_tp_sitemaps_get_max_urls( $this->_object_type ) );
        }//100
        protected function _get_users_query_args(){
            $public_post_types = $this->_get_post_types(['public' => true,]);
            $args = $this->_apply_filters('tp_sitemaps_users_query_args',
                ['has_published_posts' => array_keys( $public_post_types ), 'number' => $this->_tp_sitemaps_get_max_urls( $this->_object_type ),]
            );
            return $args;
        }//132
    }
}else{die;}