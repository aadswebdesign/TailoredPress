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
    trait _formats_06 {
        use _init_formats;
        /**
         * @description Adds all filters modifying the rel attribute of targeted links.
         */
        protected function _tp_init_targeted_link_rel_filters():void{
            $filters = $this->_rel_filters;
            foreach ( $filters as $filter ) $this->_add_filter( $filter, 'tp_targeted_link_rel' );
        }//3295
        /**
         * Removes all filters modifying the rel attribute of targeted links.
         */
        protected function _tp_remove_targeted_link_rel_filters():void{
            $filters = $this->_rel_filters;
            foreach ( $filters as $filter ) $this->_remove_filter( $filter, 'tp_targeted_link_rel' );
        }//3318
        /**
         * @description Convert one smiley code to the icon graphic file equivalent.
         * @param $matches
         * @return string
         */
        protected function _translate_smiley( $matches ):string{
            if ( count( $matches ) === 0 ) return '';
            $smiley = trim( reset( $matches ) );
            $img    = $this->_tp_smilies_trans[ $smiley ];
            $this->_apply_filters( 'smilies_src', $this->_includes_url( "images/smilies/$img" ), $img, $this->_site_url() );
            $class = 'tp-smiley';
            return sprintf("<img src='%s' alt='%s' class='{$class}' style='height: 1em; max-height: 1em;' />");
        }//3351
        /**
         * @description Convert text equivalent of smilies to images.
         * @param $text
         * @return string
         */
        protected function _convert_smilies( $text ):string{
            $output = '';
            if (! empty( $this->_tp_smilies_search ) && $this->_get_option( 'use_smilies' )) {
                $text_arr = preg_split( '/(<.*>)/U', $text, -1, PREG_SPLIT_DELIM_CAPTURE ); // Capture the tags as well as in between.
                // Loop stuff.
                $tags_to_ignore       = 'code|pre|style|script|textarea';
                $ignore_block_element = '';
                foreach ($text_arr as $iValue) {
                    $content = $iValue;
                    if ( '' === $ignore_block_element && preg_match( '/^<(' . $tags_to_ignore . ')[^>]*>/', $content, $matches ) )
                        $ignore_block_element = $matches[1];
                    if ( '' === $ignore_block_element && $content !== '' && '<' !== $content[0] )
                        $content = preg_replace_callback( $this->_tp_smilies_search, 'translate_smiley', $content );
                    if ( '' !== $ignore_block_element && '</' . $ignore_block_element . '>' === $content ) $ignore_block_element = '';
                    $output .= $content;
                }
            }else $output = $text;
            return $output;
        }//3397
        /**
         * @description Verifies that an email is valid.
         * @param $email
         * @return mixed
         */
        protected function _is_email( $email ){
            if ( strlen( $email ) < 6 )
                return $this->_apply_filters( 'is_email', false, $email, 'email_too_short' );
            if ( strpos( $email, '@', 1 ) === false )
                return $this->_apply_filters( 'is_email', false, $email, 'email_no_at' );
            @list( $local, $domain ) = explode( '@', $email, 2 );
            if ( ! preg_match( '/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]+$/', $local ) )
                return $this->_apply_filters( 'is_email', false, $email, 'local_invalid_chars' );
            if ( preg_match( '/\.{2,}/', $domain ) ) return $this->_apply_filters( 'is_email', false, $email, 'domain_period_sequence' );
            if ( trim( $domain, " \t\n\r\0\x0B." ) !== $domain ) return $this->_apply_filters( 'is_email', false, $email, 'domain_period_limits' );
            $subs = explode( '.', $domain );
            if ( 2 > count( $subs ) ) return $this->_apply_filters( 'is_email', false, $email, 'domain_no_periods' );
            foreach ( $subs as $sub ) {
                if ( trim( $sub, " \t\n\r\0\x0B-" ) !== $sub ) return $this->_apply_filters( 'is_email', false, $email, 'sub_hyphen_limits' );
                if ( ! preg_match( '/^[a-z0-9-]+$/i', $sub ) ) return $this->_apply_filters( 'is_email', false, $email, 'sub_invalid_chars' );
            }
            return $this->_apply_filters( 'is_email', $email, $email, null );
        }//3447
        /**
         * @description Convert to ASCII from email subjects.
         * @param $string
         * @return mixed
         */
        protected function _tp_iso_descrambler( $string ){
            if ( ! preg_match( '#\=\?(.+)\?Q\?(.+)\?\=#i', $string, $matches ) ) return $string;
            else {
                $subject = str_replace( '_', ' ', $matches[2] );
                return preg_replace_callback( '#\=([0-9a-f]{2})#i', '_wp_iso_convert', $subject );
            }
        }//3536
        /**
         * @description Helper function to convert hex encoded chars to ASCII
         * @param $match
         * @return string
         */
        protected function _tp_iso_convert( $match ):string{
            return chr( hexdec( strtolower( $match[1] ) ) );
        }//3555
        /**
         * @description Given a date in the timezone of the site, returns that date in UTC.
         * @param $string
         * @param string $format
         * @return false|string
         */
        protected function _get_gmt_from_date( $string, $format = 'Y-m-d H:i:s' ){
            $datetime = date_create( $string, $this->_tp_timezone() );
            if ( false === $datetime ) return gmdate( $format, 0 );
            return $datetime->setTimezone( new \DateTimeZone( 'UTC' ) )->format( $format );
        }//3571
        /**
         * @description Given a date in UTC or GMT timezone, returns that date in the timezone of the site.
         * @param $string
         * @param string $format
         * @return false|string
         */
        protected function _get_date_from_gmt( $string, $format = 'Y-m-d H:i:s' ){
            $datetime = date_create( $string, new \DateTimeZone( 'UTC' ) );
            if ( false === $datetime ) return gmdate( $format, 0 );
            return $datetime->setTimezone( $this->_tp_timezone() )->format( $format );
        }//3593
        /**
         * @description Given an ISO 8601 timezone, returns its UTC offset in seconds.
         * @param $timezone
         * @return int
         */
        protected function _iso8601_timezone_to_offset( $timezone ):int{
            if ( 'Z' === $timezone ) $offset = 0;
            else {
                $sign    = (strpos($timezone, '+') === 0) ? 1 : -1;
                $hours   = (int) substr( $timezone, 1, 2 );
                $minutes = (int) substr( $timezone, 3, 4 ) / 60;
                $offset  = $sign * HOUR_IN_SECONDS * ( $hours + $minutes );
            }
            return $offset;
        }//3611
    }
}else die;