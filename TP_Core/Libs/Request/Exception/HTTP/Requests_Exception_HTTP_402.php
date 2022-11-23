<?php
/**
 * Exception for 402 Payment Required responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_402 extends Requests_Exception_HTTP {
        protected $_code = 402;
        protected $_reason = 'Payment Required';
    }
}else die;