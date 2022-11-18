<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
use TP_Admin\Traits\AdminPageMenus\_adm_page_menu_01;
use TP_Core\Traits\Inits\_init_post;
use TP_Core\Traits\Inits\_init_post_type;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\Post\TP_Post_Type;
if(ABSPATH){
    trait _post_03{
        use _post_objects;
        use _init_post;
        use _init_post_type;
        use _adm_page_menu_01;
        /**
         * @description Retrieves a post type object by name.
         * @param $post_type
         * @return mixed
         */
        protected function _get_post_type_object( $post_type ){
            $this->tp_post_types[$post_type] = $this->_init_post_types($post_type);
            if ( ! is_scalar( $post_type ) || empty( $this->tp_post_types[$post_type] ) ) return false;
            return $this->tp_post_types[$post_type];
        }//1482
        /**
         * @description Get a list of all registered post type objects.
         * @param array $args
         * @param string $output
         * @param string $operator
         * @return mixed
         */
        protected function _get_post_types( $args = [], $output = 'names', $operator = 'and' ){
            $field = ( 'names' === $output ) ? 'name' : false;
            return $this->_tp_filter_object_list( $this->tp_post_types, $args, $operator, $field );
        }//1510
        /**
         * @description  Registers a post type.
         * @param $post_type
         * @param \array[] ...$args
         * @return TP_Error|TP_Post_Type
         */
        protected function _register_post_type( $post_type, array ...$args){
            if ( ! is_array( $this->tp_post_types ) ) $this->tp_post_types = [];
            $post_type = $this->_sanitize_key( $post_type );
            if ( empty( $post_type ) || strlen( $post_type ) > 20 ) {
                $this->_doing_it_wrong( __FUNCTION__, $this->__( 'Post type names must be between 1 and 20 characters in length.' ), '0.0.1' );
                return new TP_Error( 'post_type_length_invalid', $this->__( 'Post type names must be between 1 and 20 characters in length.' ) );
            }
            $post_type_object = new TP_Post_Type( $post_type, $args );
            $post_type_object->add_supports();
            $post_type_object->add_rewrite_rules();
            $post_type_object->register_meta_boxes();
            $this->tp_post_types[ $post_type ] = $post_type_object;

            $post_type_object->add_hooks();
            $post_type_object->register_taxonomies();
            $this->_do_action( 'registered_post_type', $post_type, $post_type_object );
            return $post_type_object;
        }//1674
        /**
         * @description Unregisters a post type.
         * @param $post_type
         * @return bool|TP_Error
         */
        protected function _unregister_post_type($post_type ){
            if ( ! $this->_post_type_exists( $post_type ) )
                return new TP_Error( 'invalid_post_type', $this->__( 'Invalid post type.' ) );
            $post_type_object = $this->_get_post_type_object( $post_type );
            /** @noinspection PhpUndefinedFieldInspection */
            if ($post_type_object->_builtin )
                return new TP_Error( 'invalid_post_type', $this->__( 'Un-registering a built-in post type is not allowed' ) );
            if( $post_type_object instanceof TP_Post_Type ){
                $post_type_object->remove_supports();
                $post_type_object->remove_rewrite_rules();
                $post_type_object->unregister_meta_boxes();
                $post_type_object->remove_hooks();
                $post_type_object->unregister_taxonomies();
            }
            unset( $this->tp_post_types[$post_type ] );
            $this->_do_action( 'unregistered_post_type', $post_type );
            return true;
        }//1725
        /**
         * @description Build an object with all post type capabilities out of a post type object
         * @param $args
         * @return object
         */
        protected function _get_post_type_capabilities( $args ){
            if ( ! is_array( $args->capability_type ) )
                $args->capability_type = [$args->capability_type, $args->capability_type . 's'];
            @list( $singular_base, $plural_base ) = $args->capability_type;
            $default_capabilities = ['edit_post' => 'edit_' . $singular_base,
                'read_post' => 'read_' . $singular_base,'delete_post' => 'delete_' . $singular_base,
                /**Primitive capabilities used outside of map_meta_cap():*/
                'edit_posts' => 'edit_' . $plural_base,'edit_others_posts' => 'edit_others_' . $plural_base,
                'delete_posts' => 'delete_' . $plural_base, 'publish_posts' => 'publish_' . $plural_base,
                'read_private_posts' => 'read_private_' . $plural_base,
            ];
            if ( $args->map_meta_cap ) {
                $default_capabilities_for_mapping = ['read' => 'read','delete_private_posts' => 'delete_private_' . $plural_base,
                    'delete_published_posts' => 'delete_published_' . $plural_base,'delete_others_posts' => 'delete_others_' . $plural_base,
                    'edit_private_posts' => 'edit_private_' . $plural_base,'edit_published_posts' => 'edit_published_' . $plural_base,
                ];
                $default_capabilities = array_merge( $default_capabilities, $default_capabilities_for_mapping );
            }
            $capabilities = array_merge( $default_capabilities, $args->capabilities );
            if ( ! isset( $capabilities['create_posts'])) $capabilities['create_posts'] = $capabilities['edit_posts'];
            if ( $args->map_meta_cap ) $this->_post_type_meta_capabilities( $capabilities );
            return (object) $capabilities;
        }//1815
        /**
         * @description Store or return a list of post type meta caps for map_meta_cap().
         * @param null $capabilities
         */
        protected function _post_type_meta_capabilities( $capabilities = null ):void{
            foreach ( $capabilities as $core => $custom ) {
                if ( in_array( $core, array( 'read_post', 'delete_post', 'edit_post' ), true ) )
                    $this->post_type_meta_caps[ $custom ] = $core;
            }
        }//1874
        /**
         * @description Builds an object with all post type labels out of a post type object.
         * @param $post_type_object
         * @return object
         */
        protected function _get_post_type_labels( $post_type_object ){
            $label = $this->_post_type_objects();
            $post_versus_page_defaults = [
                'name' => $label['name'],'singular_name' => $label['singular_name'],
                'add_new' => $label['add_new'],'add_new_item' => $label['add_new_item'],
                'edit_item' => $label['edit_item'],'new_item' => $label['new_item'],
                'view_item' => $label['view_item'],'view_items' => $label['view_items'],
                'search_items' => $label['search_items'],'not_found' => $label['not_found'],
                'not_found_in_trash' => $label['not_found_in_trash'],'parent_item_colon' => $label['parent_item_colon'],
                'all_items' => $label['all_items'],'archives' => $label['archives'],'attributes' => $label['attributes'],
                'insert_into_item' => $label['insert_into_item'],'uploaded_to_this_item' => $label['uploaded_to_this_item'],
                'featured_image' => $label['featured_image'],'set_featured_image' => $label['set_featured_image'],
                'remove_featured_image' => $label['remove_featured_image'],'use_featured_image' => $label['use_featured_image'],
                'filter_items_list' => $label['filter_items_list'],'filter_by_date' => $label['filter_by_date'],
                'items_list_navigation' => $label['items_list_navigation'], 'items_list' => $label['items_list'],
                'item_published' => $label['item_published'],'item_published_privately' => $label['item_published_privately'],
                'item_reverted_to_draft' => $label['item_reverted_to_draft'],'item_scheduled' => $label['item_scheduled'],
                'item_updated' => $label['item_updated'],'item_link' => $label['item_link'],
                'item_link_description' => $label['item_link_description'],
            ];
            $post_versus_page_defaults['menu_name'] = $post_versus_page_defaults['name'];
            $labels = $this->_get_custom_object_labels( $post_type_object, $post_versus_page_defaults );
            $post_type = $post_type_object->name;
            $default_labels = clone $labels;
            $labels = $this->_apply_filters( "post_type_labels_{$post_type}", $labels, $default_labels );
            return $labels;
        }//1957
        /**
         * @description Build an object with custom-something object (post type, taxonomy) labels out of a custom-something object
         * @param $object
         * @param $post_versus_page_defaults
         * @return object
         */
        protected function _get_custom_object_labels( $object, $post_versus_page_defaults ){
            $object->labels = (array) $object->labels;
            if ( isset( $object->label ) && empty( $object->labels['name'] ) )
                $object->labels['name'] = $object->label;
            if ( ! isset( $object->labels['singular_name'] ) && isset( $object->labels['name'] ) )
                $object->labels['singular_name'] = $object->labels['name'];
            if ( ! isset( $object->labels['name_admin_bar'] ) )
                $object->labels['name_admin_bar'] = $object->labels['singular_name'] ?? $object->name;
            if ( ! isset( $object->labels['menu_name'] ) && isset( $object->labels['name'] ) )
                $object->labels['menu_name'] = $object->labels['name'];
            if ( ! isset( $object->labels['all_items'] ) && isset( $object->labels['menu_name'] ) )
                $object->labels['all_items'] = $object->labels['menu_name'];
            if ( ! isset( $object->labels['archives'] ) && isset( $object->labels['all_items'] ) )
                $object->labels['archives'] = $object->labels['all_items'];
            $defaults = array();
            foreach ( $post_versus_page_defaults as $key => $value )
                $defaults[ $key ] = $object->hierarchical ? $value[1] : $value[0];
            $labels  = array_merge( $defaults, $object->labels );
            $object->labels = (object) $object->labels;
            return (object) $labels;
        }//2044
        /**
         * @description Add sub menus for post types.
         */
        protected function _add_post_type_sub_menus():void{
            foreach ( $this->_get_post_types( array( 'show_ui' => true ) ) as $sub_type ) {
                $sub_type_obj = $this->_get_post_type_object( $sub_type );
                // Sub-menus only.
                if ( ! $sub_type_obj->show_in_menu || true === $sub_type_obj->show_in_menu )
                    continue;
                $this->_add_submenu_page( $sub_type_obj->show_in_menu, $sub_type_obj->labels->name, $sub_type_obj->labels->all_items, $sub_type_obj->cap->edit_posts, "edit.php?post_type=$sub_type" );
            }
        }//2087
        /**
         * @description Registers support of certain features for a post type.
         * @param $post_type
         * @param $feature
         * @param array ...$args
         */
        protected function _add_post_type_support( $post_type, $feature, ...$args ):void{
            $features = (array) $feature;
            foreach ( $features as $feature_item ) {
                if ( $args ) $this->tp_post_type_features[ $post_type ][ $feature_item ] = $args;
                else $this->tp_post_type_features[ $post_type ][ $feature_item ] = true;
            }
        }//2134
    }
}else die;