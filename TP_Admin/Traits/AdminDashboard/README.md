### TP_Admin/Traits/AdminDashboard

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_dashboard_01.php: 	
	* _tp_get_dashboard_setup():string 
	* _tp_get_dashboard_module( $module_id, $module_name, $callback, $control_callback = null, $callback_args = null, $context = 'normal', $priority = 'core' ) 
	* _tp_add_dashboard_module( $module_id, $module_name, $callback, $control_callback = null, $callback_args = null, $context = 'normal', $priority = 'core' ):void 
	* _tp_get_dashboard_control_callback( $dashboard, $meta_box ):string 
	* _tp_get_dashboard() 
	* _tp_get_dashboard_right_now() 
	* _tp_get_network_dashboard_right_now() 
	* _tp_get_dashboard_quick_press( $error_msg = false ) 
	* _tp_get_dashboard_recent_drafts($drafts = false ) 
	* _tp_dashboard_get_recent_comments_row(&$comment, $show_date = true )
	* _tp_get_dashboard_site_activity():string 
	
- _adm_dashboard_02.php: 	
	* _tp_get_dashboard_recent_posts( ...$args ) 
	* _tp_get_dashboard_recent_comments( $total_items = 5 ) 
	* _tp_get_dashboard_trigger_module_control( $module_control_id = false ) 
	* _tp_dashboard_trigger_module_control( $module_control_id = false ):void 
	* _tp_get_dashboard_events_news():string 
	* _tp_get_community_events_markup():string 
	* _tp_get_dashboard_quota() 
	* _tp_get_dashboard_browser_nag() 
	
- _adm_dashboard_03.php: 	
	* tp_get_dashboard_browser_nag_class( $classes ):array 
	* _tp_check_browser_version() 
	* _tp_get_dashboard_php_nag() 
	* _dashboard_get_php_nag_class( $classes ):string 
	* _tp_get_dashboard_site_health() 
	* _tp_welcome_panel():string //todo 
