<?php
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_305 extends Requests_Exception_HTTP {
        protected $_code = 305;
        protected $_reason = 'Use Proxy';
    }
}else die;

