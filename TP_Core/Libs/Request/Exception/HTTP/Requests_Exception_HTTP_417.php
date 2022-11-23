<?php
/**
 * Exception for 417 Expectation Failed responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_417 extends Requests_Exception_HTTP {
        protected $_code = 417;
        protected $_reason = 'Expectation Failed';
    }
}else die;