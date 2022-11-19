<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 08:20
 */
namespace TP_Core\Libs\Block;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\RestApi\_rest_api_06;
if(ABSPATH){
    class TP_Block_Type{
        use _filter_01;
        use _rest_api_06;
        use _init_error;
        use _methods_10;
        public $api_version = 1;
        public $name;
        public $title = '';
        public $category;
        public $parent;
        public $icon;
        public $description = '';
        public $keywords = [];
        public $textdomain;
        public $styles = [];
        public $variations = [];
        public $supports;
        public $example;
        public $render_callback;
        public $attributes;
        public $uses_context;
        public $provides_context;
        public $editor_script;
        public $script;
        public $view_script;
        public $editor_style;
        public $style;
        public function __construct( $block_type, $args = array() ) {
            $this->name = $block_type;
            $this->set_props( $args );
        }
        public function render( $attributes = array(), $content = '' ): string{
            if ( ! $this->is_dynamic() ) return '';
            $attributes = $this->prepare_attributes_for_render( $attributes );
            return (string) call_user_func( $this->render_callback, $attributes, $content );
        }
        public function is_dynamic(): bool{
            return is_callable( $this->render_callback );
        }
        public function prepare_attributes_for_render( $attributes ) {
            if ( ! isset( $this->attributes ) ) return $attributes;
            foreach ( $attributes as $attribute_name => $value ) {
                if ( ! isset( $this->attributes[ $attribute_name ] ) ) continue;
                $schema = $this->attributes[ $attribute_name ];
                $is_valid = $this->_rest_validate_value_from_schema( $value, $schema, $attribute_name );
                if ( $this->_init_error( $is_valid ) ) unset( $attributes[ $attribute_name ] );
            }
            $missing_schema_attributes = array_diff_key( $this->attributes, $attributes );
            foreach ( $missing_schema_attributes as $attribute_name => $schema )
                if ( isset( $schema['default'] ) ) $attributes[ $attribute_name ] = $schema['default'];
            return $attributes;
        }
        public function set_props( $args ): void{
            $args = $this->_tp_parse_args($args,['render_callback' => null,]);
            $args['name'] = $this->name;
            $args = $this->_apply_filters( 'register_block_type_args', $args, $this->name );
            foreach ( $args as $property_name => $property_value ) $this->$property_name = $property_value;
        }
        public function get_attributes(): ?array{
            return is_array( $this->attributes ) ?  $this->attributes :  [];
        }
    }
}else die;