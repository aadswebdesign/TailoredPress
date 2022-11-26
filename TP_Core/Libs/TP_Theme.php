<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-3-2022
 * Time: 19:44
 */
namespace TP_Core\Libs;
use \ArrayAccess;
use TP_Core\Cores;
use TP_Core\Libs\Core_Factory\Theme_Base;
use TP_Core\Libs\Core_Factory\_array_access;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_04;
if(ABSPATH){
    final class TP_Theme extends Theme_Base implements ArrayAccess {
        use _I10n_02,_I10n_04;
        use _array_access;
        public function __construct( $theme_dir, $theme_root ){
            $theme_objects = ['headers' => $this->_headers,'errors' => $this->_errors,'stylesheet' => $this->_stylesheet,'template' => $this->_template,];
            if ( ! isset( self::$_persistently_cache ) ) {
                self::$_persistently_cache = $this->_apply_filters( 'tp_cache_themes_persistently', false, 'TP_Theme' );
                if ( self::$_persistently_cache ) {
                    $this->_tp_cache_add_global_groups( 'themes' );
                    if ( is_int( self::$_persistently_cache ) )
                        self::$_cache_expiration = self::$_persistently_cache;
                } else $this->_tp_cache_add_non_persistent_groups( 'themes' );
            }
            $this->_theme_root = $theme_root;
            $this->_stylesheet = $theme_dir;
            if ( ! in_array( $theme_root, (array) $this->_tp_theme_directories, true ) && in_array( dirname( $theme_root ), (array) $this->_tp_theme_directories, true )){
                $this->_stylesheet = basename( $this->_theme_root ) . '/' . $this->_stylesheet;
                $this->_theme_root = dirname( $theme_root );
            }
            $this->_cache_hash = md5( $this->_theme_root . '/' . $this->_stylesheet );
            $theme_file       = $this->_stylesheet . '/theme_style.css';
            $cache = $this->__cache_get( 'theme' );
            if ( is_array( $cache ) ) {
                foreach ( array( 'errors', 'headers', 'template' ) as $key ) {
                    if ( isset( $cache[ $key ] ) ) $this->$key = $cache[ $key ];
                }
                if ( $this->_errors ) return;
                if ( isset( $cache['theme_root_template'] ) )
                    $theme_root_template = $cache['theme_root_template'];
            }elseif ( ! file_exists( $this->_theme_root . '/' . $theme_file ) ) {
                $this->_headers['Name'] = $this->_stylesheet;
                if ( ! file_exists( $this->_theme_root . '/' . $this->_stylesheet ) ) {
                    $this->_errors = new TP_Error('theme_not_found', sprintf($this->__( 'The theme directory "%s" does not exist.' ), $this->_esc_html( $this->_stylesheet )));
                }else $this->_errors = new TP_Error( 'theme_no_stylesheet', $this->__( 'Stylesheet is missing.' ) );
                $this->_template = $this->_stylesheet;
                $this->__cache_add('theme',$theme_objects);
                if ( ! file_exists( $this->_theme_root ) )
                    $this->_errors->add( 'theme_root_missing', $this->__( 'Error: The themes directory is either empty or doesn&#8217;t exist. Please check your installation.' ) );
                return;
            }elseif ( ! is_readable( $this->_theme_root . '/' . $theme_file ) ) {
                $this->_headers['Name'] = $this->_stylesheet;
                $this->_errors          = new TP_Error( 'theme_stylesheet_not_readable', $this->__( 'Stylesheet is not readable.' ) );
                $this->_template        = $this->_stylesheet;
                $this->__cache_add('theme',$theme_objects);
                return;
            }else{
                $this->_headers = $this->_get_file_data( $this->_theme_root . '/' . $theme_file, self::$_file_headers, 'theme' );
                $default_theme_slug = array_search( $this->_headers['Name'], self::$_default_themes, true );
                if ($default_theme_slug && basename($this->_stylesheet) !== $default_theme_slug) $this->_headers['Name'] .= '/' . $this->_stylesheet;
            }
            if ( ! $this->_template ) $this->_template = $this->_headers['Template'];
            if ( ! $this->_template ) {
                $this->_template = $this->_stylesheet;
                //todo this should be altered later on
                if ( ! file_exists( $this->_theme_root . '/' . $this->_stylesheet . '/index.php' ) ) {
                    $error_message = sprintf(
                        $this->__( 'Template is missing. Standalone themes need to have a %1$s template file.' ),
                        '<code>index.php</code>','<code>theme_style.css</code>');
                    $this->_errors = new TP_Error( 'theme_no_index', $error_message );
                    $this->__cache_add('theme',$theme_objects );
                    return;
                }
                if ( ! is_array( $cache ) && $this->_template !== $this->_stylesheet && ! file_exists( $this->_theme_root . '/' . $this->_template . '/index.php' ) ) {
                    $theme_dir  = dirname( $this->_stylesheet );
                    $directories = $this->_search_theme_directories();
                    if ( '.' !== $theme_dir && file_exists( $this->_theme_root . '/' . $theme_dir . '/' . $this->_template . '/index.php' ) )
                        $this->_template = $theme_dir . '/' . $this->_template;
                    elseif ( $directories && isset( $directories[ $this->_template ] ) )
                        $theme_root_template = $directories[ $this->_template ]['theme_root'];
                    else{
                        //Theme dir is missing
                        $this->_errors = new TP_Error(
                            'theme_no_parent',
                            sprintf(
                                $this->__( 'This theme is missing. Please install the "%s" theme.' ),
                                $this->_esc_html( $this->_template )
                            )
                        );
                        $this->__cache_add('theme',$theme_objects );
                        $this->_theme = new TP_Theme($this->_template,$this->_theme_root);
                        return;
                    }
                }
                if (( ! $this->_init_error( $this->_errors ) || ! isset( $this->_errors->errors['theme_paused'] ) ) && $this->_tp_paused_themes()->$this->__header_get( $this->_stylesheet ))
                    $this->_errors = new TP_Error( 'theme_paused', $this->__( 'This theme failed to load properly and was paused within the admin backend.' ) );
                if ( ! is_array( $cache ) ) {
                    $cache = $theme_objects;
                    if ( isset( $theme_root_template ) )$cache['theme_root_template'] = $theme_root_template;
                    $this->__cache_add('theme',$cache);
                }
            }
        }//214
        public function __toString() {
            return (string) $this->display( 'Name' );
        }//475
        public function __isset( $offset ){
            return in_array( $offset, static::$_properties, true );
        }//487
        public function __get( $offset ){
            switch ( $offset ) {
                case 'name':
                case 'title':
                    return $this->get_theme('Name');
                case 'version':
                    return $this->get_theme('Version');
                case 'template_dir':
                    return $this->get_template_directory();
                case 'stylesheet_dir':
                    return $this->get_stylesheet_directory();
                case 'template':
                    return $this->get_template();
                case 'stylesheet':
                    return $this->get_stylesheet();
                case 'screenshot':
                    return $this->get_screenshot('relative');
                case 'description':
                    return $this->display('Description');
                case 'author':
                    return $this->display('Author');
                case 'tags':
                    return $this->get_theme('Tags');
                case 'theme_root':
                    return $this->get_theme_root();
                case 'theme_root_uri':
                    return $this->get_theme_root_uri();
                default:
                    return $this->offsetGet($offset);
            }
        }//516
        public function offsetGet( $offset ) {
            switch ( $offset ) {
                case 'Name':
                case 'Title':
                    return $this->__get( 'Name' ); //todo check out __get
                case 'Author':
                    return $this->display( 'Author' );
                case 'Author Name':
                    return $this->display( 'Author', false );
                case 'Author URI':
                    return $this->display( 'AuthorURI' );
                case 'Description':
                    return $this->display( 'Description' );
                case 'Version':
                case 'Status':
                    return $this->__get( $offset );
                case 'Template':
                    return $this->get_template();
                case 'Stylesheet':
                    return $this->get_stylesheet();
                case 'Template Files':
                    return $this->get_files( 'php', 1, true );
                case 'Stylesheet Files':
                    return $this->get_files( 'css', 0, false );
                case 'Template Dir':
                    return $this->get_template_directory();
                case 'Stylesheet Dir':
                    return $this->get_stylesheet_directory();
                case 'Screenshot':
                    return $this->get_screenshot( 'relative' );
                case 'Tags':
                    return $this->__get( 'Tags' );
                case 'Theme Root':
                    return $this->get_theme_root();
                case 'Theme Root URI':
                    return $this->get_theme_root_uri();
                case 'Parent Theme':
                    return $this->parent() ? $this->parent()->__get( 'Name' ) : '';
                default:
                    return null;
            }
        }//624
        public function errors(){
            return $this->_init_error( $this->_errors ) ? $this->_errors : false;
        }//678
        public function exists(){
            return ! ( $this->errors() && in_array( 'theme_not_found', $this->errors()->get_error_codes(), true ) );
        }//692
        public function parent(){
            $this->_set_parent = $this->_parent ?? false;
            return $this;
        }//703
        private function __cache_add( $key, $data ){
            return $this->_tp_cache_add( $key . '_' . $this->_cache_hash, $data, 'themes', self::$_cache_expiration );
        }//718
        private function __cache_get( $key ){
            return $this->_tp_cache_get( $key . '_' . $this->_cache_hash, 'themes' );
        }//732
        public function cache_delete(){
            foreach ( array( 'theme', 'screenshot', 'headers', 'post_templates' ) as $key )
                $this->_tp_cache_delete( $key . '_' . $this->_cache_hash, 'themes' );
            $this->_template          = null;
            $this->_textdomain_loaded = null;
            $this->_theme_root_uri    = null;
            $this->_errors            = null;
            $this->_headers_sanitized = null;
            $this->_name_translated   = null;
            $this->_headers           = [];
            $this->__construct( $this->_stylesheet, $this->_theme_root );
        }//741
        public function get_theme( $header ){
            if ( ! isset( $this->_headers[ $header ] ) ) return false;
            if ( ! isset( $this->headers_sanitized ) ) {
                $this->_headers_sanitized = $this->__cache_get( 'headers' );
                if ( ! is_array( $this->_headers_sanitized ) ) $this->_headers_sanitized = [];
            }
            if ( isset( $this->_headers_sanitized[ $header ] ) )
                return $this->_headers_sanitized[ $header ];
            if ( self::$_persistently_cache ) {
                foreach ( array_keys( $this->_headers ) as $_header )
                    $this->_headers_sanitized[ $_header ] = $this->__sanitize_header( $_header, $this->_headers[ $_header ] );
                $this->__cache_add( 'headers', $this->_headers_sanitized );
            } else $this->_headers_sanitized[ $header ] = $this->__sanitize_header( $header, $this->_headers[ $header ] );
            return $this->_headers_sanitized[ $header ];
        }//772
        public function display( $header, $markup = true, $translate = true ){
            $value = $this->get_theme( $header );
            if ( false === $value ) return false;
            if ( $translate && ( empty( $value ) || ! $this->load_textdomain() ) )
                $translate = false;
            if ( $translate ) $value = $this->__translate_header( $header, $value );
            if ( $markup ) $value = $this->__markup_header( $header, $value, $translate );
            return $value;
        }//812
        private function __sanitize_header( $header, $value ){
            switch ( $header ) {
                case 'Status':
                    if ( ! $value ) { $value = 'publish';}
                    break;
                case 'Name':
                    static $header_tags = ['abbr' => ['title' => true], 'acronym' => ['title' => true],
                        'code' => true,'em' => true,'strong' => true,];
                    $value = $this->_tp_kses( $value, $header_tags );
                    break;
                case 'Author':
                case 'Description':
                    static $header_tags_with_a = ['a'=> ['href'  => true,'title' => true,],'abbr' => ['title' => true],
                        'acronym' => ['title' => true],'code' => true,'em' => true,'strong'  => true,];
                    $value = $this->_tp_kses( $value, $header_tags_with_a );
                    break;
                case 'ThemeURI':
                case 'AuthorURI':
                    $value = $this->_esc_url_raw( $value );
                    break;
                case 'Tags':
                    $value = array_filter( array_map( 'trim', explode( ',', strip_tags( $value ) ) ) );
                    break;
                case 'Version':
                case 'RequiresTP':
                case 'RequiresPHP':
                    $value = strip_tags( $value );
                    break;
            }
            return $value;
        }//844
        private function __markup_header( $header, $value, $translate ){
            switch ( $header ) {
                case 'Name':
                    if ( empty( $value ) )
                        $value = $this->_esc_html( $this->_get_stylesheet() );
                    break;
                case 'Description':
                    $value = $this->_tp_texturize( $value );
                    break;
                case 'Author':
                    if ( $this->get_theme( 'AuthorURI' ) )
                        $value = sprintf( "<a href='%1\$s'>'%2\$s'</a>", $this->display( 'AuthorURI', true, $translate ), $value );
                    elseif ( ! $value ) $value = $this->__( 'Anonymous' );
                    break;
                case 'Tags':
                    static $comma = null;
                    if ( ! isset( $comma ) ) $comma = $this->__( ', ' );
                    $value = implode( $comma, $value );
                    break;
                case 'ThemeURI':
                case 'AuthorURI':
                    $value = $this->_esc_url( $value );
                    break;
            }
            return $value;
        }//907
        private function __translate_header( $header, $value ){
            if ($header === 'Name') {
                if (isset($this->_name_translated))
                    return $this->_name_translated;
                $this->_name_translated = $this->_translate($value, $this->get_theme('TextDomain'));
                return $this->_name_translated;
            }
            $value = $this->_translate($value, $this->get_theme('TextDomain'));
            return $value;
        }//950
        public function get_stylesheet(){
            return $this->_stylesheet;
        }//1028
        public function get_template(){
            return (object)$this->_template;
        }//1042
        public function get_stylesheet_directory(){
            if ( $this->errors() && in_array( 'theme_root_missing', $this->errors()->get_error_codes(), true ) )
                return '';
            return $this->_theme_root . '/' . $this->_stylesheet;
        }//1056
        public function get_template_directory(){
            $theme_root = $this->_theme_root;
            return $theme_root . '/' . $this->_template;
        }//1074
        public function get_stylesheet_directory_uri(){
            return $this->get_theme_root_uri() . '/' . str_replace( '%2F', '/', rawurlencode( $this->_stylesheet ) );
        }//1094
        public function get_template_directory_uri(){
            if ( $this->parent() ) $this->_theme_root_uri = $this->parent()->$this->__get_theme_root_uri();
            else $this->_theme_root_uri = $this->get_theme_root_uri();
            return $this->_theme_root_uri . '/' . str_replace( '%2F', '/', rawurlencode( $this->_template ) );
        }//1108
        public function get_theme_root(){
            return $this->_theme_root;
        }//1127
        public function get_theme_root_uri(){
            if ( ! isset( $this->_theme_root_uri ) )
                $this->_theme_root_uri = $this->_get_theme_root_uri( $this->_stylesheet, $this->_theme_root );
            return $this->_theme_root_uri;
        }//1142
        public function get_screenshot( $uri = 'uri' ){
            $screenshot = $this->__cache_get( 'screenshot' );
            if ( $screenshot ) {
                if ( 'relative' === $uri ) return $screenshot;
                return $this->get_stylesheet_directory_uri() . '/' . $screenshot;
            } elseif ( 0 === $screenshot ) return false;
            foreach ( array( 'png', 'gif', 'jpg', 'jpeg', 'webp' ) as $ext ) {
                if ( file_exists( $this->_get_stylesheet_directory() . "/screenshot.$ext" ) ) {
                    $this->__cache_add( 'screenshot', 'screenshot.' . $ext );
                    if ( 'relative' === $uri ) return 'screenshot.' . $ext;
                    return $this->get_stylesheet_directory_uri() . '/' . 'screenshot.' . $ext;
                }
            }
            $this->__cache_add( 'screenshot', 0 );
            return false;
        }//1162
        public function get_files( $type = null, $depth = 0, $search_parent = false ){
            $files = (array)  self::__scandir( $this->get_stylesheet_directory(), $type, $depth );
            if ( $search_parent && $this->parent() )
                $files += (array) self::__scandir( $this->get_template_directory(), $type, $depth );
            return $files;
        }//1200
        public function get_post_templates(){
            if ( $this->errors() && $this->errors()->$this->__get_error_codes() !== array( 'theme_parent_invalid' ) )//todo
                return [];
            $post_templates = $this->__cache_get( 'post_templates' );
            if ( ! is_array( $post_templates ) ) {
                $post_templates = [];
                $files = (array) $this->get_files( 'php', 1, true );
                foreach ( $files as $file => $full_path ) {
                    if ( ! preg_match( '|Template Name:(.*)$|mi', file_get_contents( $full_path ), $header ) ) continue;
                    $types = array( 'page' );
                    if ( preg_match( '|Template Post Type:(.*)$|mi', file_get_contents( $full_path ), $type ) )
                        $types = explode( ',', $this->_cleanup_header_comment( $type[1] ) );
                    foreach ( $types as $type ) {
                        $type = $this->_sanitize_key( $type );
                        if ( ! isset( $post_templates[ $type ] ) ) $post_templates[ $type ] = [];
                        $post_templates[ $type ][ $file ] = $this->_cleanup_header_comment( $header[1] );
                    }
                }
                if ( $this->_current_theme_supports( 'block-templates' ) ) {
                    $block_templates = $this->_get_block_templates( array(), 'tp_template' );
                    foreach ( $this->_get_post_types( array( 'public' => true ) ) as $type ) {
                        foreach ( (array)$block_templates as $block_template ) {
                            if ( ! $block_template->is_custom ) continue;
                            if ( isset( $block_template->post_types ) && ! in_array( $type, $block_template->post_types, true ) )
                                continue;
                            $post_templates[ $type ][ $block_template->slug ] = $block_template->title;
                        }
                    }
                }
                $this->__cache_add( 'post_templates', $post_templates );
            }
            if ( $this->load_textdomain() ) {
                foreach ( $post_templates as &$post_type ) {
                    foreach ( $post_type as &$post_template )
                        $post_template = $this->__translate_header( 'Template Name', $post_template );
                }
            }
            return $post_templates;
        }//1219
        public function get_page_templates($post = null, $post_type = 'page' ){
            if ($post ) $post_type = (string) $this->_get_post_type($post);//todo
            $post_templates = $this->get_post_templates();
            $post_templates = $post_templates[ $post_type ] ?? [];
            $post_templates = (array) $this->_apply_filters( 'theme_templates', $post_templates, $this, $post, $post_type );
            $post_templates = (array) $this->_apply_filters( "theme_{$post_type}_templates", $post_templates, $this, $post, $post_type );
            return $post_templates;
        }//1294
        private static function __scandir( $path, $extensions = null, $depth = 0, $relative_path = '' ){
            $_extensions = '';
            if ( ! is_dir( $path ) ) return false;
            if ( $extensions ) {
                $extensions  = (array) $extensions;
                $_extensions = implode( '|', $extensions );
            }
            $relative_path = (new static('theme_dir','theme_root'))->_trailingslashit( $relative_path );
            if ( '/' === $relative_path ) $relative_path = '';
            $results = scandir( $path );
            $files   = [];

            $exclusions = (array) (new static('theme_dir','theme_root'))->_apply_filters( 'theme_scandir_exclusions', array( 'CVS', 'node_modules', 'vendor', 'bower_components' ) );
            foreach ( $results as $result ) {
                if ( '.' === $result[0] || in_array( $result, $exclusions, true ) ) continue;
                if ( is_dir( $path . '/' . $result ) ) {
                    if ( ! $depth ) continue;
                    $found = self::__scandir( $path . '/' . $result, $extensions, $depth - 1, $relative_path . $result );
                    //$files = array_merge_recursive( $files, $found );
                    $files = (new static('theme_dir','theme_root'))->_tp_array_merge_recursive($files, $found);
                } elseif ( ! $extensions || preg_match( '~\.(' . $_extensions . ')$~', $result ) )
                    $files[ $relative_path . $result ] = $path . '/' . $result;
            }
            return $files;
        }//1355
        public function load_textdomain(){
            if ( isset( $this->__textdomain_loaded ) )
                return $this->__textdomain_loaded;
            $textdomain = $this->__get( 'TextDomain' );
            if ( ! $textdomain ) {
                $this->__textdomain_loaded = false;
                return false;
            }
            if ( $this->_is_textdomain_loaded( $textdomain ) ) {
                $this->__textdomain_loaded = true;
                return true;
            }
            $path       = $this->get_stylesheet_directory();
            $domain_path = $this->__get( 'DomainPath' );
            if ( $domain_path ) $path .= $domain_path;
            else $path .= '/languages';
            $this->__textdomain_loaded = $this->_load_theme_textdomain( $textdomain, $path );
            return $this->__textdomain_loaded;
        }//1411
        public function is_allowed( $check = 'both', $blog_id = null ){
            if ( ! $this->_is_multisite() ) return true;
            if ( 'both' === $check || 'network' === $check ) {
                $allowed = self::get_allowed_on_network();
                if ( ! empty( $allowed[ $this->get_stylesheet() ] ) ) return true;
            }
            if ( 'both' === $check || 'site' === $check ) {
                $allowed = self::get_allowed_on_site( $blog_id );
                if ( ! empty( $allowed[ $this->get_stylesheet() ] ) ) return true;
            }
            return false;
        }//1449
        public function is_block_theme(){
            $paths_to_index_block_template = array(
                $this->get_file_path( '/block_templates/index.html' ),
                $this->get_file_path( '/templates/index.html' ),
            );
            foreach ( $paths_to_index_block_template as $path_to_index_block_template ) {
                if ( is_file( $path_to_index_block_template ) && is_readable( $path_to_index_block_template ) )
                    return true;
            }
            return false;
        }//1478
        public function get_file_path( $file = '' ){
            $file = ltrim( $file, '/' );
            $stylesheet_directory = $this->get_stylesheet_directory();
            $template_directory   = $this->get_template_directory();
            if ( empty( $file ) )
                $path = $stylesheet_directory;
            elseif ( file_exists( $stylesheet_directory . '/' . $file ) )
                $path = $stylesheet_directory . '/' . $file;
            else $path = $template_directory . '/' . $file;
            return $this->_apply_filters( 'theme_file_path', $path, $file );
        }//1504
        public static function get_core_default_theme(){
            foreach ( array_reverse( self::$_default_themes ) as $slug => $name ) {
                $theme = (new static('theme_dir','theme_root'))->_tp_get_theme( $slug );
                if ($theme instanceof self && $theme->exists() ) return $theme;
            }
            return false;
        }//1531
        public static function get_allowed( $blog_id = null ){
            $network = (array) (new static('theme_dir','theme_root'))->_apply_filters( 'network_allowed_themes', self::get_allowed_on_network(), $blog_id );
            return $network + self::get_allowed_on_site( $blog_id );
        }//1549
        public static function get_allowed_on_network(){
            static $allowed_themes;
            $allowed_themes = (new static('theme_dir','theme_root'))->_apply_filters( 'allowed_themes', $allowed_themes );
            return $allowed_themes;
        }//1572
        public static function get_allowed_on_site( $blog_id = null ){
            static $allowed_themes = [];
            if ( ! $blog_id || ! (new static('theme_dir','theme_root'))->_is_multisite() )
                $blog_id = (new static('theme_dir','theme_root'))->_get_current_blog_id();
            if ( isset( $allowed_themes[ $blog_id ] ) )
                return (array) (new static('theme_dir','theme_root'))->_apply_filters( 'site_allowed_themes', $allowed_themes[ $blog_id ], $blog_id );
            $current = (new static('theme_dir','theme_root'))->_get_current_blog_id() === $blog_id;
            if ( $current ) $allowed_themes[ $blog_id ] = (new static('theme_dir','theme_root'))->_get_option( 'allowed_themes' );
            else{
                (new static('theme_dir','theme_root'))->_switch_to_blog( $blog_id );
                $allowed_themes[ $blog_id ] = (new static('theme_dir','theme_root'))->_get_option( 'allowed_themes' );
                (new static('theme_dir','theme_root'))->_restore_current_blog();
            }
            return (array) (new static('theme_dir','theme_root'))->_apply_filters( 'site_allowed_themes', $allowed_themes[ $blog_id ], $blog_id );
        }//1598
        public static function network_enable_theme( $stylesheets ){
            if ( ! (new static('theme_dir','theme_root'))->_is_multisite() ) return;
            if ( ! is_array( $stylesheets ) )
                $stylesheets = array( $stylesheets );
            $allowed_themes = (new static('theme_dir','theme_root'))->_get_option( 'allowed_themes' );
            foreach ( $stylesheets as $stylesheet ) $allowed_themes[ $stylesheet ] = true;
            (new static('theme_dir','theme_root'))->_update_option( 'allowed_themes', $allowed_themes );
        }//1675
        public static function network_disable_theme( $stylesheets ){
            if ( ! (new static('theme_dir','theme_root'))->_is_multisite() ) return;
            if ( ! is_array( $stylesheets ) ) $stylesheets = array( $stylesheets );
            $allowed_themes = (new static('theme_dir','theme_root'))->_get_option( 'allowed_themes' );
            foreach ( $stylesheets as $stylesheet ) {
                if ( isset( $allowed_themes[ $stylesheet ] ) )
                    unset( $allowed_themes[ $stylesheet ] );
            }
            (new static('theme_dir','theme_root'))->_update_option( 'allowed_themes', $allowed_themes );
        }//1699
        public static function sort_by_name( &$themes ){
            $_static_theme = new static('theme_dir','theme_root');
            $static_theme = null;
            if( $_static_theme instanceof Cores ){ $static_theme = $_static_theme;}
            if ( 0 === strpos( $static_theme->get_user_locale(), 'en_' ))
                uasort($themes, array( 'TP_Theme', '__name_sort' ));
            else {
                foreach ( $themes as $key => $theme )
                    $theme->this->__translate_header( 'Name', $theme->__headers['Name'] );
                uasort( $themes, array( 'TP_Theme', '__name_sort_i18n' ) );
            }
        }//1725
        //todo make it private if is needed
        protected static function _name_sort( $a, $b ){
            return strnatcasecmp( $a->__headers['Name'], $b->__headers['Name'] );
        }//1749
        protected static function _name_sort_i18n( $a, $b ){
            return strnatcasecmp( $a->name_translated, $b->name_translated );
        }
    }
}else die;