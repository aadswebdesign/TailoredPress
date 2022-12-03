<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 21:55
 */
namespace TP_Core\Traits\HTTP;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_http;
if(ABSPATH){
    trait _http_01 {
        use _init_error;
        use _init_http;
        /**
         * @description Returns the initialized TP_Http Object
         * not needed, using $this->_init_http() instead
         */
        //protected function _tp_http_get_object():TP_Http{}//20
        /**
         * @description Retrieve the raw response from a safe HTTP request.
         * @param $url
         * @param array $args
         * @return string|\TP_Core\Libs\\TP_Error
         */
        protected function _tp_safe_remote_request( $url, $args = [] ){
            $args['reject_unsafe_urls'] = true;
            return $this->_init_http()->request( $url, $args );
        }//44
        /**
         *  @description Retrieve the raw response from a safe HTTP request using the GET method.
         * @param $url
         * @param array $args
         * @return string|TP_Error
         */
        protected function _tp_safe_remote_get( $url, $args = [] ){
            return $this->_tp_safe_remote_request($url, $args);
        }//65
        /**
         * @description Retrieve the raw response from a safe HTTP request using the POST method.
         * @param $url
         * @param array $args
         * @return string|TP_Error
         */
        protected function _tp_safe_remote_post( $url, $args = [] ){
            return $this->_tp_safe_remote_request($url, $args);
        }//86
        /**
         * @description Retrieve the raw response from a safe HTTP request using the HEAD method.
         * @param $url
         * @param array $args
         * @return string|TP_Error
         */
        protected function _tp_safe_remote_head( $url, $args = [] ){
            return $this->_tp_safe_remote_request($url, $args);
        }//107
        /**
         * @description Performs an HTTP request and returns its response.
         * @param $url
         * @param array $args
         * @return string|TP_Error
         */
        protected function _tp_remote_request( $url, $args = [] ){
            return $this->_init_http()->request( $url, $args );
        }//143
        /**
         * @description Performs an HTTP request using the GET method and returns its response.
         * @param $url
         * @param array $args
         * @return string|object
         */
        protected function _tp_remote_get( $url, $args = [] ){
            return $this->_init_http()->get( $url, $args );
        }//160
        /**
         * @description Performs an HTTP request using the POST method and returns its response.
         * @param $url
         * @param array $args
         * @return mixed
         */
        protected function _tp_remote_post( $url, $args = [] ){
            return $this->_init_http()->post( $url, $args );
        }//177
        /**
         * @description Performs an HTTP request using the HEAD method and returns its response.
         * @param $url
         * @param array $args
         * @return string
         */
        protected function _tp_remote_head( $url, $args = [] ):string{
            return $this->_init_http()->head( $url, $args );
        }//194
        /**
         * @description Retrieve only the headers from the raw response.
         * @param $response
         * @return array
         */
        protected function _tp_remote_retrieve_headers( $response ):array{
            if ( $this->_init_error( $response ) || ! isset( $response['headers'] ) )return array();
            return $response['headers'];
        }//210
    }
}else die;