<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-5-2022
 * Time: 12:24
 */
declare(strict_types=1);
namespace TP_Admin\Libs;
use TP_Admin\Traits\_adm_screen;
use TP_Core\Traits\AdminConstructs\_adm_construct_screen;
use TP_Admin\Traits\AdminPost\_adm_post_04;
use TP_Admin\Traits\AdminTemplates\_adm_template_04;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Constructs\_construct_meta;
use TP_Core\Traits\Constructs\_construct_page;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_03;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Methods\_methods_05;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_20;
use TP_Core\Traits\Inits\_init_meta;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Options\_option_02;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_02;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\Taxonomy\_taxonomy_01;
use TP_Core\Traits\Taxonomy\_taxonomy_08;
use TP_Core\Traits\Templates\_template_03;
use TP_Admin\Libs\AdmRenders\render_meta_boxes_preferences;
use TP_Admin\Libs\AdmRenders\render_list_table_columns_preferences;
use TP_Admin\Libs\AdmRenders\render_per_page_options;
use TP_Admin\Libs\AdmRenders\render_screen_layout;
//use TP_Admin\Libs\Modules\render_screen_meta;
use TP_Admin\Libs\AdmRenders\render_view_mode;
use TP_Admin\Libs\AdmRenders\screen_meta_links;
if(ABSPATH){
    final class Adm_Screen{
        use _action_01;
        use _adm_construct_screen;
        use _adm_post_04;
        use _adm_screen;
        use _adm_template_04;
        use _construct_meta;
        use _construct_page;
        use _filter_01;
        use _formats_02,_formats_03,_formats_07,_formats_08;
        use _I10n_01;
        use _init_meta;
        use _methods_05;
        use _methods_08, _methods_10,_methods_20;
        use _option_01;
        use _option_02;
        use _post_01, _post_02,_post_03,_post_04;
        use _taxonomy_01,_taxonomy_08;
        use _template_03;
        private $__columns = 0;
        private $__help_sidebar = '';
        private $__help_tabs = [];
        private $__options = [];
        private $__show_screen_options;
        private $__screen_reader_content = [];
        private $__screen_settings;
        private static $__old_compat_help = [];
        private static $__registry = [];
        public $action;
        public $base;
        public $id;
        protected $in_admin;
        public $is_block_editor = false;
        public $is_network;
        public $is_user;
        public $parent_base;
        public $parent_class;
        public $parent_file;
        public $post_type;
        public $taxonomy;
        //public static $post_type_exists;
        private function __construct() {
            //self::$post_type_exists = $this->_post_type_exists( $hook_name ='' );
        }//429
        public static function get_screen( $hook_name = '' ){
            if ( $hook_name instanceof self ) {return $hook_name;}
            $post_type       = null;
            $taxonomy        = null;
            $in_admin        = false;
            $action          = '';
            $is_block_editor = false;
            if ( $hook_name ) $id = $hook_name;
            else $id = $GLOBALS['hook_suffix'];
            if ( $hook_name && ((new static)->_post_type_exists($hook_name))) {
                $post_type = $id;
                $id        = 'post'; // Changes later. Ends up being $base.
            }else{
                if ($id !== null && '.php' === substr( $id, -4 )){
                    $id = substr( $id, ' ', -4 );
                }
                if ( in_array( $id, array( 'post-new', 'link-add', 'media-new', 'user-new' ), true ) ) {
                    $id     = substr( $id, 0, -4 );
                    $action = 'add';
                }
            }
            if ( ! $post_type && $hook_name ) {
                if ( '_network' === substr( $id, -8 ) ) {
                    $id       = substr( $id, 0, -8 );
                    $in_admin = 'network';
                } elseif ( '_user' === substr( $id, -5 ) ) {
                    $id       = substr( $id, 0, -5 );
                    $in_admin = 'user';
                }
                $id = (new static)->_sanitize_key( $id );
                if ( 'edit_comments' !== $id && 'edit_tags' !== $id && strpos($id, 'edit') === 0 ) {
                    $maybe = substr( $id, 5 );
                    if ( (new static)->_taxonomy_exists( $maybe ) ) {
                        $id       = 'edit_tags';
                        $taxonomy = $maybe;
                    } elseif ( (new static)->_post_type_exists( $maybe ) ) {
                        $id        = 'edit';
                        $post_type = $maybe;
                    }
                }
                if ( ! $in_admin ) $in_admin = 'site';
            }elseif ( defined( 'TP_NETWORK_ADMIN' ) && TP_NETWORK_ADMIN ){
                $in_admin = 'network';
            }elseif ( defined( 'TP_USER_ADMIN' ) && TP_USER_ADMIN ){
                $in_admin = 'user';
            }else {$in_admin = 'site';}
            if ( 'index' === $id ) $id = 'dashboard';
            elseif ('front' === $id) $in_admin = false;
            $base = $id;
            if (!$hook_name){
                if ( isset( $_REQUEST['post_type'] ) )
                    $post_type = (new static)->_post_type_exists( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : false;
                if ( isset( $_REQUEST['taxonomy'] ) )
                    $taxonomy = (new static)->_taxonomy_exists( $_REQUEST['taxonomy'] ) ? $_REQUEST['taxonomy'] : false;
                switch ($base){
                    case 'post':
                        $post_id = null;
                        if ( isset( $_GET['post'] ,$_POST['post_ID']) && (int) $_GET['post'] !== (int) $_POST['post_ID'] )
                            (new static)->_tp_die( (new static)->__( 'A post ID mismatch has been detected.' ), (new static)->__( 'Sorry, you are not allowed to edit this item.' ), 400 );
                        elseif ( isset( $_GET['post'] ) ) $post_id = (int) $_GET['post'];
                        elseif ( isset( $_POST['post_ID'] ) ) $post_id = (int) $_POST['post_ID'];
                        else $post_id = 0;
                        if ( $post_id ) {
                            $post = (new static)->_get_post( $post_id );
                            if ( $post ) {
                                $post_type = $post->post_type;
                                $replace_editor = (new static)->_apply_filters( 'replace_editor', false, $post );
                                if ( ! $replace_editor ) $is_block_editor = (new static)->_use_block_editor_for_post( $post );
                            }
                        }
                        break;
                    case 'edit-tags':
                    case 'term':
                        if ( null === $post_type && (new static)->_is_object_in_taxonomy( 'post', $taxonomy ?: 'post_tag' ) )
                            $post_type = 'post';
                        break;
                    case 'upload':
                        $post_type = 'attachment';
                        break;
                }
            }
            switch ($base){
                case 'post':
                    if ( null === $post_type ) $post_type = 'post';
                    $post_id = null;
                    if ($post_id === null )
                        $is_block_editor = (new static)->_use_block_editor_for_post_type( $post_type );
                    $id = $post_type;
                    break;
                case 'edit':
                    if ( null === $post_type ) $post_type = 'post';
                    $id .= '-' . $post_type;
                    break;
                case 'edit-tags':
                case 'term':
                    if ( null === $taxonomy ) $taxonomy = 'post_tag';
                    if ( null === $post_type ) {
                        $post_type = 'post';
                        if ( isset( $_REQUEST['post_type'] ) && (new static)->_post_type_exists( $_REQUEST['post_type'] ) )
                            $post_type = $_REQUEST['post_type'];
                    }
                    $id = 'edit_' . $taxonomy;
                    break;
            }
            if ( 'network' === $in_admin ) {
                $id   .= '_network';
                $base .= '_network';
            } elseif ( 'user' === $in_admin ) {
                $id   .= '_user';
                $base .= '_user';
            }
            if ( isset( self::$__registry[ $id ] ) ) {
                $screen = self::$__registry[ $id ];
                if ( (new static)->_get_current_screen() === $screen ) return $screen;
            } else {
                $screen     = new self();
                $screen->id = $id;
            }
            $screen->base            = $base;
            $screen->action          = $action;
            $screen->post_type       = (string) $post_type;
            $screen->taxonomy        = (string) $taxonomy;
            $screen->is_user         = ( 'user' === $in_admin );
            $screen->is_network      = ( 'network' === $in_admin );
            $screen->in_admin        = $in_admin;
            $screen->is_block_editor = $is_block_editor;
            self::$__registry[ $id ] = $screen;
            return $screen;
            //return $screen;
        }//209
        public function set_screen(): void{
            $this->tp_current_screen = $this;
            $this->tp_tax_now         = $this->taxonomy;
            $this->tp_typenow        = $this->post_type;
            $this->_do_action( 'current_screen', $this->tp_current_screen );
        }//408 //todo need a return method for this
        public function get_in_admin( $admin = null ): bool{
            if ( empty( $admin ) ) return (bool) $this->in_admin;
            return ( $admin === $this->in_admin );
        }//440
        public function is_block_editor( $set = null ): bool{
            if ( null !== $set ) $this->is_block_editor = (bool) $set;
            return $this->is_block_editor;
        }//456
        public static function add_old_compat_help( $screen, $help ): void{
            self::$__old_compat_help[ $screen->id ] = $help;
        }//472
        public function set_parent_page(string $parent_file): void{
            $this->parent_file         = $parent_file;
            @list( $this->parent_base ) = explode( '?', $parent_file );
            $this->parent_base         = str_replace( '.php', '', $this->parent_base );
        }//485

        //todo make this method original and create method set_parent_class
        //public function set_parent_class( $parent_class,$parent_path = null,...$args ): void{//todo transfer to class way
            //$this->parent_class = $this->_locate_admin_class($parent_class,$parent_path,$args);
            //@list( $this->parent_base ) = explode( '?', $this->parent_class );
            //$this->parent_base         = $this->_strip_namespace_from_classname($this->parent_base);
        //}//485
        public function add_screen_option( $option,array ...$args): void{
            $this->__options[ $option ] = $args;
        }//502
        public function remove_screen_option( $option ): void{
            unset( $this->__options[ $option ] );
        }//51
        public function remove_screen_options(): void{
            $this->__options = [];
        }//522
        public function get_screen_options(): array {
            return $this->__options;
        }//533
        public function get_screen_option( $option, $key = false ){
            if ( ! isset( $this->__options[ $option ] ) ) return null;
            if ( $key ) {
                if ( isset( $this->__options[ $option ][ $key ] ) )
                    return $this->__options[ $option ][ $key ];
                return null;
            }
            return $this->__options[ $option ];
        }
        public function get_help_tabs(): array{
            $help_tabs = $this->__help_tabs;
            $priorities = [];
            foreach ( $help_tabs as $help_tab ) {
                if ( isset( $priorities[ $help_tab['priority'] ] ) )
                    $priorities[ $help_tab['priority'] ][] = $help_tab;
                else $priorities[ $help_tab['priority'] ] = array( $help_tab );
            }
            ksort( $priorities );
            $sorted = [];
            foreach ( $priorities as $list ) {
                foreach ( $list as $tab ) $sorted[ $tab['id'] ] = $tab;
            }
            return $sorted;
        }//568
        public function get_help_tab( $id ){
            if ( ! isset( $this->__help_tabs[ $id ] ) ) return null;
            return $this->__help_tabs[ $id ];
        }//600
        public function add_help_tab($args = null ): void{
            $defaults = ['title' => false,'id' => false,'content' => '','callback' => false,'priority' => 10,];
            $args     = $this->_tp_parse_args( $args, $defaults );
            $args['id'] = $this->_sanitize_html_class( $args['id'] );
            if ( ! $args['id'] || ! $args['title'] ) return;
            $this->__help_tabs[ $args['id'] ] = $args;
        }//631
        public function remove_help_tab( $id ): void{
            unset( $this->__help_tabs[ $id ] );
        }//659
        public function get_help_sidebar(): string{
            return $this->__help_sidebar;
        }//679
        public function set_help_sidebar( $content ): void{
            $this->__help_sidebar = $content;
        }//693
        public function get_columns(): int{
            return $this->__columns;
        }//712
        public function get_screen_reader_content(): array{
            return $this->__screen_reader_content;
        }//723
        public function get_screen_reader_text( $key ){
            if ( ! isset( $this->__screen_reader_content[ $key ] ) ) return null;
            return $this->__screen_reader_content[ $key ];
        }//735
        public function set_screen_reader_content(array ...$content): void{
            $defaults = ['heading_views' => $this->__( 'Filter items list' ),
                'heading_pagination' => $this->__( 'Items list navigation' ),
                'heading_list' => $this->__( 'Items list' ),];
            $content = $this->_tp_parse_args( $content, $defaults );
            $this->__screen_reader_content = $content;
        }//758
        public function remove_screen_reader_content(): void{
            $this->__screen_reader_content = [];
        }//774
        public function get_render_screen_meta(): string{
            $help_sidebar = $this->get_help_sidebar();
            $help_class = 'hidden';
            if ( ! $help_sidebar ) {$help_class .= ' no-sidebar';}
            $output = "<div id='screen_meta' class='block metabox-prefers'>";//div 1
            $output .= "<div id='contextual_help_wrap' class='{$this->_esc_attr($help_class)}' tabindex='-1' aria-label='{$this->_esc_attr('Contextual Help Tab')}' >";//div 2
            $output .= "<div id='contextual_help_back'></div>";//div3
            $output .= "<div id='contextual_help_columns'>";//div 4
            $output .= "<div class='contextual-help-tabs'><ul>";//div 5
            $class = " class='active'";
            foreach ( $this->get_help_tabs() as $tab ){
                $link_id  = "tab_link_{$tab['id']}";
                $panel_id = "tab_panel_{$tab['id']}";
                $output .= "<li id='{$this->_esc_attr($link_id)}' $class>";
                $output .= "<a href='#{$this->_esc_url($panel_id)}' aria-controls='{$this->_esc_attr($panel_id)}'>{$this->_esc_html($tab['title'])}</a>";
                $output .= "</li>";
                $class = '';
            }
            $output .= "</ul></div>";//div 5
            if ($help_sidebar ){
                $output .= "<div class='contextual-help-sidebar'>";//div 6
                $output .= $help_sidebar;
                $output .= "</div>";//div 6
            }
            $output .= "<div class='contextual-help-tabs-wrap'>";//div 7
            $classes = 'help-tab-content active';
            foreach ( $this->get_help_tabs() as $tab ){
                $panel_id = "tab_panel_{$tab['id']}";
                $output .= "<div id='{$this->_esc_attr($panel_id)}' class='$classes'>";//div 8
                $output .= $tab['content'];
                if (!empty($tab['callback'])){
                    $output .= call_user_func($tab['callback'], $this, $tab);
                }
                $output .= "</div>";//div 8
                $classes = 'help-tab-content';
            }
            $output .= "</div></div></div>";//div 7 //div 4 //div 2
            $columns = $this->_apply_filters( 'screen_layout_columns',[], $this->id, $this );
            if ( ! empty( $columns ) && isset( $columns[ $this->id ] ) )
                $this->add_screen_option( 'layout_columns', array( 'max' => $columns[ $this->id ] ) );
            if ( $this->get_screen_option( 'layout_columns' ) ) {
                $this->__columns = (int) $this->_get_user_option( "screen_layout_$this->id" );
                if ( ! $this->__columns && $this->get_screen_option( 'layout_columns', 'default' ) )
                    $this->__columns = $this->get_screen_option( 'layout_columns', 'default' );
            }
            $GLOBALS['screen_layout_columns'] = $this->__columns; // Set the global for back-compat.
            $this->tp_screen_layout_columns = $this->__columns;//perhaps I need this one ?
            if ( $this->show_screen_options() ) $this->render_screen_options();
            $output .= "</div>";//div 1
            //if ( ! $this->get_help_tabs() && ! $this->show_screen_options() ) return '';
            $link_args['screen_options'] = $this->show_screen_options();
            $link_args['get_help_tabs'] = $this->get_help_tabs();
            $output .= new screen_meta_links($link_args);
            return $output;
        }//787
        public function render_screen_meta(): void{
            echo $this->get_render_screen_meta();
        }
        public function show_screen_options(){
            if ( is_bool( $this->__show_screen_options ) ) return $this->__show_screen_options;
            $columns = $this->_get_column_headers( $this );
            $show_screen = ! empty( $this->tp_meta_boxes[ $this->id ] ) || $columns || $this->get_screen_option( 'per_page' );
            $this->__screen_settings = '';
            if ( 'post' === $this->base ) {
                $expand = "<fieldset class='editor-expand hidden'><legend>{$this->__( 'Additional settings' )}</legend>";
                $expand .= "<label for='editor_expand_toggle'><input type='checkbox' id='editor_expand_toggle' {$this->_get_checked( $this->_get_user_setting( 'editor_expand', 'on' ), 'on' )}/>";
                $expand .= "{$this->__( 'Enable full-height editor and distraction-free functionality.' )}</label></fieldset>";
                $this->__screen_settings = $expand;
            }
            $this->__screen_settings = $this->_apply_filters( 'screen_settings', $this->__screen_settings, $this );
            if ( $this->__screen_settings || $this->__options ) $show_screen = true;
            $this->__show_screen_options = $this->_apply_filters( 'screen_options_show_screen', $show_screen, $this );
            return $this->__show_screen_options;
        }//988
        public function get_render_screen_options(array ...$options): string{
            $options = $this->_tp_parse_args( $options, ['wrap' => true,]);
            $wrapper_start = '';
            $wrapper_end   = '';
            $form_start    = '';
            $form_end      = '';
            if ( $options['wrap'] ){
                $wrapper_start = "<div id='screen_options_wrap' class='hidden' tabindex='-1'>";
                $wrapper_end = "</div>";
            }
            if ( 'todo' !== $this->base ) {//todo
                $form_start = "\n<form id='adv-settings' method='post'>\n";
                $form_end   = "\n{$this->_tp_get_nonce_field( 'screen-options-nonce', 'screen_option_nonce', false )}\n</form>\n";
            }
            $html = $wrapper_start.$form_start;
            ob_start();
            $this->render_meta_boxes_preferences();
            $this->render_list_table_columns_preferences();
            $this->render_screen_layout();
            $this->render_per_page_options();
            $this->render_view_mode();
            $html .= ob_get_clean();
            $html .= $this->__screen_settings;
            ob_start();
            $show_button = $this->_apply_filters( 'screen_options_show_submit', false, $this );
            if ( $show_button ) $this->_submit_button( $this->__( 'Apply' ), 'primary', 'screen-options-apply', true );
            $html .= ob_get_clean();
            $html .= $form_end.$wrapper_end;
            return $html;
        }//1046
        public function render_screen_options(array ...$options): void{
            echo $this->get_render_screen_options($options);
        }
        public function get_render_meta_boxes_preferences(){
            if ( ! isset( $this->tp_meta_boxes[ $this->id ] ) ) return '';
            $args['meta_is'] = $this->id;
            return new render_meta_boxes_preferences(...$args);
        }//1105
        public function render_meta_boxes_preferences(): void{
            echo $this->get_render_meta_boxes_preferences();
        }
        public function get_render_list_table_columns_preferences(){
            $columns = $this->_get_column_headers( $this );
            $hidden  = $this->_get_hidden_columns( $this );
            if ( ! $columns ) return '';
            $args['legend'] = ! empty( $columns['_title'] ) ? $columns['_title'] : $this->__( 'Columns' );
            $args['columns'] = $columns;
            $args['hidden'] = $hidden;
            return new render_list_table_columns_preferences(...$args);
        }//1146
        public function render_list_table_columns_preferences(): void{
            echo $this->get_render_list_table_columns_preferences();
        }
        public function get_render_screen_layout(){
            if ( ! $this->get_screen_option( 'layout_columns' ) ) return '';
            $args['screen_layout_columns'] = $this->get_columns();
            $args['num']                   = $this->get_screen_option( 'layout_columns', 'max' );
            return new render_screen_layout(...$args);
        }//1194
        public function render_screen_layout(): void{
            echo $this->get_render_screen_layout();
        }
        public function get_render_per_page_options(): mixed{
            if ( null === $this->get_screen_option( 'per_page' ) ) return '';
            $per_page_label = $this->get_screen_option( 'per_page', 'label' );
            if ( null === $per_page_label ) $per_page_label = $this->__( 'Number of items per page:' );
            $screen_option = $this->get_screen_option( 'per_page', 'option' );
            if ( ! $screen_option ) $screen_option =  str_replace( '_', '-', "{$this->id}_per_page" );
            $per_page = (int) $this->_get_user_option( $screen_option );
            if ( empty( $per_page ) || $per_page < 1 ) {
                $per_page = $this->get_screen_option( 'per_page', 'default' );
                if ( ! $per_page ) $per_page = 20;
            }
            if ( 'edit_comments_per_page' === $screen_option ) {
                $comment_status = $_REQUEST['comment_status']  ?? 'all';
                $per_page = $this->_apply_filters( 'comments_per_page', $per_page, $comment_status );
            } elseif ( 'categories_per_page' === $screen_option )
                $per_page = $this->_apply_filters( 'edit_categories_per_page', $per_page );
            else $per_page = $this->_apply_filters( "{(string)($screen_option)}", $per_page );
            $this->_add_filter( 'screen_options_show_submit', '__return_true' );
            $args['per_page'] = $per_page;
            $args['per_page_label'] = $per_page_label;
            $args['screen_option'] = $screen_option;
            return new render_per_page_options(...$args);
        }//1226
        public function render_per_page_options(): void{
            echo $this->get_render_per_page_options();
        }
        public function get_render_view_mode(){
            $get_screen = $this->_get_current_screen();
            $screen = null;
            if( $get_screen instanceof self ){
                $screen = $get_screen;
            }
            if ( 'edit' !== $screen->base && 'edit-comments' !== $screen->base ) return '';
            $view_mode_post_types = $this->_get_post_types( array( 'show_ui' => true ) );
            $view_mode_post_types = $this->_apply_filters( 'view_mode_post_types', $view_mode_post_types );
            if ( 'edit' === $screen->base && ! in_array( $this->post_type, $view_mode_post_types, true ) ) return '';
            if ( ! isset( $this->__tp_mode ) ) $this->__tp_mode = $this->_get_user_setting( 'posts_list_mode', 'list' );
            $this->_add_filter( 'screen_options_show_submit', '__return_true' );
            $args['tp_mode'] = $this->__tp_mode;
            return new render_view_mode(...$args);
        }//1292
        public function render_view_mode(): void{
            echo $this->get_render_view_mode();
        }
        public function get_render_screen_reader_content( $key = '', $tag = 'h2' ): string{
            if ( ! isset( $this->__screen_reader_content[ $key ] ) ) return '';
            return "<$tag class='screen-reader-text'>{$this->__screen_reader_content[ $key ]}</$tag>";
        }//1347
        public function render_screen_reader_content( $key = '', $tag = 'h2' ): void{
            echo $this->get_render_screen_reader_content( $key, $tag);
        }
    }
}else die;