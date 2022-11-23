<?php
/**
 * Exception for 416 Requested Range Not Satisfiable responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_416 extends Requests_Exception_HTTP {
        protected $_code = 416;
        protected $_reason = 'Requested Range Not Satisfiable';
    }
}else die;