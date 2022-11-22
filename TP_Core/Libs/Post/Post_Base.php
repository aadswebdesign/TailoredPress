<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-8-2022
 * Time: 21:51
 */
namespace TP_Core\Libs\Post;
use TP_Core\Traits\Methods\_methods_11;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Meta\_meta_01;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_05;
use TP_Core\Traits\Post\_post_06;
use TP_Core\Traits\Taxonomy\_taxonomy_08;
use TP_Core\Traits\Templates\_category_template_03;
use TP_Core\Traits\Cache\_cache_01;
if(ABSPATH){
    class Post_Base{
        use _cache_01, _category_template_03, _methods_11;
        use _init_db, _meta_01, _post_01, _post_05, _post_06, _taxonomy_08;
        public $comment_count = 0;
        public $comment_status = 'open';
        public $description = '';
        public $filter;
        public $guid = '';
        public $ID;
        public $label;
        public $labels;
        public $menu_order = 0;
        public $name;
        public $ping_status = 'open';
        public $pinged = '';
        public $post_author = 0;
        public $post_content;
        public $post_content_filtered = '';
        public $post_date = '0000-00-00 00:00:00';
        public $post_date_gmt = '0000-00-00 00:00:00';
        public $post_excerpt = '';
        public $post_mime_type = '';
        public $post_modified = '0000-00-00 00:00:00';
        public $post_modified_gmt = '0000-00-00 00:00:00';
        public $post_name = '';
        public $post_parent = 0;
        public $post_password = '';
        public $post_status = 'publish';
        public $post_title = '';
        public $post_type = 'post';
        public $public = false;
        public $to_ping = '';
    }
}else{die;}