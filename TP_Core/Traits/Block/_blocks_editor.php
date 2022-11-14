<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 23:14
 */
namespace TP_Core\Traits\Block;
use TP_Core\Traits\Inits\_init_block;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_post;
use TP_Core\Traits\Inits\_init_assets;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\JSON\TP_Theme_JSON_Resolver;
use TP_Core\Libs\Block\TP_Block_Editor_Context;
use TP_Core\Libs\Block\TP_Block_Styles_Registry;
use TP_Core\Libs\Block\TP_Block_Scripts_Registry;
if(ABSPATH){
    trait _blocks_editor{
        use _init_post, _init_assets, _init_error,_init_block;
        /**
         * @return array
         * @description Returns the list of default categories for block types.
         */
        protected function _get_default_block_categories():array{
            return [
                ['slug'  => 'text','title' => $this->_x( 'Text', 'block category' ),'icon'  => null,],
                ['slug'  => 'media','title' => $this->_x( 'Media', 'block category' ),'icon'  => null,],
                ['slug'  => 'design','title' => $this->_x( 'Design', 'block category' ),'icon'  => null,],
                ['slug'  => 'widgets','title' => $this->_x( 'Widgets', 'block category' ),'icon'  => null,],
                ['slug'  => 'theme','title' => $this->_x( 'Theme', 'block category' ),'icon'  => null,],
                ['slug'  => 'embed','title' => $this->_x( 'Embeds', 'block category' ),'icon' => null,],
                ['slug'  => 'reusable','title' => $this->_x( 'Reusable Blocks', 'block category' ),'icon' => null,],
            ];
        }//17 from block-editor
        /**
         * @description Returns all the categories for block types that will be shown in the block editor.
         * @param $post_or_block_editor_context
         * @return array
         */
        protected function _get_block_categories( $post_or_block_editor_context ):array{
            $block_categories     = $this->_get_default_block_categories();
            $block_editor_context = $post_or_block_editor_context instanceof TP_Post ?
                new TP_Block_Editor_Context(['post' => $post_or_block_editor_context,]) : $post_or_block_editor_context;
            $block_categories = $this->_apply_filters( 'block_categories_all', $block_categories, $block_editor_context );
            return $block_categories;
        }//68 from block-editor
        /**
         * @description Gets the list of allowed block types to use in the block editor.
         * @param $block_editor_context
         * @return bool
         */
        protected function _get_allowed_block_types( $block_editor_context ):bool{
            $allowed_block_types = true;
            $allowed_block_types = $this->_apply_filters( 'allowed_block_types_all', $allowed_block_types, $block_editor_context );
            return $allowed_block_types;
        }//114 from block-editor
        /**
         * @description Returns the default block editor settings.
         * @return array
         */
        protected function _get_default_block_editor_settings():array{
            $max_upload_size = $this->_tp_max_upload_size();
            if ( ! $max_upload_size ) $max_upload_size = 0;
            $image_size_names = $this->_apply_filters('image_size_names_choose',
                ['thumbnail' => $this->__( 'Thumbnail' ), 'medium' => $this->__( 'Medium' ), 'large' => $this->__( 'Large' ), 'full' => $this->__( 'Full Size' ),]
            );
            $available_image_sizes = [];
            foreach ( $image_size_names as $image_size_slug => $image_size_name )
                $available_image_sizes[] = ['slug' => $image_size_slug, 'name' => $image_size_name,];
            $default_size       = $this->_get_option( 'image_default_size', 'large' );
            $image_default_size = array_key_exists($default_size, $image_size_names) ? $default_size : 'large';
            $image_dimensions = array();
            $all_sizes        = $this->_tp_get_registered_image_sub_sizes();
            foreach ( $available_image_sizes as $size ) {
                $key = $size['slug'];
                if ( isset( $all_sizes[ $key ] ) ) $image_dimensions[ $key ] = $all_sizes[ $key ];
            }
            $default_editor_styles_file = TP_CORE_ASSETS . '/css/dist/block-editor/default-editor-styles.css';
            if ( file_exists( $default_editor_styles_file ) ) $default_editor_styles = [['css' => file_get_contents( $default_editor_styles_file )],];
            else $default_editor_styles = [];
            $editor_settings = ['alignWide' => $this->_get_theme_support( 'align-wide' ),'allowedBlockTypes' => true,
                'allowedMimeTypes' => $this->_get_allowed_mime_types(),'defaultEditorStyles' => $default_editor_styles,
                'blockCategories' => $this->_get_default_block_categories(),'disableCustomColors' => $this->_get_theme_support( 'disable-custom-colors' ),
                'disableCustomFontSizes' => $this->_get_theme_support( 'disable-custom-font-sizes' ),'disableCustomGradients' => $this->_get_theme_support( 'disable-custom-gradients' ),
                'enableCustomLineHeight' => $this->_get_theme_support( 'custom-line-height' ),'enableCustomSpacing' => $this->_get_theme_support( 'custom-spacing' ),
                'enableCustomUnits' => $this->_get_theme_support( 'custom-units' ),'isRTL' => $this->_is_rtl(),'imageDefaultSize' => $image_default_size,
                'imageDimensions' => $image_dimensions,'imageEditing' => true,'imageSizes' => $available_image_sizes, 'maxUploadFileSize' => $max_upload_size,
                // The following flag is required to enable the new Gallery block format on the mobile apps in 5.9.
                '__unstableGalleryWithImageBlocks' => true,];
            $color_palette = current( $this->_get_theme_support( 'editor-color-palette' ) );
            if ( false !== $color_palette ) $editor_settings['colors'] = $color_palette;
            $font_sizes = current($this->_get_theme_support( 'editor-font-sizes' ) );
            if ( false !== $font_sizes ) $editor_settings['fontSizes'] = $font_sizes;
            $gradient_presets = current( $this->_get_theme_support( 'editor-gradient-presets' ) );
            if ( false !== $gradient_presets ) $editor_settings['gradients'] = $gradient_presets;
            return $editor_settings;
        }//154 from block-editor
        //@description Returns the block editor settings needed to use the Legacy Widget block which is not registered by default.
        //protected function _get_legacy_widget_block_editor_settings(){}//252 from block-editor todo
        /**
         * @description Returns the contextualized block editor settings for a selected editor context.
         * @param array $custom_settings
         * @param $block_editor_context
         * @return array
         */
        protected function _get_block_editor_settings( array $custom_settings, $block_editor_context ): array{
            $editor_settings = array_merge(
                $this->_get_default_block_editor_settings(),
                ['allowedBlockTypes' => $this->_get_allowed_block_types( $block_editor_context ), 'blockCategories'   => $this->_get_block_categories( $block_editor_context ),],
                $custom_settings
            );
            $global_styles = [];
            $presets  = [['css' => 'variables', '__unstableType' => 'presets',],['css' => 'presets','__unstableType' => 'presets',],];
            foreach ( $presets as $preset_style ) {
                $actual_css = $this->_tp_get_global_stylesheet( array( $preset_style['css'] ) );
                if ( '' !== $actual_css ) {
                    $preset_style['css'] = $actual_css;
                    $global_styles[] = $preset_style;
                }
            }
            if ( TP_Theme_JSON_Resolver::theme_has_support() ) {
                $block_classes = [ 'css' => 'styles', '__unstableType' => 'theme',];
                $actual_css    = $this->_tp_get_global_stylesheet( array( $block_classes['css'] ) );
                if ( '' !== $actual_css ) {
                    $block_classes['css'] = $actual_css;
                    $global_styles[] = $block_classes;
                }
            }
            $editor_settings['styles'] = array_merge( $global_styles, $this->_get_block_editor_theme_styles() );
            $editor_settings['__experimentalFeatures'] = $this->_tp_get_global_settings();
            // These settings may need to be updated based on data coming from theme.json sources.
            if ( isset( $editor_settings['__experimentalFeatures']['color']['palette'] ) ) {
                $colors_by_origin          = $editor_settings['__experimentalFeatures']['color']['palette'];
                $_color_setting = $colors_by_origin['theme'] ??  $colors_by_origin['default'];
                $editor_settings['colors'] = $colors_by_origin['custom'] ?? $_color_setting;
            }
            if ( isset( $editor_settings['__experimentalFeatures']['color']['gradients'] ) ) {
                $gradients_by_origin          = $editor_settings['__experimentalFeatures']['color']['gradients'];
                $gradient_setting = $gradients_by_origin['theme'] ?? $gradients_by_origin['default'];
                $editor_settings['gradients'] = $gradients_by_origin['custom'] ?? $gradient_setting;
            }
            if ( isset( $editor_settings['__experimentalFeatures']['typography']['fontSizes'] ) ) {
                $font_sizes_by_origin         = $editor_settings['__experimentalFeatures']['typography']['fontSizes'];
                $_font_setting = $font_sizes_by_origin['theme'] ??  $font_sizes_by_origin['default'];
                $editor_settings['fontSizes'] = $font_sizes_by_origin['custom'] ?? $_font_setting;
            }
            if ( isset( $editor_settings['__experimentalFeatures']['color']['custom'] ) ) {
                $editor_settings['disableCustomColors'] = ! $editor_settings['__experimentalFeatures']['color']['custom'];
                unset( $editor_settings['__experimentalFeatures']['color']['custom'] );
            }
            if ( isset( $editor_settings['__experimentalFeatures']['color']['customGradient'] ) ) {
                $editor_settings['disableCustomGradients'] = ! $editor_settings['__experimentalFeatures']['color']['customGradient'];
                unset( $editor_settings['__experimentalFeatures']['color']['customGradient'] );
            }
            if ( isset( $editor_settings['__experimentalFeatures']['typography']['customFontSize'] ) ) {
                $editor_settings['disableCustomFontSizes'] = ! $editor_settings['__experimentalFeatures']['typography']['customFontSize'];
                unset( $editor_settings['__experimentalFeatures']['typography']['customFontSize'] );
            }
            if ( isset( $editor_settings['__experimentalFeatures']['typography']['lineHeight'] ) ) {
                $editor_settings['enableCustomLineHeight'] = $editor_settings['__experimentalFeatures']['typography']['lineHeight'];
                unset( $editor_settings['__experimentalFeatures']['typography']['lineHeight'] );
            }
            if ( isset( $editor_settings['__experimentalFeatures']['spacing']['units'] ) ) {
                $editor_settings['enableCustomUnits'] = $editor_settings['__experimentalFeatures']['spacing']['units'];
                unset( $editor_settings['__experimentalFeatures']['spacing']['units'] );
            }
            if ( isset( $editor_settings['__experimentalFeatures']['spacing']['padding'] ) ) {
                $editor_settings['enableCustomSpacing'] = $editor_settings['__experimentalFeatures']['spacing']['padding'];
                unset( $editor_settings['__experimentalFeatures']['spacing']['padding'] );
            }
            $editor_settings = $this->_apply_filters( 'block_editor_settings_all', $editor_settings, $block_editor_context );
            return $editor_settings;
        }//300 from block-editor
        /**
         * @description  Preloads common data used with the block editor by specifying an array of REST API paths that will be preloaded for a given block editor context.
         * @param array $preload_paths
         * @param $block_editor_context
         */
        protected function _block_editor_rest_api_preload( array $preload_paths, $block_editor_context ): void{
            $preload_paths = $this->_apply_filters( 'block_editor_rest_api_preload_paths', $preload_paths, $block_editor_context );
            $tp_posts = $this->_init_post($preload_paths);
            $tp_block_scripts_instances = TP_Block_Scripts_Registry::get_instance();
            $all_scripts = null;
            if($tp_block_scripts_instances instanceof TP_Block_Scripts_Registry){
                $all_scripts = $tp_block_scripts_instances->get_all_registered();
            }
            $tp_block_styles_instances = TP_Block_Styles_Registry::get_instance();
            $all_styles = null;
            if($tp_block_styles_instances instanceof TP_Block_Scripts_Registry){
                $all_styles = $tp_block_styles_instances->get_all_registered();
            }
            if ($tp_posts === null) return;
            if($block_editor_context instanceof \stdClass && $block_editor_context->post !== null){ //todo replace stdClass
                $selected_post = $block_editor_context->post;
                $preload_paths = $this->_apply_filters_deprecated( 'block_editor_preload_paths', array( $preload_paths, $selected_post ), '0.0.1', 'block_editor_rest_api_preload_paths' );
            }
            if ( empty( $preload_paths ) ){
                return;
            }
            $backup_tp_post = $tp_posts !== null ? clone $tp_posts : $tp_posts;
            $backup_tp_scripts  = ! empty( $all_scripts ) ? clone $all_scripts : $all_scripts;
            $backup_tp_styles   = ! empty( $all_styles ) ? clone $all_styles : $all_styles;
            foreach ( $preload_paths as &$path ) {
                if ( is_string( $path ) && ! $this->_tp_str_starts_with( $path, '/' ) ) {
                    $path = '/' . $path;
                    continue;
                }
                if ( is_array( $path ) && is_string( $path[0] ) && ! $this->_tp_str_starts_with( $path[0], '/' ) )  $path[0] = '/' . $path[0];
            }
            unset( $path );
            $preload_data = array_reduce($preload_paths,'rest_preload_api_request',[]);
            $this->tp_post    = $backup_tp_post;
            $this->tp_scripts = $backup_tp_scripts;
            $this->tp_styles  = $backup_tp_styles;
            $this->tp_add_inline_script( 'tp-api-fetch', sprintf('tp.apiFetch.use( tp.apiFetch.createPreloadingMiddleware( %s ) );', $this->_tp_json_encode( $preload_data )), 'after' );
        }//440 from block-editor
        /**
         * @description Creates an array of theme styles to load into the block editor.
         * @return array
         */
        protected function _get_block_editor_theme_styles():array{
            $styles =[];
            $block_style_instances = TP_Block_Styles_Registry::get_instance();
            $block_styles = null;
            if($block_style_instances instanceof TP_Block_Styles_Registry ){
                $block_styles = $block_style_instances->get_all_registered();
            }
            if ( $block_styles && $this->_current_theme_supports( 'editor-styles' ) ) {
                foreach ( $block_styles as $style ) {
                    if ( preg_match( '~^(https?:)?//~', $style ) ) {
                        $response = $this->_tp_remote_get( $style );
                        if ( ! $this->_init_error( $response ) )
                            $styles[] = ['css' => $this->_tp_remote_retrieve_body( $response ),  '__unstableType' => 'theme',];
                    } else {
                        $file = $this->_get_theme_file_path( $style );
                        if ( is_file( $file ) )
                            $styles[] = ['css' => file_get_contents( $file ), 'baseURL' => $this->_get_theme_file_uri( $style ), '__unstableType' => 'theme',];
                    }
                }
            }
            return $styles;
        }//528 from block-editor
    }
}else die;