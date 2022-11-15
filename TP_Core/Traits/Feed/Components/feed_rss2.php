<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-5-2022
 * Time: 08:26
 */
namespace TP_Core\Traits\Feed\Components;
use TP_Core\Traits\Templates\_link_template_02;
use TP_Core\Traits\Templates\_post_template_01;
use TP_Core\Traits\Templates\_comment_template_03;
use TP_Core\Traits\Templates\_comment_template_04;
use TP_Core\Traits\Templates\_author_template_01;
use TP_Core\Traits\Query\_query_04;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\Formats\_formats_07;
if(ABSPATH){
    class feed_rss2 extends feed_base {
        use _post_template_01;
        use _formats_07,_post_04, _query_04,_link_template_02;
        use _comment_template_03,_comment_template_04,_author_template_01;
        public function __construct(){
            parent::__construct($this->_args);
            $this->_args['feed_type'] = 'rss2';
        }
        private function __to_string():string{
            $duration = 'hourly';
            $frequency = '1';
            $feed_block = " xmlns:content='" . PURL_CONTENT . "' xmlns:wfw='". WFW_COMMENT_API ."' xmlns:dc='" . PURL_DC ."' xmlns:atom='" . W3_ATOM ."' xmlns:sy='" . PURL_SY ."' xmlns:slash='" . PURL_SLASH ."'";
            $this->_xml  = TP_XML ."version='1.0' encoding='{$this->_get_option( 'blog_charset' )}' " . TP_XML_END;
            $this->_xml .= "<rss version='2.0' $feed_block>";
            $this->_xml .= $this->_do_action( 'rss2_ns' );
            $this->_xml .= "<channel>";
            $this->_xml .= "<title>{$this->_get_tp_title_rss()}</title>";
            $this->_xml .= "<atom:link rel='self' type='application/rss+xml' href='{$this->_get_self_link()}' />";
            $this->_xml .= "<link>{$this->_get_bloginfo_rss( 'url' )}</link>";
            $this->_xml .= "<lastBuildDate>{$this->_get_feed_build_date( 'D, d M Y H:i:s +0000' )}<lastBuildDate>";
            $this->_xml .= "<language>{$this->_get_bloginfo_rss( 'language' )}</language>";
            $this->_xml .= "<sy:updatePeriod>{$this->_apply_filters( 'rss_update_period', $duration )}</sy:updatePeriod>";
            $this->_xml .= "<sy:updateFrequency>{$this->_apply_filters( 'rss_update_frequency', $frequency )}</sy:updateFrequency>";
            while ( $this->_have_posts()){
                $post = $this->_init_post();
                $this->_xml .= $this->_get_posts();
                $this->_xml .= "<item>";
                $this->_xml .= "<title>{$this->_get_the_title_rss()}</title>";
                $this->_xml .= "<link>{$this->_get_the_permalink_feed_rss()}</link>";
                if ( $this->_get_comments_number() || $this->_comments_open() )
                    $this->_xml .= "<comments>{$this->_get_comments_link_feed()}</comments>";
                $this->_xml .= "<dc:creator>" . TP_CDATA . $this->_get_the_author() . TP_CDATA_END . "</dc:creator>";
                $this->_xml .= "<pubDate>{$this->_mysql2date( 'Y-m-d\TH:i:s\Z', $post->post_date_gmt, false )}<pubDate>";
                $this->_xml .= $this->_get_the_category_rss( 'rss2' );
                $this->_xml .= "<guid isPermaLink='false'>{$this->_get_the_guid()}</guid>";
                if ( $this->_get_option( 'rss_use_excerpt' ) )
                    $this->_xml .= "<description>" . TP_CDATA . $this->_get_the_excerpt_rss(). TP_CDATA_END. "</description>";
                else{
                    $this->_xml .= "<description>" . TP_CDATA . $this->_get_the_excerpt_rss(). TP_CDATA_END. "</description>";
                    $content = $this->_get_the_content_feed( 'rss2' );
                    if ($content !== '') $this->_xml .= "<content:encoded>" . TP_CDATA . $content . TP_CDATA_END. "</content:encoded>";
                    else $this->_xml .= "<content:encoded>" . TP_CDATA . $this->_get_the_excerpt_rss() . TP_CDATA_END. "</content:encoded>";
                }
                if ( $this->_get_comments_number() || $this->_comments_open() ){
                    $this->_xml .= "<wfw:commentRss>{$this->_esc_url( $this->_get_post_comments_feed_link( null, 'rss2' ) )}</wfw:commentRss>";
                    $this->_xml .= "<slash:comments>{$this->_get_comments_number()}</slash:comments>";
                }
                $this->_xml .= $this->_get_rss_enclosure();
                $this->_xml .= $this->_do_action( 'rss2_item' );
                $this->_xml .= "</item>";
            }
            $this->_xml .= "<channel></rss>";
            return (string) $this->_xml;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;
