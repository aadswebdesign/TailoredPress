<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 31-10-2022
 * Time: 06:45
 */
namespace TP_Admin\Libs\AdmPartials;
use TP_Admin\Libs\Adm_Screen;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    class Adm_Partials extends Adm_Partials_Base {
        private $__actions;
        private $__pagination;
        protected $_args;
        protected $_segment_headers;
        protected $_compat_fields = ['_args','_pagination_args','screen','_actions','_pagination'];
        protected $_compat_methods =['set_pagination_args','get_views','get_bulk_actions',
            'bulk_actions','row_actions','months_dropdown','view_switcher','comments_bubble',
            'get_items_per_page','pagination','get_sortable_columns','get_column_info',
            'get_table_classes','display_table_nav','extra_table_nav','single_row_columns',];
        protected $_modes = [];
        protected $_pagination_args = [];
        protected $_screen;
        public $items;
        public function __construct($args = null){
            $args = $this->_tp_parse_args( $args,['plural' => '','singular' => '','async' => false,'screen' => null,]);
            $_screen = $this->_convert_to_screen( $args['screen'] );
            if($_screen instanceof Adm_Screen){
                $this->_screen = $_screen;
            }
            $this->_add_filter( "manage_{$this->_screen->id}_columns", [$this, 'get_columns'], 0 );
            if ( ! $args['plural']){ $args['plural'] = $this->_screen->base;}
            $args['plural']   = $this->_sanitize_key( $args['plural'] );
            $args['singular'] = $this->_sanitize_key( $args['singular'] );
            $this->_args = $args;
            if ( $args['async'] ) {
                $this->tp_enqueue_script( 'list-segment' );
                $this->_add_action( 'admin_footer',[$this, '_js_vars']);
            }
            if ( empty( $this->_modes ) ) {
                $this->_modes = ['list' => $this->__( 'Compact view' ), 'excerpt' => $this->__( 'Extended view' ),];
            }
        }//138
        /**
         * @return mixed
         */
        public function sync_user_can(){
            die( 'function Adm_Segments::async_user_can() must be overridden in a subclass.' );
        }//255
        /**
         * @return mixed
         */
        public function prepare_items(){
            die( 'method Adm_Segments::prepare_items() must be overridden in a subclass.' );
        }//267
        protected function _set_pagination_args($args = null ):void{
            $args = $this->_tp_parse_args($args,['total_items' => 0,'total_pages' => 0,'per_page' => 0,]);
            if ( ! $args['total_pages'] && $args['per_page'] > 0 ) {
                $args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );
            }
            if ( $args['total_pages'] > 0 && ! headers_sent() && ! $this->_tp_doing_async() && $this->get_pagenum() > $args['total_pages'] ) {
                $this->_tp_redirect( $this->_add_query_arg( 'paged', $args['total_pages'] ) );
                exit;
            }
            $this->_pagination_args = $args;
        }//278
        public function get_pagination_arg( $key ):string{
            if ( 'page' === $key ) { return $this->get_pagenum(); }
            return $this->_pagination_args[$key] ?? 0;
        }//310
        public function has_items():bool {
            return ! empty( $this->items );
        }//329
        /**
         * @return mixed
         */
        public function get_no_items() {
            return $this->__( 'No items found.' );
        }//338
        public function get_search_box( $text, $input_id ):string{
            if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
                return false;}
            $sb_input_id = $input_id . '_search_input';
            $output  = "<div class='adm-segment search'><ul><li>";
            if(!empty( $_REQUEST['orderby'])){
                $output .= "<input type='hidden' value='{$this->_esc_attr( $_REQUEST['orderby'] )}'/>";
            }
            if(!empty( $_REQUEST['order'])){
                $output .= "<input type='hidden' value='{$this->_esc_attr( $_REQUEST['order'] )}'/>";
            }
            if(!empty( $_REQUEST['post_mime_type'])){
                $output .= "<input type='hidden' value='{$this->_esc_attr( $_REQUEST['post_mime_type'] ) }'/>";
            }
            if ( ! empty( $_REQUEST['detached'] ) ) {
                $output .= "<input type='hidden' value='{$this->_esc_attr( $_REQUEST['detached'] )}'/>";
            }
            $output .= "</li><li><dt><label for='$sb_input_id' class='screen-reader-text'>$text</label></dt>";
            $output .= "<dd><input id='$sb_input_id' name='s' value='{$this->_get_admin_search_query()}' type='search'/></dd>";
            $output .= "</li><li><dd>";
            $output .= $this->_get_submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) );
            $output .= "</dd></li></ul></div><!-- adm-segment search -->";
            return $output;
        }//350
        protected function _get_views():array {
            return [];
        }//388
        public function get_view():string{
            $views = $this->_get_views();
            $views = $this->_apply_filters( "views_{$this->_screen->id}", $views );
            if ( empty( $views ) ) {return false;}
            $output  = $this->_screen->get_render_screen_reader_content( 'heading_views' );
            $output .= "<ul class='sub-sub-sub'>\n";
            foreach ( $views as $class => $view ) {
                $views[ $class ] = "\t<li class='$class'>$view";
            }
            $output .= implode( " |</li>\n", $views ) . "</li>\n";
            $output .= "</ul>";
            return $output;
        }//397
        protected function _get_bulk_actions():array{
            return [];
        }//452 used to be _get_the_bulk_actions
        /**
         * @param string $which
         * @return bool|string
         */
        protected function _get_multi_bulk_actions( $which = '' ){
            if ( is_null( $this->__actions ) ) {
                $this->__actions = $this->_get_bulk_actions();
                $this->__actions = $this->_apply_filters( "bulk_actions-{$this->_screen->id}", $this->__actions );
                $two = '';
            }else { $two = '2';}
            if ( empty( $this->_actions)){return false;}
            $output  = "<div class='adm-segment bulk-actions'><ul><li>";
            $output .= "<dt><label for='bulk_action_selector_{$which}' class='screen-reader-text'>{$this->__('Select bulk action')}</label></dt>";
            $output .= "<dd><select name='action' $two id='bulk_action_selector_{$which}'>\n";
            $output .= "<option value='-1'>{$this->__('Bulk actions')}</option>\n";
            foreach ( $this->__actions as $key => $value ) {
                if ( is_array( $value ) ) {
                    $output .= "\t<optgroup>{$this->_esc_attr( $key )}\n";
                    foreach ( $value as $name => $title ) {
                        $class = ( 'edit' === $name ) ? ' class="hide-if-no-js"' : '';
                        $output .= "\t\t<option value='{$this->_esc_attr( $name )}' $class>$title</option>\n";
                    }
                    $output .= "</optgroup>\n";
                }else{
                    $class = ( 'edit' === $key ) ? ' class="hide-if-no-js"' : '';
                    $output .= "\t<option value='{$this->_esc_attr( $key )}' $class>$value</option>\n";
                }
            }
            $output .= "</select></dd></li>\n<li>";
            $output .= $this->_get_submit_button( $this->__( 'Apply' ), 'action', '', false, array( 'id' => "do_action$two" ) )."\n";
            $output .= "</li></ul></div><!-- adm-segment bulk-actions -->";
            return $output;
        }//464
        /**
         * @return mixed
         */
        public function get_current_action(){
            if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) ) {
                return false;}
            if ( isset( $_REQUEST['action'] ) && -1 !== $_REQUEST['action'] ) {
                return $_REQUEST['action'];}
            return false;
        }//524
        protected function _get_actions($always_visible = false, ...$actions):string{
            $action_count = count( $actions );
            if ( ! $action_count ){return '';}
            $mode = $this->_get_user_setting( 'posts_list_mode', 'list' );
            if ( 'excerpt' === $mode ) { $always_visible = true;}
            $_av = ( $always_visible ? 'segment-actions visible' : 'segment-actions' );
            $output  = "<div class='adm-segment $_av'><ul><li>";
            $i = 0;
            foreach ( $actions as $action => $link ) {
                ++$i;
                $sep = ( $i < $action_count ) ? ' | ' : '';
                $output .= "<p><span class='$action'>$link$sep</span></p></li><li>";
            }
            $output .= "<button class='toggle-row' type='button'><span class='screen-reader-text'>{$this->__('Show more details')}</span></button>";
            $output .= "</li></ul></div><!-- adm-segment row-actions -->";
            return $output;
        }//545
        protected function _get_months_dropdown( $post_type ):string{
            $tpdb = $this->_init_db();
            $tp_locale = $this->_init_locale();
            if ( $this->_apply_filters( 'disable_months_dropdown', false, $post_type ) ) {
                return false;}
            $months = $this->_apply_filters( 'pre_months_dropdown_query', false, $post_type );
            if ( ! is_array( $months ) ) {
                $extra_checks = "AND post_status != 'auto-draft'";
                if ( ! isset( $_GET['post_status'] ) || 'trash' !== $_GET['post_status'] ) {
                    $extra_checks .= " AND post_status != 'trash'";
                } elseif ( isset( $_GET['post_status'] ) ) {
                    $extra_checks = $tpdb->prepare( ' AND post_status = %s', $_GET['post_status'] );
                }
                $months = $tpdb->get_results(
                    $tpdb->prepare(TP_SELECT . " DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month FROM $tpdb->posts WHERE post_type = %s	$extra_checks ORDER BY post_date DESC ", $post_type)
                );
            }
            $months[] = $this->_apply_filters( 'months_dropdown_results', $months, $post_type );

            $month_count = (array)count( $months );
            if ( ! $month_count || ( 1 === $month_count && 0 === $months[0]->month ) ) {
                return false;
            }
            $m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
            $_post_type = $this->_get_post_type_object( $post_type );
            $tp_post_type = null;
            if($_post_type instanceof TP_Post){ $tp_post_type = $_post_type;}
            $output  = "<li class='row months-dropdown'>";
            $output .= "<dt><label for='filter_by_date' class='screen-reader-text'>{$tp_post_type->labels->filter_by_date}</label></dt>";
            $output .= "<dd><select id='filter_by_date'><option {$this->_get_selected( $m, 0 )} value='0'>{$this->__('All dates')}</option>";
            foreach ((array) $months as $arc_row ) {
                if ( 0 === $arc_row->year ) { continue;}
                $month = $this->_zero_ise( $arc_row->month, 2 );
                $year  = $arc_row->year;
                $output .= sprintf("<option %s value='%s'>%s</option>\n", $this->_get_selected( $m, $year . $month),
                    $this->_esc_attr( $arc_row->year . $month ),sprintf($this->__('%1$s %2$d'),$tp_locale->get_month( $month ), $year));
            }
            $output .= "</select></dd></li><!-- row months-dropdown -->";
            return $output;
        }//587
        protected function _get_view_switcher($current_mode):string{
            $output  = "<li class='row view-switcher'>";
            $output .= "<input name='mode' type='hidden' value='{$this->_esc_attr( $current_mode )}' />";
            $output .= "</li><!-- view-switcher -->";
            foreach ( $this->_modes as $mode => $title ){
                $classes = ["view-{$mode}"];
                $aria_current = '';
                if ( $current_mode === $mode ) {
                    $classes[] = 'current';
                    $aria_current = ' aria-current="page"';
                }
                $output .= "<li class='row view-switcher loop'>";
                $output .= sprintf("<dd><a href='%s' class='%s' id='view_switch_$mode' {$aria_current}><span class='screen-reader-text'>%s</span></a></dd>",
                    $this->_esc_url( $this->_remove_query_arg( 'attachment-filter', $this->_add_query_arg( 'mode', $mode ) ) ), implode(' ',$classes),$title);
                $output .= "</li>\n<!-- view-switcher loop -->";
            }
            return $output;
        }//684
        protected function _get_comments_bubble( $post_id, $pending_comments ):string{
            $approved_comments = $this->_get_comments_number();
            $approved_comments_number = $this->_number_format_i18n( $approved_comments );
            $pending_comments_number  = $this->_number_format_i18n( $pending_comments );
            $approved_only_phrase = sprintf($this->_n( '%s comment', '%s comments', $approved_comments ),$approved_comments_number);
            $approved_phrase = sprintf($this->_n( '%s approved comment', '%s approved comments', $approved_comments ),
                $approved_comments_number);
            $pending_phrase = sprintf($this->_n( '%s pending comment', '%s pending comments', $pending_comments ),
                $pending_comments_number);/* translators: %s: Number of comments. */
            $output  = "<div class='adm-segment comments-bubble '><ul><li>";
            if ( ! $approved_comments && ! $pending_comments ) {
                $output .= sprintf("<dt><span aria-hidden='true'>&#8212;</span><span class='screen-reader-text'>%s</span></dt>",
                    $this->__( 'No comments' ));// No comments at all.
                $output .= "</li><li>";
            }elseif ( $approved_comments && 'trash' === $this->_get_post_status( $post_id ) ) {
                $output .= sprintf("<dt><p class='post-com-count post-com-count-approved'><span class='comment-count-approved' aria-hidden='true'>%s</span><span class='screen-reader-text'>%s</span></p></dt>",
                    $approved_comments_number,$pending_comments ? $approved_phrase : $approved_only_phrase);
                $output .= "</li><li>";
            } elseif ( $approved_comments ) {
                $output .= sprintf("<dd><a href='%s' class='post-com-count post-com-count-approved'><span class='comment-count-approved' aria-hidden='true'>%s</span><span class='screen-reader-text'>%s</span></a></dd>",
                    $this->_esc_url( $this->_add_query_arg(['p'=> $post_id,'comment_status' => 'approved',],$this->_admin_url( 'edit-comments.php' ))),
                    $approved_comments_number, $pending_comments ? $approved_phrase : $approved_only_phrase);
                $output .= "</li><li>";
            }else{
                $output .= sprintf("<dt><p class='post-com-count post-com-count-no-comments'><span class='comment-count-approved' aria-hidden='true'>%s</span><span class='screen-reader-text'>%s</span></p></dt>",
                    $approved_comments_number,$pending_comments ? $this->__('No approved comments'):$this->__('No comments'));
                $output .= "</li><li>";
            }
            if ( $pending_comments ) {
                $output .= sprintf("<dd><a href='%s' class='post-com-count post-com-count-pending'><span class='comment-count-approved' aria-hidden='true'>%s</span><span class='screen-reader-text'>%s</span></a></dd>",
                    $this->_esc_url($this->_add_query_arg(['p'=> $post_id,'comment_status' => 'moderated',],$this->_admin_url('edit-comments.php'))),
                    $pending_comments_number,$pending_phrase);
                $output .= "</li><li>";
            }else{
                $output .= sprintf("<dt><p class='post-com-count post-com-count-pending post-com-count-no-pending'><span class='comment-count-approved' aria-hidden='true'>%s</span><span class='screen-reader-text'>%s</span></p></dt>",
                    $pending_comments_number, $approved_comments ? $this->__('No pending comments'):$this->__('No comments'));
                $output .= "</li><li>";
            }
            $output .= "</li></ul></div><!-- adm-segment comments-bubble -->";
            return $output;
        }//718
        public function get_pagenum():int{
            $pagenum = isset( $_REQUEST['paged'] ) ? $this->_abs_int( $_REQUEST['paged'] ) : 0;
            if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] ) {
                $pagenum = $this->_pagination_args['total_pages'];}
            return max( 1, $pagenum );
        }//811
        protected function _get_items_per_page($default = 20, $option = null):string{
            $per_page = (int) $this->_get_user_option( $option );
            if ( empty( $per_page ) || $per_page < 1 ) {
                $per_page = $default;
            }
            return (int) $this->_apply_filters( (string)($option), $per_page );
        }//830
        protected function _get_pagination( $which ):string{
            if (empty($this->_pagination_args)){ return false;}
            $total_items     = $this->_pagination_args['total_items'];
            $total_pages     = $this->_pagination_args['total_pages'];
            $infinite_scroll = $this->_pagination_args['infinite_scroll'] ?? false;
            if ( 'top' === $which && $total_pages > 1 ) {
                $this->_screen->render_screen_reader_content( 'heading_pagination' );
            }
            $output  = "<span class='displaying-num'>";
            $output .= sprintf($this->_n( '%s item', '%s items', $total_items ), $this->_number_format_i18n( $total_items ));
            $output .= "</span>";
            $current              = $this->get_pagenum();
            $removable_query_args = $this->_tp_removable_query_args();//http changed to https
            $current_url = $this->_set_url_scheme( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
            $current_url = $this->_remove_query_arg( $removable_query_args, $current_url );
            $page_links = array();
            $total_pages_before = "<span class='paging-input'>";
            $total_pages_after  = '</span>';//todo </span>
            $disable_first = false;
            $disable_last  = false;
            $disable_prev  = false;
            $disable_next  = false;
            if ( 1 === $current ) {
                $disable_first = true;
                $disable_prev  = true;
            }
            if ( $total_pages === $current ) {
                $disable_last = true;
                $disable_next = true;
            }
            if ( $disable_first ) {
                $page_links[] = "<span class='table-nav-pages-nav-span button disabled' aria-hidden='true'>&laquo;</span>";
            } else {
                $page_links[] = sprintf(
                    "<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                    $this->_esc_url( $this->_remove_query_arg( 'paged', $current_url ) ),
                    $this->__( 'First page' ),'&laquo;');
            }
            if ( $disable_prev ) {
                $page_links[] = "<span class='table-nav-pages-nav-span button disabled' aria-hidden='true'>&lsaquo;</span>";
            }else {
                $page_links[] = sprintf(
                    "<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                    $this->_esc_url( $this->_add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ),
                    $this->__( 'Previous page' ), '&lsaquo;');
            }
            if ( 'bottom' === $which ) {
                $html_current_page  = $current;
                $total_pages_before  = "<span class='screen-reader-text'>{$this->__('Current Page')}</span>";
                $total_pages_before .= "<span id='table_paging' class='paging-input'><span class='table-nav-paging-text'>";
            }else{
                $html_current_page = sprintf("%s<dd><input class='current-page' id='current_page_selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='table-nav-paging-text'>",//todo </dd>
                    "<dt><label for='current_page_selector' class='screen-reader-text'>{$this->__('Current Page')}</label></dt>",$current, strlen( $total_pages ));
            }
            $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", $this->_number_format_i18n( $total_pages ) );
            $page_links[]  = $total_pages_before;
            $page_links[] .= sprintf($this->_x( '%1$s of %2$s', 'paging' ),$html_current_page, $html_total_pages);
            $page_links[]  .= $total_pages_after;
            if ( $disable_next ) {
                $page_links[]  = "<span class='table-nav-pages-nav-span button disabled' aria-hidden='true'>&rsaquo;</span>";
            }else{
                $page_links[] = sprintf("<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                    $this->_esc_url( $this->_add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ), $this->__( 'Next page' ),'&rsaquo;');
            }
            if ( $disable_last ) {
                $page_links[] = "<span class='table-nav-pages-nav-span button disabled' aria-hidden='true'>&raquo;</span>";
            } else {
                $page_links[] = sprintf(
                    "<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                    $this->_esc_url( $this->_add_query_arg( 'paged', $total_pages, $current_url ) ), $this->__( 'Last page' ), '&raquo;');
            }
            $pagination_links_class = 'pagination-links';
            if ( ! empty( $infinite_scroll ) ) {
                $pagination_links_class .= ' hide-if-js';
            }
            $output .= "\n<span class='$pagination_links_class'>" . implode( "\n", $page_links ) . '</span>';
            if ( $total_pages ) {
                $page_class = $total_pages < 2 ? ' one-page' : '';
            } else { $page_class = ' no-pages';}
            $this->__pagination = "<div class='adm-block nav-pages{$page_class}'>$output</div><!-- adm-block nav-pages -->";
            return $this->__pagination;
        }//870
        //get_columns() moved to Adm_Segments_Base
        public function get_blocks(){}
        protected function _get_sortable_blocks():array{
            return [];
        }//1024
        /**
         * @return mixed
         */
        protected function _get_default_primary_name(){
            $blocks = $this->get_blocks();
            $block  = '';
            if (empty($blocks)){ return $block;}
            foreach ( $blocks as $col => $column_name ) {
                if ( 'cb' === $col ) { continue;}
                $block = $col;
                break;
            }
            return $block;
        }//1035
        /**
         * @return mixed
         */
        protected function _get_primary_name(){
            $columns = $this->_get_column_headers( $this->_screen );
            $default = $this->_get_default_primary_name();
            if ( ! isset( $columns[ $default ] ) ) {
                $default = (new self)->_get_default_primary_name();
            }
            $column = $this->_apply_filters( 'list_segment_primary_column', $default, $this->_screen->id );
            if ( empty( $column ) || ! isset( $columns[ $column ] ) ) { $column = $default;}
            return $column;
        }//1075
        /**
         * @return mixed
         */
        protected function _get_block_info(){
            $columns = $this->_get_column_headers( $this->_screen );
            $hidden  = $this->_get_hidden_columns( $this->_screen );
            $sortable_columns = $this->_get_sortable_blocks();
            $_sortable = $this->_apply_filters( "manage_{$this->_screen->id}_sortable_columns", $sortable_columns );
            $sortable = [];
            foreach ( $_sortable as $id => $data ) {
                if(empty($data)){continue;}
                $data = (array) $data;
                if(!isset($data[1])){ $data[1] = false;}
                $sortable[ $id ] = $data;
            }
            $primary               = $this->_get_primary_name();
            $this->_segment_headers = array( $columns, $hidden, $sortable, $primary );
            return $this->_segment_headers;
        }//1109
        public function get_block_count(): int{
            @list ( $columns, $hidden ) = $this->_get_block_info();
            $hidden                    = array_intersect( array_keys( $columns ), array_filter( $hidden ) );
            return count( $columns ) - count( $hidden );
        }//1170
        public function get_block_headers( $with_id = true ):string{
            //original 'http://'
            $_http = 'https://' ?:'http://';
            @list( $segments, $hidden, $sortable, $primary ) = $this->_get_block_info();
            $output  = "";
            $current_url = $this->_set_url_scheme( $_http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
            $current_url = $this->_remove_query_arg( 'paged', $current_url );
            $current_orderby = $_GET['orderby'] ?? '';
            if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
                $current_order = 'desc';
            } else { $current_order = 'asc';}
            if (!empty( $segments['cb'] ) ) {
                static $cb_counter = 1;
                $segments['cb']  = "<li><dt><label for='cb_select_all_{$cb_counter}' class='screen-reader-text'>{$this->__( 'Select All' )}</label></dt>";
                $segments['cb'] .= "<dd><input id='cb_select_all_{$cb_counter}' type='checkbox' /></dd></li>";
                $segments['cb'] .= "";
                $cb_counter++;
            }
            foreach ( $segments as $segment_key => $segment_display_name ) {
                $class = ['manage-column', "segment-$segment_key"];
                if (in_array( $segment_key, $hidden, true)){$class[] = 'hidden';}
                if ( 'cb' === $segment_key ) {$class[] = 'check-column';}
                elseif ( in_array( $segment_key, ['posts', 'comments', 'links'],true)){$class[] = 'num';}
                if ( $segment_key === $primary ) { $class[] = 'segments-primary';}
                if ( isset( $sortable[ $segment_key ] ) ) {
                    @list( $orderby, $desc_first ) = $sortable[ $segment_key ];
                    if ( $current_orderby === $orderby ) {
                        $order = 'asc' === $current_order ? 'desc' : 'asc';
                        $class[] = 'sorted';
                        $class[] = $current_order;
                    } else {
                        $order = strtolower( $desc_first );
                        if ( ! in_array( $order, array( 'desc', 'asc' ), true ) ) {
                            $order = $desc_first ? 'desc' : 'asc';}
                        $class[] = 'sortable';
                        $class[] = 'desc' === $order ? 'asc' : 'desc';
                    }
                    $segment_display_name = sprintf(
                        "<a href='%s'><span>%s</span><span class='sorting-indicator'></span></a>",
                        $this->_esc_url( $this->_add_query_arg( compact( 'orderby', 'order' ), $current_url ) ),
                        $segment_display_name);
                }
                $tag   = ( 'cb' === $segment_key ) ? 'ul' : 'div';//todo  'td' : 'th'
                $scope = ( 'div' === $tag ) ? 'scope="col"' : '';
                $id    = $with_id ? "id='$segment_key'" : '';
                if ( ! empty( $class ) ) {
                    $class = "class='adm-segment column-headers" . implode( ' ', $class ) . "'";
                }
                $output  .= "<$tag $scope $id $class>$segment_display_name</$tag><!-- adm-segment column-headers -->";
            }
            return  $output;
        }
        public function get_display():string{
            $singular = $this->_args['singular'];
            $_singular = null;
            if ( $singular ){ $_singular = " data-tp_lists='list:$singular'";}
            $classes = implode( ' ', $this->_get_classes() );
            $output  = "<div class='adm-block display'>";
            $output .= "<nav class='display nav-block top '>{$this->_get_nav_block( 'top' )}</nav><!-- display nav-block top -->";
            $output .= "<div class='display heading-list'>{$this->_screen->get_render_screen_reader_content( 'heading_list' )}</div><!-- display heading-list -->";
            $output .= "<div class='display content $classes'><ul><li>";
            $output .= $this->get_block_headers();
            $output .= "</li><li id='the_list' $_singular>";
            $output .= $this->get_display_placeholder();
            $output .= "</li></ul></div><!-- display content -->";
            $output .= "<footer class='display footer'>{$this->get_block_headers( false )}</footer><!-- display footer -->";
            $output .= "<nav class='display nav-block bottom '>{$this->_get_nav_block( 'bottom' )}</nav><!-- display nav-block bottom -->";
            $output .= "</div><!-- adm-block display -->";
            return $output;
        }//1268
        protected function _get_classes():array{
            $mode = $this->_get_user_setting( 'posts_list_mode', 'list' );
            $mode_class = $this->_esc_attr( 'table-view-' . $mode );
            return ['wide_fat', 'fixed', 'striped', $mode_class, $this->_args['plural']];
        }//1310
        protected function _get_nav_block( $which ):string{
            $output  = "";
            if ( 'top' === $which ) {
                $output .=  $this->_tp_get_nonce_field( 'bulk-' . $this->_args['plural'] );
            }
            $output .= "<div class='adm-segment block-nav {$this->_esc_attr( $which )}'>";
            if ( $this->has_items() ){
                $output .= "<div class='actions bulk'>{$this->_get_multi_bulk_actions( $which )}</div>";
            }
            $output .= $this->_get_extra_nav_block( $which );
            $output .= $this->_get_pagination( $which );
            $output .= "</div>";
            return $output;
        }//1324
        /**
         * @param $which
         * @return mixed
         */
        protected function _get_extra_nav_block( $which ){/* no content */}//1353
        public function get_display_placeholder():string{
            $output  = "";
            if ($this->has_items() ) {
                $output .= "<li class='wrapper display-placeholder'>";
                $output .= $this->get_display_blocks();
                $output .= "</li><!-- wrapper placeholder 1 -->";
            }else{
                $output .= "<li class='wrapper display-placeholder'>";
                $output .= $this->get_no_items();
                $output .= "</li><!-- wrapper display-placeholder 2 -->";
            }
            return $output;
        }//1360

        /**
         * @return mixed
         */
        public function get_display_blocks(){
            $output  = "";
            foreach ((array) $this->items as $item ) { $output .= $this->get_single_block( $item );}
            return $output;
        }//1375
        /**
         * @param $item
         * @return string
         */
        public function get_single_block( $item ):string{
            return "<li class='wrapper single-row'><dd>{$this->_get_single_blocks($item )}</dd></li><!-- wrapper single-row -->";
        }//1388
        protected function _block_default( $item, ...$column_name ):string{
            return $column_name[$item];
        }//1398
        protected function _get_cb_block( $item ):string{
            return $item;
        }//1403
        /**
         * @param $item
         * @return string
         */
        protected function _get_single_blocks( $item ):string{
            @list( $segments, $hidden, $primary ) = $this->_get_block_info();
            $output  = ""; //, $sortable
            foreach ( $segments as $segment_name => $segment_display_name ) {
                $classes = "$segment_name segment-$segment_name";
                if ( $primary === $segment_name){ $classes .= ' has-block-actions segment-primary';}
                if (in_array( $segment_name, $hidden, true )){$classes .= ' hidden';}
                $data = 'data-block_name="' . $this->_esc_attr( $this->_tp_strip_all_tags( $segment_display_name ) ) . '"';
                if ( 'cb' === $segment_name ) {
                    $output .= "<div class='adm-segment block-name cb'><ul><li>";
                    $output .= $this->_get_cb_block( $item );
                    $output .= "</li></ul></div><!-- adm-block column-name cb -->";
                }elseif(method_exists($this,'_segment_'.$segment_name)){
                    $output .= $this->{'_segment_' . $segment_name}($item, $classes, $data, $primary);
                } elseif ( method_exists( $this, 'segment_' . $segment_name ) ) {
                    $output .= "<div class='adm-segment $classes' $data><ul><li>";
                    $output .= $this->{'segment_' . $segment_name }($item);
                    $output .= $this->_get_handle_block_actions( $item, $segment_name, $primary );
                    $output .= "</li></ul></div>";
                }else{
                    $output .= "<div class='adm-segment  $classes' $data><ul><li>";
                    $output .= $this->_block_default( $item, $segment_name );
                    $output .= $this->_get_handle_block_actions( $item, $segment_name, $primary );
                    $output .= "</li></ul></div><!-- adm-segment classes -->";
                }
            }
            return $output;
        }//1412
        protected function _get_handle_block_actions( $item, $column_name, $primary ):string{
            $output  = $primary ? "<dd><button type='button' class='toggle-row'><span class='screen-reader-text'>{$this->__('Show more details')}</span></button></dd>" : '';
            return $column_name[$item] === $output;
        }//1468
        public function async_response():void{
            $this->prepare_items();
            $rows  = "";
            if ( ! empty( $_REQUEST['no_placeholder'] ) ) {
                $rows .= $this->get_display_blocks();
            }else{$rows .= $this->get_display_placeholder();}
            $response = ['rows' => $rows];
            if ( isset( $this->_pagination_args['total_items'] ) ) {
                $response['total_items_i18n'] = sprintf($this->_n( '%s item', '%s items', $this->_pagination_args['total_items']),
                    $this->_number_format_i18n( $this->_pagination_args['total_items'] ));
            }
            if (isset($this->_pagination_args['total_pages'])){
                $response['total_pages']      = $this->_pagination_args['total_pages'];
                $response['total_pages_i18n'] = $this->_number_format_i18n( $this->_pagination_args['total_pages'] );
            }
            die( $this->_tp_json_encode( $response ) );
        }//1477
        protected function _get_js_vars():string{
            $args = ['class'  => get_class( $this ),
                'screen' => ['id'=> $this->_screen->id,'base' => $this->_screen->base,],];
            return sprintf("<script>list_args ='%s'; </script>\n", $this->_tp_json_encode( $args ));
        }//1511
    }
}else{die;}