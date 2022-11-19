<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 12:27
 */
declare(strict_types=1);
namespace TP_Core\Libs\Block;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Methods\_methods_10;
if(ABSPATH){
    class TP_Block{
        //todo get this done
        use _filter_01;
        use _methods_10;
        protected $_attributes;
        protected $_available_context;
        protected $_registry;
        public $post;
        public $parsed_block;
        public $name;
        public $block_type;
        public $context =[];
        public $inner_blocks =[];
        public $inner_html ='';
        public $inner_content =[];
        public function __construct($block,$available_context = [],$registry = null){
            $this->parsed_block = $block;
            $this->name         = $block['blockName'];
            if (is_null($registry))
                $registry = TP_Block_Type_Registry::get_instance();

            $this->_registry = $registry;
            $this->block_type = $registry->registered( $this->name );
            $this->_available_context = $available_context;
            if ( ! empty( $this->block_type->uses_context ) ) {
                foreach ( $this->block_type->uses_context as $context_name ) {
                    if ( array_key_exists( $context_name, $this->_available_context ) )
                        $this->context[ $context_name ] = $this->_available_context[ $context_name ];
                }
            }
            if ( ! empty( $block['innerBlocks'] ) ) {
                $child_context = $this->_available_context;
                if ( ! empty( $this->block_type->provides_context ) ) {
                    foreach ( $this->block_type->provides_context as $context_name => $attribute_name ) {
                        if ( array_key_exists( $attribute_name, $this->_attributes ) )
                            $child_context[ $context_name ] = $this->_attributes[ $attribute_name ];
                    }
                }
                $this->inner_blocks = new TP_Block_List( $block['innerBlocks'], $child_context, $registry );
            }
            if ( ! empty( $block['innerHTML'] ) ) $this->inner_html = $block['innerHTML'];
            if ( ! empty( $block['innerContent'] ) ) $this->inner_content = $block['innerContent'];
        }
        private function __get($name){
            if ( 'attributes' === $name ) {
                $this->_attributes = isset( $this->parsed_block['attrs'] ) ?: [];
                if ( ! is_null( $this->block_type ) )
                    $this->_attributes = $this->block_type->prepare_attributes_for_render( $this->_attributes );
                return $this->_attributes;
            }
            return null;
        }
        public function post_get($name){
            return $this->__get($name);
        }
        //todo tp_enqueue_script
        public function render($options=[]){
            $options = $this->_tp_parse_args($options,['dynamic' => true,]);
            $is_dynamic    = $options['dynamic'] && $this->name && null !== $this->block_type && $this->block_type->is_dynamic();
            $block_content = '';
            if ( ! $options['dynamic'] || empty( $this->block_type->skip_inner_blocks ) ) {
                $index = 0;
                foreach ( $this->inner_content as $chunk ) {
                    if ( is_string( $chunk ) ) $block_content .= $chunk;
                    else {
                        $inner_block  = $this->inner_blocks[ $index ];
                        $parent_block = $this;
                        $pre_render = $this->_apply_filters( 'pre_render_block', null, $inner_block->parsed_block, $parent_block );
                        if ( ! is_null( $pre_render ) ) $block_content .= $pre_render;
                        else {
                            $source_block = $inner_block->parsed_block;
                            $inner_block->parsed_block = $this->_apply_filters( 'render_block_data', $inner_block->parsed_block, $source_block, $parent_block );
                            $inner_block->context = $this->_apply_filters( 'render_block_context', $inner_block->context, $inner_block->parsed_block, $parent_block );
                            $block_content .= $inner_block->render();
                        }
                        $index++;
                    }
                }
            }
            if ( $is_dynamic ) {
                $global_post = $this->post;
                $parent      = TP_Block_Supports::$block_to_render;
                TP_Block_Supports::$block_to_render = $this->parsed_block;
                $block_content = (string) call_user_func( $this->block_type->render_callback, $this->_attributes, $block_content, $this );
                TP_Block_Supports::$block_to_render = $parent;
                $this->post = $global_post;
            }
            if ( ! empty( $this->block_type->script ) ) {
                //todo wp_enqueue_script( $this->block_type->script );
            }
            if ( ! empty( $this->block_type->view_script ) && empty( $this->block_type->render_callback ) ) {
                //todo wp_enqueue_script( $this->block_type->view_script );
            }
            if ( ! empty( $this->block_type->style ) ) {
                //todo wp_enqueue_style( $this->block_type->style );
            }
            $block_content = $this->_apply_filters( 'render_block', $block_content, $this->parsed_block, $this );
            $block_content = $this->_apply_filters( "render_block_{$this->name}", $block_content, $this->parsed_block, $this );
            return $block_content;
        }
    }
}else die;