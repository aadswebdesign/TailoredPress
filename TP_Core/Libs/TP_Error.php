<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-2-2022
 * Time: 15:38
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Options\_option_02;
if(ABSPATH){
    class TP_Error {
        use _option_02;
        use _action_01;
        public $errors = [];
        public $error_data = [];
        protected $_additional_data = [];
        public function __construct( $code = '', $message = '', $data = '' ) {
            if ( empty( $code )) return;
            $this->add( $code, $message, $data );
        }
        /**
         * @description Retrieves all error codes.
         * @since
         * @return array List of error codes, if available.
         */
        public function get_error_codes(): array{
            if ( ! $this->has_errors() )  return [];
            return array_keys( $this->errors );
        }
        /**
         * @description Retrieves the first error code available.
         * @since
         * @return string|int Empty string, if no error codes.
         */
        public function get_error_code(){
            $codes = $this->get_error_codes();
            if ( empty( $codes ) ) return '';
            return $codes[0];
        }
        /**
         * @description Retrieves all error messages, or the error messages for the given error code.
         * @since
         * @param string|int $code Optional. Retrieve messages matching code, if exists.
         * @return array Error strings on success, or empty array if there are none.
         */
        public function get_error_messages( $code = '' ): ?array{
            if (empty( $code )) {
                $err_messages = [];
                foreach ( (array) $this->errors as $err_code => $messages ) {
                    $err_messages[] = $messages;
                }
                return $err_messages;
            }
            return $this->errors[$code] ?? [];

        }
        /**
         * @description Gets a single error message.
         * @note This will get the first message available for the code. If no code is given then the first code available will be used.
         * @since
         * @param string|int $code Optional. Error code to retrieve message.
         * @return string The error message.
         */
        public function get_error_message( $code = '' ): string {
            if ( empty( $code ) ) $code = $this->get_error_code();
            $messages = $this->get_error_messages( $code );
            if ( empty( $messages ) ) return '';
            return $messages[0];
        }
        /**
         * @description Retrieves the most recently added error data for an error code.
         * @since
         * @param string|int $code Optional. Error code.
         * @return mixed Error data, if it exists.
         */
        public function get_error_data( $code = '' ){
            $return = null;
            if ( empty( $code ) ) $code = $this->get_error_code();
            if ( isset( $this->error_data[ $code ] ) ) $return = $this->error_data[ $code ];
            return $return;
        }
        /**
         * @description Verifies if the instance contains errors.
         * @since
         * @return bool If the instance contains errors.
         */
        public function has_errors(): bool{
            if ( ! empty( $this->errors ) ) return true;
            return false;
        }
        /**
         * @description Adds an error or appends an additional message to an existing error.
         * @since
         * @param string|int $code    Error code.
         * @param string     $message Error message.
         * @param mixed      $data    Optional. Error data.
         */
        public function add( $code, $message, $data = '' ): void{
            //$this->errors[ $code ][] = $message; //todo
            if ( ! empty( $data )) $this->add_data( $data, $code );
            $this->_do_action( 'tp_error_added', $code, $message, $data, $this );
        }
        /**
         * @description Adds data to an error with the given code.
         * @since
         * @param mixed      $data Error data.
         * @param string|int $code Error code.
         */
        public function add_data( $data, $code = '' ): void{
            if ( empty( $code ) ) $code = $this->get_error_code();
            if ( isset( $this->error_data[ $code ] ) )
                $this->_additional_data[ $code ][] = $this->error_data[ $code ];
            $this->error_data[ $code ] = $data;
        }
        /**
         * @description Retrieves all error data for an error code in the order in which the data was added.
         * @since
         * @param string|int $code Error code.
         * @return mixed[] Array of error data, if it exists.
         */
        public function get_all_error_data( $code = '' ): array{
            if ( empty( $code ) ) $code = $this->get_error_code();
            $data = [];
            if ( isset( $this->_additional_data[ $code ] ) )
                $data = $this->_additional_data[ $code ];
            if ( isset( $this->error_data[ $code ] ) )
                $data[] = $this->error_data[ $code ];
            return $data;
        }
        /**
         * @description Removes the specified error.
         * @note This function removes all error messages associated with the specified error code, along with any error data for that code.
         * @since
         * @param string|int $code Error code.
         */
        public function remove( $code ): void{
            unset( $this->errors[ $code ],$this->error_data[ $code ],$this->_additional_data[ $code ] );
        }
        public function merge_from( TP_Error $error ): void{
            static::copy_errors( $error, $this );
        }
        public function export_to( TP_Error $error ): void{
            static::copy_errors( $this, $error );
        }
        protected static function copy_errors( TP_Error $from, TP_Error $to ): void{
            foreach ( $from->get_error_codes() as $code ) {
                foreach ( $from->get_error_messages( $code ) as $error_message ) {
                    $to->add( $code, $error_message );
                }
                foreach ( $from->get_all_error_data( $code ) as $data ) {
                    $to->add_data( $data, $code );
                }
            }
        }
    }
}else die;