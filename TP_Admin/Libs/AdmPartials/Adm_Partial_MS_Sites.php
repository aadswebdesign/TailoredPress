<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-11-2022
 * Time: 21:53
 */
namespace TP_Admin\Libs\AdmPartials;
use TP_Core\Libs\Queries\TP_User_Query;
use TP_Core\Libs\TP_Site;
if(ABSPATH){
    class Adm_Partial_MS_Sites extends Adm_Partials{
        public $status_list;
        public function __construct( ...$args) {
            $this->status_list = ['archived' => ['site-archived', $this->__( 'Archived' )],'spam' => ['site-spammed', $this->_x( 'Spam', 'site' )],
                'deleted' => ['site-deleted', $this->__( 'Deleted' )],'mature' => ['site-mature', $this->__( 'Mature' )],];
            parent::__construct(['plural' => 'sites','screen' => $args['screen'] ?? null,]);
        }//37
        public function async_user_can() {
            return $this->_current_user_can( 'manage_sites' );
        }//56
        public function prepare_items(){
            $tpdb = $this->_init_db();
            if ( ! empty( $_REQUEST['mode'] ) ) {
                $this->tp_mode = 'excerpt' === $_REQUEST['mode'] ? 'excerpt' : 'block';
                $this->_set_user_setting( 'sites_block_mode', $this->tp_mode );
            } else {$this->tp_mode = $this->_get_user_setting( 'sites_list_mode', 'block' );}
            $per_page = $this->_get_items_per_page( 'sites_network_per_page' );
            $pagenum = $this->get_pagenum();
            $this->tp_search    = isset( $_REQUEST['s'] ) ? $this->_tp_unslash( trim( $_REQUEST['s'] ) ) : '';
            $wild = '';
            if ( false !== strpos( $this->tp_search, '*' ) ) {
                $wild = '*';
                $this->tp_search    = trim( $this->tp_search, '*' );
            }
            if ( ! $this->tp_search && $this->_tp_is_large_network() ) {
                if ( ! isset( $_REQUEST['orderby'] ) ) {
                    $_GET['orderby']     = '';
                    $_REQUEST['orderby'] = '';
                }
                if ( ! isset( $_REQUEST['order'] ) ) {
                    $_GET['order']     = 'DESC';
                    $_REQUEST['order'] = 'DESC';
                }
            }
            $args = ['number' => (int) $per_page,'offset' => (int) ( ( $pagenum - 1 ) * $per_page ),'network_id' => $this->_get_current_network_id(),];
            $_preg_match_1 = preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $this->tp_search );
            $_preg_match_2 = preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.?$/', $this->tp_search );
            $_preg_match_3 = preg_match( '/^\d{1,3}\.\d{1,3}\.?$/', $this->tp_search );
            $_preg_match_4 = preg_match( '/^\d{1,3}\.$/', $this->tp_search );
            if ( empty( $this->tp_search ) ) {
                // Nothing to do.
            } elseif ($_preg_match_1|| $_preg_match_2 || $_preg_match_3 || $_preg_match_4){
                $sql = $tpdb->prepare( TP_SELECT . " blog_id FROM {$tpdb->registration_log} WHERE {$tpdb->registration_log}.IP LIKE %s", $tpdb->esc_like( $this->tp_search ) . ( ! empty( $wild ) ? '%' : '' ) );
                $reg_blog_ids = $tpdb->get_col( $sql );
                if ( $reg_blog_ids ) { $args['site__in'] = $reg_blog_ids;}
            } elseif ( is_numeric( $this->tp_search ) && empty( $wild ) ) {
                $args['ID'] = $this->tp_search;
            }else {
                $args['search'] = $this->tp_search;
                if ( ! $this->_is_subdomain_install() ) { $args['search_blocks'] = ['path'];}
            }
            $order_by = $_REQUEST['orderby'] ?? '';
            if ( 'registered' === $order_by ) {
                // 'registered' is a valid field name.
            } elseif ( 'last_updated' === $order_by ){ $order_by = 'last_updated';}
            elseif ( 'blogname' === $order_by ) {
                if ( $this->_is_subdomain_install() ) { $order_by = 'domain'; }
                else {$order_by = 'path';}
            } elseif ( 'blog_id' === $order_by ) { $order_by = 'id';}
            elseif (!$order_by){$order_by = false;}
            $args['orderby'] = $order_by;
            if ( $order_by ) {
                $args['order'] = ( isset( $_REQUEST['order'] ) && 'DESC' === strtoupper( $_REQUEST['order'] ) ) ? 'DESC' : 'ASC';
            }
            if ( $this->_tp_is_large_network()){ $args['no_found_rows'] = true;}
            else { $args['no_found_rows'] = false;}
            $status = isset( $_REQUEST['status'] ) ? $this->_tp_unslash( trim( $_REQUEST['status'] ) ) : '';
            if ( in_array( $status,['public', 'archived', 'mature', 'spam', 'deleted'], true ) ) {
                $args[ $status ] = 1;
            }
            $args = $this->_apply_filters( 'ms_sites_query_args', $args );
            $_sites = $this->_get_sites( $args );
            if ( is_array( $_sites ) ) {
                $this->_update_site_cache( $_sites );
                $this->items = array_slice( $_sites, 0, $per_page );
            }
            $total_sites = $this->_get_sites( array_merge($args,['count' => true,'offset' => 0,'number' => 0,]));
            $this->_set_pagination_args(['total_items' => $total_sites, 'per_page' => $per_page,]);
        }//69
        public function get_no_items() {
            return "<dt>{$this->__( 'No sites found.' )}</dt>";
        }//206
        protected function _get_views():array{
            $counts = $this->_tp_count_sites();
            $statuses = [
                'all'=> $this->_nx_noop("All <span class='count'>(%s)</span>","All <span class='count'>(%s)</span>",'sites'),
                'public'=> $this->_n_noop("Public <span class='count'>(%s)</span>","Public <span class='count'>(%s)</span>"),
                'archived' => $this->_n_noop("Archived <span class='count'>(%s)</span>","Archived <span class='count'>(%s)</span>"),
                'mature' => $this->_n_noop("Mature <span class='count'>(%s)</span>","Mature <span class='count'>(%s)</span>"),
                'spam' => $this->_nx_noop("Spam <span class='count'>(%s)</span>","Spam <span class='count'>(%s)</span>",'sites'),
                'deleted'  => $this->_n_noop("Deleted <span class='count'>(%s)</span>", "Deleted <span class='count'>(%s)</span>"),
            ];
            $view_links = [];
            $requested_status = isset( $_REQUEST['status'] ) ? $this->_tp_unslash( trim( $_REQUEST['status'] ) ) : '';
            $url              = 'sites.php';
            foreach ( $statuses as $status => $label_count ) {
                $current_link_attributes = $requested_status === $status||('' === $requested_status && 'all' === $status) ? ' class="current" aria-current="page"' : '';
                if ( (int) $counts[ $status ] > 0 ) {
                    $label    = sprintf( $this->_translate_nooped_plural( $label_count, $counts[ $status ] ), $this->_number_format_i18n( $counts[ $status ] ) );
                    $full_url = 'all' === $status ? $url : $this->_add_query_arg( 'status', $status, $url );
                    $view_links[ $status ] = sprintf("<a href='%1\$s' %2\$s>%3\$s</a>",$this->_esc_url( $full_url ),$current_link_attributes,$label);
                }
            }
            return $view_links;
        }//217
        protected function _get_bulk_actions():array{
            $actions = [];
            if ( $this->_current_user_can( 'delete_sites' ) ) {
                $actions['delete'] = "<dd>{$this->__( 'Delete' )}</dd>";
            }
            $actions['spam']    = "<dd>{$this->_x( 'Mark as spam', 'site' )}</dd>"; //;
            $actions['not_spam'] = "<dd>{$this->_x( 'Not spam', 'site' )}</dd>"; // ;
            return $actions;
        }//287
        protected function _get_pagination( $which ):string{
            parent::_get_pagination( $which );
            $output  = "";
            if ( 'top' === $which ) {
                $output .= $this->_get_view_switcher( $this->tp_mode );
            }
            return $output;
        }//303
        protected function _get_extra_nav_block( $which ){
            $output  = "<ul  class='block extra-nav-block actions'>";
            if('top' === $which){
                $output .= "<li class='row  extra-nav-block'>";
                $output .= $this->_get_action( 'restrict_manage_sites', $which );
                $output .= "</li><!-- row  extra-nav-block 1-->";
                if(!empty($output)){
                    $output .= "<li class='row  extra-nav-block'>";
                    $output .= $this->_get_submit_button( $this->__( 'Filter' ),'','filter_action', false,['id' => 'site_query_submit']);
                    $output .= "</li><!-- row  extra-nav-block 2-->";
                }
            }
            $output .= "</ul><!-- block extra-nav-block actions -->";
            $output .= $this->_get_action( 'manage_sites_extra_nav', $which );
            return $output;
        }//320
        public function get_blocks(){
            $sites_blocks = ['cb' => "<dd><input type='checkbox' /></dd>",'dt_open' => '<dt>','blogname' => $this->__( 'URL' ),
                'last_updated' => $this->__( 'Last Updated' ),'registered' => $this->_x( 'Registered', 'site' ),
                'users' => $this->__( 'Users' ),'dt_close' => '</dt>',];
            return $this->_apply_filters( 'tp_mu_blogs_blocks', $sites_blocks );
        }//360
        public function get_cb_block( $item ):string{
            $blog = $item;
            $output  = "";
            if (! $this->_is_main_site( $blog['blog_id'] ) ){
                $blogname = $this->_untrailingslashit( $blog['domain'] . $blog['path'] );
                $output .= "<li class='wrapper cb-block'><dt><label for='blog_{$blog['blog_id']}' class='screen-reader-text'>{$this->__('')}";
                $output .= sprintf( $this->__( 'Select %s' ), $blogname );
                $output .= "</label></dt><dd><input name='all_blogs[]' id='blog_{$blog['blog_id']}' type='checkbox' value='{$this->_esc_attr($blog['blog_id'])}' /></dd></li><!-- wrapper cb-block -->";
            }
            return $output;
        }//403
        /**
         * @param $blog
         * @return mixed
         */
        public function get_block_id( $blog ){
            return $blog['blog_id'];
        }//428
        public function get_block_blogname( $blog ):string{
            $blogname = $this->_untrailingslashit( $blog['domain'] . $blog['path'] );
            $output  = "<li class='wrapper block-blogname'><dd><strong><a href='{$this->_esc_url( $this->_network_admin_url( 'site_info.php?id='.$blog['blog_id']))}' class='edit'>$blogname</a>";
            $output .= $this->_get_site_states( $blog );
            $output .= "</strong></dd>";
            if ( 'list' !== $this->tp_mode ) {
                ob_start();
                $this->_switch_to_blog( $blog['blog_id'] );
                $output .= ob_get_clean();
                $output .= "<dt><p>";
                $output .= sprintf($this->__("%1\$s &#8211; %2\$s"),$this->_get_option( 'blogname' ),
                    "<em>{$this->_get_option( 'blogdescription' )}</em>");
                $output .= "</p></dt></li><!-- wrapper block-blogname -->";
                ob_start();
                $this->_restore_current_blog();
                $output .= ob_get_clean();
            }
            return $output;
        }//441
        public function get_block_last_updated( $blog ):string {
            if ( 'list' === $this->tp_mode ) { $date = "<dt>{$this->__( 'Y/m/d' )}</dt>"; }
            else { $date .= "<dt>{$this->__( 'Y/m/d g:i:s a' )}</dt>"; }
            return ( '0000-00-00 00:00:00' === $blog['last_updated'] ) ? $this->__( 'Never' ) : $this->_mysql2date( $date, $blog['last_updated'] );

        }//475
        public function get_block_registered( $blog ):string{
            if ( 'list' === $this->tp_mode ) { $date = "<dt>{$this->__( 'Y/m/d' )}</dt>";}
            else { $date = "<dt>{$this->__( 'Y/m/d g:i:s a' )}</dt>";}
            $output  = "";
            if('0000-00-00 00:00:00' === $blog['registered']){ $output .= '<dt>&#x2014;</dt>';}
            else {$output .= "<li class='wrapper block-registered'>{$this->_mysql2date( $date, $blog['registered'])}</li><!-- wrapper block-registered -->";}
            return $output;
        }//496
        public function get_users_block( $blog ):string{
            $user_count = $this->_tp_cache_get( $blog['blog_id'] . '_user_count', 'blog-details' );
            if ( ! $user_count ) {
                $blog_users = new TP_User_Query(['blog_id' => $blog['blog_id'],'fields' => 'ID','number' => 1,'count_total' => true,]);
                $user_count = $blog_users->get_total();
                $this->_tp_cache_set( $blog['blog_id'] . '_user_count', $user_count, 'blog-details', 12 * HOUR_IN_SECONDS );
            }
            return sprintf("<li class='wrapper users-block'><dd><a href='%s' >%s</a></dd></li><!-- wrapper users-block -->",$this->_esc_url( $this->_network_admin_url( 'site_users.php?id=' . $blog['blog_id'] ) ),$this->_number_format_i18n( $user_count ));
        }//519
        public function get_block_default( $item, $block_name ):string{
            return $this->_get_action( 'manage_sites_custom_blocks', $block_name, $item['blog_id'] );
        }//572
        public function get_display_rows():string{
            $output  = "";
            //$this->items = ['item_1' => 'Item1','item_2' => 'Item2'];

            foreach ((array)$this->items as $blog ) {
                $blog  = $blog->to_array();
                $class = '';
                reset( $this->status_list );
                foreach ( $this->status_list as $status => $block ) {
                    if ( 1 === $blog[ $status ] ) { $class = " class='{$block[0]}'";}
                }
                $output  .= "<li class='wrapper single-blocks {$class}'>{$this->_get_single_blocks( $blog )}</li><!-- wrapper single-blocks -->";
            }
            return $output;
        }//587
        protected function _get_site_states( $site ):string{
            $site_states = [];
            $__site = TP_Site::get_instance($site['blog_id']);
            $_site = null;
            if($__site instanceof TP_Site ){
                $_site = $__site;
            }
            if ($this->_is_main_site( $_site->site_id ) ) {
                $site_states['main'] = $this->__( 'Main' );
            }
            reset( $this->status_list );
            $site_status = isset( $_REQUEST['status'] ) ? $this->_tp_unslash( trim( $_REQUEST['status'] ) ) : '';
            foreach ( $this->status_list as $status => $col ) {
                if ( ( 1 === (int) $_site->{$status} ) && ( $site_status !== $status ) ) {
                    $site_states[ $col[0] ] = $col[1];
                }
            }
            $site_states = $this->_apply_filters( 'display_site_states', $site_states, $_site );
            $output  = "";
            if ( ! empty( $site_states )){
                $state_count = count( $site_states );
                $i = 0;
                $output .= " &mdash; ";
                foreach ( $site_states as $state ){
                    ++$i;
                    $sep = ( $i < $state_count ) ? ', ' : '';
                    $output .= "<span class='post-state'>{$state}{$sep}</span>";
                }
            }
            return $output;
        }//614
        protected function _get_default_primary_column_name():string {
            return 'blogname';
        }//668
        protected function _get_handle_row_actions( $item, $column_name, $primary ):string{}//684
    }
}else{die;}