<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-5-2022
 * Time: 13:00
 */
namespace TP_Core\Traits\ShortCode;
//use TP_Core\Traits\Inits\_init_shortcode_tags;
if(ABSPATH){
    trait _short_code_01{
        //use _init_shortcode_tags;
        /**
         * @description Adds a new shortcode.
         * @param $tag
         * @param $callback
         */
        protected function _add_shortcode( $tag, $callback ):void{
            if ( '' === trim( $tag ) ) {
                $this->_doing_it_wrong(__FUNCTION__,$this->__( 'Invalid shortcode name: Empty name given.' ),'0.0.1');
                return;
            }
            if ( 0 !== preg_match( '@[<>&/\[\]\x00-\x20=]@', $tag ) ) {
                $this->_doing_it_wrong(__FUNCTION__,sprintf($this->__( 'Invalid shortcode name: %1$s. Do not use spaces or reserved characters: %2$s' ),
                        $tag,'& / < > [ ] ='),'0.0.1');/* translators: 1: Shortcode name, 2: Space-separated list of reserved characters. */
                return;
            }
            $this->tp_shortcode_tags[ $tag ] = $callback;
        }//63
        /**
         * @description Removes hook for shortcode.
         * @param $tag
         */
        protected function _remove_shortcode( $tag ):void{
            unset( $this->tp_shortcode_tags[ $tag ] );
        }//101
        /**
         * @description Clear all short-codes.
         */
        protected function _remove_all_shortcodes():void{
            $this->tp_shortcode_tags = [];
        }//118
        /**
         * @description Whether a registered shortcode exists named $tag
         * @param $tag
         * @return bool
         */
        protected function _shortcode_exists( $tag ):bool{
            return array_key_exists( $tag, $this->tp_shortcode_tags );
        }//134
        /**
         * @description Whether the passed content contains the specified shortcode
         * @param $content
         * @param $tag
         * @return bool
         */
        protected function _has_shortcode( $content, $tag ):bool{
            if ( false === strpos( $content, '[' ) ) return false;
            if ( $this->_shortcode_exists( $tag ) ) {
                preg_match_all( '/' . $this->_get_shortcode_regex() . '/', $content, $matches, PREG_SET_ORDER );
                if ( empty( $matches ) ) return false;
                foreach ( $matches as $shortcode ) {
                    if ( $tag === $shortcode[2] ) return true;
                    elseif ( ! empty( $shortcode[5] ) && $this->_has_shortcode( $shortcode[5], $tag ) )
                        return true;
                }
            }
            return false;
        }//150
        /**
         * @description Search content for shortcodes and filter shortcodes through their hooks.
         * @param $content
         * @param bool $ignore_html
         * @return mixed
         */
        protected function _apply_shortcodes( $content, $ignore_html = false ){
            return $this->_do_shortcode( $content, $ignore_html );
        }//186
        /**
         * @description Search content for shortcodes and filter shortcodes through their hooks.
         * @param $content
         * @param bool $ignore_html
         * @return mixed|string
         */
        protected function _do_shortcode( $content, $ignore_html = false ){
            if ( false === strpos( $content, '[' ) ) return $content;
            if ( empty( $this->tp_shortcode_tags ) || ! is_array( $this->tp_shortcode_tags ) ) return $content;
            preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
            $tagnames = array_intersect( array_keys( $this->tp_shortcode_tags ), $matches[1] );
            if ( empty( $tagnames ) ) return $content;
            $content = $this->_do_shortcodes_in_html_tags( $content, $ignore_html, $tagnames );
            $pattern = $this->_get_shortcode_regex( $tagnames );
            $content = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $content );
            $content = $this->_un_escape_invalid_shortcodes( $content );
            return $content;
        }//206
        /**
         * @description Retrieve the shortcode regular expression for searching.
         * @param null $tagnames
         * @return string
         */
        protected function _get_shortcode_regex( $tagnames = null ):string{
            if ( empty( $tagnames ) ) $tagnames = array_keys( $this->tp_shortcode_tags );
            $tag_regexp = implode( '|', array_map( 'preg_quote', $tagnames ) );
            return '\\['                             // Opening bracket.
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]].
            . "($tag_regexp)"                     // 2: Shortcode name.
            . '(?![\\w-])'                       // Not followed by word character or hyphen.
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag.
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash.
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket.
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash.
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Self closing tag...
            .     '\\]'                          // ...and closing bracket.
            . '|'
            .     '\\]'                          // Closing bracket.
            .     '(?:'
            .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags.
            .             '[^\\[]*+'             // Not an opening bracket.
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag.
            .                 '[^\\[]*+'         // Not an opening bracket.
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Closing shortcode tag.
            .     ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]].
            // phpcs:enable
        }//259
        /**
         * @description Regular Expression callable for do_shortcode() for calling shortcode hook.
         * @param $m
         * @return string
         */
        protected function _do_shortcode_tag( $m ):string{
            if ( '[' === $m[1] && ']' === $m[6] ) return substr( $m[0], 1, -1 );
            $tag  = $m[2];
            $attr = $this->_shortcode_parse_atts( $m[3] );
            if ( ! is_callable( $this->tp_shortcode_tags[ $tag ] ) ) {
                $this->_doing_it_wrong(__FUNCTION__, sprintf( $this->__( 'Attempting to parse a shortcode without a valid callback: %s' ), $tag ),
                    '0.0.1');/* translators: %s: Shortcode tag. */
                return $m[0];
            }
            $return = $this->_apply_filters( 'pre_do_shortcode_tag', false, $tag, $attr, $m );
            if ( false !== $return ) return $return;
            $content = isset( $m[5] ) ?: null;
            $output = $m[1] . call_user_func( $this->tp_shortcode_tags[ $tag ], $attr, $content, $tag ) . $m[6];
            return $this->_apply_filters( 'do_shortcode_tag', $output, $tag, $attr, $m );
        }//315
        /**
         * @description Search only inside HTML elements for shortcodes and process them.
         * @param $content
         * @param $ignore_html
         * @param $tagnames
         * @return string
         */
        protected function _do_shortcodes_in_html_tags( $content, $ignore_html, $tagnames ):string{
            $trans   = ['&#91;' => '&#091;','&#93;' => '&#093;',];
            $content = strtr( $content, $trans );
            $trans   = ['[' => '&#91;',']' => '&#93;',];
            $pattern = $this->_get_shortcode_regex( $tagnames );
            $text_arr = $this->_tp_html_split( $content );
            foreach ( $text_arr as &$element ) {
                if ( '' === $element || '<' !== $element[0] )  continue;
                $no_open  = false === strpos( $element, '[' );
                $no_close = false === strpos( $element, ']' );
                if ( $no_open || $no_close ) {
                    if ( $no_open xor $no_close ) $element = strtr( $element, $trans );
                    continue;
                }
                if ( $ignore_html || strpos($element, '<!--') === 0 || strpos($element, '<![CDATA[') === 0) {
                    $element = strtr( $element, $trans );
                    continue;
                }
                $attributes = $this->_tp_kses_attr_parse( $element );
                if ( false === $attributes ) {
                    if ( 1 === preg_match( '%^<\s*\[\[?[^\[\]]+\]%', $element ) )
                        $element = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $element );
                    $element = strtr( $element, $trans );
                    continue;
                }
                $front   = array_shift( $attributes );
                $back    = array_pop( $attributes );
                $matches = array();
                preg_match( '%[a-zA-Z0-9]+%', $front, $matches );
                $el_name = $matches[0];
                foreach ( $attributes as &$attr ) {
                    $open  = strpos( $attr, '[' );
                    $close = strpos( $attr, ']' );
                    if ( false === $open || false === $close )
                        continue;
                    $double = strpos( $attr, '"' );
                    $single = strpos( $attr, "'" );
                    if ( ( false === $single || $open < $single ) && ( false === $double || $open < $double ) ) {
                        $attr = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $attr );
                    } else {
                        $count    = 0;
                        $new_attr = preg_replace_callback( "/$pattern/", 'do_shortcode_tag', $attr, -1, $count );
                        if ( $count > 0 ) {
                            $new_attr = $this->_tp_kses_one_attr( $new_attr, $el_name );
                            if ( '' !== trim( $new_attr ) ) $attr = $new_attr;
                        }
                    }
                }
                unset($attr);
                $element = $front . implode( '', $attributes ) . $back;
                $element = strtr( $element, $trans );
            }
            unset($element);
            $content = implode( '', $text_arr );
            return $content;
        }//386
    }
}else die;