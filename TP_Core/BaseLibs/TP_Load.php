<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-8-2022
 * Time: 13:25
 */
namespace TP_Core\BaseLibs;
use \TP_Config;
/** @Load */
use TP_Core\Traits\Load\_load_01;
use TP_Core\Traits\Load\_load_02;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Load\_load_05;
/** @Methods */
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Methods\_methods_02;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_05;
use TP_Core\Traits\Methods\_methods_06;
use TP_Core\Traits\Methods\_methods_07;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Methods\_methods_09;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_11;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Methods\_methods_13;
use TP_Core\Traits\Methods\_methods_14;
use TP_Core\Traits\Methods\_methods_15;
use TP_Core\Traits\Methods\_methods_16;
use TP_Core\Traits\Methods\_methods_17;
use TP_Core\Traits\Methods\_methods_18;
use TP_Core\Traits\Methods\_methods_19;
use TP_Core\Traits\Methods\_methods_20;
use TP_Core\Traits\Methods\_methods_21;



if(ABSPATH){
    class TP_Load{
        /** @Load */
        use _load_01,_load_02,_load_03,_load_04,_load_05;
        /** @Methods */
        use _methods_01,_methods_02,_methods_03,_methods_04,_methods_05,_methods_06,_methods_07;
        use _methods_08,_methods_09,_methods_10,_methods_11,_methods_12,_methods_13,_methods_14;
        use _methods_15,_methods_16,_methods_17,_methods_18,_methods_19,_methods_20,_methods_21;






        public function __construct(){
            $this->__tp_load();
        }
        private function __tp_load():void{
            if ( ! defined( 'ABSPATH' ) ) {
                define( 'ABSPATH', __DIR__ . '/' );
            }
            echo ABSPATH .' (from TP_Load!)<br/>';
            $this->__error_reporting();
            new TP_Config();
            $this->_tp_fix_server_vars();






        }
        private function __error_reporting():void{ //todo lookup load
            error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
        }






    }
}else{die;}