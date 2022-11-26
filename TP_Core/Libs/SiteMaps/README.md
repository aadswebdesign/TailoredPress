### TP_Core/Libs/SiteMaps

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  

- Sitemaps_Base.php: 	

- TP_Sitemaps.php: extends Sitemaps_Base 	
	* public $index, $registry, $renderer 
	* __construct() 
	* init():void 
	* sitemaps_enabled():bool 
	* register_sitemaps():void 
	* register_rewrites():void 
	* render_sitemaps():void 
	* redirect_sitemap_xml( $bypass,TP_Query $query ) 
	* add_robots( $output, $public ) 

- TP_Sitemaps_Index.php: extends Sitemaps_Base 	
	* private $__max_sitemaps 
	* protected $_registry 
	* __construct( TP_Sitemaps_Registry $registry ) 
	* get_sitemap_list():array 
	* get_index_url() 

- TP_Sitemaps_Provider.php: abstract, extends Sitemaps_Base 	
	* protected $_name, $_object_type 
	* get_url_list( $page_num, $object_subtype = '' ); abstract 
	* get_max_num_pages( $object_subtype = '' ); abstract 
	* get_sitemap_type_data():string 
	* get_sitemap_entries():array 
	* get_sitemap_url( $name, $page ) 
	* get_object_subtypes():array 

- TP_Sitemaps_Registry.php:
	* private $__providers 
	* add_provider( $name, TP_Sitemaps_Provider $provider ):bool 
	* get_provider( $name ) 
	* get_providers():array 

- TP_Sitemaps_Renderer.php: extends Sitemaps_Base 	
	* protected $_stylesheet, $_stylesheet_index 
	* __construct() 
	* get_sitemap_stylesheet_url() 
	* get_sitemap_index_stylesheet_url() 
	* render_index( $sitemaps ):void 
	* get_sitemap_index_xml( $sitemaps ) 
	* render_sitemap( $url_list ):void 
	* __check_for_simple_xml_availability():void 

- TP_Sitemaps_Stylesheet.php: extends Sitemaps_Base 	
	* render_stylesheet( $type ):void 
	* get_sitemap_stylesheet() //todo modify this to ul/li 
	* get_sitemap_index_stylesheet() 
	* get_stylesheet_css() 

- Providers/TP_Sitemaps_Posts.php: extends TP_Sitemaps_Provider 	
	* __construct() 
	* get_object_subtypes():array 
	* get_url_list( $page_num, $object_subtype = '' ) 
	* get_max_num_pages( $object_subtype = '' ) 
	* _get_posts_query_args( $post_type ) 

- Providers/TP_Sitemaps_Taxonomies.php:  extends TP_Sitemaps_Provider	
	* __construct() 
	* get_object_subtypes():array 
	* get_url_list( $page_num, $object_subtype = '' ) 
	* get_max_num_pages( $object_subtype = '' ) 
	* _get_taxonomies_query_args( $taxonomy ) 

- Providers/TP_Sitemaps_Users.php: extends TP_Sitemaps_Provider 	
	* __construct() 
	* get_url_list( $page_num, $object_subtype = '' ) 
	* get_max_num_pages( $object_subtype = '' ) 
	* _get_users_query_args() 
