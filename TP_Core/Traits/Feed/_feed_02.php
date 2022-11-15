<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 20:22
 */
namespace TP_Core\Traits\Feed;
if(ABSPATH){
    trait _feed_02 {
        /**
         * @description Display the permalink to the post for use in feeds.
         */
        protected function _get_the_permalink_feed_rss(){
            return $this->_esc_url( $this->_apply_filters( 'the_permalink_rss', [$this, '__get_permalink'] ) );
        }//242
        public function the_permalink_feed_rss():void{
            echo $this->_get_the_permalink_feed_rss();
        }//242
        /**
         * @description Outputs the link to the comments for the current post in an xml safe way
         */
        protected function _get_comments_link_feed(){
            return $this->_esc_url( $this->_apply_filters( 'comments_link_feed', [$this,'__get_comments_link'] ) );
        }//258
        public function comments_link_feed():void{
            echo $this->_get_comments_link_feed();
        }//258
        /**
         * @description Display the feed GUID for the current comment.
         * @param null $comment_id
         */
        protected function _comment_guid( $comment_id = null ):void{
            echo $this->_esc_url( $this->_get_comment_feed_guid( $comment_id ) );
        }//277
        /**
         * @description Retrieve the feed GUID for the current comment.
         * @param null $comment_id
         * @return bool|string
         */
        protected function _get_comment_feed_guid( $comment_id = null ){
            $comment = $this->_get_comment( $comment_id );
            if ( ! is_object( $comment ) ) return false;
            return $this->_get_the_guid( $comment->comment_post_ID ) . '#comment-' . $comment->comment_ID;
        }//289
        /**
         * @description Display the link to the comments.
         * @param null $comment
         */
        protected function _get_comment_feed_link( $comment = null ){
            return $this->_esc_url( $this->_apply_filters( 'comment_link',[$this,'__get_comment_link'],$comment ) );
        }//307
        public function comment_link( $comment = null ):void{
            echo $this->_get_comment_feed_link( $comment );
        }//307
        /**
         * @description Retrieve the current comment author for use in the feeds.
         * @return mixed
         */
        protected function _get_comment_feed_author_rss(){
            return $this->_apply_filters( 'comment_author_rss', $this->_get_comment_author() );
        }//327
        /**
         * @description Display the current comment author in the feed.
         */
        public function comment_author_rss():void{
            echo $this->_get_comment_feed_author_rss();
        }//345
    }
}else die;