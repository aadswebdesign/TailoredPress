<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-3-2022
 * Time: 17:40
 */
namespace TP_Core\Traits\Theme;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Theme;
use TP_Core\Traits\Constructs\_construct_theme;
use TP_Core\Traits\Inits\_init_theme;
if(ABSPATH){
    trait _theme_03 {
        use _init_theme,_construct_theme;
        /**
         * @description Validates the theme requirements for TailoredPress version and PHP version.
         * @param $stylesheet
         * @return bool|TP_Error
         */
        protected function _validate_theme_requirements( $stylesheet ){
            $_theme = $this->_tp_get_theme( $stylesheet );
            $theme = null;
            if($_theme  instanceof  TP_Theme){
                $theme = $_theme;
            }
            $requirements = array(
                'requires'     => ! empty( $theme->get_theme( 'RequiresTP' ) ) ? $theme->get_theme( 'RequiresTP' ) : '',
                'requires_php' => ! empty( $theme->get_theme( 'RequiresPHP' ) ) ? $theme->get_theme( 'RequiresPHP' ) : '',
            );
            $compatible_tp  = $this->_tp_is_version_compatible( $requirements['requires'] );
            $compatible_php = $this->_is_php_version_compatible( $requirements['requires_php'] );
            if ( ! $compatible_tp && ! $compatible_php ) {
                return new TP_Error(
                    'theme_tp_php_incompatible',
                    sprintf($this->_x( '<strong>Error:</strong> Current TailoredPress and PHP versions do not meet minimum requirements for %s.', 'theme' ),
                        $theme->display('Name')));
            }
            if ( ! $compatible_php ) {
                return new TP_Error(
                    'theme_php_incompatible',
                    sprintf($this->_x( '<strong>Error:</strong> Current PHP version does not meet minimum requirements for %s.', 'theme' ),
                        $theme->display( 'Name' )));
            }
            if ( ! $compatible_tp ) {
                return new TP_Error(
                    'theme_tp_incompatible',
                    sprintf( $this->_x( '<strong>Error:</strong> Current TailoredPress version does not meet minimum requirements for %s.', 'theme' ),
                        $theme->display( 'Name' )));
            }
            return true;
        }//908
        /**
         * @description Retrieves all theme modifications.
         * @return array
         */
        protected function _get_theme_mods():array{
            $theme_slug = $this->_get_option( 'stylesheet' );
            $mods       = $this->_get_option( "theme_mods_$theme_slug" );
            if ( false === $mods ) {
                $theme_name = $this->_get_option( 'current_theme' );
                if ( false === $theme_name ){
                    $get_theme = $this->_tp_get_theme();
                    if($get_theme  instanceof  TP_Theme){
                        $theme_name = $get_theme->get_theme( 'Name' );
                    }
                }
                $mods = $this->_get_option( "mods_$theme_name" ); // Deprecated location.
                if (false !== $mods && $this->_is_admin()) {
                    $this->_update_option( "theme_mods_$theme_slug", $mods );
                    $this->_delete_option( "mods_$theme_name" );
                }
            }
            if ( ! is_array( $mods ) ) $mods = [];
            return $mods;
        }//959
        /**
         * @description Retrieves theme modification value for the current theme.
         * @param $name
         * @param bool $default
         * @return bool
         */
        protected function _get_theme_mod( $name, $default = false ):bool{
            $mods = $this->_get_theme_mods();
            if ( isset( $mods[ $name ])) return $this->_apply_filters( "theme_mod_{$name}", $mods[ $name ] );
            if ((string) $default && preg_match('#(?<!%)%(?:\d+\$?)?s#', $default)) {
                $default = preg_replace( '#(?<!%)%$#', '', $default );
                $default = (bool)sprintf($default, $this->_get_template_directory_uri(), $this->_get_stylesheet_directory_uri() );
            }
            return $this->_apply_filters( "theme_mod_{$name}", $default );
        }//997
        /**
         * @description Updates theme modification value for the current theme.
         * @param $name
         * @param $value
         * @return mixed
         */
        protected function _set_theme_mod( $name, $value ){
            $mods      = $this->_get_theme_mods();
            $old_value = $mods[ $name ] ?? false;
            $mods[ $name ] = $this->_apply_filters( "pre_set_theme_mod_{$name}", $value, $old_value );
            $theme = $this->_get_option( 'stylesheet' );
            return $this->_update_option( "theme_mods_$theme", $mods );
        }//1038
        /**
         * @description Removes theme modification name from current theme list.
         * @param $name
         */
        protected function _remove_theme_mod( $name ):void{
            $mods = $this->_get_theme_mods();
            if (!isset( $mods[ $name ])) return;
            unset( $mods[ $name ] );
            if ( empty($mods)){
                $this->_remove_theme_mods();
                return;
            }
            $theme = $this->_get_option( 'stylesheet' );
            $this->_update_option( "theme_mods_$theme", $mods );
        }//1071
        /**
         * @description Removes theme modifications option for current theme.
         */
        protected function _remove_theme_mods():void{
            $this->_delete_option( 'theme_mods_' . $this->_get_option( 'stylesheet' ) );
            $theme_name = $this->_get_option( 'current_theme' );
            if ( false === $theme_name ){
                $get_theme = $this->_tp_get_theme();
                if($get_theme instanceof  TP_Theme){
                    $theme_name = $get_theme->get_theme( 'Name' );
                }
            }
            $this->_delete_option( 'mods_' . $theme_name );
        }//1095
        /**
         * @description Retrieves the custom header text color in 3- or 6-digit hexadecimal form.
         * @return mixed
         */
        protected function _get_header_textcolor(){
            return $this->_get_theme_mod( 'header_textcolor', $this->_get_theme_support( 'custom-header', 'default-text-color' ) );
        }//1114
        /**
         * @description Displays the custom header text color in 3- or 6-digit hexadecimal form (minus the hash symbol).
         */
        public function header_textcolor():void{
            echo $this->_get_header_textcolor();
        }//1123
        /**
         * @description Whether to display the header text.
         * @return bool
         */
        protected function _display_header_text():bool{
            if ( ! $this->_current_theme_supports( 'custom-header', 'header-text' ) ) return false;
            $text_color = $this->_get_theme_mod( 'header_textcolor', $this->_get_theme_support( 'custom-header', 'default-text-color' ) );
            return 'blank' !== $text_color;
        }//1134
        /**
         * @description Checks whether a header image is set or not.
         * @return bool
         */
        protected function _has_header_image():bool{
            return $this->_get_header_image();
        }//1152
    }
}else die;