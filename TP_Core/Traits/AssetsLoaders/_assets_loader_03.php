<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-5-2022
 * Time: 13:58
 */
namespace TP_Core\Traits\AssetsLoaders;
use TP_Core\Traits\Inits\_init_assets;
if(ABSPATH){
    trait _assets_loader_03{
        use _init_assets;
        /**
         * @description Enqueues the assets required for the format library within the block editor.
         */
        protected function _tp_enqueue_editor_format_library_assets():void{
            $this->tp_enqueue_script( 'tp_format_library' );
            $this->tp_enqueue_style( 'tp_format_library' );
        }//2626
        /**
         * @description Sanitizes an attributes array into an attributes string to be placed inside a `<script>` tag.
         * @param $attributes
         * @return string
         */
        protected function _tp_sanitize_script_attributes( $attributes ):string{
            static $attributes_string;
            foreach ( $attributes as $attribute_name => $attribute_value )
                $attributes_string = sprintf( ' %1$s="%2$s"', $this->_esc_attr( $attribute_name ), $this->_esc_attr( $attribute_value ) );
            return $attributes_string;
        }//2643
        /**
         * @description Formats `<script>` loader tags.
         * @param $attributes
         * @param null $type
         * @return string
         */
        protected function _tp_get_script_tag( $attributes, $type = null ):string{
            static $_type;
            if(!empty($type)) $_type = " type='{$type}'";
            $type_attr = $_type ?: '';
            $attributes = $this->_apply_filters( 'tp_script_attributes', $attributes,$type_attr );
            return sprintf( "<script%s></script>\n", $this->_tp_sanitize_script_attributes( $attributes ) );
        }//2673
        /**
         * @description Prints formatted `<script>` loader tag.
         * @param $attributes
         * @param null $type
         */
        public function tp_print_script_tag( $attributes, $type = null ):void{
            echo $this->_tp_get_script_tag( $attributes, $type );
        }//2701
        /**
         * @description Wraps inline JavaScript in `<script>` tag.
         * @param $javascript
         * @param array $attributes
         * @param null $type
         * @return string
         */
        protected function _tp_get_inline_script_tag( $javascript,$attributes=[], $type = null):string{
            static $_type;
            if(!empty($type)) $_type = " type='{$type}'";
            $type_attr = $_type ?: '';
            $attributes = $this->_apply_filters( 'tp_inline_script_attributes', $attributes, $javascript,$type_attr );
            $javascript = "\n" . trim( $javascript, "\n\r " ) . "\n";
            return sprintf( "<script%s>%s</script>\n", $this->_tp_sanitize_script_attributes( $attributes ), $javascript );
        }//2717
        /**
         * @description Prints inline JavaScript wrapped in `<script>` tag.
         * @param $javascript
         * @param array $attributes
         * @param null $type
         */
        public function tp_print_inline_script_tag( $javascript, $attributes = [], $type = null ):void{
            echo $this->_tp_get_inline_script_tag( $javascript,$attributes, $type);
        }//2749
        /**
         * @description Allows small styles to be inline.
         */
        protected function _tp_maybe_inline_styles(): void{
            $total_inline_limit = 20000;
            $total_inline_limit = $this->_apply_filters( 'styles_inline_size_limit', $total_inline_limit );
            $styles = [];
            $this->tp_styles = $this->_init_styles();
            foreach ( $this->tp_styles->queue as $handle ) {
                if ( $this->tp_styles->get_data( $handle, 'path' ) && file_exists( $this->tp_styles->registered[$handle ]->extra['path'])){
                    $styles[] = [
                        'handle' => $handle,
                        'src'    => $this->tp_styles->registered[ $handle ]->src,
                        'path'   => $this->tp_styles->registered[ $handle ]->extra['path'],
                        'size'   => filesize( $this->tp_styles->registered[ $handle ]->extra['path'] ),
                    ];
                }
            }
            if(! empty($styles)){
                usort($styles,static function($a,$b){ return ( $a['size'] <= $b['size'] ) ? -1 : 1;});
                $total_inline_size = 0;
                foreach ( $styles as $style ){
                    if ( $total_inline_size + $style['size'] > $total_inline_limit ) break;
                    $style['css'] = file_get_contents( $style['path'] );
                    $style['css'] = $this->_tp_normalize_relative_css_links( $style['css'], $style['src'] );
                    $this->tp_styles->registered[ $style['handle'] ]->src = false;
                    if ( empty( $this->tp_styles->registered[ $style['handle'] ]->extra['after'] ) )
                        $this->tp_styles->registered[ $style['handle'] ]->extra['after'] = array();
                    array_unshift( $this->tp_styles->registered[ $style['handle'] ]->extra['after'], $style['css'] );
                    $total_inline_size += (int) $style['size'];
                }
            }
        }//2765
        /**
         * @description Make URLs relative to the TailoredPress installation.
         * @param $css
         * @param $stylesheet_url
         * @return mixed
         */
        protected function _tp_normalize_relative_css_links($css, $stylesheet_url){
            $has_src_results = preg_match_all( '#url\s*\(\s*[\'"]?\s*([^\'"\)]+)#', $css, $src_results );
            if ( $has_src_results ){
                foreach($src_results[1] as $src_index => $src_result){
                    if ( 0 === strpos( $src_result, 'http' ) || 0 === strpos( $src_result, '//' ) )
                        continue;
                    if ( $this->_tp_str_starts_with( $src_result, '#' ) ) continue;
                    if ( $this->_tp_str_starts_with( $src_result, 'data:' ) ) continue;
                    $absolute_url = dirname( $stylesheet_url ) . '/' . $src_result;
                    $absolute_url = str_replace( '/./', '/', $absolute_url );
                    $relative_url = $this->_tp_make_link_relative( $absolute_url );
                    $css = str_replace(
                        $src_results[0][ $src_index ],
                        str_replace( $src_result, $relative_url, $src_results[0][ $src_index ] ),
                        $css
                    );
                }
            }
            return $css;
        }//2848
        /**
         * @description Function that enqueues the CSS Custom Properties coming from theme.json.
         */
        protected function _tp_enqueue_global_styles_css_custom_properties():void{
            $this->tp_register_style( 'global_styles_css_custom_properties', false, [], true, true );
            $this->tp_add_inline_style( 'global_styles_css_custom_properties', $this->_tp_get_global_stylesheet( array( 'variables' ) ) );
            $this->tp_enqueue_style( 'global_styles_css_custom_properties' );
        }//2891
        /**
         * @description This function takes care of adding inline styles
         * in the proper place, depending on the theme in use.
         * @param $style
         */
        protected function _tp_enqueue_block_support_styles( $style ):void{
            $action_hook_name = 'tp_footer';
            if ( $this->_tp_is_block_theme() ) $action_hook_name = 'tp_head';
            $this->_add_action($action_hook_name,static function () use ( $style ) {    echo "<style>$style</style>\n";});
        }//2912
    }
}else die;