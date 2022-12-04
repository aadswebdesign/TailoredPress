<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 15-10-2022
 * Time: 21:03
 */
namespace TP_Admin\Libs\AdmComponents;
use TP_Admin\Libs\Adm_Screen;

if(ABSPATH){
    class Adm_Header extends ComponentsBase {
        private $__index_title;
        public function __construct($args =null){
            parent::__construct();

            $this->adm_header_menu = $this->get_adm_component_class('Adm_Header_Menu');
            $this->adm_header_args = $args;
            if('options_general.php' === $this->adm_header_args['parent_file']){
                $this->_add_action( 'admin_head', $this->adm_header_args['get_admin_general_head'] );
            }
            if('index.php' === $this->adm_header_args['parent_file']){
                $this->_add_action( 'admin_head', $this->adm_header_args['get_admin_index_head'] );
                $this->_add_thick_box();
            }
            $this->adm_panel_title = $this->adm_header_args['panel_title'];
            $this->__index_title = $this->adm_header_args['index_title'];

        }
        public static function set_header():void{
            echo header( 'Content-Type: ' . (new self)->_get_option( 'html_type' ) . '; charset=' . (new self)->_get_option( 'blog_charset' ) );
        }
        private function __head_logic_setup():string{
            $output_head = "";
            if (empty( $this->tp_current_screen ) ) {
                ob_start();
                $this->_set_current_screen();
                $output_head .= ob_get_clean();
            }//todo has error
            $output_head .= $this->_get_adm_page_title(); //todo
            $this->tp_title = strip_tags( $this->adm_panel_title );
            $admin_title = null;
            if ( $this->_is_network_admin() ) { $admin_title = sprintf( $this->__( 'Network Admin: %s' ), $this->_get_network()->site_name );}
            elseif ( $this->_is_user_admin() ) { $admin_title = sprintf( $this->__( 'User Dashboard: %s' ), $this->_get_network()->site_name );}
            else { $admin_title = $this->_get_bloginfo( 'name' );}
            if ( $admin_title === $this->tp_title ) { $admin_title = sprintf( $this->__("%s &#8212; {$this->__index_title}"), $this->tp_title );}//todo swap the titles!
            else {
                $screen_title = $this->tp_title;
                if ( 'post' === $this->tp_current_screen->base && 'add' !== $this->tp_current_screen->action ) {
                    $post_title = $this->_get_the_title();
                    if ( ! empty( $post_title ) ) {
                        $post_type_obj = $this->_get_post_type_object( $this->tp_typenow );
                        $screen_title  = sprintf( $this->__( '%1$s &#8220;%2$s&#8221;' ), $post_type_obj->labels->edit_item, $post_title);
                    }
                }
                $admin_title = sprintf( $this->__("%1\$s &lsaquo; %2\$s &#8212;  {$this->__index_title}"), $screen_title, $admin_title );
            }
            if ( $this->_tp_is_recovery_mode() ) { $admin_title = sprintf( $this->__( 'Recovery Mode &#8212; %s' ), $admin_title );}
            $admin_title = $this->_apply_filters( 'admin_title', $admin_title, $this->tp_title );
            $this->adm_title = $admin_title;
            $output_head .=  $this->_tp_get_user_settings();
            return $output_head;
        }
        private function __to_string():string{
            $output  = $this->__head_logic_setup();
            $output .= "";
            $output .= $this->_tp_get_admin_html_begin();
            $output .= "<title>{$this->_esc_html($this->adm_title)} (Development)</title>";
            ob_start();
            $this->tp_enqueue_style( 'colors' );
            $this->tp_enqueue_script( 'utils' );
            $this->tp_enqueue_script( 'svg-painter' );
            $admin_body_class = preg_replace( '/[^a-z0-9_-]+/i', '-', $this->tp_hook_suffix );
            $this->tp_current_screen->id = $this->tp_current_screen->id ?: '10';
            $this->tp_current_screen->post_type = $this->tp_current_screen->post_type ?: 'example_type';
            $thousands_sep = $this->tp_locale->number_format['thousands_sep'] ?? '/';
            ?>
            <style>
                *{ padding: 0; margin: 0;list-style-type: none;}
                div,p{padding-left: 0.5em; }
            </style>
            <script id='add_load_event'>
                (function(){
                    const tp_async_url = 'todo';
                    const tp_pagenow = '<?php echo $this->_esc_js( $this->tp_current_screen->id ); ?>';
                    const tp_typenow = '<?php echo $this->_esc_js( $this->tp_current_screen->post_type ); ?>';
                    const tp_admin_page = '<?php echo $this->_esc_js( $admin_body_class ); ?>';
                    const tp_thousands_separator = '<?php echo $this->_esc_js( $thousands_sep ); ?>';
                    const tp_decimal_point = '<?php echo $this->_esc_js( $this->tp_locale->number_format['decimal_point'] ); ?>';
                    const tp_is_rtl = '<?php echo (int) $this->_is_rtl(); ?>';
                    console.log('TODO:','add_load_event, just some variables and no more at present!');
                    console.log('tp_async_url: ',tp_async_url);
                    console.log('tp_pagenow: ', tp_pagenow,);
                    console.log('tp_typenow: ', tp_typenow);
                    console.log('tp_admin_page: ',tp_admin_page);
                    console.log('tp_thousands_separator: ' ,tp_thousands_separator);
                    console.log('tp_decimal_point: ', tp_decimal_point);
                    console.log('tp_is_rtl: ', tp_is_rtl);
                    //console.log(':',);
                })();
            </script>
            <?php
            $output .= ob_get_clean();
            $output .= $this->_get_action("admin_enqueue_scripts-{$this->tp_hook_suffix}");
            $output .= $this->_get_action("admin_print_styles-{$this->tp_hook_suffix}");
            $output .= $this->_get_action('admin_print_styles');
            $output .= $this->_get_action("admin_print_scripts-{$this->tp_hook_suffix}");
            $output .= $this->_get_action('admin_print_scripts');
            $output .= $this->_get_action("admin_head-{$this->tp_hook_suffix}");
            $output .= $this->_get_action('admin_head');
            if ( 'fold' === $this->_get_user_setting( 'mfold' ) ) { $admin_body_class .= ' folded';}
            if ( ! $this->_get_user_setting( 'unfold' ) ) {  $admin_body_class .= ' auto-fold';}
            if ( $this->_is_admin_bar_showing() ) { $admin_body_class .= ' admin-bar'; }
            if ( $this->_is_rtl() ) { $admin_body_class .= ' rtl';}
            if ( $this->tp_current_screen->post_type ) { $admin_body_class .= ' post-type-' . $this->tp_current_screen->post_type; }
            if ( $this->tp_current_screen->taxonomy ) { $admin_body_class .= ' taxonomy-' . $this->tp_current_screen->taxonomy; }
            $admin_body_class .= ' branch-' . str_replace( array( '.', ',' ), '-', (float) $this->_get_bloginfo( 'version' ) );
            $admin_body_class .= ' version-' . str_replace( '.', '-', preg_replace( '/^([.0-9]+).*/', '$1', $this->_get_bloginfo( 'version' ) ) );
            $admin_body_class .= ' admin-color-' . $this->_sanitize_html_class( $this->_get_user_option( 'admin_color' ), 'fresh' );
            $admin_body_class .= ' locale-' . $this->_sanitize_html_class( strtolower( str_replace( '_', '-', $this->_get_user_locale() ) ) );
            if ( $this->_tp_is_mobile() ) { $admin_body_class .= ' mobile';}
            if ( $this->_is_multisite() ) { $admin_body_class .= ' multisite';}
            if ( $this->_is_network_admin() ) { $admin_body_class .= ' network-admin';}
            $admin_body_class .= ' no-customize-support no-svg';
            if ($this->tp_current_screen instanceof Adm_Screen && $this->tp_current_screen->is_block_editor() ) {
                $admin_body_class .= ' block-editor-page tp-embed-responsive';}
            $error_get_last = error_get_last();
            if ( $error_get_last && TP_DEBUG && TP_DEBUG_DISPLAY && ini_get( 'display_errors' ) && (E_NOTICE !== $error_get_last['type'] || 'TP_Config.php' !== $this->_tp_basename($error_get_last['file']))){
                $admin_body_class .= ' php-error';}
            unset( $error_get_last );
            $output .= "</head>";
            $admin_body_classes = $this->_apply_filters( 'admin_body_class', '' );
            $admin_body_classes = ltrim( $admin_body_classes . ' ' . $admin_body_class );
            $output .= "<body class='tp-admin tp-core-ui no-js $admin_body_classes '>";
            ob_start();
            ?>
            <script id='body_class_name'>document.body.className = document.body.className.replace('no-js','yes-js');</script>
            <?php
            $output .= ob_get_clean();//248
            if ($this->_current_user_can( 'customize' ) ) {
                $output .= $this->_tp_get_customize_support_script();
            }
            $output .= "<div id='tp_wrap' class='tp-container'>";//ends in Admin_footer
            if('options_general.php' === $this->adm_header_args['parent_file']) {
                $output .= $this->_admin_string();
            }
            if('index.php' === $this->adm_header_args['parent_file']){
                $output .= "<section class='dashboard-component top'>";
                $output .= $this->_tp_get_dashboard_setup();
                $output .= "</section><!-- dashboard-component top -->";
                ob_start();
                $this->tp_enqueue_script( 'dashboard' );
                if ( $this->_current_user_can( 'implement_modules' ) ) {
                    $this->tp_enqueue_script( 'implement-modules' );
                    $this->tp_enqueue_script( 'updates' );
                }
                if ( $this->_current_user_can( 'upload_files' ) ) {
                    $this->tp_enqueue_script( 'media-upload' );
                }
                if ( $this->_tp_is_mobile() ) {
                    $this->tp_enqueue_script( 'tp-mobile-scripts' );// no jQuery here!
                }
                $output .= ob_get_clean();
            }
            $output .= "<section class='tp-header-component'>";
            $output .=  $this->adm_header_menu;
            $output .= "</section><!-- tp-header-component --><section class='tp-main-component'>";
            $output .= $this->_get_action( 'in_admin_header' );
            $output .= "<main class='tp-body'>";
            ob_start();
            unset( $blog_name, $total_update_count, $update_title ); //todo
            $this->tp_current_screen->set_parent_page($this->adm_header_args['parent_file'] );//works :)
            $output .= ob_get_clean();
            $output .= "<div id='tp_content' class='tp-body-content'>";
            //$output .= $this->tp_current_screen->get_render_screen_meta();
            if ( $this->_is_network_admin() ) { $output .= $this->_get_action( 'network_admin_notices' );}
            elseif ( $this->_is_user_admin() ) { $output .= $this->_get_action( 'user_admin_notices' ); }
            else { $output .= $this->_get_action( 'admin_notices' );}
            $output .= $this->_get_action( 'all_admin_notices' );
            if('options_general.php' === $this->adm_header_args['parent_file']) {
                $output .= "<section class='settings-error-module'>";
                ob_start();
                $this->_tp_reset_vars(['action']);
                $output .= ob_get_clean();
                $output .= $this->_settings_errors();
                $output .= "</section>";
            }
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}