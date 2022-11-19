### TP_Core/Traits/Templates

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _author_template_01.php: 	
	* _get_the_author() -> mixed 
	* _the_author():void 
	* _get_the_modified_author() -> mixed 
	* _the_modified_author():void 
	* _get_the_author_meta($field = '',$user_id = false) -> mixed 
	* _the_author_meta( $field = '', $user_id = false ):void 
	* _get_the_author_link() -> mixed 
	* _the_author_link():void 
	* _get_the_author_posts():int 
	* _the_author_posts():void 

- _author_template_02.php: 	
	* _get_the_author_posts_link():string 
	* _the_author_posts_link():void 
	* _get_author_posts_url( $author_id, $author_nice_name = '' ) -> mixed 
	* _get_list_authors( ...$args):string 
	* _the_list_authors( ...$args):void 
	* _is_multi_author() -> mixed 
	* _clear_multi_author_cache():void 

- _block_template_01.php: 	
	* _add_template_loader_filters():void 
	* _locate_block_template( $template_class, $type, ...$templates ) -> string|template_canvas 
	* _resolve_block_template( $template_type, $template_hierarchy, $fallback_template ) -> mixed //todo
	* _block_template_render_title_tag():void 
	* _get_the_block_template_html():string 
	* block_template_viewport_meta_tag():void 
	* _strip_template_file_suffix( $template_file ) -> mixed 
	* _block_template_render_without_post_block_context( $context ) -> mixed 
	* _resolve_template_for_new_post( $tp_query ):void 
	* _resolve_home_block_template():array 

- _block_utils_template_01.php: 	
	* _get_block_theme_folders( $theme_stylesheet = null ):array 
	* _get_allowed_block_template_part_areas() -> mixed  
	* _get_default_block_template_types() -> mixed  
	* _filter_block_template_part_area( $type ):string 
	* _get_block_templates_paths( $base_directory ):array 
	* _get_block_template_file( $template_type, $slug ) -> array|null|string
	* _get_block_templates_files( $template_type ):?array 
	* _add_block_template_info( $template_item ) -> mixed  
	* _add_block_template_part_area_info( $template_info ) -> mixed  
	* _flatten_blocks( &$blocks ):array 

- _block_utils_template_02.php: 	
	* _inject_theme_attribute_in_block_template_content( $template_content ):string
	* _remove_theme_attribute_in_block_template_content( $template_content ):string
	* _build_block_template_result_from_file( $template_file, $template_type ):TP_Block_Template 
	* _build_block_template_result_from_post( $post ) -> TP_Block_Template | TP_Error
	* _get_block_template( $id, $template_type = 'tp_template' ) -> mixed  
	* _get_block_file_template( $id, $template_type = 'tp_template' ):TP_Block_Template 
	* _get_block_template_part($args, $block_template_args = null ):string 
	* _block_template_part($args, $block_template_args = null ):void 
	* _block_header_area():void 
	* _block_footer_area():void 
	* _tp_generate_block_templates_export_file() -> string|TP_Error

- _bookmark_template.php: 	
	* _walk_bookmarks( $bookmarks, ...$args):string  
	* _tp_get_list_bookmarks( $args = '' ):string  
	* _tp_list_bookmarks( $args = '' ):void 

- _category_template_01.php: 	
	* _get_category_link( $category ) -> int|string 
	* _get_category_parents( $category_id, $link = false, $separator = '/', $nice_name = false) -> mixed 
	* _get_the_category( $post_id = false ) -> mixed 
	* _get_the_category_by_ID( $cat_ID ):string 
	* _get_the_category_list( $separator = '', $parents = '', $post_id = false ) -> mixed 
	* _in_category( $category, $post = null ):bool 
	* the_category( $separator = '', $parents = '', $post_id = false ):void 
	* _category_description( $category = 0 ) -> mixed 
	* _tp_get_dropdown_categories(...$args):string 
	* _tp_dropdown_categories(...$args):void 
	* _tp_get_list_categories( ...$args):string 
	* _tp_list_categories( ...$args):void 

