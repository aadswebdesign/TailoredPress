<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-11-2022
 * Time: 16:11
 */
namespace TP_Admin\Libs\AdmPartials;
use TP_Core\Libs\TP_Error;
use TP_Admin\Traits\_adm_theme_install;
if(ABSPATH){
    class Adm_Partial_Themes_Install_Block extends Adm_Partial_Themes_Block{
        use _adm_theme_install;
        public $features;
        public function async_user_can() {
            return $this->_current_user_can( 'install_themes' );
        }
        public function prepare_items(){
            $this->_tp_reset_vars(['tab']);
            $search_terms  = [];
            $search_string = '';
            if ( ! empty( $_REQUEST['s'] ) ) {
                $search_string = strtolower( $this->_tp_unslash( $_REQUEST['s'] ) );
                $search_terms  = array_unique( array_filter( array_map( 'trim', explode( ',', $search_string ) ) ) );
            }
            if ( ! empty( $_REQUEST['features'] ) ) { $this->features = $_REQUEST['features'];}
            $this->tp_paged = $this->get_pagenum();
            $per_page = 36;
            $this->tp_tabs              = [];
            $this->tp_tabs['dashboard'] = $this->__( 'Search' );
            if ( 'search' === $this->tp_tab ) {
                $this->tp_tabs['search'] = $this->__( 'Search Results' );
            }
            $this->tp_tabs['upload']   = $this->__( 'Upload' );
            $this->tp_tabs['featured'] = $this->_x( 'Featured', 'themes' );
            //$tabs['popular']  = _x( 'Popular', 'themes' );
            $this->tp_tabs['new']     = $this->_x( 'Latest', 'themes' );
            $this->tp_tabs['updated'] = $this->_x( 'Recently Updated', 'themes' );
            $non_menu_tabs = array( 'theme-information' ); // Valid actions to perform which do not have a Menu item.
            $tabs = $this->_apply_filters( 'install_themes_tabs', $this->tp_tabs );
            $non_menu_tabs = $this->_apply_filters( 'install_themes_non_menu_tabs', $non_menu_tabs );
            if ( empty( $this->tp_tab ) || ( ! isset( $tabs[ $this->tp_tab ] ) && ! in_array( $this->tp_tab, (array) $non_menu_tabs, true ) ) ) {
                $this->tp_tab = key( $tabs );
            }
            $args = ['page' => $this->tp_paged,'per_page' => $per_page,'fields' => $this->tp_theme_field_defaults,];
            switch ( $this->tp_tab ) {
                case 'search':
                    $type = isset( $_REQUEST['type'] ) ? $this->_tp_unslash( $_REQUEST['type'] ) : 'term';
                    switch ( $type ) {
                        case 'tag':
                            $args['tag'] = array_map( 'sanitize_key', $search_terms );
                            break;
                        case 'term':
                            $args['search'] = $search_string;
                            break;
                        case 'author':
                            $args['author'] = $search_string;
                            break;
                    }
                    if ( ! empty( $this->features ) ) {
                        $args['tag']      = $this->features;
                        $_REQUEST['s']    = implode( ',', $this->features );
                        $_REQUEST['type'] = 'tag';
                    }
                    $this->_add_action( 'install_themes_header', 'install_theme_search_form', 10, 0 );
                    break;
                case 'featured':
                case 'new':
                case 'updated':
                    $args['browse'] = $this->tp_tab;
                    break;
                default:
                    $args = false;
                    break;
            }
            $args = $this->_apply_filters( "install_themes_table_api_args_{$this->tp_tab}", $args );
            if(!$args){ return;}
            $api = $this->_themes_api( 'query_themes', $args );
            if ($api instanceof TP_Error && $this->_init_error( $api ) ) {
                $_onclick = " onclick='document.location.reload(); return false;'";
                $this->_tp_die( "<dt><p>{$api->get_error_message()}</p></dt><dd><a href='#' $_onclick >{$this->__('Try Again')}</a></dd>" );
            }
            $this->items = $api->themes;
            $this->_set_pagination_args(['total_items' => $api->info['results'],'per_page' => $args['per_page'],'infinite_scroll' => true,]);
        }//36
        public function get_no_items() {
            return $this->__( 'No themes match your request.' );
        }//175
        protected function _get_views():array{
            $display_tabs = array();
            foreach ( (array) $this->tp_tabs as $action => $text ) {
                $current_link_attributes = ( $action === $this->tp_tab ) ? " class='current' aria-current='page'" : '';
                $href = $this->_self_admin_url( 'theme_install.php?tab=' . $action );
                $display_tabs[ 'theme_install_' . $action ] = "<dd><a href='$href' $current_link_attributes>$text</a></dd>";
            }
            return $display_tabs;
        }//184
        public function get_display():string{
            $output  = "<div class='adm-segment get-display'><ul><li>";
            $output .= $this->_tp_get_nonce_field( 'fetch_list_' . get_class( $this ), '_async_fetch_list_nonce' );
            $output .= "</li></ul>";
            $output .= "<nav class='block-nav top themes'>";
            $output .= "<header class='block-left actions'>";
            $output .= $this->_get_action( 'install_themes_block_header' );
            $output .= "</header>";
            $output .= "<div class='block-right actions'>";
            $output .= $this->_get_pagination( 'top' );
            $output .= "</div>";
            $output .= "</nav>";
            $output .= "</div><!-- adm-segment get-display -->";
            return $output;
        }//204
        public function get_display_blocks():string{
            $output  = "";
            //$themes = $this->items;(array)
            $themes = ['theme1','theme2'];
            $i=1;
            $output .= "<div class='adm-segment display-blocks'>";
            foreach ( $themes as $theme ) {
                $_i = $i++;
                $output .= "<div class='available-theme installable-theme'>";
                $output .= $this->get_single_block( $theme );
                $output .= "</div><!-- available-theme no:$_i -->";
            }
            $output .= $this->get_theme_installer();
            $output .= "</div><!-- adm-segment display-blocks -->";
            return $output;
        }//232
        public function get_single_block( $theme ):string{
            $i=1;
            if ( empty( $theme ) ){ return false;}
            $name   = $this->_tp_kses( $theme->name, $this->tp_themes_allowedtags );
            $author = $this->_tp_kses( $theme->author, $this->tp_themes_allowedtags );
            $preview_url   = $this->_add_query_arg(['tab' => 'theme-information','theme' => $theme->slug,], $this->_self_admin_url( 'theme_install.php' ));
            $actions = [];
            $install_url = $this->_add_query_arg(['action' => 'install-theme','theme' => $theme->slug,], $this->_self_admin_url( 'update.php' ));
            $update_url = $this->_add_query_arg(['action' => 'upgrade-theme', 'theme' => $theme->slug,],$this->_self_admin_url( 'update.php' ));
            $status = $this->__get_theme_status( $theme );
            switch ( $status ) {
                case 'update_available':
                    $actions[] = sprintf("<dd><a href='%s' class='install-now' title='%s'>%s</a></dd>",$this->_esc_url( $this->_tp_nonce_url( $update_url, 'upgrade-theme_' . $theme->slug )),
                        $this->_esc_attr( sprintf( $this->__( 'Update to version %s' ), $theme->version ) ),$this->__( 'Update' ));
                    break;
                case 'newer_installed':
                case 'latest_installed':
                    $actions[] = sprintf("<dt><p class='install-now' title='%s'>%s</p></dt>",$this->_esc_attr__( 'This theme is already installed and is up to date' ),$this->_x( 'Installed', 'theme' ));
                    break;
                case 'install':
                default:
                    $actions[] = sprintf("<dd><a href='%s' class='install-now' title='%s'>%s</a></dd>",$this->_esc_url( $this->_tp_nonce_url( $install_url, 'install-theme_' . $theme->slug ) ),
                        $this->_esc_attr( sprintf( $this->_x( 'Install %s', 'theme' ), $name ) ),$this->__( 'Install Now' ));
                    break;
            }
            $actions[] = sprintf("<dd><a href='%s' class='install-theme-preview' title='%s'>%s</a></dd>",$this->_esc_url( $preview_url ),
                $this->_esc_attr( sprintf( $this->__( 'Preview &#8220;%s&#8221;' ), $name ) ),$this->__( 'Preview' ));
            $actions = $this->_apply_filters( 'theme_install_actions', $actions, $theme );
            $output  = "<ul class='single-block'><li class='wrapper one'>";
            $output .= "<dt><h3>name $name</h3></dt>";
            $output .= "<dt><h4>" . sprintf($this->__('By %s'), $author ) . "</h4></dt>";
            $output .= "</li><!-- wrapper  one -->";
            $output .= "<li class='wrapper action-links'><ul>";
            foreach ( $actions as $action ){
                $_i = $i++;
                $output .= "<li class='wrapper action'>$action</li><!-- wrapper action no:$_i -->";
            }
            $output .= "</ul></li><!-- wrapper action-links --><li class='wrapper theme-info'>";
            $output .= $this->get_install_theme_info( $theme );
            $output .= "</li><!-- wrapper  theme-info --></ul><!-- single-block -->";
            return $output;
        }//270
        public function get_theme_installer():string{
            $btn_span_set = "<span class='collapse-sidebar-arrow'></span><span class='collapse-sidebar-label'>{$this->__('Collapse Sidebar')}</span>";
            $output  = "<div id='theme_installer' class='tp-full-overlay expanded'><section class='sidebar'><ul><li>";
            $output .= "<dd><a href='#' class='close-full-overlay button'>{$this->__('Close')}</a></dd>";
            $output .= "<dt><span class='theme-install'></span></dt>";
            $output .= "</li><li class='sidebar-content'>";
            $output .= "<div class='install-theme-info'></div>";
            $output .= "</li><!-- sidebar section content --><li>";
            $output .= "<dd><button type='button' class='collapse-sidebar button' aria-label='{$this->_esc_attr('Collapse Sidebar')}' aria-expanded='true'>$btn_span_set</button></dd>";
            $output .= "</li></ul>";
            $output .= "</section><!-- theme_installer sidebar section -->";
            $output .= "</div><!-- tp-full-overlay expanded -->";
            return $output;
        }//389
        public function get_theme_installer_single( $theme ):string{
            $output  = "<div id='theme_installer' class='tp-full-overlay single-theme'><section class='sidebar'>";
            $output .= $this->get_install_theme_info( $theme );
            $output .= "</section><!-- theme_installer sidebar section --><main>";
            $output .= "<iframe src='{$this->_esc_url($theme->preview_url)}'></iframe>";//todo replace iframe with a suitable alternative, perhaps svg 'foreign object'?
            $output .= "</main><!-- theme_installer main --></div><!-- theme_installer -->";
            return $output;
        }//418
        public function get_install_theme_info( $theme ):string{
            if ( empty( $theme )){ return false;}
            $name   = $this->_tp_kses( $theme->name, $this->tp_themes_allowedtags );
            $author = $this->_tp_kses( $theme->author, $this->tp_themes_allowedtags );
            $install_url = $this->_add_query_arg(['action' => 'install-theme', 'theme' => $theme->slug,], $this->_self_admin_url('update.php'));
            $update_url = $this->_add_query_arg(['action' => 'upgrade-theme', 'theme'  => $theme->slug,], $this->_self_admin_url('update.php'));
            $status = $this->__get_theme_status( $theme );
            $output  = "<ul class='block install-theme-info'><li class='wrapper theme-info'>";
            switch ( $status ) {
                case 'update_available':
                    $output .= sprintf("<dd><a href='%s' class='theme-install button button-primary' title='%s'>%s</a></dd>",
                        $this->_esc_url( $this->_tp_nonce_url( $update_url, 'upgrade_theme_' . $theme->slug )),
                        $this->_esc_attr( sprintf( $this->__( 'Update to version %s' ), $theme->version ) ),$this->__( 'Update' ));
                    break;
                case 'newer_installed':
                case 'latest_installed':
                    $output .= sprintf("<dt><span class='theme-install' title='%s'>%s</span></dt>",$this->_esc_attr__( 'This theme is already installed and is up to date' ),
                        $this->_x( 'Installed', 'theme' ));
                    break;
                case 'install':
                default:
                    $output .= sprintf("<dd><a href='%s' class='theme-install button button-primary'>%s</a></dd>",
                        $this->_esc_url( $this->_tp_nonce_url( $install_url, 'install-theme_' . $theme->slug ) ),$this->__( 'Install' ));
                    break;
            }
            $output .= "<dt><h3 class='theme-name'>$name <span class='theme-by'>";
            $output .=  sprintf($this->__('By %s'), $author);
            $output .= "</span></h3></dt>";
            if(isset($theme->screenshot_url)){
                $output .= "<dd><img src='{$this->_esc_url($theme->screenshot_url)}' class='theme-screenshot' alt=''/></dd>";
            }
            $output .= "</li><!-- wrapper li theme-info --><li class='wrapper theme-details'><ul class='theme-details'>";
            $this->_tp_get_star_rating(['rating' => $theme->rating,'type' => 'percent', 'number' => $theme->num_ratings,]);
            $output .= "<li class='theme-version'>";
            $output .= "<p><strong>{$this->__('Version:')}</strong>{$this->_tp_kses( $theme->version, $this->tp_themes_allowedtags )}</p>";
            $output .= "</li><li class='theme-description'>";
            $output .= "<dd>{$this->_tp_kses( $theme->description, $this->tp_themes_allowedtags )}</dd>";
            $output .= "</li></ul></li><!-- wrapper li theme-details --><li>";
            $output .= "<input type='hidden' class='theme-preview-url' value='{$this->_esc_url($theme->preview_url)}'/>";
            $output .= "</li></ul><!-- ul install-theme-info -->";
            return $output;
        }//438
        protected function _get_js_vars( ...$extra_args):string{
            $tab = $this->tp_tab;
            $type = $this->tp_type;
            return parent::_get_js_vars( compact( 'tab', 'type','extra_args' ) );
        }//540
        private function __get_theme_status( $theme ):string{
            $status = 'install';
            $installed_theme = $this->_tp_get_theme( $theme->slug );
            if ( $installed_theme->exists() ) {
                if ( version_compare( $installed_theme->get_theme( 'Version' ), $theme->version, '=' ) ) {
                    $status = 'latest_installed';
                } elseif ( version_compare( $installed_theme->get_theme( 'Version' ), $theme->version, '>' ) ) {
                    $status = 'newer_installed';
                } else { $status = 'update_available';}
            }
            return $status;
        }//553
    }
}else{die;}