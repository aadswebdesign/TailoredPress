### TP_Admin/Libs/AdmPartials

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:** 

- Adm_Partial_Comments.php: extends Adm_Partials 	
	* private $__user_can 
	* public $checkbox, $pending_count, $extra_items 
	* __construct($args = null) 
	* floated_admin_avatar( $name, $comment_id ):string 
	* async_user_can():string 
	* prepare_items():void 
	* get_per_page( $comment_status = 'all' ) 
	* get_no_items():string 
	* _get_views():array 
	* _get_bulk_actions():array 
	* _get_extra_nav_block( $which ):string 
	* get_current_action():bool 
	* get_blocks() 
	* _get_comment_type_dropdown( $comment_type ):string 
	* _get_sortable_blocks():array 
	* _get_default_primary_name():string 
	* get_display():string 
	* get_single_block( $item ):string 
	* _get_handle_block_actions( $item, $column_name, $primary ):string 
	* _get_cb_block( $item ):string 
	* get_block( $comment ):string 
	* get_block_author( $comment ):string 
	* get_comment_date( $comment ):string 
	* get_comment_response():string 
	* get_comment_block_default( $item, $column_name ):string 

- Adm_Partial_Compats.php: extends Adm_Partials 	
	* public $adm_segment_blocks, $adm_segment_screen 
	* __construct(Adm_Screen $screen, $columns = [] ) 
	* _get_block_info():array 
	* get_blocks() 

- Adm_Partial_Links.php: extends Adm_Partials 	
	* __construct( ...$args) 
	* async_user_can() 
	* prepare_items() 
	* get_no_items():string 
	* _get_bulk_actions():array 
	* _get_extra_nav_block( $which ) 
	* get_blocks() 
	* _get_sortable_blocks():array 
	* _get_default_primary_name():string  
	* _get_cb_block( $item ):string 
	* _get_primary_name( $link ):string 
	* get_block_url( $link ):string 
	* get_block_categories( $link ):string 
	* get_block_rel( $link ):string 
	* get_block_visible( $link ):bool 
	* get_block_rating( $link ):string 
	* get_column_default( $item, $column_name ):string 
	* get_display_blocks():string 
	* _get_handle_block_actions( $item, $column_name, $primary ):string 

- Adm_Partial_Media.php: extends Adm_Partials 	
	* protected $_comment_pending_count 
	* private $__detached, $__is_trash 
	* public $modes 
	* __construct( ...$args) 
	* async_user_can() 
	* get_prepare_items():void 
	* get_prepare_items():void 
	* _get_bulk_actions():array 
	* _get_extra_nav_block( $which ) 
	* get_current_action():bool 
	* has_items():bool 
	* get_no_items() 
	* get_views():string 
	* get_blocks() 
	* _get_sortable_blocks():array 
	* get_cb_block( $item ):string 
	* get_block_title( $post ):string 
	* get_block_author( $post ):string 
	* get_block_desc( $post ):string 
	* get_block_date( $post ):string 
	* get_block_parent( $post ):string 
	* get_block_comments( $post ):string 
	* get_block_default( $item, $block_name ):string 
	* get_display_blocks():string 
	* _get_default_primary_name():string 
	* __get_actions( $post, $att_title ):string 
	* _get_handle_block_actions( $item, $column_name, $primary ):string 

- Adm_Partial_MS_Sites.php: extends Adm_Partials 	
	* public $status_list 
	* __construct( ...$args) 
	* async_user_can() 
	* prepare_items() 
	* get_no_items() 
	* _get_views():array 
	* _get_bulk_actions():array 
	* _get_pagination( $which ):string 
	* _get_extra_nav_block( $which ) 
	* get_blocks() 
	* get_cb_block( $item ):string 
	* get_block_id( $blog ) 
	* get_block_blogname( $blog ):string 
	* get_block_last_updated( $blog ):string 
	* get_block_registered( $blog ):string 
	* get_users_block( $blog ):string 
	* get_block_default( $item, $block_name ):string 
	* get_display_rows():string 
	* _get_site_states( $site ):string 
	* _get_default_primary_column_name():string 
	* _get_handle_row_actions( $item, $column_name, $primary ):string{} 

