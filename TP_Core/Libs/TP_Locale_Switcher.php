<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-5-2022
 * Time: 18:43
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\Inits\_init_L10n;
use TP_Core\Traits\Inits\_init_locale;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_05;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Options\_option_01;
if(ABSPATH){
    class TP_Locale_Switcher{
        use _action_01,_filter_01,_I10n_01,_I10n_02,_I10n_03,_I10n_04,_I10n_05;
        use _init_locale,_init_L10n,_option_01,_load_03,_load_04;
        private $__locales = [];
        private $__original_locale;
        private $__available_languages;
        public function __construct() {
            $this->__original_locale     = $this->_determine_locale();
            $this->__available_languages = array_merge( array( 'en_US' ), $this->_get_available_languages() );
        }//50
        public function init():void {
            $this->_add_filter( 'locale', [$this, 'filter_locale'] );
        }//61
        public function switch_to_locale( $locale ):bool {
            $current_locale = $this->_determine_locale();
            if ( $current_locale === $locale ) return false;
            if ( ! in_array( $locale, $this->__available_languages, true ) )
                return false;
            $this->__locales[] = $locale;
            $this->__change_locale( $locale );
            $this->_do_action( 'switch_locale', $locale );
            return true;
        }//95
        public function restore_previous_locale() {
            $previous_locale = array_pop( $this->__locales );
            if ( null === $previous_locale ) return false;
            $locale = end( $this->__locales );
            if ( ! $locale ) $locale = $this->__original_locale;
            $this->__change_locale( $locale );
            $this->_do_action( 'restore_previous_locale', $locale, $previous_locale );
            return $locale;
        }//104
        public function restore_current_locale() {
            if ( empty( $this->locales ) ) return false;
            $this->locales = array( $this->__original_locale );
            return $this->restore_previous_locale();
        }//141
        public function is_switched():bool {
            return ! empty( $this->locales );
        }//158
        public function filter_locale( $locale ) {
            $switched_locale = end( $this->__locales );
            if ( $switched_locale ) return $switched_locale;
            return $locale;
        }//170
        private function __load_translations( $locale ):void {
            $l10n = $this->_init_L10n();
            $domains = $l10n ? array_keys( $l10n ) : [];
            $this->_load_default_textdomain( $locale );
            foreach ( $domains as $domain ) {
                if ( 'default' === $domain ) continue;
                $this->_unload_textdomain( $domain );
                $this->_get_translations_for_domain( $domain );
            }
        }//191
        private function __change_locale( $locale ):void {
            $this->_get_path_to_translation( null, true );
            $this->__load_translations( $locale );
            $tp_locale = $this->filter_locale($locale);
            $this->_init_L10n($tp_locale);//todo ?
            $this->_do_action( 'change_locale', $locale );
        }
    }
}else die;