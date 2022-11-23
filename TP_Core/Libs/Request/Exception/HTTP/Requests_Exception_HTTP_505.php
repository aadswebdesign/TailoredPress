<?php
/**
 * Exception for 505 HTTP Version Not Supported responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_505 extends Requests_Exception_HTTP {
        protected $code = 505;
        protected $reason = 'HTTP Version Not Supported';
    }
}else die;