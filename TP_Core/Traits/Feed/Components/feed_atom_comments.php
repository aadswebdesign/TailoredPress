<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-5-2022
 * Time: 08:26
 */
namespace TP_Core\Traits\Feed\Components;
use TP_Core\Traits\Comment\_comment_01;
use TP_Core\Traits\Constructs\_construct_post;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Inits\_init_comment;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\Query\_query_02;
use TP_Core\Traits\Query\_query_03;
use TP_Core\Traits\Query\_query_04;
use TP_Core\Traits\Query\_query_05;
use TP_Core\Traits\Templates\_author_template_01;
use TP_Core\Traits\Templates\_comment_template_02;
use TP_Core\Traits\Templates\_comment_template_03;
use TP_Core\Traits\Templates\_comment_template_04;
use TP_Core\Traits\Templates\_general_template_06;
use TP_Core\Traits\Templates\_general_template_08;
use TP_Core\Traits\Templates\_link_template_02;
use TP_Core\Traits\Templates\_link_template_03;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Templates\_post_template_01;
use TP_Core\Traits\Templates\_post_template_02;
use TP_Core\Traits\Templates\_post_template_03;
if(ABSPATH){
    class feed_atom_comments extends feed_base {
        use _I10n_01,_construct_post;
        use _query_02,_query_03, _query_04, _query_05, _formats_07;
        use _author_template_01, _post_template_01;
        use _general_template_06,_general_template_08;
        use _comment_template_02,_comment_template_03,_comment_template_04;
        use _link_template_02,_link_template_03, _link_template_09;
        use _post_template_02, _post_template_03;
        use _post_01, _post_04, _init_comment, _comment_01;
        public function __construct(){
            parent::__construct($this->_args);
            $this->_args['feed_type'] = 'atom';
        }
        private function __to_string():string{
            $feed_block = " xmlns='" . W3_XMLNS . "' xmlns:thr='". PURL_THR . "'";
            $this->_xml  = TP_XML ."version='1.0' encoding='{$this->_get_option( 'blog_charset' )}' " . TP_XML_END;
            $this->_xml .= $this->_do_action( 'rss_tag_pre', 'atom-comments' );
            $this->_xml .= "<feed $feed_block xml:lang='{$this->_get_bloginfo_rss( 'language' )}'";
            $this->_xml .= $this->_do_action( 'atom_ns' );
            $this->_xml .= ">";
            $this->_xml .= "<title type='text'>";
            ob_start(); /* %s are for translators */
            if ( $this->_is_singular() ) printf( $this->_ent2ncr( $this->__( 'Comments on %s' ) ), $this->_get_the_title_rss() );
            elseif ( $this->_is_search()) printf( $this->_ent2ncr( $this->__( 'Comments for %1$s searching on %2$s' ) ), $this->_get_bloginfo_rss( 'name' ), $this->_get_search_query() );
            else printf( $this->_ent2ncr( $this->__( 'Comments for %s' ) ), $this->_get_tp_title_rss() );
            $this->_xml .= ob_get_clean();
            $this->_xml .= "</title>";
            $this->_xml .= "<subtitle type='text'>{$this->_get_bloginfo_rss( 'description' )}</subtitle>";
            $this->_xml .= "<updated>{$this->_get_feed_build_date( 'Y-m-d\TH:i:s\Z' )}</updated>";
            if ( $this->_is_singular() ){
                $this->_xml .= "<link rel='alternate' type='text/html' href='{$this->_get_comments_link_feed()}'/>";
                $this->_xml .= "<link rel='self' type='application/atom+xml' href='{$this->_esc_url( $this->_get_post_comments_feed_link( '', 'atom' ) )}' />";
                $this->_xml .= "<id>{$this->_esc_url( $this->_get_post_comments_feed_link( '', 'atom' ) )}</id>";
            }elseif ( $this->_is_search()){
                $this->_xml .= "<link rel='alternate' type='text/html' href='{$this->_home_url()}?s={$this->_get_search_query()}'/>";
                $this->_xml .= "<link rel='self' type='application/atom+xml' href='{$this->_get_search_comments_feed_link( '', 'atom' )}' />";
                $this->_xml .= "<id>{$this->_get_search_comments_feed_link( '', 'atom' )}</id>";
            }else{
                $this->_xml .= "<link rel='alternate' type='text/html' href='{$this->_get_bloginfo_rss( 'url' )}'/>";
                $this->_xml .= "<link rel='self' type='application/atom+xml' href='{$this->_get_bloginfo_rss( 'comments_atom_url' )}' />";
                $this->_xml .= "<id>{$this->_get_bloginfo_rss( 'comments_atom_url' )}</id>";
            }
            $this->_xml .= $this->_do_action( 'comments_atom_head' );
            while ( $this->_have_comments()){
                ob_start();
                $this->_the_comment();
                $comment = $this->_init_comment();
                $comment_post    = $this->_get_post( $comment->comment_post_ID );
                $this->tp_post['post'] = $comment_post;
                $this->_xml .= ob_get_clean();
                $this->_xml .= "<entry>";
                $this->_xml .= "<title>";
                ob_start();
                if ( ! $this->_is_singular() ) {
                    $title = $this->_get_the_title( $comment_post->ID );
                    $title = $this->_apply_filters( 'the_title_rss', $title );
                    printf( $this->_ent2ncr( $this->__( 'Comment on %1$s by %2$s' ) ), $title, $this->_get_comment_feed_author_rss() );
                }else printf( $this->_ent2ncr( $this->__( 'By: %s' ) ), $this->_get_comment_feed_author_rss() );
                $this->_xml .= ob_get_clean();
                $this->_xml .= "</title>";
                $this->_xml .= "<link rel='alternate' type='html' href='{$this->_get_comment_link()}' />";
                $this->_xml .= "<author>";
                $this->_xml .= "<name>{$this->_get_comment_feed_author_rss()}</name>";
                if ( $this->_get_comment_author_url() ) $this->_xml .= "<uri>{$this->_get_comment_author_url()}</uri>";
                $this->_xml .= "</author>";
                $this->_xml .= "<id>{$this->_get_the_guid()}</id>";
                $this->_xml .= "<updated>{$this->_mysql2date( 'Y-m-d\TH:i:s\Z', $this->_get_comment_time( 'Y-m-d H:i:s', true, false ), false )}</updated>";
                $this->_xml .= "<published>{$this->_mysql2date( 'Y-m-d\TH:i:s\Z', $this->_get_comment_time( 'Y-m-d H:i:s', true, false ), false )}</published>";
                if ( $this->_post_password_required( $comment_post ) ){
                    $this->_xml .= "<content type='html' xml:base='{$this->_get_comment_link()}'>";
                    $this->_xml .= TP_CDATA . $this->_get_the_password_form() . TP_CDATA_END;
                    $this->_xml .= "</content>";
                }else{
                    $this->_xml .= "<content type='html' xml:base='{$this->_get_comment_link()}'>";
                    $this->_xml .= TP_CDATA . $this->_get_comment_text() . TP_CDATA_END;
                    $this->_xml .= "</content>";
                }
                if ( 0 === $comment->comment_parent )
                    $this->_xml .= "<thr:in-reply-to type='html' ref='{$this->_get_the_guid()}' href='{$this->_get_the_permalink_feed_rss()}' />";
                else{
                    $parent_comment = $this->_get_comment( $comment->comment_parent );
                    $this->_xml .= "<thr:in-reply-to type='html'  ref='{$this->_get_the_guid($parent_comment)}' href='{{$this->_get_comment_link($parent_comment)}}' />";
                }
                $this->_xml .= $this->_do_action( 'comment_atom_entry', $comment->comment_ID, $comment_post->ID );
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