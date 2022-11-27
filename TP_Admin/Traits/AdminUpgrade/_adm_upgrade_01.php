<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 29-5-2022
 * Time: 08:37
 */
namespace TP_Admin\Traits\AdminUpgrade;
if(ABSPATH){
    trait _adm_upgrade_01{
        //@description Installs the site.
        protected function _tp_install( $blog_title, $user_name, $user_email, $public, $deprecated = '', $user_password = '', $language = '' ){return '';}//47
        //@description Creates the initial content for a newly-installed site.
        protected function _tp_install_defaults( $user_id ){return '';}//154
        //@description Maybe enable pretty permalinks on installation.
        protected function _tp_install_maybe_enable_pretty_permalinks(){return '';}//482
        //@description Notifies the site admin that the installation of TailoredPress is complete.
        protected function _tp_new_blog_notification( $blog_title, $blog_url, $user_id, $password ){return '';}//561
        //@description Executes changes made in WordPress 5.9.0. to be integrated below
        //@description Runs TailoredPress Upgrade functions.
        protected function _tp_upgrade(){return '';}//641
        //@description Functions to be called in installation and upgrade scripts.
        protected function _upgrade_all(){return '';}//694
        //@description Executes network-level upgrade routines.
        protected function _upgrade_network(){return '';}//2289
        //@description Creates a table in the database, if it doesn't already exist.
        protected function _maybe_create_table( $table_name, $create_ddl ){return '';}//2452
        //@description Drops a specified index from a table.
        protected function _drop_index( $table, $index ){return '';}//2483
        //@description Adds an index to a specified table.
        protected function _add_clean_index( $table, $index ){return '';}//2511
    }
}else die;
