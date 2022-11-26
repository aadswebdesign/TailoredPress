<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-5-2022
 * Time: 07:39
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\HTTP\_http_01;
use TP_Core\Traits\HTTP\_http_02;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    class TP_SimplePie_File extends SimplePie_File{
        use _http_01;
        use _http_02;
        use _init_error;
        public function __construct( $url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false ) {
            parent::__construct($url, $timeout, $redirects, $headers, $useragent, $force_fsockopen);
            $this->sp_url       = $url;
            $this->sp_timeout   = $timeout;
            $this->sp_redirects = $redirects;
            $this->sp_headers   = $headers;
            $this->sp_useragent = $useragent;
            $this->sp_method = SP_FILE_SOURCE_REMOTE;
            if ( preg_match( '/^http(s)?:\/\//i', $url ) ) {
                $args = ['timeout' => $this->sp_timeout,'redirection' => $this->sp_redirects,];
                if ( ! empty( $this->sp_headers ) ) $args['headers'] = $this->sp_headers;
                if ( SP_USERAGENT !== $this->sp_useragent )
                    $args['user-agent'] = $this->sp_useragent;
                $_res = $this->_tp_safe_remote_request( $url, $args );
                $res = null;
                if( $_res instanceof TP_Error ){
                    $res = $_res;
                }
                if ( $this->_init_error( $res ) ) {
                    $this->sp_error   = 'TP HTTP Error: ' . $res->get_error_message();
                    $this->sp_success = false;
                } else {
                    $this->sp_headers = $this->_tp_remote_retrieve_headers( $res );
                    foreach ( $this->sp_headers as $name => $value ) {
                        if ( ! is_array( $value ) ) continue;
                        if ( 'content-type' === $name ) $this->sp_headers[ $name ] = array_pop( $value );
                        else $this->sp_headers[ $name ] = implode( ', ', $value );
                    }
                    $this->sp_body        = $this->_tp_remote_retrieve_body( $res );
                    $this->sp_status_code = $this->_tp_remote_retrieve_response_code( $res );
                }
            } else {
                $this->sp_error   = '';
                $this->sp_success = false;
            }
        }
    }
}else die;