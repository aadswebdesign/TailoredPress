<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
if(ABSPATH){
    class TP_Customize_Header_Image_Control extends TP_Customize_Image_Control{
        public $type = 'header';
        public $uploaded_headers;
        public $default_headers;
        public function __construct( $manager ) {
            parent::__construct($manager,
                'header_image',['label' => $this->__( 'Header Image' ),
                    'settings' => ['default' => 'header_image','data' => 'header_image_data',],
                    'section' => 'header_image','removed' => 'remove-header','get_url' => 'get_header_image',]);
        }
        public function enqueue():void{}//69
        public function prepare_control():void{}//101
        protected function _get_header_image_template():string{
            $output  = "_get_content_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//117
        public function print_header_image_template():void{}//117
        public function get_current_image_src() {
            $src = $this->value();
            if ( isset( $this->get_url ) ) {
                $src = call_user_func( $this->get_url, $src );
                return $src;
            }
        }//178
        protected function _get_render_content():string{
            $output  = "_get_content_template";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }//188
        public function render_content():void{}//188
    }
}else die;