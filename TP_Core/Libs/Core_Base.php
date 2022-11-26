<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-4-2022
 * Time: 17:41
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Comment\_comment_01;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_rewrite;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Feed\_feed_01;
use TP_Core\Traits\Feed\_feed_03;
use TP_Core\Traits\Feed\_feed_04;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Pluggables\_pluggable_02;
use TP_Core\Traits\Post\_post_02;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\Post\_post_09;
use TP_Core\Traits\Post\_post_11;
use TP_Core\Traits\Query\_query_01;
use TP_Core\Traits\Query\_query_02;
use TP_Core\Traits\Query\_query_03;
use TP_Core\Traits\Query\_query_04;
use TP_Core\Traits\Misc\_rewrite;
use TP_Core\Traits\Taxonomy\_taxonomy_01;
use TP_Core\Traits\Templates\_comment_template_04;
use TP_Core\Traits\Templates\_general_template_02;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\User\_user_02;
if(ABSPATH){
    class Core_Base {
        use _init_rewrite, _init_queries;
        use _filter_01, _action_01, _option_01, _I10n_01;
        use _feed_01, _feed_03;
        use _query_01, _query_02,_query_03, _query_04, _feed_04, _comment_01;
        use _comment_template_04, _pluggable_01, _pluggable_02, _load_03;
        use _methods_01, _methods_04, _methods_08, _link_template_09;
        use _general_template_02, _post_02, _post_03, _post_04;
        use _post_09, _post_11, _formats_11, _rewrite;
        use _taxonomy_01, _user_02;
        protected $_private_query_vars = ['offset', 'posts_per_page', 'posts_per_archive_page', 'showposts', 'nopaging', 'post_type', 'post_status', 'category__in', 'category__not_in', 'category__and', 'tag__in', 'tag__not_in', 'tag__and', 'tag_slug__in', 'tag_slug__and', 'tag_id', 'post_mime_type', 'perm', 'comments_per_page', 'post__in', 'post__not_in', 'post_parent', 'post_parent__in', 'post_parent__not_in', 'title', 'fields' ];
        public $did_permalink = false;
        public $extra_query_vars = [];
        public $matched_query;
        public $matched_rule;
        // t is w?
        public $public_query_vars = [ 'm', 'p', 'posts', 't', 'cat', 'with_comments', 'without_comments', 's', 'search', 'exact', 'sentence', 'calendar', 'page', 'paged', 'more', 'tb', 'pb', 'author', 'order', 'orderby', 'year', 'monthnum', 'day', 'hour', 'minute', 'second', 'name', 'category_name', 'tag', 'feed', 'author_name', 'pagename', 'page_id', 'error', 'attachment', 'attachment_id', 'subpost', 'subpost_id', 'preview', 'robots', 'favicon', 'taxonomy', 'term', 'cpage', 'post_type', 'embed'];
        public $query_string;
        public $query_vars;
        public $request;
    }
}else die;