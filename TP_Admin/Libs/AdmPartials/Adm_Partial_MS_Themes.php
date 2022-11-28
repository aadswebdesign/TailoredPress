<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-11-2022
 * Time: 10:52
 */
namespace TP_Admin\Libs\AdmPartials;
use TP_Core\Libs\TP_Theme;
if(ABSPATH){
    class Adm_Partial_MS_Themes extends Adm_Partials{
        private $__has_items;
        protected $_show_auto_updates = true;
        public $site_id;
        public $is_site_themes;
        public function __construct( ...$args){
            parent::__construct(['plural' => 'themes','screen' => $args['screen'] ?? null,]);
            $this->tp_page = $this->get_pagenum();
            $this->is_site_themes = 'site-themes-network' === $this->_screen->id;
            if ( $this->is_site_themes ) {
                $this->site_id = isset( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : 0;
            }
            $this->_show_auto_updates = $this->tp_is_auto_update_enabled_for_type( 'theme' ) &&
                ! $this->is_site_themes && $this->_current_user_can( 'update_themes' );
        }//46
        protected function get_classes():array {
            return ['wide-fat'];
        }//76
        public function async_user_can() {
            if ( $this->is_site_themes ) { return $this->_current_user_can( 'manage_sites' ); }
            return $this->_current_user_can( 'manage_network_themes' );
        }
        public function prepare_items():void{
            $auto_updates = null;
            $this->_tp_reset_vars(['orderby','order','s']);
            $themes = ['all' => $this->_apply_filters( 'all_themes', $this->_tp_get_themes() ),
                'search' => [],'enabled' => [],'disabled' => [],'upgrade' => [],
                'broken' => $this->is_site_themes ? [] : $this->_tp_get_themes(['errors' => true]),];
            if ( $this->_show_auto_updates ) {
                $auto_updates = (array) $this->_get_site_option( 'auto_update_themes',[]);
                $themes['auto-update-enabled']  = [];
                $themes['auto-update-disabled'] = [];
            }
            if ( $this->is_site_themes ) {
                $themes_per_page = $this->_get_items_per_page( 'site_themes_network_per_page' );
                $allowed_where   = 'site';
            } else {
                $themes_per_page = $this->_get_items_per_page( 'themes_network_per_page' );
                $allowed_where   = 'network';
            }
            $current      = $this->_get_site_transient( 'update_themes' );
            $maybe_update = $this->_current_user_can( 'update_themes' ) && ! $this->is_site_themes && $current;
            foreach ( (array) $themes['all'] as $key => $theme ) {
                if ( $this->is_site_themes && $theme->is_allowed( 'network' ) ) {
                    unset( $themes['all'][ $key ] );
                    continue;
                }
                if ( $maybe_update && isset( $current->response[ $key ] ) ) {
                    $themes['all'][ $key ]->update = true;
                    $themes['upgrade'][ $key ]     = $themes['all'][ $key ];
                }
                $filter                    = $theme->is_allowed( $allowed_where, $this->site_id ) ? 'enabled' : 'disabled';
                $themes[ $filter ][ $key ] = $themes['all'][ $key ];
                $theme_data = ['update_supported' => $theme->update_supported ?? true,];
                if ( isset( $current->response[ $key ] ) ) {
                    $theme_data = $this->_tp_array_merge( (array) $current->response[ $key ], $theme_data );
                } elseif ( isset( $current->no_update[ $key ] ) ) {
                    $theme_data = $this->_tp_array_merge( (array) $current->no_update[ $key ], $theme_data );
                } else {$theme_data['update_supported'] = false;}
                $theme->update_supported = $theme_data['update_supported'];
                $filter_payload = ['theme' => $key,'new_version' => '','url' => '','package' => '','requires' => '','requires_php' => '',];
                $filter_payload = (object) $this->_tp_array_merge( $filter_payload, array_intersect_key( $theme_data, $filter_payload ) );
                $auto_update_forced = $this->tp_is_auto_update_forced_for_item( 'theme', null, $filter_payload );
                if ( ! is_null( $auto_update_forced )){$theme->auto_update_forced = $auto_update_forced;}
                if ( $this->_show_auto_updates ) {
                    $enabled = in_array( $key, $auto_updates, true ) && $theme->update_supported;
                    if ( isset( $theme->auto_update_forced ) ) {$enabled = (bool) $theme->auto_update_forced;}
                    if ( $enabled ) {$themes['auto-update-enabled'][ $key ] = $theme;}
                    else {$themes['auto-update-disabled'][ $key ] = $theme;}
                }
            }
            if ( $this->tp_search ) {
                $this->tp_status           = 'search';
                $themes['search'] = array_filter( array_merge( $themes['all'], $themes['broken'] ),[$this, '_search_callback']);
            }
            $this->tp_totals    = [];
            $js_themes = [];
            foreach ( $themes as $type => $list ) {
                $this->tp_totals[ $type ]    = count( $list );
                $js_themes[ $type ] = array_keys( $list );
            }
            if ( empty( $themes[ $this->tp_status ] ) && ! in_array( $this->tp_status, array( 'all', 'search' ), true ) ) {
                $this->tp_status = 'all';
            }
            $this->items = $themes[ $this->tp_status ];
            TP_Theme::sort_by_name( $this->items );
            $this->__has_items = ! empty( $themes['all'] );
            $total_this_page = $this->tp_totals[ $this->tp_status ];
            $this->tp_localize_script(
                'updates','_tpUpdatesItemCounts',
                ['themes' => $js_themes, 'totals' => $this->_tp_get_update_data(),]
            );//todo, should become on an other way?
            if ( $this->tp_orderby ) {
                $this->tp_orderby = ucfirst( $this->tp_orderby );
                $this->tp_order   = strtoupper( $this->tp_order );
                if('Name' === $this->tp_orderby){
                    if('ASC' === $this->tp_order){$this->items = array_reverse( $this->items);}
                } else {uasort( $this->items, array( $this, '_order_callback' ) );}
            }
            $start = ( $this->tp_page - 1 ) * $themes_per_page;
            if ( $total_this_page > $themes_per_page ) {
                $this->items = array_slice( $this->items, $start, $themes_per_page, true );
            }
            $this->_set_pagination_args(
                ['total_items' => $total_this_page, 'per_page'=> $themes_per_page,]
            );
        }//100
        protected function _search_callback(TP_Theme $theme ):bool{
            static $term = null;
            if(is_null($term)){$term = $this->_tp_unslash($_REQUEST['s']);}
            foreach ( array( 'Name', 'Description', 'Author', 'Author', 'AuthorURI' ) as $field ) {
                if ( false !== stripos( $theme->display( $field, false, true ), $term ) ) {
                    return true; }
            }
            if ( false !== stripos( $theme->get_stylesheet(), $term )){
                return true;}
            if ( false !== stripos( $theme->get_template(), $term ) ) {
                return true;}
            return false;
        }//266
        protected function _order_callback( $theme_a, $theme_b ):bool{
            $a = $theme_a[ $this->tp_orderby ];
            $b = $theme_b[ $this->tp_orderby ];
            if ( $a === $b ){ return 0;}
            if ( 'DESC' === $this->tp_order ) { return ( $a < $b ) ? 1 : -1;}
            return ( $a < $b ) ? -1 : 1;
        }//298
        public function get_no_items(){
            $output  = "";
            if ( $this->__has_items ) { $output .= $this->__( 'No themes found.' );
            } else {$output .= $this->__( 'No themes are currently available.' );}
            return "<dt>$output</dt>";
        }//317
        public function get_blocks(){
            $blocks = ['cb' => "<dd><input type='checkbox' /></dd>",'dt_open' => '<dt>','name' => $this->__( 'Theme' ),'description' => $this->__( 'Description' ),'dt_close' => '</dt>',];
            if ( $this->_show_auto_updates ) {$blocks['auto-updates'] = $this->__( 'Automatic Updates' );}
            return $blocks;
        }//328
        protected function _get_sortable_blocks():array{
            return ['name' => 'name',];
        }//345
        protected function _get_primary_name():string {
            return 'name';
        }//358
        protected function _get_views():array{
            $status_links = [];
            $span_count = "<span class='count'>(%s)</span>";
            $text = null;
            foreach ((array) $this->tp_totals as $type => $count ) {
                if( ! $count ){continue;}
                switch ( $type ) {/* translators: %s: Number of themes. */
                    case 'all':
                        $text = "<dt>{$this->_nx("All $span_count","All $span_count",$count,'themes')}</dt>";
                        break;
                    case 'enabled':
                        $text = "<dt>{$this->_nx("Enabled $span_count","Enabled $span_count",$count,'themes')}</dt>";
                        break;
                    case 'disabled':
                        $text = "<dt>{$this->_nx("Disabled $span_count","Disabled $span_count",$count,'themes')}</dt>";
                        break;
                    case 'upgrade':
                        $text = "<dt>{$this->_nx("Update Available $span_count","Update Available $span_count",$count,'themes')}</dt>";
                        break;
                    case 'broken':
                        $text = "<dt>{$this->_nx("Broken $span_count","Broken $span_count",$count,'themes')}</dt>";//;
                        break;
                    case 'auto-update-enabled':
                        $text = "<dt>{$this->_n("Auto-updates Enabled $span_count","Auto-updates Enabled $span_count",$count)}</dt>";
                        break;
                    case 'auto-update-disabled':
                        $text = "<dt>{$this->_n("Auto-updates Disabled $span_count","Auto-updates Disabled $span_count",$count)}</dt>";
                        break;
                }
                if ( $this->is_site_themes ) {
                    $url = 'site_themes.php?id=' . $this->site_id;
                } else {$url = 'themes.php';}
                if ( 'search' !== $type ) {
                    $status_links[ $type ] = sprintf("<dd><a href='%s' %s>%s</a></dd>",
                        $this->_esc_url( $this->_add_query_arg( 'theme_status', $type, $url ) ),
                        ( $type === $this->tp_status ) ? ' class="current" aria-current="page"' : '',
                        sprintf( $text, $this->_number_format_i18n( $count ) ));
                }
            }
            return $status_links;
        }//367
        protected function _get_bulk_actions():array{
            $actions = [];
            if ( 'enabled' !== $this->tp_status ) {
                $actions['enable-selected'] = $this->is_site_themes ? $this->__( 'Enable' ) : $this->__( 'Network Enable' );
            }
            if ( 'disabled' !== $this->tp_status ) {
                $actions['disable-selected'] = $this->is_site_themes ? $this->__( 'Disable' ) : $this->__( 'Network Disable' );
            }
            if ( ! $this->is_site_themes ) {
                if($this->_current_user_can('update_themes')){$actions['update-selected'] = $this->__( 'Update' );}
                if($this->_current_user_can('delete_themes')){$actions['delete-selected'] = $this->__('Delete');}
            }
            if ( $this->_show_auto_updates ) {
                if ( 'auto-update-enabled' !== $this->tp_status ) {
                    $actions['enable-auto-update-selected'] = $this->__( 'Enable Auto-updates' );
                }
                if ( 'auto-update-disabled' !== $this->tp_status ) {
                    $actions['disable-auto-update-selected'] = $this->__( 'Disable Auto-updates' );
                }
            }
            return $actions;
        }//464
        public function get_display_rows():string {
            $output  = "";
            foreach ( $this->items as $theme ) {
                $output .= $this->get_single_block( $theme );
            }
            return $output;
        }//498
        public function get_cb_block(TP_Theme $item ):string{
            $theme = $item;
            $checkbox_id = 'checkbox_' . md5( $theme->get_theme( 'Name' ) );
            $output  = "<dd><input name='checked[]' id='$checkbox_id' type='checkbox' value='{$this->_esc_attr($theme->get_stylesheet())} /></dd>";
            $output .= "<dt><label for='$checkbox_id' class='screen-reader-text'>{$this->__('Select')}: {$theme->display( 'Name' )}</label></dt>";
            return $output;
        }//512
        public function get_block_name(TP_Theme $theme ):string{
            $context = $this->tp_status;
            if ( $this->is_site_themes ) {
                $url     = "site_themes.php?id={$this->site_id}&amp;";
                $allowed = $theme->is_allowed( 'site', $this->site_id );
            } else {
                $url     = 'themes.php?';
                $allowed = $theme->is_allowed( 'network' );
            }
            $actions = ['enable' => '','disable' => '','delete' => '',];
            $stylesheet = $theme->get_stylesheet();
            $theme_key  = urlencode( $stylesheet );
            if ( ! $allowed ) {
                if (!$theme->errors()){
                    $url = $this->_add_query_arg(['action' => 'enable','theme' => $theme_key,
                        'paged' => $this->tp_page,'s' => $this->tp_search,],$url);
                    if ( $this->is_site_themes ) {
                        $aria_label = sprintf( "<dt>{$this->__( 'Enable %s' )}</dt>", $theme->display( 'Name' ) );
                    } else {
                        $aria_label = sprintf( "<dt>{$this->__( 'Network Enable %s' )}</dt>", $theme->display( 'Name' ) );
                    }
                    $actions['enable'] = sprintf("<dd><a href='%s' class='edit' aria-label='%s'>%s</a></dd>",
                        $this->_esc_url( $this->_tp_nonce_url( $url, 'enable-theme_' . $stylesheet ) ),
                        $this->_esc_attr( $aria_label ),
                        ($this->is_site_themes ? $this->__("<dt>Enable</dt>") : $this->__("<dt>Network Enable</dt>")));
                }
            }else{
                $url = $this->_add_query_arg(
                    ['action' => 'disable','theme' => $theme_key,'paged' => $this->tp_page,'s' => $this->tp_search,],
                    $url);
                if ( $this->is_site_themes ) {
                    $aria_label = sprintf( $this->__( 'Disable %s' ), $theme->display( 'Name' ) );
                } else {$aria_label = sprintf( $this->__( 'Network Disable %s' ), $theme->display( 'Name' ) );}
                $actions['disable'] = sprintf( "<dd><a href='%s' aria-label='%s'>%s</a></dd>",
                    $this->_esc_url( $this->_tp_nonce_url( $url, 'disable-theme_' . $stylesheet ) ),
                    $this->_esc_attr( $aria_label ), ( $this->is_site_themes ? $this->__('Disable') : $this->__('Network Disable')));
            }
            if ( ! $allowed && ! $this->is_site_themes && $this->_current_user_can('delete_themes') && $this->_get_option( 'stylesheet' ) !== $stylesheet && $this->_get_option( 'template' ) !== $stylesheet){
                $url = $this->_add_query_arg(['action' => 'delete-selected',
                    'checked[]' => $theme_key,'theme_status' => $context,
                    'paged' => $this->tp_page,'s' => $this->tp_search,],'themes.php');
                $aria_label = sprintf( $this->_x( 'Delete %s', 'theme' ), $theme->display( 'Name' ) );
                $actions['delete'] = sprintf("<dd><a href='%s' class='delete' aria-label='%s'>%s</a></dd>",
                    $this->_esc_url( $this->_tp_nonce_url( $url, 'bulk-themes' ) ),
                    $this->_esc_attr( $aria_label ),$this->__( 'Delete' ));
            }
            $actions = $this->_apply_filters( 'theme_action_links', array_filter( $actions ), $theme, $context );
            $actions = $this->_apply_filters( "theme_action_links_{$stylesheet}", $actions, $theme, $context );
            return $this->_get_actions( $actions, true );
        }//533
        public function get_block_description(TP_Theme $theme ):string{
            $output  = "";
            if ( $theme->errors() ) {
                $pre = 'broken' === $this->tp_status ? $this->__( 'Broken Theme:' ) . ' ' : '';
                $output .= "<dt><p><strong class='error-message'>{$pre}{$theme->errors()->get_error_message()}</strong></p></dt>";
            }
            if ( $this->is_site_themes ) {$allowed = $theme->is_allowed( 'site', $this->site_id );}
            else {$allowed = $theme->is_allowed( 'network' );}
            $class = ! $allowed ? 'inactive' : 'active';
            if ( ! empty( $totals['upgrade'] ) && ! empty( $theme->update ) ) {
                $class .= ' update';
            }
            $output .= "<li><dt class='theme-description'><p>{$theme->display( 'Description' )}</p></dt></li>";
            $output .= "<li class='wrapper $class second theme-version-author-uri'><dd>";
            $stylesheet = $theme->get_stylesheet();
            $theme_meta = [];
            if ( $theme->get_theme( 'Version' ) ) {
                $theme_meta[] = sprintf( $this->__( 'Version %s' ), $theme->display( 'Version' ) );
            }
            $theme_meta[] = sprintf( $this->__( 'By %s' ), $theme->display( 'Author' ) );
            if ( $theme->get_theme( 'ThemeURI' ) ) {
                $aria_label = sprintf( $this->__( 'Visit theme site for %s' ), $theme->display( 'Name' ) );
                $theme_meta[] = sprintf("<a href='%s' aria-label='%s'>%s</a>", $theme->display( 'ThemeURI' ), $this->_esc_attr( $aria_label ), $this->__( 'Visit Theme Site' ));
            }
            if ( $theme->parent() ) {
                $theme_meta[] = sprintf($this->__( 'Child theme of %s' ),"<strong>{$theme->parent()->display( 'Name' )}</strong>" );
            }
            $theme_meta = $this->_apply_filters( 'theme_row_meta', $theme_meta, $stylesheet, $theme, $this->tp_status );
            $output .= implode( ' | ', $theme_meta );
            $output .= "</dd></li><!-- wrapper theme-version-author-uri -->";
            return $output;
        }//689 todo
        public function get_auto_updates(TP_Theme $theme ):string{
            static $auto_updates, $available_updates;
            if ( ! $auto_updates ) {$auto_updates = (array) $this->_get_site_option( 'auto_update_themes',[]);}
            if ( ! $available_updates ){$available_updates = $this->_get_site_transient('update_themes');}
            $stylesheet = $theme->get_stylesheet();
            if ( isset( $theme->auto_update_forced ) ) {
                if ( $theme->auto_update_forced ) {$text = $this->__('Auto-updates enabled');}
                else {$text = $this->__('Auto-updates disabled');}
                $action     = 'unavailable';
                $time_class = ' hidden';
            }elseif ( empty( $theme->update_supported ) ) {
                $text       = '';
                $action     = 'unavailable';
                $time_class = ' hidden';
            } elseif ( in_array( $stylesheet, $auto_updates, true ) ) {
                $text       = $this->__( 'Disable auto-updates' );
                $action     = 'disable';
                $time_class = '';
            } else {
                $text       = $this->__( 'Enable auto-updates' );
                $action     = 'enable';
                $time_class = ' hidden';
            }
            $query_args = ['action' => "{$action}-auto-update",'theme' => $stylesheet,
                'paged' => $this->tp_page,'theme_status' => $this->tp_status,];
            $url = $this->_add_query_arg( $query_args, 'themes.php' );
            $html = '';
            if ( 'unavailable' === $action ) { $html .= "<dt><span class='label'>$text</span></dt>";}
            else{
                $html .= sprintf("<dd><a href='%s' class='toggle-auto-update aria-button-if-js' data-tp_action='%s'>",$this->_tp_nonce_url( $url, 'updates' ),$action);
                $html .= "<span class='dashicons dashicons-update spin hidden' aria-hidden='true'></span>";
                $html .= "<span class='label'>$text</span></a></dd>";
            }
            if ( isset( $available_updates->response[ $stylesheet ] ) ) {
                $html .= sprintf("<dt class='auto-update-time%s'>%s</dt>",
                    $time_class,$this->tp_get_auto_update_message());
            }
            $output  = $this->_apply_filters( 'theme_auto_update_setting_html', $html, $stylesheet, $theme );
            $output .= "<dt class='notice notice-error notice-alt inline hidden'><p></p></dt>";
            return $output;
        }//771
        public function get_column_default(TP_Theme $item, $column_name ):string{
            return $this->_get_action('manage_themes_custom_column',$column_name,$item->get_stylesheet(),$item ); // Directory name of the theme. // Theme object.
        }//866
        protected function _get_single_blocks(TP_Theme $item ):string{
            @list( $columns, $hidden ) = $this->_get_block_info();//fixme not used:, $sortable , $primary
            $output  = "";
            foreach ( $columns as $column_name => $column_display_name ) {
                $extra_classes = '';
                if ( in_array( $column_name, $hidden, true ) ) {
                    $extra_classes .= ' hidden';
                }
                switch ( $column_name ) {
                    case 'cb':
                        $output .= "<li class='wrapper check-block'>{$this->get_cb_block( $item )}</li><!-- wrapper check-column -->";
                        break;
                    case 'name':
                        $active_theme_label = '';
                        if ( ! empty( $this->site_id ) ) {
                            $stylesheet = $this->_get_blog_option( $this->site_id, 'stylesheet' );
                            $template   = $this->_get_blog_option( $this->site_id, 'template' );
                            if ( $item->_get_template() === $template ) {
                                $active_theme_label = ' &mdash; ' . $this->__( 'Active Theme' );}
                            if ( $stylesheet !== $template && $item->get_stylesheet() === $stylesheet ) {
                                $active_theme_label = ' &mdash; ' . $this->__( 'Active Child Theme' );}
                        }
                        $output .= "<li class='wrapper theme-title primary{$extra_classes}'><strong>{$item->display( 'Name' )}:{$active_theme_label}</strong>{$this->get_block_name( $item )}</li><!-- wrapper theme-title  -->";
                        break;
                    case 'description':
                        $output .= "<li class='wrapper description desc{$extra_classes}'>{$this->get_block_description( $item )}</li><!-- wrapper description desc  -->";
                        break;
                    case 'auto-updates':
                        $output .= "<li class='wrapper  description desc{$extra_classes}'>{$this->get_auto_updates( $item )}</li><!-- wrapper  description desc -->";
                        break;
                    default:
                        $output .= "<li class='wrapper  $column_name block-$column_name{$extra_classes}'>{$this->get_column_default( $item, $column_name )}</li><!-- wrapper block-? -->";
                        break;
                }
            }
            return $output;
        }//891
        public function get_single_block(TP_Theme $theme ):string{
            if ( $this->is_site_themes ) { $allowed = $theme->is_allowed( 'site', $this->site_id );}
            else{$allowed = $theme->is_allowed( 'network' );}
            $stylesheet = $theme->get_stylesheet();
            $class = ! $allowed ? 'inactive' : 'active';
            if(!empty($totals['upgrade']) && !empty($theme->update)){$class .= ' update';}
            $output  = sprintf("<li class='wrapper single-block %s' data-slug='%s'>",$this->_esc_attr( $class ),$this->_esc_attr( $stylesheet ));
            $output .= $this->get_single_block( $theme )."</li><!-- wrapper single-block -->";
            ob_start();
            if ( $this->is_site_themes ) {
                $this->_remove_action( "after_theme_row_$stylesheet", 'tp_theme_update_row' );
            }
            $output .= ob_get_clean();
            $output .= $this->_get_action( 'after_theme_row', $stylesheet, $theme, $this->tp_status );
            $output .= $this->_get_action( "after_theme_row_{$stylesheet}", $stylesheet, $theme, $this->tp_status );
            return $output;
        }//967
    }
}else{die;}