<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 04:49
 */
namespace TP_Core\Traits\Post;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_05;
if(ABSPATH){
    trait _post_objects {
        use _I10n_01,_I10n_03, _I10n_05;
        protected function _post_types(){
            $insert_into ='Insert into';
            /* 0.01 internal use only. don't use this when registering your own post type. */
            /* internal use only. don't use this when registering your own post type. */
            $settings['shared'] = [
                'public' => true,'_builtin' => true, /* 0.01 */'_edit_link' => '_post_01.php?post=%d', /* 0.02 */
                'map_meta_cap' => true,'rewrite' => false,'query_var' => false,'delete_with_user' => true,
                'show_in_rest' => true,'rest_controller_class' => 'TP_REST_Posts_Controller',
            ];//
            $settings['post'] = [
                'labels' => ['name_admin_bar' => $this->_x( 'Post', 'add new from admin bar' ),],
                'capability_type' => 'post','menu_position' => 5,'menu_icon' => 'dashicons-admin-post',
                'hierarchical' => false, 'rest_base' => 'posts',
                'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'post-formats'],
            ];//22
            $settings['page'] = [
                'labels' => ['name_admin_bar' => $this->_x( 'Page', 'add new from admin bar' ),],
                'publicly_queryable' => false,'capability_type' => 'page','menu_position' => 20,
                'menu_icon' => 'dashicons-admin-page','hierarchical' => true,'rest_base' => 'pages',
                'supports' => ['title', 'editor', 'author', 'thumbnail', 'page-attributes', 'custom-fields', 'comments', 'revisions'],
            ];//45
            $settings['attachment'] = [
                'labels' => [
                    'name' => $this->_x( 'Media', 'post type general name' ), 'name_admin_bar' => $this->_x( 'Media', 'add new from admin bar' ),
                    'add_new' => $this->_x( 'Add New', 'add new media' ),'edit_item' => $this->__( 'Edit Media' ),
                    'view_item' => $this->__( 'View Attachment Page' ), 'attributes' => $this->__( 'Attachment Attributes' ),],
                'public' => true,'show_ui' => true, '_edit_link' => '_post_01.php?post=%d','_builtin' => true,'capability_type' => 'post',
                'capabilities' => ['create_posts' => 'upload_files',],'map_meta_cap' => true,'menu_icon' => 'dashicons-admin-media',
                'hierarchical' => false,'rewrite' => false,'query_var' => false,'show_in_nav_menus' => false,'delete_with_user' => true,
                'supports' => [ 'title', 'author', 'comments' ],'show_in_rest' => true,'rest_base' => 'media', 'rest_controller_class' => 'TP_REST_Attachments_Controller',
            ];//71
            $settings['revision'] = [
                'labels'=> ['name'=> $this->__( 'Revisions' ),'singular_name' => $this->__( 'Revision' ),],
                'public' => false,'_builtin' => true,'_edit_link' => 'revision.php?revision=%d', 'capability_type' => 'post',
                'map_meta_cap' => true,'hierarchical' => false,'rewrite' => false,'query_var' => false,'can_export' => false,
                'delete_with_user' => true,'supports' => ['author'],
            ];//105
            $settings['nav_menu_item'] = [
                'labels' => ['name' => $this->__( 'Navigation Menu Items' ),'singular_name' => $this->__( 'Navigation Menu Item' ),],
                'public' => false,'_builtin' => true,'hierarchical' => false,'rewrite' => false,'delete_with_user' => false,
                'query_var' => false,'map_meta_cap'  => true,'capability_type' => [ 'edit_theme_options', 'edit_theme_options'],
                'capabilities' => [
                    // Meta Capabilities.
                    'edit_post' => 'edit_post','read_post' => 'read_post','delete_post' => 'delete_post',
                    // Primitive Capabilities.
                    'edit_posts' => 'edit_theme_options','edit_others_posts' => 'edit_theme_options','delete_posts' => 'edit_theme_options',
                    'publish_posts' => 'edit_theme_options','read_private_posts' => 'edit_theme_options','read' => 'read','delete_private_posts' => 'edit_theme_options',
                    'delete_published_posts' => 'edit_theme_options','delete_others_posts' => 'edit_theme_options','edit_private_posts' => 'edit_theme_options','edit_published_posts' => 'edit_theme_options',],
                'show_in_rest' => true,'rest_base' => 'menu-items','rest_controller_class' => 'TP_REST_Menu_Items_Controller',
            ];//127
            $settings['custom_css'] = [
                'labels' => ['name' => $this->__( 'Custom CSS' ),'singular_name' => $this->__( 'Custom CSS' ), ],
                'public' => false,'hierarchical' => false,'rewrite' => false,'query_var' => false,'delete_with_user' => false,
                'can_export' => true,'_builtin' => true, 'supports'=> ['title','revisions'],
                'capabilities'=> ['delete_posts'=> 'edit_theme_options','delete_post'=> 'edit_theme_options',
                    'delete_published_posts' => 'edit_theme_options','delete_private_posts'=> 'edit_theme_options',
                    'delete_others_posts'=> 'edit_theme_options','edit_post' => 'edit_css','edit_posts' => 'edit_css',
                    'edit_others_posts' => 'edit_css','edit_published_posts' => 'edit_css','read_post' => 'read',
                    'read_private_posts' => 'read','publish_posts' => 'edit_theme_options'
                ]
            ];//165
            $settings['customize_changeset'] = [
                'labels' => [
                    'name' => $this->_x( 'Changesets', 'post type general name' ),'singular_name' => $this->_x( 'Changeset', 'post type singular name' ),
                    'add_new' => $this->_x( 'Add New', 'Customize Changeset' ),'add_new_item' => $this->__( 'Add New Changeset' ),
                    'new_item' => $this->__( 'New Changeset' ),'edit_item' => $this->__( 'Edit Changeset' ),'view_item' => $this->__( 'View Changeset' ),
                    'all_items' => $this->__( 'All Changesets' ),'search_items' => $this->__( 'Search Changesets' ), 'not_found' => $this->__( 'No changesets found.' ),
                    'not_found_in_trash' => $this->__( 'No changesets found in Trash.' ),
                ],
                'public'=> false,'_builtin'=> true,'map_meta_cap'=> true,'hierarchical'=> false,'rewrite'=> false,
                'query_var'=> false,'can_export'=> false,'delete_with_user' => false,'supports'=> ['title', 'author'],
                'capability_type'=> 'customize_change_set',
                'capabilities'=> [
                    'create_posts' => 'customize','delete_others_posts' => 'customize','delete_post' => 'customize',
                    'delete_posts' => 'customize','delete_private_posts' => 'customize','delete_published_posts' => 'customize',
                    'edit_others_posts' => 'customize','edit_post' => 'customize','edit_posts' => 'customize',
                    'edit_private_posts' => 'customize','edit_published_posts' => 'do_not_allow','publish_posts' => 'customize',
                    'read' => 'read','read_post'  => 'customize','read_private_posts'=> 'customize',
                ]
            ];//198
            $settings['oembed_cache'] = [
                'labels' => [ 'name' => $this->__( 'oEmbed Responses' ), 'singular_name' => $this->__( 'oEmbed Response' ),],
                'public' => false, 'hierarchical' => false,'rewrite' => false,'query_var' => false,
                'delete_with_user' => false,'can_export' => false,'_builtin' => true,'supports' => [],
            ];//244
            $settings['user_request'] = [
                'labels' => ['name' => $this->__( 'User Requests' ), 'singular_name' => $this->__( 'User Request' ),],
                'public' => false, '_builtin' => true, 'hierarchical' => false,'rewrite' => false,
                'query_var' => false,'can_export' => false,'delete_with_user' => false,'supports' => [],
            ];//261
            $settings['tp_block'] = [
                'labels' => [
                    'name' => $this->_x( 'Reusable blocks', 'post type general name' ),'singular_name' => $this->_x( 'Reusable block', 'post type singular name' ),
                    'add_new' => $this->_x( 'Add New', 'Reusable block' ),'add_new_item' => $this->__( 'Add new Reusable block' ),
                    'new_item' => $this->__( 'New Reusable block' ),'edit_item' => $this->__( 'Edit Reusable block' ), 'view_item' => $this->__( 'View Reusable block' ),
                    'all_items' => $this->__( 'All Reusable blocks' ),'search_items' => $this->__( 'Search Reusable blocks' ),'not_found' => $this->__( 'No reusable blocks found.' ),
                    'not_found_in_trash' => $this->__( 'No reusable blocks found in Trash.' ),'filter_items_list' => $this->__( 'Filter reusable blocks list' ),
                    'items_list_navigation' => $this->__( 'Reusable blocks list navigation' ),'items_list' => $this->__( 'Reusable blocks list' ),
                    'item_published' => $this->__( 'Reusable block published.' ),'item_published_privately' => $this->__( 'Reusable block published privately.' ),
                    'item_reverted_to_draft' => $this->__( 'Reusable block reverted to draft.' ), 'item_scheduled' => $this->__( 'Reusable block scheduled.' ),
                    'item_updated' => $this->__( 'Reusable block updated.' ),
                ],
                'public' => false,'_builtin' => true,'show_ui' => true,'show_in_menu' => false,'rewrite' => false,
                'show_in_rest' => true,'rest_base' => 'blocks','rest_controller_class' => 'TP_REST_Blocks_Controller',
                'capability_type' => 'block',
                'capabilities' => [
                    'read' => 'edit_posts','create_posts' => 'publish_posts','edit_posts' => 'edit_posts',
                    'edit_published_posts' => 'edit_published_posts','delete_published_posts' => 'delete_published_posts',
                    'edit_others_posts' => 'edit_others_posts','delete_others_posts' => 'delete_others_posts',
                ],
                'map_meta_cap' => true, 'supports'=> ['title','editor','revisions',],
            ];//280
            $settings['tp_template'] = [
                'label' => [
                    'name' => $this->_x( 'Templates', 'post type general name' ),'singular_name' => $this->_x( 'Template', 'post type singular name' ),
                    'add_new' => $this->_x( 'Add New', 'Template' ),'add_new_item' => $this->__( 'Add New Template' ),'new_item' => $this->__( 'New Template' ),
                    'edit_item' => $this->__( 'Edit Template' ),'view_item' => $this->__( 'View Template' ),'all_items' => $this->__( 'Templates' ),
                    'search_items' => $this->__( 'Search Templates' ),'parent_item_colon' => $this->__( 'Parent Template:' ),'not_found' => $this->__( 'No templates found.' ),
                    'not_found_in_trash' => $this->__( 'No templates found in Trash.' ),'archives' => $this->__( 'Template archives' ),
                    'insert_into_item' => $this->__($insert_into . 'template'),'uploaded_to_this_item' => $this->__( 'Uploaded to this template' ),
                    'filter_items_list' => $this->__( 'Filter templates list' ),'items_list_navigation' => $this->__( 'Templates list navigation' ),
                    'items_list' => $this->__( 'Templates list' ),
                ],
                'description' => $this->__( 'Templates to include in your theme.' ),
                'public' => false,'_builtin' => true,'has_archive' => false,'show_ui' => false,'show_in_menu' => false,'show_in_rest' => true,'rewrite' => false,
                'rest_base'=> 'templates','rest_controller_class' => 'TP_REST_Templates_Controller','capability_type'=> ['template', 'templates' ],
                'capabilities'=> [
                    'create_posts' => 'edit_theme_options','delete_posts' => 'edit_theme_options','delete_others_posts' => 'edit_theme_options',
                    'delete_private_posts' => 'edit_theme_options','delete_published_posts' => 'edit_theme_options','edit_posts' => 'edit_theme_options',
                    'edit_others_posts' => 'edit_theme_options','edit_private_posts' => 'edit_theme_options','edit_published_posts' => 'edit_theme_options',
                    'publish_posts' => 'edit_theme_options','read' => 'edit_theme_options','read_private_posts' => 'edit_theme_options',
                ],
                'map_meta_cap'=> true, 'supports'=> ['title','slug','excerpt','editor','revisions','author',]
            ];//333
            $settings['tp_template_part'] = [
                'label' => [
                    'name' => $this->_x( 'Template Parts', 'post type general name' ),'singular_name' => $this->_x( 'Template Part', 'post type singular name' ),
                    'add_new' => $this->_x( 'Add New', 'Template Part' ),'add_new_item'  => $this->__( 'Add New Template Part' ),
                    'new_item' => $this->__( 'New Template Part' ),'edit_item' => $this->__( 'Edit Template Part' ),
                    'view_item' => $this->__( 'View Template Part' ),'all_items' => $this->__( 'Template Parts' ),'search_items' => $this->__( 'Search Template Parts' ),
                    'parent_item_colon' => $this->__( 'Parent Template Part:' ),'not_found' => $this->__( 'No template parts found.' ),
                    'not_found_in_trash' => $this->__( 'No template parts found in Trash.' ), 'archives'=> $this->__( 'Template part archives' ),
                    'insert_into_item'=> $this->__( $insert_into . 'template part' ),'uploaded_to_this_item' => $this->__( 'Uploaded to this template part' ),
                    'filter_items_list' => $this->__( 'Filter template parts list' ),'items_list_navigation' => $this->__( 'Template parts list navigation' ),
                    'items_list' => $this->__( 'Template parts list' ),
                ],
                'description' => $this->__( 'Template parts to include in your templates.' ),'public' => false,'_builtin' => true,
                'has_archive' => false,'show_ui' => false,'show_in_menu' => false,'show_in_rest' => true,'rewrite' => false,
                'rest_base'=> 'template-parts','rest_controller_class' => 'TP_REST_Templates_Controller','map_meta_cap' => true,
                'capabilities' => ['create_posts' => 'edit_theme_options','delete_posts' => 'edit_theme_options',
                    'delete_others_posts' => 'edit_theme_options', 'delete_private_posts' => 'edit_theme_options',
                    'delete_published_posts' => 'edit_theme_options','edit_posts' => 'edit_theme_options',
                    'edit_others_posts' => 'edit_theme_options','edit_private_posts' => 'edit_theme_options',
                    'edit_published_posts' => 'edit_theme_options','publish_posts' => 'edit_theme_options',
                    'read' => 'edit_theme_options','read_private_posts' => 'edit_theme_options',
                ],
                'supports' => ['title','slug','excerpt','editor','revisions','author',]
            ];//392
            $settings['tp_global_styles'] = [
                'label' => $this->_x( 'Global Styles', 'post type general name' ),
                'description' => $this->__( 'Global styles to include in themes.' ),
                'public' => false,'_builtin' => true,'show_ui' => false,'show_in_rest' => false,
                'rewrite' => false,'capabilities' => ['read' => 'edit_theme_options',
                    'create_posts' => 'edit_theme_options','edit_posts' => 'edit_theme_options',
                    'edit_published_posts' => 'edit_theme_options','delete_published_posts' => 'edit_theme_options',
                    'edit_others_posts' => 'edit_theme_options','delete_others_posts' => 'edit_theme_options',
                ],'map_meta_cap' => true,'supports' => ['title','editor','revisions']
            ];//452
            $settings['tp_navigation'] = [
                'labels' => [
                    'name' => $this->_x( 'Navigation Menus', 'post type general name' ),
                    'singular_name' => $this->_x( 'Navigation Menu', 'post type singular name' ),
                    'add_new' => $this->_x( 'Add New', 'Navigation Menu' ),'add_new_item' => $this->__( 'Add New Navigation Menu' ),
                    'new_item' => $this->__( 'New Navigation Menu' ),'edit_item' => $this->__( 'Edit Navigation Menu' ),
                    'view_item' => $this->__( 'View Navigation Menu' ),'all_items' => $this->__( 'Navigation Menus' ),
                    'search_items' => $this->__( 'Search Navigation Menus' ),'parent_item_colon' => $this->__( 'Parent Navigation Menu:' ),
                    'not_found' => $this->__( 'No Navigation Menu found.' ),'not_found_in_trash' => $this->__( 'No Navigation Menu found in Trash.' ),
                    'archives' => $this->__( 'Navigation Menu archives' ),'insert_into_item' => $this->__( "{Insert into Navigation Menu}" ),//todo works this?
                    'uploaded_to_this_item' => $this->__( 'Uploaded to this Navigation Menu' ),
                    'filter_items_list' => $this->__( 'Filter Navigation Menu list' ),'items_list_navigation' => $this->__( 'Navigation Menus list navigation' ),
                    'items_list' => $this->__( 'Navigation Menus list' ),
                ],
                'description' => $this->__( 'Navigation menus that can be inserted into your site.' ),
                'public' => false,'_builtin' => true,'has_archive' => false,'show_ui' => true,'show_in_menu' => false,
                'show_in_admin_bar' => false,'show_in_rest' => true,'rewrite' => false,'map_meta_cap' => true,
                'capabilities' => ['edit_others_posts' => 'edit_theme_options',
                    'delete_posts' => 'edit_theme_options','publish_posts' => 'edit_theme_options',
                    'create_posts' => 'edit_theme_options','read_private_posts' => 'edit_theme_options',
                    'delete_private_posts' => 'edit_theme_options','delete_published_posts' => 'edit_theme_options',
                    'delete_others_posts' => 'edit_theme_options','edit_private_posts' => 'edit_theme_options',
                    'edit_published_posts' => 'edit_theme_options','edit_posts' => 'edit_theme_options',
                ],'rest_base' => 'navigation','rest_controller_class' => 'TP_REST_Posts_Controller',
                'supports' => ['title','editor','revisions',]
            ];//480
            return $settings;
        }
        protected function _post_status_levels(){
            $class_count = 'count';
            $settings['publish'] = [
                'label' => $this->_x( 'Published', 'post status' ),
                'public' => true, '_builtin' => true,
                /* translators: %s: Number of published posts. */
                'label_count' => $this->_n_noop(
                    "Published <span class='{$class_count}'>(%s)</span> ",
                    "Published <span class='{$class_count}'>(%s)</span> "
                ),
            ];//536
            $settings['future'] = [
                'label' => $this->_x( 'Scheduled', 'post status' ),
                'protected' => true,'_builtin' => true,
                'label_count' => $this->_n_noop(
                    "Scheduled <span class='{$class_count}'>(%s)</span> ",
                    "Scheduled <span class='{$class_count}'>(%s)</span> "
                ),
            ];//550
            $settings['draft'] = [
                'label' => $this->_x( 'Draft', 'post status' ),
                'protected' => true,'_builtin' => true,
                'label_count' => $this->_n_noop(
                    "Draft <span class='{$class_count}'>(%s)</span> ",
                    "Drafts <span class='{$class_count}'>(%s)</span> "
                ),
                'date_floating' => true,
            ];//564
            $settings['pending'] = [
                'label' => $this->_x( 'Pending', 'post status' ),
                'protected' => true,'_builtin' => true,
                'label_count' => $this->_n_noop(
                    "Pending <span class='{$class_count}'>(%s)</span> ",
                    "Pending <span class='{$class_count}'>(%s)</span> "
                ),
                'date_floating' => true,
            ];//579
            $settings['private'] = [
                'label'=> $this->_x( 'Private', 'post status' ),
                'private'=> true,'_builtin'=> true,
                'label_count' => $this->_n_noop(
                    "Private <span class='{$class_count}'>(%s)</span> ",
                    "Private <span class='{$class_count}'>(%s)</span> "
                ),
            ];//594
            $settings['trash'] = [
                'label' => $this->_x( 'Trash', 'post status' ),
                'internal' => true,'_builtin' => true,
                'label_count'  => $this->_n_noop(
                    "Trash <span class='{$class_count}'>(%s)</span> ",
                    "Trash <span class='{$class_count}'>(%s)</span> "
                ),
                'show_in_admin_status_list' => true,
            ];//609
            $settings['auto_draft'] = [
                'label' => 'auto-draft','internal' => true, '_builtin' => true, 'date_floating' => true,
            ];//623
            $settings['inherit'] = [
                'label' => 'inherit','internal' => true,'_builtin' => true,'exclude_from_search' => false,
            ];//633
            $settings['request_pending'] = [
                'label' => $this->_x( 'Pending', 'request status' ),
                'internal'=> true,'_builtin' => true,
                'label_count' => $this->_n_noop(
                    "Pending <span class='{$class_count}'>(%s)</span> ",
                    "Pending <span class='{$class_count}'>(%s)</span> "
                ),
                'exclude_from_search' => false,
            ];//643
            $settings['request_confirmed'] = [
                'label' => $this->_x( 'Confirmed', 'request status' ),
                'internal' => true,'_builtin' => true,
                'label_count' => $this->_n_noop(
                    "Confirmed <span class='{$class_count}'>(%s)</span> ",
                    "Confirmed <span class='{$class_count}'>(%s)</span> "
                ),
                'exclude_from_search' => false,
            ];//658
            $settings['request_failed'] = [
                'label' => $this->_x( 'Failed', 'request status' ),
                'internal' => true,'_builtin' => true,
                'label_count' => $this->_n_noop(
                    "Failed <span class='{$class_count}'>(%s)</span> ",
                    "Failed <span class='{$class_count}'>(%s)</span> "
                ),
                'exclude_from_search' => false
            ];//673
            $settings['request_completed'] = [
                'label'  => $this->_x( 'Completed', 'request status' ),
                'internal' => true,
                '_builtin' => true,
                'label_count' => $this->_n_noop(
                    "Completed <span class='{$class_count}'>(%s)</span> ",
                    "Completed <span class='{$class_count}'>(%s)</span>"
                ),
                'exclude_from_search' => false,
            ];//687
            return $settings;
        }
        protected function _post_type_objects(){
            $settings['name'] = [$this->_x( 'Posts', 'post type general name' ), $this->_x( 'Pages', 'post type general name' )];
            $settings['singular_name'] = [$this->_x( 'Post', 'post type singular name' ),$this->_x( 'Page', 'post type singular name')];
            $settings['add_new'] = [$this->_x( 'Add New', 'page' )];
            $settings['add_new_item'] = [$this->__('Add New Post'),$this->__('Add New Page')];
            $settings['edit_item'] = [$this->__('Edit Post'),$this->__('Edit Page')];
            $settings['new_item'] = [$this->__('New Post'),$this->__('New Page')];
            $settings['view_item'] = [$this->__('View Post'),$this->__('View Page')];
            $settings['view_items'] = [$this->__('View Posts'),$this->__('View Pages')];
            $settings['search_items'] = [$this->__('Search Posts'),$this->__('Search Pages')];
            $settings['not_found'] = [$this->__('No posts found.'),$this->__('No pages found.')];
            $settings['not_found_in_trash'] = [$this->__('No posts found in Trash.'),$this->__('No pages found in Trash.')];
            $settings['parent_item_colon'] = [null,$this->__('Parent Page:')];
            $settings['all_items'] = [$this->__('All Posts'),$this->__('All Pages')];
            $settings['archives'] = [$this->__('Post Archives'),$this->__('Page Archives')];
            $settings['attributes'] = [$this->__('Post Attributes'),$this->__('Page Attributes')];
            $settings['insert_into_item'] = [$this->__("{Insert into post}"),$this->__("{Insert into page}")];//todo works this?
            $settings['uploaded_to_this_item'] = [$this->__('Uploaded to this post'),$this->__('Uploaded to this page')];
            $settings['featured_image'] = [$this->_x('Featured image','post'),$this->_x('Featured image','page')];
            $settings['set_featured_image'] = [$this->_x('Set featured image','post'),$this->_x('Set featured image','page')];
            $settings['remove_featured_image'] = [$this->_x('Remove featured image','post'),$this->_x('Remove featured image','page')];
            $settings['use_featured_image'] = [$this->_x('Use as featured image','post'),$this->_x('Use as featured image','page')];
            $settings['filter_items_list'] = [$this->__('Filter posts list'),$this->__('Filter pages list')];
            $settings['filter_by_date'] = [$this->__('Filter by posted date'),$this->__('Filter by created page date')];
            $settings['items_list_navigation'] = [$this->__('Posts list navigation'),$this->__('Pages list navigation')];
            $settings['items_list'] = [$this->__('Posts list'),$this->__('Pages list')];
            $settings['item_published'] = [$this->__('Post published.'),$this->__('Page published.')];
            $settings['item_published_privately'] = [$this->__('Post published privately.'),$this->__('Page published privately.')];
            $settings['item_reverted_to_draft'] = [$this->__('Post reverted to draft.'),$this->__('Page reverted to draft.')];
            $settings['item_scheduled'] = [$this->__('Post scheduled.'),$this->__('Page scheduled.')];
            $settings['item_updated'] = [$this->__('Post updated.'),$this->__('Page updated.')];
            $settings['item_link'] = [
                $this->_x('Post Link','navigation link block title'),
                $this->_x('Page Link','navigation link block title'),
            ];
            $settings['item_link_description'] = [
                $this->_x('A link to a post.','navigation link block description'),
                $this->_x('A link to a page.','navigation link block description'),
            ];
            return $settings;
        }
    }
}else die;