- _category_template_02.php: 	
	* _tp_get_tag_cloud( ...$args) -> mixed 
	* _tp_tag_cloud( ...$args):void 
	* _default_topic_count_scale( $count ):float 
	* _tp_generate_tag_cloud( $tags, ...$args) -> array|string 
	* _tp_object_name_sort_cb( $a, $b ):int 
	* _tp_object_count_sort_cb( $a, $b ):bool 
	* _walk_category_tree( ...$args ):string 
	* _walk_category_dropdown_tree( ...$args ):string 
	* _get_tag_link( $tag ) -> mixed 
	* _get_the_tags( $post_id = 0 ) -> mixed 
	* _get_the_tag_list( $before = '', $sep = '', $after = '', $post_id = 0 ) -> mixed 

- _category_template_03.php: 	
	* _the_tags( $before = null, $sep = ', ', $after = '' ):void 
	* _tag_description( $tag = 0 ):string 
	* _term_description($term = 0):string 
	* _get_the_terms( $post, $taxonomy ):bool 
	* _get_the_term_list( $post_id, $taxonomy, $before = '', $sep = '', $after = '' ):bool 
	* _get_term_parents_list( $term_id, $taxonomy, $args = [] ):string 
	* _get_cat_terms( $post_id, $taxonomy, $before = '', $sep = ', ', $after = '' ):bool 
	* the_cat_terms( $post_id, $taxonomy, $before = '', $sep = ', ', $after = '' ):void 
	* _has_category( $category = '', $post = null ) -> mixed 
	* _has_tag( $tag = '', $post = null ) -> mixed 
	* _has_term( $term = '', $taxonomy = '', $post = null ):bool 

- _comment_template_01.php: 	
	* _get_comment_author($comment_ID = 0 ) -> mixed 
	* _comment_author( $comment_ID = 0 ):void 
	* _get_comment_author_email( $comment_ID = 0 ) -> mixed 
	* _comment_author_email( $comment_ID = 0 ):void 
	* _comment_author_email_link( $linktext = '', $before = '', $after = '', $comment = null ):void 
	* _get_comment_author_email_link( $linktext = '', $before = '', $after = '', $comment = null ):string 
	* _get_comment_author_link( $comment_ID = 0 ) -> mixed 
	* comment_author_link( $comment_ID = 0 ):void 
	* _get_comment_author_IP( $comment_ID = 0 ) -> mixed 
	* comment_author_IP( $comment_ID = 0 ):void 

- _comment_template_02.php: 	
	* _get_comment_author_url( $comment_ID = 0 ) -> mixed  
	* comment_author_url( $comment_ID = 0 ):void 
	* _get_comment_author_url_link( $linktext = '', $before = '', $after = '', $comment = 0 ) -> mixed  
	* comment_author_url_link( $linktext = '', $before = '', $after = '', $comment = 0 ):void 
	* _comment_class( $css_class  = '', $comment = null, $post_id = null ):string 
	* _get_comment_class( $css_class = '', $comment_id = null, $post_id = null ):array 
	* _get_comment_date( $format = '', $comment_ID = 0 ) -> mixed  
	* comment_date( $format = '', $comment_ID = 0 ):void 
	* _get_comment_excerpt( $comment_ID = 0 ) -> mixed  
	* _comment_excerpt( $comment_ID = 0 ):void 

- _comment_template_03.php: 	
	* _get_comment_ID() -> mixed  
	* comment_ID():void 
	* _get_comment_link( $comment = null, $args = [] ) -> mixed  
	* _get_comments_link($post_id = 0 ) -> mixed  
	* _get_comments_number( $post_id = 0 ) -> mixed  
	* comments_number( $zero = false, $one = false, $more = false, $post_id = 0 ):void 
	* _get_comments_number_text( $zero = false, $one = false, $more = false, $post_id = 0 ) -> mixed  
	* _get_comment_text( $comment_ID = 0, ...$args ) -> mixed  
	* _get_text_comment( $comment_ID = 0, ...$args) -> mixed  
	* _comment_text( $comment_ID = 0, ...$args):void 
	* _get_comment_time( $format = '', $gmt = false, $translate = true ) -> mixed  

