<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 10:04
 */
namespace TP_Core\Traits\Formats;
if(ABSPATH){
    trait _formats_08 {
        /**
         * @description Performs esc_url() for database or redirect usage.
         * @param $url
         * @param null $protocols
         * @return mixed|string
         */
        protected function _esc_url_raw( $url, $protocols = null ){
            return $this->_esc_url( $url, $protocols, 'db' );
        }//4460
        /**
         * @description Performs esc_url() for database or redirect usage.
         * @param $url
         * @param null $protocols
         * @return mixed|string
         */
        protected function _sanitize_url( $url, $protocols = null ){
            return $this->_esc_url_raw( $url, $protocols );
        }//4480
        /**
         * @description Convert entities, while preserving already-encoded entities.
         * @param $my_html
         * @return mixed
         */
        protected function _html_entities2( $my_html ){
            $translation_table = get_html_translation_table( HTML_ENTITIES, ENT_QUOTES );
            $translation_table[ chr( 38 ) ] = '&';
            return preg_replace( '/&(?![A-Za-z]{0,4}\w{2,3};|#\d{2,3};)/', '&amp;', strtr( $my_html, $translation_table ) );
        }//4494
        /**
         * @description Escape single quotes, html_special_char " < > &, and fix line endings.
         * @param $text
         * @return mixed
         */
        protected function _esc_js( $text ){
            $safe_text = $this->_tp_check_invalid_utf8( $text );
            $safe_text = $this->_tp_special_chars( $safe_text, ENT_COMPAT );
            $safe_text = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", stripslashes( $safe_text ) );
            $safe_text = str_replace( "\r", '', $safe_text );
            $safe_text = str_replace( "\n", '\\n', addslashes( $safe_text ) );
            return $this->_apply_filters( 'js_escape', $safe_text, $text );
        }//4512
        /**
         * @description Escaping for HTML blocks.
         * @param $text
         * @return mixed
         */
        protected function _esc_html( $text ){
            $safe_text = $this->_tp_check_invalid_utf8( $text );
            $safe_text = $this->_tp_special_chars( $safe_text, ENT_QUOTES );
            return $this->_apply_filters( 'esc_html', $safe_text, $text );
        }//4540
        /**
         * @description Escaping for HTML attributes.
         * @param $text
         * @return mixed
         */
        protected function _esc_attr( $text ){
            $safe_text = $this->_tp_check_invalid_utf8( $text );
            $safe_text = $this->_tp_special_chars( $safe_text, ENT_QUOTES );
            return $this->_apply_filters( 'attribute_escape', $safe_text, $text );
        }//4565
        /**
         * @description Escaping for textarea values.
         * @param $text
         * @return mixed
         */
        protected function _esc_textarea( $text ){
            $safe_text = htmlspecialchars( $text, ENT_QUOTES, $this->_get_option( 'blog_charset' ) );
            return $this->_apply_filters( 'esc_textarea', $safe_text, $text );
        }//4590
        /**
         * @description Escaping for XML blocks.
         * @param $text
         * @return mixed
         */
        protected function _esc_xml( $text ){
            $safe_text = $this->_tp_check_invalid_utf8( $text );
            $cdata_regex = '\<\!\[CDATA\[.*?\]\]\>';
            $regex       = <<<EOF
/
	(?=.*?{$cdata_regex})                 # lookahead that will match anything followed by a CDATA Section
	(?<non_cdata_followed_by_cdata>(.*?)) # the "anything" matched by the lookahead
	(?<cdata>({$cdata_regex}))            # the CDATA Section matched by the lookahead
|	                                      # alternative
	(?<non_cdata>(.*))                    # non-CDATA Section
/sx
EOF;
            $_matches = static function( $matches ) {
                if ( ! $matches[0] ) return '';
                if ( ! empty( $matches['non_cdata'] ) ) return (new self)->_tp_special_chars( $matches['non_cdata'], ENT_XML1 );
                return (new self)->_tp_special_chars( $matches['non_cdata_followed_by_cdata'], ENT_XML1 ) . $matches['cdata'];
            };
            $safe_text = (string) preg_replace_callback($regex,$_matches,$safe_text);
            return $this->_apply_filters( 'esc_xml', $safe_text, $text );
        }//4611
        /**
         * @description Escape an HTML tag name.
         * @param $tag_name
         * @return mixed
         */
        protected function _tag_escape( $tag_name ){
            $safe_tag = strtolower( preg_replace( '/[^a-zA-Z0-9_:]/', '', $tag_name ) );
            return $this->_apply_filters( 'tag_escape', $safe_tag, $tag_name );
        }//4668
        /**
         * @description Convert full URL paths to absolute paths.
         * @since
         * @param string $link Full URL path.
         * @return string Absolute path.
         */
        protected function _tp_make_link_relative( $link ): string{
            return preg_replace( '|^(https?:)?//[^/]+(/?.*)|i', '$2', $link );
        }//4693
    }
}else die;