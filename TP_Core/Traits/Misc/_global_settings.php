<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-5-2022
 * Time: 14:25
 */
namespace TP_Core\Traits\Misc;
use TP_Core\Libs\JSON\TP_Theme_JSON_Resolver;
if(ABSPATH){
    trait _global_settings{
        /**
         * @description Function to get the settings resulting of merging core, theme, and user data.
         * @param array $path
         * @param array $context
         * @return mixed
         */
        protected function _tp_get_global_settings( $path = [], $context = [] ){
            if ( ! empty( $context['block_name'] ) )
                $path = array_merge( array( 'blocks', $context['block_name'] ), $path );
            $origin = 'custom';
            if ( isset( $context['origin'] ) && 'base' === $context['origin'] )
                $origin = 'theme';
            $settings = TP_Theme_JSON_Resolver::get_merged_data( $origin )->get_settings();
            return $this->_tp_array_get( $settings, $path, $settings );
        }//27
        /**
         * @description Function to get the styles resulting of merging core, theme, and user data.
         * @param array $path
         * @param array $context
         * @return mixed
         */
        protected function _tp_get_global_styles( $path = [], $context = [] ){
            if ( ! empty( $context['block_name'] ) )
                $path = array_merge( array( 'blocks', $context['block_name'] ), $path );
            $origin = 'custom';
            if ( isset( $context['origin'] ) && 'base' === $context['origin'] )
                $origin = 'theme';
            $styles = TP_Theme_JSON_Resolver::get_merged_data( $origin )->get_raw_data()['styles'];
            return $this->_tp_array_get( $styles, $path, $styles );
        }//61
        /**
         * @description Returns the stylesheet resulting of merging core, theme, and user data.
         * @param array $types
         * @return string
         */
        protected function _tp_get_global_stylesheet( $types=[]):string{
            $can_use_cached = (
                ( ! defined( 'TP_DEBUG' ) || ! TP_DEBUG ) &&
                ( ! defined( 'TP_SCRIPT_DEBUG' ) || ! TP_SCRIPT_DEBUG ) &&
                ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) &&
                ! $this->_is_admin()
            );
            $transient_name = 'global_styles_' . $this->_get_stylesheet();
            if ( $can_use_cached ) {
                $cached = $this->_get_transient( $transient_name );
                if ( $cached ) return $cached;
            }
            $tree = TP_Theme_JSON_Resolver::get_merged_data();
            $supports_theme_json = TP_Theme_JSON_Resolver::theme_has_support();
            if ( empty( $types ) && ! $supports_theme_json )
                $types = array( 'variables', 'presets' );
            elseif ( empty( $types ) )
                $types = array( 'variables', 'styles', 'presets' );
            $styles_variables = '';
            if ( in_array( 'variables', $types, true ) ) {
                $styles_variables = $tree->get_stylesheet( array( 'variables' ) );
                $types            = array_diff( $types, array( 'variables' ) );
            }
            $styles_rest = '';
            if ( ! empty( $types ) ) {
                $origins = array( 'default', 'theme', 'custom' );
                if ( ! $supports_theme_json ) $origins = [ 'default'];
                $styles_rest = $tree->get_stylesheet( $types, $origins );
            }
            $stylesheet = $styles_variables . $styles_rest;
            if ( $can_use_cached )
                $this->_set_transient( $transient_name, $stylesheet, MINUTE_IN_SECONDS );
            return $stylesheet;
        }//88
        /**
         * @description Returns a string containing the SVGs to be referenced as filters (duotone).
         * @return string
         */
        protected function _tp_get_global_styles_svg_filters():string{
            $can_use_cached = (
                ( ! defined( 'TP_DEBUG' ) || ! TP_DEBUG ) &&
                ( ! defined( 'TP_SCRIPT_DEBUG' ) || ! TP_SCRIPT_DEBUG ) &&
                ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) &&
                ! $this->_is_admin()
            );
            $transient_name = 'global_styles_svg_filters_' . $this->_get_stylesheet();
            if ( $can_use_cached ) {
                $cached = $this->_get_transient( $transient_name );
                if ( $cached ) return $cached;
            }
            $supports_theme_json = TP_Theme_JSON_Resolver::theme_has_support();
            $origins = [ 'default', 'theme', 'custom'];
            if ( ! $supports_theme_json ) $origins = array( 'default' );
            $tree = TP_Theme_JSON_Resolver::get_merged_data();
            $svgs = $tree->get_svg_filters( $origins );
            if ( $can_use_cached )
                $this->_set_transient( $transient_name, $svgs, MINUTE_IN_SECONDS );
            return $svgs;
        }//161
        /**
         * @description Test if the current browser runs on a mobile device (smart phone, tablet, etc.)
         * @return mixed
         */
        protected function _tp_is_mobile(){
            if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
                $is_mobile = false;
            } elseif ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Mobile' ) !== false // Many mobile devices (all iPhone, iPad, etc.)
                || strpos( $_SERVER['HTTP_USER_AGENT'], 'Android' ) !== false
                || strpos( $_SERVER['HTTP_USER_AGENT'], 'Silk/' ) !== false
                || strpos( $_SERVER['HTTP_USER_AGENT'], 'Kindle' ) !== false
                || strpos( $_SERVER['HTTP_USER_AGENT'], 'BlackBerry' ) !== false
                || strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera Mini' ) !== false
                || strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera Mobi' ) !== false ) {
                $is_mobile = true;
            } else {
                $is_mobile = false;
            }
            return $this->_apply_filters( 'tp_is_mobile', $is_mobile );
        }//148 from vars.php
    }
}else die;