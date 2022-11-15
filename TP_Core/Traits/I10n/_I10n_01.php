<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-2-2022
 * Time: 04:54
 */
namespace TP_Core\Traits\I10n;
if(ABSPATH){
    trait _I10n_01 {
        /**
         * @description Retrieve the translation of $text.
         * @param $text
         * @param string $domain
         * @return mixed
         */
        protected function __( $text, $domain = 'default' ){
            return $this->_translate($text, $domain);
        }//296
        /**
         * @description Removes last item on a pipe-delimited string.
         * @param $string
         * @return string
         */
        protected function _before_last_bar( $string ):string{
            $last_bar = strrpos( $string, '|' );
            if ( false === $last_bar )return $string;
            else return substr( $string, 0, $last_bar );
        }//226
        /**
         * @description Determine the current locale desired for the request. has todo's
         * @return string
         */
        protected function _determine_locale():string{
            $determined_locale = $this->_apply_filters( 'pre_determine_locale', null );
            if ( ! empty( $determined_locale ) && is_string( $determined_locale ) ) return $determined_locale;
            $determined_locale = $this->_get_locale();
            if ( $this->_is_admin() )$determined_locale = $this->_get_user_locale();
            if ( isset( $_GET['_locale'] ) && 'user' === $_GET['_locale'] && $this->_tp_is_json_request() )
                $determined_locale = $this->_get_user_locale();
            $tp_lang = '';
            if ( ! empty( $_GET['tp_lang'] ) ) $tp_lang = $this->_sanitize_text_field( $_GET['tp_lang'] );
            elseif ( ! empty( $_COOKIE['tp_lang'] ) ) $tp_lang = $this->_sanitize_text_field( $_COOKIE['tp_lang'] );
            if(! empty($tp_lang)&& !empty($this->__globals_page_now)) //todo sort this out
                $determined_locale = $tp_lang;
            return $this->_apply_filters( 'determine_locale', $determined_locale );
        }//121
        /**
         * @description Display translated text.
         * @param $text
         * @param string $domain
         */
        protected function _e( $text, $domain = 'default' ):void{
            echo $this->_translate($text, $domain);
        }//342
        /**
         * @description Retrieve the translation of $text and escapes it for safe use in an attribute.
         * @param $text
         * @param string $domain
         * @return mixed
         */
        protected function _esc_attr__( $text, $domain = 'default' ){
            return $this->_esc_attr( $this->_translate( $text, $domain ) );
        }//312
        /**
         * @description Display translated text that has been escaped for safe use in an attribute.
         * @param $text
         * @param string $domain
         */
        protected function _esc_attr_e( $text, $domain = 'default' ):void{
            echo $this->_esc_attr( $this->_translate( $text, $domain ) );
        }//360
        /**
         * @description Translate string with gettext context, and escapes it for safe use in an attribute.
         * @param $text
         * @param $context
         * @param string $domain
         * @return mixed
         */
        protected function _esc_attr_x( $text, $context, $domain = 'default' ){
            return $this->_esc_attr( $this->_translate_with_get_text_context( $text, $context, $domain ) );
        }//431
        /**
         * todo
         * @description Retrieves the translation of $text and escapes it for safe use in HTML output.
         * @param $text
         * @param string $domain
         * @return mixed
         */
        protected function _esc_html__( $text, $domain = 'default' ){
            return $this->_esc_html( $this->_translate( $text, $domain ) );
         }//329
        /**
         * @description Displays translated text that has been escaped for safe use in HTML output.
         * @param $text
         * @param string $domain
         */
        protected function _esc_html_e( $text, $domain = 'default' ):void{
            echo $this->_esc_attr( $this->_translate( $text, $domain ) );
        }//378

        /**
         * @description Translates string with gettext context, and escapes it for safe use in HTML output.
         * @param $text
         * @param $context
         * @param string $domain
         * @return mixed
         */
        protected function _esc_html_x( $text, $context, $domain = 'default' ){
            return $this->_esc_html( $this->_translate_with_get_text_context( $text, $context, $domain ) );
        }//449
    }
}else die;