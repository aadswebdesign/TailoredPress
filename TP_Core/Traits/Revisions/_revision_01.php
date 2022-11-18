<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 15-5-2022
 * Time: 15:26
 */
namespace TP_Core\Traits\Revisions;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _revision_01{
        use _init_db;
        use _init_error;
        /**
         *  @description Determines which fields of posts are to be saved in revisions.
         * @param mixed $post
         * @return array|null
         */
        protected function _tp_post_revision_fields(...$post):array{
            static $fields = null;
            if ( ! is_array( $post ) ) $post = $this->_get_post( $post, ARRAY_A );
            if ( is_null( $fields ) ) {
                $fields = ['post_title' => $this->__( 'Title' ),'post_content' => $this->__( 'Content' ),'post_excerpt' => $this->__( 'Excerpt' ),];
            }
            $fields = $this->_apply_filters( '_tp_post_revision_fields', $fields, $post );
            foreach ( array( 'ID', 'post_name', 'post_parent', 'post_date', 'post_date_gmt', 'post_status', 'post_type', 'comment_count', 'post_author' ) as $protect )
                unset( $fields[ $protect ] );
            return $fields;
        }//22
        /**
         * @description Returns a post array ready to be inserted into the posts table as a post revision.
         * @param mixed $post
         * @param bool $autosave
         * @return array
         */
        protected function _tp_post_revision_data( $post = [], $autosave = false ):array{
            if ( ! is_array( $post ) ) $post = $this->_get_post( $post, ARRAY_A );
            $fields = $this->_tp_post_revision_fields( $post );
            $revision_data = array();
            foreach ( array_intersect( array_keys( $post ), array_keys( $fields ) ) as $field )
                $revision_data[ $field ] = $post[ $field ];
            $revision_data['post_parent']   = $post['ID'];
            $revision_data['post_status']   = 'inherit';
            $revision_data['post_type']     = 'revision';
            $revision_data['post_name']     = $autosave ? "$post[ID]-autosave-v1" : "$post[ID]-revision-v1"; // "1" is the revisioning system version.
            $revision_data['post_date']     = $post['post_modified'] ?? '';
            $revision_data['post_date_gmt'] = $post['post_modified_gmt'] ?? '';
            return $revision_data;
        }//75
        /**
         * @description Creates a revision for the current version of a post.
         * @param $post_id
         * @return bool|string
         */
        protected function _tp_save_post_revision( $post_id ){
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return false;
            $post = $this->_get_post( $post_id );
            if ( ! $post ) return false;
            if ( ! $this->_post_type_supports( $post->post_type, 'revisions' ) ) return false;
            if ( 'auto-draft' === $post->post_status ) return false;
            if ( ! $this->_tp_revisions_enabled( $post ) ) return false;
            $revisions = $this->_tp_get_post_revisions( $post_id );
            if ( $revisions ) {
                foreach ( $revisions as $revision ) {
                    if ( false !== strpos( $revision->post_name, "{$revision->post_parent}-revision" ) ) {
                        $last_revision = $revision;
                        break;
                    }
                }
                if ( isset( $last_revision ) && $this->_apply_filters( 'wp_save_post_revision_check_for_changes', true, $last_revision, $post ) ) {
                    $post_has_changed = false;
                    foreach ( array_keys( $this->_tp_post_revision_fields( $post ) ) as $field ) {
                        if ( $this->_normalize_whitespace( $post->$field ) !== $this->_normalize_whitespace( $last_revision->$field ) ) {
                            $post_has_changed = true;
                            break;
                        }
                    }
                    $post_has_changed = (bool) $this->_apply_filters( 'tp_save_post_revision_post_has_changed', $post_has_changed, $last_revision, $post );
                    if ( ! $post_has_changed ) return false;
                }
            }
            $return = $this->_tp_put_post_revision( $post );
            $revisions_to_keep = $this->_tp_revisions_to_keep( $post );
            if ( $revisions_to_keep < 0 ) return $return;
            $revisions = $this->_tp_get_post_revisions( $post_id, array( 'order' => 'ASC' ) );
            $delete = count( $revisions ) - $revisions_to_keep;
            if ( $delete < 1 ) return $return;
            $revisions = array_slice( $revisions, 0, $delete );
            for ( $i = 0; isset( $revisions[ $i ] ); $i++ ) {
                if ( false !== strpos( $revisions[ $i ]->post_name, 'autosave' ) ) continue;
                $this->_tp_delete_post_revision( $revisions[ $i ]->ID );
            }
            return $return;
        }//109
        /**
         * @description Retrieve the auto-saved data of the specified post.
         * @param $post_id
         * @param int $user_id
         * @return bool
         */
        protected function _tp_get_post_autosave( $post_id, $user_id = 0 ):bool{
            $this->tpdb = $this->_init_db();
            $autosave_name = $post_id . '-autosave-v1';
            $user_id_query = ( 0 !== $user_id ) ? "AND post_author = $user_id" : null;
            $autosave_query = TP_SELECT . "  * FROM $this->tpdb->posts WHERE post_parent = %d AND post_type = 'revision' AND post_status = 'inherit' AND post_name   = %s " . $user_id_query . ' ORDER BY post_date DESC LIMIT 1';
            $autosave = $this->tpdb->get_results( $this->tpdb->prepare( $autosave_query, $post_id,$autosave_name));
            if ( ! $autosave ) return false;
            return $this->_get_post( $autosave[0] );
        }//236
        /**
         * @description Determines if the specified post is a revision.
         * @param mixed $post
         * @return bool|int
         */
        protected function _tp_is_post_revision(TP_Post $post ){
            $post = $this->_tp_get_post_revision( $post );
            if ( ! $post ) return false;
            return (int) $post->post_parent;
        }//276
        /**
         * @description Determines if the specified post is an autosave.
         * @param mixed $post
         * @return bool|int
         */
        protected function _tp_is_post_autosave(TP_Post $post ){
            $post = $this->_tp_get_post_revision( $post );
            if ( ! $post ) return false;
            if ( false !== strpos( $post->post_name, "{$post->post_parent}-autosave" ) )
                return (int) $post->post_parent;
            return false;
        }//293
        /**
         * @description Inserts post data into the posts table as a post revision.
         * @param null $post
         * @param bool $autosave
         * @return TP_Error
         */
        protected function _tp_put_post_revision( $post = null, $autosave = false ):TP_Error{
            if ( is_object( $post ) ) $post = get_object_vars( $post );
            elseif ( ! is_array( $post ) ) $post = $this->_get_post( $post, ARRAY_A );
            if ( ! $post || empty( $post['ID'] ) )
                return new TP_Error( 'invalid_post', $this->__( 'Invalid post ID.' ) );
            if ( isset( $post['post_type'] ) && 'revision' === $post['post_type'] )
                return new TP_Error( 'post_type', $this->__( 'Cannot create a revision of a revision' ) );
            $post = $this->_tp_post_revision_data( $post, $autosave );
            $post = $this->_tp_slash( $post ); // Since data is from DB.
            $revision_id = $this->_tp_insert_post( $post, true );
            if ( $this->_init_error( $revision_id ) ) return $revision_id;
            if ( $revision_id ) $this->_do_action( '_tp_put_post_revision', $revision_id );
            return $revision_id;
        }//316
        /**
         * @description Gets a post revision.
         * @param $post
         * @param string $output
         * @param string $filter
         * @return array|null
         */
        protected function _tp_get_post_revision( &$post, $output = OBJECT, $filter = 'raw' ):array{
            $revision = $this->_get_post( $post, OBJECT, $filter );
            if ( ! $revision ) return $revision;
            if ( 'revision' !== $revision->post_type ) return null;
            if ( OBJECT === $output ) return $revision;
            elseif ( ARRAY_A === $output ) {
                $_revision = get_object_vars( $revision );
                return $_revision;
            } elseif ( ARRAY_N === $output ) {
                $_revision = array_values( get_object_vars( $revision ) );
                return $_revision;
            }
            return $revision;
        }//365
        /**
         * @description Restores a post to the specified revision.
         * @param $revision_id
         * @param null $fields
         * @return array|bool|null
         */
        protected function _tp_restore_post_revision( $revision_id, $fields = null ){
            $revision = $this->_tp_get_post_revision( $revision_id, ARRAY_A );
            if ( ! $revision ) return $revision;
            if ( ! is_array( $fields ) ) $fields = array_keys( $this->_tp_post_revision_fields( $revision ) );
            $update = [];
            foreach ( array_intersect( array_keys( $revision ), $fields ) as $field ) $update[ $field ] = $revision[ $field ];
            if ( ! $update ) return false;
            $update['ID'] = $revision['post_parent'];
            $update = $this->_tp_slash( $update ); // Since data is from DB.
            $post_id = $this->_tp_update_post( $update );
            if ( ! $post_id || $this->_init_error( $post_id ) ) return $post_id;
            $this->_update_post_meta( $post_id, '_edit_last', $this->_get_current_user_id() );
            $this->_do_action( 'tp_restore_post_revision', $post_id, $revision['ID'] );
            return $post_id;
        }//398
        /**
         * @description Deletes a revision.
         * @param $revision_id
         * @return array|null
         */
        protected function _tp_delete_post_revision( $revision_id ):array{
            $revision = $this->_tp_get_post_revision( $revision_id );
            if ( ! $revision )  return $revision;
            $delete = null;
            if($revision  instanceof TP_Post ){
                $delete = $this->_tp_delete_post( $revision->ID );
            }
            if ( $delete ) $this->_do_action( 'tp_delete_post_revision', $revision->ID, $revision );
            return $delete;
        }//452
    }
}else die;