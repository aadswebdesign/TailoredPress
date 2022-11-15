<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-5-2022
 * Time: 12:57
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\PHP\Mailer\PHPMailer;
if(ABSPATH){
    trait _init_mailer{
        protected $_tp_mailer;
        protected function _init_mailer():PHPMailer{
            if(!($this->_tp_mailer instanceof PHPMailer))
                $this->_tp_mailer = new PHPMailer();
            return $this->_tp_mailer;
        }
    }
}else die;