- _comment_template_04.php: 	
	* comment_time( $format = '' ):void 
	* _get_comment_type( $comment_ID = 0 ) -> mixed 
	* _comment_type( $comment_txt = false, $trackback_txt = false, $pingback_txt = false ):void 
	* _get_trackback_url() -> mixed 
	* trackback_url():void 
	* _trackback_rdf():void 
	* _comments_open($post_id = null) -> mixed 
	* _pings_open($post_id = null) -> mixed 
	* _tp_comment_form_unfiltered_html_nonce():void 
	* _get_comments_template($separate_comments = false, $args, $comments_args = null):string 
	* _print_comments_template($separate_comments = false, $args, $comments_args = null):void 

- _comment_template_05.php: 	
	* _comments_popup_link( $zero = false, $one = false, $more = false, $css_class = '', $none = false ):string 
	* _get_comment_reply_link( $comment = null, $post = null, ...$args ):bool 
	* comment_reply_link( $comment = null, $post = null, ...$args):void 
	* _get_post_reply_link($post = null, ...$args):bool 
	* post_reply_link($post = null, ...$args):void 
	* _get_cancel_comment_reply_link( $text = '' ) -> mixed 
	* cancel_comment_reply_link( $text = '' ):void 
	* _get_comment_id_fields( $post_id = 0 ) -> mixed 
	* comment_id_fields( $post_id = 0 ):void 
	* _comment_form_title( $no_reply_text = false, $reply_text = false, $link_to_parent = true ):void 

- _comment_template_06.php: 	
	* _tp_list_comments($comments = null, ...$args):string 
	* _setup_comment_form($post_id = null, ...$args ) -> bool|string
	* comment_form($post_id = null, ...$args ):void 

- _general_template_01.php: 	
	* _get_header( $args, $header_args = null):?string 
	* _print_header($args, $header_args = null):void 
	* _get_footer($args, $footer_args = null):string 
	* _print_footer($args, $footer_args = null):void 
	* _get_sidebar($args, $sidebar_args = null):string 
	* _print_sidebar($args, $sidebar_args = null):void 
	* _get_partial($args, $partial_args = null):string 
	* _print_partial($args, $partial_args = null):void 
	* _get_search_form($args,$search_args = null):?string 
	* _print_search_form($args, ...$class_args):void 
	* _tp_login_logout( $redirect = '', $echo = true ) -> mixed 
	* _tp_logout_url( $redirect='' ) -> mixed

- _general_template_02.php: 	
	* _tp_login_url( $redirect='',$force_re_auth = false ) -> mixed 
	* _tp_registration_url() -> mixed 
	* _tp_get_login_form( ...$args):string 
	* _tp_login_form( $args = [] ):void 
	* _tp_lost_password_url( $redirect = '' ):string 
	* _tp_get_register( $before = '<li>', $after = '</li>'):string 
	* _tp_register( $before = '<li>', $after = '</li>'):void 
	* _tp_get_meta():string 
	* _tp_meta():void 
	* _bloginfo( $show = '' ):void 
	* _get_bloginfo( $show = '', $filter = 'raw' ):string 
	* _get_site_icon_url( $size = 512, $url = '', $blog_id = 0 ) -> mixed 
	* site_icon_url( $size = 512, $url = '', $blog_id = 0 ):void 

- _general_template_03.php: 	
	* _has_site_icon( $blog_id = 0 ):bool 
	* _has_custom_logo( $blog_id = 0 ):bool 
	* _get_custom_logo( $blog_id = 0 ) -> mixed 
	* _the_custom_logo( $blog_id = 0 ):void 
	* _tp_get_document_title():array 
	* _tp_get_render_title_tag():string 
	* _tp_render_title_tag():void 
	* _tp_get_title( $sep = '&raquo;', $sep_location = '' ) -> mixed  
	* _tp_title( $sep = '&raquo;', $sep_location = '' ):void 
	* _get_single_post_title( $prefix = '') -> bool|string
	* _single_post_title( $prefix = ''):void 
	* _get_post_type_archive_title( $prefix = '') -> mixed  
	* _post_type_archive_title( $prefix = ''):void 
	* _single_cat_title( $prefix = ''):string 

