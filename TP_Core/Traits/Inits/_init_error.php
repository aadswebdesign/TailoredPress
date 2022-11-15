<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-4-2022
 * Time: 13:15
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _init_error{
        use _action_01;
        protected $_tp_error,$_tp_errors;
        protected function _init_error( $thing = '' ): TP_Error{
            if(!($this->_tp_error instanceof TP_Error))
                $this->_tp_error = new TP_Error($thing);
            if($this->_tp_error instanceof TP_Error)
                $this->_do_action( 'init_error_instance', $thing );
            return $this->_tp_error;
        }
    }
}else die;