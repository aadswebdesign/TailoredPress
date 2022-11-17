<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-9-2022
 * Time: 08:34
 */

namespace TP_Core\Traits\Misc;
if(ABSPATH){
    trait _update{
        //@description
        //protected function tp_version_check($force_check = false,...$extra_stats )_{return '';}//26
        //@description
        //protected function _tp_update_themes( ...$extra_stats){return '';}//550
        //@description
        //protected function _tp_maybe_auto_update(){return '';}//724
        //@description
        //protected function _tp_get_translation_updates(){return '';}//739
        //@description
        protected function _tp_get_update_data():string{return '';}//769
        //@description
        //protected function _maybe__update_core(){return '';}//865
        //@description
        //protected function _maybe__update_themes(){return '';}//911
        //@description
        //protected function _tp_schedule_update_checks(){return '';}//928
        //@description
        //protected function _tp_clean_update_cache(){return '';}//947
        //@description
        //protected function _{return '';}//
    }
}else{die;}