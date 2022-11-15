<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-3-2022
 * Time: 04:04
 */
namespace TP_Core\Traits\Media;
use TP_Core\Traits\Inits\_init_images;
if(ABSPATH){
    trait _media_05 {
        use _init_images;
        /**
         * @note for ie left out, that is past :)
         * @description Outputs and enqueue default scripts and styles for play-lists.
         */
        public function tp_playlist_scripts():void{
            $this->tp_enqueue_style( 'tp-media_element' );
            $this->tp_enqueue_script( 'tp-playlist' );
            $this->_add_action( 'tp_footer', [$this,'tp_underscore_playlist_templates'], 0 );
            $this->_add_action( 'admin_footer', [$this,'tp_underscore_playlist_templates'], 0 );
        }//2578
        /**
         * @description Builds the Playlist shortcode output.
         * @param $attr
         * @return string
         */
        protected function _get_playlist_shortcode( $attr ):string{
            $post = $this->_get_post();
            static $instance = 0;
            $instance++;
            $output = $this->_apply_filters( 'post_playlist', '', $attr, $instance );
            if ( ! empty( $output ) ) return $output;
            $atts = $this->_shortcode_atts(
                ['type' => 'audio','order' => 'ASC','orderby' => 'menu_order ID',
                    'id' => $post ? $post->ID : 0,'include' => '','exclude' => '','style' => 'light',
                    'tracklist' => true,'tracknumbers' => true,'images' => true,'artists' => true,],
                $attr,
                'playlist'
            );
            $id = (int) $atts['id'];
            if ( 'audio' !== $atts['type'] ) $atts['type'] = 'video';
            $args = ['post_status' => 'inherit','post_type' => 'attachment','post_mime_type' => $atts['type'],'order' => $atts['order'],'orderby' => $atts['orderby'],];
            if ( ! empty( $atts['include'] ) ) {
                $args['include'] = $atts['include'];
                $_attachments    = $this->_get_posts( $args );
                $attachments = [];
                foreach ( $_attachments as $key => $val ) $attachments[ $val->ID ] = $_attachments[ $key ];
            } elseif ( ! empty( $atts['exclude'] ) ) {
                $args['post_parent'] = $id;
                $args['exclude']     = $atts['exclude'];
                $attachments         = $this->_get_children( $args );
            } else {
                $args['post_parent'] = $id;
                $attachments         = $this->_get_children( $args );
            }
            if ( empty( $attachments ) ) return '';
            if ( $this->_is_feed() ) {
                $output = "\n";
                foreach ( $attachments as $att_id => $attachment )
                    $output .= $this->_tp_get_attachment_link( $att_id ) . "\n";
                return $output;
            }
            $outer = 22; // Default padding and border of wrapper.
            $default_width  = 640;
            $default_height = 360;
            $theme_width  = empty( $this->content_width ) ? $default_width : ( $this->content_width - $outer );
            $theme_height = empty( $this->content_width ) ? $default_height : round( ( $default_height * $theme_width ) / $default_width );
            $data = [
                'type'=> $atts['type'],
                // Don't pass strings to JSON, will be truthy in JS.
                'tracklist' => $this->_tp_validate_boolean( $atts['tracklist'] ),
                'tracknumbers' => $this->_tp_validate_boolean( $atts['tracknumbers'] ),
                'images' => $this->_tp_validate_boolean( $atts['images'] ),
                'artists' =>$this->_tp_validate_boolean( $atts['artists'] ),
            ];
            $tracks = [];
            foreach ( $attachments as $attachment ) {
                $url   = $this->_tp_get_attachment_url( $attachment->ID );
                $ftype = $this->_tp_check_file_type( $url, $this->_tp_get_mime_types() );
                $track = ['src' => $url,'type' => $ftype['type'],'title' => $attachment->post_title,
                    'caption' => $attachment->post_excerpt,'description' => $attachment->post_content,
                ];
                $track['meta'] = [];
                $meta          = $this->_tp_get_attachment_metadata( $attachment->ID );
                if ( ! empty( $meta ) ) {
                    foreach ( $this->_tp_get_attachment_id_3_keys( $attachment ) as $key => $label ) {
                        if ( ! empty( $meta[ $key ] ) ) $track['meta'][ $key ] = $meta[ $key ];
                    }
                    if ( 'video' === $atts['type'] ) {
                        if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
                            $width        = $meta['width'];
                            $height       = $meta['height'];
                            $theme_height = round( ( $height * $theme_width ) / $width );
                        } else {
                            $width  = $default_width;
                            $height = $default_height;
                        }
                        $track['dimensions'] = ['original' => compact( 'width', 'height' ),
                            'resized'  => ['width'  => $theme_width,'height' => $theme_height,],];
                    }
                }
                if ( $atts['images'] ) {
                    $thumb_id = $this->_get_post_thumbnail_id( $attachment->ID );
                    if ( ! empty( $thumb_id ) ) {
                        @list( $src, $width, $height ) = $this->_tp_get_attachment_image_src( $thumb_id, 'full' );
                        $track['image']               = compact( 'src', 'width', 'height' );
                        @list( $src, $width, $height ) = $this->_tp_get_attachment_image_src( $thumb_id, 'thumbnail' );
                        $track['thumb']               = compact( 'src', 'width', 'height' );
                    } else {
                        $src            = $this->_tp_mime_type_icon( $attachment->ID );
                        $width          = 48;
                        $height         = 64;
                        $track['image'] = compact( 'src', 'width', 'height' );
                        $track['thumb'] = compact( 'src', 'width', 'height' );
                    }
                }
                $tracks[] = $track;
            }//foreach
            $data['tracks'] = $tracks;
            $safe_type  = $this->_esc_attr( $atts['type'] );
            $safe_style = $this->_esc_attr( $atts['style'] );
            ob_start();
            if ( 1 === $instance )
                $this->_do_action( 'tp_playlist_scripts', $atts['type'], $atts['style'] );
            $html = "<div class='tp-playlist container tp-{$safe_type}-playlist tp-playlist-{$safe_style}'>";
            if ( 'audio' === $atts['type'] ){
                $html .= "<div class='tp-playlist current item'></div>";
            }
            $html .= "<$safe_type controls='controls' preload='none' width='{(int) $theme_width}'";
            if ( 'video' === $safe_type )$html .= " height='{(int) $theme_height}'";
            $html .= "></$safe_type>";
            $html .= "<div class='tp-playlist next'></div>";
            $html .= "<div class='tp-playlist prev'></div>";
            $html .= "<noscript>";
            $html .= "<ol>";
            ob_start();
            foreach ( $attachments as $att_id => $attachment )
                printf( '<li>%s</li>', $this->_tp_get_attachment_link( $att_id ) );
            $html .= ob_get_clean();
            $html .= "</ol>";
            $html .= "</noscript>";
            $html .= "<script type='application/json' class='tp-playlist script'>{$this->_tp_json_encode( $data )}</script>";
            $html .= "</div>";
            return $html;
        }//2624
        public function tp_playlist_shortcode( $attr ):string{
            return $this->_get_playlist_shortcode( $attr );
        }
        /**
         * @description Provides a No-JS Flash fallback as a last resort for audio / video.
         * @param $url
         * @return mixed
         */
        protected function _tp_media_element_fallback( $url ){
            return $this->_apply_filters( 'tp_media_element_fallback', sprintf( "<a href='" . '%1$s' ."'>" . '%1$s' ."</a>", $this->_esc_url( $url ) ), $url );
        }//2852
        /**
         * @description Returns a filtered list of supported audio formats.
         * @return mixed
         */
        protected function _tp_get_audio_extensions(){
            return $this->_apply_filters( 'tp_audio_extensions', array( 'mp3', 'ogg', 'flac', 'm4a', 'wav' ) );
        }//2871
        /**
         * @description Returns useful keys to use to lookup data from an attachment's stored metadata.
         * @param $attachment
         * @param string $context
         * @return mixed
         */
        protected function _tp_get_attachment_id_3_keys( $attachment, $context = 'display' ){
            $fields = array(
                'artist' => $this->__( 'Artist' ),
                'album'  => $this->__( 'Album' ),
            );
            if ( 'display' === $context ) {
                $fields['genre']            = $this->__( 'Genre' );
                $fields['year']             = $this->__( 'Year' );
                $fields['length_formatted'] = $this->_x( 'Length', 'video or audio' );
            } elseif ( 'js' === $context ) {
                $fields['bitrate']      = $this->__( 'Bitrate' );
                $fields['bitrate_mode'] = $this->__( 'Bitrate Mode' );
            }
            return $this->_apply_filters( 'tp_get_attachment_id3_keys', $fields, $attachment, $context );
        }//2892
        /**
         * @description Builds the Audio shortcode output.
         * @param $attr
         * @param string $content
         * @return mixed
         */
        protected function _get_audio_shortcode( $attr, $content = '' ){
            $post_id = $this->_get_post() ? $this->_get_the_ID() : 0;
            static $instance = 0;
            $instance++;
            $override = $this->_apply_filters( 'tp_audio_shortcode_override', '', $attr, $content, $instance );
            if ( '' !== $override ) return $override;
            $audio = null;
            $default_types = $this->_tp_get_audio_extensions();
            $defaults_atts = ['src' => '','loop' => '','autoplay' => '','preload' => 'none',
                'class' => 'tp-audio-shortcode','style' => 'width: 100%;',];
            foreach ( $default_types as $type ) $defaults_atts[ $type ] = '';
            $atts = $this->_shortcode_atts( $defaults_atts, $attr, 'audio' );
            $primary = false;
            if ( ! empty( $atts['src'] ) ) {
                $type = $this->_tp_check_file_type( $atts['src'], $this->_tp_get_mime_types() );
                if ( ! in_array( strtolower( $type['ext'] ), $default_types, true ) )
                    return sprintf( "<a class='tp-embedded-audio' href='%s'>%s</a>", $this->_esc_url( $atts['src'] ), $this->_esc_html( $atts['src'] ) );
                $primary = true;
                array_unshift( $default_types, 'src' );
            } else {
                foreach ( $default_types as $ext ) {
                    if ( ! empty( $atts[ $ext ] ) ) {
                        $type = $this->_tp_check_file_type( $atts[ $ext ], $this->_tp_get_mime_types() );
                        if ( strtolower( $type['ext'] ) === $ext ) $primary = true;
                    }
                }
            }
            if ( ! $primary ) {
                $audios = $this->_get_attached_media( 'audio',(object) $post_id );
                if ( empty( $audios ) ) return false;
                $audio       = reset( $audios );
                $atts['src'] =  $this->_tp_get_attachment_url( $audio->ID );
                if ( empty( $atts['src'] ) ) return false;
                array_unshift( $default_types, 'src' );
            }
            $library = $this->_apply_filters( 'tp_audio_shortcode_library', 'media_element' );
            if ( 'media_element' === $library && $this->_did_action( 'init' ) ) {
                $this->tp_enqueue_style( 'tp-media_element' );
                $this->tp_enqueue_script( 'tp-media_element' );
            }
            $atts['class'] = $this->_apply_filters( 'tp_audio_shortcode_class', $atts['class'], $atts );

            $html_atts = ['class' => $atts['class'],'id' => sprintf( 'audio-%d-%d', $post_id, $instance ),
                'loop' => $this->_tp_validate_boolean( $atts['loop'] ),'autoplay' => $this->_tp_validate_boolean( $atts['autoplay'] ),
                'preload' => $atts['preload'],'style' => $atts['style'],];
            foreach ( array( 'loop', 'autoplay', 'preload' ) as $a ) {
                if ( empty( $html_atts[ $a ] ) ) unset( $html_atts[ $a ] );
            }
            $attr_strings = array();
            foreach ( $html_atts as $k => $v )
                $attr_strings[] = "$k='{$this->_esc_attr( $v )}'";
            ob_start();
            sprintf( '', implode( "<audio %s controls='controls'>", $attr_strings ) );
            $html = ob_get_clean();
            $fileurl = '';
            $source ="<source type='%s' src='%s'/>";
            foreach ( $default_types as $fallback ) {
                if ( ! empty( $atts[ $fallback ] ) ) {
                    if ( empty( $fileurl ) ) $fileurl = $atts[ $fallback ];
                    $type  =  $this->_tp_check_file_type( $atts[ $fallback ],  $this->_tp_get_mime_types() );
                    $url   = $this->_add_query_arg( '_', $instance, $atts[ $fallback ] );
                    $html .= sprintf( $source, $type['type'], $this->_esc_url( $url ) );
                }
            }
            if ( 'media_element' === $library ) $html .= $this->_tp_media_element_fallback( $fileurl );
            $html .= "</audio>";
            return $this->_apply_filters( 'tp_audio_shortcode', $html, $atts, $audio, $post_id, $library );
        }//2939
        public function tp_audio_shortcode( $attr, $content = '' ){
            return $this->_get_audio_shortcode( $attr, $content);
        }
        /**
         * @description Returns a filtered list of supported video formats.
         * @return mixed
         */
        protected function _tp_get_video_extensions(){
            return $this->_apply_filters( 'tp_video_extensions',['mp4', 'm4v', 'webm', 'ogv', 'flv']);
        }//3117
        /**
         * @description Builds the Video shortcode output.
         * @param $attr
         * @param string $content
         * @return mixed
         */
        protected function _get_video_shortcode( $attr, $content = '' ){
            $post_id = $this->_get_post() ? $this->_get_the_ID() : 0;
            static $instance = 0;
            $instance++;
            $override = $this->_apply_filters( 'tp_video_shortcode_override', '', $attr, $content, $instance );
            if ( '' !== $override ) return $override;
            $video = null;
            $default_types = $this->_tp_get_video_extensions();
            $defaults_atts = ['src' => '','poster' => '','loop' => '','autoplay' => '',
                'preload' => 'metadata','width' => 640,'height' => 360,'class' => 'tp-video-shortcode',];
            foreach ( $default_types as $type ) $defaults_atts[ $type ] = '';
            $atts = $this->_shortcode_atts( $defaults_atts, $attr, 'video' );
            if ( $this->_is_admin() ) {
                if ( $atts['width'] > $defaults_atts['width'] ) {
                    $atts['height'] = round( ( $atts['height'] * $defaults_atts['width'] ) / $atts['width'] );
                    $atts['width']  = $defaults_atts['width'];
                }
            } else if ( ! empty( $this->content_width ) && $atts['width'] > $this->content_width ) {
                $atts['height'] = round( ( $atts['height'] * $this->content_width ) / $atts['width'] );
                $atts['width']  = $this->content_width;
            }
            $is_vimeo      = false;
            $is_youtube    = false;
            $yt_pattern    = '#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#';
            $vimeo_pattern = '#^https?://(.+\.)?vimeo\.com/#';
            $primary = false;
            if ( ! empty( $atts['src'] ) ) {
                $is_vimeo   = ( preg_match( $vimeo_pattern, $atts['src'] ) );
                $is_youtube = ( preg_match( $yt_pattern, $atts['src'] ) );
                if ( ! $is_youtube && ! $is_vimeo ) {
                    $type = $this->_tp_check_file_type( $atts['src'], $this->_tp_get_mime_types() );
                    if ( ! in_array( strtolower( $type['ext'] ), $default_types, true ) )
                        return sprintf( "<a class='tp-embedded-video' href='%s'>%s</a>", $this->_esc_url( $atts['src'] ), $this->_esc_html( $atts['src'] ) );
                }
                if ( $is_vimeo ) $this->tp_enqueue_script( 'media_element-vimeo' );
                $primary = true;
                array_unshift( $default_types, 'src' );
            }else {
                foreach ( $default_types as $ext ) {
                    if ( ! empty( $atts[ $ext ] ) ) {
                        $type = $this->_tp_check_file_type( $atts[ $ext ], $this->_tp_get_mime_types() );
                        if ( strtolower( $type['ext'] ) === $ext ) $primary = true;
                    }
                }
            }
            if ( ! $primary ) {
                $videos = $this->_get_attached_media( 'video',(object) $post_id );
                if ( empty( $videos ) ) return false;
                $video       = reset( $videos );
                $atts['src'] = $this->_tp_get_attachment_url( $video->ID );
                if ( empty( $atts['src'] ) ) return false;
                array_unshift( $default_types, 'src' );
            }
            $library = $this->_apply_filters( 'tp_video_shortcode_library', 'media_element' );
            if ( 'media_element' === $library && $this->_did_action( 'init' ) ) {
                $this->tp_enqueue_style( 'tp-media_element' );
                $this->tp_enqueue_script( 'tp-media_element' );
                $this->tp_enqueue_script( 'media_element-vimeo' );
            }
            if ( 'media_element' === $library ) {
                if ( $is_youtube ) {
                    // Remove `feature` query arg and force SSL - see #40866.
                    $atts['src'] = $this->_remove_query_arg( 'feature', $atts['src'] );
                    $atts['src'] = $this->_set_url_scheme( $atts['src'], 'https' );
                } elseif ( $is_vimeo ) {
                    // Remove all query arguments and force SSL - see #40866.
                    $parsed_vimeo_url = $this->_tp_parse_url( $atts['src'] );
                    $vimeo_src        = 'https://' . $parsed_vimeo_url['host'] . $parsed_vimeo_url['path'];
                    $loop        = $atts['loop'] ? '1' : '0';
                    $atts['src'] = $this->_add_query_arg( 'loop', $loop, $vimeo_src );
                }
            }
            $atts['class'] = $this->_apply_filters( 'tp_video_shortcode_class', $atts['class'], $atts );
            $html_atts = ['class' => $atts['class'],'id' => sprintf( 'video-%d-%d', $post_id, $instance ),
                'width' => $this->_abs_int( $atts['width'] ), 'height' => $this->_abs_int( $atts['height'] ),
                'poster' => $this->_esc_url( $atts['poster'] ),'loop' => $this->_tp_validate_boolean( $atts['loop'] ),
                'autoplay' => $this->_tp_validate_boolean( $atts['autoplay'] ),'preload' => $atts['preload'],];
            foreach ( array( 'poster', 'loop', 'autoplay', 'preload' ) as $a )
                if ( empty( $html_atts[ $a ] ) ) unset( $html_atts[ $a ] );
            $attr_strings = array();
            foreach ( $html_atts as $k => $v )  $attr_strings[] = "$k='{$this->_esc_attr( $v )}'";
            ob_start();
            sprintf( "<video %s controls>", implode( ' ', $attr_strings ) );
            $html = ob_get_clean();
            $fileurl = '';
            $source ="<source type='%s' src='%s'/>";
            foreach ( $default_types as $fallback ) {
                if ( ! empty( $atts[ $fallback ] ) ) {
                    if ( empty( $fileurl ) )
                        $fileurl = $atts[ $fallback ];
                    if ( 'src' === $fallback && $is_youtube )
                        $type = array( 'type' => 'video/youtube' );
                    elseif ( 'src' === $fallback && $is_vimeo )
                        $type = array( 'type' => 'video/vimeo' );
                    else $type = $this->_tp_check_file_type( $atts[ $fallback ], $this->_tp_get_mime_types() );
                    $url   = $this->_add_query_arg( '_', $instance, $atts[ $fallback ] );
                    $html .= sprintf( $source, $type['type'], $this->_esc_url( $url ) );
                }
            }
            if ( ! empty( $content ) ) {
                if ( false !== strpos( $content, "\n" ) )
                    $content = str_replace( array( "\r\n", "\n", "\t" ), '', $content );
                $html .= trim( $content );
            }
            if ( 'media_element' === $library ) $html .= $this->_tp_media_element_fallback( $fileurl );
            $html .= "</video>";
            $width_rule = '';
            if ( ! empty( $atts['width'] ) )
                $width_rule = sprintf( 'width: %dpx;', $atts['width'] );
            $output = sprintf( "<div style='%s' class='tp-video'>%s</div>", $width_rule, $html );
            return $this->_apply_filters( 'tp_video_shortcode', $output, $atts, $video, $post_id, $library );
        }//3156
        public function tp_video_shortcode( $attr, $content = '' ){
            return $this->_get_video_shortcode( $attr, $content);
        }
        /**
         * @description Gets the previous image link that has the same post parent.
         * @param string $size
         * @param bool $text
         * @return mixed
         */
        protected function _get_previous_image_link( $size = 'thumbnail', $text = false ){
            return $this->_get_adjacent_image_link( true, $size, $text );
        }//3407
        /**
         * @description Displays previous image link that has the same post parent.
         * @param string $size
         * @param bool $text
         */
        public function previous_image_link( $size = 'thumbnail', $text = false ):void{
            echo $this->_get_previous_image_link( $size, $text );
        }//3420
     }
}else die;