<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 10:04
 */
namespace TP_Core\Traits\Formats;
use TP_Core\Libs\HTTP\TP_Http;
if(ABSPATH){
    trait _formats_10 {
        //private $__links_add_base;
        /**
         * @description Callback to add a base url to relative links in passed content. todo
         * @param $m
         * @return string
         */
        protected function _links_add_base( $m ):string{
            return $m[1] . '=' . $m[2] .
            ( preg_match( '#^(\w{1,20}):#', $m[3], $protocol ) && in_array( $protocol[1], $this->_tp_allowed_protocols(), true ) ?
                $m[3] : TP_Http::make_absolute_url( $m[3], $this->_links_add_base )
            )
            . $m[2];
        }//5291
        /**
         * @description Adds a Target attribute to all links in passed content.
         * @param $content
         * @param string $target
         * @param mixed $tags
         * @return mixed
         */
        protected function _links_add_target_key( $content, $target = '_blank',$tags = ['a'] ){
            $this->_links_add_target = $target;
            $tags              = implode( '|', $tags );
            return preg_replace_callback( "!<($tags)((\s[^>]*)?)>!i", '__links_add_target', $content );
        }//5319
        /**
         * @description Callback to add a target attribute to all links in passed content.
         * @param $m
         * @return string
         */
        protected function _links_add_target_value( $m ):string{
            $tag  = $m[1];
            $link = preg_replace( '|( target=([\'"])(.*?)\2)|i', '', $m[2] );
            return "{$tag}{$link} target={$this->_esc_attr( $this->_links_add_target )}>";
        }//5337
        /**
         * @description Normalize EOL characters and strip duplicate whitespace.
         * @param $str
         * @return mixed|string
         */
        protected function _normalize_whitespace( $str ){
            $str = trim( $str );
            $str = str_replace( "\r", "\n", $str );
            $str = preg_replace( array( '/\n+/', '/[ \t]+/' ), array( "\n", ' ' ), $str );
            return $str;
        }//5352
        /**
         * @param $string
         * @param bool $remove_breaks
         * @return string
         */
        protected function _tp_strip_all_tags( $string, $remove_breaks = false ):string{
            $string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
            $string = strip_tags( $string );
            if ( $remove_breaks ) $string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
            return trim( $string );
        }//5372
        /**
         * @description Sanitizes a string from user input or from the database.
         * @param $str
         * @return mixed
         */
        protected function _sanitize_text_field( $str ){
            $filtered = $this->_sanitize_text_fields( $str, false );
            return $this->_apply_filters( 'sanitize_text_field', $filtered, $str );
        }//5401
        /**
         * @description Sanitizes a multi line string from user input or from the database.
         * @param $str
         * @return mixed
         */
        protected function _sanitize_textarea_field( $str ){
            $filtered = $this->_sanitize_text_fields( $str, true );
            return $this->_apply_filters( 'sanitize_textarea_field', $filtered, $str );
        }//5429
        /**
         * @description Internal helper function to sanitize a string from user input or from the db
         * @param $str
         * @param bool $keep_newlines
         * @return mixed|string
         */
        protected function _sanitize_text_fields( $str, $keep_newlines = false ){
            if ( is_object( $str ) || is_array( $str ) ) return '';
            $filtered = $this->_tp_check_invalid_utf8( $str );
            if ( strpos( $filtered, '<' ) !== false ) {
                $filtered = $this->_tp_pre_kses_less_than( $filtered );
                $filtered = $this->_tp_strip_all_tags( $filtered, false );
                $filtered = str_replace( "<\n", "&lt;\n", $filtered );
            }
            if ( ! $keep_newlines ) $filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
            $filtered = trim( $filtered );
            $found = false;
            while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
                $filtered = str_replace( $match[0], '', $filtered );
                $found    = true;
            }
            if ( $found ) $filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
            return $filtered;
        }//5453
        /**
         * @description i18n friendly version of basename()
         * @param $path
         * @param string $suffix
         * @return string
         */
        protected function _tp_basename( $path, $suffix = '' ):string{
            return urldecode( basename( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ), $suffix ) );
        }//5500
        /**
         * @description Sanitize a mime type
         * @param $mime_type
         * @return mixed
         */
        protected function _sanitize_mime_type( $mime_type ){
            $sanitize_mime_type = preg_replace( '/[^-+*.a-zA-Z0-9\/]/', '', $mime_type );
            return $this->_apply_filters( 'sanitize_mime_type', $sanitize_mime_type, $mime_type );
        }//5542
     }
}else die;