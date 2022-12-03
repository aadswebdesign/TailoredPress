<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-7-2022
 * Time: 11:41
 */
declare(strict_types=1);
namespace TP_Admin\Libs\AdmRenders;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\I10n\_I10n_01;
if(ABSPATH){
    class render_per_page_options{
        use _I10n_01;
        use _formats_08;
        protected $_html;
        protected $_args;
        public function __construct($args){
            $this->_args['per_page'] = $args['per_page'];
            $this->_args['per_page_label'] = $args['per_page_label'];
            $this->_args['screen_option'] = $args['screen_option'];
        }
        private function __to_string():string{
            $this->_html = "<fieldset class='screen-options'><legend>{$this->__('Pagination')}</legend>";
            if($this->_args['per_page_label']){
                $this->_html .= "<label for='{$this->_esc_attr( $this->_args['screen_option'] )}'>{$this->__($this->_args['per_page_label'])}</label>";
                $this->_html .= "<input type='number' step='1' max='999' class='screen-per-page' name='tp_screen_options[value]' id='{$this->_esc_attr( $this->_args['screen_option'] )}' maxlength='3' value='{$this->_esc_attr( $this->_args['per_page'] )}'/>";
            }
            $this->_html .= "<input type='hidden' name='tp_screen_options[screen_option]' value='{$this->_esc_attr( $this->_args['screen_option'] )}'/>";
            $this->_html .= "</fieldset>";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;