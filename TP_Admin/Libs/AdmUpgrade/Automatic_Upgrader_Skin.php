<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-9-2022
 * Time: 05:12
 */
namespace TP_Admin\Libs\AdmUpgrade;
use TP_Core\Libs\TP_Error;

if(ABSPATH){
    class Automatic_Upgrader_Skin extends TP_Upgrader_Skin {
        protected $_messages = [];
        public function request_filesystem_credentials( $error = false, $context = '', $allow_relaxed_file_ownership = false ):string {
            if ( $context ) {$this->options['context'] = $context;}
            ob_start();
            $result = parent::request_filesystem_credentials( $error, $context, $allow_relaxed_file_ownership );
            ob_end_clean();
            return $result;
        }//40
        public function get_upgrade_messages():string {
            return $this->_messages;
        }//61
        public function feedback(TP_Error $feedback, ...$args ):void {
            if ( $this->_init_error( $feedback ) ) {
                $string = $feedback->get_error_message();
            } elseif ( is_array((array)$feedback ) ) { return;}
            else {$string = $feedback;}
            if ( ! empty( $this->upgrader->strings[ $string ] ) ) {
                $string = $this->upgrader->strings[ $string ];
            }
            if (!empty($args) && (strpos($string, '%') !== false)) {
                $string = vsprintf( $string, $args );
            }
            $string = trim( $string );
            $string = $this->_tp_kses($string,['a' => ['href' => true,],'br' => true,'em' => true,'strong' => true,]);
            if ( empty( $string )){return;}
            $this->_messages[] = $string;
        }//74
        public function get_header() {
            return ob_start();
        }
        public function get_footer() {
            $output = ob_get_clean();
            if ( ! empty( $output ) ) {
                $this->feedback( $output );
            }
            return $output;
        }
    }
}else{die;}