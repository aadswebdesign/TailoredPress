<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 18-4-2022
 * Time: 08:57
 */
namespace TP_Core\Libs\HTTP;
use TP_Core\Traits\Methods\_methods_12;
if(ABSPATH){
    class TP_HTTP_Response{
        use _methods_12;
        public $data;
        public $headers;
        public $status;
        public function __construct( $data = null, $status = 200, $headers = array() ) {
            $this->set_data( $data );
            $this->set_status( $status );
            $this->set_headers( $headers );
        }
        public function get_headers() {
            return $this->headers;
        }
        public function set_headers( $headers ):void {
            $this->headers = $headers;
        }
        public function header( $key, $value, $replace = true ):void {
            if ( $replace || ! isset( $this->headers[ $key ] ) ) $this->headers[ $key ] = $value;
            else $this->headers[ $key ] .= ', ' . $value;
        }
        public function get_status() {
            return $this->status;
        }
        public function set_status( $code ) :void{
            $this->status = $this->_abs_int( $code );
        }
        public function get_data() {
            return $this->data;
        }
        public function set_data( $data ):void {
            $this->data = $data;
        }
        public function jsonSerialize() {
            return $this->get_data();
        }
    }
}else die;