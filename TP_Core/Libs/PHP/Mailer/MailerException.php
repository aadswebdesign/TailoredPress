<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 16:44
 */
namespace TP_Core\Libs\PHP\Mailer;
if(ABSPATH){
    class MailerException extends \Exception{
        public function errorMessage():string{
            return '<strong>' . htmlspecialchars($this->getMessage(), ENT_COMPAT | ENT_HTML401) . "</strong><br />\n";
        }
    }
}else die;