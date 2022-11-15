<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 10:04
 */
namespace TP_Core\Traits\Formats;
if(ABSPATH){
    trait _formats_11 {
        /**
         * @description Sanitize space or carriage return separated URLs that are used to send trackbacks.
         * @param $to_ping
         * @return mixed
         */
        protected function _sanitize_trackback_urls( $to_ping ){
            $urls_to_ping = preg_split( '/[\r\n\t ]/', trim( $to_ping ), -1, PREG_SPLIT_NO_EMPTY );
            foreach ( $urls_to_ping as $k => $url ) {
                if ( ! preg_match( '#^https?://.#i', $url ) ) unset( $urls_to_ping[ $k ] );
            }
            $urls_to_ping = array_map( 'esc_url_raw', $urls_to_ping );
            $urls_to_ping = implode( "\n", $urls_to_ping );
            return $this->_apply_filters( 'sanitize_trackback_urls', $urls_to_ping, $to_ping );
        }//5563
        /**
         * @description Adds slashes to a string or recursively adds slashes to strings within an array.
         * @param $value
         * @return array|string
         */
        protected function _tp_slash( $value ){
            if ( is_array( $value ) ) $value = array_map( 'tp_slash', $value );
            if ( is_string( $value ) ) return addslashes( $value );
            return $value;
        }//5598
        /**
         * @description Removes slashes from a string or recursively removes slashes from strings within an array.
         * @param $value
         * @return mixed
         */
        protected function _tp_unslash( $value ){
            return $this->_strip_slashes_deep( $value );
        }//5621
        /**
         * @description Extract and return the first URL from passed content.
         * @param $content
         * @return bool
         */
        protected function _get_url_in_content( $content ):bool{
            if ( empty( $content ) ) return false;
            if ( preg_match( '/<a\s[^>]*?href=([\'"])(.+?)\1/is', $content, $matches ) )
                return $this->_esc_url_raw( $matches[2] );
            return false;
        }//5633 todo
        /**
         * @description Returns the regexp for common whitespace characters.
         * @return mixed
         */
        protected function _tp_spaces_regexp(){
            static $spaces = '';
            if ( empty( $spaces ) ) $spaces = $this->_apply_filters( 'tp_spaces_regexp', '[\r\n\t ]|\xC2\xA0|&nbsp;' );
            return $spaces;
        }//5656
        //todo, left out emoji 5683, 5716, 5735,5806,5993  or I want this all?
        /**
         * @description Shorten a URL, to be used as link text.
         * @since
         * @param string $url    URL to shorten.
         * @param int    $length Optional. Maximum length of the shortened URL. Default 35 characters.
         * @return string Shortened URL.
         */
        protected function _url_shorten( $url, $length = 35 ):string{
            $stripped  = str_replace( array( 'https://', 'http://', 'www.' ), '', $url );
            $short_url = $this->_untrailingslashit( $stripped );
            if ( strlen( $short_url ) > $length ) $short_url = substr( $short_url, 0, $length - 3 ) . '&hellip;';
            return $short_url;
        }//6018
        /**
         * @description Sanitizes a hex color.
         * @since
         * @param string $color
         * @return mixed
         */
        protected function _sanitize_hex_color( $color ) {
            $return = null;
            if ( '' === $color ) $return = '';
            // 3 or 6 hex digits, or the empty string.
            if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) $return =  $color;
            return $return;
        }//6039
        /**
         * @description Sanitizes a hex color without a hash.
         * @since
         * @param string $color
         * @return string|null
         */
        protected function _sanitize_hex_color_no_hash( $color ):string{
            $color = ltrim( $color, '#' );
            if ( '' === $color ) return '';
            return $this->_sanitize_hex_color("#{$color}" ) ? $color : null;
        }//6064
        /**
         * @description Ensures that any hex color is properly hashed. Otherwise, returns value untouched.
         * @since
         * @param string $color
         * @return string
         */
        protected function _maybe_hash_hex_color( $color ):string{
            $un_hashed = $this->_sanitize_hex_color_no_hash( $color );
            $return = null;
            if ( $un_hashed ) $return = "#{$un_hashed}";
            return $return;
        }//6085
    }
}else die;