### TP_Core/Traits/Query

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _query_01.php: 	
	* _get_query_var($var, $default =''): string 
	* _get_queried_object() 
	* _get_queried_object_id(): int 
	* _set_query_var( $var, $value ): void 
	* _query_posts( $query ) -> mixed todo
	* _tp_reset_query(): void 
	* _tp_reset_post_data(): void 
	* _is_archive(): bool 
	* _is_post_type_archive($post_types = ''): bool
	* _is_attachment( $attachment = '' ): bool  

- _query_02.php: 	
	* _is_author($author=''): bool  
	* _is_category($category = ''): bool 
	* _is_tag($tag =''): bool 
	* _is_tax($taxonomy = '', $term = ''): bool 
	* _is_date(): bool 
	* _is_day(): bool 
	* _is_feed( $feeds = '' ): bool 
	* _is_comment_feed(): bool 
	* _is_front_page(): bool 
	* _is_home(): bool 

- _query_03.php: 	
	* _is_privacy_policy():bool 
	* _is_month():bool 
	* _is_page($page = ''):bool 
	* _is_page($page = ''):bool 
	* _is_preview():bool 
	* _is_robots():bool 
	* _is_favicon():bool 
	* _is_search():bool 
	* _is_single($post = ''):bool 
	* _is_singular($post_types = ''):bool 

- _query_04.php: 	
	* _is_time():bool 
	* _is_trackback():bool   
	* _is_year():bool 
	* _is_404():bool 
	* _is_embed():bool 
	* _is_main_query():bool 
	* _have_posts():bool 
	* _in_the_loop():bool 
	* _rewind_posts():bool 

- _query_05.php: 	
	* _the_post():bool 
	* _have_comments():bool 
	* _the_comment():bool 
	* _tp_old_slug_redirect(): void 
	* _find_post_by_old_slug( $post_type ): int 
	* _find_post_by_old_date( $post_type ): int 
	* _setup_postdata( $post ): bool 
	* _generate_postdata( $post ) -> array|bool 
