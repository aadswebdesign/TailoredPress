<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 06:05
 */
namespace TP_Core\Libs\SimplePie\Factory;
if(ABSPATH){
    trait _sp_vars{
        //PRIVATES
        private $__sp_add_attributes = ['audio' => ['preload' => 'none'], 'iframe' => ['sandbox' => 'allow-scripts allow-same-origin'], 'video' => ['preload' => 'none']];
        private $__sp_all_discovered_feeds = [];
        private $__sp_autodiscovery = SP_LOCATOR_ALL; //todo remove the quote marks
        private $__sp_autodiscovery_cache_duration = 604800; // 7 Days.
        private $__sp_built_in;
        private $__sp_cache = true;
        private $__sp_cache_duration = 3600;
        private $__sp_cache_handlers = [
            'mysql'     => '_SimplePie_Cache_MySQL',
            'memcache'  => '_SimplePie_Cache_Memcache',
            'memcached' => '_SimplePie_Cache_Memcached',
            //'redis'     => 'SimplePie_Cache_Redis' //todo
        ];
        private $__sp_cache_location = './cache'; //todo set the right path
        private $__sp_cache_name_function = 'md5'; //todo a better option as md5
        private $__sp_check_modified = false;
        private $__sp_compressed_data;
        private $__sp_compressed_size;
        private $__sp_config_settings;
        private $__sp_consumed;
        private $__sp_curl_options = [];
        private $__sp_data = [];
        private $__sp_data_length;
        private $__sp_email;
        private $__sp_enable_exceptions = false;
        private $__sp_error;

        private $__sp_feed;
        private $__sp_feed_url;
        private $__sp_file;
        private $__sp_flags;
        private $__sp_force_cache_fallback = false;
        private $__sp_force_feed = false;
        private $__sp_force_fsock_open = false;
        private $__sp_image_handler = '';
        private $__sp_input_encoding = false;
        private $__sp_item_limit = 0;
        private $__sp_link;
        private $__sp_max_checked_feeds = 10;
        private $__sp_min_compressed_size = 18;
        private $__sp_multi_feed_objects = [];
        private $__sp_multi_feed_url = [];
        private $__sp_name;
        private $__sp_order_by_date = true;
        private $__sp_permanent_url;
        private $__sp_position = 0;
        private $__sp_raw_data;
        private $__sp_sanitize;
        private $__sp_state;
        private $__sp_status_code;
        private $__sp_strip_attributes = ['bgsound', 'class', 'expr', 'id', 'style', 'onclick', 'onerror', 'onfinish', 'onmouseover', 'onmouseout', 'onfocus', 'onblur', 'lowsrc', 'dynsrc'];
        private $__sp_strip_html_tags = ['base', 'blink', 'body', 'doctype', 'embed', 'font', 'form', 'frame', 'frameset', 'html', 'iframe', 'input', 'marquee', 'meta', 'noscript', 'object', 'param', 'script', 'style'];
        private $__sp_timeout = 10;
        private $__sp_user = [];
        private $__sp_useragent = SP_USERAGENT; //todo remove the quote marks

        //PROTECTS
        protected $_sp_cache;
        protected $_sp_classes;
        protected $_sp_data = '';
        protected $_sp_data_length = '';
        protected $_sp_database_ids =[];
        protected $_sp_date = '';
        protected $_sp_default;
        protected $_sp_extension;
        protected $_sp_filename;
        protected $_sp_i_fragment;
        protected $_sp_i_host;
        protected $_sp_i_path = '';
        protected $_sp_i_query;
        protected $_sp_i_user_info;
        protected $_sp_id;
        protected $_sp_legacy;
        protected $_sp_location;
        protected $_sp_mysql;
        protected $_sp_name = '';
        protected $_sp_normalization = [
            'acap' => ['port' => 674],
            'dict' => ['port' => 2628],
            'file' => ['i_host' => 'localhost'],
            'http' => ['port' => 80,'i_path' => '/'],
            'https' => ['port' => 443,'i_path' => '/'],
        ];
        protected $_sp_options;
        protected $_sp_port;
        protected $_sp_position = 0;
        protected $_sp_prepare;
        protected $_sp_quote;
        protected $_sp_registry;
        protected $_sp_sanitize;
        protected $_sp_scheme;
        protected $_sp_state = 'http_version';
        protected $_sp_value ='';
        //PUBLICS
        public $sp_add_attributes;
        public $sp_base;
        public $sp_base_location;
        public $sp_bitrate;
        public $sp_body;
        public $sp_cache;
        public $sp_cache_class;
        public $sp_cache_location;
        public $sp_cache_name_function;
        public $sp_cached_entities;
        public $sp_captions;
        public $sp_categories;
        public $sp_channels;
        public $sp_check_modified = false;
        public $sp_checked_feeds;
        public $sp_child;
        public $sp_comment;
        public $sp_copyright;
        public $sp_credits;
        public $sp_curl_options;
        public $sp_current_byte;
        public $sp_current_column;
        public $sp_current_line;
        public $sp_current_xhtml_construct;
        public $sp_data= [];
        public $sp_data_s = [];
        public $sp_description;
        public $sp_dom;
        public $sp_dom_atts;
        public $sp_dom_element = [];
        public $sp_dom_name;
        public $sp_elements = [];
        public $sp_elsewhere;
        public $sp_enable_cache;
        public $sp_encode_instead_of_strip;
        public $sp_encoding;
        public $sp_error;
        public $sp_error_code;
        public $sp_error_string;
        public $sp_extension;
        public $sp_extra_field;
        public $sp_extra_flags;
        public $sp_duration;
        public $sp_expression;
        public $sp_file;
        public $sp_file_class;
        public $sp_file_class_args;
        public $sp_filename;
        public $sp_force_fsockopen;
        public $sp_framerate;
        public $sp_handler;
        public $sp_hashes;
        public $sp_headers = [];
        public $sp_height;
        public $sp_http_base;
        public $sp_http_version =0.0;
        public $sp_https_domains;
        public $sp_image_handler;
        public $sp_item;
        public $sp_javascript;
        public $sp_keywords;
        public $sp_label;
        public $sp_lang;
        public $sp_length;
        public $sp_link;
        public $sp_local;
        public $sp_location;
        public $sp_max_checked_feeds;
        public $sp_medium;
        public $sp_method = SP_FILE_SOURCE_NONE;
        public $sp_micro_time;
        public $sp_name;
        public $sp_namespace =[];
        public $sp_object;
        public $sp_os;
        public $sp_output_encoding;
        public $sp_pcre;
        public $sp_permanent_url;
        public $sp_placeholder;
        public $sp_player;
        public $sp_position = 0;
        public $sp_ratings;
        public $sp_reason;
        public $sp_redirects = 0;
        public $sp_registry;
        public $sp_relationship;
        public $sp_remove_div;
        public $sp_replace_url_attributes;
        public $sp_restrictions;
        public $sp_role;
        public $sp_samplingrate;
        public $sp_sanitize;
        public $sp_scheme;
        public $sp_separator;
        public $sp_standalone;
        public $sp_status_code = 0;
        public $sp_strip_attributes;
        public $sp_strip_comments;
        public $sp_strip_html_tags;
        public $sp_sub_id1;
        public $sp_sub_id2;
        public $sp_success = true;
        public $sp_term;
        public $sp_text;
        public $sp_thumbnails;
        public $sp_time = [];
        public $sp_timeout;
        public $sp_title;
        public $sp_type;
        public $sp_url;
        public $sp_useragent;
        public $sp_val;
        public $sp_value;
        public $sp_version;
        public $sp_width;
        public $sp_xml_base = [];
        public $sp_xml_base_explicit = [];
        public $sp_xml_lang = [];
    }
}else die;
