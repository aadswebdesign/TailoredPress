<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-9-2022
 * Time: 20:24
 */
namespace TP_Admin\Libs\AdmPartials;
use TP_Admin\Traits\_adm_screen;
use TP_Admin\Traits\AdminTemplates\_adm_template_02;
use TP_Admin\Traits\AdminTemplates\_adm_template_03;
use TP_Admin\Traits\AdminTemplates\_adm_template_04;
use TP_Admin\Traits\AdminTemplates\_adm_template_05;
use TP_Admin\Traits\AdminTheme\_adm_theme_01;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Constructs\_construct_db;
use TP_Core\Traits\Constructs\_construct_template;
use TP_Core\Traits\Constructs\_construct_utils;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_05;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_locale;
use TP_Core\Traits\K_Ses\_k_ses_01;
use TP_Core\Traits\K_Ses\_k_ses_02;
use TP_Core\Traits\K_Ses\_k_ses_03;
use TP_Core\Traits\K_Ses\_k_ses_04;
use TP_Core\Traits\Load\_load_03;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Media\_media_01;
use TP_Core\Traits\Meta\_meta_01;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Methods\_methods_05;
use TP_Core\Traits\Methods\_methods_09;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_11;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Methods\_methods_15;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Options\_option_02;
use TP_Core\Traits\Pluggables\_pluggable_03;
use TP_Core\Traits\Pluggables\_pluggable_04;
use TP_Core\Traits\Post\_post_02;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Post\_post_05;
use TP_Core\Traits\Post\_post_11;
use TP_Core\Traits\Query\_query_02;
use TP_Core\Traits\Templates\_author_template_02;
use TP_Core\Traits\Templates\_comment_template_03;
use TP_Core\Traits\Templates\_link_template_01;
use TP_Core\Traits\Templates\_link_template_04;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Templates\_link_template_10;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Taxonomy\_taxonomy_01;
use TP_Admin\Traits\AdminRewrite\_adm_rewrite_02;
use TP_Admin\Traits\_adm_comment;
use TP_Core\Traits\Multisite\Methods\_ms_methods_06;
use TP_Core\Traits\Comment\_comment_01;
use TP_Core\Traits\Comment\_comment_02;
use TP_Core\Traits\Comment\_comment_03;
use TP_Core\Traits\Comment\_comment_04;
use TP_Core\Traits\Comment\_comment_07;
use TP_Core\Traits\Constructs\_construct_comment;
use TP_Core\Traits\Constructs\_construct_post;
use TP_Core\Traits\Formats\_formats_09;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\Media\_media_02;
use TP_Core\Traits\Pluggables\_pluggable_05;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Templates\_comment_template_01;
use TP_Core\Traits\Templates\_comment_template_02;
use TP_Core\Traits\Templates\_post_template_01;
use TP_Admin\Traits\_adm_bookmark;
use TP_Core\Traits\Misc\_bookmark;
use TP_Core\Traits\Taxonomy\_taxonomy_02;
use TP_Core\Traits\Templates\_category_template_01;
use TP_Core\Traits\Cron\_cron_02;
use TP_Core\Traits\Formats\_formats_03;
use TP_Core\Traits\Media\_media_06;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\Post\_post_06;
use TP_Core\Traits\Query\_query_04;
use TP_Core\Traits\Query\_query_05;
use TP_Core\Traits\Taxonomy\_taxonomy_04;
use TP_Core\Traits\Templates\_author_template_01;
use TP_Core\Traits\Templates\_category_template_03;
use TP_Core\Traits\Templates\_general_template_06;
use TP_Core\Traits\Templates\_post_template_02;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Methods\_methods_13;
use TP_Core\Traits\Multisite\_ms_load;
use TP_Core\Traits\Multisite\Blog\_ms_blog_02;
use TP_Core\Traits\Multisite\Blog\_ms_blog_03;
use TP_Core\Traits\Multisite\Site\_ms_site_01;
use TP_Core\Traits\Constructs\_construct_page;
use TP_Admin\Traits\AdminUpdate\_adm_update_02;
use TP_Core\Traits\Methods\_methods_20;
use TP_Core\Traits\Misc\_update;
use TP_Core\Traits\Misc\tp_script;
use TP_Core\Traits\Multisite\Blog\_ms_blog_01;
use TP_Core\Traits\Options\_option_03;
use TP_Core\Traits\Theme\_theme_01;
use TP_Core\Traits\Capabilities\_capability_02;
use TP_Core\Traits\Constructs\_construct_user;
use TP_Core\Traits\Multisite\_ms_network;
use TP_Core\Traits\Multisite\Methods\_ms_methods_01;
use TP_Admin\Traits\AdminMultiSite\_adm_multisite_02;
use TP_Admin\Traits\AdminTemplates\_adm_template_01;
use TP_Admin\Traits\AdminPost\_adm_post_01;
use TP_Admin\Traits\AdminPost\_adm_post_02;
use TP_Admin\Traits\AdminPost\_adm_post_03;
use TP_Core\Traits\Formats\_format_post_01;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Post\_post_08;
use TP_Core\Traits\Post\_post_10;
use TP_Core\Traits\Post\_post_12;
use TP_Core\Traits\Post\_post_13;
use TP_Core\Traits\Taxonomy\_taxonomy_08;
use TP_Core\Traits\Theme\_theme_02;
use TP_Core\Traits\Theme\_theme_03;
use TP_Core\Traits\Theme\_theme_07;
use TP_Core\Traits\Theme\_theme_08;
use TP_Core\Traits\User\_user_01;
use TP_Core\Traits\User\_user_03;
use TP_Admin\Traits\_adm_privacy_tools;
use TP_Core\Traits\Pluggables\_pluggable_02;
use TP_Core\Traits\User\_user_05;
use TP_Core\Traits\User\_user_07;
use TP_Core\Traits\Constructs\_construct_taxonomy;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Taxonomy\_taxonomy_06;
use TP_Core\Traits\Taxonomy\_taxonomy_07;
use TP_Core\Traits\Templates\_link_template_03;


