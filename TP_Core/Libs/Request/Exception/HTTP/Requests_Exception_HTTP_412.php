<?php
/**
 * Exception for 412 Precondition Failed responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_412 extends Requests_Exception_HTTP {
        protected $_code = 412;
        protected $_reason = 'Precondition Failed';
    }
}else die;