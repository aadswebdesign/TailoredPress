<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-11-2022
 * Time: 16:11
 */
namespace TP_Admin\Libs\AdmPartials;
if(ABSPATH){
    class Adm_Partial_Privacy_Matters extends Adm_Partials{
        public const TP_INVALID = 'INVALID';
        protected $_request_type = self::TP_INVALID;
        protected $_post_type = self::TP_INVALID;
        public function get_blocks(){
            $blocks = ['cb' => "<dd><input type='checkbox' /></dd>",'dt_open' => '<dt>','email' => $this->__( 'Requester' ),
                'status' => $this->__( 'Status' ),'created_timestamp' => $this->__( 'Requested' ),'next_steps' => $this->__( 'Next steps' ),'dt_close' => '</dt>',];
            return $blocks;
        }//40
        protected function _get_admin_partial_url(){
            $pagenow = $this->_request_type;
            if ( 'remove-personal-data' === $pagenow ){ $pagenow = 'erase-personal-data';}
            return $this->_admin_url( $pagenow . '.php' );
        }//58
        protected function _get_sortable_blocks():array{
            $desc_first = isset( $_GET['orderby'] );
            return ['email' => 'requester','created_timestamp' => ['requested', $desc_first],];
        }//75
        protected function _get_default_primary_name():string {
            return 'email';
        }//96
        protected function _get_request_counts():int{
            $tpdb = $this->_init_db();
            $cache_key = $this->_post_type . '_' . $this->_request_type;
            $counts    = $this->_tp_cache_get( $cache_key, 'counts' );
            if(false !== $counts){ return $counts;}
            $query = TP_SELECT . " post_status, COUNT( * ) AS num_posts FROM {$tpdb->posts} WHERE post_type = %s AND post_name = %s GROUP BY post_status";
            $results = (array) $tpdb->get_results( $tpdb->prepare( $query, $this->_post_type, $this->_request_type ), ARRAY_A );
            $counts  = array_fill_keys( $this->_get_post_stati(), 0 );
            foreach ( $results as $row ) { $counts[ $row['post_status'] ] = $row['num_posts'];}
            $counts = (object) $counts;
            $this->_tp_cache_set( $cache_key, $counts, 'counts' );
            return (string)$counts;
        }//109
        protected function _get_views():array{
            $current_status = isset( $_REQUEST['filter-status'] ) ? $this->_sanitize_text_field( $_REQUEST['filter-status'] ) : '';
            $statuses = $this->_tp_privacy_statuses();
            $views = [];
            $counts = $this->_get_request_counts();
            $total_requests = $this->_abs_int( array_sum( (array) $counts ) );
            $admin_url = $this->_get_admin_url();
            $current_link_attributes = empty( $current_status ) ? " class='current' aria-current='page'" : '';
            $status_label = sprintf($this->_nx("<dt><span class='count'>(%s)</span></dt>","<dt><span class='count'>(%s)</span></dt>",$total_requests,
                'requests'),$this->_number_format_i18n( $total_requests ));
            $views['all'] = sprintf("<dd><a href='%s' %s>%s</a></dd>",$this->_esc_url($admin_url),$current_link_attributes, $status_label);
            foreach ( $statuses as $status => $label ) {
                $post_status = $this->_get_post_status_object( $status );
                if(! $post_status){continue;}
                $current_link_attributes = $status === $current_status ? " class='current' aria-current='page'" : '';
                $total_status_requests   = $this->_abs_int( $counts->{$status} );
                if(! $total_status_requests){continue;}
                $status_label = sprintf(
                    $this->_translate_nooped_plural( $post_status->label_count, $total_status_requests ),
                    $this->_number_format_i18n( $total_status_requests ));
                $status_link = $this->_add_query_arg( 'filter-status', $status, $admin_url );
                $views[ $status ] = sprintf("<dd><a href='%s' %s>%s</a></dd>",$this->_esc_url($status_link),$current_link_attributes, $status_label);
            }
            return $views;
        }//146
        protected function _get_bulk_actions():array{
            return ['resend' => $this->__( 'Resend confirmation requests' ),'complete' => $this->__( 'Mark requests as completed' ),'delete' => $this->__( 'Delete requests' ),];
        }//213
        public function get_process_bulk_action():string{
            $action      = $this->get_current_action();
            $request_ids = isset( $_REQUEST['request_id'] ) ? $this->_tp_parse_id_list( $this->_tp_unslash( $_REQUEST['request_id'] ) ) : array();
            if ( empty( $request_ids ) ) {return false;}
            $count    = 0;
            $failures = 0;
            $output  = $this->_check_admin_referer( 'bulk-privacy_requests' );
            ob_start();
            switch ( $action ) {
                case 'resend':
                    foreach ( $request_ids as $request_id ) {
                        $resend = $this->_tp_privacy_resend_request( $request_id );
                        if($resend && ! $this->_init_error( $resend)){$count++;}
                        else {$failures++;}
                    }
                    if ( $failures ){
                        $this->_add_settings_error('bulk_action','bulk_action',sprintf($this->_n('%d confirmation requests failed to resend.',
                            '%d confirmation requests failed to resend.',$failures),$failures),'error');
                    }
                    if ( $count ){
                        $this->_add_settings_error('bulk_action','bulk_action',sprintf($this->_n('%d confirmation request re-sent successfully.',
                            '%d confirmation request re-sent successfully.',$count),$count),'success');
                    }
                    break;
                case 'complete':
                    foreach ( $request_ids as $request_id ) {
                        $result = $this->_tp_privacy_completed_request( $request_id );
                        if ( $result && ! $this->_init_error( $result ) ) { $count++;}
                    }
                    $this->_add_settings_error('bulk_action','bulk_action',sprintf($this->_n('%d request marked as complete.',
                        '%d request marked as complete.',$count),$count),'success');
                    break;
                case 'delete':
                    foreach ( $request_ids as $request_id ) {
                        if($this->_tp_delete_post($request_id,true)){$count++;}
                        else{$failures++;}
                    }
                    if ( $count ){
                        $this->_add_settings_error('bulk_action','bulk_action',sprintf($this->_n('%d  requests deleted successfully.',
                            '%d requests deleted successfully.',$count),$count),'success');
                    }
                    break;
            }
            $output .= ob_get_clean();
            return $output;
        }//227
        public function prepare_items(){
            $this->items = [];
            $posts_per_page = $this->_get_items_per_page( $this->_request_type . '_requests_per_page' );
            $args = ['post_type' => $this->_post_type,'post_name__in' => array( $this->_request_type ),'posts_per_page' => $posts_per_page,
                'offset' => isset( $_REQUEST['paged'] ) ? max( 0, $this->_abs_int( $_REQUEST['paged'] ) - 1 ) * $posts_per_page : 0,
                'post_status' => 'any','s' => isset( $_REQUEST['s'] ) ? $this->_sanitize_text_field( $_REQUEST['s'] ) : '',];
            $orderby_mapping = ['requester' => 'post_title','requested' => 'post_date',];
            if ( isset( $_REQUEST['orderby'], $orderby_mapping[ $_REQUEST['orderby'] ] ) ) {
                $args['orderby'] = $orderby_mapping[ $_REQUEST['orderby'] ];}
            if ( isset( $_REQUEST['order'] ) && in_array( strtoupper( $_REQUEST['order'] ), array( 'ASC', 'DESC' ), true ) ) {
                $args['order'] = strtoupper( $_REQUEST['order'] );}
            if ( ! empty( $_REQUEST['filter-status'] ) ) {
                $filter_status       = isset( $_REQUEST['filter-status'] ) ? $this->_sanitize_text_field( $_REQUEST['filter-status'] ) : '';
                $args['post_status'] = $filter_status;}
            $requests_query = $this->_init_query($args);
            $requests       = $requests_query->posts;
            foreach ((array) $requests as $request ) {$this->items[] = $this->_tp_get_user_request( $request->ID );}
            $this->items = array_filter( $this->items );
            $this->items = array_filter( $this->items );
            $this->_set_pagination_args(['total_items' => $requests_query->found_posts,'per_page' => $posts_per_page,]);
        }//366
        public function _get_cb_block( $item ):string{
            return sprintf("<dd><input name='request_id[]' type='checkbox' value='%1\$s'/><span class='spinner'></span></dd>",$this->_esc_attr( $item->ID));
        }//421
        /**
         * @param $item
         * @return mixed
         */
        public function get_block_status( $item ){
            $status        = $this->_get_post_status( $item->ID );
            $status_object = $this->_get_post_status_object( $status );
            if (!$status_object || empty($status_object->label)){return '-';}
            $timestamp = false;
            switch ( $status ) {
                case 'request-confirmed':
                    $timestamp = $item->confirmed_timestamp;
                    break;
                case 'request-completed':
                    $timestamp = $item->completed_timestamp;
                    break;
            }
            $get_timestamp = null;
            if ( $timestamp ){ $get_timestamp = ": ({$this->_get_timestamp_as_date( $timestamp )})";}
            return "<span class='status-label status-{$this->_esc_attr($status)}'>{$this->_esc_html('$status_object->label')}{$get_timestamp}</span>";
        }//433
        protected function _get_timestamp_as_date( $timestamp ):string{
            if(empty($timestamp)){return '';}
            $time_diff = time() - $timestamp;
            if ( $time_diff >= 0 && $time_diff < DAY_IN_SECONDS ) {
                return sprintf( $this->__( '%s ago' ), $this->_human_time_diff( $timestamp ) );
            }
            return $this->_date_i18n( $this->_get_option( 'date_format' ), $timestamp );
        }//470
        public function get_block_default( $item, $column_name ):string{
            return $this->_get_action( "manage_{$this->_screen->id}_custom_column", $column_name, $item );
        }//494
        public function get_block_created_timestamp( $item ):string{
            return $this->_get_timestamp_as_date( $item->created_timestamp );
        }//517
        public function get_block_email( $item ):string{
            $mail_to = $this->_esc_url("mailto:{$item->email}");
            return sprintf("<dd><a href='%1\$s'>%2\$s</a>%3\$s</dd>",$mail_to, $item->email, $this->_get_actions([]));
        }//529
        public function set_column_next_steps( $item ):void{}//540
        public function get_single_block( $item ):string{
            $status = $item->status;
            return "<li id='request_{$this->_esc_attr($item->ID)}' class='wrapper status-{$this->_esc_attr($status)}'>{$this->_get_single_blocks( $item )}</li><!-- wrapper status-? -->";
        }//549
        public function set_embedded_scripts():void{}//562
    }
}else{die;}