<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-11-2022
 * Time: 08:45
 */

namespace TP_Admin\Libs\AdmPartials;
if(ABSPATH){
    class Adm_Partial_Comments extends Adm_Partials  {
        private $__user_can;
        public $checkbox = true;
        public $pending_count = [];
        public $extra_items;
        public function __construct($args = null) {
            $this->tp_post_id = isset( $_REQUEST['p'] ) ? $this->_abs_int( $_REQUEST['p'] ) : 0;
            if ( $this->_get_option( 'show_avatars' ) ) {
                $this->_add_filter( 'comment_author',[$this,'floated_admin_avatar'], 10, 2 );
            }
            $construct = ['plural'   => 'comments','singular' => 'comment','async' => true,'screen' => $args['screen'] ?? null,];
            parent::__construct($construct);
        }
        public function floated_admin_avatar( $name, $comment_id ):string {
            $comment = $this->_get_comment( $comment_id );
            $avatar  = $this->_get_avatar( $comment, 32, 'mystery' );
            return "$avatar $name";
        }//67
        public function async_user_can():string {
            return $this->_current_user_can( 'edit_posts' );
        }//76
        public function prepare_items():void {
            if ( ! empty( $_REQUEST['mode'] ) ) {
                $this->tp_mode = 'excerpt' === $_REQUEST['mode'] ? 'excerpt' : 'list';
                $this->_set_user_setting( 'posts_list_mode', $this->tp_mode );
            } else { $this->tp_mode = $this->_get_user_setting( 'posts_list_mode', 'list' ); }
            $this->tp_comment_status = $_REQUEST['comment_status'] ?? 'all';
            if ( ! in_array( $this->tp_comment_status,['all','mine','moderated','approved','spam','trash'],true )){
                $this->tp_comment_status = 'all';
            }
            $this->tp_comment_type = ! empty( $_REQUEST['comment_type'] ) ? $_REQUEST['comment_type'] : '';
            $this->tp_search = $_REQUEST['s'] ?? '';
            $this->tp_post_type = ( isset( $_REQUEST['post_type'] ) ) ? $this->_sanitize_key( $_REQUEST['post_type'] ) : '';
            $user_id = $_REQUEST['user_id'] ?? '';
            $orderby = $_REQUEST['orderby'] ?? '';
            $order   = $_REQUEST['order'] ?? '';
            $comments_per_page = $this->get_per_page( $this->tp_comment_status );
            $doing_async = $this->_tp_doing_async();
            if ( isset( $_REQUEST['number'] ) ) { $number = (int) $_REQUEST['number'];}
            else { $number = $comments_per_page + min( 8, $comments_per_page ); }
            $page = $this->get_pagenum();
            /** @noinspection NullCoalescingOperatorCanBeUsedInspection */
            if ( isset( $_REQUEST['start'])){$start = $_REQUEST['start'];}
            else {$start = ( $page - 1 ) * $comments_per_page;}
            if ( $doing_async && isset( $_REQUEST['offset'] ) ){ $start += $_REQUEST['offset'];}
            $status_map = ['mine' => '','moderated' => 'hold','approved' => 'approve','all' => '',];
            $args = ['status' => $status_map[ $this->tp_comment_status ] ?? $this->tp_comment_status,
                'search' => $this->tp_search,'user_id' => $user_id, 'offset' => $start,'number' => $number,
                'post_id' => $this->tp_post_id,'type' => $this->tp_comment_type,
                'orderby' => $orderby,'order' => $order,'post_type' => $this->tp_post_type,];
            $args = $this->_apply_filters( 'comments_list_table_query_args', $args );
            $_comments = $this->_get_comments( $args );
            if ( is_array( $_comments ) ) {
                $this->_update_comment_cache( $_comments );
                $this->items       = array_slice( $_comments, 0, $comments_per_page );
                $this->extra_items = array_slice( $_comments, $comments_per_page );
                $_comment_post_ids = array_unique( $this->_tp_list_pluck( $_comments, 'comment_post_ID' ) );
                $this->pending_count = $this->_get_pending_comments_num( $_comment_post_ids );
            }
            $total_comments = $this->_get_comments(
                array_merge($args,['count'=> true,'offset' => 0,'number' => 0,]));
            $this->_set_pagination_args(['total_items' => $total_comments,'per_page'=> $comments_per_page,]);
        }//87
        public function get_per_page( $comment_status = 'all' ){
            $comments_per_page = $this->_get_items_per_page( 'edit_comments_per_page' );
            return $this->_apply_filters( 'comments_per_page', $comments_per_page, $comment_status );
        }//201
        public function get_no_items():string {
            $output  = "";
            if ( 'moderated' === $this->tp_comment_status ) { $output .= $this->__( 'No comments awaiting moderation.' );}
            elseif ( 'trash' === $this->tp_comment_status ) {$output .= $this->__( 'No comments found in Trash.' );}
            else {$output .= $this->__( 'No comments found.' );}
            return $output;
        }//218
        protected function _get_views():array{
            $status_links = [];
            $num_comments = ($this->tp_post_id) ? $this->_tp_count_comments($this->tp_post_id) : $this->_tp_count_comments();
            $stati = [
                'all' => $this->_nx_noop("All <span class='count'>(%s)</span>", "All <span class='count'>(%s)</span>", 'comments'),
                'mine' => $this->_nx_noop("Mine <span class='count'>(%s)</span>", "Mine <span class='count'>(%s)</span>", 'comments'),
                'moderated' => $this->_nx_noop("Pending <span class='count'>(%s)</span>", "Pending <span class='count'>(%s)</span>", 'comments'),
                'approved' => $this->_nx_noop("Approved <span class='count'>(%s)</span>", "Approved <span class='count'>(%s)</span>", 'comments'),
                'spam' => $this->_nx_noop("Spam <span class='count'>(%s)</span>", "Spam <span class='count'>(%s)</span>", 'comments'),
                'trash' => $this->_nx_noop("Trash <span class='count'>(%s)</span>", "Trash <span class='count'>(%s)</span>", 'comments'),
            ];
            if (!EMPTY_TRASH_DAYS) { unset($stati['trash']);}
            $link = $this->_admin_url('edit_comments.php');
            if (!empty($this->tp_comment_type) && 'all' !== $this->tp_comment_type) {
                $link = $this->_add_query_arg('comment_type', $this->tp_comment_type, $link);
            }
            /** @noinspection LoopWhichDoesNotLoopInspection */ //todo
            foreach ($stati as $status => $label) {
                $current_link_attributes = '';
                if ($status === $this->tp_comment_status) {
                    $current_link_attributes .= " class='current' aria-current='page'";
                    if ('mine' === $status) {
                        $current_user_id = $this->_get_current_user_id();
                        $num_comments->mine = $this->_get_comments(['post_id' => $this->tp_post_id ?: 0, 'user_id' => $current_user_id, 'count' => true,]);
                        $link = $this->_add_query_arg('user_id', $current_user_id, $link);
                    } else { $link = $this->_remove_query_arg('user_id', $link);}
                    if (!isset($num_comments->$status)) { $num_comments->$status = 10;}
                    $link = $this->_add_query_arg('comment_status', $status, $link);
                    if ($this->tp_post_id) { $link = $this->_add_query_arg('p', $this->_abs_int($this->tp_post_id), $link);}
                    $status_links[$status] = "<a href='$link' $current_link_attributes>";
                    $status_links[$status] .= sprintf($this->_translate_nooped_plural($label, $num_comments->$status),
                        "<span class='%s-count'>%s</span>", ('moderated' === $status) ? 'pending' : $status, $this->_number_format_i18n($num_comments->$status));
                    $status_links[$status] .= "</a>";
                }
                return $this->_apply_filters('comment_status_links', $status_links);
            }//235
        }
        protected function _get_bulk_actions():array {
            $actions =[];
            if ( in_array( $this->tp_comment_status,['all', 'approved'], true ) ){
                $actions['unapprove'] = $this->__( 'Unapprove' );}
            if ( in_array( $this->tp_comment_status,['all', 'moderated'],true )){
                $actions['approve'] = $this->__( 'Approve' );}
            if ( in_array( $this->tp_comment_status,['all', 'moderated', 'approved', 'trash'], true ) ) {
                $actions['spam'] = $this->_x( 'Mark as spam', 'comment' );}
            if ( 'trash' === $this->tp_comment_status ) { $actions['untrash'] = $this->__( 'Restore' );}
            elseif ( 'spam' === $this->tp_comment_status ) {$actions['unspam'] = $this->_x( 'Not spam', 'comment' );}
            if (! EMPTY_TRASH_DAYS || in_array( $this->tp_comment_status, ['trash', 'spam'], true )) {
                $actions['delete'] = $this->__( 'Delete permanently' );
            } else { $actions['trash'] = $this->__( 'Move to Trash' );}
            return $actions;
        }//359
        protected function _get_extra_nav_block( $which ):string{
            static $has_items;
            if(! isset( $has_items)){ $has_items = $this->has_items();}
            $output  = "<div class='adm-segment actions'><ul><li>";
            if ( 'top' === $which ) {
                $output .= $this->_get_comment_type_dropdown( $this->tp_comment_type );
                $output .= $this->_get_action( 'restrict_manage_comments' );
                if ( ! empty( $output ) && $this->has_items() ) {
                    $output .= "</li><li><dd>";
                    $output .= $this->_get_submit_button( $this->__( 'Filter' ), '', 'filter_action', false,['id' => 'post-query-submit']);
                    $output .= "</dd>";
                }
            }
            if ( ( 'spam' === $this->tp_comment_status || 'trash' === $this->tp_comment_status ) && $has_items && $this->_current_user_can( 'moderate_comments' )){
                $output .= "</li><li>{$this->_tp_get_nonce_field( 'bulk-destroy', '_destroy_nonce' )}";
                $title = ( 'spam' === $this->tp_comment_status ) ? $this->_esc_attr__( 'Empty Spam' ) : $this->_esc_attr__( 'Empty Trash' );
                $output .= "</li><li><dd>{$this->_get_submit_button( $title, 'apply', 'delete_all', false )}</dd>";
            }
            $output .= $this->_get_action( 'manage_comments_nav', $this->tp_comment_status, $which );
            $output .= "</li></ul></div><!-- adm-segment action -->";
            return $output;
        }//397
        public function get_current_action():bool {
            if(isset($_REQUEST['delete_all'])||isset($_REQUEST['delete_all2'])){ return 'delete_all';}
            return parent::get_current_action();
        }//451
        public function get_blocks() {
            $columns = [];
            if ( $this->checkbox ) { $columns['cb'] = "<input type='checkbox' />";}
            $columns['author']  = $this->__( 'Author' );
            $columns['comment'] = $this->_x( 'Comment', 'column name' );
            if ( ! $this->tp_post_id ) { $columns['response'] = $this->__( 'In response to' );}
            $columns['date'] = $this->_x( 'Submitted on', 'column name' );
            return $columns;
        }//465
        protected function _get_comment_type_dropdown( $comment_type ):string{
            $output  = "";
            $comment_types = $this->_apply_filters('admin_comment_types_dropdown',['comment' => $this->__( 'Comments' ),'pings' => $this->__( 'Pings' ),]);
            if ( $comment_types && is_array( $comment_types ) ) {
                $output .= sprintf("<dt><label for='filter_by_comment_type' class='screen-reader-text'>%s</label></dt>",$this->__('Filter by comment type'));
                $output .= "<dd><select id='filter_by_comment_type' name='comment_type'>";
                $output .= sprintf("\t<option value=''>%s</option>",$this->__('All comment types'));
                foreach ( $comment_types as $type => $label ) {
                    if ( $this->_get_comments(['number' => 1,'type'=> $type,])) {
                        $output .= sprintf("\t<option value='%s' %s>%s</option>",$this->_esc_attr( $type ),
                            $this->_get_selected( $comment_type, $type),$this->_esc_html( $label ));
                    }
                }
                $output .= "</select></dd>";
            }
            return $output;
        }//495
        protected function _get_sortable_blocks():array {
            return ['author'=> 'comment_author','response' => 'comment_post_ID','date' => 'comment_date',];
        }//541
        protected function _get_default_primary_name():string {
            return 'comment';
        }//556
        public function get_display():string{
            static $has_items;
            $screen_reader_content = $this->_screen->get_render_screen_reader_content( 'heading_list' );
            $block_classes = implode( ' ', $this->_get_classes() );
            $output  = "<div class='adm-segment comment content display $block_classes'><ul><li>";
            $output .= $this->_tp_get_nonce_field( 'fetch-list-' . get_class( $this ), '_async_fetch_list_nonce' );
            if ( ! isset( $has_items ) ) {
                $has_items = $this->has_items();
                if ( $has_items ) {$output .= $this->_get_nav_block( 'top' );}
            }
            if(!empty($screen_reader_content)){ $output .= "$screen_reader_content</li><li>";}
            if($this->get_block_headers(false)){
                $output .= "</li><li>{$this->get_block_headers(false)}";
            }
            $output .= "</li><li id='the_comment_list' data-tp_lists='list:comment'>";
            $output .= $this->get_display_placeholder();
            $output .= "</li><li id='the_extra_comment_list' data-tp_lists='list:comment' style='display:none;'>";
            $output .= $items = $this->items;
            $output .= $this->items = $this->extra_items;
            $output .= $this->get_display_placeholder();
            $output .= $this->items = $items;
            $output .= "</li><li>";
            $output .= $this->_get_nav_block( 'bottom' );
            $output .= "</li><!-- li 1 --></ul></div><!-- adm-segment comment display -->";
            return $output;
        }//567
        /**
         * @param $item
         * @return string
         */
        public function get_single_block( $item ):string{
            $this->tp_comment = $item;
            $the_comment_class = $this->_tp_get_comment_status( $this->tp_comment );
            if ( ! $the_comment_class ) { $the_comment_class = '';}
            $the_comment_class = implode( ' ', $this->_get_comment_class( $the_comment_class, $this->tp_comment, $this->tp_comment->comment_post_ID ) );
            if ( $this->tp_comment->comment_post_ID > 0 ) {
                $this->tp_post = $this->_get_post( $this->tp_comment->comment_post_ID );}
            $this->__user_can = $this->_current_user_can( 'edit_comment', $this->tp_comment->comment_ID );
            $output  = "<div class='adm-segment single-block'><ul>";
            $output .= "<li id='comment_{$this->tp_comment->comment_ID}' class=' $the_comment_class'>{$this->_get_single_blocks( $this->tp_comment )}</li>\n";
            $output .= "</ul></div><!-- adm-segment single-block -->";
            unset( $GLOBALS['post'], $GLOBALS['comment'] );//todo
            return $output;
        }
        protected function _get_handle_block_actions( $item, $column_name, $primary ):string{
            if ( $primary !== $column_name ){return '';}
            if ( ! $this->__user_can ) {return ''; }
            $comment            = $item;
            $the_comment_status = $this->_tp_get_comment_status( $comment );
            $delete_nonce     = $this->_esc_html( '_tp_nonce=' . $this->_tp_create_nonce( "delete-comment_$comment->comment_ID" ) );
            $approve_nonce = $this->_esc_html( '_tp_nonce=' . $this->_tp_create_nonce( "approve-comment_$comment->comment_ID" ) );
            $url = "comment.php?c=$comment->comment_ID";//todo
            $approve_url   = $this->_esc_url( $url . "&action=approve-comment&$approve_nonce" );
            $unapprove_url = $this->_esc_url( $url . "&action=unapprove-comment&$approve_nonce" );
            $spam_url      = $this->_esc_url( $url . "&action=spam-comment&$delete_nonce" );
            $unspam_url    = $this->_esc_url( $url . "&action=unspam-comment&$delete_nonce" );
            $trash_url     = $this->_esc_url( $url . "&action=trash-comment&$delete_nonce" );
            $untrash_url   = $this->_esc_url( $url . "&action=untrash-comment&$delete_nonce" );
            $delete_url    = $this->_esc_url( $url . "&action=delete-comment&$delete_nonce" );
            $actions = ['approve' => '','unapprove' => '','reply' => '','quick_edit' => '','edit' => '',
                'spam' => '','unspam' => '','trash' => '','untrash' => '','delete' => '',];
            if ( $this->tp_comment_status && 'all' !== $this->tp_comment_status ) {
                if ( 'approved' === $the_comment_status ) {
                    $actions['unapprove'] = sprintf(
                        "<a href='%s' data-tp_list='%s' class='vim-u vim-destructive aria-button-if-js' aria-label='%s'>%s</a>",
                        $unapprove_url,"delete:the-comment-list:comment-{$comment->comment_ID}:e7e7d3:action=dim-comment&amp;new=unapproved",
                        $this->_esc_attr__( 'Unapprove this comment' ),$this->__( 'Unapprove' ));
                } elseif ( 'unapproved' === $the_comment_status ) {
                    $actions['approve'] = sprintf(
                        "<a href='%s' data-tp_list='%s' class='vim-a vim-destructive aria-button-if-js' aria-label='%s'>%s</a>",
                        $approve_url,"delete:the-comment-list:comment-{$comment->comment_ID}:e7e7d3:action=dim-comment&amp;new=approved",
                        $this->_esc_attr__( 'Approve this comment' ), $this->__( 'Approve' ));
                }
            } else {
                $actions['approve'] = sprintf("<a href='%s' data-tp_list='%s' class='vim-a aria-button-if-js' aria-label='%s'>%s</a>",
                    $approve_url,"dim:the-comment-list:comment-{$comment->comment_ID}:unapproved:e7e7d3:e7e7d3:new=approved",
                    $this->_esc_attr__( 'Approve this comment' ),$this->__( 'Approve' ));
                $actions['unapprove'] = sprintf("<a href='%s' data-tp_list='%s' class='vim-u aria-button-if-js' aria-label='%s'>%s</a>",
                    $unapprove_url,"dim:the-comment-list:comment-{$comment->comment_ID}:unapproved:e7e7d3:e7e7d3:new=unapproved",
                    $this->_esc_attr__( 'Unapprove this comment' ),$this->__( 'Unapprove' ));
            }
            if ( 'spam' !== $the_comment_status ) {
                $actions['spam'] = sprintf("<a href='%s' data-tp_list='%s' class='vim-s vim-destructive aria-button-if-js' aria-label='%s'>%s</a>",
                    $spam_url,"delete:the-comment-list:comment-{$comment->comment_ID}::spam=1",
                    $this->_esc_attr__( 'Mark this comment as spam' ),$this->_x( 'Spam', 'verb' ));/* translators: "Mark as spam" link. */
            } elseif ( 'spam' === $the_comment_status ) {
                $actions['unspam'] = sprintf("<a href='%s' data-tp_list='%s' class='vim-z vim-destructive aria-button-if-js' aria-label='%s'>%s</a>",
                    $unspam_url,"delete:the-comment-list:comment-{$comment->comment_ID}:66cc66:unspam=1",
                    $this->_esc_attr__( 'Restore this comment from the spam' ), $this->_x( 'Not Spam', 'comment' ));
            }
            if ( 'trash' === $the_comment_status ) {
                $actions['untrash'] = sprintf("<a href='%s' data-tp_list='%s' class='vim-z vim-destructive aria-button-if-js' aria-label='%s'>%s</a>",
                    $untrash_url,"delete:the-comment-list:comment-{$comment->comment_ID}:66cc66:untrash=1",
                    $this->_esc_attr__( 'Restore this comment from the Trash' ),$this->__( 'Restore' ));
            }
            if ( 'spam' === $the_comment_status || 'trash' === $the_comment_status || ! EMPTY_TRASH_DAYS ) {
                $actions['delete'] = sprintf("<a href='%s' data-tp_list='%s' class='vim-d vim-destructive aria-button-if-js' aria-label='%s'>%s</a>",
                    $delete_url,"delete:the-comment-list:comment-{$comment->comment_ID}::delete=1",
                    $this->_esc_attr__( 'Delete this comment permanently' ),$this->__( 'Delete Permanently' ));
            } else {
                $actions['trash'] = sprintf("<a href='%s' data-tp_list='%s' class='vim-d vim-destructive aria-button-if-js' aria-label='%s'>%s</a>",
                    $trash_url,"delete:the-comment-list:comment-{$comment->comment_ID}::trash=1",
                    $this->_esc_attr__( 'Move this comment to the Trash' ),$this->_x('Trash','verb'));
            }
            if ( 'spam' !== $the_comment_status && 'trash' !== $the_comment_status ) {
                $format = "<button type='button' data-comment_id='%d' data-post_id='%d' data-action='%d' class='%s button-link' aria-expanded='false' aria-label='%s'>%s</button>";
                $actions['quick_edit'] = sprintf($format,$comment->comment_ID,$comment->comment_post_ID,'edit',
                    'vim-q comment-inline',$this->_esc_attr__( 'Quick edit this comment inline' ), $this->__( 'Quick&nbsp;Edit' ));
                $actions['reply'] = sprintf($format,$comment->comment_ID,$comment->comment_post_ID,'reply_to',
                    'vim-r comment-inline',$this->_esc_attr__( 'Reply to this comment' ),$this->__( 'Reply' ));
            }
            $actions = $this->_apply_filters( 'comment_row_actions', array_filter( $actions ), $comment );
            $always_visible = false;
            $mode = $this->_get_user_setting( 'posts_list_mode', 'list' );
            if ( 'excerpt' === $mode ) { $always_visible = true;}
            $visible_status = ( $always_visible ? 'block-actions visible' : 'block-actions' );
            $output  = "<div class='adm-segment handling-block-actions'><ul class='$visible_status'><li>";
            $i = 0;
            foreach ( $actions as $action => $link ) {
                ++$i;
                if ((('approve' === $action || 'unapprove' === $action ) && 2 === $i)|| 1 === $i){$sep = '';}
                else {$sep = ' | ';}
                if (( 'reply' === $action || 'quick_edit' === $action ) && ! $this->_tp_doing_async()){
                    $action .= ' hide-if-no-js';}
                elseif(( 'untrash' === $action && 'trash' === $the_comment_status ) || ( 'unspam' === $action && 'spam' === $the_comment_status )) {
                    if ( '1' === $this->_get_comment_meta( $comment->comment_ID, '_tp_trash_meta_status', true ) ) { $action .= ' approve';}
                    else { $action .= ' unapprove';}
                }
                $output .= "<dt><span class='$action'>$sep$link</span></dt></li><li>";
            }
            $output .= "<button type='button' class='toggle-row'><span class='screen-reader-text'>{$this->__('Show more details')}</span></button>";
            $output .= "</li></ul></div><!-- adm-segment handling-block-actions -->";
            return $output;
        }
        protected function _get_cb_block( $item ):string{
            $comment = $item;
            $output  = "";
            if ( $this->__user_can ){
                $output .= "<dt><label for='cb_select_{$comment->comment_ID}' class='screen-reader-text'>{$this->__('Select comment')}</label></dt>";
                $output .= "<dd><input id='cb_select_{$comment->comment_ID}' type='checkbox' name='delete_comments[]' value='{$comment->comment_ID}'/></dd>";
            }
            return $output;
        }//878
        public function get_block( $comment ):string{
            $output  = "<div class='adm-segment comment-block'><ul><li>";
            if($this->get_block_author( $comment )){
                $output .= "<div class='comment-author'>{$this->get_block_author( $comment )}</div>";
                $output .= "</li><li>";
            }
            if ( $comment->comment_parent ) {
                $parent = $this->_get_comment( $comment->comment_parent );
                if ( $parent ) {
                    $parent_link = $this->_esc_url( $this->_get_comment_link( $parent ) );
                    $name        = $this->_get_comment_author( $parent );
                    $output .= "<dt><p>";
                    $output .= sprintf($this->__("<span>In reply to: </span>%s"),"<a href='$parent_link'>$name</a>.");
                    $output .= "</p></dt></li><li>";
                }
            }
            $output .= $this->_get_text_comment( $comment );
            if ($this->__user_can ) {
                $comment_content = $this->_apply_filters( 'comment_edit_pre', $comment->comment_content );
                $output .= "<div id='inline_{$comment->comment_ID}' class='hidden'><ul><li>";
                $output .= "<dt><textarea class='comment' rows='1' cols='1'>{$this->_esc_textarea( $comment_content )}</textarea></dt>";
                $output .= "</li><li>";
                $output .= "<dt class='author email'>{$this->_esc_attr( $comment->comment_author_email )}</dt>";
                $output .= "</li><li>";
                $output .= "<dt class='author'>{$this->_esc_attr( $comment->comment_author )}</dt>";
                $output .= "</li><li>";
                $output .= "<dt class='author url'>{$this->_esc_attr( $comment->comment_author_url )}</dt>";
                $output .= "</li><li>";
                $output .= "<dt class='comment status'>{$this->_esc_attr( $comment->comment_approved )}</dt>";
                $output .= "</li></ul></div><!-- inline comment -->";
                $output .= "</li><li>";
            }
            $output .= "</li></ul></div><!-- adm-segment comment-block -->";
            return $output;
        }
        public function get_block_author( $comment ):string{
            $author_url = $this->_get_comment_author_url( $comment );
            $author_url_display = $this->_untrailingslashit( preg_replace( '|^http(s)?://(www\.)?|i', '', $author_url ) );
            if ( strlen( $author_url_display ) > 50 ) {
                $author_url_display = $this->_tp_html_excerpt( $author_url_display, 49, '&hellip;' );
            }
            $output  = "<div class='adm-segment comment-author'><ul><li>";
            $output .= "<dt><p><strong>{$this->_get_comment_author( $comment )}</strong></p></dt></li><li>";
            if (! empty( $author_url_display ) ) {
                $output .= sprintf("<dd><a href='%s' rel='noopener noreferrer'>%s</a></dd></li><li>",$this->_esc_url( $author_url ), $this->_esc_html( $author_url_display ));
            }
            if ( $this->__user_can ){
                if ( ! empty( $comment->comment_author_email ) ) {
                    $email = $this->_apply_filters( 'comment_email', $comment->comment_author_email, $comment );
                    if (!empty($email) && '@' !== $email ){
                        $output .= sprintf("<dd><a href='%1\$s'>%2\$s</a></dd>", $this->_esc_url( 'mailto:' . $email ), $this->_esc_html( $email ));
                        $output .= "</li><li>";
                    }
                }
                $author_ip = $this->_get_comment_author_IP( $comment );
                if ( $author_ip ) {
                    $author_ip_url = $this->_add_query_arg( ['s' => $author_ip,'mode' => 'detail',], $this->_get_admin_url( 'edit_comments.php' ));
                    if ( 'spam' === $this->tp_comment_status ) { $author_ip_url = $this->_add_query_arg( 'comment_status', 'spam', $author_ip_url );}
                    $output .= sprintf("<a href='%1\$s'>%2\$s</a>", $this->_esc_url( $author_ip_url ), $this->_esc_html( $author_ip ));
                    $output .= "</li><li>";
                }
            }
            $output .= "</li></ul></div><!-- adm-segment comment-author -->";
            return $output;
        }//934
        public function get_comment_date( $comment ):string{
            $submitted = sprintf($this->__( '%1$s at %2$s' ),$this->_get_comment_date( $this->__( 'Y/m/d' ), $comment ),
                $this->_get_comment_date( $this->__( 'g:i a' ), $comment ));
            $output  = "<div class='adm-segment comment-date'><ul>";
            if (! empty( $comment->comment_post_ID && 'approved' === $this->_tp_get_comment_status( $comment )) ) {
                $output .= "<li><dd>";
                $output .= sprintf("<a href='%s'>%s</a>",$this->_esc_url( $this->_get_comment_link( $comment ) ), $submitted);
                $output .= "</dd></li>";
            }else{$output .= "<li><dt>$submitted</dt></li>";}
            $output .= "</ul></div><!-- adm-segment comment-date -->";
            return $output;
        }//991
        public function get_comment_response():string{
            $post = $this->_get_post();
            if ( ! $post ) {return false;}
            static $pending_comments = '';
            if ( isset( $this->pending_count[ $post->ID ] ) ) { $pending_comments .= $this->pending_count[ $post->ID ];}
            else {
                $_pending_count_temp              = $this->_get_pending_comments_num([ $post->ID ]);
                $pending_comments                .= $_pending_count_temp[ $post->ID ];
                $this->pending_count[ $post->ID ] = $pending_comments;
            }
            if ( $this->_current_user_can( 'edit_post', $post->ID ) ) {
                $post_link  = "<a href='{$this->_get_edit_post_link( $post->ID )}' class='comments-edit-item-link'>";
                $post_link .= $this->_esc_html( $this->_get_the_title( $post->ID ) ) . '</a>';
            } else {$post_link = $this->_esc_html( $this->_get_the_title( $post->ID ) );}
            $output  = "<div class='adm-segment comment-response'><ul><li>";
            if ( 'attachment' === $post->post_type ) {
                $thumb = $this->_tp_get_attachment_image( $post->ID, array( 80, 60 ), true );
                if($thumb){$output .= "</li>$thumb<li>";}
            }
            if($post_link){ $output .= "<dd>$post_link</dd></li><li>";}
            $post_type_object = $this->_get_post_type_object( $post->post_type );
            $output .= "<dd><a href='{$this->_get_permalink( $post->ID )}' class='comments-view-item-link'>{$post_type_object->labels->view_item}</a></dd>";
            $output .= "<dt><span class='post-com-count-wrapper post-com-count-{$post->ID}'>{$this->_get_comments_bubble( $post->ID, $pending_comments )}</span></dt>";
            $output .= "</li></ul></div><!-- adm-segment comment-response -->";
            return $output;
        }//1019
        public function get_comment_block_default( $item, $column_name ):string{
            return $this->_get_action( 'manage_comments_custom_column', $column_name, $item->comment_ID );
        }//1068
    }
}else{die;}