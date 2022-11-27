<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-10-2022
 * Time: 13:32
 */
namespace TP_Admin\Traits\AdminMultiSite;
if(ABSPATH){
    trait _adm_multisite_hooks{
        /**
         * @description Multisite Administration hooks
         */
        protected function _ms_admin_filters_hooks():void{
            $this->_add_filter( 'tp_handle_upload_prefilter', [$this,'check_upload_size'] );//return
            // User hooks.
            $this->_add_action( 'user_admin_notices',[$this,'get_new_user_email_admin_notice']);//return
            $this->_add_action( 'user_admin_notices',[$this,'new_user_email_admin_notice']);//void
            $this->_add_action( 'network_admin_notices',[$this,'get_new_user_email_admin_notice']);//return
            $this->_add_action( 'network_admin_notices',[$this,'new_user_email_admin_notice']);//void
            $this->_add_action( 'admin_page_access_denied', [$this,'access_denied_splash'], 99 );//void
            // Site hooks.
            $this->_add_action( 'tp_mu_edit_blog_action', [$this,'get_upload_space_setting'] );//return
            $this->_add_action( 'tp_mu_edit_blog_action', [$this,'upload_space_setting'] );//void
            // Network hooks.
            $this->_add_action( 'update_site_option_admin_email', [$this,'tp_network_admin_email_change_notification'], 10, 4 );//void
            // Taxonomy hooks.
            $this->_add_filter( 'get_term',[$this,'sync_category_tag_slugs'], 10, 2 );//return
            // Post hooks.
            $this->_add_filter( 'tp_insert_post_data', [$this,'avoid_blog_page_permalink_collision'], 10, 2 );//return
            // Tools hooks.
            $this->_add_filter( 'import_allow_create_users', [$this,'check_import_new_users'] );//return
            // Notices hooks.
            $this->_add_action( 'admin_notices', [$this,'get_site_admin_notice'] );//return
            $this->_add_action( 'admin_notices', [$this,'site_admin_notice'] );//void
            $this->_add_action( 'network_admin_notices', [$this,'get_site_admin_notice'] );//return
            $this->_add_action( 'network_admin_notices', [$this,'site_admin_notice']);//void
            // Update hooks.
            //$this->_add_action( 'network_admin_notices',[$this,'_get_update_nag'] , 3 );//return
            $this->_add_action( 'network_admin_notices',[$this,'update_nag'] , 3 );//void
            //$this->_add_action( 'network_admin_notices',[$this,'_get_maintenance_nag'] ,10 );//return
            $this->_add_action( 'network_admin_notices',[$this,'maintenance_nag'] ,10 );//void
            // Network Admin hooks.
            $this->_add_action( 'add_site_option_new_admin_email',[$this,'update_network_option_new_admin_email'], 10, 2 );//void
            $this->_add_action( 'update_site_option_new_admin_email',[$this,'update_network_option_new_admin_email'], 10, 2 );//void
        }//from ms-admin-filters.php
    }
}else{die;}