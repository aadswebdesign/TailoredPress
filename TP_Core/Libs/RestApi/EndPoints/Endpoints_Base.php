<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-8-2022
 * Time: 07:59
 */
namespace TP_Core\Libs\RestApi\EndPoints;
use TP_Admin\Traits\Users\_user_admin_01;
use TP_Admin\Traits\Users\_user_admin_02;
use TP_Core\Traits\Block\_blocks_03;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Comment\_comment_02;
use TP_Core\Traits\Comment\_comment_03;
use TP_Core\Traits\Comment\_comment_04;
use TP_Core\Traits\Comment\_comment_05;
use TP_Core\Traits\Formats\_format_post_01;
use TP_Core\Traits\HTTP\_http_01;
use TP_Core\Traits\HTTP\_http_02;
use TP_Core\Traits\Inits\_init_block;
use TP_Core\Traits\Inits\_init_user;
use TP_Core\Traits\K_Ses\_k_ses_04;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Methods\_methods_20;
use TP_Core\Traits\Multisite\Methods\_ms_methods_01;
use TP_Core\Traits\Multisite\Methods\_ms_methods_02;
use TP_Core\Traits\Multisite\Site\_ms_site_01;
use TP_Core\Traits\RestApi\_rest_api_07;
use TP_Core\Traits\Taxonomy\_taxonomy_05;
use TP_Core\Traits\Taxonomy\_taxonomy_07;
use TP_Core\Traits\Templates\_block_utils_template_02;
use TP_Core\Traits\Templates\_category_template_03;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_03;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\Formats\_formats_06;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Methods\_methods_07;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Methods\_methods_09;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_11;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Methods\_methods_17;
use TP_Core\Traits\Templates\_comment_template_03;
use TP_Core\Traits\Templates\_comment_template_04;
use TP_Core\Traits\Multisite\Methods\_ms_methods_06;
use TP_Core\Traits\Media\_media_02;
use TP_Core\Traits\Media\_media_03;
use TP_Core\Traits\Media\_media_07;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Options\_option_02;
use TP_Core\Traits\Options\_option_03;
use TP_Core\Traits\Options\_option_04;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Pluggables\_pluggable_02;
use TP_Core\Traits\Pluggables\_pluggable_04;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_02;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\Post\_post_05;
use TP_Core\Traits\Post\_post_06;
use TP_Core\Traits\Post\_post_07;
use TP_Core\Traits\Post\_post_08;
use TP_Core\Traits\Post\_post_10;
use TP_Core\Traits\Post\_post_11;
use TP_Core\Traits\Post\_post_12;
use TP_Core\Traits\Post\_post_13;
use TP_Core\Traits\Post\_post_14;
use TP_Core\Traits\Templates\_link_template_01;
use TP_Core\Traits\Templates\_link_template_03;
use TP_Core\Traits\Templates\_link_template_04;
use TP_Core\Traits\Templates\_link_template_10;
use TP_Core\Traits\Templates\_post_template_01;
use TP_Core\Traits\Templates\_post_template_02;
use TP_Core\Traits\Templates\_post_template_04;
use TP_Core\Traits\Templates\_post_thumbnail_template;
use TP_Core\Traits\Query\_query_05;
use TP_Core\Traits\RestApi\_rest_api_01;
use TP_Core\Traits\RestApi\_rest_api_02;
use TP_Core\Traits\RestApi\_rest_api_03;
use TP_Core\Traits\RestApi\_rest_api_04;
use TP_Core\Traits\RestApi\_rest_api_05;
use TP_Core\Traits\RestApi\_rest_api_06;
use TP_Core\Traits\RestApi\_rest_api_08;
use TP_Core\Traits\Revisions\_revision_01;
use TP_Core\Traits\Revisions\_revision_02;
use TP_Core\Traits\Taxonomy\_taxonomy_01;
use TP_Core\Traits\Taxonomy\_taxonomy_02;
use TP_Core\Traits\Taxonomy\_taxonomy_04;
use TP_Core\Traits\Theme\_theme_01;
use TP_Core\Traits\User\_user_01;
use TP_Core\Traits\User\_user_02;
use TP_Core\Traits\User\_user_03;
use TP_Core\Traits\User\_user_04;
use TP_Core\Traits\User\_user_07;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_rest;
use TP_Core\Traits\Inits\_init_post;

//admin
use TP_Admin\Traits\File\_file_01;
use TP_Admin\Traits\File\_file_02;
use TP_Admin\Traits\MultiSite\_ms_admin_01;
use TP_Admin\Traits\Image\_image_01;
use TP_Admin\Traits\Image\_image_02;
use TP_Core\Traits\Templates\_author_template_02;

if(ABSPATH){
    class Endpoints_Base{
        use _block_utils_template_02, _blocks_03, _capability_01, _category_template_03;
        use _comment_02, _comment_03, _comment_04, _comment_05;
        use _comment_template_03, _comment_template_04,_filter_01, _formats_08;
        use _formats_03, _formats_04, _formats_06, _formats_10, _formats_11, _format_post_01;
        use _methods_01, _methods_03,_methods_07, _methods_08, _methods_09,_methods_10, _methods_11, _methods_12, _methods_17,_methods_20;
        use _I10n_01, _I10n_02,_I10n_03, _load_03, _load_04, _http_01,_http_02;
        use _link_template_01,_link_template_04, _link_template_03, _link_template_10;
        use _media_02, _media_03, _media_07,_ms_methods_01,_ms_methods_02, _ms_methods_06,_ms_site_01;
        use _option_01, _option_02, _option_03, _option_04;
        use _pluggable_01,_pluggable_02,_pluggable_04;
        use _post_01, _post_02, _post_03, _post_04, _post_05, _post_06, _post_07, _post_08;
        use _post_10, _post_11, _post_12, _post_13, _post_14;
        use _post_template_01, _post_template_02, _post_template_04,_author_template_02;
        use _post_thumbnail_template, _query_05, _k_ses_04;
        use _rest_api_01, _rest_api_02,_rest_api_03, _rest_api_04, _rest_api_05, _rest_api_06,_rest_api_07, _rest_api_08;
        use _revision_01, _revision_02, _taxonomy_01, _taxonomy_02, _taxonomy_04, _taxonomy_05;
        use _taxonomy_07, _theme_01,_user_01, _user_02,_user_03,_user_04,_user_07;
        use _init_error, _init_rest, _init_post, _init_block,_init_user;
        //admin
        use _file_01, _file_02, _ms_admin_01,_image_01,_image_02,_user_admin_01,_user_admin_02;
        protected $_fake;
        protected $_namespace;
        protected $_rest_base;
        protected $_schema;
        protected $_tp_rest_additional_fields;
        protected $_parent_post_type;
        protected $_parent_controller;
        protected $_parent_base;
        protected $_revisions_controller;
        public $request,$item;
    }
}else{die;}