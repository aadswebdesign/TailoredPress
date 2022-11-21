<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 18:39
 */
namespace  TP_Core\Libs\JSON;
use TP_Core\Libs\Block\TP_Block_Styles_Registry;
use TP_Core\Libs\Block\TP_Block_Type_Registry;
if(ABSPATH){
    class TP_Theme_JSON extends JSON_Base {
        public function __construct( $theme_json = [], $origin = 'theme' ) {
            if ( ! in_array( $origin, self::VALID_ORIGINS, true ) ) $origin = 'theme';
            $this->_theme_json    = TP_Theme_JSON_Schema::migrate( $theme_json );
            $valid_block_names   = array_keys( static::_get_blocks_metadata() );
            $valid_element_names = array_keys( static::ELEMENTS );
            $theme_json = self::_sanitize( $this->_theme_json, $valid_block_names, $valid_element_names );
            $this->_theme_json    = self::_maybe_opt_in_into_settings( $theme_json );
            $nodes = self::_get_setting_nodes( $this->_theme_json );
            foreach ( $nodes as $node ) {
                foreach ( self::PRESETS_METADATA as $preset_metadata ) {
                    $path   = array_merge( $node['path'], $preset_metadata['path'] );
                    $preset = $this->_tp_array_get( $this->_theme_json, $path, null );
                    if ( null !== $preset ) {
                        if ( isset( $preset[0] ) || empty( $preset ) )
                            $this->_tp_array_set( $this->_theme_json, $path, [$origin => $preset] );
                    }
                }
            }
        }//368
        protected static function _maybe_opt_in_into_settings( $theme_json ) {
            $new_theme_json = $theme_json;
            if (isset( $new_theme_json['settings']['appearanceTools'])&& true === $new_theme_json['settings']['appearanceTools'])
                static::_do_opt_in_into_settings( $new_theme_json['settings'] );
            if ( isset( $new_theme_json['settings']['blocks'] ) && is_array( $new_theme_json['settings']['blocks'] ) ) {
                foreach ( $new_theme_json['settings']['blocks'] as &$block )
                    if ( isset( $block['appearanceTools'] ) && ( true === $block['appearanceTools'] ) ) {
                        static::_do_opt_in_into_settings( $block );
                }
            }
            return $new_theme_json;
        }//403
        protected static function _do_opt_in_into_settings( &$context ): void{
            $to_opt_in = [
                ['border', 'color'], ['border', 'radius'],['border', 'style'],['border', 'width'],['color', 'link'],
                ['spacing', 'blockGap'],['spacing', 'margin'],['spacing', 'padding'],['typography', 'lineHeight'],
            ];
            foreach ( $to_opt_in as $path ) {
                if ( 'unset prop' === (new static)->_tp_array_get( $context, $path, 'unset prop' ) )
                    (new static)->_tp_array_set( $context, $path, true );
            }
            unset( $context['appearanceTools'] );
        }//431
        protected static function _sanitize( $input, $valid_block_names, $valid_element_names ){
            $output = [];
            if ( ! is_array( $input ) ) return $output;
            $output = array_intersect_key( $input, array_flip( static::VALID_TOP_LEVEL_KEYS ) );
            $styles_non_top_level = static::VALID_STYLES;
            foreach ( array_keys( $styles_non_top_level ) as $section ) {
                foreach ( array_keys( $styles_non_top_level[ $section ] ) as $prop ) {
                    if ( 'top' === $styles_non_top_level[ $section ][ $prop ] ) unset( $styles_non_top_level[ $section ][ $prop ] );
                }
            }
            $schema                 = [];
            $schema_styles_elements = [];
            foreach ( $valid_element_names as $element )
                $schema_styles_elements[ $element ] = $styles_non_top_level;
            $schema_styles_blocks   = [];
            $schema_settings_blocks = [];
            foreach ( $valid_block_names as $block ) {
                $schema_settings_blocks[ $block ] = static::VALID_SETTINGS;
                $schema_styles_blocks[ $block ] = $styles_non_top_level;
                $schema_styles_blocks[ $block ]['elements'] = $schema_styles_elements;
            }
            $schema['styles']             = static::VALID_STYLES;
            $schema['styles']['blocks']   = $schema_styles_blocks;
            $schema['styles']['elements'] = $schema_styles_elements;
            $schema['settings']           = static::VALID_SETTINGS;
            $schema['settings']['blocks'] = $schema_settings_blocks;
            foreach ( array( 'styles', 'settings' ) as $subtree ) {
                if ( ! isset( $input[ $subtree ] ) ) continue;
                if ( ! is_array( $input[ $subtree ] ) ) {
                    unset( $output[ $subtree ] );
                    continue;
                }
                $result = static::_remove_keys_not_in_schema( $input[ $subtree ], $schema[ $subtree ] );
                if ( empty( $result ) ) unset( $output[ $subtree ] );
                else $output[ $subtree ] = $result;
            }
            return $output;
        }//466
        protected static function _get_blocks_metadata(){
            if ( null !== static::$_blocks_metadata ) return static::$_blocks_metadata;
            static::$_blocks_metadata = [];
            $_registry = TP_Block_Type_Registry::get_instance();
            $registry = null;
            if($_registry  instanceof TP_Block_Styles_Registry ){
                $registry = $_registry;
            }
            $blocks   = $registry->get_all_registered();
            foreach ( $blocks as $block_name => $block_type ) {
                if (isset( $block_type->supports['__experimentalSelector'] ) && is_string( $block_type->supports['__experimentalSelector'] ))
                    static::$_blocks_metadata[ $block_name ]['selector'] = $block_type->supports['__experimentalSelector'];
                else static::$_blocks_metadata[ $block_name ]['selector'] = '.tp-block-' . str_replace( '/', '-', str_replace( 'core/', '', $block_name ) );
                if (isset( $block_type->supports['color']['__experimentalDuoTone'] ) && is_string( $block_type->supports['color']['__experimentalDuoTone']))
                    static::$_blocks_metadata[ $block_name ]['duo_tone'] = $block_type->supports['color']['__experimentalDuotone'];
                $block_selectors = explode( ',', static::$_blocks_metadata[ $block_name ]['selector'] );
                foreach ( static::ELEMENTS as $el_name => $el_selector ) {
                    $element_selector = [];
                    foreach ( $block_selectors as $selector ) $element_selector[] = $selector . ' ' . $el_selector;
                    static::$_blocks_metadata[ $block_name ]['elements'][ $el_name ] = implode( ',', $element_selector );
                }
            }
            return static::$_blocks_metadata;
        }//557
        protected static function _remove_keys_not_in_schema( $tree, $schema ){
            $tree = array_intersect_key( $tree, $schema );
            foreach ( $schema as $key => $data ) {
                if (!isset($tree[$key])) continue;
                if ( is_array( $schema[ $key ] ) && is_array( $tree[ $key ])){
                    $tree[ $key ] = static::_remove_keys_not_in_schema( $tree[ $key ], $schema[ $key ] );
                    if ( empty( $tree[ $key ] ) ) unset( $tree[ $key ] );
                } elseif ( is_array( $schema[ $key ] ) && ! is_array( $tree[$key]))  unset( $tree[ $key ] );
            }
            return $tree;
        }//610
        public function get_settings(){
            if (!isset( $this->_theme_json['settings'])) return [];
            else return $this->_theme_json['settings'];
        }//654
        public function get_stylesheet( $types = ['variables', 'styles', 'presets'], $origins = null ): string{
            if ( null === $origins )  $origins = static::VALID_ORIGINS;
            if ( $types !== null ) {
                $this->_deprecated_argument( __FUNCTION__, '0.0.1' );
                if ( 'block_styles' === $types ) $types = array( 'styles', 'presets' );
                elseif ( 'css_variables' === $types ) $types = array( 'variables' );
                else  $types = array( 'variables', 'styles', 'presets' );
            }
            $blocks_metadata = static::_get_blocks_metadata();
            $style_nodes     = static::_get_style_nodes( $this->_theme_json, $blocks_metadata );
            $setting_nodes   = static::_get_setting_nodes( $this->_theme_json, $blocks_metadata );
            $stylesheet = '';
            if ( in_array( 'variables', $types, true ) ) $stylesheet .= $this->_get_css_variables( $setting_nodes, $origins );
            if ( in_array( 'styles', $types, true ) ) $stylesheet .= $this->_get_block_classes( $style_nodes );
            if ( in_array( 'presets', $types, true ) ) $stylesheet .= $this->_get_preset_classes( $setting_nodes, $origins );
            return $stylesheet;
        }//676
        public function get_custom_templates(): array{
            $custom_templates = [];
            if ( ! isset( $this->_theme_json['customTemplates'] ) || ! is_array( $this->_theme_json['customTemplates']))
                return $custom_templates;
            foreach ( $this->_theme_json['customTemplates'] as $item ) {
                if ( isset( $item['name'] ) )
                    $custom_templates[ $item['name'] ] = array('title' => $item['title'] ?? '', 'postTypes' => $item['postTypes'] ?? array( 'page' ),);
            }
            return $custom_templates;
        }//721
        public function get_template_parts(): array{
            $template_parts = array();
            if ( ! isset( $this->_theme_json['templateParts'] ) || ! is_array( $this->_theme_json['templateParts'] ) )
                return $template_parts;
            foreach ( $this->_theme_json['templateParts'] as $item ) {
                if ( isset( $item['name'] ) )
                    $template_parts[ $item['name'] ] = ['title' => $item['title'] ?? '', 'area'  => $item['area'] ?? '',];
            }
            return $template_parts;
        }//745
        //todo set my own styles and rules, things as float will be thrown out first.
        protected function _get_block_classes( $style_nodes ): string{
            $block_rules = '';
            foreach ( $style_nodes as $metadata ) {
                if ( null === $metadata['selector'] )  continue;
                $node         = $this->_tp_array_get( $this->_theme_json, $metadata['path'], array() );
                $selector     = $metadata['selector'];
                $settings     = $this->_tp_array_get( $this->_theme_json, array( 'settings' ) );
                $declarations = static::_compute_style_properties( $node, $settings );
                $declarations_duo_tone = array();
                foreach ($declarations as $index => $declaration ) {
                    if ( 'filter' === $declaration['name'] ) {
                        unset( $declarations[ $index ] );
                        $declarations_duo_tone[] = $declaration;
                    }
                }
                if ( static::ROOT_BLOCK_SELECTOR === $selector ) $block_rules .= 'body { margin: 0; }';
                $block_rules .= static::_to_rule_set( $selector, $declarations );
                if ( isset( $metadata['duo_tone'] ) && ! empty( $declarations_duo_tone ) ) {
                    $selector_duo_tone = static::_scope_selector( $metadata['selector'], $metadata['duo_tone'] );
                    $block_rules     .= static::_to_rule_set( $selector_duo_tone, $declarations_duo_tone );
                }
                if ( static::ROOT_BLOCK_SELECTOR === $selector ) {
                    $block_rules .= '.tp-site-blocks > .alignleft { float: left; margin-right: 2em; }';
                    $block_rules .= '.tp-site-blocks > .alignright { float: right; margin-left: 2em; }';
                    $block_rules .= '.tp-site-blocks > .aligncenter { justify-content: center; margin-left: auto; margin-right: auto; }';
                    $has_block_gap_support = $this->_tp_array_get( $this->_theme_json, array( 'settings', 'spacing', 'blockGap' ) ) !== null;
                    if ( $has_block_gap_support ) {
                        $block_rules .= '.tp-site-blocks > * { margin-top: 0; margin-bottom: 0; }';
                        $block_rules .= '.tp-site-blocks > * + * { margin-top: var( --tp--style--block-gap ); }';
                    }
                }
            }
            return $block_rules;
        }//782
        protected function _get_preset_classes( $setting_nodes, $origins ): string{
            $preset_rules = '';
            foreach ( $setting_nodes as $metadata ) {
                if ( null === $metadata['selector'] ) continue;
                $selector      = $metadata['selector'];
                $node          = $this->_tp_array_get( $this->_theme_json, $metadata['path'],[]);
                $preset_rules .= static::_compute_preset_classes( $node, $selector, $origins );
            }
            return $preset_rules;
        }//871
        protected function _get_css_variables( $nodes, $origins ): string{
            $stylesheet = '';
            foreach ( $nodes as $metadata ) {
                if ( null === $metadata['selector'] ) continue;
                $selector = $metadata['selector'];
                $node         = $this->_tp_array_get( $this->_theme_json, $metadata['path'], array() );
                $declarations = array_merge( static::_compute_preset_vars( $node, $origins ), static::_compute_theme_vars( $node ) );
                $stylesheet .= static::_to_rule_set( $selector, $declarations );
            }
            return $stylesheet;
        }//908
        protected static function _to_rule_set( $selector, $declarations ): string{
            if ( empty( $declarations ) ) return '';
            $declaration_block = array_reduce(
                $declarations,
                static function ( $carry, $element ) {
                    $carry .= $element['name'] . ': ' . $element['value'] . ';';
                    return  $carry;},
                ''
            );
            return $selector . '{' . $declaration_block . '}';
        }//936
        protected static function _append_to_selector( $selector, $to_append ){
            $new_selectors = [];
            $selectors     = explode( ',', $selector );
            foreach ( $selectors as $sel ) $new_selectors[] = $sel . $to_append;
            return implode( ',', $new_selectors );
        }//964
        protected static function _compute_preset_classes( $settings, $selector, $origins ): string{
            if ( static::ROOT_BLOCK_SELECTOR === $selector ) $selector = '';
            $stylesheet = '';
            foreach ( static::PRESETS_METADATA as $preset_metadata ) {
                $slugs = static::_get_settings_slugs( $settings, $preset_metadata, $origins );
                foreach ( $preset_metadata['classes'] as $class => $property ) {
                    foreach ( $slugs as $slug ) {
                        $css_var     = static::_replace_slug_in_string( $preset_metadata['css_vars'], $slug );
                        $class_name  = static::_replace_slug_in_string( $class, $slug );
                        $stylesheet .= static::_to_rule_set(
                            static::_append_to_selector( $selector, $class_name ),
                            [['name'  => $property, 'value' => 'var(' . $css_var . ') !important',],]
                        );
                    }
                }
            }
            return $stylesheet;
        }//986
        protected static function _scope_selector( $scope, $selector ){
            $scopes    = explode( ',', $scope );
            $selectors = explode( ',', $selector );
            $selectors_scoped = array();
            foreach ( $scopes as $outer ) {
                foreach ( $selectors as $inner ) $selectors_scoped[] = trim( $outer ) . ' ' . trim( $inner );
            }
            return implode( ', ', $selectors_scoped );
        }//1033
        protected static function _get_settings_values_by_slug( $settings, $preset_metadata, $origins ): array{
            $preset_per_origin = (new static)->_tp_array_get( $settings, $preset_metadata['path'], array() );
            $result = [];
            foreach ( $origins as $origin ) {
                if ( ! isset( $preset_per_origin[ $origin ] ) ) continue;
                foreach ( $preset_per_origin[ $origin ] as $preset ) {
                    $slug = (new static)->_tp_to_kebab_case( $preset['slug'] );
                    if ( isset( $preset_metadata['value_key'], $preset[ $preset_metadata['value_key'] ] ) ) {
                        $value_key = $preset_metadata['value_key'];
                        $value     = $preset[ $value_key ];
                    } elseif ( isset($preset_metadata['value_func'])&& is_callable( $preset_metadata['value_func'])){
                        $value_func = $preset_metadata['value_func'];
                        $value      = $value_func($preset);
                    } else continue;
                    $result[ $slug ] = $value;
                }
            }
            return $result;
        }//1083
        protected static function _get_settings_slugs( $settings, $preset_metadata, $origins = null ): array{
            if ( null === $origins )  $origins = static::VALID_ORIGINS;
            $preset_per_origin = (new static)->_tp_array_get( $settings, $preset_metadata['path'], [] );
            $result =[];
            foreach ( $origins as $origin ) {
                if ( ! isset( $preset_per_origin[ $origin ] ) ) continue;
                foreach ( $preset_per_origin[ $origin ] as $preset ) {
                    $slug = (new static)->_tp_to_kebab_case( $preset['slug'] );
                    $result[ $slug ] = $slug;
                }
            }
            return $result;
        }//1125
        protected static function _replace_slug_in_string( $input, $slug ){
            return strtr( $input, array( '$slug' => $slug ) );
        }//1156
        protected static function _compute_preset_vars( $settings, $origins ): array{
            $declarations = [];
            foreach ( static::PRESETS_METADATA as $preset_metadata ) {
                $values_by_slug = static::_get_settings_values_by_slug( $settings, $preset_metadata, $origins );
                foreach ( $values_by_slug as $slug => $value )
                    $declarations[] = ['name'  => static::_replace_slug_in_string( $preset_metadata['css_vars'], $slug ),
                        'value' => $value,];
            }
            return $declarations;
        }//1177
        protected static function _compute_theme_vars( $settings ): array{
            $declarations  = [];
            $custom_values = (new static)->_tp_array_get( $settings, array( 'custom' ), array() );
            $css_vars      = static::_flatten_tree( $custom_values );
            foreach ( $css_vars as $key => $value )
                $declarations[] = ['name'  => '--tp--custom--' . $key,'value' => $value,];
            return $declarations;
        }//1207
        protected static function _flatten_tree( $tree, $prefix = '', $token = '--' ){
            $result = [];
            foreach ( $tree as $property => $value ) {
                $new_key = $prefix . str_replace('/','-', strtolower( (new static)->_tp_to_kebab_case( $property ) ));
                if ( is_array( $value ) ) {
                    $new_prefix = $new_key . $token;
                    $result     = (new static)->_tp_array_merge($result, static::_flatten_tree( $value, $new_prefix, $token ));
                } else $result[ $new_key ] = $value;
            }
            return $result;
        }//1258
        protected static function _compute_style_properties( $styles, $settings = [], $properties = null ): array{
            if ( null === $properties ) $properties = static::PROPERTIES_METADATA;
            $declarations = array();
            if ( empty( $styles ) ) return $declarations;
            foreach ( $properties as $css_property => $value_path ) {
                $value = static::_get_property_value( $styles, $value_path );
                if ( is_array( $value_path ) ) {
                    $path_string = implode( '.', $value_path );
                    if (array_key_exists( $path_string, static::PROTECTED_PROPERTIES ) &&  (new static)->_tp_array_get( $settings, static::PROTECTED_PROPERTIES[ $path_string ], null ) === null)
                        continue;
                }
                $has_missing_value = empty( $value ) && ! is_numeric( $value );
                if ( $has_missing_value || is_array( $value ) ) continue;
                $declarations[] = ['name'  => $css_property,'value' => $value,];
            }
            return $declarations;
        }//1297
        protected static function _get_property_value( $styles, $path ){
            $value = (new static)->_tp_array_get( $styles, $path, '' );
            if ( '' === $value || is_array( $value )) return $value;
            $prefix     = 'var:';
            $prefix_len = strlen( $prefix );
            $token_in   = '|';
            $token_out  = '--';
            if ( 0 === strncmp( $value, $prefix, $prefix_len ) ) {
                $unwrapped_name = str_replace($token_in,$token_out,substr( $value, $prefix_len ));
                $value          = "var(--tp--$unwrapped_name)";
            }
            return $value;
        }//1351
        protected static function _get_setting_nodes( $theme_json, $selectors = [] ): array{
            $nodes = [];
            if ( ! isset( $theme_json['settings'] ) ) return $nodes;
            $nodes[] = ['path' => ['settings'], 'selector' => static::ROOT_BLOCK_SELECTOR,];
            if ( ! isset( $theme_json['settings']['blocks'] ) ) return $nodes;
            foreach ( $theme_json['settings']['blocks'] as $name => $node ) {
                $selector = null;
                if ( isset( $selectors[ $name ]['selector'] ) ) $selector = $selectors[ $name ]['selector'];
                $nodes[] = ['path' => [ 'settings', 'blocks', $name ],'selector' => $selector,];
            }
            return $nodes;
        }//1394
        protected static function _get_style_nodes( $theme_json, $selectors = [] ): array{
            $nodes = [];
            if ( ! isset( $theme_json['styles'] ) ) return $nodes;
            $nodes[] = ['path'     => ['styles'],'selector' => static::ROOT_BLOCK_SELECTOR,];
            if ( isset( $theme_json['styles']['elements'] ) ) {
                foreach ( $theme_json['styles']['elements'] as $element => $node )
                    $nodes[] = ['path'     => ['styles', 'elements', $element],'selector' => static::ELEMENTS[ $element ],];
            }
            if ( ! isset( $theme_json['styles']['blocks'] ) ) return $nodes;
            foreach ( $theme_json['styles']['blocks'] as $name => $node ) {
                $selector = null;
                if ( isset( $selectors[ $name ]['selector'] ) ) $selector = $selectors[ $name ]['selector'];
                $duo_tone_selector = null;
                if ( isset( $selectors[ $name ]['duo_tone'] ) ) $duo_tone_selector = $selectors[ $name ]['duo_tone'];
                $nodes[] = ['path'     => ['styles', 'blocks', $name], 'selector' => $selector,'duo_tone'  => $duo_tone_selector,];
                if ( isset( $theme_json['styles']['blocks'][ $name ]['elements'] ) ) {
                    foreach ( $theme_json['styles']['blocks'][ $name ]['elements'] as $element => $inner_node )
                        $nodes[] = ['path' => ['styles', 'blocks', $name, 'elements', $element],'selector' => $selectors[ $name ]['elements'][ $element ],];
                }
            }
            return $nodes;
        }//1448
        public function merge( $incoming ): void{
            $incoming_data    = $incoming->this->get_raw_data();
            $this->_theme_json = array_replace_recursive( $this->_theme_json, $incoming_data );
            $nodes        = static::_get_setting_nodes( $incoming_data );
            $slugs_global = static::_get_default_slugs( $this->_theme_json, array( 'settings' ) );
            foreach ( $nodes as $node ) {
                $slugs_node = static::_get_default_slugs( $this->_theme_json, $node['path'] );
                $slugs      = array_merge_recursive( $slugs_global, $slugs_node );
                $path    = array_merge( $node['path'], array( 'spacing', 'units' ) );
                $content = $this->_tp_array_get( $incoming_data, $path, null );
                if ( isset( $content ) ) $this->_tp_array_set( $this->_theme_json, $path, $content );
                foreach ( static::PRESETS_METADATA as $preset ) {
                    $override_preset = static::_should_override_preset( $this->_theme_json, $node['path'], $preset['override'] );
                    foreach ( static::VALID_ORIGINS as $origin ) {
                        $base_path = array_merge( $node['path'], $preset['path'] );
                        $path      = array_merge( $base_path, array( $origin ) );
                        $content   = $this->_tp_array_get( $incoming_data, $path, null );
                        if ( ! isset( $content ) )  continue;
                        if ( 'theme' === $origin && $preset['use_default_names'] ) {
                            foreach ((array) $content as &$item ) {
                                if ( ! array_key_exists( 'name', $item ) ) {
                                    $name = (new static)->_get_name_from_defaults( $item['slug'], $base_path );
                                    if ( null !== $name ) $item['name'] = $name;
                                }
                            }
                            unset($item);
                        }
                        if (( 'theme' !== $origin ) ||( 'theme' === $origin && $override_preset ))
                            $this->_tp_array_set( $this->_theme_json, $path, $content );
                        else {
                            $slugs_for_preset = $this->_tp_array_get( $slugs, $preset['path'], array() );
                            $content          = static::_filter_slugs( $content, $slugs_for_preset );
                            $this->_tp_array_set( $this->_theme_json, $path, $content );
                        }
                    }
                }
            }
        }//1512
        public function get_svg_filters( $origins ): string{
            $blocks_metadata = static::_get_blocks_metadata();
            $setting_nodes   = static::_get_setting_nodes( $this->_theme_json, $blocks_metadata );
            $filters = '';
            foreach ( $setting_nodes as $metadata ) {
                $node = $this->_tp_array_get( $this->_theme_json, $metadata['path'], []);
                if ( empty( $node['color']['duo_tone'] ) ) continue;
                $duo_tone_presets = $node['color']['duo_tone'];
                foreach ( $origins as $origin ) {
                    if ( ! isset( $duo_tone_presets[ $origin ] ) ) continue;
                    foreach ( $duo_tone_presets[ $origin ] as $duo_tone_preset )
                        $filters .= $this->_tp_get_duotone_filter_svg( $duo_tone_preset );
                }
            }
            return $filters;
        }//1597
        protected static function _should_override_preset( $theme_json, $path, $override ): bool{
            if(is_bool( $override)) return $override;
            if ( is_array( $override ) ) {
                $value = (new static)->_tp_array_get( $theme_json, array_merge( $path, $override ) );
                if ( isset( $value ) )
                    return ! $value;
                $value = (new static)->_tp_array_get( $theme_json, array_merge( array( 'settings' ), $override ) );
                if ( isset( $value ) )
                    return ! $value;
                return true;
            }
            return false;
        }//1633
        protected static function _get_default_slugs( $data, $node_path ): array {
            $slugs = [];
            foreach ( static::PRESETS_METADATA as $metadata ) {
                $path   = array_merge( $node_path, $metadata['path'], array( 'default' ) );
                $preset = (new static)->_tp_array_get( $data, $path, null );
                if ( ! isset( $preset ) ) continue;
                $slugs_for_preset = [];
                $slugs_for_preset .= array_map(
                    static function( $value ) {
                        return $value['slug'] ?? null;
                    },
                    $preset
                );
                (new static)->_tp_array_set( $slugs, $metadata['path'], $slugs_for_preset );
            }
            return $slugs;
        }//1685
        protected function _get_name_from_defaults( $slug, $base_path ){
            $path            = array_merge( $base_path, array( 'default' ) );
            $default_content = (new static)->_tp_array_get( $this->_theme_json, $path, null );
            if ( ! $default_content ) return null;
            foreach ( $default_content as $item ) {
                if ( $slug === $item['slug'] ) return $item['name'];
            }
            return null;
        }//1717
        protected static function _filter_slugs( $node, $slugs ){
            if ( empty( $slugs ) ) return $node;
            $new_node = array();
            foreach ( $node as $value ) {
                if ( isset( $value['slug'] ) && ! in_array( $value['slug'], $slugs, true )) $new_node[] = $value;
            }
            return $new_node;
        }//1740
        public static function remove_insecure_properties( $theme_json ){
            $sanitized =[] ;
            $theme_json = TP_Theme_JSON_Schema::migrate( $theme_json );
            $valid_block_names   = array_keys( static::_get_blocks_metadata() );
            $valid_element_names = array_keys( static::ELEMENTS );
            $theme_json          = static::_sanitize( $theme_json, $valid_block_names, $valid_element_names );
            $blocks_metadata = static::_get_blocks_metadata();
            $style_nodes     = static::_get_style_nodes( $theme_json, $blocks_metadata );
            foreach ( $style_nodes as $metadata ) {
                $input = (new static)->_tp_array_get( $theme_json, $metadata['path'], array() );
                if ( empty( $input ) ) continue;
                $output = static::_remove_insecure_styles( $input );
                if ( ! empty( $output ) )
                    (new static)->_tp_array_set( $sanitized, $metadata['path'], $output );
            }
            $setting_nodes = static::_get_setting_nodes( $theme_json );
            foreach ( $setting_nodes as $metadata ) {
                $input = (new static)->_tp_array_get( $theme_json, $metadata['path'], array() );
                if (empty($input)) continue;
                $output = static::_remove_insecure_settings( $input );
                if ( ! empty( $output ) )
                    (new static)->_tp_array_set( $sanitized, $metadata['path'], $output );
            }
            if ( empty( $sanitized['styles'] ) ) unset( $theme_json['styles'] );
            else $theme_json['styles'] = $sanitized['styles'];
            if ( empty( $sanitized['settings'] ) ) unset( $theme_json['settings'] );
            else $theme_json['settings'] = $sanitized['settings'];
            return $theme_json;
        }//1763
        protected static function _remove_insecure_settings( $input ): array{
            $output = array();
            foreach ( static::PRESETS_METADATA as $preset_metadata ) {
                foreach ( static::VALID_ORIGINS as $origin ) {
                    $path_with_origin = array_merge( $preset_metadata['path'], array( $origin ) );
                    $presets          = (new static)->_tp_array_get( $input, $path_with_origin, null );
                    if ( null === $presets ) continue;
                    $escaped_preset = [];
                    foreach ( (array)$presets as $preset ) {
                        if ((new static)->_sanitize_html_class( $preset['slug'] ) === $preset['slug'] && (new static)->_esc_attr( (new static)->_esc_html( $preset['name'] ) ) === $preset['name']) {
                            $value = null;
                            if ( isset( $preset_metadata['value_key'], $preset[ $preset_metadata['value_key']]))
                                $value = $preset[ $preset_metadata['value_key'] ];
                            elseif(isset( $preset_metadata['value_func'])&& is_callable( $preset_metadata['value_func']))
                                $value = call_user_func( $preset_metadata['value_func'], $preset );
                            $preset_is_valid = true;
                            foreach ( $preset_metadata['properties'] as $property ) {
                                if ( ! static::_is_safe_css_declaration( $property, $value ) ) {
                                    $preset_is_valid = false;
                                    break;
                                }
                            }
                            if ( $preset_is_valid ) $escaped_preset[] = $preset;
                        }
                    }
                    if ( ! empty( $escaped_preset ) )
                        (new static)->_tp_array_set( $output, $path_with_origin, $escaped_preset );
                }
            }
            return $output;
        }//1823
        protected static function _remove_insecure_styles( $input ): array{
            $output       = [];
            $declarations = static::_compute_style_properties( $input );
            foreach ( $declarations as $declaration ) {
                if ( static::_is_safe_css_declaration( $declaration['name'], $declaration['value'] ) ) {
                    $path = static::PROPERTIES_METADATA[ $declaration['name'] ];
                    $value = (new static)->_tp_array_get( $input, $path, array() );
                    if ( ! is_array( $value ) ) (new static)->_tp_array_set( $output, $path, $value );
                }
            }
            return $output;
        }//1880
        protected static function _is_safe_css_declaration( $property_name, $property_value ): bool{
            $style_to_validate = $property_name . ': ' . $property_value;
            $filtered          = (new static)->_esc_html( (new static)->_safe_css_filter_attr( $style_to_validate ) );
            return ! empty( trim( $filtered ) );
        }//1908
        public function get_raw_data(){
            return $this->_theme_json;
        }//1921
        public static function get_from_editor_settings( $settings ): array{
            $theme_settings = ['version'  => static::LATEST_SCHEMA,'settings' => [],];
            //todo, check of what I need from this?
            // Deprecated theme supports.
            if ( isset( $settings['disableCustomColors'] ) ) {
                if ( ! isset( $theme_settings['settings']['color'] ) )
                    $theme_settings['settings']['color'] = [];
                $theme_settings['settings']['color']['custom'] = ! $settings['disableCustomColors'];
            }
            if ( isset( $settings['disableCustomGradients'] ) ) {
                if ( ! isset( $theme_settings['settings']['color'] ) )
                    $theme_settings['settings']['color'] = [];
                $theme_settings['settings']['color']['customGradient'] = ! $settings['disableCustomGradients'];
            }
            if ( isset( $settings['disableCustomFontSizes'] ) ) {
                if ( ! isset( $theme_settings['settings']['typography'] ) )
                    $theme_settings['settings']['typography'] = [];
                $theme_settings['settings']['typography']['customFontSize'] = ! $settings['disableCustomFontSizes'];
            }
            if ( isset( $settings['enableCustomLineHeight'] ) ) {
                if ( ! isset( $theme_settings['settings']['typography'] ) )
                    $theme_settings['settings']['typography'] = [];
                $theme_settings['settings']['typography']['lineHeight'] = $settings['enableCustomLineHeight'];
            }
            if ( isset( $settings['enableCustomUnits'] ) ) {
                if ( ! isset( $theme_settings['settings']['spacing'] ) )
                    $theme_settings['settings']['spacing'] = [];
                $theme_settings['settings']['spacing']['units'] = ( true === $settings['enableCustomUnits'] ) ?
                    ['px', 'em', 'rem', 'vh', 'vw', '%', 'fr']: $settings['enableCustomUnits'];
            }
            if ( isset( $settings['colors'] ) ) {
                if ( ! isset( $theme_settings['settings']['color'] ) )
                    $theme_settings['settings']['color'] = [];
                $theme_settings['settings']['color']['palette'] = $settings['colors'];
            }
            if ( isset( $settings['gradients'] ) ) {
                if ( ! isset( $theme_settings['settings']['color'] ) )
                    $theme_settings['settings']['color'] = [];
                $theme_settings['settings']['color']['gradients'] = $settings['gradients'];
            }
            if ( isset( $settings['fontSizes'] ) ) {
                $font_sizes = $settings['fontSizes'];
                if ( ! isset( $theme_settings['settings']['typography'] ) )
                    $theme_settings['settings']['typography'] = [];
                $theme_settings['settings']['typography']['fontSizes'] = $font_sizes;
            }
            if ( isset( $settings['enableCustomSpacing'] ) ) {
                if ( ! isset( $theme_settings['settings']['spacing'] ) )
                    $theme_settings['settings']['spacing'] = [];
                $theme_settings['settings']['spacing']['padding'] = $settings['enableCustomSpacing'];
            }
            return $theme_settings;
        }//1934
    }
}else die;