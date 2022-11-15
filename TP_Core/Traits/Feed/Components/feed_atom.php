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
use TP_Core\Traits\Templates\_comment_template_03;
use TP_Core\Traits\Templates\_comment_template_04;
use TP_Core\Traits\Templates\_general_template_06;
use TP_Core\Traits\Templates\_link_template_02;
use TP_Core\Traits\Templates\_post_template_01;
if(ABSPATH){
    class feed_atom extends feed_base {
        use _query_04, _post_04, _formats_07;
        use _author_template_01, _post_template_01;
        use _general_template_06, _comment_template_03;
        use _comment_template_04, _link_template_02;
        public function __construct(){
            parent::__construct($this->_args);
            $this->_args['feed_type'] = 'atom';
        }
        private function __to_string():string{
            $feed_block = " xmlns='" . W3_XMLNS . "' xmlns:thr='". PURL_THR . "'";
            $this->_xml  = TP_XML ."version='1.0' encoding='{$this->_get_option( 'blog_charset' )}' " . TP_XML_END;
            $this->_xml .= $this->_do_action( 'rss_tag_pre', 'atom' );
            $this->_xml .= "<feed $feed_block xml:lang='{$this->_get_bloginfo_rss( 'language' )}'";
            $this->_xml .= $this->_do_action( 'atom_ns' );
            $this->_xml .= ">";
            $this->_xml .= "<title type='text'>{$this->_get_tp_title_rss()}</title>";
            $this->_xml .= "<subtitle type='text'>{$this->_get_bloginfo_rss( 'description' )}</subtitle>";
            $this->_xml .= "<updated>{$this->_get_feed_build_date( 'Y-m-d\TH:i:s\Z' )}</updated>";
            $this->_xml .= "<link rel='self' type='application/atom+xml' href='{$this->_get_self_link()}' />";
            $this->_xml .= $this->_do_action( 'atom_head' );
            while ( $this->_have_posts()){
                $this->_xml .= $this->_get_posts();
                $this->_xml .= "<entry>";
                $this->_xml .= "<author>";
                $this->_xml .= "<name>{$this->_get_the_author()}</name>";
                $author_url = $this->_get_the_author_meta( 'url' );
                if ( ! empty( $author_url ) )
                    $this->_xml .= "<uri>$author_url</uri>";
                $this->_xml .= $this->_do_action( 'atom_author' );
                $this->_xml .= "</author>";
                $this->_xml .= "<title type='html'>". TP_CDATA . $this->_get_tp_title_rss() . TP_CDATA_END. "</title>";
                $this->_xml .= "<link rel='alternate' type='html' href='{$this->_get_the_permalink_feed_rss()}' />";
                $this->_xml .= "<id>{$this->_get_the_guid()}</id>";
                $this->_xml .= "<updated>{$this->_get_post_modified_time( 'Y-m-d\TH:i:s\Z', true )}</updated>";
                $this->_xml .= "<published>{$this->_get_post_time( 'Y-m-d\TH:i:s\Z', true )}</published>";
                $this->_xml .= $this->_get_the_category_rss();
                $this->_xml .= "<summary type='html'>" . TP_CDATA . $this->_get_the_excerpt_rss() . TP_CDATA_END. "</summary>";
                if ( ! $this->_get_option( 'rss_use_excerpt' ) )
                    $this->_xml .= "<content type='html' xml:base='{$this->_get_the_permalink_feed_rss()}'>" . TP_CDATA . $this->_get_the_content_feed() . TP_CDATA_END. "</content>";
                $this->_xml .= $this->_get_atom_enclosure();
                $this->_xml .= $this->_do_action( 'atom_entry' );
                if ( $this->_get_comments_number() || $this->_comments_open() ){
                    $this->_xml .= "<link rel='replies' type='html' href='{$this->_get_the_permalink_feed_rss()}' />";
                    $this->_xml .= "<link rel='replies' type='application/atom+xml' href='{$this->_esc_url( $this->_get_post_comments_feed_link( 0, 'atom' ) )}'/>";
                    $this->_xml .= "<thr:total>{$this->_get_comments_number()}</thr:total>";
                }
                $this->_xml .= "</entry>";
            }
            $this->_xml .= "</feed>";
            return (string) $this->_xml;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;