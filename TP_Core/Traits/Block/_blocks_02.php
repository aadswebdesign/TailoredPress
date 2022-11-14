<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 20:53
 */
namespace TP_Core\Traits\Block;
//use TP_Managers\Post_Manager\TP_Post;
use TP_Core\Libs\Block\TP_Block_Type;
use TP_Core\Libs\Block\TP_Block_Type_Registry;
if(ABSPATH){
    trait _blocks_02 {
        /**
         * @description Returns an array of the names of all registered dynamic block types.
         * @return array
         */
        protected function _get_dynamic_block_names(): array{
            $dynamic_block_names = [];
            $block_types = TP_Block_Type_Registry::get_instance()->get_all_registered();
            foreach ($block_types as $block_type ) {
                if ($block_type instanceof TP_Block_Type && $block_type->is_dynamic() ) $dynamic_block_names[] = $block_type->name;
            }
            return $dynamic_block_names;
        }//496
        /**
         * @description Given an array of attributes, returns a string in the serialized attributes
         * @param $block_attributes
         * @return mixed
         */
        protected function _serialize_block_attributes( $block_attributes ){
            $encoded_attributes = $this->_tp_json_encode( $block_attributes, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
            $encoded_attributes = str_replace( '/--/', '\\u002d\\u002d', $encoded_attributes );
            $encoded_attributes = preg_replace( '/</', '\\u003c', $encoded_attributes );
            $encoded_attributes = preg_replace( '/>/', '\\u003e', $encoded_attributes );
            $encoded_attributes = preg_replace( '/&/', '\\u0026', $encoded_attributes );
            // Regex: /\\"/
            $encoded_attributes = preg_replace( '/\\\\"/', '\\u0022', $encoded_attributes );
            return $encoded_attributes;
        }//526
        /**
         * @description Returns the block name to use for serialization. This will remove the default
         * @param null $block_name
         * @return null|string
         */
        protected function _strip_core_block_namespace( $block_name = null ): ?string{
            if ( is_string( $block_name ) && 0 === strpos( $block_name, 'core/' ) )
                return substr( $block_name, 5 );
            return $block_name;
        }//547
        /**
         * @description Returns the content of a block, including comment delimiters.
         * @param $block_name
         * @param $block_attributes
         * @param $block_content
         * @return string
         */
        protected function _get_comment_delimited_block_content( $block_name, $block_attributes, $block_content ): string{
            if ( is_null( $block_name ) ) return $block_content;
            $serialized_block_name = $this->_strip_core_block_namespace( $block_name );
            $serialized_attributes = empty( $block_attributes ) ? '' : $this->_serialize_block_attributes( $block_attributes ) . ' ';
            if ( empty( $block_content ) )  return sprintf( '<!-- tp:%s %s/-->', $serialized_block_name, $serialized_attributes );
            return sprintf('<!-- tp:%s %s-->%s<!-- /tp:%s -->',$serialized_block_name,$serialized_attributes,$block_content, $serialized_block_name);
        }//566
        /**
         * @description Returns the content of a block, including comment delimiters,
         * @description . serializing all attributes from the given parsed block.
         * @param $block
         * @return string
         */
        protected function _serialize_block( $block ): string{
            $block_content = '';
            $index = 0;
            foreach ( $block['innerContent'] as $chunk )
                $block_content .= is_string( $chunk ) ? $chunk : $this->_serialize_block( $block['innerBlocks'][ $index++ ] );
            if ( ! is_array( $block['attrs'] ) ) $block['attrs'] = [];
            return $this->_get_comment_delimited_block_content($block['blockName'],$block['attrs'],$block_content);
        }//601
        /**
         * @descriptionReturns a joined string of the aggregate serialization of the given parsed blocks.
         * @param $blocks
         * @return string
         */
        protected function _serialize_blocks( $blocks ): string{
            return implode( '', array_map( '__serialize_block', $blocks ) );
        }//629
        /**
         * @description Filters and sanitizes block content to remove non-allowable HTML from parsed block attribute values.
         * @param $text
         * @param string $allowed_html
         * @param array $allowed_protocols
         * @return string
         */
        protected function _filter_block_content( $text, $allowed_html = 'post', $allowed_protocols = [] ): string{
            $result = '';
            $blocks = $this->_parse_blocks( $text );
            foreach ( $blocks as $block ) {
                $block   = $this->_filter_block_kses( $block, $allowed_html, $allowed_protocols );
                $result .= $this->_serialize_block( $block );
            }
            return $result;
        }//628
        /**
         * @description Filters and sanitizes a parsed block to remove non-allowable HTML from block
         * attribute values.
         * @param $block
         * @param $allowed_html
         * @param array $allowed_protocols
         * @return mixed
         */
        protected function _filter_block_kses( $block, $allowed_html, $allowed_protocols = array() ){
            $block['attrs'] = $this->_filter_block_kses_value( $block['attrs'], $allowed_html, $allowed_protocols );
            if ( is_array( $block['innerBlocks'] ) ) {
                foreach ( $block['innerBlocks'] as $i => $inner_block )
                    $block['innerBlocks'][ $i ] = $this->_filter_block_kses( $inner_block, $allowed_html, $allowed_protocols );
            }
            return $block;
        }//671
        /**
         * @description Filters and sanitizes a parsed block attribute value to remove non-allowable HTML.
         * @param $value
         * @param $allowed_html
         * @param array $allowed_protocols
         * @return array
         */
        protected function _filter_block_kses_value( $value, $allowed_html, $allowed_protocols = [] ): array{
            if ( is_array( $value ) ) {
                foreach ((array)$value as $key => $inner_value ) {
                    $filtered_key   = (string)$this->_filter_block_kses_value( $key, $allowed_html, $allowed_protocols );
                    $filtered_value = $this->_filter_block_kses_value( $inner_value, $allowed_html, $allowed_protocols );
                    if ($filtered_key !== $key ) unset( $value[ $key ] );
                    $value[$filtered_key ] = $filtered_value;
                }
            } elseif ( is_string( $value ) )
                return $this->_tp_kses( $value, $allowed_html, $allowed_protocols );
            return $value;
        }//696
        /**
         * @description Parses blocks out of a content string, and renders those appropriate for the excerpt.
         * @param $content
         * @return string
         */
        protected function _excerpt_remove_blocks( $content ): string{
            $allowed_inner_blocks = [
                null,
                'core/freeform','core/heading','core/html','core/list','core/media-text', 'core/paragraph',
                'core/pre_formatted','core/pull_quote','core/quote','core/table','core/verse',
            ];
            $allowed_wrapper_blocks = ['core/columns','core/column','core/group',];
            $allowed_wrapper_blocks = $this->_apply_filters( 'excerpt_allowed_wrapper_blocks', $allowed_wrapper_blocks );
            $allowed_blocks = array_merge( $allowed_inner_blocks, $allowed_wrapper_blocks );
            $allowed_blocks = $this->_apply_filters( 'excerpt_allowed_blocks', $allowed_blocks );
            $blocks         = $this->_parse_blocks( $content );
            $output         = '';
            foreach ( $blocks as $block ) {
                if ( in_array( $block['blockName'], $allowed_blocks, true ) ) {
                    if ( ! empty( $block['innerBlocks'] ) ) {
                        if ( in_array( $block['blockName'], $allowed_wrapper_blocks, true ) ) {
                            $output .= $this->_excerpt_render_inner_blocks( $block, $allowed_blocks );
                            continue;
                        }
                        foreach ( $block['innerBlocks'] as $inner_block ) {
                            if (! empty( $inner_block['innerBlocks'] || ! in_array( $inner_block['blockName'], $allowed_inner_blocks, true ))) continue 2;
                        }
                    }
                    $output .= $this->_render_block( $block );
                }
            }
            return $output;
        }//726
    }
}else die;