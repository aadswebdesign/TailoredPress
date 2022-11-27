<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 29-5-2022
 * Time: 08:37
 */
namespace TP_Admin\Traits\AdminUpgrade;
if(ABSPATH){
    trait _adm_upgrade_03{
        //@description Translate user level to user role name.
        protected function _translate_level_to_role( $level ){return '';}//3335
        //@description Checks the version of the installed MySQL binary.
        protected function _tp_check_mysql_version(){return '';}//3364
        //@description Disables the Link Manager on upgrade if, at the time of upgrade, no links exist in the DB.
        protected function _maybe_disable_link_manager(){return '';}//3397
        //@description Runs before the schema is upgraded.
        protected function _pre_schema_upgrade(){return '';}//3413
        //@description Install global terms.
        protected function _install_global_terms(){return '';}//3476
        //@description Determine if global tables should be upgraded.
        protected function _tp_should_upgrade_global_tables(){return '';}//3512
        //@description
        //protected function _{return '';}//
    }
}else die;