- Adm_Partial_MS_Themes.php: extends Adm_Partials 	
	* private $__has_items 
	* protected $_show_auto_updates 
	* public $site_id, $is_site_themes 
	* __construct( ...$args) 
	* get_classes():array 
	* async_user_can() 
	* prepare_items():void 
	* _search_callback(TP_Theme $theme ):bool 
	* _order_callback( $theme_a, $theme_b ):bool 
	* get_no_items() 
	* get_blocks() 
	* _get_sortable_blocks():array 
	* _get_primary_name():string 
	* _get_views():array 
	* _get_bulk_actions():array 
	* get_display_rows():string 
	* get_cb_block(TP_Theme $item ):string 
	* get_block_name(TP_Theme $theme ):string 
	* get_block_description(TP_Theme $theme ):string 
	* get_auto_updates(TP_Theme $theme ):string 
	* get_column_default(TP_Theme $item, $column_name ):string 
	* _get_single_blocks(TP_Theme $item ):string 
	* get_single_block(TP_Theme $theme ):string 

- Adm_Partial_MS_Users.php: extends Adm_Partials 	
	* async_user_can() 
	* prepare_items():void 
	* _get_bulk_actions():array 
	* get_no_items() 
	* _get_views():array 
	* _get_pagination( $which ):string 
	* get_blocks() 
	* _get_sortable_blocks():array 
	* _get_cb_block( $item ):string 
	* get_block_id( $user ):string 
	* get_block_username($user ):string 
	* get_block_name($user ):string 
	* get_block_email( $user ):string 
	* get_block_registered( $user ):string 
	* _get_blogs( $user, $classes ='', $data='', $primary=true ):string 
	* get_user_blogs( ):string //todo
	* get_block_default( $item, $block_name ):string 
	* get_display_rows():string //todo 
	* _get_default_primary_name():string 
	* _get_handle_block_actions( $item, $block_name, $primary ):string 

- Adm_Partial_Post_Comments.php: extends Adm_Partials 	
	* _get_block_info():string 
	* _get_classes():array 
	* get_display( $output_empty = false ):string 
	* get_per_page( $comment_status = false ):int 

- Adm_Partial_Posts_Block.php: extends Adm_Partials //todo refining	
	* private $__user_posts_count, $__sticky_posts_count, $__is_trash 
	* protected $_hierarchical_display, $_comment_pending_count, $_current_level 
	* __construct( ...$args) 
	* set_hierarchical_display( $display ):void 
	* async_user_can():string 
	* prepare_items():void 
	* has_items():bool 
	* get_no_items():string 
	* is_base_request():int 
	* _get_edit_link( $args, $label, $class = '' ):string 
	* _get_views():array 
	* _get_bulk_actions():array 
	* _get_categories_dropdown( $post_type ):array 
	* _get_formats_dropdown( $post_type ):array 
	* _get_extra_nav_block( $which ) 
	* get_current_action():bool 
	* _get_classes():array 
	* get_blocks() 
	* _get_sortable_blocks():array 
	* get_display_blocks($level = 0, ...$posts):string 
	* __get_display_blocks( $posts, $level = 0 ):string 
	* __get_display_blocks_hierarchical( $pages, $pagenum = 1, $per_page = 20 ):string 
	* __get_page_blocks( &$children_pages, &$count, $parent, $level, $pagenum, $per_page, &$to_display ):string 
	* get_cb_block( $item ):string 
	* _get_block_title( $post, $classes, $data, $primary ):string 
	* get_block_title($post):string 
	* get_block_date($post):string 
	* get_block_comments($post):string 
	* get_block_author( $post ):string 
	* get_block_default( $item, $column_name ):string 
	* get_single_block( $post, $level = 0 ):string 
	* _get_default_primary_name():string 
	* _get_handle_block_actions( $item, $column_name, $primary ):string 
	* get_inline_edit():string 

- Adm_Partial_Privacy_Data_Export_Requests.php: extends Adm_Partial_Privacy_Matters 	
	* protected $_request_type, $_post_type 
	* get_column_email( $item ):string 
	* get_block_next_steps( $item ):string 

- Adm_Partial_Privacy_Data_Removal_Requests.php: extends Adm_Partial_Privacy_Matters 	
	* protected $_request_type, $_post_type 
	* get_block_email( $item ):string 
	* get_column_next_steps( $item ):string 

- Adm_Partial_Privacy_Matters.php: extends Adm_Partials 	
	* public const TP_INVALID 
	* protected $_request_type, $_post_type 
	* get_blocks() 
	* _get_admin_partial_url() 
	* _get_sortable_blocks():array 
	* _get_default_primary_name():string 
	* _get_request_counts():int 
	* _get_views():array 
	* _get_bulk_actions():array 
	* get_process_bulk_action():string 
	* prepare_items() 
	* _get_cb_block( $item ):string 
	* get_block_status( $item ) 
	* _get_timestamp_as_date( $timestamp ):string 
	* get_block_default( $item, $column_name ):string 
	* get_block_created_timestamp( $item ):string 
	* get_block_email( $item ):string 
	* set_column_next_steps( $item ):void 
	* set_embedded_scripts():void{} 

