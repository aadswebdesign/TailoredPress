<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-5-2022
 * Time: 14:53
 */
namespace TP_Core\Traits\Query;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _query_05{
        use _init_queries;
        use _init_db;
        /**
         * @description Iterate the post index in the loop.
         */
        protected function _the_post():bool{
            $this->_init_query()->the_post();
        }//982
        /**
         * @description Determines whether current TailoredPress query has comments to loop over.
         * @return bool
         */
        protected function _have_comments():bool{
            return $this->_init_query()->have_comments();
        }//1000
        /**
         * @description Iterate comment index in the comment loop.
         */
        protected function _the_comment():bool{
            $this->_init_query()->the_comment();
        }//1014
        /**
         * @description Redirect old slugs to the correct permalink.
         */
        protected function _tp_old_slug_redirect(): void{
            if ( $this->_is_404() && '' !== $this->_get_query_var( 'name' ) ) {
                if ( $this->_get_query_var( 'post_type' ) ) $post_type = $this->_get_query_var( 'post_type' );
                elseif ( $this->_get_query_var( 'attachment' ) ) $post_type = 'attachment';
                elseif ( $this->_get_query_var( 'pagename' ) ) $post_type = 'page';
                else $post_type = 'post';
                if ( is_array( $post_type ) ) {
                    if ( count( $post_type ) > 1 ) return;
                    $post_type = reset( $post_type );
                }
                if ( $this->_is_post_type_hierarchical( $post_type ) ) return;
                $id = $this->_find_post_by_old_slug( $post_type );
                if ( ! $id ) $id = $this->_find_post_by_old_date( $post_type );
                $id = $this->_apply_filters( 'old_slug_redirect_post_id', $id );
                if ( ! $id ) return;
                $link = $this->_get_permalink( $id );
                if ( $this->_get_query_var( 'paged' ) > 1 )
                    $link = $this->_user_trailingslashit( $this->_trailingslashit( $link ) . 'page/' . $this->_get_query_var( 'paged' ) );
                elseif ( $this->_is_embed() )
                    $link = $this->_user_trailingslashit( $this->_trailingslashit( $link ) . 'embed' );
                $link = $this->_apply_filters( 'old_slug_redirect_url', $link );
                if ( ! $link ) return;
                $this->_tp_redirect( $link, 301 ); // Permanent redirect.
                exit;
            }

        }//1026
        /**
         * @description Find the post ID for redirecting an old slug.
         * @param $post_type
         * @return int
         */
        protected function _find_post_by_old_slug( $post_type ): int{
            $tpdb = $this->_init_db();
            $query = $tpdb->prepare( TP_SELECT ." post_id FROM $tpdb->post_meta, $tpdb->posts WHERE ID = post_id AND post_type = %s AND meta_key = '_wp_old_slug' AND meta_value = %s", $post_type, $this->_get_query_var( 'name' ) );
            if ( $this->_get_query_var( 'year' ) )
                $query .= $tpdb->prepare( ' AND YEAR(post_date) = %d', $this->_get_query_var( 'year' ) );
            if ( $this->_get_query_var( 'monthnum' ) )
                $query .= $tpdb->prepare( ' AND MONTH(post_date) = %d', $this->_get_query_var( 'monthnum' ) );
            if ( $this->_get_query_var( 'day' ) )
                $query .= $tpdb->prepare( ' AND DAYOFMONTH(post_date) = %d', $this->_get_query_var( 'day' ) );
            return (int) $tpdb->get_var( $query );
        }//1108
        /**
         * @description Find the post ID for redirecting an old date.
         * @param $post_type
         * @return int
         */
        protected function _find_post_by_old_date( $post_type ): int{
            $tpdb = $this->_init_db();
            $date_query = '';
            if ( $this->_get_query_var( 'year' ) )
                $date_query .= $tpdb->prepare( ' AND YEAR(pm_date.meta_value) = %d', $this->_get_query_var( 'year' ) );
            if ( $this->_get_query_var( 'monthnum' ) )
                $date_query .= $tpdb->prepare( ' AND MONTH(pm_date.meta_value) = %d', $this->_get_query_var( 'monthnum' ) );
            if ( $this->_get_query_var( 'day' ) )
                $date_query .= $tpdb->prepare( ' AND DAYOFMONTH(pm_date.meta_value) = %d', $this->_get_query_var( 'day' ) );
            $id = 0;
            if ( $date_query ) {
                $id = (int) $tpdb->get_var( $tpdb->prepare( TP_SELECT ." post_id FROM $tpdb->post_meta AS pm_date, $tpdb->posts WHERE ID = post_id AND post_type = %s AND meta_key = '_wp_old_date' AND post_name = %s" . $date_query, $post_type, $this->_get_query_var( 'name' ) ) );
                if ( ! $id )  $id = (int) $tpdb->get_var( $tpdb->prepare( TP_SELECT ." ID FROM $tpdb->posts, $tpdb->post_meta AS pm_slug, $tpdb->post_meta AS pm_date WHERE ID = pm_slug.post_id AND ID = pm_date.post_id AND post_type = %s AND pm_slug.meta_key = '_wp_old_slug' AND pm_slug.meta_value = %s AND pm_date.meta_key = '_wp_old_date'" . $date_query, $post_type, $this->_get_query_var( 'name' ) ) );
            }
            return $id;
        }//1131
        /**
         * @description Set up global post data.
         * @param $post
         * @return bool
         */
        protected function _setup_postdata( $post ): bool{
            $tp_query = $this->_init_query();
            if ($tp_query !== null) return $tp_query->setup_postdata( $post );
            return false;
        }//1180
        /**
         * @description Generates post data.
         * @param $post
         * @return array|bool
         */
        protected function _generate_postdata( $post ){
            $tp_query = $this->_init_query();
            if ($tp_query !== null) return $tp_query->generate_postdata( $post );
            return false;
        }//1200
    }
}else die;