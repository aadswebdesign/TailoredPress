<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-10-2022
 * Time: 20:05
 */
namespace TP_Admin\Libs\AdmComponents;
use TP_Admin\Traits\AdminNavMenu\_adm_nav_menu_01;
use TP_Admin\Traits\AdminNavMenu\_adm_nav_menu_02;
use TP_Admin\Traits\AdminPageMenus\_adm_page_menu_02;
use TP_Admin\Traits\AdminPageMenus\_adm_page_menu_03;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Constructs\_construct_menu;
use TP_Core\Traits\Constructs\_construct_page;
use TP_Core\Traits\Constructs\_construct_utils;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_03;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Options\_option_01;
if(ABSPATH){
    class Adm_Header_Menu{
        use _adm_nav_menu_01,_adm_nav_menu_02, _adm_page_menu_02,_adm_page_menu_03;
        use _capability_01, _construct_menu,_construct_page,_construct_utils;
        use _filter_01, _formats_01,_formats_02, _formats_03, _formats_07,_formats_08;
        use _I10n_01,_I10n_02,_I10n_03,_I10n_04, _methods_03, _option_01;
        protected $_args;
        public function __construct($menu_args = null){
            $this->_args = $menu_args;
            $this->tp_self = preg_replace( '|^.*/TP_Admin/Network/|i', '', $_SERVER['PHP_SELF'] );
            $this->tp_self = preg_replace( '|^.*/TP_Admin/|i', '', $this->tp_self );
            $this->tp_self = preg_replace( '|^.*/TP_Content/TP_Library/|i', '', $this->tp_self );
            $this->tp_self = preg_replace( '|^.*/TP_Content/TP_Library/Multi_Site_Libs/|i', '', $this->tp_self );
            $this->tp_parent_file = $this->_apply_filters( 'parent_file', $this->tp_parent_file );
            $this->tp_submenu_file = $this->_apply_filters( 'submenu_file', $this->tp_submenu_file, $this->tp_parent_file );
        }
        /**
         * @param $menu
         * @param $submenu
         * @param bool|string $submenu_as_parent
         * @return string
         */
        private function __menu_output($menu, $submenu, $submenu_as_parent = true):string{
            $output  = "";
            $this->tp_self;
            $this->tp_parent_file;
            $this->tp_submenu_file;
            $this->tp_library_page;
            $this->tp_typenow;
            $first = true;
            foreach((array)$menu as $key => $item){
                $admin_is_parent = false;
                $class           = [];
                $aria_attributes = '';
                $aria_hidden     = '';
                $is_separator    = false;
                if ( $first ) {
                    $class[] = 'tp-first-item';
                    $first   = false;
                }
                $submenu_items = array();
                if ( ! empty( $submenu[ $item[2] ] ) ) {
                    $class[]       = 'tp-has-submenu';
                    $submenu_items = $submenu[ $item[2] ];
                }
                if ( ( $this->tp_parent_file && $item[2] === $this->tp_parent_file ) || ( empty( $this->tp_typenow ) && $this->tp_self === $item[2] ) ) {
                    if ( ! empty( $submenu_items ) ) { $class[] = 'tp-has-current-submenu tp-menu-open'; }
                    else {
                        $class[]          = 'current';
                        $aria_attributes .= " aria-current='page'";
                    }
                }else{
                    $class[] = 'tp-not-current-submenu';
                    if ( ! empty( $submenu_items ) ) { $aria_attributes .= " aria-haspopup='true'";}
                }
                if ( ! empty( $item[4] ) ) { $class[] = $this->_esc_attr( $item[4] );}
                $_class = implode( ' ', $class );
                $class = $class ? " class='$_class'" : '';
                $_id = preg_replace( '|[^a-zA-Z0-9_:.]|', '_', $item[5] );
                $id = !empty($item[5]) ? " id='$_id'" : '';
                $img       = '';
                $img_style = '';
                $img_class = ' dashicons-before';
                if ( false !== strpos( $class, 'tp-menu-separator' ) ) { $is_separator = true;}
                if ( ! empty( $item[6] ) ) { //todo get rid of those br elements
                    $img = "<img src='{$item[6]}' alt=''/>";
                    if ( 'none' === $item[6] || 'div' === $item[6] ) { $img = '<br />';
                    } elseif ( 0 === strpos( $item[6], 'data:image/svg+xml;base64,' ) ) {
                        $img = '<br />';
                        $img_style = " style='background-image:url({$this->_esc_attr($item[6])});'";
                        $img_class = ' svg';
                    } elseif ( 0 === strpos( $item[6], 'dashicons-' ) ) {
                        $img       = '<br />';
                        $img_class = ' dashicons-before ' . $this->_sanitize_html_class( $item[6] );
                    }
                }
                $arrow = "<div class='tp-menu-arrow'></div>";//I'm stubborn and leave '</div></div>' out for now
                $title = $this->_tp_texturize( $item[0] );
                if ( $is_separator ) { $aria_hidden = " aria-hidden='true'";}
                $output .= "\n\t<li$class$id$aria_hidden>";
                if ( $is_separator ) {
                    $output .= "<div class='separator'></div>";
                }elseif($submenu_as_parent && ! empty( $submenu_items )){//todo, this block needs lookup
                    $submenu_items = array_values( $submenu_items );  // Re-index.
                    $menu_hook     = $this->_get_adm_library_page_hook( $submenu_items[0][2], $item[2] );
                    $menu_file     = $submenu_items[0][2];
                    $pos           = strpos( $menu_file, '?' );
                    if ( false !== $pos ) { $menu_file = substr( $menu_file, 0, $pos );}
                    if (!empty($menu_hook) || (('index.php' !== $submenu_items[0][2]) && file_exists( TP_CONTENT_LIBS . "/$menu_file" ))){
                        $admin_is_parent = true;
                        $output .= "<a href='admin.php?page={$submenu_items[0][2]}' $class $aria_attributes>$arrow<div class='tp-menu-image$img_class' $img_style aria-hidden='true'>$img</div><div class='tp-menu-name'>$title</div></a>";
                    } else { $output .= "\n\t<a href='{$submenu_items[0][2]}' $class $aria_attributes>$arrow<div class='tp-menu-image$img_class' $img_style aria-hidden='true'>$img</div><div class='tp-menu-name'>$title</div></a>"; }
                }elseif ( ! empty( $item[2] ) && $this->_current_user_can( $item[1] ) ) {
                    $menu_hook = $this->_get_adm_library_page_hook($item[2],'admin.php');
                    $menu_file = $item[2];
                    $pos       = strpos( $menu_file, '?' );
                    if ( false !== $pos ) { $menu_file = substr( $menu_file, 0, $pos );}
                    if ( ! empty( $menu_hook )|| ( ( 'index.php' !== $item[2] ) && file_exists( TP_CONTENT_LIBS . "/$menu_file" ))){
                        $admin_is_parent = true;
                        $output .= "\n\t<a href='admin.php?page={$item[2]}' $class $aria_attributes>$arrow<div class='tp-menu-image $img_class' $img_style aria-hidden='true'>$img</div><div class='tp-menu-name'>{$item[0]}</div></a>";
                    } else { $output .= "\n\t<a href='{$item[2]}' $class $aria_attributes>$arrow<div class='tp-menu-image $img_class' $img_style aria-hidden='true'>$img</div><div class='tp-menu-name'>{$item[0]}</div></a>";}
                }
                if ( ! empty( $submenu_items ) ) {
                    $output .= "\n\t<ul class='tp-submenu tp-submenu-wrap'>";
                    $output .= "<li class='tp-submenu-head' aria-hidden='true'>{$item[0]}</li>";
                    $first = true;
                    foreach ( $submenu_items as $sub_key => $sub_item ) {
                        if (!$this->_current_user_can( $sub_item[1])){ continue;}
                        $class = [];
                        $aria_attributes = '';
                        if ( $first ) {
                            $class = ['tp-first-item'];
                            $first   = false;
                        }
                        $menu_file = $item[2];
                        $pos       = strpos( $menu_file, '?' );
                        if ( false !== $pos ) {$menu_file = substr( $menu_file, 0, $pos );}
                        // Handle current for post_type=post|page|foo pages, which won't match $self.
                        $self_type = ! empty( $this->tp_typenow ) ? $this->tp_self . '?post_type=' . $this->tp_typenow : 'nothing';
                        if ( isset( $this->tp_submenu_file ) ) {
                            if ( $this->tp_submenu_file === $sub_item[2] ) {
                                $class = ['current'];
                                $aria_attributes .= " aria-current='page'";
                            }
                            // If library_page is set the parent must either match the current page or not physically exist.
                            // This allows library pages with the same hook to exist under different parents.
                        } elseif ((!isset( $this->tp_library_page ) && $this->tp_self === $sub_item[2] ) || ( isset($this->tp_library_page ) && $this->tp_library_page === $sub_item[2] && ( $item[2] === $self_type || $item[2] === $this->tp_self || file_exists( $menu_file ) === false))){
                            $class = ['current'];
                            $aria_attributes .= " aria-current='page'";
                        }
                        if ( ! empty( $sub_item[4] ) ) { $class = [$this->_esc_attr( $sub_item[4] )];}
                        $class = $class ? " class='". implode( ' ', $class ) ."'" : '';
                        $menu_hook = $this->_get_adm_library_page_hook( $sub_item[2], $item[2] );
                        $sub_file  = $sub_item[2];
                        $pos       = strpos( $sub_file, '?' );
                        if ( false !== $pos ) { $sub_file = substr( $sub_file, 0, $pos );}
                        $title = $this->_tp_texturize( $sub_item[0] );
                        if (!empty($menu_hook) || (('index.php' !== $sub_item[2] ) && file_exists(TP_CONTENT_LIBS . "/$sub_file"))){
                            // If admin.php is the current page or if the parent exists as a file in the plugins or admin directory.
                            if (file_exists( $menu_file ) || ( ! $admin_is_parent && file_exists( TP_CONTENT_LIBS . "/$menu_file" ) && ! is_dir( TP_CONTENT_LIBS . "/{$item[2]}" ) )) {
                                $sub_item_url = $this->_add_query_arg( array( 'page' => $sub_item[2] ), $item[2] );
                            } else { $sub_item_url = $this->_add_query_arg( array( 'page' => $sub_item[2] ), 'admin.php' );}
                            $sub_item_url = $this->_esc_url( $sub_item_url );
                            $output .= "<li$class><a href='$sub_item_url' $class $aria_attributes >$title</a></li>";
                        } else {
                            $output .= "<li$class><a href='{$sub_item[2]}' $class $aria_attributes >$title</a></li>";
                        }
                    }
                    $output .= '</ul>';
                }
                $output .= "</li>";
            }
            $output .= "<li id='collapse_menu' class='hide-if-no-js'>";
            $output .= "<button type='button' id='collapse_button' aria-label='{$this->_esc_attr__('Collapse Main menu')}' aria-expanded='true'>{$this->_esc_html('')}";
            $output .= "<span class='collapse-button-icon' aria-hidden='true'></span>";
            $output .= "<span class='collapse-button-label'>{$this->__('Collapse menu')}</span>";
            $output .= "</button></li>";
            return $output;
        }
        private function __to_string():string{
            $_tp_content = "#tp_content";
            $_tp_toolbar = "#tp_toolbar";
            $output  = "<nav id='admin_menu_main' aria-label='{$this->_esc_attr('Main menu')}'><ul><li>";
            $output .= "<a href='$_tp_content' class='screen-reader-shortcut'>{$this->__('Skip to main content')}</a></li><li>";
            $output .= "<a href='$_tp_toolbar' class='screen-reader-shortcut'>{$this->__('Skip to toolbar')}</a>";
            $output .= "</li></ul><div id='admin_menu_back'></div>";
            $output .= "<div class='admin_menu_wrap'><ul id='admin_menu'>";
            $output .= $this->__menu_output( $this->tp_menu, $this->tp_submenu );
            $output .= $this->_get_action( 'admin_menu' );
            $output .= "</ul></div></nav>";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}