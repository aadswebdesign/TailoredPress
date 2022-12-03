<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 23:09
 */
namespace TP_Core\Traits\K_Ses;
use TP_Core\Traits\Inits\_init_json;
if(ABSPATH){
    trait _k_ses_04 {
        use _init_json;
        /**
         * @description Converts all numeric HTML entities to their named counterparts.
         * @param $string
         * @return mixed
         */
        protected function _tp_kses_decode_entities( $string ){
            $string = preg_replace_callback( '/&#(\d+);/', [$this,'_tp_kses_decode_entities_chr'], $string );
            $string = preg_replace_callback( '/&#[Xx]([0-9A-Fa-f]+);/', [$this,'_tp_kses_decode_entities_chr_hex_dec'], $string );
            return $string;
        }//2029
        /**
         * @description Regex callback for `tp_kses_decode_entities()`.
         * @param $match
         * @return string
         */
        protected function _tp_kses_decode_entities_chr( $match ):string{
            return chr( $match[1] );
        }//2046
        /**
         * @description Regex callback for `tp_kses_decode_entities()`.
         * @param $match
         * @return string
         */
        protected function _tp_kses_decode_entities_chr_hex_dec( $match ):string{
            return chr( hexdec( $match[1] ) );
        }//2060
        /**
         * @description Sanitize content with allowed HTML KSES rules.
         * @param $data
         * @return string
         */
        protected function _tp_filter_kses( $data ):string{
            return addslashes( $this->_tp_kses( stripslashes( $data ), $this->_current_filter() ) );
        }//2074
        /**
         * @description Sanitize content with allowed HTML KSES rules.
         * @param $data
         * @return mixed
         */
        protected function _tp_kses_data( $data ){
            return $this->_tp_kses( $data, $this->_current_filter() );
        }//2088
        /**
         * @description Sanitizes content for allowed HTML tags for post content.
         * @param $data
         * @return string
         */
        protected function _tp_filter_post_kses( $data ):string{
            return addslashes( $this->_tp_kses( stripslashes( $data ), 'post' ) );
        }//2105
        /**
         * @description Sanitizes global styles user content removing unsafe rules.
         * @param $data
         * @return mixed
         */
        protected function _tp_filter_global_styles_post( $data ){
            $decoded_data        = json_decode( $this->_tp_unslash( $data ), true );
            $json_decoding_error = json_last_error();
            if (
                JSON_ERROR_NONE === $json_decoding_error &&
                is_array( $decoded_data ) &&
                isset( $decoded_data['isGlobalStylesUserThemeJSON'] ) &&
                $decoded_data['isGlobalStylesUserThemeJSON']
            ) {
                unset( $decoded_data['isGlobalStylesUserThemeJSON'] );
                $data_to_encode = $this->_init_theme_json()->remove_insecure_properties( $decoded_data );
                $data_to_encode['isGlobalStylesUserThemeJSON'] = true;
                return $this->_tp_slash( $this->_tp_json_encode( $data_to_encode ) );
            }
            return $data;
        }//2117
        /**
         * @description Sanitizes content for allowed HTML tags for post content.
         * @param $data
         * @return mixed
         */
        protected function _tp_kses_post( $data ){
            return $this->_tp_kses( $data, 'post' );
        }//2149
        /**
         * @description Navigates through an array, object, or scalar, and sanitizes content for allowed HTML tags for post content.
         * @param $data
         * @return mixed
         */
        protected function _tp_kses_post_deep( $data ){
            return $this->_map_deep( $data, [$this,'__tp_kses_post'] );
        }//2164
        /**
         * @description Strips all HTML from a text string.
         * @param $data
         * @return string
         */
        protected function _tp_filter_no_html_kses( $data ):string{
            return addslashes( $this->_tp_kses( stripslashes( $data ), 'strip' ) );
        }//2178
    }
}else die;