if(ABSPATH){
    class Adm_Partials_Base{
        use tp_script, _action_01, _adm_bookmark, _adm_comment, _adm_multisite_02, _adm_post_01, _adm_post_02,_adm_post_03;
        use _adm_privacy_tools, _adm_rewrite_02, _adm_screen, _adm_template_01, _adm_template_02,_adm_template_03,_adm_template_04,_adm_template_05;
        use _adm_theme_01,_adm_update_02, _author_template_01, _author_template_02, _bookmark, _cache_01, _capability_01,_capability_02;
        use _category_template_01,_category_template_03, _comment_01,_comment_02,_comment_03,_comment_04,_comment_07;
        use _comment_template_01,_comment_template_02,_comment_template_03,_construct_comment,_construct_db, _construct_page,_construct_template;
        use _construct_post, _construct_taxonomy, _construct_user, _construct_utils, _cron_02, _filter_01;
        use _format_post_01, _formats_02,_formats_03,_formats_04,_formats_07,_formats_08,_formats_09,_formats_10,_formats_11;
        use _general_template_06, _I10n_01, _I10n_02,_I10n_03,_I10n_04,_I10n_05, _init_db, _init_locale,_k_ses_01,_k_ses_02,_k_ses_03,_k_ses_04;
        use _link_template_01,_link_template_03,_link_template_04,_link_template_09,_link_template_10, _load_03, _load_04;
        use _media_01, _media_02,_media_06,_meta_01;
        use _methods_01,_methods_03, _methods_05,_methods_08,_methods_09,_methods_10,_methods_11,_methods_12,_methods_13,_methods_15,_methods_20;
        use _ms_blog_01,_ms_blog_02,_ms_blog_03, _ms_load, _ms_methods_01,_ms_methods_06, _ms_network, _ms_site_01;
        use _option_01,_option_02,_option_03, _pluggable_01,_pluggable_02,_pluggable_03,_pluggable_04,_pluggable_05;
        use _post_01,_post_02,_post_03,_post_04,_post_05,_post_06,_post_08,_post_10,_post_11,_post_12,_post_13, _post_template_01,_post_template_02,_query_02,_query_04,_query_05;
        use _taxonomy_01, _taxonomy_02,_taxonomy_04,_taxonomy_06,_taxonomy_07,_taxonomy_08, _theme_01, _theme_02, _theme_03, _theme_07,_theme_08;
        use _update, _user_01, _user_03, _user_05,_user_07;

        //use _theme_08,_adm_theme_01;
        /**
         * @return mixed
         */
        public function get_blocks() {
            if(get_class($this)){
                die( 'method Adm_Segments::get_segment_blocks() must be overridden in a subclass.' );
            }
            return true;

        }//1008
    }
}else{die;}