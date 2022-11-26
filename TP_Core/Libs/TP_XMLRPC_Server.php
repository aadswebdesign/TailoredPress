<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-5-2022
 * Time: 06:57
 */
namespace TP_Core\Libs;
use TP_Admin\Traits\AdminImage\_adm_image_01;
use TP_Admin\Traits\AdminMultiSite\_adm_multisite_01;
use TP_Admin\Traits\_adm_category;
use TP_Admin\Traits\AdminPost\_adm_post_01;
use TP_Admin\Traits\AdminPost\_adm_post_02;
use TP_Admin\Traits\AdminTheme\_adm_theme_01;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Capabilities\_capability_02;
use TP_Core\Traits\Categories\_category_01;
use TP_Core\Traits\Comment\_comment_01;
use TP_Core\Traits\Comment\_comment_04;
use TP_Core\Traits\Comment\_comment_05;
use TP_Core\Traits\Comment\_comment_03;
use TP_Core\Traits\Inits\_init_ixr;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_format_post_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Formats\_formats_06;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Formats\_formats_11;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Methods\_methods_02;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_07;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Methods\_methods_17;
use TP_Core\Traits\HTTP\_http_01;
use TP_Core\Traits\HTTP\_http_02;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Media\_media_01;
use TP_Core\Traits\Meta\_meta_01;
use TP_Core\Traits\Multisite\Blog\_ms_blog_02;
use TP_Core\Traits\Multisite\Blog\_ms_blog_03;
use TP_Core\Traits\Multisite\Methods\_ms_methods_01;
use TP_Core\Traits\Multisite\Methods\_ms_methods_06;
use TP_Core\Traits\Multisite\Site\_ms_site_01;
use TP_Core\Traits\Multisite\Site\_ms_site_03;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_02;
use TP_Core\Traits\Post\_post_03;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\Post\_post_05;
use TP_Core\Traits\Post\_post_07;
use TP_Core\Traits\Post\_post_08;
use TP_Core\Traits\Post\_post_10;
use TP_Core\Traits\Post\_post_06;
use TP_Core\Traits\Post\_post_13;
use TP_Core\Traits\Revisions\_revision_01;
use TP_Core\Traits\Revisions\_revision_02;
use TP_Core\Traits\Misc\_rewrite;
use TP_Core\Traits\Taxonomy\_taxonomy_01;
use TP_Core\Traits\Taxonomy\_taxonomy_02;
use TP_Core\Traits\Taxonomy\_taxonomy_03;
use TP_Core\Traits\Taxonomy\_taxonomy_04;
use TP_Core\Traits\Taxonomy\_taxonomy_05;
use TP_Core\Traits\Taxonomy\_taxonomy_08;
use TP_Core\Traits\Templates\_category_template_01;
use TP_Core\Traits\Templates\_category_template_02;
use TP_Core\Traits\Templates\_comment_template_03;
use TP_Core\Traits\Templates\_comment_template_04;
use TP_Core\Traits\Templates\_general_template_02;
use TP_Core\Traits\Templates\_link_template_01;
use TP_Core\Traits\Templates\_link_template_02;
use TP_Core\Traits\Templates\_link_template_03;
use TP_Core\Traits\Templates\_link_template_09;
use TP_Core\Traits\Templates\_link_template_10;
use TP_Core\Traits\Templates\_post_template_01;
use TP_Core\Traits\Templates\_post_template_02;
use TP_Core\Traits\Templates\_post_template_04;
use TP_Core\Traits\Templates\_post_thumbnail_template;
use TP_Core\Traits\Theme\_theme_07;
use TP_Core\Traits\User\_user_02;
use TP_Core\Traits\User\_user_04;
use TP_Core\Libs\IXR\IXR_Server;
use TP_Core\Libs\IXR\IXR_Error;
use TP_Core\Libs\IXR\IXR_Date;
use TP_Core\Libs\IXR\IXR_Client;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\Post\TP_Post_Type;
if(ABSPATH){
    class TP_XMLRPC_Server extends IXR_Server {
        use _action_01, _filter_01, _I10n_01, _option_01;
        use _formats_02, _formats_06, _formats_07, _formats_08;
        use _formats_11, _format_post_01, _methods_01, _load_04;
        use _methods_02, _methods_04, _methods_07, _methods_10, _methods_12;
        use _methods_17, _post_01, _post_02, _post_03, _post_04;
        use _post_05, _post_06, _post_07, _post_08, _post_10;
        use _post_13, _adm_post_01, _adm_post_02, _adm_theme_01;
        use _adm_multisite_01, _media_01, _category_01, _capability_01;
        use _capability_02, _pluggable_01, _init_queries, _init_error;
        use _meta_01, _ms_blog_02, _ms_blog_03, _ms_site_01,_init_ixr;
        use _ms_site_03, _ms_methods_01, _ms_methods_06; //get_space_allowed will be moved
        use _taxonomy_01, _taxonomy_02, _taxonomy_03, _taxonomy_04;
        use _taxonomy_05, _taxonomy_08, _category_template_01, _comment_template_03;
        use _comment_template_04, _general_template_02, _link_template_01;
        use _link_template_02, _link_template_03, _link_template_09, _link_template_10;
        use _category_template_02, _adm_category, _post_template_01, _post_template_02;
        use _post_template_04, _post_thumbnail_template, _comment_01, _comment_03, _comment_04;
        use _comment_05, _theme_07, _user_02, _user_04, _rewrite;
        use _revision_01, _revision_02, _http_01, _http_02, _adm_image_01;
        protected $_auth_failed = false;
        private $__is_enabled;
        public $methods;
        public $blog_options;
        public $error;
        public function __construct(){
            parent::__construct();
            $this->methods = [
                // TailoredPress API.
                'tp.getUsersBlogs' => 'this:tp_getUsersBlogs','tp.newPost' => 'this:tp_newPost','tp.editPost' => 'this:tp_editPost',
                'tp.deletePost' => 'this:tp_deletePost','tp.getPost' => 'this:tp_getPost','tp.getPosts' => 'this:tp_getPosts',
                'tp.newTerm' => 'this:tp_newTerm','tp.editTerm' => 'this:tp_editTerm','tp.deleteTerm' => 'this:tp_deleteTerm',
                'tp.getTerm' => 'this:tp_getTerm','tp.getTerms' => 'this:tp_getTerms','tp.getTaxonomy' => 'this:tp_getTaxonomy',
                'tp.getTaxonomies' => 'this:tp_getTaxonomies','tp.getUser' => 'this:tp_getUser','tp.getUsers' => 'this:tp_getUsers',
                'tp.getProfile' => 'this:tp_getProfile','tp.editProfile' => 'this:tp_editProfile','tp.getPage' => 'this:tp_getPage',
                'tp.getPages' => 'this:tp_getPages','tp.newPage' => 'this:tp_newPage','tp.deletePage' => 'this:tp_deletePage',
                'tp.editPage' => 'this:tp_editPage','tp.getPageList' => 'this:tp_getPageList','tp.getAuthors' => 'this:tp_getAuthors',
                'tp.getCategories' => 'this:mw_getCategories','tp.getTags' => 'this:tp_getTags','tp.newCategory' => 'this:tp_newCategory',
                'tp.deleteCategory' => 'this:tp_deleteCategory','tp.suggestCategories' => 'this:tp_suggestCategories','tp.uploadFile' => 'this:mw_newMediaObject',
                'tp.deleteFile' => 'this:tp_deletePost','tp.getCommentCount' => 'this:tp_getCommentCount','tp.getPostStatusList' => 'this:tp_getPostStatusList',
                'tp.getPageStatusList' => 'this:tp_getPageStatusList','tp.getPageTemplates' => 'this:tp_getPageTemplates','tp.getOptions' => 'this:tp_getOptions',
                'tp.setOptions' => 'this:tp_setOptions','tp.getComment' => 'this:tp_getComment','tp.getComments' => 'this:tp_getComments',
                'tp.deleteComment' => 'this:tp_deleteComment','tp.editComment' => 'this:tp_editComment','tp.newComment' => 'this:tp_newComment',
                'tp.getCommentStatusList' => 'this:tp_getCommentStatusList','tp.getMediaItem' => 'this:tp_getMediaItem','tp.getMediaLibrary' => 'this:tp_getMediaLibrary',
                'tp.getPostFormats' => 'this:tp_getPostFormats','tp.getPostType' => 'this:tp_getPostType','tp.getPostTypes' => 'this:tp_getPostTypes',
                'tp.getRevisions' => 'this:tp_getRevisions','tp.restoreRevision' => 'this:tp_restoreRevision',
                // Blogger API.
                'blogger.getUsersBlogs' => 'this:blogger_getUsersBlogs','blogger.getUserInfo' => 'this:blogger_getUserInfo','blogger.getPost' => 'this:blogger_getPost',
                'blogger.getRecentPosts' => 'this:blogger_getRecentPosts','blogger.newPost' => 'this:blogger_newPost','blogger.editPost' => 'this:blogger_editPost',
                'blogger.deletePost' => 'this:blogger_deletePost',
                // MetaWeblog API (with MT extensions to structures).
                'metaWeblog.newPost' => 'this:mw_newPost','metaWeblog.editPost' => 'this:mw_editPost','metaWeblog.getPost' => 'this:mw_getPost',
                'metaWeblog.getRecentPosts' => 'this:mw_getRecentPosts','metaWeblog.getCategories' => 'this:mw_getCategories','metaWeblog.newMediaObject' => 'this:mw_newMediaObject',
                // MetaWeblog API aliases for Blogger API. // See http://www.xmlrpc.com/stories/storyReader$2460
                'metaWeblog.deletePost' => 'this:blogger_deletePost','metaWeblog.getUsersBlogs' => 'this:blogger_getUsersBlogs',
                // MovableType API.
                'mt.getCategoryList' => 'this:mt_getCategoryList','mt.getRecentPostTitles' => 'this:mt_getRecentPostTitles','mt.getPostCategories' => 'this:mt_getPostCategories',
                'mt.setPostCategories' => 'this:mt_setPostCategories','mt.supportedMethods' => 'this:mt_supportedMethods','mt.supportedTextFilters' => 'this:mt_supportedTextFilters',
                'mt.getTrackbackPings' => 'this:mt_getTrackbackPings','mt.publishPost' => 'this:mt_publishPost',
                // Pingback.
                'pingback.ping' => 'this:pingback_ping','pingback.extensions.getPingbacks' => 'this:pingback_extensions_getPingbacks',
                //Demo
                'demo.sayHello' => 'this:sayHello','demo.addTwoNumbers' => 'this:addTwoNumbers',
            ];
            $this->initialise_blog_option_info();
            $this->methods = $this->_apply_filters( 'xmlrpc_methods', $this->methods );
            $this->__set_is_enabled();
        }//68
        private function __set_is_enabled(): void{
            $is_enabled = $this->_apply_filters( 'pre_option_enable_xmlrpc', false );
            if ( false === $is_enabled )
                $is_enabled = $this->_apply_filters( 'option_enable_xmlrpc', true );
            $this->__is_enabled = $this->_apply_filters( 'xmlrpc_enabled', $is_enabled );
        }//186
        public function serve_request(): void{
            $this->_init_ixr_server($this->methods);
        }//242
        public function sayHello():string{
            return 'Hello!';
        }//253
        public function addTwoNumbers(int $args){
            $number1 = $args[0];
            /** @noinspection MultiAssignmentUsageInspection */
            $number2 = $args[1];
            return $number1 + $number2;
        }//270
        public function login( $username, $password ){
            if ( ! $this->__is_enabled ) {
                $this->error = new IXR_Error( METHOD_NOT_ALLOWED, sprintf( $this->__( 'XML-RPC services are disabled on this site.' ) ) );
                return false;
            }
            if ( $this->_auth_failed ) $user = new TP_Error( 'login_prevented' );
            else $user = $this->_tp_authenticate( $username, $password );
            if ( $this->_init_error( $user ) ) {
                $this->error = new IXR_Error( FORBIDDEN, $this->__( 'Incorrect username or password.' ) );
                $this->_auth_failed = true;
                $this->error = $this->_apply_filters( 'xmlrpc_login_error', $this->error, $user );
                return false;
            }
            $this->_tp_set_current_user( $user->ID );
            return $user;
        }//285
        public function login_pass_ok( $username, $password ): bool{
            return (bool) $this->login( $username, $password );
        }//330
        public function escape( &$data ){
            if ( ! is_array( $data ) ) return $this->_tp_slash( $data );
            foreach ( $data as &$v ) {
                if ( is_array( $v ) )  $this->escape( $v );
                elseif ( ! is_object( $v ) ) $v = $this->_tp_slash( $v );
            }
            return true;
        }//343
        public function ixr_error( $error, $message = false ): void{
            if ( $message && ! is_object( $error ) )
                $error = new IXR_Error( $error, $message );
            if ( ! $this->__is_enabled )
                $this->_status_header( $error->code );
            $this->output( $error->getXml() );
        }//368
        public function get_custom_fields( $post_id ): array{
            $post_id = (int) $post_id;
            $custom_fields = [];
            foreach ( (array) $this->_has_meta( $post_id ) as $meta ) {
                if ( ! $this->_current_user_can( 'edit_post_meta', $post_id, $meta['meta_key'] ) )
                    continue;
                $custom_fields[] = ['id' => $meta['meta_id'],'key' => $meta['meta_key'],'value' => $meta['meta_value'],];
            }
            return $custom_fields;
        }//389
        public function set_custom_fields( $post_id, $fields ): void{
            $post_id = (int) $post_id;
            foreach ( (array) $fields as $meta ) {
                if ( isset( $meta['id'] ) ) {
                    $meta['id'] = (int) $meta['id'];
                    $pmeta      = $this->_get_metadata_by_mid( 'post', $meta['id'] );
                    if (! $pmeta || $pmeta->post_id !== $post_id ) continue;
                    if ( isset( $meta['key'] ) ) {
                        $meta['key'] = $this->_tp_unslash( $meta['key'] );
                        if ( $meta['key'] !== $pmeta->meta_key ) continue;
                        $meta['value'] = $this->_tp_unslash( $meta['value'] );
                        if ( $this->_current_user_can( 'edit_post_meta', $post_id, $meta['key'] ) )
                            $this->_update_metadata_by_mid( 'post', $meta['id'], $meta['value'] );
                    } elseif ( $this->_current_user_can( 'delete_post_meta', $post_id, $pmeta->meta_key ) )
                        $this->_delete_metadata_by_mid( 'post', $meta['id'] );
                } elseif ( $this->_current_user_can( 'add_post_meta', $post_id, $this->_tp_unslash( $meta['key'] ) ) )
                    $this->_add_post_meta( $post_id, $meta['key'], $meta['value'] );
            }
        }//418
        public function get_term_custom_fields( $term_id ): array{
            $term_id = (int) $term_id;
            $custom_fields = [];
            foreach ( (array) $this->_has_term_meta( $term_id ) as $meta ) {
                if ( ! $this->_current_user_can( 'edit_term_meta', $term_id ) ) continue;
                $custom_fields[] = ['id' => $meta['meta_id'],'key' => $meta['meta_key'],'value' => $meta['meta_value'],];
            }
            return $custom_fields;
        }//456
        public function set_term_custom_fields( $term_id, $fields ): void{
            $term_id = (int) $term_id;
            foreach ( (array) $fields as $meta ) {
                if ( isset( $meta['id'] ) ) {
                    $meta['id'] = (int) $meta['id'];
                    $pmeta      = $this->_get_metadata_by_mid( 'term', $meta['id'] );
                    if ( isset( $meta['key'] ) ) {
                        $meta['key'] = $this->_tp_unslash( $meta['key'] );
                        if ( $meta['key'] !== $pmeta->meta_key ) continue;
                        $meta['value'] = $this->_tp_unslash( $meta['value'] );
                        if ( $this->_current_user_can( 'edit_term_meta', $term_id ) )
                            $this->_update_metadata_by_mid( 'term', $meta['id'], $meta['value'] );
                    } elseif ( $this->_current_user_can( 'delete_term_meta', $term_id ) )
                        $this->_delete_metadata_by_mid( 'term', $meta['id'] );
                } elseif ( $this->_current_user_can( 'add_term_meta', $term_id ) )
                    $this->_add_term_meta( $term_id, $meta['key'], $meta['value'] );
            }
        }//485
        public function initialise_blog_option_info(): void{
            $this->blog_options = [
                // Read-only options.
                'software_name' => ['desc' => $this->__( 'Software Name' ),'readonly' => true,'value' => 'TailoredPress',],
                'software_version' => ['desc' => $this->__( 'Software Version' ),'readonly' => true,'value' => $this->_get_bloginfo( 'version' ),],
                'blog_url' => ['desc' => $this->__( 'TailoredPress Address (URL)' ),'readonly' => true,'option' => 'siteurl',],
                'home_url' => ['desc' => $this->__( 'Site Address (URL)' ),'readonly' => true,'option' => 'home',],
                'login_url' => ['desc' => $this->__( 'Login Address (URL)' ),'readonly' => true,'value' => $this->_tp_login_url(),],
                'admin_url' => ['desc' => $this->__( 'The URL to the admin area' ),'readonly' => true,'value' => $this->_get_admin_url(),],
                'image_default_link_type' => ['desc' => $this->__( 'Image default link type' ),'readonly' => true,'option' => 'image_default_link_type',],
                'image_default_size' => ['desc' => $this->__( 'Image default size' ),'readonly' => true,'option' => 'image_default_size',],
                'image_default_align' => ['desc' => $this->__( 'Image default align' ),'readonly' => true,'option' => 'image_default_align',],
                'template' => ['desc' => $this->__( 'Template' ),'readonly' => true,'option' => 'template',],
                'stylesheet' => ['desc' => $this->__( 'Stylesheet' ),'readonly' => true,'option' => 'stylesheet',],
                'post_thumbnail' => ['desc' => $this->__( 'Post Thumbnail' ), 'readonly' => true,'value' => $this->_current_theme_supports( 'post-thumbnails' ),],
                // Updatable options.
                'time_zone' => ['desc' => $this->__( 'Time Zone' ),'readonly' => false,'option' => 'gmt_offset',],
                'blog_title' => ['desc' => $this->__( 'Site Title' ),'readonly' => false,'option' => 'blogname',],
                'blog_tagline' => ['desc' => $this->__( 'Site Tagline' ),'readonly' => false,'option' => 'blogdescription',],
                'date_format' => ['desc' => $this->__( 'Date Format' ),'readonly' => false,'option' => 'date_format',],
                'time_format'=> ['desc'=> $this->__( 'Time Format' ),'readonly' => false,'option'=> 'time_format',],
                'users_can_register' => ['desc' => $this->__( 'Allow new users to sign up' ),'readonly' => false,'option' => 'users_can_register',],
                'thumbnail_size_w'=> ['desc' => $this->__( 'Thumbnail Width' ),'readonly' => false,'option' => 'thumbnail_size_w',],
                'thumbnail_size_h' => ['desc' => $this->__( 'Thumbnail Height' ),'readonly' => false,'option' => 'thumbnail_size_h',],
                'thumbnail_crop' => ['desc'=> $this->__( 'Crop thumbnail to exact dimensions' ),'readonly' => false,'option'=> 'thumbnail_crop',],
                'medium_size_w' => ['desc' => $this->__( 'Medium size image width' ),'readonly' => false,'option' => 'medium_size_w',],
                'medium_size_h'=> ['desc'=> $this->__( 'Medium size image height' ),'readonly' => false,'option'=> 'medium_size_h',],
                'medium_large_size_w' => ['desc' => $this->__( 'Medium-Large size image width' ),'readonly' => false,'option' => 'medium_large_size_w',],
                'medium_large_size_h' => ['desc' => $this->__( 'Medium-Large size image height' ),'readonly' => false,'option' => 'medium_large_size_h',],
                'large_size_w' => ['desc' => $this->__( 'Large size image width' ),'readonly' => false,'option' => 'large_size_w',],
                'large_size_h'=> ['desc'=> $this->__( 'Large size image height' ),'readonly' => false,'option'=> 'large_size_h',],
                'default_comment_status' => ['desc' => $this->__( 'Allow people to submit comments on new posts.' ),'readonly' => false,'option' => 'default_comment_status',],
                'default_ping_status' => ['desc' => $this->__( 'Allow link notifications from other blogs (pingbacks and trackbacks) on new posts.' ),'readonly' => false,'option' => 'default_ping_status', ],
            ];
            $this->blog_options = $this->_apply_filters( 'xmlrpc_blog_options', $this->blog_options );
        }//517
        public function tp_getUsersBlogs(array ...$args): array{
            if ( ! $this->_minimum_args( $args, 2 ) ) return $this->error;
            if ( ! $this->_is_multisite() ) {
                array_unshift( $args, 1 );
                return $this->blogger_getUsersBlogs( $args );
            }
            $this->escape( $args );
            $username = $args[0];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[1];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getUsersBlogs', $args, $this );
            $blogs           = $this->_get_blogs_of_user( $user->ID );
            $struct          = array();
            $primary_blog_id = 0;
            $active_blog     = $this->_get_active_blog_for_user( $user->ID );
            if ( $active_blog ) {
                /** @noinspection PhpUndefinedFieldInspection *///todo
                $primary_blog_id = (int) $active_blog->blog_id;
            }
            foreach ( $blogs as $blog ) {
                if ( $this->_get_current_network_id() !== $blog->site_id )
                    continue;
                $blog_id = $blog->userblog_id;
                $this->_switch_to_blog( $blog_id );
                $is_admin   = $this->_current_user_can( 'manage_options' );
                $is_primary = ( (int) $blog_id === $primary_blog_id );
                $struct[] = array(
                    'isAdmin'   => $is_admin,
                    'isPrimary' => $is_primary,
                    'url'       => $this->_home_url( '/' ),
                    'blogid'    => (string) $blog_id,
                    'blogName'  => $this->_get_option( 'blogname' ),
                    'xmlrpc'    => $this->_site_url( 'xmlrpc.php', 'rpc' ),//todo
                );
                $this->_restore_current_blog();
            }
            return $struct;

        }//698
        protected function _minimum_args( $args, $count ): bool{
            if ( ! is_array( $args ) || count( $args ) < $count ) {
                $this->error = new IXR_Error( BAD_REQUEST, $this->__( 'Insufficient arguments passed to this XML-RPC method.' ) );
                return false;
            }
            return true;
        }//780
        protected function _prepare_taxonomy( $taxonomy, $fields ){
            $_taxonomy = [
                'name' => $taxonomy->name,'label' => $taxonomy->label,'hierarchical' => (bool) $taxonomy->hierarchical,
                'public' => (bool) $taxonomy->public,'show_ui' => (bool) $taxonomy->show_ui,'_builtin' => (bool) $taxonomy->_builtin,];
            if ( in_array( 'labels', $fields, true ) )
                $_taxonomy['labels'] = (array) $taxonomy->labels;
            if ( in_array( 'cap', $fields, true ) )
                $_taxonomy['cap'] = (array) $taxonomy->cap;
            if ( in_array( 'menu', $fields, true ) )
                $_taxonomy['show_in_menu'] = (bool) $taxonomy->show_in_menu;
            if ( in_array( 'object_type', $fields, true ) )
                $_taxonomy['object_type'] = array_unique( (array) $taxonomy->object_type );
            return $this->_apply_filters( 'xmlrpc_prepare_taxonomy', $_taxonomy, $taxonomy, $fields );
        }//796
        protected function _prepare_term( $term ){
            $_term = $term;
            if ( ! is_array( $_term ) ) $_term = get_object_vars( $_term );
            $_term['term_id'] = (string) $_term['term_id'];
            $_term['term_group']  = (string) $_term['term_group'];
            $_term['term_taxonomy_id'] = (string) $_term['term_taxonomy_id'];
            $_term['parent'] = (string) $_term['parent'];
            $_term['count'] = (int) $_term['count'];
            $_term['custom_fields'] = $this->get_term_custom_fields( $_term['term_id'] );
            return $this->_apply_filters( 'xmlrpc_prepare_term', $_term, $term );
        }//840
        protected function _convert_date( $date ): IXR_Date{
            if ( '0000-00-00 00:00:00' === $date )
                return new IXR_Date( '00000000T00:00:00Z' );
            return new IXR_Date( $this->_mysql2date( 'Ymd\TH:i:s', $date, false ) );
        }//875
        protected function _convert_date_gmt( $date_gmt, $date ){
            if ( '0000-00-00 00:00:00' !== $date && '0000-00-00 00:00:00' === $date_gmt )
                return new IXR_Date( $this->_get_gmt_from_date( $this->_mysql2date( 'Y-m-d H:i:s', $date, false ), 'Ymd\TH:i:s' ) );
            return $this->_convert_date( $date_gmt );
        }//889
        protected function _prepare_post( $post, $fields ){
            $_post = ['post_id' => (string) $post['ID']];
            $post_fields = [
                'post_title'        => $post['post_title'],
                'post_date'         => $this->_convert_date( $post['post_date'] ),
                'post_date_gmt'     => $this->_convert_date_gmt( $post['post_date_gmt'], $post['post_date'] ),
                'post_modified'     => $this->_convert_date( $post['post_modified'] ),
                'post_modified_gmt' => $this->_convert_date_gmt( $post['post_modified_gmt'], $post['post_modified'] ),
                'post_status'       => $post['post_status'],
                'post_type'         => $post['post_type'],
                'post_name'         => $post['post_name'],
                'post_author'       => $post['post_author'],
                'post_password'     => $post['post_password'],
                'post_excerpt'      => $post['post_excerpt'],
                'post_content'      => $post['post_content'],
                'post_parent'       => (string) $post['post_parent'],
                'post_mime_type'    => $post['post_mime_type'],
                'link'              => $this->_get_permalink( $post['ID'] ),
                'guid'              => $post['guid'],
                'menu_order'        => (int) $post['menu_order'],
                'comment_status'    => $post['comment_status'],
                'ping_status'       => $post['ping_status'],
                'sticky'            => ( 'post' === $post['post_type'] && $this->_is_sticky( $post['ID'] ) ),
            ];
            $post_fields['post_thumbnail'] = [];
            $thumbnail_id                  = $this->_get_post_thumbnail_id( $post['ID'] );
            if ( $thumbnail_id ) {
                $thumbnail_size                = $this->_current_theme_supports( 'post-thumbnail' ) ? 'post-thumbnail' : 'thumbnail';
                $post_fields['post_thumbnail'] = $this->_prepare_media_item( $this->_get_post( $thumbnail_id ), $thumbnail_size );
            }
            if ( 'future' === $post_fields['post_status'] ) $post_fields['post_status'] = 'publish';
            $post_fields['post_format'] = $this->_get_post_format( $post['ID'] );
            if ( empty( $post_fields['post_format'] ) ) $post_fields['post_format'] = 'standard';
            if ( in_array( 'post', $fields, true ) ) $_post = array_merge( $_post, $post_fields );
            else {
                $requested_fields = array_intersect_key( $post_fields, array_flip( $fields ) );
                $_post            = array_merge( $_post, $requested_fields );
            }
            $all_taxonomy_fields = in_array( 'taxonomies', $fields, true );
            if ( $all_taxonomy_fields || in_array( 'terms', $fields, true ) ) {
                $post_type_taxonomies = $this->_get_object_taxonomies( $post['post_type'], 'names' );
                $terms                = $this->_tp_get_object_terms( $post['ID'], $post_type_taxonomies );
                $_post['terms']       = [];
                foreach ((array) $terms as $term )  $_post['terms'][] = $this->_prepare_term( $term );
            }
            if ( in_array( 'custom_fields', $fields, true ) )
                $_post['custom_fields'] = $this->get_custom_fields( $post['ID'] );
            if ( in_array( 'enclosure', $fields, true ) ) {
                $_post['enclosure'] = array();
                $enclosures         = (array) $this->_get_post_meta( $post['ID'], 'enclosure' );
                if ( ! empty( $enclosures ) ) {
                    $encdata                      = explode( "\n", $enclosures[0] );
                    $_post['enclosure']['url']    = trim( htmlspecialchars( $encdata[0] ) );
                    $_post['enclosure']['length'] = (int) trim( $encdata[1] );
                    $_post['enclosure']['type']   = trim( $encdata[2] );
                }
            }
            return $this->_apply_filters( 'xmlrpc_prepare_post', $_post, $post, $fields );
        }//903
        protected function _prepare_post_type( $post_type, $fields ){
            $_post_type = ['name' => $post_type->name,'label' => $post_type->label,
                'hierarchical' => (bool) $post_type->hierarchical,'public' => (bool) $post_type->public,
                'show_ui' => (bool) $post_type->show_ui,'_builtin' => (bool) $post_type->_builtin,
                'has_archive' => (bool) $post_type->has_archive,'supports' => $this->_get_all_post_type_supports( $post_type->name ),];
            if ( in_array( 'labels', $fields, true ) ) $_post_type['labels'] = (array) $post_type->labels;
            if ( in_array( 'cap', $fields, true ) ) {
                $_post_type['cap']          = (array) $post_type->cap;
                $_post_type['map_meta_cap'] = (bool) $post_type->map_meta_cap;
            }
            if ( in_array( 'menu', $fields, true ) ) {
                $_post_type['menu_position'] = (int) $post_type->menu_position;
                $_post_type['menu_icon']     = $post_type->menu_icon;
                $_post_type['show_in_menu']  = (bool) $post_type->show_in_menu;
            }
            if ( in_array( 'taxonomies', $fields, true ) )
                $_post_type['taxonomies'] = $this->_get_object_taxonomies( $post_type->name, 'names' );
            return $this->_apply_filters( 'xmlrpc_prepare_post_type', $_post_type, $post_type );
        }//1006
        protected function _prepare_media_item( $media_item, $thumbnail_size = 'thumbnail' ){
            $_media_item = ['attachment_id' => (string) $media_item->ID,
                'date_created_gmt' => $this->_convert_date_gmt( $media_item->post_date_gmt, $media_item->post_date ),
                'parent' => $media_item->post_parent,'link' => $this->_tp_get_attachment_url( $media_item->ID ),
                'title' => $media_item->post_title,'caption' => $media_item->post_excerpt,
                'description' => $media_item->post_content,'metadata' => $this->_tp_get_attachment_metadata( $media_item->ID ),
                'type' => $media_item->post_mime_type,];
            $thumbnail_src = $this->_image_downsize( $media_item->ID, $thumbnail_size );
            if ( $thumbnail_src ) $_media_item['thumbnail'] = $thumbnail_src[0];
            else $_media_item['thumbnail'] = $_media_item['link'];
            return $this->_apply_filters( 'xmlrpc_prepare_media_item', $_media_item, $media_item, $thumbnail_size );
        }//1056
        protected function _prepare_page( $page ){
            $full_page = $this->_get_extended( $page->post_content );
            $link      = $this->_get_permalink( $page->ID );
            $parent_title = '';
            if ( ! empty( $page->post_parent ) ) {
                $parent       = $this->_get_post( $page->post_parent );
                $parent_title = $parent->post_title;
            }
            $allow_comments = $this->_comments_open( $page->ID ) ? 1 : 0;
            $allow_pings    = $this->_pings_open( $page->ID ) ? 1 : 0;
            $page_date     = $this->_convert_date( $page->post_date );
            $page_date_gmt = $this->_convert_date_gmt( $page->post_date_gmt, $page->post_date );
            $categories = [];
            if ( $this->_is_object_in_taxonomy( 'page', 'category' ) ) {
                foreach ( $this->_tp_get_post_categories( $page->ID ) as $cat_id )
                    $categories[] = $this->_get_cat_name( $cat_id );
            }
            $author = $this->_get_user_data( $page->post_author );
            $page_template = $this->_get_page_template_slug( $page->ID );
            if ( empty( $page_template ) )  $page_template = 'default';
            $_page = ['dateCreated' => $page_date,'userid' => $page->post_author,'page_id' => $page->ID,
                'page_status' => $page->post_status,'description' => $full_page['main'],'title' => $page->post_title,
                'link' => $link,'permaLink' => $link,'categories' => $categories,'excerpt' => $page->post_excerpt,
                'text_more' => $full_page['extended'],'mt_allow_comments' => $allow_comments,'mt_allow_pings' => $allow_pings,
                'tp_slug' => $page->post_name,'tp_password' => $page->post_password,'tp_author' => $author->display_name,
                'tp_page_parent_id' => $page->post_parent,'tp_page_parent_title' => $parent_title,'tp_page_order' => $page->menu_order,
                'tp_author_id' => (string) $author->ID,'tp_author_display_name' => $author->display_name,'date_created_gmt' => $page_date_gmt,
                'custom_fields' => $this->get_custom_fields( $page->ID ),'tp_page_template' => $page_template,
            ];
            return $this->_apply_filters( 'xmlrpc_prepare_page', $_page, $page );
        }//1094
        protected function _prepare_comment( $comment ){
            $comment_date_gmt = $this->_convert_date_gmt( $comment->comment_date_gmt, $comment->comment_date );
            if ( '0' === $comment->comment_approved ) $comment_status = 'hold';
            elseif ( 'spam' === $comment->comment_approved ) $comment_status = 'spam';
            elseif ( '1' === $comment->comment_approved ) $comment_status = 'approve';
            else $comment_status = $comment->comment_approved;
            $_comment = ['date_created_gmt' => $comment_date_gmt,'user_id' => $comment->user_id,'comment_id' => $comment->comment_ID,
                'parent' => $comment->comment_parent,'status' => $comment_status,'content' => $comment->comment_content,
                'link' => $this->_get_comment_link( $comment ),'post_id' => $comment->comment_post_ID,'post_title' => $this->_get_the_title( $comment->comment_post_ID ),
                'author' => $comment->comment_author,'author_url' => $comment->comment_author_url,'author_email' => $comment->comment_author_email,
                'author_ip' => $comment->comment_author_IP,'type' => $comment->comment_type,
            ];
            return $this->_apply_filters( 'xmlrpc_prepare_comment', $_comment, $comment );
        }//1174
        protected function _prepare_user( $user, $fields ){
            $_user = ['user_id' => (string) $user->ID];
            $user_fields = ['username' => $user->user_login,
                'first_name' => $user->user_firstname,'last_name' => $user->user_lastname,
                'registered' => $this->_convert_date( $user->user_registered ),
                'bio' => $user->user_description,'email' => $user->user_email,
                'nickname' => $user->nickname,'nicename' => $user->user_nicename,
                'url' => $user->user_url,'display_name' => $user->display_name,'roles' => $user->roles,];
            if ( in_array( 'all', $fields, true ) ) {
                $_user = array_merge( $_user, $user_fields );
            } else {
                if ( in_array( 'basic', $fields, true ) ) {
                    $basic_fields = ['username', 'email', 'registered', 'display_name', 'nicename'];
                    $fields       = array_merge( $fields, $basic_fields );
                }
                $requested_fields = array_intersect_key( $user_fields, array_flip( $fields ) );
                $_user            = array_merge( $_user, $requested_fields );
            }
            return $this->_apply_filters( 'xmlrpc_prepare_user', $_user, $user, $fields );

        }//1222
        public function tp_newPost(array ...$args){
            if ( ! $this->_minimum_args( $args, 4 ) ) return $this->error;
            $this->escape( $args );
            @list($username,$password,$content_struct) = $args;
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( isset( $content_struct['post_date'] ) && ! ( $content_struct['post_date'] instanceof IXR_Date ) )
                $content_struct['post_date'] = $this->_convert_date( $content_struct['post_date'] );
            if ( isset( $content_struct['post_date_gmt'] ) && ! ( $content_struct['post_date_gmt'] instanceof IXR_Date ) ) {
                if ( '0000-00-00 00:00:00' === $content_struct['post_date_gmt'] || isset( $content_struct['post_date'] ) )
                    unset( $content_struct['post_date_gmt'] );
                else  $content_struct['post_date_gmt'] = $this->_convert_date( $content_struct['post_date_gmt'] );
            }
            $this->_do_action( 'xmlrpc_call', 'tp.newPost', $args, $this );
            unset( $content_struct['ID'] );
            return $this->_insert_post( $user, $content_struct );

        }//1309
        //1358 not used anywhere
        private function __toggle_sticky($post_data, $update = false ){
            $_post_type = $this->_get_post_type_object( $post_data['post_type'] );
            $post_type = null;
            if($_post_type instanceof TP_Post_Type){
                $post_type = $_post_type;
            }
            if ( 'private' === $post_data['post_status'] || ! empty( $post_data['post_password'] ) ) {
                if ( ! empty( $post_data['sticky'] ) )
                    return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you cannot stick a private post.' ) );
                if ( $update ) $this->_unstick_post( $post_data['ID'] );
            } elseif ( isset( $post_data['sticky'] ) ) {
                if ( ! $this->_current_user_can( $post_type->cap->edit_others_posts ) )
                    return new IXR_Error( 401, $this->__( 'Sorry, you are not allowed to make posts sticky.' ) );
                $sticky = $this->_tp_validate_boolean( $post_data['sticky'] );
                if ( $sticky ) $this->_stick_post( $post_data['ID'] );
                else $this->_unstick_post( $post_data['ID'] );
            }
            return true;

        }//1372
        protected function _insert_post( $user, $content_struct ){
            $defaults = ['post_status' => 'draft','post_type' => 'post','post_author' => null,'post_password' => null,
                'post_excerpt' => null,'post_content' => null,'post_title' => null,'post_date' => null,'post_date_gmt' => null,
                'post_format' => null,'post_name' => null,'post_thumbnail' => null,'post_parent' => null,'ping_status' => null,
                'comment_status' => null,'custom_fields' => null,'terms_names' => null,'terms' => null,
                'sticky' => null,'enclosure' => null,'ID' => null,];
            $post_data = $this->_tp_parse_args( array_intersect_key( $content_struct, $defaults ), $defaults );
            $_post_type = $this->_get_post_type_object( $post_data['post_type'] );
            $post_type = null;
            if($_post_type instanceof TP_Post_Type){
                $post_type = $_post_type;
            }
            if ( ! $post_type ) return new IXR_Error( FORBIDDEN, $this->__( 'Invalid post type.' ) );
            $update = ! empty( $post_data['ID'] );
            if ( $update ) {
                if ( ! $this->_get_post( $post_data['ID'] ) )
                    return new IXR_Error( UNAUTHORIZED, $this->__( 'Invalid post ID.' ) );
                if ( ! $this->_current_user_can( 'edit_post', $post_data['ID'] ) )
                    return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this post.' ) );
                if ( $this->_get_post_type( $post_data['ID'] ) !== $post_data['post_type'] )
                    return new IXR_Error( UNAUTHORIZED, $this->__( 'The post type may not be changed.' ) );
            } else if ( ! $this->_current_user_can( $post_type->cap->create_posts ) || ! $this->_current_user_can( $post_type->cap->edit_posts ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to post on this site.' ) );
            switch ( $post_data['post_status'] ) {
                case 'draft':
                case 'pending':
                    break;
                case 'private':
                    if ( ! $this->_current_user_can( $post_type->cap->publish_posts ) )
                        return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to create private posts in this post type.' ) );
                    break;
                case 'publish':
                case 'future':
                    if ( ! $this->_current_user_can( $post_type->cap->publish_posts ) )
                        return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to publish posts in this post type.' ) );
                    break;
                default:
                    if ( ! $this->_get_post_status_object( $post_data['post_status'] ) )
                        $post_data['post_status'] = 'draft';
                    break;
            }
            if ( ! empty( $post_data['post_password'] ) && ! $this->_current_user_can( $post_type->cap->publish_posts ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to create password protected posts in this post type.' ) );
            $post_data['post_author'] = $this->_abs_int( $post_data['post_author'] );
            if ( ! empty( $post_data['post_author'] ) && $post_data['post_author'] !== $user->ID ) {
                if ( ! $this->_current_user_can( $post_type->cap->edit_others_posts ) )
                    return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to create posts as this user.' ) );
                $author = $this->_get_user_data( $post_data['post_author'] );
                if ( ! $author ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid author ID.' ) );
            } else $post_data['post_author'] = $user->ID;
            if ( isset( $post_data['comment_status'] ) && 'open' !== $post_data['comment_status'] && 'closed' !== $post_data['comment_status'] )
                unset( $post_data['comment_status'] );
            if ( isset( $post_data['ping_status'] ) && 'open' !== $post_data['ping_status'] && 'closed' !== $post_data['ping_status'] )
                unset( $post_data['ping_status'] );
            if ( ! empty( $post_data['post_date_gmt'] ) ){
                if($post_data['post_date_gmt'] instanceof IXR_Date ){}
                $dateCreated = rtrim( $post_data['post_date_gmt']->getIso(), 'Z' ) . 'Z';
            }elseif ( ! empty( $post_data['post_date'] ) ){
                if($post_data['post_date'] instanceof IXR_Date ){}
                $dateCreated = $post_data['post_date']->getIso();
            }
            $post_data['edit_date'] = false;
            if ( ! empty( $dateCreated ) ) {
                $post_data['post_date']     = $this->_iso8601_to_datetime( $dateCreated );
                $post_data['post_date_gmt'] = $this->_iso8601_to_datetime( $dateCreated, 'gmt' );
                $post_data['edit_date'] = true;
            }
            if ( ! isset( $post_data['ID'] ) ) {
                $post_id = $this->_get_default_post_to_edit( $post_data['post_type'], true );
                if($post_id instanceof TP_Post){
                    $post_data['ID'] = $post_id->ID;
                }
            }
            $post_ID = $post_data['ID'];
            if ( 'post' === $post_data['post_type'] ) {
                $error = $this->__toggle_sticky( $post_data, $update );
                if ( $error )  return $error;
            }
            if ( isset( $post_data['post_thumbnail'] ) ) {
                if ( ! $post_data['post_thumbnail'] )
                    $this->_delete_post_thumbnail( $post_ID );
                elseif ( ! $this->_get_post( $this->_abs_int( $post_data['post_thumbnail'] ) ) )
                    return new IXR_Error( NOT_FOUND, $this->__( 'Invalid attachment ID.' ) );
                $this->_set_post_thumbnail( $post_ID, $post_data['post_thumbnail'] );
                unset( $content_struct['post_thumbnail'] );
            }
            if ( isset( $post_data['custom_fields'] ) )
                $this->set_custom_fields( $post_ID, $post_data['custom_fields'] );
            if ( isset( $post_data['terms'] ) || isset( $post_data['terms_names'] ) ) {
                $post_type_taxonomies = $this->_get_object_taxonomies( $post_data['post_type'], 'objects' );
                $terms = [];
                if ( isset( $post_data['terms'] ) && is_array( $post_data['terms'] ) ) {
                    $taxonomies = array_keys( $post_data['terms'] );
                    foreach ( $taxonomies as $taxonomy ) {
                        if ( ! array_key_exists( $taxonomy, $post_type_taxonomies ) )
                            return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, one of the given taxonomies is not supported by the post type.' ) );
                        if ( ! $this->_current_user_can( $post_type_taxonomies[ $taxonomy ]->cap->assign_terms ) )
                            return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to assign a term to one of the given taxonomies.' ) );
                        $term_ids           = $post_data['terms'][ $taxonomy ];
                        $terms[ $taxonomy ] = array();
                        foreach ( $term_ids as $term_id ) {
                            $term = $this->_get_term_by( 'id', $term_id, $taxonomy );
                            if ( ! $term ) return new IXR_Error( FORBIDDEN, $this->__( 'Invalid term ID.' ) );
                            $terms[ $taxonomy ][] = (int) $term_id;
                        }
                    }
                }
                if ( isset( $post_data['terms_names'] ) && is_array( $post_data['terms_names'] ) ) {
                    $taxonomies = array_keys( $post_data['terms_names'] );
                    foreach ( $taxonomies as $taxonomy ) {
                        if ( ! array_key_exists( $taxonomy, $post_type_taxonomies ) )
                            return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, one of the given taxonomies is not supported by the post type.' ) );
                        if ( ! $this->_current_user_can( $post_type_taxonomies[ $taxonomy ]->cap->assign_terms ) )
                            return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to assign a term to one of the given taxonomies.' ) );
                        $ambiguous_terms = [];
                        if ( $this->_is_taxonomy_hierarchical( $taxonomy ) ) {
                            $tax_term_names = $this->_get_terms(['taxonomy' => $taxonomy,'fields' => 'names','hide_empty' => false,]);
                            $tax_term_names_count = array_count_values( $tax_term_names );
                            $ambiguous_tax_term_counts = array_filter( $tax_term_names_count, array( $this, '_is_greater_than_one' ) );
                            $ambiguous_terms = array_keys( $ambiguous_tax_term_counts );
                        }
                        $term_names = $post_data['terms_names'][ $taxonomy ];
                        foreach ( $term_names as $term_name ) {
                            if ( in_array( $term_name, $ambiguous_terms, true ) )
                                return new IXR_Error( UNAUTHORIZED, $this->__( 'Ambiguous term name used in a hierarchical taxonomy. Please use term ID instead.' ) );
                            $term = $this->_get_term_by( 'name', $term_name, $taxonomy );
                            if ( ! $term ) {
                                if ( ! $this->_current_user_can( $post_type_taxonomies[ $taxonomy ]->cap->edit_terms ) )
                                    return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to add a term to one of the given taxonomies.' ) );
                                $term_info = $this->_tp_insert_term( $term_name, $taxonomy );
                                if ( $this->_init_error( $term_info ) ) {
                                    if($term_info instanceof TP_Error){}//todo
                                    return new IXR_Error( INTERNAL_SERVER_ERROR, $term_info->get_error_message() );
                                }
                                $terms[ $taxonomy ][] = (int) $term_info['term_id'];
                            } else $terms[ $taxonomy ][] = (int) $term->term_id;
                        }
                    }
                }
                $post_data['tax_input'] = $terms;
                unset( $post_data['terms'], $post_data['terms_names'] );
            }
            if ( isset( $post_data['post_format'] ) ) {
                $format = $this->_set_post_format( $post_ID, $post_data['post_format'] );
                if ( $this->_init_error( $format ) )
                    return new IXR_Error( INTERNAL_SERVER_ERROR, $format->get_error_message() );
                unset( $post_data['post_format'] );
            }
            $enclosure = $post_data['enclosure'] ?? null;
            $this->add_enclosure_if_new( $post_ID, $enclosure );
            $this->attach_uploads( $post_ID, $post_data['post_content'] );
            $post_data = $this->_apply_filters( 'xmlrpc_tp_insert_post_data', $post_data, $content_struct );
            $post_ID = $update ? $this->_tp_update_post( $post_data, true ) : $this->_tp_insert_post( $post_data, true );
            if ($post_ID instanceof TP_Error && $this->_init_error($post_ID)) {
                return new IXR_Error( 500, $post_ID->get_error_message() );
            }
            if ( ! $post_ID ) {
                if ( $update ) return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, the post could not be updated.' ) );
                else  return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, the post could not be created.' ) );
            }
            return (string) $post_ID;

        }//1410
        public function tp_editPost( array ...$args ){
            if ( ! $this->_minimum_args( $args, 5 ) ) return $this->error;
            $this->escape( $args );
            $username       = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password       = $args[2];
            $post_id        = (int) $args[3];
            $content_struct = $args[4];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.editPost', $args, $this );
            $post = $this->_get_post( $post_id, ARRAY_A );
            if ( empty( $post['ID'] ) )
                return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( isset( $content_struct['if_not_modified_since'] ) ) {
                if($content_struct['if_not_modified_since'] instanceof IXR_Date){

                }
                if ( $this->_mysql2date( 'U', $post['post_modified_gmt'] ) > $content_struct['if_not_modified_since']->getTimestamp() )
                    return new IXR_Error( CONFLICT, $this->__( 'There is a revision of this post that is more recent.' ) );
            }
            $post['post_date'] = $this->_convert_date( $post['post_date'] );
            if ( '0000-00-00 00:00:00' === $post['post_date_gmt'] || isset( $content_struct['post_date'] ) )
                unset( $post['post_date_gmt'] );
            else $post['post_date_gmt'] = $this->_convert_date( $post['post_date_gmt'] );
            if ( ! isset( $content_struct['post_date'] ) ) unset( $post['post_date'] );
            $this->escape( $post );
            $merged_content_struct = array_merge((array) $post, $content_struct );
            $retval = $this->_insert_post( $user, $merged_content_struct );
            if ( $retval instanceof IXR_Error ) return $retval;
            return true;
        }//1718
        public function tp_deletePost( array ...$args ){
            if ( ! $this->_minimum_args( $args, 4 ) ) return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $post_id  = (int) $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user )  return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.deletePost', $args, $this );
            $post = $this->_get_post( $post_id, ARRAY_A );
            if ( empty( $post['ID'] ) ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_current_user_can( 'delete_post', $post_id ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to delete this post.' ) );
            $result = $this->_tp_delete_post( $post_id );
            if ( ! $result )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, the post could not be deleted.' ) );
            return true;
        }//1802
        public function tp_getPost( array ...$args ){
            if ( ! $this->_minimum_args( $args, 4 ) ) return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $post_id  = (int) $args[3];
            if ( isset( $args[4] ) ) $fields = $args[4];
            else $fields = $this->_apply_filters( 'xmlrpc_default_post_fields', [ 'post', 'terms', 'custom_fields' ], 'tp.getPost' );
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getPost', $args, $this );
            $post = $this->_get_post( $post_id, ARRAY_A );
            if ( empty( $post['ID'] ) ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_current_user_can( 'edit_post', $post_id ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this post.' ) );
            return $this->_prepare_post( $post, $fields );
        }//1888
        public function tp_getPosts( array ...$args ): array{
            if ( ! $this->_minimum_args( $args, 3 ) ) return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $filter   = $args[3] ?? [];
            if ( isset( $args[4] ) ) $fields = $args[4];
            else $fields = $this->_apply_filters( 'xmlrpc_default_post_fields', ['post', 'terms', 'custom_fields'], 'tp.getPosts' );
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getPosts', $args, $this );
            $query = array();
            if ( isset( $filter['post_type'] ) ) {
                $post_type = $this->_get_post_type_object( $filter['post_type'] );
                if ( ! ($post_type ) ) return (string) new IXR_Error( FORBIDDEN, $this->__( 'Invalid post type.' ) );
            } else $post_type = $this->_get_post_type_object( 'post' );
            if ( ! $this->_current_user_can( $post_type->cap->edit_posts ) )
                return (string) new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit posts in this post type.' ) );
            $query['post_type'] = $post_type->name;
            if ( isset( $filter['post_status'] ) ) $query['post_status'] = $filter['post_status'];
            if ( isset( $filter['number'] ) ) $query['numberposts'] = $this->_abs_int( $filter['number'] );
            if ( isset( $filter['offset'] ) ) $query['offset'] = $this->_abs_int( $filter['offset'] );
            if ( isset( $filter['orderby'] ) ) {
                $query['orderby'] = $filter['orderby'];
                if ( isset( $filter['order'] ) ) $query['order'] = $filter['order'];
            }
            if ( isset( $filter['s'] ) ) $query['s'] = $filter['s'];
            $posts_list = $this->_tp_get_recent_posts( $query );
            if ( ! $posts_list ) return array();
            $struct = [];
            foreach ((array) $posts_list as $post ) {
                if ( ! $this->_current_user_can( 'edit_post', $post['ID'] ) ) continue;
                $struct[] = $this->_prepare_post( $post, $fields );
            }
            return $struct;
        }//1956
        public function tp_newTerm( array ...$args ){
            if ( ! $this->_minimum_args( $args, 4 ) ) return $this->error;
            $this->escape( $args );
            $username       = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password       = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $content_struct = $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.newTerm', $args, $this );
            if ( ! $this->_taxonomy_exists( $content_struct['taxonomy'] ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Invalid taxonomy.' ) );
            $taxonomy = $this->_get_taxonomy( $content_struct['taxonomy'] );
            $_taxonomy = null;
            if($taxonomy instanceof TP_Post_Type){
                $_taxonomy = $taxonomy;
            }
            if ( ! $this->_current_user_can( $_taxonomy->cap->edit_terms ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to create terms in this taxonomy.' ) );
            $taxonomy = (array) $taxonomy;
            $term_data = [];
            $term_data['name'] = trim( $content_struct['name'] );
            if ( empty( $term_data['name'] ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'The term name cannot be empty.' ) );
            if ( isset( $content_struct['parent'] ) ) {
                if ( ! $taxonomy['hierarchical'] )
                    return new IXR_Error( METHOD_NOT_ALLOWED, ( 'This taxonomy is not hierarchical.' ) );
                $parent_term_id = (int) $content_struct['parent'];
                $parent_term    = $this->_get_term( $parent_term_id, $taxonomy['name'] );
                //if($parent_term instanceof TP_Error);
                if ( $this->_init_error( $parent_term ) )
                    return new IXR_Error( INTERNAL_SERVER_ERROR, $parent_term->get_error_message() );
                if ( ! $parent_term ) return new IXR_Error( NOT_FOUND, $this->__( 'Parent term does not exist.' ) );
                $term_data['parent'] = $content_struct['parent'];
            }
            if ( isset( $content_struct['description'] ) ) $term_data['description'] = $content_struct['description'];
            if ( isset( $content_struct['slug'] ) ) $term_data['slug'] = $content_struct['slug'];
            $term = $this->_tp_insert_term( $term_data['name'], $taxonomy['name'], $term_data );
            //if($term instanceof TP_Error);
            if ( $this->_init_error( $term ) ) return new IXR_Error( INTERNAL_SERVER_ERROR, $term->get_error_message() );
            if ( ! $term ) return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, the term could not be created.' ) );
            if ( isset( $content_struct['custom_fields'] ) )
                $this->set_term_custom_fields( $term['term_id'], $content_struct['custom_fields'] );
            return (string) $term['term_id'];
        }//2062
        public function tp_editTerm( array ...$args ){
            if ( ! $this->_minimum_args( $args, 5 ) )return $this->error;
            $this->escape( $args );
            $username       = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password       = $args[2];
            $term_id        = (int) $args[3];
            $content_struct = $args[4];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.editTerm', $args, $this );
            if ( ! $this->_taxonomy_exists( $content_struct['taxonomy'] ) )
                return new IXR_Error( 403, $this->__( 'Invalid taxonomy.' ) );
            $taxonomy = $this->_get_taxonomy( $content_struct['taxonomy'] );
            $taxonomy = (array) $taxonomy;
            $term_data = [];
            $term = $this->_get_term( $term_id, $content_struct['taxonomy'] );
            //if($term instanceof TP_Error);
            if ( $this->_init_error( $term ) )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $term->get_error_message() );
            if ( ! $term )  return new IXR_Error( NOT_FOUND, $this->__( 'Invalid term ID.' ) );
            if ( ! $this->_current_user_can( 'edit_term', $term_id ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this term.' ) );
            if ( isset( $content_struct['name'] ) ) {
                $term_data['name'] = trim( $content_struct['name'] );
                if ( empty( $term_data['name'] ) )
                    return new IXR_Error( METHOD_NOT_ALLOWED, $this->__( 'The term name cannot be empty.' ) );
            }
            if ( ! empty( $content_struct['parent'] ) ) {
                if ( ! $taxonomy['hierarchical'] )
                    return new IXR_Error( METHOD_NOT_ALLOWED, $this->__( 'Cannot set parent term, taxonomy is not hierarchical.' ) );
                $parent_term_id = (int) $content_struct['parent'];
                $parent_term    = $this->_get_term( $parent_term_id, $taxonomy['name'] );
                //if($parent_term instanceof TP_Error);
                if ( $this->_init_error( $parent_term ) )
                    return new IXR_Error( INTERNAL_SERVER_ERROR, $parent_term->get_error_message() );
                if ( ! $parent_term )
                    return new IXR_Error( METHOD_NOT_ALLOWED, $this->__( 'Parent term does not exist.' ) );
                $term_data['parent'] = $content_struct['parent'];
            }
            if ( isset( $content_struct['description'] ) )
                $term_data['description'] = $content_struct['description'];
            if ( isset( $content_struct['slug'] ) )
                $term_data['slug'] = $content_struct['slug'];
            $term = $this->_tp_update_term( $term_id, $taxonomy['name'], $term_data );
            //if($term instanceof TP_Error);
            if ( $this->_init_error( $term ) )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $term->get_error_message() );
            if ( ! $term )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, editing the term failed.' ) );
            if ( isset( $content_struct['custom_fields'] ) )
                $this->set_term_custom_fields( $term_id, $content_struct['custom_fields'] );
            return true;
        }//2166
        public function tp_deleteTerm($args ){
            if ( ! $this->_minimum_args( $args,5)) return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $taxonomy = $args[3];
            $term_id  = (int) $args[4];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.deleteTerm', $args, $this );
            if ( ! $this->_taxonomy_exists( $taxonomy ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Invalid taxonomy.' ) );
            $_taxonomy = $this->_get_taxonomy( $taxonomy );
            $taxonomy = null;
            if($_taxonomy instanceof TP_Term){
                $taxonomy = $_taxonomy;
            }            $term     = $this->_get_term( $term_id, $taxonomy->name );
            if ( $this->_init_error( $term ) )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $term->get_error_message() );
            if ( ! $term ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid term ID.' ) );
            if ( ! $this->_current_user_can( 'delete_term', $term_id ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to delete this term.' ) );
            $result = $this->_tp_delete_term( $term_id, $taxonomy->name );
            if ( $this->_init_error( $result ) )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $term->get_error_message() );
            if ( ! $result ) return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, deleting the term failed.' ) );
            return $result;
        }//2282
        public function tp_getTerm($args ){
            if ( ! $this->_minimum_args( $args, 5 ) ) return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $taxonomy = $args[3];
            $term_id  = (int) $args[4];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getTerm', $args, $this );
            if ( ! $this->_taxonomy_exists( $taxonomy ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Invalid taxonomy.' ) );
            $_taxonomy = $this->_get_taxonomy( $taxonomy );
            $taxonomy = null;
            if($_taxonomy instanceof TP_Term){
                $taxonomy = $_taxonomy;
            }
            $term = $this->_get_term( $term_id, $taxonomy->name, ARRAY_A );
            if ( $this->_init_error( $term ))
                return new IXR_Error( INTERNAL_SERVER_ERROR, $term->get_error_message() );
            if ( ! $term ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid term ID.' ) );
            if ( ! $this->_current_user_can( 'assign_term', $term_id ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to assign this term.' ) );
            return $this->_prepare_term( $term );
        }//2361
        public function tp_getTerms( array ...$args ){
            if (! $this->_minimum_args( $args, 4)) return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $taxonomy = $args[3];
            $filter   = $args[4] && [];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getTerms', $args, $this );
            if ( ! $this->_taxonomy_exists( $taxonomy ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Invalid taxonomy.' ) );
            $taxonomy = $this->_get_taxonomy( $taxonomy );
            if($taxonomy instanceof TP_Post_Type){}
            if ( ! $this->_current_user_can( $taxonomy->cap->assign_terms ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to assign terms in this taxonomy.' ) );
            $query = ['taxonomy' => $taxonomy->name];
            if ( isset( $filter['number'] ) )
                $query['number'] = $this->_abs_int( $filter['number'] );
            if ( isset( $filter['offset'] ) )
                $query['offset'] = $this->_abs_int( $filter['offset'] );
            if ( isset( $filter['orderby'] ) ) {
                $query['orderby'] = $filter['orderby'];
                if ( isset( $filter['order'] ) ) $query['order'] = $filter['order'];
            }
            if ( isset( $filter['hide_empty'] ) )
                $query['hide_empty'] = $filter['hide_empty'];
            else $query['get'] = 'all';
            if ( isset( $filter['search'] ) ) $query['search'] = $filter['search'];
            $terms = $this->_get_terms( $query );
            //if($terms instanceof TP_Error){}
            if ( $this->_init_error( $terms ) )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $terms->get_error_message() );
            $struct = [];
            foreach ((array)$terms as $term )
                $struct[] = $this->_prepare_term( $term );
            return $struct;
        }//2426
        public function tp_getTaxonomy($args ){
            if ( ! $this->_minimum_args( $args, 4 ) )
                return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $taxonomy = $args[3];
            if ( isset( $args[4] ) ) $fields = $args[4];
            else $fields = $this->_apply_filters( 'xmlrpc_default_taxonomy_fields', array( 'labels', 'cap', 'object_type' ), 'tp.getTaxonomy' );
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getTaxonomy', $args, $this );
            if ( ! $this->_taxonomy_exists( $taxonomy ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Invalid taxonomy.' ) );
            $taxonomy = $this->_get_taxonomy( $taxonomy );
            $_taxonomy = null;
            if($taxonomy instanceof TP_Post_Type){
                $_taxonomy = $taxonomy;
            }
            if ( ! $this->_current_user_can( $_taxonomy->cap->assign_terms ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to assign terms in this taxonomy.' ) );
            return $this->_prepare_taxonomy( $taxonomy, $fields );
        }//2519
        public function tp_getTaxonomies( array ...$args ): array{
            if(!$this->_minimum_args( $args, 3)) return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $filter   = $args[3] ?? array( 'public' => true );
            if ( isset( $args[4] ) ) $fields = $args[4];
            else $fields = $this->_apply_filters( 'xmlrpc_default_taxonomy_fields', array( 'labels', 'cap', 'object_type' ), 'tp.getTaxonomies' );
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getTaxonomies', $args, $this );
            $taxonomies = $this->_get_taxonomies( $filter, 'objects' );
            $struct = [];
            foreach ( (array)$taxonomies as $taxonomy ) {
                if ( ! $this->_current_user_can( $taxonomy->cap->assign_terms ) )
                    continue;
                $struct[] = $this->_prepare_taxonomy( $taxonomy, $fields );
            }
            return $struct;

        }//2584
        public function tp_getUser( $args ){
            if ( ! $this->_minimum_args( $args, 4 ) ) return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user_id  = (int) $args[3];
            if ( isset( $args[4] ) ) $fields = $args[4];
            else $fields = $this->_apply_filters( 'xmlrpc_default_user_fields', array( 'all' ), 'tp.getUser' );
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getUser', $args, $this );
            if ( ! $this->_current_user_can( 'edit_user', $user_id ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this user.' ) );
            $user_data = $this->_get_user_data( $user_id );
            if ( ! $user_data ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid user ID.' ) );
            return $this->_prepare_user( $user_data, $fields );
        }//2553
        public function tp_getUsers( $args ){
            if ( ! $this->_minimum_args( $args, 3 ) ) return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $filter   = $args[3] ?? array();
            if ( isset( $args[4] ) ) $fields = $args[4];
            else $fields = $this->_apply_filters( 'xmlrpc_default_user_fields', array( 'all' ), 'tp.getUsers' );
            $user = $this->login( $username, $password );
            if ( ! $user )  return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getUsers', $args, $this );
            if ( ! $this->_current_user_can( 'list_users' ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to list users.' ) );
            $query = array( 'fields' => 'all_with_meta' );
            $query['number'] = ( isset( $filter['number'] ) ) ? $this->_abs_int( $filter['number'] ) : 50;
            $query['offset'] = ( isset( $filter['offset'] ) ) ? $this->_abs_int( $filter['offset'] ) : 0;
            if ( isset( $filter['orderby'] ) ) {
                $query['orderby'] = $filter['orderby'];
                if ( isset( $filter['order'] ) ) $query['order'] = $filter['order'];
            }
            if ( isset( $filter['role'] ) ) {
                if ( $this->_get_role( $filter['role'] ) === null )
                    return new IXR_Error( FORBIDDEN, $this->__( 'Invalid role.' ) );
                $query['role'] = $filter['role'];
            }
            if ( isset( $filter['who'] ) ) $query['who'] = $filter['who'];
            $users = $this->_get_users( $query );
            $_users = [];
            foreach ($users as $user_data ) {
                if ( $this->_current_user_can( 'edit_user', $user_data->ID ) )
                    $_users[] = $this->_prepare_user( $user_data, $fields );
            }
            return $_users;
        }//2733
        public function tp_getProfile( $args ){
            if ( ! $this->_minimum_args( $args, 3 ) ) return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            if ( isset( $args[3] ) ) $fields = $args[3];
            else $fields = $this->_apply_filters( 'xmlrpc_default_user_fields', array( 'all' ), 'tp.getProfile' );
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getProfile', $args, $this );
            if ( ! $this->_current_user_can( 'edit_user', $user->ID ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit your profile.' ) );
            $user_data = $this->_get_user_data( $user->ID );
            return $this->_prepare_user( $user_data, $fields );
        }//2814
        public function tp_editProfile( $args ){
            if ( ! $this->_minimum_args( $args, 4 ) ) return $this->error;
            $this->escape( $args );
            $username       = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password       = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $content_struct = $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.editProfile', $args, $this );
            if ( ! $this->_current_user_can( 'edit_user', $user->ID ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit your profile.' ) );
            $user_data       = array();
            $user_data['ID'] = $user->ID;
            if ( isset( $content_struct['first_name'] ) )
                $user_data['first_name'] = $content_struct['first_name'];
            if ( isset( $content_struct['last_name'] ) )
                $user_data['last_name'] = $content_struct['last_name'];
            if ( isset( $content_struct['url'] ) )
                $user_data['user_url'] = $content_struct['url'];
            if ( isset( $content_struct['display_name'] ) )
                $user_data['display_name'] = $content_struct['display_name'];
            if ( isset( $content_struct['nickname'] ) )
                $user_data['nickname'] = $content_struct['nickname'];
            if ( isset( $content_struct['nicename'] ) )
                $user_data['user_nicename'] = $content_struct['nicename'];
            if ( isset( $content_struct['bio'] ) )
                $user_data['description'] = $content_struct['bio'];
            $result = $this->_tp_update_user( $user_data );
            if ( $this->_init_error( $result ) )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $result->get_error_message() );
            if ( ! $result )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, the user could not be updated.' ) );
            return true;
        }//2870
        public function tp_getPage($args ){
            $this->escape( $args );
            $page_id  = (int) $args[1];
            $username = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $page = $this->_get_post( $page_id );
            if ( ! $page ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_current_user_can( 'edit_page', $page_id ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this page.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.getPage', $args, $this );
            /** @noinspection PhpUndefinedFieldInspection */
            if ( $page->ID && ( 'page' === $page->post_type ) )
                return $this->_prepare_page( $page );
            else return new IXR_Error( NOT_FOUND, $this->__( 'Sorry, no such page.' ) );

        }//2954
        public function tp_getPages( array ...$args ){
            $this->escape( $args );
            $username  = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password  = $args[2];
            $num_pages = isset( $args[3] ) ? (int) $args[3] : 10;
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_pages' ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit pages.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.getPages', $args, $this );
            $pages     = $this->_get_posts(['post_type' => 'page','post_status' => 'any','numberposts' => $num_pages,]);
            $num_pages = count( $pages );
            if ( $num_pages >= 1 ) {
                $pages_struct = [];
                foreach ((array) $pages as $page ) {
                    if ( $this->_current_user_can( 'edit_page', $page->ID ) )
                        $pages_struct[] = $this->_prepare_page( $page );
                }
                return $pages_struct;
            }
            return [];
        }//3002
        public function tp_newPage( $args ){
            $username = $this->escape( $args[1] );
            $password = $this->escape( $args[2] );
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.newPage', $args, $this );
            $args[3]['post_type'] = 'page';
            return $this->mw_newPost( $args );
        }//3063
        public function tp_deletePage( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $page_id  = (int) $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.deletePage', $args, $this );
            $actual_page = $this->_get_post( $page_id, ARRAY_A );
            if ( ! $actual_page || ( 'page' !== $actual_page['post_type'] ) )
                return new IXR_Error( NOT_FOUND, $this->__( 'Sorry, no such page.' ) );
            if ( ! $this->_current_user_can( 'delete_page', $page_id ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to delete this page.' ) );
            $result = $this->_tp_delete_post( $page_id );
            if ( ! $result ) return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Failed to delete the page.' ) );
            $this->_do_action( 'xmlrpc_call_success_tp_deletePage', $page_id, $args );
            return true;
        }//3098
        public function tp_editPage( $args ){
            $page_id  = (int) $args[1];
            $username = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[3];
            /** @noinspection MultiAssignmentUsageInspection */
            $content  = $args[4];
            /** @noinspection MultiAssignmentUsageInspection */
            $publish  = $args[5];
            $escaped_username = $this->escape( $username );
            $escaped_password = $this->escape( $password );
            $user = $this->login( $escaped_username, $escaped_password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.editPage', $args, $this );
            $actual_page = $this->_get_post( $page_id, ARRAY_A );
            if ( ! $actual_page || ( 'page' !== $actual_page['post_type'] ) )
                return new IXR_Error( NOT_FOUND, $this->__( 'Sorry, no such page.' ) );
            if ( ! $this->_current_user_can( 'edit_page', $page_id ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this page.' ) );
            $content['post_type'] = 'page';
            $args = [$page_id,$username,$password,$content,$publish,];
            return $this->mw_editPost( $args );
        }//3161
        public function tp_getPageList( $args ){
            $tpdb = $this->_init_db();
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_pages' ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit pages.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.getPageList', $args, $this );
            $page_list = $tpdb->get_results( TP_SELECT . " ID page_id, post_title page_title, post_parent page_parent_id, post_date_gmt, post_date, post_status FROM {$tpdb->posts} WHERE post_type = 'page' ORDER BY ID");
            foreach ($page_list as $iValue) {
                $iValue->dateCreated      = $this->_convert_date( $iValue->post_date );
                $iValue->date_created_gmt = $this->_convert_date_gmt( $iValue->post_date_gmt, $iValue->post_date );
                unset( $iValue->post_date_gmt , $iValue->post_date , $iValue->post_status );
            }
            return $page_list;
        }//3223
        public function tp_getAuthors( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_posts' ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit posts.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.getAuthors', $args, $this );
            $authors = [];
            foreach ($this->_get_users( array( 'fields' => array( 'ID', 'user_login', 'display_name' ) ) ) as $user )
                $authors[] = ['user_id' => $user->ID,'user_login' => $user->user_login,'display_name' => $user->display_name,];
            return $authors;
        }//3286
        public function tp_getTags( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_posts' ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you must be able to edit posts on this site in order to view tags.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.getKeywords', $args, $this );
            $tags = [];
            $all_tags = $this->_get_tags();
            if ( $all_tags ) {
                foreach ($all_tags as $tag ) {
                    $struct             = [];
                    $struct['tag_id']   = $tag->term_id;
                    $struct['name']     = $tag->name;
                    $struct['count']    = $tag->count;
                    $struct['slug']     = $tag->slug;
                    $struct['html_url'] = $this->_esc_html( $this->_get_tag_link( $tag->term_id ) );
                    $struct['rss_url']  = $this->_esc_html( $this->_get_tag_feed_link( $tag->term_id ) );
                    $tags[] = $struct;
                }
            }
            return $tags;
        }//3330
        public function tp_newCategory( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $category = $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.newCategory', $args, $this );
            if ( ! $this->_current_user_can( 'manage_categories' ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to add a category.' ) );
            if ( empty( $category['slug'] ) ) $category['slug'] = '';
            if ( ! isset( $category['parent_id'] ) ) $category['parent_id'] = '';
            if ( empty( $category['description'] ) ) $category['description'] = '';
            $new_category = ['cat_name' => $category['name'],'category_nicename' => $category['slug'],
                'category_parent' => $category['parent_id'],'category_description' => $category['description'],];
            $_cat_id = $this->_tp_insert_category( $new_category, true );
            $cat_id = null;
            if($_cat_id instanceof TP_Error){
                $cat_id = $_cat_id;
            }
            if ( $this->_init_error( $cat_id ) ) {
                if ( 'term_exists' === $cat_id->get_error_code() )
                    return (int) $cat_id->get_error_data();
                else return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, the category could not be created.' ) );
            } elseif ( ! $cat_id )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, the category could not be created.' ) );
            $this->_do_action( 'xmlrpc_call_success_tp_newCategory', $cat_id, $args );
            return $cat_id;
        }//3383
        public function tp_deleteCategory( $args ){
            $this->escape( $args );
            $username    = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password    = $args[2];
            $category_id = (int) $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.deleteCategory', $args, $this );
            if ( ! $this->_current_user_can( 'delete_term', $category_id ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to delete this category.' ) );
            $status = $this->_tp_delete_term( $category_id, 'category' );
            if ( true === $status ) $this->_do_action( 'xmlrpc_call_success_tp_deleteCategory', $category_id, $args );
            return $status;
        }//3466
        public function tp_suggestCategories( $args ){
            $this->escape( $args );
            $username    = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password    = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $category    = $args[3];
            $max_results = (int) $args[4];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_posts' ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you must be able to edit posts on this site in order to view categories.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.suggestCategories', $args, $this );
            $category_suggestions = [];
            $args = ['get' => 'all','number' => $max_results,'name__like' => $category,];
            foreach ( $this->_get_categories( $args ) as $cat )
                $category_suggestions[] = ['category_id' => $cat->term_id,'category_name' => $cat->name,];
            return $category_suggestions;
        }//3518
        public function tp_getComment( $args ){
            $this->escape( $args );
            $username   = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password   = $args[2];
            $comment_id = (int) $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getComment', $args, $this );
            $comment = $this->_get_comment( $comment_id );
            if ( ! $comment ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid comment ID.' ) );
            if ( ! $this->_current_user_can( 'edit_comment', $comment_id ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you are not allowed to moderate or edit this comment.' ) );
            return $this->_prepare_comment( $comment );
        }//3569
        public function tp_getComments( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $struct   = $args[3] ?? [];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getComments', $args, $this );
            if ( isset( $struct['status'] ) ) $status = $struct['status'];
            else $status = '';
            if ('approve' !== $status && ! $this->_current_user_can( 'moderate_comments' ))
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Invalid comment status.' ) );
            $post_id = '';
            if ( isset( $struct['post_id'] ) ) $post_id = $this->_abs_int( $struct['post_id'] );
            $post_type = '';
            if ( isset( $struct['post_type'] ) ) {
                $_post_type_object = $this->_get_post_type_object( $struct['post_type'] );
                $post_type_object = null;
                if($_post_type_object  instanceof TP_Post_Type ){
                    $post_type_object = $_post_type_object;
                }
                if (! $post_type_object || ! $this->_post_type_supports( $post_type_object->name, 'comments' ) )
                    return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post type.' ) );
                $post_type = $struct['post_type'];
            }
            $offset = 0;
            if ( isset( $struct['offset'] ) ) $offset = $this->_abs_int( $struct['offset'] );
            $number = 10;
            if ( isset( $struct['number'] ) ) $number = $this->_abs_int( $struct['number'] );
            $comments = $this->_get_comments(
                ['status' => $status,'post_id' => $post_id,'offset' => $offset,'number' => $number,'post_type' => $post_type,]
            );
            $comments_struct = [];
            if ( is_array( $comments ) ) {
                foreach ( $comments as $comment ) $comments_struct[] = $this->_prepare_comment( $comment );
            }
            return $comments_struct;
        }//3622
        public function tp_deleteComment( $args ){
            $this->escape( $args );
            $username   = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password   = $args[2];
            $comment_ID = (int) $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_get_comment( $comment_ID ) )
                return new IXR_Error( NOT_FOUND, $this->__( 'Invalid comment ID.' ) );
            if ( ! $this->_current_user_can( 'edit_comment', $comment_ID ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you are not allowed to delete this comment.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.deleteComment', $args, $this );
            $status = $this->_tp_delete_comment( $comment_ID );
            if ( $status ) $this->_do_action( 'xmlrpc_call_success_tp_deleteComment', $comment_ID, $args );
            return $status;
        }//3709
        public function tp_editComment( $args ){
            $this->escape( $args );
            $username       = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password       = $args[2];
            $comment_ID     = (int) $args[3];
            $content_struct = $args[4];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_get_comment( $comment_ID ) )
                return new IXR_Error( NOT_FOUND, $this->__( 'Invalid comment ID.' ) );
            if ( ! $this->_current_user_can( 'edit_comment', $comment_ID ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you are not allowed to moderate or edit this comment.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.editComment', $args, $this );
            $comment = ['comment_ID' => $comment_ID,];
            if ( isset( $content_struct['status'] ) ) {
                $statuses = $this->_get_comment_statuses();
                $statuses = array_keys( $statuses );
                if ( ! in_array( $content_struct['status'], $statuses, true ) )
                    return new IXR_Error( UNAUTHORIZED, $this->__( 'Invalid comment status.' ) );
                $comment['comment_approved'] = $content_struct['status'];
            }
            if ( $content_struct['date_created_gmt'] instanceof IXR_Date && ! empty( $content_struct['date_created_gmt'] ) ) {
                $dateCreated                 = rtrim( $content_struct['date_created_gmt']->getIso(), 'Z' ) . 'Z';
                $comment['comment_date']     = $this->_get_date_from_gmt( $dateCreated );
                $comment['comment_date_gmt'] = $this->_iso8601_to_datetime( $dateCreated, 'gmt' );
            }
            if ( isset( $content_struct['content'] ) )
                $comment['comment_content'] = $content_struct['content'];
            if ( isset( $content_struct['author'] ) )
                $comment['comment_author'] = $content_struct['author'];
            if ( isset( $content_struct['author_url'] ) )
                $comment['comment_author_url'] = $content_struct['author_url'];
            if ( isset( $content_struct['author_email'] ) )
                $comment['comment_author_email'] = $content_struct['author_email'];
            $result = $this->_tp_update_comment( $comment, true );
            if ( $this->_init_error( $result ) )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $result->get_error_message() );
            if ( ! $result )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, the comment could not be updated.' ) );
            $this->_do_action( 'xmlrpc_call_success_wp_editComment', $comment_ID, $args ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
            return true;
        }//3776
        public function tp_newComment( $args ){
            $this->escape( $args );
            $username       = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password       = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $post           = $args[3];
            /** @noinspection MultiAssignmentUsageInspection */
            $content_struct = $args[4];
            $allow_anon = $this->_apply_filters( 'xmlrpc_allow_anonymous_comments', false );
            $user = $this->login( $username, $password );
            if ( ! $user ) {
                $logged_in = false;
                if ( $allow_anon && $this->_get_option( 'comment_registration' ) )
                    return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you must be logged in to comment.' ) );
                elseif ( ! $allow_anon ) return $this->error;
            } else $logged_in = true;
            if ( is_numeric( $post ) ) $post_id = $this->_abs_int( $post );
            else $post_id = $this->_url_to_postid( $post );
            if ( ! $post_id ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_get_post( $post_id ) )
                return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_comments_open( $post_id ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, comments are closed for this item.' ) );
            if ('publish' === $this->_get_post_status( $post_id ) && ! $this->_current_user_can( 'edit_post', $post_id ) &&
                $this->_post_password_required( $post_id ))
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you are not allowed to comment on this post.' ) );
            if ('private' === $this->_get_post_status( $post_id ) && ! $this->_current_user_can( 'read_post', $post_id ))
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you are not allowed to comment on this post.' ) );
            $comment = ['comment_post_ID' => $post_id,'comment_content' => trim( $content_struct['content'] ),];
            if ( $logged_in ) {
                $display_name = $user->display_name;
                $user_email   = $user->user_email;
                $user_url     = $user->user_url;
                $comment['comment_author']       = $this->escape( $display_name );
                $comment['comment_author_email'] = $this->escape( $user_email );
                $comment['comment_author_url']   = $this->escape( $user_url );
                $comment['user_ID']              = $user->ID;
            } else {
                $comment['comment_author'] = '';
                if ( isset( $content_struct['author'] ) )
                    $comment['comment_author'] = $content_struct['author'];
                $comment['comment_author_email'] = '';
                if ( isset( $content_struct['author_email'] ) )
                    $comment['comment_author_email'] = $content_struct['author_email'];
                $comment['comment_author_url'] = '';
                if ( isset( $content_struct['author_url'] ) )
                    $comment['comment_author_url'] = $content_struct['author_url'];
                $comment['user_ID'] = 0;
                if ( $this->_get_option( 'require_name_email' ) ){
                    if ('' === $comment['comment_author'] || strlen( $comment['comment_author_email'] ) < 6 )
                        return new IXR_Error( FORBIDDEN, $this->__( 'Comment author name and email are required.' ) );
                    elseif ( ! $this->_is_email( $comment['comment_author_email'] ) )
                        return new IXR_Error( FORBIDDEN, $this->__( 'A valid email address is required.' ) );
                }
            }
            $comment['comment_parent'] = isset( $content_struct['comment_parent'] ) ? $this->_abs_int( $content_struct['comment_parent'] ) : 0;
            $allow_empty = $this->_apply_filters( 'allow_empty_comment', false, $comment );
            if ( ! $allow_empty && '' === $comment['comment_content'] )
                return new IXR_Error( FORBIDDEN, $this->__( 'Comment is required.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.newComment', $args, $this );
            $_comment_ID = $this->_tp_new_comment( $comment, true );
            $comment_ID = null;
            if($_comment_ID  instanceof TP_Error ){
                $comment_ID = $_comment_ID;
            }
            if ( $this->_init_error( $comment_ID ) )
                return new IXR_Error( FORBIDDEN, $comment_ID->get_error_message() );
            if ( ! $comment_ID ) return new IXR_Error( FORBIDDEN, $this->__( 'Something went wrong.' ) );
            $this->_do_action( 'xmlrpc_call_success_tp_newComment', $comment_ID, $args );
            return $comment_ID;

        }//3876
        public function tp_getCommentStatusList( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'publish_posts' ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you are not allowed to access details about this site.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.getCommentStatusList', $args, $this );
            return $this->_get_comment_statuses();
        }//4029
        public function tp_getCommentCount( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $post_id  = (int) $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $post = $this->_get_post( $post_id, ARRAY_A );
            if ( empty( $post['ID'] ) )
                return new IXR_Error( 404, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_current_user_can( 'edit_post', $post_id ) )
                return new IXR_Error( 403, $this->__( 'Sorry, you are not allowed to access details of this post.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.getCommentCount', $args, $this );
            $count = $this->_tp_count_comments( $post_id );
            //todo this instead of $count->--
            return array(
                'approved'            => $count['approved'],
                'awaiting_moderation' => $count['moderated'],
                'spam'                => $count['spam'],
                'total_comments'      => $count['total_comments'],
            );
        }//4065
        public function tp_getPostStatusList( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_posts' ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you are not allowed to access details about this site.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.getPostStatusList', $args, $this );
            return $this->_get_post_statuses();
        }//4113
        public function tp_getPageStatusList( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_pages' ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you are not allowed to access details about this site.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.getPageStatusList', $args, $this );
            return $this->_get_page_statuses();
        }//4148
        public function tp_getPageTemplates( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_pages' ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you are not allowed to access details about this site.' ) );
            $templates            = $this->_get_page_templates();
            $templates['Default'] = 'default';
            return $templates;
        }//4183
        public function tp_getOptions( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $options  = isset( $args[3] ) ? (array) $args[3] : [];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( count( $options ) === 0 ) $options = array_keys( $this->blog_options );
            return $this->getListOptions( $options );
        }//4219
        public function getListOptions( $options ): array{
            $data       = [];
            $can_manage = $this->_current_user_can( 'manage_options' );
            foreach ( $options as $option ) {
                if ( array_key_exists( $option, $this->blog_options ) ) {
                    $data[ $option ] = $this->blog_options[ $option ];
                    if ( isset( $data[ $option ]['option'] ) ) {
                        $data[ $option ]['value'] = $this->_get_option( $data[ $option ]['option'] );
                        unset( $data[ $option ]['option'] );
                    }
                    if ( ! $can_manage ) $data[ $option ]['readonly'] = true;
                }
            }
            return $data;
        }//4247
        public function tp_setOptions( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $options  = (array) $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'manage_options' ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you are not allowed to update options.' ) );
            $option_names = [];
            foreach ( $options as $o_name => $o_value ) {
                $option_names[] = $o_name;
                if ( ! array_key_exists( $o_name, $this->blog_options ) ) continue;
                if ( true === $this->blog_options[ $o_name ]['readonly'] ) continue;
                $this->_update_option( $this->blog_options[ $o_name ]['option'], $this->_tp_unslash( $o_value ) );
            }
            return $this->getListOptions( $option_names );
        }//4283
        public function tp_getMediaItem( $args ){
            $this->escape( $args );
            $username      = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password      = $args[2];
            $attachment_id = (int) $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'upload_files' ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you are not allowed to upload files.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.getMediaItem', $args, $this );
            $attachment = $this->_get_post( $attachment_id );
            $attachment_post_type = null;
            if($attachment instanceof \stdClass ){//todo
                $attachment_post_type = $attachment->post_type;
            }
            if ( ! $attachment || 'attachment' !== $attachment_post_type )
                return new IXR_Error( NOT_FOUND, $this->__( 'Invalid attachment ID.' ) );
            return $this->_prepare_media_item( $attachment );
        }//4340
        public function tp_getMediaLibrary( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $struct   = $args[3] ?? [];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'upload_files' ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to upload files.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.getMediaLibrary', $args, $this );
            $parent_id = ( isset( $struct['parent_id'] ) ) ? $this->_abs_int( $struct['parent_id'] ) : '';
            $mime_type = $struct['mime_type'] ?? '';
            $offset    = ( isset( $struct['offset'] ) ) ? $this->_abs_int( $struct['offset'] ) : 0;
            $number    = ( isset( $struct['number'] ) ) ? $this->_abs_int( $struct['number'] ) : -1;
            $attachments = $this->_get_posts(
                ['post_type' => 'attachment','post_parent' => $parent_id,'offset' => $offset,'numberposts' => $number,'post_mime_type' => $mime_type,]
            );
            $attachments_struct = array();
            foreach ((array) $attachments as $attachment )
                $attachments_struct[] = $this->_prepare_media_item( $attachment );
            return $attachments_struct;
        }//4393
        public function tp_getPostFormats( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_posts' ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Sorry, you are not allowed to access details about this site.' ) );
            $this->_do_action( 'xmlrpc_call', 'tp.getPostFormats', $args, $this );
            $formats = $this->_get_post_format_strings();
            if (isset($args[3]) && is_array($args[3]) && $args[3]['show-supported'] && $this->_current_theme_supports('post-formats')) {
                $supported = $this->_get_theme_support( 'post-formats' );
                $data              = [];
                $data['all']       = $formats;
                $data['supported'] = $supported[0];
                $formats = $data;
            }
            return $formats;
        }//4450
        public function tp_getPostType( $args ){
            if ( ! $this->_minimum_args( $args, 4 ) ) return $this->error;
            $this->escape( $args );
            $username       = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password       = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $post_type_name = $args[3];
            if ( isset( $args[4] ) ) $fields = $args[4];
            else $fields = $this->_apply_filters( 'xmlrpc_default_posttype_fields', array( 'labels', 'cap', 'taxonomies' ), 'tp.getPostType' );
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getPostType', $args, $this );
            if ( ! $this->_post_type_exists( $post_type_name ) )
                return new IXR_Error( FORBIDDEN, $this->__( 'Invalid post type.' ) );
            $_post_type = $this->_get_post_type_object( $post_type_name );
            $post_type = null;
            if($_post_type  instanceof TP_Post_Type ){
                $post_type = $_post_type;
            }
            if ( ! $this->_current_user_can( $post_type->cap->edit_posts ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit posts in this post type.' ) );
            return $this->_prepare_post_type( $post_type, $fields );
        }//4515
        public function tp_getPostTypes( $args ): array{
            if ( ! $this->_minimum_args( $args, 3 ) ) return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $filter   = $args[3] ?? ['public' => true];
            if ( isset( $args[4] ) ) $fields = $args[4];
            else $fields = $this->_apply_filters( 'xmlrpc_default_posttype_fields', array( 'labels', 'cap', 'taxonomies' ), 'tp.getPostTypes' );
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getPostTypes', $args, $this );
            $post_types = $this->_get_post_types( $filter, 'objects' );
            $struct = [];
            foreach ((array) $post_types as $post_type ) {
                if ( ! $this->_current_user_can( $post_type->cap->edit_posts )) continue;
                $struct[ $post_type->name ] = $this->_prepare_post_type( $post_type, $fields );
            }
            return $struct;
        }//4579
        public function tp_getRevisions( $args ){
            if ( ! $this->_minimum_args( $args, 4 ) ) return $this->error;
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $post_id  = (int) $args[3];
            if ( isset( $args[4] ) ) $fields = $args[4];
            else $fields = $this->_apply_filters( 'xmlrpc_default_revision_fields', array( 'post_date', 'post_date_gmt' ), 'tp.getRevisions' );
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.getRevisions', $args, $this );
            $post = $this->_get_post( $post_id );
            if ( ! $post )  return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_current_user_can( 'edit_post', $post_id ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit posts.' ) );
            if ( ! $this->_tp_revisions_enabled( $post ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, revisions are disabled.' ) );
            $revisions = $this->_tp_get_post_revision( $post_id );
            if ( ! $revisions ) return [];
            $struct = array();
            foreach ((array) $revisions as $revision ) {
                if ( ! $this->_current_user_can( 'read_post', $revision->ID ) ) continue;
                if ( $this->_tp_is_post_autosave( $revision ) ) continue;
                $struct[] = $this->_prepare_post( get_object_vars( $revision ), $fields );
            }
            return $struct;
        }//4642
        public function tp_restoreRevision( $args ){
            if ( ! $this->_minimum_args( $args, 3 ) ) return $this->error;
            $this->escape( $args );
            $username    = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password    = $args[2];
            $revision_id = (int) $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'tp.restoreRevision', $args, $this );
            $_revision = $this->_tp_get_post_revision( $revision_id );
            $revision = null;
            if($_revision instanceof TP_Post ){
                $revision = $_revision;
            }
            if ( ! $revision ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( $this->_tp_is_post_autosave( $revision ) ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            $post = $this->_get_post( $revision->post_parent );
            if ( ! $post ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_current_user_can( 'edit_post', $revision->post_parent ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this post.' ) );
            if ( ! $this->_tp_revisions_enabled( $post ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, revisions are disabled.' ) );
            $post = $this->_tp_restore_post_revision( $revision_id );
            return (bool) $post;

        }//4730
        public function blogger_getUsersBlogs( $args ){
            if ( ! $this->_minimum_args( $args, 3 ) ) return $this->error;
            if ( $this->_is_multisite() ) return $this->__multisite_getUsersBlogs( $args );
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'blogger.getUsersBlogs', $args, $this );
            $is_admin = $this->_current_user_can( 'manage_options' );
            $struct = ['isAdmin' => $is_admin,'url' => $this->_get_option( 'home' ) . '/',
                'blogid' => '1','blogName' => $this->_get_option( 'blogname' ),
                'xmlrpc' => $this->_site_url( 'xmlrpc.php', 'rpc' ),];//todo xmlrpc.php
            return array( $struct );
        }//4798
        private function __multisite_getUsersBlogs( $args ){
            $_current_blog = $this->_get_site();
            $current_blog = null;
            if($_current_blog  instanceof  TP_Network){
                $current_blog = $_current_blog;
            }
            $domain = $current_blog->domain;
            $path   = $current_blog->path . 'xmlrpc.php';
            $rpc = new IXR_Client( $this->_set_url_scheme( "https://{$domain}{$path}" ) );
            $rpc->query( 'tp.getUsersBlogs', $args[1], $args[2] );
            $blogs = $rpc->getResponse();
            if ( isset( $blogs['faultCode'] ) )
                return new IXR_Error( $blogs['faultCode'], $blogs['faultString'] );
            if ( $_SERVER['HTTP_HOST'] === $domain && $_SERVER['REQUEST_URI'] === $path )
                return $blogs;
            else {
                foreach ( (array) $blogs as $blog ) {
                    if ( strpos( $blog['url'], $_SERVER['HTTP_HOST'] ) ) return array( $blog );
                }
                return [];
            }
        }//4846
        public function blogger_getUserInfo( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_posts' ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to access user data on this site.' ) );
            $this->_do_action( 'xmlrpc_call', 'blogger.getUserInfo', $args, $this );
            $struct = ['nickname' => $user->nickname,'userid' => $user->ID,
                'url' => $user->user_url,'lastname' => $user->last_name,'firstname' => $user->first_name,];
            return $struct;
        }//4888
        public function blogger_getPost( $args ){
            $this->escape( $args );
            $post_ID  = (int) $args[1];
            $username = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $post_data = $this->_get_post( $post_ID, ARRAY_A );
            if ( ! $post_data ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_current_user_can( 'edit_post', $post_ID ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this post.' ) );
            $this->_do_action( 'xmlrpc_call', 'blogger.getPost', $args, $this );
            $categories = implode( ',', $this->_tp_get_post_categories( $post_ID ) );
            $content  = "<title>{$this->_tp_unslash( $post_data['post_title'] )}</title>";
            $content .= "<category>$categories</category>";
            $content .= $this->_tp_unslash( $post_data['post_content'] );
            $struct = ['userid' => $post_data['post_author'],'dateCreated' => $this->_convert_date( $post_data['post_date'] ),
                'content' => $content,'postid' => (string) $post_data['ID'],];
            return $struct;
        }//4932
        public function blogger_getRecentPosts( $args ){
            $this->escape( $args );
            $username = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[3];
            if ( isset( $args[4] ) ) $query = array( 'numberposts' => $this->_abs_int( $args[4] ) );
            else $query = [];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_posts' ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit posts.' ) );
            $this->_do_action( 'xmlrpc_call', 'blogger.getRecentPosts', $args, $this );
            $posts_list = $this->_tp_get_recent_posts( $query );
            if ( ! $posts_list ) {
                $this->error = new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Either there are no posts, or something went wrong.' ) );
                return $this->error;
            }
            $recent_posts = array();
            foreach ((array) $posts_list as $entry ) {
                if ( ! $this->_current_user_can( 'edit_post', $entry['ID'] ) ) continue;
                $post_date  = $this->_convert_date( $entry['post_date'] );
                $categories = implode( ',', $this->_tp_get_post_categories( $entry['ID'] ) );
                $content  = "<title>{$this->_tp_unslash( $entry['post_title'] )}</title>";
                $content .= "<category>$categories</category>";
                $content .= $this->_tp_unslash( $entry['post_content'] );
                $recent_posts[] = ['userid' => $entry['post_author'],'dateCreated' => $post_date,
                    'content' => $content,'postid' => (string) $entry['ID'],];
            }
            return $recent_posts;
        }//4988
        public function blogger_newPost( $args ){
            $this->escape( $args );
            $username = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[3];
            /** @noinspection MultiAssignmentUsageInspection */
            $content  = $args[4];
            /** @noinspection MultiAssignmentUsageInspection */
            $publish  = $args[5];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'blogger.newPost', $args, $this );
            $cap = ( $publish ) ? 'publish_posts' : 'edit_posts';
            if (! $this->_current_user_can( $cap ) || ! $this->_current_user_can( $this->_get_post_type_object( 'post' )->cap->create_posts ))
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to post on this site.' ) );
            $post_status = ( $publish ) ? 'publish' : 'draft';
            $post_author = $user->ID;
            $post_title    = $this->_xml_rpc_get_post_title( $content );
            $post_category = $this->_xml_rpc_get_post_category( $content );
            $post_content  = $this->_xml_rpc_remove_post_data( $content );
            $post_date     = $this->_current_time( 'mysql' );
            $post_date_gmt = $this->_current_time( 'mysql', 1 );
            $post_data = compact( 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_category', 'post_status' );
            $_post_ID = $this->_tp_insert_post( $post_data );
            $post_ID = null;
            if( $_post_ID instanceof TP_Error ){
                $post_ID = $_post_ID;
            }
            if ( $this->_init_error( $post_ID ) )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $post_ID->get_error_message() );
            if ( ! $post_ID ) return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, the post could not be created.' ) );
            $this->attach_uploads( $post_ID, $post_content );
            $this->_do_action( 'xmlrpc_call_success_blogger_newPost', $post_ID, $args );
            return $post_ID;
        }//5087
        public function blogger_editPost( $args ){
            $this->escape( $args );
            $post_ID  = (int) $args[1];
            $username = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[3];
            /** @noinspection MultiAssignmentUsageInspection */
            $content  = $args[4];
            /** @noinspection MultiAssignmentUsageInspection */
            $publish  = $args[5];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'blogger.editPost', $args, $this );
            $actual_post = $this->_get_post( $post_ID, ARRAY_A );
            if ( ! $actual_post || 'post' !== $actual_post['post_type'] )
                return new IXR_Error( NOT_FOUND, $this->__( 'Sorry, no such post.' ) );
            $this->escape( $actual_post );
            if ( ! $this->_current_user_can( 'edit_post', $post_ID ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this post.' ) );
            if ( 'publish' === $actual_post['post_status'] && ! $this->_current_user_can( 'publish_posts' ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to publish this post.' ) );
            $postdata                  = [];
            $postdata['ID']            = $actual_post['ID'];
            $postdata['post_content']  = $this->_xml_rpc_remove_post_data( $content );
            $postdata['post_title']    = $this->_xml_rpc_get_post_title( $content );
            $postdata['post_category'] = $this->_xml_rpc_get_post_category( $content );
            $postdata['post_status']   = $actual_post['post_status'];
            $postdata['post_excerpt']  = $actual_post['post_excerpt'];
            $postdata['post_status']   = $publish ? 'publish' : 'draft';
            $result = $this->_tp_update_post( $postdata );
            if ( ! $result ) return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, the post could not be updated.' ) );
            $this->attach_uploads( $actual_post['ID'], $postdata['post_content'] );
            $this->_do_action( 'xmlrpc_call_success_blogger_editPost', $post_ID, $args );
            return true;
        }//5162
        public function blogger_deletePost( $args ){
            $this->escape( $args );
            $post_ID  = (int) $args[1];
            $username = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'blogger.deletePost', $args, $this );
            $actual_post = $this->_get_post( $post_ID, ARRAY_A );
            if ( ! $actual_post || 'post' !== $actual_post['post_type'] )
                return new IXR_Error( NOT_FOUND, $this->__( 'Sorry, no such post.' ) );
            if ( ! $this->_current_user_can( 'delete_post', $post_ID ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to delete this post.' ) );
            $result = $this->_tp_delete_post( $post_ID );
            if ( ! $result ) return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, the post could not be deleted.' ) );
            $this->_do_action( 'xmlrpc_call_success_blogger_deletePost', $post_ID, $args );
            return true;
        }//5239
        public function mw_newPost( $args ){
            $this->escape( $args );
            $username       = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password       = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $content_struct = $args[3];
            $publish        = $args[4] ?? 0;
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'metaWeblog.newPost', $args, $this );
            $page_template = '';
            if ( ! empty( $content_struct['post_type'] ) ) {
                if ( 'page' === $content_struct['post_type'] ) {
                    if ( $publish ) $cap = 'publish_pages';
                    elseif ( isset( $content_struct['page_status'] ) && 'publish' === $content_struct['page_status'] )
                        $cap = 'publish_pages';
                    else $cap = 'edit_pages';
                    $error_message = $this->__( 'Sorry, you are not allowed to publish pages on this site.' );
                    $post_type     = 'page';
                    if ( ! empty( $content_struct['tp_page_template'] ) )
                        $page_template = $content_struct['tp_page_template'];
                } elseif ( 'post' === $content_struct['post_type'] ) {
                    if ( $publish ) $cap = 'publish_posts';
                    elseif ( isset( $content_struct['post_status'] ) && 'publish' === $content_struct['post_status'] )
                        $cap = 'publish_posts';
                    else $cap = 'edit_posts';
                    $error_message = $this->__( 'Sorry, you are not allowed to publish posts on this site.' );
                    $post_type     = 'post';
                } else return new IXR_Error( UNAUTHORIZED, $this->__( 'Invalid post type.' ) );
            } else {
                if ( $publish ) $cap = 'publish_posts';
                elseif ( isset( $content_struct['post_status'] ) && 'publish' === $content_struct['post_status'] )
                    $cap = 'publish_posts';
                else $cap = 'edit_posts';
                $error_message = $this->__( 'Sorry, you are not allowed to publish posts on this site.' );
                $post_type     = 'post';
            }
            $_post_type_object = $this->_get_post_type_object( $post_type );
            if ($_post_type_object instanceof TP_Post_Type && ! $this->_current_user_can( $_post_type_object->cap->create_posts ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to publish posts on this site.' ) );
            if ( ! $this->_current_user_can( $cap ) )
                return new IXR_Error( UNAUTHORIZED, $error_message );
            if ( isset( $content_struct['tp_post_format'] ) ) {
                $content_struct['tp_post_format'] = $this->_sanitize_key( $content_struct['tp_post_format'] );
                if ( ! array_key_exists( $content_struct['tp_post_format'], $this->_get_post_format_strings() ) )
                    return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post format.' ) );
            }
            $post_name = '';
            if ( isset( $content_struct['tp_slug'] ) ) $post_name = $content_struct['tp_slug'];
            if ( isset( $content_struct['tp_password'] ) ) $post_password = $content_struct['tp_password'];
            else $post_password = '';
            if ( isset( $content_struct['tp_page_parent_id'] ) ) $post_parent = $content_struct['tp_page_parent_id'];
            else $post_parent = 0;
            if ( isset( $content_struct['tp_page_order'] ) )
                $menu_order = $content_struct['tp_page_order'];
            else $menu_order = 0;
            $post_author = $user->ID;
            if ( isset( $content_struct['tp_author_id'] ) && ( $user->ID !== $content_struct['wp_author_id'] ) ) {
                switch ( $post_type ) {
                    case 'post':
                        if ( ! $this->_current_user_can( 'edit_others_posts' ) )
                            return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to create posts as this user.' ) );
                        break;
                    case 'page':
                        if ( ! $this->_current_user_can( 'edit_others_pages' ) )
                            return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to create pages as this user.' ) );
                        break;
                    default:
                        return new IXR_Error( UNAUTHORIZED, $this->__( 'Invalid post type.' ) );
                }
                $author = $this->_get_user_data( $content_struct['tp_author_id'] );
                if ( ! $author ) return new IXR_Error( UNAUTHORIZED, $this->__( 'Invalid author ID.' ) );
                $post_author = $content_struct['tp_author_id'];
            }
            $post_title   = $content_struct['title'] ?? null;
            $post_content = $content_struct['description'] ?? null;
            $post_status = $publish ? 'publish' : 'draft';
            if ( isset( $content_struct[ "{$post_type}_status" ] ) ) {
                switch ( $content_struct[ "{$post_type}_status" ] ) {
                    case 'draft':
                    case 'pending':
                    case 'private':
                    case 'publish':
                        $post_status = $content_struct[ "{$post_type}_status" ];
                        break;
                    default:
                        $post_status = $publish ? 'publish' : 'draft';
                        break;
                }
            }
            $post_excerpt = $content_struct['mt_excerpt'] ?? null;
            $post_more    = $content_struct['mt_text_more'] ?? null;
            $tags_input = $content_struct['mt_keywords'] ?? null;
            if ( isset( $content_struct['mt_allow_comments'] ) ) {
                if ( ! is_numeric( $content_struct['mt_allow_comments'] ) ) {
                    switch ( $content_struct['mt_allow_comments'] ) {
                        case 'closed':
                            $comment_status = 'closed';
                            break;
                        case 'open':
                            $comment_status = 'open';
                            break;
                        default:
                            $comment_status = $this->_get_default_comment_status( $post_type );
                            break;
                    }
                } else {
                    switch ( (int) $content_struct['mt_allow_comments'] ) {
                        case 0:
                        case 2:
                            $comment_status = 'closed';
                            break;
                        case 1:
                            $comment_status = 'open';
                            break;
                        default:
                            $comment_status = $this->_get_default_comment_status( $post_type );
                            break;
                    }
                }
            } else $comment_status = $this->_get_default_comment_status( $post_type );
            if ( isset( $content_struct['mt_allow_pings'] ) ) {
                if ( ! is_numeric( $content_struct['mt_allow_pings'] ) ) {
                    switch ( $content_struct['mt_allow_pings'] ) {
                        case 'closed':
                            $ping_status = 'closed';
                            break;
                        case 'open':
                            $ping_status = 'open';
                            break;
                        default:
                            $ping_status = $this->_get_default_comment_status( $post_type, 'pingback' );
                            break;
                    }
                } else {
                    switch ( (int) $content_struct['mt_allow_pings'] ) {
                        case 0:
                            $ping_status = 'closed';
                            break;
                        case 1:
                            $ping_status = 'open';
                            break;
                        default:
                            $ping_status = $this->_get_default_comment_status( $post_type, 'pingback' );
                            break;
                    }
                }
            } else $ping_status = $this->_get_default_comment_status( $post_type, 'pingback' );
            if ( $post_more ) $post_content .= $post_content . '<!--more-->' . $post_more;
            $to_ping = null;
            if ( isset( $content_struct['mt_tb_ping_urls'] ) ) {
                $to_ping = $content_struct['mt_tb_ping_urls'];
                if ( is_array( $to_ping ) ) $to_ping = implode( ' ', $to_ping );
            }
            if ( ! empty( $content_struct['date_created_gmt'] ) ) {
                if( $content_struct['date_created_gmt'] instanceof IXR_Date ){
                    $dateCreated = rtrim( $content_struct['date_created_gmt']->getIso(), 'Z' ) . 'Z';
                }
            } elseif ( ! empty( $content_struct['dateCreated'] ) ) {
                if( $content_struct['dateCreated'] instanceof IXR_Date ){
                    $dateCreated = $content_struct['dateCreated']->getIso();
                }
            }
            if ( ! empty( $dateCreated ) ) {
                $post_date     = $this->_iso8601_to_datetime( $dateCreated );
                $post_date_gmt = $this->_iso8601_to_datetime( $dateCreated, 'gmt' );
            } else {
                $post_date     = '';
                $post_date_gmt = '';
            }
            $post_category = array();
            if ( isset( $content_struct['categories'] ) ) {
                $catnames = $content_struct['categories'];
                if ( is_array( $catnames ) ) {
                    foreach ( $catnames as $cat ) $post_category[] = $this->_get_cat_ID( $cat );
                }
            }
            $postdata = compact( 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_category', 'post_status', 'post_excerpt', 'comment_status', 'ping_status', 'to_ping', 'post_type', 'post_name', 'post_password', 'post_parent', 'menu_order', 'tags_input', 'page_template' );
            $_post_id = $this->_get_default_post_to_edit( $post_type, true );
            $post_id= null;
            if($_post_id  instanceof  TP_Post){
                $post_id = $_post_id;
            }
            $post_ID        = $post_id->ID;
            $postdata['ID'] = $post_ID;
            if ( 'post' === $post_type && isset( $content_struct['sticky'] ) ) {
                $data           = $postdata;
                $data['sticky'] = $content_struct['sticky'];
                $error          = $this->__toggle_sticky( $data );
                if ( $error ) return $error;
            }
            if ( isset( $content_struct['custom_fields'] ) )
                $this->set_custom_fields( $post_ID, $content_struct['custom_fields'] );
            if ( isset( $content_struct['tp_post_thumbnail'] ) ) {
                if ( $this->_set_post_thumbnail( $post_ID, $content_struct['tp_post_thumbnail'] ) === false )
                    return new IXR_Error( NOT_FOUND, $this->__( 'Invalid attachment ID.' ) );
                unset( $content_struct['tp_post_thumbnail'] );
            }
            $thisEnclosure = $content_struct['enclosure'] ?? null;
            $this->add_enclosure_if_new( $post_ID, $thisEnclosure );
            $this->attach_uploads( $post_ID, $post_content );
            if ( isset( $content_struct['tp_post_format'] ) )
                $this->_set_post_format( $post_ID, $content_struct['tp_post_format'] );
            $post_ID = $this->_tp_insert_post( $postdata, true );
            if ($post_ID  instanceof TP_Error && $this->_init_error( $post_ID ) )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $post_ID->get_error_message() );
            if ( ! $post_ID )
                return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, the post could not be created.' ) );
            $this->_do_action( 'xmlrpc_call_success_mw_newPost', $post_ID, $args ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.NotLowercase
            return (string) $post_ID;
        }//5326
        public function add_enclosure_if_new( $post_ID, $enclosure ): void{
            if ( is_array( $enclosure ) && isset( $enclosure['url'], $enclosure['length'], $enclosure['type'] ) ) {
                $encstring  = $enclosure['url'] . "\n" . $enclosure['length'] . "\n" . $enclosure['type'] . "\n";
                $found      = false;
                $enclosures = $this->_get_post_meta( $post_ID, 'enclosure' );
                if ( $enclosures ) {
                    foreach ( $enclosures as $enc ) {
                        if ( rtrim( $enc, "\n" ) === rtrim( $encstring, "\n" ) ) {
                            $found = true;
                            break;
                        }
                    }
                }
                if ( ! $found ) $this->_add_post_meta( $post_ID, 'enclosure', $encstring );
            }
        }//5644
        public function attach_uploads( $post_ID, $post_content ): void{
            $tpdb = $this->_init_db();
            $attachments = $tpdb->get_results(TP_SELECT . " ID, guid FROM {$tpdb->posts} WHERE post_parent = '0' AND post_type = 'attachment'" );
            if ( is_array( $attachments ) ) {
                foreach ( $attachments as $file ) {
                    if ( ! empty( $file->guid ) && strpos( $post_content, $file->guid ) !== false )
                        $tpdb->update( $tpdb->posts, array( 'post_parent' => $post_ID ), array( 'ID' => $file->ID ) );
                }
            }
        }//5674
        public function mw_editPost( $args ){
            $this->escape( $args );
            $post_ID        = (int) $args[0];
            $username       = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password       = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $content_struct = $args[3];
            $publish        = $args[4] ?? 0;
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'metaWeblog.editPost', $args, $this );
            $postdata = $this->_get_post( $post_ID, ARRAY_A );
            if ( ! $postdata || empty( $postdata['ID'] ) )
                return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_current_user_can( 'edit_post', $post_ID ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this post.' ) );
            if ( ! in_array( $postdata['post_type'], array( 'post', 'page' ), true ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Invalid post type.' ) );
            if ( ! empty( $content_struct['post_type'] ) && ( $content_struct['post_type'] !== $postdata['post_type'] ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'The post type may not be changed.' ) );
            if ( isset( $content_struct['tp_post_format'] ) ) {
                $content_struct['tp_post_format'] = $this->_sanitize_key( $content_struct['tp_post_format'] );
                if ( ! array_key_exists( $content_struct['tp_post_format'], $this->_get_post_format_strings() ) )
                    return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post format.' ) );
            }
            $this->escape( $postdata );
            $ID             = $postdata['ID'];
            $post_content   = $postdata['post_content'];
            $post_title     = $postdata['post_title'];
            $post_excerpt   = $postdata['post_excerpt'];
            $post_password  = $postdata['post_password'];
            $post_parent    = $postdata['post_parent'];
            $post_type      = $postdata['post_type'];
            $menu_order     = $postdata['menu_order'];
            $ping_status    = $postdata['ping_status'];
            $comment_status = $postdata['comment_status'];
            $post_name = $postdata['post_name'];
            if ( isset( $content_struct['tp_slug'] ) )
                $post_name = $content_struct['tp_slug'];
            if ( isset( $content_struct['tp_password'] ) )
                $post_password = $content_struct['tp_password'];
            if ( isset( $content_struct['tp_page_parent_id'] ) )
                $post_parent = $content_struct['tp_page_parent_id'];
            if ( isset( $content_struct['tp_page_order'] ) )
                $menu_order = $content_struct['tp_page_order'];
            $page_template = null;
            if ( ! empty( $content_struct['tp_page_template'] ) && 'page' === $post_type )
                $page_template = $content_struct['tp_page_template'];
            $post_author = $postdata['post_author'];
            if ( isset( $content_struct['tp_author_id'] ) ) {
                if ( $user->ID !== $content_struct['tp_author_id'] || $user->ID !== $post_author ) {
                    switch ( $post_type ) {
                        case 'post':
                            if ( ! $this->_current_user_can( 'edit_others_posts' ) )
                                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to change the post author as this user.' ) );
                            break;
                        case 'page':
                            if ( ! $this->_current_user_can( 'edit_others_pages' ) )
                                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to change the page author as this user.' ) );
                            break;
                        default:
                            return new IXR_Error( UNAUTHORIZED, $this->__( 'Invalid post type.' ) );
                    }
                    $post_author = $content_struct['tp_author_id'];
                }
            }
            if ( isset( $content_struct['mt_allow_comments'] ) ) {
                if ( ! is_numeric( $content_struct['mt_allow_comments'] ) ) {
                    switch ( $content_struct['mt_allow_comments'] ) {
                        case 'closed':
                            $comment_status = 'closed';
                            break;
                        case 'open':
                            $comment_status = 'open';
                            break;
                        default:
                            $comment_status = $this->_get_default_comment_status( $post_type );
                            break;
                    }
                } else {
                    switch ( (int) $content_struct['mt_allow_comments'] ) {
                        case 0:
                        case 2:
                            $comment_status = 'closed';
                            break;
                        case 1:
                            $comment_status = 'open';
                            break;
                        default:
                            $comment_status = $this->_get_default_comment_status( $post_type );
                            break;
                    }
                }
            }
            if ( isset( $content_struct['mt_allow_pings'] ) ) {
                if ( ! is_numeric( $content_struct['mt_allow_pings'] ) ) {
                    switch ( $content_struct['mt_allow_pings'] ) {
                        case 'closed':
                            $ping_status = 'closed';
                            break;
                        case 'open':
                            $ping_status = 'open';
                            break;
                        default:
                            $ping_status = $this->_get_default_comment_status( $post_type, 'pingback' );
                            break;
                    }
                } else {
                    switch ( (int) $content_struct['mt_allow_pings'] ) {
                        case 0:
                            $ping_status = 'closed';
                            break;
                        case 1:
                            $ping_status = 'open';
                            break;
                        default:
                            $ping_status = $this->_get_default_comment_status( $post_type, 'pingback' );
                            break;
                    }
                }
            }
            if ( isset( $content_struct['title'] ) ) $post_title = $content_struct['title'];
            if ( isset( $content_struct['description'] ) )  $post_content = $content_struct['description'];
            $post_category = [];
            if ( isset( $content_struct['categories'] ) ) {
                $catnames = $content_struct['categories'];
                if ( is_array( $catnames ) ) {
                    foreach ( $catnames as $cat ) $post_category[] = $this->_get_cat_ID( $cat );
                }
            }
            if ( isset( $content_struct['mt_excerpt'] ) ) $post_excerpt = $content_struct['mt_excerpt'];
            $post_more = $content_struct['mt_text_more'] ?? null;
            $post_status = $publish ? 'publish' : 'draft';
            if ( isset( $content_struct[ "{$post_type}_status" ] ) ) {
                switch ( $content_struct[ "{$post_type}_status" ] ) {
                    case 'draft':
                    case 'pending':
                    case 'private':
                    case 'publish':
                        $post_status = $content_struct[ "{$post_type}_status" ];
                        break;
                    default:
                        $post_status = $publish ? 'publish' : 'draft';
                        break;
                }
            }
            $tags_input = $content_struct['mt_keywords'] ?? null;
            if ( 'publish' === $post_status || 'private' === $post_status ) {
                if ( 'page' === $post_type && ! $this->_current_user_can( 'publish_pages' ) ) {
                    return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to publish this page.' ) );
                }
                if ( ! $this->_current_user_can( 'publish_posts' ) ) {
                    return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to publish this post.' ) );
                }
            }
            if ( $post_more ) $post_content .= $post_content . '<!--more-->' . $post_more;
            $to_ping = null;
            if ( isset( $content_struct['mt_tb_ping_urls'] ) ) {
                $to_ping = $content_struct['mt_tb_ping_urls'];
                if ( is_array( $to_ping ) ) $to_ping = implode( ' ', $to_ping );
            }
            if ( ! empty( $content_struct['date_created_gmt'] ) ) {
                if( $content_struct['date_created_gmt'] instanceof IXR_Date ){}
                $dateCreated = rtrim( $content_struct['date_created_gmt']->getIso(), 'Z' ) . 'Z';
            } elseif ( ! empty( $content_struct['dateCreated'] ) ) {
                if( $content_struct['dateCreated'] instanceof IXR_Date ){
                    $dateCreated = $content_struct['dateCreated']->getIso();
                }

            }
            $edit_date = false;
            if ( ! empty( $dateCreated ) ) {
                $post_date     = $this->_iso8601_to_datetime( $dateCreated );
                $post_date_gmt = $this->_iso8601_to_datetime( $dateCreated, 'gmt' );
                $edit_date = true;
            } else {
                $post_date     = $postdata['post_date'];
                $post_date_gmt = $postdata['post_date_gmt'];
            }
            $newpost = compact( 'ID', 'post_content', 'post_title', 'post_category', 'post_status', 'post_excerpt', 'comment_status', 'ping_status', 'edit_date', 'post_date', 'post_date_gmt', 'to_ping', 'post_name', 'post_password', 'post_parent', 'menu_order', 'post_author', 'tags_input', 'page_template' );
            $result = $this->_tp_update_post( $newpost, true );
            if($result  instanceof TP_Error ){}
            if ( $this->_init_error( $result ) )  return new IXR_Error( INTERNAL_SERVER_ERROR, $result->get_error_message() );
            if ( ! $result ) return new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Sorry, the post could not be updated.' ) );
            if ( 'post' === $post_type && isset( $content_struct['sticky'] ) ) {
                $data              = $newpost;
                $data['sticky']    = $content_struct['sticky'];
                $data['post_type'] = 'post';
                $error             = $this->__toggle_sticky( $data, true );
                if ( $error ) return $error;
            }
            if ( isset( $content_struct['custom_fields'] ) )
                $this->set_custom_fields( $post_ID, $content_struct['custom_fields'] );
            if ( isset( $content_struct['tp_post_thumbnail'] ) ) {
                if ( empty( $content_struct['tp_post_thumbnail'] ) )
                    $this->_delete_post_thumbnail( $post_ID );
                else if ( $this->_set_post_thumbnail( $post_ID, $content_struct['tp_post_thumbnail'] ) === false )
                    return new IXR_Error( NOT_FOUND, $this->__( 'Invalid attachment ID.' ) );
                unset( $content_struct['tp_post_thumbnail'] );
            }
            $thisEnclosure = $content_struct['enclosure'] ?? null;
            $this->add_enclosure_if_new( $post_ID, $thisEnclosure );
            $this->attach_uploads( $ID, $post_content );
            if ( isset( $content_struct['tp_post_format'] ) )
                $this->_set_post_format( $post_ID, $content_struct['tp_post_format'] );
            $this->_do_action( 'xmlrpc_call_success_mw_editPost', $post_ID, $args );
            return true;
        }//5704
        public function mw_getPost( $args ){
            $this->escape( $args );
            $post_ID  = (int) $args[0];
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $postdata = $this->_get_post( $post_ID, ARRAY_A );
            if ( ! $postdata ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_current_user_can( 'edit_post', $post_ID ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this post.' ) );
            $this->_do_action( 'xmlrpc_call', 'metaWeblog.getPost', $args, $this );
            if ( '' !== $postdata['post_date'] ) {
                $post_date         = $this->_convert_date( $postdata['post_date'] );
                $post_date_gmt     = $this->_convert_date_gmt( $postdata['post_date_gmt'], $postdata['post_date'] );
                $post_modified     = $this->_convert_date( $postdata['post_modified'] );
                $post_modified_gmt = $this->_convert_date_gmt( $postdata['post_modified_gmt'], $postdata['post_modified'] );
                $categories = [];
                $catids     = $this->_tp_get_post_categories( $post_ID );
                foreach ( (array) $catids as $catid )  $categories[] = $this->_get_cat_name( $catid );
                $tagnames = [];
                $tags     = $this->_tp_get_post_tags( $post_ID );
                if ( ! empty( $tags ) ) {
                    foreach ((array) $tags as $tag ) $tagnames[] = $tag->name;
                    $tagnames = implode( ', ', $tagnames );
                } else  $tagnames = '';
                $post = $this->_get_extended( $postdata['post_content'] );
                $link = $this->_get_permalink( $postdata['ID'] );
                $_author = $this->_get_user_data( $postdata['post_author'] );
                $author = null;
                if($_author instanceof TP_Post || $_author instanceof \stdClass ){
                    $author = $_author;
                }
                $allow_comments = ( 'open' === $postdata['comment_status'] ) ? 1 : 0;
                $allow_pings    = ( 'open' === $postdata['ping_status'] ) ? 1 : 0;
                if ( 'future' === $postdata['post_status'] ) $postdata['post_status'] = 'publish';
                $post_format = $this->_get_post_format( $post_ID );
                if ( empty( $post_format ) ) $post_format = 'standard';
                $sticky = false;
                if ( $this->_is_sticky( $post_ID ) ) $sticky = true;
                $enclosure = [];
                $encdata = [];
                foreach ( (array) $this->_get_post_custom( $post_ID ) as $key => $val ) {
                    if ( 'enclosure' === $key ) {
                        /** @noinspection LoopWhichDoesNotLoopInspection */ //fixme don't get this wp thing right
                        foreach ($val as $enc ) {
                            $encdata[]           = explode( "\n", $enc );
                            $enclosure['url']    = trim( htmlspecialchars( $encdata[0] ) );
                            $enclosure['length'] = (int) trim( $encdata[1] );
                            $enclosure['type']   = trim( $encdata[2] );
                            break 2;
                        }
                    }
                }
                $resp = ['dateCreated' => $post_date,'userid' => $postdata['post_author'],'postid' => $postdata['ID'],
                    'description' => $post['main'],'title' => $postdata['post_title'],'link' => $link,'permaLink' => $link,
                    'categories' => $categories,'mt_excerpt' => $postdata['post_excerpt'],'mt_text_more' => $post['extended'],
                    'tp_more_text' => $post['more_text'],'mt_allow_comments' => $allow_comments,'mt_allow_pings' => $allow_pings,
                    'mt_keywords' => $tagnames,'tp_slug' => $postdata['post_name'],'tp_password' => $postdata['post_password'],
                    'tp_author_id' => (string) $author->ID,'tp_author_display_name' => $author->display_name,'date_created_gmt' => $post_date_gmt,
                    'post_status' => $postdata['post_status'],'custom_fields' => $this->get_custom_fields( $post_ID ),'tp_post_format' => $post_format,
                    'sticky' => $sticky,'date_modified' => $post_modified,'date_modified_gmt' => $post_modified_gmt,
                ];
                if ( ! empty( $enclosure ) ) $resp['enclosure'] = $enclosure;
                $resp['tp_post_thumbnail'] = $this->_get_post_thumbnail_id( $postdata['ID'] );
                return $resp;
            } else  return new IXR_Error( NOT_FOUND, $this->__( 'Sorry, no such post.' ) );
        }//6036
        public function mw_getRecentPosts( $args ): array{
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            if ( isset( $args[3] ) ) $query = array( 'numberposts' => $this->_abs_int( $args[3] ) );
            else $query = array();
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_posts' ) )
                return (string) new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit posts.' ) );
            $this->_do_action( 'xmlrpc_call', 'metaWeblog.getRecentPosts', $args, $this );
            $posts_list = $this->_tp_get_recent_posts( $query );
            if ( ! $posts_list ) return [];
            $recent_posts = array();
            foreach ((array) $posts_list as $entry ) {
                if ( ! $this->_current_user_can( 'edit_post', $entry['ID'] ) ) continue;
                $post_date         = $this->_convert_date( $entry['post_date'] );
                $post_date_gmt     = $this->_convert_date_gmt( $entry['post_date_gmt'], $entry['post_date'] );
                $post_modified     = $this->_convert_date( $entry['post_modified'] );
                $post_modified_gmt = $this->_convert_date_gmt( $entry['post_modified_gmt'], $entry['post_modified'] );
                $categories = [];
                $catids     = $this->_tp_get_post_categories( $entry['ID'] );
                foreach ( (array)$catids as $catid ) $categories[] = $this->_get_cat_name( $catid );
                $tagnames = array();
                $tags     = $this->_tp_get_post_tags( $entry['ID'] );
                if ( ! empty( $tags ) ) {
                    foreach ((array) $tags as $tag ) $tagnames[] = $tag->name;
                    $tagnames = implode( ', ', $tagnames );
                } else $tagnames = '';
                $post = $this->_get_extended( $entry['post_content'] );
                $link = $this->_get_permalink( $entry['ID'] );
                $_author = $this->_get_user_data( $entry['post_author'] );
                $author = null;
                if($_author instanceof TP_Post || $_author instanceof \stdClass ){
                    $author = $_author;
                }
                $allow_comments = ( 'open' === $entry['comment_status'] ) ? 1 : 0;
                $allow_pings    = ( 'open' === $entry['ping_status'] ) ? 1 : 0;
                if ( 'future' === $entry['post_status'] )
                    $entry['post_status'] = 'publish';
                $post_format = $this->_get_post_format( $entry['ID'] );
                if ( empty( $post_format ) )
                    $post_format = 'standard';
                $recent_posts[] = ['dateCreated' => $post_date,'userid' => $entry['post_author'],'postid' => (string) $entry['ID'],
                    'description' => $post['main'],'title' => $entry['post_title'],'link' => $link,
                    'permaLink' => $link,'categories' => $categories,'mt_excerpt' => $entry['post_excerpt'],
                    'mt_text_more' => $post['extended'],'tp_more_text' => $post['more_text'],'mt_allow_comments' => $allow_comments,
                    'mt_allow_pings' => $allow_pings,'mt_keywords' => $tagnames,'tp_slug' => $entry['post_name'],
                    'tp_password' => $entry['post_password'],'tp_author_id' => (string) $author->ID,'tp_author_display_name' => $author->display_name,
                    'date_created_gmt' => $post_date_gmt,'post_status' => $entry['post_status'],'custom_fields' => $this->get_custom_fields( $entry['ID'] ),
                    'tp_post_format' => $post_format,'date_modified' => $post_modified,'date_modified_gmt' => $post_modified_gmt,
                    'sticky' => ( 'post' === $entry['post_type'] && $this->_is_sticky( $entry['ID'] ) ), 'tp_post_thumbnail' => $this->_get_post_thumbnail_id( $entry['ID'] ),
                ];
            }
            return $recent_posts;
        }//6177
        public function mw_getCategories( $args ): array {
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user )  return $this->error;
            if ( ! $this->_current_user_can( 'edit_posts' ) )
                return (string) new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you must be able to edit posts on this site in order to view categories.' ) );
            $this->_do_action( 'xmlrpc_call', 'metaWeblog.getCategories', $args, $this );
            $categories_struct = array();
            $cats = $this->_get_categories( array( 'get' => 'all' ) );
            if ( $cats ) {
                foreach ( $cats as $cat ) {
                    $struct                        = [];
                    $struct['categoryId']          = $cat->term_id;
                    $struct['parentId']            = $cat->parent;
                    $struct['description']         = $cat->name;
                    $struct['categoryDescription'] = $cat->description;
                    $struct['categoryName']        = $cat->name;
                    $struct['htmlUrl']             = $this->_esc_html( $this->_get_category_link( $cat->term_id ) );
                    $struct['rssUrl']              = $this->_esc_html( $this->_get_category_feed_link( $cat->term_id, 'rss2' ) );
                    $categories_struct[] = $struct;
                }
            }
            return $categories_struct;
        }//6303
        public function mw_newMediaObject( $args ){
            $this->_init_db();//or this make sense?
            $username = $this->escape( $args[1] );
            $password = $this->escape( $args[2] );
            $data     = $args[3];
            $name = $this->_sanitize_file_name( $data['name'] );
            $type = $data['type'];
            $bits = $data['bits'];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'metaWeblog.newMediaObject', $args, $this );
            if ( ! $this->_current_user_can( 'upload_files' ) ) {
                $this->error = new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to upload files.' ) );
                return $this->error;
            }
            if ( $this->_is_multisite() && $this->_upload_is_user_over_quota( false ) ) {
                $this->error = new IXR_Error(
                    UNAUTHORIZED,/* translators: %s: Allowed space allocation. */
                    sprintf($this->__( 'Sorry, you have used your space allocation of %s. Please delete some files to upload more files.' ),
                        $this->_size_format( $this->_get_space_allowed() * MB_IN_BYTES )));
                return $this->error;
            }
            $upload_err = $this->_apply_filters( 'pre_upload_error', false );
            if ( $upload_err ) return new IXR_Error( INTERNAL_SERVER_ERROR, $upload_err );
            $upload = $this->_tp_upload_bits( $name, null, $bits );
            if ( ! empty( $upload['error'] ) ) { /* translators: 1: File name, 2: Error message. */
                $errorString = sprintf( $this->__( 'Could not write file %1$s (%2$s).' ), $name, $upload['error'] );
                return new IXR_Error( INTERNAL_SERVER_ERROR, $errorString );
            }
            $post_id = 0;
            if ( ! empty( $data['post_id'] ) ) {
                $post_id = (int) $data['post_id'];
                if ( ! $this->_current_user_can( 'edit_post', $post_id ) )
                    return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this post.' ) );
            }
            $attachment = ['post_title' => $name,'post_content' => '','post_type' => 'attachment',
                'post_parent' => $post_id,'post_mime_type' => $type,'guid' => $upload['url'],];
            $id = $this->_tp_insert_attachment( $attachment, $upload['file'], $post_id );
            $this->_tp_update_attachment_metadata( $id, $this->_tp_generate_attachment_metadata( $id, $upload['file'] ) );
            $this->_do_action( 'xmlrpc_call_success_mw_newMediaObject', $id, $args );
            return $this->_prepare_media_item( $this->_get_post( $id ) );

        }//6363
        public function mt_getRecentPostTitles( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            if ( isset( $args[3])) $query = ['numberposts' => $this->_abs_int( $args[3] )];
            else $query = [];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'mt.getRecentPostTitles', $args, $this );
            $posts_list = $this->_tp_get_recent_posts( $query );
            if ( ! $posts_list ) {
                $this->error = new IXR_Error( INTERNAL_SERVER_ERROR, $this->__( 'Either there are no posts, or something went wrong.' ) );
                return $this->error;
            }
            $recent_posts = [];
            foreach ( (array)$posts_list as $entry ) {
                if ( ! $this->_current_user_can( 'edit_post', $entry['ID'] ) ) continue;
                $post_date     = $this->_convert_date( $entry['post_date'] );
                $post_date_gmt = $this->_convert_date_gmt( $entry['post_date_gmt'], $entry['post_date'] );
                $recent_posts[] = ['dateCreated' => $post_date,'userid' => $entry['post_author'],'postid' => (string) $entry['ID'],
                    'title' => $entry['post_title'],'post_status' => $entry['post_status'],'date_created_gmt' => $post_date_gmt,];
            }
            return $recent_posts;
        }//6482
        public function mt_getCategoryList( $args ){
            $this->escape( $args );
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_current_user_can( 'edit_posts' ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you must be able to edit posts on this site in order to view categories.' ) );
            $this->_do_action( 'xmlrpc_call', 'mt.getCategoryList', $args, $this );
            $categories_struct = [];
            $cats = $this->_get_categories( ['hide_empty' => 0, 'hierarchical' => 0,] );
            if ( $cats ) {
                foreach ( $cats as $cat ) {
                    $struct                 = [];
                    $struct['categoryId']   = $cat->term_id;
                    $struct['categoryName'] = $cat->name;
                    $categories_struct[] = $struct;
                }
            }
            return $categories_struct;
        }//6545
        public function mt_getPostCategories( $args ){
            $this->escape( $args );
            $post_ID  = (int) $args[0];
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            if ( ! $this->_get_post( $post_ID ) )
                return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_current_user_can( 'edit_post', $post_ID ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this post.' ) );
            $this->_do_action( 'xmlrpc_call', 'mt.getPostCategories', $args, $this );
            $categories = [];
            $catids     = $this->_tp_get_post_categories($post_ID );
            $isPrimary = true;
            foreach ((array) $catids as $catid ) {
                $categories[] = ['categoryName' => $this->_get_cat_name( $catid ),
                    'categoryId' => (string) $catid,'isPrimary' => $isPrimary,];
                $isPrimary    = false;
            }
            return $categories;
        }//6598
        public function mt_setPostCategories( $args ){
            $this->escape( $args );
            $post_ID    = (int) $args[0];
            $username   = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password   = $args[2];
            /** @noinspection MultiAssignmentUsageInspection */
            $categories = $args[3];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'mt.setPostCategories', $args, $this );
            if ( ! $this->_get_post( $post_ID ) )
                return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_current_user_can( 'edit_post', $post_ID ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to edit this post.' ) );
            $catids = [];
            foreach ( $categories as $cat ) $catids[] = $cat['categoryId'];
            $this->_tp_set_post_categories( $post_ID, $catids );
            return true;
        }//6652
        public function mt_supportedMethods(){
            $this->_do_action( 'xmlrpc_call', 'mt.supportedMethods', array(), $this );
            return array_keys( $this->methods );
        }//6693
        public function mt_supportedTextFilters(){
            $this->_do_action( 'xmlrpc_call', 'mt.supportedTextFilters', array(), $this );
            return $this->_apply_filters( 'xmlrpc_text_filters', array() );
        }//6705
        public function mt_getTrackbackPings( $post_ID ){
            $tpdb = $this->_init_db();
            $this->_do_action( 'xmlrpc_call', 'mt.getTrackbackPings', $post_ID, $this );
            $actual_post = $this->_get_post( $post_ID, ARRAY_A );
            if ( ! $actual_post ) return new IXR_Error( 404, $this->__( 'Sorry, no such post.' ) );
            $comments = $tpdb->get_results( $tpdb->prepare( TP_SELECT . " comment_author_url, comment_content, comment_author_IP, comment_type FROM $tpdb->comments WHERE comment_post_ID = %d", $post_ID ) );
            if ( ! $comments ) return [];
            $trackback_pings = [];
            foreach ( $comments as $comment ) {
                if ( 'trackback' === $comment->comment_type ) {
                    $content           = $comment->comment_content;
                    $title             = substr( $content, 8, ( strpos( $content, '</strong>' ) - 8 ) );
                    $trackback_pings[] = ['pingTitle' => $title,'pingURL' => $comment->comment_author_url,
                        'pingIP' => $comment->comment_author_IP,];
                }
            }
            return $trackback_pings;
        }//6729
        public function mt_publishPost( ...$args ){
            $this->escape( $args );
            $post_ID  = (int) $args[0];
            $username = $args[1];
            /** @noinspection MultiAssignmentUsageInspection */
            $password = $args[2];
            $user = $this->login( $username, $password );
            if ( ! $user ) return $this->error;
            $this->_do_action( 'xmlrpc_call', 'mt.publishPost', $args, $this );
            $postdata = $this->_get_post( $post_ID, ARRAY_A );
            if ( ! $postdata ) return new IXR_Error( NOT_FOUND, $this->__( 'Invalid post ID.' ) );
            if ( ! $this->_current_user_can( 'publish_posts' ) || ! $this->_current_user_can( 'edit_post', $post_ID ) )
                return new IXR_Error( UNAUTHORIZED, $this->__( 'Sorry, you are not allowed to publish this post.' ) );
            $postdata['post_status'] = 'publish';
            $postdata['post_category'] = $this->_tp_get_post_categories( $post_ID );
            $this->escape( $postdata );
            return $this->_tp_update_post( $postdata );
        }//6777
        public function pingback_ping( $args ){
            $tpdb = $this->_init_db();
            $this->_do_action( 'xmlrpc_call', 'pingback.ping', $args, $this );
            $this->escape( $args );
            $pagelinkedfrom = str_replace( '&amp;', '&', $args[0] );
            $pagelinkedto   = str_replace(array('&amp;', '&'), array('&', '&amp;'), $args[1]);
            $pagelinkedfrom = $this->_apply_filters( 'pingback_ping_source_uri', $pagelinkedfrom, $pagelinkedto );
            if ( ! $pagelinkedfrom ) return $this->_pingback_error( 0, $this->__( 'A valid URL was not provided.' ) );
            $pos1 = strpos( $pagelinkedto, str_replace( array( 'http://www.', 'http://', 'https://www.', 'https://' ), '', $this->_get_option( 'home' ) ) );
            if ( ! $pos1 ) return $this->_pingback_error( 0, $this->__( 'Is there no link to us?' ) );
            $urltest = parse_url( $pagelinkedto );
            $post_ID = $this->_url_to_postid( $pagelinkedto );
            if ( $post_ID ) {
                // $way
            } elseif ( isset( $urltest['path'] ) && preg_match( '#p/[\d]{1,}#', $urltest['path'], $match ) ) {
                $blah    = explode( '/', $match[0] );
                $post_ID = (int) $blah[1];
            } elseif ( isset( $urltest['query'] ) && preg_match( '#p=[\d]{1,}#', $urltest['query'], $match ) ) {
                $blah    = explode( '=', $match[0] );
                $post_ID = (int) $blah[1];
            } elseif ( isset( $urltest['fragment'] ) ) {
                if ( (int) $urltest['fragment'] ) {
                    $post_ID = (int) $urltest['fragment'];
                } elseif ( preg_match( '/post-[\d]+/', $urltest['fragment'] ) ) {
                    $post_ID = preg_replace( '/[^\D]+/', '', $urltest['fragment'] );
                } elseif ( is_string( $urltest['fragment'] ) ) {
                    $title   = preg_replace( '/[^a-z0-9]/i', '.', $urltest['fragment'] );
                    $sql     = $tpdb->prepare( TP_SELECT . " ID FROM $tpdb->posts WHERE post_title RLIKE %s", $title );
                    $post_ID = $tpdb->get_var( $sql );
                    if ( ! $post_ID ) return $this->_pingback_error( 0, '' );
                }
            }else {
                return $this->_pingback_error( 33, $this->__( 'The specified target URL cannot be used as a target. It either doesn&#8217;t exist, or it is not a pingback-enabled resource.' ) );
            }
            $post_ID = (int) $post_ID;
            $post = $this->_get_post( $post_ID );
            if ( ! $post )
                return $this->_pingback_error( 33, $this->__( 'The specified target URL cannot be used as a target. It either doesn&#8217;t exist, or it is not a pingback-enabled resource.' ) );
            if ( $this->_url_to_postid( $pagelinkedfrom ) === $post_ID )
                return $this->_pingback_error( 0, $this->__( 'The source URL and the target URL cannot both point to the same resource.' ) );
            if ( ! $this->_pings_open( $post ) )
                return $this->_pingback_error( 33, $this->__( 'The specified target URL cannot be used as a target. It either doesn&#8217;t exist, or it is not a pingback-enabled resource.' ) );
            if ( $tpdb->get_results( $tpdb->prepare( TP_SELECT . " * FROM $tpdb->comments WHERE comment_post_ID = %d AND comment_author_url = %s", $post_ID, $pagelinkedfrom ) ) )
                return $this->_pingback_error( 48, $this->__( 'The pingback has already been registered.' ) );
            sleep( 1 );
            $remote_ip = preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] );
            $user_agent = $this->_apply_filters( 'http_headers_useragent', 'TailoredPress/' . $this->_get_bloginfo( 'version' ) . '; ' . $this->_get_bloginfo( 'url' ), $pagelinkedfrom );
            $http_api_args = array(
                'timeout'             => 10,
                'redirection'         => 0,
                'limit_response_size' => 153600, // 150 KB
                'user-agent'          => "$user_agent; verifying pingback from $remote_ip",
                'headers'             => array(
                    'X-Pingback-Forwarded-For' => $remote_ip,
                ),
            );
            $request                = $this->_tp_safe_remote_get( $pagelinkedfrom, $http_api_args );
            $remote_source          = $this->_tp_remote_retrieve_body( $request );
            $remote_source_original = $remote_source;
            if ( ! $remote_source )
                return $this->_pingback_error( 16, $this->__( 'The source URL does not exist.' ) );
            $remote_source = $this->_apply_filters( 'pre_remote_source', $remote_source, $pagelinkedto );
            // Work around bug in strip_tags():
            $remote_source = str_replace( '<!DOC', '<DOC', $remote_source );
            $remote_source = preg_replace( '/[\r\n\t ]+/', ' ', $remote_source ); // normalize spaces
            $remote_source = preg_replace( '/<\/*(h1|h2|h3|h4|h5|h6|p|th|td|li|dt|dd|pre|caption|input|textarea|button|body)[^>]*>/', "\n\n", $remote_source );
            preg_match( '|<title>([^<]*.?)</title>|is', $remote_source, $matchtitle );
            $title = $matchtitle[1] ?? '';
            if ( empty( $title ) )
                return $this->_pingback_error( 32, $this->__( 'We cannot find a title on that page.' ) );
            $remote_source = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $remote_source );
            $remote_source = strip_tags( $remote_source, '<a>' );
            $p = explode( "\n\n", $remote_source );
            $preg_target = preg_quote( $pagelinkedto, '|' );
            $excerpt ='';
            foreach ( $p as $para ) {
                if ( strpos( $para, $pagelinkedto ) !== false ) { // It exists, but is it a link?
                    preg_match( '|<a[^>]+?' . $preg_target . '[^>]*>([^>]+?)</a>|', $para, $context );
                    if ( empty( $context ) ) continue;
                    $excerpt = preg_replace( '|\</?tpcontext\>|', '', $para );
                    if ( strlen( $context[1] ) > 100 )
                        $context[1] = substr( $context[1], 0, 100 ) . '&#8230;';
                    $marker      = "<tpcontext>{$context[1]}</tpcontext>";
                    $excerpt     = str_replace( $context[0], $marker, $excerpt ); // Swap out the link for our marker.
                    $excerpt     = strip_tags( $excerpt, '<tpcontext>' );         // Strip all tags but our context marker.
                    $excerpt     = trim( $excerpt );
                    $preg_marker = preg_quote( $marker, '|' );
                    $excerpt     = preg_replace( "|.*?\s(.{0,100}$preg_marker.{0,100})\s.*|s", '$1', $excerpt );
                    $excerpt     = strip_tags( $excerpt ); // YES, again, to remove the marker wrapper.
                    break;
                }
            }
            if ( empty( $context ) )// Link to target not found.
                return $this->_pingback_error( 17, $this->__( 'The source URL does not contain a link to the target URL, and so cannot be used as a source.' ) );
            $pagelinkedfrom = str_replace( '&', '&amp;', $pagelinkedfrom );
            $context        = '[&#8230;] ' . $this->_esc_html( $excerpt ) . ' [&#8230;]';
            $pagelinkedfrom = $this->escape( $pagelinkedfrom );
            $comment_post_ID      = $post_ID;
            $comment_author       = $title;
            $comment_author_email = '';
            $this->escape( $comment_author );
            $comment_author_url = $pagelinkedfrom;
            $comment_content    = $context;
            $this->escape( $comment_content );
            $comment_type = 'pingback';
            $commentdata = compact(
                'comment_post_ID','comment_author','comment_author_url','comment_author_email',
                'comment_content','comment_type','remote_source','remote_source_original'
            );
            $comment_ID = $this->_tp_new_comment( $commentdata );
            if ( $comment_ID instanceof TP_Error && $this->_init_error( $comment_ID ) )
                return $this->_pingback_error( 0, $comment_ID->get_error_message() );
            $this->_do_action( 'pingback_post', $comment_ID );
            return sprintf( $this->__( 'Pingback from %1$s to %2$s registered. Keep the web talking! :-)' ), $pagelinkedfrom, $pagelinkedto );
        }//6828
        public function pingback_extensions_getPingbacks( $url ){
            $tpdb = $this->_init_db();
            $this->_do_action( 'xmlrpc_call', 'pingback.extensions.getPingbacks', $url, $this );
            $url = $this->escape( $url );
            $post_ID = $this->_url_to_postid( $url );
            if ( ! $post_ID )
                return $this->_pingback_error( 33, $this->__( 'The specified target URL cannot be used as a target. It either doesn&#8217;t exist, or it is not a pingback-enabled resource.' ) );
            $actual_post = $this->_get_post( $post_ID, ARRAY_A );
            if ( ! $actual_post )
                return $this->_pingback_error( 32, $this->__( 'The specified target URL does not exist.' ) );
            $comments = $tpdb->get_results( $tpdb->prepare( TP_SELECT . " comment_author_url, comment_content, comment_author_IP, comment_type FROM $tpdb->comments WHERE comment_post_ID = %d", $post_ID ) );
            if ( ! $comments ) return [];
            $pingbacks = [];
            foreach ( $comments as $comment ) {
                if ( 'pingback' === $comment->comment_type ) $pingbacks[] = $comment->comment_author_url;
            }
            return $pingbacks;
        }//7067
        protected function _pingback_error( $code, $message ){
            return $this->_apply_filters( 'xmlrpc_pingback_error', new IXR_Error( $code, $message ) );
        }//7113
    }
}else die;