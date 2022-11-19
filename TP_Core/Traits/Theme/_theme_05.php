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
    trait _theme_05 {
        use _construct_theme;
        /**
         * @description Unregister default headers.
         * @param $header
         * @return bool|null
         */
        protected function _unregister_default_headers( $header ):bool{
            $return = null;
            if ( is_array( $header ) )
                array_map( 'unregister_default_headers', $header );
            elseif ( isset( $this->tp_default_headers[ $header ] ) ) {
                unset( $this->tp_default_headers[ $header ] );
                $return = true;
            }else $return = false;
            return $return;
        }//1526
        /**
         * @description Checks whether a header video is set or not.
         * @return bool
         */
        protected function _has_header_video():bool{
            return $this->_get_header_video_url();
        }//1548
        /**
         * @description Retrieves header video URL for custom header.
         * @return bool
         */
        protected function _get_header_video_url():bool{
            $id = $this->_abs_int( $this->_get_theme_mod( 'header_video' ) );
            if ( $id ) $url = $this->_tp_get_attachment_url( $id );
            else $url = $this->_get_theme_mod( 'external_header_video' );
            $url = $this->_apply_filters( '__get_header_video_url', $url );
            if ( ! $id && ! $url )return false;
            return $this->_esc_url_raw( $this->_set_url_scheme( $url ) );
        }//1561
        /**
         * @description Displays header video URL.
         */
        public function the_header_video_url():void{
            $video = $this->_get_header_video_url();
            if ( $video ) echo $this->_esc_url( $video );
        }//1592
        /**
         * @description Retrieves header video settings.
         * @return mixed
         */
        protected function _get_header_video_settings(){
            $header     = $this->_get_custom_header();
            $video_url  = $this->_get_header_video_url();
            $video_type = $this->_tp_check_file_type( $video_url, $this->_tp_get_mime_types() );
            $settings = [
                'mimeType'  => '',
                'posterUrl' => $this->_get_header_image(),
                'videoUrl'  => $video_url,
                'width'     => $this->_abs_int( $header->width ),
                'height'    => $this->_abs_int( $header->height ),
                'minWidth'  => 900,
                'minHeight' => 500,
                'l10n'      => [
                    'pause' => $this->__( 'Pause' ),'play' => $this->__( 'Play' ),
                    'pauseSpeak' => $this->__( 'Video is paused.' ),'playSpeak' => $this->__( 'Video is playing.' ),
                ],
            ];
            if ( preg_match( '#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#', $video_url))
                $settings['mimeType'] = 'video/x-youtube';
            elseif ( ! empty( $video_type['type'])) $settings['mimeType'] = $video_type['type'];
            return $this->_apply_filters( '__header_video_settings', $settings );
        }//1607
        /**
         * @description Checks whether a custom header is set or not.
         * @return bool
         */
        protected function _has_custom_header():bool{
            if ( $this->_has_header_image() || ( $this->_has_header_video() && $this->_is_header_video_active() ) )
                return true;
            return false;
        }//1651
        /**
         * @description Checks whether the custom header video is eligible to show on the current page.
         * @return bool
         */
        protected function _is_header_video_active():bool{
            if ( ! $this->_get_theme_support( 'custom-header', 'video' )) return false;
            $video_active_cb = $this->_get_theme_support( 'custom-header', 'video-active-callback' );
            if ( empty( $video_active_cb ) || ! is_callable( $video_active_cb ) ) $show_video = true;
            else $show_video = $video_active_cb();
            return $this->_apply_filters( '__is_header_video_active', $show_video );
        }//1666
        /**
         * @description Retrieves the markup for a custom header.
         * @return string
         */
        protected function _get_custom_header_markup():string{
            if ( ! $this->_has_custom_header() && ! $this->_is_customize_preview() )return '';
            $custom_header_start = "<div id='tp_custom_header' class='tp-custom-header'>";
            return sprintf( $custom_header_start. '%s</div>', $this->_get_header_image_tag());
        }//1700
        /**
         * @description Prints the markup for a custom header.
         */
        public function the_custom_header_markup():void{
            $custom_header = $this->_get_custom_header_markup();
            if ( empty( $custom_header ) )return;
            echo $custom_header;
            if ( $this->_is_header_video_active() && ( $this->_has_header_video() || $this->_is_customize_preview() ) ) {
                $this->tp_enqueue_script( 'tp-custom-header' );
                $this->tp_localize_script( 'tp-custom-header', '_tpCustomHeaderSettings',[$this, '_get_header_video_settings']);
            }
        }//1718
        /**
         * @description Retrieves background image for custom background.
         * @return mixed
         */
        protected function _get_background_image(){
            return $this->_get_theme_mod( 'background_image', $this->_get_theme_support( 'custom-background', 'default-image' ) );
        }//1739
    }
}else die;