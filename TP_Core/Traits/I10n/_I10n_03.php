<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-2-2022
 * Time: 04:54
 */
namespace TP_Core\Traits\I10n;
//use TP_Core\Traits\Inits\_init_L10n;
use TP_Core\Libs\PoMo\NOOP_Translations;
use TP_Core\Libs\PoMo\TP_Translations;
use TP_Core\Traits\Inits\_init_assets;
use TP_Core\Libs\PoMo\MO;
use TP_Core\Traits\Inits\_init_translate;
if(ABSPATH){
    trait _I10n_03 {
        use _init_assets;
        use _init_translate;
        //876 load_plugin_textdomain skipped
        //917 load_mu_plugin_textdomain skipped
        /**
         * @description Whether there are translations for the text domain.
         * @param $domain
         * @return bool
         */
        protected function _is_textdomain_loaded( $domain ):bool{
            return isset( $tp_I10n[ $domain ] );
        }//1337
        /**
         * @description Load default translated strings based on locale.
         * @param null $locale
         * @return bool
         */
        protected function _load_default_textdomain( $locale = null ):bool{
            if ( null === $locale ) $locale = $this->_determine_locale();
            $this->_unload_textdomain( 'default' );
            $return = $this->_load_textdomain( 'default', TP_CORE_LANG . "$locale.mo" );
            if ( ( $this->_is_multisite() || ( defined( 'TP_INSTALLING_NETWORK' ) && TP_INSTALLING_NETWORK ) ) && ! file_exists( TP_CORE_LANG . "admin-$locale.mo" ) ) {
                $this->_load_textdomain( 'default', TP_CORE_LANG . "ms-$locale.mo" );
                return $return;
            }
            if (( defined( 'TP_REPAIRING' ) && TP_REPAIRING ) || $this->_is_admin() || $this->_tp_installing())
                $this->_load_textdomain( 'default', TP_CORE_LANG . "admin-$locale.mo" );
            if (( defined( 'TP_INSTALLING_NETWORK' ) && TP_INSTALLING_NETWORK ) || $this->_is_network_admin())
                $this->_load_textdomain( 'default', TP_CORE_LANG . "admin-network-$locale.mo" );
            return $return;
        }//833
        /**
         * @description Loads the script translated strings.
         * @param $handle
         * @param string $domain
         * @param null $path
         * @return bool|string
         */
        protected function _load_script_textdomain( $handle, $domain = 'default', $path = null ){
            $tp_scripts = $this->_init_scripts();
            if ( ! isset( $tp_scripts->registered[ $handle ] ) ) return false;
            $path   = $this->_untrailingslashit( $path );
            $locale = $this->_determine_locale();
            $file_base = 'default' === $domain ? $locale : $domain . '-' . $locale;
            $handle_filename = $file_base . '-' . $handle . '.json';
            if ( $path ) {
                $translations = $this->_load_script_translations( $path . '/' . $handle_filename, $handle, $domain );
                if ( $translations ) return $translations;
            }
            $src = $tp_scripts->registered[ $handle ]->src;
            if ( ! preg_match( '|^(https?:)?//|', $src ) && ! ( $tp_scripts->content_url && 0 === strpos( $src, $tp_scripts->content_url ) ) )
                $src = $tp_scripts->base_url . $src;
            $relative       = false;
            $languages_path = TP_CORE_LANG;
            $src_url     = $this->_tp_parse_url( $src );
            $content_url = $this->_tp_parse_url( $this->_content_url() );
            $site_url    = $this->_tp_parse_url( $this->_site_url() );
            if (( ! isset( $content_url['path'] ) || strpos( $src_url['path'], $content_url['path'] ) === 0 )&& ( ! isset( $src_url['host'] , $content_url['host'] ) || $src_url['host'] === $content_url['host'] )){
                if ( isset( $content_url['path'] ) ) $relative = substr( $src_url['path'], strlen( $content_url['path'] ) );
                else $relative = $src_url['path'];
                $relative = trim( $relative, '/' );
                $relative = explode( '/', $relative );
                $languages_path = TP_LANG_DIR . '/' . $relative[0];
                $relative = array_slice( $relative, 2 ); // Remove themes/<theme name>.
                $relative = implode( '/', $relative );
            }elseif( ! isset( $src_url['host'], $site_url['host'] ) || $src_url['host'] === $site_url['host'] ){
                if ( ! isset( $site_url['path'] ) ) $relative = trim( $src_url['path'], '/' );
                elseif ( ( strpos( $src_url['path'], $this->_trailingslashit( $site_url['path'] ) ) === 0 ) ) {
                    // Make the src relative to the WP root.
                    $relative = substr( $src_url['path'], strlen( $site_url['path'] ) );
                    $relative = trim( $relative, '/' );
                }
            }
            $relative = $this->_apply_filters( 'load_script_textdomain_relative_path', $relative, $src );
            if ( false === $relative ) return $this->_load_script_translations( false, $handle, $domain );
            if ( substr( $relative, -7 ) === '.min.js' ) $relative = substr( $relative, 0, -7 ) . '.js';
            $md5_filename = $file_base . '-' . md5( $relative ) . '.json';
            if ( $path ) {
                $translations = $this->_load_script_translations( $path . '/' . $md5_filename, $handle, $domain );
                if ( $translations ) return $translations;
            }
            if (!empty($translations)) {
                return  (string) $this->_load_script_translations( $languages_path . '/' . $md5_filename, $handle, $domain );
            }
            return $this->_load_script_translations( false, $handle, $domain );
        }//1011
        /**
         * @description Loads the translation data for the given script handle and text domain.
         * @param $file
         * @param $handle
         * @param $domain
         * @return bool|string
         */
        protected function _load_script_translations( $file, $handle, $domain ) {
            $translations = $this->_apply_filters( 'pre_load_script_translations', null, $file, $handle, $domain );
            if ( null !== $translations ) return $translations;
            $file = $this->_apply_filters( 'load_script_translation_file', $file, $handle, $domain );
            if ( ! $file || ! is_readable( $file ) ) return false;
            $translations = file_get_contents( $file );
            return $this->_apply_filters( 'load_script_translations', $translations, $file, $handle, $domain );
        }//1142
        /**
         * @description Load a .mo file into the text domain $domain.
         * @param $domain
         * @param $mo_file
         * @return bool
         */
        protected function _load_textdomain( $domain, $mo_file ):bool {
            $this->tp_I10n_unloaded = (array) $this->tp_I10n_unloaded;
            //plugin related's are left out, because no plugins
            $this->_do_action( 'load_textdomain', $domain, $mo_file );
            $mo_file = $this->_apply_filters( 'load_textdomain_mo_file', $mo_file, $domain );
            if ( ! is_readable( $mo_file ) ) return false;
            $mo = $this->_init_mo();
            if ($mo instanceof MO && ! $mo->import_from_file( $mo_file ) ) return false;
            if ($mo instanceof NOOP_Translations && isset( $this->tp_I10n[ $domain ] ) ) $mo->merge_with( $this->tp_I10n[ $domain ] );
            unset( $this->tp_I10n_unloaded[ $domain ] );
            $this->tp_I10n[ $domain ] = &$mo;
            return true;
        }//706
        /**
         * @description Loads theme textdomains just-in-time.
         * @param $domain
         * @return bool
         */
        protected function _load_textdomain_just_in_time( $domain ):bool{
            $l10n_unloaded = (array) $this->tp_I10n_unloaded;
            if ( 'default' === $domain || isset( $l10n_unloaded[ $domain ] ) )
                return false;
            $translation_path = $this->_get_path_to_translation( $domain );
            if ( false === $translation_path ) return false;
            return $this->_load_textdomain( $domain, $translation_path );
        }//1207
        /**
         * @description Load the theme's translated strings.
         * @param $domain
         * @param bool $path
         * @return bool
         */
        protected function _load_theme_textdomain( $domain, $path = false ):bool{
            $locale = $this->_apply_filters( 'theme_locale', $this->_determine_locale(), $domain );
            $mo_file = $domain . '-' . $locale . '.mo';
            if ( $this->_load_textdomain( $domain, TP_DIR_THEMES_LANG . $mo_file ) ) return true;
            if ( ! $path ) $path = $this->_get_template_directory();
            return $this->_load_textdomain( $domain, $path . '/' . $locale . '.mo' );
        }//949
        //989 load_child_theme_textdomain skipped
        /**
         * @description Translates and retrieves the singular or plural form based on the supplied number.
         * @param $single
         * @param $plural
         * @param $number
         * @param string $domain
         * @return mixed
         */
        protected function _n( $single, $plural, $number, $domain = 'default' ){
            $_translations = $this->_get_translations_for_domain( $domain );
            $translations = null;
            if( $_translations instanceof TP_Translations ){
                $translations = $_translations;
            }
            $translation = null;
            if($translation !== null){
                $translation  = $translations->translate_plural( $single, $plural, $number );
                $translation = $this->_apply_filters( 'ngettext', $translation, $single, $plural, $number, $domain );
                $translation = $this->_apply_filters( "ngettext_{$domain}", $translation, $single, $plural, $number, $domain );

            }
            return $translation;
        }//473
        /**
         * @description Registers plural strings in POT file, but does not translate them.
         * @param $singular
         * @param $plural
         * @param null $domain
         * @return array
         */
        protected function _n_noop( $singular, $plural, $domain = null ):array{
            return [0 => $singular,1 => $plural,'singular' => $singular,'plural' => $plural,'context' => null,'domain' => $domain,];
        }//598
        /**
         * @description Translates and retrieves the singular or plural form based on the supplied number, with gettext context.
         * @param $single
         * @param $plural
         * @param $number
         * @param $context
         * @param string $domain
         * @return mixed
         */
        protected function _nx( $single, $plural, $number, $context, $domain = 'default' ){
            $_translations = $this->_get_translations_for_domain( $domain );
            $translations = null;
            if( $_translations instanceof TP_Translations ){
                $translations = $_translations;
            }
            $translation  = $translations->translate_plural( $single, $plural, $number, $context );
            $translation = $this->_apply_filters( "ngettext_with_context_{$domain}", $translation, $single, $plural, $number, $context, $domain );
            return $translation;

        }//532
    }
}else die;