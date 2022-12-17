<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-5-2022
 * Time: 00:06
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_03;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\I10n\_I10n_05;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Inits\_init_core;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Methods\_methods_16;
use TP_Core\Traits\Methods\_methods_17;
use TP_Core\Traits\Misc\_rewrite;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Taxonomy\_taxonomy_01;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Controller;
use TP_Core\Libs\RestApi\EndPoints\TP_REST_Terms_Controller;
use TP_Libs\Constants;

if(ABSPATH){
    class TP_Taxonomy{
        use Constants;
        use _filter_01, _formats_02, _formats_03,_I10n_01,_I10n_02,_I10n_03,_I10n_04,_I10n_05,_init_core;
        use _load_03, _methods_10, _methods_16, _methods_17,_option_01, _post_03, _rewrite,  _taxonomy_01;
        public $name;
        public $label;
        public $labels;
        public $description = '';
        public $public = true;
        public $publicly_queryable = true;
        public $hierarchical = false;
        public $show_ui = true;
        public $show_in_menu = true;
        public $show_in_nav_menus = true;
        public $show_tag_cloud = true;
        public $show_in_quick_edit = true;
        public $show_admin_column = false;
        public $meta_box_cb;
        public $meta_box_sanitize_cb;
        public $object_type;
        public $cap;
        public $rewrite;
        public $query_var;
        public $update_count_callback;
        public $show_in_rest;
        public $rest_base;
        public $rest_namespace;
        public $rest_controller_class;
        public $rest_controller;
        public $default_term;
        public $sort;
        public $args;
        public $_builtin;
        public function __construct( $taxonomy, $object_type,array ...$args) {
            $this->_rewrite_constants();
            $this->name = $taxonomy;
            $this->set_props( $object_type, $args );
        }//279
        public function set_props( $object_type, array ...$args ): void{
            $args = $this->_tp_parse_args( $args );
            $args = $this->_apply_filters( 'register_taxonomy_args', $args, $this->name, (array) $object_type );
            $defaults = ['labels' => [],'description' => '','public' => true,'publicly_queryable' => null,
                'hierarchical' => false,'show_ui' => null,'show_in_menu' => null,'show_in_nav_menus' => null,
                'show_tagcloud' => null,'show_in_quick_edit' => null,'show_admin_column' => false,
                'meta_box_cb' => null,'meta_box_sanitize_cb' => null,'capabilities' => array(),'rewrite' => true,
                'query_var' => $this->name,'update_count_callback' => '','show_in_rest' => false,'rest_base' => false,
                'rest_namespace' => false,'rest_controller_class' => false,'default_term' => null,'sort' => null,
                'args' => null,'_builtin' => false,
            ];
            $args = array_merge( $defaults, $args );
            if ( null === $args['publicly_queryable'] ) $args['publicly_queryable'] = $args['public'];
            if ( false !== $args['query_var'] && ( $this->_is_admin() || false !== $args['publicly_queryable'] ) ) {
                if ( true === $args['query_var'] )  $args['query_var'] = $this->name;
                else $args['query_var'] = $this->_sanitize_title_with_dashes( $args['query_var'] );
            } else $args['query_var'] = false;
            if ( false !== $args['rewrite'] && ( $this->_is_admin() || $this->_get_option( 'permalink_structure' ) ) ) {
                $args['rewrite'] = $this->_tp_parse_args($args['rewrite'], ['with_front' => true,'hierarchical' => false,'ep_mask' => EP_NONE,]);
                if ( empty( $args['rewrite']['slug'] ) )
                    $args['rewrite']['slug'] = $this->_sanitize_title_with_dashes( $this->name );
            }
            if ( null === $args['show_ui'] ) $args['show_ui'] = $args['public'];
            if ( null === $args['show_in_menu'] || ! $args['show_ui'] ) $args['show_in_menu'] = $args['show_ui'];
            if ( null === $args['show_in_nav_menus'] ) $args['show_in_nav_menus'] = $args['public'];
            if ( null === $args['show_tagcloud'] )  $args['show_tagcloud'] = $args['show_ui'];
            if ( null === $args['show_in_quick_edit'] ) $args['show_in_quick_edit'] = $args['show_ui'];
            if ( false === $args['rest_namespace'] && ! empty( $args['show_in_rest'] ) ) $args['rest_namespace'] = 'wp/v2';
            $default_caps = ['manage_terms' => 'manage_categories','edit_terms' => 'manage_categories',
                'delete_terms' => 'manage_categories','assign_terms' => 'edit_posts',];
            $args['cap'] = (object) array_merge( $default_caps, $args['capabilities'] );
            unset( $args['capabilities'] );
            $args['object_type'] = array_unique( (array) $object_type );
            if ( null === $args['meta_box_cb'] ) {
                if ( $args['hierarchical'] ) $args['meta_box_cb'] = 'post_categories_meta_box';
                else $args['meta_box_cb'] = 'post_tags_meta_box';
            }
            $args['name'] = $this->name;
            if ( null === $args['meta_box_sanitize_cb'] ) {
                switch ( $args['meta_box_cb'] ) {
                    case 'post_categories_meta_box':
                        $args['meta_box_sanitize_cb'] = 'taxonomy_meta_box_sanitize_cb_checkboxes';
                        break;
                    case 'post_tags_meta_box':
                    default:
                        $args['meta_box_sanitize_cb'] = 'taxonomy_meta_box_sanitize_cb_input';
                        break;
                }
            }
            if ( ! empty( $args['default_term'] ) ) {
                if ( ! is_array( $args['default_term'] ) ) $args['default_term'] = ['name' => $args['default_term']];
                $args['default_term'] = $this->_tp_parse_args($args['default_term'],['name' => '','slug' => '','description' => '',]);
            }
            foreach ( $args as $property_name => $property_value ) $this->$property_name = $property_value;
            $this->labels = $this->_get_taxonomy_labels( $this );
            $this->label  = $this->labels->name;
        }//295
        public function add_rewrite_rules(): void {
            $tp = $this->_init_core();
            if ( false !== $this->query_var && $tp ) $tp->add_query_var( $this->query_var );
            if ( false !== $this->rewrite && ( $this->_is_admin() || $this->_get_option( 'permalink_structure' ) ) ) {
                if ( $this->hierarchical && $this->rewrite['hierarchical'] ) $tag = '(.+?)';
                else $tag = '([^/]+)';
                $this->_add_rewrite_tag( "%$this->name%", $tag, $this->query_var ? "{$this->query_var}=" : "taxonomy=$this->name&term=" );
                $this->_add_permastruct( $this->name, "{$this->rewrite['slug']}/%$this->name%", $this->rewrite );
            }
        }//468
        public function remove_rewrite_rules(): void{
            $tp = $this->_init_core();
            if ( false !== $this->query_var ) $tp->remove_query_var( $this->query_var );
            if ( false !== $this->rewrite ) {
                $this->_remove_rewrite_tag( "%$this->name%" );
                $this->_remove_permastruct( $this->name );
            }
        }//496
        public function add_hooks(): void{
            $this->_add_filter( 'tp_ajax_add-' . $this->name, '_tp_ajax_add_hierarchical_term' );
        }//517
        public function remove_hooks(): void{
            $this->_remove_filter( 'tp_ajax_add-' . $this->name, '_tp_ajax_add_hierarchical_term' );
        }//526
        public function get_rest_controller(): string{
            if ( ! $this->show_in_rest ) return null;
            $class = $this->rest_controller_class ?: TP_REST_Terms_Controller::class;
            if ( ! class_exists( $class ) ) return null;
            if ( ! is_subclass_of( $class, TP_REST_Controller::class ) ) return null;
            if ( ! $this->rest_controller ) $this->rest_controller = new $class( $this->name );
            if ( ! ( $this->rest_controller instanceof $class ) ) return null;
            return $this->rest_controller;
        }//540
    }
}else die;