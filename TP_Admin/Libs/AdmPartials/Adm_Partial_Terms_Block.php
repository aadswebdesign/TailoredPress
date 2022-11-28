<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-11-2022
 * Time: 16:11
 */
namespace TP_Admin\Libs\AdmPartials;
if(ABSPATH){
    class Adm_Partial_Terms_Block extends Adm_Partials  {
        private $__level;
        public $callback_args;
        public function __construct( ...$args){
            parent::__construct(['plural' => 'tags','singular' => 'tag','screen' => $args['screen'] ?? null,]);
            $this->tp_action    = $this->_screen->action;
            $this->tp_post_type = $this->_screen->post_type;
            $this->tp_taxonomy  = $this->_screen->taxonomy;
            if(empty($this->tp_taxonomy)){$this->tp_taxonomy = 'post_tag';}
            if (!$this->_taxonomy_exists($this->tp_taxonomy)){
                $this->_tp_die($this->__('Invalid taxonomy.'));
            }
            $this->tp_tax = $this->_get_taxonomy( $this->tp_taxonomy );
        }//38
        public function async_user_can() {
            return $this->_current_user_can( $this->_get_taxonomy( $this->_screen->taxonomy )->cap->manage_terms );
        }
        public function prepare_items():void{
            $taxonomy = $this->_screen->taxonomy;
            $tags_per_page = $this->_get_items_per_page( "edit_{$taxonomy}_per_page" );
            if ('post_tag' === $taxonomy){
                $tags_per_page = $this->_apply_filters( 'edit_tags_per_page', $tags_per_page );
            }elseif ('category' === $taxonomy){
                $tags_per_page = $this->_apply_filters( 'edit_categories_per_page', $tags_per_page );
            }
            $search = ! empty( $_REQUEST['s'] ) ? trim( $this->_tp_unslash( $_REQUEST['s'] ) ) : '';
            $args = ['taxonomy' => $taxonomy,'search' => $search,'page' => $this->get_pagenum(),'number' => $tags_per_page,'hide_empty' => 0,];
            if ( ! empty( $_REQUEST['orderby'] ) ) {
                $args['orderby'] = trim( $this->_tp_unslash( $_REQUEST['orderby'] ) );
            }
            if ( ! empty( $_REQUEST['order'] ) ) {
                $args['order'] = trim( $this->_tp_unslash( $_REQUEST['order'] ) );
            }
            $args['offset'] = ( $args['page'] - 1 ) * $args['number'];
            $this->callback_args = $args;
            if (! isset( $args['orderby'] ) && $this->_is_taxonomy_hierarchical( $taxonomy )) {
                $args['number'] = 0;
                $args['offset'] = $args['number'];
            }
            $this->items = $this->_get_terms( $args );
            $this->_set_pagination_args(['total_items' => $this->_tp_count_terms(
                ['taxonomy' => $taxonomy,'search' => $search,]),'per_page' => $tags_per_page,]);
        }//79
        public function get_no_items():string{
            return $this->_get_taxonomy($this->_screen->taxonomy )->labels->not_found;
        }//160
        protected function _get_bulk_actions():array{
            $actions = [];
            if ( $this->_current_user_can( $this->_get_taxonomy($this->_screen->taxonomy)->cap->delete_terms)){
                $actions['delete'] = $this->__('Delete');}
            return $actions;
        }//167
        public function get_current_action():bool {
            if ( isset( $_REQUEST['action'], $_REQUEST['delete_tags'] ) && 'delete' === $_REQUEST['action'] ) {
                return 'bulk-delete';}
            return parent::get_current_action();
        }
        public function get_blocks() {
            $columns = ['cb' => "<dd><input type='checkbox' /></dd>",'dt_open' => '<dt>','name' => $this->_x( 'Name', 'term name' ),'description' => $this->__( 'Description' ),'slug' => $this->__( 'Slug' ),'dt_close' => '</dt>',];
            if ( 'link_category' === $this->_screen->taxonomy ){ $columns['links'] = $this->__( 'Links' );}
            else {$columns['posts'] = $this->_x( 'Count', 'Number/count of items' );}
            return $columns;
        }//191
        protected function _get_sortable_blocks():array{
            return ['name' => 'name','description' => 'description','slug' => 'slug','posts' => 'count','links' => 'count',];
        }//211
        public function get_display_placeholder():string{
            $taxonomy = $this->_screen->taxonomy;
            $number = $this->callback_args['number'];
            $offset = $this->callback_args['offset'];
            $count = 0;
            $output  = "";
            if(empty($this->items) || !is_array($this->items)){
                $output .= "<li class='wrapper display-placeholder'><dt>";
                $output .= "{$this->get_no_items()}</dt></li><!-- wrapper display-placeholder 1 -->";
            }
            if (! isset( $this->callback_args['orderby'] ) && $this->_is_taxonomy_hierarchical( $taxonomy )) {
                if ( ! empty( $this->callback_args['search'])){$children = [];}
                else {$children = $this->_get_term_hierarchy( $taxonomy );}
                $output .= "<li class='wrapper display-placeholder'>";
                $output .= $this->__rows( $taxonomy, $this->items, $children, $offset, $number, $count );
                $output .= "</li><!-- wrapper display-placeholder 2 -->";
            }else {
                foreach ((array) $this->items as $term ) { $output .= $this->get_single_block( $term );}
            }
            return $output;
        }//223
        private function __rows( $taxonomy, $terms, &$children, $start, $per_page, &$count, $parent = 0, $level = 0 ):string{
            $end = $start + $per_page;
            $output  = "";
            foreach ((array) $terms as $key => $term ) {
                if($count >= $end){break;}
                if($term->parent !== $parent && empty( $_REQUEST['s'])){continue;}
                if ( $count === $start && $term->parent > 0 && empty( $_REQUEST['s'] ) ) {
                    $my_parents = [];
                    $parent_ids = [];
                    $p          = $term->parent;
                    while ( $p ) {
                        $my_parent    = $this->_get_term( $p, $taxonomy );
                        $my_parents[] = $my_parent;
                        $p            = $my_parent->parent;
                        if(in_array( $p,$parent_ids,true)){ break;}
                        $parent_ids[] = $p;
                    }
                    unset( $parent_ids );
                    $num_parents = count( $my_parents );
                    while ( $my_parent = array_pop( $my_parents ) ) {
                        $output .= "\t{$this->get_single_block( $my_parent, $level - $num_parents )}";
                        $num_parents--;
                    }
                }
                if ($count >= $start){$output .= "\t{$this->get_single_block( $term, $level )}";}
                ++$count;
                unset( $terms[ $key ] );
                if ( isset( $children[ $term->term_id ] ) && empty( $_REQUEST['s'] ) ) {
                    $output .= $this->__rows( $taxonomy, $terms, $children, $start, $per_page, $count, $term->term_id, $level + 1 );
                }
            }
            return $output;
        }//268
        public function get_single_block( $tag, $level = 0 ):string{
            $tag = $this->_sanitize_term( $tag, $this->tp_taxonomy );
            $this->__level = $level;
            if ( $tag->parent ) {
                $count = count( $this->_get_ancestors( $tag->term_id, $this->tp_taxonomy, 'taxonomy' ) );
                $this->__level = 'level-' . $count;
            } else {$this->__level = 'level-0';}
            $output  = "<li id='tag_{$tag->term_id}' class='wrapper single-block'>";
            $output .= "{$this->_get_single_blocks( $tag )}</li><!-- wrapper single-block {$tag->term_id} -->";
            return $output;
        }//331
        public function _get_cb_block( $item ):string{
            $tag = $item;
            $output  = "";
            if ( $this->_current_user_can( 'delete_term', $tag->term_id ) ) {
                $output_set = "<dt><label for='cb_select_%1\$s' class='screen-reader-text'>%2\$s</label></dt>";
                $output_set .="<dd><input name='delete_tags[]' id='cb_select_%1\$s' type='checkbox' value='%1\$s' /></dd>";
                $output .= sprintf($output_set,$tag->term_id,sprintf( $this->__( 'Select %s' ), $tag->name ));
            }
            return $output;
        }//355
        public function get_block_name( $tag ):string{
            $taxonomy = $this->_screen->taxonomy;
            $pad = str_repeat( '&#8212; ', max( 0, $this->__level ) );
            $name = $this->_apply_filters( 'term_name', $pad . ' ' . $tag->name, $tag );
            $qe_data = $this->_get_term( $tag->term_id, $taxonomy, OBJECT, 'edit' );
            $uri = $this->_tp_doing_async() ? $this->_tp_get_referer() : $_SERVER['REQUEST_URI'];
            $edit_link = $this->_get_edit_term_link( $tag, $taxonomy, $this->_screen->post_type );
            if ( $edit_link ) {
                $edit_link = $this->_add_query_arg('tp_http_referer',urlencode( $this->_tp_unslash($uri)),$edit_link);
                $name = sprintf("<a class='row-title' href='%s' aria-label='%s'>%s</a>",$this->_esc_url( $edit_link ),
                    $this->_esc_attr( sprintf( $this->__( '&#8220;%s&#8221; (Edit)' ), $tag->name ) ),$name);
            }
            $output  = "<li class='wrapper block-name hidden'>";
            $output .= sprintf("<dd><strong>%s</strong></dd>",$name);
            $output .= "<dt id='inline_{$qe_data->term_id}'><p>";
            $output .= "<span class='name'>{$qe_data->name}</span>";
            $output .= "<span class='slug'>{$this->_apply_filters( 'editable_slug', $qe_data->slug, $qe_data )}</span>";
            $output .= "<span class='parent'>{$qe_data->parent}</span></p></dt></li><!-- wrapper block-name -->";
            return $output;
        }//376
        protected function _get_default_primary_name():string {
            return 'name';
        }//439
        protected function _get_handle_block_actions( $item, $column_name, $primary ):string{
            if ( $primary !== $column_name ){ return '';}
            $tag      = $item;
            $taxonomy = $this->_screen->taxonomy;
            $tax      = $this->_get_taxonomy( $taxonomy );
            $uri      = $this->_tp_doing_async() ? $this->_tp_get_referer() : $_SERVER['REQUEST_URI'];
            $edit_link = $this->_add_query_arg('tp_http_referer', urlencode($this->_tp_unslash($uri)),$this->_get_edit_term_link( $tag, $taxonomy, $this->_screen->post_type));
            $actions = [];
            if ( $this->_current_user_can( 'edit_term', $tag->term_id ) ) {
                $actions['edit'] = sprintf("<a href='%s' aria-label='%s'>%s</a>",$this->_esc_url( $edit_link ),
                    $this->_esc_attr( sprintf( $this->__( 'Edit &#8220;%s&#8221;' ), $tag->name ) ), $this->__( 'Edit' ));
                $actions['inline hide-if-no-js'] = sprintf("<button class='button-link edit-inline' type='button' aria-label='%s' aria-expanded='false'>%s</button>",
                    $this->_esc_attr( sprintf( $this->__( 'Quick edit &#8220;%s&#8221; inline' ), $tag->name ) ), $this->__( 'Quick&nbsp;Edit' ));
            }
            if ( $this->_current_user_can( 'delete_term', $tag->term_id ) ) {
                $delete_action = $this->_tp_nonce_url( "edit-tags.php?action=delete&amp;taxonomy=$taxonomy&amp;tag_ID=$tag->term_id", 'delete-tag_' . $tag->term_id );
                $actions['delete'] = sprintf("<a href='%s' class='delete-tag aria-button-if-js' aria-label='%s'>%s</a>",$delete_action,$this->_esc_attr( sprintf($this->__('Delete &#8220;%s&#8221;'),$tag->name)),$this->__('Delete'));
            }
            if ( $this->_is_taxonomy_viewable( $tax ) ) {
                $actions['view'] = sprintf("<a href='%s' aria-label='%s'>%s</a>",$this->_get_term_link( $tag ),$this->_esc_attr( sprintf( $this->__( 'View &#8220;%s&#8221; archive' ), $tag->name )),$this->__('View'));
            }
            $actions = $this->_apply_filters( 'tag_row_actions', $actions, $tag );
            return $this->_get_actions( $actions );
        }//455
        public function get_block_description( $tag ) {
            if ( $tag->description ) {return $tag->description;}
            return "<span aria-hidden='true'>&#8212;</span><span class='screen-reader-text'>{$this->__('No description')}</span>";
        }//548
        public function get_block_slug( $tag ){
            return $this->_apply_filters( 'editable_slug', $tag->slug,$tag);
        }//560
        public function get_block_posts( $tag ):string{
            $count = $this->_number_format_i18n( $tag->count );
            $tax = $this->_get_taxonomy( $this->_screen->taxonomy );
            $p_type_object = $this->_get_post_type_object( $this->_screen->post_type );
            if(!$p_type_object->show_ui){ return $count;}
            if ($tax->query_var){$args = [$tax->query_var => $tag->slug];}
            else {$args = ['taxonomy' => $tax->name,'term' => $tag->slug,];}
            if('post' !== $this->_screen->post_type){ $args['post_type'] = $this->_screen->post_type;}
            if ( 'attachment' === $this->_screen->post_type ) {
                return "<a href='{$this->_esc_url( $this->_add_query_arg( $args, 'upload.php' ) )}'>$count</a>";
            }
            return "<a href='{$this->_esc_url( $this->_add_query_arg( $args, 'edit.php' ) )}'>$count</a>";
        }//569
        public function get_block_links( $tag ):string{
            $count = $this->_number_format_i18n( $tag->count );
            if ( $count ) {$count = "<dd><a href='link_manager.php?cat_id={$tag->term_id}'>$count</a></dd>";}
            return $count;
        }//603
        public function get_block_default( $item, $column_name ):string{
            return $this->_apply_filters( "manage_{$this->_screen->taxonomy}_custom_column", '', $column_name, $item->term_id );
        }//620
        public function get_inline_edit():string{
            $core_blocks = ['cb' => true,'description' => true,'name' => true,'slug' => true,'posts' => true,];
            @list( $blocks ) = $this->_get_block_info();
            $tax = $this->_get_taxonomy( $this->_screen->taxonomy );
            if (!$this->_current_user_can( $tax->cap->edit_terms )){return false;}
            $output  = "<div class='adm-segment inline-edit'><form method='get'><fieldset>";
            $output .= "<legend class='inline-edit-legend'>{$this->__('Quick Edit')}</legend><ul class='inline-edit-col' style='display:block;'>";
            $output .= "<li><dt class='title'><label>{$this->__('Name', 'term name')}</label></dt>";
            $output .= "<dd class='input-text-wrap'><input class='p-title' name='name' type='text' value=''/></dd></li><!-- li 1-->";
            if ( ! $this->_global_terms_enabled() ){
                $output .= "<li><dt><label for='' class=''>{$this->__('Slug')}</label></dt>";
                $output .= "<dd><input class='p-title' name='slug' type='text' value=''/></dd></li><!-- li 2-->";
            }
            $output .= "</ul></fieldset><ul>";
            foreach((array)$blocks as $block_name => $block_display_name){
                if (isset($core_blocks[ $block_name ])){ continue;}
                $output .= "<li>";
                $output .= $this->_get_action( 'quick_edit_custom_box', $block_name, 'edit-tags', $this->tp_screen->taxonomy );
                $output .= "</li><!-- li 3-->";
            }
            $output .= "<li class='inline-edit-save submit'>";
            $output .= "<button type='button' id='' class='button cancel'>{$this->__('Cancel')}</button>";
            $output .= "<button type='button' id='' class='button save button-primary'>{$tax->labels->update_item}</button>";
            $output .= "</li><!-- li 4--><li>";
            $output .= $this->_tp_get_nonce_field( 'tax_inline_edit_nonce', '_inline_edit', false );
            $output .= "<input name='taxonomy' type='hidden' value='{$this->_esc_attr( $this->tp_screen->taxonomy)}'/>";
            $output .= "<input name='post_type' type='hidden' value='{$this->_esc_attr($this->tp_screen->post_type)}'/>";
            $output .= "</li><!-- li 5--><li class='notice notice-error notice-alt inline hidden'>";
            $output .= "<p class='error'></p>";
            $output .= "</li><!-- li 6--></ul></form></div><!-- adm-segment inline-edit -->";
            return $output;
        }//646
    }
}else{die;}