<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
use TP_Core\Traits\Feed\Components\feed_rdf;
use TP_Core\Traits\Inits\_init_core;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _methods_04{
        use _init_core;
        use _init_db;
        use _init_queries;
        /**
         * @description Set up the TailoredPress query.
         * @param string $query_vars
         */
        protected function _tp_method( $query_vars = '' ):void{
            $this->tp_query = $this->_init_query();
            $this->tp_core = $this->_init_core();
            $this->tp_core->main( $query_vars );
        }//1307 original _tp_core
        /**
         * @description Retrieve the description for the HTTP status.
         * @param $code
         * @return mixed
         */
        protected function _get_status_header_desc( $code ){
            $code = $this->_abs_int( $code );
            if(!isset($this->tp_header_to_desc))
            $this->tp_header_to_desc = [
                100 => 'Continue',101 => 'Switching Protocols',102 => 'Processing',103 => 'Early Hints',
                200 => 'OK',201 => 'Created',202 => 'Accepted',203 => 'Non-Authoritative Information',
                204 => 'No Content',205 => 'Reset Content',206 => 'Partial Content',207 => 'Multi-Status',226 => 'IM Used',
                300 => 'Multiple Choices',301 => 'Moved Permanently',302 => 'Found',303 => 'See Other',304 => 'Not Modified',
                305 => 'Use Proxy',306 => 'Reserved',307 => 'Temporary Redirect',308 => 'Permanent Redirect',
                400 => 'Bad Request',401 => 'Unauthorized',402 => 'Payment Required',403 => 'Forbidden',404 => 'Not Found',
                405 => 'Method Not Allowed',406 => 'Not Acceptable',407 => 'Proxy Authentication Required',408 => 'Request Timeout',409 => 'Conflict',
                410 => 'Gone',411 => 'Length Required',412 => 'Precondition Failed',413 => 'Request Entity Too Large',414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',416 => 'Requested Range Not Satisfiable',417 => 'Expectation Failed',418 => 'I\'m a teapot',421 => 'Misdirected Request',
                422 => 'Un-processable Entity',423 => 'Locked',424 => 'Failed Dependency',426 => 'Upgrade Required',428 => 'Precondition Required',
                429 => 'Too Many Requests',431 => 'Request Header Fields Too Large',451 => 'Unavailable For Legal Reasons',
                500 => 'Internal Server Error',501 => 'Not Implemented', 502 => 'Bad Gateway',503 => 'Service Unavailable',
                504 => 'Gateway Timeout',505 => 'HTTP Version Not Supported',506 => 'Variant Also Negotiates',
                507 => 'Insufficient Storage',510 => 'Not Extended',511 => 'Network Authentication Required',
            ];
            if (isset( $this->tp_header_to_desc[ $code ]))
                return $this->tp_header_to_desc[ $code ];
            else return '';
        }//1330
        /**
         * @description Set HTTP status header.
         * @param $code
         * @param string $description
         */
        protected function _status_header( $code, $description = '' ):void{
            if ( ! $description ) $description = $this->_get_status_header_desc( $code );
            if ( empty( $description ) ) return;
            $protocol      = $this->_tp_get_server_protocol();
            $status_header = "$protocol $code $description";
            $status_header = $this->_apply_filters( 'status_header', $status_header, $code, $description, $protocol );
            if ( ! headers_sent() ) header( $status_header, true, $code );
        }//1422
        /**
         * @description Get the header information to prevent caching.
         * @return array
         */
        protected function _tp_get_nocache_headers():array{
            $headers = ['Expires'=> 'Wed, 11 Jan 1984 05:00:00 GMT','Cache-Control' => 'no-cache, must-revalidate, max-age=0',];
            $headers = (array) $this->_apply_filters( 'nocache_headers', $headers );
            $headers['Last-Modified'] = false;
            return $headers;
        }//1463
        /**
         * @description Set the headers to prevent caching for the different browsers.
         */
        protected function _nocache_headers(){
            $header_arr = [];
            if ( headers_sent() ) return false;
            $headers = $this->_tp_get_nocache_headers();
            unset( $headers['Last-Modified'] );
            header_remove( 'Last-Modified' );
            foreach ( $headers as $name => $field_value )
                $header_arr = header( "{$name}: {$field_value}" );
            return $header_arr;
        }//1501
        /**
         * @description Set the headers for caching for 10 days with JavaScript content type.
         */
        protected function _cache_javascript_headers():void{
            $expires_offset = 10 * DAY_IN_SECONDS;
            header( 'Content-Type: text/javascript; charset=' . $this->_get_bloginfo( 'charset' ) );
            header( 'Vary: Accept-Encoding' ); // Handle proxies.
            header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + $expires_offset ) . ' GMT' );
        }//1522
        /**
         * @description Retrieve the number of database queries during the TailoredPress execution.
         * @return int
         */
        protected function _get_num_queries():int{
            $this->_tpdb = $this->_init_db();
            return $this->_tpdb->num_queries;
        }//1539
        /**
         * @description  Whether input is yes or no.
         * @param $yes_no
         * @return bool
         */
        protected function _bool_from_yes_no( $yes_no ):bool{
            return ( 'y' === strtolower( $yes_no ) );
        }//1554
        /**
         * @description Load the feed template from the use of an action hook.
         */
        protected function _do_feed():void{
            $tp_query = $this->_init_query();
            $feed = $this->_get_query_var( 'feed' );
            $feed = ltrim($feed, '_');
            if ( '' === $feed || 'feed' === $feed )$feed = $this->_get_default_feed();
            if ( ! $this->_has_action( "do_feed_{$feed}" ) )
                $this->_tp_die( $this->__( 'Error: This is not a valid feed template.' ), '', array( 'response' => 404 ) );
            $this->_do_action( "do_feed_{$feed}", $tp_query->is_comment_feed, $feed );
        }//1570
        /**
         * @description Load the RDF RSS 0.91 Feed template.
         */
        protected function _do_feed_rdf():void{
            echo new feed_rdf();
        }//1614
    }
}else die;