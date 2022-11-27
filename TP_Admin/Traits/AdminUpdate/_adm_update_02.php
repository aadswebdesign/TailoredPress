<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 29-8-2022
 * Time: 17:41
 */
namespace TP_Admin\Traits\AdminUpdate;
if(ABSPATH){
    trait _adm_update_02{
        //@description
        public function get_theme_updates(){return '';}//592
        //@description
        public function tp_theme_update_rows(){return '';}//611
        //@description Displays update information for a theme.
        public function tp_theme_update_row( $theme_key, $theme ){return '';}//635
        //@description
        public function maintenance_nag(){return '';}//809
        //@description Prints the JavaScript templates for update admin notices.
        public function tp_print_admin_notice_templates(){return '';}//865
        //@description Prints the JavaScript templates for update and deletion rows in list tables.
        public function tp_print_update_row_templates(){return '';}//957
        //@description Displays a notice when the user is in recovery mode.
        public function tp_recovery_mode_nag(){return '';}//997
        //@description Checks whether auto-updates are enabled.
        public function tp_is_auto_update_enabled_for_type( $type ){return '';}//1022
        //@description Checks whether auto-updates are forced for an item.
        public function tp_is_auto_update_forced_for_item( $type, $update, $item ){return '';}//1072
        //@description Determines the appropriate auto-update message to be displayed.
        public function tp_get_auto_update_message(){return '';}//1084
    }
}else{die;}