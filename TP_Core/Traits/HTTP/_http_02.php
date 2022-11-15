<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 21:55
 */
namespace TP_Core\Traits\HTTP;
use TP_Core\Traits\Inits\_init_http;
if(ABSPATH){
    trait _http_02 {
        use _init_http;
        /**
         * @description Retrieve a single header by name from the raw response.
         * @param $response
         * @param $header
         * @return string
         */
        protected function _tp_remote_retrieve_header( $response, $header ):string{
            if ( $this->_init_error( $response ) || ! isset( $response['headers'] ) )
                return '';
            if ( isset( $response['headers'][ $header ] ) )
                return $response['headers'][ $header ];
            return '';
        }//228
        /**
         * @description Retrieve only the response code from the raw response.
         * @param $response
         * @return string
         */
        protected function _tp_remote_retrieve_response_code( $response ):string{
            if ( $this->_init_error( $response ) || ! isset( $response['response'] ) || ! is_array( $response['response'] ) )
                return '';
            return $response['response']['code'];
        }//250
        /**
         * @description Retrieve only the response message from the raw response.
         * @param $response
         * @return string
         */
        protected function _tp_remote_retrieve_response_message( $response ):string{
            if ( $this->_init_error( $response ) || ! isset( $response['response'] ) || ! is_array( $response['response'] ) )
                return '';
            return $response['response']['message'];
        }//268
        /**
         * @description Retrieve only the body from the raw response.
         * @param $response
         * @return string
         */
        protected function _tp_remote_retrieve_body( $response ):string{
            if ( $this->_init_error( $response ) || ! isset( $response['body'] ) )
                return '';
            return $response['body'];
        }//284
        /**
         * @description Retrieve only the cookies from the raw response.
         * @param $response
         * @return array
         */
        protected function _tp_remote_retrieve_cookies( $response ):array{
            if ( $this->_init_error( $response ) || ! isset( $response['cookies'] ) )
                return [];
            return $response['cookies'];
        }//300
        /**
         * @description Retrieve a single cookie by name from the raw response.
         * @param $response
         * @param $name
         * @return string
         */
        protected function _tp_remote_retrieve_cookie( $response, $name ):string{
            $cookies = $this->_tp_remote_retrieve_cookies( $response );
            if ( empty( $cookies ) ) return '';
            foreach ( $cookies as $cookie ) {
                if ( $cookie->name === $name ) return $cookie;
            }
            return '';
        }//317
        /**
         * @description Retrieve a single cookie's value by name from the raw response.
         * @param $response
         * @param $name
         * @return string
         */
        protected function _tp_remote_retrieve_cookie_value( $response, $name ):string{
            $cookie = $this->_tp_remote_retrieve_cookie( $response, $name );
            if ( ! is_a( $cookie, 'TP_Http_Cookie' ) )
                return '';
            return $cookie['value'];
        }//342
        /**
         * @description Determines if there is an HTTP Transport that can process this request.
         * @param array $capabilities
         * @param null $url
         * @return bool
         */
        protected function _tp_http_supports( $capabilities = array(), $url = null ):bool{
            $http = $this->_init_http();
            $capabilities = $this->_tp_parse_args( $capabilities );
            $count = count( $capabilities );
            if ( $count && count( array_filter( array_keys( $capabilities ), 'is_numeric' ) ) === $count )
                $capabilities = array_combine( array_values( $capabilities ), array_fill( 0, $count, true ) );
            if ( $url && ! isset( $capabilities['ssl'] ) ) {
                $scheme = parse_url( $url, PHP_URL_SCHEME );
                if ( 'https' === $scheme || 'ssl' === $scheme )
                    $capabilities['ssl'] = true;
            }
            return (bool) $http->get_first_available_transport( $capabilities );
        }//363
        /**
         * @description Get the HTTP Origin of the current request.
         * @return mixed
         */
        protected function _get_http_origin(){
            $origin = '';
            if ( ! empty( $_SERVER['HTTP_ORIGIN'] ) ) $origin = $_SERVER['HTTP_ORIGIN'];
            return $this->_apply_filters( 'http_origin', $origin );
        }//392
        /**
         * @description Retrieve list of allowed HTTP origins.
         * @return mixed
         */
        protected function _get_allowed_http_origins(){
            $admin_origin = parse_url( $this->_admin_url() );
            $home_origin  = parse_url( $this->_home_url() );
            // @todo Preserve port? and stick to https and leave http out
            $allowed_origins = array_unique(
                array(
                    'http://' . $admin_origin['host'],
                    'https://' . $admin_origin['host'],
                    'http://' . $home_origin['host'],
                    'https://' . $home_origin['host'],
                )
            );
            return $this->_apply_filters( 'allowed_http_origins', $allowed_origins );
        }//415
    }
}else die;