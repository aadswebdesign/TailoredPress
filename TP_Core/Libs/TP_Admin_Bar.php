<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-9-2022
 * Time: 11:07
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Misc\_global_settings;
use TP_Core\Traits\Misc\tp_link_styles;
use TP_Core\Traits\Misc\tp_script;
use TP_Core\Traits\Multisite\Methods\_ms_methods_01;
use TP_Core\Traits\Pluggables\_pluggable_02;
use TP_Core\Traits\Templates\_general_template_01;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Templates\_link_template_10;
use TP_Core\Traits\Theme\_theme_07;
use TP_Core\Traits\User\_user_02;
if(ABSPATH){
    class TP_Admin_Bar{
        use _action_01, _formats_04,_formats_07,_formats_08, _general_template_01,_global_settings;
        use _link_template_09,_link_template_10, _load_03,_load_04,_methods_10, _ms_methods_01;
        use _pluggable_02, _theme_07, tp_link_styles,tp_script, _user_02;
        private $__nodes = [];
        private $__bound = false;
        public $user;
        public function initialize():void{
            $this->user = new \stdClass;
            if ( $this->_is_user_logged_in() ) {
                /* Populate settings we need for the menu based on the current user. */
                $this->user->blogs = $this->_get_blogs_of_user( $this->_get_current_user_id() );
                if ( $this->_is_multisite() ) {
                    $this->user->active_blog    = $this->_get_active_blog_for_user( $this->_get_current_user_id() );
                    $this->user->domain         = empty( $this->user->active_blog ) ? $this->_user_admin_url() : $this->_trailingslashit( $this->_get_home_url( $this->user->active_blog->blog_id ) );
                    $this->user->account_domain = $this->user->domain;
                } else {
                    $this->user->active_blog    = $this->user->blogs[ $this->_get_current_blog_id() ];
                    $this->user->domain         = $this->_trailingslashit( $this->_home_url() );
                    $this->user->account_domain = $this->user->domain;
                }
            }
            $this->_add_action( 'tp_head', 'tp_admin_bar_header' );
            $this->_add_action( 'admin_head', 'tp_admin_bar_header' );
            $header_callback = null;
            if ( $this->_current_theme_supports( 'admin-bar' ) ){
                $admin_bar_args  = $this->_get_theme_support( 'admin-bar' );
                $header_callback = $admin_bar_args[0]['callback'];
            }
            if ( empty( $header_callback ) ) { $header_callback = '_admin_bar_bump_cb';}
            $this->_add_action( 'tp_head', $header_callback );
            $this->tp_enqueue_script( 'admin-bar' );
            $this->tp_enqueue_style( 'admin-bar' );
            $this->_do_action( 'admin_bar_init' );
        }//42
        public function add_menu( $node ):void{
            $this->add_node( $node );
        }//96
        public function remove_menu( $id ):void{
            $this->remove_node( $id );
        }//107
        public function add_node( $args ):void{
            if ( is_object( $args ) ) { $args = get_object_vars( $args );}
            if ( empty( $args['title'])){ return;}
            $defaults = ['id' => false,'title' => false, 'parent' => false,'href' => false,'group' => false,'meta' => [],];
            $maybe_defaults = (object)$this->get_node( $args['id'] );
            if ( $maybe_defaults ) {$defaults = get_object_vars( $maybe_defaults );}
            if ( ! empty( $defaults['meta'] ) && ! empty( $args['meta'] ) ) {
                $args['meta'] = $this->_tp_parse_args( $args['meta'], $defaults['meta'] );
            }
            $args = $this->_tp_parse_args( $args, $defaults );
            $this->_set_node( $args );
        }//129
        final protected function _set_node( $args ):void{
            $this->__nodes[ $args['id'] ] = (object) $args;
        }//191
        final public function get_node( $id ){
            $node = $this->_get_node( $id );
            $clone = null;
            if ( $node ) {$clone = $node;}
            return $clone;
        }//203
        final protected function _get_node( $id ){
            if ($this->__bound ){ return;}
            if (empty($id)){$id = 'root';}
            return $this->__nodes[$id] ?? null;
        }//216
        final public function get_nodes(){
            $nodes = $this->_get_nodes();
            if(!$nodes){ return;}
            foreach ((array)$nodes as &$node){$node = clone $node;}
            return $nodes;
        }//235
        final protected function _get_nodes(){
            if ( $this->__bound ){return;}
            return $this->__nodes;
        }//252
        final public function add_group( $args ):void{
            $args['group'] = true;
            $this->add_node( $args );
        }//276
        public function remove_node( $id ):void{
            $this->_unset_node( $id );
        }//289
        final protected function _unset_node( $id ):void {
            unset( $this->__nodes[ $id ] );
        }//298
        public function render():void {
            $root = $this->_bind();
            if ( $root ) {echo $this->_get_render( $root );}
        }
        //public function get_render(){}//305 todo
        //public function set_render(){}//305 todo
        final protected function _bind(){
            if($this->__bound){return;}
            $this->remove_node( 'root' );
            $this->add_node(['id' => 'root','group' => false,]);
            foreach ( $this->_get_nodes() as $node ) {
                $node->children = [];
                $node->type = ( $node->group ) ? 'group' : 'item';
                unset( $node->group );
                if ( ! $node->parent ){$node->parent = 'root';}
            }
            foreach ( $this->_get_nodes() as $node ) {
                if('root' === $node->id){ continue;}
                $parent = $this->_get_node( $node->parent );
                if(!$parent){ continue;}
                $group_class = ( 'root' === $node->parent ) ? 'ab-top-menu' : 'ab-submenu';
                if ( 'group' === $node->type ) {
                    if(empty($node->meta['class'])){ $node->meta['class'] = $group_class;}
                    else{ $node->meta['class'] .= ' ' . $group_class;}
                }
                if ('item' === $parent->type && 'item' === $node->type ){
                    $default_id = $parent->id . '-default';
                    $default    = $this->_get_node( $default_id );
                    if(!$default){
                        $this->_set_node(['id' => $default_id,'parent' => $parent->id,'type' => 'group',
                            'children' => [],'meta' => ['class' => $group_class,],'title' => false,'href' => false,]);
                        $default = $this->_get_node( $default_id );
                        $parent->children[] = $default;
                    }
                    $parent = $default;
                }elseif('group' === $parent->type && 'group' === $node->type){
                    $container_id = $parent->id . '_container';
                    $container    = $this->_get_node( $container_id );
                    if(!$container){
                        $this->_set_node(['id' => $container_id,'type' => 'container','children' => [$parent],
                            'parent' => false,'title' => false,'href' => false,'meta' => [],]);
                        $container = $this->_get_node( $container_id );
                        $grandparent = $this->_get_node( $parent->parent );
                        if ( $grandparent ) {
                            $container->parent = $grandparent->id;
                            $index = array_search( $parent, $grandparent->children, true );
                            if(false === $index){ $grandparent->children[] = $container;}
                            else{ array_splice( $grandparent->children, $index, 1,[$container]);}
                        }
                        $parent->parent = $container->id;
                    }
                    $parent = $container;
                }
                $node->parent = $parent->id;
                $parent->children[] = $node;
            }
            $root = $this->_get_node( 'root' );
            $this->__bound = true;
            return $root;
        }//317
        final protected function _get_render( $root ):string{
            $class = 'no-js'; //no-jq left out
            if($this->_tp_is_mobile()){$class .= ' mobile';}
            $output  = "<div id='tp_adminbar' class='$class'>";
            if ( ! $this->_is_admin() && ! $this->_did_action( 'tp_body_open' ) ) {
                $toolbar = '#tp_toolbar';
                $output .= "<a href='$toolbar' class='screen-reader-shortcut' tabindex='1'>{$this->_esc_attr('Skip to toolbar')}</a>";
            }
            $output .= "<nav id='tp_toolbar' class='quick-links' role='navigation' aria-label='{$this->_esc_attr('Toolbar')}'>";
            foreach ( $root->children as $group ) { $output .= $this->_get_render_group( $group );}
            $output .= "</nav>";
            if ( $this->_is_user_logged_in() ){
                $output .= "<a href='{$this->_esc_url( $this->_tp_logout_url() )}' class='screen-reader-shortcut'>{$this->__('Log Out')}</a>";
            }
            $output .= "</div>";
            return $output;
        }//455
        final protected function _get_render_container( $node ):string{
            if('container' !== $node->type||empty($node->children)){return false;}
            $output = "<div id='{$this->_esc_attr('tp_admin_bar_' . $node->id)}' class='ab-group-container'>";
            foreach ( $node->children as $group ) {
                $output .= $this->_get_render_group( $group );
            }
            $output .= "</div>";
            return $output;
        }//488
        final protected function _get_render_group( $node ):string{
            if ( 'container' === $node->type ) {return $this->_get_render_container( $node );}
            if ( 'group' !== $node->type || empty( $node->children)){ return false;}
            if ( ! empty( $node->meta['class'] ) ) {
                $class = " class='{$this->_esc_attr( trim( $node->meta['class'] ) )}'";
            } else { $class = '';}
            $output  = "<ul id='{$this->_esc_attr( 'tp_admin_bar_' . $node->id )}' $class>";
            foreach ( $node->children as $item ) {
                $output .= $this->_get_render_item( $item );
            }
            $output .= "</ul>";
            return $output;
        }//505
        final protected function _get_render_item( $node ):string{
            if('item' !== $node->type){ return false;}
            $is_parent = ! empty( $node->children );
            $has_link = ! empty( $node->href );
            $is_root_top_item = 'root-default' === $node->parent;
            $is_top_secondary_item = 'top-secondary' === $node->parent;
            $tabindex = ( isset( $node->meta['tabindex'] ) && is_numeric( $node->meta['tabindex'] ) ) ? (int) $node->meta['tabindex'] : '';
            $aria_attributes = ( '' !== $tabindex ) ? " tabindex='$tabindex'" : '';
            $menu_class = '';
            $arrow = '';
            if ( $is_parent ) {
                $menu_class = 'menu-pop ';
                $aria_attributes .= ' aria-haspopup="true"';
            }
            if(!empty( $node->meta['class'])){ $menu_class .= $node->meta['class'];}
            if ( ! $is_root_top_item && ! $is_top_secondary_item && $is_parent ) {
                $arrow = "<span class='tp-admin-bar-arrow' aria-hidden='true'></span>";
            }
            if ( $menu_class ) {
                $menu_class = " class='{$this->_esc_attr( trim( $menu_class ) )}'";
            }
            $output  = "<li id='{$this->_esc_attr( 'tp_admin_bar_' . $node->id )}' $menu_class>";
            $attributes = ['onclick','target','title','rel','lang','dir'];
            if ( $has_link ) {
                $output .= "<a href='{$this->_esc_url( $node->href )}' class='ab-item' $aria_attributes"; //>
            }else{ $output .= "<div class='ab-item ab-empty-item' $aria_attributes";} //>
            foreach ( $attributes as $attribute ) {
                if (empty($node->meta[$attribute])){ continue;}
                if ( 'onclick' === $attribute ) {
                    $output .= " {$attribute}='{$this->_esc_js( $node->meta[$attribute])}'";
                }
            }
            $output .= ">{$arrow}{$node->title}";
            if($has_link){$output .= "</a>";}
            else{$output .= "</div>";}
            if ( $is_parent ) {
                $output .= "<div class='ab-sub-wrapper'>";
                foreach($node->children as $group){$output .= $this->_get_render_group( $group );}
                $output .= "</div>";
            }
            if(!empty($node->meta['html'])){ $output .= $node->meta['html'];}
            $output .= "</li>";
            return $output;
        }//532
        public function add_menus():void{
            // User-related, aligned right.
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_my_account_menu', 0 );
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_search_menu', 4 );
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_my_account_item', 7 );
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_recovery_mode_menu', 8 );
            // Site-related.
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_sidebar_toggle', 0 );
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_tp_menu', 10 );
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_my_sites_menu', 20 );
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_site_menu', 30 );
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_edit_site_menu', 40 );
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_customize_menu', 40 );
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_updates_menu', 50 );
            // Content-related.
            if ( ! $this->_is_network_admin() && ! $this->_is_user_admin() ) {
                $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_comments_menu', 60 );
                $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_new_content_menu', 70 );
            }
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_edit_menu', 80 );
            $this->_add_action( 'admin_bar_menu', 'tp_admin_bar_add_secondary_groups', 200 );
            $this->_do_action( 'add_admin_bar_menus' );
        }//633
    }
}else{die;}