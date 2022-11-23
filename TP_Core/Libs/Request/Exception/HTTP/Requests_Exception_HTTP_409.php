<?php
/**
 * Exception for 409 Conflict responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_409 extends Requests_Exception_HTTP {
        protected $_code = 409;
        protected $_reason = 'Conflict';
    }
}else die;