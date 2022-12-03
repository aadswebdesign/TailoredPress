<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-3-2022
 * Time: 17:40
 */
namespace TP_Core\Traits\Theme;
use TP_Core\Traits\Constructs\_construct_editor;
use TP_Admin\Libs\AdmCustoms\TP_Custom_Image_Header;
use TP_Admin\Libs\AdmCustoms\TP_Custom_Background;
if(ABSPATH){
    trait _theme_07 {
        use _construct_editor;
        /**
         * @description Retrieves any registered editor stylesheet URLs.
         * @return string
         */
        protected function _get_editor_stylesheets():string{
            $stylesheets = [];
            if ( ! empty( $this->tp_editor_styles ) && is_array( $this->tp_editor_styles ) ) {
                $editor_styles = $this->tp_editor_styles;
                $editor_styles = array_unique( array_filter( $editor_styles ) );
                $style_uri     = $this->_get_stylesheet_directory_uri();
                $style_dir     = $this->_get_stylesheet_directory();
                foreach ( $editor_styles as $key => $file ) {
                    if ( preg_match( '~^(https?:)?//~', $file ) ) {
                        $stylesheets[] = $this->_esc_url_raw( $file );
                        unset( $editor_styles[ $key ] );
                    }
                }
                //child_themes skipped
                foreach ( $editor_styles as $file ) {
                    if ( $file && file_exists( "$style_dir/$file" ) ) {
                        $stylesheets[] = "$style_uri/$file";
                    }
                }
                return $this->_apply_filters( 'editor_stylesheets', $stylesheets );
            }
        }//2128
        /**
         * @description Expands a theme's starter content configuration using core-provided data.
         * @return mixed
         */
        protected function _get_theme_starter_content(){
            $theme_support = $this->_get_theme_support( 'starter-content' );
            if ( is_array( $theme_support ) && ! empty( $theme_support[0] ) && is_array( $theme_support[0] ) ) {
                $config = $theme_support[0];
            } else {$config = [];}
            $core_content = [];//todo adding the logic to this array
            $content = [];
            foreach ( $config as $type => $args ) {
                switch ( $type ) {
                    case 'options':
                    case 'theme_mods':
                        $content[ $type ] = $config[ $type ];
                        break;
                    //widgets skipped
                    case 'nav_menus':
                        foreach ( $config[ $type ] as $nav_menu_location => $nav_menu ) {
                            if ( empty( $nav_menu['name'] ) ) { $nav_menu['name'] = $nav_menu_location;}
                            $content[ $type ][ $nav_menu_location ]['name'] = $nav_menu['name'];
                            foreach ( $nav_menu['items'] as $id => $nav_menu_item ) {
                                if ((array)$nav_menu_item && ! empty( $core_content[ $type ][ $id ] )) {
                                    $nav_menu_item = $this->_tp_array_merge($core_content[ $type ][ $id ], $nav_menu_item );
                                    $content[ $type ][ $nav_menu_location ]['items'][] = $nav_menu_item;
                                }elseif ( is_string( $nav_menu_item ) && ! empty( $core_content[ $type ] ) && ! empty( $core_content[ $type ][ $nav_menu_item ])){
                                    $content[ $type ][ $nav_menu_location ]['items'][] = $core_content[ $type ][ $nav_menu_item ];
                                }
                            }
                        }
                        break;
                    case 'attachments':
                        foreach ( $config[ $type ] as $id => $item ) {
                            if(!empty($item['file'])){ $content[ $type ][ $id ] = $item;}
                        }
                        break;
                    case 'posts':
                        foreach ( $config[ $type ] as $id => $item ) {
                            if((array) $item && !empty( $core_content[ $type ][ $id ] )){
                                $item = $this->_tp_array_merge( $core_content[ $type ][ $id ], $item);
                                $content[ $type ][ $id ] = $this->_tp_array_slice_assoc($item,[
                                    'post_type','post_title','post_excerpt','post_name','post_content',
                                    'menu_order','comment_status','thumbnail','template',]);
                            }elseif ( is_string( $item ) && ! empty( $core_content[ $type ][ $item ] ) ) {
                                $content[ $type ][ $item ] = $core_content[ $type ][ $item ];
                            }
                        }
                        break;
                }
            }
            return $this->_apply_filters( 'get_theme_starter_content', $content, $config );
        }//2182
        /**
         * @description  Registers theme support for a given feature.
         * @param $feature
         * @param \array[] ...$args
         * @return mixed
         */
        protected function _add_theme_support( $feature,array ...$args ):mixed{
            if ( ! $args ) { $args = (array) true;}
            switch ( $feature ) {
                case 'post-thumbnails':
                    // All post types are already supported.
                    if ( true === $this->_get_theme_support( 'post-thumbnails' ) ) {return true;}
                    if ( isset( $args[0],$this->tp_theme_features['post-thumbnails'] ) && is_array( $args[0] )) {
                        $args[0] = array_unique( array_merge( $this->tp_theme_features['post-thumbnails'][0], $args[0] ) );
                    }
                    break;
                case 'post-formats':
                    if ( isset( $args[0] ) && is_array( $args[0] ) ) {
                        $post_formats = $this->_get_post_format_slugs();
                        unset( $post_formats['standard'] );
                        $args[0] = array_intersect( $args[0], array_keys( $post_formats ) );
                    } else {
                        $this->_doing_it_wrong("add_theme_support( 'post-formats' )",$this->__( 'You need to pass an array of post formats.' ),'0.0.1' );
                        return false;
                    }
                    break;
                case 'custom-logo':
                    if ( true === $args ){$args = [0=>[]]; }
                    $defaults = ['width' => null,'height' => null,'flex-width' => false,
                        'flex-height' => false,'header-text' => '','unlink-homepage-logo' => false,];
                    $args[0]  = $this->_tp_parse_args( array_intersect_key( $args[0], $defaults ), $defaults );
                    if ( is_null( $args[0]['width'] ) && is_null( $args[0]['height'] ) ) {
                        $args[0]['flex-width']  = true;
                        $args[0]['flex-height'] = true;
                    }
                    break;
                case 'custom-header-uploads':
                    return $this->_add_theme_support( 'custom-header',['uploads' => true]);
                case 'custom-header':
                    if ( true === $args ){ $args = [0 => []]; }
                    $defaults = ['default-image' => '','random-default' => false,'width' => 0,'height' => 0,
                        'flex-height' => false,'flex-width' => false,'default-text-color' => '','header-text' => true,
                        'uploads' => true,'tp-head-callback' => '','admin-head-callback' => '','admin-preview-callback' => '',
                        'video' => false,'video-active-callback' => 'is_front_page',];
                    $jit = isset( $args[0]['__jit'] );
                    unset( $args[0]['__jit'] );
                    if ( isset( $this->tp_theme_features['custom-header'] ) ) {
                        $args[0] = $this->_tp_parse_args( $this->tp_theme_features['custom-header'][0], $args[0] );}
                    if ( $jit ) {$args[0] = $this->_tp_parse_args( $args[0], $defaults );}
                    if ( defined( 'NO_HEADER_TEXT' ) ) {
                        $args[0]['header-text'] = ! NO_HEADER_TEXT;
                    } elseif ( isset( $args[0]['header-text'] ) ) {
                        define( 'NO_HEADER_TEXT', empty( $args[0]['header-text'] ) );
                    }
                    if ( defined( 'HEADER_IMAGE_WIDTH' ) ) {
                        $args[0]['width'] = HEADER_IMAGE_WIDTH;
                    } elseif ( isset( $args[0]['width'] ) ) {
                        define( 'HEADER_IMAGE_WIDTH', (int) $args[0]['width'] );
                    }
                    if ( defined( 'HEADER_IMAGE_HEIGHT' ) ) {
                        $args[0]['height'] = HEADER_IMAGE_HEIGHT;
                    } elseif ( isset( $args[0]['height'] ) ) {
                        define( 'HEADER_IMAGE_HEIGHT', (int) $args[0]['height'] );
                    }
                    if ( defined( 'HEADER_TEXTCOLOR' ) ) {
                        $args[0]['default-text-color'] = HEADER_TEXTCOLOR;
                    } elseif ( isset( $args[0]['default-text-color'] ) ) {
                        define( 'HEADER_TEXTCOLOR', $args[0]['default-text-color'] );
                    }
                    if ( defined( 'HEADER_IMAGE' ) ) {
                        $args[0]['default-image'] = HEADER_IMAGE;
                    } elseif ( isset( $args[0]['default-image'] ) ) {
                        define( 'HEADER_IMAGE', $args[0]['default-image'] );
                    }
                    if ( $jit && ! empty( $args[0]['default-image'] ) ) {
                        $args[0]['random-default'] = false;
                    }
                    if ( $jit ) {
                        if ( empty( $args[0]['width'] ) && empty( $args[0]['flex-width'] ) ) {
                            $args[0]['flex-width'] = true;
                        }
                        if ( empty( $args[0]['height'] ) && empty( $args[0]['flex-height'] ) ) {
                            $args[0]['flex-height'] = true;
                        }
                    }
                    break;
                case 'custom-background':
                    if ( true === $args ){ $args = [0 => []]; }
                    $defaults = ['default-image' => '','default-preset' => 'default','default-position-x' => 'left',
                        'default-position-y' => 'top','default-size' => 'auto','default-repeat' => 'repeat',
                        'default-attachment' => 'scroll','default-color' => '','tp-head-callback' => '_custom_background_cb',
                        'admin-head-callback' => '','admin-preview-callback' => '',];
                    $jit = isset( $args[0]['__jit'] );
                    unset( $args[0]['__jit'] );
                    if ( isset( $this->tp_theme_features['custom-background'] ) ) {
                        $args[0] = $this->_tp_parse_args( $this->tp_theme_features['custom-background'][0], $args[0] );}
                    if ( $jit ) {$args[0] = $this->_tp_parse_args( $args[0], $defaults );}
                    if ( defined( 'BACKGROUND_COLOR' ) ) {
                        $args[0]['default-color'] = BACKGROUND_COLOR;
                    } elseif ( isset( $args[0]['default-color'] ) || $jit ) {
                        define( 'BACKGROUND_COLOR', $args[0]['default-color'] );
                    }
                    if ( defined( 'BACKGROUND_IMAGE' ) ) {
                        $args[0]['default-image'] = BACKGROUND_IMAGE;
                    } elseif ( isset( $args[0]['default-image'] ) || $jit ) {
                        define( 'BACKGROUND_IMAGE', $args[0]['default-image'] );
                    }
                    break;
                case 'title-tag':
                    // Can be called in functions.php but must happen before wp_loaded, i.e. not in header.php.
                    if ( $this->_did_action( 'tp_loaded' ) ) {
                        $this->_doing_it_wrong(
                            "add_theme_support( 'title-tag' )",
                            sprintf($this->__( 'Theme support for %1$s should be registered before the %2$s hook.' ),
                                '<code>title-tag</code>','<code>tp_loaded</code>'),'0.0.1');/* translators: 1: title-tag, 2: wp_loaded */
                        return false;
                    }
            }
            $this->tp_theme_features[ $feature ] = $args;
        }//2568
        /**
         * @description Registers the internal custom header and background routines.
         */
        protected function _custom_header_background_just_in_time():void{
            if ( $this->_current_theme_supports( 'custom-header' ) ) {
                // In case any constants were defined after an add_custom_image_header() call, re-run.
                $this->_add_theme_support( 'custom-header', array( '__jit' => true ) );
                $args = $this->_get_theme_support( 'custom-header' );
                if ( $args[0]['tp-head-callback'] ) {
                    $this->_add_action( 'tp_head', $args[0]['tp-head-callback'] );
                }
                if ( $this->_is_admin() ) {
                    //require_once ABSPATH . 'wp-admin/includes/class-custom-image-header.php';
                    $this->tp_custom_image_header = new TP_Custom_Image_Header( $args[0]['admin-head-callback'], $args[0]['admin-preview-callback'] );
                }
            }
            if ( $this->_current_theme_supports( 'custom-background' ) ) {
                // In case any constants were defined after an add_custom_background() call, re-run.
                $this->_add_theme_support( 'custom-background', array( '__jit' => true ) );
                $args = $this->_get_theme_support( 'custom-background' );
                $this->_add_action( 'tp_head', $args[0]['tp-head-callback'] );
                if ( $this->_is_admin() ) {
                    $this->tp_custom_background = new TP_Custom_Background( $args[0]['admin-head-callback'], $args[0]['admin-preview-callback'] );
                }
            }
        }//2820
        /**
         * @description Adds CSS to hide header text for custom logo, based on Customizer setting.
         */
        protected function _custom_logo_header_styles():void{
            if ( ! $this->_current_theme_supports( 'custom-header', 'header-text' ) && $this->_get_theme_support( 'custom-logo', 'header-text') && ! $this->_get_theme_mod( 'header_text', true )){
                $classes = (array) $this->_get_theme_support( 'custom-logo', 'header-text' );
                $classes = array_map( 'sanitize_html_class', $classes );
                $classes = '.' . implode( ', .', $classes );
                $custom_logo_css = static function()use($classes){
                    ?>
                    <style id='custom_logo_css'>
                        <?php echo $classes; ?> {
                            position: absolute;
                            clip: rect(1px, 1px, 1px, 1px);
                        }
                    </style>
                    <?php
                };
                echo (string)$custom_logo_css;
            }
        }//2858
        /**
         * @description Gets the theme support arguments passed when registering that support.
         * @param $feature
         * @param array ...$args
         * @return string
         */
        protected function _get_theme_support($feature, ...$args):string{
            if ( ! isset( $this->tp_theme_features[ $feature ] ) ) {
                return false;
            }
            if ( ! $args){return $this->tp_theme_features[ $feature ];}
            switch ( $feature ) {
                case 'custom-logo':
                case 'custom-header':
                case 'custom-background':
                    return $this->tp_theme_features[$feature][0][$args[0]] ?? false;
                default:
                    return $this->tp_theme_features[$feature];
            }
        }//2899
        /**
         * @description Allows a theme to de-register its support of a certain feature
         * @param $feature
         * @return bool|string
         */
        protected function _remove_theme_support( $feature ){
            if ( in_array( $feature, array( 'editor-style', 'widgets', 'menus' ), true ) ) {
                return false;
            }
            return $this->_remove_theme_support( $feature );
        }//2938
        /**
         * @description  Do not use. Removes theme support internally without knowledge of those not used by themes directly.
         * @param $feature
         * @return bool|void
         */
        protected function _remove_theme_support_internally( $feature ){
            if ($feature === 'custom-header-uploads') {
                if (!isset($this->tp_theme_features['custom-header'])){return false;}
                $this->_add_theme_support('custom-header', array('uploads' => false));
                return;
            }
            if ( ! isset( $this->tp_theme_features[ $feature ] ) ) {
                return false;
            }
            switch ( $feature ) {
                case 'custom-header':
                    if ( ! $this->_did_action( 'tp_loaded' ) ) {
                        break;
                    }
                    $support = $this->_get_theme_support( 'custom-header' );
                    if ( isset( $support[0]['tp-head-callback'] ) ) {
                        $this->_remove_action( 'tp_head', $support[0]['tp-head-callback'] );
                    }
                    if ( isset( $this->tp_custom_image_header ) ) {
                        $this->_remove_action( 'admin_menu', array( $this->tp_custom_image_header, 'init' ) );
                        unset( $this->tp_custom_image_header );
                    }
                    break;
                case 'custom-background':
                    if ( ! $this->_did_action( 'tp_loaded' ) ) {break;}
                    $support = $this->_get_theme_support( 'custom-background' );
                    if ( isset( $support[0]['tp-head-callback'] ) ) {
                        $this->_remove_action( 'tp_head', $support[0]['tp-head-callback'] );
                    }
                    $this->_remove_action( 'admin_menu', array(  $this->tp_custom_background, 'init' ) );
                    unset( $this->tp_custom_background );
                    break;
            }
            unset( $this->tp_theme_features[ $feature ] );
            return true;
        }//2961
        /**
         * @description Checks a theme's support for a given feature.
         * @param $feature
         * @param array ...$args
         * @return bool
         */
        protected function _current_theme_supports( $feature, ...$args):bool{
            if ( 'custom-header-uploads' === $feature ) {
                return $this->_current_theme_supports( 'custom-header', 'uploads' );
            }
            if (!isset( $this->tp_theme_features[ $feature ])){return false;}
            if (!$args){ return true;}
            switch ( $feature ) {
                case 'post-thumbnails':
                    if ( true === $this->tp_theme_features[ $feature ] ) {  // Registered for all types.
                        return true;
                    }
                    $content_type = $args[0];
                    return in_array( $content_type, $this->tp_theme_features[ $feature ][0], true );
                case 'post-formats':
                    $type = $args[0];
                    return in_array( $type, $this->tp_theme_features[ $feature ][0], true );
                case 'custom-logo':
                case 'custom-header':
                case 'custom-background':
                    // Specific capabilities can be registered by passing an array to add_theme_support().
                    return ( isset( $this->tp_theme_features[ $feature ][0][ $args[0] ] ) && $this->tp_theme_features[ $feature ][0][ $args[0] ] );
            }
            return $this->_apply_filters( "current_theme_supports_{$feature}", true, $args, $this->tp_theme_features[ $feature ] );
        }//3029
        /**
         * @description Checks a theme's support for a given feature before loading the functions which implement it.
         * @param $feature
         * @param $include
         * @return bool
         */
        protected function _require_if_theme_supports( $feature, $include ):bool{
            if ( $this->_current_theme_supports( $feature ) ){
                /** @noinspection PhpIncludeInspection */
                require $include;
                return true;
            }
            return false;
        }//3101
    }
}else die;