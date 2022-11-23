<?php
namespace TP_Core\Libs\Request\Exception\HTTP;
use TP_Core\Libs\Request\Exception\Requests_Exception_HTTP;
if(ABSPATH){
    class Requests_Exception_HTTP_306 extends Requests_Exception_HTTP {
        protected $_code = 306;
        protected $_reason = 'Switch Proxy';
    }
}else die;