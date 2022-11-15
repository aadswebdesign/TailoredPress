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
    trait _formats_04 {
        use _init_formats;
        /**
         * @description Add leading zeros when necessary.
         * @param $number
         * @param $threshold
         * @return string
         */
        protected function _zero_ise( $number, $threshold ):string{
            return sprintf( '%0' . $threshold . 's', $number );
        }//2712
        /**
         * @description Adds backslashes before letters and before a number at the start of a string.
         * @param $string
         * @return string
         */
        protected function _backslashit( $string ):string{
            if ( isset( $string[0] ) && $string[0] >= '0' && $string[0] <= '9' ) $string = '\\\\' . $string;
            return addcslashes( $string, 'A..Za..z' );
        }//2724
        /**
         * @description Appends a trailing slash.
         * @param $string
         * @return string
         */
        protected function _trailingslashit( $string ):string{
            //if ( isset( $string[0] ) && $string[0] >= '0' && $string[0] <= '9' )
                //$string = '\\\\' . $string;
            //return addcslashes( $string, 'A..Za..z' );
            return $this->_untrailingslashit( $string ) . '/';

        }//2745
        /**
         * @description Removes trailing forward slashes and backslashes if they exist.
         * @param $string
         * @return string
         */
        protected function _untrailingslashit( $string ):string{
            return rtrim( $string, '/\\' );
        }//2760
        /**
         * @description Adds slashes to a string or recursively adds slashes to strings within an array.
         * @param $gpc
         * @return mixed
         */
        protected function _add_slashes_gpc( $gpc ){
            return $this->_tp_slash($gpc);
        }//2775
        /**
         * @description Navigates through an array, object, or scalar, and removes slashes from the values.
         * @param $value
         * @return mixed
         */
        protected function _strip_slashes_deep( $value ){
            return $this->_map_deep( $value, [$this,'_strip_slashes_from_strings_only'] );
        }//2787
        /**
         * @description Callback function for `strip_slashes_deep()` which strips slashes from strings.
         * @param $value
         * @return string
         */
        protected function _strip_slashes_from_strings_only( $value ):string{
            return is_string( $value ) ? stripslashes( $value ) : $value;
        }//2799
        /**
         * @description Navigates through an array, object, or scalar, and encodes the values to be used in a URL.
         * @param $value
         * @return mixed
         */
        protected function _url_encode_deep( $value ){
            return $this->_map_deep( $value, [$this,'_url_encode'] );
        }//2811
        /**
         * @description Navigates through an array, object, or scalar, and raw-encodes the values to be used in a URL.
         * @param $value
         * @return mixed
         */
        protected function _raw_url_encode_deep( $value ){
            return $this->_map_deep( $value, [$this,'_raw_url_encode'] );//todo testing
        }//2823
        /**
         * @description Navigates through an array, object, or scalar, and decodes URL-encoded values
         * @param $value
         * @return mixed
         */
        protected function _url_decode_deep( $value ){
            return $this->_map_deep( $value, [$this,'_url_decode'] );
        }//2835
        /**
         * @description Converts email addresses characters to HTML entities to block spam bots.
         * @param $email_address
         * @param int $hex_encoding
         * @return mixed
         */
        protected function _anti_spam_bot( $email_address, $hex_encoding = 0 ){
            $email_no_spam_address = '';
            for ( $i = 0, $len = strlen( $email_address ); $i < $len; $i++ ) {
                $j = random_int( 0, 1 + $hex_encoding );
                if ( 0 === $j ) $email_no_spam_address .= '&#' . ord( $email_address[ $i ] ) . ';';
                elseif ( 1 === $j ) $email_no_spam_address .= $email_address[ $i ];
                elseif ( 2 === $j ) $email_no_spam_address .= '%' . $this->_zero_ise( dechex( ord( $email_address[ $i ] ) ), 2 );
            }
            return str_replace( '@', '&#64;', $email_no_spam_address );
        }//2848
        protected function _url_encode($string):string{
            return urlencode(utf8_encode($string));
        }//added
        protected function _raw_url_encode($string):string{
            return rawurlencode($string);
        }//added
        protected function _url_decode($string):string{
            return  utf8_decode(urldecode($string));
        }//added
    }
}else die;