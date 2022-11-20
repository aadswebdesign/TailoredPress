<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-7-2022
 * Time: 20:20
 */
namespace TP_Core\Libs\Customs;
use TP_Admin\Traits\File\_file_03;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Background_Image_Control;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Background_Image_Setting;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Background_Position_Control;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Color_Control;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Code_Editor_Control;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Cropped_Image_Control;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Custom_CSS_Setting;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Filter_Setting;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Header_Image_Control;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Header_Image_Setting;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Media_Control;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Site_Icon_Control;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Themes_Panel;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Themes_Section;
use TP_Core\Libs\Customs\Customizers\TP_Customize_Selective_Refresh;
use TP_Admin\Traits\AdminMedia\_adm_media_01;
use TP_Admin\Traits\AdminTemplates\_adm_template_05;
use TP_Admin\Traits\File\_file_01;
use TP_Admin\Traits\Image\_image_01;
use TP_Admin\Traits\PostAdmin\_post_admin_03;
use TP_Admin\Traits\Theme\_theme_admin_01;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\Queries\TP_Query;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Theme;
use TP_Core\Traits\AdminBar\_admin_bar_03;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Constructs\_construct_page;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_03;
use TP_Core\Traits\Formats\_formats_06;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\HTTP\_http_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\K_Ses\_k_ses_01;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Methods\_methods_02;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_05;
use TP_Core\Traits\Methods\_methods_07;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_16;
use TP_Core\Traits\Methods\_methods_17;
use TP_Core\Traits\Methods\_methods_20;
use TP_Core\Traits\Methods\_methods_21;
use TP_Core\Traits\Misc\_global_settings;
use TP_Core\Traits\Misc\_update;
use TP_Core\Traits\Misc\tp_link_styles;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Pluggables\_pluggable_02;
use TP_Core\Traits\Pluggables\_pluggable_03;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_02;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\Post\_post_05;
use TP_Core\Traits\Post\_post_06;
use TP_Core\Traits\Post\_post_07;
use TP_Core\Traits\Post\_post_08;
use TP_Core\Traits\Post\_post_10;
use TP_Core\Traits\Post\_post_12;
use TP_Core\Traits\Revisions\_revision_01;
use TP_Core\Traits\Revisions\_revision_02;
use TP_Core\Traits\Templates\_general_template_03;
use TP_Core\Traits\Templates\_general_template_06;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Templates\_link_template_11;
use TP_Core\Traits\Theme\_theme_01;
use TP_Core\Traits\Theme\_theme_02;
use TP_Core\Traits\Theme\_theme_05;
use TP_Core\Traits\User\_user_02;
if(ABSPATH){
    class TP_Customize_Manager extends Customize_Base{
        use _pluggable_01,_pluggable_02,_pluggable_03,_http_03,_admin_bar_03,_revision_01,_revision_02,_formats_02,_formats_03,_formats_06,_formats_07,_formats_10,_adm_media_01,_image_01;
        use _theme_01,_theme_02,_theme_05,_post_01,_post_02,_post_03,_post_04,_post_05,_post_06,_post_07,_post_08,_post_10,_post_12,_load_03,_cache_01,_user_02,_theme_admin_01;
        use _methods_01,_methods_02,_methods_03,_methods_04,_methods_05,_methods_07,_methods_10,_methods_16,_methods_17,_methods_20,_methods_21,_k_ses_01,_file_01,_file_03;
        use tp_link_styles,_link_template_09,_link_template_11,_I10n_04,_post_admin_03,_update,_global_settings,_adm_template_05,_general_template_03,_general_template_06;
        use _construct_page;
        private $__post_values;
        private $__changeset_uuid;
        private $__changeset_post_id;
        private $__changeset_data;
        protected $_theme;
        protected $_original_stylesheet;
        protected $_previewing = false;
        protected $_settings = [];
        protected $_containers = [];
        protected $_panels = [];
        protected $_components = ['nav_menus'];//'widgets',
        protected $_sections = [];
        protected $_controls = [];
        protected $_registered_panel_types = [];
        protected $_registered_section_types = [];
        protected $_registered_control_types = [];
        protected $_preview_url;
        protected $_return_url;
        protected $_autofocus = [];
        protected $_messenger_channel;
        protected $_autosaved = false;
        protected $_branching = true;
        protected $_settings_previewed = true;
        protected $_saved_starter_content_changeset = false;
        protected $_pending_starter_content_settings_ids = [];
        protected $_store_changeset_revision;
        public $selective_refresh;
        public function __construct(...$args){}//261
        public function doing_async( $action = null ):bool{
            if ( ! $this->_tp_doing_async() ) return false;
            if ( ! $action ) return true;
            else return isset( $_REQUEST['action'] ) && $this->_tp_unslash( $_REQUEST['action'] ) === $action;
        }//418
        protected function _tp_die( $async_message, $message = null ):void{
            if ($this->_tp_doing_async())$this->_tp_die( $async_message );
            if ( ! $message ) $message = $this->__( 'Something went wrong.' );
            if ( $this->_messenger_channel ) {
                ob_start();
                $this->tp_enqueue_assets();
                $this->tp_print_scripts(['customize-base']);
                $settings = ['messengerArgs' => ['channel' => $this->_messenger_channel,'url'=> $this->_tp_customize_url(),],
                    'error'=> $async_message,];
                //todo js  out of the php functions and handled elsewhere
                ?>
                <!--suppress ALL, UnterminatedStatementJS -->

                <script>
                    //( function( api, settings ) {
                        //let preview = new api.Messenger( settings.messengerArgs );
                        //preview.send( 'iframe-loading-error', settings.error );
                    //} )( tp.customize, <?php echo $this->_tp_json_encode( $settings ); ?> );
                </script>
                <?php
                $message .= ob_get_clean();
            }
            $this->_tp_die( $message );
        }//443
        public function setup_theme():void{ //todo void now but maybe return
            if ( 'customize.php' === $this->tp_pagenow && ! $this->_current_user_can( 'customize' ) ) {
                if ( ! $this->_is_user_logged_in()){ $this->_auth_redirect();}
                else {
                    $this->_tp_die(
                        "<h1>{$this->__( 'You need a higher level of permission.' )}</h1>".
                        "<p>{$this->__( 'Sorry, you are not allowed to customize this site.' )}</p>",
                        403
                    );
                }
                return;
            }
            if ( isset( $this->_changeset_uuid ) && false !== $this->_changeset_uuid && ! $this->_tp_is_uuid( $this->_changeset_uuid ) ) {
                $this->_tp_die( -1, $this->__( 'Invalid changeset UUID' ) );}
            $has_post_data_nonce = ( $this->_check_async_referer( 'preview-customize_' . $this->get_stylesheet(), 'nonce', false )
                || $this->_check_async_referer( 'save-customize_' . $this->get_stylesheet(), 'nonce', false )
                || $this->_check_async_referer( 'preview-customize_' . $this->get_stylesheet(), 'customize_preview_nonce', false )
            );
            if (! $has_post_data_nonce || ! $this->_current_user_can( 'customize' )) {
                unset( $_POST['customized'], $_REQUEST['customized'] );}
            if ( ! $this->_current_user_can( 'customize' ) && ! $this->changeset_post_id() ) {
                $this->_tp_die( $this->_messenger_channel ? 0 : -1, $this->__( 'Non-existent changeset UUID.' ) );
            }
            if ( ! headers_sent() ) {$this->_send_origin_headers();}
            if ( $this->_messenger_channel ) {$this->_show_admin_bar( false );}
            if ( $this->is_theme_active() ) {$this->_add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );}
            else {
                if ( ! $this->_current_user_can( 'switch_themes' ) ) {
                    $this->_tp_die( -1, $this->__( 'Sorry, you are not allowed to edit theme options on this site.' ) );
                }
                if ( $this->theme()->errors() ) { $this->_tp_die( -1, $this->theme()->errors()->get_error_message());}
                if ( ! $this->theme()->is_allowed() ) { $this->_tp_die( -1, $this->__( 'The requested theme does not exist.' ) );}
            }
            $this->_add_action( 'after_setup_theme',[$this, 'establish_loaded_changeset'], 5 );
            if ('customize.php' === $this->tp_pagenow && $this->_get_option( 'fresh_site' )) {
                $this->_add_action( 'after_setup_theme', array( $this, 'import_theme_starter_content' ), 100 );
            }
            $this->start_previewing_theme();
        }//505
        public function establish_loaded_changeset():void{
            if ( empty( $this->__changeset_uuid ) ) {
                $changeset_uuid = null;
                if ( ! $this->branching() && $this->is_theme_active() ) {
                    $unpublished_changeset_posts = $this->_get_changeset_posts(
                        ['post_status' => array_diff( $this->_get_post_stati(),['auto-draft','publish','trash','inherit','private']),
                            'exclude_restore_dismissed' => false,'author' => 'any','posts_per_page' => 1,'order' => 'DESC','orderby' => 'date',]
                    );
                    $unpublished_changeset_post  = array_shift( $unpublished_changeset_posts );
                    if ( ! empty( $unpublished_changeset_post ) && $this->_tp_is_uuid( $unpublished_changeset_post->post_name ) ) {
                        $changeset_uuid = $unpublished_changeset_post->post_name;}
                }
                if ( empty( $changeset_uuid ) ) { $changeset_uuid = $this->_tp_generate_uuid4();}
                $this->__changeset_uuid = $changeset_uuid;
            }
            if ('customize.php' === $this->tp_pagenow && $this->_is_admin() ) {
                $this->set_changeset_lock( $this->changeset_post_id() );}
        }//612
        public function after_setup_theme():void{
            $doing_async_or_is_customized = ( $this->doing_async() || isset( $_POST['customized'] ) );
            if ( ! $doing_async_or_is_customized && ! $this->_validate_current_theme() ) {
                $this->_tp_redirect( 'themes.php?broken=true' );
                exit;
            }
        }//653
        public function start_previewing_theme():void{
            if($this->is_preview()){ return;}
            $this->_previewing = true;
            if ( ! $this->is_theme_active() ) {
                $this->_add_filter( 'template', [$this, 'get_template'] );
                $this->_add_filter( 'stylesheet',[$this, 'get_stylesheet']);
                $this->_add_filter( 'pre_option_current_theme',[$this,'current_theme']);
                $this->_add_filter( 'pre_option_stylesheet',[$this,'get_stylesheet']);
                $this->_add_filter( 'pre_option_template',[$this,'get_template']);
                $this->_add_filter( 'pre_option_stylesheet_root',[$this,'get_stylesheet_root']);
                $this->_add_filter( 'pre_option_template_root',[$this,'get_template_root']);
            }
            $this->_do_action( 'start_previewing_theme', $this );
        }//667
        public function stop_previewing_theme():void{
            if (!$this->is_preview()){return;}
            $this->_previewing = false;
            if ( ! $this->is_theme_active() ) {
                $this->_remove_filter( 'template', array( $this, 'get_template' ) );
                $this->_remove_filter( 'stylesheet', array( $this, 'get_stylesheet' ) );
                $this->_remove_filter( 'pre_option_current_theme', array( $this, 'current_theme' ) );
                $this->_remove_filter( 'pre_option_stylesheet', array( $this, 'get_stylesheet' ) );
                $this->_remove_filter( 'pre_option_template', array( $this, 'get_template' ) );
                $this->_remove_filter( 'pre_option_stylesheet_root', array( $this, 'get_stylesheet_root' ) );
                $this->_remove_filter( 'pre_option_template_root', array( $this, 'get_template_root' ) );
            }
            $this->_do_action( 'stop_previewing_theme', $this );
        }//706
        public function settings_previewed():bool{
            return $this->_settings_previewed;
        }//746
        public function autosaved():bool{
            return $this->_autosaved;
        }//759
        public function branching(){
            $this->_branching = $this->_apply_filters( 'customize_changeset_branching', $this->_branching, $this );
            return $this->_branching;
        }//772
        public function changeset_uuid():void{}//813
        public function theme():TP_Theme{
            if (!$this->_theme){$this->_theme = $this->_tp_get_theme();}
            return $this->_theme;
        }//827
        public function settings():array{
            return $this->_settings;
        }//841
        public function controls():array{
            return $this->_controls;
        }//852
        public function containers():array{
            return $this->_containers;
        }//863
        public function sections():array{
            return $this->_sections;
        }//874
        public function panels():array{
            return $this->_panels;
        }//885
        public function is_theme_active():bool {
            return $this->get_stylesheet() === $this->_original_stylesheet;
        }//896
        public function tp_loaded():void{
            $this->register_panel_type( 'TP_Customize_Panel' );
            $this->register_panel_type( 'TP_Customize_Themes_Panel' );
            $this->register_section_type( 'TP_Customize_Section' );
            $this->register_section_type( 'TP_Customize_Sidebar_Section' );
            $this->register_section_type( 'TP_Customize_Themes_Section' );
            $this->register_control_type( 'TP_Customize_Color_Control' );
            $this->register_control_type( 'TP_Customize_Media_Control' );
            $this->register_control_type( 'TP_Customize_Upload_Control' );
            $this->register_control_type( 'TP_Customize_Image_Control' );
            $this->register_control_type( 'TP_Customize_Background_Image_Control' );
            $this->register_control_type( 'TP_Customize_Background_Position_Control' );
            $this->register_control_type( 'TP_Customize_Cropped_Image_Control' );
            $this->register_control_type( 'TP_Customize_Site_Icon_Control' );
            $this->register_control_type( 'TP_Customize_Theme_Control' );
            $this->register_control_type( 'TP_Customize_Code_Editor_Control' );
            $this->register_control_type( 'TP_Customize_Date_Time_Control' );
            $this->_do_action( 'customize_register', $this );
            if ( $this->settings_previewed() ) {
                foreach ( $this->_settings as $setting ){ $setting->preview();}
            }
            if ( $this->is_preview() && ! $this->_is_admin()){ $this->customize_preview_init();}
        }//905
        public function find_changeset_post_id( $uuid ){
            $cache_group       = 'customize_changeset_post';
            $changeset_post_id = $this->_tp_cache_get( $uuid, $cache_group );
            if ( $changeset_post_id && 'customize_changeset' === $this->_get_post_type( $changeset_post_id ) ) {
                return $changeset_post_id;}
            $changeset_post_query = new TP_Query(['post_type' => 'customize_changeset','post_status' => $this->_get_post_stati(),
                    'name' => $uuid,'posts_per_page' => 1,'no_found_rows' => true,'cache_results' => true,
                    'update_post_meta_cache' => false,'update_post_term_cache' => false,'lazy_load_term_meta' => false,]);
            if ( ! empty( $changeset_post_query->posts ) ) {
                $changeset_post_id = $changeset_post_query->posts[0]->ID;
                $this->_tp_cache_set( $uuid, $changeset_post_id, $cache_group );
                return $changeset_post_id;
            }
            return null;
        }//976
        protected function _get_changeset_posts(array $args){
            $default_args = ['exclude_restore_dismissed' => true,'posts_per_page' => -1,'post_type' => 'customize_changeset',
                'post_status' => 'auto-draft','order' => 'DESC','orderby' => 'date','no_found_rows' => true,'cache_results' => true,
                'update_post_meta_cache' => false,'update_post_term_cache' => false,'lazy_load_term_meta' => false,];
            if ( $this->_get_current_user_id() ) { $default_args['author'] = $this->_get_current_user_id();}
            $args = array_merge( $default_args, $args );
            if ( ! empty( $args['exclude_restore_dismissed'] ) ) {
                unset( $args['exclude_restore_dismissed'] );
                $args['meta_query'] = [['key' => '_customize_restore_dismissed','compare' => 'NOT EXISTS',],];
            }
            return $this->_get_posts( $args );
        }//1021
        protected function _dismiss_user_auto_draft_changesets():int{
            $changeset_autodraft_posts = $this->_get_changeset_posts(
                ['post_status' => 'auto-draft','exclude_restore_dismissed' => true,'posts_per_page' => -1,]
            );
            $dismissed = 0;
            foreach ( $changeset_autodraft_posts as $autosave_autodraft_post ) {
                if ( $autosave_autodraft_post->ID === $this->changeset_post_id() ) { continue;}
                if ( $this->_update_post_meta( $autosave_autodraft_post->ID, '_customize_restore_dismissed', true ) ) {
                    $dismissed++;}
            }
            return $dismissed;
        }//1059
        public function changeset_post_id(){
            if ( ! isset( $this->_changeset_post_id ) ) {
                $post_id = $this->find_changeset_post_id( $this->changeset_uuid() );
                if ( ! $post_id ) {$post_id = false;}
                $this->_changeset_post_id = $post_id;
            }
            if ( false === $this->_changeset_post_id ) { return null;}
            return $this->_changeset_post_id;
        }//1086
        protected function _get_changeset_post_data( $post_id ){
            if ( ! $post_id ) {return new TP_Error( 'empty_post_id' );}
            $changeset_post = $this->_get_post( $post_id );
            if ( ! $changeset_post ) { return new TP_Error( 'missing_post' );}
            if ( 'revision' === $changeset_post->post_type ) {
                if ( 'customize_changeset' !== $this->_get_post_type( $changeset_post->post_parent ) ) {
                    return new TP_Error( 'wrong_post_type' );}
            } elseif ( 'customize_changeset' !== $changeset_post->post_type ) { return new TP_Error( 'wrong_post_type' );}
            $changeset_data = json_decode( $changeset_post->post_content, true );
            $last_error     = json_last_error();
            if ( $last_error ) { return new TP_Error( 'json_parse_error', '', $last_error );}
            if ( ! is_array( $changeset_data ) ) { return new TP_Error( 'expected_array' );}
            return $changeset_data;
        }//1108
        public function changeset_data(){
            if ( isset( $this->_changeset_data )){ return $this->_changeset_data;}
            $changeset_post_id = $this->changeset_post_id();
            if ( ! $changeset_post_id ) { $this->_changeset_data = [];}
            else {
                if ( $this->autosaved() && $this->_is_user_logged_in() ) {
                    $autosave_post = $this->_tp_get_post_autosave( $changeset_post_id, $this->_get_current_user_id() );
                    if ( $autosave_post instanceof TP_Post) {
                        $data = $this->_get_changeset_post_data( $autosave_post->ID );
                        if ( ! $this->_init_error( $data ) ) { $this->_changeset_data = $data;}
                    }
                }
                if ( ! isset( $this->_changeset_data ) ) {
                    $data = $this->_get_changeset_post_data( $changeset_post_id );
                    if ( ! $this->_init_error( $data ) ) { $this->_changeset_data = $data;}
                    else {$this->_changeset_data = [];}
                }
            }
            return $this->_changeset_data;
        }//1142
        public function import_theme_starter_content(array $starter_content):void{
            if ( empty( $starter_content ) ) { $starter_content = $this->_get_theme_starter_content();}
            $changeset_data = [];
            if ( $this->changeset_post_id() ) {
                if ('auto-draft' !== $this->_get_post_status( $this->changeset_post_id())){return;}
                $changeset_data = $this->_get_changeset_post_data( $this->changeset_post_id() );
            }
            $sidebars_widgets = isset( $starter_content['widgets'] ) && ! empty( $this->widgets ) ? $starter_content['widgets'] : [];
            $attachments      = isset( $starter_content['attachments'] ) && ! empty( $this->nav_menus ) ? $starter_content['attachments'] : [];
            $posts            = isset( $starter_content['posts'] ) && ! empty( $this->nav_menus ) ? $starter_content['posts'] : [];
            $options          = $starter_content['options'] ?? [];
            $nav_menus        = isset( $starter_content['nav_menus'] ) && ! empty( $this->nav_menus ) ? $starter_content['nav_menus'] : [];
            $theme_mods       = $starter_content['theme_mods'] ?? [];
            $max_widget_numbers = array();
            foreach ( $sidebars_widgets as $sidebar_id => $widgets ) {
                $sidebar_widget_ids = array();
                foreach ( $widgets as $widget ) {
                    @list( $id_base, $instance ) = $widget;
                    if ( ! isset( $max_widget_numbers[ $id_base ] ) ) {
                        $settings = $this->_get_option( "widget_{$id_base}", array() );
                        if ( $settings instanceof \ArrayObject || $settings instanceof \ArrayIterator ) {
                            $settings = $settings->getArrayCopy();
                        }
                        unset( $settings['_multiwidget'] );
                        $widget_numbers = array_keys( $settings );
                        if ( count( $widget_numbers ) > 0 ) {
                            $widget_numbers[]               = 1;
                            $max_widget_numbers[ $id_base ] = max( ...$widget_numbers );
                        } else {$max_widget_numbers[ $id_base ] = 1;}
                    }
                    ++$max_widget_numbers[$id_base];
                    $widget_id  = sprintf( '%s-%d', $id_base, $max_widget_numbers[ $id_base ] );
                    $setting_id = sprintf( 'widget_%s[%d]', $id_base, $max_widget_numbers[ $id_base ] );
                    $setting_value = $this->widgets->sanitize_widget_js_instance( $instance );
                    if ( empty( $changeset_data[ $setting_id ] ) || ! empty( $changeset_data[ $setting_id ]['starter_content'] ) ) {
                        $this->set_post_value( $setting_id, $setting_value );
                        $this->_pending_starter_content_settings_ids[] = $setting_id;
                    }
                    $sidebar_widget_ids[] = $widget_id;
                }
                $setting_id = sprintf( 'sidebars_widgets[%s]', $sidebar_id );
                if ( empty( $changeset_data[ $setting_id ] ) || ! empty( $changeset_data[ $setting_id ]['starter_content'] ) ) {
                    $this->set_post_value( $setting_id, $sidebar_widget_ids );
                    $this->_pending_starter_content_settings_ids[] = $setting_id;
                }
            }
            $starter_content_auto_draft_post_ids = array();
            if ( ! empty( $changeset_data['nav_menus_created_posts']['value'] ) ) {
                $starter_content_auto_draft_post_ids = array_merge( $starter_content_auto_draft_post_ids, $changeset_data['nav_menus_created_posts']['value'] );
            }
            $needed_posts = [];
            $attachments  = $this->_prepare_starter_content_attachments( $attachments );
            foreach ( $attachments as $attachment ) {
                $key = 'attachment:' . $attachment['post_name'];
                $needed_posts[ $key ] = true;
            }
            foreach ( array_keys( $posts ) as $post_symbol ) {
                if ( empty( $posts[ $post_symbol ]['post_name'] ) && empty( $posts[ $post_symbol ]['post_title'] ) ) {
                    unset( $posts[ $post_symbol ] );
                    continue;
                }
                if ( empty( $posts[ $post_symbol ]['post_name'] ) ) {
                    $posts[ $post_symbol ]['post_name'] = $this->_sanitize_title( $posts[ $post_symbol ]['post_title'] );
                }
                if ( empty( $posts[ $post_symbol ]['post_type'] ) ) {$posts[ $post_symbol ]['post_type'] = 'post';}
                $needed_posts[ $posts[ $post_symbol ]['post_type'] . ':' . $posts[ $post_symbol ]['post_name'] ] = true;
            }
            $all_post_slugs = array_merge(
                $this->_tp_list_pluck( $attachments, 'post_name' ),
                $this->_tp_list_pluck( $posts, 'post_name' )
            );
            $post_types = array_filter( array_merge(['attachment'], $this->_tp_list_pluck( $posts, 'post_type' ) ) );
            $existing_starter_content_posts = [];
            if ( ! empty( $starter_content_auto_draft_post_ids ) ) {
                $existing_posts_query = new TP_Query(['post__in' => $starter_content_auto_draft_post_ids,
                    'post_status' => 'auto-draft', 'post_type' => $post_types,'posts_per_page' => -1,]);
                foreach ( $existing_posts_query->posts as $existing_post ) {
                    $post_name = $existing_post->post_name;
                    if ( empty( $post_name ) ) {
                        $post_name = $this->_get_post_meta( $existing_post->ID, '_customize_draft_post_name', true );
                    }
                    $existing_starter_content_posts[ $existing_post->post_type . ':' . $post_name ] = $existing_post;
                }
            }
            if ( ! empty( $all_post_slugs ) ) {
                $existing_posts_query = new TP_Query(['post_name__in' => $all_post_slugs,
                    'post_status' => array_diff( $this->_get_post_stati(), array( 'auto-draft' ) ),
                    'post_type' => 'any','posts_per_page' => -1,]);
                foreach ( $existing_posts_query->posts as $existing_post ) {
                    $key = $existing_post->post_type . ':' . $existing_post->post_name;
                    if ( isset( $needed_posts[ $key ] ) && ! isset( $existing_starter_content_posts[ $key ] ) ) {
                        $existing_starter_content_posts[ $key ] = $existing_post;
                    }
                }
            }
            if ( ! empty( $attachments ) ) {
                $attachment_ids = array();
                foreach ( $attachments as $symbol => $attachment ) {
                    $file_array  = ['name' => $attachment['file_name'],];
                    $file_path  = $attachment['file_path'];
                    $attachment_id = null;
                    $attached_file = null;
                    $_existing_starter_content = $existing_starter_content_posts[ 'attachment:' . $attachment['post_name'] ];
                    if ( isset($_existing_starter_content) ) {
                        $attachment_post = $existing_starter_content_posts[ 'attachment:' . $attachment['post_name'] ];
                        $attachment_id   = $attachment_post->ID;
                        $attached_file   = $this->_get_attached_file( $attachment_id );
                        if ( empty( $attached_file ) || ! file_exists( $attached_file ) ) {
                            $attachment_id = null;
                            $attached_file = null;
                        } elseif ( $this->get_stylesheet() !== $this->_get_post_meta( $attachment_post->ID, '_starter_content_theme', true ) ) {
                            $metadata = $this->_tp_generate_attachment_metadata( $attachment_post->ID, $attached_file );
                            $this->_tp_update_attachment_metadata( $attachment_id, $metadata );
                            $this->_update_post_meta( $attachment_id, '_starter_content_theme', $this->get_stylesheet() );
                        }
                    }
                    if ( ! $attachment_id ) {
                        $temp_file_name = $this->_tp_temp_name( $this->_tp_basename( $file_path ) );
                        if ( $temp_file_name && copy( $file_path, $temp_file_name )){ $file_array['tmp_name'] = $temp_file_name;}
                        if ( empty( $file_array['tmp_name'])){ continue;}
                        $attachment_post_data = array_merge(
                            $this->_tp_array_slice_assoc( $attachment, ['post_title', 'post_content', 'post_excerpt'] ),
                            ['post_status' => 'auto-draft', ]
                        );
                        $attachment_id = $this->_media_handle_sideload( $file_array, 0, null, $attachment_post_data );
                        if ( $this->_init_error( $attachment_id ) ){continue;}
                        $this->_update_post_meta( $attachment_id, '_starter_content_theme', $this->get_stylesheet() );
                        $this->_update_post_meta( $attachment_id, '_customize_draft_post_name', $attachment['post_name'] );
                    }
                    $attachment_ids[ $symbol ] = $attachment_id;
                }
                $starter_content_auto_draft_post_ids = array_merge( $starter_content_auto_draft_post_ids, array_values( $attachment_ids ) );
            }
            if ( ! empty( $posts ) ) {
                foreach ( array_keys( $posts ) as $post_symbol ) {
                    if ( empty( $posts[ $post_symbol ]['post_type'] ) || empty( $posts[ $post_symbol ]['post_name'] ) ) {
                        continue;
                    }
                    $post_type = $posts[ $post_symbol ]['post_type'];
                    if ( ! empty( $posts[ $post_symbol ]['post_name'] ) ) {
                        $post_name = $posts[ $post_symbol ]['post_name'];
                    } elseif ( ! empty( $posts[ $post_symbol ]['post_title'] ) ) {
                        $post_name = $this->_sanitize_title( $posts[ $post_symbol ]['post_title'] );
                    } else { continue;}
                    $starter_content = $existing_starter_content_posts[ $post_type . ':' . $post_name ];
                    if ( isset($starter_content) ) {
                        $posts[ $post_symbol ]['ID'] = $existing_starter_content_posts[ $post_type . ':' . $post_name ]->ID;
                        continue;
                    }
                    if ( ! empty( $posts[ $post_symbol ]['thumbnail'] )
                        && preg_match( '/^{{(?P<symbol>.+)}}$/', $posts[ $post_symbol ]['thumbnail'], $matches )
                        && isset( $attachment_ids[ $matches['symbol'] ] ) ) {
                        $posts[ $post_symbol ]['meta_input']['_thumbnail_id'] = $attachment_ids[ $matches['symbol'] ];
                    }
                    if ( ! empty( $posts[ $post_symbol ]['template'] ) ) {
                        $posts[ $post_symbol ]['meta_input']['_tp_page_template'] = $posts[ $post_symbol ]['template'];
                    }
                    $r = $this->nav_menus->insert_auto_draft_post( $posts[ $post_symbol ] );
                    if ( $r instanceof TP_Post ) {
                        $posts[ $post_symbol ]['ID'] = $r->ID;
                    }
                }
                $starter_content_auto_draft_post_ids = array_merge( $starter_content_auto_draft_post_ids, $this->_tp_list_pluck( $posts, 'ID' ) );
            }
            if ( ! empty( $this->nav_menus ) && ! empty( $starter_content_auto_draft_post_ids ) ) {
                $setting_id = 'nav_menus_created_posts';
                $this->set_post_value( $setting_id, array_unique( array_values( $starter_content_auto_draft_post_ids ) ) );
                $this->_pending_starter_content_settings_ids[] = $setting_id;
            }
            $placeholder_id              = -1;
            $reused_nav_menu_setting_ids = [];
            foreach ( $nav_menus as $nav_menu_location => $nav_menu ) {
                $nav_menu_term_id    = null;
                $nav_menu_setting_id = null;
                $matches             = [];
                foreach ( $changeset_data as $setting_id => $setting_params ) {
                    $can_reuse = ( ! empty( $setting_params['starter_content'] ) && ! in_array( $setting_id, $reused_nav_menu_setting_ids, true )
                        && preg_match( '#^nav_menu\[(?P<nav_menu_id>-?\d+)\]$#', $setting_id, $matches )
                    );
                    if ( $can_reuse ) {
                        $nav_menu_term_id              = (int) $matches['nav_menu_id'];
                        $nav_menu_setting_id           = $setting_id;
                        $reused_nav_menu_setting_ids[] = $setting_id;
                        break;
                    }
                }
                if ( ! $nav_menu_term_id ) {
                    while ( isset( $changeset_data[ sprintf( 'nav_menu[%d]', $placeholder_id ) ] ) ) {
                        $placeholder_id--; }
                    $nav_menu_term_id = $placeholder_id;
                    $nav_menu_setting_id = sprintf( 'nav_menu[%d]', $placeholder_id );
                }
                $this->set_post_value( $nav_menu_setting_id,['name' => $nav_menu['name'] ?? $nav_menu_location,]);
                $this->_pending_starter_content_settings_ids[] = $nav_menu_setting_id;
                $position = 0;
                foreach ( $nav_menu['items'] as $nav_menu_item ) {
                    $nav_menu_item_setting_id = sprintf( 'nav_menu_item[%d]', $placeholder_id-- );
                    if ( ! isset( $nav_menu_item['position'] ) ) {
                        $nav_menu_item['position'] = $position++;
                    }
                    $nav_menu_item['nav_menu_term_id'] = $nav_menu_term_id;
                    if ( isset( $nav_menu_item['object_id'] ) ) {
                        if ( 'post_type' === $nav_menu_item['type'] && preg_match( '/^{{(?P<symbol>.+)}}$/', $nav_menu_item['object_id'], $matches ) && isset( $posts[ $matches['symbol'] ] ) ) {
                            $nav_menu_item['object_id'] = $posts[ $matches['symbol'] ]['ID'];
                            if ( empty( $nav_menu_item['title'] ) ) {
                                $original_object        = $this->_get_post( $nav_menu_item['object_id'] );
                                $nav_menu_item['title'] = $original_object->post_title;
                            }
                        } else { continue; }
                    } else {$nav_menu_item['object_id'] = 0;}
                    if ( empty( $changeset_data[ $nav_menu_item_setting_id ] ) || ! empty( $changeset_data[ $nav_menu_item_setting_id ]['starter_content'] ) ) {
                        $this->set_post_value( $nav_menu_item_setting_id, $nav_menu_item );
                        $this->_pending_starter_content_settings_ids[] = $nav_menu_item_setting_id;
                    }
                }
                $setting_id = sprintf( 'nav_menu_locations[%s]', $nav_menu_location );
                if ( empty( $changeset_data[ $setting_id ] ) || ! empty( $changeset_data[ $setting_id ]['starter_content'] ) ) {
                    $this->set_post_value( $setting_id, $nav_menu_term_id );
                    $this->_pending_starter_content_settings_ids[] = $setting_id;
                }
            }
            foreach ( $options as $name => $value ) {
                $value = $this->_maybe_serialize( $value );
                if ( $this->_is_serialized( $value ) ) {
                    if ( preg_match( '/s:\d+:"{{(?P<symbol>.+)}}"/', $value, $matches ) ) {
                        if ( isset( $posts[ $matches['symbol'] ] ) ) {
                            $symbol_match = $posts[ $matches['symbol'] ]['ID'];
                        } elseif ( isset( $attachment_ids[ $matches['symbol'] ] ) ) {
                            $symbol_match = $attachment_ids[ $matches['symbol'] ];}
                        if ( isset( $symbol_match ) ) {
                            $value = str_replace( $matches[0], "i:{$symbol_match}", $value );
                        } else {continue;}
                    }
                } elseif ( preg_match( '/^{{(?P<symbol>.+)}}$/', $value, $matches ) ) {
                    if ( isset( $posts[ $matches['symbol'] ] ) ) {
                        $value = $posts[ $matches['symbol'] ]['ID'];
                    } elseif ( isset( $attachment_ids[ $matches['symbol'] ] ) ) {
                        $value = $attachment_ids[ $matches['symbol'] ];
                    } else { continue;}
                }
                $value = $this->_maybe_unserialize( $value );
                if ( empty( $changeset_data[ $name ] ) || ! empty( $changeset_data[ $name ]['starter_content'] ) ) {
                    $this->set_post_value( $name, $value );
                    $this->_pending_starter_content_settings_ids[] = $name;
                }
            }
            foreach ( $theme_mods as $name => $value ) {
                $value = $this->_maybe_serialize( $value );
                if ( $this->_is_serialized( $value ) ) {
                    if ( preg_match( '/s:\d+:"{{(?P<symbol>.+)}}"/', $value, $matches ) ) {
                        if ( isset( $posts[ $matches['symbol'] ] ) ) {
                            $symbol_match = $posts[ $matches['symbol'] ]['ID'];
                        } elseif ( isset( $attachment_ids[ $matches['symbol'] ] ) ) {
                            $symbol_match = $attachment_ids[ $matches['symbol'] ];
                        }
                        if ( isset( $symbol_match ) ) {
                            $value = str_replace( $matches[0], "i:{$symbol_match}", $value );
                        } else { continue; }
                    }
                } elseif ( preg_match( '/^{{(?P<symbol>.+)}}$/', $value, $matches ) ) {
                    if ( isset( $posts[ $matches['symbol'] ] ) ) {
                        $value = $posts[ $matches['symbol'] ]['ID'];
                    } elseif ( isset( $attachment_ids[ $matches['symbol'] ] ) ) {
                        $value = $attachment_ids[ $matches['symbol'] ];
                    } else { continue;}
                }
                $value = $this->_maybe_unserialize( $value );
                if ( 'header_image' === $name ) {
                    $name     = 'header_image_data';
                    $metadata = $this->_tp_get_attachment_metadata( $value );
                    if ( empty( $metadata ) ) { continue; }
                    $value = ['attachment_id' => $value,'url' => $this->_tp_get_attachment_url( $value ),'height' => $metadata['height'],'width' => $metadata['width'],];
                } elseif ( 'background_image' === $name ) {$value = $this->_tp_get_attachment_url( $value );}
                if ( empty( $changeset_data[ $name ] ) || ! empty( $changeset_data[ $name ]['starter_content'] ) ) {
                    $this->set_post_value( $name, $value );
                    $this->_pending_starter_content_settings_ids[] = $name;
                }
            }
            if ( ! empty( $this->pending_starter_content_settings_ids ) ) {
                if ( $this->_did_action( 'customize_register' ) ) {$this->_save_starter_content_changeset(); }
                else { $this->_add_action( 'customize_register', array( $this, '_save_starter_content_changeset' ), 1000 );}
            }
        }//1188
        protected function _prepare_starter_content_attachments( $attachments ):string{
            $prepared_attachments = [];
            if ( empty( $attachments ) ) {return $prepared_attachments;}
            foreach ( $attachments as $symbol => $attachment ) {
                if ( empty( $attachment['file'] ) || preg_match( '#^https?://$#', $attachment['file'] ) ) {
                    continue;}
                $file_path = null;
                if ( file_exists( $attachment['file'] ) ) {
                    $file_path = $attachment['file']; // Could be absolute path to file in plugin.
                } elseif ( file_exists( $this->_get_template_directory() . '/' . $attachment['file'] ) ) {
                    $file_path = $this->_get_template_directory() . '/' . $attachment['file'];
                } else {continue;}
                $file_name = $this->_tp_basename( $attachment['file'] );
                $checked_filetype = $this->_tp_check_file_type( $file_name );
                if ( empty( $checked_filetype['type'])){ continue;}
                if ( empty( $attachment['post_name'] ) ) {
                    if ( ! empty( $attachment['post_title'] ) ) { $attachment['post_name'] = $this->_sanitize_title( $attachment['post_title'] );}
                    else {$attachment['post_name'] = $this->_sanitize_title( preg_replace( '/\.\w+$/', '', $file_name ) );}
                }
                $attachment['file_name']         = $file_name;
                $attachment['file_path']         = $file_path;
                $prepared_attachments[ $symbol ] = $attachment;
            }
            return $prepared_attachments;
        }//1644
        protected function _save_starter_content_changeset():void{
            if ( empty( $this->pending_starter_content_settings_ids )){ return;}
            $this->save_changeset_post(['data'=> array_fill_keys( $this->pending_starter_content_settings_ids, array( 'starter_content' => true ) ),
                'starter_content' => true,]);
            $this->_saved_starter_content_changeset = true;
            $this->_pending_starter_content_settings_ids = [];
        }//1701
        public function unsanitized_post_values(array ...$args){
            $args = array_merge(['exclude_changeset' => false,'exclude_post_data' => ! $this->_current_user_can( 'customize' ),],$args);
            $values = [];
            if ( ! $this->is_theme_active() ) {
                $stashed_theme_mods = $this->_get_option( 'customize_stashed_theme_mods' );
                $stylesheet         = $this->get_stylesheet();
                if ( isset( $stashed_theme_mods[ $stylesheet ] ) ) {
                    $values = array_merge( $values, $this->_tp_list_pluck( $stashed_theme_mods[ $stylesheet ], 'value' ) );
                }
            }
            if ( ! $args['exclude_changeset'] ) {
                foreach ( $this->changeset_data() as $setting_id => $setting_params ) {
                    if ( ! array_key_exists( 'value', $setting_params )){continue;}
                    if ( isset( $setting_params['type'] ) && 'theme_mod' === $setting_params['type'] ) {
                        $namespace_pattern = '/^(?P<stylesheet>.+?)::(?P<setting_id>.+)$/';
                        if ( preg_match( $namespace_pattern, $setting_id, $matches ) && $this->get_stylesheet() === $matches['stylesheet'] ) {
                            $values[ $matches['setting_id'] ] = $setting_params['value'];
                        }
                    } else {$values[ $setting_id ] = $setting_params['value'];}
                }
            }
            if ( ! $args['exclude_post_data'] ) {
                if ( ! isset( $this->_post_values ) ) {
                    if ( isset( $_POST['customized'] ) ) {
                        $post_values = json_decode( $this->_tp_unslash( $_POST['customized'] ), true );
                    } else { $post_values = [];}
                    if ( is_array( $post_values ) ) {
                        $this->_post_values = $post_values;
                    } else {$this->_post_values = [];}
                }
                $values = array_merge( $values, $this->_post_values );
            }
            return $values;
        }//1745
        public function post_value(TP_Customize_Setting $setting, $default = null ){
            $post_values = $this->unsanitized_post_values();
            if ( ! array_key_exists( $setting->id, $post_values )){ return $default;}
            $value = $post_values[ $setting->id ];
            $valid = $setting->validate( $value );
            if ( $this->_init_error( $valid ) ) { return $default;}
            $value = $setting->sanitize( $value );
            if ( is_null( $value ) || $this->_init_error( $value ) ){ return $default;}
            return $value;
        }//1821
        public function set_post_value( $setting_id, $value ):void{
            $this->unsanitized_post_values(); // Populate _post_values from $_POST['customized'].
            $this->__post_values[ $setting_id ] = $value;
            $this->_do_action( "customize_post_value_set_{$setting_id}", $value, $this );
            $this->_do_action( 'customize_post_value_set', $setting_id, $value, $this );
        }//1894
        public function customize_preview_init():void{
            if ( ! headers_sent() ) {
                $this->_nocache_headers();
                header( 'X-Robots: noindex, nofollow, noarchive' );
            }
            $this->_add_filter( 'tp_robots', 'tp_robots_no_robots' );
            $this->_add_filter( 'tp_headers', [ $this, 'filter_iframe_security_headers']);
            if ( $this->_messenger_channel && ! $this->_current_user_can( 'customize' ) ) {
                $this->_tp_die(-1,sprintf( $this->__( 'Unauthorized. You may remove the %s param to preview as frontend.' ),
                        '<code>customize_messenger_channel<code>'));
                return;
            }
            $this->prepare_controls();
            $this->_add_filter( 'tp_redirect', array( $this, 'add_state_query_params' ) );
            $this->tp_enqueue_script( 'customize-preview' );
            $this->tp_enqueue_style( 'customize-preview' );
            $this->_add_action( 'tp_head', array( $this, 'customize_preview_loading_style' ) );
            $this->_add_action( 'tp_head', array( $this, 'remove_frameless_preview_messenger_channel' ) );
            $this->_add_action( 'tp_footer', array( $this, 'customize_preview_settings' ), 20 );
            $this->_add_filter( 'get_edit_post_link', '__return_empty_string' );
            $this->_do_action( 'customize_preview_init', $this );
        }//1889
        public function filter_iframe_security_headers( $headers ) {
            $headers['X-Frame-Options']         = 'SAMEORIGIN';
            $headers['Content-Security-Policy'] = "frame-ancestors 'self'";
            return $headers;
        }//1953
        public function add_state_query_params( $url ){
            $parsed_original_url = $this->_tp_parse_url( $url );
            $is_allowed = false;
            foreach ( $this->get_allowed_urls() as $allowed_url ) {
                $parsed_allowed_url = $this->_tp_parse_url( $allowed_url );
                $is_allowed = ( $parsed_allowed_url['scheme'] === $parsed_original_url['scheme'] &&
                    $parsed_allowed_url['host'] === $parsed_original_url['host'] &&
                    0 === strpos( $parsed_original_url['path'], $parsed_allowed_url['path'] ) );
                if ( $is_allowed ) { break;}
            }
            if ( $is_allowed ) {
                $query_params = ['customize_changeset_uuid' => $this->changeset_uuid(),];
                if ( ! $this->is_theme_active()){$query_params['customize_theme'] = $this->get_stylesheet();}
                if ( $this->_messenger_channel ){
                    $query_params['customize_messenger_channel'] = $this->_messenger_channel;
                }
                $url = $this->_add_query_arg( $query_params, $url );
            }
            return $url;
        }//1970
        public function get_customize_preview_loading_style():string{
            ob_start();
            ?>
            <style>/* todo , is for later */</style>
            <?php
            return ob_get_clean();
        }//2039
        public function customize_preview_loading_style():void{
            echo $this->get_customize_preview_loading_style();
        }//2039
        public function get_remove_frameless_preview_messenger_channel():string{
            if ( ! $this->_messenger_channel ){ return;}
            ob_start();
            ?>
            <script>
                (function(){
                    let oldQueryParams, newQueryParams, i;
                    if ( parent !== window ) {
                        return;
                    }
                    const urlParser = document.createElement( 'a' );
                    urlParser.href = location.href;
                    oldQueryParams = urlParser.search.substr( 1 ).split( /&/ );
                    newQueryParams = [];
                    for ( i = 0; i < oldQueryParams.length; i += 1 ) {
                        if ( ! /^customize_messenger_channel=/.test( oldQueryParams[ i ] ) ) {
                            newQueryParams.push( oldQueryParams[ i ] );
                        }
                    }
                    urlParser.search = newQueryParams.join( '&' );
                    if ( urlParser.search !== location.search ) {
                        location.replace( urlParser.href );
                    }
                })();
            </script>
            <?php
            return ob_get_clean();
        }//2071
        public function remove_frameless_preview_messenger_channel():void{
            echo $this->get_remove_frameless_preview_messenger_channel();
        }//2071
        public function get_customize_preview_settings():string{
            $post_values                 = $this->unsanitized_post_values( array( 'exclude_changeset' => true ) );
            $setting_validities          = $this->validate_setting_values( $post_values );
            $exported_setting_validities = array_map( [$this, 'prepare_setting_validity_for_js'], $setting_validities );
            $self_url           = empty( $_SERVER['REQUEST_URI'] ) ? $this->_home_url( '/' ) : $this->_esc_url_raw( $this->_tp_unslash( $_SERVER['REQUEST_URI'] ) );
            $state_query_params = ['customize_theme','customize_changeset_uuid', 'customize_messenger_channel',];
            $self_url           = $this->_remove_query_arg( $state_query_params, $self_url );
            $allowed_urls  = $this->get_allowed_urls();
            $allowed_hosts = [];
            foreach ( $allowed_urls as $allowed_url ) {
                $parsed = $this->_tp_parse_url( $allowed_url );
                if(empty( $parsed['host'])){ continue;}
                $host = $parsed['host'];
                if ( ! empty( $parsed['port'])){$host .= ':' . $parsed['port'];}
                $allowed_hosts[] = $host;
            }
            $switched_locale = $this->_switch_to_locale( $this->_get_user_locale() );
            $l10n = ['shiftClickToEdit'  => $this->__( 'Shift-click to edit this element.' ),
                'linkUnpreviewable' => $this->__( 'This link is not live-previewable.' ),
                'formUnpreviewable' => $this->__( 'This form is not live-previewable.' ),];
            if ( $switched_locale ) {$this->_restore_previous_locale();}
            $settings = ['changeset' => ['uuid' => $this->changeset_uuid(),'autosaved' => $this->autosaved(),],
                'timeouts' => ['selectiveRefresh' => 250,'keepAliveSend' => 1000,],
                'theme' => ['stylesheet' => $this->get_stylesheet(),'active' => $this->is_theme_active(),],
                'url' => ['self' => $self_url,'allowed' => array_map( 'esc_url_raw', $this->get_allowed_urls() ),
                    'allowedHosts' => array_unique( $allowed_hosts ),'isCrossDomain' => $this->is_cross_domain(),],
                'channel' => $this->_messenger_channel,'activePanels' => [],'activeSections' => [],'activeControls' => [],
                'settingValidities' => $exported_setting_validities,'nonce' => $this->_current_user_can( 'customize' ) ? $this->get_nonces() : array(),
                'l10n' => $l10n,'_dirty' => array_keys( $post_values ),];

            foreach ( $this->_panels as $panel_id => $panel ) {
                if ( $panel->check_capabilities() ) {
                    $settings['activePanels'][ $panel_id ] = $panel->active();
                    foreach ( $panel->sections as $section_id => $section ) {
                        if(($section instanceof TP_Customize_Setting) && $section instanceof TP_Customize_Section && $section->check_capabilities()) {
                            $settings['activeSections'][ $section_id ] = $section->active();
                        }
                    }
                }
            }
            foreach ( $this->_sections as $id => $section ) {
                if ( $section->check_capabilities()){ $settings['activeSections'][ $id ] = $section->active();}
            }
            foreach ( $this->_controls as $id => $control ) {
                if ( $control->check_capabilities() ) { $settings['activeControls'][ $id ] = $control->active();}
            }
            $v = 'v';
            ob_start();
            ?>
            <!--suppress JSUnusedAssignment -->
            <script>
                let _tpCustomizeSettings = <?php echo $this->_tp_json_encode( $settings ); ?>;
                _tpCustomizeSettings.values = {};
                (function(<?php echo $v ?>){
                    <?php
                    foreach ( $this->_settings as $id => $setting ) {
                        if ( $setting->check_capabilities() ) {
                            printf(
                                "v[%s] = %s;\n",
                                $this->_tp_json_encode( $id ),
                                $this->_tp_json_encode( $setting->js_value() )
                            );
                        }
                    }
                    ?>
                })(_tpCustomizeSettings.values);
            </script>
            <?php
            return ob_get_clean();
        }//2105
        public function customize_preview_settings():void{
            echo $this->get_customize_preview_settings();
        }//2105
        public function is_preview():bool{
            return (bool) $this->_previewing;
        }//2251
        public function get_template(){
            return $this->theme()->get_template();
        }//2262
        public function get_stylesheet() {
            return $this->theme()->get_stylesheet();
        }//2273
        public function get_template_root() {
            return $this->_get_raw_theme_root( $this->get_template(), true );
        }//2284
        public function get_stylesheet_root() {
            return $this->_get_raw_theme_root( $this->get_stylesheet(), true );
        }//2295
        public function current_theme() { //not used  $current_theme
            return $this->theme()->display( 'Name' );
        }//2307
        public function validate_setting_values( $setting_values,array ...$options):string{
            $options = $this->_tp_parse_args($options,['validate_capability' => false,'validate_existence' => false,]);
            $validities = [];
            foreach ( $setting_values as $setting_id => $unsanitized_value ) {
                $setting = $this->get_setting( $setting_id );
                if ( ! $setting ) {
                    if ( $options['validate_existence'] ) {
                        $validities[ $setting_id ] = new TP_Error( 'unrecognized', $this->__( 'Setting does not exist or is unrecognized.' ) );
                    }
                    continue;
                }
                if ( $options['validate_capability'] && ! $this->_current_user_can( $setting->capability ) ) {
                    $validity = new TP_Error( 'unauthorized', $this->__( 'Unauthorized to modify setting due to capability.' ) );
                } else {
                    if ( is_null( $unsanitized_value ) ) { continue;}
                    $validity = $setting->validate( $unsanitized_value );
                }
                if ( ! $this->_init_error( $validity ) ) {
                    /** This filter is documented in wp-includes/class-wp-customize-setting.php */
                    $late_validity = $this->_apply_filters( "customize_validate_{$setting->id}", new TP_Error(), $unsanitized_value, $setting );
                    if ( $this->_init_error( $late_validity ) && $late_validity->has_errors() ) {
                        $validity = $late_validity;}
                }
                if ( ! $this->_init_error( $validity ) ) {
                    $value = $setting->sanitize( $unsanitized_value );
                    if(is_null($value)){ $validity = false;}
                    elseif($this->_init_error($value)){$validity = $value;}
                }
                if(false === $validity){$validity = new TP_Error('invalid_value',$this->__( 'Invalid value.'));}
                $validities[ $setting_id ] = $validity;
            }
            return $validities;
        }//2333
        public function prepare_setting_validity_for_js(TP_Error $validity ){
            if ( $this->_init_error( $validity ) ) {
                $notification = [];
                foreach ( $validity->errors as $error_code => $error_messages ) {
                    $notification[ $error_code ] = array(
                        'message' => implode( ' ', $error_messages ),
                        'data'    => $validity->get_error_data( $error_code ),
                    );
                }
                return $notification;
            }
            return true;
        }//2395
        public function save():void{
            if ( ! $this->_is_user_logged_in() ) { $this->_tp_send_json_error( 'unauthenticated' );}
            if ( ! $this->is_preview() ) {$this->_tp_send_json_error( 'not_preview' );}
            $action = 'save-customize_' . $this->get_stylesheet();
            if ( ! $this->_check_async_referer( $action, 'nonce', false ) ) {$this->_tp_send_json_error( 'invalid_nonce' );}
            $changeset_post_id = $this->changeset_post_id();
            $is_new_changeset  = $changeset_post_id === null;
            if ( $is_new_changeset ) {
                if ( ! $this->_current_user_can( $this->_get_post_type_object( 'customize_changeset' )->cap->create_posts ) ) {
                    $this->_tp_send_json_error( 'cannot_create_changeset_post' );}
            } else if ( ! $this->_current_user_can( $this->_get_post_type_object( 'customize_changeset' )->cap->edit_post, $changeset_post_id ) ) {
                $this->_tp_send_json_error( 'cannot_edit_changeset_post' );}
            if ( ! empty( $_POST['customize_changeset_data'] ) ) {
                $input_changeset_data = json_decode( $this->_tp_unslash( $_POST['customize_changeset_data'] ), true );
                if(!is_array( $input_changeset_data)){ $this->_tp_send_json_error('invalid_customize_changeset_data');}
            } else { $input_changeset_data = [];}
            $changeset_title = null;
            if ( isset( $_POST['customize_changeset_title'] ) ) {
                $changeset_title = $this->_sanitize_text_field( $this->_tp_unslash( $_POST['customize_changeset_title'] ) );
            }
            $is_publish       = null;
            $changeset_status = null;
            if ( isset( $_POST['customize_changeset_status'] ) ) {
                $changeset_status = $this->_tp_unslash( $_POST['customize_changeset_status'] );
                if ( ! $this->_get_post_status_object( $changeset_status ) || ! in_array( $changeset_status, array( 'draft', 'pending', 'publish', 'future' ), true ) ) {
                    $this->_tp_send_json_error( 'bad_customize_changeset_status', 400 );}
                $is_publish = ( 'publish' === $changeset_status || 'future' === $changeset_status );
                if ( $is_publish && ! $this->_current_user_can( $this->_get_post_type_object( 'customize_changeset' )->cap->publish_posts ) ) {
                    $this->_tp_send_json_error( 'changeset_publish_unauthorized', 403 ); }
            }
            $changeset_date_gmt = null;
            if ( isset( $_POST['customize_changeset_date'] ) ) {
                $changeset_date = $this->_tp_unslash( $_POST['customize_changeset_date'] );
                if ( preg_match( '/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', $changeset_date ) ) {
                    $mm         = substr( $changeset_date, 5, 2 );
                    $jj         = substr( $changeset_date, 8, 2 );
                    $aa         = substr( $changeset_date, 0, 4 );
                    $valid_date = $this->_tp_check_date( $mm, $jj, $aa, $changeset_date );
                    if ( ! $valid_date ) { $this->_tp_send_json_error( 'bad_customize_changeset_date', 400 );}
                    $changeset_date_gmt = $this->_get_gmt_from_date( $changeset_date );
                } else {
                    $timestamp = strtotime( $changeset_date );
                    if ( ! $timestamp ) { $this->_tp_send_json_error( 'bad_customize_changeset_date', 400 );}
                    $changeset_date_gmt = gmdate( 'Y-m-d H:i:s', $timestamp );
                }
            }
            $lock_user_id = null;
            $autosave     = ! empty( $_POST['customize_changeset_autosave'] );
            if ( ! $is_new_changeset ) {$lock_user_id = $this->_tp_check_post_lock( $this->changeset_post_id() );}
            if ( $lock_user_id && ! $autosave ) {
                $autosave           = true;
                $changeset_status   = null;
                $changeset_date_gmt = null;
            }
            $autosaved = false;
            $r = $this->save_changeset_post(['status' => $changeset_status,'title' => $changeset_title,
                    'date_gmt' => $changeset_date_gmt,'data' => $input_changeset_data,'autosave' => $autosave,]);
            if ( $autosave && ! $this->_init_error( $r ) ) {$autosaved = true;}
            if ( $lock_user_id && ! $this->_init_error( $r ) ) {
                $r = new TP_Error('changeset_locked',$this->__( 'Changeset is being edited by other user.' ),
                    ['lock_user' => $this->_get_lock_user_data( $lock_user_id ),]);
            }
            if ( $this->_init_error( $r ) ) {
                $response = ['message' => $r->get_error_message(),'code' => $r->get_error_code(),];
                if(is_array( $r->get_error_data())){$response = array_merge( $response, $r->get_error_data() );}
                else { $response['data'] = $r->get_error_data();}
            } else {
                $response       = $r;
                $changeset_post = $this->_get_post( $this->changeset_post_id() );
                if ( $is_new_changeset ) {$this->_dismiss_user_auto_draft_changesets();}
                $response['changeset_status'] = $changeset_post->post_status;
                if ( $is_publish && 'trash' === $response['changeset_status'] ) {
                    $response['changeset_status'] = 'publish';}
                if ( 'publish' !== $response['changeset_status'] ) {
                    $this->set_changeset_lock( $changeset_post->ID );}
                if ( 'future' === $response['changeset_status'] ) {
                    $response['changeset_date'] = $changeset_post->post_date;}
                if ( 'publish' === $response['changeset_status'] || 'trash' === $response['changeset_status'] ) {
                    $response['next_changeset_uuid'] = $this->_tp_generate_uuid4();}
            }
            if ( $autosave ) {$response['autosaved'] = $autosaved;}
            if ( isset( $response['setting_validities'] ) ) {
                $response['setting_validities'] = array_map( array( $this, 'prepare_setting_validity_for_js' ), $response['setting_validities'] );
            }
            $response = $this->_apply_filters( 'customize_save_response', $response, $this );
            if ( $this->_init_error( $r ) ) {$this->_tp_send_json_error( $response );}
            else {$this->_tp_send_json_success( $response );}
        }//2416
        public function save_changeset_post(array $args):string{
            $args = array_merge(['status' => null,'title' => null,'data' => [],'date_gmt' => null,
                    'user_id' => $this->_get_current_user_id(),'starter_content' => false,'autosave' => false,],$args);
            $changeset_post_id       = $this->changeset_post_id();
            $existing_changeset_data = [];
            if ( $changeset_post_id ) {
                $existing_status = $this->_get_post_status( $changeset_post_id );
                if ( 'publish' === $existing_status || 'trash' === $existing_status ) {
                    return new TP_Error('changeset_already_published',
                        $this->__( 'The previous set of changes has already been published. Please try saving your current set of changes again.' ),
                        ['next_changeset_uuid' => $this->_tp_generate_uuid4(),]);
                }
                $existing_changeset_data = $this->_get_changeset_post_data( $changeset_post_id );
                if($this->_init_error( $existing_changeset_data )){return $existing_changeset_data;}
            }
            if ( 'publish' === $args['status'] && false === $this->_has_action( 'transition_post_status', '_wp_customize_publish_changeset' ) ) {
                return new TP_Error( 'missing_publish_callback' );}
            $now = gmdate( 'Y-m-d H:i:59' );
            if ( $args['date_gmt'] ) {
                $is_future_dated = ( $this->_mysql2date( 'U', $args['date_gmt'], false ) > $this->_mysql2date( 'U', $now, false ) );
                if ( ! $is_future_dated ) {
                    return new TP_Error( 'not_future_date', $this->__( 'You must supply a future date to schedule.' ) ); // Only future dates are allowed.
                }
                if (( 'future' === $args['status'] || $is_future_dated ) &&  ! $this->is_theme_active() ) {
                    return new TP_Error( 'cannot_schedule_theme_switches' );  }
                $will_remain_auto_draft = ( ! $args['status'] && ( ! $changeset_post_id || 'auto-draft' === $this->_get_post_status( $changeset_post_id ) ) );
                if ( $will_remain_auto_draft ) {return new TP_Error( 'cannot_supply_date_for_auto_draft_changeset' );}
            } elseif ( $changeset_post_id && 'future' === $args['status'] ) {
                $changeset_post = $this->_get_post( $changeset_post_id );
                if ( $this->_mysql2date( 'U', $changeset_post->post_date_gmt, false ) <= $this->_mysql2date( 'U', $now, false ) ) {
                    return new TP_Error( 'not_future_date', $this->__( 'You must supply a future date to schedule.' ) );
                }
            }
            if ( ! empty( $is_future_dated ) && 'publish' === $args['status']){ $args['status'] = 'future';}
            if ( $args['autosave'] ) {
                if ($args['date_gmt']) { return new TP_Error( 'illegal_autosave_with_date_gmt' );}
                if ($args['status']) { return new TP_Error( 'illegal_autosave_with_status' );}
                if ($args['user_id'] && $this->_get_current_user_id() !== $args['user_id']) {
                    return new TP_Error( 'illegal_autosave_with_non_current_user' );}
            }
            $update_transactionally = (bool) $args['status'];
            $allow_revision         = (bool) $args['status'];
            foreach ( $args['data'] as $setting_id => $setting_params ) {
                if ( is_array( $setting_params ) && array_key_exists( 'value', $setting_params ) ) {
                    $this->set_post_value( $setting_id, $setting_params['value'] );}
            }
            $post_values = $this->unsanitized_post_values(['exclude_changeset' => true,'exclude_post_data' => false,]);
            $this->add_dynamic_settings( array_keys( $post_values ) );
            $changed_setting_ids = [];
            foreach ( $post_values as $setting_id => $setting_value ) {
                $setting = $this->get_setting( $setting_id );
                if ( $setting && 'theme_mod' === $setting->type ) {
                    $prefixed_setting_id = $this->get_stylesheet() . '::' . $setting->id;
                } else { $prefixed_setting_id = $setting_id;}
                $is_value_changed = (! isset( $existing_changeset_data[ $prefixed_setting_id ] )
                    || ! array_key_exists( 'value', $existing_changeset_data[ $prefixed_setting_id ] )
                    || $existing_changeset_data[ $prefixed_setting_id ]['value'] !== $setting_value);
                if ( $is_value_changed ) { $changed_setting_ids[] = $setting_id;}
            }
            $this->_do_action( 'customize_save_validation_before', $this );
            $validated_values = array_merge( array_fill_keys( array_keys( $args['data'] ), null ), $post_values,$changed_setting_ids);//$changed_setting_ids added?
            $setting_validities = $this->validate_setting_values($validated_values,['validate_capability' => true,'validate_existence' => true,]);
            $invalid_setting_count = count( array_filter( $setting_validities, 'is_wp_error' ) );
            if ( $update_transactionally && $invalid_setting_count > 0 ) {
                $response = ['setting_validities' => $setting_validities,
                    'message' => sprintf( $this->_n( 'Unable to save due to %s invalid setting.', 'Unable to save due to %s invalid settings.', $invalid_setting_count ), $this->_number_format_i18n( $invalid_setting_count ) ),];
                return new TP_Error( 'transaction_fail', '', $response );
            }
            $original_changeset_data = $this->_get_changeset_post_data( $changeset_post_id );
            $data                    = $original_changeset_data;
            if ( $this->_init_error( $data ) ) {$data = [];}
            foreach ( $post_values as $setting_id => $post_value ) {
                if ( ! isset( $args['data'][ $setting_id ] ) ) { $args['data'][ $setting_id ] = [];}
                if ( ! isset( $args['data'][ $setting_id ]['value'] )){ $args['data'][ $setting_id ]['value'] = $post_value;}
            }
            foreach ( $args['data'] as $setting_id => $setting_params ) {
                $setting = $this->get_setting( $setting_id );
                if( ! $setting || ! $setting->check_capabilities()){ continue;}
                if ( isset( $setting_validities[ $setting_id ] ) && $this->_init_error( $setting_validities[ $setting_id ] ) ) {
                    continue;}
                $changeset_setting_id = $setting_id;
                if ( 'theme_mod' === $setting->type ) {
                    $changeset_setting_id = sprintf( '%s::%s', $this->get_stylesheet(), $setting_id );}
                if ( null === $setting_params ) { unset( $data[ $changeset_setting_id ] );}
                else {
                    if ( ! isset( $data[ $changeset_setting_id ])){$data[ $changeset_setting_id ] = [];}
                    $merged_setting_params = array_merge( $data[ $changeset_setting_id ], $setting_params );
                    if ( $data[ $changeset_setting_id ] === $merged_setting_params ){ continue;}
                    $data[ $changeset_setting_id ] = array_merge(
                        $merged_setting_params,['type' => $setting->type,'user_id' => $args['user_id'],'date_modified_gmt' => $this->_current_time( 'mysql', true ),]
                    );
                    if ( empty( $args['starter_content'] ) ) {
                        unset( $data[ $changeset_setting_id ]['starter_content'] );
                    }
                }
            }
            $filter_context = ['uuid' => $this->changeset_uuid(),'title' => $args['title'],
                'status' => $args['status'],'date_gmt' => $args['date_gmt'],'post_id' => $changeset_post_id,
                'previous_data' => $this->_init_error( $original_changeset_data ) ? [] : $original_changeset_data,
                'manager' => $this,];
            $data = $this->_apply_filters( 'customize_changeset_save_data', $data, $filter_context );
            if ( 'publish' === $args['status'] && ! $this->is_theme_active() ) {
                $this->stop_previewing_theme();
                $this->_switch_theme( $this->get_stylesheet() );
                $this->_update_option( 'theme_switched_via_customizer', true );
                $this->start_previewing_theme();
            }
            $post_array = ['post_content' => $this->_tp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ),];
            if ( $args['title'] ) {$post_array['post_title'] = $args['title'];}
            if ( $changeset_post_id ) {$post_array['ID'] = $changeset_post_id;}
            else {
                $post_array['post_type']   = 'customize_changeset';
                $post_array['post_name']   = $this->changeset_uuid();
                $post_array['post_status'] = 'auto-draft';
            }
            if ( $args['status'] ) { $post_array['post_status'] = $args['status'];}
            if ( 'publish' === $args['status'] ) {
                $post_array['post_date_gmt'] = '0000-00-00 00:00:00';
                $post_array['post_date']     = '0000-00-00 00:00:00';
            } elseif ( $args['date_gmt'] ) {
                $post_array['post_date_gmt'] = $args['date_gmt'];
                $post_array['post_date']     = $this->_get_date_from_gmt( $args['date_gmt'] );
            } elseif ( $changeset_post_id && 'auto-draft' === $this->_get_post_status( $changeset_post_id ) ) {
                $post_array['post_date']     = $this->_current_time( 'mysql' );
                $post_array['post_date_gmt'] = '';
            }
            $this->_store_changeset_revision = $allow_revision;
            $this->_add_filter( 'tp_save_post_revision_post_has_changed', array( $this, '_filter_revision_post_has_changed' ), 5, 3 );
            $this->_add_filter( 'tp_insert_post_data', array( $this, 'preserve_insert_changeset_post_content' ), 5, 3 );
            if ( $changeset_post_id ) {
                if ( $args['autosave'] && 'auto-draft' !== $this->_get_post_status( $changeset_post_id ) ) {
                    $this->_add_filter( 'map_meta_cap', array( $this, 'grant_edit_post_capability_for_changeset' ), 10, 4 );
                    $post_array['post_ID']   = $post_array['ID'];
                    $post_array['post_type'] = 'customize_changeset';
                    $r = $this->_tp_create_post_autosave( $this->_tp_slash( $post_array ) );
                    $this->_remove_filter( 'map_meta_cap', array( $this, 'grant_edit_post_capability_for_changeset' ), 10 );
                } else {
                    $post_array['edit_date'] = true; // Prevent date clearing.
                    $r = $this->_tp_update_post( $this->_tp_slash( $post_array ), true );
                    if ( ! empty( $args['user_id'] ) ) {
                        $autosave_draft = $this->_tp_get_post_autosave( $changeset_post_id, $args['user_id'] );
                        if ( $autosave_draft instanceof TP_Post) {$this->_tp_delete_post( $autosave_draft->ID, true );}
                    }
                }
            } else {
                $_r = $this->_tp_insert_post( $this->_tp_slash( $post_array ), true );
                $r = null;
                if($_r instanceof TP_Error ){ $r = $_r; }
                if ( ! $this->_init_error( $r ) ) { $this->__changeset_post_id = $r; }
            }
            $this->_remove_filter( 'tp_insert_post_data', array( $this, 'preserve_insert_changeset_post_content' ), 5 );
            $this->__changeset_data = null;
            $this->_remove_filter( 'tp_save_post_revision_post_has_changed', array( $this, '_filter_revision_post_has_changed' ) );
            $response = ['setting_validities' => $setting_validities,];
            if ( $this->_init_error( $r ) ) {
                $response['changeset_post_save_failure'] = $r->get_error_code();
                return new TP_Error( 'changeset_post_save_failure', '', $response );
            }
            return $response;
        }//2625
        public function preserve_insert_changeset_post_content( $data, $unsanitized_postarr ):string{//not used , $postarr
            if ((isset($data['post_type'], $unsanitized_postarr['post_content']) && 'customize_changeset' === $data['post_type']) ||
                ('revision' === $data['post_type'] && ! empty( $data['post_parent'] ) &&
                    'customize_changeset' === $this->_get_post_type( $data['post_parent'] ))) {
                $data['post_content'] = $unsanitized_postarr['post_content'];}
            return $data;
        }//3023
        public function trash_changeset_post( $post ):string{
            $tpdb = $this->_init_db();
            $post = $this->_get_post( $post );
            if ( ! ( $post instanceof TP_Post ) ){ return $post;}
            $post_id = $post->ID;
            if (! EMPTY_TRASH_DAYS ){return $this->_tp_delete_post( $post_id, true );}
            if ( 'trash' === $this->_get_post_status( $post )){ return false;}
            $check = $this->_apply_filters( 'pre_trash_post', null, $post );
            if ( null !== $check ) {return $check;}
            $this->_do_action( 'tp_trash_post', $post_id );
            $this->_add_post_meta( $post_id, '_tp_trash_meta_status', $post->post_status );
            $this->_add_post_meta( $post_id, '_tp_trash_meta_time', time() );
            $old_status = $post->post_status;
            $new_status = 'trash';
            $tpdb->update( $tpdb->posts, array( 'post_status' => $new_status ), array( 'ID' => $post->ID ) );
            $this->_clean_post_cache( $post->ID );
            $post->post_status = $new_status;
            $this->_tp_transition_post_status( $new_status, $old_status, $post );
            $this->_do_action( "edit_post_{$post->post_type}", $post->ID, $post );
            $this->_do_action( 'edit_post', $post->ID, $post );
            $this->_do_action( "save_post_{$post->post_type}", $post->ID, $post, true );
            $this->_do_action( 'save_post', $post->ID, $post, true );
            $this->_do_action( 'tp_insert_post', $post->ID, $post, true );
            $this->_tp_after_insert_post( $this->_get_post( $post_id ), true, $post );
            $this->_tp_trash_post_comments( $post_id );
            $this->_do_action( 'trashed_post', $post_id );
            return $post;
        }//3055
        public function handle_changeset_trash_request():void{
            if ( ! $this->_is_user_logged_in() ) { $this->_tp_send_json_error( 'unauthenticated' );}
            if ( ! $this->is_preview() ) { $this->_tp_send_json_error( 'not_preview' );}
            if ( ! $this->_check_async_referer( 'trash_customize_changeset', 'nonce', false ) ) {
                $this->_tp_send_json_error(['code' => 'invalid_nonce','message' => $this->__( 'There was an authentication problem. Please reload and try again.' ),]);
            }
            $changeset_post_id = $this->changeset_post_id();
            if ( ! $changeset_post_id ) {
                $this->_tp_send_json_error(['message' => $this->__( 'No changes saved yet, so there is nothing to trash.' ),
                    'code' => 'non_existent_changeset',]);
                return;
            }
            if ( $changeset_post_id ) {
                if ( ! $this->_current_user_can( $this->_get_post_type_object( 'customize_changeset' )->cap->delete_post, $changeset_post_id ) ) {
                    $this->_tp_send_json_error(['code' => 'changeset_trash_unauthorized',
                        'message' => $this->__( 'Unable to trash changes.' ),]);
                }
                $lock_user = (int) $this->_tp_check_post_lock( $changeset_post_id );
                if ( $lock_user && $this->_get_current_user_id() !== $lock_user ) {
                    $this->_tp_send_json_error(['code' => 'changeset_locked',
                        'message' => $this->__( 'Changeset is being edited by other user.' ),
                        'lockUser' => $this->_get_lock_user_data( $lock_user ),]);
                }
            }
            if ( 'trash' === $this->_get_post_status( $changeset_post_id ) ) {
                $this->_tp_send_json_error(['message' => $this->__( 'Changes have already been trashed.' ),
                    'code' => 'changeset_already_trashed',]);
                return;
            }
            $r = $this->trash_changeset_post( $changeset_post_id );
            if ( ! ( $r instanceof TP_Post ) ) {
                $this->_tp_send_json_error(['code' => 'changeset_trash_failure',
                    'message' => $this->__( 'Unable to trash changes.' ),]);
            }
            $this->_tp_send_json_success(['message' => $this->__( 'Changes trashed successfully.' ),]);
        }//3123
        public function grant_edit_post_capability_for_changeset( $caps, $cap, $user_id, $args ):string{
            if ( 'edit_post' === $cap && ! empty( $args[0] ) && 'customize_changeset' === $this->_get_post_type( $args[0] ) ) {
                $post_type_obj = $this->_get_post_type_object( 'customize_changeset' );
                $caps          = $this->_map_meta_cap( $post_type_obj->cap->$cap, $user_id );
            }
            return $caps;
        }//3225
        public function set_changeset_lock( $changeset_post_id, $take_over = false ):void{
            if ( $changeset_post_id ) {
                $can_override = ! (bool) $this->_get_post_meta( $changeset_post_id, '_edit_lock', true );
                if ($take_over){ $can_override = true;}
                if ( $can_override ) {
                    $lock = sprintf( '%s:%s', time(), $this->_get_current_user_id() );
                    $this->_update_post_meta( $changeset_post_id, '_edit_lock', $lock );
                } else { $this->refresh_changeset_lock( $changeset_post_id );}
            }
        }//3241
        public function refresh_changeset_lock( $changeset_post_id ):void{
            if ( ! $changeset_post_id ) {return;}
            $lock = $this->_get_post_meta( $changeset_post_id, '_edit_lock', true );
            $lock = explode( ':', $lock );
            if ( $lock && ! empty( $lock[1] ) ) {
                $user_id         = (int) $lock[1];
                $current_user_id = $this->_get_current_user_id();
                if ( $user_id === $current_user_id ) {
                    $lock = sprintf( '%s:%s', time(), $user_id );
                    $this->_update_post_meta( $changeset_post_id, '_edit_lock', $lock );
                }
            }
        }//3265
        public function add_customize_screen_to_heartbeat_settings( $settings ):string{
            if ( 'customize.php' === $this->tp_pagenow ){$settings['screenId'] = 'customize';}
            return $settings;
        }//3289
        protected function _get_lock_user_data( $user_id ):array{
            if ( ! $user_id ) { return null;}
            $lock_user = $this->_get_user_data( $user_id );
            if ( ! $lock_user ){ return null;}
            return ['id' => $lock_user->ID,'name' => $lock_user->display_name,
                'avatar' => $this->_get_avatar_url( $lock_user->ID, array( 'size' => 128 ) ),];
        }//3305
        public function check_changeset_lock_with_heartbeat( $response, $data, $screen_id ):string{
            if ( isset( $data['changeset_uuid'] ) ) { $changeset_post_id = $this->find_changeset_post_id( $data['changeset_uuid'] );}
            else {$changeset_post_id = $this->changeset_post_id();}
            if (array_key_exists( 'check_changeset_lock', $data ) && 'customize' === $screen_id && $changeset_post_id
                && $this->_current_user_can( $this->_get_post_type_object( 'customize_changeset' )->cap->edit_post, $changeset_post_id )){
                $lock_user_id = $this->_tp_check_post_lock( $changeset_post_id );
                if ( $lock_user_id ) { $response['customize_changeset_lock_user'] = $this->_get_lock_user_data( $lock_user_id );}
                else { $this->refresh_changeset_lock( $changeset_post_id );}
            }
            return $response;
        }//3330
        public function handle_override_changeset_lock_request():void{
            if(!$this->is_preview()){$this->_tp_send_json_error('not_preview',400);}
            if ( ! $this->_check_async_referer( 'customize_override_changeset_lock', 'nonce', false ) ) {
                $this->_tp_send_json_error(['code' => 'invalid_nonce', 'message' => $this->__( 'Security check failed.' ),]);
            }
            $changeset_post_id = $this->changeset_post_id();
            if ($changeset_post_id === null) {
                $this->_tp_send_json_error(['code' => 'no_changeset_found_to_take_over',
                    'message' => $this->__( 'No changeset found to take over' ),]);
            }
            if ( ! $this->_current_user_can( $this->_get_post_type_object( 'customize_changeset' )->cap->edit_post, $changeset_post_id ) ) {
                $this->_tp_send_json_error(['code' => 'cannot_remove_changeset_lock',
                    'message' => $this->__( 'Sorry, you are not allowed to take over.' ),]);
            }
            $this->set_changeset_lock( $changeset_post_id, true );
            $this->_tp_send_json_success( 'changeset_taken_over' );
        }//3362
        protected function _filter_revision_post_has_changed( $post_has_changed, $last_revision, $post ):bool{
            // not needed unset( $last_revision ); $last_revision added to condition below
            if ($last_revision && 'customize_changeset' === $post->post_type ) {
                $post_has_changed = $this->_store_changeset_revision;}
            return $post_has_changed;
        }//3421
        protected function _publish_changeset_values( $changeset_post_id ):bool{
            $tpdb = $this->_init_db();
            $publishing_changeset_data = $this->_get_changeset_post_data( $changeset_post_id );
            if ( $this->_init_error( $publishing_changeset_data ) ) {
                return $publishing_changeset_data;}
            $changeset_post = $this->_get_post( $changeset_post_id );
            $previous_changeset_post_id = $this->__changeset_post_id;
            $this->__changeset_post_id   = $changeset_post_id;
            $previous_changeset_uuid    = $this->__changeset_uuid;
            $this->__changeset_uuid      = $changeset_post->post_name;
            $previous_changeset_data    = $this->__changeset_data;
            $this->__changeset_data      = $publishing_changeset_data;
            $setting_user_ids   = [];
            $theme_mod_settings = [];
            $namespace_pattern  = '/^(?P<stylesheet>.+?)::(?P<setting_id>.+)$/';
            $matches            = [];
            foreach ( $this->__changeset_data as $raw_setting_id => $setting_params ) {
                $actual_setting_id    = null;
                $is_theme_mod_setting = ( isset( $setting_params['value'], $setting_params['type'] )
                    && 'theme_mod' === $setting_params['type'] && preg_match( $namespace_pattern, $raw_setting_id, $matches ));
                if ( $is_theme_mod_setting ) {
                    if(!isset( $theme_mod_settings[ $matches['stylesheet']])){
                        $theme_mod_settings[ $matches['stylesheet']] = [];}
                    $theme_mod_settings[ $matches['stylesheet'] ][ $matches['setting_id'] ] = $setting_params;
                    if ($this->get_stylesheet() === $matches['stylesheet']){
                        $actual_setting_id = $matches['setting_id']; }
                } else { $actual_setting_id = $raw_setting_id;}
                if ( $actual_setting_id && isset( $setting_params['user_id'] ) ) {
                    $setting_user_ids[ $actual_setting_id ] = $setting_params['user_id'];
                }
            }
            $changeset_setting_values = $this->unsanitized_post_values(
                ['exclude_post_data' => true,'exclude_changeset' => false,]);
            $changeset_setting_ids    = array_keys( $changeset_setting_values );
            $this->add_dynamic_settings( $changeset_setting_ids );
            $this->_do_action( 'customize_save', $this );
            $original_setting_capabilities = array();
            foreach ( $changeset_setting_ids as $setting_id ) {
                $setting = $this->get_setting( $setting_id );
                if ( $setting && ! isset( $setting_user_ids[ $setting_id ] ) ) {
                    $original_setting_capabilities[ $setting->id ] = $setting->capability;
                    $setting->capability = 'exist';
                }
            }
            $original_user_id = $this->_get_current_user_id();
            foreach ( $changeset_setting_ids as $setting_id ) {
                $setting = $this->get_setting( $setting_id );
                if ( $setting ) {
                   if ( isset( $setting_user_ids[ $setting_id ] ) ) {
                        $this->_tp_set_current_user( $setting_user_ids[ $setting_id ] );
                    } else { $this->_tp_set_current_user( $original_user_id );}
                    $setting->save();
                }
            }
            $this->_tp_set_current_user( $original_user_id );
            if ( $this->_did_action( 'switch_theme' ) ) {
                $other_theme_mod_settings = $theme_mod_settings;
                unset( $other_theme_mod_settings[ $this->get_stylesheet() ] );
                $this->_update_stashed_theme_mod_settings( $other_theme_mod_settings );
            }
            $this->_do_action( 'customize_save_after', $this );
            foreach ( $original_setting_capabilities as $setting_id => $capability ) {
                $setting = $this->get_setting( $setting_id );
                if ( $setting ) { $setting->capability = $capability;}
            }
            $this->__changeset_data    = $previous_changeset_data;
            $this->__changeset_post_id = $previous_changeset_post_id;
            $this->__changeset_uuid    = $previous_changeset_uuid;
            $revisions = $this->_tp_get_post_revisions( $changeset_post_id, array( 'check_enabled' => false ) );
            foreach ( $revisions as $revision ) {
                if ( false !== strpos( $revision->post_name, "{$changeset_post_id}-autosave" ) ) {
                    $tpdb->update($tpdb->posts,['post_status' => 'auto-draft','post_type' => 'customize_changeset',
                            'post_name' => $this->_tp_generate_uuid4(),'post_parent' => 0,],['ID' => $revision->ID,]);
                    $this->_clean_post_cache( $revision->ID );
                }
            }
            return true;
        }//3450
        public function publish_changeset_values( $changeset_post_id ):bool{
            return $this->_publish_changeset_values( $changeset_post_id );
        }
        protected function _update_stashed_theme_mod_settings( $inactive_theme_mod_settings ):string{
            $stashed_theme_mod_settings = $this->_get_option( 'customize_stashed_theme_mods' );
            if ( empty( $stashed_theme_mod_settings )){ $stashed_theme_mod_settings = [];}
            unset( $stashed_theme_mod_settings[ $this->get_stylesheet() ] );
            foreach ( $inactive_theme_mod_settings as $stylesheet => $theme_mod_settings ) {
                if (!isset( $stashed_theme_mod_settings[ $stylesheet ])){
                    $stashed_theme_mod_settings[ $stylesheet ] = [];}
                $stashed_theme_mod_settings[ $stylesheet ] = array_merge(
                    $stashed_theme_mod_settings[ $stylesheet ],$theme_mod_settings);
            }
            $autoload = false;
            $result   = $this->_update_option( 'customize_stashed_theme_mods', $stashed_theme_mod_settings, $autoload );
            if ( ! $result ) { return false;}
            return $stashed_theme_mod_settings;
        }//3627
        public function refresh_nonces():void{
            if ( ! $this->is_preview() ) {$this->_tp_send_json_error( 'not_preview' );}
            $this->_tp_send_json_success( $this->get_nonces() );
        }//3661
        public function handle_dismiss_autosave_or_lock_request():void{
            if ( ! $this->_is_user_logged_in() ) { $this->_tp_send_json_error( 'unauthenticated', 401 );}
            if ( ! $this->is_preview() ) { $this->_tp_send_json_error( 'not_preview', 400 );}
            if ( ! $this->_check_async_referer( 'customize_dismiss_autosave_or_lock', 'nonce', false ) ) {
                $this->_tp_send_json_error( 'invalid_nonce', 403 );}
            $changeset_post_id = $this->changeset_post_id();
            $dismiss_lock      = ! empty( $_POST['dismiss_lock'] );
            $dismiss_autosave  = ! empty( $_POST['dismiss_autosave'] );
            if ( $dismiss_lock ) {
                if ( $changeset_post_id === null && ! $dismiss_autosave ) {
                    $this->_tp_send_json_error( 'no_changeset_to_dismiss_lock',404);}
                if (! $dismiss_autosave && ! $this->_current_user_can( $this->_get_post_type_object( 'customize_changeset' )->cap->edit_post, $changeset_post_id )) {
                    $this->_tp_send_json_error( 'cannot_remove_changeset_lock', 403);}
                $this->_delete_post_meta( $changeset_post_id, '_edit_lock' );
                if ( ! $dismiss_autosave ) { $this->_tp_send_json_success( 'changeset_lock_dismissed' );}
            }
            if ( $dismiss_autosave ) {
                if ( $changeset_post_id === null || 'auto-draft' === $this->_get_post_status( $changeset_post_id ) ) {
                    $dismissed = $this->_dismiss_user_auto_draft_changesets();
                    if ( $dismissed > 0 ) {$this->_tp_send_json_success( 'auto_draft_dismissed' );}
                    else {$this->_tp_send_json_error( 'no_auto_draft_to_delete', 404 );}
                } else {
                    $revision = $this->_tp_get_post_autosave( $changeset_post_id, $this->_get_current_user_id() );
                    if ( $revision instanceof TP_Post) {
                        if ( ! $this->_current_user_can( $this->_get_post_type_object( 'customize_changeset' )->cap->delete_post, $changeset_post_id ) ) {
                            $this->_tp_send_json_error( 'cannot_delete_autosave_revision', 403 );}
                        if ( ! $this->_tp_delete_post( $revision->ID, true ) ) {
                            $this->_tp_send_json_error( 'autosave_revision_deletion_failure', 500 );}
                        else {$this->_tp_send_json_success( 'autosave_revision_deleted' );}
                    } else {$this->_tp_send_json_error( 'no_autosave_revision_to_delete', 404 );}
                }
            }
            $this->_tp_send_json_error( 'unknown_error', 500 );
        }//3674
        public function add_setting( $id,array ...$args):array{
            if ( $id instanceof TP_Customize_Setting ) { $setting = $id;}
            else {
                $class = 'TP_Customize_Setting';
                $args = $this->_apply_filters( 'customize_dynamic_setting_args', $args, $id );
                $class = $this->_apply_filters( 'customize_dynamic_setting_class', $class, $id, $args );
                $setting = new $class( $this, $id, $args );
            }
            $this->_settings[ $setting->id ] = $setting;
            return $setting;
        }//3752
        public function add_dynamic_settings( $setting_ids ):array{
            $new_settings = [];
            foreach ( $setting_ids as $setting_id ) {
                if ( $this->get_setting( $setting_id)){ continue;}
                $setting_args  = false;
                $setting_class = 'TP_Customize_Setting';
                //$setting_class = $this->_tp_loading_dynamic_classes('settings_class',TP_NS_CORE_LIBS.'Customs\\','TP_Customize_Setting');//todo for later!
                $setting_args = $this->_apply_filters( 'customize_dynamic_setting_args', $setting_args, $setting_id );
                if ( false === $setting_args ){ continue;}
                $setting_class = $this->_apply_filters( 'customize_dynamic_setting_class', $setting_class, $setting_id, $setting_args );
                $setting = new $setting_class( $this, $setting_id, $setting_args );
                $this->add_setting( $setting );
                $new_settings[] = $setting;
            }
            return $new_settings;
        }//3785
        public function get_setting( $id ) {
            return $this->_settings[$id] ?? false;
        }//3840
        public function remove_setting( $id ):void {
            unset( $this->_settings[ $id ] );
        }//3855
        public function add_panel( $id,array ...$args):string {
            if ( $id instanceof TP_Customize_Panel ){$panel = $id;}
            else {$panel = new TP_Customize_Panel( $this, $id, $args );}
            $this->_panels[ $panel->id ] = $panel;
            return $panel;
        }//3873
        public function get_panel( $id ):bool {
            if ( isset( $this->_panels[ $id ] ) )
                return $this->_panels[ $id ];
            return false;
        }//3892
        public function remove_panel( $id ):void{//todo might not be needed
            if ( in_array( $id, $this->_components, true ) ) {
                $this->_doing_it_wrong(
                    __METHOD__,
                    sprintf($this->__( 'Removing %1$s manually will cause PHP warnings. Use the %2$s filter instead.' ),
                        $id,sprintf("<a href='%s'>%s</a>",
                            $this->_esc_url( 'https://developer.wordpress.org/reference/hooks/customize_loaded_components/' ),
                            '<code>customize_loaded_components</code>')), '0.0.1');
            }
            unset( $this->_panels[ $id ] );
        }//3907
        public function register_panel_type( $panel ):void {
            $this->_registered_panel_types[] = $panel;
        }//3939
        public function render_panel_templates():void{
            foreach ( $this->_registered_panel_types as $panel_type ) {
                $panel = new $panel_type( $this, 'temp', [] );
                if($panel instanceof TP_Customize_Section){$panel->print_template();}
            }
        }//3948
        public function add_section( $id,array ...$args):string{
            if ($id instanceof TP_Customize_Section){$section = $id;}
            else { $section = new TP_Customize_Section( $this, $id, $args );}
            $this->_sections[ $section->id ] = $section;
            return $section;
        }//3969
        public function get_section( $id ):bool {
            if ( isset( $this->_sections[ $id ] ) )
                return $this->_sections[ $id ];
            return false;
        }//3988
        public function remove_section( $id ):void {
            unset( $this->_sections[ $id ] );
        }//4003
        public function register_section_type( $section ):void {
            $this->_registered_section_types[] = $section;
        }//4018
        public function render_section_templates():void{
            foreach ( $this->_registered_section_types as $section_type ) {
                $section = new $section_type( $this, 'temp', array() );
                if($section instanceof TP_Customize_Section){$section->print_template();}
            }
        }//4027
        public function add_control( $id,array ...$args):string{
            if ( $id instanceof TP_Customize_Control ) {$control = $id;}
            else { $control = new TP_Customize_Control( $this, $id, $args );}
            $this->_controls[ $control->id ] = $control;
            return $control;
        }//4048
        public function get_control( $id ) {
            return $this->_controls[$id] ?? false;
        }//4067
        public function register_control_type( $control ):void {
            $this->_registered_control_types[] = $control;
        }//4096
        public function get_render_control_templates():string{return 'todo';}//4105
        public function render_control_templates():void{
            echo $this->get_render_control_templates();
        }//4105
        public function prepare_controls():void{
            $controls       = [];
            $this->_controls = $this->_tp_list_sort($this->_controls,['priority' => 'ASC','instance_number' => 'ASC',],'ASC',true);
            foreach ( $this->_controls as $id => $control ) {
                if ( ! isset( $this->_sections[ $control->section ] ) || ! $control->check_capabilities() ) {
                    continue;}
                $this->_sections[ $control->section ]->controls[] = $control;
                $controls[ $id ]                                 = $control;
            }
            $this->_controls = $controls;
            $this->_sections = $this->_tp_list_sort($this->_sections,['priority' => 'ASC', 'instance_number' => 'ASC',],'ASC',true);
            $sections = [];
            foreach ( $this->_sections as $section ) {
                if ( ! $section->check_capabilities()){continue;}
                $section->controls = $this->_tp_list_sort(
                    $section->controls,['priority' => 'ASC','instance_number' => 'ASC',]);
                if ( ! $section->panel ) { $sections[ $section->id ] = $section;}
                else if ( isset( $this->_panels [ $section->panel ] ) ) {
                    $this->_panels[ $section->panel ]->sections[ $section->id ] = $section;
                }
            }
            $this->_sections = $sections;
            $this->_panels = $this->_tp_list_sort( $this->_panels,['priority' => 'ASC','instance_number' => 'ASC',],'ASC', true);
            $panels = [];
            foreach ( $this->_panels as $panel ) {
                if ( ! $panel->check_capabilities()){continue;}
                $panel->sections = $this->_tp_list_sort($panel->sections,['priority'=> 'ASC','instance_number' => 'ASC',],'ASC',true);
                $panels[ $panel->id ] = $panel;
            }
            $this->_panels = $panels;
            $this->_containers = array_merge( $this->_panels, $this->_sections );
            $this->_containers = $this->_tp_list_sort( $this->_containers,['priority' => 'ASC','instance_number' => 'ASC',],'ASC',true);
        }//4399
        public function enqueue_control_scripts():void{
            foreach ( $this->_controls as $control ){ $control->enqueue();}
            if ( ! $this->_is_multisite() && ( $this->_current_user_can( 'install_themes' ) || $this->_current_user_can( 'update_themes' ) || $this->_current_user_can( 'delete_themes' ) ) ) {
                $this->tp_enqueue_script( 'updates' );
                $this->tp_localize_script('updates','_tpUpdatesItemCounts',['totals' => $this->_tp_get_update_data(),]);
            }
        }//4507
        public function is_ios():bool{
            return $this->_tp_is_mobile() && preg_match( '/iPad|iPod|iPhone/', $_SERVER['HTTP_USER_AGENT'] );
        }//4531
        public function get_document_title_template():string{
            if ( $this->is_theme_active() ) { $document_title_tmpl = $this->__( 'Customize: %s' );} else {
                $document_title_tmpl = $this->__('Live Preview: %s');}
            $document_title_tmpl = html_entity_decode( $document_title_tmpl, ENT_QUOTES, 'UTF-8' ); // Because exported to JS and assigned to document.title.
            return $document_title_tmpl;
        }//4542
        public function set_preview_url( $preview_url ):void{
            $preview_url       = $this->_esc_url_raw( $preview_url );
            $this->_preview_url = $this->_tp_validate_redirect( $preview_url, $this->_home_url( '/' ) );
        }//4563
        public function get_preview_url():string{
            if(empty($this->preview_url)){$preview_url = $this->_home_url( '/' );}
            else {$preview_url = $this->preview_url;}
            return $preview_url;
        }//4575
        public function is_cross_domain():string{
            $admin_origin = $this->_tp_parse_url( $this->_admin_url() );
            $home_origin  = $this->_tp_parse_url( $this->_home_url() );
            $cross_domain = ( strtolower( $admin_origin['host'] ) !== strtolower( $home_origin['host'] ) );
            return $cross_domain;
        }//4591
        public function get_allowed_urls():array{
            $allowed_urls = [$this->_home_url( '/' )];
            if ( $this->_is_ssl() && ! $this->is_cross_domain() ) {$allowed_urls[] = $this->_home_url( '/', 'https' );}
            $allowed_urls = array_unique( $this->_apply_filters( 'customize_allowed_urls', $allowed_urls ) );
            return $allowed_urls;
        }//4612
        public function get_messenger_channel() {
            return $this->_messenger_channel;
        }//4638
        public function set_return_url( $return_url ):void{
            $return_url       = $this->_esc_url_raw( $return_url );
            $return_url       = $this->_remove_query_arg( $this->_tp_removable_query_args(), $return_url );
            $return_url       = $this->_tp_validate_redirect( $return_url );
            $this->_return_url = $return_url;
        }//4651
        public function get_return_url():string{
            $referer = $this->_tp_get_referer();
            $excluded_referer_basenames = ['customize.php', 'tp-login.php'];
            if ( $this->_return_url ) {$return_url = $this->_return_url;}
            elseif ( $referer && ! in_array( $this->_tp_basename( parse_url( $referer, PHP_URL_PATH ) ), $excluded_referer_basenames, true ) ) {
                $return_url = $referer;
            } elseif ( $this->_preview_url ) { $return_url = $this->_preview_url;}
            else {$return_url = $this->_home_url( '/' );}
            $return_url_basename = $this->_tp_basename( parse_url( $this->_return_url, PHP_URL_PATH ) );
            $return_url_query    = parse_url( $this->_return_url, PHP_URL_QUERY );
            if ( 'themes.php' === $return_url_basename && $return_url_query ) {
                parse_str( $return_url_query, $query_vars );
                if ( isset( $query_vars['page'] ) && ! isset( $this->_registered_pages[ "appearance_page_{$query_vars['page']}" ] ) ) {
                    $return_url = $this->_admin_url( 'themes.php' );}
            }
            return $return_url;
        }//4667
        public function set_autofocus( $autofocus ):void{
            $this->_autofocus = array_filter( $this->_tp_array_slice_assoc( $autofocus,['panel', 'section', 'control']), 'is_string' );
        }//4714
        public function get_autofocus():array {
            return $this->_autofocus;
        }//4711
        public function get_nonces():array{
            $nonces = ['save' => $this->_tp_create_nonce( 'save-customize_' . $this->get_stylesheet() ),
                'preview' => $this->_tp_create_nonce( 'preview-customize_' . $this->get_stylesheet() ),
                'switch_themes' => $this->_tp_create_nonce( 'switch_themes' ),
                'dismiss_autosave_or_lock' => $this->_tp_create_nonce( 'customize_dismiss_autosave_or_lock' ),
                'override_lock' => $this->_tp_create_nonce( 'customize_override_changeset_lock' ),
                'trash' => $this->_tp_create_nonce( 'trash_customize_changeset' ),];
            $nonces = $this->_apply_filters( 'customize_refresh_nonces', $nonces, $this );
            return $nonces;
        }//4742
        public function get_customize_pane_settings():string{
            $login_url = $this->_add_query_arg(['interim-login' => 1,'customize-login' => 1,],$this->_tp_login_url());
            foreach ( array_keys( $this->unsanitized_post_values() ) as $setting_id ) {
                $setting = $this->get_setting( $setting_id );
                if ( $setting ) {$setting->dirty = true;}
            }
            $autosave_revision_post  = null;
            $autosave_autodraft_post = null;
            $changeset_post_id       = $this->changeset_post_id();
            if ( ! $this->_saved_starter_content_changeset && ! $this->autosaved() ) {
                if ( $changeset_post_id ) {
                    if ( $this->_is_user_logged_in() ) {
                        $autosave_revision_post = $this->_tp_get_post_autosave( $changeset_post_id, $this->_get_current_user_id() );
                    }
                } else {
                    $autosave_autodraft_posts = $this->_get_changeset_posts(
                        ['posts_per_page' => 1,'post_status' => 'auto-draft','exclude_restore_dismissed' => true,]
                    );
                    if ( ! empty( $autosave_autodraft_posts ) ) {
                        $autosave_autodraft_post = array_shift( $autosave_autodraft_posts );
                    }
                }
            }
            $current_user_can_publish = $this->_current_user_can( $this->_get_post_type_object( 'customize_changeset' )->cap->publish_posts );
            $status_choices = [];
            if ( $current_user_can_publish ) {
                $status_choices[] = ['status' => 'publish','label'=> $this->__( 'Publish' ),];
            }
            $status_choices[] = ['status' => 'draft','label' => $this->__( 'Save Draft' ),];
            if ( $current_user_can_publish ) {
                $status_choices[] = ['status' => 'future',
                    'label'  => $this->_x( 'Schedule','customizer changeset action/button label' ),];
            }
            $changeset_post = null;
            if ( $changeset_post_id ) {$changeset_post = $this->_get_post( $changeset_post_id );}
            $current_time = $this->_current_time( 'mysql', false );
            $initial_date = $current_time;
            if ( $changeset_post ) {
                $initial_date = $this->_get_the_time( 'Y-m-d H:i:s', $changeset_post->ID );
                if ( $initial_date < $current_time ){$initial_date = $current_time;}
            }
            $lock_user_id = false;
            if ( $this->changeset_post_id() ) {$lock_user_id = $this->_tp_check_post_lock( $this->changeset_post_id());}
            $settings = ['changeset' => ['uuid' => $this->changeset_uuid(),'branching' => $this->branching(),
                    'autosaved' => $this->autosaved(),'hasAutosaveRevision' => $autosave_revision_post !== null,
                    'latestAutoDraftUuid' => $autosave_autodraft_post ? $autosave_autodraft_post->post_name : null,
                    'status' => $changeset_post ? $changeset_post->post_status : '','currentUserCanPublish' => $current_user_can_publish,
                    'publishDate' => $initial_date,'statusChoices' => $status_choices,'lockUser' => $lock_user_id ? $this->_get_lock_user_data( $lock_user_id ) : null,],
                'initialServerDate' => $current_time,'dateFormat' => $this->_get_option( 'date_format' ),'timeFormat' => $this->_get_option( 'time_format' ),
                'initialServerTimestamp' => floor( microtime( true ) * 1000 ),'initialClientTimestamp' => -1,
                'timeouts' => ['windowRefresh' => 250,'changesetAutoSave' => AUTOSAVE_INTERVAL * 1000,
                    'keepAliveCheck' => 2500,'reflowPaneContents' => 100,'previewFrameSensitivity' => 2000,],
                'theme' => ['stylesheet' => $this->get_stylesheet(),'active' => $this->is_theme_active(),
                    '_canInstall' => $this->_current_user_can( 'install_themes' ),],
                'url' => ['preview' => $this->_esc_url_raw( $this->get_preview_url()),'return' => $this->_esc_url_raw( $this->get_return_url() ),
                    'parent' => $this->_esc_url_raw( $this->_admin_url() ),'activated' => $this->_esc_url_raw( $this->_home_url( '/' ) ),
                    'async' => $this->_esc_url_raw( $this->_admin_url( 'admin-sync.php', 'relative' ) ),
                    'allowed' => array_map( 'esc_url_raw', $this->get_allowed_urls() ),'isCrossDomain' => $this->is_cross_domain(),
                    'home' => $this->_esc_url_raw( $this->_home_url( '/' ) ),'login' => $this->_esc_url_raw( $login_url ),],
                'browser' => ['mobile' => $this->_tp_is_mobile(),'ios' => $this->is_ios(),],
                'panels' => [],'sections' => [],'nonce' => $this->get_nonces(),'autofocus' => $this->get_autofocus(),
                'documentTitleTmpl' => $this->get_document_title_template(),'previewableDevices' => $this->get_previewable_devices(),
                'l10n' => ['confirmDeleteTheme'   => $this->__( 'Are you sure you want to delete this theme?' ),
                    'themeSearchResults' => $this->__( '%d themes found' ),'announceThemeCount' => $this->__( 'Displaying %d themes' ),'announceThemeDetails' => $this->__( 'Showing details for theme: %s' ),],];
            $filesystem_method = $this->_get_filesystem_method();
            $filesystem_credentials_are_stored = $this->_get_request_filesystem_credentials( $this->_self_admin_url() );
            if ( 'direct' !== $filesystem_method && ! $filesystem_credentials_are_stored ) {
                $settings['theme']['_filesystemCredentialsNeeded'] = true;
            }
            foreach ( $this->sections() as $id => $section ) {
                if ( $section->check_capabilities() ) { $settings['sections'][ $id ] = $section->json();}
            }
            foreach ( $this->panels() as $panel_id => $panel ) {
                if ( $panel->check_capabilities() ) {
                    $settings['panels'][ $panel_id ] = $panel->json();
                    foreach ( $panel->sections as $section_id => $section ) {
                        if ($section instanceof TP_Customize_Panel && $section->check_capabilities() ) {
                            $settings['sections'][ $section_id ] = $section->json();}
                    }
                }
            }
            ob_start();
            ?>
            <script>
                let _tpCustomizeSettings = <?php echo $this->_tp_json_encode( $settings ); ?>;
                _tpCustomizeSettings.initialClientTimestamp = Date.now(); //instead of _.now()
                _tpCustomizeSettings.controls = {};
                _tpCustomizeSettings.settings = {};
                <?php
                $script  = "(function ( s ){\n";
                foreach ( $this->settings() as $setting ) {
                    if ( $setting->check_capabilities() ){
                        $script .= sprintf("s[%s] = %s;\n",$this->_tp_json_encode($setting->id),$this->_tp_json_encode( $setting->json()));
                    }
                }
                foreach ( $this->controls() as $control ) {
                    if ( $control->check_capabilities() ) {
                        $script .= sprintf("c[%s] = %s;\n",$this->_tp_json_encode($control->id),$this->_tp_json_encode( $control->json()));
                    }
                }
                $script .= "})( _tpCustomizeSettings.settings );\n";
                echo $script;
                ?>
            </script>
            <?php
            return ob_get_clean();
        }//4771
        public function customize_pane_settings():void{}//4771
        public function get_previewable_devices():array{
            $devices = ['desktop' => ['label' => $this->__( 'Enter desktop preview mode' ),'default' => true,],
                'tablet'  => ['label' => $this->__( 'Enter tablet preview mode' ),],
                'mobile'  => ['label' => $this->__( 'Enter mobile preview mode' ),],];
            $devices = $this->_apply_filters( 'customize_previewable_devices', $devices );
            return $devices;
        }//4988
        public function register_controls():void{
            $this->add_panel( new TP_Customize_Themes_Panel($this,'themes', ['title' => $this->theme()->display( 'Name' ),
                'description' => ("<p>{$this->__('Looking for a theme?.')}</p>"."<p>{$this->__('TODO')}</p>"),
                'capability' => 'switch_themes','priority' => 0,]));
            $this->add_section( new TP_Customize_Themes_Section($this,'installed_themes',
                ['title' => $this->__('Installed themes'),'action' => 'installed',
                    'capability' => 'switch_themes','panel' => 'themes','priority' => 0,]));
            if ( ! $this->_is_multisite() ) {
                $this->add_section(new TP_Customize_Themes_Section($this,'tp_org_themes',['title' => $this->__( 'TODO' ),
                    'action' => 'tp_org','filter_type' => 'remote','capability' => 'install_themes','panel' => 'themes','priority' => 5,]));
            }
            $this->add_setting( new TP_Customize_Filter_Setting( $this, 'active_theme',['capability' => 'switch_themes',]));
            $this->add_section('title_tagline',['title' => $this->__( 'Site Identity' ),'priority' => 20,]);
            $this->add_setting('blogname',['default' => $this->_get_option( 'blogname' ),'type' => 'option','capability' => 'manage_options',]);
            $this->add_control('blogname', ['label' => $this->__( 'Site Title' ),'section' => 'title_tagline',]);
            $this->add_setting('blogdescription',['default' => $this->_get_option( 'blogdescription' ),'type' => 'option','capability' => 'manage_options',]);
            $this->add_control('blogdescription',['label' => $this->__( 'Tagline' ),'section' => 'title_tagline',]);
            if ( ! $this->_current_theme_supports( 'custom-header', 'header-text' ) ) {
                $this->add_setting( 'header_text',['theme_supports' => ['custom-logo', 'header-text'],
                    'default' => 1,'sanitize_callback' => 'absint',]);
                $this->add_control('header_text',['label' => $this->__( 'Display Site Title and Tagline' ),
                    'section'  => 'title_tagline','settings' => 'header_text','type' => 'checkbox',]);
            }
            $this->add_setting('site_icon',['type' => 'option', 'capability' => 'manage_options','transport' => 'postMessage', ]);
            $this->add_control( new TP_Customize_Site_Icon_Control(
                $this, 'site_icon',['label' => $this->__( 'Site Icon' ),
                'description' => sprintf(
                    '<p>' . $this->__( 'Site Icons are what you see in browser tabs, bookmark bars, and within the TailoredPress mobile apps. Upload one here!' ) . '</p>' .
                    '<p>' . $this->__( 'Site Icons should be square and at least %s pixels.' ) . '</p>',
                    '<strong>512 &times; 512</strong>'),'section' => 'title_tagline','priority' => 60,'height' => 512,'width' => 512,]));
            $this->add_setting('custom_logo',['theme_supports' => ['custom-logo'], 'transport' => 'postMessage',]);
            $custom_logo_args = $this->_get_theme_support( 'custom-logo' );
            $this->add_control(new TP_Customize_Cropped_Image_Control($this,'custom_logo',
                ['label' => $this->__( 'Logo' ),'section' => 'title_tagline',
                    'priority' => 8,'height' => $custom_logo_args[0]['height'] ?? null,
                    'width' => $custom_logo_args[0]['width'] ?? null,
                    'flex_height' => $custom_logo_args[0]['flex-height'] ?? null,
                    'flex_width' => $custom_logo_args[0]['flex-width'] ?? null,
                    'button_labels' => ['select' => $this->__( 'Select logo' ),
                        'change' => $this->__( 'Change logo' ),'remove' => $this->__( 'Remove' ),
                        'default' => $this->__( 'Default' ),'placeholder' => $this->__( 'No logo selected' ),
                        'frame_title' => $this->__( 'Select logo' ),'frame_button' => $this->__( 'Choose logo' ),],
                ]));
            if($this->selective_refresh instanceof TP_Customize_Selective_Refresh){
                $this->selective_refresh->add_partial('custom_logo',['settings' => ['custom_logo'],
                    'selector' => '.custom-logo-link','render_callback' => [$this, '_render_custom_logo_partial'],
                    'container_inclusive' => true,]);
            }
            $this->add_section('colors',['title' => $this->__( 'Colors' ), 'priority' => 40,]);
            $this->add_setting('header_textcolor',
                ['theme_supports' => ['custom-header', 'header-text'],'default' => $this->_get_theme_support( 'custom-header', 'default-text-color' ),
                    'sanitize_callback' => [$this, '_sanitize_header_textcolor'],'sanitize_js_callback' => 'maybe_hash_hex_color',]);
            $this->add_control('display_header_text',['settings' => 'header_textcolor',
                    'label' => $this->__( 'Display Site Title and Tagline' ),'section' => 'title_tagline',
                    'type' => 'checkbox','priority' => 40,]);
            $this->add_control( new TP_Customize_Color_Control($this,'header_textcolor',
                    ['label' => $this->__( 'Header Text Color' ),'section' => 'colors',]));
            $this->add_setting('background_color',['default' => $this->_get_theme_support( 'custom-background', 'default-color' ),
                'theme_supports' => 'custom-background','sanitize_callback' => 'sanitize_hex_color_no_hash',
                'sanitize_js_callback' => 'maybe_hash_hex_color',]);
            $this->add_control(new TP_Customize_Color_Control($this,'background_color',['label' => $this->__( 'Background Color' ),'section' => 'colors',]));
            if ( $this->_current_theme_supports( 'custom-header', 'video' ) ) {
                $title       = $this->__( 'Header Media' );
                $description = "<p>{$this->__( 'If you add a video, the image will be used as a fallback while the video loads.' )}</p>";
                $width  = $this->_abs_int( $this->_get_theme_support( 'custom-header', 'width' ) );
                $height = $this->_abs_int( $this->_get_theme_support( 'custom-header', 'height' ) );
                if ( $width && $height ) {
                    $control_description = sprintf(
                        $this->__( 'Upload your video in %1$s format and minimize its file size for best results. Your theme recommends dimensions of %2$s pixels.' ),
                        '<code>.mp4</code>',sprintf( '<strong>%s &times; %s</strong>', $width, $height ));
                } elseif ( $width ) {
                    $control_description = sprintf($this->__( 'Upload your video in %1$s format and minimize its file size for best results. Your theme recommends a width of %2$s pixels.' ),
                        '<code>.mp4</code>',sprintf( '<strong>%s</strong>', $width ));
                } else {
                    $control_description = sprintf($this->__( 'Upload your video in %1$s format and minimize its file size for best results. Your theme recommends a height of %2$s pixels.' ),
                        '<code>.mp4</code>',sprintf( '<strong>%s</strong>', $height ));
                }
            }else {
                $title = $this->__( 'Header Image' );
                $description = '';
                $control_description = '';
            }
            $this->add_section('header_image',['title' => $title,'description'=> $description,'theme_supports' => 'custom-header','priority'=> 60,]);
            $this->add_setting('header_video',['theme_supports' => ['custom-header','video'],'transport' => 'postMessage',
                    'sanitize_callback' => 'absint','validate_callback' => [$this, '_validate_header_video'],]);
            $this->add_setting('external_header_video',['theme_supports' => ['custom-header','video'],
                    'transport' => 'postMessage','sanitize_callback' => [$this, '_sanitize_external_header_video'],
                    'validate_callback' => [$this, '_validate_external_header_video'],]
            );
            $this->add_setting(
                new TP_Customize_Filter_Setting($this,'header_image',
                    ['default' => sprintf( $this->_get_theme_support( 'custom-header', 'default-image' ), $this->_get_template_directory_uri(), $this->_get_stylesheet_directory_uri() ),
                        'theme_supports' => 'custom-header',]
                )
            );
            $this->add_setting( new TP_Customize_Header_Image_Setting($this,'header_image_data',['theme_supports' => 'custom-header',]));
            if ( $this->_current_theme_supports( 'custom-header', 'video' ) ) {
                $this->get_setting( 'header_image' )->transport= 'postMessage';
                $this->get_setting( 'header_image_data' )->transport = 'postMessage';
            }
            $this->add_control( new TP_Customize_Media_Control($this,'header_video',['theme_supports' => ['custom-header','video'],
                        'label' => $this->__( 'Header Video' ),'description' => $control_description,
                        'section' => 'header_image','mime_type' => 'video','active_callback' => 'is_header_video_active',]));
            $this->add_control('external_header_video', ['theme_supports' => ['custom-header','video'],
                    'type' => 'url','description' => $this->__( 'Or, enter a YouTube URL:' ),'section' => 'header_image','active_callback' => 'is_header_video_active',]
            );
            $this->add_control( new TP_Customize_Header_Image_Control( $this ) );
            $this->selective_refresh->add_partial('custom_header',['selector' => '#tp_custom_header',
                    'render_callback' => 'the_custom_header_markup','settings' => ['header_video','external_header_video','header_image'],
                    'container_inclusive' => true,]);
            $this->add_section('background_image',['title' => $this->__( 'Background Image' ),'theme_supports' => 'custom-background','priority' => 80,]);
            $this->add_setting('background_image', ['default' => $this->_get_theme_support( 'custom-background', 'default-image' ),
                    'theme_supports' => 'custom-background','sanitize_callback' => [$this, '_sanitize_background_setting'],]);
            $this->add_setting(
                new TP_Customize_Background_Image_Setting($this,'background_image_thumb',
                    ['theme_supports'=> 'custom-background', 'sanitize_callback' => [$this, '_sanitize_background_setting'],]));
            $this->add_control( new TP_Customize_Background_Image_Control( $this ) );
            $this->add_setting('background_preset',['default' => $this->_get_theme_support( 'custom-background', 'default-preset' ),
                'theme_supports' => 'custom-background','sanitize_callback' => [$this, '_sanitize_background_setting'],]);
            $this->add_control('background_preset',['label' => $this->_x( 'Preset', 'Background Preset' ),
                    'section' => 'background_image','type' => 'select',
                    'choices' => ['default' => $this->_x( 'Default', 'Default Preset' ),
                        'fill' => $this->__( 'Fill Screen' ),'fit' => $this->__( 'Fit to Screen' ),
                        'repeat' => $this->_x( 'Repeat', 'Repeat Image' ),'custom' => $this->_x( 'Custom', 'Custom Preset' ),],
                ]
            );
            $this->add_setting('background_position_x',['default'=> $this->_get_theme_support( 'custom-background', 'default-position-x' ),
                'theme_supports'=> 'custom-background','sanitize_callback' => [$this, '_sanitize_background_setting'],]);
            $this->add_setting('background_position_y',['default'=> $this->_get_theme_support( 'custom-background', 'default-position-y' ),
                'theme_supports'=> 'custom-background','sanitize_callback' => [$this, '_sanitize_background_setting'],]);
            $this->add_control(
                new TP_Customize_Background_Position_Control(
                    $this,'background_position',['label' => $this->__( 'Image Position' ),'section' => 'background_image',
                        'settings' => ['x' => 'background_position_x','y' => 'background_position_y',], ]));
            $this->add_setting('background_size',['default'=> $this->_get_theme_support( 'custom-background', 'default-size' ),
                    'theme_supports'=> 'custom-background','sanitize_callback' => [$this, '_sanitize_background_setting'],]
            );
            $this->add_control('background_size',['label' => $this->__( 'Image Size' ),'section' => 'background_image',
                    'type' => 'select','choices' => ['auto' => $this->_x( 'Original', 'Original Size' ),'contain' => $this->__( 'Fit to Screen' ),'cover' => $this->__( 'Fill Screen' ),],]);
            $this->add_setting('background_repeat',['default'=> $this->_get_theme_support( 'custom-background', 'default-repeat' ),
                'sanitize_callback' => [$this, '_sanitize_background_setting'],'theme_supports' => 'custom-background',]);
            $this->add_control('background_repeat',['label' => $this->__( 'Repeat Background Image' ),
                    'section' => 'background_image','type' => 'checkbox',]
            );
            $this->add_setting('background_attachment',['default' => $this->_get_theme_support( 'custom-background', 'default-attachment' ),
                'sanitize_callback' => [$this, '_sanitize_background_setting'],'theme_supports' => 'custom-background',]);
            $this->add_control('background_attachment',['label'=> $this->__( 'Scroll with Page' ),'section' => 'background_image','type' => 'checkbox',]);
            if ( $this->_get_theme_support( 'custom-background', 'tp-head-callback' ) === '_custom_background_cb' ) {
                foreach (['color', 'image', 'preset', 'position_x', 'position_y', 'size', 'repeat', 'attachment'] as $prop ) {
                    $this->get_setting( 'background_' . $prop )->transport = 'postMessage';
                }
            }
            $this->add_section('static_front_page',['title' => $this->__( 'Homepage Settings' ),
                'priority' => 120,'description' => $this->__( 'You can choose what&#8217;s displayed on the homepage of your site. It can be posts in reverse chronological order (classic blog), or a fixed/static page. To set a static homepage, you first need to create two Pages. One will become the homepage, and the other will be where your posts are displayed.' ),
                'active_callback' => [$this,'has_published_pages'],]);
            $this->add_setting('show_on_front',['default' => $this->_get_option( 'show_on_front' ),'capability' => 'manage_options','type' => 'option',]);
            $this->add_control('show_on_front',['label' => $this->__( 'Your homepage displays' ),'section' => 'static_front_page','type' => 'radio',
                    'choices' => ['posts' => $this->__('Your latest posts'),'page' => $this->__( 'A static page' ),],]);
            $this->add_setting('page_on_front',['type' => 'option','capability' => 'manage_options',]);
            $this->add_control('page_on_front',['label' => $this->__( 'Homepage' ), 'section' => 'static_front_page',
                'type' => 'dropdown-pages','allow_addition' => true,]);
            $this->add_setting('page_for_posts',['type' => 'option','capability' => 'manage_options',]);
            $this->add_control('page_for_posts',['label' => $this->__( 'Posts page' ),'section' => 'static_front_page','type' => 'dropdown-pages','allow_addition' => true,]);
            $section_description  = '<p>';
            $section_description .= $this->__( 'Add your own CSS code here to customize the appearance and layout of your site.' );
            $section_description .= sprintf("<a href='%1\$s' class='external-link' target='_blank'>%2\$s<span class='screen-reader-text'> %3\$s</span></a>",
                $this->_esc_url($this->__('#todo')),$this->__( 'Learn more about CSS' ),$this->__( '(opens in a new tab)' ));
            $section_description .= '</p>';
            $section_description .= "<p id='editor_keyboard_trap_help_1'>{$this->__( 'When using a keyboard to navigate:' )}</p>";
            $section_description .= '<ul>';
            $section_description .= "<li id='editor_keyboard_trap_help_2' >{$this->__('In the editing area, the Tab key enters a tab character.')}</li>";
            $section_description .= "<li id='editor_keyboard_trap_help_3' >{$this->__('To move away from this area, press the Esc key followed by the Tab key.')}</li>";
            $section_description .= "<li id='editor_keyboard_trap_help_4' >{$this->__('Screen reader users: when in forms mode, you may need to press the Esc key twice.')}</li>";
            $section_description .= '</ul>';
            if ( 'false' !== $this->_tp_get_user_current()->syntax_highlighting ) {
                $section_description .= '<p>';
                $section_description .= sprintf($this->__("The edit field automatically highlights code syntax. You can disable this in your <a href='%1\$s' %2\$s>user profile%3\$s</a> to work in plain text mode."),
                    $this->_esc_url( $this->_get_edit_profile_url() ),"class='external-link' target='_blank'",sprintf("<span class='screen-reader-text'> %s</span>",$this->__( '(opens in a new tab)' )));
                $section_description .= '</p>';
            }
            $section_description .= "<p class='section-description-buttons' >";
            $section_description .= "<button type='button' class='button-link section-description-close' >{$this->__('Close')}</button>";
            $section_description .= "</p>";
            $this->add_section('custom_css',['title' => $this->__('Additional CSS'),'priority' => 200,
                'description_hidden' => true,'description' => $section_description,]);
            $custom_css_setting = new TP_Customize_Custom_CSS_Setting(
                $this,sprintf( 'custom_css[%s]', $this->_get_stylesheet() ),['capability' => 'edit_css', 'default' => '',]);
            $this->add_setting( $custom_css_setting );
            $this->add_control(
                new TP_Customize_Code_Editor_Control(
                    $this,'custom_css',['label' => $this->__( 'CSS code' ),'section' => 'custom_css',
                        'settings' => ['default' => $custom_css_setting->id],'code_type' => 'text/css',
                        'input_attrs' => ['aria-describedby' => 'editor-keyboard-trap-help-1 editor-keyboard-trap-help-2 editor-keyboard-trap-help-3 editor-keyboard-trap-help-4',],
                    ]));
        }//5021
        public function has_published_pages():int{
            $setting = $this->get_setting( 'nav_menus_created_posts' );
            if ( $setting ) {
                foreach ( $setting->value() as $post_id ) {
                    if ( 'page' === $this->_get_post_type( $post_id ) ){
                        return true;}
                }
            }
            return 0 !== count( $this->_get_pages() );
        }//5743
        public function register_dynamic_settings():void{
            $setting_ids = array_keys( $this->unsanitized_post_values() );
            $this->add_dynamic_settings( $setting_ids );
        }//5763
        public function handle_load_themes_request():void{
            $this->_check_async_referer( 'switch_themes', 'nonce' );
            if ( ! $this->_current_user_can( 'switch_themes')){$this->_tp_die( -1 );}
            if(empty($_POST['theme_action'])){$this->_tp_send_json_error( 'missing_theme_action' );}
            $theme_action = $this->_sanitize_key( $_POST['theme_action'] );
            $themes       = [];
            $args         = [];
            if ( !array_key_exists( 'search',$_POST)){$args['search'] = '';}
            else { $args['search'] = $this->_sanitize_text_field( $this->_tp_unslash( $_POST['search'] ) );}
            if ( ! array_key_exists( 'tags', $_POST ) ){$args['tag'] = '';}
            else { $args['tag'] = array_map( 'sanitize_text_field', $this->_tp_unslash( (array) $_POST['tags'] ) );}
            if ( ! array_key_exists( 'page', $_POST )){ $args['page'] = 1;}
            else { $args['page'] = $this->_abs_int( $_POST['page']);}
            if ( 'installed' === $theme_action ) {
                $themes = array( 'themes' => array() );
                foreach ( $this->_tp_prepare_themes_for_js() as $theme ) {
                    $theme['type'] = 'installed';
                    $theme['active'] = ( isset( $_POST['customized_theme'] ) && $_POST['customized_theme'] === $theme['id'] );
                    $themes['themes'][] = $theme;
                }
            } elseif ( 'tp_org' === $theme_action ) {
                if ( ! $this->_current_user_can( 'install_themes' )){$this->_tp_die( -1 );}
                $tp_org_args = ['per_page' => 100,'fields' => ['reviews_url' => true,],];
                $args = array_merge( $tp_org_args, $args );
                if ( '' === $args['search'] && '' === $args['tag'] ) {$args['browse'] = 'new'; }
                $themes = $this->_themes_api( 'query_themes', $args );
                if ( $this->_init_error( $themes )){ $this->_tp_send_json_error();}
                $themes_allowedtags = array_fill_keys(['a','abbr','acronym','code','pre','em','strong','div','p','ul','ol','li','h1','h2','h3','h4','h5','h6','img'], []);
                $themes_allowedtags['a'] = array_fill_keys(['href','title','target'],true );
                $themes_allowedtags['acronym']['title'] = true;
                $themes_allowedtags['abbr']['title']= true;
                $themes_allowedtags['img']= array_fill_keys(['src','class','alt'], true );
                $installed_themes = [];
                $_tp_themes = $this->_tp_get_themes();
                $tp_themes = null;
                if($_tp_themes  instanceof TP_Theme ){ $tp_themes = $_tp_themes;}
                foreach ((array)$tp_themes as $theme ){ $installed_themes[] = $theme->get_stylesheet();}
                $update_php = $this->_network_admin_url( 'update.php?action=install-theme' );
                foreach ((array) $themes->themes as &$theme ) {
                    $theme->install_url = $this->_add_query_arg(
                        ['theme'=> $theme->slug, '_tpnonce' => $this->_tp_create_nonce( 'install-theme_' . $theme->slug ),],$update_php);
                    $theme->name        = $this->_tp_kses( $theme->name, $themes_allowedtags );
                    $theme->version     = $this->_tp_kses( $theme->version, $themes_allowedtags );
                    $theme->description = $this->_tp_kses( $theme->description, $themes_allowedtags );
                    $theme->stars       = $this->_tp_star_rating(['rating' => $theme->rating,'type'=> 'percent','number' => $theme->num_ratings,'echo'=> false,]);
                    $theme->num_ratings = $this->_number_format_i18n( $theme->num_ratings );
                    $theme->preview_url = $this->_set_url_scheme( $theme->preview_url );
                    if ( in_array( $theme->slug, $installed_themes, true ) ) { $theme->type = 'installed';}
                    else { $theme->type = $theme_action;}
                    $theme->active = ( isset( $_POST['customized_theme'] ) && $_POST['customized_theme'] === $theme->slug );
                    $theme->id            = $theme->slug;
                    $theme->screenshot    = array( $theme->screenshot_url );
                    $theme->authorAndUri  = $this->_tp_kses( $theme->author['display_name'], $themes_allowedtags );
                    $theme->compatibleTP  = $this->_tp_is_version_compatible( $theme->requires );
                    $theme->compatiblePHP = $this->_is_php_version_compatible( $theme->requires_php );
                    if ( isset( $theme->parent)){ $theme->parent = $theme->parent['slug'];}
                    else { $theme->parent = false;}
                    unset( $theme->slug, $theme->screenshot_url, $theme->author );
                } // End foreach().
                unset($theme);
            } // End if().
            $themes = $this->_apply_filters( 'customize_load_themes', $themes, $args, $this );
            $this->_tp_send_json_success( $themes );
        }//5773
        protected function _sanitize_header_textcolor( $color ):string{
            if ( 'blank' === $color ) {return 'blank';}
            $color = $this->_sanitize_hex_color_no_hash( $color );
            if( empty( $color )){
                $color = $this->_get_theme_support('custom-header','default-text-color');
            }
            return $color;
        }//5948
        protected function _sanitize_background_setting( $value, $setting ):string{
            if ( 'background_repeat' === $setting->id ) {
                if ( ! in_array( $value,['repeat-x','repeat-y','repeat','no-repeat'], true ) ) {
                    return new TP_Error( 'invalid_value', $this->__( 'Invalid value for background repeat.' ) );
                }
            } elseif ( 'background_attachment' === $setting->id ) {
                if ( ! in_array( $value,['fixed','scroll'], true ) ) {
                    return new TP_Error( 'invalid_value', $this->__( 'Invalid value for background attachment.' ) );
                }
            } elseif ( 'background_position_x' === $setting->id ) {
                if ( ! in_array( $value,['left','center','right'], true ) ) {
                    return new TP_Error( 'invalid_value', $this->__( 'Invalid value for background position X.' ) );
                }
            } elseif ( 'background_position_y' === $setting->id ) {
                if ( ! in_array( $value,['top','center','bottom'], true ) ) {
                    return new TP_Error( 'invalid_value', $this->__( 'Invalid value for background position Y.' ) );
                }
            } elseif ( 'background_size' === $setting->id ) {
                if ( ! in_array( $value,['auto','contain','cover'], true ) ) {
                    return new TP_Error( 'invalid_value', $this->__( 'Invalid value for background size.' ) );}
            } elseif ( 'background_preset' === $setting->id ) {
                if ( ! in_array( $value,['default','fill','fit','repeat','custom'], true ) ) {
                    return new TP_Error( 'invalid_value', $this->__( 'Invalid value for background size.' ) );}
            } elseif ( 'background_image' === $setting->id || 'background_image_thumb' === $setting->id ) {
                $value = empty( $value ) ? '' : $this->_esc_url_raw( $value );
            } else { return new TP_Error( 'unrecognized_setting', $this->__('Unrecognized background setting.'));}
            return $value;
        }//5970
        public function export_header_video_settings( $response, $partials ):string{//not used , $selective_refresh
            if ( isset( $partials['custom_header'] ) ) {
                $response['custom_header_settings'] = $this->_get_header_video_settings();
            }
            return $response;
        }//6013
        protected function _validate_header_video(TP_Error $validity, $value ):string{
            $video = $this->_get_attached_file( $this->_abs_int( $value ) );
            if ( $video ) {
                $size = filesize( $video );
                if ( $size > 8 * MB_IN_BYTES ) {
                    $validity->add('size_too_large',
                        $this->__( 'This video file is too large to use as a header video. Try a shorter video or optimize the compression settings and re-upload a file that is less than 8MB. Or, upload your video to YouTube and link it with the option below.' )
                    );
                }
                if ( '.mp4' !== substr( $video, -4 ) && '.mov' !== substr( $video, -4 ) ) { // Check for .mp4 or .mov format, which (assuming h.264 encoding) are the only cross-browser-supported formats.
                    $validity->add('invalid_file_type',
                        sprintf( $this->__( 'Only %1$s or %2$s files may be used for header video. Please convert your video file and try again, or, upload your video to YouTube and link it with the option below.' ),
                            '<code>.mp4</code>','<code>.mov</code>' ));
                }
            }
            return $validity;
        }//6032
        protected function _validate_external_header_video(TP_Error $validity, $value ):string{
            $video = $this->_esc_url_raw( $value );
            if ($video && !preg_match('#^https?://(?:www\.)?(?:youtube\.com/watch|youtu\.be/)#', $video)) {
                $validity->add( 'invalid_url', $this->__( 'Please enter a valid YouTube URL.' ) );
            }
            return $validity;
        }//6068
        protected function _sanitize_external_header_video( $value ):string{
            return $this->_esc_url_raw( trim( $value ) );
        }//6086
        protected function _render_custom_logo_partial():string {
            return $this->_get_custom_logo();
        }//6105
    }
}else die;