- _general_template_04.php: 	
	* _single_tag_title( $prefix = ''):string 
	* _get_single_term_title($prefix = '') -> bool|string 
	* _single_term_title($prefix = ''):void 
	* _get_single_month_title( $prefix = '') -> bool|string 
	* _single_month_title( $prefix = ''):void 
	* _get_the_assembled_archive_title( $before = '', $after = '' ):string 
	* _print_the_assembled_archive_title( $before = '', $after = '' ):void 
	* _get_the_archive_title() -> mixed 
	* _get_the_assembled_archive_description( $before = '', $after = '' ):string 
	* _print_the_assembled_archive_description( $before = '', $after = '' ):void 
	* _get_the_archive_description() -> mixed 
	* _get_the_post_type_description() -> mixed 
	* _get_archives_link( $url, $text, $format = 'html', $before = '', $after = '', $selected = false ) -> mixed 
	* _tp_get_archives( $args = '' ) -> bool|string 
	* _tp_print_archives( $args = '' ):void 

- _general_template_05.php: 	
	* _calendar_week_mod( $num ) -> mixed 
	* _get_calendar( $initial = true):string 
	* _print_calendar( $initial = true):void 
	* _delete_get_calendar_cache():void 
	* _allowed_tags():string 
	* _get_the_date_xml():string 
	* _the_date_xml():void 
	* _get_the_assembled_date( $format = '', $before = '', $after = ''):string 
	* _get_the_date($format = '', $post = null):bool 
	* _get_the_assembled_modified_date( $format = '', $before = '', $after = ''):string 
	* _get_the_modified_date( $format = '', $post = null ) -> mixed 
	* the_time( $format = '' ):void 

- _general_template_06.php: 	
	* _get_the_time( $format = '', $post = null ):bool 
	* _get_post_time( $format = 'U', $gmt = false, $post = null, $translate = false ):bool 
	* _get_post_datetime( $post = null, $field = 'date', $source = 'local'  ) -> bool|\DateTimeImmutable 
	* _get_post_timestamp( $post = null, $field = 'date' ) -> bool|int 
	* the_modified_time( $format = '' ):void 
	* _get_the_modified_time( $format = '', $post = null ) -> mixed  
	* _get_post_modified_time( $format = 'U', $gmt = false, $post = null, $translate = false ) -> mixed  
	* _get_the_weekday():string 
	* _the_weekday():void 
	* _get_the_weekday_date( $before = '', $after = '' ):string 
	* _the_weekday_date( $before = '', $after = '' ):void 
	* _tp_head():void 

- _general_template_07.php: 	
	* _tp_get_footer():string 
	* _tp_footer():void 
	* _tp_get_body_open() -> mixed  
	* _tp_body_open():void 
	* _get_feed_links( ...$args):string 
	* _feed_links( ...$args):void  
	* _feed_get_links_extra(...$args):string  
	* _feed_links_extra(...$args):void 
	* _get_rsd_link():string 
	* _rsd_link():void 
	* _get_window_live_writer_manifest_link():string 
	* _window_live_writer_manifest_link():void 
	* _tp_get_strict_cross_origin_referrer():string 
	* _tp_strict_cross_origin_referrer():void 
	* _tp_get_site_icon() -> bool|string
	* _tp_site_icon():void 
	* _tp_get_resource_hints():string 
	* _tp_resource_hints():void 
	* _tp_dependencies_unique_hosts():array 

- _general_template_08.php: 	
	* _user_can_rich_edit() -> mixed 
	* _tp_default_editor() -> mixed 
	* _tp_get_editor($content, $editor_id, ...$settings):string 
	* _tp_editor( $content, $editor_id, ...$settings):void 
	* _tp_get_enqueue_editor():TP_Editor 
	* _tp_enqueue_editor():void 
	* _tp_get_enqueue_code_editor( ...$args ) -> bool|string 
	* _tp_get_code_editor_settings( ...$args ) -> //todo 
	* _get_search_query($escaped = true) -> mixed 
	* the_search_query():void 
	* _get_language_attributes( $doctype = 'html' ) -> mixed 
	* _language_attributes( $doctype = 'html' ):void 

