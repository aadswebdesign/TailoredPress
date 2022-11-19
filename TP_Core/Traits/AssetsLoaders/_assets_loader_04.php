<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-5-2022
 * Time: 13:58
 */
namespace TP_Core\Traits\AssetsLoaders;
use TP_Core\Libs\JSON\TP_Theme_JSON_Resolver;
if(ABSPATH){
    trait _assets_loader_04{
        /**
         * @description Enqueues a stylesheet for a specific block.
         * @param $block_name
         * @param $args
         */
        protected function _tp_enqueue_block_style( $block_name, $args ):void{
            $args = $this->_tp_parse_args(
                $args,['handle' => '','src' => '','deps' => [],'ver' => false, 'media' => 'all',]
            );
            $callback = static function( $content ) use ( $args ) {
                if ( ! empty( $args['src'] ) )
                    (new self)->tp_register_style( $args['handle'], $args['src'], $args['deps'], $args['ver'], $args['media'] );
                if ( isset( $args['path'] ) ) {
                    (new self)->tp_style_add_data( $args['handle'], 'path', $args['path'] );
                    $rtl_file_path = str_replace( '.css', '-rtl.css', $args['path'] );
                    if ( file_exists( $rtl_file_path ) ) {
                        (new self)->tp_style_add_data( $args['handle'], 'rtl', 'replace' );
                        if ( (new self)->_is_rtl() ) (new self)->tp_style_add_data( $args['handle'], 'path', $rtl_file_path );
                    }
                }
                (new self)->tp_enqueue_style( $args['handle'] );
                return $content;
            };
            $hook = $this->_did_action( 'tp_enqueue_scripts' ) ? 'tp_footer' : 'tp_enqueue_scripts';
            if ( $this->_tp_should_load_separate_core_block_assets() ) {
                $callback_separate = static function( $content, $block ) use ( $block_name, $callback ) {
                    if ( ! empty( $block['blockName'] ) && $block_name === $block['blockName'] )
                        return $callback( $content );
                    return $content;
                };
                $this->_add_filter( 'render_block', $callback_separate, 10, 2 );
                return;
            }
            $this->_add_filter( $hook, $callback );
            $this->_add_action( 'enqueue_block_assets', $callback );
        }//2937
        /**
         * @description Runs the theme.json webfonts handler.
         */
        protected function _tp_theme_json_webfonts_handler():void{
            if ( $this->_tp_installing() ) return;
            $registered_webfonts = [];
            $fn_get_webfonts_from_theme_json = static function() {
                $settings = TP_Theme_JSON_Resolver::get_merged_data()->get_settings();
                if (( defined( 'REST_REQUEST' ) && REST_REQUEST ) || (new self)->_is_admin()) {
                    $variations = TP_Theme_JSON_Resolver::get_style_variations();
                    foreach ( $variations as $variation ) {
                        if ( empty( $variation['settings']['typography']['fontFamilies'] ) ) continue;
                        if ( empty( $settings['typography'] ) ) $settings['typography'] = [];
                        if ( empty( $settings['typography']['fontFamilies'] ) )
                            $settings['typography']['fontFamilies'] = [];
                        if ( empty( $settings['typography']['fontFamilies']['theme'] ) )
                            $settings['typography']['fontFamilies']['theme'] = [];
                        $settings['typography']['fontFamilies']['theme'] = array_merge( $settings['typography']['fontFamilies']['theme'], $variation['settings']['typography']['fontFamilies']['theme'] );
                        $settings['typography']['fontFamilies']          = array_unique( $settings['typography']['fontFamilies'] );
                    }
                }
                if ( empty( $settings['typography']['fontFamilies'] ) ) return [];
                $webfonts = array();
                foreach ( $settings['typography']['fontFamilies'] as $font_families ) {
                    foreach ( $font_families as $font_family ) {
                        if ( empty( $font_family['fontFace'] ) ) continue;
                        if ( ! is_array( $font_family['fontFace'] ) )
                            continue;
                        $webfonts = (new self)->_tp_array_merge($webfonts, $font_family['fontFace']);
                    }
                }
                return $webfonts;
            };
            $fn_transform_src_into_uri = static function( array $src ) {
                foreach ( $src as $key => $url ) {
                    if ( ! (new self)->_tp_str_starts_with( $url, 'file:./' ) ) continue;
                    $src[ $key ] = (new self)->_get_theme_file_uri( str_replace( 'file:./', '', $url ) );
                }
                return $src;
            };
            $fn_convert_keys_to_kebab_case = static function( array $font_face ) {
                foreach ( $font_face as $property => $value ) {
                    $kebab_case = (new self)->_tp_to_kebab_case( $property );
                    $font_face[ $kebab_case ] = $value;
                    if ( $kebab_case !== $property ) unset( $font_face[ $property ] );
                }
                return $font_face;
            };
            $fn_validate_web_font = static function( $web_font ) {
                $web_font = (new self)->_tp_parse_args(
                    $web_font,['font-family' => '','font-style' => 'normal','font-weight' => '400','font-display' => 'fallback','src' => [],]
                );
                if ( empty( $web_font['font-family'] ) || ! is_string( $web_font['font-family'] ) ) {
                    trigger_error( (new self)->__( 'Web-font font family must be a non-empty string.' ) );
                    return false;
                }
                if ( empty( $web_font['src'] ) || ( ! is_string( $web_font['src'] ) && ! is_array( $web_font['src'] ) ) ) {
                    trigger_error( (new self)->__( 'Web-font src must be a non-empty string or an array of strings.' ) );
                    return false;
                }
                foreach ( (array) $web_font['src'] as $src ) {
                    if ( ! is_string( $src ) || '' === trim( $src ) ) {
                        trigger_error( (new self)->__( 'Each web-font src must be a non-empty string.' ) );
                        return false;
                    }
                }
                if ( ! is_string( $web_font['font-weight'] ) && ! is_int( $web_font['font-weight'] ) ) {
                    trigger_error( (new self)->__( 'Web-font font weight must be a properly formatted string or integer.' ) );
                    return false;
                }
                if ( ! in_array( $web_font['font-display'], array( 'auto', 'block', 'fallback', 'swap' ), true ) )
                    $web_font['font-display'] = 'fallback';
                $valid_props = ['ascend-override','descend-override','font-display','font-family','font-stretch','font-style','font-weight',
                    'font-variant','font-feature-settings','font-variation-settings','line-gap-override','size-adjust','src','unicode-range',];
                foreach ( $web_font as $prop => $value )
                    if ( ! in_array( $prop, $valid_props, true ) ) unset( $web_font[ $prop ] );
                return $web_font;
            };
            $fn_register_webfonts = static function() use ( &$registered_webfonts, $fn_get_webfonts_from_theme_json, $fn_convert_keys_to_kebab_case, $fn_validate_web_font, $fn_transform_src_into_uri ) {
                $registered_webfonts = [];
                foreach ( $fn_get_webfonts_from_theme_json() as $web_font ) {
                    if ( ! is_array( $web_font ) ) continue;
                    $web_font = $fn_convert_keys_to_kebab_case( $web_font );
                    $web_font = $fn_validate_web_font( $web_font );
                    $web_font['src'] = $fn_transform_src_into_uri( (array) $web_font['src'] );
                    if ( empty( $web_font ) ) continue;
                    $registered_webfonts[] = $web_font;
                }
            };
            $fn_order_src = static function( array $web_font ) {
                $src  = [];
                $src_ordered = [];
                foreach ( $web_font['src'] as $url ) {
                    // Add data URIs first.
                    if ( (new self)->_tp_str_starts_with( trim( $url ), 'data:' ) ) {
                        $src_ordered[] = ['url' => $url,'format' => 'data',];
                        continue;
                    }
                    $format         = pathinfo( $url, PATHINFO_EXTENSION );
                    $src[ $format ] = $url;
                }
                // adding the different font formats
                if ( ! empty( $src['woff2'] ) )
                    $src_ordered[] = ['url' => (new self)->_sanitize_url( $src['woff2'] ),'format' => 'woff2',];
                if ( ! empty( $src['woff'] ) )
                    $src_ordered[] = ['url' => (new self)->_sanitize_url( $src['woff'] ),'format' => 'woff',];
                if ( ! empty( $src['ttf'] ) )
                    $src_ordered[] = ['url' => (new self)->_sanitize_url( $src['ttf'] ),'format' => 'truetype',];
                if ( ! empty( $src['eot'] ) )
                    $src_ordered[] = ['url' => (new self)->_sanitize_url( $src['eot'] ),'format' => 'embedded-opentype',];
                if ( ! empty( $src['otf'] ) )
                    $src_ordered[] = ['url' => (new self)->_sanitize_url( $src['otf'] ),'format' => 'opentype',];
                $web_font['src'] = $src_ordered;
                return $web_font;
            };
            $fn_compile_src = static function( $font_family, array $value ) {
                $src = "local($font_family)";
                foreach ( $value as $item ) {
                    if (
                        (new self)->_tp_str_starts_with( $item['url'], (new self)->_site_url() ) ||
                        (new self)->_tp_str_starts_with( $item['url'], (new self)->_home_url() )
                    ) $item['url'] = (new self)->_tp_make_link_relative( $item['url'] );

                    $src .= ( 'data' === $item['format'] ) ? ", url({$item['url']})" : ", url('{$item['url']}') format('{$item['format']}')";
                }
                return $src;
            };
            $fn_compile_variations = static function( array $font_variation_settings ) {
                $variations = '';
                foreach ( $font_variation_settings as $key => $value ) $variations .= "$key $value";
                return $variations;
            };
            $fn_build_font_face_css = static function( array $web_font ) use ( $fn_compile_src, $fn_compile_variations ) {
                $css = '';
                if (
                    (new self)->_tp_str_contains( $web_font['font-family'], ' ' ) &&
                    ! (new self)->_tp_str_contains( $web_font['font-family'], '"' ) &&
                    ! (new self)->_tp_str_contains( $web_font['font-family'], "'" )
                )  $web_font['font-family'] = '"' . $web_font['font-family'] . '"';
                foreach ( $web_font as $key => $value ) {
                    if ( 'provider' === $key ) continue;
                    if ( 'src' === $key )
                        $value = $fn_compile_src( $web_font['font-family'], $value );
                    if ( 'font-variation-settings' === $key && is_array( $value ) )
                        $value = $fn_compile_variations( $value );
                    if ( ! empty( $value ) ) $css .= "$key:$value;";
                }
                return $css;
            };
            $fn_get_css = static function() use ( &$registered_webfonts, $fn_order_src, $fn_build_font_face_css ) {
                $css = '';
                foreach ( $registered_webfonts as $web_font ) {
                    $web_font = $fn_order_src( $web_font );
                    $css .= '@font-face{' . $fn_build_font_face_css( $web_font ) . '}';
                }
                return $css;
            };
            $fn_generate_and_enqueue_styles = static function() use ( $fn_get_css ) {
                $styles = $fn_get_css();
                if ( '' === $styles ) return;
                (new self)->tp_register_style( 'tp_webfonts', '' );
                (new self)->tp_enqueue_style( 'tp_webfonts' );
                (new self)->tp_add_inline_style( 'tp_webfonts', $styles );
            };
            $fn_generate_and_enqueue_editor_styles = static function() use ( $fn_get_css ) {
                $styles = $fn_get_css();
                if ( '' === $styles ) return;
                (new self)->tp_add_inline_style( 'tp_block_library', $styles );
            };
            $this->_add_action( 'tp_loaded', $fn_register_webfonts );
            $this->_add_action( 'tp_enqueue_assets', $fn_generate_and_enqueue_styles );
            $this->_add_action( 'admin_init', $fn_generate_and_enqueue_editor_styles );
        }//3058
        protected function _loader_hooks():void{
            $this->_add_action('tp_head',[$this,'tp_enqueue_assets']);
            $this->_add_action('tp_enqueue_assets',[$this,'tp_enqueue_block_assets']);
            $this->_add_action('tp_enqueue_assets',[$this,'tp_print_head_scripts']);
            $this->_add_action('tp_footer',[$this,'tp_print_footer_assets']);
        }//
        public function tp_enqueue_block_assets():void{
            $this->_do_action( 'tp_enqueue_block_assets' );
        }
    }
}else die;