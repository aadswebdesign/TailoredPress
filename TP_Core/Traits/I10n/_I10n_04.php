<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-2-2022
 * Time: 04:54
 */
namespace TP_Core\Traits\I10n;
//use TP_Admin\Factory\Install\_install_translation;
use TP_Admin\Traits\_adm_translation_install;
use TP_Core\Libs\PoMo\TP_Translations;
use TP_Core\Traits\Inits\_init_locale;
use TP_Core\Traits\Inits\_init_translate;
if(ABSPATH){
    trait _I10n_04 {
        use _init_locale;
        use _init_translate;
        use _adm_translation_install;
        /**
         * @description Registers plural strings with gettext context in POT file, but does not translate them.
         * @param $singular
         * @param $plural
         * @param $context
         * @param null $domain
         * @return array
         */
        protected function _nx_noop( $singular, $plural, $context, $domain = null ):array{
            return [0 => $singular,1 => $plural, 2 => $context,'singular' => $singular,'plural' => $plural,'context' => $context,'domain' => $domain,];
        }//644
        /**
         * @description  Switches the translations according to the given locale.
         * @param $locale
         * @return bool
         */
        protected function _switch_to_locale( $locale ):bool{
            return $this->_init_locale_switcher()->switch_to_locale( $locale );
        }//1675
        /**
         * @description Restores the translations according to the original locale.
         * @return bool|mixed|string
         */
        protected function _restore_current_locale(){
            return $this->_init_locale_switcher()->restore_current_locale();
        }//1707
        /**
         * @description Restores the translations according to the original locale.
         */
        protected function _restore_previous_locale(){
            return $this->_init_locale_switcher()->restore_previous_locale();
        }//1691
        /**
         * @description Language selector.
         * @param array ...$args
         * @return bool|string
         */
        protected function _tp_get_dropdown_languages( ...$args) {
            $parsed_args = $this->_tp_parse_args(
                $args,
                ['id' => 'locale','name' => 'locale','languages' => [],'translations' => [],'selected' => '', 'echo' => 1,
                    'show_available_translations' => true,'show_option_site_default' => false,'show_option_en_us' => true,'explicit_option_en_us' => false,]
            );
            if ( ! $parsed_args['id'] || ! $parsed_args['name'] ) return false;
            if ( 'en_US' === $parsed_args['selected'] && ! $parsed_args['explicit_option_en_us'] )
                $parsed_args['selected'] = '';
            $translations = $parsed_args['translations'];
            if ( empty( $translations ) )  $translations = $this->_tp_get_available_translations();
            $languages = [];
            foreach ( $parsed_args['languages'] as $locale ) {
                if ( isset( $translations[ $locale ] ) ) {
                    $translation = $translations[ $locale ];
                    $languages[] = ['language' => $translation['language'],
                        'native_name' => $translation['native_name'],'lang' => current( $translation['iso'] ),];
                    unset( $translations[ $locale ] );
                } else $languages[] = ['language' => $locale, 'native_name' => $locale,'lang' => '',];
            }
            $translations_available = ( ! empty( $translations ) && $parsed_args['show_available_translations'] );
            $structure = [];
            if ( $translations_available )
                $structure[] = "<optgroup label='{$this->_esc_attr_x( 'Installed', 'translations' )}'>";
            if ( $parsed_args['show_option_site_default'] ) {
                $structure[] = sprintf(
                    "<option value='site-default' data-installed='1' %s>%s</option>",
                    $this->_get_selected( 'site-default', $parsed_args['selected']),
                    $this->_x( 'Site Default', 'default site language' )
                );
            }
            if ( $parsed_args['show_option_en_us'] ) {
                $value       = ( $parsed_args['explicit_option_en_us'] ) ? 'en_US' : '';
                $structure[] = sprintf(
                    "<option value='%s' lang='en' data-installed='1' %s>English (United States)</option>",
                    $this->_esc_attr( $value ),
                    $this->_get_selected( '', $parsed_args['selected'])
                );
            }
            foreach ( $languages as $language ) {
                $structure[] = sprintf(
                    "<option value='%s' lang='%s' %s data-installed='1'>%s</option>",
                    $this->_esc_attr( $language['language'] ),
                    $this->_esc_attr( $language['lang'] ),
                    $this->_get_selected( $language['language'], $parsed_args['selected']),
                    $this->_esc_html( $language['native_name'] )
                );
            }
            if ( $translations_available ) $structure[] = '</optgroup>';
            // List available translations.
            if ( $translations_available ) {
                $structure[] = "<optgroup label='{$this->_esc_attr_x( 'Available', 'translations' )}'>";
                foreach ( $translations as $translation ) {
                    $structure[] = sprintf(
                        "<option value='%s' lang='%s' %s>%s</option>",
                        $this->_esc_attr( $translation['language'] ),
                        $this->_esc_attr( current( $translation['iso'] ) ),
                        $this->_get_selected( $translation['language'], $parsed_args['selected']),
                        $this->_esc_html( $translation['native_name'] )
                    );
                }
                $structure[] = '</optgroup>';
            }
            $output  = sprintf( "<select name='%s' id='%s'>", $this->_esc_attr( $parsed_args['name'] ), $this->_esc_attr( $parsed_args['id'] ) );
            $output .= implode( "\n", $structure );
            $output .= '</select>';
            return $output;
        }//1516
        public function tp_dropdown_languages( ...$args):void{
            echo $this->_tp_get_dropdown_languages($args);
        }
        /**
         * @description Get installed translations.
         * @param $type
         * @return array
         */
        protected function _tp_get_installed_translations( $type ):array{
            if ( 'themes' !== $type && 'core' !== $type )
                return [];
            $dir = 'core' === $type ? '' : "/$type";
            if ( ! is_dir( TP_CORE_LANG ) ) return [];
            if ( $dir && ! is_dir( TP_CORE_LANG . $dir ) ) return [];
            $files = scandir( TP_CORE_LANG . $dir );
            if ( ! $files ) return [];
            $language_data = [];
            foreach ( $files as $file ) {
                if ( '.' === $file[0] || is_dir( TP_CORE_LANG . "$dir/$file" ) ) continue;
                if ( substr( $file, -3 ) !== '.po' ) continue;
                if ( ! preg_match( '/(?:(.+)-)?([a-z]{2,3}(?:_[A-Z]{2})?(?:_[a-z0-9]+)?).po/', $file, $match ) )
                    continue;
                if ( ! in_array( substr( $file, 0, -3 ) . '.mo', $files, true ) ) continue;
                @list( , $textdomain, $language ) = $match;
                if ( '' === $textdomain ) $textdomain = 'default';
                $language_data[ $textdomain ][ $language ] = $this->_tp_get_pomo_file_data( TP_CORE_LANG . "$dir/$file" );
            }
            return $language_data;
        }//1414
        /**
         * @description Extract headers from a PO file.
         * @param $po_file
         * @return mixed
         */
        protected function _tp_get_pomo_file_data( $po_file ) {
            $headers = $this->_get_file_data(
                $po_file,
                ['POT-Creation-Date' => '"POT-Creation-Date','PO-Revision-Date' => '"PO-Revision-Date',
                    'Project-Id-Version' => '"Project-Id-Version','X-Generator' => '"X-Generator',]
            );
            foreach ( $headers as $header => $value )
                $headers[ $header ] = preg_replace( '~(\\\n)?"$~', '', $value );
            return $headers;
        }//1467
        /**
         * @description Retrieves the translation of $text.
         * @param $text
         * @param string $domain
         * @return mixed
         */
        protected function _translate( $text, $domain = 'default' ){
            $_translations = $this->_get_translations_for_domain( $domain );
            $translations = null;
            $translation = null;

            if( $_translations instanceof TP_Translations ){
                $translations = $_translations;
            }
            if($translations !== null){
                $translation  = $translations->translate( $text );
            }else{
                $translation = $text;//todo is temporary, just to get some output for now!
            }
            $translation = $this->_apply_filters( 'gettext', $translation, $text, $domain ); //todo params
            $translation = $this->_apply_filters( "gettext_{$domain}", $translation, $text, $domain );
            return $translation;
        }//184
        /**
         * @description Translates and retrieves the singular or plural form of a string that's been registered with $this->__n_noop() or $this->__nx_noop().
         * @param $nooped_plural
         * @param $count
         * @param string $domain
         * @return mixed
         */
        protected function _translate_nooped_plural( $nooped_plural, $count, $domain = 'default' ){
            if ( $nooped_plural['domain'] ) $domain = $nooped_plural['domain'];
            if ( $nooped_plural['context'] )return $this->_nx( $nooped_plural['singular'], $nooped_plural['plural'], $count, $nooped_plural['context'], $domain );
            else return $this->_n( $nooped_plural['singular'], $nooped_plural['plural'], $count, $domain );
        }//676
        /**
         * @description Translates the provided settings value using its i18n schema.
         * @param $i18n_schema
         * @param $settings
         * @param $textdomain
         * @return array
         */
        protected function _translate_settings_using_i18n_schema( $i18n_schema, $settings, $textdomain ):array{
            if ( empty( $i18n_schema )||empty( $settings )||empty($textdomain)) return $settings;
            if ( is_string( $i18n_schema ) && is_string( $settings ) )
                return $this->_translate_with_get_text_context( $settings, $i18n_schema, $textdomain );
            if ( is_array( $i18n_schema ) && is_array( $settings ) ) {
                $translated_settings = array();
                foreach ( $settings as $value ) $translated_settings[] = $this->_translate_settings_using_i18n_schema( $i18n_schema[0], $value, $textdomain );
                return $translated_settings;
            }
            if ( is_object( $i18n_schema ) && is_array( $settings ) ) {
                $group_key           = '*';
                $translated_settings = array();
                foreach ( $settings as $key => $value ) {
                    if ( isset( $i18n_schema->$key ) )
                        $translated_settings[ $key ] = $this->_translate_settings_using_i18n_schema( $i18n_schema->$key, $value, $textdomain );
                    elseif ( isset( $i18n_schema->$group_key ) )
                        $translated_settings[ $key ] = $this->_translate_settings_using_i18n_schema( $i18n_schema->$group_key, $value, $textdomain );
                    else $translated_settings[ $key ] = $value;
                }
                return $translated_settings;
            }
            return $settings;
        }//1742
    }
}else die;