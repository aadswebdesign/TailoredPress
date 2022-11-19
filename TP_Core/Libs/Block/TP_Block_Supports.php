<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 13:46
 */
namespace TP_Core\Libs\Block;
if(ABSPATH){
    class TP_Block_Supports{
        private $__block_supports = [];
        private static $__instance;
        public static $block_to_render;
        public static function get_instance():TP_Block_Supports {
            if ( null === self::$__instance ) self::$__instance = new self();
            return self::$__instance;
        }
        public static function init():void {
            $instance = self::get_instance();
            $instance->__register_attributes();
        }
        public function register( $block_support_name, $block_support_config ):void {
            $this->__block_supports[ $block_support_name ] = array_merge($block_support_config,['name' => $block_support_name]);
        }
        public function apply_block_supports():array {
            $block_attributes = self::$block_to_render['attrs'];
            $block_type       = TP_Block_Type_Registry::get_instance()->registered(
                self::$block_to_render['blockName']
            );
            if ( ! $block_type || empty( $block_type ) ) return array();
            $output = array();
            foreach ( $this->__block_supports as $block_support_config ) {
                if ( ! isset( $block_support_config['apply'] ) ) continue;
                $new_attributes = call_user_func($block_support_config['apply'],$block_type,$block_attributes);
                if ( ! empty( $new_attributes ) ) {
                    foreach ( $new_attributes as $attribute_name => $attribute_value ) {
                        if ( empty( $output[ $attribute_name ] ) )  $output[ $attribute_name ] = $attribute_value;
                        else $output[ $attribute_name ] .= " $attribute_value";
                    }
                }
            }
            return $output;
        }
        private function __register_attributes():void {
            $block_registry         = TP_Block_Type_Registry::get_instance();
            $registered_block_types = $block_registry->get_all_registered();
            foreach ( $registered_block_types as $block_type ) {
                if ( ! property_exists( $block_type, 'supports' ) ) continue;
                if ( ! $block_type->attributes ) $block_type->attributes = [];
                foreach ( $this->__block_supports as $block_support_config ) {
                    if ( ! isset( $block_support_config['register_attribute'] ) ) continue;
                    call_user_func($block_support_config['register_attribute'],$block_type);
                }
            }
        }
    }
}else die;