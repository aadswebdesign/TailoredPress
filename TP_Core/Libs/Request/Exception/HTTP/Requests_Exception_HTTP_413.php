<?php
/**
 * Exception for 413 Request Entity Too Large responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_413 extends Requests_Exception_HTTP {
        protected $_code = 413;
        protected $_reason = 'Request Entity Too Large';
    }
}else die;