<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-2-2022
 * Time: 04:54
 */
namespace TP_Core\Traits\I10n;
//use TP_Core\Traits\Inits\_init_L10n;
use TP_Core\Libs\TP_Locale;
use TP_Core\Traits\Inits\_init_locale;
use TP_Core\Libs\PoMo\NOOP_Translations; //todo
use TP_Core\Libs\Users\TP_User;
use TP_Core\Traits\Inits\_init_translate;
if(ABSPATH){
    trait _I10n_02 {
        use _init_locale;
        use _init_translate;
        /**
         * @description Display translated string with gettext context.
         * @param $text
         * @param $context
         * @param string $domain
         */
        protected function _ex( $text, $context, $domain = 'default' ):void{
            echo $this->_x( $text, $context, $domain );
        }//413
        /**
         * @description Get all available languages based on the presence of *.mo files in a given directory.
         * @param null $dir
         * @return mixed
         */
        protected function _get_available_languages( $dir = null ){
            $languages = [];
            $lang_files = glob( ( is_null( $dir ) ? TP_CORE_LANG : $dir ) . '/*.mo' );
            if ( $lang_files ) {
                foreach ( $lang_files as $lang_file ) {
                    $lang_file = basename( $lang_file, '.mo' );
                    if ( 0 !== strpos( $lang_file, 'continents-cities' ) && 0 !== strpos( $lang_file, 'ms-' ) &&
                        0 !== strpos( $lang_file, 'admin-' ) ) {
                        $languages[] = $lang_file;
                    }
                }
            }
            return $this->_apply_filters( 'get_available_languages', $languages, $dir );
        }//1378
        /**
         * @description Retrieves the current locale.
         * @return mixed
         */
        protected function _get_locale(){
            if(isset($this->locale)) return $this->_apply_filters('locale',$this->locale);
            if(isset($this->tp_local_package)) $this->locale = $this->tp_local_package;
            if ( defined( 'TP_LANG' ) ) $this->locale = TP_LANG;
            if($this->_is_multisite()){
                if($this->_tp_installing()) $ms_locale = $this->_get_site_option( 'TP_LANG' );
                else{
                    $ms_locale = $this->_get_option( 'TP_LANG' );
                    if ( false === $ms_locale ) $ms_locale = $this->_get_option('TP_LANG');
                }
                if ( false !== $ms_locale ) $this->locale = $ms_locale;
            }else{
                $db_locale = $this->_get_option('TP_LANG');
                if ( false !== $db_locale ) $this->locale = $db_locale;
            }
            if ( empty( $this->locale ) ) $this->locale = 'en_US';
            return $this->_apply_filters('locale', $this->locale);
        }//30
        /**
         * @param $domain
         * @param bool $reset
         * @return mixed
         */
        protected function _get_path_to_translation( $domain, $reset = false ){
            static $available_translations = [];
            if ( true === $reset ) {$available_translations = [];}
            if ( ! isset( $available_translations[ $domain ] ) ) {
                $available_translations[ $domain ] = $this->_get_path_to_translation_from_lang_dir( $domain );
            }
            return $available_translations[ $domain ];
        }//1239
        /**
         * @param $domain
         * @return bool|string
         */
        protected function _get_path_to_translation_from_lang_dir( $domain ){
            static $cached_mo_files = null;
            if ( null === $cached_mo_files ) {
                $cached_mo_files = [];
                $locations = [TP_CONTENT_LANG];// no plugins
                foreach ( $locations as $location ) {
                    $mo_files = glob( $location . '/*.mo' );
                    if ( $mo_files ) {
                        $cached_mo_files = $this->_tp_array_merge($cached_mo_files, $mo_files);
                    }
                }
            }
            $locale = $this->_determine_locale();
            $mo_file = "{$domain}-{$locale}.mo";
            $path = TP_CONTENT_LANG . $mo_file;
            if ( in_array( $path, $cached_mo_files, true ) ) {
                return $path;
            }
            return false;
        }//1266
        /**
         * @param $domain
         * @return null|NOOP_Translations
         */
        protected function _get_translations_for_domain($domain ):NOOP_Translations{
            if ( isset( $tp_I10n[ $domain ] ) || ( $this->_load_textdomain_just_in_time( $domain ) && isset( $tp_I10n[ $domain ] ) ) )
                return $tp_I10n[ $domain ];
            $this->noop_translations = null;
            if ( null === $this->noop_translations ) $this->noop_translations = $this->_init_noop_translations(); //todo test
            return $this->noop_translations;
        }//1313
        /**
         * @description Retrieves the locale of a user.
         * @param int $user_id
         * @return mixed
         */
        protected function _get_user_locale( $user_id = 0 ){
            $user = false;
            if ( 0 === $user_id && function_exists('_tp_get_user_current') )
                $user = $this->_tp_get_user_current();
            elseif ( $user_id instanceof TP_User ) $user = $user_id;
            elseif ( $user_id && is_numeric( $user_id ) ) $user = $this->_get_user_by( 'id', $user_id );
            if ( ! $user ) return $this->_get_locale();
            $locale = $user->locale;
            return $locale ?? $this->_get_locale();
        }//94
        public function get_user_locale( $user_id = 0 ){
            return $this->_get_user_locale($user_id);
        }
        protected function _is_locale_switched():bool{
            return $this->_init_locale_switcher()->is_switched();
        }//1723
        protected function _is_rtl():bool{
            if(!($this->_init_locale() instanceof TP_Locale)){
                return false;
            }
            return $this->_init_locale()->is_rtl();
        }//1657
    }
}else die;