### TP_Core/Traits/Feed

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/Methods:** 
- _feed_01.php: 	
	*  _get_bloginfo_rss( $show = '' )
	*  bloginfo_rss( $show = '' ):void
	*  _get_default_feed():string
	*  _get_tp_title_rss()
	*  tp_title_rss():void
	*  _get_the_title_rss()
	*  _the_title_rss():void
	*  _get_the_content_feed( $feed_type = null )
	*  _the_content_feed( $feed_type = null ):void
	*  _get_the_excerpt_rss()
	*  the_excerpt_rss():void

- _feed_02.php: 	
	* _get_the_permalink_feed_rss() 
	* the_permalink_feed_rss():void 
	* _get_comments_link_feed() 
	* comments_link_feed():void 
	* _comment_guid( $comment_id = null ):void 
	* _get_comment_feed_guid( $comment_id = null ) 
	* _get_comment_feed_link( $comment = null ) 
	* comment_link( $comment = null ):void 
	* _get_comment_feed_author_rss() 
	* comment_author_rss():void 

- _feed_03.php: 	
	* _get_comment_text_rss() 
	* comment_text_rss():void 
	* _get_the_category_rss( $type = null ) 
	* the_category_rss( $type = null ):void 
	* html_type_rss():void 
	* _get_rss_enclosure() 
	* rss_enclosure():void 
	* _get_atom_enclosure() 
	* atom_enclosure():void 
	* _prep_atom_text_construct( $data ):?array 

- _feed_04.php: 	
	* atom_site_icon():void 
	* _rss2_site_icon():string 
	* rss2_site_icon():void 
	* _get_self_link() 
	* self_link():void 
	* _get_feed_build_date( $format ) 
	* _feed_content_type($type ='') 
	* _fetch_feed( $url ) 
	*  tp_feed_atom_comments():void 
