<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-2-2022
 * Time: 04:54
 */
namespace TP_Core\Traits\I10n;
use TP_Core\Libs\PoMo\TP_Translations;

if(ABSPATH){
    trait _I10n_05 {
        /**
         * @description Translates role name.
         * @param $name
         * @param string $domain
         * @return mixed
         */
        protected function _translate_user_role( $name, $domain = 'default' ) {
            return $this->_translate_with_get_text_context( $this->_before_last_bar( $name ), 'User role', $domain );
        }//1362
        /**
         * @description Retrieves the translation of $text in the context defined in $context.
         * @param $text
         * @param $context
         * @param string $domain
         * @return mixed
         */
        protected function _translate_with_get_text_context( $text, $context, $domain = 'default' ){
            $_translations = $this->_get_translations_for_domain( $domain );
            $translations = null;
            if($_translations instanceof TP_Translations){
                $translations = $_translations;
            }
            $translation = null;
            if($translations !== null){
                $translation  = $translations->translate( $text, $context );
            }//else{
                //$translation = $text;//todo is temporary, just to get some output for now!
            //}

            $translation = $this->_apply_filters( 'gettext_with_context', $translation, $text, $context, $domain );
            $translation = $this->_apply_filters( "gettext_with_context_{$domain}", $translation, $text, $context, $domain );

            return $translation;
        }//251
        /**
         * @description Unload translations for a text domain.
         * @param $domain
         * @return bool
         */
        protected function _unload_textdomain( $domain ):bool{
            $this->tp_I10n_unloaded = (array) $this->tp_I10n_unloaded;
            //plugin related is left out
            if ( isset( $this->tp_I10n[ $domain ] ) ){
                unset( $this->tp_I10n[ $domain ] );
                $this->tp_I10n_unloaded[ $domain ] = true;
                return true;
            }
            return false;
        }//779
        /**
         * @description Retrieve translated string with gettext context.
         * @param $text
         * @param $context
         * @param string $domain
         * @return mixed
         */
        protected function _x( $text, $context, $domain = 'default' ){
            return $this->_translate_with_get_text_context( $text, $context, $domain );
        }//399
        protected function _tp_dropdown_languages(array ...$args ):void{
            echo $this->_tp_get_dropdown_languages($args );
        }
    }
}else die;