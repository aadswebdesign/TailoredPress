<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-3-2022
 * Time: 17:40
 */
namespace TP_Core\Traits\Theme;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Traits\Constructs\_construct_editor;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _theme_06 {
        use _init_queries,_init_error,_construct_editor;
        /**
         * @description Displays background image path.
         */
        public function background_image():void{
            echo $this->_get_background_image();
        }//1748
        /**
         * @description Retrieves value for custom background color.
         * @return mixed
         */
        protected function _get_background_color(){
            return $this->_get_theme_mod( 'background_color', $this->_get_theme_support( 'custom-background', 'default-color' ) );
        }//1759
        /**
         * @description Displays background color value.
         */
        public function background_color():void{
            echo $this->_get_background_color();
        }//1768
        /**
         * @description Default custom background callback.
         */
        protected function _custom_background_cb():void{
            // $background is the saved custom image, or the default image.
            $background = $this->_set_url_scheme( $this->_get_background_image() );
            $color = $this->_get_background_color();
            if ( $this->_get_theme_support( 'custom-background', 'default-color' ) === $color )
                $color = false;
            $type_attr = $this->_current_theme_supports( 'html5', 'style' ) ? '' : " type='text/css' rel='stylesheet'";
            if ( ! $background && ! $color ) {
                if ( $this->_is_customize_preview() ) printf( '<style%s id="custom-background-css"></style>', $type_attr );
                return;
            }
            $style = $color ? "background-color: #$color;" : '';
            $image = '';
            if ($background) $image = ' background-image: url("' . $this->_esc_url_raw( $background ) . '");';
            // Background Position.
            $position_x = $this->_get_theme_mod( 'background_position_x', $this->_get_theme_support( 'custom-background', 'default-position-x' ) );
            $position_y = $this->_get_theme_mod( 'background_position_y', $this->_get_theme_support( 'custom-background', 'default-position-y' ) );
            if ( ! in_array( $position_x, array('left', 'center', 'right'),true)) $position_x = 'left';
            if ( ! in_array( $position_y, array('top', 'center', 'bottom'),true)) $position_y = 'top';
            $position = " background-position: $position_x $position_y;";
            // Background Size.
            $size = $this->_get_theme_mod( 'background_size', $this->_get_theme_support( 'custom-background', 'default-size' ) );
            if ( ! in_array( $size, array( 'auto', 'contain', 'cover' ), true ) )  $size = 'auto';
            $size = " background-size: $size;";
            $repeat = $this->_get_theme_mod( 'background_repeat', $this->_get_theme_support( 'custom-background', 'default-repeat' ) );
            if ( ! in_array( $repeat, array('repeat-x', 'repeat-y', 'repeat', 'no-repeat'), true )) $repeat = 'repeat';
            $repeat = " background-repeat: $repeat;";
            $attachment = $this->_get_theme_mod( 'background_attachment', $this->_get_theme_support( 'custom-background', 'default-attachment' ) );
            if ( 'fixed' !== $attachment ) $attachment = 'scroll';
            $attachment = " background-attachment: $attachment;";
            $style .= $image . $position . $size . $repeat . $attachment;
            $html = "<style {$type_attr} id='custom_background_css'>";
            ob_start();
                ?>body.custom-background:<?php echo trim( $style ); ?>;<?php
            $html .= ob_get_clean();
            $html .="</style>";
            echo $html;
        }//1777
        /**
         * @description Renders the Custom CSS style element.
         */
        public function tp_custom_css_cb():void{
            $styles = $this->_tp_get_custom_css();
            if ( $styles || $this->_is_customize_preview() ){
                $type_attr = $this->_current_theme_supports( 'html5', 'style' ) ? '' : "type='text/css' rel='stylesheet'";
                $html = "<style {$type_attr} id='tp_custom_css'>";
                $html .= strip_tags($styles);
                $html .= "</style>";
                echo $html;
            }
        }//1858
        /**
         * @description Fetches the `custom_css` post for a given theme.
         * @param string $stylesheet
         * @return mixed
         */
        protected function _tp_get_custom_css_post( $stylesheet = '' ){
            if ( empty($stylesheet)) $stylesheet = $this->_get_stylesheet();
            $custom_css_query_vars = [
                'post_type'              => 'custom_css',
                'post_status'            => $this->_get_post_stati(),
                'name'                   => $this->_sanitize_title( $stylesheet ),
                'posts_per_page'         => 1,
                'no_found_rows'          => true,
                'cache_results'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'lazy_load_term_meta'    => false,
            ];
            $post = null;
            if ( $this->_get_stylesheet() === $stylesheet ) {
                $post_id = $this->_get_theme_mod( 'custom_css_post_id' );
                if ( $post_id > 0 && $this->_get_post( $post_id ) )
                    $post = $this->_get_post( $post_id );
                if ( ! $post && -1 !== $post_id ) {
                    $tp_query = $this->_init_query( $custom_css_query_vars);
                    $post  = $tp_query->post;
                    $this->_set_theme_mod( 'custom_css_post_id', $post ? $post->ID : -1 );
                }
            }else{
                $tp_query = $this->_init_query( $custom_css_query_vars);
                $post  = $tp_query->post;
            }
            return $post;
        }//1881
        /**
         * @description Fetches the saved Custom CSS content for rendering.
         * @param string $stylesheet
         * @return string
         */
        protected function _tp_get_custom_css( $stylesheet = '' ):string{
            $css = '';
            if (empty($stylesheet)){$stylesheet = $this->_get_stylesheet();}
            $post = $this->_tp_get_custom_css_post( $stylesheet );
            if ($post instanceof TP_Post &&  $post ){$css = $post->post_content;}
            $css = $this->_apply_filters( 'tp_get_custom_css', $css, $stylesheet );
            return $css;
        }//1932
        /**
         * @description Updates the `custom_css` post for a given theme.
         * @param $css
         * @param \array[] ...$args
         * @return mixed
         */
        protected function _tp_update_custom_css_post( $css,array ...$args){
            $args = $this->_tp_parse_args($args, ['preprocessed' => '', 'stylesheet' => $this->_get_stylesheet(),]);
            $data = ['css' => $css,'preprocessed' => $args['preprocessed'],];
            $data = $this->_apply_filters( 'update_custom_css_data', $data, array_merge( $args, compact( 'css' ) ) );
            $post_data = ['post_title' => $args['stylesheet'],'post_name' => $this->_sanitize_title( $args['stylesheet'] ),
                'post_type' => 'custom_css','post_status' => 'publish','post_content' => $data['css'],'post_content_filtered' => $data['preprocessed'],];
            $_post = $this->_tp_get_custom_css_post( $args['stylesheet'] );
            $post = null;
            $post_status = null;
            if($_post instanceof TP_Post ){$post = $_post;}
            if ( $post ) {
                $post_data['ID'] = $post->ID;
                $post_status = $this->_tp_update_post( $this->_tp_slash( $post_data ), true );
            }else{
                $post_status = $this->_tp_insert_post( $this->_tp_slash( $post_data ), true );
                if ( ! $this->_init_error( $post_status ) ) {
                    if ( $this->_get_stylesheet() === $args['stylesheet'] ) {
                        $this->_set_theme_mod( 'custom_css_post_id', $post_status );
                    }
                    if ( 0 === count( $this->_tp_get_post_revisions( $post_status ) ) ) {
                        $this->_tp_save_post_revision( $post_status );
                    }
                }
            }
            if ( $this->_init_error( $post_status ) ) {
                return $post_status;
            }
            return $this->_get_post( $post_status );
        }//1975
        /**
         * @description Adds callback for custom TinyMCE editor stylesheets.
         * @param $stylesheet
         */
        protected function _add_editor_style($stylesheet = 'editor-style.css' ):void{
            $this->_add_theme_support( 'editor-style' );
            $editor_styles = (array) $this->tp_editor_styles;
            $stylesheets    = [$stylesheet];
            $rtl_stylesheet = null;
            if ( $this->_is_rtl() ) {
                $rtl_stylesheet = str_replace( '.css', '-rtl.css', $stylesheets[0] );
                $stylesheets[]   = $rtl_stylesheet;
            }
            $this->tp_editor_styles = array_merge( $editor_styles, $stylesheets );
        }//2083
        /**
         * @description Removes all visual editor stylesheets.
         * @return bool
         */
        protected function _remove_editor_styles():bool{
            if(!$this->_current_theme_supports('editor-style')){return false;}
            $this->_remove_theme_support('editor-style');
            if($this->_is_admin()){$this->tp_editor_styles = [];}
            return true;
        }//2108
    }
}else die;