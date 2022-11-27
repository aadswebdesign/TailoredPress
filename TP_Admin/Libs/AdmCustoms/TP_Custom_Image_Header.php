<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 18-8-2022
 * Time: 20:35
 */
declare(strict_types=1);
namespace TP_Admin\Libs\AdmCustoms;
use TP_Core\Traits\Actions\_action_01;
if(ABSPATH){
    class TP_Custom_Image_Header{
        use _action_01;
        private $__updated;
        public $admin_header_callback;
        public $admin_image_div_callback;
        public $default_headers = [];
        public function __construct( $admin_header_callback, $admin_image_div_callback = '' ) {
            $this->admin_header_callback    = $admin_header_callback;
            $this->admin_image_div_callback = $admin_image_div_callback;
            $this->_add_action( 'admin_menu', array( $this, 'init' ) );
            $this->_add_action( 'customize_save_after', array( $this, 'customize_set_last_used' ) );
            $this->_add_action( 'tp_async_custom-header-crop', array( $this, 'async_header_crop' ) );
            $this->_add_action( 'tp_async_custom-header-add', array( $this, 'async_header_add' ) );
            $this->_add_action( 'tp_async_custom-header-remove', array( $this, 'async_header_remove' ) );
        }//55
        public function init(){}//72
        public function help(){}//95
        public function step(){return '';}//148
        public function js_includes(){}//169
        public function css_includes(){}//188
        public function take_action(){return '';}//203
        public function process_default_headers(){}//264
        public function show_header_selector( $type = 'default' ){}//305
        public function js(){}//346
        public function js_1(){}//361
        public function step_1(){}//498
        public function step_2(){}//814
        public function step_2_manage_upload(){return '';}//957
        public function step_3(){return '';}//1000
        public function finished() {
            $this->__updated = true;
            $this->step_1();
        }//1094
        public function admin_page(){}//1104
        public function attachment_fields_to_edit( $form_fields ) {
            return $form_fields;
        }//1128
        public function filter_upload_tabs( $tabs ) {
            return $tabs;
        }//1140
        final public function set_header_image( $choice ){}//1156
        final public function remove_header_image() {
            $this->set_header_image( 'remove-header' );
        }//1211
        final public function reset_header_image(){}//1222
        final public function get_header_dimensions( $dimensions ){return '';}//1253
        final public function create_attachment_object( $cropped, $parent_attachment_id ){return '';}//1305
        final public function insert_attachment( $object, $cropped ){return '';}//1334
        public function async_header_crop(){}//1368
        public function async_header_add(){}//1437
        public function async_header_remove(){}//1465
        public function customize_set_last_used( $tp_customize ){}//1491
        public function get_default_header_images(){return '';}//1517
        public function get_uploaded_header_images(){return '';}//1561
        public function get_previous_crop( $object ){return '';}//1583
    }
}else{die;}