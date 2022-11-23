<?php
/**
 * Exception for 408 Request Timeout responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_408 extends Requests_Exception_HTTP {
        protected $_code = 408;
        protected $_reason = 'Request Timeout';
    }
}else die;