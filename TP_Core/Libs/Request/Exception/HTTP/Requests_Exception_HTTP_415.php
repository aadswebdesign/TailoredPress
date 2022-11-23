<?php
/**
 * Exception for 415 Unsupported Media Type responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_415 extends Requests_Exception_HTTP {
        protected $_code = 415;
        protected $_reason = 'Unsupported Media Type';
    }
}else die;