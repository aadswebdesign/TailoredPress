<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
use TP_Core\Libs\Customs\TP_Customize_Control;
use TP_Core\Traits\Templates\_general_template_08;
if(ABSPATH){
    class TP_Customize_Code_Editor_Control  extends TP_Customize_Control {
        use _general_template_08;
        public $type = 'code_editor';
        public $code_type = '';
        public $editor_settings = [];
        public function enqueue():void{
            $this->editor_settings = $this->_tp_enqueue_code_editor(
                array_merge(['type' => $this->code_type,'code_mirror' => ['indentUnit' => 2,'tabSize' => 2,],],
                    $this->editor_settings));
        }//49
        public function json():array{
            $json                    = parent::json();
            $json['editor_settings'] = $this->editor_settings;
            $json['input_attrs']     = $this->input_attrs;
            return $json;
        }//73
        public function get_render_content():void{}//85
        public function render_content():void{}//85
        protected function _get_content_template():string{
            $output  = "_get_content_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//92
    }
}else die;