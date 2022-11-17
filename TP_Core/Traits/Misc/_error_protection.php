<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 20:41
 */
namespace TP_Core\Traits\Misc;
use TP_Core\Libs\Recovery\TP_Recovery_Mode;
use TP_Core\Libs\TP_Fatal_Error_Handler;
use TP_Core\Libs\TP_Paused_Extensions_Storage;
if(ABSPATH){
    trait _error_protection{
        /**
         * @description Get the instance for storing paused extensions.
         * @return null|TP_Paused_Extensions_Storage
         */
        protected function _tp_paused_themes():TP_Paused_Extensions_Storage{
            static $storage = null;
            if ( null === $storage ){$storage = new TP_Paused_Extensions_Storage( 'theme' );}
            return $storage;
        }//29
        /**
         * @description Get a human readable description of an extension's error
         * @param $error
         * @return string
         */
        protected function _tp_get_extension_error_description( $error ):string{
            $constants   = get_defined_constants( true );
            $constants   = $constants['Core'] ?? $constants['internal'];
            $core_errors = [];
            foreach ( $constants as $constant => $value ) {
                if(0 === strpos( $constant,'E_')){$core_errors[ $value ] = $constant;}
            }
            if ( isset( $core_errors[ $error['type'] ] ) ) { $error['type'] = $core_errors[ $error['type'] ];}
            /* translators: 1: Error type, 2: Error line number, 3: Error file name, 4: Error message. */
            $error_message = $this->__( 'An error of type %1$s was caused in line %2$s of the file %3$s. Error message: %4$s' );
            return sprintf($error_message,"<code>{$error['type']}</code>","<code>{$error['line']}</code>","<code>{$error['file']}</code>","<code>{$error['message']}</code>");
        }//47
        /**
         * @description Registers the shutdown handler for fatal errors.
         */
        protected function _tp_register_fatal_error_handler():void{
            if ( ! $this->_tp_is_fatal_error_handler_enabled() ) {
                return;
            }
            $handler = null;
            if ( ! is_object( $handler ) || ! is_callable( array( $handler, 'handle' ) ) ) {
                $handler = new TP_Fatal_Error_Handler();
            }
            register_shutdown_function( array( $handler, 'handle' ) );
        }//81
        /**
         * @description Checks whether the fatal error handler is enabled.
         * @return mixed
         */
        protected function _tp_is_fatal_error_handler_enabled(){
            $enabled = ! defined( 'TP_DISABLE_FATAL_ERROR_HANDLER' ) || ! TP_DISABLE_FATAL_ERROR_HANDLER;
            return $this->_apply_filters( 'tp_fatal_error_handler_enabled', $enabled );
        }//108
        /**
         * @description Access the TailoredPress Recovery Mode instance.
         * @return TP_Recovery_Mode
         */
        protected function _tp_recovery_mode():TP_Recovery_Mode{
            static $tp_recovery_mode;
            if ( ! $tp_recovery_mode ){$tp_recovery_mode = new TP_Recovery_Mode();}
            return $tp_recovery_mode;
        }//149
    }
}else{die;}