<?php
/**
 * Exception for 403 Forbidden responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_403 extends Requests_Exception_HTTP {
        protected $_code = 403;
        protected $_reason = 'Forbidden';
    }
}else die;