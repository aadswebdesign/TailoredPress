<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:23
 */
namespace TP_Core\Traits\Templates;
if(ABSPATH){
    trait _link_template_04 {
        /**
         * @description Retrieves the URL used for the post preview.
         * @param null $post
         * @param string $preview_link
         * @param string|\array[] ...$query_args
         * @return bool
         */
        protected function _get_preview_post_link( $post = null, $preview_link = '', array ...$query_args):bool{
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $post_type_object = $this->_get_post_type_object( $post->post_type );
            if ( $this->_is_post_type_viewable( $post_type_object ) ) {
                if ( ! $preview_link ) $preview_link = $this->_set_url_scheme( $this->_get_permalink( $post ) );
                $query_args['preview'] = 'true';
                $preview_link= (string)$this->_add_query_arg( $preview_link, $query_args );
            }
            return $this->_apply_filters( 'preview_post_link', $preview_link, $post );
        }//1403 from link-template
        /**
         * @description Retrieves the edit post link for post.
         * @param int $id
         * @param string $context
         * @return bool
         */
        protected function _get_edit_post_link( $id = 0, $context = 'display' ):bool{
            $post = $this->_get_post( $id );
            if ( ! $post ) return false;
            if ( 'revision' === $post->post_type ) $action = '';
            elseif ( 'display' === $context ) $action = '&amp;action=edit';
            else $action = '&action=edit';
            $post_type_object = $this->_get_post_type_object( $post->post_type );
            if ( ! $post_type_object ) return false;
            if ( ! $this->_current_user_can( 'edit_post', $post->ID ) ) return false;
            if ( $post_type_object->_edit_link )
                $link = $this->_admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
            else $link = '';
            return $this->_apply_filters( 'get_edit_post_link', $link, $post->ID, $context );
        }//1444 from link-template
        /**
         * @description Displays the edit post link for post.
         * @param null $text
         * @param string $before
         * @param string $after
         * @param int $id
         * @param string $class
         */
        protected function _edit_post_link( $text = null, $before = '', $after = '', $id = 0, $class = 'post-edit-link' ):void{
            $post = $this->_get_post( $id );
            if ( ! $post ) return;
            $url = $this->_get_edit_post_link( $post->ID );
            if ( ! $url ) return;
            if ( null === $text ) $text = $this->__( 'Edit This' );
            $link = "<a class='{$this->_esc_attr( $class )}' href='{$this->_esc_url($url )}'>$text</a>";
            echo $before . $this->_apply_filters( 'edit_post_link', $link, $post->ID, $text ) . $after;
        }//1498 from link-template
        /**
         * @description Retrieves the delete posts link for post.
         * @param int $id
         * @param bool $force_delete
         * @return bool
         */
        protected function _get_delete_post_link( $id = 0, $force_delete = false ):bool{
            $post = $this->_get_post( $id );
            if ( ! $post ) return false;
            $post_type_object = $this->_get_post_type_object( $post->post_type );
            if ( ! $post_type_object ) return false;
            if ( ! $this->_current_user_can( 'delete_post', $post->ID ) ) return false;
            $action = ( $force_delete || ! EMPTY_TRASH_DAYS ) ? 'delete' : 'trash';
            $delete_link = $this->_add_query_arg( 'action', $action, $this->_admin_url( sprintf( $post_type_object->_edit_link, $post->ID ) ) );
            return $this->_apply_filters( 'get_delete_post_link', $this->_tp_nonce_url( $delete_link, "$action-post_{$post->ID}" ), $post->ID, $force_delete );
        }//1539 from link-template
        /**
         * @description Retrieves the edit comment link.
         * @param int $comment_id
         * @return bool
         */
        protected function _get_edit_comment_link( $comment_id = 0 ):bool{
            $comment = $this->_get_comment( $comment_id );
            if ( ! $this->_current_user_can( 'edit_comment', $comment->comment_ID ) ) return false;
            $location = $this->_admin_url( 'comment.php?action=editcomment&amp;c=' ) . $comment->comment_ID;//todo
            return $this->_apply_filters( 'get_edit_comment_link', $location );
        }//1582 from link-template

        /**
         * @description Displays the edit comment link with formatting.
         * @param null $text
         * @param string $before
         * @param string $after
         * @return bool|string
         */
        protected function _get_edit_comment_link_string( $text = null, $before = '', $after = '' ){
            $comment = $this->_get_comment();
            if ( ! $this->_current_user_can( 'edit_comment', $comment->comment_ID ) ) return false;
            if ( null === $text ) $text = $this->__( 'Edit This' );
            $link = "<a class='comment-edit-link' href='{$this->_esc_url($this->_get_edit_comment_link( $comment ) )}'>$text</a>";
            return $before . $this->_apply_filters( 'edit_comment_link', $link, $comment->comment_ID, $text ) . $after;
        }//1610 from link-template
        protected function _edit_comment_link( $text = null, $before = '', $after = '' ):void{
            echo $this->_get_edit_comment_link_string( $text, $before, $after);
        }
        /**
         * @description Displays the edit bookmark link.
         * @param string|int $link
         * @return bool
         */
        protected function _get_edit_bookmark_link( $link = 0 ):bool{
            $link = (string)$this->_get_bookmark( $link );
            $_link = null;
            if( $link instanceof \stdClass ){
                $_link = $link;
            }
            if ( ! $this->_current_user_can( 'manage_links' ) ) return false;
            $location = $this->_admin_url( 'link.php?action=edit&amp;link_id=' ) . $_link->link_id; //todo
            return $this->_apply_filters( 'get_edit_bookmark_link', $location, $_link->link_id );
        }//1643 from link-template
        /**
         * @description Displays the edit bookmark link anchor content.
         * @param string $link
         * @param string $before
         * @param string $after
         * @param null $bookmark
         */
        protected function _edit_bookmark_link( $link = '', $before = '', $after = '', $bookmark = null ):void{
            $bookmark = $this->_get_bookmark( $bookmark );
            if ( ! $this->_current_user_can( 'manage_links' ) ) return;
            if ( empty( $link ) ) $link = $this->__( 'Edit This' );
            $link = "<a href='{$this->_esc_url(  $this->_get_edit_bookmark_link( $bookmark ))}'>$link</a>";
            echo $before . $this->_apply_filters( 'edit_bookmark_link', $link, $bookmark->link_id ) . $after;
        }//1673 from link-template
        /**
         * @description Retrieves the edit user link.
         * @param null $user_id
         * @return string
         */
        protected function _get_edit_user_link( $user_id = null ):string{
            if ( ! $user_id ) $user_id = $this->_get_current_user_id();
            if ( empty( $user_id ) || ! $this->_current_user_can( 'edit_user', $user_id ) )  return '';
            $user = $this->_get_user_data( $user_id );
            if ( ! $user ) return '';
            if ( $this->_get_current_user_id() === $user->ID ) $link = $this->_get_edit_profile_url( $user->ID );
            else $link = $this->_add_query_arg( 'user_id', $user->ID, $this->_self_admin_url( 'user-edit.php' ) );
            return $this->_apply_filters( 'get_edit_user_link', $link, $user->ID );
        }//1705 from link-template
        //@description Retrieves the previous post that is adjacent to the current post.
        protected function _get_previous_post( $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ){
            return $this->_get_adjacent_post( $in_same_term, $excluded_terms, true, $taxonomy );
        }//1752 from link-template
    }
}else die;