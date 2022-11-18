<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 14:42
 */
namespace TP_Core\Traits\Multisite\Blog;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\Queries\TP_Site_Query;
if(ABSPATH){
    trait _ms_blog_03 {
        use _init_db;
        /**
         * @description Get a list of most recently updated blogs.
         * @param int $start
         * @param int $quantity
         * @return array|null
         */
        protected function _get_last_updated($start = 0, $quantity = 40 ):array{
            $this->tpdb = $this->_init_db();
            return $this->tpdb->get_results( $this->tpdb->prepare( TP_SELECT . " blog_id, domain, path FROM $this->tpdb->blogs WHERE site_id = %d AND public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0' AND last_updated != '0000-00-00 00:00:00' ORDER BY last_updated DESC limit %d, %d", $this->_get_current_network_id(), $start, $quantity ), ARRAY_A );
        }//778
        /**
         * @description Handler for updating the site's last updated date when a post is published or
         * @description . an already published post is changed.
         * @param $new_status
         * @param $old_status
         * @param $post
         */
        protected function _update_blog_date_on_post_publish( $new_status, $old_status, $post ):void{
            $post_type_obj = $this->_get_post_type_object( $post->post_type );
            if ( ! $post_type_obj || ! $post_type_obj->public ) return;
            if ( 'publish' !== $new_status && 'publish' !== $old_status ) return;
            $this->_tp_mu_update_blogs_date();
        }//798
        /**
         * @description Handler for updating the current site's last updated date when a published
         * @description .post is deleted.
         * @param $post_id
         */
        protected function _update_blog_date_on_post_delete( $post_id ):void{
            $post = $this->_get_post( $post_id );
            $post_type_obj = $this->_get_post_type_object( $post->post_type );
            if ( ! $post_type_obj || ! $post_type_obj->public ) return;
            if ( 'publish' !== $post->post_status ) return;
            $this->_tp_mu_update_blogs_date();
        }//821
        /**
         * @description Handler for updating the current site's posts count when a post is deleted.
         * @param $post_id
         */
        protected function _update_posts_count_on_delete( $post_id ):void{
            $post = $this->_get_post( $post_id );
            if ( ! $post || 'publish' !== $post->post_status || 'post' !== $post->post_type ) return;
            $this->_update_posts_count();
        }//843
        /**
         * @description Handler for updating the current site's posts count when a post status changes.
         * @param $new_status
         * @param $old_status
         * @param null $post
         */
        protected function _update_posts_count_on_transition_post_status( $new_status, $old_status, $post = null ):void{
            if ( $new_status === $old_status ) return;
            if ( 'post' !== $this->_get_post_type( $post ) ) return;
            if ( 'publish' !== $new_status && 'publish' !== $old_status )
                return;
            $this->_update_posts_count();
        }//863
        /**
         * @description Count number of sites grouped by site status.
         * @param null $network_id
         * @return array
         */
        protected function _tp_count_sites( $network_id = null ):array{
            if ( empty( $network_id ) ) $network_id = $this->_get_current_network_id();
            $counts = [];
            $args   = ['network_id' => $network_id,'number' => 1,
                'fields' => 'ids','no_found_rows' => false,];
            $q             = new TP_Site_Query( $args );
            $counts['all'] = $q->found_sites;
            $_args    = null;
            $statuses = array( 'public', 'archived', 'mature', 'spam', 'deleted' );
            foreach ( $statuses as $status ) {
                $_args            = $args;
                $_args[ $status ] = 1;
                $q                 = new TP_Site_Query( $_args );
                $counts[ $status ] = $q->found_sites;
            }
            return $counts;
        }//896
    }
}else die;