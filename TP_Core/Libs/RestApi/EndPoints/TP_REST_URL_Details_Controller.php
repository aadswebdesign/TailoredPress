<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 14:10
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Core\Traits\Templates\_general_template_02;
use TP_Core\Libs\HTTP\TP_Http;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_REST_URL_Details_Controller extends TP_REST_Controller{
        use _general_template_02;
        public function __construct() {
            $this->_namespace = 'tp-block-editor/v1';
            $this->_rest_base = 'url-details';
        }
        public function register_routes():void{
            $this->_register_rest_route(
                $this->_namespace,'/' . $this->_rest_base,
                [['methods' => TP_GET,
                    'callback' => [$this,'parse_url_details'],
                    'args' =>['url' => [
                        'required' => true,'description' => $this->__( 'The URL to process.' ),
                        'validate_callback' => 'tp_http_validate_url','sanitize_callback' => 'esc_url_raw',
                        'type' => 'string','format' => 'uri',
                    ]],
                    'permission_callback' => [$this, 'permissions_check'],
                    'schema' => [$this, 'get_public_item_schema'],
                ],]
            );
        }//35
        public function get_item_schema(){
            if ( $this->_schema )
                return $this->_add_additional_fields_schema( $this->_schema );
            $schema = ['$schema' => 'http://json-schema.org/draft-04/schema#',
                'title' => 'url-details','type' => 'object',
                'properties' => [
                    'title' => ['description' => sprintf(
                        $this->__( 'The contents of the %s element from the URL.' ),
                        '<title>'),
                        'type' => 'string','context' => ['view','edit','embed'],'readonly' => true,
                    ],
                    'icon' => ['description' => sprintf(
                        $this->__( 'The favicon image link of the %s element from the URL.' ),
                        "<link rel='icon'>"),
                        'type' => 'string','format' => 'uri','context' => ['view','edit','embed'],'readonly' => true,
                    ],
                    'description' => [
                        'description' => sprintf(
                            $this->__( 'The content of the %s element from the URL.' ),
                            "<meta name='description'>"),
                        'type' => 'string','context' => ['view','edit','embed'],'readonly' => true,
                    ],
                    'image' => ['description' => sprintf(
                        $this->__( 'The Open Graph image link of the %1$s or %2$s element from the URL.' ),
                        "<meta property='og:image'>","<meta property='og:image:url'>"),
                        'type' => 'string','format' => 'uri','context' => ['view','edit','embed'],'readonly' => true,
                    ],
                ]
            ];
            $this->_schema = $schema;
            return $this->_add_additional_fields_schema( $this->_schema );
        }//67
        public function parse_url_details( $request ){
            $url = $this->_untrailingslashit( $request['url'] );
            if ( empty( $url ) )
                return new TP_Error( 'rest_invalid_url', $this->__( 'Invalid URL' ), array( 'status' => 404 ) );
            $cache_key = $this->__build_cache_key_for_url( $url );
            $cached_response = $this->__get_cache( $cache_key );
            if ( ! empty( $cached_response ) ) $remote_url_response = $cached_response;
            else {
                $remote_url_response = $this->__get_remote_url( $url );
                if ( empty( $remote_url_response ) || $this->_init_error( $remote_url_response ))
                    return $remote_url_response;
                $this->__set_cache( $cache_key, $remote_url_response );
            }
            $html_head     = $this->__get_document_head( $remote_url_response );
            $meta_elements = $this->__get_meta_with_content_elements( $html_head );
            $data = $this->_add_additional_fields_to_object(
                ['title' => $this->__get_title( $html_head ),'icon' => $this->__get_icon( $html_head, $url ),
                    'description' => $this->__get_description( $meta_elements ),'image' => $this->__get_image( $meta_elements, $url ),],
                $request
            );
            $response = $this->_rest_ensure_response( $data );
            return $this->_apply_filters( 'rest_prepare_url_details', $response, $url, $request, $remote_url_response );
        }//134
        public function permissions_check(){
            if ( $this->_current_user_can( 'edit_posts' ) ) return true;
            foreach ( $this->_get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
                if ( $this->_current_user_can( $post_type->cap->edit_posts ) ) return true;
            }
            return new TP_Error('rest_cannot_view_url_details',
                $this->__( 'Sorry, you are not allowed to process remote urls.' ),
                ['status' => $this->_rest_authorization_required_code()]
            );
        }//197
        private function __get_remote_url( $url ){
            $modified_user_agent = 'TP-URLDetails/' . $this->_get_bloginfo( 'version' ) . ' (+' . $this->_get_bloginfo( 'url' ) . ')';
            $args = ['limit_response_size' => 150 * KB_IN_BYTES, 'user-agent' => $modified_user_agent,];
            $args = $this->_apply_filters( 'rest_url_details_http_request_args', $args, $url );
            $response = $this->_tp_safe_remote_get( $url, $args );
            if ( OK !== $this->_tp_remote_retrieve_response_code( $response ) )
                return new TP_Error('no_response',
                    $this->__( 'URL not found. Response returned a non-200 status code for this URL.' ),
                    ['status' => NOT_FOUND]);
            $remote_body = $this->_tp_remote_retrieve_body( $response );
            if ( empty( $remote_body ) )
                return new TP_Error('no_content',
                    $this->__( 'Unable to retrieve body from response at this URL.' ),
                    ['status' => NOT_FOUND]);
            return $remote_body;
        }//224
        private function __get_title( $html ){
            $pattern = '#<title[^>]*>(.*?)<\s*/\s*title>#is';
            preg_match( $pattern, $html, $match_title );
            if ( empty( $match_title[1] ) || ! is_string( $match_title[1] ) ) return '';
            $title = trim( $match_title[1] );
            return $this->__prepare_metadata_for_output( $title );
        }//286
        private function __get_icon( $html, $url ){
            $pattern = '#<link\s[^>]*rel=(?:[\"\']??)\s*(?:icon|shortcut icon|icon shortcut)\s*(?:[\"\']??)[^>]*\/?>#isU';
            preg_match( $pattern, $html, $element );
            if ( empty( $element[0] ) || ! is_string( $element[0] ) ) return '';
            $element = trim( $element[0] );
            $pattern = '#href=([\"\']??)([^\" >]*?)\\1[^>]*#isU';
            preg_match( $pattern, $element, $icon );
            if ( empty( $icon[2] ) || ! is_string( $icon[2] ) ) return '';
            $icon = trim( $icon[2] );
            $parsed_icon = parse_url( $icon );
            if ( isset( $parsed_icon['scheme'] ) && 'data' === $parsed_icon['scheme'] ) return $icon;
            if ( ! is_string( $url ) || '' === $url ) return $icon;
            $parsed_url = parse_url( $url );
            if ( isset( $parsed_url['scheme'],$parsed_url['host'] ) ) {
                $root_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/';
                $icon     = TP_Http::make_absolute_url( $icon, $root_url );
            }
            return $icon;
        }//308
        private function __get_description( $meta_elements ){
            if ( empty( $meta_elements[0] ) ) return '';
            $description = $this->__get_metadata_from_meta_element(
                $meta_elements,'name','(?:description|og:description)');
            if ( '' === $description ) return '';
            return $this->__prepare_metadata_for_output( $description );
        }//358
        private function __get_image( $meta_elements, $url ){
            $image = $this->__get_metadata_from_meta_element(
                $meta_elements,'property','(?:og:image|og:image:url)');
            if ( '' === $image ) return '';
            $parsed_url = parse_url( $url );
            if ( isset( $parsed_url['scheme'], $parsed_url['host'] ) ) {
                $root_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/';
                $image    = TP_Http::make_absolute_url( $image, $root_url );
            }
            return $image;
        }//395
        private function __prepare_metadata_for_output( $metadata ){
            $metadata = html_entity_decode( $metadata, ENT_QUOTES, $this->_get_bloginfo( 'charset' ) );
            $metadata = $this->_tp_strip_all_tags( $metadata );
            return $metadata;
        }//427
        private function __build_cache_key_for_url( $url ): string{
            return 'g_url_details_response_' . md5( $url );
        }//444
        private function __get_cache( $key ) {
            return $this->_get_site_transient( $key );
        }//453
        private function __set_cache( $key, $data = '' ){
            $ttl = HOUR_IN_SECONDS;
            $cache_expiration = $this->_apply_filters( 'rest_url_details_cache_expiration', $ttl );
            return $this->_set_site_transient( $key, $data, $cache_expiration );
        }//466
        private function __get_document_head( $html ){
            $head_html = $html;
            $head_start = strpos( $html, '<head' );
            if ( false === $head_start ) return $html;
            $head_end = strpos( $head_html, '</head>' );
            if ( false === $head_end ) {
                $head_end = strpos( $head_html, '<body' );
                if ( false === $head_end ) return $html;
            }
            $head_html  = substr( $head_html, $head_start, $head_end );
            $head_html .= '</head>';
            return $head_html;
        }//492
        private function __get_meta_with_content_elements( $html ){
            $pattern = '#<meta\s' . '[^>]*' . 'content=(["\']??)(.*)\1' . '[^>]*' .'\/?>#' . 'isU';
            preg_match_all( $pattern, $html, $elements );
            return $elements;
        }//535
        private function __get_metadata_from_meta_element( $meta_elements, $attr, $attr_value ){
            if ( empty( $meta_elements[0] ) ) return '';
            $metadata = '';
            $pattern  = '#' .
                $attr . '=([\"\']??)\s*' . $attr_value . '\s*\1' . '#isU';
            foreach ( $meta_elements[0] as $index => $element ) {
                preg_match( $pattern, $element, $match );
                if ( empty( $match ) ) continue;
                if ( isset( $meta_elements[2][ $index ] ) && is_string( $meta_elements[2][ $index ] ) )
                    $metadata = trim( $meta_elements[2][ $index ] );
                break;
            }
            return $metadata;
        }//618
    }
}else die;