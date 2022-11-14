<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 20:53
 */
namespace TP_Core\Traits\Block;
use TP_Core\Libs\Block\TP_Block_Parser;
use TP_Core\Libs\Block\TP_Block_Styles_Registry;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Traits\Inits\_init_block;
use TP_Core\Traits\Inits\_init_post;
if(ABSPATH){
    trait _blocks_03 {
        use _init_block;
        use _init_post;
        /**
         * @description Render inner blocks from the allowed wrapper blocks for generating an excerpt.
         * @param $parsed_block
         * @param $allowed_blocks
         * @return string
         */
        protected function _excerpt_render_inner_blocks( $parsed_block, $allowed_blocks ): string{
            $output = '';
            foreach ( $parsed_block['innerBlocks'] as $inner_block ) {
                if ( ! in_array( $inner_block['blockName'], $allowed_blocks, true ) ) continue;
                if ( empty( $inner_block['innerBlocks'] ) ) $output .= $this->_render_block( $inner_block );
                else $output .= $this->_excerpt_render_inner_blocks( $inner_block, $allowed_blocks );
            }
            return $output;
        }//812
        /**
         * @description Renders a single block into a HTML string.
         * @param $parsed_block
         * @return string
         */
        protected function _render_block( $parsed_block ): string{
            $parent_block = null;
            $pre_render = $this->_apply_filters( 'pre_render_block', null, $parsed_block, $parent_block );
            if ( ! is_null( $pre_render ) ) return $pre_render;
            $source_block = $parsed_block;
            $parsed_block = $this->_apply_filters( 'render_block_data', $parsed_block, $source_block, $parent_block );
            $context = [];
            if ( $this->tp_post instanceof TP_Post ) {
                $context['postId'] = $this->tp_post->ID;
                $context['postType'] = $this->tp_post->post_type;
            }
            $context = $this->_apply_filters( 'render_block_context', $context, $parsed_block, $parent_block );
            return $this->_init_block( $parsed_block, $context )->render();
        }//840
        /**
         * @description Parses blocks out of a content string.
         * @param $content
         * @return mixed
         */
        protected function _parse_blocks( $content ){
            $block_parser = new TP_Block_Parser();
            $block_parser = $this->_apply_filters( 'block_parser_class', $block_parser );
            $parser = null;
            if($block_parser instanceof TP_Block_Parser){
                $parser = $this->_init_block_parser()->parse( $content );
            }
            return $parser;
        }//912
        /**
         * @description Parses dynamic blocks out of `post_content` and re-renders them.
         * @param $content
         * @return string
         */
        protected function _do_blocks( $content ): string{
            $blocks = $this->_parse_blocks( $content );
            $output = '';
            foreach ( $blocks as $block ) $output .= $this->_render_block( $block );
            $priority = $this->_has_filter( 'the_content', 'tp_autop' );
            if ( false !== $priority && $this->_doing_filter( 'the_content' ) && $this->_has_blocks( $content ) ) {
                $this->_remove_filter( 'the_content', 'tp_autop', $priority );
                $this->_add_filter( 'the_content', '_restore_tp_autop_hook', $priority + 1 );
            }
            return $output;
        }//934
        /**
         * @description If do_blocks() needs to remove tp_autop() from the `the_content` filter,
         * @description . this re-adds it afterwards, for subsequent `the_content` usage.
         * @param $content
         * @return mixed
         */
        protected function _restore_tp_autop_hook( $content ){
            $current_priority = $this->_has_filter( 'the_content', '_restore_tp_autop_hook' );
            $this->_add_filter( 'the_content', 'tp_autop', $current_priority - 1 );
            $this->_remove_filter( 'the_content', '_restore_tp_autop_hook', $current_priority );
            return $content;
        }//962
        /**
         * @param $content
         * @return float|int
         */
        protected function _block_version( $content ){
            return $this->_has_blocks( $content ) ? 0.1 : 0;
        }//981
        /**
         * @description Registers a new block style.
         * @param $block_name
         * @param $style_properties
         * @return bool
         */
        protected function _register_block_style( $block_name, $style_properties ): bool{
            $registry = TP_Block_Styles_Registry::get_instance();
            $register = null;
            if($registry !== null){
                $register = $registry->register( $block_name, $style_properties );
            }
            return $register;
        }//996
        /**
         * @description Unregisters a block style.
         * @param $block_name
         * @param $block_style_name
         * @return bool
         */
        protected function _unregister_block_style( $block_name, $block_style_name ): bool{
            $registry = TP_Block_Styles_Registry::get_instance();
            $unregister = null;
            if($registry !== null){
                $unregister = $registry->unregister( $block_name, $block_style_name);
            }
            return $unregister;
        }//992
        /**
         * @description Checks whether the current block type supports the feature requested.
         * @param $block_type
         * @param $feature
         * @param bool $default
         * @return bool
         */
        protected function _block_has_support( $block_type, $feature, $default = false ): bool{
            $block_support = $default;
            if ( $block_type && property_exists( $block_type, 'supports' ) )
                $block_support = $this->_tp_array_get( $block_type->supports, $feature, $default );
            return true === $block_support || is_array( $block_support );
        }//1023
        /**
         * @description Helper function that constructs a TP_Query args array from a `Query` block properties.
         * @param $block
         * @param $page
         * @return array
         */
        protected function _build_query_vars_from_query_block( $block, $page ): array{
            $query = ['post_type' => 'post','order' => 'DESC','orderby' => 'date','post__not_in' => [],];
            if ( isset( $block->context['query'] ) ) {
                if ( ! empty( $block->context['query']['postType'] ) ) {
                    $post_type_param = $block->context['query']['postType'];
                    if ( $this->_is_post_type_viewable( $post_type_param ) ) $query['post_type'] = $post_type_param;
                }
                if ( isset( $block->context['query']['sticky'] ) && ! empty( $block->context['query']['sticky'] ) ) {
                    $sticky = $this->_get_option( 'sticky_posts' );
                    if ( 'only' === $block->context['query']['sticky'] ) $query['post__in'] = $sticky;
                    else  $query['post__not_in'] = array_merge( $query['post__not_in'], $sticky );
                }
                if ( ! empty( $block->context['query']['exclude'] ) ) {
                    $excluded_post_ids     = array_map( 'intval', $block->context['query']['exclude'] );
                    $excluded_post_ids     = array_filter( $excluded_post_ids );
                    $query['post__not_in'] = array_merge( $query['post__not_in'], $excluded_post_ids );
                }
                if (isset( $block->context['query']['perPage'] ) && is_numeric( $block->context['query']['perPage'] )){
                    $per_page = $this->_abs_int( $block->context['query']['perPage'] );
                    $offset   = 0;
                    if (isset( $block->context['query']['offset'] ) && is_numeric( $block->context['query']['offset'] ))
                        $offset = $this->_abs_int( $block->context['query']['offset'] );
                    $query['offset']         = ( $per_page * ( $page - 1 ) ) + $offset;
                    $query['posts_per_page'] = $per_page;
                }
                if ( ! empty( $block->context['query']['categoryIds'] ) ) {
                    $term_ids              = array_map( 'intval', $block->context['query']['categoryIds'] );
                    $term_ids              = array_filter( $term_ids );
                    $query['category__in'] = $term_ids;
                }
                if ( ! empty( $block->context['query']['tagIds'] ) ) {
                    $term_ids         = array_map( 'intval', $block->context['query']['tagIds'] );
                    $term_ids         = array_filter( $term_ids );
                    $query['tag__in'] = $term_ids;
                }
                if (isset( $block->context['query']['order'] ) && in_array( strtoupper( $block->context['query']['order'] ), array( 'ASC', 'DESC' ), true ))
                    $query['order'] = strtoupper( $block->context['query']['order'] );
                if ( isset( $block->context['query']['orderBy'] ) ) $query['orderby'] = $block->context['query']['orderBy'];
                if (isset( $block->context['query']['author'] )&& (int) $block->context['query']['author'] > 0)
                    $query['author'] = (int) $block->context['query']['author'];
                if ( ! empty( $block->context['query']['search'] ) ) $query['s'] = $block->context['query']['search'];
            }
            return $query;
        }//1097
    }
}else die;