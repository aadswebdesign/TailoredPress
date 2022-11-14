### TP_Core/Traits/

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _comment_01.php: 	
	* _check_comment( $author, $email, $url, $comment, $user_ip, $user_agent, $comment_type ): bool 
	* _get_approved_comments( $post_id, ...$args ): array  
	* _get_comment( $comment = null, $output = OBJECT ): ?array  
	* _get_comments( ...$args):array  
	* _get_comment_statuses():array  
	* _get_default_comment_status( $post_type = 'post', $comment_type = 'comment' ) -> mixed  
	* _get_last_comment_modified( $timezone = 'server' ):?bool  
	* _get_comment_count( $post_id = 0 ):array  
	* _add_comment_meta( $comment_id, $meta_key, $meta_value, $unique = false ) 
	* _delete_comment_meta( $comment_id, $meta_key, $meta_value = '' )  

- _comment_02.php: 	
	* _get_comment_meta( $comment_id, $key = '', $single = false ) -> mixed
	* _update_comment_meta( $comment_id, $meta_key, $meta_value, $prev_value = '' ) -> mixed  
	* _tp_queue_comments_for_comment_meta_lazy_load($comments ):void  
	* _tp_set_comment_cookies( $comment,TP_User $user, $cookies_consent = true ):void  
	* _sanitize_comment_cookies():void  
	* _tp_allow_comment( $comment_data, $tp_error = false ): TP_Error  
	* _check_comment_flood_db():void  
	* _tp_check_comment_flood( $is_flood, $ip, $email, $date, $avoid_die = false ):bool  
	* _separate_comments( &$comments ):array  
	* _get_comment_pages_count( $comments = null, $per_page = null, $threaded = null ) -> float|int  

- _comment_03.php: 	
	* _get_page_of_comment($comment_ID, ...$args ) -> mixed  
	* _tp_get_comment_fields_max_lengths() -> mixed 
	* _tp_check_comment_data_max_lengths( $comment_data ) -> mixed 
	* _tp_check_comment_disallowed_list( $author, $email, $url, $comment, $user_ip, $user_agent ):string  
	* _tp_count_comments( $post_id = 0 ) -> mixed  
	* _tp_delete_comment( $comment_id, $force_delete = false ) -> mixed  
	* _tp_trash_comment( $comment_id ) -> bool|string 
	* _tp_untrash_comment( $comment_id ):bool 
	* _tp_spam_comment( $comment_id ):bool  
	* _tp_unspam_comment( $comment_id ):bool  

- _comment_04.php: 	
	* _tp_get_comment_status( $comment_id ) -> bool|string  
	* _tp_transition_comment_status( $new_status, $old_status,TP_Comment $comment ):void  
	* _clear_modified_cache_on_transition_comment_status( $new_status, $old_status ):void 
	* _tp_get_current_commenter() -> mixed   
	* _tp_get_unapproved_comment_author_email():string  
	* _tp_insert_comment( $comment_data ) -> bool|int 
	* _tp_filter_comment( $comment_data ) -> mixed  
	* _tp_throttle_comment_flood( $block, $time_last_comment, $time_new_comment ):bool  
	* _tp_new_comment( $comment_data, $tp_error = false ) -> bool|int 
	* _tp_new_comment_notify_moderator( $comment_ID ):bool  

- _comment_05.php: 	
	* _tp_new_comment_notify_post_author( $comment_ID ):bool  
	* _tp_set_comment_status( $comment_id, $comment_status, $tp_error = false ) -> bool|TP_Error  
	* _tp_update_comment( $comment_arr, $tp_error = false )  
	* _tp_defer_comment_counting( $defer = null ):bool  
	* _tp_update_comment_count( $post_id, $do_deferred = false ) ->  bool|null|string 
	* _tp_update_comment_count_now( $post_id ):bool  
	* _discover_pingback_server_uri( $url) -> bool|string 
	* _do_all_pings():void 
	* _do_all_ping_backs():void 
	* _do_all_enclosures():void 

- _comment_06.php: 	
	* _do_all_trackbacks():void 
	* _do_trackbacks( $post_id ):bool 
	* _generic_ping( $post_id = 0 ):int 
	* _pingback( $content, $post_id ):void 
	* _privacy_ping_filter( $sites ):string 
	* _trackback( $trackback_url, $title, $excerpt, $ID ) -> bool|int 
	* _weblog_ping( $server = '', $path = '' ):void 
	* _pingback_ping_source_uri( $source_uri ):string 
	* _xmlrpc_pingback_error( $ixr_error ): IXR_Error 
	* _clean_comment_cache( $ids ):void 

- _comment_07.php: 	
	* _update_comment_cache( $comments, $update_meta_cache = true ):void 
	* _prime_comment_caches( $comment_ids, $update_meta_cache = true ):void 
	* _close_comments_for_old_posts( $posts, TP_Query $query ) -> mixed 
	* _close_comments_for_old_post( $open, $post_id ):bool 
	* _tp_handle_comment_submission( $comment_data ):TP_Error 
	* _tp_register_comment_personal_data_exporter( $exporters ) -> mixed 
	* _tp_comments_personal_data_exporter( $email_address, $page = 1 ):array 
	* _tp_register_comment_personal_data_eraser( $erasers ) -> mixed 
	* _tp_comments_personal_data_eraser( $email_address, $page = 1 ):array 
	* _tp_cache_set_comments_last_changed():void 

- _comment_08.php: 	
	* _tp_batch_update_comment_type():void 
	* _tp_check_for_scheduled_update_comment_type():void 
