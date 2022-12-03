<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-5-2022
 * Time: 13:11
 */
namespace TP_Core\Libs\PHP\Mailer;
if(ABSPATH){
    class Mailer_Exception extends \Exception{
        public function errorMessage():string{
            return '<strong>' . htmlspecialchars($this->getMessage(), ENT_COMPAT | ENT_HTML401) . "</strong><br />\n";
        }
    }
}else die;
