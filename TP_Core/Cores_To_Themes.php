<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-9-2022
 * Time: 08:44
 */
namespace TP_Core;

if(ABSPATH){
    class Cores_To_Themes extends Cores {
        /** @description uses from the Core Directory */

        /** @description uses from the Admin Directory */

        public function __construct(){
            parent::__construct();
            $fake = 'fake';
            var_dump($fake);
            /** @description from the Core Directory */
            /** @description from the Admin Directory */

        }


    }
}else{die;}