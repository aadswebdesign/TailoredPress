<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 29-5-2022
 * Time: 08:37
 */
namespace TP_Admin\Traits\AdminUpgrade;
if(ABSPATH){
    trait _adm_upgrade_02{
        //@description Adds column to a database table, if it doesn't already exist.
        protected function _maybe_add_column( $table_name, $column_name, $create_ddl ){return '';}//2532
        //@description If a table only contains utf8 or utf8mb4 columns, convert it to utf8mb4.
        protected function _maybe_convert_table_to_utf8mb4( $table ){return '';}//2564
        //@description Utility version of get_option that is private to installation/upgrade.
        private function __get_option( $setting ){return '';}//2633
        //@description Filters for content to remove unnecessary slashes.
        protected function _deslash( $content ){return '';}//2665
        //@description Modifies the database based on specified SQL statements.
        protected function _db_delta( $queries = '', $execute = true ){return '';}//2702
        //@description Updates the database tables to a new schema.
        protected function _make_db_current( $tables = 'all' ){return '';}//3081
        //@description Updates the database tables to a new schema, but without displaying results.
        protected function _make_db_current_silent( $tables = 'all' ){return '';}//3102
        //@description Creates a site theme from an existing theme.
        protected function _make_site_theme_from_oldschool( $theme_name, $template ){return '';}//3117
        //@description Creates a site theme from the default theme.
        protected function _make_site_theme_from_default( $theme_name, $template ){return '';}//3210
        //@description Creates a site theme.
        protected function _make_site_theme(){return '';}//3285
    }
}else die;
