<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 20:53
 */
namespace TP_Core\Traits\Block;
use TP_Core\Libs\Block\TP_Block_Type_Registry;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    trait _blocks_01 {
        /**
         * @description Removes the block asset's path prefix if provided.
         * @param $asset_handle_or_path
         * @return string
         */
        protected function _remove_block_asset_path_prefix( $asset_handle_or_path ): string{
            $path_prefix = 'file:';
            if ( 0 !== strpos( $asset_handle_or_path, $path_prefix ) )
                return $asset_handle_or_path;
            $path = substr(
                $asset_handle_or_path,
                strlen( $path_prefix )
            );
            if ( strpos( $path, './' ) === 0 )
                $path = substr( $path, 2 );
            return $path;
        }//18
        /**
         * @description Generates the name for an asset based on the name of the block
         * @description . and the field name provided.
         * @param $block_name
         * @param $field_name
         * @return mixed|string
         */
        protected function _generate_block_asset_handle( $block_name, $field_name ) {
            if ( 0 === strpos( $block_name, 'core/' ) ) {
                $asset_handle = str_replace( 'core/', 'tp_block_', $block_name );
                if ( 0 === strpos( $field_name, 'editor'))
                    $asset_handle .= '_editor';
                if ( 0 === strpos( $field_name, 'view'))
                    $asset_handle .= '_view';
                return $asset_handle;
            }
            $field_mappings = array(
                'editorScript' => 'editor-script',
                'script'       => 'script',
                'viewScript'   => 'view-script',
                'editorStyle'  => 'editor-style',
                'style'        => 'style',
            );
            return str_replace( '/', '-', $block_name ) .
            '-' . $field_mappings[ $field_name ];
        }//43
        /**
         * @description Finds a script handle for the selected block metadata field. It detects
         * @description . when a path to file was provided and finds a corresponding asset file
         * @description . with details necessary to register the script under automatically
         * @description . generated handle name. It returns unprocessed script handle otherwise.
         * @param $metadata
         * @param $field_name
         * @return bool
         */
        protected function _register_block_script_handle( $metadata, $field_name ): bool{
            if ( empty( $metadata[ $field_name ] ) ) return false;
            $script_handle = $metadata[ $field_name ];
            $script_path   = $this->_remove_block_asset_path_prefix( $metadata[ $field_name ] );
            if ( $script_handle === $script_path ) return $script_handle;
            $script_handle = $this->_generate_block_asset_handle( $metadata['name'], $field_name );
            //todo
            $script_asset_path = $this->_tp_normalize_path(
                realpath( dirname( $metadata['file'] ) . '/' .
                    substr_replace( $script_path, '.asset.php', - strlen( '.js' ) )
                )
            );
            if ( ! file_exists( $script_asset_path ) ) {
                $this->_doing_it_wrong(
                    __FUNCTION__,/* translators: 1: Field name, 2: Block name. */
                    sprintf( $this->__( 'The asset file for the "%1$s" defined in "%2$s" block definition is missing.' ),
                        $field_name,$metadata['name']),'0.0.1'
                );
                return false;
            }
            $tp_inc_path_norm  = $this->_tp_normalize_path( realpath( TP_LIBS_ASSETS ) );
            $theme_path_norm  = $this->_tp_normalize_path( $this->_get_theme_file_path() );
            $script_path_norm = $this->_tp_normalize_path( realpath( dirname( $metadata['file'] ) . '/' . $script_path ) );
            $is_core_block    = isset( $metadata['file'] ) && 0 === strpos( $metadata['file'], $tp_inc_path_norm );
            $is_theme_block   = 0 === strpos( $script_path_norm, $theme_path_norm );
            $script_uri = $this->_includes_url( $script_path, $metadata['file'] );
            if ( $is_core_block )
                $script_uri = $this->_includes_url( str_replace( $tp_inc_path_norm, '', $script_path_norm ) );
            elseif ( $is_theme_block )
                $script_uri = $this->_get_theme_file_uri( str_replace( $theme_path_norm, '', $script_path_norm ) );
            /** @noinspection PhpIncludeInspection */
            $script_asset        = require $script_asset_path;
            $script_dependencies = $script_asset['dependencies'] ?? [];
            $result              = $this->tp_register_script(
                $script_handle,
                $script_uri,
                $script_dependencies,
                $script_asset['version'] ?? false
            );
            if ( ! $result ) return false;
            if ( ! empty( $metadata['textdomain'] ) && in_array( 'wp-i18n', $script_dependencies, true ) )
                $this->tp_set_script_translations( $script_handle, $metadata['textdomain'] );
            return $script_handle;
        }
        /**
         * @description Finds a style handle for the block metadata field. It detects when a path
         * @description . to file was provided and registers the style under automatically
         * @description . generated handle name. It returns unprocessed style handle otherwise.
         * @param $metadata
         * @param $field_name
         * @return bool|mixed|string
         */
        protected function _register_block_style_handle( $metadata, $field_name ) {
            if ( empty( $metadata[ $field_name ] ) ) return false;
            $tp_inc_path_norm = $this->_tp_normalize_path( realpath( TP_LIBS_ASSETS ) );
            $theme_path_norm = $this->_tp_normalize_path( $this->_get_theme_file_path() );
            $is_core_block   = isset( $metadata['file'] ) && 0 === strpos( $metadata['file'], $tp_inc_path_norm );
            if ( $is_core_block && ! $this->_tp_should_load_separate_core_block_assets() )
                return false;
            $suffix = TP_SCRIPT_DEBUG ? '' : '.min';
            $style_handle = $metadata[ $field_name ];
            $style_path   = $this->_remove_block_asset_path_prefix( $metadata[ $field_name ] );
            if ( $style_handle === $style_path && ! $is_core_block )
                return $style_handle;
            $style_uri = $this->_includes_url( $style_path, $metadata['file'] );
            if ( $is_core_block ) {
                $style_path = "style$suffix.css";
                $style_uri  = $this->_includes_url( 'blocks/' . str_replace( 'core/', '', $metadata['name'] ) . "/style$suffix.css" );
            }
            $style_path_norm = $this->_tp_normalize_path( realpath( dirname( $metadata['file'] ) . '/' . $style_path ) );
            $is_theme_block  = 0 === strpos( $style_path_norm, $theme_path_norm );
            if ( $is_theme_block )
                $style_uri = $this->_get_theme_file_uri( str_replace( $theme_path_norm, '', $style_path_norm ) );
            $style_handle   = $this->_generate_block_asset_handle( $metadata['name'], $field_name );
            $block_dir      = dirname( $metadata['file'] );
            $style_file     = realpath( "$block_dir/$style_path" );
            $has_style_file = false !== $style_file;
            $version        = ! $is_core_block && isset( $metadata['version'] ) ? $metadata['version'] : false;
            $style_uri      = $has_style_file ? $style_uri : false;
            $result         = $this->tp_register_style($style_handle,$style_uri,[],$version);
            if ( file_exists( str_replace( '.css', '_rtl.css', $style_file ) ) )
                $this->tp_style_add_data( $style_handle, 'rtl', 'replace' );
            if ( $has_style_file )
                $this->tp_style_add_data( $style_handle, 'path', $style_file );
            $rtl_file = str_replace( "$suffix.css", "_rtl$suffix.css", $style_file );
            if ( $this->_is_rtl() && file_exists( $rtl_file ) )
                $this->tp_style_add_data( $style_handle, 'path', $rtl_file );
            return $result ? $style_handle : false;
        }//154
        /**
         * @description Gets i18n schema for block's metadata read from `block.json` file.
         * @return mixed
         */
        protected function _get_block_metadata_i18n_schema() {
            static $i18n_block_schema;
            if ( ! isset( $i18n_block_schema ) )
                $i18n_block_schema = $this->_tp_json_file_decode( TP_LIBS_ASSETS . 'json/block-i18n.json' );
            return $i18n_block_schema;
        }//222
        /**
         * @description Registers a block type from the metadata stored in the `block.json` file.
         * @param $file_or_folder
         * @param array $args
         * @return bool
         */
        protected function _register_block_type_from_metadata( $file_or_folder, $args = [] ): bool{
            $filename = 'block.json';
            $metadata_file = (substr($file_or_folder, -strlen($filename)) !== $filename) ?
                $this->_trailingslashit($file_or_folder) . $filename : $file_or_folder;
            if (!file_exists($metadata_file)) return false;
            $metadata = $this->_tp_json_file_decode($metadata_file, array('associative' => true));
            if (!is_array($metadata) || empty($metadata['name'])) return false;
            $metadata['file'] = $this->_tp_normalize_path(realpath($metadata_file));
            $metadata = $this->_apply_filters('block_type_metadata', $metadata);
            if (!empty($metadata['name']) && 0 === strpos($metadata['name'], 'core/')) {
                $block_name = str_replace('core/', '', $metadata['name']);
                if (!isset($metadata['style']))
                    $metadata['style'] = "tp_block_$block_name";
                if (!isset($metadata['editorStyle']))
                    $metadata['editorStyle'] = "tp_block-{$block_name}_editor";
            }
            $settings = [];
            $property_mappings = [
                'apiVersion' => 'api_version','title' => 'title','category' => 'category',
                'parent' => 'parent','icon' => 'icon','description' => 'description',
                'keywords' => 'keywords','attributes' => 'attributes','providesContext' => 'provides_context','usesContext' => 'uses_context',
                'supports' => 'supports','styles' => 'styles','variations' => 'variations','example' => 'example',
            ];
            $textdomain = !empty($metadata['textdomain']) ? $metadata['textdomain'] : null;
            $i18n_schema = $this->_get_block_metadata_i18n_schema();
            foreach ($property_mappings as $key => $mapped_key) {
                if (isset($metadata[$key])) {
                    $settings[$mapped_key] = $metadata[$key];
                    if ($textdomain && isset($i18n_schema->$key))
                        $settings[$mapped_key] = $this->_translate_settings_using_i18n_schema($i18n_schema->$key, $settings[$key], $textdomain);
                }
            }
            if (!empty($metadata['editorScript']))
                $settings['editor_script'] = $this->_register_block_script_handle($metadata,'editorScript');
            if (!empty($metadata['script']))
                $settings['script'] = $this->_register_block_script_handle($metadata,'script');
            if (!empty($metadata['viewScript']))
                $settings['view_script'] = $this->_register_block_script_handle($metadata,'viewScript');
            if (!empty($metadata['editorStyle']))
                $settings['editor_style'] = $this->_register_block_style_handle($metadata,'editorStyle');
            if (!empty($metadata['style']))
                $settings['style'] = $this->_register_block_style_handle($metadata,'style');
            $settings = $this->_apply_filters('block_type_metadata_settings', array_merge($settings,$args), $metadata);
            return TP_Block_Type_Registry::get_instance()->register($metadata['name'],$settings);
        }//247
        /**
         * @description Registers a block type. The recommended way is to register a block type using
         * @description . the metadata stored in the `block.json` file.
         * @param $block_type
         * @param array $args
         * @return bool
         */
        protected function _register_block_type( $block_type, $args = array() ): bool{
            if ( is_string( $block_type ) && file_exists( $block_type ) )
                return $this->_register_block_type_from_metadata( $block_type, $args );
            return TP_Block_Type_Registry::get_instance()->register( $block_type, $args );
        }//388
        /**
         * @description Unregisters a block type.
         * @param $name
         * @return bool
         */
        protected function _unregister_block_type( $name ): bool{
            return TP_Block_Type_Registry::get_instance()->unregister( $name );
        }//405
        /**
         * @description Determines whether a post or content string has blocks.
         * @param null $post
         * @return bool
         */
        protected function _has_blocks( $post = null ): bool{
            if ( ! is_string( $post ) ) {
                $tp_post = $this->_get_post( $post );
                if ( $tp_post instanceof TP_Post ) $post = $tp_post->post_content;
            }
            return false !== strpos( (string) $post, '<!-- wp:' );
        }//424
        /**
         * @description Determines whether a $post or a string contains a specific block type.
         * @param $block_name
         * @param null $post
         * @return bool
         */
        protected function _has_block( $block_name, $post = null ): bool{
            if ( ! $this->_has_blocks( $post ) ) return false;
            if ( ! is_string( $post ) ) {
                $tp_post = $this->_get_post( $post );
                if ( $tp_post instanceof TP_Post ) $post = $tp_post->post_content;
            }
            if ( false === strpos( $block_name, '/' ) ) $block_name = 'core/' . $block_name;
            $has_block = false !== strpos( $post, '<!-- tp:' . $block_name . ' ' );
            if ( ! $has_block ) {
                $serialized_block_name = $this->_strip_core_block_namespace( $block_name );
                if ( $serialized_block_name !== $block_name )
                    $has_block = false !== strpos( $post, '<!-- tp:' . $serialized_block_name . ' ' );
            }
            return $has_block;
        }//451
    }
}else die;