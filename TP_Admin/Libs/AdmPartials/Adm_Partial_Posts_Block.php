<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-9-2022
 * Time: 16:30
 */
namespace TP_Admin\Libs\AdmPartials;
use TP_Core\Libs\TP_Taxonomy;
if(ABSPATH){
    class Adm_Partial_Posts_Block extends Adm_Partials { //todo refining later
        private $__user_posts_count;
        private $__sticky_posts_count = 0;
        private $__is_trash;
        protected $_hierarchical_display;
        protected $_comment_pending_count;
        protected $_current_level = 0;
        public function __construct( ...$args){
            $tpdb = $this->_init_db();
            parent::__construct(['plural' => 'posts','screen' => $args['screen'] ?? null,]);
            $post_type        = $this->_screen->post_type;
            $this->tp_post_type_object = $this->_get_post_type_object( $post_type );
            $exclude_states = $this->_get_post_stati(['show_in_admin_all_block' => false,]);
            $this->__user_posts_count = (int) $tpdb->get_var(
                $tpdb->prepare(TP_SELECT . " COUNT(1) FROM $tpdb->posts WHERE post_type = %s AND post_status NOT IN ( '" . implode( "','", $exclude_states ) . "' ) AND post_author = %d",$post_type,$this->_get_current_user_id()));
            if ( $this->__user_posts_count && empty( $_REQUEST['post_status'] ) && empty( $_REQUEST['all_posts'] ) && empty( $_REQUEST['author'] ) && empty( $_REQUEST['show_sticky'] )
                && ! $this->_current_user_can( $this->tp_post_type_object->cap->edit_others_posts )
            ) { $_GET['author'] = $this->_get_current_user_id();}
            $sticky_posts = $this->_get_option( 'sticky_posts' );
            if ( 'post' === $post_type && $sticky_posts ) {
                $sticky_posts = implode( ', ', array_map( [$this,'_abs_int'], (array) $sticky_posts ) );
                $this->__sticky_posts_count = (int) $tpdb->get_var(
                    $tpdb->prepare(TP_SELECT . " COUNT( 1 ) FROM $tpdb->posts WHERE post_type = %s AND post_status NOT IN ('trash', 'auto-draft') AND ID IN ($sticky_posts)",$post_type));
            }
        }//74
        public function set_hierarchical_display( $display ):void {
            $this->_hierarchical_display = $display;
        }//138
        public function async_user_can():string {
            return $this->_current_user_can( $this->_get_post_type_object( $this->_screen->post_type )->cap->edit_posts );
        }//145
        public function prepare_items():void{
            $tp_query = $this->_init_query();
            if ( ! empty( $_REQUEST['mode'] ) ) {
                $this->tp_mode = 'excerpt' === $_REQUEST['mode'] ? 'excerpt' : 'list';
                $this->_set_user_setting( 'posts_block_mode', $this->tp_mode );
            } else {$this->tp_mode = $this->_get_user_setting( 'posts_block_mode', 'list' );}
            $avail_post_stati = $this->_tp_edit_posts_query();
            $this->set_hierarchical_display(
                $this->_is_post_type_hierarchical( $this->_screen->post_type )
                && 'menu_order title' === $tp_query->query['orderby']
            );
            $post_type = $this->_screen->post_type;
            $per_page  = $this->_get_items_per_page( 'edit_' . $post_type . '_per_page' );
            $per_page = $this->_apply_filters( 'edit_posts_per_page', $per_page, $post_type );
            if ( $this->_hierarchical_display ) { $total_items = $tp_query->post_count;}
            elseif ( $tp_query->found_posts || $this->get_pagenum() === 1 ) {
                $total_items = $tp_query->found_posts;}
            else {
                $post_counts = (array) $this->_tp_count_posts( $post_type, 'readable' );
                if ( isset( $_REQUEST['post_status'] ) && in_array( $_REQUEST['post_status'], $avail_post_stati, true ) ) {
                    $total_items = $post_counts[ $_REQUEST['post_status'] ];
                } elseif ( isset( $_REQUEST['show_sticky'] ) && $_REQUEST['show_sticky'] ) {
                    $total_items = $this->__sticky_posts_count;
                } elseif ( isset( $_GET['author'] ) && $this->_get_current_user_id() === (int) $_GET['author'] ) {
                    $total_items = $this->__user_posts_count;
                } else {
                    $total_items = array_sum( $post_counts );
                    foreach ( $this->_get_post_stati( array( 'show_in_admin_all_block' => false ) ) as $state ) {
                        $total_items -= $post_counts[ $state ];
                    }
                }
            }
            $this->__is_trash = isset( $_REQUEST['post_status'] ) && 'trash' === $_REQUEST['post_status'];
            $this->_set_pagination_args(['total_items' => $total_items,'per_page' => $per_page,]);
        }//155
        public function has_items():bool {
            return $this->_have_posts();
        }//215
        public function get_no_items():string {
            $output  = "";
            if ( isset( $_REQUEST['post_status'] ) && 'trash' === $_REQUEST['post_status'] ) {
                $output .= $this->_get_post_type_object( $this->_screen->post_type )->labels->not_found_in_trash;
            } else {$output .= $this->_get_post_type_object( $this->_screen->post_type )->labels->not_found;}
            return $output;
        }//221
        protected function is_base_request():int {
            $vars = $_GET;
            unset( $vars['paged'] );
            if (empty( $vars )){ return true;}
            if (1 === count( $vars ) && ! empty( $vars['post_type'] )) {
                return $this->_screen->post_type === $vars['post_type'];
            }
            return 1 === count( $vars ) && ! empty( $vars['mode'] );
        }//236
        protected function _get_edit_link( $args, $label, $class = '' ):string{
            $url = $this->_add_query_arg( $args, 'edit.php' );
            $class_html   = '';
            $aria_current = '';
            if ( ! empty( $class ) ) {
                $class_html = sprintf(" class='%s'", $this->_esc_attr($class));
                if ( 'current' === $class ) { $aria_current = " aria-current='page'";}
            }
            return sprintf("<dd><a href='%s' %s %s>%s</a></dd>",$this->_esc_url( $url ),$class_html,$aria_current,$label);
        }//259
        protected function _get_views():array{
            $post_type = $this->_screen->post_type;
            if(!empty($this->tp_locked_post_status)){ return [];}
            $status_links = [];
            $num_posts    = $this->_tp_count_posts( $post_type, 'readable' );
            $total_posts  = array_sum( (array) $num_posts );
            $class        = '';
            $current_user_id = $this->_get_current_user_id();
            $all_args        = ['post_type' => $post_type];
            $mine            = '';
            foreach ( $this->_get_post_stati( array( 'show_in_admin_all_block' => false ) ) as $state ) {
                $total_posts -= $num_posts->$state;
            }
            if($this->__user_posts_count && $this->__user_posts_count !== $total_posts ) {
                if(isset($_GET['author'])&&($current_user_id === (int) $_GET['author'])){$class = 'current';}
                $mine_args = ['post_type' => $post_type,'author' => $current_user_id,];
                $mine_inner_html = sprintf(
                    $this->_nx("Mine <span class='count'>(%s)</span>","Mine <span class='count'>(%s)</span>",$this->__user_posts_count,'posts'),
                    $this->_number_format_i18n( $this->__user_posts_count )
                );
                $mine_inner_html = "<dt>$mine_inner_html</dt>";
                $mine = $this->_get_edit_link( $mine_args, $mine_inner_html, $class );
                $all_args['all_posts'] = 1;
                $class                 = '';
            }
            if(empty($class) && ($this->is_base_request() || isset( $_REQUEST['all_posts']))){ $class = 'current';}
            $all_inner_html = sprintf($this->_nx("All <span class='count'>(%s)</span>","All <span class='count'>(%s)</span>",
                    $total_posts,'posts'),$this->_number_format_i18n( $total_posts ));
            $all_inner_html = "<dt>$all_inner_html</dt>";
            $status_links['all'] = $this->_get_edit_link( $all_args, $all_inner_html, $class );
            if ( $mine ){$status_links['mine'] = $mine;}
            foreach ( $this->_get_post_stati( array( 'show_in_admin_status_block' => true ), 'objects' ) as $status ) {
                $class = '';
                $status_name = $status->name;
                if (empty( $num_posts->$status_name ) || ! in_array( $status_name, $this->tp_avail_post_stati, true )) {
                    continue;}
                if ( isset( $_REQUEST['post_status'] ) && $status_name === $_REQUEST['post_status'] ) {
                    $class = 'current';}
                $status_args = ['post_status' => $status_name,'post_type'=> $post_type,];
                $status_label = sprintf(
                    $this->_translate_nooped_plural( $status->label_count, $num_posts->$status_name ),
                    $this->_number_format_i18n( $num_posts->$status_name )
                );
                $status_label = "<dt>$status_label</dt>";
                $status_links[ $status_name ] = $this->_get_edit_link( $status_args, $status_label, $class );
            }
            if ( ! empty( $this->sticky_posts_count ) ) {
                $class = ! empty( $_REQUEST['show_sticky'] ) ? 'current' : '';
                $sticky_args = ['post_type' => $post_type,'show_sticky' => 1,];
                $sticky_inner_html = sprintf($this->_nx("Sticky <span class='count'>(%s)</span>",
                        "Sticky <span class='count'>(%s)</span>",$this->sticky_posts_count,'posts'
                    ), $this->_number_format_i18n( $this->sticky_posts_count ));
                $sticky_inner_html = "<dt>$sticky_inner_html</dt>";
                $sticky_link = ['sticky' => $this->_get_edit_link( $sticky_args, $sticky_inner_html, $class ),];
                $split        = 1 + array_search( ( isset( $status_links['publish'] ) ? 'publish' : 'all' ), array_keys( $status_links ), true );
                $status_links = array_merge( array_slice( $status_links, 0, $split ), $sticky_link, array_slice( $status_links, $split ) );
            }
            return $status_links;
        }//290
        protected function _get_bulk_actions():array{
            $actions       = [];
            $post_type_obj = $this->_get_post_type_object( $this->_screen->post_type );
            if ( $this->_current_user_can( $post_type_obj->cap->edit_posts ) ) {
                if($this->__is_trash){ $actions['untrash'] = $this->__( 'Restore' );}
                else {$actions['edit'] = $this->__( 'Edit' );}
            }
            if ( $this->_current_user_can( $post_type_obj->cap->delete_posts ) ) {
                if($this->__is_trash || ! EMPTY_TRASH_DAYS){ $actions['delete'] = $this->__('Delete permanently');}
                else {$actions['trash'] = $this->__('Move to Trash');}
            }
            return $actions;
        }//421
        protected function _get_categories_dropdown( $post_type ):array{
            if ( false !== $this->_apply_filters( 'disable_categories_dropdown', false, $post_type)){return null;}
            $output  = "";
            if ( $this->_is_object_in_taxonomy( $post_type, 'category' ) ) {
                $dropdown_options = ['show_option_all' => $this->_get_taxonomy( 'category' )->labels->all_items,
                    'hide_empty' => 0,'hierarchical' => 1,'show_count' => 0,'orderby' => 'name','selected' => $this->tp_cat,];
                $output .= "<dt><label for='cat' class='screen-reader-text'>{$this->_get_taxonomy( 'category' )->labels->filter_by_item}</label></dt>";
                $output .= $this->_tp_get_dropdown_categories( $dropdown_options );
            }
            return $output;
        }//453
        protected function _get_formats_dropdown( $post_type ):array{
            if ( $this->_apply_filters( 'disable_formats_dropdown', false, $post_type)){return null;}
            if ($this->__is_trash || ! $this->_is_object_in_taxonomy($post_type, 'post_format')){return null;}
            $used_post_formats = $this->_get_terms(['taxonomy' => 'post_format','hide_empty' => true,]);
            if ( ! $used_post_formats ){return null;}
            $displayed_post_format = $_GET['post_format'] ?? '';
            $output  = "<dt><label for='filter_by_format' class='screen-reader-text'>{$this->__('Filter by post format')}</label></dt>";
            $output .= "<dd><select name='post_format' id='filter_by_format' >";
            $output .= "<option {$this->_get_selected( $displayed_post_format, '' )} value=''>{$this->__('All formats')}</option>";
            foreach ( $used_post_formats as $used_post_format ){
                $slug = str_replace( 'post-format-', '', $used_post_format->slug );
                $pretty_name = $this->_get_post_format_string( $slug );
                if ( 'standard' === $slug ){continue;}
                $output .= "<option {$this->_get_selected( $displayed_post_format,$slug)} value={$this->_esc_attr($slug)}>{$this->_esc_html( $pretty_name )}</option>";
            }
            $output .= "</select></dd>";
            return $output;
        }//492
        protected function _get_extra_nav_block( $which ){
            if('top'!== $which){ return false;}
            $output  = "<ul class='adm-segment extra-nav-block actions'>";
            $output .= "<li class='wrapper'><dd>{$this->_get_months_dropdown( $this->_screen->post_type )}</dd></li><!-- wrapper 1 -->";
            $output .= "<li class='wrapper'><dd>{$this->_get_categories_dropdown( $this->_screen->post_type )}</dd></li><!-- wrapper 2 -->";
            $output .= "<li class='wrapper'><dd>{$this->_get_formats_dropdown( $this->_screen->post_type )}</dd></li><!-- wrapper 3 -->";
            $output .= "<li class='wrapper'>{$this->_get_action( 'restrict_manage_posts', $this->_screen->post_type, $which )}</li><!-- wrapper 4 -->";
            $output .= "<li class='wrapper'><dd>{$this->_get_submit_button( $this->__( 'Filter' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) )}</dd></li><!-- wrapper 5 -->";
            if ( $this->__is_trash && $this->has_items() && $this->_current_user_can( $this->_get_post_type_object( $this->_screen->post_type )->cap->edit_others_posts )){
                $output .= "<li class='wrapper'><dd>{$this->_get_submit_button( $this->__( 'Empty Trash' ), 'apply', 'delete_all', false )}</dd></li><!-- wrapper 6 -->";
            }
            if($this->_has_action('manage_posts_extra_nav_block')){
                $output .= "<li class='wrapper'><dd>{$this->_get_action( 'manage_posts_extra_nav_block', $which )}</dd></li><!-- wrapper 7 -->";
            }
            $output .= "</ul><!-- adm-segment extra-nav-block actions -->";
            return $output;
        }//552
        public function get_current_action():bool {
            if ( isset( $_REQUEST['delete_all'] ) || isset( $_REQUEST['delete_all2'] ) ) {
                return 'delete_all';}
            return parent::get_current_action();
        }//610
        protected function _get_classes():array{
            $mode_class = $this->_esc_attr("table-view-{$this->tp_mode}");
            return ['wide_fat','fixed','striped',$mode_class,
                $this->_is_post_type_hierarchical( $this->_screen->post_type ) ? 'pages' : 'posts',
            ];
        }//623
        public function get_blocks(){
            $post_type = $this->_screen->post_type;
            $posts_columns = [];
            $posts_columns['cb'] = "<dd><input type='checkbox' /></dd>";
            $posts_columns['title'] = "<dt>{$this->_x( 'Title', 'column name' )}</dt>";
            if ( $this->_post_type_supports( $post_type, 'author' ) ) {
                $posts_columns['author'] = "<dt>{$this->__( 'Author' )}</dt>";
            }
            $taxonomies = $this->_get_object_taxonomies( $post_type, 'objects' );
            $taxonomies = $this->_tp_filter_object_list( $taxonomies,['show_admin_column' => true],'and','name');
            $taxonomies = $this->_apply_filters( "manage_taxonomies_for_{$post_type}_columns", $taxonomies, $post_type );
            $taxonomies = array_filter( $taxonomies, 'taxonomy_exists' );
            foreach ( $taxonomies as $taxonomy ) {
                if ( 'category' === $taxonomy ){$column_key = 'categories';}
                elseif ( 'post_tag' === $taxonomy ){$column_key = 'tags';}
                else { $column_key = 'taxonomy-' . $taxonomy;}
                $_get_tax = $this->_get_taxonomy( $taxonomy );
                $get_taxonomy = null;
                if($_get_tax instanceof TP_Taxonomy ){$get_taxonomy = $_get_tax;}
                $posts_columns[ $column_key ] = $get_taxonomy->labels->name;
            }
            $post_status = ! empty( $_REQUEST['post_status'] ) ? $_REQUEST['post_status'] : 'all';
            if($this->_post_type_supports( $post_type,'comments') && !in_array( $post_status, array( 'pending', 'draft', 'future' ), true )){
                $posts_columns['comments'] = sprintf( "<span class='vers comment-grey-bubble' title='%1\$s'><span class='screen-reader-text'>%2\$s</span></span>",
                    $this->_esc_attr__('Comments'),$this->__('Comments'));
            }
            $posts_columns['date'] = $this->__( 'Date' );
            if ( 'page' === $post_type ) {
                $posts_columns = $this->_apply_filters( 'manage_pages_columns', $posts_columns );
            } else { $posts_columns = $this->_apply_filters( 'manage_posts_columns', $posts_columns, $post_type );}
            return $this->_apply_filters( "manage_{$post_type}_posts_columns", $posts_columns );
        }//640
        protected function _get_sortable_blocks():array {
            return ['title' => 'title','parent' => 'parent','comments' => 'comment_count','date' => ['date', true],];
        }//745
        public function get_display_blocks($level = 0, ...$posts):string{
            $tp_query = $this->_init_query();
            if ( empty( $posts ) ) { $posts = $tp_query->posts;}
            $output = $this->_add_filter( 'the_title', 'esc_html' );
            if ( $this->_hierarchical_display ) {
                $output .= $this->__get_display_blocks_hierarchical( $posts, $this->get_pagenum(), $this->tp_posts_per_page );
            } else {$output .= $this->__get_display_blocks( $posts, $level );}
            return $output;
        }//760
        private function __get_display_blocks( $posts, $level = 0 ):string{
            $post_type = $this->_screen->post_type;
            $post_ids = [];
            $output  = "";
            foreach ( $posts as $a_post ) { $post_ids[] = $a_post->ID;}
            if ($this->_post_type_supports( $post_type,'comments')){
                $this->_comment_pending_count = $this->_get_pending_comments_num( $post_ids );
                $output .= $this->_comment_pending_count;
            }
            foreach ( $posts as $post ) { $output .= $this->get_single_block( $post, $level );}
            return $output;
        }//780 ?
        private function __get_display_blocks_hierarchical( $pages, $pagenum = 1, $per_page = 20 ):string{
            $tpdb = $this->_init_db();
            $level = 0;
            if ( ! $pages ) {
                $pages = $this->_get_pages(['sort_column' => 'menu_order']);
                if(!$pages){return;}
            }
            if ( empty( $_REQUEST['s'])){
                $top_level_pages = [];
                $children_pages  = [];
                foreach ( $pages as $page ) {
                     if ( $page->post_parent === $page->ID ) {
                        $page->post_parent = 0;
                        $tpdb->update( $tpdb->posts, array( 'post_parent' => 0 ),['ID' => $page->ID]);
                        $this->_clean_post_cache( $page );
                    }
                    if ( $page->post_parent > 0 ) { $children_pages[ $page->post_parent ][] = $page;}
                    else {$top_level_pages[] = $page;}
                }
                $pages = &$top_level_pages;
            }
            $count      = 0;
            $start      = ( $pagenum - 1 ) * $per_page;
            $end        = $start + $per_page;
            $to_display = [];
            $output  = "";
            foreach ( $pages as $page ) {
                if ( $count >= $end ) { break;}
                if ( $count >= $start ) {$to_display[ $page->ID ] = $level;}
                $count++;
                if ( isset( $children_pages ) ) {
                    $output .= $this->__get_page_blocks( $children_pages, $count, $page->ID, $level + 1, $pagenum, $per_page, $to_display );
                }
            }
            if ( isset( $children_pages ) && $count < $end ) {
                foreach ( $children_pages as $orphans ) {
                    foreach ( $orphans as $op ) {
                        if ( $count >= $end ){ break;}
                        if ( $count >= $start ) { $to_display[ $op->ID ] = 0;}
                        $count++;
                    }
                }
            }
            ob_start();
            $ids = array_keys( $to_display );
            $this->_prime_post_caches( $ids );
            if ( ! isset( $GLOBALS['post'] ) ) { $GLOBALS['post'] = reset( $ids );}
            $output .= ob_get_clean();
            foreach ( $to_display as $page_id => $level ) {
                $output .= "\t";
                $output .= $this->get_single_block( $page_id, $level );
            }
            return $output;
        }//806
        private function __get_page_blocks( &$children_pages, &$count, $parent, $level, $pagenum, $per_page, &$to_display ):string{
            if(!isset($children_pages[ $parent ])){ return;}
            $start = ( $pagenum - 1 ) * $per_page;
            $end   = $start + $per_page;
            $output  = "";
            foreach ( $children_pages[ $parent ] as $page ) {
                if ( $count >= $end ){ break;}
                if ( $count === $start && $page->post_parent > 0 ) {
                    $my_parents = array();
                    $my_parent  = $page->post_parent;
                    while ( $my_parent ) {
                        $parent_id = $my_parent;
                        if ( is_object( $my_parent ) ) {$parent_id = $my_parent->ID;}
                        $my_parent    = $this->_get_post( $parent_id );
                        $my_parents[] = $my_parent;
                        if ( ! $my_parent->post_parent ){break;}
                        $my_parent = $my_parent->post_parent;
                    }
                    $num_parents = count( $my_parents );
                    while ( $my_parent = array_pop( $my_parents ) ) {
                        $to_display[ $my_parent->ID ] = $level - $num_parents;
                        $num_parents--;
                    }
                }
                if ( $count >= $start ){ $to_display[ $page->ID ] = $level;}
                $count++;
                $output .= $this->__get_page_blocks( $children_pages, $count, $page->ID, $level + 1, $pagenum, $per_page, $to_display );
            }
            unset( $children_pages[ $parent ] );
            return $output;
        }//914
        public function get_cb_block( $item ):string{
            $post = $item;
            $show = $this->_current_user_can( 'edit_post', $post->ID );
            $output  = "";
            if ( $this->_apply_filters( 'tp_list_table_show_post_checkbox', $show, $post ) ){
                $draft1 = sprintf( $this->__( 'Select %s' ), $this->_draft_or_post_title());
                $draft2 = sprintf($this->__('&#8220;%s&#8221; is locked'),$this->_draft_or_post_title());
                $output .= "<dt><label for='cb_select_{$this->_get_the_ID()}' class='screen-reader-text'>$draft1</label></dt>";
                $output .= "<dd><input name='post[]' id='cb_select_{$this->_get_the_ID()}' type='checkbox' value='{$this->_get_the_ID()}' /></dd>";
                $output .= "<div class='locked-indicator'>";
                $output .= "<span class='locked-indicator-icon' aria-hidden='true'></span>";
                $output .= "<span class='screen-reader-text'>$draft2</span>";
                $output .= "</div>";
            }
            return $output;
        }//978
        protected function _get_block_title( $post, $classes, $data, $primary ):string{
            $output  = "<div class='t-cell $classes page-title' $data>";
            $output .= $this->get_block_title( $post );
            $output .= $this->_get_handle_block_actions( $post, 'title', $primary );
            $output .= "</div>";
            return $output;
        }//1026
        public function get_block_title($post):string{
            $output  = "";
            if ($this->_hierarchical_display && 0 === $this->_current_level && (int)$post->post_parent > 0) {
                $find_main_page = (int) $post->post_parent;
                while ( $find_main_page > 0 ) {
                    $parent = $this->_get_post( $find_main_page );
                    if(is_null($parent)){ break;}
                    $this->_current_level++;
                    $find_main_page = (int) $parent->post_parent;
                    if ( ! isset( $parent_name ) ) {
                        $parent_name = $this->_apply_filters( 'the_title', $parent->post_title, $parent->ID );
                    }
                }
            }
            $can_edit_post = $this->_current_user_can( 'edit_post', $post->ID );
            if ( $can_edit_post && 'trash' !== $post->post_status ) {
                $lock_holder = $this->_tp_check_post_lock( $post->ID );
                if ( $lock_holder ) {
                    $lock_holder   = $this->_get_user_data( $lock_holder );
                    $locked_avatar = $this->_get_avatar( $lock_holder->ID, 18 );
                    $locked_text = $this->_esc_html( sprintf( $this->__( '%s is currently editing' ), $lock_holder->display_name ) );
                } else {
                    $locked_avatar = '';
                    $locked_text   = '';
                }
                $output .= "<div class='locked-info'><span class='locked-avatar'>$locked_avatar</span><span class='locked-text'>$locked_text</span></div>\n";
            }
            $pad = str_repeat( '&#8212; ', $this->_current_level );
            $title = $this->_draft_or_post_title();
            $output .= "<dt><strong>";
            if($can_edit_post && 'trash' !== $post->post_status){
                $output .= sprintf("<a href='%s' class='row-title' aria-label='%s'>%s%s</a>",$this->_get_edit_post_link( $post->ID ),
                    $this->_esc_attr( sprintf( $this->__( '&#8220;%s&#8221; (Edit)' ),$title)),$pad,$title);
            }else{
                $output .= sprintf("<span>%s%s</span>",$pad,$title);
            }
            $output .= $this->_post_states( $post );
            if ( isset( $parent_name ) ) {
                $post_type_object = $this->_get_post_type_object( $post->post_type );
                $output .= "| {$post_type_object->labels->parent_item_colon} : {$this->_esc_html( $parent_name )}";
            }
            $output .= "</strong></dt>\n";
            if ( 'excerpt' === $this->tp_mode
                && ! $this->_is_post_type_hierarchical( $this->_screen->post_type) && $this->_current_user_can('read_post',$post->ID)){
                if ( $this->_post_password_required( $post ) ) {
                    $output .= "<span class='protected-post-excerpt'>{$this->_esc_html( $this->_get_the_excerpt() )}</span>";
                }else{ $output .= $this->_esc_html( $this->_get_the_excerpt() );}
            }
            $output .= $this->_get_inline_data( $post );
            return $output;
        }//1042
        public function get_block_date($post):string{
            $output  = "";
            if ( '0000-00-00 00:00:00' === $post->post_date ) {
                $t_time    = $this->__( 'Unpublished' );
                $time_diff = 0;
            } else {
                $t_time = sprintf(
                    $this->__( '%1$s at %2$s' ),
                    $this->_get_the_time( $this->__( 'Y/m/d' ), $post ),
                    $this->_get_the_time( $this->__( 'g:i a' ), $post )
                );
                $time      = $this->_get_post_timestamp( $post );
                $time_diff = time() - $time;
            }
            if ( 'publish' === $post->post_status ) {
                $status = $this->__( 'Published' );
            } elseif ( 'future' === $post->post_status ) {
                if ( $time_diff > 0 ) {$status = "<strong class='error-message'>{$this->__( 'Missed schedule' )}</strong>";}
                else {$status = $this->__( 'Scheduled' );}
            } else {$status = $this->__( 'Last Modified' );}
            $status = $this->_apply_filters( 'post_date_column_status', $status, $post, 'date', $this->tp_mode );
            if ( $status ) {$output .= "<dt>{$status}</dt>";}
            $output .= $this->_apply_filters( 'post_date_column_time', $t_time, $post, 'date', $this->tp_mode );
            return $output;
        }//1139
        public function get_block_comments($post):string{
            $pending_comments = $this->_comment_pending_count[ $post->ID ] ?? 0;
            $output  = "<div class='post-com-count-wrapper'>";
            $output .= $this->_get_comments_bubble( $post->ID, $pending_comments );
            $output .= "</div>";
            return $output;
        }//1210
        public function get_block_author( $post ):string{
            $args = ['post_type' => $post->post_type,'author' => $this->_get_the_author_meta( 'ID' ),];
            return $this->_get_edit_link( $args, $this->_get_the_author() );
        }//1229
        public function get_block_default( $item, $column_name ):string{
            $output  = "";
            $post = $item;
            if ( 'categories' === $column_name){ $taxonomy = 'category';}
            elseif ( 'tags' === $column_name ){ $taxonomy = 'post_tag';}
            elseif ( 0 === strpos( $column_name, 'taxonomy-')){$taxonomy = substr( $column_name, 9 );}
            else {$taxonomy = false;}
            if ( $taxonomy ) {
                $_taxonomy_object = $this->_get_taxonomy( $taxonomy );
                $taxonomy_object = null;
                if($_taxonomy_object instanceof TP_Taxonomy){
                    $taxonomy_object = $_taxonomy_object;
                }
                $terms = $this->_get_the_terms( $post->ID, $taxonomy );
                if(is_array($terms)){
                    $term_links = [];
                    foreach ( $terms as $t ) {
                        $posts_in_term_qv = [];
                        if ( 'post' !== $post->post_type ) { $posts_in_term_qv['post_type'] = $post->post_type;}
                        if ( $taxonomy_object->query_var ) {
                            $posts_in_term_qv[ $taxonomy_object->query_var ] = $t->slug;
                        } else {
                            $posts_in_term_qv['taxonomy'] = $taxonomy;
                            $posts_in_term_qv['term']     = $t->slug;
                        }
                        $label = $this->_esc_html( $this->_sanitize_term_field( 'name', $t->name, $t->term_id, $taxonomy, 'display' ) );
                        $term_links[] = $this->_get_edit_link( $posts_in_term_qv, $label );
                    }
                    $term_links = $this->_apply_filters( 'post_column_taxonomy_links', $term_links, $taxonomy, $terms );
                    $output .= implode( $this->__( ', ' ), $term_links );
                }else{ $output .= "<span aria-hidden='true'>&#8212;</span><span class='screen-reader-text'>{$taxonomy_object->labels->no_terms}</span>";}
            }
            if ( $this->_is_post_type_hierarchical( $post->post_type ) ) {
                $output .= $this->_do_action( 'manage_pages_custom_column', $column_name, $post->ID );
            }else{ $output .= $this->_do_action( 'manage_posts_custom_column', $column_name, $post->ID );}
            $output .= $this->_do_action( "manage_{$post->post_type}_posts_custom_column", $column_name, $post->ID );
            return $output;
        }//1246
        public function get_single_block( $post, $level = 0 ):string{
            $global_post = $this->_get_post();
            $post = $this->_get_post( $post );
            $this->_current_level = $level;
            $GLOBALS['post'] = $post;
            $this->_setup_postdata( $post );
            $classes = 'i-edit author-' . ( $this->_get_current_user_id() === (int) $post->post_author ? 'self' : 'other' );
            $lock_holder = $this->_tp_check_post_lock( $post->ID );
            if ( $lock_holder ) { $classes .= ' tp-locked';}
            if ( $post->post_parent ) {
                $count    = count( $this->_get_post_ancestors( $post->ID ) );
                $classes .= ' level-' . $count;
            } else {$classes .= ' level-0';}
            $row_data = implode( ' ', $this->_get_post_class( $classes, $post->ID ) );
            $output  = "<div id='post_{$post->ID}' class='t-row' $row_data>{$this->_get_single_blocks( $post )}</div>";
            ob_start();
            $GLOBALS['post'] = $global_post;
            $output .= ob_get_clean();
            return $output;
        }//1359
        protected function _get_default_primary_name():string{
            return 'title';
        }//1397
        protected function _get_handle_block_actions( $item, $column_name, $primary ):string{
            if($primary !== $column_name){ return '';}
            $post             = $item;
            $post_type_object = $this->_get_post_type_object( $post->post_type );
            $can_edit_post    = $this->_current_user_can( 'edit_post', $post->ID );
            $actions          = [];
            $title            = $this->_draft_or_post_title();
            if ( $can_edit_post && 'trash' !== $post->post_status ) {
                $actions['edit'] = sprintf("<a href='%s' aria-label='%s'>%s</a>",
                    $this->_get_edit_post_link( $post->ID ), $this->_esc_attr( sprintf( $this->__( 'Edit &#8220;%s&#8221;' ), $title ) ),
                    $this->__('Edit'));
            }
            if('tp_block' !== $post->post_type ){
                $actions['inline hide-if-no-js'] = sprintf("<button type='button' class='button-link edit-inline' aria-label='%s' aria-expanded='false'>%s</button>",
                    $this->_esc_attr( sprintf( $this->__( 'Quick edit &#8220;%s&#8221; inline' ), $title ) ),$this->__('Quick&nbsp;Edit'));
            }
            if ( $this->_current_user_can( 'delete_post', $post->ID ) ){
                if ( 'trash' === $post->post_status ) {
                    $actions['untrash'] = sprintf("<a href='%s' aria-label='%s'>%s</a>",
                        $this->_tp_nonce_url( $this->_admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ),
                        $this->_esc_attr( sprintf( $this->__( 'Restore &#8220;%s&#8221; from the Trash' ), $title ) ),
                        $this->__( 'Restore' ));
                } elseif ( EMPTY_TRASH_DAYS ) {
                    $actions['trash'] = sprintf("<a href='%s' class='submit-delete' aria-label='%s'>%s</a>", $this->_get_delete_post_link( $post->ID ),
                        $this->_esc_attr( sprintf( $this->__( 'Move &#8220;%s&#8221; to the Trash' ), $title ) ),
                        $this->_x( 'Trash', 'verb' ));
                }
                if ( 'trash' === $post->post_status || ! EMPTY_TRASH_DAYS ) {
                    $actions['delete'] = sprintf("<a href='%s' class='submit-delete' aria-label='%s'>%s</a>",
                        $this->_get_delete_post_link( $post->ID, true ),
                        $this->_esc_attr( sprintf( $this->__( 'Delete &#8220;%s&#8221; permanently' ), $title ) ),
                        $this->__('Delete Permanently'));
                }
            }
            if ( $this->_is_post_type_viewable( $post_type_object ) ) {
                if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ), true ) ) {
                    if ( $can_edit_post ) {
                        $preview_link    = $this->_get_preview_post_link( $post );
                        $actions['view'] = sprintf("<a href='%s' rel='bookmark' aria-label='%s'>%s</a>", $this->_esc_url( $preview_link ),
                            $this->_esc_attr( sprintf( $this->__( 'Preview &#8220;%s&#8221;' ), $title ) ), $this->__( 'Preview' ));
                    }
                } elseif ( 'trash' !== $post->post_status ) {
                    $actions['view'] = sprintf("<a href='%s' rel='bookmark' aria-label='%s'>%s</a>",$this->_get_permalink( $post->ID ),
                        $this->_esc_attr( sprintf( $this->__( 'View &#8220;%s&#8221;' ), $title ) ),$this->__( 'View' ));
                }
            }
            if ( 'tp_block' === $post->post_type ) {
                $actions['export'] = sprintf("<button type='button' class='tp-list-reusable-blocks__export button-link' data-id='%s' aria-label='%s'>%s</button>",
                    $post->ID,$this->_esc_attr( sprintf( $this->__( 'Export &#8220;%s&#8221; as JSON' ), $title ) ), $this->__( 'Export as JSON' ));
            }
            if ( $this->_is_post_type_hierarchical( $post->post_type ) ) {
                $actions = $this->_apply_filters( 'page_row_actions', $actions, $post );
            }else{ $actions = $this->_apply_filters( 'post_row_actions', $actions, $post );}
            return $this->_get_actions( $actions );
        }//1413
        public function get_inline_edit():string{
            $screen = $this->_screen;
            $post             = $this->_get_default_post_to_edit( $screen->post_type );
            $post_type_object = $this->_get_post_type_object( $screen->post_type );
            $taxonomy_names          = $this->_get_object_taxonomies( $screen->post_type );
            $hierarchical_taxonomies = [];
            $flat_taxonomies         = [];
            foreach ( $taxonomy_names as $taxonomy_name ) {
                $_taxonomy = $this->_get_taxonomy( $taxonomy_name );
                $taxonomy = null;
                if( $_taxonomy instanceof TP_Taxonomy ){ $taxonomy = $_taxonomy;}
                $show_in_quick_edit = $taxonomy->show_in_quick_edit;
                if ( ! $this->_apply_filters( 'quick_edit_show_taxonomy', $show_in_quick_edit, $taxonomy_name, $screen->post_type ) ) {
                    continue;}
                if ( $taxonomy->hierarchical ) {$hierarchical_taxonomies[] = $taxonomy;
                } else {$flat_taxonomies[] = $taxonomy;}
            }
            $m            = ( isset( $this->tp_mode ) && 'excerpt' === $this->tp_mode ) ? 'excerpt' : 'list';
            $can_publish  = $this->_current_user_can( $post_type_object->cap->publish_posts );
            $core_columns = ['cb' => true,'date' => true,'title' => true,'categories' => true,'tags' => true,'comments' => true,'author' => true,];
            $h_class              = count( $hierarchical_taxonomies ) ? 'post' : 'page';
            $inline_edit_classes = "inline-edit-row inline-edit-row-$h_class";
            $bulk_edit_classes   = "bulk-edit-row bulk-edit-row-$h_class bulk-edit-{$screen->post_type}";
            $quick_edit_classes  = "quick-edit-row quick-edit-row-$h_class inline-edit-{$screen->post_type}";
            //$bulk = 0;
            $output  = "Setup of this is for later!";
            $output .= $post;
            $output .= $flat_taxonomies;
            $output .= $m;
            $output .= $can_publish;
            $output .= $core_columns;
            $output .= $inline_edit_classes;
            $output .= $bulk_edit_classes;
            $output .= $quick_edit_classes;
            return $output;
        }//1549
    }
}else{die;}