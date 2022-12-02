<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 19:56
 */
namespace TP_Core\Libs\DB\Factory;
if(ABSPATH){
    trait _database_vars {
        private $__EZ_SQL_ERROR;//added
        private $__has_connected = false;
        private $__use_mysqli = true;//set to true because mysql is deprecated in php
        protected $_check_current_query = true;
        protected $_col_info;
        protected $_col_meta = [];
        protected $_db_user;
        protected $_db_host;
        protected $_db_name;
        protected $_db_password;
        protected $_dbh;
        protected $_incompatible_modes = [
            'NO_ZERO_DATE',
            'ONLY_FULL_GROUP_BY',
            'STRICT_TRANS_TABLES',
            'STRICT_ALL_TABLES',
            'TRADITIONAL',
            'ANSI',
        ];
        protected $_reconnect_retries = 5;
        protected $_result;
        protected $_table_charset = [];
        public $base_prefix;
        public $blog_id = 0;
        public $blog_meta;
        public $blogs;
        public $charset;
        public $collate;
        public $comment_meta;
        public $comments;
        public $error;
        public $field_types = [];
        public $func_call;
        public $global_tables = ['users', 'user_meta' ];
        public $insert_id = 0;
        public $is_mysql = [
            'blogs','blog_meta','signups','site','site_meta',
            'site_categories','registration_log',
        ];
        public $num_queries = 0;
        public $num_rows = 0;
        public $old_tables = [];
        public $options;
        public $post_meta;
        public $posts;
        public $prefix = '';
        public $queries;
        public $ready = false;
        public $registration_log;
        public $required_mysql_version;
        public $rows_affected = 0;
        public $show_errors = false;
        public $signups;
        public $site;
        public $site_categories;
        public $site_id = 0;
        public $site_meta;
        public $suppress_errors = false;
        public $tables = [
            'posts','comments','links','options','post_meta','terms',
            'term_taxonomy','term_relationships','term_meta','comment_meta',
        ];
        public $term_meta;
        public $term_relationships;
        public $term_taxonomy;
        public $terms;
        public $time_start;
        public $tp_version;
        public $user_meta;
        public $users;
    }
}else die;