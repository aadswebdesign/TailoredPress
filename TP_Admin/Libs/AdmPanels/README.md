### TP_Admin/Libs/AdmPanels

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:** 

- Adm_Index_Panel.php: extends Admins, used by TP_Admin/index.php 	
	* protected $_args 
	* private $__screen 
	* __construct($downstream_args = null ,$args = null) 
	* __info_setup():void 
	* __welcome_panel():string 
	* __to_string():string 
	* __toString() 

- Adm_Options_General_Panel.php: extends Admins, used by TP_Admin/options_general.php 	
	* private $__tp_site_url_class,$__tp_home_class,$__new_admin_email 
	* protected $_args 
	* __construct($args =null) 
	* __get_options_general_pre():string 
	* __get_options_general_help() 
	* __get_language_setup():string 
	* __get_timezone_setup():string 
	* __get_date_format_setup():string 
	* __get_time_format_setup():string 
	* __get_start_of_week():string 
	* __to_string():string 
	* __toString() 

- Adm_Test_Panel.php: extends Admins 	
	* is a temporary class and will be moved to the developer branch later on. 

- Adm_Upgrade_Panel.php: extends Admins, used by TP_Admin/upgrade.php //todo	
	* protected $_args 
	* __construct($args = null) 
	* __to_string():string 
	* __toString() 
	*  
	*  
	*  
	*  
	*  
	*  
