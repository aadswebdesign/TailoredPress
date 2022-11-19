<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-3-2022
 * Time: 17:40
 */
namespace TP_Core\Traits\Theme;
use TP_Core\Traits\Constructs\_construct_theme;
use TP_Core\Traits\Inits\_init_locale;
use TP_Core\Traits\Inits\_init_theme;
use TP_Core\Libs\TP_Theme;
if(ABSPATH){
    trait _theme_01 {
        use _construct_theme;
        use _init_theme;
        use _init_locale;
        /**
         * @description Returns an array of TP_Theme objects based on the arguments.
         * @param array $args
         * @return array
         */
        protected function _tp_get_themes( $args = [] ):array{
            $defaults = ['errors'  => false,'allowed' => null,'blog_id' => 0,];
            $args     = $this->_tp_parse_args( $args, $defaults );
            $theme_directories = $this->_search_theme_directories();
            if ( is_array( $this->tp_theme_directories ) && count( $this->tp_theme_directories ) > 1 ) {
                $current_theme = $this->_get_stylesheet();
                if ( isset( $theme_directories[ $current_theme ] ) ) {
                    $root_of_current_theme = $this->_get_raw_theme_root( $current_theme );
                    if ( !in_array( $root_of_current_theme, $this->tp_theme_directories,true))
                        $root_of_current_theme = TP_CONTENT_DIR . $root_of_current_theme;
                    $theme_directories[ $current_theme ]['theme_root'] = $root_of_current_theme;
                }
            }
            if(empty($theme_directories)) return [];
            if (null !== $args['allowed']&&$this->_is_multisite() ) {
                $allowed = $args['allowed'];
                if ( 'network' === $allowed ) $theme_directories = array_intersect_key( $theme_directories, TP_Theme::get_allowed_on_network() );
                elseif ( 'site' === $allowed ) $theme_directories = array_intersect_key( $theme_directories, TP_Theme::get_allowed_on_site( $args['blog_id'] ) );
                elseif ( $allowed ) $theme_directories = array_intersect_key( $theme_directories, TP_Theme::get_allowed( $args['blog_id'] ) );
                else $theme_directories = array_diff_key( $theme_directories, TP_Theme::get_allowed( $args['blog_id'] ) );
            }
            $themes = [];
            $_themes = [];
            foreach ( $theme_directories as $theme => $theme_root ) {
                $_theme_root = $_themes[ $theme_root['theme_root'].'/'.$theme ];
                if ( isset($_theme_root))
                    $themes[ $theme ] = $_themes[ $theme_root['theme_root'] . '/' . $theme ];
                else {
                    $themes[ $theme ] = new TP_Theme( $theme, $theme_root['theme_root'] );
                    $_themes[ $theme_root['theme_root'] . '/' . $theme ] = $themes[ $theme ];
                }
            }
            if ( null !== $args['errors'] ) {
                foreach ( $themes as $theme => $tp_theme ) {
                    if ( $tp_theme->errors() !== $args['errors'] ) unset( $themes[ $theme ] );
                }
            }
            return $themes;
        }//35
        /**
         * @description Gets a TP_Theme object for a theme.
         * @param string $stylesheet
         * @param string $theme_root
         * @return TP_Theme
         */
        protected function _tp_get_theme( $stylesheet = '', $theme_root = '' ):TP_Theme{
            if ( empty( $stylesheet ) ) $stylesheet = $this->_get_stylesheet();
            if ( empty( $theme_root ) ) {
                $theme_root = (string) $this->_get_raw_theme_root( $stylesheet );
                if ( false === $theme_root ) $theme_root = TP_CONTENT_DIR . '/Themes';
                elseif ( ! in_array( $theme_root, (array) $this->tp_theme_directories, true ) )
                    $theme_root = TP_CONTENT_DIR . $theme_root;
            }
            return new TP_Theme( $stylesheet, $theme_root );
        }//115
        /**
         * @description Clears the cache held by get_theme_roots() and TP_Theme.
         * @param bool $clear_update_cache
         */
        protected function _tp_clean_themes_cache( $clear_update_cache = true ):void{
            if ( $clear_update_cache ) $this->_delete_site_transient( 'update_themes' );
            $this->_search_theme_directories( true );
            foreach ($this->_tp_get_themes( array( 'errors' => null ) ) as $theme ) {
                if($theme  instanceof TP_Theme ){
                    $theme->cache_delete();
                }
            }
        }//140
        /**
         * @description Retrieves name of the current stylesheet.
         * @return mixed
         */
        protected function _get_stylesheet(){
            return $this->_apply_filters( 'stylesheet', $this->_get_option( 'stylesheet' ) );
        }//173
        /**
         * @description Retrieves stylesheet directory path for current theme.
         * @return mixed
         */
        protected function _get_stylesheet_directory(){
            $stylesheet     = $this->_get_stylesheet();
            $theme_root     = $this->_get_theme_root( $stylesheet );
            $stylesheet_dir = "$theme_root/$stylesheet";
            return $this->_apply_filters( 'stylesheet_directory', $stylesheet_dir, $stylesheet, $theme_root );
        }//191
        /**
         * @description Retrieves stylesheet directory URI for current theme.
         * @return mixed
         */
        protected function _get_stylesheet_directory_uri(){
            $stylesheet         = str_replace( '%2F', '/', rawurlencode( $this->_get_stylesheet() ) );
            $theme_root_uri     = $this->_get_theme_root_uri( $stylesheet );
            $stylesheet_dir_uri = "$theme_root_uri/$stylesheet";
            return $this->_apply_filters( 'stylesheet_directory_uri', $stylesheet_dir_uri, $stylesheet, $theme_root_uri );
        }//215
        /**
         * @description Retrieves stylesheet URI for current theme.
         * @return mixed
         */
        protected function _get_stylesheet_uri(){
            $stylesheet_dir_uri = $this->_get_stylesheet_directory_uri();
            $stylesheet_uri     = $stylesheet_dir_uri . '/style.css';
            return $this->_apply_filters( 'stylesheet_uri', $stylesheet_uri, $stylesheet_dir_uri );
        }//242
        /**
         * @description Retrieves the localized stylesheet URI.
         * @return mixed
         */
        protected function _get_locale_stylesheet_uri(){
            $stylesheet_dir_uri = $this->_get_stylesheet_directory_uri();
            $dir                = $this->_get_stylesheet_directory();
            $locale             = $this->_get_locale();
            $tp_locale = $this->_init_locale();
            if(file_exists( "$dir/$locale.css")) $stylesheet_uri = "$stylesheet_dir_uri/$locale.css";
            elseif(!empty($tp_locale->text_direction)&&file_exists("$dir/{$tp_locale->text_direction}.css"))
                $stylesheet_uri = "$stylesheet_dir_uri/{tp_locale->text_direction}.css";
            else $stylesheet_uri = '';
            return $this->_apply_filters( 'locale_stylesheet_uri', $stylesheet_uri, $stylesheet_dir_uri );
        }//277 //todo adjusting the routing's
        /**
         * @description Retrieves name of the current theme.
         * @return mixed
         */
        protected function _get_template(){
            return $this->_apply_filters( 'template', $this->_get_option( 'template' ) );
        }//307
        /**
         * @description Retrieves template directory path for current theme.
         * @return mixed
         */
        protected function _get_template_directory(){
            $template     = $this->_get_template();
            $theme_root   = $this->_get_theme_root( $template );
            $template_dir = "$theme_root/$template";
            return $this->_apply_filters( 'template_directory', $template_dir, $template, $theme_root );
        }//325
    }
}else die;