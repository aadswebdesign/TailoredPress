<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-5-2022
 * Time: 09:04
 */
namespace TP_Core\Traits\Feed\Components;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Feed\_feed_01;
use TP_Core\Traits\Feed\_feed_02;
use TP_Core\Traits\Feed\_feed_03;
use TP_Core\Traits\Feed\_feed_04;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Inits\_init_post;

if(ABSPATH){
    class feed_base{
        use _action_01, _option_01, _filter_01;
        use _feed_01, _feed_02, _feed_03, _feed_04;
        use _init_post,_methods_01;
        protected $_xml;
        protected $_args;
        public function __construct(...$args){
            $this->_args = $args;
            header("Content-Type:'{$this->_feed_content_type($this->_args['feed_type'])}'; charset='{$this->_get_option( 'blog_charset' )}'", true);
        }
        private function __to_string():string{
            $this->_xml = "";
            return (string) $this->_xml;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;