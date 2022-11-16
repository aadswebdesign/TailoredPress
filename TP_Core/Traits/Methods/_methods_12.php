<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _methods_12{
        use _init_error;
        use _init_db;
        //todo might not use this? @description Determines if Widgets library should be loaded.
        //protected function _tp_maybe_load_widgets(){}//5163
        //todo might not use this? @description Append the Widgets menu to the themes main menu.
        //protected function _tp_widgets_add_menu(){}//5191
        /**
         * @description Flush all output buffers for PHP 5.2.
         */
        protected function _tp_ob_end_flush_all():void{
            $levels = ob_get_level();
            for ( $i = 0; $i < $levels; $i++ ) ob_end_flush();
        }//5209
        /**
         * @description Load custom DB error or display TailoredPress DB error.
         */
        protected function _dead_db():void{
            $this->tpdb = $this->_init_db();
            $this->_tp_load_translations_early();
            if (defined( 'TP_ADMIN' ) || $this->_tp_installing())
                $this->_tp_die( $this->tpdb->error );
            $this->_tp_die( "<h1>{$this->__( 'Error establishing a database connection' )}</h1>", $this->__( 'Database Error' ) );
        }//5234
        /**
         * @description Convert a value to non-negative integer.
         * @param $maybe_int
         * @return number
         */
        protected function _abs_int( $maybe_int ){
            return abs( (int) $maybe_int );
        }//5262
        /**
         * @description Mark a function as deprecated and inform when it has been used.
         * @param $function
         * @param $version
         * @param string $replacement
         */
        protected function _deprecated_function( $function, $version, $replacement = '' ):void{
            $this->_do_action( 'deprecated_function_run', $function, $replacement, $version );
            if ( TP_DEBUG && $this->_apply_filters( 'deprecated_function_trigger_error', true ) ) {
                if ( $replacement ) {
                    trigger_error(
                        sprintf(
                        /* translators: 1: PHP function name, 2: Version number, 3: Alternative function name. */
                            $this->__( '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.' ),
                            $function,
                            $version,
                            $replacement
                        ),
                        E_USER_DEPRECATED
                    );
                } else {
                    trigger_error(
                        sprintf(
                        /* translators: 1: PHP function name, 2: Version number. */
                            $this->__( '%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.' ),
                            $function,
                            $version
                        ),
                        E_USER_DEPRECATED
                    );
                }
            }
        }//5292
        /**
         * @description Mark a function argument as deprecated and inform when it has been used.
         * @param $function
         * @param $version
         * @param string $message
         */
        protected function _deprecated_argument($function, $version, $message = ''):void{
            $this->_do_action( 'deprecated_argument_run', $function, $message, $version );
            if ( TP_DEBUG && $this->_apply_filters( 'deprecated_argument_trigger_error', true ) ){
                if ( function_exists([$this,'__']) ) {
                    if ( $message )
                        trigger_error(
                            sprintf(
                                $this->__( '%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s! %3$s' ),
                                $function, $version,$message ),E_USER_DEPRECATED);
                    else trigger_error(
                            sprintf(
                            /* translators: 1: PHP function name, 2: Version number. */
                                $this->__( '%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s with no alternative available.' ),
                                $function,$version),E_USER_DEPRECATED);
                }else if ( $message )
                    trigger_error(
                        sprintf(
                            '%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s! %3$s',
                            $function,$version,$message), E_USER_DEPRECATED );
                else trigger_error(
                        sprintf(
                            '%1$s was called with an argument that is <strong>deprecated</strong> since version %2$s with no alternative available.',
                            $function,$version),E_USER_DEPRECATED);
            }
        }//5576
        /**
         * @description Marks a deprecated action or filter hook as deprecated and throws a notice.
         * @param $hook
         * @param $version
         * @param string $replacement
         * @param string $message
         */
        protected function _deprecated_hook( $hook, $version, $replacement = '', $message = '' ):void{
            $this->_do_action( 'deprecated_hook_run', $hook, $replacement, $version, $message );
            if ( TP_DEBUG && $this->_apply_filters( 'deprecated_hook_trigger_error', true ) ) {
                $message = empty( $message ) ? '' : ' ' . $message;
                if ( $replacement ) {
                    trigger_error(
                        sprintf(
                        /* translators: 1: WordPress hook name, 2: Version number, 3: Alternative hook name. */
                            $this->__( '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.' ),
                            $hook, $version, $replacement ) . $message, E_USER_DEPRECATED);
                } else {
                    trigger_error(
                        sprintf(
                        /* translators: 1: WordPress hook name, 2: Version number. */
                            $this->__( '%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.' ),
                            $hook,$version) . $message, E_USER_DEPRECATED);
                }
            }
        }//5665
        /**
         * @description Mark something as being incorrectly called.
         * @param $function
         * @param $message
         * @param $version
         */
        protected function _doing_it_wrong( $function, $message, $version ):void{
            $this->_do_action( 'doing_it_wrong_run', $function, $message, $version );
            if ( TP_DEBUG && $this->_apply_filters( 'doing_it_wrong_trigger_error', true, $function, $message, $version ) ) {
                if ( function_exists([$this,'__'])) {
                    if ( $version ) $version = sprintf( $this->__( '(This message was added in version %s.)' ), $version );/* translators: %s: Version number. */
                    $message .= 'todo';
                    trigger_error(
                    /* translators: Developer debugging message. 1: PHP function name, 2: Explanatory message, 3: WordPress version number. */
                        sprintf($this->__( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s' ),$function,$message,$version), E_USER_NOTICE);
                }else{
                    if ( $version ) $version = sprintf( '(This message was added in version %s.)', $version );
                    $message .= sprintf("Please see <a href='%s'>Debugging in TailoredPress</a> for more information.", '/');
                    trigger_error( sprintf('%1$s was called <strong>incorrectly</strong>. %2$s %3$s',$function,$message, $version), E_USER_NOTICE);
                }
            }
        }//5723
        /**
         * @description Does the specified module exist in the Apache config?
         * @param $mod
         * @param bool $default
         * @return bool
         */
        protected function _apache_mod_loaded( $mod, $default = false ):bool{
            if ( ! $this->tp_is_apache ) return false;
            if ( function_exists('apache_get_modules')){
                $mods = apache_get_modules();
                if ( in_array( $mod, $mods, true ) ) return true;
            }elseif ( function_exists( 'phpinfo' ) && false === strpos( ini_get( 'disable_functions' ), 'phpinfo' ) ) {
                ob_start();
                phpinfo( 8 );
                $php_info = ob_get_clean();
                if ( false !== strpos( $php_info, $mod ) ) return true;
            }
            return $default;
        }//5818
        /**
         * @description Validates a file name and path against an allowed set of rules.
         * @param $file
         * @param array $allowed_files
         * @return int
         */
        protected function _validate_file( $file, $allowed_files = [] ):int{
            if ( ! is_scalar( $file ) || '' === $file ) return 0;
            // `../` on its own is not allowed:
            if ( '../' === $file ) return 1;
            // More than one occurrence of `../` is not allowed:
            if ( preg_match_all( '#\.\./#', $file, $matches, PREG_SET_ORDER ) && ( count( $matches ) > 1 ) ) return 1;
            // `../` which does not occur at the end of the path is not allowed:
            if ( false !== strpos( $file, '../' ) && '../' !== mb_substr( $file, -3, 3 ) ) return 1;
            // Files not in the allowed file list are not allowed:
            if ( ! empty( $allowed_files ) && ! in_array( $file, $allowed_files, true ) ) return 3;
            // Absolute Windows drive paths are not allowed:
            if ( ':' === $file[1]) return 2;
            return 0;
        }//5956
    }
}else die;