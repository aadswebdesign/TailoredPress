<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 18-4-2022
 * Time: 06:34
 */
namespace TP_Core\Libs\HTTP;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\HTTP\_http_01;
use TP_Core\Traits\HTTP\_http_02;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\IXR\IXR_Client;
use TP_Core\Libs\IXR\IXR_Error;
use TP_Core\Libs\IXR\IXR_Message;
use TP_Core\Libs\IXR\IXR_Request;
if(ABSPATH){
    class TP_HTTP_IXR_Client extends IXR_Client{
        use _filter_01;
        use _http_01,_http_02;
        use _init_error;
        protected $_error;
        protected $_scheme;
        public function __construct( $server, $path = false, $port = false, $timeout = 15 ) {
            parent::__construct($server, $path, $port, $timeout);
            if ( ! $path ) {
                // Assume we have been given a URL instead.
                $bits         = parse_url( $server );
                $this->_scheme = $bits['scheme'];
                $this->server = $bits['host'];
                $this->port   = $bits['port'] ?? $port;
                $this->path   = ! empty( $bits['path'] ) ? $bits['path'] : '/';
                // Make absolutely sure we have a path.
                if ( ! $this->path ) $this->path = '/';
                if ( ! empty( $bits['query'] ) ) $this->path .= '?' . $bits['query'];
            } else {
                $this->_scheme = 'http';
                $this->server = $server;
                $this->path   = $path;
                $this->port   = $port;
            }
            $this->user_agent = 'The Incutio XML-RPC PHP Library';
            $this->timeout   = $timeout;
        }//21
        public function query( ...$args ):string {
            $method  = array_shift( $args );
            $request = new IXR_Request( $method, $args );
            $xml     = $request->getXml();
            $port = $this->port ? ":$this->port" : '';
            $url  = $this->_scheme . '://' . $this->server . $port . $this->path;
            $args = array(
                'headers'    => array( 'Content-Type' => 'text/xml' ),
                'user-agent' => $this->user_agent,
                'body'       => $xml,
            );
            // Merge Custom headers ala #8145.
            foreach ( $this->headers as $header => $value ) {
                $args['headers'][ $header ] = $value;
            }
            /**
             * Filters the headers collection to be sent to the XML-RPC server.
             *
             * @since 4.4.0
             *
             * @param string[] $headers Associative array of headers to be sent.
             */
            $args['headers'] = $this->_apply_filters( 'tp_http_ixr_client_headers', $args['headers'] );
            if ( false !== $this->timeout ) $args['timeout'] = $this->timeout;
            // Now send the request.
            if ( $this->debug )
                echo '<pre class="ixr_request">' . htmlspecialchars( $xml ) . "\n</pre>\n\n";
            $response = $this->_tp_remote_post( $url, $args );
            if ( $this->_init_error( $response ) ) {
                $response_block = $response ?: $this->_init_error($response);
                $err_no       = $response_block->get_error_code();
                $error_str    = $response_block->get_error_message();
                $this->_error = new IXR_Error( -32300, "transport error: $err_no $error_str" );
                return false;
            }
            if ( 200 !== $this->_tp_remote_retrieve_response_code( $response ) ) {
                $this->_error = new IXR_Error( -32301, 'transport error - HTTP status code was not 200 (' . $this->_tp_remote_retrieve_response_code( $response ) . ')' );
                return false;
            }
            if ( $this->debug )
                echo '<pre class="ixr-response">' . htmlspecialchars( $this->_tp_remote_retrieve_body( $response ) ) . "\n</pre>\n\n";
            $this->message = new IXR_Message( $this->_tp_remote_retrieve_body( $response ) );
            if ( ! $this->message->parse_messages() ) {
                // XML error.
                $this->_error = new IXR_Error( -32700, 'parse error. not well formed' );
                return false;
            }
            if ( 'fault' === $this->message->message_type ) {
                $this->_error = new IXR_Error( $this->message->fault_code, $this->message->fault_string );
                return false;
            }
            return true;
        }
    }
}else die;