<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 10:04
 */
namespace TP_Core\Traits\Formats;
use TP_Core\Traits\Inits\_init_formats;
if(ABSPATH){
    trait _formats_02 {
        use _init_formats;
        /**
         * @description Don't autop wrap shortcode's that stand alone
         * @param $pee
         * @return mixed
         */
        protected function _shortcode_un_autop( $pee ){
            if ( empty( $this->__shortcode_tags ) || ! is_array( $this->__shortcode_tags ) ) return $pee;
            $tag_regexp = implode( '|', array_map( 'preg_quote', array_keys( $this->__shortcode_tags ) ) );
            $spaces    = $this->_tp_spaces_regexp();
            $pattern = '/<p>(?:' . $spaces . ')*+'. '(\\['."($tag_regexp)".'(?![\\w-])[^\\]\\/]*(?:'
                .'\\/(?!\\])[^\\]\\/]*)*?(?:\\/\\]|\\](?:[^\\[]*+(?:'
                .'\\[(?!\\/\\2\\])[^\\[]*+)*+\\[\\/\\2\\])?))(?:' . $spaces . ')*+<\\/p>/';
            return preg_replace( $pattern, '$1', $pee );
        }//820
        /**
         * @description Checks to see if a string is utf8 encoded.
         * @param $str
         * @return bool
         */
        protected function _seems_utf8( $str ):bool{
            $this->_mb_string_binary_safe_encoding();
            $length = strlen( $str );
            $this->_reset_mb_string_encoding();
            for ( $i = 0; $i < $length; $i++ ) {
                $c = ord( $str[ $i ] );
                if ( $c < 0x80 ) $n = 0; // 0b
                elseif ( ( $c & 0xE0 ) === 0xC0 ) $n = 1; // 110bbbbb
                elseif ( ( $c & 0xF0 ) === 0xE0 ) $n = 2; // 1110bbbb
                elseif ( ( $c & 0xF8 ) === 0xF0 ) $n = 3; // 11110bbb
                elseif ( ( $c & 0xFC ) === 0xF8 ) $n = 4; // 111110bb
                elseif ( ( $c & 0xFE ) === 0xFC ) $n = 5; // 1111110b
                else return false; // Does not match any model.
                // n bytes matching 10b follow ?
                for ( $j = 0; $j < $n; $j++ ) if ( ( ++$i === $length ) || ( ( ord( $str[ $i ] ) & 0xC0 ) !== 0x80 ) ) return false;


            }
            return true;
        }//879
        /**
         * @description Converts a number of special characters into their HTML entities.
         * @param $string
         * @param int $quote_style
         * @param mixed $charset
         * @param bool $double_encode
         * @return string
         */
        protected function _tp_special_chars( $string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false ): string {
            $string = (string) $string;
            if ($string === '') return '';
            if ( ! preg_match( '/[&<>"\']/', $string ) ) return $string;
            if ( empty( $quote_style ) )
                $quote_style = ENT_NOQUOTES;
            elseif ( ENT_XML1 === $quote_style )
                $quote_style = ENT_QUOTES | ENT_XML1;
            elseif ( ! in_array( $quote_style, array( ENT_NOQUOTES, ENT_COMPAT, ENT_QUOTES, 'single', 'double' ), true ) )
                $quote_style = ENT_QUOTES;
            if ( ! $charset ) {
                $_charset = null;
                if ( ! isset( $_charset ) ) {
                    $all_options = $this->_tp_load_all_options();
                    $_charset   = $all_options['blog_charset'] ?? '';
                }
                $charset = $_charset;
            }
            if ( in_array( $charset, array( 'utf8', 'utf-8', 'UTF8' ), true ) ) $charset = 'UTF-8';
            if ( 'double' === $quote_style ) {
                $quote_style  = ENT_COMPAT;
            } elseif ( 'single' === $quote_style )
                $quote_style = ENT_NOQUOTES;
            if ( ! $double_encode ) $string = $this->_tp_kses_normalize_entities( $string, ( $quote_style & ENT_XML1 ) ? 'xml' : 'html' );
            $string = htmlspecialchars( $string, $quote_style, $charset, $double_encode );
            return $string;
        }//934
        /**
         * @description Converts a number of HTML entities into their special characters.
         * @param $string
         * @param int $quote_style
         * @return mixed|string
         */
        protected function _tp_special_chars_decode( $string, $quote_style = ENT_NOQUOTES ){
            $string = (string) $string;
            if ($string === '') return '';
            if ( strpos( $string, '&' ) === false ) return $string;
            if ( empty( $quote_style ) ) $quote_style = ENT_NOQUOTES;
            elseif ( ! in_array( $quote_style, array( 0, 2, 3, 'single', 'double' ), true ) ) $quote_style = ENT_QUOTES;
            if ( ENT_QUOTES === $quote_style ) {
                $this->_translation      = array_merge( $this->_single, $this->_double, $this->_others );
                $this->_translation_preg = array_merge( $this->_single_preg, $this->_double_preg, $this->_others_preg );
            } elseif ( ENT_COMPAT === $quote_style || 'double' === $quote_style ) {
                $this->_translation      = array_merge( $this->_double, $this->_others );
                $this->_translation_preg = array_merge( $this->_double_preg, $this->_others_preg );
            } elseif ( 'single' === $quote_style ) {
                $this->_translation      = array_merge( $this->_single, $this->_others );
                $this->_translation_preg = array_merge( $this->_single_preg, $this->_others_preg );
            } elseif ( ENT_NOQUOTES === $quote_style ) {
                $this->_translation      = $this->_others;
                $this->_translation_preg = $this->_others_preg;
            }
            $string = preg_replace( array_keys( $this->_translation_preg ), array_values( $this->_translation_preg ), $string );
            return strtr( $string, $this->_translation );
        }//1014
        /**
         * @description Checks for invalid UTF8 in a string.
         * @param $string
         * @param bool $strip
         * @return string
         */
        protected function _tp_check_invalid_utf8( $string, $strip = false ):string{
            $string = (string) $string;
            if ($string === '') return '';
            $utf = [ 'utf8', 'utf-8', 'UTF8', 'UTF-8'];
            if ( ! isset( $this->__is_utf8 ) ) $this->__is_utf8 = in_array( $this->_get_option( 'blog_charset' ), $utf, true );
            if (!$this->__is_utf8 ) return $string;
            if(! isset($this->__utf8_pcre)) $this->__utf8_pcre = @preg_match( '/^./u', 'a' );
            if(! $this->__utf8_pcre) return $string;
            if ( 1 === @preg_match( '/^./us', $string ) ) return $string;
            if ( $strip && function_exists( 'iconv' ) ) return iconv( 'utf-8', 'utf-8', $string );
            return '';
        }//1097
        /**
         * @description Encode the Unicode values to be used in the URI.
         * @param $utf8_string
         * @param int $length
         * @param bool $encode_ascii_characters
         * @return string
         */
        protected function _utf8_uri_encode( $utf8_string, $length = 0, $encode_ascii_characters = false ):string{
            $unicode        = '';
            $values         = [];
            $num_octets     = 1;
            $unicode_length = 0;
            $this->_mb_string_binary_safe_encoding();
            $string_length = strlen( $utf8_string );
            $this->_reset_mb_string_encoding();
            for ( $i = 0; $i < $string_length; $i++ ) {
                $value = ord( $utf8_string[ $i ] );
                if ( $value < 128 ) {
                    $char                = chr( $value );
                    $encoded_char        = $encode_ascii_characters ? rawurlencode( $char ) : $char;
                    $encoded_char_length = strlen( $encoded_char );
                    if ( $length && ( $unicode_length + $encoded_char_length ) > $length ) break;
                    $unicode        .= $encoded_char;
                    $unicode_length += $encoded_char_length;
                }else{
                    if ( count( $values ) === 0 ) {
                        if ( $value < 224 ) $num_octets = 2;
                        elseif ( $value < 240 ) $num_octets = 3;
                        else $num_octets = 4;
                    }
                    $values[] = $value;
                    if ( $length && ( $unicode_length + ( $num_octets * 3 ) ) > $length ) break;
                    if ( count( $values ) === $num_octets ) {
                        for ( $j = 0; $j < $num_octets; $j++ ) $unicode .= '%' . dechex( $values[ $j ] );
                        $unicode_length += $num_octets * 3;
                        $values = [];
                        $num_octets = 1;
                    }
                }
            }
            return $unicode;
        }//1148
        /**
         * @description Converts all accent characters to ASCII characters.
         * @param $string
         * @return mixed|string
         */
        protected function _remove_accents( $string ){
            if ( ! preg_match( '/[\x80-\xff]/', $string ) ) return $string;
            if ( $this->_seems_utf8( $string ) ){
                $chars = $this->_chars;
                $locale = $this->_get_locale();
                if ( in_array( $locale, array( 'de_DE', 'de_DE_formal', 'de_CH', 'de_CH_informal', 'de_AT' ), true ) ) {
                    $chars['Ä'] = 'Ae';
                    $chars['ä'] = 'ae';
                    $chars['Ö'] = 'Oe';
                    $chars['ö'] = 'oe';
                    $chars['Ü'] = 'Ue';
                    $chars['ü'] = 'ue';
                    $chars['ß'] = 'ss';
                }elseif ( 'da_DK' === $locale ) {
                    $chars['Æ'] = 'Ae';
                    $chars['æ'] = 'ae';
                    $chars['Ø'] = 'Oe';
                    $chars['ø'] = 'oe';
                    $chars['Å'] = 'Aa';
                    $chars['å'] = 'aa';
                } elseif ( 'ca' === $locale ) {
                    $chars['l·l'] = 'll';
                } elseif ( 'sr_RS' === $locale || 'bs_BA' === $locale ) {
                    $chars['Đ'] = 'DJ';
                    $chars['đ'] = 'dj';
                }
                $string = strtr( $string, $chars );
            }else{
                $chars = [];
                // Assume ISO-8859-1 if not UTF-8.
                $chars['in'] = $this->_chars_in;
                $chars['out'] = 'EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy';
                $string  = strtr( $string, $chars['in'], $chars['out'] );
                $double_chars        = array();
                $double_chars['in']  = $this->_double_chars_inn;
                $double_chars['out']  = $this->_double_chars_out;
                $string              = str_replace( $double_chars['in'], $double_chars['out'], $string );
            }
            return $string;
        }//1590
        /**
         * @description Sanitizes a filename, replacing whitespace with dashes.
         * @param $filename
         * @return mixed
         */
        protected function _sanitize_file_name( $filename ){
            $filename_raw = $filename;
            $filename     = $this->_remove_accents( $filename );
            $special_chars = array_merge($this->_special_chars,(array)chr( 0 ));
            $this->_utf8_pcre = null;
            if ( ! isset( $this->_utf8_pcre ) ) $this->_utf8_pcre = @preg_match( '/^./u', 'a' );
            if ( ! $this->_seems_utf8( $filename ) ) {
                $_ext     = pathinfo( $filename, PATHINFO_EXTENSION );
                $_name    = pathinfo( $filename, PATHINFO_FILENAME );
                $filename = $this->_sanitize_title_with_dashes( $_name ) . '.' . $_ext;
            }
            if ( $this->_utf8_pcre ) $filename = preg_replace( "#.\x{00a0}#siu", ' ', $filename );
            $special_chars = $this->_apply_filters('sanitize_file_name_chars', $special_chars, $filename_raw );
            $filename = str_replace( $special_chars, '', $filename );
            $filename .= str_replace( array( '%20', '+' ), '-', $filename );
            $filename = preg_replace( '/[\r\n\t -]+/', '-', $filename );
            $filename = trim( $filename, '.-_' );
            if ( false === strpos( $filename, '.' ) ) {
                $mime_types = $this->_tp_get_mime_types();
                $file_type   = $this->_tp_check_file_type( 'test.' . $filename, $mime_types );
                if ( $file_type['ext'] === $filename )
                    $filename = 'unnamed-file.' . $file_type['ext'];
            }
            $parts = explode( '.', $filename );
            if ( count( $parts ) <= 2 ) return $this->_apply_filters( 'sanitize_file_name', $filename, $filename_raw );
            $filename  = array_shift( $parts );
            $extension = array_pop( $parts );
            $mimes     = $this->_get_allowed_mime_types();
            foreach ($parts as $part ) {
                $filename .= '.' . $part;
                if ( preg_match( '/^[a-zA-Z]{2,5}\d?$/', $part ) ) {
                    $allowed = false;
                    foreach ( $mimes as $ext_preg => $mime_match ) {
                        $ext_preg = '!^(' . $ext_preg . ')$!i';
                        if ( preg_match( $ext_preg, $part ) ) {
                            $allowed = true;
                            break;
                        }
                    }
                    if ( ! $allowed ) $filename .= '_';
                }
            }
            $filename .= '.' . $extension;
            return $this->_apply_filters( 'sanitize_file_name', $filename, $filename_raw );
        }//1992
        /**
         * @description Sanitizes a username, stripping out unsafe characters.
         * @param $username
         * @param bool $strict
         * @return mixed
         */
        protected function _sanitize_user( $username, $strict = false ){
            $raw_username = $username;
            $username     = $this->_tp_strip_all_tags( $username );
            $username     = $this->_remove_accents( $username );
            $username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
            $username = preg_replace( '/&.+?;/', '', $username );
            if ( $strict ) $username = preg_replace( '|[^a-z0-9 _.\-@]|i', '', $username );
            $username = trim( $username );
            $username = preg_replace( '|\s+|', ' ', $username );
            return $this->_apply_filters( 'sanitize_user', $username, $raw_username, $strict );
        }//2102
        /**
         * @description Sanitizes a string key.
         * @param $key
         * @return mixed
         */
        protected function _sanitize_key( $key ){
            $sanitized_key = '';
            if ( is_scalar( $key ) ) {
                $sanitized_key = strtolower( $key );
                $sanitized_key = preg_replace( '/[^a-z0-9_\-]/', '', $sanitized_key );
            }
            return $this->_apply_filters( 'sanitize_key', $sanitized_key, $key );
        }//2143
     }
}else die;