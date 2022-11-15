<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 23:09
 */
namespace TP_Core\Traits\K_Ses;
if(ABSPATH){
    trait _k_ses_03 {
        /**
         * @description Converts the keys of an array to lowercase.
         * @param $in_array
         * @return array
         */
        protected function _tp_kses_array_lc( $in_array ):array{
            $out_array = [];
            foreach ( (array) $in_array as $in_key => $in_val ) {
                $out_key              = strtolower( $in_key );
                $out_array[ $out_key ] = array();
                foreach ( (array) $in_val as $in_key2 => $in_val2 ) {
                    $out_key2                         = strtolower( $in_key2 );
                    $out_array[ $out_key ][ $out_key2 ] = $in_val2;
                }
            }
            return $out_array;
        }//1760
        /**
         * @description Handles parsing errors in `tp_kses_hair()`.
         * @param $string
         * @return mixed
         */
        protected function _tp_kses_html_error( $string ){
            return preg_replace( '/^("[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*/', '', $string );
        }//1787
        /**
         * @description Sanitizes content from bad protocols and other characters.
         * @param $string
         * @param $allowed_protocols
         * @param int $count
         * @return mixed|string
         */
        protected function _tp_kses_bad_protocol_once( $string, $allowed_protocols, $count = 1 ){
            $string  = preg_replace( '/(&#0*58(?![;0-9])|&#x0*3a(?![;a-f0-9]))/i', '$1;', $string );
            $string2 = preg_split( '/:|&#0*58;|&#x0*3a;|&colon;/i', $string, 2 );
            if ( isset( $string2[1] ) && ! preg_match( '%/\?%', $string2[0] ) ) {
                $string   = trim( $string2[1] );
                $protocol = $this->_tp_kses_bad_protocol_once2( $string2[0], $allowed_protocols );
                if ( 'feed:' === $protocol ) {
                    if ( $count > 2 ) return '';
                    $string = $this->_tp_kses_bad_protocol_once( $string, $allowed_protocols, ++$count );
                    if ( empty( $string ) ) return $string;
                }
                $string = $protocol . $string;
            }
            return $string;
        }//1804
        /**
         * @description Callback for `tp_kses_bad_protocol_once()` regular expression.
         * @param $string
         * @param $allowed_protocols
         * @return string
         */
        protected function _tp_kses_bad_protocol_once2( $string, $allowed_protocols ):?string{
            $string2 = $this->_tp_kses_decode_entities( $string );
            $string2 = preg_replace( '/\s/', '', $string2 );
            $string2 = $this->_tp_kses_no_null( $string2 );
            $string2 = strtolower( $string2 );
            $allowed = false;
            foreach ( (array) $allowed_protocols as $one_protocol ) {
                if ( strtolower( $one_protocol ) === $string2 ) {
                    $allowed = true;
                    break;
                }
            }
            if ( $allowed ) return "$string2:";
            else return '';
        }//1840
       /**
         * @description Converts and fixes HTML entities.
         * @param $string
         * @param string $context
         * @return mixed
         */
        protected function _tp_kses_normalize_entities( $string, $context = 'html' ){
            $string = str_replace( '&', '&amp;', $string );
            if ( 'xml' === $context )
                $string = preg_replace_callback( '/&amp;([A-Za-z]{2,8}\d{0,2});/', [$this,'_tp_kses_xml_named_entities'], $string );
            else $string = preg_replace_callback( '/&amp;([A-Za-z]{2,8}\d{0,2});/',[$this,'_tp_kses_named_entities'], $string );
            $string = preg_replace_callback( '/&amp;#(0*\d{1,7});/',[$this,'_tp_kses_normalize_entities2'], $string );
            $string = preg_replace_callback( '/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/',[$this,'_tp_kses_normalize_entities3'], $string );
            return $string;
        }//1878
        /**
         * @description Callback for `tp_kses_normalize_entities()` regular expression.
         * @param $matches
         * @return string
         */
        protected function _tp_kses_named_entities( $matches ):string{
            if ( empty( $matches[1] ) ) return '';
            $i = $matches[1];
            $_allowed_entity_names = null;
            if(null !== $this->allowed_entity_names){
                $_allowed_entity_names = ( ! in_array( $i, $this->allowed_entity_names, true ) );
            }
            return $_allowed_entity_names ? "&amp;$i;" : "&$i;";
        }//1907
        /**
         * @description Callback for `tp_kses_normalize_entities()` regular expression.
         * @param $matches
         * @return string
         */
        protected function _tp_kses_xml_named_entities( $matches ):string{
            if ( empty( $matches[1] ) ) return '';
            $i = $matches[1];
            if ( in_array( $i, $this->allowed_entity_names, true ) ) return "&$i;";
            elseif ( in_array( $i, $this->allowed_entity_names, true ) )
                return html_entity_decode( "&$i;", ENT_HTML5 );
            return "&amp;$i;";
        }//1933
        /**
         * @description Callback for `tp_kses_normalize_entities()` regular expression.
         * @param $matches
         * @return string
         */
        protected function _tp_kses_normalize_entities2( $matches ):string{
            if ( empty( $matches[1] ) ) return '';
            $i = $matches[1];
            if ( $this->_valid_unicode( $i ) ) {
                $i = str_pad( ltrim( $i, '0' ), 3, '0', STR_PAD_LEFT );
                $i = "&#$i;";
            } else $i = "&amp;#$i;";
            return $i;
        }//1964
        /**
         * @description Callback for `tp_kses_normalize_entities()` for regular expression.
         * @param $matches
         * @return string
         */
        protected function _tp_kses_normalize_entities3( $matches ):string{
            if ( empty( $matches[1] ) ) return '';
            $hex_chars = $matches[1];
            return ( ! $this->_valid_unicode( hexdec( $hex_chars ) ) ) ? "&amp;#x$hex_chars;" : '&#x' . ltrim( $hex_chars, '0' ) . ';';
        }//1993
        /**
         * @description Determines if a Unicode code point is valid.
         * @param $i
         * @return bool
         */
        protected function _valid_unicode( $i ):bool{
            return ( 0x9 === $i || 0xa === $i || 0xd === $i ||
                ( 0x20 <= $i && $i <= 0xd7ff ) ||
                ( 0xe000 <= $i && $i <= 0xfffd ) ||
                ( 0x10000 <= $i && $i <= 0x10ffff ) );
        }//2010
    }
}else die;