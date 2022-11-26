<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 10:02
 */
namespace TP_Core\Libs;
use TP_Admin\Traits\AdminRewrite\_adm_rewrite_01;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Inits\_init_core;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_13;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_02;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Post\_post_09;
use TP_Core\Traits\Post\_post_10;
use TP_Core\Traits\Templates\_link_template_01;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Misc\_rewrite;
if(ABSPATH){
    class Rewrite_Base{
        use _init_db, _init_queries, _init_core, _option_01, _filter_01;
        use _methods_03, _methods_10, _methods_13, _rewrite,_adm_rewrite_01;
        use _post_01, _post_02, _post_03, _post_09, _post_10;
        use _action_01, _link_template_01, _link_template_09;
        use _formats_04, _formats_08, _load_04;
        public $author_base = 'author';
        public $author_structure;
        public $comment_feed_structure;
        public $comments_base = 'comments';
        public $comments_pagination_base = 'comment-page';
        public $date_structure;
        public $endpoints;
        public $extra_permanent_structures = [];
        public $extra_rules = [];
        public $extra_rules_top = [];
        public $feed_base = 'feed';
        public $feed_structure;
        public $feeds = ['feed', 'rdf', 'rss', 'rss2', 'atom'];
        public $front;
        public $index = 'index.php';
        public $matches = '';
        public $non_tp_rules = [];
        public $page_structure;
        public $pagination_base = 'page';
        public $permalink_structure;
        public $query_replace = ['year=', 'monthnum=','day=','hour=','minute=','second=','name=','p=','author_name=','pagename=','s=',];
        public $search_base = 'search';
        public $search_structure;
        public $rewrite_code = ['%year%','%monthnum%','%day%','%hour%','%minute%','%second%','%postname%','%post_id%','%author%','%pagename%','%search%',];
        //public $rewrite_replace = ['([0-9]{4})','([0-9]{1,2})','([0-9]{1,2})','([0-9]{1,2})','([0-9]{1,2})','([0-9]{1,2})','([^/]+)','([0-9]+)','([^/]+)','([^/]+?)','(.+)',];
        public $rewrite_replace = ['(\d{4})','(\d{1,2})','(\d{1,2})','(\d{1,2})','(\d{1,2})','(\d{1,2})','([^/]+)','(\d+)','([^/]+)','([^/]+?)','(.+)',];
        public $root = '';
        public $rules;
        public $use_trailing_slashes;
        public $use_verbose_page_rules = true;
        public $use_verbose_rules = false;
    }
}else die;