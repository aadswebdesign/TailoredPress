<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-5-2022
 * Time: 10:19
 */
namespace TP_Core\Traits\Embed;
use TP_Core\Libs\Embed\TP_ObjEmbed;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\TP_REST_Server;
if(ABSPATH){
    trait _embed_03{
        /**
         * @description Ensures that the specified format is either 'json' or 'xml'.
         * @param $format
         * @return string
         */
        protected function _tp_obj_embed_ensure_format( $format ):string{
            if ( ! in_array( $format, array( 'json', 'xml' ), true ) )
                return 'json';
            return $format;
        }//738
        /**
         * @description Hooks into the REST API output to print XML instead of JSON.
         * @param $served
         * @param $result
         * @param TP_REST_Request $request
         * @param TP_REST_Server $server
         * @return bool
         */
        protected function _obj_embed_rest_pre_serve_request( $served, $result,TP_REST_Request $request, TP_REST_Server $server ):bool{
            $params = $request->get_params();
            if ( '/oembed/1.0/embed' !== $request->get_route() || 'GET' !== $request->get_method() )
                return $served;
            if ( ! isset( $params['format'] ) || 'xml' !== $params['format'] )
                return $served;
            $data = $server->response_to_data( $result, false );
            if ( ! class_exists( 'SimpleXMLElement' ) ) {
                $this->_status_header(NOT_IMPLEMENTED);
                die( $this->_get_status_header_desc(NOT_IMPLEMENTED) );
            }
            $result = $this->_obj_embed_create_xml( $data );
            if ( ! $result ) {
                $this->_status_header(NOT_IMPLEMENTED);
                return $this->_get_status_header_desc(NOT_IMPLEMENTED);
            }
            if ( ! headers_sent() )
                $server->send_header( 'Content-Type', 'text/xml; charset=' . $this->_get_option( 'blog_charset' ) );
            echo $result;
            return true;
        }//761
        /**
         * @description Creates an XML string from a given array.
         * @param $data
         * @param null $node
         * @return bool|mixed
         */
        protected function _obj_embed_create_xml( $data, $node = null ){
            if ( ! is_array( $data ) || empty( $data ) ) return false;
            if ( null === $node ) $node = new \SimpleXMLElement( '<oembed></oembed>' );
            foreach ( $data as $key => $value ) {
                if ( is_numeric( $key ) ) $key = 'oembed';
                if ( is_array( $value ) ) {
                    $item = $node->addChild( $key );
                    $this->_obj_embed_create_xml( $value, $item );
                } else $node->addChild( $key, $this->_esc_html( $value ) );
            }
            return $node->asXML();
        }//807
        /**
         * @description Filters the given Obj_Embed HTML to make sure iframes have a title attribute.
         * @param $result
         * @param $data
         * @param $url
         * @return mixed
         */
        protected function _tp_filter_obj_embed_iframe_title_attribute( $result, $data, $url ){
            if ( false === $result || ! in_array( $data->type, array( 'rich', 'video' ), true ) )
                return $result;
            $title = ! empty( $data->title ) ? $data->title : '';
            $pattern = '`<iframe([^>]*)>`i';
            if ( preg_match( $pattern, $result, $matches ) ) {
                $attrs = $this->_tp_kses_hair( $matches[1], $this->_tp_allowed_protocols() );
                foreach ( $attrs as $attr => $item ) {
                    $lower_attr = strtolower( $attr );
                    if ( $lower_attr === $attr ) continue;
                    if ( ! isset( $attrs[ $lower_attr ] ) ) {
                        $attrs[ $lower_attr ] = $item;
                        unset( $attrs[ $attr ] );
                    }
                }
            }
            if ( ! empty( $attrs['title']['value'] ) ) $title = $attrs['title']['value'];
            $title = $this->_apply_filters( 'oembed_iframe_title_attribute', $title, $result, $data, $url );
            if ( '' === $title )  return $result;
            if ( isset( $attrs['title'] ) ) {
                unset( $attrs['title'] );
                $attr_string = implode( ' ', $this->_tp_list_pluck( $attrs, 'whole' ) );
                $result      = str_replace( $matches[0], '<iframe ' . trim( $attr_string ) . '>', $result );
            }
            return str_ireplace( '<iframe ', sprintf( '<iframe title="%s" ', $this->_esc_attr( $title ) ), $result );
        }//842
        /**
         * @description Filters the given Obj_Embed HTML.
         * @param $result
         * @param $data
         * @param $url
         * @return bool|mixed|string
         */
        protected function _tp_filter_obj_embed_result( $result, $data, $url ){
            if ( false === $result || ! in_array( $data->type, array( 'rich', 'video' ), true ) )
                return $result;
            $_oembed = $this->_tp_obj_embed_get_object();
            $tp_oembed = null;
            if($_oembed instanceof TP_ObjEmbed){
                $tp_oembed = $_oembed;
            }
            if ( false !== $tp_oembed->get_provider( $url, array( 'discover' => false ) ) ) return $result;
            $allowed_html = ['a' => ['href' => true,],'blockquote' => [],
                'iframe' => ['src' => true,'width' => true,'height' => true,'frameborder' => true,
                'marginwidth' => true,'marginheight' => true,'scrolling' => true,'title' => true,],];
            $html = $this->_tp_kses( $result, $allowed_html );
            preg_match( '|(<blockquote>.*?</blockquote>)?.*(<iframe.*?></iframe>)|ms', $html, $content );
            if ( empty( $content[2] ) ) return false;
            $html = $content[1] . $content[2];
            preg_match( '/ src=([\'"])(.*?)\1/', $html, $results );
            if ( ! empty( $results ) ) {
                $secret = $this->_tp_generate_password( 10, false );
                $url = $this->_esc_url( "{$results[2]}#?secret=$secret" );
                $q   = $results[1];
                $html = str_replace(array($results[0], '<blockquote'), array(" src='$q{$url}$q' data-secret='$q{$secret}$q'", "<blockquote data-secret='$secret'"), $html);
            }
            $allowed_html['blockquote']['data-secret'] = true;
            $allowed_html['iframe']['data-secret'] = true;
            $html = $this->_tp_kses( $html, $allowed_html );
            if ( ! empty( $content[1] ) ) {
                $html = str_replace(array('<iframe', '<blockquote'), array('<iframe style="position: absolute; clip: rect(1px, 1px, 1px, 1px);"', '<blockquote class="tp-embedded-content"'), $html);
            }
            $html = str_ireplace( '<iframe', '<iframe class="tp-embedded-content" sandbox="allow-scripts" security="restricted"', $html );
            return $html;
        }//909
        /**
         * @description Filters the string in the 'more' link displayed after a trimmed excerpt.
         * @param $more_string
         * @return string
         */
        protected function _tp_embed_excerpt_more( $more_string ):string{
            if ( !  $this->_is_embed() ) return $more_string;
            $percent = ['one' =>'%1$s','two' =>'%2$s'];
            $link = sprintf(
                "<a href='{$percent['one']}' class='tp-embed-more' target='_top'>{$percent['two']}</a>",
                $this->_esc_url(  $this->_get_permalink() ),/* translators: %s: Post title. */
                sprintf(  $this->__( 'Continue reading %s' ), "<span class='screen-reader-text'>{$this->_get_the_title()}</span>" )
            );
            return ' &hellip; ' . $link;
        }//986
        /**
         * @description Displays the post excerpt for the embed template.
         */
        public function the_excerpt_embed():void{
            $output = $this->_get_the_excerpt();
            echo $this->_apply_filters( 'the_excerpt_embed', $output );
        }//1007
        /**
         * @param $content
         * @return mixed
         */
        protected function _tp_embed_excerpt_attachment( $content ){
            if ( $this->_is_attachment() ) return $this->_prepend_attachment( '' );
            return $content;
        }//1030
        /**
         * @description Prints the CSS in the embed iframe header.
         * @return string
         */
        protected function _print_get_embed_styles():string{
            $suffix    = TP_SCRIPT_DEBUG ? '' : '.min';
            $style = "<style>{file_get_contents( TP_LIBS_ASSETS \"/css/tp_embed_template_$suffix.css\" )}</style>";
            return $style;
        }//1064
        public function print_embed_styles():void{
            echo $this->_print_get_embed_styles();
        }
    }
}else die;