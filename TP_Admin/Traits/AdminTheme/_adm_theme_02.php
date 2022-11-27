<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-5-2022
 * Time: 12:21
 */
namespace TP_Admin\Traits\AdminTheme;
if(ABSPATH){
    trait _adm_theme_02{
        //@description Gets the error that was recorded for a paused theme.
        protected function _tp_get_theme_error( $theme ){return '';}//1108
        //@description Tries to resume a single theme.
        protected function _resume_theme( $theme, $redirect = '' ){return '';}//1137
        //@description Renders an admin notice in case some themes have been paused due to errors.
        protected function _paused_themes_notice(){return '';}//1190
    }
}else die;