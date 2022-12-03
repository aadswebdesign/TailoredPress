<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-6-2022
 * Time: 19:11
 */
namespace TP_Admin\Traits\AdminNavMenu;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Libs\Walkers\TP_Walker_Nav_Menu_Checklist;
use TP_Core\Libs\Queries\TP_Query;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _adm_nav_menu_01{
        use _init_error;
        /**
         * @description Prints the appropriate response to a menu quick search.
         * @param array ...$request
         * @return bool|string
         */
        protected function _get_async_menu_quick_search( ...$request){
            $args            = [];
            $type            = $request['type'] ?? '';
            $object_type     = $request['object_type'] ?? '';
            $query           = $request['q'] ?? '';
            $response_format = $request['response-format'] ?? '';
            $output  = "";
            if ( ! $response_format || ! in_array( $response_format,['json', 'markup'], true ) ) {
                $response_format = 'json';}
            if ( 'markup' === $response_format ) { $args['walker'] = new TP_Walker_Nav_Menu_Checklist;}
            if ( 'get-post-item' === $type ) {
                if ( $this->_post_type_exists( $object_type ) ) {
                    if ( isset( $request['ID'] ) ) {
                        $object_id = (int) $request['ID'];
                        if ( 'markup' === $response_format ) {
                            $output .= $this->_walk_nav_menu_tree( array_map( 'tp_setup_nav_menu_item',[$this->_get_post( $object_id )]), 0, (object) $args );
                        }elseif ( 'json' === $response_format ) {
                            $output .= $this->_tp_json_encode(['ID'=> $object_id,'post_title' => $this->_get_the_title( $object_id ),'post_type'  => $this->_get_post_type( $object_id ),])."\n";
                        }
                    }
                }elseif ( $this->_taxonomy_exists( $object_type ) ) {
                    if ( isset( $request['ID'] ) ) {
                        $object_id = (int) $request['ID'];
                        if ( 'markup' === $response_format ) {
                            $output .= $this->_walk_nav_menu_tree( array_map( 'tp_setup_nav_menu_item',[$this->_get_term( $object_id, $object_type )] ), 0, (object) $args );
                        }elseif ( 'json' === $response_format ) {
                            $post_obj = $this->_get_term( $object_id, $object_type );
                            $output .= $this->_tp_json_encode(['ID'=> $object_id,'post_title' => $post_obj->name,'post_type'  => $object_type,])."\n";
                        }
                    }
                }
            }elseif ( preg_match( '/quick-search-(posttype|taxonomy)-([a-zA-Z_-]*\b)/', $type, $matches ) ) {
                if ( 'posttype' === $matches[1] && $this->_get_post_type_object( $matches[2] ) ) {
                    $post_type_obj = $this->_tp_nav_menu_meta_box_object( $this->_get_post_type_object( $matches[2] ) );
                    $_post_args = ['no_found_rows' => true, 'update_post_meta_cache' => false,'update_post_term_cache' => false,
                        'posts_per_page' => 10, 'post_type' => $matches[2], 's' => $query,];
                    $args = array_merge($args,$_post_args);
                    if ( isset( $post_type_obj->_default_query ) ) {
                        $args = array_merge( $args, (array) $post_type_obj->_default_query );
                    }
                    $search_results_query = new TP_Query( $args );
                    if ( ! $search_results_query->have_posts() ) { return false;}
                    while ( $search_results_query->have_posts() ) {
                        $post = $search_results_query->next_post();
                        if ( 'markup' === $response_format ) {
                            $var_by_ref = $post->ID;
                            $output .= $this->_walk_nav_menu_tree( array_map( 'tp_setup_nav_menu_item',[$this->_get_post( $var_by_ref )]), 0, (object) $args );
                        } elseif ( 'json' === $response_format ) {
                            $output .= $this->_tp_json_encode(['ID' => $post->ID, 'post_title' => $this->_get_the_title( $post->ID ),'post_type'  => $matches[2],])."\n";
                        }
                    }
                }elseif ( 'taxonomy' === $matches[1] ) {
                    $terms = $this->_get_terms(['taxonomy' => $matches[2], 'name__like' => $query, 'number' => 10, 'hide_empty' => false,]);
                    if ( empty( $terms ) || $this->_init_error( $terms ) ) { return false;}
                    foreach ( (array) $terms as $term ) {
                        if ( 'markup' === $response_format ) {
                            $output .= $this->_walk_nav_menu_tree( array_map( 'tp_setup_nav_menu_item',[$term] ), 0, (object) $args );
                        }elseif ( 'json' === $response_format ) {
                            $output .= $this->_tp_json_encode(['ID' => $term->term_id,'post_title' => $term->name,'post_type' => $matches[2],])."\n";
                        }
                    }
                }
            }
            return $output;
        }//added
        protected function _tp_async_menu_quick_search( ...$request):void{
            echo $this->_get_async_menu_quick_search($request);
        }//23
        /**
         * @description Register nav menu meta boxes and advanced menu items.
         */
        protected function _tp_nav_menu_setup():void{
            $this->_tp_nav_menu_post_type_meta_boxes();
            $this->_add_meta_box( 'add-custom-links', $this->__( 'Custom Links' ), 'tp_nav_menu_item_link_meta_box', 'nav-menus', 'side', 'default' );
            $this->_tp_nav_menu_taxonomy_meta_boxes();
            $this->_add_filter( 'manage_nav-menus_columns', 'tp_nav_menu_manage_columns' );
            if ( false === $this->_get_user_option( 'manage_nav_menus_columnshidden' ) ) {
                $_user = $this->_tp_get_current_user();
                $user = null;
                if($_user instanceof TP_User ){$user = $_user; }
                $this->_update_user_meta(
                    $user->ID,
                    'manage_nav_menus_columnshidden',
                    [0 => 'link-target',1 => 'css-classes',2 => 'xfn',3 => 'description',4 => 'title-attribute',]
                );
            }
        }//145
        /**
         * @description Limit the amount of meta boxes to pages, posts, links,
         * @description . and categories for first time users.
         */
        protected function _tp_initial_nav_menu_meta_boxes():void{
            if (! is_array( $this->tp_meta_boxes ) || $this->_get_user_option( 'metabox_hidden_nav-menus' ) !== false) {
                return;
            }
            $initial_meta_boxes = array( 'add-post-type-page', 'add-post-type-post', 'add-custom-links', 'add-category' );
            $hidden_meta_boxes  = array();
            foreach ( array_keys( $this->tp_meta_boxes['nav-menus'] ) as $context ) {
                foreach ( array_keys( $this->tp_meta_boxes['nav-menus'][ $context ] ) as $priority ) {
                    foreach ( $this->tp_meta_boxes['nav-menus'][ $context ][ $priority ] as $box ) {
                        if ( in_array( $box['id'], $initial_meta_boxes, true ) ) { unset( $box['id'] );}
                        else { $hidden_meta_boxes[] = $box['id'];}
                    }
                }
            }
            $_user = $this->_tp_get_current_user();
            $user = null;
            if($_user instanceof TP_User ){$user = $_user; }
            $this->_update_user_meta( $user->ID, 'metabox_hidden_nav-menus', $hidden_meta_boxes );
        }//178
        /**
         * @description Creates meta boxes for any post type menu item..
         */
        protected function _tp_nav_menu_post_type_meta_boxes():void{
            $taxonomies = $this->_get_taxonomies( array( 'show_in_nav_menus' => true ), 'object' );
            if ( ! $taxonomies ) { return;}
            foreach ( $taxonomies as $tax ) {
                /** This filter is documented in wp-admin/includes/nav-menu.php */
                $tax = $this->_apply_filters( 'nav_menu_meta_box_object', $tax );
                if ( $tax ) {
                    $id = $tax->name;
                    $this->_add_meta_box( "add_{$id}", $tax->labels->name, 'tp_nav_menu_item_taxonomy_meta_box', 'nav-menus', 'side', 'default', $tax );
                }
            }
        }//209
        /**
         * @description Creates meta boxes for any taxonomy menu item.
         */
        protected function _tp_nav_menu_taxonomy_meta_boxes():void{
            $taxonomies = $this->_get_taxonomies( array( 'show_in_nav_menus' => true ), 'object' );
            if ( ! $taxonomies ) { return;}
            foreach ( $taxonomies as $tax ) {
                $tax = $this->_apply_filters( 'nav_menu_meta_box_object', $tax );
                if ( $tax ) {
                    $id = $tax->name;
                    $this->_add_meta_box( "add_{$id}", $tax->labels->name, 'tp_nav_menu_item_taxonomy_meta_box', 'nav-menus', 'side', 'default', $tax );
                }
            }
        }//244
        /**
         * @description Check whether to disable the Menu Locations meta box submit button and inputs.
         * @param $nav_menu_selected_id
         * @return bool
         */
        protected function _tp_get_nav_menu_disabled_check( $nav_menu_selected_id):bool{
            if ( $this->tp_one_theme_location_no_menus ) { return false;}
            return $this->_get_disabled( $nav_menu_selected_id, 0 );
        }//273
        /**
         * @description Displays a meta box for the custom links menu item.
         * @return string
         */
        protected function _tp_get_nav_menu_item_link_meta_box():string{
            $this->tp_nav_menu_placeholder = 0 > $this->tp_nav_menu_placeholder ? $this->tp_nav_menu_placeholder - 1 : -1;
            $output  = "<div id='custom_link_block' class='custom-link-block'><ul><li>";
            $output .= "<input name='menu-item[{$this->tp_nav_menu_placeholder}][menu-item-type]' type='hidden' value='custom'/>";
            $output .= "</li><li id='menu_item_url_wrap'>";
            $output .= "<dt><label for='custom_menu_item_url' class='how-to'>{$this->__('URL')}</label></dt>";
            $output .= "<dd><input id='custom_menu_item_url' name='menu-item[{$this->tp_nav_menu_placeholder}][menu-item-url]' {$this->_tp_get_nav_menu_disabled_check( $this->tp_nav_menu_selected_id )} class='code menu-item-textbox form-required' type='text' placeholder='https://'/></dd>";
            $output .= "</li><li id='menu_item_name_wrap'>";
            $output .= "<dt><label for='custom_menu_item_name' class='how-to'>{$this->__('Link Text')}</label></dt>";
            $output .= "<dd><input id='custom_menu_item_name' name='menu-item[{$this->tp_nav_menu_placeholder}][menu-item-title]' type='text' class='regular-text menu-item-textbox' {$this->_tp_get_nav_menu_disabled_check( $this->tp_nav_menu_selected_id )}/></dd>";
            $output .= "</li><li class='button-controls'>";
            $output .= "<dd class='add-to-menu'><input name='add-custom-menu-item' id='submit_custom_link_block' class='button submit-add-to-menu right' type='submit' value='{$this->_esc_attr('Add to Menu')}' {$this->_tp_get_nav_menu_disabled_check( $this->tp_nav_menu_selected_id )}/></dd>";
            $output .= "<span class='spinner'></span></li></ul></div>";
            return $output;
        }//291
        protected function _tp_nav_menu_item_link_meta_box():void{
            echo $this->_tp_get_nav_menu_item_link_meta_box();
        }//291
        /**
         * @description Displays a meta box for a post type menu item.
         * @param $box
         * @return string
         */
        protected function _tp_get_nav_menu_item_post_type_meta_box($box ):string{// $object,  nowhere used
            $output  = "";
            $post_type_name = $box['args']->name;
            $post_type      = $this->_get_post_type_object( $post_type_name );
            $tab_name       = $post_type_name . '-tab';
            $per_page = 50;
            $pagenum  = isset( $_REQUEST[ $tab_name ], $_REQUEST['paged'] ) ? $this->_abs_int( $_REQUEST['paged'] ) : 1;
            $offset   = 0 < $pagenum ? $per_page * ( $pagenum - 1 ) : 0;
            $args = ['offset' => $offset,'order' => 'ASC','orderby' => 'title','posts_per_page' => $per_page,'post_type' => $post_type_name,
                'suppress_filters' => true,'update_post_term_cache' => false,'update_post_meta_cache' => false,];
            if ( isset( $box['args']->_default_query ) ) { $args = array_merge( $args, (array) $box['args']->_default_query );}
            $important_pages = [];
            if ( 'page' === $post_type_name ) {
                $suppress_page_ids = [];
                $front_page = 'page' === $this->_get_option( 'show_on_front' ) ? (int) $this->_get_option( 'page_on_front' ) : 0;
                $front_page_obj = null;
                if ( ! empty( $front_page ) ) {
                    $front_page_obj                = $this->_get_post( $front_page );
                    $front_page_obj->front_or_home = true;
                    $important_pages[]   = $front_page_obj;
                    $suppress_page_ids[] = $front_page_obj->ID;
                } else {
                    $this->tp_nav_menu_placeholder = ( 0 > $this->tp_nav_menu_placeholder ) ? (int) $this->tp_nav_menu_placeholder - 1 : -1;
                    $front_page_obj = (object) ['front_or_home' => true,'ID' => 0,'object_id' => $this->tp_nav_menu_placeholder,'post_content' => '',
                        'post_excerpt' => '','post_parent' => '','post_title' => $this->_x( 'Home', 'nav menu home label' ),'post_type' => 'nav_menu_item','type' => 'custom','url' => $this->_home_url( '/' ),];
                    $important_pages[] = $front_page_obj;
                }
                // Insert Posts Page.
                $posts_page = 'page' === $this->_get_option( 'show_on_front' ) ? (int) $this->_get_option( 'page_for_posts' ) : 0;
                if ( ! empty( $posts_page ) ) {
                    $posts_page_obj             = $this->_get_post( $posts_page );
                    $posts_page_obj->posts_page = true;
                    $important_pages[]   = $posts_page_obj;
                    $suppress_page_ids[] = $posts_page_obj->ID;
                }
                // Insert Privacy Policy Page.
                $privacy_policy_page_id = (int) $this->_get_option( 'tp_page_for_privacy_policy' );
                if ( ! empty( $privacy_policy_page_id ) ) {
                    $privacy_policy_page = $this->_get_post( $privacy_policy_page_id );
                    if ( $privacy_policy_page instanceof TP_Post && 'publish' === $privacy_policy_page->post_status ) {
                        $privacy_policy_page->privacy_policy_page = true;
                        $important_pages[]   = $privacy_policy_page;
                        $suppress_page_ids[] = $privacy_policy_page->ID;
                    }
                }
                if ( ! empty( $suppress_page_ids ) ) { $args['post__not_in'] = $suppress_page_ids;}
            }
            // @todo Transient caching of these results with proper invalidation on updating of a post of this type.
            $get_posts = new TP_Query;
            $posts     = $get_posts->query_main( $args );
            if ( ! $get_posts->post_count ) {
                if ( ! empty( $suppress_page_ids ) ) {
                    unset( $args['post__not_in'] );
                    $get_posts = new TP_Query;
                    $posts     = $get_posts->query_main( $args );
                } else {
                    return "<p>{$this->__('No items.')}</p>";
                }
            } elseif ( ! empty( $important_pages ) ) {
                $posts = array_merge( $important_pages, $posts );
            }
            $num_pages = $get_posts->max_num_pages;
            $page_links = $this->_paginate_links(['base' => $this->_add_query_arg(
                [ $tab_name => 'all','paged' => '%#%','item-type' => 'post_type','item-object' => $post_type_name,]),
                'format' => '',
                'prev_text' => "<dt><span aria-label='{$this->_esc_attr__( 'Previous page' )}'>{$this->__('&laquo;')}</span></dt>",
                'next_text' => "<dt><span aria-label='{$this->_esc_attr__( 'Next page' )}'>{$this->__('&raquo;')}</span></dt>",
                'before_page_number' => "<dt><span class='screen-reader-text'>{$this->__('Page')}</span></dt>",
                'total' => $num_pages,'current' => $pagenum,]);
            $db_fields = false;
            if ( $this->_is_post_type_hierarchical( $post_type_name ) ) {
                $db_fields = ['parent' => 'post_parent','id' => 'ID',];
            }
            $walker = new TP_Walker_Nav_Menu_Checklist( $db_fields );
            $current_tab = 'most-recent';
            if ( isset( $_REQUEST[ $tab_name ] ) && in_array( $_REQUEST[ $tab_name ], array( 'all', 'search' ), true ) ) {
                $current_tab = $_REQUEST[ $tab_name ];}
            if ( ! empty( $_REQUEST[ 'quick-search-posttype-' . $post_type_name ] ) ) {
                $current_tab = 'search';}
            $removed_args = ['action','custom-link-tab','edit-menu-item','menu-item','page-tab','_tp_nonce',];
            $most_recent_url = '';
            $view_all_url    = '';
            $search_url      = '';
            if ( $this->tp_nav_menu_selected_id ) {
                $most_recent_url = $this->_esc_url( $this->_add_query_arg( $tab_name, 'most-recent', $this->_remove_query_arg( $removed_args ) ) );
                $view_all_url    = $this->_esc_url( $this->_add_query_arg( $tab_name, 'all', $this->_remove_query_arg( $removed_args ) ) );
                $search_url      = $this->_esc_url( $this->_add_query_arg( $tab_name, 'search', $this->_remove_query_arg( $removed_args ) ) );
            }
            $_li_class_recent = ('most-recent' === $current_tab ? " class='tabs'" : '');
            $_li_class_all = ('all' === $current_tab ? " class='tabs'" : '');
            $_li_class_search = ('search' === $current_tab ? " class='tabs'" : '');
            $_div_class_recent_inner = ('most-recent' === $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive');
            $_div_class_recent = " class='tabs-panel $_div_class_recent_inner'";
            $_div_class_search_inner = ('search' === $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive');
            $_div_class_search = " class='tabs-panel $_div_class_search_inner'";
            $_div_class_all_inner = ('all' === $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive');
            $_div_class_all = " class='tabs-panel $_div_class_all_inner'";
            $recent_args = array_merge( $args,['orderby' => 'post_date','order' => 'DESC','posts_per_page' => 15,]);
            $most_recent    = $get_posts->query_main( $recent_args );
            $args['walker'] = $walker;
            $most_recent = $this->_apply_filters( "nav_menu_items_{$post_type_name}_recent", $most_recent, $args, $box, $recent_args );
            $_search_request = $_REQUEST[ 'quick-search-posttype-' . $post_type_name ];
            if ( isset($_search_request)){
                $searched= $this->_esc_attr( $_REQUEST[ 'quick-search-posttype-' . $post_type_name ] );
                $search_results = $this->_get_posts(['s' => $searched,'post_type' => $post_type_name,'fields' => 'all','order' => 'DESC',]);
            } else {
                $searched       = '';
                $search_results = [];
            }
            $output .= "<div id='posttype_{$post_type_name}' class='posttype-module'>";
            $output .= "<header><ul id='posttype_{$post_type_name}_tab' class='posttype-tabs add-menu-item-tabs'>";
            $output .= "<li $_li_class_recent>";
            $output .= "<a href='{$most_recent_url}#tabs_panel_posttype_{$post_type_name}_most_recent' class='nav-tab-link' data-type='tabs-panel-posttype-{$this->_esc_attr( $post_type_name)}'>{$this->__('Most Recent')}</a>";
            $output .= "</li><li $_li_class_all >";
            $output .= "<a href='{$view_all_url}#{$post_type_name}_all' class='nav-tab-link' data-type='{$this->_esc_attr( $post_type_name)}-all'>{$this->__('View All')}</a>";
            $output .= "</li><li $_li_class_search>";
            $output .= "<a href='{$search_url}#tabs_panel_posttype-{$post_type_name}-search' class='nav-tab-link' data-type='{$this->_esc_attr( $post_type_name)}-search'>{$this->__('Search')}</a>";
            $output .= "</li></ul></header>";//posttype header
            $output .= "<div id='tabs_panel_posttype_{$post_type_name}' $_div_class_recent role='region' aria-label='{$this->__('Most Recent')}' tabindex='0'>";
            $output .= "<ul id='{$post_type_name}_checklist_most_recent' class='category-checklist'>";
            $output .= $this->_walk_nav_menu_tree( array_map( 'tp_setup_nav_menu_item', $most_recent ), 0, (object) $args );
            $output .= "</ul></div>"; //tabs-panel MOST RECENT
            $output .= "<div id='tabs_panel_posttype_{$post_type_name}_search' $_div_class_search role='region' aria-label='{$post_type->labels->search_items}' tabindex='0'>";
            $output .= "<div class='menu-block'><ul><li class='quick-search-wrap'>";
            $output .= "<dt><label for='quick_search_posttype_{$post_type_name}' class='screen-reader-text'>{$this->__('Search')}</label></dt>";
            $output .= "<dd><input name='quick_search_posttype_{$post_type_name}' id='quick_search_posttype_{$post_type_name}' type='search' value='$searched' {$this->_tp_get_nav_menu_disabled_check( $this->tp_nav_menu_selected_id )}/></dd>";
            $output .= "<span class='spinner'></span>";
            $output .= "</li><li>";
            $output .= $this->_get_submit_button( $this->__( 'Search' ), 'small quick-search-submit hide-if-js', 'submit', false, array( 'id' => 'submit_quick_search_posttype_' . $post_type_name ) );
            $output .= "</li></ul></div>";//menu-block one
            $output .= "<div class='menu-block'><ul id='{$post_type_name}_search_checklist' class='category-checklist' data-tp-list='list:{$post_type_name}'><li>";
            if ( ! empty( $search_results ) && ! $this->_init_error( $search_results ) ){
                $args['walker'] = $walker;
                $output .= $this->_walk_nav_menu_tree( array_map( 'tp_setup_nav_menu_item', $search_results ), 0, (object) $args );
            }elseif ($search_results instanceof TP_Error && $this->_init_error( $search_results ) ){
                $output .= "{$search_results->get_error_message()}</li><li>";
            }elseif ( ! empty( $searched ) ){
                $output .= "{$this->__('No results found.')}</li><li>";
            }
            $output .= "</li></ul></div></div>";//menu-block two //tabs-panel SEARCH
            $output .= "<div id='{$post_type_name}_all' $_div_class_all>";
            if ( ! empty( $page_links ) ){
                $output .= "<div class='menu-block'><ul><li>$page_links</li></ul></div>";//menu-block two
            }
            $output .= "<div class='menu-block'><ul id='{$post_type_name}_checklist' class='category-checklist' data-tp-list='list:{$post_type_name}'><li>";
            $args['walker'] = $walker;
            if ( $post_type->has_archive ) {
                $this->tp_nav_menu_placeholder = ( 0 > $this->tp_nav_menu_placeholder ) ? (int) $this->tp_nav_menu_placeholder - 1 : -1;
                $_posts_unshift = ['ID'=> 0,'object_id' => $this->tp_nav_menu_placeholder,'object' => $post_type_name,'post_content' => '','post_excerpt' => '',
                    'post_title' => $post_type->labels->archives,'post_type' => 'nav_menu_item','type' => 'post_type_archive','url' => $this->_get_post_type_archive_link( $post_type_name ),];
                array_unshift($posts,$_posts_unshift);
            }
            $posts = $this->_apply_filters( "nav_menu_items_{$post_type_name}", $posts, $args, $post_type );
            $checkbox_items = $this->_walk_nav_menu_tree( array_map( 'tp_setup_nav_menu_item', $posts ), 0, (object) $args );
            $output .= "$checkbox_items</li></ul></div>";//menu-block three
            if ( ! empty( $page_links ) ){
                $output .= "<div class='menu-block add-menu-item-page-links'><ul><li>$page_links</li></ul></div>";//menu-block four
            }
            $output .= "</div>";//tabs-panel ALL
            $output .= "<div class='tabs-panel-footer'><ul><li class='list-controls hide-if-no-js'>";
            $output .= "<dd><input id='{$this->_esc_attr($tab_name)}' class='select-all' type='checkbox' {$this->_tp_get_nav_menu_disabled_check( $this->tp_nav_menu_selected_id )}/></dd>";
            $output .= "<dt><label for='{$this->_esc_attr($tab_name)}'>{$this->__('Select All')}</label></dt>";
            $output .= "</li><li class='add-to-menu'>";
            $_submit_posttype = "submit_posttype_{$post_type_name}";
            $output .= "<dd><input id='{$this->_esc_attr($_submit_posttype)}' name='add-post-type-menu-item' type='submit' class='button submit-add-to-menu' value='{$this->_esc_attr('Add to Menu')}' {$this->_tp_get_nav_menu_disabled_check( $this->tp_nav_menu_selected_id )}/></dd>";
            $output .= "<span class='spinner'></span>";
            $output .= "</li></ul></div></div>"; //tabs-panel-footer //posttype-module
            return $output;
        }//338
        protected function _tp_nav_menu_item_post_type_meta_box($box ):void{
            echo $this->_tp_get_nav_menu_item_post_type_meta_box($box );
        }//338
        /**
         * @description Displays a meta box for a taxonomy menu item.
         * @param $box
         * @return string
         */
        protected function _tp_get_nav_menu_item_taxonomy_meta_box( $box ):string{ //not used  $object,
            $output  = "";
            $taxonomy_name = $box['args']->name;
            $taxonomy      = $this->_get_taxonomy( $taxonomy_name );
            $tab_name      = $taxonomy_name . '-tab';
            $per_page = 50;
            $pagenum  = isset( $_REQUEST[ $tab_name ], $_REQUEST['paged'] ) ? $this->_abs_int( $_REQUEST['paged'] ) : 1;
            $offset   = 0 < $pagenum ? $per_page * ( $pagenum - 1 ) : 0;
            $args = ['taxonomy' => $taxonomy_name,'child_of' => 0,'exclude' => '','hide_empty' => false,'hierarchical' => 1,'include' => '','number' => $per_page,'offset' => $offset, 'order' => 'ASC','orderby' => 'name','pad_counts' => false,];
            $terms = $this->_get_terms( $args );
            if ( ! $terms || $this->_init_error( $terms ) ) {
                return "<p>{$this->__( 'No items.' )}</p>";
            }
            $num_pages = ceil($this->_tp_count_terms(array_merge( $args,['number' => '','offset' => '',])) / $per_page );
            $page_links = $this->_paginate_links(['base' => $this->_add_query_arg([$tab_name => 'all', 'paged' => '%#%','item-type' => 'taxonomy','item-object' => $taxonomy_name,]),
                'format' => '','prev_text' => "<dt><span aria-label='{$this->_esc_attr__( 'Previous page' )}'>{$this->__('&laquo;')}</span></dt>",
                'next_text' => "<dt><span aria-label='{$this->_esc_attr__( 'Next page' )}'>{$this->__('&raquo;')}</span></dt>",
                'before_page_number' => "<dt><span class='screen-reader-text'>{$this->__('Page')}</span></dt>",'total' => $num_pages,'current' => $pagenum,]);
            $db_fields = false;
            if ( $this->_is_taxonomy_hierarchical( $taxonomy_name ) ) {
                $db_fields = ['parent' => 'parent','id' => 'term_id',];
            }
            $walker = new TP_Walker_Nav_Menu_Checklist( $db_fields );
            $current_tab = 'most-used';
            if ( isset( $_REQUEST[ $tab_name ] ) && in_array( $_REQUEST[ $tab_name ], array( 'all', 'most-used', 'search' ), true ) ) { $current_tab = $_REQUEST[ $tab_name ];}
            if ( ! empty( $_REQUEST[ 'quick-search-taxonomy-' . $taxonomy_name ] ) ) { $current_tab = 'search';}

            $removed_args = ['action','custom-link-tab','edit-menu-item','menu-item','page-tab','_tp_nonce',];
            $most_used_url = '';
            $view_all_url  = '';
            $search_url    = '';
            if ( $this->tp_nav_menu_selected_id ) {
                $most_used_url = $this->_esc_url( $this->_add_query_arg( $tab_name, 'most-used', $this->_remove_query_arg( $removed_args ) ) );
                $view_all_url  = $this->_esc_url( $this->_add_query_arg( $tab_name, 'all', $this->_remove_query_arg( $removed_args ) ) );
                $search_url    = $this->_esc_url( $this->_add_query_arg( $tab_name, 'search', $this->_remove_query_arg( $removed_args ) ) );
            }
            $_li_class_most_used = ('most-used' === $current_tab ? " class='tabs'" : '');
            $_li_class_all = ('all' === $current_tab ? " class='tabs'" : '');
            $_li_class_search = ('search' === $current_tab ? " class='tabs'" : '');
            $_div_class_pop_inner = ('most-used' === $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive');
            $_div_class_pop = " class='tabs-panel $_div_class_pop_inner'";
            $_div_class_all_inner = ('all' === $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive');
            $_div_class_all = " class='tabs-panel tabs-panel-view-all $_div_class_all_inner'";
            $_div_class_search_inner = ('search' === $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive');
            $_div_class_search = " class='tabs-panel $_div_class_search_inner'";
            $popular_terms  = $this->_get_terms(['taxonomy' => $taxonomy_name,'orderby' => 'count','order' => 'DESC','number' => 10,'hierarchical' => false,]);
            $output .= "<div id='taxonomy_{$taxonomy_name}' class='taxonomy-module'>";
            $output .= "<header><ul id='taxonomy_{$taxonomy_name}_tabs' class='taxonomy-tabs add-menu-item-tabs'>";
            $output .= "<li $_li_class_most_used >";
            $output .= "<a href='{$most_used_url}#tabs_panel_{$taxonomy_name}_pop' class='nav-tab-link' data-type='tabs-panel-{$this->_esc_attr( $taxonomy_name)}-pop'>{$this->_esc_html($taxonomy->labels->most_used)}</a>";
            $output .= "<li $_li_class_all >";
            $output .= "<a href='{$view_all_url}#tabs_panel_{$taxonomy_name}_all' class='nav-tab-link' data-type='tabs-panel-{$this->_esc_attr( $taxonomy_name)}-all'>{$this->__('View All')}</a>";
            $output .= "</li><li $_li_class_search>";
            $output .= "<a href='{$search_url}#tabs_panel_search_taxonomy_{$taxonomy_name}' class='nav-tab-link' data-type='tabs-panel-search-taxonomy-{$this->_esc_attr( $taxonomy_name)}'>{$this->__('Search')}</a>";
            $output .= "</li></ul></header>";//taxonomy header
            $output .= "<div id='tabs_panel_{$taxonomy_name}_pop' role='region' $_div_class_pop aria-label='{$taxonomy->labels->most_used}' tabindex='0' >";
            $output .= "<ul id='{$taxonomy_name}_checklist_pop'  class='category-checklist'><li>";
            $args['walker'] = $walker;
            $output .= $this->_walk_nav_menu_tree( array_map( 'tp_setup_nav_menu_item', $popular_terms  ), 0, (object) $args );
            $output .= "</li></ul></div>";//tabs-panel POP
            $output .= "<div id='tabs_panel_{$taxonomy_name}_all' role='region' $_div_class_all aria-label='{$taxonomy->labels->all_items}' tabindex='0' >";
            if ( ! empty( $page_links ) ){
                $output .= "<div  class='menu-block'><ul><li>$page_links</li></ul></div>";//menu-block one
            }
            $output .= "<div class='menu-block'><ul id='{$taxonomy_name}_checklist' class='category-checklist' data-tp-list='list:{$taxonomy_name}'>";
            $args['walker'] = $walker;
            $output .= $this->_walk_nav_menu_tree( array_map( 'tp_setup_nav_menu_item', $terms  ), 0, (object) $args );
            if ( ! empty( $page_links ) ){ $output .= "<li>$page_links</li>";}
            $output .= "</ul></div></div>";//menu-block two //tabs-panel ALL
            $output .= "<div id='tabs_panel_search_taxonomy_{$taxonomy_name}' role='region' $_div_class_search aria-label='{$taxonomy->labels->search_items}' tabindex='0' >";
            $_search_request = $_REQUEST[ 'quick-search-taxonomy-' . $taxonomy_name ];
            if ( isset($_search_request) ) {
                $searched       = $this->_esc_attr( $_REQUEST[ 'quick-search-taxonomy-' . $taxonomy_name ] );
                $search_results = $this->_get_terms(['taxonomy' => $taxonomy_name,'name__like' => $searched,
                    'fields' => 'all','orderby' => 'count','order' => 'DESC','hierarchical' => false,]);
            }else {
                $searched       = '';
                $search_results = [];
            }
            $output .= "<div class='menu-block'><ul><li>";
            $output .= "<li class='quick-search-wrap'><dt><label for='quick_search_taxonomy_{$taxonomy_name}'>{$this->__('Search')}</label></dt>";
            $output .= "<dd><input name='quick_search_taxonomy_{$taxonomy_name}' id='quick_search_taxonomy_{$taxonomy_name}' class='quick-search' type='search' value='$searched'/></dd>";
            $output .= "<span class='spinner'></span>";
            $output .= "</li><li class='quick-search-submit'>";
            $output .= $this->_get_submit_button( $this->__( 'Search' ), 'small quick-search-submit hide-if-js', 'submit', false, array( 'id' => 'submit-quick-search-taxonomy-' . $taxonomy_name ) );
            $output .= "</li></ul></div>";//menu-block three
            $output .= "<div class='menu-block'><ul id='{$taxonomy_name}_search_checklist' class='category-checklist' data-tp-list='list:{$taxonomy_name}'><li>";
            if ( ! empty( $search_results ) && ! $this->_init_error( $search_results ) ){
                $args['walker'] = $walker;
                $output .= $this->_walk_nav_menu_tree( array_map( 'tp_setup_nav_menu_item', $search_results   ), 0, (object) $args );
            }elseif ($search_results instanceof TP_Error &&  $this->_init_error( $search_results ) ){
                $output .= "{$search_results->get_error_message()}</li><li>";
            }elseif ( ! empty( $searched ) ){
                $output .= "{$this->__('No results found.')}</li><li>";
            }
            $output .= "</li></ul></div></div>";//menu-block four // tabs-panel SEARCH
            $output .= "<div class='tabs-panel-footer'><ul><li>";
            $output .= "<dd><input id='{$this->_esc_attr($tab_name )}' class='select-all' type='checkbox' {$this->_tp_get_nav_menu_disabled_check( $this->tp_nav_menu_selected_id )}/></dd>";
            $output .= "<dt><label for='{$this->_esc_attr($tab_name )}'>{$this->__('Select All')}</label></dt>";
            $output .= "</li><li>";
            $_submit_tax_id = "submit_taxonomy_{$taxonomy_name}";
            $output .= "<dd><input name='add-taxonomy-menu-item' id='{$this->_esc_attr($_submit_tax_id)}' class='button submit-add-to-menu' type='submit' value='{$this->_esc_attr('Add to Menu')}' {$this->_tp_get_nav_menu_disabled_check( $this->tp_nav_menu_selected_id )}/></dd>";
            $output .= "<span class='spinner'></span>";
            $output .= "</li></ul></div></div>";//tabs-panel-footer //taxonomy-module
            return $output;
        }//702
        protected function _tp_nav_menu_item_taxonomy_meta_box($box ):void{
            echo $this->_tp_get_nav_menu_item_taxonomy_meta_box( $box );
        }//702
        /**
         * @description Save posted nav menu item data.
         * @param int $menu_id
         * @param array ...$menu_data
         * @return string
         */
        protected function _tp_save_nav_menu_items( $menu_id = 0, ...$menu_data):string{
            $menu_id     = (int) $menu_id;
            $items_saved = [];
            if ( 0 === $menu_id || $this->_is_nav_menu( $menu_id ) ) {
                foreach ($menu_data as $_possible_db_id => $_item_object_data ) {
                    if ( empty( $_item_object_data['menu-item-object-id'] ) &&( ! isset( $_item_object_data['menu-item-type'] ) ||
                            in_array( $_item_object_data['menu-item-url'], array( 'https://', 'http://', '' ), true ) ||
                            ! ( 'custom' === $_item_object_data['menu-item-type'] && ! isset( $_item_object_data['menu-item-db-id'] ) ) ||
                            ! empty( $_item_object_data['menu-item-db-id'] ))){
                        continue;
                    }
                    if ( empty( $_item_object_data['menu-item-db-id'] ) || ( 0 > $_possible_db_id ) || $_possible_db_id !== $_item_object_data['menu-item-db-id']){
                        $_actual_db_id = 0;
                    } else { $_actual_db_id = (int) $_item_object_data['menu-item-db-id'];}
                    $args = [
                        'menu-item-db-id'       => ($_item_object_data['menu-item-db-id'] ?? '' ),
                        'menu-item-object-id'   => ($_item_object_data['menu-item-object-id'] ?? '' ),
                        'menu-item-object'      => ($_item_object_data['menu-item-object'] ?? '' ),
                        'menu-item-parent-id'   => ($_item_object_data['menu-item-parent-id'] ?? '' ),
                        'menu-item-position'    => ($_item_object_data['menu-item-position'] ?? '' ),
                        'menu-item-type'        => ($_item_object_data['menu-item-type'] ?? '' ),
                        'menu-item-title'       => ($_item_object_data['menu-item-title'] ?? '' ),
                        'menu-item-url'         => ($_item_object_data['menu-item-url'] ?? '' ),
                        'menu-item-description' => ($_item_object_data['menu-item-description'] ?? '' ),
                        'menu-item-attr-title'  => ($_item_object_data['menu-item-attr-title'] ?? '' ),
                        'menu-item-target'      => ($_item_object_data['menu-item-target'] ?? '' ),
                        'menu-item-classes'     => ($_item_object_data['menu-item-classes'] ?? '' ),
                        'menu-item-xfn'         => ($_item_object_data['menu-item-xfn'] ?? '' ),
                    ];
                    $items_saved[] = $this->_tp_update_nav_menu_item( $menu_id, $_actual_db_id, $args );
                }
            }
            return $items_saved;
        }//925
    }
}else die;