<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-6-2022
 * Time: 06:42
 */
namespace TP_Admin\Traits;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Filters\_filter_01;
//use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _adm_translation_install{
        use _filter_01;//todo
        //use _init_error;
        /**
         * @description Retrieve translations from TailoredPress Translation API.
         * @param $type
         * @param null $args
         * @return mixed|TP_Error
         */
        protected function _translations_api( $type, $args = null ){
            if ( ! in_array( $type, array( 'plugins', 'themes', 'core' ), true ) ) {
                return new TP_Error( 'invalid_type', $this->__( 'Invalid translation type.' ) );
            }
            $res = $this->_apply_filters( 'translations_api', false, $type, $args );
            if ( false === $res ) {
                $url      = 'http://api.wordpress.org/translations/' . $type . '/1.0/';
                $http_url = $url;
                $ssl      = $this->_tp_http_supports( array( 'ssl' ) );
                if ( $ssl ) {
                    $url = $this->_set_url_scheme( $url, 'https' );
                }
                $options = ['timeout' => 3,'body' => ['tp_version' => TP_VERSION,'locale' => $this->_get_locale(),'version' => $args['version'],],]; // Version of theme or core.
                if ( 'core' !== $type ) {
                    $options['body']['slug'] = $args['slug']; //Theme slug.
                }
                $_request = $this->_tp_remote_post( $url, $options );
                $request = null;
                if( $_request instanceof TP_Error ){$request = $_request;}
                if ( $ssl && $this->_init_error( $request ) ) {
                    //trigger_error(sprintf(""));//todo
                    $request = $this->_tp_remote_post( $http_url, $options );
                }
                if ( $this->_init_error( $request ) ) {
                    $res = new TP_Error('translations_api_failed','todo', $request->get_error_message());
                }else {
                    $res = json_decode( $this->_tp_remote_retrieve_body( $request ), true );
                    if ( ! is_object( $res ) && ! is_array( $res ) ) {
                        $res = new TP_Error('translations_api_failed','todo',$this->_tp_remote_retrieve_body( $request ));
                    }
                }
            }
            return $this->_apply_filters( 'translations_api_result', $res, $type, $args );
        }//19
        /**
         * @description Get available translations from the TailoredPress API.
         * @return array|string
         */
        protected function _tp_get_available_translations(){
            if ( ! $this->_tp_installing() ) {
                $translations = $this->_get_site_transient( 'available_translations' );
                if ( false !== $translations ) {return $translations;}
            }
            $api = $this->_translations_api( 'core', array( 'version' => TP_VERSION ) );
            if ( $this->_init_error( $api ) || empty( $api['translations'] ) ) {
                return [];
            }
            $translations = [];
            foreach ( $api['translations'] as $translation ) {
                $translations[ $translation['language'] ] = $translation;
            }
            if ( ! defined( 'TP_INSTALLING' ) ) {
                $this->_set_site_transient( 'available_translations', $translations, 3 * HOUR_IN_SECONDS );
            }
            return $translations;
        }//122
        /**
         * @description Output the select form for the language selection on the installation screen.
         * @param $languages
         * @return mixed
         */
        protected function _tp_get_install_language_form( $languages ){
            $installed_languages = $this->_get_available_languages();
            $language_form = static function() use($languages,$installed_languages){
                $html = "<li><dt><label class='screen-reader-text' for='language'>Select a default language</label></dt>\n";
                $html .= "<dd><select size='14' name='language' id='language'>\n";
                $html .= "<option value='' lang='en' selected='selected' data-continue='Continue' data-installed='1'>English (United States)</option>\n";
                if (!empty((new self)->_tp_local_package) && isset($languages[(new self)->_tp_local_package],$languages[(new self)->_tp_local_package])) {
                    $language = $languages[ (new self)->_tp_local_package ];
                    $html .= sprintf("<option value='%s' lang='%s' data-continue='%s' %s>%s</option>\n",(new self)->_esc_attr($language['language']),
                        (new self)->_esc_attr(current($language['iso'])),(new self)->_esc_attr( $language['strings']['continue'] ?: 'Continue' ),
                        in_array( $language['language'], $installed_languages, true ) ? ' data-installed="1"' : '', (new self)->_esc_html( $language['native_name']));
                    unset( $languages[ (new self)->_tp_local_package ] );
                }
                foreach ( $languages as $language ) {
                    $html .= sprintf("<option value='%s' lang='%s' data-continue='%s' %s>%s</option>\n",(new self)->_esc_attr($language['language']),
                        (new self)->_esc_attr(current($language['iso'])),(new self)->_esc_attr( $language['strings']['continue'] ?: 'Continue' ),
                        in_array( $language['language'], $installed_languages, true ) ? ' data-installed="1"' : '',(new self)->_esc_html( $language['native_name']));
                }
                $html .= "</select></dd></li>\n";
                $html .= "<li><dt><p class='step'><span class='spinner'></span><input id='language_continue' type='submit' class='button button-primary button-large' value='Continue' /></p></dt></li>";
                return $html;
            };
            return $language_form;
        }//161
        //@description Download a language pack.
        protected function _tp_download_language_pack( $download ){
            if ( in_array( $download, $this->_get_available_languages(), true ) ) {
                return $download;
            }
            if ( ! $this->_tp_is_file_mod_allowed( 'download_language_pack' ) ) {
                return false;
            }
            $translations = $this->_tp_get_available_translations();
            if ( ! $translations ) { return false;}
            foreach ( $translations as $translation ) {
                if ( $translation['language'] === $download ) {
                    $translation_to_load = true;
                    break;
                }
            }
            if ( empty($translation_to_load)){return false;}


            return '';
        }//212
        //@description Check if TailoredPress has access to the filesystem without asking for * credentials.
        protected function _tp_can_install_language_pack():string{return '';}//260
    }
}else die;