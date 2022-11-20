<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-4-2022
 * Time: 16:33
 */
namespace TP_Core\Libs\HTTP;
use TP_Core\Libs\Request\Requests_Hooks;
use TP_Core\Traits\Actions\_action_01;
if(ABSPATH){
    class TP_HTTP_Requests_Hooks extends Requests_Hooks{
        use _action_01;
        protected $_request = array();
        protected $_url;
        public function __construct( $url, $request ) {
            $this->_url     = $url;
            $this->_request = $request;
        }//38
        /**
         * @param $hook
         * @param $parameters
         * @return mixed
         */
        public function dispatch( $hook, $parameters){
            $result = parent::dispatch( $hook, $parameters );
            $this->_do_action_ref_array( "requests-{$hook}", $parameters, $this->_request, $this->_url );
            return $result;
        }//50
    }
}else die;