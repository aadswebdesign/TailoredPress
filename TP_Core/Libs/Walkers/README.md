### TP_Core/Libs/Walkers

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  

- TP_Walker.php: abstract, implements Walker_Interface 	
	* public $tree_type, $db_fields, $max_pages, $has_children 
	* display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ):string 
	* walk( $elements, $max_depth, ...$args ):string 
	* paged_walk( $elements, $max_depth, $page_num, $per_page, ...$args ):string 
	* get_number_of_root_elements( $elements ):string 
	* unset_children( $element, &$children_elements ):string 

- TP_Walker_Category.php: extends TP_Walker 	
	* private $__alt 
	* public $tree_type, $db_fields 
	* start_lvl( &$output, $depth = 0, ...$args):string 
	* end_lvl( &$output, $depth = 0, ...$args ):string 
	* start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args ):string 
	* end_el( &$output, $data_object, $depth = 0, ...$args):string 

- TP_Walker_Category_Checklist.php: extends TP_Walker 	
	* __construct() 
	* start_lvl( &$output, $depth = 0, ...$args):void 
	* end_lvl( &$output, $depth = 0, ...$args ):void 
	* start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args):void 
	* end_el( &$output, $data_object, $depth = 0, ...$args ):void 

- TP_Walker_CategoryDropdown.php: extends TP_Walker 	
	* public $tree_type, $db_fields 
	* start_lvl( &$output, $depth = 0, ...$args ):string{} 
	* end_lvl( &$output, $depth = 0, ...$args ):string{} 
	* start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args ):string 
	* end_el( &$output, $data_object, $depth = 0, ...$args ):string{} 

- TP_Walker_Comment.php: extends TP_Walker 	
	* private $__html  
	* protected $_comment //todo
	* public $tree_type, $db_fields, $comment_depth 
	* start_lvl( &$output, $depth = 0, ...$args ):string 
	* end_lvl( &$output, $depth = 0, ...$args ):string 
	* display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ):string 
	* start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args ):string 
	* end_el( &$output, $data_object, $depth = 0, ...$args ):string 
	* _ping( $comment, $args ):string 
	* filter_comment_text( $comment_text, $comment ) 
	* _get_comment( $comment, $depth, $args ):string 

- TP_Walker_Nav_Menu.php: extends TP_Walker 	
	* public $tree_type, $db_fields 
	* end_lvl( &$output, $depth = 0, ...$args):void 
	* start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args):string 
	* end_el( &$output, $data_object, $depth = 0, ...$args ):void 

- TP_Walker_Nav_Menu_Checklist.php: extends TP_Walker 	
	* public $_nav_menu_placeholder, $nav_menu_selected_id 
	* __construct( $fields = false ) 
	* start_lvl( &$output, $depth = 0, ...$args):void 
	* end_lvl( &$output, $depth = 0, ...$args):void 
	* start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args ):string 

- TP_Walker_Nav_Menu_Edit.php: extends TP_Walker 	
	* public $tp_nav_menu_max_depth 
	* getTpNavMenuMaxDepth() 
	* start_lvl( &$output, $depth = 0, ...$args):void {}  
	* end_lvl( &$output, $depth = 0, ...$args):void {} 
	* start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args):string 

- TP_Walker_Page.php: extends TP_Walker	
	* public $tree_type, $db_fields 
	* start_lvl( &$output, $depth = 0, ...$args ):string 
	* end_lvl( &$output, $depth = 0, ...$args ):string  
	* start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args ):string 
	* end_el( &$output, $data_object, $depth = 0, ...$args ):string 

- TP_Walker_PageDropdown.php: extends TP_Walker 	
	* public $tree_type, $db_fields 
	* start_lvl( &$output, $depth = 0, ...$args ):string {} 
	* end_lvl( &$output, $depth = 0, ...$args ):string{} 
	* start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args ):string 
	* end_el( &$output, $data_object, $depth = 0, ...$args ):string 

- Walker_Interface.php: extends TP_Walker 	
	* start_lvl( &$output, $depth = 0, ...$args); 
	* end_lvl( &$output, $depth = 0, ...$args ); 
	* start_el( &$output, $data_object, $depth = 0, $current_object_id = 0, ...$args); 
	* end_el( &$output, $data_object, $depth = 0, ...$args ); 
	* display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ); 
	* walk( $elements, $max_depth, ...$args ); 
	* paged_walk( $elements, $max_depth, $page_num, $per_page, ...$args ); 
	* get_number_of_root_elements( $elements ); 
	* unset_children( $element, &$children_elements ); 
