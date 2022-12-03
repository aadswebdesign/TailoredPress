<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-7-2022
 * Time: 12:43
 */
declare(strict_types=1);
namespace TP_Admin\Libs\AdmRenders;
use TP_Admin\Traits\_adm_screen;
use TP_Core\Traits\I10n\_I10n_01;
if(ABSPATH){
    class render_view_mode{
        use _I10n_01;
        use _adm_screen;
        protected $_html;
        protected $_args;
        public function __construct($args){
            $this->_args['tp_mode'] = $args['tp_mode'];
        }
        private function __to_string():string{
            $this->_html = "<fieldset class='metabox-prefers view-mode'><legend>{$this->__('View mode')}</legend>";
            $this->_html .= "<label for='list_view_mode'>";
            $this->_html .= "<input type='radio' id='list_view_mode' name='mode' value='list' {$this->_get_checked( 'list', $this->_args['tp_mode'] )}/>";
            $this->_html .= "</label><label for='excerpt_view_mode'>";
            $this->_html .= "<input type='radio' id='excerpt_view_mode' name='mode' value='excerpt' {$this->_get_checked( 'excerpt', $this->_args['tp_mode'] )} />";
            $this->_html .= "</label></fieldset>";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;