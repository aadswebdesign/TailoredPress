<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-11-2022
 * Time: 16:11
 */
namespace TP_Admin\Libs\AdmPartials;
use TP_Core\Libs\TP_Theme;
if(ABSPATH){
    class Adm_Partial_Themes_Block extends Adm_Partials  {
        protected $_search_terms = [];
        public $features        = [];
        public function __construct( ...$args) {
            parent::__construct(['async' => true,'screen' => $args['screen'] ?? null,]);
        }//32
        public function async_user_can() {
            return $this->_current_user_can( 'switch_themes' );
        }//44
        public function prepare_items(){
            $themes = $this->_tp_get_themes( array( 'allowed' => true ) );
            if ( ! empty( $_REQUEST['s'] ) ) {
                $this->_search_terms = array_unique( array_filter( array_map( 'trim', explode( ',', strtolower( $this->_tp_unslash( $_REQUEST['s']))))));
            }
            if ( ! empty( $_REQUEST['features'])){ $this->features = $_REQUEST['features'];}
            if ( $this->_search_terms || $this->features ) {
                foreach ($themes as $key => $theme ) {
                    if ( ! $this->get_search_theme( $theme )){ unset( $themes[ $key ]);}
                }
            }
            unset( $themes[ $this->_get_option( 'stylesheet' ) ] );
            TP_Theme::sort_by_name($themes);
            $per_page = 36;
            $page     = $this->get_pagenum();
            $start = ( $page - 1 ) * $per_page;
            $this->items = array_slice( $themes, $start, $per_page, true );
            $this->_set_pagination_args(['total_items' => count( $themes ),'per_page' => $per_page,'infinite_scroll' => true,]);
        }//51
        public function get_no_items(){
            $output  = "";
            if ( $this->_search_terms || $this->features ) { return $this->__( 'No items found.' );}
            $blog_id = $this->_get_current_blog_id();
            if ( $this->_is_multisite()){
                if ( $this->_current_user_can( 'install_themes' ) && $this->_current_user_can( 'manage_network_themes' ) ) {
                    $output = sprintf($this->__("You only have one theme enabled for this site right now. Visit the Network Admin to <a href='%1\$s'>enable</a> or <a href='%2\$s'>install</a> more themes."),
                        $this->_network_admin_url( 'site_themes.php?id=' . $blog_id ),$this->_network_admin_url( 'theme-install.php' ));
                }elseif ( $this->_current_user_can( 'manage_network_themes' ) ) {
                    $output = sprintf($this->__("You only have one theme enabled for this site right now. Visit the Network Admin to <a href='%s'>enable</a> more themes."),$this->_network_admin_url( 'site-themes.php?id=' . $blog_id ));
                }
                return $output;
            }
            if ( $this->_current_user_can( 'install_themes' ) ){
                return sprintf($this->__("You only have one theme installed right now. Live a little! You can choose from over 1,000 free themes in the WordPress Theme Directory at any time: just click on the <a href='%s'>Install Themes</a> tab above."),
                    $this->_admin_url( 'theme_install.php' ));
            }
            $output .= sprintf($this->__("Only the current theme is available to you. Contact the %s administrator for information about accessing additional themes."),$this->_get_site_option( 'site_name' ));
            return "<dt><p>$output</p></dt>";
        }//91
        protected function _get_nav_block( $which = 'top' ):string{
            if ( $this->get_pagination_arg( 'total_pages' ) <= 1 ) {
                return false;
            }
            $output  = "<li class='wrapper themes $which'>";
            $output .=  $this->_get_pagination( $which );
            $output .= "<span class='spinner'></span></li>";
            return $output;
        }//140
        public function get_display():string{
            $output  = $this->_tp_get_nonce_field( 'fetch-list-' . get_class( $this ), '_async_fetch_list_nonce' );
            $output .= $this->_get_nav_block( 'top' );
            $output .= "<li class='wrapper available-themes'>{$this->get_display_placeholder()}</li><!-- wrapper available-themes -->";
            $output .= $this->_get_nav_block( 'bottom' );
            return $output;
        }//160
        public function get_blocks() {
            return [];
        }//176
        public function get_display_placeholder():string{
            $output  = "";
            if ($this->has_items()){ $output .= $this->get_display_blocks();
            }else{ $output .= "<li class='wrapper no-items'>{$this->get_no_items()}</li><!-- wrapper no-items -->";}
            return $output;
        }//182
        public function get_display_blocks(){
            $themes = ['theme1','theme2'];//$this->_init_theme();
            $output  = "";
            $i = 1;
            $j = 1;
            foreach ($themes as $theme){
                $_i = $i++;
                $template   = 'placeholder template';//$theme->get_template();
                $stylesheet = 'placeholder stylesheet';//$theme->get_stylesheet();
                $title      = 'placeholder title';//$theme->display( 'Name' );
                $version    = 'placeholder version';//$theme->display( 'Version' );
                $author     = 'placeholder author';//$theme->display( 'Author' );
                $activate_link = $this->_tp_nonce_url( 'themes.php?action=activate&amp;template=' . urlencode( $template ) . '&amp;stylesheet=' . urlencode( $stylesheet ), 'switch-theme_' . $stylesheet );
                $actions = [];
                $actions['li_open_1'] = "<li class='wrapper display-blocks'>";
                $actions['activate'] = sprintf("<dd><a href='%s' class='activate-link' aria-label='%s'>%s</a></dd>",$activate_link,
                    $this->_esc_attr( sprintf( $this->_x( 'Activate &#8220;%s&#8221;', 'theme' ), $title ) ), $this->__( 'Activate' ));
                if ( $this->_current_user_can( 'edit_theme_options' ) && $this->_current_user_can( 'customize' ) ) {
                    $actions['preview'] .= sprintf("<dd><a href='%s' class='load-customize hide-if-no-customize'>%s</a></dd>",
                        $this->_tp_customize_url( $stylesheet ),$this->__('Live Preview'));
                }
                if ( ! $this->_is_multisite() && $this->_current_user_can( 'delete_themes' ) ) {
                    $confirm = "{return confirm( '%s' )}";//todo this way?
                    $actions['delete'] = sprintf("<dd><a href='%s' class='submit-delete deletion' onclick=' " . $confirm . " ' >%s</a></dd>",
                        $this->_tp_nonce_url( 'themes.php?action=delete&amp;stylesheet=' . urlencode( $stylesheet ), 'delete-theme_' . $stylesheet ),
                        $this->_esc_js( sprintf( $this->__( "You are about to delete this theme '%s'\n  'Cancel' to stop, 'OK' to delete." ), $title ) ),
                        $this->__('Delete'));
                }
                $actions['li_close_1'] = "</li><!-- wrapper display-blocks:1 no:$_i -->";
                $actions['li_open_2'] = "<li class='wrapper display-blocks'>";
                $actions = $this->_apply_filters( 'theme_action_links', $actions, $theme, 'all' );
                $actions = $this->_apply_filters( "theme_action_links_{$stylesheet}", $actions, $theme, 'all' );
                $delete_action = $actions['delete'] ?? '';
                unset( $actions['delete'] );
                $screenshot = 'placeholder screenshot';//$theme->get_screenshot();
                $actions['dt_open_1'] .= "<dt class='screenshot hide-if-customize'>";
                if ( $screenshot ){$actions['img_1'] .= "<img src='{$this->_esc_url( $screenshot )}' alt=''/>";}
                $actions['dt_close_1'] .= "</dt>";
                $actions['dd_open_1'] .= "<dd><a href='{$this->_tp_customize_url( $stylesheet )}' class='screenshot load-customize hide-if-no-customize'>";
                if ( $screenshot ){$actions['img_2'] .= "<img src='{$this->_esc_url( $screenshot )}' alt='' />";}
                $actions['dd_close_1'] .= "</a></dd><dt><h3>$title</h3></dt>";
                $actions['li_close_2'] .= "</li><!-- wrapper display-blocks:2 no:$_i -->";
                $output .= "<div class='adm-segment display-blocks'><ul>";
                $output .= "<li class='wrapper theme-author'><dt>" . sprintf($this->__('By %s'), $author) . "</dt></li><!-- wrapper theme-author no:$_i -->";
                $output .= "<li class='wrapper action-links'><ul>";
                foreach ( $actions as $action ){
                    $_j = $j++;
                    $output .= "$action<!-- action  no:$_i/$_j -->";
                }
                $output .= "<li class='wrapper hide-if-no-js'><dd><a href='#' class='theme-detail'>{$this->__('Details')}</a></dd></li><!-- wrapper  hide-if-no-js  no:$_i -->";
                $output .= "</ul></li><!-- wrapper action-links no:$_i -->";
                $output .= "<li class='wrapper delete'>$delete_action</li><!-- wrapper delete no:$_i -->";
                $output .= "<li class='wrapper update'>{$this->_get_theme_update_available( $theme )}</li><!-- wrapper update no:$_i -->";
                $output .= "</ul><ul class='theme-detail hide-if-js'><li>";
                $output .= "<dt><p><strong>{$this->__('Version:')}</strong>$version</p></dt>";
                $output .= "<dt><p>{$this->__( 'Placeholder: Description' )}</p></dt>";//todo {$theme->display( 'Description' )}
                $output .= "</li></ul></div><!-- adm-segment display-blocks  no:$_i-->";
            }
            return $output;
        }//194
        public function get_search_theme(TP_Theme $theme ):bool{
            foreach ( $this->features as $word ) {
                if(!in_array( $word, $theme->get_theme('Tags'),true)){return false;}
            }
            foreach ( $this->_search_terms as $word ) {
                if(in_array( $word, $theme->get_theme( 'Tags' ),true )){continue;}
                foreach ( array( 'Name', 'Description', 'Author', 'AuthorURI' ) as $header ) {
                    if ( false !== stripos( strip_tags( $theme->display( $header, false, true ) ), $word ) ) {
                        continue 2;}
                }
                if (false !== stripos( $theme->get_stylesheet(), $word)){continue;}
                if ( false !== stripos( $theme->get_template(),$word)){continue;}
                return false;
            }
            return true;
        }//302
        protected function _get_js_vars( ...$extra_args):string{
            $search_string = isset( $_REQUEST['s'] ) ? $this->_esc_attr( $this->_tp_unslash( $_REQUEST['s'] ) ) : '';
            $args = [ 'search' => $search_string,'features' => $this->features,'paged' => $this->get_pagenum(),
                'total_pages' => ! empty( $this->_pagination_args['total_pages'] ) ? $this->_pagination_args['total_pages'] : 1,
            ];
            if (is_array($extra_args )){$args = array_merge( $args, $extra_args );}
            $percent_list = "%s;";
            ob_start();
            ?>
            <!--suppress JSUnusedLocalSymbols -->
            <script id='theme_list_args'> let theme_list_args ='<?php echo $percent_list ?>';</script>
            <?php
            $theme_list = ob_get_clean();
            $output  = sprintf($theme_list,$this->_tp_json_encode( $args ));
            ob_start();
            parent::_get_js_vars();
            $output .= ob_get_clean();
            return $output;
        }//344
    }
}else{die;}