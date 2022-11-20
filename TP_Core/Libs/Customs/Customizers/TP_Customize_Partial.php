<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
if(ABSPATH){
    class TP_Customize_Partial{
        protected $_id_data = [];
        public $component;
        public $id;
        public $type = 'default';
        public $selector;
        public $settings;
        public $primary_setting;
        public $capability;
        public $render_callback;
        public $container_inclusive = false;
        public $fallback_refresh = true;
        public function __construct(){}//160
        final public function id_data():array{
            return $this->_id_data;
        }
        final public function render(array ...$container_context):array{}//215
        public function is_render_callback():bool{return false;}//282
        public function json():void{}//294
        final public function check_capabilities():void{}
    }
}else die;