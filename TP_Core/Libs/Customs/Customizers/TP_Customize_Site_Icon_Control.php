<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
use TP_Core\Traits\Actions\_action_01;

if(ABSPATH){
    class TP_Customize_Site_Icon_Control extends TP_Customize_Cropped_Image_Control {
        use _action_01;
        public $type = 'site_icon';
        public function __construct( $manager, $id,array ...$args) {
            parent::__construct( $manager, $id, $args );
            $this->_add_action( 'customize_controls_print_styles', 'wp_site_icon', 99 );
        }
        protected function _get_content_template():string{
            $output  = "_get_content_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//52
    }
}else die;