- Adm_Partial_Terms_Block.php: extends Adm_Partials 	
	* private $__level 
	* public $callback_args 
	* __construct( ...$args) 
	* async_user_can() 
	* prepare_items():void 
	* get_no_items():string 
	* _get_bulk_actions():array 
	* get_current_action():bool 
	* get_blocks() 
	* _get_sortable_blocks():array 
	* get_display_placeholder():string 
	* __rows( $taxonomy, $terms, &$children, $start, $per_page, &$count, $parent = 0, $level = 0 ):string 
	* get_single_block( $tag, $level = 0 ):string 
	* _get_cb_block( $item ):string 
	* get_block_name( $tag ):string 
	* _get_default_primary_name():string 
	* _get_handle_block_actions( $item, $column_name, $primary ):string 
	* get_block_description( $tag ) 
	* get_block_slug( $tag ) 
	* get_block_posts( $tag ):string 
	* get_block_links( $tag ):string 
	* get_block_default( $item, $column_name ):string 
	* get_inline_edit():string 

- Adm_Partial_Themes_Block.php: extends Adm_Partials	
	* protected $_search_terms 
	* public $features 
	* __construct( ...$args) 
	* async_user_can() 
	* prepare_items() 
	* get_no_items() 
	* _get_nav_block( $which = 'top' ):string 
	* get_display():string 
	* get_blocks() 
	* get_display_placeholder():string 
	* get_display_blocks() 
	* get_search_theme(TP_Theme $theme ):bool 
	* _get_js_vars( ...$extra_args):string 

- Adm_Partial_Themes_Install_Block.php:  extends Adm_Partial_Themes_Block	
	* public $features 
	* async_user_can() 
	* prepare_items() 
	* get_no_items() 
	* _get_views():array 
	* get_display():string 
	* get_display_blocks():string 
	* get_single_block( $theme ):string 
	* get_theme_installer():string 
	* get_theme_installer_single( $theme ):string 
	* get_install_theme_info( $theme ):string 
	* _get_js_vars( ...$extra_args):string 
	* __get_theme_status( $theme ):string 

- Adm_Partial_Users_Block.php:  extends Adm_Partials	
	* public $site_id, $is_site_users 
	* __construct( $args = array() ) 
	* async_user_can() 
	* prepare_items() 
	* get_no_items() 
	* _get_views():array 
	* _get_multi_bulk_actions( $which = '') 
	* _get_extra_nav_block( $which ):string 
	* get_current_action():string 
	* get_blocks() 
	* _get_sortable_blocks():array 
	* get_display_blocks():string 
	* get_single_block( $user_object, $style = '', $role = '', $num_posts = 0 ):string 
	* _get_default_primary_name():string 
	* _get_role_block( $user_object ):string 

- Adm_Partials.php:  extends Adm_Partials_Base	
	* private $__actions;, $__pagination 
	* protected $_args, $_segment_headers, $_compat_fields, _compat_methods, $_modes, $_pagination_args, $_screen 
	* public $items 
	* __construct($args = null) 
	* sync_user_can() 
	* prepare_items() 
	* _set_pagination_args($args = null ):void 
	* get_pagination_arg( $key ):string 
	* has_items():bool 
	* get_search_box( $text, $input_id ):string 
	* _get_views():array 
	* _get_bulk_actions():array 
	* _get_multi_bulk_actions( $which = '' ) 
	* get_current_action() 
	* _get_actions($always_visible = false, ...$actions):string 
	* _get_months_dropdown( $post_type ):string 
	* _get_view_switcher($current_mode):string 
	* _get_comments_bubble( $post_id, $pending_comments ):string 
	* get_pagenum():int 
	* _get_items_per_page($default = 20, $option = null):string 
	* _get_pagination( $which ):string 
	* get_blocks(){} 
	* _get_sortable_blocks():array 
	* _get_default_primary_name() 
	* _get_primary_name() 
	* _get_block_info() 
	* get_block_count(): int 
	* get_block_headers( $with_id = true ):string 
	* get_display():string 
	* _get_classes():array 
	* _get_nav_block( $which ):string 
	* _get_extra_nav_block( $which ){/* no content */} 
	* get_display_placeholder():string 
	* get_display_blocks() 
	* get_single_block( $item ):string 
	* _block_default( $item, ...$column_name ):string 
	* _get_cb_block( $item ):string 
	* _get_single_blocks( $item ):string 
	* _get_handle_block_actions( $item, $column_name, $primary ):string 
	* async_response():void 
	* _get_js_vars():string 

- Adm_Partials_Base.php: 	
	* get_blocks() 
