<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\TP_Metadata_Lazyloader;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    trait _post_13 {
        use _init_db;
        /**
         * @description Sets the post thumbnail (featured image) for the given post.
         * @param $post
         * @param $thumbnail_id
         * @return bool
         */
        protected function _set_post_thumbnail( $post, $thumbnail_id ):bool{
            $post         = $this->_get_post( $post );
            $thumbnail_id = $this->_abs_int( $thumbnail_id );
            if ( $post && $thumbnail_id && $this->_get_post( $thumbnail_id ) ) {
                if ( $this->_tp_get_attachment_image( $thumbnail_id, 'thumbnail' ) )
                    return $this->_update_post_meta( $post->ID, '_thumbnail_id', $thumbnail_id );
                else return $this->_delete_post_meta( $post->ID, '_thumbnail_id' );
            }
            return false;
        }//7737
        /**
         * @description Removes the thumbnail (featured image) from the given post.
         * @param $post
         * @return bool
         */
        protected function _delete_post_thumbnail( $post ):bool{
            $post = $this->_get_post( $post );
            if ( $post ) return $this->_delete_post_meta( $post->ID, '_thumbnail_id' );
            return false;
        }//7758
        /**
         * @description Delete auto-drafts for new posts that are > 7 days old.
         */
        protected function _tp_delete_auto_drafts():void{
            $this->tpdb = $this->_init_db();
            $old_posts = $this->tpdb->get_col( TP_SELECT ." ID FROM $this->tpdb->posts WHERE post_status = 'auto-draft' AND DATE_SUB( NOW(), INTERVAL 7 DAY ) > post_date" );
            foreach ($old_posts as $delete ) $this->_tp_delete_post( $delete, true );
        }//7773
        /**
         * @description Queues posts for lazy-loading of term meta.
         * @param $posts
         */
        protected function _tp_queue_posts_for_term_meta_lazy_load( $posts ):void{
            $post_type_taxonomies = [];
            $term_ids             = [];
            foreach ( $posts as $post ) {
                if ( ! ( $post instanceof TP_Post ) ) continue;
                if ( ! isset( $post_type_taxonomies[ $post->post_type ] ) )
                    $post_type_taxonomies[ $post->post_type ] = $this->_get_object_taxonomies( $post->post_type );
                foreach ( $post_type_taxonomies[ $post->post_type ] as $taxonomy ) {
                    $terms = $this->_get_object_term_cache( $post->ID, $taxonomy );
                    if ( false !== $terms ) {
                        foreach ( $terms as $term ) {
                            if ( ! in_array( $term->term_id, $term_ids, true ) ) $term_ids[] = $term->term_id;
                        }
                    }
                }
            }
            if ( $term_ids ) {
                $lazyloader = $this->_tp_metadata_lazy_loader();
                if($lazyloader  instanceof TP_Metadata_Lazyloader ){
                    $lazyloader->queue_objects( 'term', $term_ids );
                }//todo
            }
        }//7791
        /**
         * @description Update the custom taxonomies' term counts when a post's status is changed.
         * @param $post
         */
        protected function _update_term_count_on_transition_post_status( $post ):void{
            //todo $new_status, $old_status,
            foreach ( (array) $this->_get_object_taxonomies( $post->post_type ) as $taxonomy ) {
                $tt_ids = $this->_tp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'tt_ids' ) );
                $this->_tp_update_term_count( $tt_ids, $taxonomy );
            }
        }//7835
        /**
         * @description Adds any posts from the given IDs to the cache that do not already exist in cache.
         * @param $ids
         * @param bool $update_term_cache
         * @param bool $update_meta_cache
         */
        protected function _prime_post_caches( $ids, $update_term_cache = true, $update_meta_cache = true ):void{
            $this->tpdb = $this->_init_db();
            $non_cached_ids = $this->_get_non_cached_ids( $ids, 'posts' );
            if ( ! empty( $non_cached_ids ) ) {
                $fresh_posts = $this->tpdb->get_results( sprintf( TP_SELECT . " $this->tpdb->posts.* FROM $this->tpdb->posts WHERE ID IN (%s)", implode( ',', $non_cached_ids ) ) );
                $this->_update_post_caches( $fresh_posts, 'any', $update_term_cache, $update_meta_cache );
            }
        }//7857
        /**
         * @description Adds a suffix if any trashed posts have a given slug.
         * @param $post_name
         * @param int $post_ID
         */
        protected function _tp_add_trashed_suffix_to_post_name_for_trashed_posts( $post_name, $post_ID = 0 ):void{
            $trashed_posts_with_desired_slug = $this->_get_posts(
                ['name' => $post_name,'post_status'  => 'trash', 'post_type' => 'any','nopaging' => true,'post__not_in' => [ $post_ID ],]);
            if ( ! empty( $trashed_posts_with_desired_slug ) ) {
                foreach ( $trashed_posts_with_desired_slug as $_post )
                    $this->_tp_add_trashed_suffix_to_post_name_for_post( $_post );
            }
        }//7882
        /**
         * @description Adds a trashed suffix for a given post.
         * @param $post
         * @return string
         */
        protected function _tp_add_trashed_suffix_to_post_name_for_post( $post ):string{
            $this->tpdb = $this->_init_db();
            $post = $this->_get_post( $post );
            if ( '__trashed' === substr( $post->post_name, -9 ) ) return $post->post_name;
            $this->_add_post_meta( $post->ID, '_tp_desired_post_slug', $post->post_name );
            $post_name = $this->_truncate_post_slug( $post->post_name, 191 ) . '__trashed';
            $this->tpdb->update( $this->tpdb->posts, array( 'post_name' => $post_name ), array( 'ID' => $post->ID ) );
            $this->_clean_post_cache( $post->ID );
            return $post_name;
        }//7914
        /**
         * @description Filters the SQL clauses of an attachment query to include file names.
         * @param $clauses
         * @return mixed
         */
        protected function _filter_query_attachment_file_names( $clauses ){
            $this->tpdb = $this->_init_db();
            $this->_remove_filter( 'posts_clauses', __FUNCTION__ );
            $clauses['join'] .= " LEFT JOIN {$this->tpdb->post_meta} AS sq1 ON ( {$this->tpdb->posts}.ID = sq1.post_id AND sq1.meta_key = '_wp_attached_file' )";
            $clauses['groupby'] = "{$this->tpdb->posts}.ID";
            $clauses['where'] = preg_replace("/\({$this->tpdb->posts}.post_content (NOT LIKE|LIKE) (\'[^']+\')\)/",'$0 OR ( sq1.meta_value $1 $2 )',$clauses['where']);
            return $clauses;
        }//7941
        /**
         * @description Sets the last changed time for the 'posts' cache group.
         */
        protected function _tp_cache_set_posts_last_changed():void{
            $this->_tp_cache_set( 'last_changed', microtime(), 'posts' );
        }//7964
        /**
         * @description Get all available post MIME types for a given post type.
         * @param string $type
         * @return array
         */
        protected function _get_available_post_mime_types( $type = 'attachment' ):array{
            $this->tpdb = $this->_init_db();
            $types = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " DISTINCT post_mime_type FROM $this->tpdb->posts WHERE post_type = %s", $type ) );
            return $types;
        }//7978
    }
}else die;