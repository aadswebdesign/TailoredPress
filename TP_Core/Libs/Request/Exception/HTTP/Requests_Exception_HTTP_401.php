<?php
/**
 * Exception for 401 Unauthorized responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_401 extends Requests_Exception_HTTP {
        protected $_code = 401;
        protected $_reason = 'Unauthorized';
    }
}else die;