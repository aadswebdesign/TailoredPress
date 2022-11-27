<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-9-2022
 * Time: 17:19
 */
namespace TP_Admin\Traits\AdminSchema;
use TP_Core\Libs\DB\TP_Db;
use TP_Core\Libs\TP_Theme;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_05;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_13;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Options\_option_02;
use TP_Core\Traits\Theme\_theme_01;
if(ABSPATH){
    trait _adm_schema_01{
        use _adm_schema_construct, _capability_01, _I10n_01, _I10n_05, _methods_10;
        use _methods_13, _option_01,_option_02, _theme_01,_load_04;
        /**
         * @description  Retrieve the SQL for creating database tables.
         * @param string $scope
         * @param null $blog_id
         * @return array
         */
        protected function _tp_get_db_schema( $scope = 'all', $blog_id = null ):array{
            $this->__schema_construct();
            if ($this->tpdb instanceof TP_Db && $blog_id && $blog_id !== $this->tpdb->blog_id ) {
                $old_blog_id = $this->tpdb->set_blog_id( $blog_id );
            }
            $is_multisite = $this->_is_multisite() || ( defined( 'TP_INSTALLING_NETWORK' ) && TP_INSTALLING_NETWORK );
            $max_index_length = 191;
            // Blog-specific tables.
            $blog_tables = TP_CREATE_TABLE . " $this->tpdb->term_meta (
                meta_id bigint(20) unsigned NOT NULL auto_increment,
                term_id bigint(20) unsigned NOT NULL default '0',
                meta_key varchar(255) default NULL,
                meta_value longtext,
                PRIMARY KEY  (meta_id),
                KEY term_id (term_id),
                KEY meta_key (meta_key($max_index_length))
            ) $this->tp_charset_collate;
            CREATE TABLE $this->tpdb->terms (
                 term_id bigint(20) unsigned NOT NULL auto_increment,
                 name varchar(200) NOT NULL default '',
                 slug varchar(200) NOT NULL default '',
                 term_group bigint(10) NOT NULL default 0,
                 PRIMARY KEY  (term_id),
                 KEY slug (slug($max_index_length)),
                 KEY name (name($max_index_length))
            ) $this->tp_charset_collate;
            CREATE TABLE $this->tpdb->term_taxonomy (
                 term_taxonomy_id bigint(20) unsigned NOT NULL auto_increment,
                 term_id bigint(20) unsigned NOT NULL default 0,
                 taxonomy varchar(32) NOT NULL default '',
                 description longtext NOT NULL,
                 parent bigint(20) unsigned NOT NULL default 0,
                 count bigint(20) NOT NULL default 0,
                 PRIMARY KEY  (term_taxonomy_id),
                 UNIQUE KEY term_id_taxonomy (term_id,taxonomy),
                 KEY taxonomy (taxonomy)
            ) $this->tp_charset_collate;
            CREATE TABLE $this->tpdb->term_relationships (
                 object_id bigint(20) unsigned NOT NULL default 0,
                 term_taxonomy_id bigint(20) unsigned NOT NULL default 0,
                 term_order int(11) NOT NULL default 0,
                 PRIMARY KEY  (object_id,term_taxonomy_id),
                 KEY term_taxonomy_id (term_taxonomy_id)
            ) $this->tp_charset_collate;
            CREATE TABLE $this->tpdb->comment_meta (
                meta_id bigint(20) unsigned NOT NULL auto_increment,
                comment_id bigint(20) unsigned NOT NULL default '0',
                meta_key varchar(255) default NULL,
                meta_value longtext,
                PRIMARY KEY  (meta_id),
                KEY comment_id (comment_id),
                KEY meta_key (meta_key($max_index_length))
            ) $this->tp_charset_collate;            
            CREATE TABLE $this->tpdb->comments (
                comment_ID bigint(20) unsigned NOT NULL auto_increment,
                comment_post_ID bigint(20) unsigned NOT NULL default '0',
                comment_author tinytext NOT NULL,
                comment_author_email varchar(100) NOT NULL default '',
                comment_author_url varchar(200) NOT NULL default '',
                comment_author_IP varchar(100) NOT NULL default '',
                comment_date datetime NOT NULL default '0000-00-00 00:00:00',
                comment_date_gmt datetime NOT NULL default '0000-00-00 00:00:00',
                comment_content text NOT NULL,
                comment_karma int(11) NOT NULL default '0',
                comment_approved varchar(20) NOT NULL default '1',
                comment_agent varchar(255) NOT NULL default '',
                comment_type varchar(20) NOT NULL default 'comment',
                comment_parent bigint(20) unsigned NOT NULL default '0',
                user_id bigint(20) unsigned NOT NULL default '0',
                PRIMARY KEY  (comment_ID),
                KEY comment_post_ID (comment_post_ID),
                KEY comment_approved_date_gmt (comment_approved,comment_date_gmt),
                KEY comment_date_gmt (comment_date_gmt),
                KEY comment_parent (comment_parent),
                KEY comment_author_email (comment_author_email(10))
            ) $this->tp_charset_collate; 
            CREATE TABLE $this->tpdb->links (
                link_id bigint(20) unsigned NOT NULL auto_increment,
                link_url varchar(255) NOT NULL default '',
                link_name varchar(255) NOT NULL default '',
                link_image varchar(255) NOT NULL default '',
                link_target varchar(25) NOT NULL default '',
                link_description varchar(255) NOT NULL default '',
                link_visible varchar(20) NOT NULL default 'Y',
                link_owner bigint(20) unsigned NOT NULL default '1',
                link_rating int(11) NOT NULL default '0',
                link_updated datetime NOT NULL default '0000-00-00 00:00:00',
                link_rel varchar(255) NOT NULL default '',
                link_notes mediumtext NOT NULL,
                link_rss varchar(255) NOT NULL default '',
                PRIMARY KEY  (link_id),
                KEY link_visible (link_visible)
            )$this->tp_charset_collate; 
            CREATE TABLE $this->tpdb->options (
                option_id bigint(20) unsigned NOT NULL auto_increment,
                option_name varchar(191) NOT NULL default '',
                option_value longtext NOT NULL,
                autoload varchar(20) NOT NULL default 'yes',
                PRIMARY KEY  (option_id),
                UNIQUE KEY option_name (option_name),
                KEY autoload (autoload)
            )$this->tp_charset_collate; 
            CREATE TABLE $this->tpdb->post_meta (
                meta_id bigint(20) unsigned NOT NULL auto_increment,
                post_id bigint(20) unsigned NOT NULL default '0',
                meta_key varchar(255) default NULL,
                meta_value longtext,
                PRIMARY KEY  (meta_id),
                KEY post_id (post_id),
                KEY meta_key (meta_key($max_index_length))
            )$this->tp_charset_collate; 
            CREATE TABLE $this->tpdb->posts (
                ID bigint(20) unsigned NOT NULL auto_increment,
                post_author bigint(20) unsigned NOT NULL default '0',
                post_date datetime NOT NULL default '0000-00-00 00:00:00',
                post_date_gmt datetime NOT NULL default '0000-00-00 00:00:00',
                post_content longtext NOT NULL,
                post_title text NOT NULL,
                post_excerpt text NOT NULL,
                post_status varchar(20) NOT NULL default 'publish',
                comment_status varchar(20) NOT NULL default 'open',
                ping_status varchar(20) NOT NULL default 'open',
                post_password varchar(255) NOT NULL default '',
                post_name varchar(200) NOT NULL default '',
                to_ping text NOT NULL,
                pinged text NOT NULL,
                post_modified datetime NOT NULL default '0000-00-00 00:00:00',
                post_modified_gmt datetime NOT NULL default '0000-00-00 00:00:00',
                post_content_filtered longtext NOT NULL,
                post_parent bigint(20) unsigned NOT NULL default '0',
                guid varchar(255) NOT NULL default '',
                menu_order int(11) NOT NULL default '0',
                post_type varchar(20) NOT NULL default 'post',
                post_mime_type varchar(100) NOT NULL default '',
                comment_count bigint(20) NOT NULL default '0',
                PRIMARY KEY  (ID),
                KEY post_name (post_name($max_index_length)),
                KEY type_status_date (post_type,post_status,post_date,ID),
                KEY post_parent (post_parent),
                KEY post_author (post_author)
            ) $this->tp_charset_collate;\n";//188
            // Single site users table. The multisite flavor of the users table is handled below.
            $users_single_table = TP_CREATE_TABLE . " $this->tpdb->users (
                ID bigint(20) unsigned NOT NULL auto_increment,
                user_login varchar(60) NOT NULL default '',
                user_pass varchar(255) NOT NULL default '',
                user_nicename varchar(50) NOT NULL default '',
                user_email varchar(100) NOT NULL default '',
                user_url varchar(100) NOT NULL default '',
                user_registered datetime NOT NULL default '0000-00-00 00:00:00',
                user_activation_key varchar(255) NOT NULL default '',
                user_status int(11) NOT NULL default '0',
                display_name varchar(250) NOT NULL default '',
                PRIMARY KEY  (ID),
                KEY user_login_key (user_login),
                KEY user_nicename (user_nicename),
                KEY user_email (user_email)
            )$this->tp_charset_collate;\n";
            // Multisite users table.
            $users_multi_table = TP_CREATE_TABLE . " $this->tpdb->users (
                ID bigint(20) unsigned NOT NULL auto_increment,
                user_login varchar(60) NOT NULL default '',
                user_pass varchar(255) NOT NULL default '',
                user_nicename varchar(50) NOT NULL default '',
                user_email varchar(100) NOT NULL default '',
                user_url varchar(100) NOT NULL default '',
                user_registered datetime NOT NULL default '0000-00-00 00:00:00',
                user_activation_key varchar(255) NOT NULL default '',
                user_status int(11) NOT NULL default '0',
                display_name varchar(250) NOT NULL default '',
                spam tinyint(2) NOT NULL default '0',
                deleted tinyint(2) NOT NULL default '0',
                PRIMARY KEY  (ID),
                KEY user_login_key (user_login),
                KEY user_nicename (user_nicename),
                KEY user_email (user_email)
            )$this->tp_charset_collate;\n";
            // Usermeta.
            $user_meta_table = TP_CREATE_TABLE . " $this->tpdb->user_meta (
                umeta_id bigint(20) unsigned NOT NULL auto_increment,
                user_id bigint(20) unsigned NOT NULL default '0',
                meta_key varchar(255) default NULL,
                meta_value longtext,
                PRIMARY KEY  (umeta_id),
                KEY user_id (user_id),
                KEY meta_key (meta_key($max_index_length))
            ) $this->tp_charset_collate;\n";
            if ( $is_multisite ) {
                $global_tables = $users_multi_table . $user_meta_table;
            } else {
                $global_tables = $users_single_table . $user_meta_table;
            }
            // Multisite global tables.
            $ms_global_tables = TP_CREATE_TABLE . " $this->tpdb->blogs (
                blog_id bigint(20) NOT NULL auto_increment,
                site_id bigint(20) NOT NULL default '0',
                domain varchar(200) NOT NULL default '',
                path varchar(100) NOT NULL default '',
                registered datetime NOT NULL default '0000-00-00 00:00:00',
                last_updated datetime NOT NULL default '0000-00-00 00:00:00',
                public tinyint(2) NOT NULL default '1',
                archived tinyint(2) NOT NULL default '0',
                mature tinyint(2) NOT NULL default '0',
                spam tinyint(2) NOT NULL default '0',
                deleted tinyint(2) NOT NULL default '0',
                lang_id int(11) NOT NULL default '0',
                PRIMARY KEY  (blog_id),
                KEY domain (domain(50),path(5)),
                KEY lang_id (lang_id)
            ) $this->tp_charset_collate;
            CREATE TABLE $this->tpdb->blog_meta (
                meta_id bigint(20) unsigned NOT NULL auto_increment,
                blog_id bigint(20) NOT NULL default '0',
                meta_key varchar(255) default NULL,
                meta_value longtext,
                PRIMARY KEY  (meta_id),
                KEY meta_key (meta_key($max_index_length)),
                KEY blog_id (blog_id)
            ) $this->tp_charset_collate;
            CREATE TABLE $this->tpdb->registration_log (
                ID bigint(20) NOT NULL auto_increment,
                email varchar(255) NOT NULL default '',
                IP varchar(30) NOT NULL default '',
                blog_id bigint(20) NOT NULL default '0',
                date_registered datetime NOT NULL default '0000-00-00 00:00:00',
                PRIMARY KEY  (ID),
                KEY IP (IP)
            ) $this->tp_charset_collate;
            CREATE TABLE $this->tpdb->site (
                id bigint(20) NOT NULL auto_increment,
                domain varchar(200) NOT NULL default '',
                path varchar(100) NOT NULL default '',
                PRIMARY KEY  (id),
                KEY domain (domain(140),path(51))
            ) $this->tp_charset_collate;
            CREATE TABLE $this->tpdb->site_meta (
                meta_id bigint(20) NOT NULL auto_increment,
                site_id bigint(20) NOT NULL default '0',
                meta_key varchar(255) default NULL,
                meta_value longtext,
                PRIMARY KEY  (meta_id),
                KEY meta_key (meta_key($max_index_length)),
                KEY site_id (site_id)
            )  $this->tp_charset_collate;
            CREATE TABLE $this->tpdb->signups (
                signup_id bigint(20) NOT NULL auto_increment,
                domain varchar(200) NOT NULL default '',
                path varchar(100) NOT NULL default '',
                title longtext NOT NULL,
                user_login varchar(60) NOT NULL default '',
                user_email varchar(100) NOT NULL default '',
                registered datetime NOT NULL default '0000-00-00 00:00:00',
                activated datetime NOT NULL default '0000-00-00 00:00:00',
                active tinyint(1) NOT NULL default '0',
                activation_key varchar(50) NOT NULL default '',
                meta longtext,
                PRIMARY KEY  (signup_id),
                KEY activation_key (activation_key),
                KEY user_email (user_email),
                KEY user_login_email (user_login,user_email),
                KEY domain_path (domain(140),path(51))
            )  $this->tp_charset_collate;";
            switch ( $scope ) {
                case 'blog':
                    $this->tp_queries = $blog_tables;
                    break;
                case 'global':
                    $this->tp_queries = $global_tables;
                    if ( $is_multisite ) {
                        $this->tp_queries .= $ms_global_tables;
                    }
                    break;
                case 'ms_global':
                    $this->tp_queries = $ms_global_tables;
                    break;
                case 'all':
                default:
                $this->tp_queries = $global_tables . $blog_tables;
                    if ( $is_multisite ) {
                        $this->tp_queries .= $ms_global_tables;
                    }
                    break;
            }
            if ( isset( $old_blog_id ) ) {
                $this->tpdb->set_blog_id( $old_blog_id );
            }
            return $this->tp_queries;
        }//36
        /**
         * @description Create TailoredPress options and set the default values.
         * @param \array[] ...$options
         */
        protected function _populate_options(array ...$options):void{
            $this->__schema_construct();
            $guess_url = $this->_tp_guess_url();
            $this->_do_action( 'populate_options' );
            $stylesheet = TP_DEFAULT_THEME;
            $template   = TP_DEFAULT_THEME;
            $theme      = $this->_tp_get_theme( TP_DEFAULT_THEME );
            if ( ! $theme->exists() ) { $theme = TP_Theme::get_core_default_theme();}
            if ( $theme ) {
                $stylesheet = $theme->get_stylesheet();
                $template   = $theme->get_template();
            }
            $timezone_string = '';
            $gmt_offset      = 0;
            $offset_or_tz = $this->_x( '0', 'default GMT offset or timezone string' );
            if ( is_numeric( $offset_or_tz ) ) {$gmt_offset = $offset_or_tz;}
            elseif ( $offset_or_tz && in_array( $offset_or_tz, timezone_identifiers_list(), true ) ) {
                $timezone_string = $offset_or_tz;}
            $defaults = ['siteurl' => $guess_url,'home' => $guess_url,'blogname' => $this->__( 'My Site' ),
                'blogdescription' => $this->__( 'Just another TailoredPress site' ),'users_can_register' => 0,
                'admin_email' => 'you@example.com','start_of_week' => $this->_x( '1', 'start of week' ),'use_balanceTags' => 0,
                'use_smilies' => 1,'require_name_email' => 1,'comments_notify' => 1,'posts_per_rss' => 10,'rss_use_excerpt' => 0,
                'mailserver_url' => 'mail.example.com','mailserver_login' => 'login@example.com','mailserver_pass' => 'password','mailserver_port' => 110,
                'default_category' => 1,'default_comment_status' => 'open','default_ping_status' => 'open','default_pingback_flag' => 1,'posts_per_page' => 10,
                'date_format' => $this->__( 'F j, Y' ),'time_format' => $this->__( 'g:i a' ),'links_updated_date_format' => $this->__( 'F j, Y g:i a' ),
                'comment_moderation' => 0, 'moderation_notify' => 1,'permalink_structure' => '','rewrite_rules' => '',
                'hack_file' => 0,'blog_charset' => 'UTF-8','moderation_keys'=> '','category_base' => '','ping_sites' => 'http://rpc.pingomatic.com/',
                'comment_max_links' => 2,'gmt_offset' => $gmt_offset, 'default_email_category' => 1,'recently_edited' => '','template' => $template,
                'stylesheet' => $stylesheet,'comment_registration' => 0,'html_type' => 'text/html','use_trackback' => 0,'default_role' => 'subscriber',
                'db_version' => TP_DB_VERSION,'uploads_use_yearmonth_folders' => 1,'upload_path' => '','blog_public' => '1','default_link_category' => 2,
                'show_on_front' => 'posts','tag_base' => '','show_avatars' => '1','avatar_rating' => 'G','upload_url_path' => '','thumbnail_size_w' => 150,
                'thumbnail_size_h' => 150,'thumbnail_crop' => 1,'medium_size_w' => 300,'medium_size_h' => 300,'avatar_default' => 'mystery','large_size_w' => 1024,
                'large_size_h' => 1024,'image_default_link_type' => 'none','image_default_size' => '','image_default_align' => '',
                'close_comments_for_old_posts' => 0,'close_comments_days_old' => 14,'thread_comments' => 1,'thread_comments_depth' => 5,'page_comments' => 0,
                'comments_per_page' => 50,'default_comments_page' => 'newest','comment_order' => 'asc','sticky_posts' => [],'widget_categories' => [],
                'widget_text' => [],'widget_rss' => [],'timezone_string' => $timezone_string,'page_for_posts' => 0,'page_on_front' => 0,'default_post_format'=> 0,
                'link_manager_enabled' => 0,'finished_splitting_shared_terms' => 1,'site_icon' => 0,'medium_large_size_w' => 768,'medium_large_size_h' => 0,
                'tp_page_for_privacy_policy' => 0,'show_comments_cookies_opt_in' => 1,'admin_email_lifespan' => ( time() + 6 * MONTH_IN_SECONDS ),
                'disallowed_keys' => '','comment_previously_approved' => 1,'auto_theme_update_emails' => [],
            ];
            // more if needed, from schema.php  line 444 and up
            $options = $this->_tp_parse_args( $options, $defaults );
            $fat_options = ['moderation_keys','recently_edited','disallowed_keys','auto_theme_update_emails',];
            $keys             = "'" . implode( "', '", array_keys( $options ) ) . "'";
            $existing_options = null;
            if($this->tpdb instanceof TP_Db){
                $existing_options = $this->tpdb->get_col( TP_SELECT . " option_name FROM $this->tpdb->options WHERE option_name in ( $keys )" );
            }
            $insert = '';
            foreach ( $options as $option => $value ) {
                if (in_array($option, $existing_options, true)) { continue;}
                if (in_array($option, $fat_options, true)) { $autoload = 'no';}
                else {$autoload = 'yes';}
                if (is_array($value)) {$value = serialize($value);}
                if (!empty($insert)) {$insert .= ', ';}
                $insert .= $this->tpdb->prepare( '(%s, %s, %s)', $option, $value, $autoload );
            }
            $un_used_options = ['blodotgsping_url','bodyterminator','emailtestonly','phoneemail_separator','smilies_directory',
                'subjectprefix','use_bbcode','use_blodotgsping','use_phoneemail','use_quicktags','use_weblogsping',
                'weblogs_cache_file','use_preview','use_htmltrans','smilies_directory','fileupload_allowedusers',
                'use_phoneemail','default_post_status','default_post_category','archive_mode','time_difference',
                'links_minadminlevel','links_use_adminlevels','links_rating_type','links_rating_char','links_rating_ignore_zero',
                'links_rating_single_image','links_rating_image0','links_rating_image1','links_rating_image2',
                'links_rating_image3','links_rating_image4','links_rating_image5','links_rating_image6','links_rating_image7',
                'links_rating_image8','links_rating_image9','links_recently_updated_time','links_recently_updated_prepend',
                'links_recently_updated_append','weblogs_cacheminutes','comment_allowed_tags','search_engine_friendly_urls',
                'default_geourl_lat','default_geourl_lon','use_default_geourl','weblogs_xml_url','new_users_can_blog',
                '_tpnonce','_tp_http_referer','Update','action','rich_editing','autosave_interval',
                'can_compress_scripts','page_uris','update_core','update_themes','doing_cron','random_seed',
                'rss_excerpt_length','secret','use_linksupdate','default_comment_status_page',
                'wporg_popular_tags','what_to_show','rss_language','language','enable_xmlrpc',
                'enable_app','embed_autourls','default_post_edit_rows','gzipcompression','advanced_edit',];
            foreach ( $un_used_options as $option ) { $this->_delete_option( $option );}
            $this->tpdb->query(TP_DELETE . " FROM $this->tpdb->options WHERE option_name REGEXP '^rss_[0-9a-f]{32}(_ts)?$'" );
            $this->_delete_expired_transients( true );
        }//361
        /**
         * @description Execute TailoredPress role creation for TailoredPress.
         */
        protected function _populate_roles():void{
            $this->__populate_roles_admin();
            $this->__populate_roles_editor();
            $this->__populate_roles_admin_editor();
            $this->__populate_roles_author();
            $this->__populate_roles_contributor();
            $this->__populate_roles_subscriber();
        }//705 t/m 928
        private function __populate_roles_admin():void{
            $this->_add_role( 'administrator', 'Administrator' );
            $role = $this->_get_role( 'administrator' );
            $role->add_cap( 'switch_themes' );
            $role->add_cap( 'edit_themes' );
            $role->add_cap( 'activate_plugins' );
            $role->add_cap( 'edit_plugins' );
            $role->add_cap( 'edit_users' );
            $role->add_cap( 'edit_files' );
            $role->add_cap( 'manage_options' );
            $role->add_cap( 'moderate_comments' );
            $role->add_cap( 'manage_categories' );
            $role->add_cap( 'manage_links' );
            $role->add_cap( 'upload_files' );
            $role->add_cap( 'import' );
            $role->add_cap( 'unfiltered_html' );
            $role->add_cap( 'edit_posts' );
            $role->add_cap( 'edit_others_posts' );
            $role->add_cap( 'edit_published_posts' );
            $role->add_cap( 'publish_posts' );
            $role->add_cap( 'edit_pages' );
            $role->add_cap( 'read' );
            $role->add_cap( 'level_10' );
            $role->add_cap( 'level_9' );
            $role->add_cap( 'level_8' );
            $role->add_cap( 'level_7' );
            $role->add_cap( 'level_6' );
            $role->add_cap( 'level_5' );
            $role->add_cap( 'level_4' );
            $role->add_cap( 'level_3' );
            $role->add_cap( 'level_2' );
            $role->add_cap( 'level_1' );
            $role->add_cap( 'level_0' );
            if ( ! empty( $role ) ) {
                $role->add_cap( 'delete_users' );
                $role->add_cap( 'create_users' );
                $role->add_cap( 'unfiltered_upload' );
                $role->add_cap( 'edit_dashboard' );
                $role->add_cap( 'update_themes' );//todo might not use this?
                $role->add_cap( 'install_themes' );//todo might not use this?
                $role->add_cap( 'edit_theme_options' );//todo might not use this?
                $role->add_cap( 'delete_themes' );//todo might not use this?
                $role->add_cap( 'update_core' );
                $role->add_cap( 'list_users' );
                $role->add_cap( 'remove_users' );
                $role->add_cap( 'promote_users' );
                $role->add_cap( 'export' );
            }
        }//705 t/m 928 populate_roles_160()
        private function __populate_roles_editor():void{
            $this->_add_role( 'editor', 'Editor' );
            $role = $this->_get_role( 'editor' );
            $role->add_cap( 'moderate_comments' );
            $role->add_cap( 'manage_categories' );
            $role->add_cap( 'manage_links' );
            $role->add_cap( 'upload_files' );
            $role->add_cap( 'unfiltered_html' );
            $role->add_cap( 'edit_posts' );
            $role->add_cap( 'edit_others_posts' );
            $role->add_cap( 'edit_published_posts' );
            $role->add_cap( 'publish_posts' );
            $role->add_cap( 'edit_pages' );
            $role->add_cap( 'read' );
            $role->add_cap( 'level_7' );
            $role->add_cap( 'level_6' );
            $role->add_cap( 'level_5' );
            $role->add_cap( 'level_4' );
            $role->add_cap( 'level_3' );
            $role->add_cap( 'level_2' );
            $role->add_cap( 'level_1' );
            $role->add_cap( 'level_0' );
        }//705 t/m 928 populate_roles_160()
        private function __populate_roles_admin_editor():void{
            $roles = ['administrator', 'editor'];
            foreach ( $roles as $role ) {
                $role = $this->_get_role( $role );
                if(empty($role)){continue;}
                $role->add_cap( 'edit_others_pages' );
                $role->add_cap( 'edit_published_pages' );
                $role->add_cap( 'publish_pages' );
                $role->add_cap( 'delete_pages' );
                $role->add_cap( 'delete_others_pages' );
                $role->add_cap( 'delete_published_pages' );
                $role->add_cap( 'delete_posts' );
                $role->add_cap( 'delete_others_posts' );
                $role->add_cap( 'delete_published_posts' );
                $role->add_cap( 'delete_private_posts' );
                $role->add_cap( 'edit_private_posts' );
                $role->add_cap( 'read_private_posts' );
                $role->add_cap( 'delete_private_pages' );
                $role->add_cap( 'edit_private_pages' );
                $role->add_cap( 'read_private_pages' );
            }
        }
        private function __populate_roles_author():void{
            $this->_add_role( 'author', 'Author' );
            $role = $this->_get_role( 'author' );
            $role->add_cap( 'upload_files' );
            $role->add_cap( 'edit_posts' );
            $role->add_cap( 'edit_published_posts' );
            $role->add_cap( 'publish_posts' );
            $role->add_cap( 'read' );
            $role->add_cap( 'level_2' );
            $role->add_cap( 'level_1' );
            $role->add_cap( 'level_0' );
            if ( ! empty( $role ) ) {
                $role->add_cap( 'delete_posts' );
                $role->add_cap( 'delete_published_posts' );
            }
        }//705 t/m 928 populate_roles_160()
        private function __populate_roles_contributor():void{
            $this->_add_role( 'contributor', 'Contributor' );
            $role = $this->_get_role( 'contributor' );
            $role->add_cap( 'edit_posts' );
            $role->add_cap( 'read' );
            $role->add_cap( 'level_1' );
            $role->add_cap( 'level_0' );
            if ( ! empty( $role ) ) {
                $role->add_cap( 'delete_posts' );
            }
        }//705 t/m 928 populate_roles_160()
        private function __populate_roles_subscriber():void{
            $this->_add_role( 'subscriber', 'Subscriber' );
            $role = $this->_get_role( 'subscriber' );
            $role->add_cap( 'read' );
            $role->add_cap( 'level_0' );
        }//705 t/m 928 populate_roles_160()
    }
}else{die;}