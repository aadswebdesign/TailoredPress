<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-12-2022
 * Time: 08:22
 */
namespace TP_Content\Themes;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Load\_load_05;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_21;
use TP_Core\Traits\Query\_query_02;
use TP_Core\Traits\Query\_query_03;
use TP_Core\Traits\Query\_query_04;
use TP_Libs\TP_Trackback;

if(ABSPATH){
    class ThemePanel{
        use _action_01;
        use _filter_01;
        use _load_05;
        use _methods_04;
        use _methods_21;
        use _query_02;
        use _query_03;
        use _query_04;
        private $__load_theme;
        protected $_args;
        public function __construct($args = null){
            if (! defined( 'TP_NS_CONTENT' ) ) define('TP_NS_CONTENT','TP_Content\\');
            if (! defined( 'TP_NS_THEMES' ) ) define('TP_NS_THEMES',TP_NS_CONTENT.'Themes\\');
            $this->_args = $args;
            //$this->__load_theme = $this->_tp_load_class('Theme_Index',TP_NS_THEMES,'Tailored_One\\Theme_Index');
        }
        private function __template_loader():string{
            $output  = "";
            if ( $this->_tp_using_themes() ){
                $output .= $this->_get_action( 'template_redirect' );
            }
            if ( 'HEAD' === $_SERVER['REQUEST_METHOD'] && $this->_apply_filters( 'exit_on_http_head', true ) ) {
                exit;
            }
            if ( $this->_is_robots() ) {
                $output .= $this->_get_action( 'do_robots' );
            }elseif ( $this->_is_favicon() ) {
                $output .= $this->_get_action( 'do_favicon' );
            } elseif ( $this->_is_feed() ) {
                $output .= $this->_do_feed();
            } //elseif ( $this->_is_trackback() ) {
                $output .= new TP_Trackback();

            //}

            $output .= "";
            $output .= "";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }
        public function __toString():string{
            //todo this is what it is for now
            return $this->__template_loader(); //__load_theme;
        }
    }
}else{die;}