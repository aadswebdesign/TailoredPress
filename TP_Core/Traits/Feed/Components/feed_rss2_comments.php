<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-5-2022
 * Time: 08:26
 */
namespace TP_Core\Traits\Feed\Components;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Query\_query_05;
use TP_Core\Traits\Query\_query_03;
use TP_Core\Traits\Query\_query_04;
use TP_Core\Traits\Templates\_general_template_08;
use TP_Core\Traits\Templates\_post_template_01;
use TP_Core\Traits\Templates\_post_template_02;
use TP_Core\Traits\Templates\_post_template_03;
use TP_Core\Traits\Templates\_comment_template_03;
use TP_Core\Traits\Inits\_init_comment;
use TP_Core\Traits\Constructs\_construct_post;
if(ABSPATH){
    class feed_rss2_comments extends feed_base {
        use _I10n_01;
        use _formats_07,_query_03, _query_04,_query_05;
        use _post_template_01,_post_template_02, _post_template_03;
        use _init_comment,_post_01,_general_template_08;
        use _comment_template_03,_construct_post;
        public function __construct(){
            parent::__construct($this->_args);
            $this->_args['feed_type'] = 'rss2';
        }
        private function __to_string():string{
            $link = ($this->_is_single() ) ? $this->_get_the_permalink_feed_rss() : $this->_get_bloginfo_rss( 'url' );
            $feed_block = " xmlns:content='" . PURL_CONTENT . "' xmlns:dc='" . PURL_DC ."' xmlns:atom='" . W3_ATOM ."' xmlns:sy='" . PURL_SY ."'";
            $this->_xml  = TP_XML ."version='1.0' encoding='{$this->_get_option( 'blog_charset' )}' " . TP_XML_END;
            $this->_xml .= $this->_do_action( 'rss_tag_pre', 'rss2_comments' );
            $this->_xml .= "<rss version='2.0' $feed_block>";
            $this->_xml .= $this->_do_action( 'rss2_ns' );
            $this->_xml .= $this->_do_action( 'rss2_comments_ns' );
            $this->_xml .= "<channel>";
            $this->_xml .= "<title>";
            ob_start();/* %s are for translators */
            if ( $this->_is_singular() ) printf( $this->_ent2ncr( $this->__( 'Comments on %s' ) ), $this->_get_the_title_rss() );
            elseif ( $this->_is_search()) printf( $this->_ent2ncr( $this->__( 'Comments for %1$s searching on %2$s' ) ), $this->_get_bloginfo_rss( 'name' ), $this->_get_search_query() );
            else printf( $this->_ent2ncr( $this->__( 'Comments for %s' ) ), $this->_get_tp_title_rss() );
            $this->_xml .= ob_get_clean();
            $this->_xml .= "</title>";
            $this->_xml .= "<atom:link rel='self' type='application/rss+xml' href='{$this->_get_self_link()}' />";
            $this->_xml .= "<link>$link</link>";
            $this->_xml .= "<description>{$this->_get_bloginfo_rss( 'description' )}</description>";
            $this->_xml .= "<lastBuildDate>{$this->_get_feed_build_date( 'D, d M Y H:i:s +0000' )}<lastBuildDate>";
            $this->_xml .= "<sy:updatePeriod>{$this->_apply_filters( 'rss_update_period', 'hourly' )}</sy:updatePeriod>";
            $this->_xml .= "<sy:updateFrequency>{$this->_apply_filters( 'rss_update_frequency', '1' )}</sy:updateFrequency>";
            $this->_xml .= $this->_do_action( 'commentsrss2_head' );
            while ( $this->_have_comments()){
                ob_start();
                $post = $this->_init_post();
                $this->_the_comment();
                $comment = $this->_init_comment();
                $comment_post    = $this->_get_post( $comment->comment_post_ID );
                $this->tp_post['post'] = $comment_post;
                $this->_xml .= ob_get_clean();
                $this->_xml .= "<item>";
                $this->_xml .= "<title>";
                ob_start();
                if ( ! $this->_is_singular() ) {
                    $title = $this->_get_the_title( $comment_post->ID );
                    $title = $this->_apply_filters( 'the_title_rss', $title );
                    printf( $this->_ent2ncr( $this->__( 'Comment on %1$s by %2$s' ) ), $title, $this->_get_comment_feed_author_rss() );
                }else printf( $this->_ent2ncr( $this->__( 'By: %s' ) ), $this->_get_comment_feed_author_rss() );
                $this->_xml .= ob_get_clean();
                $this->_xml .= "</title>";
                $this->_xml .= "<link>{$this->_get_comment_link()}</link>";
                $this->_xml .= "<dc:creator>" . TP_CDATA . $this->_get_comment_feed_author_rss() . TP_CDATA_END . "</dc:creator>";
                $this->_xml .= "<pubDate>{$this->_mysql2date( 'Y-m-d\TH:i:s\Z', $post->post_date_gmt, false )}<pubDate>";
                $this->_xml .= "<guid isPermaLink='false'>{$this->_get_the_guid()}</guid>";
                if ( $this->_post_password_required( $comment_post ) ){
                    $this->_xml .= "<description>{$this->_ent2ncr( $this->__( 'Protected Comments: Please enter your password to view comments.' ) )}</description>";
                    $this->_xml .= "<content:encoded>".TP_CDATA . $this->_get_the_password_form() . TP_CDATA_END. "</content:encoded>";
                }else{
                    $this->_xml .= "<description>".TP_CDATA . $this->_get_comment_text_rss() . TP_CDATA_END. "</description>";
                    $this->_xml .= "<content:encoded>".TP_CDATA . $this->_get_comment_text() . TP_CDATA_END. "</content:encoded>";
                }
                $this->_xml .= $this->_do_action( 'commentrss2_item', $comment->comment_ID, $comment_post->ID );
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