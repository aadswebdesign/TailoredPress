<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 20:22
 */
namespace TP_Core\Traits\Feed;
use TP_Core\Traits\Feed\Components\feed_atom_comments;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_simplepie;
use TP_Core\Libs\SimplePie\SimplePie;
use TP_Core\Libs\SimplePie\SP_Components\TP_SimplePie_Sanitize_KSES;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_Cache;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _feed_04 {
        use _init_queries;
        use _init_simplepie;
        /**
         * @description Displays Site Icon in atom feeds.
         */
        public function atom_site_icon():void{
            $url = $this->_get_site_icon_url( 32 );
            if ( $url ) echo "<icon>{$this->_convert_chars( $url )}</icon>\n";
        }//622
        /**
         * @description Displays Site Icon in RSS2.
         * @return string
         */
        protected function _rss2_site_icon():string{
            $rss_title = $this->_get_tp_title_rss();
            if ( empty( $rss_title ) )
                $rss_title = $this->_get_bloginfo_rss( 'name' );
            $xml = '';
            $url = $this->_get_site_icon_url( 32 );
            if ( $url ){
                $xml .= "<image>";
                $xml .= "<url>{$this->_convert_chars( $url )}</url>";
                $xml .= "<title>$rss_title</title>";
                $xml .= "<link>{$this->_get_bloginfo_rss('url')}</link>";
                $xml .= "<width>32</width>";
                $xml .= "<height>32</height>";
                $xml .= "</image>\n";
            }
            return $xml;
        }//634
        public function rss2_site_icon():void{
            echo $this->_rss2_site_icon();
        }
        /**
         * @description Returns the link for the currently displayed feed.
         * @return mixed
         */
        protected function _get_self_link(){
            $host = parse_url( $this->_home_url() );
            return $this->_set_url_scheme( 'http://' . $host['host'] . $this->_tp_unslash( $_SERVER['REQUEST_URI'] ) );
        }//660
        /**
         * @description Display the link for the currently displayed feed in a XSS safe way.
         */
        public function self_link():void{
            echo $this->_esc_url( $this->_apply_filters( 'self_link', [$this,'_get_self_link'] ) );
        }//672
        /**
         * @description Get the UTC time of the most recently modified post from TP_Query
         * @param $format
         * @return mixed
         */
        protected function _get_feed_build_date( $format ){
            $tp_query = $this->_init_query();
            $datetime          = false;
            $max_modified_time = false;
            $utc = new \DateTimeZone( 'UTC' );
            if ( $tp_query !== null && $tp_query->have_posts() ) {
                $modified_times = $this->_tp_list_pluck( $tp_query->posts, 'post_modified_gmt' );
                if ($tp_query->comment_count && $tp_query->is_comment_feed()) {
                    $comment_times = $this->_tp_list_pluck( $tp_query->comments, 'comment_date_gmt' );
                    $modified_times = array_merge( $modified_times, $comment_times );
                }
                $datetime = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', max( $modified_times ), $utc );
            }
            if ( false === $datetime )
                $datetime = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $this->_get_last_post_modified( 'GMT' ), $utc );
            if ( false !== $datetime )
                $max_modified_time = $datetime->format( $format );
            return $this->_apply_filters( 'get_feed_build_date', $max_modified_time, $format );
        }//699
        /**
         * @description Return the content type for specified feed type.
         * @param string $type
         * @return mixed
         */
        protected function _feed_content_type($type =''){
            if ( empty( $type )) $type = $this->_get_default_feed();
            $types = ['rss' => 'application/rss+xml','rss2' => 'application/rss+xml','rss-http' => 'text/xml',
                'atom' => 'application/atom+xml','rdf' => 'application/rdf+xml',];
            $content_type = ( ! empty( $types[ $type ] ) ) ? $types[ $type ] : 'application/octet-stream';
            return $this->_apply_filters( 'feed_content_type', $content_type, $type );
        }//751
        /**
         * @description Build SimplePie object based on RSS or Atom feed from URL.
         * @param $url
         * @return TP_Error|SimplePie
         */
        protected function _fetch_feed( $url ){
            $feed = new SimplePie();
            $feed->sp_set_sanitize_class( 'TP_SimplePie_Sanitize_KSES' );
            $feed->sp_sanitize = new TP_SimplePie_Sanitize_KSES();
            SimplePie_Cache::register( 'tp_transient', 'TP_Feed_Cache_Transient' );
            $feed->sp_set_cache_location( 'tp_transient' );
            $feed->sp_set_file_class( 'TP_SimplePie_File' );
            $feed->sp_set_feed_url( $url );
            $feed->sp_set_cache_duration( $this->_apply_filters( 'tp_feed_cache_transient_lifetime', 12 * HOUR_IN_SECONDS, $url ) );
            $this->_do_action_ref_array( 'tp_feed_options', array( &$feed, $url ) );
            $feed->sp_init();
            $feed->sp_set_output_encoding( $this->_get_option( 'blog_charset' ) );
            if ($feed->sp_errors() ) return new TP_Error( 'simplepie-error', $feed->sp_errors() );
            return $feed;
        }//787
        public function tp_feed_atom_comments():void{
            echo new feed_atom_comments();
        }
    }
}else die;