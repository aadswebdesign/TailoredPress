<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-8-2022
 * Time: 21:51
 */
namespace TP_Core\Libs\Post;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\I10n\_I10n_05;
use TP_Core\Traits\Inits\_init_core;
use TP_Core\Traits\Inits\_init_rewrite;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Misc\_rewrite;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\Taxonomy\_taxonomy_02;
use TP_Core\Traits\Formats\_formats_03;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Taxonomy\_taxonomy_01;
use TP_Libs\Constants;

if(ABSPATH){
    class PostType_Base{
        use _action_01, _post_01, _post_03, _post_04, _load_03, _init_rewrite,_init_core;
        use _filter_01, _formats_03, _methods_10, _taxonomy_01,_taxonomy_02, _option_01;
        use _rewrite,_I10n_02,_I10n_04,_I10n_05;
        use Constants;
        //protected static $_default_labels = [];
        public $tp_post_type_features; //added
        public $post_type_meta_caps; //added

        public $builtin = false;
        public $can_export = true;
        public $cap;
        public $capability_type = 'post';
        public $delete_with_user;
        public $description = '';
        public $edit_link = 'post.php?post=%d';//todo
        public $exclude_from_search;
        public $has_archive = false;
        public $hierarchical = false;
        public $label;
        public $labels;
        public $map_meta_cap = false;
        public $menu_icon;
        public $menu_position;
        public $name;
        //public $post_type_defaults;//added
        public $public = false;
        public $publicly_queryable;
        public $query_var;
        public $register_meta_box_cb;
        public $rest_base;
        public $rest_controller;
        public $rest_controller_class;
        public $rest_namespace;
        public $rewrite;
        public $show_in_menu;
        public $show_ui;
        public $show_in_admin_bar;
        public $show_in_nav_menus;
        public $show_in_rest;
        public $supports;
        public $taxonomies = [];
        public $template = [];
        public $template_lock = false;
        //added for _I10n_03.php
        public $tp_I10n_unloaded;
    }
}else{die;}