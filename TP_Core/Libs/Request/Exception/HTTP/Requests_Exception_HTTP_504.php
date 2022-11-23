<?php
/**
 * Exception for 504 Gateway Timeout responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_504 extends Requests_Exception_HTTP {
        protected $_code = 504;
        protected $_reason = 'Gateway Timeout';
    }
}else die;