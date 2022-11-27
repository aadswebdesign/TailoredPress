<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 29-8-2022
 * Time: 17:41
 */
namespace TP_Admin\Traits\AdminUpdate;
if(ABSPATH){
    trait _adm_update_01{
        //@description Selects the first update version from the update_core option.
        public function get_preferred_from_update_core(){return '';}//16
        //@description Gets available core updates
        public function get_core_updates( $options = [] ){return '';}//36
        //@description Gets the best available (and enabled) Auto-Update for TailoredPress core.
        public function find_core_auto_update(){return '';}//87
        //@descriptionGets and caches the checksums for the given version of TailoredPress.
        public function get_core_checksums( $version, $locale ){return '';}//122
        //@description Dismisses core update.
        public function dismiss_core_update( $update ){return '';}//170
        //@description Un-dismisses core update.
        public function un_dismiss_core_update( $version, $locale ){return '';}//185
        //@description Finds the available update for TailoredPress core.
        public function find_core_update( $version, $locale ){return '';}//206
        //@description
        public function core_update_footer( $msg = '' ){return '';}//228
        //@description
        public function update_nag(){return '';}//283
        //@description Displays TailoredPress version and active theme in the 'At a Glance' dashboard widget.
        public function get_update_right_now_message(){return '';}//332
        public function update_right_now_message(){}//332
    }
}else{die;}