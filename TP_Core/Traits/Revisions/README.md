### TP_Core/Traits/Revisions

**Note:** For what it is now and subject to change. 

**Files/Methods:** 
- _revision_01.php: 	
	- _tp_post_revision_fields(...$post):array 
	- _tp_post_revision_data( $post = [], $autosave = false ):array 
	- _tp_save_post_revision( $post_id ) -> bool|string
	- _tp_get_post_autosave( $post_id, $user_id = 0 ):bool 
	- _tp_is_post_revision(TP_Post $post ) -> bool|int 
	- _tp_is_post_autosave(TP_Post $post ) -> bool|int  
	- _tp_put_post_revision( $post = null, $autosave = false ):TP_Error 
	- _tp_get_post_revision( &$post, $output = OBJECT, $filter = 'raw' ):array
	- _tp_restore_post_revision( $revision_id, $fields = null ) -> bool|null  
	- _tp_delete_post_revision( $revision_id ):array 
- _revision_02.php: 	
	- _tp_get_post_revisions( $post_id = 0, $args = null ):array 
	- _tp_get_post_revisions_url( $post_id = 0 ) -> mixed 
	- _tp_revisions_enabled( $post ):bool 
	- _tp_revisions_to_keep( $post ):int 
	- _set_preview( $post ) -> mixed 
	- _show_post_preview():void 
	- _tp_preview_terms_filter( $terms, $post_id, $taxonomy ):array 
	- _tp_preview_post_thumbnail_filter( $value, $post_id, $meta_key ):string 
	- _tp_get_post_revision_version( $revision ) -> mixed 
	- _tp_upgrade_revisions_of_post( $post, $revisions ):bool 
