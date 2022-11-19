<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-3-2022
 * Time: 17:40
 */
namespace TP_Core\Traits\Theme;
use TP_Core\Libs\TP_Paused_Extensions_Storage;
use TP_Core\Traits\Inits\_init_custom;
use TP_Core\Traits\Inits\_init_theme;
use TP_Core\Libs\TP_Theme;
if(ABSPATH){
    trait _theme_02 {
        use _init_theme;
        use _init_custom;
        /**
         * @description Retrieves template directory URI for current theme.
         * @return mixed
         */
        protected function _get_template_directory_uri(){
            $template         = str_replace( '%2F', '/', rawurlencode( $this->_get_template() ) );
            $theme_root_uri   = $this->_get_theme_root_uri( $template );
            $template_dir_uri = "$theme_root_uri/$template";
            return $this->_apply_filters( 'template_directory_uri', $template_dir_uri, $template, $theme_root_uri );
        }//349
        /**
         * @description Retrieves theme roots.
         * @return mixed
         */
        protected function _get_theme_roots(){
            if ( ! is_array( $this->tp_theme_directories ) || count( $this->tp_theme_directories ) <= 1 )
                return '/themes';
            $theme_roots = $this->_get_site_transient( 'theme_roots' );
            if ( false === $theme_roots ) {
                $this->_search_theme_directories( true ); // Regenerate the transient.
                $theme_roots = $this->_get_site_transient( 'theme_roots' );
            }
            return $theme_roots;
        }//376
        /**
         * @description Registers a directory that contains themes.
         * @param $directory
         * @return bool
         */
        protected function _register_theme_directory( $directory ):bool{
            if ( ! file_exists( $directory ) ) {
                $directory = TP_CONTENT_DIR . '/' . $directory;
                if ( ! file_exists( $directory ) ) return false;
            }
            if ( ! is_array( $this->tp_theme_directories ) ) $this->tp_theme_directories = [];
            $un_trailed = $this->_untrailingslashit( $directory );
            if ( ! empty( $un_trailed ) && ! in_array( $un_trailed, $this->tp_theme_directories, true ) )
                $this->tp_theme_directories[] = $un_trailed;
            return true;
        }//403
        /**
         * todo set the stylesheet name
         * @description Searches all registered theme directories for complete and valid themes.
         * @param bool $force
         * @return array|bool|null
         */
        protected function _search_theme_directories( $force = false ){
            static $found_themes = null;
            if ( empty( $this->tp_theme_directories )) return false;
            if ( ! $force && isset($found_themes)) return $found_themes;
            $found_themes = [];
            $tp_theme_directories = (array) $this->tp_theme_directories;
            $relative_theme_roots = [];
            foreach ( $tp_theme_directories as $theme_root ) {
                if ( 0 === strpos( $theme_root, TP_CONTENT_DIR))
                    $relative_theme_roots[ str_replace( TP_CONTENT_DIR, '', $theme_root ) ] = $theme_root;
                else $relative_theme_roots[ $theme_root ] = $theme_root;
            }
            $this->tp_cache_expiration = $this->_apply_filters( 'tp_cache_themes_persistently', false, [$this,'_search_theme_directories'] );
            if ( $this->tp_cache_expiration ) { //todo
                $cached_roots = $this->_get_site_transient( 'theme_roots' );
                if ( is_array( $cached_roots ) ) {
                    foreach ( $cached_roots as $theme_dir => $theme_root ) {
                        if ( ! isset( $relative_theme_roots[ $theme_root ] ) )continue;
                        $found_themes[ $theme_dir ] = array(
                            'theme_file' => $theme_dir . '/style.css',
                            'theme_root' => $relative_theme_roots[ $theme_root ], // Convert relative to absolute.
                        );
                    }
                    return $found_themes;
                }
                if ( ! is_int( $this->tp_cache_expiration ) ) $this->tp_cache_expiration = 30 * MINUTE_IN_SECONDS;
            }else $this->tp_cache_expiration = 30 * MINUTE_IN_SECONDS;
            foreach ( $tp_theme_directories as $theme_root ) {
                $dirs = @ scandir( $theme_root );
                if ( ! $dirs ) {
                    trigger_error( "$theme_root is not readable", E_USER_NOTICE );
                    continue;
                }
                foreach ( $dirs as $dir ) {
                    if (!is_dir( $theme_root.'/'.$dir )||'.' === $dir[0]||'CVS' === $dir) continue;
                    if ( file_exists( $theme_root . '/' . $dir . '/style.css' ) )
                        $found_themes[ $dir ] = ['theme_file' => $dir . '/style.css','theme_root' => $theme_root,];
                    else{
                        $found_theme = false;
                        $sub_dirs = @ scandir( $theme_root . '/' . $dir );
                        if ( ! $sub_dirs ) {
                            trigger_error( "$theme_root/$dir is not readable", E_USER_NOTICE );
                            continue;
                        }
                        foreach ($sub_dirs as $sub_dir ) {
                            if ( ! is_dir( $theme_root . '/' . $dir . '/' . $sub_dir )||'.' === $dir[0]||'CVS'===$dir) continue;
                            if ( ! file_exists( $theme_root . '/' . $dir . '/' . $sub_dir . '/style.css' ) ) continue;
                            $found_themes[ $dir . '/' . $sub_dir ] = ['theme_file' => $dir . '/' . $sub_dir . '/style.css','theme_root' => $theme_root,];
                            $found_theme = true;
                        }
                        if ( ! $found_theme )  $found_themes[ $dir ] = ['theme_file' => $dir . '/style.css','theme_root' => $theme_root,];
                    }
                }
            }
            asort( $found_themes );
            $this->tp_theme_roots = []; //todo
            $relative_theme_roots = array_flip( $relative_theme_roots );
            foreach ( $found_themes as $theme_dir => $theme_data )
                $this->tp_theme_roots[ $theme_dir ] = $relative_theme_roots[ $theme_data['theme_root'] ]; // Convert absolute to relative.
            return $found_themes;
        }//437
        /**
         * todo set the theme dir
         * @description Retrieves path to themes directory.
         * @param string $stylesheet_or_template
         * @return mixed
         */
        protected function _get_theme_root( $stylesheet_or_template = '' ){
            $theme_root = '';
            if ( $stylesheet_or_template ) {
                $theme_root = $this->_get_raw_theme_root( $stylesheet_or_template );
                if ($theme_root && !in_array($theme_root, (array)$this->tp_theme_directories, true)) $theme_root = TP_CONTENT_DIR . $theme_root;
            }
            if ( ! $theme_root ) $theme_root = TP_CONTENT_DIR . '/Themes';
            return $this->_apply_filters( 'theme_root', $theme_root );
        }//582
        /**
         * @description Retrieves URI for themes directory.
         * @param string $stylesheet_or_template
         * @param string $theme_root
         * @return mixed
         */
        protected function _get_theme_root_uri( $stylesheet_or_template = '', $theme_root = '' ){
            if ( $stylesheet_or_template && ! $theme_root )
                $theme_root = (string)$this->_get_raw_theme_root( $stylesheet_or_template );
            if ( $stylesheet_or_template && $theme_root ) {
                if ( in_array( $theme_root, (array) $this->tp_theme_directories, true ) ) {
                    if ( 0 === strpos( $theme_root, TP_CONTENT_DIR ) )
                        $theme_root_uri = $this->_content_url( str_replace( TP_CONTENT_DIR, '', $theme_root ) );
                    elseif ( 0 === strpos( $theme_root, ABSPATH ) )
                        $theme_root_uri = $this->_site_url( str_replace( ABSPATH, '', $theme_root ) );
                    else $theme_root_uri = $theme_root;
                } else $theme_root_uri = $this->_content_url( $theme_root );
            } else  $theme_root_uri = $this->_content_url( 'themes' );
            return $this->_apply_filters( 'theme_root_uri', $theme_root_uri, $this->_get_option( 'siteurl' ), $stylesheet_or_template );
        }//627
        /**
         * @description Gets the raw theme root relative to the content directory with no filters applied.
         * @param $stylesheet_or_template
         * @param bool $skip_cache
         * @return bool|string
         */
        protected function _get_raw_theme_root( $stylesheet_or_template, $skip_cache = false ){
            if ( ! is_array( $this->tp_theme_directories ) || count( $this->tp_theme_directories ) <= 1 )
                return '/themes';
            $theme_root = false;
            // If requesting the root for the current theme, consult options to avoid calling get_theme_roots().
            if ( ! $skip_cache ) {
                if ( $this->_get_option( 'stylesheet' ) === $stylesheet_or_template )
                    $theme_root = $this->_get_option( 'stylesheet_root' );
                elseif ( $this->_get_option( 'template' ) === $stylesheet_or_template )
                    $theme_root = $this->_get_option( 'template_root' );
            }
            if ( empty( $theme_root ) ) {
                $theme_roots = $this->_get_theme_roots();
                if ( ! empty( $theme_roots[ $stylesheet_or_template ] ) ) $theme_root = $theme_roots[ $stylesheet_or_template ];
            }
            return $theme_root;
        }//677
        /**
         * @description Displays localized stylesheet link element.
         */
        protected function _locale_stylesheet():void{
            $stylesheet = $this->_get_locale_stylesheet_uri();
            if ( empty( $stylesheet)) return;
            $type_attr = $this->_current_theme_supports( 'html5', 'style' ) ? '' : ' type="text/css"';
            $percent_s = '%s';
            printf("<link rel='stylesheet' href='{$percent_s}' media='screen' />", $stylesheet, $type_attr);
        }//710
        /**
         * todo needs lookup/edits
         * @description Switches the theme.
         * @param $stylesheet
         */
        protected function _switch_theme( $stylesheet ):void{
            $tp_customize_manager = $this->_init_customize_manager();
            $requirements = $this->_validate_theme_requirements( $stylesheet );
            if ( $this->_init_error( $requirements ) ) $this->_tp_die( $requirements );
            $_sidebars_widgets = null;
            if ( 'tp_async_customize_save' === $this->_current_action() ) {
                /** @noinspection PhpUndefinedMethodInspection */
                $old_sidebars_widgets_data_setting = $tp_customize_manager->get_setting( 'old_sidebars_widgets_data' );
                if ( $old_sidebars_widgets_data_setting )
                    $_sidebars_widgets = $tp_customize_manager->post_value( $old_sidebars_widgets_data_setting );
            }elseif ( is_array( $this->tp_sidebars_widgets ) )$_sidebars_widgets = $this->tp_sidebars_widgets;
            if ( is_array( $_sidebars_widgets ) ) $this->_set_theme_mod('sidebars_widgets',['time' => time(), 'data' => $_sidebars_widgets,]);
            $nav_menu_locations = $this->_get_theme_mod( 'nav_menu_locations' );
            $this->_update_option( 'theme_switch_menu_locations', $nav_menu_locations );
            if ( func_num_args() > 1 ) $stylesheet = func_get_arg( 1 );
            $old_theme = $this->_tp_get_theme();
            $new_theme = $this->_tp_get_theme( $stylesheet );
            $template  = $new_theme->this->get_template();
            if ( $this->_tp_is_recovery_mode() ) {
                $paused_themes = $this->_tp_paused_themes();
                if($paused_themes instanceof TP_Paused_Extensions_Storage) {
                    $paused_themes->delete( $old_theme->this->get_stylesheet() );
                    $paused_themes->delete( $old_theme->this->get_template() );
                }
            }
            $this->_update_option( 'template', $template );
            $this->_update_option( 'stylesheet', $stylesheet );
            if ( count( $this->_tp_theme_directories ) > 1 ) {
                $this->_update_option( 'template_root', $this->_get_raw_theme_root( $template, true ) );
                $this->_update_option( 'stylesheet_root', $this->_get_raw_theme_root( $stylesheet, true ) );
            } else {
                $this->_delete_option( 'template_root' );
                $this->_delete_option( 'stylesheet_root' );
            }
            $new_name = $new_theme->this->get( 'Name' );
            $this->_update_option( 'current_theme', $new_name );
            if ( $this->_is_admin() && false === $this->_get_option( 'theme_mods_' . $stylesheet ) ) {
                $default_theme_mods = (array) $this->_get_option( 'mods_' . $new_name );
                if ( ! empty( $nav_menu_locations ) && empty( $default_theme_mods['nav_menu_locations'] ) )
                    $default_theme_mods['nav_menu_locations'] = $nav_menu_locations;
                $this->_add_option( "theme_mods_$stylesheet", $default_theme_mods );
            }else if ( 'tp_async_customize_save' === $this->_current_action()) $this->_remove_theme_mod( 'sidebars_widgets' );
            $this->_update_option( 'theme_switched', $old_theme->this->__get_stylesheet() );
            $this->_do_action( 'switch_theme', $new_name, $new_theme, $old_theme );
        }//739
        /**
         * todo needs lookup/edits
         * @description Checks that the current theme has 'index.php' and 'style.css' files.
         * @return bool
         */
        protected function _validate_current_theme():bool{
            if ( $this->_tp_installing() || ! $this->_apply_filters( 'validate_current_theme', true ) ){return true;}
            if ( ! file_exists( $this->_get_template_directory() . '/index.php' ) ){return true;}
            if ( ! file_exists( $this->_get_template_directory() . '/style.css' ) ){}
            else return true;// Valid.
            $_default = $this->_tp_get_theme( TP_DEFAULT_THEME );
            $default = null;
            if($_default instanceof TP_Theme ){
                $default = $_default;
            }
            if ( $default->exists() ) {
                $this->_switch_theme( TP_DEFAULT_THEME );
                return false;
            }
            $_default = TP_Theme::get_core_default_theme();
            $default = null;
            if($_default instanceof TP_Theme ){
                $default = $_default;
            }
            if ( false === $default || $this->_get_stylesheet() === $default->get_stylesheet()) return true;
            $this->_switch_theme( $default->get_stylesheet() );
            return false;
        }//848
    }
}else die;