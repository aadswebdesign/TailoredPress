<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 10:02
 */
namespace TP_Core\Traits\Feed;
if(ABSPATH){
    trait _feed_01 {
        /**
         * @description RSS container for the bloginfo function.
         * @param string $show
         * @return mixed
         */
        protected function _get_bloginfo_rss( $show = '' ){
            $info = strip_tags( $this->_get_bloginfo( $show ) );
            return $this->_apply_filters( 'get_bloginfo_rss', [$this,'__convert_chars'], $info, $show );
        }//27
        /**
         * @deprecated
         * @description Display RSS container for the bloginfo function.
         * @param string $show
         */
        public function bloginfo_rss( $show = '' ):void{
            echo $this->_apply_filters( 'bloginfo_rss', [$this, '_get_bloginfo_rss'], $show );
        }//56
        /**
         * @description Retrieve the default feed.
         * @return string
         */
        protected function _get_default_feed():string{
            $default_feed = $this->_apply_filters( 'default_feed', 'rss2' );
            return ( 'rss' === $default_feed ) ? 'rss2' : $default_feed;
        }//80
        /**
         * @description Retrieve the blog title for the feed title.
         * @return mixed
         */
        protected function _get_tp_title_rss(){
            return $this->_apply_filters( 'get_tp_title_rss', [$this, '__tp_get_document_title'] );
        }//103
        /**
         * @description  Display the blog title for display of the feed title.
         */
        public function tp_title_rss():void{
            echo $this->_apply_filters( 'tp_title_rss', [$this,'__get_tp_title_rss'] );
        }//129
        /**
         * @description Retrieve the current post title for the feed.
         * @return mixed
         */
        protected function _get_the_title_rss(){
            $title = $this->_get_the_title();
            return $this->_apply_filters( 'the_title_rss', $title );
        }//156
        /**
         * @description Display the post title in the feed.
         */
        protected function _the_title_rss():void{
            echo $this->_get_the_title_rss();
        }//174
        /**
         * @description Retrieve the post content for feeds.
         * @param null $feed_type
         * @return mixed
         */
        protected function _get_the_content_feed( $feed_type = null ){
            if ( ! $feed_type )$feed_type = $this->_get_default_feed();
            $content = $this->_apply_filters( 'the_content',[$this,'__get_the_content'] );
            $content = str_replace( ']]>', ']]&gt;', $content );
            return $this->_apply_filters( 'the_content_feed', $content, $feed_type );
        }//188
        /**
         * @description Display the post content for feeds.
         * @param null $feed_type
         */
        protected function _the_content_feed( $feed_type = null ):void{
            echo $this->_get_the_content_feed( $feed_type );
        }//216
        /**
         * @description Display the post excerpt for the feed.
         */
        protected function _get_the_excerpt_rss(){
            $output = $this->_get_the_excerpt();
            return $this->_apply_filters( 'the_excerpt_rss', $output );
        }//225
        public function the_excerpt_rss():void{
            echo $this->_get_the_excerpt_rss();
        }//225
    }
}else die;