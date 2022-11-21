<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 18:33
 */
namespace  TP_Core\Libs\JSON;
if(ABSPATH){
    class TP_Theme_JSON_Resolver extends JSON_Base {
        /**
         * @description Processes a file that adheres to the theme.json schema
         * @param $file_path
         * @return array|mixed|null
         */
        protected static function _read_json_file( $file_path ){
            $config = array();
            if ( $file_path ) {
                $decoded_file = (new static)->_tp_json_file_decode( $file_path, array( 'associative' => true ) );
                if ( is_array( $decoded_file ) ) $config = $decoded_file;
            }
            return $config;
        }//81
        /**
         * @description Given a theme.json structure modifies it in place to update certain values
         * @description . by its translated strings according to the language set by the user.
         * @param $theme_json
         * @param string $domain
         * @return array
         */
        protected static function _translate_json( $theme_json, $domain = 'default' ): array{
            if ( null === static::$_i18n_schema ) {
                $i18n_schema         = (new static)->_tp_json_file_decode( __DIR__ . '/theme-i18n.json' );
                static::$_i18n_schema = $i18n_schema ?? array();
            }
            return (new static)->_translate_settings_using_i18n_schema( static::$_i18n_schema, $theme_json, $domain );
        }//116
        /**
         * @description Return core's origin config.
         * @return null
         */
        public static function get_core_data(){
            if ( null !== static::$_core ) return static::$_core;
            $config       = static::_read_json_file( __DIR__ . '/theme.json' ); //todo
            $config       = (new static)->_translate( $config );
            static::$_core = new TP_Theme_JSON( $config, 'default' );
            return static::$_core;
        }//132
        public static function get_theme_data( $deprecated = []): TP_Theme_JSON {
            if ( ! empty( $deprecated ) ) (new static)->_deprecated_argument( __METHOD__, '0.0.1' );
            if ( null === static::$_theme ) {
                $theme_json_data = static::_read_json_file( static::_get_file_path_from_theme( 'theme.json' ) );
                $theme_json_data = (new static)->_translate( $theme_json_data, (new self)->_tp_get_theme()->get_theme( 'TextDomain' ) );
                static::$_theme   = new TP_Theme_JSON( $theme_json_data );
                if ( (new self)->_tp_get_theme()->parent() ) {
                    $parent_theme_json_data = self::_read_json_file( (self::_get_file_path_from_theme( 'theme.json', true ) ));
                    $parent_theme_json_data = (new static)->_translate( $parent_theme_json_data, (new self)->_tp_get_theme()->parent()->get_theme( 'TextDomain' ) );
                    $parent_theme           = new TP_Theme_JSON( $parent_theme_json_data );
                    $parent_theme->merge( static::$_theme );
                    static::$_theme = $parent_theme;
                }
            }
            $theme_support_data = TP_Theme_JSON::get_from_editor_settings( (new static)->_get_default_block_editor_settings() );
            if ( ! static::theme_has_support() ) {
                if (!isset( $theme_support_data['settings']['color'])) $theme_support_data['settings']['color'] = [];
                $default_palette = false;
                if ( (new static)->_current_theme_supports( 'default-color-palette' ) ) $default_palette = true;
                if ( ! isset( $theme_support_data['settings']['color']['palette'] ) ) $default_palette = true;
                $theme_support_data['settings']['color']['defaultPalette'] = $default_palette;
                $default_gradients = false;
                if ( (new static)->_current_theme_supports('default-gradient-presets' )) $default_gradients = true;
                if ( ! isset( $theme_support_data['settings']['color']['gradients'])) $default_gradients = true;
                $theme_support_data['settings']['color']['defaultGradients'] = $default_gradients;
            }
            $with_theme_supports = new TP_Theme_JSON( $theme_support_data );
            $with_theme_supports->merge( static::$_theme );
            return $with_theme_supports;
        }//158
        public static function get_user_data_from_tp_global_styles( $theme, $create_post = false, $post_status_filter = array( 'publish' ) ){
            $tp_theme = (new self)->_init_theme($theme);
            $user_cpt         = [];
            $post_type_filter = 'tp_global_styles';
            $args = [
                'numberposts' => 1,
                'orderby'     => 'date',
                'order'       => 'desc',
                'post_type'   => $post_type_filter,
                'post_status' => $post_status_filter,
                'tax_query'   => [[
                    'taxonomy' => 'tp_theme',
                    'field'    => 'name',
                    'terms'    => $tp_theme->get_stylesheet(),
                ]],
            ];
            $cache_key = sprintf( 'tp_global_styles_%s', md5( serialize( $args ) ) );
            $post_id   = (new static)->_tp_cache_get( $cache_key );
            if ( (int) $post_id > 0 ) (new static)->_get_post( $post_id, ARRAY_A );
            if ( -1 === $post_id && ! $create_post ) return $user_cpt;
            $recent_posts = (new static)->_tp_get_recent_posts( $args );
            if(is_array($recent_posts)&&(count($recent_posts)===1)) $user_cpt = $recent_posts[0];
            elseif($create_post){
                $cpt_post_id = (new static)->_tp_insert_post(
                   [
                       'post_content' => '{"version": ' . TP_Theme_JSON::LATEST_SCHEMA . ', "isGlobalStylesUserThemeJSON": true }',
                       'post_status'  => 'publish',
                       'post_title'   => 'Custom Styles',
                       'post_type'    => $post_type_filter,
                       'post_name'    => 'tp-global-styles-' . urlencode( $tp_theme->get_stylesheet() ),
                       'tax_input'    => ['tp_name' => [$tp_theme->get_stylesheet()]]
                   ],
                   true
                );
                $user_cpt = (new static)->_get_post( $cpt_post_id, ARRAY_A );
            }
            $cache_expiration = $user_cpt ? DAY_IN_SECONDS : HOUR_IN_SECONDS;
            (new static)->_tp_cache_set( $cache_key, $user_cpt ? $user_cpt['ID'] : -1, '', $cache_expiration );
            return $user_cpt;
        }//236
        public static function get_user_data(): TP_Theme_JSON{
            if( null !== static::$_user) return static::$_user;
            $config   = [];
            $user_cpt = static::get_user_data_from_tp_global_styles( (new static)->_tp_get_theme());
            if ( array_key_exists('post_content',(array) $user_cpt)){
                $decoded_data = json_decode( $user_cpt['post_content'], true );
                $json_decoding_error = json_last_error();
                if ( JSON_ERROR_NONE !== $json_decoding_error ) {
                    trigger_error( 'Error when decoding a theme.json schema for user data. ' . json_last_error_msg() );
                    return new TP_Theme_JSON( $config, 'custom' );
                }
                if ( is_array( $decoded_data ) &&
                    isset( $decoded_data['isGlobalStylesUserThemeJSON'])&& $decoded_data['isGlobalStylesUserThemeJSON']){
                    unset( $decoded_data['isGlobalStylesUserThemeJSON'] );
                    $config = $decoded_data;
                }
            }
            static::$_user = new TP_Theme_JSON( $config, 'custom' );
            return static::$_user;
        }//301
        public static function get_merged_data( $origin = 'custom' ): TP_Theme_JSON {
            if ( is_array((array) $origin ) )
                (new static)->_deprecated_argument( __FUNCTION__, '0.0.1' );
            $result = new TP_Theme_JSON();
            $result->merge( static::get_core_data() );
            $result->merge( static::get_theme_data() );
            if ( 'custom' === $origin ) $result->merge( static::get_user_data() );
            return $result;
        }//360
        public static function get_user_global_styles_post_id(){
            if ( null !== static::$_user_custom_post_type_id ) return static::$_user_custom_post_type_id;
            $user_cpt = static::get_user_data_from_tp_global_styles( (new static)->_tp_get_theme(), true );
            if ( array_key_exists( 'ID',(array) $user_cpt ) ) static::$_user_custom_post_type_id = $user_cpt['ID'];
            return static::$_user_custom_post_type_id;
        }//384
        public static function theme_has_support(){
            if ( ! isset( static::$_theme_has_support ) )
                static::$_theme_has_support = (is_readable( static::_get_file_path_from_theme( 'theme.json'))|| is_readable( static::_get_file_path_from_theme( 'theme.json', true )));
            return static::$_theme_has_support;
        }//406
        protected static function _get_file_path_from_theme( $file_name, $template = false ): string{
            $path      = $template ? (new static)->_get_template_directory() : (new static)->_get_stylesheet_directory();
            $candidate = $path . '/' . $file_name;
            return is_readable( $candidate ) ? $candidate : '';
        }//429
        public static function clean_cached_data(): void{
            static::$_core                     = null;
            static::$_theme                    = null;
            static::$_user                     = null;
            static::$_user_custom_post_type_id = null;
            static::$_theme_has_support        = null;
            static::$_i18n_schema              = null;
        }//443
        public static function get_style_variations(): array{
            $variations     = [];
            $base_directory = (new self)->_get_stylesheet_directory() . '/styles';
            if ( is_dir( $base_directory ) ) {
                $nested_files      = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $base_directory ) );
                $nested_html_files = iterator_to_array( new \RegexIterator( $nested_files, '/^.+\.json$/i', \RecursiveRegexIterator::GET_MATCH ) );
                ksort( $nested_html_files );
                foreach ( $nested_html_files as $path => $file ) {
                    $decoded_file = (new self)->_tp_json_file_decode( $path, array( 'associative' => true ) );
                    if ( is_array( $decoded_file ) ) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $translated = (new static)->_translate( $decoded_file, (new self)->__tp_get_theme()->get( 'TextDomain' ) );
                        $variation  = ( new TP_Theme_JSON( $translated ) )->get_raw_data();
                        if ( empty( $variation['title'] ) )
                            $variation['title'] = basename( $path, '.json' );
                        $variations[] = $variation;
                    }
                }
            }
            return $variations;
        }//476
    }
}else die;