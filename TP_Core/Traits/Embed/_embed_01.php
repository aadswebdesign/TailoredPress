<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-5-2022
 * Time: 10:19
 */
namespace TP_Core\Traits\Embed;
use TP_Core\Traits\Inits\_init_embed;
use TP_Core\Libs\Embed\TP_ObjEmbed;
if(ABSPATH){
    trait _embed_01{
        use _init_embed;
        /**
         * @param $id
         * @param $regex
         * @param $callback
         * @param int $priority
         */
        protected function _tp_embed_register_handler( $id, $regex, $callback, $priority = 10 ):void{
            $tp_embed = $this->_init_embed();
            $tp_embed->register_handler( $id, $regex, $callback, $priority );
        }//25
        /**
         * @description Unregisters a previously-registered embed handler.
         * @param $id
         * @param int $priority
         */
        protected function _tp_embed_unregister_handler( $id, $priority = 10 ):void{
            $tp_embed = $this->_init_embed();
            $tp_embed->unregister_handler( $id, $priority  );
        }//40
        /**
         * @description Creates default array of embed parameters.
         * @param string $url
         * @return mixed
         */
        protected function _tp_embed_defaults( $url = '' ){
            if ( ! empty( $this->__content_width ) ) $width = (int) $this->__content_width;
            if ( empty( $width ) ) $width = 500;
            $height = min( ceil( $width * 1.5 ), 1000 );
            return $this->_apply_filters( 'embed_defaults', compact( 'width', 'height' ), $url );
        }//67
        /**
         * @description Attempts to fetch the embed HTML for a provided URL using obj_Embed.
         * @param $url
         * @param string $args
         * @return mixed
         */
        protected function _tp_obj_embed_get( $url, $args = '' ){
            $_obj_embed = $this->_tp_obj_embed_get_object();
            $obj_embed = null;
            if( $_obj_embed instanceof TP_ObjEmbed ){
                $obj_embed = $_obj_embed;
            }
            return $obj_embed->get_html( $url, $args );
        }//113
        /**
         * @description Returns the initialized obj_Embed object.
         * @return null|TP_ObjEmbed
         */
        protected function _tp_obj_embed_get_object(): ?TP_ObjEmbed{
            static $tp_obj_embed = null;
            if (is_null($tp_obj_embed)) $tp_obj_embed = new TP_ObjEmbed();
            return $tp_obj_embed;
        }//126
        /**
         * @description Adds a URL format and obj_Embed provider URL pair.
         * @param $format
         * @param $provider
         * @param bool $regex
         */
        protected function _tp_obj_embed_add_provider( $format, $provider, $regex = false ):void{
            TP_ObjEmbed::_add_provider_early( $format, $provider, $regex );
        }//147
        /**
         * @description Removes an oEmbed provider.
         * @param $format
         * @return bool
         */
        protected function _tp_obj_embed_remove_provider( $format ):bool{
            TP_ObjEmbed::_remove_provider_early( $format );
            return false;
        }//166
        /**
         * @description Determines if default embed handlers should be loaded.
         */
        protected function _tp_maybe_load_embeds():void{
            if ( ! $this->_apply_filters( 'load_default_embeds', true ) ) return;
            $this->_tp_embed_register_handler( 'youtube_embed_url', '#https?://(www.)?youtube\.com/(?:v|embed)/([^/]+)#i', 'wp_embed_handler_youtube' );
            $this->_tp_embed_register_handler( 'audio', '#^https?://.+?\.(' . implode( '|', $this->_tp_get_audio_extensions() ) . ')$#i', $this->_apply_filters( 'tp_audio_embed_handler', 'tp_embed_handler_audio' ), 9999 );
            $this->_tp_embed_register_handler( 'video', '#^https?://.+?\.(' . implode( '|', $this->_tp_get_video_extensions() ) . ')$#i', $this->_apply_filters( 'tp_video_embed_handler', 'tp_embed_handler_video' ), 9999 );
        }//191
        /**
         * @description YouTube iframe embed handler callback.
         * @param $matches
         * @param $attr
         * @param $url
         * @param $rawattr
         * @return mixed
         */
        protected function _tp_embed_handler_youtube( $matches, $attr, $url, $rawattr ){
            $tp_embed = $this->_init_embed();
            $embed = $tp_embed->auto_embed( sprintf( 'https://youtube.com/watch?v=%s', urlencode( $matches[2] ) ) );
            return $this->_apply_filters( 'tp_embed_handler_youtube', $embed, $attr, $url, $rawattr );
        }//242
        /**
         * @description Audio embed handler callback.
         * @param $attr
         * @param $url
         * @param $rawattr
         * @return mixed
         */
        protected function _tp_embed_handler_audio( $attr, $url, $rawattr ){ //not used $matches,
            $audio = sprintf( '[audio src="%s" /]', $this->_esc_url( $url ) );
            return $this->_apply_filters( 'tp_embed_handler_audio', $audio, $attr, $url, $rawattr );
        }//272
    }
}else die;