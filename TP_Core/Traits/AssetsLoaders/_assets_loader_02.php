<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-5-2022
 * Time: 13:58
 */
namespace TP_Core\Traits\AssetsLoaders;
use TP_Admin\Libs\Adm_Screen;
use TP_Admin\Traits\_adm_screen as _admin_screen;
use TP_Admin\Traits\AdminInits\_adm_init_screen;
use TP_Core\Traits\Inits\_init_block;
use TP_Core\Traits\Inits\_init_assets;
use TP_Core\Libs\Block\TP_Block_Type_Registry;
use TP_Core\Libs\Block\TP_Block_Styles_Registry;
if(ABSPATH){
    trait _assets_loader_02{
        use _admin_screen;
        use _init_block;
        use _adm_init_screen;
        use _init_assets;
        /**
         * @description Determine the concatenation and compression settings for scripts and styles.
         */
        protected function _assets_concat_settings():void{
            $compressed_output = ( ini_get( 'zlib.output_compression' ) || 'ob_gzhandler' === ini_get( 'output_handler' ) );
            $can_compress_scripts = ! $this->_tp_installing() && $this->_get_site_option( 'can_compress_scripts' );
            if ( ! isset( $this->tp_concatenate_scripts ) ) {
                $this->tp_concatenate_scripts = defined( 'CONCATENATE_SCRIPTS' ) ? CONCATENATE_SCRIPTS : true;
                if ( ( defined( 'TP_SCRIPT_DEBUG' ) && TP_SCRIPT_DEBUG ) || ( ! $this->_is_admin() && ! $this->_did_action( 'login_init' )))
                    $this->tp_concatenate_scripts = false;
            }
            if ( ! isset( $this->tp_compress_scripts ) ) {
                $this->tp_compress_scripts = defined( 'COMPRESS_SCRIPTS' ) ? COMPRESS_SCRIPTS : true;
                if ( $this->tp_compress_scripts && ( ! $can_compress_scripts || $compressed_output ) )
                    $this->tp_compress_scripts = false;
            }
            if ( ! isset( $this->tp_compress_css ) ) {
                $this->tp_compress_css = defined( 'COMPRESS_CSS' ) ? COMPRESS_CSS : true;
                if ( $this->tp_compress_css && ( ! $can_compress_scripts || $compressed_output ) )
                    $this->tp_compress_css = false;
            }
        }//2284
        /**
         * @description Handles the enqueueing of block scripts and styles that are common to both
         * @description . the editor and the front-end.
         */
        protected function _tp_common_block_assets():void{
            if ( $this->_is_admin() && ! $this->_tp_should_load_block_editor_assets() )
                return;
            $this->tp_enqueue_style( 'tp_block_library' );
            if ( $this->_current_theme_supports( 'tp_block_styles' ) ) {
                if ( $this->_tp_should_load_separate_core_block_assets() ) {
                    $suffix = defined( 'TP_SCRIPT_DEBUG' ) && TP_SCRIPT_DEBUG ? 'css' : 'min.css';
                    $files  = glob( __DIR__ . "/blocks/**/theme.$suffix" );
                    foreach ( $files as $path ) {
                        $block_name = basename( dirname( $path ) );
                        if ( $this->_is_rtl() && file_exists( __DIR__ . "/blocks/$block_name/theme_rtl.$suffix" ) )
                            $path = __DIR__ . "/blocks/$block_name/theme-rtl.$suffix";
                        $this->tp_add_inline_style( "tp_block_{$block_name}", file_get_contents( $path ) );
                    }
                } else $this->tp_enqueue_style( 'tp_block_library_theme' );
            }
        }//2319
        /**
         * @description Enqueues the global styles defined via theme.json.
         */
        protected function _tp_enqueue_global_styles(): void{
            $separate_assets  = $this->_tp_should_load_separate_core_block_assets();
            $is_block_theme   = $this->_tp_is_block_theme();
            $is_classic_theme = ! $is_block_theme;
            if (
                ( $is_block_theme && $this->_doing_action( 'tp_footer' ) ) ||
                ( $is_classic_theme && $this->_doing_action( 'tp_footer' ) && ! $separate_assets ) ||
                ( $is_classic_theme && $this->_doing_action( 'tp_enqueue_assets' ) && $separate_assets )
            ) return;
            $stylesheet = $this->_tp_get_global_stylesheet();
            if (empty($stylesheet)) return;
            $this->tp_register_style( 'global_styles', false, array(), true, true );
            $this->tp_add_inline_style( 'global_styles', $stylesheet );
            $this->tp_enqueue_style( 'global_styles' );
        }//2360
        /**
         * @description Render the SVG filters supplied by theme.json.
         */
        protected function _get_global_styles_render_svg_filters(): string{
            $_current_screen = $this->_get_current_screen();
            $current_screen = null;
            if( $_current_screen instanceof Adm_Screen ){
                $current_screen = $_current_screen;
            }
            if ( $this->_is_admin() && !$current_screen->is_block_editor())
                return null;
            $filters = $this->_tp_get_global_styles_svg_filters();
            if (!empty($filters)) return $filters;
        }//2400 added
        protected function _tp_global_styles_render_svg_filters():void{
            echo $this->_get_global_styles_render_svg_filters();
        }//2400
        /**
         * @description Checks if the editor scripts and styles for all registered block types
         * @description . should be enqueued on the current screen.
         * @return mixed
         */
        protected function _tp_should_load_block_editor_assets(){
            $tp_screen = $this->_init_get_screen();
            return $this->_apply_filters( 'should_load_block_editor_assets', $tp_screen->is_block_editor() );
        }//2428
        /**
         * @description Checks whether separate styles should be loaded for core blocks on-render.
         * @return bool
         */
        protected function _tp_should_load_separate_core_block_assets():bool{
            if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || $this->_is_admin() || $this->_is_feed()) return false;
            return $this->_apply_filters( 'should_load_separate_core_block_assets', false );
        }// 2465
        /**
         * @description Enqueues registered block scripts and styles, depending on current rendered
         */
        protected function _tp_enqueue_registered_block_assets():void{
            if ( $this->_tp_should_load_separate_core_block_assets()) return;
            $load_editor_scripts = $this->_is_admin() && $this->_tp_should_load_block_editor_assets();
            $block_type_instances = $this->_init_block_type_instance();
            $block_registry = null;
            if($block_type_instances instanceof TP_Block_Type_Registry ){
                $block_registry = $block_type_instances->get_all_registered();
            }
            foreach ((array) $block_registry as $block_name => $block_type ) {
                if ( ! empty( $block_type->style ) ) $this->tp_enqueue_style( $block_type->style );
                if ( ! empty( $block_type->script ) ) $this->tp_enqueue_script( $block_type->script );
                if ( $load_editor_scripts && ! empty( $block_type->editor_style ) )
                    $this->tp_enqueue_style( $block_type->editor_style );
                if ( $load_editor_scripts && ! empty( $block_type->editor_script ) )
                    $this->tp_enqueue_script( $block_type->editor_script );
            }
        }//2492
        /**
         * @description Function responsible for enqueuing the styles required for block styles
         * @description  . functionality on the editor and on the frontend.
         */
        protected function _enqueue_block_styles_assets(): void{
            $this->tp_styles = $this->_init_styles();
            $block_style_instances = $this->_init_block_style_get_instance();
            $block_styles = null;
            if($block_style_instances instanceof TP_Block_Styles_Registry ){
                $block_styles = $block_style_instances->get_all_registered();
            }
            foreach ((array) $block_styles as $block_name => $styles ) {
                foreach ((array) $styles as $style_properties ) {
                    if(isset( $style_properties['style_handle'])){
                        if ( $this->_tp_should_load_separate_core_block_assets() ) {
                            $render_function = function($block) use($block_name, $style_properties ){
                                if ( $block['blockName'] === $block_name ) $this->tp_enqueue_style( $style_properties['style_handle']);
                            };
                            $this->_add_filter('render_block',$render_function,10,2);
                        }else $this->tp_enqueue_style( $style_properties['style_handle']);
                    }
                    if ( isset( $style_properties['inline_style'] ) ){
                        $handle = 'tp_block_library';
                        if ( $this->_tp_should_load_separate_core_block_assets() ){
                            $block_stylesheet_handle = $this->_generate_block_asset_handle( $block_name, 'style' );

                            if ( isset( $this->tp_styles->registered[ $block_stylesheet_handle ] ) )
                                $handle = $block_stylesheet_handle;
                        }
                        $this->tp_add_inline_style( $handle, $style_properties['inline_style'] );
                    }
                }
            }
        }//2491
        /**
         * @description Function responsible for enqueuing the assets
         * @description  . required for block styles functionality on the editor.
         */
        protected function _enqueue_editor_block_styles_assets():void{
            $block_style_instances = $this->_init_block_style_get_instance();
            $block_styles = null;
            if($block_style_instances instanceof TP_Block_Styles_Registry ){
                $block_styles = $block_style_instances->get_all_registered();
            }
            $register_script_lines = ['( function() {'];
            foreach ($block_styles as $block_name => $styles ){
                foreach ( $styles as $style_properties ){
                    $block_style = ['name'  => $style_properties['name'],'label' => $style_properties['label'],];
                    if(isset( $style_properties['is_default'])) $block_style['isDefault'] = $style_properties['is_default'];
                    $register_script_lines[] = sprintf("    tp.blocks.registerBlockStyle( \'%s\', %s );",$block_name, $this->_tp_json_encode( $block_style ));
                }
            }
            $register_script_lines[] = '} )();';
            $inline_script = implode( "\n", $register_script_lines );
            $this->tp_register_script( 'tp_block_styles', false, array( 'tp_blocks' ), true, true );
            $this->tp_add_inline_script( 'tp_block_styles', $inline_script );
            $this->tp_enqueue_script( 'tp_block_styles' );
        }//2584
        /**
         * @description Enqueues the assets required for the block directory within the block editor.
         */
        protected function _tp_enqueue_editor_block_directory_assets(): void {
            $this->tp_enqueue_script( 'tp_block_directory' );
            $this->tp_enqueue_style( 'tp_block_directory' );
        }//2617
    }
}else die;