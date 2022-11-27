<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 15-10-2022
 * Time: 21:03
 */
namespace TP_Admin\Libs\AdmComponents;
use TP_Admin\Admins;
if(ABSPATH){
    class Adm_Footer extends Admins {
        public function __construct($args = null){
            parent::__construct();
            $this->adm_footer_args = $args;
        }
        private function __to_string():string{
            $output  = "</div></main></section><!-- tp-main-component -->";//todo has to be moved to another file?
            $output .= "<section class='tp-footer-component'>";
            $output .= "<footer class='admin-footer' role='contentinfo'>";
            $output .= $this->_get_action( 'in_admin_footer' );//should be 'return' actions
            $text = sprintf(
                $this->__( 'Thank you for creating with <a href="%s">TailoredPress</a>.' ),
                $this->_esc_url( 'https://www.aadswebdesign.nl/' )
            );
            $output .= "<p class='welcome-paragraph block-left'>";
            $output .= $this->_apply_filters( 'admin_footer_text', "<span id='footer_thank_you'>$text</span>" );
            $output .= "</p>";
            $output .= "<p id='footer_upgrade' class='upgrade-paragraph block-right'>";
            $output .= $this->_apply_filters( 'update_footer', '' );
            $output .= "</p></footer>";//todo
            $output .= $this->_get_action( "admin_print_footer_scripts-{$this->tp_hook_suffix}" );
            $output .= $this->_get_action( "admin_footer-{$this->tp_hook_suffix}" );
            $output .= $this->_get_action( 'admin_print_footer_scripts' );
            $output .= $this->_get_site_option( 'can_compress_scripts' );
            $output .= "</section><!-- tp-footer-component -->";
            if('index.php' === $this->adm_footer_args['parent_file']){
                $output .= "<section id='dashboard_component_wrap' class='dashboard-component bottom'>";
                $output .= $this->_tp_get_dashboard();
                $output .= "</section><!--dashboard-component bottom -->";
            }
            $output .= "</div><!-- tp-container -->";//.tp-wrap in Admin_Header
            $output .= "</body></html>";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}