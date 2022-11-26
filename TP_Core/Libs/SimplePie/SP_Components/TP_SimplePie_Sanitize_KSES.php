<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-5-2022
 * Time: 21:29
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Traits\K_Ses\_k_ses_04;
if(ABSPATH){
    class TP_SimplePie_Sanitize_KSES extends SimplePie_Sanitize {
        use _k_ses_04;
        public function sanitize( $data, $type, $base = '' ) {
            $data = trim( $data );
            if ( $type & SP_CONSTRUCT_MAYBE_HTML ) {
                if ( preg_match( '/(&(#(x[0-9a-fA-F]+|[0-9]+)|[a-zA-Z0-9]+)|<\/[A-Za-z][^\x09\x0A\x0B\x0C\x0D\x20\x2F\x3E]*' . SP_PCRE_HTML_ATTRIBUTE . '>)/', $data ) )
                    $type |= SP_CONSTRUCT_HTML;
                else $type |= SP_CONSTRUCT_TEXT;
            }
            if ( $type & SP_CONSTRUCT_BASE64 ) $data = base64_decode( $data );
            if ( $type & ( SP_CONSTRUCT_HTML ) ) {
                $data = $this->_tp_kses_post( $data );
                if (($this->sp_registry instanceof SimplePie_Registry) && 'UTF-8' !== $this->sp_output_encoding ) {
                    $data = $this->sp_registry->call( 'Misc', 'change_encoding',[$data, 'UTF-8', $this->sp_output_encoding] );
                }
                return $data;
            } else return parent::sanitize( $data, $type, $base );
        }
    }
}else die;