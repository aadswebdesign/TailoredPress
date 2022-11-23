<?php
/**
 * Exception for 410 Gone responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_410 extends Requests_Exception_HTTP {
        protected $_code = 410;
        protected $_reason = 'Gone';
    }
}else die;