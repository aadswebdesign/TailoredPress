<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-6-2022
 * Time: 15:09
 */
namespace TP_Core\Libs\Queries;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Comment\_comment_01;
use TP_Core\Traits\Constructs\_construct_post;
use TP_Core\Traits\Constructs\_construct_queries;
use TP_Core\Traits\Feed\_feed_01;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Categories\_category_02;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Cache\_cache_02;
use TP_Core\Traits\Comment\_comment_02;
use TP_Core\Traits\Comment\_comment_07;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_03;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\Inits\_init_user;
use TP_Core\Traits\Meta\_meta_02;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Methods\_methods_02;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_11;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Methods\_methods_15;
use TP_Core\Traits\Methods\_methods_16;
use TP_Core\Traits\Methods\_methods_18;
use TP_Core\Traits\Load\_load_02;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Methods\_methods_20;
use TP_Core\Traits\Multisite\Methods\_ms_methods_06;
use TP_Core\Traits\Multisite\Site\_ms_site_01;
use TP_Core\Traits\Post\_post_11;
use TP_Core\Traits\Query\_query_01;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_05;
use TP_Core\Traits\Media\_media_06;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Pluggables\_pluggable_02;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_02;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\Post\_post_06;
use TP_Core\Traits\Post\_post_09;
use TP_Core\Traits\Post\_post_12;
use TP_Core\Traits\Post\_post_13;
use TP_Core\Traits\Taxonomy\_taxonomy_01;
use TP_Core\Traits\Taxonomy\_taxonomy_02;
use TP_Core\Traits\Taxonomy\_taxonomy_03;
use TP_Core\Traits\Taxonomy\_taxonomy_04;
use TP_Core\Traits\Taxonomy\_taxonomy_06;
use TP_Core\Traits\User\_user_02;
use TP_Core\Traits\Inits\_init_post;
use TP_Core\Traits\Inits\_init_taxonomy;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Multisite\_ms_network;

if(ABSPATH){
    class Query_Base{
        use _init_queries, _init_error, _init_post, _init_db, _init_taxonomy, _init_user;
        use _action_01, _cache_01, _cache_02, _capability_01, _category_02,_comment_01, _comment_02, _comment_07;
        use _filter_01, _feed_01, _formats_02, _formats_03, _formats_04,_formats_07,_formats_08, _formats_10,_formats_11;
        use _methods_01,_methods_02, _methods_10,_methods_11,_methods_12,_methods_15, _methods_16,_methods_18,_methods_20;
        use _I10n_01, _I10n_05, _media_06,_user_02,_query_01, _meta_02,_load_02,_load_03, _load_04;
        use _option_01, _pluggable_01,_pluggable_02, _post_01,_post_02, _post_03,_post_04;
        use _post_06,_post_09, _post_11, _post_12, _post_13, _taxonomy_01, _taxonomy_02, _taxonomy_03, _taxonomy_04,_taxonomy_06;
        use _ms_network,_ms_site_01,_ms_methods_06,_construct_queries,_construct_post;
        protected $_fields;
        protected $_limits;
        protected $_orderby;
        protected $_groupby;
        protected $_where;
        protected $_join;
        protected $_from;

        //protected $_limit


        public $comment;
        public $comment_count = 0;
        public $comments;
        public $current_comment = -1;
        public $current_post = -1;
        public $date_query = false;
        public $found_comments = 0;
        public $found_networks = 0;
        public $found_posts = 0;
        public $found_sites = 0;
        public $in_the_loop = false;
        public $is_404 = false;
        public $is_admin = false;
        public $is_archive = false;
        public $is_attachment = false;
        public $is_author = false;
        public $is_category = false;
        public $is_comment_feed = false;
        public $is_date = false;
        public $is_day = false;
        public $is_embed = false;
        public $is_favicon = false;
        public $is_feed = false;
        public $is_home = false;
        public $is_month = false;
        public $is_page = false;
        public $is_paged = false;
        public $is_posts_page = false;
        public $is_post_type_archive = false;
        public $is_preview = false;
        public $is_privacy_policy = false;
        public $is_search = false;
        public $is_single = false;
        public $is_singular = false;
        public $is_robots = false;
        public $is_tag = false;
        public $is_tax = false;
        public $is_time = false;
        public $is_trackback = false;
        public $is_year = false;
        public $max_num_comment_pages = 0;
        public $max_num_pages = 0;
        public $meta_query = false;

        public $post;
        public $post_count = 0;
        public $posts;
        public $queried_object;
        public $queried_object_id;
        public $query;
        public $query_var_defaults;
        public $query_vars;
        public $request;
        public $sites;
        public $tax_query;
        public $terms;



        //added
        protected $_array_keys = [];
        protected $_filtered_where_clause;
        protected $_meta_query_clauses;
        protected $_names;
        protected $_query_keys = [];
        protected $_query_vars_changed;
        protected $_query_vars_hash;
        protected $_results;
        protected $_total_users = 0;
        protected $_tp_taxonomy;
        //added;
        public $meta_key;
        public $meta_value;
        public $networks;

        //












    }
}else die;