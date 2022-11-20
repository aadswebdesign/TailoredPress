<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
use TP_Core\Libs\Customs\TP_Customize_Control;
use TP_Core\Traits\Misc\tp_link_styles;
if(ABSPATH){
    class TP_Customize_Color_Control extends TP_Customize_Control {
        use tp_link_styles;
        public $type = 'color';
        public $statuses;
        public $mode = 'full';
        public function __construct( $manager, $id,array ...$args) {
            $this->statuses = array( '' => $this->__( 'Default' ) );
            parent::__construct( $manager, $id, $args );
        }//53
        public function enqueue():void {
            $this->tp_enqueue_script( 'wp-color-picker' );
            $this->tp_enqueue_style( 'wp-color-picker' );
        }
        public function to_json():void {
            parent::to_json();
            $this->json['statuses']     = $this->statuses;
            $this->json['defaultValue'] = $this->setting->default;
            $this->json['mode']         = $this->mode;
        }
        public function render_content():void {}
        protected function _get_content_template():string{
            $output  = "_get_content_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//93
    }
}else die;