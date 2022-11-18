<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-5-2022
 * Time: 13:00
 */
namespace TP_Core\Traits\ShortCode;
if(ABSPATH){
    trait _short_code_02{
        /**
         * @description Remove placeholders added by do_shortcodes_in_html_tags().
         * @param $content
         * @return string
         */
        protected function _un_escape_invalid_shortcodes( $content ):string{
            $trans = ['&#91;' => '[','&#93;' => ']',];
            $content = strtr( $content, $trans );
            return $content;
        }//493
        /**
         * @description Retrieve the shortcode attributes regex.
         * @return string
         */
        protected function _get_shortcode_atts_regex():string{
            return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
        }//512
        /**
         * @description Retrieve all attributes from the shortcodes tag.
         * @param $text
         * @return array
         */
        protected function _shortcode_parse_atts( $text ):array{
            $atts = [];
            $pattern = $this->_get_shortcode_atts_regex();
            $text = preg_replace( "/[\x{00a0}\x{200b}]+/u", ' ', $text );
            if ( preg_match_all( $pattern, $text, $match, PREG_SET_ORDER ) ) {
                foreach ( $match as $m ) {
                    if ( ! empty( $m[1] ) )  $atts[ strtolower( $m[1] ) ] = stripcslashes( $m[2] );
                    elseif ( ! empty( $m[3] ) )$atts[ strtolower( $m[3] ) ] = stripcslashes( $m[4] );
                    elseif ( ! empty( $m[5] ) ) $atts[ strtolower( $m[5] ) ] = stripcslashes( $m[6] );
                    elseif ( isset( $m[7] ) && $m[7] !== '')  $atts[] = stripcslashes( $m[7] );
                    elseif ( isset( $m[8] ) && $m[8] !== '') $atts[] = stripcslashes( $m[8] );
                    elseif ( isset( $m[9] ) )  $atts[] = stripcslashes( $m[9] );
                }
                foreach ( $atts as &$value ) {
                    if ((false !== strpos($value, '<')) && 1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) $value = '';
                }
            } else $atts = ltrim( $text );
            return $atts;
        }//531
        /**
         * @description Combine user attributes with known attributes and fill in defaults when needed.
         * @param $pairs
         * @param $atts
         * @param string $shortcode
         * @return array
         */
        protected function _shortcode_atts( $pairs, $atts, $shortcode = '' ):array{
            $atts = (array) $atts;
            $out  = array();
            foreach ( $pairs as $name => $default ) {
                if ( array_key_exists( $name, $atts ) )
                    $out[ $name ] = $atts[ $name ];
                else $out[ $name ] = $default;
            }
            if ( $shortcode )
                $out = $this->_apply_filters( "shortcode_atts_{$shortcode}", $out, $pairs, $atts, $shortcode );
            return $out;
        }//584
        /**
         * @description Remove all shortcode tags from the given content.
         * @param $content
         * @return mixed
         */
        protected function _strip_shortcodes( $content ){
            if ( false === strpos( $content, '[' ) ) return $content;
            if ( empty( $this->tp_shortcode_tags ) || ! is_array( $this->tp_shortcode_tags ) ) return $content;
            preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
            $tags_to_remove = array_keys( $this->tp_shortcode_tags );
            $tags_to_remove = $this->_apply_filters( 'strip_shortcodes_tagnames', $tags_to_remove, $content );
            $tagnames = array_intersect( $tags_to_remove, $matches[1] );
            if ( empty( $tagnames ) ) return $content;
            $content = $this->_do_shortcodes_in_html_tags( $content, true, $tagnames );
            $pattern = $this->_get_shortcode_regex( $tagnames );
            $content = preg_replace_callback( "/$pattern/", 'strip_shortcode_tag', $content );
            $content = $this->_un_escape_invalid_shortcodes( $content );
            return $content;
        }//626
        /**
         * @description Strips a shortcode tag based on RegEx matches against post content.
         * @param $m
         * @return string
         */
        protected function _strip_shortcode_tag( $m ):string{
            if ( '[' === $m[1] && ']' === $m[6] )
                return substr( $m[0], 1, -1 );
            return $m[1] . $m[6];
        }//677
    }
}else die;