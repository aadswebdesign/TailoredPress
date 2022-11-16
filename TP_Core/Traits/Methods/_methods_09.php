<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_xmlrpc_server;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\IXR\IXR_Error;
if(ABSPATH){
    trait _methods_09{
        use _init_error;
        use _init_xmlrpc_server;
        /**
         * @description Kills TailoredPress execution and displays XML response with an error message.
         * @param $message
         * @param string $title
         * @param array $args
         */
        protected function _tp_xml_rpc_die_handler( $message, $title = '', ...$args):void{
            $tp_xmlrpc_server = $this->_init_xmlrpc_server();
            @list( $message, $title, $parsed_args ) = $this->_tp_die_process_input( $message, $title, $args );
            if ( ! headers_sent() ) $this->_nocache_headers();
            if ( $tp_xmlrpc_server ) {
                $error = new IXR_Error( $parsed_args['response'], $message, $title );
                $tp_xmlrpc_server->output( $error->getXml() );
            }
            if ( $parsed_args['exit'] ) die();
        }//4003 _xmlrpc_wp_die_handler
        /**
         * @description Kills TailoredPress execution and displays XML response,
         * @description . with an error message.
         * @param $message
         * @param string $title
         * @param array ...$args
         * @return string
         */
        protected function _tp_get_xml_die_handler( $message, $title = '', ...$args):string{
            @list( $message, $title, $parsed_args ) = $this->_tp_die_process_input( $message, $title, $args );
            $message = htmlspecialchars( $message );
            $title   = htmlspecialchars( $title );
            $xml = "<error>";
            $xml .= "<code>{$parsed_args['code']}</code>";
            $xml .= "<title>";
            $xml .= TP_CDATA . $title . TP_CDATA_END;
            $xml .= "</title>";
            $xml .= "<message>";
            $xml .= TP_CDATA . $message . TP_CDATA_END;
            $xml .= "</message>";
            $xml .= "<data><status>{$parsed_args['response']}</status></data>";
            $xml .= "</error>";
            if ( ! headers_sent() ) {
                header( "Content-Type: text/xml; charset={$parsed_args['charset']}" );
                if ( null !== $parsed_args['response'] )
                    $this->_status_header( $parsed_args['response'] );
                $this->_nocache_headers();
            }
            if ( $parsed_args['exit'] ) die();
            return $xml;
        }//4033
        public function tp_xml_die_handler( $message, $title = '', ...$args):void{
            echo $this->_tp_get_xml_die_handler( $message, $title,$args);
        }
        /**
         * @description Kills TailoredPress execution and displays an error message.
         * @param string $message
         * @param string $title
         * @param array $args
         */
        protected function _tp_scalar_die_handler( $message = '', $title = '', ...$args ):void{
            @list( $message, $title, $parsed_args ) = $this->_tp_die_process_input( $message, $title, $args );
            if ( $parsed_args['exit'] ) {
                if ( is_scalar( $message ) ) die( (string) $message );
                die();
            }
            $html = (string)$title;
            $html .= $message;
            if ( is_scalar( $message ) ) echo $html;
        }//4078 _scalar_wp_die_handler
        /**
         * @description Processes arguments passed to tp_die() consistently for its handlers.
         * @param $message
         * @param string $title
         * @param array $args
         * @return array
         */
        protected function _tp_die_process_input( $message, $title = '', $args = [] ):array{
            $defaults = [
                'response' => 0, 'code' => '', 'exit' => true, 'back_link' => false, 'link_url' => '',
                'link_text' => '', 'text_direction' => '', 'charset' => 'utf-8', 'additional_errors' => [],
            ];
            $args = $this->_tp_parse_args( $args, $defaults );
            $this->tp_msg = $this->_init_error($message);
            if ( ! empty( $this->tp_msg->errors ) ) {
                $errors = [];
                foreach ( (array) $this->tp_msg->errors as $error_code => $error_messages ) {
                    foreach ( (array) $error_messages as $error_message ) {
                        $errors[] = array(
                            'code'    => $error_code,
                            'message' => $error_message,
                            'data'    => $this->tp_msg->get_error_data( $error_code ),
                        );
                    }
                }
                $msg = $errors[0]['message'];
                if ( empty( $args['code'] ) ) $args['code'] = $errors[0]['code'];
                if ( empty( $args['response'] ) && is_array( $errors[0]['data'] ) && ! empty( $errors[0]['data']['status'] ) )
                    $args['response'] = $errors[0]['data']['status'];
                if ( empty( $title ) && is_array( $errors[0]['data'] ) && ! empty( $errors[0]['data']['title'] ) )
                    $title = $errors[0]['data']['title'];
                unset( $errors[0] );
                $args['additional_errors'] = array_values( $errors );
            }else $msg = '';
            if ( empty( $args['code'] ) ) $args['code'] = 'tp_die';
            if ( empty( $args['response'] ) ) $args['response'] = 500;
            if ( empty( $title ) ) $title = $this->__( 'TailoredPress &rsaquo; Error');
            if ( empty( $args['text_direction'] ) || ! in_array( $args['text_direction'], array( 'ltr', 'rtl' ), true ) ) {
                $args['text_direction'] = 'ltr';
                if ( function_exists( '__is_rtl' ) && $this->_is_rtl() )
                    $args['text_direction'] = 'rtl';
            }
            if ( ! empty( $args['charset'] ) )
                $args['charset'] = $this->_canonical_charset( $args['charset'] );
            return [$msg, $title, $args];
        }//4110
        /**
         * @description Encode a variable into JSON, with some sanity checks.
         * @param $data
         * @param int $options
         * @param int $depth
         * @return bool|string
         */
        protected function _tp_json_encode( $data, $options = 0, $depth = 512 ){
            $json = json_encode( $data, $options, $depth );
            if ( false !== $json ) return $json;
            try {
                $data = $this->_tp_json_sanity_check( $data, $depth );
            } catch ( \Exception $e ) {
                return false;
            }
            return json_encode( $data, $options, $depth );
        }//4194
        /**
         * @description Perform sanity checks on data that shall be encoded to JSON.
         * @param $data
         * @param $depth
         * @return mixed
         * @throws \OutOfRangeException
         */
        protected function _tp_json_sanity_check( $data, $depth ):mixed{
            if ( $depth < 0 ) throw new \OutOfRangeException( 'Reached depth limit' );
            if ( is_array( $data ) ) {
                $output = [];
                foreach ( $data as $id => $el ) {
                    // Don't forget to sanitize the ID!
                    if ( is_string( $id ) ) $clean_id = $this->_tp_json_convert_string( $id );
                    else $clean_id = $id;
                    if ( is_array( $el ) || is_object( $el ) )
                        $output[ $clean_id ] = $this->_tp_json_sanity_check( $el, $depth - 1 );
                    elseif ( is_string( $el ) )
                        $output[ $clean_id ] = $this->_tp_json_convert_string( $el );
                    else $output[ $clean_id ] = $el;
                }
            } elseif ( is_object( $data ) ) {
                $output = new \stdClass;
                foreach ( $data as $id => $el ) {
                    if ( is_string( $id ) )
                        $clean_id = $this->_tp_json_convert_string( $id );
                    else $clean_id = $id;
                    if ( is_array( $el ) || is_object( $el ) )
                        $output->$clean_id = $this->_tp_json_sanity_check( $el, $depth - 1 );
                    elseif ( is_string( $el ) ) $output->$clean_id = $this->_tp_json_convert_string( $el );
                    else $output->$clean_id = $el;
                }
            }elseif ( is_string( $data ) ) return $this->_tp_json_convert_string( $data );
            else return $data;
            return $output;
        }//4226
        /**
         * @description Convert a string to UTF-8, so that it can be safely encoded to JSON.
         * @param $string
         * @return string
         */
        protected function _tp_json_convert_string( $string ):string{
            static $use_mb = null;
            if ( is_null( $use_mb ) )  $use_mb = function_exists( 'mb_convert_encoding' );
            if ( $use_mb ) {
                $encoding = mb_detect_encoding( $string, mb_detect_order(), true );
                if ( $encoding ) return mb_convert_encoding( $string, 'UTF-8', $encoding );
                 else return mb_convert_encoding( $string, 'UTF-8', 'UTF-8' );
            } else  return $this->_tp_check_invalid_utf8( $string, true );
        }//4288
        /**
         * @description Send a JSON response back to an Ajax request.
         * @param $response
         * @param null $status_code
         * @param int $options
         */
        protected function _tp_send_json( $response, $status_code = null, $options = 0 ):void{
            if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                $this->_doing_it_wrong(
                    __FUNCTION__,
                    sprintf(
                        $this->__( 'Return a %1$s or %2$s object from your callback when using the REST API.' ),
                        'TP_REST_Response','TP_Error'),'0.0.1');
            }
            if ( ! headers_sent() ) {
                header( 'Content-Type: application/json; charset=' . $this->_get_option( 'blog_charset' ) );
                if ( null !== $status_code ) $this->_status_header( $status_code );
            }
            echo $this->_tp_json_encode( $response, $options );
            if ( $this->_tp_doing_async())
                $this->_tp_die('','',['response' => null,]);
            else die;
        }//4337
        /**
         * @description Send a JSON response back to an Ajax request, indicating success.
         * @param null $data
         * @param null $status_code
         * @param int $options
         */
        protected function _tp_send_json_success( $data = null, $status_code = null, $options = 0 ):void{
            $response = array( 'success' => true );
            if ( isset( $data ) ) $response['data'] = $data;
            $this->_tp_send_json( $response, $status_code, $options );
        }//4384
        /**
         * @description Send a JSON response back to an Ajax request, indicating failure.
         * @param TP_Error $data
         * @param null $status_code
         * @param int $options
         */
        protected function _tp_send_json_error(TP_Error $data = null, $status_code = null, $options = 0 ):void{
            $response = ['success' => false];
            if ( isset( $data ) ) {
                if ( $this->_init_error( $data ) ) {
                    $result = [];
                    foreach ((array) $data->errors as $code => $messages ) {
                        foreach ( $messages as $message )
                            $result[] = ['code' => $code, 'message' => $message,];
                    }
                    $response['data'] = $result;
                } else $response['data'] = $data;
            }
            $this->_tp_send_json( $response, $status_code, $options );
        }//4411
    }
}else die;