<?php
/**
 * Exception for 502 Bad Gateway responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_502 extends Requests_Exception_HTTP {
        protected $_code = 502;
        protected $_reason = 'Bad Gateway';
    }
}else die;