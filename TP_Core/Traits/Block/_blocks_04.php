<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 20:53
 */
namespace TP_Core\Traits\Block;
use TP_Core\Libs\Queries\TP_Comment_Query;
if(ABSPATH){
    trait _blocks_04 {
        /**
         * @description Helper function that returns the proper pagination arrow html
         * @description . for`QueryPaginationNext` and `QueryPaginationPrevious` blocks based on the provided `paginationArrow`
         * @description . from `QueryPagination` context.
         * @param $block
         * @param $is_next
         * @return null|string
         */
        protected function _get_query_pagination_arrow( $block, $is_next ):?string{
            $arrow_map = ['none'=> '','arrow'=> ['next' => '→', 'previous' => '←',],'chevron' => ['next' => '»','previous' => '«',],];
            if ( ! empty( $block->context['paginationArrow'] ) && array_key_exists( $block->context['paginationArrow'], $arrow_map ) && ! empty( $arrow_map[ $block->context['paginationArrow'] ] ) ) {
                $pagination_type = $is_next ? 'next' : 'previous';
                $arrow_attribute = $block->context['paginationArrow'];
                $arrow           = $arrow_map[ $block->context['paginationArrow'] ][ $pagination_type ];
                $arrow_classes   = "tp_block_query_pagination_{$pagination_type}_arrow is_arrow-$arrow_attribute";
                return "<span class='$arrow_classes'>$arrow</span>";
            }
            return null;
        }//1209
        /**
         * @description Allows multiple block styles.
         * @param $metadata
         * @return mixed
         */
        protected function _tp_multiple_block_styles( $metadata ){
            foreach ( array( 'style', 'editorStyle' ) as $key ) {
                if ( ! empty( $metadata[ $key ] ) && is_array( $metadata[ $key ] ) ) {
                    $default_style = array_shift( $metadata[ $key ] );
                    foreach ( $metadata[ $key ] as $handle ) {
                        $args = ['handle' => $handle];
                        if (isset( $metadata['file'] ) && 0 === strpos( $handle, 'file:' )) {
                            $style_path      = $this->_remove_block_asset_path_prefix( $handle );
                            $theme_path_norm = $this->_tp_normalize_path( $this->_get_theme_file_path() );
                            $style_path_norm = $this->_tp_normalize_path( realpath( dirname( $metadata['file'] ) . '/' . $style_path ) );
                            $is_theme_block  = isset( $metadata['file'] ) && 0 === strpos( $metadata['file'], $theme_path_norm );
                            $style_uri = $this->_includes_url( $style_path, $metadata['file'] );
                            if ( $is_theme_block )
                                $style_uri = $this->_get_theme_file_uri( str_replace( $theme_path_norm, '', $style_path_norm ) );
                            $args = [
                                'handle' => $this->_sanitize_key( "{$metadata['name']}_{$style_path}" ),
                                'src'    => $style_uri,
                            ];
                        }
                        $this->_tp_enqueue_block_style( $metadata['name'], $args );
                    }
                    $metadata[ $key ] = $default_style;
                }
            }
            return $metadata;
        }//1239
        /**
         * @description Helper function that constructs a comment query vars array from the passed block properties.
         * @param $block
         * @return array
         */
        protected function _build_comment_query_vars_from_block( $block ):array{
            $comment_args = ['orderby' => 'comment_date_gmt','order' => 'ASC','status' => 'approve','no_found_rows' => false,];
            if ( $this->_is_user_logged_in() )
                $comment_args['include_unapproved'] = [$this->_get_current_user_id()];
           else {
                $unapproved_email = $this->_tp_get_unapproved_comment_author_email();
                if ( $unapproved_email )
                    $comment_args['include_unapproved'] = [$unapproved_email];
            }
            if ( ! empty( $block->context['postId'] ) )
                $comment_args['post_id'] = (int) $block->context['postId'];
            if ( $this->_get_option( 'thread_comments' ) )
                $comment_args['hierarchical'] = 'threaded';
            else $comment_args['hierarchical'] = false;
            if ( $this->_get_option( 'page_comments' ) === '1' || $this->_get_option( 'page_comments' ) === true ) {
                $per_page     = $this->_get_option( 'comments_per_page' );
                $default_page = $this->_get_option( 'default_comments_page' );
                if ( $per_page > 0 ) {
                    $comment_args['number'] = $per_page;
                    $page = (int) $this->_get_query_var( 'cpage' );
                    if ( $page ) $comment_args['paged'] = $page;
                    elseif ( 'oldest' === $default_page ) $comment_args['paged'] = 1;
                    elseif ( 'newest' === $default_page ) {
                        $max_num_pages = (int) ( new TP_Comment_Query( $comment_args ) )->max_num_pages;
                        if ( 0 !== $max_num_pages ) $comment_args['paged'] = $max_num_pages;
                    }
                    if ( 0 === $page && isset( $comment_args['paged'] ) && $comment_args['paged'] > 0 )
                        $this->_set_query_var( 'cpage', $comment_args['paged'] );
                }
            }
            return $comment_args;
        }//1287
        /**
         * @description Helper function that returns the proper pagination arrow HTML for
         * @description . `CommentsPaginationNext` and `CommentsPaginationPrevious` blocks based on the
         * @description . provided `paginationArrow` from `CommentsPagination` context.
         * @param $block
         * @param string $pagination_type
         * @return null|string
         */
        protected function _get_comments_pagination_arrow( $block, $pagination_type = 'next' ):?string{
            $arrow_map = ['none' => '','arrow' => ['next' => '→','previous' => '←',],'chevron' => ['next' => '»','previous' => '«',],];
            if ( ! empty( $block->context['comments/paginationArrow'] ) && ! empty( $arrow_map[ $block->context['comments/paginationArrow'] ][ $pagination_type ] ) ) {
                $arrow_attribute = $block->context['comments/paginationArrow'];
                $arrow           = $arrow_map[ $block->context['comments/paginationArrow'] ][ $pagination_type ];
                $arrow_classes   = "tp_block_comments_pagination_{$pagination_type}_arrow is_arrow_{$arrow_attribute}";
                return "<span class='$arrow_classes'>$arrow</span>";
            }
            return null;
        }//1359
        protected function _comments_hooks():void{
            $this->_add_filter( 'block_type_metadata',[$this,'tp_multiple_block_styles']);
        }//added
    }
}else die;