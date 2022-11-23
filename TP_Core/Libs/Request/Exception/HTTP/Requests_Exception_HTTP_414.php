<?php
/**
 * Exception for 414 Request-URI Too Large responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_414 extends Requests_Exception_HTTP {
        protected $_code = 414;
        protected $_reason = 'Request-URI Too Large';
    }
}else die;