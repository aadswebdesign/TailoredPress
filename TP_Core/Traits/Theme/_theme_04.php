<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-3-2022
 * Time: 17:40
 */
namespace TP_Core\Traits\Theme;
use TP_Core\Traits\Constructs\_construct_theme;

if(ABSPATH){
    trait _theme_04 {
        use _construct_theme;
        /**
         * @description Retrieves header image for custom header.
         * @return bool
         */
        protected function _get_header_image():bool{
            $url = $this->_get_theme_mod( 'header_image', $this->_get_theme_support( 'custom-header', 'default-image' ) );
            if ( 'remove-header' === $url ) return false;
            if ( $this->_is_random_header_image() ) $url = $this->_get_random_header_image();
            return $this->_esc_url_raw( $this->_set_url_scheme( $url ) );
        }//1163
        /**
         * @description Creates image tag markup for a custom header image.
         * @param array $attr
         * @return string
         */
        protected function _get_header_image_tag( $attr = [] ):string{
            $header      = $this->_get_custom_header();
            $header->url = $this->_get_header_image();
            if ( ! $header->url ) return '';
            $width  = $this->_abs_int( $header->width );
            $height = $this->_abs_int( $header->height );
            $alt    = '';
            if ( ! empty( $header->attachment_id ) ) {
                $image_alt = $this->_get_post_meta( $header->attachment_id, '_tp_attachment_image_alt', true );
                if ( is_string( $image_alt ) ) $alt = $image_alt;
            }
            $attr = $this->_tp_parse_args($attr,['src' => $header->url,'width' => $width,'height' => $height,'alt' => $alt,]);
            if ( empty( $attr['srcset'] ) && ! empty( $header->attachment_id ) ) {
                $image_meta = $this->_get_post_meta( $header->attachment_id, '_tp_attachment_metadata', true );
                $size_array = array( $width, $height );
                if ( is_array( $image_meta ) ) {
                    $srcset = $this->_tp_calculate_image_srcset( $size_array, $header->url, $image_meta, $header->attachment_id );
                    if ( ! empty( $attr['sizes'])) $sizes = $attr['sizes'];
                    else  $sizes = $this->_tp_calculate_image_sizes( $size_array, $header->url, $image_meta, $header->attachment_id );
                    if ( $srcset && $sizes ) {
                        $attr['srcset'] = $srcset;
                        $attr['sizes']  = $sizes;
                    }
                }
            }
            $attr = $this->_apply_filters( 'get_header_image_tag_attributes', $attr, $header );
            $attr = array_map( 'esc_attr', $attr );
            $html = '<img';
            foreach ( $attr as $name => $value ) $html .=" {$name} = '{$value}'";
            $html .= ' />';
            return $this->_apply_filters( 'get_header_image_tag', $html, $header, $attr );
        }//1186
        /**
         * @description Displays the image markup for a custom header image.
         * @param array $attr
         */
        public function the_header_image_tag( $attr = [] ):void{
            echo $this->_get_header_image_tag( $attr );
        }//1276
        /**
         * @description Gets random header image data from registered images in theme.
         * @return null|object|\stdClass
         */
        protected function _get_random_header_data(){
            static $_tp_random_header = null;
            if ( empty( $_tp_random_header ) ) {
                $header_image_mod = $this->_get_theme_mod( 'header_image', '' );
                $headers = [];
                if ( 'random-uploaded-image' === $header_image_mod )
                    $headers = $this->_get_uploaded_header_images();
                elseif ( ! empty( $this->__tp_default_headers ) ) {
                    if ( 'random-default-image' === $header_image_mod )$headers = $this->__tp_default_headers;
                    else if ( $this->_current_theme_supports( 'custom-header', 'random-default' ) )
                        $headers = $this->__tp_default_headers;
                }
                if ( empty( $headers ) ) return new \stdClass;
                $_tp_random_header = (object) $headers[ array_rand( $headers ) ];
                $_tp_random_header->url = sprintf(
                    $_tp_random_header->url,
                    $this->_get_template_directory_uri(),
                    $this->_get_stylesheet_directory_uri()
                );
                $_tp_random_header->thumbnail_url = sprintf(
                    $_tp_random_header->thumbnail_url,
                    $this->_get_template_directory_uri(),
                    $this->_get_stylesheet_directory_uri()
                );
            }
            return $_tp_random_header;
        }//1291
        /**
         * @description Gets random header image URL from registered images in theme.
         * @return string
         */
        protected function _get_random_header_image():string{
            $random_image = $this->_get_random_header_data();
            if ($random_image === null && empty( $random_image->url ) )return '';
            return $random_image->url;
        }//1340
        /**
         * @description Checks if random header image is in use.
         * @param string $type
         * @return bool
         */
        protected function _is_random_header_image( $type = 'any' ):bool{
            $header_image_mod = $this->_get_theme_mod( 'header_image', $this->_get_theme_support( 'custom-header', 'default-image' ) );
            if ( 'any' === $type ) {
                if ( 'random-default-image' === $header_image_mod
                    || 'random-uploaded-image' === $header_image_mod
                    || ( '' !== $this->_get_random_header_image() && empty( $header_image_mod ) )
                )  return true;
            } else if ( "random-$type-image" === $header_image_mod ) return true;
            elseif ( 'default' === $type && empty( $header_image_mod ) && '' !== $this->_get_random_header_image() )
                return true;
            return false;
        }//1363
        /**
         * @description Displays header image URL.
         */
        public function header_image():void{
            $image = $this->_get_header_image();
            if ( $image ) echo $this->_esc_url( $image );
        }//1389
        /**
         * @todo Caching. and more
         * @description Gets the header images uploaded for the current theme.
         * @return array
         */
        protected function _get_uploaded_header_images():array{
            $header_images = [];
            $headers = $this->_get_posts(
                ['post_type' => 'attachment','meta_key' => '_tp_attachment_is_custom_header',
                    'meta_value' => $this->_get_option( 'stylesheet' ),'orderby' => 'none','no_paging' => true,]
            );
            if ( empty( $headers ) ) return [];
            foreach ( (array) $headers as $header ) {
                $url          = $this->_esc_url_raw( $this->_tp_get_attachment_url( $header->ID ) );
                $header_data  = $this->_tp_get_attachment_metadata( $header->ID );
                $header_index = $header->ID;
                $header_images[ $header_index ]                  = array();
                $header_images[ $header_index ]['attachment_id'] = $header->ID;
                $header_images[ $header_index ]['url']           = $url;
                $header_images[ $header_index ]['thumbnail_url'] = $url;
                $header_images[ $header_index ]['alt_text']      = $this->_get_post_meta( $header->ID, '_tp_attachment_image_alt', true );
                if ( isset( $header_data['attachment_parent'] ) )
                    $header_images[ $header_index ]['attachment_parent'] = $header_data['attachment_parent'];
                else $header_images[ $header_index ]['attachment_parent'] = '';
                if ( isset( $header_data['width'] ) )
                    $header_images[ $header_index ]['width'] = $header_data['width'];
                if ( isset( $header_data['height'] ) )
                    $header_images[ $header_index ]['height'] = $header_data['height'];
            }
            return $header_images;
        }//1404
        /**
         * @description Gets the header image data.
         * @return object
         */
        protected function _get_custom_header(){
            if ( $this->_is_random_header_image() ) $data = $this->_get_random_header_data();
            else{
                $data = $this->_get_theme_mod( 'header_image_data' );
                if ( ! $data && $this->_current_theme_supports( 'custom-header', 'default-image' )){
                    $directory_args        = [$this->_get_template_directory_uri(), $this->_get_stylesheet_directory_uri()];
                    $data                  = [];
                    $data['url']           = vsprintf( $this->_get_theme_support( 'custom-header', 'default-image' ), $directory_args );
                    $data['thumbnail_url'] = $data['url'];
                    if ( ! empty( $this->__tp_default_headers ) ) {
                        foreach ( (array) $this->__tp_default_headers as $default_header ) {
                            $url = vsprintf( $default_header['url'], $directory_args );
                            if ( $data['url'] === $url ) {
                                $data                  = $default_header;
                                $data['url']           = $url;
                                $data['thumbnail_url'] = vsprintf( $data['thumbnail_url'], $directory_args );
                                break;
                            }
                        }
                    }
                }
            }
            $default = [
                'url'           => '',
                'thumbnail_url' => '',
                'width'         => $this->_get_theme_support( 'custom-header', 'width' ),
                'height'        => $this->_get_theme_support( 'custom-header', 'height' ),
                'video'         => $this->_get_theme_support( 'custom-header', 'video' ),
            ];
            return (object) $this->_tp_parse_args( $data, $default );
        }//1459
        /**
         * @description Registers a selection of default headers to be displayed by the custom header admin UI.
         * @param $headers
         */
        protected function _register_default_headers( $headers ):void{
            $this->tp_default_headers = array_merge( (array) $this->tp_default_headers, (array) $headers );
        }//1505
    }
}else die;