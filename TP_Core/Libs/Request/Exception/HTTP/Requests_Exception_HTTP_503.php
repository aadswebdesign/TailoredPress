<?php
/**
 * Exception for 503 Service Unavailable responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_503 extends Requests_Exception_HTTP {
        protected $_code = 503;
        protected $_reason = 'Service Unavailable';
    }
}else die;