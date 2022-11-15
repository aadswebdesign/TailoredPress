<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-5-2022
 * Time: 01:01
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_XMLRPC_Server;
if(ABSPATH){
    trait _init_xmlrpc_server{
        protected $_tp_xmlrpc_server;
        protected function _init_xmlrpc_server():TP_XMLRPC_Server{
            if(!($this->_tp_xmlrpc_server instanceof TP_XMLRPC_Server))
                $this->_tp_xmlrpc_server = new TP_XMLRPC_Server();
            return $this->_tp_xmlrpc_server;
        }
    }
}else die;