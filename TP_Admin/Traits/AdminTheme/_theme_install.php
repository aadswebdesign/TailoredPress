<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-5-2022
 * Time: 12:21
 */
namespace TP_Admin\Traits\Theme;
if(ABSPATH){
    trait _theme_install{
        //@description
        protected function _themes_tags(){return '';}//added
        //@description Display search form for searching themes.
        protected function _install_theme_search_form( $type_selector = true ){return '';}//91
        //@description Display tags filter for themes.
        protected function _install_themes_dashboard(){return '';}//136
        //@description
        protected function _install_themes_upload(){return '';}//180
        //@description Display theme content based on theme list.
        protected function _display_themes(){return '';}//218
        //@description Display theme information in dialog box form.
        protected function _install_theme_information(){return '';}//236
    }
}else die;