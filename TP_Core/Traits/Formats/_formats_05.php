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
    trait _formats_05 {
        use _init_formats;
        /**
         * @description Callback to convert URI match to HTML A element.
         * @param $matches
         * @return string
         */
        protected function _make_url_clickable_cb( $matches ):string{
            $url = $matches[2];
            if ( ')' === $matches[3] && strpos( $url, '(' ) ) {
                $url   .= $matches[3];
                $suffix = '';
            }else $suffix = $matches[3];
            while ( substr_count( $url, '(' ) < substr_count( $url, ')' ) ) {
                $suffix = strrchr( $url, ')' ) . $suffix;
                $url    = substr( $url, 0, strrpos( $url, ')' ) );
            }
            $url = $this->_esc_url( $url );
            if ( empty( $url ) ) $rel = 'nofollow ugc';
            else $rel = 'nofollow';
            $rel = $this->_apply_filters( 'make_clickable_rel', $rel, $url );
            $rel = $this->_esc_attr( $rel );
            return $matches[1] . "<a href='{$url}' rel='{$rel}'>$url</a>" . $suffix;
        }//2875
        /**
         * @description Callback to convert URL match to HTML A element.
         * @param $matches
         * @return string
         */
        protected function _make_web_ftp_clickable_cb( $matches ):string{
            $ret  = '';
            $destination = $matches[2];
            $destination = 'http://' . $destination;

            // Removed trailing [.,;:)] from URL.
            $last_char = substr( $destination, -1 );
            if ( in_array( $last_char, array( '.', ',', ';', ':', ')' ), true ) === true ) {
                $ret  = $last_char;
                $destination = substr( $destination, 0, -1);
            }
            $destination = $this->_esc_url( $destination );
            if ( empty( $destination ) ) return $matches[0];
            if ( 'comment_text' === $this->_current_filter() ) $rel = 'nofollow ugc';
            else $rel = 'nofollow';
            $rel = $this->_apply_filters( 'make_clickable_rel', $rel, $destination );
            $rel = $this->_esc_attr( $rel );
            return $matches[1] . "<a href=\"$destination\" rel=\"$rel\">$destination</a>$ret";
        }//2929
        /**
         * @description Callback to convert email address match to HTML A element.
         * @param $matches
         * @return string
         */
        protected function _make_email_clickable_cb( $matches ):string{
            $email = $matches[2] . '@' . $matches[3];
            return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
        }//2970
        /**
         * @description Convert plaintext URI to HTML links.
         * @param $text
         * @return mixed
         */
        protected function _make_clickable( $text ){
            $r               = '';
            $text_arr         = preg_split( '/(<[^<>]+>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE );
            $nested_code_pre = 0;
            foreach ( $text_arr as $piece ) {
                if ( preg_match( '|^<code[\s>]|i', $piece ) || preg_match( '|^<pre[\s>]|i', $piece ) || preg_match( '|^<script[\s>]|i', $piece ) || preg_match( '|^<style[\s>]|i', $piece ) )
                    $nested_code_pre++;
                elseif ( $nested_code_pre && ( '</code>' === strtolower( $piece ) || '</pre>' === strtolower( $piece ) || '</script>' === strtolower( $piece ) || '</style>' === strtolower( $piece ) ) )
                    $nested_code_pre--;
                if ( $nested_code_pre || empty( $piece ) || ( '<' === $piece[0] && ! preg_match( '|^<\s*[\w]{1,20}+://|', $piece ) ) ) {
                    $r .= $piece;
                    continue;
                }
                if ( 10000 < strlen( $piece ) ) {
                    foreach ( $this->_split_str_by_whitespace( $piece, 2100 ) as $chunk ) {
                        if ( 2101 < strlen( $chunk ) )  $r .= $chunk;
                        else $r .= $this->_make_clickable( $chunk );
                    }
                }else{
                    $ret = " $piece ";
                    //todo original $url_clickable = '~([\\s(<.,;:!?])( [\\w]{1,20}+://(?=\S{1,2000}\s)[\\w\\x80-\\xff#%\\~/@\\[\\]*(+=&$-]*+(?:[\\' .,;:!?)][\\w\\x80-\\xff#%\\~/@\\[\\]*(+=&$-]++)*)(\)?)~xS';
                    $url_clickable = "~([\\s(<.,;:!?])( [\\w]{1,20}+://(?=\S{1,2000}\s)[\\w\\x80-\\xff#%\\~/@\\[\\]*(+=&$-]*+(?:[\\' .,;:!?)][\\w\\x80-\\xff#%\\~/@\\[\\]*(+=&$-]++)*)(\)?)~xS";
                    $ret = preg_replace_callback( $url_clickable, '_make_url_clickable_cb', $ret );
                    $ret = preg_replace_callback( '#.([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]+)#is', '_make_web_ftp_clickable_cb', $ret );
                    $ret = preg_replace_callback( '#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb', $ret );
                    $ret = substr( $ret, 1, -1 );
                    $r  .= $ret;
                }
            }
            return preg_replace( '#(<a([ \r\n\t]+[^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i', '$1$3</a>', $r );
        }//2986
        /**
         * @description Breaks a string into chunks by splitting at whitespace characters.
         * @param $string
         * @param $goal
         * @return array
         */
        protected function _split_str_by_whitespace( $string, $goal ):array{
            $chunks = [];
            $string_null_space = strtr( $string, "\r\n\t\v\f ", "\000\000\000\000\000\000" );
            while ( $goal < strlen( $string_null_space ) ) {
                $pos = strrpos( substr( $string_null_space, 0, $goal + 1 ), "\000" );
                if ( false === $pos ) {
                    $pos = strpos( $string_null_space, "\000", $goal + 1 );
                    if ( false === $pos ) break;
                }
                $chunks[]         = substr( $string, 0, $pos + 1 );
                $string           = substr( $string, $pos + 1 );
                $string_null_space = substr( $string_null_space, $pos + 1 );
            }
            if ( $string ) $chunks[] = $string;
            return $chunks;
        }//3075
        /**
         * @description Callback to add a rel attribute to HTML A element.
         * @param $matches
         * @param $rel
         * @return string
         */
        protected function _tp_rel_callback( $matches, $rel ):string{
            $text = $matches[1];
            $atts = $this->_tp_kses_hair( $matches[1], $this->_tp_allowed_protocols() );
            if (!empty($atts['href']) && in_array(strtolower($this->_tp_parse_url($atts['href']['value'], PHP_URL_SCHEME)), array('http', 'https'), true) && strtolower($this->_tp_parse_url($atts['href']['value'], PHP_URL_HOST)) === strtolower($this->_tp_parse_url($this->_home_url(), PHP_URL_HOST))) return "<a $text>";
            if ( ! empty( $atts['rel'] ) ) {
                $parts     = array_map( 'trim', explode( ' ', $atts['rel']['value'] ) );
                $rel_array = array_map( 'trim', explode( ' ', $rel ) );
                $parts     = array_unique( array_merge( $parts, $rel_array ) );
                $rel       = implode( ' ', $parts );
                unset( $atts['rel'] );

                $html = '';
                foreach ( $atts as $name => $value ) {
                    if ( isset( $value['vless'] ) && 'y' === $value['vless'] )  $html .= $name . ' ';
                    else $html .= "{$name}=\"" . $this->_esc_attr( $value['value'] ) . '" ';
                }
                $text = trim( $html );
            }
            return "<a $text rel=\"" . $this->_esc_attr( $rel ) . '">';
        }//3113
        /**
         * @description Adds `rel="nofollow"` string to all HTML A elements in content.
         * @param $text
         * @return mixed
         */
        protected function _tp_rel_nofollow( $text ){
            $text = stripslashes( $text );
            $text = preg_replace_callback('|<a (.+?)>|i',static function( $matches ){return (new self)->_tp_rel_callback( $matches, 'nofollow' );},$text);
            return $this->_tp_slash( $text );
        }//3153
        /**
         * @description Adds `rel="nofollow ugc"` string to all HTML A elements in content.
         * @param $text
         * @return mixed
         */
        protected function _tp_rel_ugc( $text ){
            $text = stripslashes( $text );
            $text = preg_replace_callback('|<a (.+?)>|i', static function( $matches ) { return (new self)->_tp_rel_callback( $matches, 'nofollow ugc' ); }, $text );
            return $this->_tp_slash( $text );
        }//3187
        /**
         * @description Adds `rel="no_opener"` to all HTML A elements that have a target.
         * @param $text
         * @return string
         */
        protected function _tp_targeted_link_rel( $text ):string{
            if ( stripos( $text, 'target' ) === false || stripos( $text, '<a ' ) === false || $this->_is_serialized( $text ) )
                return $text;
            $script_and_style_regex = '/<(script|style).*?<\/\\1>/si';
            preg_match_all( $script_and_style_regex, $text, $matches );
            $extra_parts = $matches[0];
            $html_parts  = preg_split( $script_and_style_regex, $text );
            foreach ( $html_parts as &$part ) $part = preg_replace_callback( '|<a\s([^>]*target\s*=[^>]*)>|i', '__tp_targeted_link_rel_callback', $part );
            unset($part);
            $text = '';
            for ($i = 0, $iMax = count($html_parts); $i < $iMax; $i++ ) {
                $text .= $html_parts[ $i ];
                if ( isset( $extra_parts[ $i ] ) ) $text .= $extra_parts[ $i ];
            }
            return $text;
        }//3209
        /**
         * @description Callback to add `rel="no_opener"` string to HTML A element.
         * @param $matches
         * @return string
         */
        protected function _tp_targeted_link_rel_callback( $matches ):string{
            $link_html          = $matches[1];
            $original_link_html = $link_html;
            $is_escaped = ! preg_match( '/(^|[^\\\\])[\'"]/', $link_html );
            if ( $is_escaped ) $link_html = preg_replace( '/\\\\([\'"])/', '$1', $link_html );
            $atts = $this->_tp_kses_hair( $link_html, $this->_tp_allowed_protocols() );
            $rel = $this->_apply_filters( '__tp_targeted_link_rel', 'no_opener', $link_html );
            if ( ! $rel || ! isset( $atts['target'] ) ) return "<a $original_link_html>";
            if ( isset( $atts['rel'] ) ) {
                $all_parts = preg_split( '/\s/', "{$atts['rel']['value']} $rel", -1, PREG_SPLIT_NO_EMPTY );
                $rel       = implode( ' ', array_unique( $all_parts ) );
            }
            $atts['rel']['whole'] = 'rel="' . $this->_esc_attr( $rel ) . '"';
            $link_html            = implode( ' ', array_column( $atts, 'whole' ) );
            if ( $is_escaped ) $link_html = preg_replace( '/[\'"]/', '\\\\$0', $link_html );
            return "<a $link_html>";
        }//3247
    }
}else die;