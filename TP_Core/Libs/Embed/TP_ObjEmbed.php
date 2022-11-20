<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-5-2022
 * Time: 23:33
 */
namespace TP_Core\Libs\Embed;
use TP_Core\Libs\TP_Error;
if(ABSPATH) {
    class TP_ObjEmbed extends Embed_Base {
        //todo nowhere used private $__compat_methods = ['__fetch_with_format', '__parse_json', '__parse_xml', '__parse_xml_body'];
        public function __construct() {
            $host = urlencode( $this->_home_url() );
            $providers = [
                '#https?://((m|www)\.)?youtube\.com/watch.*#i' => array( 'https://www.youtube.com/oembed', true ),
                '#https?://((m|www)\.)?youtube\.com/playlist.*#i' => array( 'https://www.youtube.com/oembed', true ),
                '#https?://((m|www)\.)?youtube\.com/shorts/*#i' => array( 'https://www.youtube.com/oembed', true ),
                '#https?://youtu\.be/.*#i'                     => array( 'https://www.youtube.com/oembed', true ),
                '#https?://(.+\.)?vimeo\.com/.*#i'             => array( 'https://vimeo.com/api/oembed.{format}', true ),
                '#https?://(www\.)?dailymotion\.com/.*#i'      => array( 'https://www.dailymotion.com/services/oembed', true ),
                '#https?://dai\.ly/.*#i'                       => array( 'https://www.dailymotion.com/services/oembed', true ),
                '#https?://(www\.)?flickr\.com/.*#i'           => array( 'https://www.flickr.com/services/oembed/', true ),
                '#https?://flic\.kr/.*#i'                      => array( 'https://www.flickr.com/services/oembed/', true ),
                '#https?://(.+\.)?smugmug\.com/.*#i'           => array( 'https://api.smugmug.com/services/oembed/', true ),
                '#https?://(www\.)?scribd\.com/(doc|document)/.*#i' => array( 'https://www.scribd.com/services/oembed', true ),
                '#https?://wordpress\.tv/.*#i'                 => array( 'https://wordpress.tv/oembed/', true ),
                '#https?://(.+\.)?polldaddy\.com/.*#i'         => array( 'https://api.crowdsignal.com/oembed', true ),
                '#https?://poll\.fm/.*#i'                      => array( 'https://api.crowdsignal.com/oembed', true ),
                '#https?://(.+\.)?survey\.fm/.*#i'             => array( 'https://api.crowdsignal.com/oembed', true ),
                '#https?://(www\.)?twitter\.com/\w{1,15}/status(es)?/.*#i' => array( 'https://publish.twitter.com/oembed', true ),
                '#https?://(www\.)?twitter\.com/\w{1,15}$#i'   => array( 'https://publish.twitter.com/oembed', true ),
                '#https?://(www\.)?twitter\.com/\w{1,15}/likes$#i' => array( 'https://publish.twitter.com/oembed', true ),
                '#https?://(www\.)?twitter\.com/\w{1,15}/lists/.*#i' => array( 'https://publish.twitter.com/oembed', true ),
                '#https?://(www\.)?twitter\.com/\w{1,15}/timelines/.*#i' => array( 'https://publish.twitter.com/oembed', true ),
                '#https?://(www\.)?twitter\.com/i/moments/.*#i' => array( 'https://publish.twitter.com/oembed', true ),
                '#https?://(www\.)?soundcloud\.com/.*#i'       => array( 'https://soundcloud.com/oembed', true ),
                '#https?://(.+?\.)?slideshare\.net/.*#i'       => array( 'https://www.slideshare.net/api/oembed/2', true ),
                '#https?://(open|play)\.spotify\.com/.*#i'     => array( 'https://embed.spotify.com/oembed/', true ),
                '#https?://(.+\.)?imgur\.com/.*#i'             => array( 'https://api.imgur.com/oembed', true ),
                '#https?://(www\.)?meetu(\.ps|p\.com)/.*#i'    => array( 'https://api.meetup.com/oembed', true ),
                '#https?://(www\.)?issuu\.com/.+/docs/.+#i'    => array( 'https://issuu.com/oembed_wp', true ),
                '#https?://(www\.)?mixcloud\.com/.*#i'         => array( 'https://www.mixcloud.com/oembed', true ),
                '#https?://(www\.|embed\.)?ted\.com/talks/.*#i' => array( 'https://www.ted.com/services/v1/oembed.{format}', true ),
                '#https?://(www\.)?(animoto|video214)\.com/play/.*#i' => array( 'https://animoto.com/oembeds/create', true ),
                '#https?://(.+)\.tumblr\.com/post/.*#i'        => array( 'https://www.tumblr.com/oembed/1.0', true ),
                '#https?://(www\.)?kickstarter\.com/projects/.*#i' => array( 'https://www.kickstarter.com/services/oembed', true ),
                '#https?://kck\.st/.*#i'                       => array( 'https://www.kickstarter.com/services/oembed', true ),
                '#https?://cloudup\.com/.*#i'                  => array( 'https://cloudup.com/oembed', true ),
                '#https?://(www\.)?reverbnation\.com/.*#i'     => array( 'https://www.reverbnation.com/oembed', true ),
                '#https?://videopress\.com/v/.*#'              => array( 'https://public-api.wordpress.com/oembed/?for=' . $host, true ),
                '#https?://(www\.)?reddit\.com/r/[^/]+/comments/.*#i' => array( 'https://www.reddit.com/oembed', true ),
                '#https?://(www\.)?speakerdeck\.com/.*#i'      => array( 'https://speakerdeck.com/oembed.{format}', true ),
                '#https?://(www\.)?screencast\.com/.*#i'       => array( 'https://api.screencast.com/external/oembed', true ),
                '#https?://([a-z0-9-]+\.)?amazon\.(com|com\.mx|com\.br|ca)/.*#i' => array( 'https://read.amazon.com/kp/api/oembed', true ),
                '#https?://([a-z0-9-]+\.)?amazon\.(co\.uk|de|fr|it|es|in|nl|ru)/.*#i' => array( 'https://read.amazon.co.uk/kp/api/oembed', true ),
                '#https?://([a-z0-9-]+\.)?amazon\.(co\.jp|com\.au)/.*#i' => array( 'https://read.amazon.com.au/kp/api/oembed', true ),
                '#https?://([a-z0-9-]+\.)?amazon\.cn/.*#i'     => array( 'https://read.amazon.cn/kp/api/oembed', true ),
                '#https?://(www\.)?a\.co/.*#i'                 => array( 'https://read.amazon.com/kp/api/oembed', true ),
                '#https?://(www\.)?amzn\.to/.*#i'              => array( 'https://read.amazon.com/kp/api/oembed', true ),
                '#https?://(www\.)?amzn\.eu/.*#i'              => array( 'https://read.amazon.co.uk/kp/api/oembed', true ),
                '#https?://(www\.)?amzn\.in/.*#i'              => array( 'https://read.amazon.in/kp/api/oembed', true ),
                '#https?://(www\.)?amzn\.asia/.*#i'            => array( 'https://read.amazon.com.au/kp/api/oembed', true ),
                '#https?://(www\.)?z\.cn/.*#i'                 => array( 'https://read.amazon.cn/kp/api/oembed', true ),
                '#https?://www\.someecards\.com/.+-cards/.+#i' => array( 'https://www.someecards.com/v2/oembed/', true ),
                '#https?://www\.someecards\.com/usercards/viewcard/.+#i' => array( 'https://www.someecards.com/v2/oembed/', true ),
                '#https?://some\.ly\/.+#i'                     => array( 'https://www.someecards.com/v2/oembed/', true ),
                '#https?://(www\.)?tiktok\.com/.*/video/.*#i'  => array( 'https://www.tiktok.com/oembed', true ),
                '#https?://([a-z]{2}|www)\.pinterest\.com(\.(au|mx))?/.*#i' => array( 'https://www.pinterest.com/oembed.json', true ),
                '#https?://(www\.)?wolframcloud\.com/obj/.+#i' => array( 'https://www.wolframcloud.com/oembed', true ),
            ];
            if ( ! empty( self::$early_providers['add'] ) ) {
                foreach ( self::$early_providers['add'] as $format => $data )
                    $providers[ $format ] = $data;
            }
            if ( ! empty( self::$early_providers['remove'] ) ) {
                foreach ( self::$early_providers['remove'] as $format )
                    unset( $providers[ $format ] );
            }
            self::$early_providers = array();
            $this->providers = $this->_apply_filters( 'obj_embed_providers', $providers );
            $this->_add_filter( 'obj_embed_data_parse', array( $this, 'strip_newlines' ), 10, 3 );
        }//50
        public function get_provider( $url,...$args){
            $args = $this->_tp_parse_args( $args );
            $provider = false;
            if (!isset($args['discover']))  $args['discover'] = true;
            foreach ( $this->providers as $match_mask => $data ) {
                @list( $provider_url, $regex ) = $data;
                if ( ! $regex ) {
                    $match_mask = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $match_mask ), '#' ) ) . '#i';
                    $match_mask = preg_replace( '|^#http\\\://|', '#https?\://', $match_mask );
                }
                if ( preg_match( $match_mask, $url ) ) {
                    $provider = str_replace( '{format}', 'json', $provider_url ); // JSON is easier to deal with than XML.
                    break;
                }
            }
            if ( ! $provider && $args['discover'] ) $provider = $this->discover( $url );
            return $provider;
        }//254
        public static function _add_provider_early( $format, $provider, $regex = false ): void{
            if ( empty( self::$early_providers['add'] ) )
                self::$early_providers['add'] = [];
            self::$early_providers['add'][ $format ] = array( $provider, $regex );
        }//303
        public static function _remove_provider_early( $format ): void{
            if ( empty( self::$early_providers['remove'] ) )
                self::$early_providers['remove'] = [];
            self::$early_providers['remove'][] = $format;
        }//326
        public function get_data( $url, ...$args){
            $args = $this->_tp_parse_args( $args );
            $provider = $this->get_provider( $url, $args );
            if ( ! $provider ) return false;
            $data = $this->fetch( $provider, $url, $args );
            if ( false === $data ) return false;
            return $data;
        }//346
        public function get_html( $url, $args = '' ){
            $pre = $this->_apply_filters( 'pre_oembed_result', null, $url, $args );
            if ( null !== $pre ) return $pre;
            $data = $this->get_data( $url, $args );
            if ( false === $data ) return false;
            return $this->_apply_filters( 'obj_embed_result', $this->data2html( $data, $url ), $url, $args );
        }//378
        public function discover( $url ){
            $providers = [];
            $args      =['limit_response_size' => 153600,]; // 150 KB
            $args = $this->_apply_filters( 'obj_embed_remote_get_args', $args, $url );
            $request = $this->_tp_safe_remote_get( $url, $args );
            $html    = $this->_tp_remote_retrieve_body( $request );
            if ( $html ) {
                $linktypes = $this->_apply_filters(
                    'obj_embed_link_types',
                    ['application/json+oembed' => 'json', 'text/xml+oembed' => 'xml', 'application/xml+oembed' => 'xml',]
                );
                $html_head_end = stripos( $html, '</head>' );
                if ( $html_head_end ) {
                    $html = substr( $html, 0, $html_head_end );
                }
                $tagfound = false;
                foreach ( $linktypes as $linktype => $format ) {
                    if ( stripos( $html, $linktype ) ) {
                        $tagfound = true;
                        break;
                    }
                }
                if ( $tagfound && preg_match_all( '#<link([^<>]+)/?>#iU', $html, $links ) ) {
                    foreach ( $links[1] as $link ) {
                        $atts = $this->_shortcode_parse_atts( $link );
                        if ( ! empty( $atts['type'] ) && ! empty( $linktypes[ $atts['type'] ] ) && ! empty( $atts['href'] ) ) {
                            $providers[ $linktypes[ $atts['type'] ] ] = htmlspecialchars_decode( $atts['href'] );
                            if ( 'json' === $linktypes[ $atts['type'] ] )  break;
                        }
                    }
                }
            }
            if ( ! empty( $providers['json'] ) )  return $providers['json'];
            elseif ( ! empty( $providers['xml'] ) ) return $providers['xml'];
            else return false;
        }//429
        public function fetch( $provider, $url, ...$args){
            $args = $this->_tp_parse_args( $args, $this->_tp_embed_defaults( $url ) );
            $provider = $this->_add_query_arg( 'maxwidth', (int) $args['width'], $provider );
            $provider = $this->_add_query_arg( 'maxheight', (int) $args['height'], $provider );
            $provider = $this->_add_query_arg( 'url', urlencode( $url ), $provider );
            $provider = $this->_add_query_arg( 'dnt', 1, $provider );
            $provider = $this->_apply_filters( 'obj_embed_fetch_url', $provider, $url, $args );
            foreach ( array( 'json', 'xml' ) as $format ) {
                $result = $this->__fetch_with_format( $provider, $format );
                if ( $this->_init_error( $result ) && 'not-implemented' === $result->get_error_code() )
                    continue;
                return ( $result && ! $this->_init_error( $result ) ) ? $result : false;
            }
            return false;
        }//522
        private function __fetch_with_format( $provider_url_with_args, $format ){
            $provider_url_with_args = $this->_add_query_arg( 'format', $format, $provider_url_with_args );
            $args = $this->_apply_filters( 'obj_embed_remote_get_args', array(), $provider_url_with_args );
            $response = $this->_tp_safe_remote_get( $provider_url_with_args, $args );
            if ( 501 === $this->_tp_remote_retrieve_response_code( $response ) )
                return new TP_Error( 'not-implemented' );
            $body = $this->_tp_remote_retrieve_body( $response );
            if ( ! $body ) return false;
            $parse_method = "_parse_$format";
            return $this->$parse_method( $body );
        }//562
        //todo nowhere used
        private function __parse_json( $response_body ){
            $data = json_decode(trim($response_body), false);
            return ( $data && is_object( $data ) ) ? $data : false;
        }//588
        protected function _parse_json( $response_body ){
            return $this->__parse_json( $response_body );
        }
        //todo nowhere used
        private function __parse_xml( $response_body ){
            if ( ! function_exists( 'libxml_disable_entity_loader' ) ) return false;
            if ( PHP_VERSION_ID < 80000 )
                $loader = libxml_disable_entity_loader( true );
            $errors = libxml_use_internal_errors( true );
            $return = $this->__parse_xml_body( $response_body );
            libxml_use_internal_errors( $errors );
            if ( PHP_VERSION_ID < 80000 && isset( $loader ) )
                libxml_disable_entity_loader( $loader );
            return $return;
        }//601
        protected function _parse_xml( $response_body ){
            return $this->__parse_xml( $response_body );
        }
        private function __parse_xml_body( $response_body ){
            if ( ! function_exists( 'simplexml_import_dom' ) || ! class_exists( 'DOMDocument', false ) )
                return false;
            $dom     = new \DOMDocument;
            $success = $dom->loadXML( $response_body );
            if ( ! $success ) return false;
            if ( isset( $dom->doctype ) ) return false;
            foreach ( $dom->childNodes as $child ) {
                if ( XML_DOCUMENT_TYPE_NODE === $child->nodeType )
                    return false;
            }
            $xml = simplexml_import_dom( $dom );
            if ( ! $xml ) return false;
            $return = new \stdClass;
            foreach ( $xml as $key => $value )
                $return->$key = (string) $value;
            return $return;
        }//635
        public function data2html( $data, $url ){
            if ( ! is_object( $data ) || empty( $data->type ) ) return false;
            $return = false;
            switch ( $data->type ) {
                case 'photo':
                    if ( empty( $data->url ) || empty( $data->width ) || empty( $data->height ) )
                        break;
                    if ( ! is_string( $data->url ) || ! is_numeric( $data->width ) || ! is_numeric( $data->height ) )
                        break;
                    $title  = ! empty( $data->title ) && is_string( $data->title ) ? $data->title : '';
                    $return = "<a href='{$this->_esc_url( $url )}'>";
                    $return .= "<img src='{$this->_esc_url( $data->url )}' alt='{$this->_esc_attr( $title )}' width='{$this->_esc_attr( $data->width )}' height='{$this->_esc_attr( $data->height )}'/>";
                    $return .= "</a>";
                    break;
                case 'video':
                case 'rich':
                    if ( ! empty( $data->html ) && is_string( $data->html ) )
                        $return = $data->html;
                    break;
                case 'link':
                    if ( ! empty( $data->title ) && is_string( $data->title ) )
                        $return = "<a href='{$this->_esc_url( $url )}'>{$this->_esc_html( $data->title )}</a>";
                    break;
               default:
                    $return = false;
            }
            return $this->_apply_filters( 'obj_embed_data_parse', $return, $data, $url );
        }
        public function strip_newlines( $html ){ //not used , $data, $url
            if ( false === strpos( $html, "\n" ) ) return $html;
            $count     = 1;
            $found     = [];
            $token     = '__PRE__';
            $search    = array( "\t", "\n", "\r", ' ' );
            $replace   = array( '__TAB__', '__NL__', '__CR__', '__SPACE__' );
            $tokenized = str_replace( $search, $replace, $html );
            preg_match_all( '#(<pre[^>]*>./s+?</pre>)#i', $tokenized, $matches, PREG_SET_ORDER );
            foreach ( $matches as $i => $match ) {
                $tag_html  = str_replace( $replace, $search, $match[0] );
                $tag_token = $token . $i;
                $found[ $tag_token ] = $tag_html;
                $html                = str_replace( $tag_html, $tag_token, $html, $count );
            }
            $replaced = str_replace( $replace, $search, $html );
            $stripped = str_replace( array( "\r\n", "\n" ), '', $replaced );
            $pre      = array_values( $found );
            $tokens   = array_keys( $found );
            return str_replace( $tokens, $pre, $stripped );
        }//740
    }
}else die;