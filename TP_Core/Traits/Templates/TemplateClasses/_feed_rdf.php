<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-3-2022
 * Time: 16:35
 */
namespace TP_Core\Traits\Templates\TemplateClasses;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Feed\_feed_01;
use TP_Core\Traits\Feed\_feed_04;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Options\_option_01;
if(ABSPATH){
    class _feed_rdf {
        use _formats_08;
        use _feed_01;
        use _feed_04;
        use _option_01;
        use _action_01;
        private $__html;
        public function __construct()
        {
        }
        private function __to_string():string{
            $this->__html = $this->_esc_html("header( 'Content-Type:'");
            $this->__html .= $this->_esc_html($this->_feed_content_type( 'rdf' ));
            $this->__html .= $this->_esc_html("; charset=");
            $this->__html .= $this->_esc_html($this->_get_option( 'blog_charset', true ));
            $this->__html .= $this->_esc_html(")");
            $this->__html .= $this->_esc_html("<?xml version='1.0' encoding='{$this->_get_option( 'blog_charset' )}'?>");
            ob_start();
                $this->_do_action( 'rss_tag_pre', 'rdf' );
            $this->__html .= ob_get_clean();
            $this->__html .= $this->_esc_html("<rdf:RDF");
            $this->__html .= $this->_esc_html(" xmlns='http://purl.org/rss/1.0/'");
            $this->__html .= $this->_esc_html(" xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'");
            $this->__html .= $this->_esc_html(" xmlns:dc='http://purl.org/dc/elements/1.1/'");
            $this->__html .= $this->_esc_html(" xmlns:sy='http://purl.org/rss/1.0/modules/syndication/'");
            $this->__html .= $this->_esc_html(" xmlns:admin='http://webns.net/mvcb/'");
            $this->__html .= $this->_esc_html(" xmlns:content='http://purl.org/rss/1.0/modules/content/'");
            ob_start();
                $this->_do_action( 'rdf_ns' );
            $this->__html .= ob_get_clean();
            $this->__html .= $this->_esc_html(">");
            $this->__html .= $this->_esc_html("<channel rdf:about='{$this->_get_bloginfo_rss( 'url' )}'>");
            $this->__html .= $this->_esc_html("<title>{$this->_get_tp_title_rss()}</title>");
            $this->__html .= $this->_esc_html("<link>{$this->_get_bloginfo_rss( 'url' )}</link>");
            $this->__html .= $this->_esc_html("");
            $this->__html .= $this->_esc_html("");
            $this->__html .= $this->_esc_html("");
            $this->__html .= $this->_esc_html("");
            $this->__html .= $this->_esc_html("</channel>");
            $this->__html .= $this->_esc_html("");
            $this->__html .= $this->_esc_html("");
            $this->__html .= $this->_esc_html("");
            $this->__html .= $this->_esc_html("");
            $this->__html .= $this->_esc_html("");
            $this->__html .= $this->_esc_html("");
            $this->__html .= $this->_esc_html("");
            return (string) $this->__html;
        }

        public function __toString()
        {
            return $this->__to_string();
        }
    }
}else die;
