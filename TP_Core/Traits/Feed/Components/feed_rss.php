<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-5-2022
 * Time: 08:26
 */
namespace TP_Core\Traits\Feed\Components;
use TP_Core\Traits\Query\_query_04;
use TP_Core\Traits\Post\_post_04;
if(ABSPATH){
    class feed_rss extends feed_base {
        use _post_04, _query_04;
        public function __construct(){
            parent::__construct($this->_args);
            $this->_args['feed_type'] = 'rss';
        }
        private function __to_string():string{
            $this->_xml  = TP_XML ."version='1.0' encoding='{$this->_get_option( 'blog_charset' )}' " . TP_XML_END;
            $this->_xml .= "<rss version='0.92'>";
            $this->_xml .= "<channel>";
            $this->_xml .= "<title>{$this->_get_tp_title_rss()}</title>";
            $this->_xml .= "<link>{$this->_get_bloginfo_rss( 'url' )}</link>";
            $this->_xml .= "<description>{$this->_get_bloginfo_rss( 'description' )}</description>";
            $this->_xml .= "<lastBuildDate>{$this->_get_feed_build_date( 'D, d M Y H:i:s +0000' )}<lastBuildDate>";
            $this->_xml .= "<docs>http://backend.userland.com/rss092</docs>";
            $this->_xml .= "<language>{$this->_get_bloginfo_rss( 'language' )}</language>";
            $this->_xml .= $this->_do_action( 'rss_head' );
            while ( $this->_have_posts()){
                $this->_xml .= $this->_get_posts();
                $this->_xml .= "<item>";
                $this->_xml .= "<title>{$this->_get_the_title_rss()}</title>";
                $this->_xml .= "<description>" . TP_CDATA . $this->_get_the_excerpt_rss(). TP_CDATA_END."</description>";
                $this->_xml .= "<link>{$this->_get_the_permalink_feed_rss()}</link>";
                $this->_xml .= $this->_do_action( 'rss_item' );
                $this->_xml .= "</item>";
            }
            $this->_xml .= "</channel></rss>";
            return (string) $this->_xml;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;
