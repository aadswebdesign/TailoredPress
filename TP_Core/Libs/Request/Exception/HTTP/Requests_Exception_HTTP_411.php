<?php
/**
 * Exception for 411 Length Required responses
 *
 * @package Requests
 */
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_411 extends Requests_Exception_HTTP {
        protected $_code = 411;
        protected $_reason = 'Length Required';
    }
}else die;