<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-3-2022
 * Time: 16:39
 */
namespace TP_Core\Traits\Block;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\Block\TP_Block_Patterns_Registry;
use TP_Core\Libs\Block\TP_Block_Patterns_Data;
use TP_Core\Libs\Block\TP_Block_Patterns_Files;
use TP_Core\Libs\JSON\TP_Theme_JSON_Resolver;
use TP_Core\Libs\RestApi\TP_REST_Response;
if(ABSPATH){
    trait _block_category_patterns {
        /**
         * @description Registers the core block patterns and categories.
         */
        protected function _register_core_block_patterns_and_categories():void{
            $should_register_core_patterns = $this->_get_theme_support( 'core-block-patterns' );
            if ($should_register_core_patterns ){
                $core_block_patterns = TP_Block_Patterns_Data::getPatterns();
                $core_block_props =  TP_Block_Patterns_Data::getProps();
                foreach ((array)$core_block_patterns as $core_block_pattern ){
                    foreach ((array)$core_block_props as $core_block_prop)//todo are empty methods
                        $this->_register_block_pattern($core_block_pattern,$core_block_prop);
                }
            }
            $this->_register_block_pattern_category('buttons',['label' => $this->_x('Buttons','Block pattern category')]);
            $this->_register_block_pattern_category('columns',['label' => $this->_x('Columns','Block pattern category')]);
            $this->_register_block_pattern_category('featured',['label' => $this->_x('Featured','Block pattern category')]);
            $this->_register_block_pattern_category('gallery',['label' => $this->_x('Gallery','Block pattern category')]);
            $this->_register_block_pattern_category('header',['label' => $this->_x('Headers','Block pattern category')]);
            $this->_register_block_pattern_category('text',['label' => $this->_x('Text','Block pattern category')]);
            $this->_register_block_pattern_category('query',['label' => $this->_x('Query','Block pattern category')]);
        }//17
        /**
         * todo think of an alternative for this
         * @description  Register Core's official patterns from wordpress.org/patterns.
         */
        protected function _load_remote_block_patterns():void{
            $supports_core_patterns = $this->_get_theme_support( 'core-block-patterns' );
            $should_load_remote = $this->_apply_filters( 'should_load_remote_block_patterns', true );
            if ( $supports_core_patterns && $should_load_remote ) {
                $request         = new TP_REST_Request( 'GET', '/tp/v1/pattern-directory/patterns' );
                $core_keyword_id = 11; // 11 is the ID for "core".
                $request->set_param( 'keyword', $core_keyword_id );
                $_response = $this->_rest_do_request( $request );
                $response = null;
                if($_response  instanceof TP_REST_Response ){
                    $response = $_response;
                }
                if ( $response->is_error() )
                    return;
                $patterns = $response->get_data();
                foreach ( $patterns as $settings ) {
                    $pattern_name = 'core/' . $this->_sanitize_title( $settings['title'] );
                    $this->_register_block_pattern( $pattern_name, (array) $settings );
                }
            }
        }//55
        /**
         * @description  Register `Featured` (category) patterns from ?/patterns.
         */
        protected function _load_remote_featured_patterns():void{
            $supports_core_patterns = $this->_get_theme_support( 'core-block-patterns' );
            $should_load_remote = $this->_apply_filters( 'should_load_remote_block_patterns', true );
            if ( ! $should_load_remote || ! $supports_core_patterns ) return;
            $request         = new TP_REST_Request( 'GET', '/tp/v1/pattern-directory/patterns' );
            $featured_cat_id = 26;
            $request->set_param( 'category', $featured_cat_id );
            $_response = $this->_rest_do_request( $request );
            $response = null;
            if($_response  instanceof TP_REST_Response ){
                $response = $_response;
            }
            if ( $response->is_error() ) return;
            $patterns = $response->get_data();
            $block_patterns_instances = TP_Block_Patterns_Registry::get_instance();
            $registry = null;
            if($block_patterns_instances instanceof TP_Block_Patterns_Registry ){
                $registry = $block_patterns_instances;
            }
            foreach ( $patterns as $pattern ) {
                $pattern_name = $this->_sanitize_title( $pattern['title'] );
                $is_registered = $registry->is_registered( $pattern_name ) || $registry->is_registered( "core/$pattern_name" );
                if ( ! $is_registered )
                    $this->_register_block_pattern( $pattern_name, (array) $pattern );
            }
        }//97
        /**
         * @description Registers patterns from Pattern Directory provided by a theme's
         */
        protected function _register_remote_theme_patterns():void{
            if (!$this->_get_theme_support( 'core-block-patterns')) return;
            if (!$this->_apply_filters( 'should_load_remote_block_patterns',true)) return;
            if (!TP_Theme_JSON_Resolver::theme_has_support()) return;
            /** @noinspection PhpUndefinedMethodInspection */
            $pattern_settings = TP_Theme_JSON_Resolver::get_theme_data()->get_patterns();
            if ( empty( $pattern_settings ) ) return;
            $request         = new TP_REST_Request( 'GET', '/tp/v1/pattern-directory/patterns' );
            $request['slug'] = $pattern_settings;
            $_response        = $this->_rest_do_request( $request );
            $response = null;
            if($_response  instanceof TP_REST_Response ){
                $response = $_response;
            }
            if ( $response->is_error() ) return;
            $patterns          = $response->get_data();
            $block_patterns_instances = TP_Block_Patterns_Registry::get_instance();
            $patterns_registry = null;
            if($block_patterns_instances instanceof TP_Block_Patterns_Registry ){
                $patterns_registry = $block_patterns_instances;
            }
            foreach ( $patterns as $pattern ) {
                $pattern_name = $this->_sanitize_title( $pattern['title'] );
                $is_registered = $patterns_registry->is_registered( $pattern_name ) || $patterns_registry->is_registered( "core/$pattern_name" );
                if ( ! $is_registered )
                    $this->_register_block_pattern( $pattern_name, (array) $pattern );
            }
        }//135
        /**
         * @description Register any patterns that the active theme may provide under its
         * @description . `./patterns/` directory. Each pattern is defined as a PHP file and defines
         * @description . its metadata using plugin-style headers. The minimum required definition is:
         */
        protected function _register_theme_block_patterns():void{
            $default_headers = [
                'title' => 'Title','slug' => 'Slug','description' => 'Description','viewportWidth' => 'Viewport Width',
                'categories' => 'Categories','keywords' => 'Keywords','blockTypes' => 'Block Types','inserter' => 'Inserter',
            ];
            $themes     = [];
            $stylesheet = $this->_get_stylesheet();
            $template   = $this->_get_template();
            if ( $stylesheet !== $template )
                $themes[] = $this->_tp_get_theme( $stylesheet );
            $themes[] = $this->_tp_get_theme( $template );
            foreach ( $themes as $theme ){
                $dirpath = $theme->get_stylesheet_directory() . '/patterns/';
                if (! is_dir( $dirpath) || !is_readable( $dirpath )) continue;
                if ( file_exists( $dirpath ) ) {
                    $files = TP_Block_Patterns_Files::getFiles();//todo empty method
                    if ( $files ){
                        foreach((array)$files as $file){
                            $pattern_data = $this->_get_file_data( $file, $default_headers );
                            if ( empty( $pattern_data['slug'] ) ) {
                                $this->_doing_it_wrong('__register_theme_block_patterns',
                                    /* translators: %s: file name. */
                                    sprintf($this->__( 'Could not register file "%s" as a block pattern ("Slug" field missing)'), $file), '0.0.1');
                                continue;
                            }
                            if ( ! preg_match( '/^[A-z0-9\/_-]+$/', $pattern_data['slug'] ) ) {
                                $this->_doing_it_wrong(
                                    '__register_theme_block_patterns',
                                    /* translators: %1s: file name; %2s: slug value found. */
                                    sprintf($this->__( 'Could not register file "%1$s" as a block pattern (invalid slug "%2$s")' ), $file,$pattern_data['slug']),'0.0.1');
                            }
                            $block_patterns_instances = TP_Block_Patterns_Registry::get_instance();
                            $block_patterns = null;
                            if($block_patterns_instances instanceof TP_Block_Patterns_Registry ){
                                $block_patterns = $block_patterns_instances;
                            }
                            if ( $block_patterns->is_registered( $pattern_data['slug'] ) )
                                continue;

                            if ( ! $pattern_data['title'] ) {
                                $this->_doing_it_wrong(
                                    '__register_theme_block_patterns',
                                    /* translators: %1s: file name; %2s: slug value found. */
                                    sprintf( $this->__( 'Could not register file "%s" as a block pattern ("Title" field missing)' ), $file ),'0.0.1');
                                continue;
                            }
                            foreach ( array( 'categories', 'keywords', 'blockTypes' ) as $property ) {
                                if ( ! empty( $pattern_data[ $property ] ) ) {
                                    $pattern_data[ $property ] = array_filter(
                                        preg_split('/[\s,]+/',(string) $pattern_data[ $property ]));
                                } else unset( $pattern_data[ $property ] );
                            }
                            foreach ( array( 'viewportWidth' ) as $property ) {
                                if ( ! empty( $pattern_data[ $property ] ) )
                                    $pattern_data[ $property ] = (int) $pattern_data[ $property ];
                                else unset( $pattern_data[ $property ] );
                            }
                            foreach ( array( 'inserter' ) as $property ) {
                                if ( ! empty( $pattern_data[ $property ] ) ) {
                                    $pattern_data[ $property ] = in_array(strtolower( $pattern_data[ $property ] ), array( 'yes', 'true' ),true);
                                } else unset( $pattern_data[ $property ] );
                            }
                            $text_domain = $theme->get( 'TextDomain' );
                            $pattern_data['title'] = $this->_translate_with_get_text_context( $pattern_data['title'], 'Pattern title', $text_domain );
                            if ( ! empty( $pattern_data['description'] ) )
                                $pattern_data['description'] = $this->_translate_with_get_text_context( $pattern_data['description'], 'Pattern description', $text_domain );
                            $pattern_data['content'] = ob_get_clean();
                            if ( ! $pattern_data['content'] )continue;
                            $this->_register_block_pattern( $pattern_data['slug'], $pattern_data );
                        }
                    }
                }
            }
        }//200
        protected function _block_category_patterns_hooks():void{
            $this->_add_theme_support( 'core_block_patterns' );
            $this->_add_action( 'init', [$this,'register_theme_block_patterns'] );
        }//added
    }
}else die;