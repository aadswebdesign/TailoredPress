<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-10-2022
 * Time: 10:19
 */
namespace TP_Admin\Libs\AdmComponents;
use TP_Admin\Libs\Adm_User_Edit;
if(ABSPATH){
    class Adm_Profile{
        public static function get_profile():string{
            return new Adm_User_Edit();
        }

    }
}else{die;}

