<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-4-2022
 * Time: 18:50
 */
namespace TP_Core\Libs\HTTP;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Libs\Request\Requests_Response;
use TP_Core\Libs\Request\Utility\Requests_Utility_CaseInsensitiveDictionary;
use TP_Core\Libs\Request\Response\Requests_Response_Headers;
if(ABSPATH){
    class TP_HTTP_Requests_Response{
        use _methods_04, _methods_12;
        protected $_filename;
        protected $_response;
        public function __construct( Requests_Response $response, $filename = '' ) {
            $this->_response = $response;
            $this->_filename = $filename;
        }//42
        public function get_response_object(): Requests_Response{
            return $this->_response;
        }//54
        public function get_headers(): Requests_Utility_CaseInsensitiveDictionary{
            $converted = new Requests_Utility_CaseInsensitiveDictionary();
            foreach ( $this->_response->headers->getAll() as $key => $value ) {
                if ( count( $value ) === 1 ) $converted[ $key ] = $value[0];
                else $converted[ $key ] = $value;
            }
            return $converted;
        }//65
        public function set_headers( $headers ): void{
            $this->_response->headers = new Requests_Response_Headers( $headers );
        }//87
        public function header( $key, $value, $replace = true ): void{
            if ( $replace ) unset( $this->_response->headers[ $key ] );
            $this->_response->headers[ $key ] = $value;
        }//101
        public function get_status(): bool{
            return $this->_response->status_code;
        }//116
        public function set_status( $code ): void {
            $this->_response->status_code = $this->_abs_int( $code );
        }//127
        public function get_data(): string {
            return $this->_response->body;
        }//138
        public function set_data( $data ): void {
            $this->_response->body = $data;
        }//149
        public function get_cookies():array {
            $cookies = array();
            foreach ( $this->_response->cookies as $cookie ) {
                $cookies[] = new TP_Http_Cookie(
                    array(
                        'name'      => $cookie->name,
                        'value'     => urldecode( $cookie->value ),
                        'expires'   => $cookie->attributes['expires'] ?? null,
                        'path'      => $cookie->attributes['path'] ?? null,
                        'domain'    => $cookie->attributes['domain'] ?? null,
                        'host_only' => $cookie->flags['host-only'] ?? null,
                    )
                );
            }
            return $cookies;
        }//160
        public function to_array():array {
            return array(
                'headers'  => $this->get_headers(),
                'body'     => $this->get_data(),
                'response' => array(
                    'code'    => $this->get_status(),
                    'message' => $this->_get_status_header_desc( $this->get_status() ),
                ),
                'cookies'  => $this->get_cookies(),
                'filename' => $this->_filename,
            );
        }//185
    }
}else die;