<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-5-2022
 * Time: 20:19
 */
namespace TP_Core\Traits\Block;
use TP_Core\Libs\Block\TP_Block_Type_Registry;
use TP_Core\Libs\Block\TP_Block_Supports;
if(ABSPATH){
    trait _block_duotone_02{
        /**
         * @description Render out the duotone stylesheet and SVG.
         * @param $block_content
         * @param $block
         * @return mixed
         */
        protected function _tp_render_duotone_support( $block_content, $block ){
            $block_type = TP_Block_Type_Registry::get_instance()->registered( $block['blockName'] );
            $duotone_support = false;
            if ( $block_type && property_exists( $block_type, 'supports' ) )
                $duotone_support = $this->_tp_array_get( $block_type->supports, ['color', '__experimentalDuotone'], false );
            $has_duotone_attribute = isset( $block['attrs']['style']['color']['duotone'] );
            if (! $duotone_support || ! $has_duotone_attribute) return $block_content;
            $filter_preset   = [
                'slug'   => $this->_tp_unique_id( $this->_sanitize_key( implode( '-', $block['attrs']['style']['color']['duotone'] ) . '-' ) ),
                'colors' => $block['attrs']['style']['color']['duotone'],
            ];
            $filter_property = $this->_tp_get_duotone_filter_property( $filter_preset );
            $filter_id       = $this->_tp_get_duotone_filter_id( $filter_preset );
            $filter_svg      = $this->_tp_get_duotone_filter_svg( $filter_preset );
            $scope     = '.' . $filter_id;
            $selectors = explode( ',', $duotone_support );
            $scoped    = [];
            foreach ( $selectors as $sel ) $scoped[] = $scope . ' ' . trim( $sel );
            $selector = implode( ', ', $scoped );// !important removed because I don't like that
            $filter_style = defined( 'TP_SCRIPT_DEBUG' ) && TP_SCRIPT_DEBUG
                ? $selector . " {\n\t filter: " . $filter_property . ";\n}\n"
                : $selector . "{filter:". $filter_property .";}";
            $this->tp_register_style( $filter_id, false, array(), true, true );
            $this->tp_add_inline_style( $filter_id, $filter_style );
            $this->tp_enqueue_style( $filter_id );
            $svg_function = static function() use ( $filter_svg, $selector ){
                echo $filter_svg;
                if(! (new static)->_is_safari){
                    $script_setup = "<!--suppress BadExpressionStatementJS, UnterminatedStatementJS -->
<script>( const el = document.querySelector('%s'); const display = el.style.display = 'none'; el.offsetHeight; el.style.display = display;)</script>";
                    printf($script_setup, (new static)->_tp_json_encode( $selector ));
                }
            };
            $this->_add_action('tp_footer',$svg_function);
            return preg_replace('/' . preg_quote("class='", "/") . "/","class='duotone {$filter_id}'",$block_content, 1);
        }//505
        protected function _duotone_hooks(): void{
            $block_supports = TP_Block_Supports::get_instance();
            if($block_supports !== null){
                $block_supports->register('duotone',['register_attribute' => [$this,'tp_render_duotone_support'],]);
            }
            $this->_add_filter( 'render_block', [$this,'tp_render_duotone_support'], 10, 2 );
        }
    }
}else die;