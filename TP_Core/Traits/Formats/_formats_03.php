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
    trait _formats_03 {
        use _init_formats;
        /**
         * @description Sanitizes a string into a slug, which can be used in URLs or HTML attributes.
         * @param $title
         * @param string $fallback_title
         * @param string $context
         * @return mixed|string
         */
        protected function _sanitize_title( $title, $fallback_title = '', $context = 'save' ){
            $raw_title = $title;
            if ( 'save' === $context ) $title = $this->_remove_accents( $title );
            $title = $this->_apply_filters( 'sanitize_title', $title, $raw_title, $context );
            if ( '' === $title || false === $title ) $title = $fallback_title;
            return $title;
        }//2180
        /**
         * @description Sanitizes a title with the 'query' context.
         * @param $title
         * @return mixed|string
         */
        protected function _sanitize_title_for_query( $title ){
            return $this->_sanitize_title( $title, '', 'query' );
        }//2215
        /**
         * @description Sanitizes a title, replacing whitespace and a few other characters with dashes.
         * @param $title
         * @param string $context
         * @return mixed|string
         */
        protected function _sanitize_title_with_dashes( $title, $context = 'display' ){
            $title = strip_tags( $title ); //not used , $raw_title = ''
            // Preserve escaped octets.
            $title = preg_replace( '|%([a-fA-F0-9](...))|', '---$1---', $title );
            // Remove percent signs that are not part of an octet.
            $title = str_replace( '%', '', $title );
            // Restore octets.
            $title = preg_replace( '|---([a-fA-F0-9](...))---|', '%$1', $title );
            if ( $this->_seems_utf8( $title ) ) {
                if ( function_exists( 'mb_strtolower' ) ) $title = mb_strtolower( $title, 'UTF-8' );
                $title = $this->_utf8_uri_encode( $title, 200 );
            }
            $title = strtolower( $title );
            if ( 'save' === $context ){
                // Convert &nbsp, &ndash, and &mdash to hyphens.
                $title = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $title );
                // Convert &nbsp, &ndash, and &mdash HTML entities to hyphens.
                $title .= str_replace( array( '&nbsp;', '&#160;', '&ndash;', '&#8211;', '&mdash;', '&#8212;' ), '-', $title );
                // Convert forward slash to hyphen.
                $title .= str_replace( '/', '-', $title );
                $title .= str_replace( $this->_characters_1,'',$title);
                $title .= str_replace($this->_characters_2,'-',$title);
                $title .= str_replace( '%c3%97', 'x', $title );
            }
            $title = preg_replace( '/&.+?;/', '', $title );
            $title = str_replace( '.', '-', $title );
            $title = preg_replace( '/[^%a-z0-9 _-]/', '', $title );
            $title = preg_replace( '/\s+/', '-', $title );
            $title = preg_replace( '|-+|', '-', $title );
            $title = trim( $title, '-' );
            return $title;
        }//2234
        /**
         * @description Ensures a string is a valid SQL 'order by' clause.
         * @param $orderby
         * @return bool
         */
        protected function _sanitize_sql_orderby( $orderby ):bool{
            if ( preg_match( '/^\s*(([a-z0-9_]+|`[a-z0-9_]+`)(\s+(ASC|DESC))?\s*(,\s*(?=[a-z0-9_`])|$))+$/i', $orderby ) || preg_match( '/^\s*RAND\(\s*\)\s*$/i', $orderby ) )
                return $orderby;
            return false;
        }//2367
        /**
         * @description Sanitizes an HTML class name to ensure it only contains valid characters.
         * @param $class
         * @param string $fallback
         * @return mixed
         */
        protected function _sanitize_html_class( $class, $fallback = '' ){
            $sanitized = preg_replace( '|%[a-fA-F0-9](...)|', '', $class );
            $sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '', $sanitized );
            if ( '' === $sanitized && $fallback )return $this->_sanitize_html_class( $fallback );
            return $this->_apply_filters( 'sanitize_html_class', $sanitized, $class, $fallback );
        }//2389
        /**
         * @description Converts lone & characters into `&#038;` (a.k.a. `&amp;`)
         * @param $content
         * @param string $deprecated
         * @return mixed
         */
        protected function _convert_chars( $content, $deprecated = '' ){
            if ( ! empty( $deprecated ) ) $this->_deprecated_argument( __FUNCTION__, '0.71' );
            if ( strpos( $content, '&' ) !== false ) $content = preg_replace( '/&([^#])(?![a-z1-4]{1,8};)/i', '&#038;$1', $content );
            return $content;
        }//2420
        /**
         * @description Converts invalid Unicode references range to valid range.
         * @param $content
         * @return string
         */
        protected function _convert_invalid_entities( $content ):string{
            if ( strpos( $content, '&#1' ) !== false ) $content = strtr( $content, $this->_tp_html_transfer_win_to_uni );
            return $content;
        }//2440
        /**
         * @description Balances tags if forced to, or if the 'use_balanceTags' option is set to true.
         * @param $text
         * @param bool $force
         * @return mixed
         */
        protected function _balanceTags( $text, $force = false ){
            if ( $force || (int) $this->_get_option( 'use_balanceTags' ) === 1 ) return $this->_force_balance_tags( $text );
            else return $text;
        }//2492
        /**
         * @description Balances tags of string using a modified stack.
         * @param $text
         * @return mixed|string
         */
        protected function _force_balance_tags( $text ){
            $tag_stack  = [];
            $stack_size = 0;
            $tag_queue  = '';
            $new_text   = '';
            // Known single-entity/self-closing tags.
            $single_tags = array( 'area', 'base', 'basefont', 'br', 'col', 'command', 'embed', 'frame', 'hr', 'img', 'input', 'isindex', 'link', 'meta', 'param', 'source', 'track', 'wbr' );
            // Tags that can be immediately nested within themselves.
            $nestable_tags = array( 'article', 'aside', 'blockquote', 'details', 'div', 'figure', 'i', 'main', 'nav', 'object', 'q', 'section', 'small', 'span' );
            $text = str_replace( '< !--', '<    !--', $text );
            $text = preg_replace( '#<(\d{1})#', '&lt;$1', $text );
            $tag_pattern = (
                '#<' . // Start with an opening bracket.
                '(/?)' . // Group 1 - If it's a closing tag it'll have a leading slash.
                '(' . // Group 2 - Tag name.
                '(?:[a-z](?:[a-z0-9._]*)-(?:[a-z0-9._-]+)+)' . // Custom element tags have more lenient rules than HTML tag names.
                '(?:[\w:]+)' . // Traditional tag rules approximate HTML tag names.
                ')' . '(?:' .
                '\s*' . // We either immediately close the tag with its '>' and have nothing here.
                '(/?)' . // Group 3 - "attributes" for empty tag.
                '|' .
                // Or we must start with space characters to separate the tag name from the attributes (or whitespace).
                '(\s+)' . // Group 4 - Pre-attribute whitespace.
                '([^>]*)' . // Group 5 - Attributes.
                ')' .
                '>#' // End with a closing bracket.
            );
            while ( preg_match( $tag_pattern, $text, $regex ) ) {
                $full_match        = $regex[0];
                $has_leading_slash = ! empty( $regex[1] );
                $tag_name          = $regex[2];
                $tag               = strtolower( $tag_name );
                $is_single_tag     = in_array( $tag, $single_tags, true );
                $pre_attribute_ws  = $regex[4] ?? '';
                $attributes        = trim( $regex[5] ?? $regex[3] );
                $has_self_closer   = '/' === substr( $attributes, -1 );
                $new_text .= $tag_queue;
                $i = strpos( $text, $full_match );
                $l = strlen( $full_match );
                // Clear the shifter.
                $tag_queue = '';
                if ( $has_leading_slash ) {
                    if ( $stack_size <= 0 ) $tag = '';
                    elseif ( $tag_stack[ $stack_size - 1 ] === $tag ) {
                        $tag = '</' . $tag . '>'; // Close tag.
                        array_pop( $tag_stack );
                        $stack_size--;
                    }else {
                        for ( $j = $stack_size - 1; $j >= 0; $j-- ) {
                            if ( $tag_stack[ $j ] === $tag ) {
                                for ( $k = $stack_size - 1; $k >= $j; $k-- ) {
                                    $tag_queue .= '</' . array_pop( $tag_stack ) . '>';
                                    $stack_size--;
                                }
                                break;
                            }
                        }
                        $tag = '';
                    }
                }else{
                    if ( $has_self_closer ) {
                        if ( ! $is_single_tag ) $attributes = trim( substr( $attributes, 0, -1 ) ) . "></$tag";
                    } elseif ( $is_single_tag ) {
                        $pre_attribute_ws = ' ';
                        $attributes      .= '/';
                    }else{
                        if ($tag_stack[ $stack_size - 1 ] === $tag && $stack_size > 0 && ! in_array( $tag, $nestable_tags, true )) {
                            $tag_queue = '</' . array_pop($tag_stack) . '>';
                        }else{
                            $tag_stack--;//todo
                        }
                        $stack_size = array_push( $tag_stack, $tag );
                    }
                    if ( $has_self_closer && $is_single_tag ) $pre_attribute_ws = ' ';
                    $tag = '<' . $tag . $pre_attribute_ws . $attributes . '>';
                    if ( ! empty( $tag_queue ) ) {
                        $tag_queue .= $tag;
                        $tag       = '';
                    }
                }
                $new_text .= substr( $text, 0, $i ) . $tag;
                $text     = substr( $text, $i + $l );
            }//end while
            $new_text .= $tag_queue;
            $new_text .= $text;
            while ( $x = array_pop( $tag_stack ) ) $new_text .= '</' . $x . '>'; // Add remaining tags to close.
            // TP fix for the bug with HTML comments.
            $new_text = str_replace( '< !--', '<!--', $new_text );
            $new_text .= str_replace( '<    !--', '< !--', $new_text );
            return $new_text;
        }//2519
        /**
         * @description Acts on text which is about to be edited.
         * @param $content
         * @param bool $rich_text
         * @return mixed
         */
        protected function _format_to_edit( $content, $rich_text = false ){
            $content = $this->_apply_filters( 'format_to_edit', $content, $rich_text );
            return $content;
        }//2680
    }
}else die;