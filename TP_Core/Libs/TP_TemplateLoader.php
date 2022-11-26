<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-7-2022
 * Time: 18:39
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Load\_load_05;
use TP_Core\Traits\Query\_query_03;
use TP_Core\Traits\Query\_query_02;
use TP_Core\Traits\Query\_query_04;
if(ABSPATH){
    class TP_TemplateLoader{
        use _action_01, _filter_01, _load_05, _methods_04;
        use _query_02, _query_03, _query_04;
        protected $_themes, $_template;
        public function __construct(){
            if ( $this->_tp_using_themes() )
                $this->_do_action( 'template_redirect' );//Fires before determining which template to load.
            if ( 'HEAD' === $_SERVER['REQUEST_METHOD'] && $this->_apply_filters( 'exit_on_http_head', true ) ) exit;
            if ($this->_is_robots()) {
                $this->_do_action( 'do_robots' );
                return;
            }
            if ($this->_is_favicon()) {
                $this->_do_action( 'do_favicon' );
                return;
            }
            if ($this->_is_feed()) {
                $this->_do_feed();
                return;
            }
            if ($this->_is_trackback()) {
                //require ABSPATH . 'wp-trackback.php';
                return;
            }
        }
    }
}else die;