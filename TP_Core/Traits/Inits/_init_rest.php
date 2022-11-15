<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-5-2022
 * Time: 13:55
 */

namespace TP_Core\Traits\Inits;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Controller;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\RestApi\TP_REST_Server;
if(ABSPATH){
    trait _init_rest{

        protected $_tp_rest_controller;
        protected $_tp_rest_server;
        protected $_tp_rest_request;
        protected function _init_rest_controller():TP_REST_Controller{
            if(!($this->_tp_rest_controller instanceof TP_REST_Controller))
                $this->_tp_rest_controller = new TP_REST_Controller();
            return $this->_tp_rest_controller;
        }
        protected function _init_rest_server():TP_REST_Server{
            if(!($this->_tp_rest_server instanceof TP_REST_Server))
                $this->_tp_rest_server = new TP_REST_Server();
            return $this->_tp_rest_server;
        }
        protected function _init_rest_request($request):TP_REST_Request{
            if(!($this->_tp_rest_request instanceof TP_REST_Request)|| is_string( $request))
                $this->_tp_rest_request = new TP_REST_Request(TP_GET,$request);
            return $this->_tp_rest_request;
        }
    }
}else die;