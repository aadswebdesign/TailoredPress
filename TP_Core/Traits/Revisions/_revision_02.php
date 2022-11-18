<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 15-5-2022
 * Time: 15:26
 */
namespace TP_Core\Traits\Revisions;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    trait _revision_02{
        use _init_db;
        /**
         * @description Returns all revisions of specified post.
         * @param int $post_id
         * @param null $args
         * @return array
         */
        protected function _tp_get_post_revisions( $post_id = 0, $args = null ):array{
            $post = $this->_get_post( $post_id );
            if ( ! $post || empty( $post->ID ) ) return [];
            $defaults = ['order' => 'DESC','orderby' => 'date ID','check_enabled' => true,];
            $args     = $this->_tp_parse_args( $args, $defaults );
            if ( $args['check_enabled'] && ! $this->_tp_revisions_enabled( $post ) ) return [];
            $args = array_merge($args, ['post_parent' => $post->ID,'post_type' => 'revision','post_status' => 'inherit',]);
            $revisions = $this->_get_children( $args );
            if ( ! $revisions ) return [];
            return $revisions;
        }//485
        /**
         * @description Returns the url for viewing and potentially restoring revisions of a given post.
         * @param int $post_id
         * @return mixed
         */
        protected function _tp_get_post_revisions_url( $post_id = 0 ){
            $post = $this->_get_post( $post_id );
            if ( ! $post instanceof TP_Post ) return null;
            if ( 'revision' === $post->post_type ) return $this->_get_edit_post_link( $post );
            if ( ! $this->_tp_revisions_enabled( $post ) ) return null;
            $revisions = $this->_tp_get_post_revisions( $post->ID, array( 'posts_per_page' => 1 ) );
            if ( 0 === count( $revisions ) ) return null;
            $revision = reset( $revisions );
            return $this->_get_edit_post_link( $revision );
        }//527
        /**
         * @description Determine if revisions are enabled for a given post.
         * @param $post
         * @return bool
         */
        protected function _tp_revisions_enabled( $post ):bool{
            return $this->_tp_revisions_to_keep( $post ) !== 0;
        }//561
        /**
         * @description Determine how many revisions to retain for a given post.
         * @param $post
         * @return int
         */
        protected function _tp_revisions_to_keep( $post ):int{
            $num = TP_POST_REVISIONS;
            if ( true === $num ) $num = -1;
            else $num = (int) $num;
            if ( ! $this->_post_type_supports( $post->post_type, 'revisions' ) ) $num = 0;
            $num = $this->_apply_filters( 'tp_revisions_to_keep', $num, $post );
            $num = $this->_apply_filters( "tp_{$post->post_type}_revisions_to_keep", $num, $post );
            return (int) $num;
        }//578
        /**
         * @description Sets up the post object for preview based on the post autosave.
         * @param $post
         * @return mixed
         */
        protected function _set_preview( $post ){
            if ( ! is_object( $post ) ) return $post;
            $preview = $this->_tp_get_post_autosave( $post->ID );
            if ( is_object( $preview ) ) {
                $preview = $this->_sanitize_post( $preview );
                $post->post_content = $preview->post_content;
                $post->post_title   = $preview->post_title;
                $post->post_excerpt = $preview->post_excerpt;
            }
            $this->_add_filter( 'get_the_terms', '_tp_preview_terms_filter', 10, 3 );
            $this->_add_filter( 'get_post_metadata', '_tp_preview_post_thumbnail_filter', 10, 3 );
            return $post;
        }//635
        /**
         * @description Filters the latest content for preview from the post autosave.
         */
        protected function _show_post_preview():void{
            if ( isset( $_GET['preview_id'], $_GET['preview_nonce'] ) ) {
                $id = (int) $_GET['preview_id'];
                if ( false === $this->_tp_verify_nonce( $_GET['preview_nonce'], 'post_preview_' . $id ) )
                    $this->_tp_die( $this->__( 'Sorry, you are not allowed to preview drafts.' ), 403 );
                $this->_add_filter( 'the_preview', '_set_preview' );
            }
        }//662
        /**
         * @description Filters terms lookup to set the post format.
         * @param $terms
         * @param $post_id
         * @param $taxonomy
         * @return array
         */
        protected function _tp_preview_terms_filter( $terms, $post_id, $taxonomy ):array{
            $post = $this->_get_post();
            if ( ! $post ) return $terms;
            if ( empty( $_REQUEST['post_format'] ) || $post->ID !== $post_id || 'post_format' !== $taxonomy || 'revision' === $post->post_type)
                return $terms;
            if ( 'standard' === $_REQUEST['post_format'] ) $terms = [];
            else {
                $term = $this->_get_term_by( 'slug', 'post-format-' . $this->_sanitize_key( $_REQUEST['post_format'] ), 'post_format' );
                if ( $term ) $terms = [ $term];
            }
            return $terms;
        }//685
        /**
         * @description Filters post thumbnail lookup to set the post thumbnail.
         * @param $value
         * @param $post_id
         * @param $meta_key
         * @return string
         */
        protected function _tp_preview_post_thumbnail_filter( $value, $post_id, $meta_key ):string{
            $post = $this->_get_post();
            if ( ! $post ) return $value;
            if ( empty( $_REQUEST['_thumbnail_id'] ) || empty( $_REQUEST['preview_id'] ) || $post->ID !== $post_id || '_thumbnail_id' !== $meta_key || 'revision' === $post->post_type || $post_id !== $_REQUEST['preview_id'] )
                return $value;
            $thumbnail_id = (int) $_REQUEST['_thumbnail_id'];
            if ( $thumbnail_id <= 0 ) return '';
            return (string) $thumbnail_id;
        }//720
        /**
         * @description Gets the post revision version.
         * @param $revision
         * @return bool|int
         */
        protected function _tp_get_post_revision_version( $revision ){
            if ( is_object( $revision ) ) $revision = get_object_vars( $revision );
            elseif ( ! is_array( $revision ) ) return false;
            if ( preg_match( '/^\d+-(?:autosave|revision)-v(\d+)$/', $revision['post_name'], $matches ) )
                return (int) $matches[1];
            return 0;
        }//753
        /**
         * @description Upgrade the revisions author, add the current post as a revision and set the revisions version to 1
         * @param $post
         * @param $revisions
         * @return bool
         */
        protected function _tp_upgrade_revisions_of_post( $post, $revisions ):bool{
            $this->tpdb = $this->_init_db();
            $lock   = "revision-upgrade-{$post->ID}";
            $now    = time();
            $result = $this->tpdb->query( $this->tpdb->prepare( TP_INSERT . " IGNORE INTO `$this->tpdb->options` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, 'no') /* LOCK */", $lock, $now ) );
            if ( ! $result ) {
                $locked = $this->_get_option( $lock );
                if ( ! $locked ) return false;
                if ( $locked > $now - 3600 ) return false;
            }
            $this->_update_option( $lock, $now );
            reset( $revisions );
            $add_last = true;
            do {
                $this_revision = current( $revisions );
                $prev_revision = next( $revisions );
                $this_revision_version = $this->_tp_get_post_revision_version( $this_revision );
                if ( false === $this_revision_version ) continue;
                if ( 0 < $this_revision_version ) {
                    $add_last = false;
                    continue;
                }
                $update = ['post_name' => preg_replace( '/^(\d+-(?:autosave|revision))[\d-]*$/', '$1-v1', $this_revision->post_name ),];
                if ( $prev_revision ) {
                    $prev_revision_version = $this->_tp_get_post_revision_version( $prev_revision );
                    if ( $prev_revision_version < 1 ) $update['post_author'] = $prev_revision->post_author;
                }
                $result = $this->tpdb->update( $this->tpdb->posts, $update, array( 'ID' => $this_revision->ID ) );
                if ( $result ) $this->_tp_cache_delete( $this_revision->ID, 'posts' );
            } while ( $prev_revision );
            $this->_delete_option( $lock );
            if ( $add_last )  $this->_tp_save_post_revision( $post->ID );
            return true;
        }//779
    }
}else die;