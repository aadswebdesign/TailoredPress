<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 18-8-2022
 * Time: 21:39
 */
namespace TP_Admin\Libs\AdmCustoms;
use TP_Core\Traits\Actions\_action_01;
if(ABSPATH){
    class TP_Custom_Background{
        use _action_01;
        private $__updated;
        public $admin_header_callback;
        public $admin_image_div_callback;
        public function __construct( $admin_header_callback = '', $admin_image_div_callback = '' ) {
            $this->admin_header_callback    = $admin_header_callback;
            $this->admin_image_div_callback = $admin_image_div_callback;
            $this->_add_action( 'admin_menu',[$this,'init']);
            $this->_add_action( 'tp_ajax_custom-background-add',[$this,'ajax_background_add']);
            $this->_add_action( 'tp_ajax_set-background-image',[$this,'tp_set_background_image']);
        }
        public function init(){}//64
        public function admin_load(){}//84
        public function take_action(){}//113
        public function admin_page(){}//229
        public function async_background_add(){}//547
        public function attachment_fields_to_edit( $form_fields ) {
            return $form_fields;
        }//571
        public function filter_upload_tabs( $tabs ) {
            return $tabs;
        }//584
        public function tp_set_background_image(){}//590
    }
}else{die;}