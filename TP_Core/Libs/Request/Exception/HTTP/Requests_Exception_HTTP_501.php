<?php
/**
 * Exception for 501 Not Implemented responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_501 extends Requests_Exception_HTTP {
        protected $_code = 501;
        protected $_reason = 'Not Implemented';
    }
}else die;