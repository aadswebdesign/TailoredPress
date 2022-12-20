<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 05:01
 */
namespace TP_Core\Libs\Post;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Posts_Controller;
if(ABSPATH){
    final class TP_Post_Type extends PostType_Base {
        /**
         * @description TP_Post_Type constructor.
         * @param $post_type
         * @param \array[] ...$args
         */
        public function __construct( $post_type = null, ...$args){
            $this->_rewrite_constants();
            $this->name = $post_type;
            $this->set_properties( $args );
        }//405
        /**
         * @description Sets post type properties.
         * @param $args
         */
        public function set_properties( $args ): void{
            $args = $this->_tp_parse_args( $args );
            $has_edit_link = ! empty( $args['_edit_link'] );
            $defaults = [
                'labels' => [],'description' => '','public' => false,'hierarchical' => false,'exclude_from_search' => null,
                'publicly_queryable' => null,'show_ui' => null,'show_in_menu' => null,'show_in_nav_menus' => null,'show_in_admin_bar' => null,
                'menu_position' => null,'menu_icon' => null,'capability_type' => 'post','capabilities' => [],'map_meta_cap' => null,
                'supports' => [],'register_meta_box_cb' => null,'taxonomies' => [],'has_archive' => false,'rewrite' => true,
                'query_var' => true,'can_export' => true,'delete_with_user' => null,'show_in_rest' => false,'rest_base' => false,
                'rest_namespace' => false,'rest_controller_class' => false,'template' => [],'template_lock' => false,'_builtin' => false,
                '_edit_link' => 'post.php?post=%d',//todo
            ];
            $args = array_merge( $defaults, $args );
            $args['name'] = $this->name;
            if ( null === $args['publicly_queryable'] ) $args['publicly_queryable'] = $args['public'];
            if ( null === $args['show_ui'] ) $args['show_ui'] = $args['public'];
            if ( false === $args['rest_namespace'] && ! empty( $args['show_in_rest'] ) ) $args['rest_namespace'] = 'tp/v1';
            if ( null === $args['show_in_menu'] || ! $args['show_ui'] ) $args['show_in_menu'] = $args['show_ui'];
            if ( null === $args['show_in_admin_bar'] ) $args['show_in_admin_bar'] = (bool) $args['show_in_menu'];
            if ( null === $args['show_in_nav_menus'] ) $args['show_in_nav_menus'] = $args['public'];
            if ( null === $args['exclude_from_search'] ) $args['exclude_from_search'] = ! $args['public'];
            if ( empty( $args['capabilities'] ) && null === $args['map_meta_cap'] && in_array( $args['capability_type'], array('post','page'),true))
                $args['map_meta_cap'] = true;
            if ( null === $args['map_meta_cap'] ) $args['map_meta_cap'] = false;
            if ( ! $args['show_ui'] && ! $has_edit_link ) $args['_edit_link'] = '';
            $this->cap = $this->_get_post_type_capabilities( (object) $args );
            unset( $args['capabilities'] );
            if ( is_array( $args['capability_type'] ) ) $args['capability_type'] = $args['capability_type'][0];
            if ( false !== $args['query_var'] ) {
                if ( true === $args['query_var'] ) $args['query_var'] = $this->name;
                else $args['query_var'] = $this->_sanitize_title_with_dashes( $args['query_var'] );
            }
            if ( false !== $args['rewrite'] && ( $this->_is_admin() || $this->_get_option( 'permalink_structure' ) ) ) {
                if ( ! is_array( $args['rewrite'] ) ) $args['rewrite'] = array();
                if ( empty( $args['rewrite']['slug'] ))  $args['rewrite']['slug'] = $this->name;
                if ( ! isset( $args['rewrite']['with_front'] ))  $args['rewrite']['with_front'] = true;
                if ( ! isset( $args['rewrite']['pages'] ) )$args['rewrite']['pages'] = true;
                if ( ! isset( $args['rewrite']['feeds'] ) || ! $args['has_archive'] )
                    $args['rewrite']['feeds'] = (bool) $args['has_archive'];
                if ( ! isset( $args['rewrite']['ep_mask'] ) ) {
                    if ( isset( $args['permalink_ep_mask'] ) )
                        $args['rewrite']['ep_mask'] = $args['permalink_ep_mask'];
                    else $args['rewrite']['ep_mask'] = EP_PERMALINK;
                }
            }
            foreach ( $args as $property_name => $property_value )
                $this->$property_name = $property_value;
            $this->labels = $this->_get_post_type_labels( $this );
            $this->label  = $this->labels->name;
        }//420
        /**
         * @description Sets the features support for the post type.
         */
        public function add_supports(): void{
            if ( ! empty( $this->supports ) ) {
                foreach ( $this->supports as $feature => $args ) {
                    if ( is_array( $args ) )
                        $this->_add_post_type_support( $this->name, $feature, $args );
                    else $this->_add_post_type_support( $this->name, $args );
                }
                unset( $this->supports );
            } elseif ( false !== $this->supports )// Add default features.
                $this->_add_post_type_support( $this->name, array( 'title', 'editor' ) );
        }//580
        /**
         * @description Adds the necessary rewrite rules for the post type.
         */
        public function add_rewrite_rules(): void{
            $tp_rewrite = $this->_init_rewrite();
            $tp_core = $this->_init_core();
            if ( false !== $this->query_var && $tp_core && $this->_is_post_type_viewable( $this ) ) {
                $tp_core->add_query_var( $this->query_var );
            }
            if ( false !== $this->rewrite && ( $this->_is_admin() || $this->_get_option( 'permalink_structure' ) ) ) {
                if ( $this->hierarchical ) {
                    $this->_add_rewrite_tag( "%$this->name%", '(.+?)', $this->query_var ? "{$this->query_var}=" : "post_type=$this->name&pagename=" );
                } else {
                    $this->_add_rewrite_tag( "%$this->name%", '([^/]+)', $this->query_var ? "{$this->query_var}=" : "post_type=$this->name&name=" );
                }
                if ( $this->has_archive ) {
                    $archive_slug = true === $this->has_archive ? $this->rewrite['slug'] : $this->has_archive;
                    if ( $this->rewrite['with_front'] ) {
                        $archive_slug = substr( $tp_rewrite->front, 1 ) . $archive_slug;
                    } else {
                        $archive_slug = $tp_rewrite->root . $archive_slug;
                    }
                    $this->_add_rewrite_rule( "{$archive_slug}/?$", "index.php?post_type=$this->name", 'top' );
                    if ( $this->rewrite['feeds'] && $tp_rewrite->feeds ) {
                        $feeds = '(' . trim( implode( '|', $tp_rewrite->feeds ) ) . ')';
                        $this->_add_rewrite_rule( "{$archive_slug}/feed/$feeds/?$", "index.php?post_type=$this->name" . '&feed=$matches[1]', 'top' );
                        $this->_add_rewrite_rule( "{$archive_slug}/$feeds/?$", "index.php?post_type=$this->name" . '&feed=$matches[1]', 'top' );
                    }
                    if ( $this->rewrite['pages'] ) {
                        $this->_add_rewrite_rule( "{$archive_slug}/{$tp_rewrite->pagination_base}/([0-9]{1,})/?$", "index.php?post_type=$this->name" . '&paged=$matches[1]', 'top' );
                    }
                }
                $permastruct_args         = $this->rewrite;
                $permastruct_args['feed'] = $permastruct_args['feeds'];
                $this->_add_permastruct( $this->name, "{$this->rewrite['slug']}/%$this->name%", $permastruct_args );
            }
        }//604
        /**
         * @description Registers the post type meta box if a custom callback was specified.
         */
        public function register_meta_boxes(): void{
            if ( $this->register_meta_box_cb )
                $this->_add_action( 'add_meta_boxes_' . $this->name, $this->register_meta_box_cb, 10, 1 );
        }//648
        /**
         * @description Adds the future post hook action for the post type.
         */
        public function add_hooks(): void{
            $this->_add_action( 'future_' . $this->name, '_future_post_hook', 5, 2 );
        }//659
        /**
         * @description Registers the taxonomies for the post type.
         */
        public function register_taxonomies(): void{
            foreach ( $this->taxonomies as $taxonomy )
                $this->_register_taxonomy_for_object_type( $taxonomy, $this->name );
        }//668
        /**
         * @description Removes the features support for the post type.
         */
        public function remove_supports(): void{
            unset( $this->_tp_post_type_features[ $this->name ] );
        }//681
        /**
         * @description Removes any rewrite rules, permastructs, and rules for the post type.
         */
        public function remove_rewrite_rules(): void{
            $tp_rewrite = $this->_init_rewrite();
            $tp_core = $this->_init_core();
            $post_type_meta_caps = $this->post_type_meta_caps; //todo
            if ( false !== $this->query_var ) {
                $tp_core->remove_query_var( $this->query_var );
            }
            if ( false !== $this->rewrite ) {
                $this->_remove_rewrite_tag( "%$this->name%" );
                $this->_remove_permastruct( $this->name );
                foreach ( $tp_rewrite->extra_rules_top as $regex => $query ) {
                    if ( false !== strpos( $query, "index.php?post_type=$this->name" ) ) {
                        unset( $tp_rewrite->extra_rules_top[ $regex ] );
                    }
                }
            }
            foreach ( $this->cap as $cap ) {
                unset( $post_type_meta_caps[ $cap ] );
            }
        }//696
        /**
         * @description Unregisters the post type meta box if a custom callback was specified.
         */
        public function unregister_meta_boxes(): void{
            if ( $this->register_meta_box_cb )
                $this->_remove_action( 'add_meta_boxes_' . $this->name, $this->register_meta_box_cb, 10 );
        }//726
        /**
         * @description Removes the post type from all taxonomies.
         */
        public function unregister_taxonomies(): void{
            foreach ( $this->_get_object_taxonomies( $this->name ) as $taxonomy )
                $this->_unregister_taxonomy_for_object_type( $taxonomy, $this->name );
        }//737
        /**
         * @description Removes the future post hook action for the post type.
         */
        public function remove_hooks(): void{
            $this->_remove_action( 'future_' . $this->name, '_future_post_hook', 5 );
        }//748
        public function get_rest_controller(){
            if ( ! $this->show_in_rest ) { return null; }
            $class = $this->rest_controller_class ?: TP_REST_Posts_Controller::class;
            if ( ! class_exists( $class ) ) {return null;}
            if ( ! is_subclass_of( $class, TP_REST_Controller::class ) ) {return null;}
            if ( ! $this->rest_controller ) {$this->rest_controller = new $class( $this->name );}
            if ( ! ( $this->rest_controller instanceof $class ) ) {return null; }
            return $this->rest_controller;
        }//762
    }
}else die;