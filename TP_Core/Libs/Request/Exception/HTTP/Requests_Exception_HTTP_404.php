<?php
/**
 * Exception for 404 Not Found responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_404 extends Requests_Exception_HTTP {
        protected $_code = 404;
        protected $_reason = 'Not Found';
    }
}else die;