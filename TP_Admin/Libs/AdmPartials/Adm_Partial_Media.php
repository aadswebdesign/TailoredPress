<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-9-2022
 * Time: 16:30
 */
namespace TP_Admin\Libs\AdmPartials;
use TP_Core\Libs\Post\TP_Post_Type;
use TP_Core\Libs\TP_Taxonomy;
if(ABSPATH){
    class Adm_Partial_Media extends Adm_Partials {
        protected $_comment_pending_count = [];
        private $__detached;
        private $__is_trash;
        public $modes;
        public function __construct( ...$args) {
            $this->__detached = ( isset( $_REQUEST['attachment-filter'] ) && 'detached' === $_REQUEST['attachment-filter'] );
            $this->modes = ['block' => $this->__( 'Block view' ), 'grid' => $this->__( 'Grid view' ),];
            parent::__construct(['plural' => 'media','screen' => $args['screen'] ?? null,] );
        }//40
        public function async_user_can() {
            return $this->_current_user_can( 'upload_files' );
        }//59
        public function get_prepare_items():void{
            $tp_query = $this->_init_query();
            $this->tp_mode = empty( $_REQUEST['mode'] ) ? 'block' : $_REQUEST['mode'];
            $not_in = [];
            $crons = $this->_get_cron_array();
            if ( is_array( $crons ) ) {
                foreach ( $crons as $cron ) {
                    if ( isset( $cron['upgrader_scheduled_cleanup'] ) ) {
                        $details = reset( $cron['upgrader_scheduled_cleanup'] );
                        if ( ! empty( $details['args'][0] ) ) { $not_in[] = (int) $details['args'][0];}
                    }
                }
            }
            if ( ! empty( $_REQUEST['post__not_in'] ) && is_array( $_REQUEST['post__not_in'] ) ) {
                $not_in = array_merge( array_values( $_REQUEST['post__not_in'] ), $not_in ); }
            if ( ! empty( $not_in ) ) { $_REQUEST['post__not_in'] = $not_in;}
            @list( $this->tp_post_mime_types, $this->tp_avail_post_mime_types ) = $this->_tp_edit_attachments_query( $_REQUEST );
            $this->__is_trash = isset( $_REQUEST['attachment-filter'] ) && 'trash' === $_REQUEST['attachment-filter'];
            $this->_set_pagination_args(['total_items' => $tp_query->found_posts,'total_pages' => $tp_query->max_num_pages,'per_page' => $tp_query->query_vars['posts_per_page'],]);
        }//69
        protected function _get_views():array{
            $type_links = [];
            $filter = empty( $_GET['attachment-filter'] ) ? '' : $_GET['attachment-filter'];
            $type_links['all'] = sprintf("<option value='' %s>%s</option>",$this->_get_selected( $filter, true),$this->__('All media items'));
            foreach ((array) $this->tp_post_mime_types as $mime_type => $label ) {
                if ( ! $this->_tp_match_mime_types( $mime_type, $this->tp_avail_post_mime_types )){continue;}
                $selected = $this->_get_selected($filter && 0 === strpos( $filter, 'post_mime_type:' ) &&
                    $this->_tp_match_mime_types( $mime_type, str_replace( 'post_mime_type:', '', $filter ) ),
                    true);
                $type_links[ $mime_type ] = sprintf("<option value='post_mime_type:%s' %s>%s</option>",$this->_esc_attr( $mime_type ),$selected,$label[0]);
            }
            $_detached = ( $this->__detached ? " selected='selected'" : '' );
            $type_links['detached'] = "<option value='detached' $_detached>{$this->_x( 'Unattached', 'media items' )}</option>";
            $type_links['mine'] = sprintf("<option value='mine' %s>%s</option>",$this->_get_selected( 'mine' === $filter, true),$this->_x( 'Mine', 'media items' ));
            if ( $this->__is_trash || ( defined( 'MEDIA_TRASH' ) && MEDIA_TRASH ) ) {
                $type_links['trash'] = sprintf("<option value='trash' %s>%s</option>",
                    $this->_get_selected( 'trash' === $filter, true ),$this->_x( 'Trash', 'attachment filter' ));
            }
            return $type_links;
        }//121
        protected function _get_bulk_actions():array{
            $actions = [];
            if ( __MEDIA_TRASH ) {
                if ( $this->__is_trash ) {
                    $actions['untrash'] = $this->__( 'Restore' );
                    $actions['delete']  = $this->__( 'Delete permanently' );
                } else { $actions['trash'] = $this->__( 'Move to Trash' );}
            } else {$actions['delete'] = $this->__( 'Delete permanently' );}
            if ( $this->__detached ) {$actions['attach'] = $this->__( 'Attach' );}
            return $actions;
        }//176
        protected function _get_extra_nav_block( $which ){
            if('bar'!== $which){ return false;}
            $output  = "<ul  class='block extra-nav-block actions'>";
            if ( ! $this->__is_trash ) { $output .= $this->_get_months_dropdown( 'attachment' );}
            if($this->_has_action('restrict_manage_posts')){
                $output .= "<li class='row  extra-nav-block'>";
                $output .= $this->_get_action( 'restrict_manage_posts', $this->_screen->post_type, $which );
                $output .= "</li><!-- row  extra-nav-block 1-->";
            }
            $output .= "<li class='row  extra-nav-block'>";
            $output .= "<dd>{$this->_get_submit_button( $this->__( 'Filter' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) )}</dd>";
            $output .= "</li><!-- row  extra-nav-block 2-->";
            if ( $this->__is_trash && $this->has_items() && $this->_current_user_can( 'edit_others_posts' )) {
                $output .= "<li class='row  extra-nav-block'>";
                $output .= $this->_get_submit_button( $this->__( 'Empty Trash' ), 'apply', 'delete_all', false );
                $output .= "</li><!-- row  extra-nav-block 3-->";
            }
            $output .= "</ul><!-- block extra-nav-block actions -->";
            return $output;
        }
        public function get_current_action():bool{
            if(isset($_REQUEST['found_post_id'], $_REQUEST['media'])){return 'attach';}
            if(isset( $_REQUEST['parent_post_id'], $_REQUEST['media'])){ return 'detach';}
            if(isset( $_REQUEST['delete_all'] ) || isset( $_REQUEST['delete_all2'])){ return 'delete_all';}
            return parent::get_current_action();
        }//229
        public function has_items():bool {
            return $this->_have_posts();
        }//248
        public function get_no_items(){
            $output  = "<dt>";
            if ( $this->__is_trash ) { $output .= $this->__( 'No media files found in Trash.' );
            } else { $output .= $this->__( 'No media files found.' );}
            $output .= "</dt>";
            return $output;
        }//254
        public function get_views():string{
            $views = $this->_get_views();
            $_heading_views = $this->_screen->get_render_screen_reader_content( 'heading_views' );
            $output  = "<div class='adm-segment view-block'><ul>";
            if($_heading_views){ $output .= "<li class='wrapper headings-view'>$_heading_views</li><!--wrapper  headings-view -->";}
            $output .= "<li class='wrapper view-switcher tp-filter'><ul class='block view-switcher filter-items'>";
            $output .= $this->_get_view_switcher( $this->tp_mode );
            $output .= "</ul><!-- block view-switcher filter-items --><li class='wrapper attachments'>";
            $output .= "<dt><label for='attachment_filter' class='screen-reader-text'>{$this->__('Filter by type')}</label></dt>";
            $output .= "<dd><select id='attachment_filter' name='attachment-filter' class='attachment-filters'>";
            if(!empty($views)){ foreach ($views as $class => $view ){ $output .= "\t$view\n";}}
            $output .= "</select></dd></li><!--wrapper attachments --><li class='wrapper nav-block'>";
            $output .= $this->_get_extra_nav_block( 'bar' );
            $output .= "</li><!-- wrapper nav-block --><li class='wrapper search-form'>";
            $output .= "<dt><label for='media_search_input' class='media-search-input-label'>{$this->_esc_html( 'Search' )}</label></dt>";
            $output .= "<dd><input name='s' id='media_search_input' class='search' type='search' value='{$this->_get_admin_search_query()}'/></dd>";
            $output .= "</li><!--wrapper search-form --></ul></div><!-- adm-segment view-block -->";
            return $output;
        }
        public function get_blocks(){
            $posts_columns       = [];
            $posts_columns['cb'] = "<dd><input type='checkbox' /></dd>";
            $posts_columns['title']  = $this->_x( 'File', 'block name' );
            $posts_columns['author'] = "<dt>{$this->__( 'Author' )}</dt>";
            $taxonomies = $this->_get_taxonomies_for_attachments( 'objects' );
            $taxonomies = $this->_tp_filter_object_list( $taxonomies, ['show_admin_column' => true], 'and', 'name' );
            $taxonomies = $this->_apply_filters( 'manage_taxonomies_for_attachment_columns', $taxonomies, 'attachment' );
            $taxonomies = array_filter( $taxonomies, [$this,'taxonomy_exists'] );
            foreach ( $taxonomies as $taxonomy ) {
                if ( 'category' === $taxonomy ) { $column_key = 'categories';
                } elseif ( 'post_tag' === $taxonomy ) { $column_key = 'tags';}
                else {$column_key = 'taxonomy-' . $taxonomy;}
                $_get_taxonomy = $this->_get_taxonomy( $taxonomy );
                $get_taxonomy = null;
                if($_get_taxonomy instanceof TP_Taxonomy){ $get_taxonomy = $_get_taxonomy;}
                $posts_columns[ $column_key ] = $get_taxonomy->labels->name;
            }
            if ( ! $this->__detached ) {
                $posts_columns['parent'] = $this->_x( 'Uploaded to', 'column name' );
                if ( $this->_post_type_supports( 'attachment', 'comments' ) ) {
                    $posts_columns['comments'] = "<span class='version comment-grey-bubble' title='{$this->_esc_attr__( 'Comments' )}'><span class='screen-reader-text'>{$this->__('Comments')}</span></span>";
                }
            }
            $posts_columns['date'] = $this->_x( 'Date', 'column name' );
            return $this->_apply_filters( 'manage_media_columns', $posts_columns, $this->__detached );
        }//317
        protected function _get_sortable_blocks():array {
            return ['title' => 'title','author' => 'author','parent' => 'parent','comments' => 'comment_count','date' => ['date', true],];
        }//376
        public function get_cb_block( $item ):string{
            $this->tp_post = $item;
            $output  = "";
            if ($this->_current_user_can( 'edit_post', $this->tp_post->ID ) ){
                $_select = sprintf($this->__('Select %s'),$this->_draft_or_post_title());
                $output .= "<dd><input name='media[]' id='cb_select_{$this->tp_post->ID}' type='checkbox' value='{$this->tp_post->ID}' /></dd>";
                $output .= "<dt><label for='cb_select_{$this->tp_post->ID}' class='screen-reader-text'>$_select</label></dt>";
            }
            return $output;
        }//394
        public function get_block_title( $post ):string{
            @list( $mime ) = explode( '/', $post->post_mime_type );
            $title      = $this->_draft_or_post_title();
            $thumb      = $this->_tp_get_attachment_image( $post->ID, [60, 60], true,['alt' => '']);
            $link_start = '';
            $link_end   = '';
            $output  = "";
            if ( ! $this->__is_trash && $this->_current_user_can( 'edit_post', $post->ID )) {
                $link_start = sprintf("<a href='%s' aria-label='%s'>",$this->_get_edit_post_link( $post->ID ),
                    $this->_esc_attr( sprintf( $this->__( '&#8220;%s&#8221; (Edit)' ), $title ) ));
                $link_end = '</a>';
            }
            $class = $thumb ? " class='has-media-icon'" : '';
            $output .= "<li class='wrapper block-title'><dd><p><strong $class>$link_start";
            if ( $thumb ){
                $output .= "<span class='media-icon {$this->_sanitize_html_class( $mime . '-icon' )}'>$thumb</span>";
            }
            $output .= $title.$link_end.$this->_get_media_states( $post )."</strong></p></dd>";
            $output .= "<dd><p class='filename'><span class='screen-reader-text'>{$this->__('File name:')}</span>";
            $file = $this->_get_attached_file( $post->ID );
            $output .= "{$this->_esc_html( $this->_tp_basename( $file ) )}</p></dd></li><!-- wrapper block-title -->";
            return $output;
        }//418
        public function get_block_author( $post ):string{//todo
            $output  = "";
            if($this->_get_the_author()){
                $output .= sprintf("<li class='wrapper block-author'><dd><a href='%s'>%s</a></dd></li><!-- wrapper block-author -->",$this->_esc_url( $this->_add_query_arg(['author' => $this->_get_the_author_meta($post->ID)], 'upload.php' ) ),$this->_get_the_author());
            }
            return $output;
        }//470
        public function get_block_desc( $post ):string{
            return $this->_has_excerpt() ? $post->post_excerpt : '';
        }//485
        public function get_block_date( $post ):string{
            if ( '0000-00-00 00:00:00' === $post->post_date ) {
                $h_time = $this->__( 'Unpublished' );
            } else {
                $time      = $this->_get_post_timestamp( $post );
                $time_diff = time() - $time;
                if ( $time && $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
                    $h_time = sprintf( $this->__( '%s ago' ), $this->_human_time_diff( $time ) );
                } else {$h_time = $this->_get_the_time( $this->__( 'Y/m/d' ), $post );}
            }
            return $h_time;
        }//496
        public function get_block_parent( $post ):string{
            $user_can_edit = $this->_current_user_can( 'edit_post', $post->ID );
            if ( $post->post_parent > 0 ) {
                $parent = $this->_get_post( $post->post_parent );
            } else { $parent = false;}
            $output  = "";
            if ( $parent ) {
                $title       = $this->_draft_or_post_title( $post->post_parent );
                $parent_type = $this->_get_post_type_object( $parent->post_type );
                if ($parent_type instanceof TP_Post_Type && $parent_type && $parent_type->show_ui && $this->_current_user_can( 'edit_post', $post->post_parent ) ) {
                    $output .= sprintf( "<li class='row block-parent'><dd><strong><a href=\"%s\">%s</a></strong></dd></li><!-- row block-parent 1-->", $this->_get_edit_post_link( $post->post_parent ), $title );
                } elseif ( $parent_type && $this->_current_user_can( 'read_post', $post->post_parent ) ) {
                    $output .= sprintf( "<li class='row block-parent'><dd><strong>%s</strong></dd></li><!-- row block-parent 2-->", $title );
                } else {$output .= "<li class='row block-parent'><dt><{$this->__( '(Private post)' )}</dt></li><!-- row block-parent 3-->";}
                if ( $user_can_edit ){
                    $detach_url = $this->_add_query_arg(['parent_post_id' => $post->post_parent,'media[]' => $post->ID,
                        '_tp_nonce' => $this->_tp_create_nonce( 'bulk-' . $this->_args['plural'] ),],'upload.php');
                    $output .= sprintf("<li class='row block-parent'><dd><a href='%s' class='hide-if-no-js detach-from-parent' aria-label='%s'>%s</a></dd></li><!-- row block-parent 4-->",$detach_url,
                        $this->_esc_attr( sprintf( $this->__( 'Detach from &#8220;%s&#8221;' ), $title ) ),$this->__( 'Detach' ));
                }
            }else{
                $output .= "<li class='row block-parent'><dd>{$this->__( '(Unattached)' )}</dd></li><!-- row block-parent 5-->";//todo might need a surrounding html element
                if ($user_can_edit ){
                    $title = $this->_draft_or_post_title( $post->post_parent );
                    $find_posts = " onclick='findPosts.open( \'media[]\', \'%s\' ); return false;'";
                    $the_list = '#the-list';
                    $output .= sprintf("<li class='row block-parent'><dd><a href='$the_list' $find_posts class='hide-if-no-js aria-button-if-js' aria-label='%s'>%s</a></dd></li><!-- row block-parent 6-->",
                        $post->ID,$this->_esc_attr( sprintf( $this->__( 'Attach &#8220;%s&#8221; to existing content' ), $title ) ),$this->__( 'Attach' ));
                }
            }
            return $output;
        }//521
        public function get_block_comments( $post ):string{
            $output  = "<div class='post-com-count-wrapper'>";
            $pending_comments = $this->_comment_pending_count[$post->ID] ?? $this->_get_pending_comments_num($post->ID);
            $output .= $this->_get_comments_bubble( $post->ID, $pending_comments );
            $output .= "</div>";
            return $output;
        }//583 //todo, db has to be setup first
        public function get_block_default( $item, $block_name ):string{//todo, db has to be setup first
            $post = $item;
            if ( 'categories' === $block_name ) {$taxonomy = 'category';}
            elseif ( 'tags' === $block_name ) {$taxonomy = 'post_tag';}
            elseif ( 0 === strpos( $block_name, 'taxonomy-')){$taxonomy = substr( $block_name, 9 );}
            else {$taxonomy = false;}
            $output  = "";
            if ( $taxonomy ) {
                $terms = $this->_get_the_terms( $post->ID, $taxonomy );
                if ( is_array( $terms ) ) {
                    $out = [];
                    foreach ( $terms as $t ) {
                        $posts_in_term_qv             = [];
                        $posts_in_term_qv['taxonomy'] = $taxonomy;
                        $posts_in_term_qv['term']     = $t->slug;
                        $out[] = sprintf("<a href='%s'>%s</a>",$this->_esc_url( $this->_add_query_arg( $posts_in_term_qv, 'upload.php' ) ),
                            $this->_esc_html( $this->_sanitize_term_field( 'name', $t->name, $t->term_id, $taxonomy, 'display' ) )    );
                    }
                    $output .= implode( $this->__( ', ' ), $out );
                }else{
                    $_taxonomy = $this->_get_taxonomy( $taxonomy );
                    $tax = null;
                    if($_taxonomy instanceof TP_Taxonomy ){
                        $tax = $_taxonomy;
                    }
                    $output .= "<span aria-hidden='true'>&#8212;</span><span class='screen-reader-text'>{$tax->labels->no_terms}</span>";
                }
            }
            $output .= $this->_get_action( 'manage_media_custom_block', $block_name, $post->ID );//todo
            return $output;
        }//606 //todo, db has to be setup first
        public function get_display_blocks():string{
            $tp_query = $this->_init_query();
            $tp_post = $this->_init_post();
            $post_ids = $this->_tp_list_pluck( $tp_query->posts, 'ID' );
            reset( $tp_query->posts );
            $this->_comment_pending_count = $this->_get_pending_comments_num( $post_ids );
            $output  = $this->_add_filter( 'the_title', 'esc_html' );
            while ( $this->_have_posts() ){
                $this->_the_post();//todo output it or not?
                if ( ($this->__is_trash && 'trash' !== $tp_post->post_status) || (!$this->__is_trash && 'trash' === $tp_post->post_status)){
                    continue;}
                $post_owner = ( $this->_get_current_user_id() === (int) $tp_post->post_author ) ? 'self' : 'other';
                $_trim_post = trim( ' author-' . $post_owner . ' status-' . $tp_post->post_status );
                $output .= "<div id='post_{$tp_post->ID}' class='adm_segment $_trim_post'>{$this->_get_single_blocks( $tp_post )}</div><!-- adm-segment -->";
            }
            return $output;
        }//661 //todo, db has to be setup first
        protected function _get_default_primary_name():string {
            return 'title';
        }//696
        private function __get_actions( $post, $att_title ):string{
            $actions = [];
            if ( $this->__detached ) {
                if ( $this->_current_user_can( 'edit_post', $post->ID ) ) {
                    $actions['edit'] = sprintf("<a href='%s' aria-label='%s'>%s</a>", $this->_get_edit_post_link( $post->ID ),
                        $this->_esc_attr( sprintf( $this->__( 'Edit &#8220;%s&#8221;' ), $att_title ) ),$this->__( 'Edit' ));/* translators: %s: Attachment title. */
                }
                if ( $this->_current_user_can( 'delete_post', $post->ID ) ){
                    if ( EMPTY_TRASH_DAYS && __MEDIA_TRASH ) {
                        $actions['trash'] = sprintf("<a href='%s' class='submit-delete aria-button-if-js' aria-label='%s'>%s</a>",
                            $this->_tp_nonce_url( "post.php?action=trash&amp;post=$post->ID", 'trash-post_' . $post->ID ),
                            $this->_esc_attr( sprintf( $this->__( 'Move &#8220;%s&#8221; to the Trash' ), $att_title ) ),
                            $this->_x( 'Trash', 'verb' ));/* translators: %s: Attachment title. */
                    } else {
                        $delete_ays        = ! __MEDIA_TRASH ? " onclick='return showNotice.warn();'" : '';
                        $actions['delete'] = sprintf("<a href='%s' class='submit-delete aria-button-if-js' %s aria-label='%s'>%s</a>",
                            $this->_tp_nonce_url( "post.php?action=delete&amp;post=$post->ID", 'delete-post_' . $post->ID ),
                            $delete_ays,$this->_esc_attr( sprintf( $this->__( 'Delete &#8220;%s&#8221; permanently' ), $att_title ) ),
                            $this->__( 'Delete Permanently' ));/* translators: %s: Attachment title. */
                    }
                }
                $actions['view'] = sprintf("<a href='%s' rel='bookmark' aria-label='%s'>%s</a>",$this->_get_permalink( $post->ID ),
                    $this->_esc_attr( sprintf( $this->__( 'View &#8220;%s&#8221;' ), $att_title ) ),$this->__('View'));/* translators: %s: Attachment title. */
                if ( $this->_current_user_can( 'edit_post', $post->ID ) ) {
                    $the_list = '#the-list';
                    $find_posts = " onclick='findPosts.open( \'media[]\', '\%s' );  return false;'";//todo
                    $actions['attach'] = sprintf("<a href='$the_list' $find_posts class='hide-if-no-js aria-button-if-js' aria-label='%s'>%s</a>",
                        $post->ID,$this->_esc_attr( sprintf( $this->__( 'Attach &#8220;%s&#8221; to existing content' ), $att_title ) ), $this->__( 'Attach' ));/* translators: %s: Attachment title. */
                }
            }else{
                if (! $this->__is_trash && $this->_current_user_can( 'edit_post', $post->ID )) {
                    $actions['edit'] = sprintf("<a href='%s' aria-label='%s'>%s</a>",$this->_get_edit_post_link( $post->ID ),
                        $this->_esc_attr( sprintf( $this->__( 'Edit &#8220;%s&#8221;' ), $att_title ) ), $this->__( 'Edit' )); /* translators: %s: Attachment title. */
                }
                if ( $this->_current_user_can( 'delete_post', $post->ID ) ) {
                    if ( $this->__is_trash ) {
                        $actions['untrash'] = sprintf("<a href='%s' class='submit-delete aria-button-if-js' aria-label='%s'>%s</a>",
                            $this->_tp_nonce_url( "post.php?action=untrash&amp;post=$post->ID", 'untrash-post_' . $post->ID ),
                            $this->_esc_attr( sprintf( $this->__( 'Restore &#8220;%s&#8221; from the Trash' ), $att_title ) ),
                            $this->__( 'Restore' ));/* translators: %s: Attachment title. */
                    }elseif ( EMPTY_TRASH_DAYS && __MEDIA_TRASH ) {
                        $actions['trash'] = sprintf("<a href='%s' class='submit-delete aria-button-if-js' aria-label='%s'>%s</a>",
                            $this->_tp_nonce_url( "post.php?action=trash&amp;post=$post->ID", 'trash-post_' . $post->ID ),
                            $this->_esc_attr( sprintf( $this->__( 'Move &#8220;%s&#8221; to the Trash' ), $att_title ) ),$this->_x( 'Trash', 'verb' )
                        );/* translators: %s: Attachment title. */
                    }
                    if ( $this->__is_trash || ! EMPTY_TRASH_DAYS || ! __MEDIA_TRASH ) {
                        $delete_ays        = ( ! $this->__is_trash && ! __MEDIA_TRASH ) ? " onclick='return showNotice.warn();'" : '';
                        $actions['delete'] = sprintf("<a href='%s' class='submit-delete aria-button-if-js' %s aria-label='%s'>%s</a>",
                            $this->_tp_nonce_url( "post.php?action=delete&amp;post=$post->ID", 'delete-post_' . $post->ID ),
                            $delete_ays,$this->_esc_attr( sprintf( $this->__( 'Delete &#8220;%s&#8221; permanently' ), $att_title ) ),
                            $this->__( 'Delete Permanently' ) );/* translators: %s: Attachment title. */
                    }
                }
                if ( ! $this->__is_trash ) {
                    $actions['view'] = sprintf("<a href='%s' rel='bookmark' aria-label='%s'>%s</a>",
                        $this->_get_permalink( $post->ID ),$this->_esc_attr( sprintf( $this->__( 'View &#8220;%s&#8221;' ), $att_title ) ),
                        $this->__( 'View' ) );/* translators: %s: Attachment title. */
                }
            }
            return $this->_apply_filters( 'media_row_actions', $actions, $post, $this->__detached );
        }//705 //todo, db has to be setup first
        protected function _get_handle_block_actions( $item, $column_name, $primary ):string{
            if( $primary !== $column_name ){ return '';}
            $att_title = $this->_draft_or_post_title();
            $actions   = $this->__get_actions( $item, $att_title);
            return $this->_get_actions( $actions );
        }//838//todo, db has to be setup first
    }
}else{die;}