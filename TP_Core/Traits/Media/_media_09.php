<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-3-2022
 * Time: 04:04
 */
namespace TP_Core\Traits\Media;
if(ABSPATH){
    trait _media_09 {
        /**
         * @description Extracts meta information about a WebP file: width, height, and type.
         * @param $filename
         * @return array
         */
        protected function _tp_get_webp_info( $filename ):array{
            $width  = false;
            $height = false;
            $type   = false;
            if ( 'image/webp' !== $this->_tp_get_image_mime( $filename ) )
                return compact( 'width', 'height', 'type' );
            try {
                $handle = fopen( $filename, 'rb' );
                if ( $handle ) {
                    $magic = fread( $handle, 40 );
                    fclose( $handle );
                    if ( strlen( $magic ) < 40 ) return compact( 'width', 'height', 'type' );
                    switch ( substr( $magic, 12, 4 ) ) {
                        // Lossy WebP.
                        case 'VP8 ':
                            $parts  = unpack( 'v2', substr( $magic, 26, 4 ) );
                            $width  = ( $parts[1] & 0x3FFF );
                            $height = ( $parts[2] & 0x3FFF );
                            $type   = 'lossy';
                            break;
                        // Lossless WebP.
                        case 'VP8L':
                            $parts  = unpack( 'C4', substr( $magic, 21, 4 ) );
                            $width  = ( $parts[1] | ( ( $parts[2] & 0x3F ) << 8 ) ) + 1;
                            $height = ( ( ( $parts[2] & 0xC0 ) >> 6 ) | ( $parts[3] << 2 ) | ( ( $parts[4] & 0x03 ) << 10 ) ) + 1;
                            $type   = 'lossless';
                            break;
                        // Animated/alpha WebP.
                        case 'VP8X':
                            // Pad 24-bit int.
                            $width = unpack( 'V', substr( $magic, 24, 3 ) . "\x00" );
                            $width = ( $width[1] & 0xFFFFFF ) + 1;
                            // Pad 24-bit int.
                            $height = unpack( 'V', substr( $magic, 27, 3 ) . "\x00" );
                            $height = ( $height[1] & 0xFFFFFF ) + 1;
                            $type   = 'animated-alpha';
                            break;
                    }
                }
            } catch ( \Exception $e ) {}
            return compact( 'width', 'height', 'type' );
        }//5232
        /**
         * @description Gets the default value to use for a `loading` attribute on an element.
         * @param $context
         * @return bool|string
         */
        protected function _tp_get_loading_attr_default( $context ){
            if ( 'the_content' !== $context && 'the_post_thumbnail' !== $context )
                return 'lazy';
            if ( $this->_is_admin() || ! $this->_in_the_loop() || ! $this->_is_main_query() )
                return 'lazy';
            $content_media_count = $this->_tp_increase_content_media_count();
            if ( $content_media_count <= $this->_tp_omit_loading_attr_threshold() )
                return false;
            return 'lazy';
        }//5308
        /**
         * @description Gets the threshold for how many of the first content media elements to not lazy-load.
         * @param bool $force
         * @return mixed
         */
        protected function _tp_omit_loading_attr_threshold( $force = false ){
            static $omit_threshold;
            if ( ! isset( $omit_threshold ) || $force )
                $omit_threshold = $this->_apply_filters( 'tp_omit_loading_attr_threshold', 1 );
            return $omit_threshold;
        }//5343
        /**
         * @description Increases an internal content media count variable.
         * @param int $amount
         * @return int
         */
        protected function _tp_increase_content_media_count( $amount = 1 ):int{
            static $content_media_count = 0;
            $content_media_count += $amount;
            return $content_media_count;
        }//5373
        protected function _img_hooks():void{
            $this->_add_shortcode( 'tp_caption', [$this,'img_caption_shortcode']);//media 04, 177
            $this->_add_shortcode( 'caption', [$this,'img_caption_shortcode']);//media 04, 177
            $this->_add_shortcode( 'gallery', [$this,'gallery_shortcode'] );//media 04, 277
            $this->_add_shortcode( 'playlist',[$this,'tp_playlist_shortcode']);//media 05, 150
            $this->_add_shortcode( 'audio',[$this,'tp_audio_shortcode']);//media 05, 261
            $this->_add_shortcode( 'video',[$this,'tp_video_shortcode']);
            //$this->add_action('tp_assets'
        }//added
     }
}else die;