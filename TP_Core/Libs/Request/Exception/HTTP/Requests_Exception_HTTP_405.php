<?php
/**
 * Exception for 405 Method Not Allowed responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_405 extends Requests_Exception_HTTP {
        protected $_code = 405;
        protected $_reason = 'Method Not Allowed';
    }
}else die;