- _general_template_09.php: 	
	* _paginate_links(...$args) -> array|string 
	* _tp_admin_css_color( $key, $name, $url, $colors = [], $icons = []):void 
	* _register_admin_color_schemes():void 
	* _tp_admin_css_uri( $file = 'tp-admin' ):string 
	* _tp_get_admin_css( $file = 'tp-admin') -> mixed  
	* _tp_admin_css( $file = 'tp-admin'):void 
	* _add_thick_box():void 
	* _get_checked( $checked, $current = true):string 
	* _get_selected( $selected, $current = true ):string 
	* _get_disabled( $disabled, $current = true):string 
	* _tp_get_readonly( $readonly, $current = true ):string 
	* _get_checked_selected_helper( $helper, $current, $type ):string 

- _general_template_10.php: 	
	* _tp_heartbeat_settings( $settings ) -> mixed todo might be double? 

- _link_template_01.php: 	
	* _the_permalink( $post = 0 ):void 
	* _user_trailingslashit($string, $type_of_url = '') -> mixed
	* _get_permalink_anchor( $mode = 'id' ):array 
	* _permalink_anchor( $mode = 'id' ):void 
	* _tp_force_plain_post_permalink( $post = null, $sample = null ):bool 
	* _get_the_permalink( $post = 0, $leave_name = false ):string 
	* _get_permalink( $post= 0,$leave_name = false ) -> mixed 
	* _get_post_permalink( $id = 0, $leave_name = false, $sample = false ) -> mixed 
	* _get_page_link( $post = false, $leave_name = false, $sample = false ) -> mixed 
	* _get_protected_page_link( $post = false, $leave_name = false, $sample = false )_get_protected_page_link( $post = false, $leave_name = false, $sample = false ) -> mixed 
	* _get_attachment_link( $post = null, $leave_name = false ) -> mixed 

- _link_template_02.php: 	
	* _get_year_link( $year ) -> mixed 
	* _get_month_link( $year, $month ) -> mixed 
	* _get_day_link( $year, $month, $day ) -> mixed 
	* _the_feed_link( $anchor, $feed = '' ):void //todo adding a return method
	* _get_feed_link($feed = '') -> mixed 
	* _get_post_comments_feed_link( $post_id = 0,$feed = '' ):string 
	* _post_comments_feed_link( $link_text = '', $post_id = '', $feed = '' ):void 
	* _get_author_feed_link( $author_id , $feed = ''):string 
	* _get_category_feed_link( $cat,$feed ='' ):string 
	* _get_term_feed_link( $term, $taxonomy = '', $feed = '' ) -> bool|string 

- _link_template_03.php: 	
	* _get_tag_feed_link( $tag,$feed ='' ) -> mixed 
	* _get_edit_tag_link( $tag, $taxonomy = 'post_tag' ) -> mixed 
	* _edit_tag_link( $link = '', $before = '', $after = '', $tag = null ):void 
	* _get_edit_term_link( $term, $taxonomy = '', $object_type = '' ):bool 
	* _edit_term_link( $link = '', $before = '', $after = '', $term = null) -> bool|string 
	* _get_search_link( $query = '' ) -> mixed 
	* _get_search_feed_link($search_query = '',$feed = '') -> mixed 
	* _get_search_comments_feed_link( $search_query = '', $feed = '' ) -> mixed 
	* _get_post_type_archive_link( $post_type ):bool 
	* _get_post_type_archive_feed_link( $post_type, $feed ='' ):bool 

- _link_template_04.php: 	
	* _get_preview_post_link( $post = null, $preview_link = '', array ...$query_args):bool 
	* _get_edit_post_link( $id = 0, $context = 'display' ):bool 
	* _edit_post_link( $text = null, $before = '', $after = '', $id = 0, $class = 'post-edit-link' ):void 
	* _get_delete_post_link( $id = 0, $force_delete = false ):bool 
	* _get_edit_comment_link( $comment_id = 0 ):bool 
	* _get_edit_comment_link_string( $text = null, $before = '', $after = '' ) 
	* _edit_comment_link( $text = null, $before = '', $after = '' ):void 
	* _get_edit_bookmark_link( $link = 0 ):bool 
	* _edit_bookmark_link( $link = '', $before = '', $after = '', $bookmark = null ):void 
	* _get_edit_user_link( $user_id = null ):string 
	* _get_previous_post( $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ) -> //todo

