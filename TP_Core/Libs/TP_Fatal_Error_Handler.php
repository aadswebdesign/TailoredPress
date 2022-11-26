<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 20:20
 */
namespace TP_Core\Libs;
use TP_Core\Libs\Recovery\TP_Recovery_Mode;
use TP_Core\Traits\Misc\_error_protection;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Load\_load_01;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Load\_load_03;
if(ABSPATH){
    class TP_Fatal_Error_Handler{
        use _methods_08;
        use _I10n_01;
        use _load_01;
        use _load_03, _load_04;
        use _filter_01;
        use _error_protection;
        public function handle(): void{
            if ( defined( 'TP_SANDBOX_SCRAPING' ) && TP_SANDBOX_SCRAPING ) {return;}
            if ( $this->_tp_is_maintenance_mode() ) {return;}
            try {
                $error = $this->_detect_error();// Bail if no error found.
                if ( ! $error ) {return;}
                if ( ! isset( $GLOBALS['wp_locale'] ) && function_exists( 'load_default_textdomain' ) ) {
                    load_default_textdomain();
                }
                $handled = false;
                $_recovery_mode = $this->_tp_recovery_mode();
                $recovery_mode = null;
                if( $_recovery_mode instanceof TP_Recovery_Mode ){
                    $recovery_mode = $_recovery_mode;
                }
                if ( ! $this->_is_multisite() && $recovery_mode->is_initialized() ) {
                    $handled = $recovery_mode->handle_error( $error );
                }
                if ( $this->_is_admin() || ! headers_sent() ) {// Display the PHP error template if headers not sent.
                    $this->_display_error_template( $error, $handled );
                }
            } catch ( \Exception $e ) {
                // Catch exceptions and remain silent.
            }

        }
        protected function _detect_error(){
            $error = error_get_last();
            if ( null === $error ) { return null;}// No error, just skip the error handling code.
            if ( ! $this->_should_handle_error( $error ) ){return null;}// Bail if this error should not be handled.
            return $error;
        }
        protected function _should_handle_error( $error ): bool{
            $error_types_to_handle = [E_ERROR,E_PARSE,E_USER_ERROR,E_COMPILE_ERROR,E_RECOVERABLE_ERROR,];
            if ( isset( $error['type'] ) && in_array( $error['type'], $error_types_to_handle, true ) ) {
                return true;
            }
            return (bool) $this->_apply_filters( 'tp_should_handle_php_error', false, $error );
        }
        protected function _display_error_template( $error, $handled ): void{
            if ( defined( 'TP_CONTENT_DIR' ) ) {
                // Load custom PHP error template, if present.
                $php_error_pluggable = TP_CONTENT_DIR . '/php-error.php';//todo
                if ( is_readable( $php_error_pluggable ) ) {
                    //todo
                    return;
                }
            }
            // Otherwise, display the default error template.
            $this->_display_default_error_template( $error, $handled );
        }//todo
        protected function _display_default_error_template( $error, $handled ): void{

        }//todo
    }
}else{die;}