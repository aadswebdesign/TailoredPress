<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-9-2022
 * Time: 03:38
 */
namespace TP_Admin\Libs\AdmUpgrade;
use TP_Admin\Traits\Rewrite\_rewrite_02;
use TP_Admin\Traits\File\_file_03;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\K_Ses\_k_ses_01;
use TP_Core\Traits\Methods\_methods_05;
use TP_Core\Traits\Methods\_methods_10;

if(ABSPATH){
    class TP_Upgrader_Skin{
        use _file_03;
        use _formats_08;
        use _init_error;
        use _k_ses_01;
        use _methods_05;
        use _methods_10;
        use _rewrite_02;
        public $upgrader;
        public $done_header = false;
        public $done_footer = false;
        public $result = false;
        public $options = [];
        public function __construct( array $args) {
            $defaults = ['url' => '','nonce' => '','title' => '','context' => false,];
            $this->options = $this->_tp_parse_args( $args, $defaults );
        }//73
        public function set_upgrader( &$upgrader ):void {
            if ( is_object( $upgrader ) ) {
                $this->upgrader =& $upgrader;
            }
            $this->add_strings();
        }//88
        public function add_strings():string {}//98
        public function set_result( $result ):void {
            $this->result = $result;
        }//108
        public function request_filesystem_credentials( $error = false, $context = '', $allow_relaxed_file_ownership = false ):string {
            $url = $this->options['url'];
            if ( ! $context ) {$context = $this->options['context'];}
            if ( ! empty( $this->options['nonce'] ) ) {
                $url = $this->_tp_nonce_url( $url, $this->options['nonce'] );
            }
            $extra_fields = [];
            return $this->_request_filesystem_credentials( $url, '', $error, $context, $extra_fields, $allow_relaxed_file_ownership );
        }//128
        public function get_header() {
            if ( $this->done_header ){return;}
            $this->done_header = true;
            $html  = "<div class='wrap tp-flex'>";
            $html .= "<h1>{$this->options['title']}</h1>";
            return $html;
        }//152
        public function header():void {
            echo $this->get_header();
        }//152
        public function get_footer(){
            if ( $this->done_footer ){return;}
            $this->done_footer = true;
            return "</div>";
        }//157
        public function footer():void{
            echo $this->get_footer();//157
        }
        public function get_error(TP_Error $errors ):void{
            if ( ! $this->done_header ) { $errors .= $this->get_header();}
            if ( is_string((string)$errors ) ) {
                $this->feedback( $errors );
            } elseif ( $this->_init_error( $errors ) && $errors->has_errors() ) {
                foreach ( $errors->get_error_messages() as $message ) {
                    if ( $errors->get_error_data() && is_string( $errors->get_error_data() ) ) {
                        $this->feedback( $message . ' ' . $this->_esc_html( strip_tags( $errors->get_error_data() ) ) );
                    } else {$this->feedback( $message );}
                }
            }
        }//170
        public function feedback( $feedback, ...$args ):void {
            if ( isset( $this->upgrader->strings[ $feedback ] ) ) {
                $feedback = $this->upgrader->strings[ $feedback ];
            }
            if ($args &&(strpos($feedback, '%') !== false)) {
                $args     = array_map( 'strip_tags', $args );
                $args     = array_map( 'esc_html', $args );
                $feedback = vsprintf( $feedback, $args );
            }
            if ( empty( $feedback ) ) { return;}
            $this->_show_message( $feedback );
        }//194
        public function before():string {}
        public function after():string {}
        protected function get_decrement_update_count( $type ) {
            $script = '';
            if ( ! $this->result || 'up_to_date' === $this->result || $this->_init_error( $this->result ) ) {
                return;
            }
            if ( defined( 'IFRAME_REQUEST' ) ) {
                /** @noinspection JSCheckFunctionSignatures */
                $script .= '<script type="text/javascript">
					if ( window.postMessage && JSON ) {
						window.parent.postMessage( JSON.stringify( { action: "decrementUpdateCount", upgradeType: "' . $type . '" } ), window.location.protocol + "//" + window.location.hostname );
					}
				</script>';
            } else {
                /** @noinspection JSUnresolvedVariable */
                $script .= '<script>
					(function( tp ) {
					    //noinspection JSUnresolvedVariable
						if ( tp && tp.updates && tp.updates.decrementCount ) {
						    //noinspection JSUnresolvedVariable, JSUnresolvedFunction 
							tp.updates.decrementCount( "' . $type . '" );
						}
						
					})(window.tp );
				</script>';
            }
            return $script;
        }//234
        public function bulk_header():string{}//121
        public function bulk_footer():string {}//264
        public function hide_process_failed( $tp_error ):bool {
            if($tp_error){
                $this->_init_error($tp_error);
            }
            return false;
        }
    }
}else{die;}