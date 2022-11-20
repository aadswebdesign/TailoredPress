<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-5-2022
 * Time: 15:14
 */
namespace TP_Core\Libs\Editor;
use TP_Core\Libs\Queries\TP_Query;
if(ABSPATH){
    final class TP_Editor extends Editor_base{
        private function __construct() {}//32
        public static function parse_settings( $editor_id, $settings ):array {
            $settings = (new static())->_apply_filters( 'tp_editor_settings', $settings, $editor_id );
            $set = (new static())->_tp_parse_args($settings,['tpautop' => ! (new self)->_has_blocks(), 'media_buttons' => true,'default_editor' => '',
                'drag_drop_upload' => false,'textarea_name' => $editor_id,'textarea_rows' => 20,'tabindex' => '',
                'tabfocus_elements' => ':prev,:next','editor_css' => '','editor_class' => '','teeny' => false,
                '_content_editor_dfw' => false,'tinymce' => true,'quicktags' => true,]);
            self::$_this_tinymce = ( $set['tinymce'] && (new static)->_user_can_rich_edit() );
            if (self::$_this_tinymce && false !== strpos($editor_id, '[')) {
                self::$_this_tinymce = false;
                (new static)->_deprecated_argument( 'tp_editor()', '3.9.0', 'TinyMCE editor IDs cannot have brackets.' );
            }
            self::$_this_quicktags = (bool) $set['quicktags'];
            if ( self::$_this_tinymce ) { self::$_has_tinymce = true;}
            if ( self::$_this_quicktags ) { self::$_has_quicktags = true;}
            if ( empty( $set['editor_height'] ) ) { return $set;}
            if ( 'content' === $editor_id && empty( $set['tinymce']['wp_autoresize_on'] ) ) {
                $cookie = (int) (new static)->_get_user_setting( 'ed_size' );
                if ( $cookie ) { $set['editor_height'] = $cookie;}
            }
            if ( $set['editor_height'] < 50 ) { $set['editor_height'] = 50;
            } elseif ( $set['editor_height'] > 5000 ) { $set['editor_height'] = 5000;}
            return $set;
        }//69
        public static function get_editor( $content, $editor_id,array ...$settings):string{
            $set            = self::parse_settings( $editor_id, $settings );
            $_class= trim( (new static)->_esc_attr( $set['editor_class'] ) . ' tp-editor-area' );
            $editor_class   = " class='$_class'";
            $tabindex       = $set['tabindex'] ? " tabindex='". (int) $set['tabindex'] . "'" : '';
            $default_editor = 'html';
            $buttons        = '';
            $autocomplete   = '';
            $editor_id_attr = (new static)->_esc_attr( $editor_id );
            if ( $set['drag_drop_upload'] ) { self::$_drag_drop_upload = true;}
            if ( ! empty( $set['editor_height'] ) ) {
                $height = " style='height: " . (int) $set['editor_height'] ."px'";
            } else { $height = " rows='" . (int) $set['textarea_rows'] . "'";}
            if ( ! (new static)->_current_user_can( 'upload_files' ) ) {$set['media_buttons'] = false;}
            if ( self::$_this_tinymce ) {
                $autocomplete = ' autocomplete="off"';
                if ( self::$_this_quicktags ) {
                    $default_editor = $set['default_editor'] ?: (new static)->_tp_default_editor();
                    if ( 'html' !== $default_editor ) {$default_editor = 'tinymce';}
                    $buttons .= "<button type='button' id='{$editor_id_attr}_tmce' class='tp-switch-editor switch-tmce' data-tp-editor-id='$editor_id_attr'>{(new static)->_x( 'Visual', 'Name for the Visual editor tab' )}</button>\n";
                    $buttons .= "<button type='button' id='{$editor_id_attr}_html' class='tp-switch-editor switch-html' data-tp-editor-id='$editor_id_attr'>{(new static)->_x( 'Text', 'Name for the Text editor tab (formerly HTML)' )}</button>\n";
                } else {$default_editor = 'tinymce';}
            }
            $switch_class = 'html' === $default_editor ? 'html-active' : 'tmce-active';
            $wrap_class   = 'tp-core-ui tp-editor-wrap ' . $switch_class;
            if ( $set['_content_editor_dfw'] ) { $wrap_class .= ' has-dfw';}
            $output  = "<div id='tp_{$editor_id_attr}_wrap' class='$wrap_class'>";
            ob_start();
            if ( self::$_editor_buttons_css ) {
                (new static)->tp_print_styles( 'editor-buttons' );
                self::$_editor_buttons_css = false;
            }
            $output .= ob_get_clean();
            if ( ! empty( $set['editor_css'])){ $output .= $set['editor_css'] . "\n";}
            if ( ! empty( $buttons ) || $set['media_buttons'] ) {
                $output .= "<div id='tp_{$editor_id_attr}_editor_tools' class='tp-editor-tools hide-if-no-js'>";
                if ( $set['media_buttons'] ) {
                    self::$_has_medialib = true;
                    $output .= "<div id='tp_{$editor_id_attr}_media_buttons' class='tp-media-buttons'>";
                    $output .= (new static)->_do_action( 'media_buttons', $editor_id );//todo
                    $output .= "</div>\n";
                }
                $output .= "<div class='tp-editor-tabs'>$buttons</div>\n";
                $output .= "</div>\n";
            }
            $quicktags_toolbar = '';
            if ( self::$_this_quicktags ) {
                if ( 'content' === $editor_id && ! empty( $GLOBALS['current_screen'] ) && 'post' === $GLOBALS['current_screen']->base ) {
                    $toolbar_id = 'ed_toolbar';
                } else {$toolbar_id = 'qt_' . $editor_id_attr . '_toolbar';}
                $quicktags_toolbar = "<div id='$toolbar_id' class='quicktags-toolbar hide-if-no-js'></div>";
            }
            $_the_editor = "<div id='tp_{$editor_id_attr}_editor_container' class='tp-editor-container'><ul><li>$quicktags_toolbar</li><li>";
            $_the_editor .= "<dd><textarea id='$editor_id_attr' name='" . (new static)->_esc_attr( $set['textarea_name'] ) . "' $editor_class $height $tabindex $autocomplete cols='40'></textarea>%s</dd></li></ul></div>";
            $the_editor = (new static)->_apply_filters('the_editor',$_the_editor);
            if ( self::$_this_tinymce ) {
                (new static)->_add_filter( 'the_editor_content', 'format_for_editor', 10, 2 );
            }
            $content = (new static)->_apply_filters( 'the_editor_content', $content, $default_editor );
            if ( self::$_this_tinymce ) {
                (new static)->_remove_filter( 'the_editor_content', 'format_for_editor' );
            }
            if ( false !== stripos( $content, 'textarea' ) ) {
                $content = preg_replace( '%</textarea%i', '&lt;/textarea', $content );
            }
            $output .= sprintf( $the_editor, $content);
            $output .= "\n</div>\n\n";
            //$output .= self::get_editor_settings( $editor_id, $set );
            return $output;
        }//156
        public static function editor( $content, $editor_id,array ...$settings):void{
            echo self::get_editor( $content, $editor_id,$settings);
        }
        public static function get_editor_settings( $editor_id, $set ):string{return '';}//316
        public static function editor_settings( $editor_id, $set ):void{}//316
        private static function __parse_init( $init ){return '';}//824
        public static function enqueue_scripts( $default_scripts = false ){}//851
        public static function enqueue_default_editor(){}//897
        public static function print_default_editor_scripts(){}//923
        public static function get_mce_locale(){}//1024
        public static function get_baseurl(){return '';}//1040
        //private static function __default_settings(){}//1058
        //private static function __get_translation() {}//1126
        public static function tp_mce_translation( $mce_locale = '', $json_only = false ){}//1456
        public static function force_uncompressed_tinymce(){}//1515
        public static function print_tinymce_scripts(){}//1541
        /**
         * more to do here, just that isn't the direction a wanna go!
         */
        public static function get_editor_js():string{
            $tmce_on = ! empty( self::$_mce_settings );
            $mceInit = '';
            $qtInit  = '';
            if ( $tmce_on ) {
                foreach ( self::$_mce_settings as $editor_id => $init ) {
                    $options  = self::__parse_init( $init );
                    $mceInit .= "'$editor_id':{$options},";
                }
                $mceInit = '{' . trim( $mceInit, ',' ) . '}';
            } else {
                $mceInit = '{}';
            }
            if ( ! empty( self::$_qt_settings ) ) {
                foreach ( self::$_qt_settings as $editor_id => $init ) {
                    $options = self::__parse_init( $init );
                    $qtInit .= "'$editor_id':{$options},";
                }
                $qtInit = '{' . trim( $qtInit, ',' ) . '}';
            } else {
                $qtInit = '{}';
            }
            $ref = array(
                'plugins'  => implode( ',', self::$_plugins ),//tiny_mce plugins
                'theme'    => 'modern',
                'language' => self::$mce_locale,
            );
            $suffix  = TP_SCRIPT_DEBUG ? '' : '.min';
            $baseurl = self::get_baseurl();
            $version = 'ver="'. (new self)->_tinymce_version .'"';
            (new self)->_do_action( 'before_tp_tiny_mce', self::$_mce_settings );
            ob_start();
            $tiny_script = '';
            ?>
            <script>
                tinyMCEPreInit = {
                    baseURL: "<?php echo $baseurl; ?>",
                    suffix: "<?php echo $suffix; ?>",
                    <?php

                    if ( self::$_drag_drop_upload ) {
                        echo 'dragDropUpload: true,';
                    }

                    ?>
                    mceInit: <?php echo $mceInit; ?>,
                    qtInit: <?php echo $qtInit; ?>,
                    ref: <?php echo self::__parse_init( $ref ); ?>,
                    load_ext: function(url,lang){
                        //noinspection JSUnresolvedVariable
                        let sl=tinymce.ScriptLoader;
                        //noinspection JSUnresolvedFunction
                        sl.markDone(url+'/langs/'+lang+'.js');
                        //noinspection JSUnresolvedFunction
                        sl.markDone(url+'/langs/'+lang+'_dlg.js');}
                };
            </script>
            <?php
            $tiny_script .= ob_get_clean();
            $tiny_script .= (new self)->_do_action( 'tp_tiny_mce_init', self::$_mce_settings );
            ob_start();
            ?>
            <script>
                tinyMCEPreInit = {
                    baseURL: "<?php echo $baseurl; ?>",
                    suffix: "<?php echo $suffix; ?>",
                    <?php

                    if ( self::$_drag_drop_upload ) {
                        echo 'dragDropUpload: true,';
                    }

                    ?>
                    mceInit: <?php echo $mceInit; ?>,
                    qtInit: <?php echo $qtInit; ?>,
                    ref: <?php echo self::__parse_init( $ref ); ?>,
                    load_ext: function(url,lang){
                        //noinspection JSUnresolvedVariable
                        let sl=tinymce.ScriptLoader;
                        //noinspection JSUnresolvedFunction
                        sl.markDone(url+'/langs/'+lang+'.js');
                        //noinspection JSUnresolvedFunction
                        sl.markDone(url+'/langs/'+lang+'_dlg.js');}
                };
            </script>
            <?php
            $tiny_script .= ob_get_clean();
            return $tiny_script;
        }//1566
        //public static function editor_js(){}
        public static function tp_link_query( $args = []){
            $pts      = (new self)->_get_post_types( array( 'public' => true ), 'objects' );
            $pt_names = array_keys( $pts );
            $query = [  'post_type' => $pt_names,'suppress_filters' => true,'update_post_term_cache' => false,
                'update_post_meta_cache' => false,'post_status' => 'publish','posts_per_page' => 20,];
            $args['pagenum'] = isset( $args['pagenum'] ) ? (new self)->_abs_int( $args['pagenum'] ) : 1;
            if ( isset( $args['s'] ) ) {
                $query['s'] = $args['s'];
            }
            $query['offset'] = $args['pagenum'] > 1 ? $query['posts_per_page'] * ( $args['pagenum'] - 1 ) : 0;
            $query = (new self)->_apply_filters( 'tp_link_query_args', $query );
            $get_posts = new TP_Query;
            $posts     = $get_posts->query_main( $query );
            $results = array();
            foreach ( $posts as $post ) {
                if ( 'post' === $post->post_type ) {
                    $info = (new self)->_mysql2date( (new self)->__( 'Y/m/d' ), $post->post_date );
                } else {
                    $info = $pts[ $post->post_type ]->labels->singular_name;
                }
                $results[] = array(
                    'ID'        => $post->ID,
                    'title'     => trim( (new self)->_esc_html( strip_tags( (new self)->_get_the_title( $post ) ) ) ),
                    'permalink' => (new self)->_get_permalink( $post->ID ),
                    'info'      => $info,
                );
            }
            $results = (new self)->_apply_filters( 'tp_link_query', $results, $query );

            return ! empty( $results ) ? $results : false;
        }//1766
        public static function tp_get_link_dialog():string{
            self::$_html  = '';
            if ( self::$_link_dialog_printed ) {
                self::$_html  .=  self::$_link_dialog_printed;
            }
            self::$_html .= "<div id='tp-link-backdrop' style='display: none;'></div>";
            self::$_html .= "<div id='tp_link_wrap' class='tp-core-ui' style='display: none;' role='dialog' aria-labelledby='link-modal-title'>";
            self::$_html .= "<form id='tp_link' tabindex=\"-1\">";
            self::$_html .= "<div id='link_selector'>";
            self::$_html .= "<div id='link_options'>";
            self::$_html .= "<p class='howto' id='tp_link_enter_url'>{(new self)->__('Enter the destination URL,')}</p>";
            self::$_html .= "<div>";
            self::$_html .= "<label><span>{(new self)->__('URL')}</span>";
            self::$_html .= "<input id='tp_link_url' type='text'/></label>";
            self::$_html .= "</div>";
            self::$_html .= "<div class='tp-link-text-field'>";
            self::$_html .= "<label><span>{(new self)->__('Link Text')}</span>";
            self::$_html .= "<input id='tp_link_text' type='text'/></label>";
            self::$_html .= "</div>";
            self::$_html .= "<div class='link-target'>";
            self::$_html .= "<label>";
            self::$_html .= "<input id='tp_link_target' type='checkbox'/><span>{(new self)->__('Open link in a new tab')}</span></label>";
            self::$_html .= "</div>";
            self::$_html .= "</div>";//link_options
            self::$_html .= "<p class='howto' id='tp_link_enter_url'>{(new self)->__('Or link to existing content.')}</p>";
            self::$_html .= "<div id='search_panel'>";
            self::$_html .= "<div class='link-search-wrapper'><label>";
            self::$_html .= "<span class='search-label'>{(new self)->__( 'Search' )}</span>";
            self::$_html .= "<input type='search' id='tp_link_search' class='link-search-field' autocomplete='off' aria-describedby='tp-link-link-existing-content' />";
            self::$_html .= "<span class='spinner'></span>";
            self::$_html .= "</label></div>";//link-search-wrapper
            self::$_html .= "<div id='search_results' class='query-results' tabindex='0'>";
            self::$_html .= "<ul></ul>";
            self::$_html .= "<div class='river-waiting'><span class='spinner'></span>";
            self::$_html .= "</div>";//search_results
            self::$_html .= "<div id='most_recent_results' class='query-results' tabindex='0'>";
            self::$_html .= "<div id='query_notice_message' class='query-notice'>";
            self::$_html .= "<em class='query-notice-default'>{(new self)->__( 'No search term specified. Showing recent items.' )}</em>";
            self::$_html .= "<em class='query-notice-hint screen-reader-text'>{(new self)->__( 'Search or use up and down arrow keys to select an item.' )}</em>";
            self::$_html .= "</div><ul></ul>";
            self::$_html .= "<div class='river-waiting'><span class='spinner'></span></div>";
            self::$_html .= "</div>";//most_recent_results
            self::$_html .= "</div>";//search_panel
            self::$_html .= "<div class='submitbox'>";
            self::$_html .= "<div id='tp_link_cancel'>";
            self::$_html .= "<button type='button' class='button'>{(new self)->__( 'Cancel' )}></button>";
            self::$_html .= "</div>";
            self::$_html .= "<div id='tp_link_update'>";
            $value = (new self)->_esc_attr( 'Add Link' );
            self::$_html .= "<input type='submit' id='tp_link_submit' name='tp_link_submit' class='button button-primary' value='{$value}'>";
            self::$_html .= "</div></div>";//submitbox
            self::$_html .= "</div>";//link_selector
            self::$_html .= "</form></div>";//tp_link_wrap
            return (string) self::$_html;
        }//1843
        public static function tp_link_dialog():void{
            echo self::tp_get_link_dialog();
        }
    }
}else die;