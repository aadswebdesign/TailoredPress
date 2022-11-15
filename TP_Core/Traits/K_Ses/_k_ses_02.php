<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 23:09
 */
namespace TP_Core\Traits\K_Ses;
if(ABSPATH){
    trait _k_ses_02 {
        /**
         * @description Callback for `tp_kses_split()` for fixing malformed HTML tags.
         * @param $string
         * @param $allowed_html
         * @param $allowed_protocols
         * @return string
         */
        protected function _tp_kses_split2( $string, $allowed_html, $allowed_protocols ):string{
            $string = $this->_tp_kses_strip_slashes( $string );
            if (strpos($string, '<') !== 0) return '&gt;';
            if (strpos($string, '<!--') === 0) {
                $string = str_replace( array( '<!--', '-->' ), '', $string );
                while ( ( $new_string = $this->_tp_kses( $string, $allowed_html, $allowed_protocols ) ) !== $string )
                    $string = $new_string;
                if ( '' === $string ) return '';
                $string = preg_replace( '/--+/', '-', $string );
                $string = preg_replace( '/-$/', '', $string );
                return "<!--{$string}-->";
            }
            if ( ! preg_match( '%^<\s*(/\s*)?([a-zA-Z0-9-]+)([^>]*)>?$%', $string, ...$matches ) )
                return '';
            @list($slash_raw,$elem,$attr_list) = $matches;
            $slash    = trim( $slash_raw );
            if ( ! is_array( $allowed_html ) ) $allowed_html = $this->_tp_kses_allowed_html( $allowed_html );
            if ( ! isset( $allowed_html[ strtolower( $elem ) ] ) ) return '';
            if ( '' !== $slash ) return "</$elem>";
            return $this->_tp_kses_attr( $elem, $attr_list, $allowed_html, $allowed_protocols );
        }//1089
        /**
         * @description  Removes all attributes, if none are allowed for this element.
         * @param $element
         * @param $attr
         * @param $allowed_html
         * @param $allowed_protocols
         * @return string
         */
        protected function _tp_kses_attr( $element, $attr, $allowed_html, $allowed_protocols ):string{
            if ( ! is_array( $allowed_html ) )
                $allowed_html = $this->_tp_kses_allowed_html( $allowed_html );
            $xhtml_slash = '';
            if ( preg_match( '%\s*/\s*$%', $attr ) ) $xhtml_slash = ' /';
            $element_low = strtolower( $element );
            if ( empty( $allowed_html[ $element_low ] ) || true === $allowed_html[ $element_low ] )
                return "<$element$xhtml_slash>";
            $attr_arr = $this->_tp_kses_hair( $attr, $allowed_protocols );
            $required_attrs = array_filter(
                $allowed_html[ $element_low ],
                static function( $required_attr_limits ) {
                    return isset( $required_attr_limits['required'] ) && true === $required_attr_limits['required'];
                }
            );
            $stripped_tag = '';
            if ( empty( $xhtml_slash ) ) $stripped_tag = "<$element>";
            $attr2 = '';
            foreach ( $attr_arr as $arr_each ) {
                $required = isset( $required_attrs[ strtolower( $arr_each['name'] ) ] );

                if ( $this->_tp_kses_attr_check( $arr_each['name'], $arr_each['value'], $arr_each['whole'], $arr_each['vless'], $element, $allowed_html ) ) {
                    $attr2 .= ' ' . $arr_each['whole'];
                    if ( $required ) unset( $required_attrs[ strtolower( $arr_each['name'] ) ] );
                } elseif ( $required ) return $stripped_tag;
            }
            if ( ! empty( $required_attrs ) )  return $stripped_tag;
            $attr2 = preg_replace( '/[<>]/', '', $attr2 );
            return "<$element$attr2$xhtml_slash>";
        }//1168
        /**
         * @description Determines whether an attribute is allowed.
         * @param $name
         * @param $value
         * @param $whole
         * @param $vless
         * @param $element
         * @param $allowed_html
         * @return bool
         */
        protected function _tp_kses_attr_check( &$name, &$value, &$whole, $vless, $element, $allowed_html ):bool{
            $name_low    = strtolower( $name );
            $element_low = strtolower( $element );
            if ( ! isset( $allowed_html[ $element_low ] ) ) {
                $name  = '';
                $value = '';
                $whole = '';
                return false;
            }
            $allowed_attr = $allowed_html[ $element_low ];
            if ( ! isset( $allowed_attr[ $name_low ] ) || '' === $allowed_attr[ $name_low ] ) {
                if ( ! empty( $allowed_attr['data-*'] ) && strpos( $name_low, 'data-' ) === 0 && preg_match( '/^data(?:-[a-z0-9_]+)+$/', $name_low, $match ) ) {
                    $allowed_attr[ $match[0] ] = $allowed_attr['data-*'];
                } else {
                    $name  = '';
                    $value = '';
                    $whole = '';
                    return false;
                }
            }
            if ( 'style' === $name_low ) {
                $new_value = $this->_safe_css_filter_attr( $value );
                if ( empty( $new_value ) ) {
                    $name  = '';
                    $value = '';
                    $whole = '';
                    return false;
                }
                $whole = str_replace( $value, $new_value, $whole );
                $value = $new_value;
            }
            if ( is_array( $allowed_attr[ $name_low ] ) ) {
                // There are some checks.
                foreach ( $allowed_attr[ $name_low ] as $curr_key => $curr_val ) {
                    if ( ! $this->_tp_kses_check_attr_val( $value, $vless, $curr_key, $curr_val ) ) {
                        $name  = '';
                        $value = '';
                        $whole = '';
                        return false;
                    }
                }
            }
            return true;
        }//1250
        /**
         * @description Builds an attribute list from string containing attributes.
         * @param $attr
         * @param $allowed_protocols
         * @return array
         */
        protected function _tp_kses_hair( $attr, $allowed_protocols ):array{
            $attr_arr  = [];
            $mode     = 0;
            $attr_name = '';
            $uris     = $this->_tp_kses_uri_attributes();
            while ($attr !== '') {
                $working = 0;
                switch ( $mode ) {
                    case 0:
                        if ( preg_match( '/^([_a-zA-Z][-_a-zA-Z0-9:.]*)/', $attr, $match ) ) {
                            $attr_name = $match[1];
                            $working  = 1;
                            $mode     = 1;
                            $attr     = preg_replace( '/^[_a-zA-Z][-_a-zA-Z0-9:.]*/', '', $attr );
                        }
                        break;
                    case 1:
                        if ( preg_match( '/^\s*=\s*/', $attr ) ) { // Equals sign.
                            $working = 1;
                            $mode    = 2;
                            $attr    = preg_replace( '/^\s*=\s*/', '', $attr );
                            break;
                        }
                        if ( preg_match( '/^\s+/', $attr ) ) { // Valueless.
                            $working = 1;
                            $mode    = 0;
                            if ( false === array_key_exists( $attr_name, $attr_arr ) ) {
                                $attr_arr[ $attr_name ] = array(
                                    'name'  => $attr_name,
                                    'value' => '',
                                    'whole' => $attr_name,
                                    'vless' => 'y',
                                );
                            }
                            $attr = ltrim($attr);
                        }
                        break;
                    case 2:
                        if ( preg_match( '%^"([^"]*)"(\s+|/?$)%', $attr, $match ) ) {
                            // "value"
                            $this_val = $match[1];
                            if ( in_array( strtolower( $attr_name ), $uris, true ) )
                                $this_val = $this->_tp_kses_bad_protocol( $this_val, $allowed_protocols );
                            if ( false === array_key_exists( $attr_name, $attr_arr ) ) {
                                $attr_arr[ $attr_name ] = array(
                                    'name'  => $attr_name,
                                    'value' => $this_val,
                                    'whole' => "$attr_name=\"$this_val\"",
                                    'vless' => 'n',
                                );
                            }
                            $working = 1;
                            $mode    = 0;
                            $attr    = preg_replace( '/^"[^"]*"(\s+|$)/', '', $attr );
                            break;
                        }
                        if ( preg_match( "%^'([^']*)'(\s+|/?$)%", $attr, $match ) ) {
                            // 'value'
                            $this_val = $match[1];
                            if ( in_array( strtolower( $attr_name ), $uris, true ) )
                                $this_val = $this->_tp_kses_bad_protocol( $this_val, $allowed_protocols );
                            if ( false === array_key_exists( $attr_name, $attr_arr ) ) {
                                $attr_arr[ $attr_name ] = array(
                                    'name'  => $attr_name,
                                    'value' => $this_val,
                                    'whole' => "$attr_name='$this_val'",
                                    'vless' => 'n',
                                );
                            }
                            $working = 1;
                            $mode    = 0;
                            $attr    = preg_replace( "/^'[^']*'(\s+|$)/", '', $attr );
                            break;
                        }
                        if ( preg_match( "%^([^\s\"']+)(\s+|/?$)%", $attr, $match ) ) {
                            // value
                            $this_val = $match[1];
                            if ( in_array( strtolower( $attr_name ), $uris, true ) )
                                $this_val = $this->_tp_kses_bad_protocol( $this_val, $allowed_protocols );
                            if ( false === array_key_exists( $attr_name, $attr_arr ) ) {
                                $attr_arr[ $attr_name ] = array(
                                    'name'  => $attr_name,
                                    'value' => $this_val,
                                    'whole' => "$attr_name=\"$this_val\"",
                                    'vless' => 'n',
                                );
                            }
                            $working = 1;
                            $mode    = 0;
                            $attr    = preg_replace( "%^[^\s\"']+(\s+|$)%", '', $attr );
                        }
                        break;
                }
                if ( 0 === $working ) {
                    $attr = $this->_tp_kses_html_error( $attr );
                    $mode = 0;
                }
            }
            if ( 1 === $mode && false === array_key_exists( $attr_name, $attr_arr ) ) {
                $attr_arr[ $attr_name ] = array(
                    'name'  => $attr_name,
                    'value' => '',
                    'whole' => $attr_name,
                    'vless' => 'y',
                );
            }
            return $attr_arr;
        }//1334
        /**
         * @description Finds all attributes of an HTML element.
         * @param $element
         * @return array|bool
         */
        protected function _tp_kses_attr_parse( $element ){
            $valid = preg_match( '%^(<\s*)(/\s*)?([a-zA-Z0-9]+\s*)([^>]*)(>?)$%', $element, ...$matches );
            if ( 1 !== $valid ) return false;
            @list($begin,$slash,$el_name,$attr,$end)= $matches;
            if ( '' !== $slash ) return false;
            if ( 1 === preg_match( '%\s*/\s*$%', $attr, $matches ) ) {
                $xhtml_slash = $matches[0];
                $attr        = substr( $attr, 0, -strlen( $xhtml_slash ) );
            } else  $xhtml_slash = '';
            $attr_arr = $this->_tp_kses_hair_parse( $attr );
            if ( false === $attr_arr ) return false;
            array_unshift( $attr_arr, $begin . $slash . $el_name );
            $attr_arr[] = $xhtml_slash . $end;
            return $attr_arr;
        }//1479
        /**
         * @description Builds an attribute list from string containing attributes.
         * @param $attr
         * @return array|bool
         */
        protected function _tp_kses_hair_parse( $attr ){
            if ( '' === $attr ) return array();
            $regex =
                '(?:'
                .     '[_a-zA-Z][-_a-zA-Z0-9:.]*'
                . '|'
                .     '\[\[?[^\[\]]+\]\]?'
                . ')'
                . '(?:'
                .     '\s*=\s*'
                .     '(?:'
                .         '"[^"]*"'
                .     '|'
                .         "'[^']*'"
                .     '|'
                .         '[^\s"\']+'
                .         '(?:\s|$)'
                .     ')'
                . '|'
                .     '(?:\s|$)'
                . ')'
                . '\s*';
            $validation = "%^($regex)+$%";
            $extraction = "%$regex%";
            if ( 1 === preg_match( $validation, $attr ) ) {
                preg_match_all( $extraction, $attr, $attr_arr );
                return $attr_arr[0];
            } else return false;
        }//1530
        /**
         * @description Performs different checks for attribute values.
         * @param $value
         * @param $vless
         * @param $check_name
         * @param $check_value
         * @return bool
         */
        protected function _tp_kses_check_attr_val( $value, $vless, $check_name, $check_value ):bool{
            $ok = true;
            switch ( strtolower( $check_name ) ) {
                case 'maxlength': //maxlen
                    if ( strlen( $value ) > $check_value ) $ok = false;
                    break;
                case 'minlength': //minlen
                    if ( strlen( $value ) < $check_value ) $ok = false;
                    break;
                case 'maxvalue'://maxval
                    if ( ! preg_match( '/^\s{0,6}\d{1,6}\s{0,6}$/', $value ) ) $ok = false;
                    if ( $value > $check_value ) $ok = false;
                    break;
                case 'minvalue'://minval
                    if ( ! preg_match( '/^\s{0,6}\d{1,6}\s{0,6}$/', $value ) ) $ok = false;
                    if ( $value < $check_value ) $ok = false;
                    break;
                case 'valueless':
                    if ( strtolower( $check_value ) !== $vless ) $ok = false;
                    break;
                case 'values':
                    if (!in_array( strtolower( $value ), $check_value, true )) $ok = false;
                    break;
                case 'value_callback':
                    if ( !$check_value($value)) $ok = false;
                    break;
            } // End switch.
            return $ok;
        }//1586
        /**
         * @description Sanitizes a string and removed disallowed URL protocols.
         * @param $string
         * @param $allowed_protocols
         * @return mixed|string
         */
        protected function _tp_kses_bad_protocol( $string, $allowed_protocols ){
            $string     = $this->_tp_kses_no_null( $string );
            $iterations = 0;
            do {
                $original_string = $string;
                $string          = $this->_tp_kses_bad_protocol_once( $string, $allowed_protocols );
            } while ( $original_string !== $string && ++$iterations < 6 );
            if ( $original_string !== $string ) return '';
            return $string;
        }//1697
        /**
         * @description Removes any invalid control characters in a text string.
         * @param $string
         * @param null $options
         * @return mixed
         */
        protected function _tp_kses_no_null( $string, $options = null ){
            if ( ! isset( $options['slash_zero'] ) )
                $options = array( 'slash_zero' => 'remove' );
            $string = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string );
            if ( 'remove' === $options['slash_zero'] )
                $string = preg_replace( '/\\\\+0+/', '', $string );
            return $string;
        }//1724
        /**
         * @description Strips slashes from in front of quotes.
         * @param $string
         * @return mixed
         */
        protected function _tp_kses_strip_slashes( $string ){
            return preg_replace( '%\\\\"%', '"', $string );
        }//1748
    }
}else die;