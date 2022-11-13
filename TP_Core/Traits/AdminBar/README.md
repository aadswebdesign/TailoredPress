### TP_Core/Traits/

**Note:** For what it is now and subject to change. Also one of my habbits is to give classes/methods a name that speaks for itself.

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods
- _admin_bar_01.php: 	
	* _tp_admin_bar_init():bool 
	* _tp_admin_bar_render():bool 
	* _tp_admin_bar_wp_menu(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_sidebar_toggle(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_my_account_item(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_my_account_menu(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_site_menu(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_edit_site_menu(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_customize_menu(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_my_sites_menu(TP_Admin_Bar $tp_admin_bar ):void 

- _admin_bar_02.php: 	
	* _tp_admin_bar_shortlink_menu(TP_Admin_Bar $tp_admin_bar ) 
	* _tp_admin_bar_edit_menu(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_new_content_menu(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_comments_menu(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_appearance_menu(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_updates_menu(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_search_menu(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_recovery_mode_menu(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_admin_bar_add_secondary_groups(TP_Admin_Bar $tp_admin_bar ):void 
	* _tp_get_admin_bar_header():string
	* _tp_admin_bar_header():void
	
- _admin_bar_03.php: 	
	* _get_admin_bar_bump_cb():string 
	* _admin_bar_bump_cb():void 
	* _show_admin_bar( $show ):void 
	* _is_admin_bar_showing():bool 
	* _get_admin_bar_pref( $context = 'front', $user = 0 ):bool 

