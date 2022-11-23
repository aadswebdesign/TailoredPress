<?php
/**
 * Exception for 406 Not Acceptable responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_406 extends Requests_Exception_HTTP {
        protected $_code = 406;
        protected $_reason = 'Not Acceptable';
    }
}else die;