<?php
/**
 * Exception for 407 Proxy Authentication Required responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_407 extends Requests_Exception_HTTP {
        protected $_code = 407;
        protected $_reason = 'Proxy Authentication Required';
    }
}else die;