<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 09:20
 */
namespace TP_Core\Traits\Taxonomy;
use TP_Core\Traits\Inits\_init_rewrite;
use TP_Core\Traits\Inits\_init_taxonomy;
use TP_Core\Libs\TP_Taxonomy;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _taxonomy_01 {
        use _init_rewrite;
        use _init_taxonomy;
        /**
         * @description Creates the initial taxonomies.
         */
        protected function _create_initial_taxonomies():void{
            $this->tp_rewrite = $this->_init_rewrite();
            if ( ! $this->_did_action( 'init' ) )
                $rewrite = ['category' => false, 'post_tag' => false,'post_format' => false,];
            else {
                $post_format_base = $this->_apply_filters( 'post_format_rewrite_base', 'type' );
                $rewrite = [
                    'category' => ['hierarchical' => true,
                        'slug' => $this->_get_option( 'category_base' ) ?: 'category',
                        'with_front' => ! $this->_get_option( 'category_base' ) || $this->tp_rewrite->using_index_permalinks(),
                        'ep_mask' => EP_CATEGORIES,
                    ],
                    'post_tag' => ['hierarchical' => false,
                        'slug' => $this->_get_option( 'tag_base' ) ?: 'tag',
                        'with_front' => ! $this->_get_option( 'tag_base' ) || $this->tp_rewrite->using_index_permalinks(),
                        'ep_mask' => EP_TAGS,
                    ],
                    'post_format' => $post_format_base ? array( 'slug' => $post_format_base ) : false,
                ];
            }
            $this->_register_taxonomy(
                'category','post',
                ['hierarchical' => true,'query_var' => 'category_name','rewrite' => $rewrite['category'],
                    'public' => true,'show_ui' => true,'show_admin_column' => true,'_builtin' => true,
                    'capabilities' => ['manage_terms' => 'manage_categories', 'edit_terms' => 'edit_categories',
                        'delete_terms' => 'delete_categories','assign_terms' => 'assign_categories',],
                    'show_in_rest' => true,'rest_base' => 'categories','rest_controller_class' => 'TP_REST_Terms_Controller',
                ]
            );
            $this->_register_taxonomy(
                'post_tag','post',
                ['hierarchical' => false,'query_var' => 'tag','rewrite' => $rewrite['post_tag'],
                    'public' => true,'show_ui' => true,'show_admin_column' => true,'_builtin' => true,
                    'capabilities' => ['manage_terms' => 'manage_post_tags','edit_terms' => 'edit_post_tags',
                        'delete_terms' => 'delete_post_tags','assign_terms' => 'assign_post_tags',],
                    'show_in_rest' => true,'rest_base' => 'tags','rest_controller_class' => 'TP_REST_Terms_Controller',
                ]
            );
            $this->_register_taxonomy(
                'nav_menu','nav_menu_item',
                ['public' => false,'hierarchical' => false,
                    'labels' => ['name' => $this->__( 'Navigation Menus' ),'singular_name' => $this->__( 'Navigation Menu' ),],
                    'query_var' => false,'rewrite' => false,'show_ui' => false,'_builtin' => true,'show_in_nav_menus' => false,
                    'capabilities' => ['manage_terms' => 'edit_theme_options','edit_terms' => 'edit_theme_options',
                        'delete_terms' => 'edit_theme_options','assign_terms' => 'edit_theme_options',],
                    'show_in_rest' => true,'rest_base' => 'menus','rest_controller_class' => 'TP_REST_Menus_Controller',
                ]
            );
            $this->_register_taxonomy(
                'link_category','link',
                ['hierarchical' => false,
                    'labels' => ['name' => $this->__( 'Link Categories' ),'singular_name' => $this->__( 'Link Category' ),
                        'search_items' => $this->__( 'Search Link Categories' ),'popular_items' => null,
                        'all_items' => $this->__( 'All Link Categories' ),'edit_item' => $this->__( 'Edit Link Category' ),
                        'update_item' => $this->__( 'Update Link Category' ),'add_new_item' => $this->__( 'Add New Link Category' ),
                        'new_item_name' => $this->__( 'New Link Category Name' ),'separate_items_with_commas' => null,
                        'add_or_remove_items' => null,'choose_from_most_used' => null,'back_to_items' => $this->__( '&larr; Go to Link Categories' ),],
                    'capabilities' => ['manage_terms' => 'manage_links','edit_terms' => 'manage_links',
                        'delete_terms' => 'manage_links','assign_terms' => 'manage_links',],
                    'query_var' => false,'rewrite' => false,'public' => false,'show_ui' => true,'_builtin' => true,
                ]
            );
            $this->_register_taxonomy(
                'post_format','post',
                ['public' => true,'hierarchical' => false,
                    'labels' => ['name' => $this->_x( 'Formats', 'post format' ),'singular_name' => $this->_x( 'Format', 'post format' ),],
                    'query_var' => true,'rewrite' => $rewrite['post_format'],'show_ui' => false,
                    '_builtin' => true,'show_in_nav_menus' => $this->_current_theme_supports( 'post-formats' ),
                ]
            );
            $this->_register_taxonomy(
                'tp_theme',['tp_template', 'tp_template_part', 'tp_global_styles'],
                ['public' => false,'hierarchical' => false,
                    'labels' => ['name' => $this->__( 'Themes' ),'singular_name' => $this->__( 'Theme' ),],
                    'query_var' => false,'rewrite' => false,'show_ui' => false,'_builtin' => true,
                    'show_in_nav_menus' => false, 'show_in_rest' => false,
                ]
            );
            $this->_register_taxonomy(
                'tp_template_part_area',['tp_template_part'],
                ['public' => false,'hierarchical' => false,
                    'labels' => ['name' => $this->__( 'Template Part Areas' ), 'singular_name' => $this->__( 'Template Part Area' ),],
                    'query_var' => false,'rewrite' => false,'show_ui' => false,'_builtin' => true,
                    'show_in_nav_menus' => false,'show_in_rest' => false,
                ]
            );
        }//25
        /**
         * @description Retrieves a list of registered taxonomy names or objects.
         * @param array $args
         * @param string $output
         * @param string $operator
         * @return mixed
         */
        protected function _get_taxonomies( $args = [], $output = 'names', $operator = 'and' ){
            $tp_taxonomies = $this->_init_taxonomy();
            $field = ( 'names' === $output ) ? 'name' : false;
            return $this->_tp_filter_object_list( $tp_taxonomies, $args, $operator, $field );
        }//241
        /**
         * @description Return the names or objects of the taxonomies which are registered for the requested object or object type,
         * @description . such as a post object or post type name.
         * @param $object
         * @param string $output
         * @return mixed
         */
        protected function _get_object_taxonomies( $object, $output = 'names' ){
            $this->tp_taxonomies = $this->_init_taxonomy();
            if ( is_object( $object ) ) {
                if ( 'attachment' === $object->post_type )
                    return $this->_get_attachment_taxonomies( $object, $output );
                $object = $object->post_type;
            }
            $object = (array) $object;
            $taxonomies = [];
            foreach ( (array) $this->tp_taxonomies as $tax_name => $tax_obj ) {
                if ( array_intersect( $object, (array) $tax_obj->object_type ) ) {
                    if ( 'names' === $output ) $taxonomies[] = $tax_name;
                    else $taxonomies[ $tax_name ] = $tax_obj;
                }
            }
            return $taxonomies;
        }//270
        /**
         * @description Retrieves the taxonomy object of $taxonomy.
         * @param $taxonomy
         * @return mixed
         */
        protected function _get_taxonomy($taxonomy){
            $tp_taxonomies = $this->_init_taxonomy($taxonomy);
            if ( ! $this->_taxonomy_exists( $taxonomy ) ) return false;
            return $tp_taxonomies;
        }//309
        /**
         * @description Determines whether the taxonomy name exists.
         * @param $taxonomy
         * @return mixed
         */
        protected function _taxonomy_exists( $taxonomy ){
            //$tp_taxonomies = $this->_init_taxonomy($taxonomy);
            return (array) isset($tp_taxonomies[$taxonomy] );

        }//335
        public function taxonomy_exists( $taxonomy ){
            return $this->_taxonomy_exists($taxonomy);
        }
        /**
         * @description Determines whether the taxonomy object is hierarchical.
         * @param $taxonomy
         * @return bool
         */
        protected function _is_taxonomy_hierarchical( $taxonomy ):bool{
            if ( ! $this->_taxonomy_exists( $taxonomy ) ) return false;
            $taxonomy = $this->_get_taxonomy( $taxonomy );
            if($taxonomy instanceof TP_Taxonomy )
            return $taxonomy->hierarchical;
        }//359
        /**
         * @description Creates or modifies a taxonomy object.
         * @param $taxonomy
         * @param $object_type
         * @param array $args
         * @return TP_Error|TP_Taxonomy
         */
        protected function _register_taxonomy( $taxonomy, $object_type, $args = [] ){
            $tp_taxonomies = $this->_init_taxonomy();
            if ( ! is_array( $tp_taxonomies ) ) $tp_taxonomies = [];
            $args = $this->_tp_parse_args( $args );
            if ( empty( $taxonomy ) || strlen( $taxonomy ) > 32 ) {
                $this->_doing_it_wrong( __FUNCTION__, $this->__( 'Taxonomy names must be between 1 and 32 characters in length.' ), '4.2.0' );
                return new TP_Error( 'taxonomy_length_invalid', $this->__( 'Taxonomy names must be between 1 and 32 characters in length.' ) );
            }
            $taxonomy_object = new TP_Taxonomy( $taxonomy, $object_type, $args );
            $taxonomy_object->add_rewrite_rules();
            $tp_taxonomies[ $taxonomy ] = $taxonomy_object;
            $taxonomy_object->add_hooks();
            if ( ! empty( $taxonomy_object->default_term ) ) {
                $term = $this->_term_exists( $taxonomy_object->default_term['name'], $taxonomy );
                if ( $term ) $this->_update_option( 'default_term_' . $taxonomy_object->name, $term['term_id'] );
                else {
                    $term = $this->_tp_insert_term($taxonomy_object->default_term['name'],$taxonomy,
                        ['slug' => $this->_sanitize_title( $taxonomy_object->default_term['slug'] ),
                            'description' => $taxonomy_object->default_term['description'],]);
                    if ( ! $this->_init_error( $term ) )
                        $this->_update_option( 'default_term_' . $taxonomy_object->name, $term['term_id'] );
                }
            }
            $this->_do_action( 'registered_taxonomy', $taxonomy, $object_type, (array) $taxonomy_object );
            return $taxonomy_object;
        }//476
        /**
         * @description Unregisters a taxonomy.
         * @param $taxonomy
         * @return bool|TP_Error
         */
        protected function _unregister_taxonomy( $taxonomy ){
            if ( ! $this->_taxonomy_exists( $taxonomy ) )
                return new TP_Error( 'invalid_taxonomy', $this->__( 'Invalid taxonomy.' ) );
            $_taxonomy_object = $this->_get_taxonomy( $taxonomy );
            $taxonomy_object = null;
            if($_taxonomy_object instanceof TP_Taxonomy ){
                $taxonomy_object = $_taxonomy_object;
            }
            if ( $taxonomy_object->_builtin )
                return new TP_Error( 'invalid_taxonomy', $this->__( 'Unregistering a built-in taxonomy is not allowed.' ) );
            $tp_taxonomies = $this->_init_taxonomy();
            $taxonomy_object->remove_rewrite_rules();
            $taxonomy_object->remove_hooks();
            if ( ! empty( $taxonomy_object->default_term ) )
                $this->_delete_option( 'default_term_' . $taxonomy_object->name );
            unset( $tp_taxonomies[ $taxonomy ] );
            $this->_do_action( 'unregistered_taxonomy', $taxonomy );
            return true;
        }//546
        /**
         * @description Builds an object with all taxonomy labels out of a taxonomy object.
         * @param $tax
         * @return object
         */
        protected function _get_taxonomy_labels( $tax ){
            $tax->labels = (array) $tax->labels;
            if ( isset( $tax->helps ) && empty( $tax->labels['separate_items_with_commas'] ) )
                $tax->labels['separate_items_with_commas'] = $tax->helps;
            if ( isset( $tax->no_tagcloud ) && empty( $tax->labels['not_found'] ) )
                $tax->labels['not_found'] = $tax->no_tagcloud;
            $name_field_description   = $this->__( 'The name is how it appears on your site.' );
            $slug_field_description   = $this->__( 'The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.' );
            $parent_field_description = $this->__( 'Assign a parent term to create a hierarchy. The term Jazz, for example, would be the parent of Bebop and Big Band.' );
            $desc_field_description   = $this->__( 'The description is not prominent by default; however, some themes may show it.' );
            $nohier_vs_hier_defaults = [
                'name'                       => [$this->_x( 'Tags', 'taxonomy general name' ), $this->_x( 'Categories', 'taxonomy general name' )],
                'singular_name'              => [$this->_x( 'Tag', 'taxonomy singular name' ), $this->_x( 'Category', 'taxonomy singular name' )],
                'search_items'               => [$this->__( 'Search Tags' ), $this->__( 'Search Categories' )],
                'popular_items'              => [$this->__( 'Popular Tags' ), null],
                'all_items'                  => [$this->__( 'All Tags' ), $this->__( 'All Categories' )],
                'parent_item'                => [null, $this->__( 'Parent Category' )],
                'parent_item_colon'          => [null, $this->__( 'Parent Category:' )],
                'name_field_description'     => $name_field_description, $name_field_description ,
                'slug_field_description'     => [$slug_field_description, $slug_field_description],
                'parent_field_description'   => [null, $parent_field_description],
                'desc_field_description'     => [$desc_field_description, $desc_field_description],
                'edit_item'                  => [$this->__( 'Edit Tag' ), $this->__( 'Edit Category' )],
                'view_item'                  => [$this->__( 'View Tag' ), $this->__( 'View Category' )],
                'update_item'                => [$this->__( 'Update Tag' ), $this->__( 'Update Category' )],
                'add_new_item'               => [$this->__( 'Add New Tag' ), $this->__( 'Add New Category' )],
                'new_item_name'              => [$this->__( 'New Tag Name' ), $this->__( 'New Category Name' )],
                'separate_items_with_commas' => [$this->__( 'Separate tags with commas' ), null],
                'add_or_remove_items'        => [$this->__( 'Add or remove tags' ), null],
                'choose_from_most_used'      => [$this->__( 'Choose from the most used tags' ), null],
                'not_found'                  => [$this->__( 'No tags found.' ), $this->__( 'No categories found.' )],
                'no_terms'                   => [$this->__( 'No tags' ), $this->__( 'No categories' )],
                'filter_by_item'             => [null, $this->__( 'Filter by category' )],
                'items_list_navigation'      => [$this->__( 'Tags list navigation' ), $this->__( 'Categories list navigation' )],
                'items_list'                 => [$this->__( 'Tags list' ), $this->__( 'Categories list' )],
                /* translators: Tab heading when selecting from the most used terms. */
                'most_used'                  => [$this->_x( 'Most Used', 'tags' ), $this->_x( 'Most Used', 'categories' )],
                'back_to_items'              => [$this->__( '&larr; Go to Tags' ), $this->__( '&larr; Go to Categories' )],
                'item_link'                  => [$this->_x( 'Tag Link', 'navigation link block title' ), $this->_x( 'Category Link', 'navigation link block description' ),],
                'item_link_description'      => [$this->_x( 'A link to a tag.', 'navigation link block description' ), $this->_x( 'A link to a category.', 'navigation link block description' ),],
            ];
            $nohier_vs_hier_defaults['menu_name'] = $nohier_vs_hier_defaults['name'];
            $labels = $this->_get_custom_object_labels( $tax, $nohier_vs_hier_defaults );
            $taxonomy = $tax->name;
            $default_labels = clone $labels;
            $labels = $this->_apply_filters( "taxonomy_labels_{$taxonomy}", $labels );
            $labels = (object) array_merge( (array) $default_labels, (array) $labels );
            return $labels;
        }//651
        /**
         * @description Add an already registered taxonomy to an object type.
         * @param $taxonomy
         * @param $object_type
         * @return bool
         */
        protected function _register_taxonomy_for_object_type( $taxonomy, $object_type ):bool{
            if ( ! isset( $wp_taxonomies[ $taxonomy ] ) ) return false;
            if ( ! $this->_get_post_type_object( $object_type ) ) return false;
            if ( ! in_array( $object_type, $wp_taxonomies[ $taxonomy ]->object_type, true ) )
                $this->tp_taxonomies[ $taxonomy ]->object_type[] = $object_type;
            $this->tp_taxonomies[ $taxonomy ]->object_type = array_filter( $this->tp_taxonomies[ $taxonomy ]->object_type );
            $this->_do_action( 'registered_taxonomy_for_object_type', $taxonomy, $object_type );
            return true;
        }//748
    }
}else die;