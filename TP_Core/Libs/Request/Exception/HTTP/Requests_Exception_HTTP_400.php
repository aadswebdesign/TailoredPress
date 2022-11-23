<?php
/**
 * Exception for 400 Bad Request responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_400 extends Requests_Exception_HTTP {
        protected $_code = 400;
        protected $_reason = 'Bad Request';
    }
}else die;