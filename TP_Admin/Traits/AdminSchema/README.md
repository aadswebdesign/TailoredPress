### TP_Admin/Traits/AdminSchema

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** Each Trait has about 10 methods

- _adm_schema_01.php: 	
	* _tp_get_db_schema( $scope = 'all', $blog_id = null ):array 
	* _populate_options(array ...$options):void 
	* _populate_roles():void 
	* __populate_roles_admin():void 
	* __populate_roles_editor():void 
	* __populate_roles_admin_editor():void 
	* __populate_roles_author():void 
	* __populate_roles_contributor():void 
	* __populate_roles_subscriber():void 

- _adm_schema_02.php: 	
	* _install_network():void 
	* _populate_network( $network_id = 1, $domain = '', $email = '', $site_name = '', $path = '/', $subdomain_install = false ):bool 
	* _populate_network_meta(int $network_id, array ...$meta):void 
	* _populate_site_meta(int $site_id, array ...$meta):void 

- _adm_schema_construct.php: 	
	* public $tpdb,$tp_charset_collate,$tp_queries,$tp_rewrite 
	* _schema_construct():void 