- _link_template_05.php: 	
	* _get_next_post( $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ):string 
	* _get_adjacent_post( $in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'category' ):string 
	* _get_adjacent_post_rel_link( $title = '%title', $in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'category' ):bool 
	* _adjacent_posts_rel_link( $title = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ):void 
	* _adjacent_posts_rel_link_tp_head():void 
	* _next_post_rel_link( $title = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ):void 
	* _prev_post_rel_link( $title = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ):void 
	* _get_boundary_post( $in_same_term = false, $excluded_terms = '', $start = true, $taxonomy = 'category' ):string 
	* _get_previous_post_link( $format = '&laquo; %link', $link = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ) -> mixed 
	* _previous_post_link( $format = '&laquo; %link', $link = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ):void 

- _link_template_06.php: 	
	* _get_next_post_link( $format = '%link &raquo;', $link = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ) -> mixed 
	* _next_post_link( $format = '%link &raquo;', $link = '%title', $in_same_term = false, $excluded_terms = '', $taxonomy = 'category' ):void 
	* _get_adjacent_post_link( $format, $link, $in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'category' ) -> mixed 
	* _adjacent_post_link( $format, $link, $in_same_term = false, $excluded_terms = '', $previous = true, $taxonomy = 'category' ):void 
	* _get_page_num_link($pagenum = 1,$escape = true) -> mixed 
	* _get_next_posts_page_link( $max_page = 0 ) -> mixed 
	* _get_next_posts( $max_page = 0) -> mixed 
	* _next_posts( $max_page = 0 ):void 
	* _get_next_posts_link( $label = null, $max_page = 0 ):string 
	* _next_posts_link( $label = null, $max_page = 0 ):void 
	* _get_previous_posts_page_link() -> mixed 

- _link_template_07.php: 	
	* _get_previous_posts() -> mixed 
	* _previous_posts():void 
	* _get_previous_posts_link( $label = null ):string 
	* _previous_posts_link( $label = null ):void 
	* _get_posts_nav_link(array ...$args):string 
	* _posts_nav_link( $sep = '', $pre_label = '', $nxt_label = '' ):void 
	* _get_the_post_navigation(array ...$args):string 
	* _the_post_navigation(array ...$args):void 
	* _get_the_posts_navigation(array ...$args):string 
	* _the_posts_navigation(array ...$args):void 
	* _get_the_posts_pagination(array ...$args):string 

- _link_template_08.php: 	
	* _the_posts_pagination(array ...$args):void 
	* _navigation_markup( $links, $class = 'posts-navigation', $screen_reader_text = '', $aria_label = '' ):string 
	* _get_comments_page_num_link( $pagenum = 1, $max_page = 0 ) -> mixed 
	* _get_next_comments_link( $label = '', $max_page = 0 ) -> mixed 
	* _next_comments_link( $label = '', $max_page = 0 ):void 
	* _get_previous_comments_link( $label = '' ) ->bool|string
	* _previous_comments_link( $label = '' ):void 
	* _get_paginate_comments_links(array ...$args):bool 
	* _paginate_comments_links(array ...$args):void 
	* _get_the_comments_navigation(array ...$args):string 
	* _the_comments_navigation(array ...$args):void 

- _link_template_09.php: 	
	* _get_the_comments_pagination(array ...$args):string 
	* _home_url( $path = '', $scheme = null ) -> mixed  
	* _get_home_url( $blog_id = null, $path = '', $scheme = null ) -> mixed  
	* _site_url( $path = '', $scheme = null ) -> mixed  
	* _get_site_url( $blog_id = null, $path = '', $scheme = null ) -> mixed  
	* _admin_url($path='', $scheme = 'admin') -> mixed  
	* _get_admin_url( $blog_id = null, $path = '', $scheme = 'admin' ) -> mixed  
	* _includes_url( $path = '', $scheme = null ) -> mixed  
	* _content_url( $path = '' ) -> mixed  
	* //protected function _libs_url( $path = '', $lib = '') todo 
	* _network_site_url( $path = '', $scheme = null ) -> mixed  

