<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-5-2022
 * Time: 10:08
 */
namespace TP_Admin\Traits\AdminPost;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _adm_post_02{
        use _init_db;
        /**
         * @description Returns a list of previously defined keys.
         * @return array
         */
        protected function _get_meta_keys():array{
            $this->tpdb = $this->_init_db();
            return $this->tpdb->get_col(TP_SELECT . " meta_key FROM $this->tpdb->postmeta GROUP BY meta_key ORDER BY meta_key");
        }//989
        /**
         * @description Returns post meta data by meta ID.
         * @param $mid
         * @return mixed
         */
        protected function _get_post_meta_by_id( $mid ){
            return $this->_get_metadata_by_mid( 'post', $mid );
        }//1011
        /**
         * @description Returns meta data for the given post ID.
         * @param $postid
         * @return array|null
         */
        protected function _has_meta( $postid ):array{
            $this->tpdb = $this->_init_db();
            return $this->tpdb->get_results( $this->tpdb->prepare(TP_SELECT . " meta_key, meta_value, meta_id, post_id FROM $this->tpdb->postmeta WHERE post_id = %d ORDER BY meta_key,meta_id", $postid ), ARRAY_A);
        }//1036
        /**
         * @description Updates post meta data by meta ID.
         * @param $meta_id
         * @param $meta_key
         * @param $meta_value
         * @return mixed
         */
        protected function _update_meta( $meta_id, $meta_key, $meta_value ){
            $meta_key   = $this->_tp_unslash( $meta_key );
            $meta_value = $this->_tp_unslash( $meta_value );
            return $this->_update_metadata_by_mid( 'post', $meta_id, $meta_value, $meta_key );
        }//1060
        /**
         * @description Replace href-s of attachment anchors with up-to-date permalinks.
         * @param $post
         */
        protected function _fix_attachment_links( $post ){
            $post    = $this->_get_post( $post, ARRAY_A );
            $content = $post['post_content'];
            if ( ! $this->_get_option( 'permalink_structure' ) || ! in_array( $post['post_status'], array( 'publish', 'future', 'private' ), true ) ) {
                return;}
            if ( ! strpos( $content, '?attachment_id=' ) || ! preg_match_all( '/<a ([^>]+)>[\s\S]+?<\/a>/', $content, $link_matches ) ) {
                return;}
            $site_url = $this->_get_bloginfo( 'url' );
            $site_url = substr( $site_url, (int) strpos( $site_url, '://' ) ); // Remove the http(s).
            $replace  = '';
            foreach ( $link_matches[1] as $key => $value ) {
                if ( ! strpos( $value, '?attachment_id=' ) || ! strpos( $value, 'tp-att-' )
                    || ! preg_match( '/href=(["\'])[^"\']*\?attachment_id=(\d+)[^"\']*\\1/', $value, $url_match )
                    || ! preg_match( '/rel=["\'][^"\']*tp-att-(\d+)/', $value, $rel_match ) ) {
                    continue;}
                $quote  = $url_match[1]; // The quote (single or double).
                $url_id = (int) $url_match[2];
                $rel_id = (int) $rel_match[1];
                if ( ! $url_id || ! $rel_id || $url_id !== $rel_id || strpos( $url_match[0], $site_url ) === false ) {
                    continue;}
                $link    = $link_matches[0][ $key ];
                $replace = str_replace( $url_match[0], 'href=' . $quote . $this->_get_attachment_link( $url_id ) . $quote, $link );
                $content = str_replace( $link, $replace, $content );
            }
            if ( $replace ) {
                $post['post_content'] = $content;
                $post = $this->_add_magic_quotes( $post );
                return $this->_tp_update_post( $post );
            }
        }//1080
        /**
         * @description Returns all the possible statuses for a post type.
         * @param string $type
         * @return array
         */
        protected function _get_available_post_statuses( $type = 'post' ):array{
            $stati = $this->_tp_count_posts( $type );
            return array_keys( get_object_vars( $stati ) );
        }//1136
        /**
         * @description Runs the query to fetch the posts for listing on the edit posts page.
         * @param bool $q
         * @return array
         */
        protected function _tp_edit_posts_query( $q = false ):array{
            if ( false === $q ) { $q = $_GET;}
            $q['m']     = isset( $q['m'] ) ? (int) $q['m'] : 0;
            $q['cat']   = isset( $q['cat'] ) ? (int) $q['cat'] : 0;
            $post_stati = $this->_get_post_stati();
            if ( isset( $q['post_type'] ) && in_array( $q['post_type'], $this->_get_post_types(), true )){ $post_type = $q['post_type'];}
            else { $post_type = 'post';}
            $avail_post_stati = $this->_get_available_post_statuses( $post_type );
            $post_status      = '';
            $perm             = '';
            if ( isset( $q['post_status'] ) && in_array( $q['post_status'], $post_stati, true ) ) {
                $post_status = $q['post_status'];
                $perm        = 'readable';
            }
            $orderby = '';
            if ( isset( $q['orderby'] ) ) { $orderby = $q['orderby'];}
            elseif ( isset( $q['post_status'] ) && in_array( $q['post_status'], array( 'pending', 'draft' ), true ) ) {
                $orderby = 'modified';}
            $order = '';
            if ( isset( $q['order'])){ $order = $q['order'];}
            elseif ( isset( $q['post_status'] ) && 'pending' === $q['post_status']){ $order = 'ASC';}
            $per_page       = "edit_{$post_type}_per_page";
            $posts_per_page = (int) $this->_get_user_option( $per_page );
            if ( empty( $posts_per_page ) || $posts_per_page < 1 ) { $posts_per_page = 20;}
            $posts_per_page = $this->_apply_filters( "edit_{$post_type}_per_page", $posts_per_page );
            $posts_per_page = $this->_apply_filters( 'edit_posts_per_page', $posts_per_page, $post_type );
            $query = compact( 'post_type', 'post_status', 'perm', 'order', 'orderby', 'posts_per_page' );
            if (empty( $orderby ) && $this->_is_post_type_hierarchical( $post_type )) {
                $query['orderby']                = 'menu_order title';
                $query['order']                  = 'asc';
                $query['posts_per_page']         = -1;
                $query['posts_per_archive_page'] = -1;
                $query['fields']                 = 'id=>parent';
            }
            if ( ! empty( $q['show_sticky'] ) ) { $query['post__in'] = (array) $this->_get_option( 'sticky_posts' );}
            $this->_tp_method( $query );
            return $avail_post_stati;
        }//1151
        /**
         * @description Returns the query variables for the current attachments request.
         * @param bool $q
         * @return bool
         */
        protected function _tp_edit_attachments_query_vars( $q = false ):bool{
            if ( false === $q ) { $q = $_GET;}
            $q['m']         = isset( $q['m'] ) ? (int) $q['m'] : 0;
            $q['cat']       = isset( $q['cat'] ) ? (int) $q['cat'] : 0;
            $q['post_type'] = 'attachment';
            $post_type      = $this->_get_post_type_object( 'attachment' );
            $states         = 'inherit';
            if ( $this->_current_user_can( $post_type->cap->read_private_posts ) ) {
                $states .= ',private';}
            $q['post_status']  = isset( $q['status'] ) && 'trash' === $q['status'] ? 'trash' : $states;
            $q['post_status'] .= isset( $q['attachment-filter'] ) && 'trash' === $q['attachment-filter'] ? 'trash' : $states;
            $media_per_page = (int) $this->_get_user_option( 'upload_per_page' );
            if ( empty( $media_per_page ) || $media_per_page < 1 ) { $media_per_page = 20;}
            $q['posts_per_page'] = $this->_apply_filters( 'upload_per_page', $media_per_page );
            $post_mime_types = $this->_get_post_mime_types();
            if ( isset( $q['post_mime_type'] ) && ! array_intersect( (array) $q['post_mime_type'], array_keys( $post_mime_types ) ) ) {
                unset( $q['post_mime_type'] );}
            foreach ( array_keys( $post_mime_types ) as $type ) {
                if ( isset( $q['attachment-filter'] ) && "post_mime_type:$type" === $q['attachment-filter'] ) {
                    $q['post_mime_type'] = $type;
                    break;
                }
            }
            if ( isset( $q['detached'] ) || ( isset( $q['attachment-filter'] ) && 'detached' === $q['attachment-filter'] ) ) {
                $q['post_parent'] = 0;}
            if ( isset( $q['mine'] ) || ( isset( $q['attachment-filter'] ) && 'mine' === $q['attachment-filter'] ) ) {
                $q['author'] = $this->_get_current_user_id();}
            if ( isset( $q['s'] ) ) { $this->_add_filter( 'posts_clauses', '_filter_query_attachment_filenames' );}
            return $q;
        }//1253
        /**
         * @description Executes a query for attachments. An array of TP_Query arguments
         * @description . can be passed in, which will override the arguments set by this function.
         * @param bool $q
         * @return array
         */
        protected function _tp_edit_attachments_query( $q = false ):array{
            $this->_tp_method( $this->_tp_edit_attachments_query_vars( $q ) );
            $post_mime_types       = $this->_get_post_mime_types();
            $avail_post_mime_types = $this->_get_available_post_mime_types( 'attachment' );
            return [$post_mime_types, $avail_post_mime_types];
        }//1321
        /**
         * @description Returns the list of classes to be used by a meta box.
         * @param $box_id
         * @param $screen_id
         * @return string
         */
        protected function _postbox_classes( $box_id, $screen_id ):string{
            if ( isset( $_GET['edit'] ) && $_GET['edit'] === $box_id ) {
                $classes = array( '' );
            } elseif ( $this->_get_user_option( 'closedpostboxes_' . $screen_id ) ) {
                $closed = $this->_get_user_option( 'closedpostboxes_' . $screen_id );
                if ( ! is_array( $closed ) ) { $classes =[''];}
                else { $classes = in_array( $box_id, $closed, true ) ? ['closed'] : [''];}
            } else {$classes = [];}
            $classes = $this->_apply_filters( "postbox_classes_{$screen_id}_{$box_id}", $classes );
            return implode( ' ', $classes );
        }//1339
    }
}else die;