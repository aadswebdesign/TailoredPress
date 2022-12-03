<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 08:32
 */
namespace TP_Core\Traits\Compat;
use TP_Core\Traits\Inits\_init_compat;

if(ABSPATH){
    trait _compat_01 {
        use _init_compat;
        /**
         * @description Returns whether PCRE/u (PCRE_UTF8 modifier) is available for use.
         * @param null $set
         * @return int|null|string
         */
        protected function _tp_can_use_pcre_u( $set = null ){
            $this->_utf8_pcre = 'reset';
            if ( null !== $set ) $this->_utf8_pcre = $set;
            if ( 'reset' === $this->_utf8_pcre ) $this->_utf8_pcre = @preg_match( '/^./u', 'a' );
            return $this->_utf8_pcre;
        }//28

        protected function _mb_substr( $str, $start, $length = null, $encoding = null ):string {
            return $this->_get_mb_substr( $str, $start, $length, $encoding );
        }//59
        /**
         * @description Internal compat function to mimic mb_substr().
         * @param $str
         * @param $start
         * @param null $length
         * @param null $encoding
         * @return string
         */
        protected function _get_mb_substr( $str, $start, $length = null, $encoding = null ):string{
            if ( null === $str ) return '';
            if ( null === $encoding ) $encoding = $this->_get_option( 'blog_charset' );
            if ( ! in_array( $encoding, array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ), true ) )
                return is_null( $length ) ? substr( $str, $start ) : substr( $str, $start, $length );
            if ( $this->_tp_can_use_pcre_u() ) {
                preg_match_all( '/./us', $str, $match );
                $chars = is_null( $length ) ? array_slice( $match[0], $start ) : array_slice( $match[0], $start, $length );
                return implode( '', $chars );
            }
            $regex = '/(
		[\x00-\x7F]                  # single-byte sequences   0xxxxxxx
		| [\xC2-\xDF][\x80-\xBF]       # double-byte sequences   110xxxxx 10xxxxxx
		| \xE0[\xA0-\xBF][\x80-\xBF]   # triple-byte sequences   1110xxxx 10xxxxxx * 2
		| [\xE1-\xEC][\x80-\xBF]{2}
		| \xED[\x80-\x9F][\x80-\xBF]
		| [\xEE-\xEF][\x80-\xBF]{2}
		| \xF0[\x90-\xBF][\x80-\xBF]{2} # four-byte sequences   11110xxx 10xxxxxx * 3
		| [\xF1-\xF3][\x80-\xBF]{3}
		| \xF4[\x80-\x8F][\x80-\xBF]{2}
	)/x';
            $chars = array( '' );
            do {
                array_pop( $chars );
                $pieces = preg_split( $regex, $str, 1000, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
                $chars = $this->_tp_array_merge($chars, $pieces);
            } while ( count( $pieces ) > 1 && $str = array_pop( $pieces ) );
            return implode( '', array_slice( $chars, $start, $length ) );
        }//81
        /**
         * @description  Compat function to mimic mb_str len().
         * @param $str
         * @param null $encoding
         * @return int
         */
        protected function _mb_str_len( $str, $encoding = null ):int{
            return $this->_get_mb_str_len( $str, $encoding );
        }//150
        /**
         * @description Internal compat function to mimic mb_str_len().
         * @param $str
         * @param null $encoding
         * @return int
         */
        protected function _get_mb_str_len( $str, $encoding = null ):int{
            if ( null === $encoding ) $encoding = $this->_get_option( 'blog_charset' );
            if ( ! in_array( $encoding, array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ), true ) )
                return strlen( $str );
            if ( $this->_tp_can_use_pcre_u() ) {
                // Use the regex unicode support to separate the UTF-8 characters into an array.
                preg_match_all( '/./us', $str, $match );
                return count( $match[0] );
            }
            $regex = '/(?:
		[\x00-\x7F]                  # single-byte sequences   0xxxxxxx
		| [\xC2-\xDF][\x80-\xBF]       # double-byte sequences   110xxxxx 10xxxxxx
		| \xE0[\xA0-\xBF][\x80-\xBF]   # triple-byte sequences   1110xxxx 10xxxxxx * 2
		| [\xE1-\xEC][\x80-\xBF]{2}
		| \xED[\x80-\x9F][\x80-\xBF]
		| [\xEE-\xEF][\x80-\xBF]{2}
		| \xF0[\x90-\xBF][\x80-\xBF]{2} # four-byte sequences   11110xxx 10xxxxxx * 3
		| [\xF1-\xF3][\x80-\xBF]{3}
		| \xF4[\x80-\x8F][\x80-\xBF]{2}
	)/x';
            $count = 1;
            do {
                $count--;
                $pieces = preg_split( $regex, $str, 1000 );
                $count += count( $pieces );
            } while ( $str = array_pop( $pieces ) );
            return --$count;
        }//169
        /**
         * @description Compat function to mimic hash_hmac().
         * @param $al_go
         * @param $data
         * @param $key
         * @param bool $raw_output
         * @return bool|string
         */
        protected function _hash_hmac( $al_go, $data, $key, $raw_output = false ){
            return $this->_get_hash_hmac( $al_go, $data, $key, $raw_output );
        }//246
        /**
         * @description Internal compat function to mimic hash_h_mac().
         * @param $al_go
         * @param $data
         * @param $key
         * @param bool $raw_output
         * @return bool|string
         */
        protected function _get_hash_hmac( $al_go, $data, $key, $raw_output = false ){
            $packs = ['md5'  => 'H32','sha1' => 'H40',];
            if ( ! isset( $packs[ $al_go ] ) ) return false;
            $pack = $packs[ $al_go ];
            if ( strlen( $key ) > 64 ) $key = pack( $pack, $al_go( $key ) );
            $key = str_pad( $key, 64, chr( 0 ) );
            $i_pad = ( substr( $key, 0, 64 ) ^ str_repeat( chr( 0x36 ), 64 ) );
            $o_pad = ( substr( $key, 0, 64 ) ^ str_repeat( chr( 0x5C ), 64 ) );
            $h_mac = $al_go( $o_pad . pack( $pack, $al_go( $i_pad . $data ) ) );
            if ( $raw_output ) return pack( $pack, $h_mac );
            return $h_mac;
        }//265
        /**
         * @description Timing attack safe string comparison
         * @param $a
         * @param $b
         * @return bool
         */
        protected function _hash_equals( $a, $b ):bool{
            $a_length = strlen( $a );
            if ( strlen( $b ) !== $a_length ) return false;
            $result = 0;
            // Do not attempt to "optimize" this.
            for ( $i = 0; $i < $a_length; $i++ )
                $result |= ord( $a[ $i ] ) ^ ord( $b[ $i ] );
            return 0 === $result;
        }//315
        /* todo
        // random_int() was introduced in PHP 7.0.
        if ( ! function_exists( 'random_int' ) ) {
            require ABSPATH . WPINC . '/random_compat/random.php';
        }
        // sodium_crypto_box() was introduced in PHP 7.2.
        if ( ! function_exists( 'sodium_crypto_box' ) ) {
            require ABSPATH . WPINC . '/sodium_compat/autoload.php';
        }
         */
        /**
         * @description Polyfill for is_countable() function added in PHP 7.3.
         * @param $var
         * @return bool
         */
        protected function _is_countable( $var ):bool{
            return ( is_array( $var )
                || $var instanceof \Countable
                || $var instanceof \SimpleXMLElement
                || $var instanceof \ResourceBundle
            );
        }//352
        /**
         * @description Polyfill for is_iterable() function added in PHP 7.1.
         * @param $var
         * @return bool
         */
        protected function _is_iterable( $var ):bool{
            return ( is_array( $var ) || $this->_is_iterable($var) );
        }//373
        /**
         * @description Polyfill for array_key_first() function added in PHP 7.3.
         * @param array $arr
         * @return int|string
         */
        protected function _array_key_first( array $arr ){
            if(!empty($arr)) foreach ( $arr as $key => $value ) return $key;
            return null;
        }//391
    }
}else die;