- _link_template_10.php: 	
	* _network_home_url( $path = '', $scheme = null ) -> mixed 
	* _network_admin_url( $path = '', $scheme = 'admin' ) -> mixed 
	* _user_admin_url( $path = '', $scheme = 'admin' ) -> mixed 
	* _self_admin_url( $path = '', $scheme = 'admin' ) -> mixed 
	* _set_url_scheme( $url, $scheme = null ) -> mixed 
	* _get_dashboard_url( $user_id = 0, $path = '', $scheme = 'admin' ) -> mixed 
	* _get_edit_profile_url( $user_id = 0, $scheme = 'admin' ) -> mixed 
	* _tp_get_canonical_url( $post = null ):bool 
	* _get_rel_canonical()//todo 
	* _rel_canonical():void 
	* _tp_get_short_link( $id = 0, $context = 'post', $allow_slugs = true ):string 

- _link_template_11.php: 	
	* _tp_shortlink_tp_head():void 
	* _tp_shortlink_header():void 
	* _get_the_shortlink( $text = '', $title = '', $before = '', $after = '' ):?string 
	* _the_shortlink( $text = '', $title = '', $before = '', $after = '' ):void 
	* _get_avatar_url( $id_or_email, $args = null ) -> mixed 
	* _is_avatar_comment_type( $comment_type ):bool 
	* _get_avatar_data( $id_or_email, $args = null ) -> mixed 
	* _get_theme_file_uri( $file = '' ) -> mixed 
	* _get_parent_theme_file_uri( $file = '' ) -> mixed 
	* _get_theme_file_path( $file = '' ) -> mixed 
	* _get_parent_theme_file_path( $file = '' ) -> mixed 

- _link_template_12.php: 	
	* _get_privacy_policy_url() -> mixed 
	* _the_privacy_policy_link( $before = '', $after = '' ):void 
	* _get_the_privacy_policy_link( $before = '', $after = '' ):string 

- _media_template_01.php: //todo	
	* tp_underscore_audio_template():void 
	* tp_underscore_video_template():void 
	* tp_print_media_templates():void 

- _nav_menu_template.php: //todo	
	* _tp_get_nav_menu( $args = []):string 
	* _tp_nav_menu( $args = []):void 
	* _tp_menu_item_classes_by_context( &$menu_items ):void 
	* _walk_nav_menu_tree( $items, $depth, $r ):string 
	* _nav_menu_item_id_use_once( $id, $item ):string 

- _post_template_01.php: 	
	* _the_ID():void 
	* _get_the_ID():bool 
	* _get_title( $before = '', $after = ''):string 
	* _the_title( $before = '', $after = ''):void 
	* _get_the_title_attribute(array ...$args) -> bool|string
	* _the_title_attribute( array ...$args):void 
	* _get_the_title( $post = 0 ) 
	* _the_guid( $post = 0 ):void 
	* _get_the_guid( $post = 0 ) -> mixed
	* _the_content( $more_link_text = null, $strip_teaser = false ):void 
	* _get_the_content( $more_link_text = null, $strip_teaser = false, $post = null ):string 
	* _the_excerpt():void 

- _post_template_02.php: 	
	* _get_the_excerpt( $post = null ):string 
	* _has_excerpt( $post = 0 ):bool 
	* _get_the_post_class( $class = '', $post_id = null ):string 
	* _post_class( $class = '', $post_id = null ):void 
	* _get_post_class( $class = '', $post_id = null ):array 
	* _get_the_body_class( $class = '' ):string 
	* _body_class( $class = '' ):void 
	* _get_body_class( $class = '' ):array
	* _post_password_required( $post = null ) 
	* _tp_get_link_pages( $args = '' ) -> mixed
	* _tp_link_pages( $args = '' ):void 
	* _tp_link_page( $i ):string 
	* _post_custom( $key = '' ):bool 

