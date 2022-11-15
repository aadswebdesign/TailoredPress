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
    trait _formats_01 {
        use _init_formats;
        /**
         * @description Replaces common plain text characters with formatted entities.
         * @param $text
         * @param bool $reset
         * @return string
         */
        protected function _tp_texturize( $text, $reset = false ):string{
            static $static_characters        = null,
            $static_replacements             = null,
            $dynamic_characters              = null,
            $dynamic_replacements            = null,
            $default_no_texturize_tags       = null,
            $default_no_texturize_shortcodes = null,
            $run_texturize                   = true,
            $apos                            = null,
            $prime                           = null,
            $double_prime                    = null,
            $opening_quote                   = null,
            $closing_quote                   = null,
            $opening_single_quote            = null,
            $closing_single_quote            = null,
            $open_q_flag                     = '<!--oq-->',
            $open_sq_flag                    = '<!--osq-->',
            $apos_flag                       = '<!--apos-->';
            if ( empty( $text ) || false === $run_texturize ) return $text;
            if ( $reset || ! isset( $static_characters ) ){
                $run_texturize = $this->_apply_filters( 'run_tp_texturize', $run_texturize );
                if ( false === $run_texturize ) return $text;
                $opening_quote = $this->_x( '&#8220;', 'opening curly double quote' );
                $closing_quote = $this->_x( '&#8221;', 'closing curly double quote' );
                $apos = $this->_x( '&#8217;', 'apostrophe' );
                $prime = $this->_x( '&#8242;', 'prime' );
                $double_prime = $this->_x( '&#8243;', 'double prime' );
                $opening_single_quote = $this->_x( '&#8216;', 'opening curly single quote' );
                $closing_single_quote = $this->_x( '&#8217;', 'closing curly single quote' );
                $en_dash = $this->_x( '&#8211;', 'en dash' );
                $em_dash = $this->_x( '&#8212;', 'em dash' );
                $default_no_texturize_tags       = ['pre', 'code', 'kbd', 'style', 'script', 'tt'];
                $default_no_texturize_shortcodes = ['code'];
                if ( isset( $this->__tp_cockney_replace ) ) {
                    $cockney        = array_keys( $this->__tp_cockney_replace );
                    $cockney_replace = array_values( $this->__tp_cockney_replace );
                }else{
                    $cockney = explode(',', $this->_x("'tain't,'twere,'twas,'tis,'twill,'til,'bout,'nuff,'round,'cause,'em",
                        'Comma-separated list of words to texturize in your language'));
                    $cockney_replace = explode(',',$this->_x('&#8220;tain&#8217;t,&#8217;twere,&#8217;twas,&#8217;tis,&#8217;twill,&#8217;til,&#8217;bout,&#8217;nuff,&#8217;round,&#8217;cause,&#8217;em',
                        'Comma-separated list of replacement words in your language'));
                }
                $static_characters = array_merge( array( '...', '``', '\'\'', ' (tm)' ), $cockney );
                $static_replacements = array_merge( array( '&#8230;', $opening_quote, $closing_quote, ' &#8482;' ), $cockney_replace );
                $dynamic_characters = ['apos' => [],'quote' => [],'dash' => [],];
                $dynamic_replacements = ['apos' => [],'quote' => [],'dash' => []];
                $dynamic              = [];
                $spaces               = $this->_tp_spaces_regexp();
                if ( "'" !== $apos || "'" !== $closing_single_quote )
                    $dynamic[ '/\'(\d\d)\'(?=\Z|[.,:;!?)}\-\]]|&gt;|' . $spaces . ')/' ] = $apos_flag . '$1' . $closing_single_quote;
                if ( "'" !== $apos || '"' !== $closing_quote )
                    $dynamic[ '/\'(\d\d)"(?=\Z|[.,:;!?)}\-\]]|&gt;|' . $spaces . ')/' ] = $apos_flag . '$1' . $closing_quote;
                if ( "'" !== $apos )  $dynamic['/\'(?=\d\d(?:\Z|(?![%\d]|[.,]\d)))/'] = $apos_flag;
                if ( "'" !== $opening_single_quote && "'" !== $closing_single_quote )
                    $dynamic[ '/(?<=\A|' . $spaces . ')\'(\d[.,\d]*)\'/' ] = $open_sq_flag . '$1' . $closing_single_quote;
                if ( "'" !== $opening_single_quote )
                    $dynamic[ '/(?<=\A|[([{"\-]|&lt;|' . $spaces . ')\'/' ] = $open_sq_flag;
                if ( "'" !== $apos ) $dynamic[ '/(?<!' . $spaces . ')\'(?!\Z|[.,:;!?"\'(){}[\]\-]|&[lg]t;|' . $spaces . ')/' ] = $apos_flag;
                $dynamic_characters['apos']   = array_keys( $dynamic );
                $dynamic_replacements['apos'] = array_values( $dynamic );
                $dynamic                      = [];
                if ( '"' !== $opening_quote && '"' !== $closing_quote )
                    $dynamic[ '/(?<=\A|' . $spaces . ')"(\d[.,\d]*)"/' ] = $open_q_flag . '$1' . $closing_quote;
                if ( '"' !== $opening_quote )
                    $dynamic[ '/(?<=\A|[([{\-]|&lt;|' . $spaces . ')"(?!' . $spaces . ')/' ] = $open_q_flag;
                $dynamic_characters['quote']   = array_keys( $dynamic );
                $dynamic_replacements['quote'] = array_values( $dynamic );
                $dynamic                       = [];
                $dynamic['/---/'] = $em_dash;
                $dynamic[ '/(?<=^|' . $spaces . ')--(?=$|' . $spaces . ')/' ] = $em_dash;
                $dynamic['/(?<!xn)--/']                                       = $en_dash;
                $dynamic[ '/(?<=^|' . $spaces . ')-(?=$|' . $spaces . ')/' ]  = $en_dash;
                $dynamic_characters['dash']   = array_keys( $dynamic );
                $dynamic_replacements['dash'] = array_values( $dynamic );
            }
            $no_texturize_tags = $this->_apply_filters('no_texturize_tags', $default_no_texturize_tags);
            $no_texturize_shortcodes = $this->_apply_filters('no_texturize_short_codes', $default_no_texturize_shortcodes);
            $no_texturize_tags_stack = [];
            $no_texturize_shortcodes_stack = [];
            preg_match_all( '@\[/?([^<>&/\[\]\x00-\x20=]++)@', $text, $matches );
            $tag_names         = array_intersect( array_keys( $this->_tp_shortcode_tags ), $matches[1] );
            $found_shortcodes = ! empty( $tag_names );
            $shortcode_regex  = $found_shortcodes ? $this->_get_tp_texturize_shortcode_regex( $tag_names ) : '';
            $regex            = $this->_get_tp_texturize_split_regex( $shortcode_regex );
            $text_arr = preg_split( $regex, $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
            foreach ( $text_arr as &$curl ) {
                $first = $curl[0];
                if ( '<' === $first ) {
                    if (strpos($curl, '<!--') === 0) continue;
                    else{
                        $curl = preg_replace( '/&(?!#(?:\d+|x[a-f0-9]+);|[a-z1-4]{1,8};)/i', '&#038;', $curl );
                        $this->_tp_texturize_push_pop_element( $curl, $no_texturize_tags_stack, $no_texturize_tags );
                    }
                }elseif ( '' === trim( $curl ) ) continue;
                elseif ( '[' === $first && $found_shortcodes && 1 === preg_match( '/^' . $shortcode_regex . '$/', $curl ) ) {
                    if ( strpos($curl, '[[') !== 0 && ']]' !== substr( $curl, -2 ) )
                        $this->_tp_texturize_push_pop_element($curl, $no_texturize_shortcodes_stack, $no_texturize_shortcodes);
                    else continue;
                }elseif ( empty( $no_texturize_shortcodes_stack ) && empty( $no_texturize_tags_stack ) ) {
                    $curl = str_replace( $static_characters, $static_replacements, $curl );
                    if ( false !== strpos( $curl, "'" ) ) {
                        $curl = preg_replace( $dynamic_characters['apos'], $dynamic_replacements['apos'], $curl );
                        $curl = $this->_tp_texturize_primes( $curl, "'", $prime, $open_sq_flag, $closing_single_quote );
                        $curl = str_replace(array($apos_flag, $open_sq_flag), array($apos, $opening_single_quote), $curl);
                    }
                    if ( false !== strpos( $curl, '"' ) ) {
                        $curl = preg_replace( $dynamic_characters['quote'], $dynamic_replacements['quote'], $curl );
                        $curl = $this->_tp_texturize_primes( $curl, '"', $double_prime, $open_q_flag, $closing_quote );
                        $curl = str_replace( $open_q_flag, $opening_quote, $curl );
                    }
                    if ( 1 === preg_match( '/(?<=\d)x\d/', $curl ) )
                        $curl = preg_replace( '/\b(\d(?(?<=0)[\d\.,]+|[\d\.,]*))x(\d[\d\.,]*)\b/', '$1&#215;$2', $curl );
                    $curl = preg_replace( '/&(?!#(?:\d+|x[a-f0-9]+);|[a-z1-4]{1,8};)/i', '&#038;', $curl );
                }
            }
            return implode( '', $text_arr );
        }//37
        /**
         * @description Implements a logic tree to determine whether or not "7'." represents seven feet,
         * @description. then converts the special char into either a prime char or a closing quote char.
         * @param $haystack
         * @param $needle
         * @param $prime
         * @param $open_quote
         * @param $close_quote
         * @return string
         */
        protected function _tp_texturize_primes( $haystack, $needle, $prime, $open_quote, $close_quote ):string{
            $spaces           = $this->_tp_spaces_regexp();
            $flag             = '<!--tp-prime-or-quote-->';
            $quote_pattern    = "/$needle(?=\\Z|[.,:;!?)}\\-\\]]|&gt;|" . $spaces . ')/';
            $prime_pattern    = "/(?<=\\d)$needle/";
            $flag_after_digit = "/(?<=\\d)$flag/";
            $flag_no_digit    = "/(?<!\\d)$flag/";
            $sentences = explode( $open_quote, $haystack );
            foreach ( $sentences as $key => &$sentence ) {
                if ( false === strpos( $sentence, $needle ) ) continue;
                elseif ( 0 !== $key && 0 === substr_count( $sentence, $close_quote ) ){
                    $sentence = preg_replace( $quote_pattern, $flag, $sentence, -1, $count );
                    if ( $count > 1 ) {
                        $sentence = preg_replace( $flag_no_digit, $close_quote, $sentence, -1, $count2 );
                        if ( 0 === $count2 ) {
                            // Try looking for a quote followed by a period.
                            $count2 = substr_count( $sentence, "$flag." );
                            if ( $count2 > 0 )$pos = strrpos( $sentence, "$flag." );
                            else $pos = strrpos( $sentence, $flag );
                            $sentence = substr_replace( $sentence, $close_quote, $pos, strlen( $flag ) );
                        }
                        $sentence = preg_replace( $prime_pattern, $prime, $sentence );
                        $sentence = preg_replace( $flag_after_digit, $prime, $sentence );
                        $sentence = str_replace( $flag, $close_quote, $sentence );
                    }elseif ( 1 === $count ) {
                        // Found only one closing quote candidate, so give it priority over primes.
                        $sentence = str_replace( $flag, $close_quote, $sentence );
                        $sentence = preg_replace( $prime_pattern, $prime, $sentence );
                    }else $sentence = preg_replace( $prime_pattern, $prime, $sentence );
                }else{
                    $sentence = preg_replace( $prime_pattern, $prime, $sentence );
                    $sentence = preg_replace( $quote_pattern, $close_quote, $sentence );
                }
                if ( '"' === $needle && false !== strpos( $sentence, '"' ) )
                    $sentence = str_replace( '"', $close_quote, $sentence );
            }
            return implode( $open_quote, $sentences );





        }//318
        /**
         * @description Search for disabled element tags. Push element to stack on tag open and pop on tag close.
         * @param $text
         * @param $stack
         * @param $disabled_elements
         */
        protected function _tp_texturize_push_pop_element( $text, &$stack, $disabled_elements ):void{
            if ( isset( $text[1] ) && '/' !== $text[1] ) {
                $opening_tag = true;
                $name_offset = 1;
            } elseif ( 0 === count( $stack ) ) return;
            else {
                $opening_tag = false;
                $name_offset = 2;
            }
            $space = strpos( $text, ' ' );
            if ( false === $space )  $space = -1;
            else $space -= $name_offset;
            $tag = substr( $text, $name_offset, $space );
            if ( in_array( $tag, $disabled_elements, true ) ) {
                if ( $opening_tag ) $stack[] = $tag;
                elseif ( end( $stack ) === $tag )array_pop( $stack );
            }
        }//387
        /**
         * @description Replaces double line breaks with paragraph elements.
         * @param $pee
         * @param bool $br
         * @return mixed|string
         */
        protected function _tp_autop( $pee, $br = true ){
            $this->_tags['pre'] = [];
            if ( trim( $pee ) === '' ) return '';
            $pee .= "\n";
            if ( strpos( $pee, '<pre' ) !== false ) {
                $pee_parts = explode( '</pre>', $pee );
                $last_pee  = array_pop( $pee_parts );
                $pee       = '';
                $i         = 0;
                foreach ( $pee_parts as $pee_part ) {
                    $start = strpos( $pee_part, '<pre' );
                    if ( false === $start ) {
                        $pee .= $pee_part;
                        continue;
                    }
                    $tp_pre = 'tp-pre-tag-';
                    $name = "<pre {$tp_pre}$i></pre>";
                    $this->_tags['pre'][ $name ] = substr( $pee_part, $start ) . '</pre>';
                    $pee .= substr( $pee_part, 0, $start ) . $name;
                    $i++;
                }
                $pee .= $last_pee;
            }
            $pee = preg_replace( '|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee );
            $all_blocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
            $pee = preg_replace( '!(<' . $all_blocks . '[\s/>])!', "\n\n$1", $pee );
            $pee = preg_replace( '!(</' . $all_blocks . '>)!', "$1\n\n", $pee );
            $pee = preg_replace( '!(<hr\s*?/?>)!', "$1\n\n", $pee );
            $pee = str_replace( array( "\r\n", "\r" ), "\n", $pee );
            $pee = $this->_tp_replace_in_html_tags( $pee, array( "\n" => ' <!-- tpnl --> ' ) );
            if ( strpos( $pee, '<option' ) !== false ) {
                $pee = preg_replace( '|\s*<option|', '<option', $pee );
                $pee = preg_replace( '|</option>\s*|', '</option>', $pee );
            }
            if ( strpos( $pee, '</object>' ) !== false ) {
                $pee = preg_replace( '|(<object[^>]*>)\s*|', '$1', $pee );
                $pee = preg_replace( '|\s*</object>|', '</object>', $pee );
                $pee = preg_replace( '%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $pee );
            }
            if ( strpos( $pee, '<source' ) !== false || strpos( $pee, '<track' ) !== false ) {
                $pee = preg_replace( '%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $pee );
                $pee = preg_replace( '%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $pee );
                $pee = preg_replace( '%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $pee );
            }
            if ( strpos( $pee, '<figcaption' ) !== false ) {
                $pee = preg_replace( '|\s*(<figcaption[^>]*>)|', '$1', $pee );
                $pee = preg_replace( '|</figcaption>\s*|', '</figcaption>', $pee );
            }
            $pee = preg_replace( "/\n\n+/", "\n\n", $pee );
            $pees = preg_split( '/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY );
            $pee = '';
            foreach ( $pees as $tinkle )$pee .= "<p>{trim($tinkle,\"\n\")}</p>\n";
            $pee = preg_replace( '|<p>\s*</p>|', '', $pee );
            $pee = preg_replace( '!<p>([^<]+)</(div|address|form)>!', '<p>$1</p></$2>', $pee );
            $q_m = '&#63;';
            $pee = preg_replace( '!<p>\s*(</'. $q_m . $all_blocks . '[^>]*>)\s*</p>!', '$1', $pee );
            $pee = preg_replace( '|<p>(<li.+?)</p>|', '$1', $pee );
            $pee = preg_replace( '|<p><blockquote([^>]*)>|i', '<blockquote$1><p>', $pee );
            $pee = str_replace( '</blockquote></p>', '</p></blockquote>', $pee );
            $pee = preg_replace( '!<p>\s*(</'. $q_m . $all_blocks . '[^>]*>)!', '$1', $pee );
            $pee = preg_replace( '!(</?' . $all_blocks . '[^>]*>)\s*</p>!', '$1', $pee );
            if ( $br ) {
                $pee = preg_replace_callback( '/<(script|style|svg).*?<\/\\1>/s', [$this, '__autop_newline_preservation_helper'], $pee );
                $pee = str_replace( array( '<br>', '<br/>' ), '<br />', $pee );
                $pee = preg_replace( '|(?<!<br />)\s*\n|', "<br />\n", $pee );
                $tp_new_line = 'TP-Preserve-Newline';
                $pee = str_replace(($tp_new_line), "\n", $pee );
            }
            $pee = preg_replace( '!(</?' . $all_blocks . '[^>]*>)\s*<br />!', '$1', $pee );
            $pee = preg_replace( '!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee );
            $pee = preg_replace( "|\n</p>$|", '</p>', $pee );
            if ( ! empty( $this->_tags['pre'] ) ) $pee = str_replace( array_keys( $this->_tags['pre'] ), array_values( $this->_tags['pre'] ), $pee );
            if ( false !== strpos( $pee, '<!-- tpnl -->' ) )
                $pee = str_replace( array( ' <!-- tpnl --> ', '<!-- tpnl -->' ), "\n", $pee );
            return $pee;
        }//442
        /**
         * @description Separate HTML elements and comments from the text.
         * @param $input
         * @return array
         */
        protected function _tp_html_split( $input ):array{
            return preg_split( $this->_get_html_split_regex(), $input, -1, PREG_SPLIT_DELIM_CAPTURE );
        }//611
        /**
         * @description Retrieve the regular expression for an HTML element.
         * @return string
         */
        protected function _get_html_split_regex():string{
            if ( ! isset( $this->_regex ) ) {
                $comments =
                    '!'             // Start of comment, after the <.
                    . '(?:'         // Unroll the loop: Consume everything until --> is found.
                    .     '-(?!->)' // Dash not followed by end of comment.
                    .     '[^\-]*+' // Consume non-dashes.
                    . ')*+'         // Loop possessively.
                    . '(?:-->)?';   // End of comment. If not found, match all input.
                $cdata =
                    '!\[CDATA\['    // Start of comment, after the <.
                    . '[^\]]*+'     // Consume non-].
                    . '(?:'         // Unroll the loop: Consume everything until ]]> is found.
                    .     '](?!]>)' // One ] not followed by end of comment.
                    .     '[^\]]*+' // Consume non-].
                    . ')*+'         // Loop possessively.
                    . '(?:]]>)?';   // End of comment. If not found, match all input.
                $escaped =
                    '(?='             // Is the element escaped?
                    .    '!--'
                    . '|'
                    .    '!\[CDATA\['
                    . ')'
                    . '(?(?=!-)'      // If yes, which type?
                    .     $comments
                    . '|'
                    .     $cdata
                    . ')';
                $this->_regex =
                    '/('                // Capture the entire match.
                    .     '<'           // Find start of element.
                    .     '(?'          // Conditional expression follows.
                    .         $escaped  // Find end of escaped element.
                    .     '|'           // ...else...
                    .         '[^>]*>?' // Find end of normal element.
                    .     ')'
                    . ')/';
                // phpcs:enable
            }
            return $this->_regex;
        }//622
        /**
         * @description Retrieve the combined regular expression for HTML and shortcodes.
         * @param string $shortcode_regex
         * @return string
         */
        protected function _get_tp_texturize_split_regex( $shortcode_regex = '' ):string{
            if ( ! isset( $this->_html['regex'] ) ) {
                $comment_regex = '!' . '(?:'.'-(?!->)'.'[^\-]*+'.')*+'.'(?:-->)?';
                // Needs replaced with tp_html_split() per Shortcode API Roadmap.
                $this->_html['regex'] = '<' . '(?(?=!--)' . $comment_regex . '|'.'[^>]*>?'.')';
            }
            if ( empty( $shortcode_regex ) ) $regex = '/(' . $this->_html['regex'] . ')/';
            else $regex = '/(' . $this->_html['regex'] . '|' . $shortcode_regex . ')/';
            return $regex;
        }//682
        /**
         * @description Retrieve the regular expression for shortcodes.
         * @param $tag_names
         * @return string
         */
        protected function _get_tp_texturize_shortcode_regex( $tag_names ):string{
            $tag_regexp = implode( '|', array_map( 'preg_quote', $tag_names ) );
            $tag_regexp = "(?:$tag_regexp)(?=[\\s\\]\\/])";
            $regex = '\['.'[\/\[]?' . $tag_regexp .'(?:'.'[^\[\]<>]+'.'|'.'<[^\[\]>]*>'.')*+'.'\]'.'\]?';
            return $regex;
        }//724
        /**
         * @description Replace characters or phrases within HTML elements only.
         * @param $haystack
         * @param $replace_pairs
         * @return string
         */
        protected function _tp_replace_in_html_tags( $haystack, $replace_pairs ): string{
            $text_arr = $this->_tp_html_split( $haystack );
            $changed = false;
            if ( 1 === count( $replace_pairs ) ) {
                foreach ( $replace_pairs as $needle => $replace ){
                    for ( $i = 1, $c = count( $text_arr ); $i < $c; $i += 2 ) {
                        if ( false !== strpos( $text_arr[ $i ], $needle ) ) {
                            $text_arr[ $i ] = str_replace( $needle, $replace, $text_arr[ $i ] );
                            $changed  = true;
                        }
                    }
                }
            }else{
                $needles = array_keys( $replace_pairs );
                for ( $i = 1, $c = count( $text_arr ); $i < $c; $i += 2 ) {
                    foreach ( $needles as $needle ) {
                        if ( false !== strpos( $text_arr[ $i ], $needle ) ) {
                            $text_arr[ $i ] = strtr( $text_arr[ $i ], $replace_pairs );
                            $changed       = true;
                            break;
                        }
                    }
                }
            }
            if ( $changed ) $haystack = implode( $text_arr );
            return $haystack;
        }//753
        /**
         * @description Newline preservation help function for __tp_autop
         * @param $matches
         * @return mixed
         */
        protected function _autop_newline_preservation_helper( $matches ){
            $tp_new_line = 'TP-Preserve-Newline';
            return str_replace( "\n", $tp_new_line, $matches[0] );
        }//804
    }
}else die;