<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-5-2022
 * Time: 08:26
 */
namespace TP_Core\Traits\Feed\Components;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\Query\_query_04;
use TP_Core\Traits\Templates\_author_template_01;
if(ABSPATH){
    class feed_rdf extends feed_base {
        use _formats_07, _query_04,_author_template_01;
        use _post_04;
        public function __construct(){
            parent::__construct($this->_args);
            $this->_args['feed_type'] = 'rdf';
        }
        private function __to_string():string{
            $feed_block = " xmlns='" . PURL . "' xmlns:rdf='". W3_RDF . "' xmlns:dc='" . PURL_DC ."' xmlns:sy='" . PURL_SY . "' xmlns:admin='". WEB_NS_MVCB . "' xmlns:content='" . PURL_CONTENT . "'";
            $this->_xml  = TP_XML ."version='1.0' encoding='{$this->_get_option( 'blog_charset' )}' " . TP_XML_END;
            $this->_xml .= $this->_do_action( 'rss_tag_pre', 'rdf' );
            $this->_xml .= "<rdf:RDF $feed_block xml:lang='{$this->_get_bloginfo_rss( 'language' )}'";
            $this->_xml .= $this->_do_action( 'rdf_ns' ).">";
            $this->_xml .= "<channel rdf:about='{$this->_get_bloginfo_rss( 'url' )}'>";
            $this->_xml .= "<title>{$this->_get_tp_title_rss()}</title>";
            $this->_xml .= "<link>{$this->_get_bloginfo_rss( 'url' )}</link>";
            $this->_xml .= "<description>{$this->_get_bloginfo_rss( 'description' )}</description>";
            $this->_xml .= "<dc:date>{$this->_get_feed_build_date( 'Y-m-d\TH:i:s\Z' )}</dc:date>";
            $this->_xml .= "<sy:updatePeriod>{$this->_apply_filters( 'rss_update_period', 'hourly' )}</sy:updatePeriod>";
            $this->_xml .= "<sy:updateFrequency>{$this->_apply_filters( 'rss_update_frequency', '1' )}</sy:updateFrequency>";
            $this->_xml .= "<sy:updateBase>2000-01-01T12:00+00:00</sy:updateBase>";
            $this->_xml .= $this->_do_action( 'rdf_header' );
            $this->_xml .= "<items><rdf:Seq>";
            while ( $this->_have_posts()){
                $this->_xml .= $this->_get_posts();
                $this->_xml .= "<rdf:li rdf:resource='{$this->_get_the_permalink_feed_rss()}' />";
            }
            $this->_xml .= "</rdf:Seq></items>";
            $this->_xml .= "</channel>";
            ob_start();
            $this->_rewind_posts();
            $this->_xml .= ob_get_clean();
            while ( $this->_have_posts()){
                $post = $this->_init_post();
                $this->_xml .= $this->_get_posts();
                $this->_xml .= "<item rdf:about='{$this->_get_the_permalink_feed_rss()}'>";
                $this->_xml .= "<title>{$this->_get_the_title_rss()}</title>";
                $this->_xml .= "<link>{$this->_get_the_permalink_feed_rss()}</link>";
                $this->_xml .= "<dc:creator>" . TP_CDATA . $this->_get_the_author() . TP_CDATA_END . "</dc:creator>";
                $this->_xml .= "<dc:date>{$this->_mysql2date( 'Y-m-d\TH:i:s\Z', $post->post_date_gmt, false )}</dc:date>";
                $this->_xml .= $this->_get_the_category_rss( 'rdf' );
                if ( $this->_get_option( 'rss_use_excerpt' ) ) $this->_xml .= "<description>" . TP_CDATA . $this->_get_the_excerpt_rss() . TP_CDATA_END . "</description>";
                else {
                    $this->_xml .= "<description>" . TP_CDATA . $this->_get_the_excerpt_rss() . TP_CDATA_END . "</description>";
                    $this->_xml .= "<content:encoded>" . TP_CDATA . $this->_get_the_content_feed( 'rdf' ) . TP_CDATA_END . "</content:encoded>";
                }
                $this->_xml .= $this->_do_action( 'rdf_item' );
                $this->_xml .= "</item>";
            }
            $this->_xml .= "</rdf:RDF>";
            return (string) $this->_xml;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;
