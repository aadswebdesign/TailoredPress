<?php
/**
 * Exception for 500 Internal Server Error responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_500 extends Requests_Exception_HTTP {
        protected $_code = 500;
        protected $_reason = 'Internal Server Error';
    }
}else die;