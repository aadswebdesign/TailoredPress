### TP_Core/Traits/Categories

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _category_01.php: 	
	* _get_categories( ...$args):array  
	* _get_category( $category, $output = OBJECT, $filter = 'raw' )  
	*  _get_category_by_path( $category_path, $full_match = true, $output = OBJECT ) 
	* _get_category_by_slug( $slug ) 
	* _get_cat_ID( $cat_name ):int  
	* _get_cat_name($cat_id)  
	* _cat_is_ancestor_of( $cat1, $cat2 )  
	* _sanitize_category( $category, $context = 'display' )  
	* _sanitize_category_field( $field, $value, $cat_id, $context )  
	* _get_tags( ...$args):array  

- _category_02.php: 	
	* _get_tag( $tag, $output = OBJECT, $filter = 'raw' )  
	* _clean_category_cache( $id ):void  
	* _make_cat_compat( &$category ):void 