- _post_template_03.php: 	
	* _get_the_meta():?string
	* _the_meta():void 
	* _tp_get_dropdown_pages(array ...$args) 
	* _tp_dropdown_pages(array ...$args):void 
	* _tp_get_list_pages(array ...$args) -> mixed 
	* _tp_list_pages(array ...$args):void 
	* _tp_get_page_menu(array ...$args):string 
	* _tp_page_menu(array ...$args):void 
	* _walk_page_tree( $pages, $depth, $current_page, $r ):string 
	* _walk_page_dropdown_tree( ...$args ):string 
	* _get_the_attachment_link( $id = 0, $full_size = false, $permalink = false ):string 
	* _the_attachment_link( $id = 0, $full_size = false, $permalink = false ):void 
	* _tp_get_attachment_link( $id = 0, $size = 'thumbnail', $permalink = false, $icon = false, $text = false, $attr = '' ) 
	* _prepend_attachment( $content ):string 
	* _get_the_password_form($post = 0 ) -> mixed 

- _post_template_04.php: 	
	* _is_page_template( ...$template):bool 
	* _get_page_template_slug( $post = null ) -> bool|string
	* _tp_post_revision_title( $revision, $link = true ) -> bool|string 
	* _tp_post_revision_title_expanded( $revision, $link = true ):bool 
	* _tp_get_list_post_revisions( $post_id = 0, $type = 'all' ):string 
	* _tp_list_post_revisions( $post_id = 0, $type = 'all' ):void 
	* _get_post_parent( $post = null ) -> mixed 
	* _has_post_parent( $post = null ):bool 

- _post_thumbnail_template.php: 	
	* _has_post_thumbnail( $post = null ):bool 
	* _get_post_thumbnail_id( $post = null ) 
	* _the_post_thumbnail( $size = 'post-thumbnail', $attr = '' ):void 
	* _update_post_thumbnail_cache( $tp_query = null ):void 
	* _get_the_post_thumbnail( $post = null, $size = 'post-thumbnail', ...$attr):string 
	* _get_the_post_thumbnail_url( $post = null, $size = 'post-thumbnail' ):bool 
	* _the_post_thumbnail_url( $size = 'post-thumbnail' ):void 
	* _get_the_post_thumbnail_caption( $post = null ):string 
	* _the_post_thumbnail_caption( $post = null ):void 

- _robots_template.php: 	
	* _tp_get_robots() 
	* _tp_robots():void 
	* _tp_robots_no_index( array $robots ) 
	* _tp_robots_no_index_embed( array $robots ):array 
	* _tp_robots_no_index_search( array $robots ):array 
	* _tp_robots_no_robots( array $robots ):array 
	* _tp_get_robots_sensitive_page( array $robots ):array 
	* _tp_robots_max_image_preview_large( array $robots ):array 

- _template_01.php: //todo	
	* _get_query_template($type,...$templates):string 
	* //_get_index_template() 
	* //_get_404_template() 
	* //_get_archive_template() 
	* //_get_post_type_archive_template() 
	* //_get_author_template() 
	* //_get_category_template() 
	* //_get_tag_template() 
	* //_get_taxonomy_template() 
	* //_get_date_template() 
	* //_get_home_template() 

- _template_02.php: //todo	
	* _get_front_page_template() 
	* _get_privacy_policy_template() 
	* _get_page_template() 
	* _get_search_template() 
	* _get_single_template() 
	* _get_embed_template() 
	* _get_singular_template() 
	* _get_attachment_template() 
	* _locate_template($path=null,$template_classes =null, ...$args):string 

- _template_03.php://todo 	
	* _load_template($_template_file, $require_once = true, array ...$args) 
	* _locate_library_class($classes = null, $lib_path = null, ...$args) 
	* _get_library_class($class) 
	* _locate_admin_class($classes = null, $lib_path = null, ...$args) 

- _theme_template.php: 	
	* _tp_set_unique_slug_on_create_template_part( $post_id ):void 
	* _tp_filter_tp_template_unique_post_slug( $override_slug, $slug, $post_ID, $post_type ):string  
	* __block_template_skip_link_script():string 
	* __block_template_skip_link_style():string  
	* _get_the_block_template_skip_link():string 
	* _the_block_template_skip_link():void 
	* _tp_enable_block_templates():void 
