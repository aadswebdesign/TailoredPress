<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-3-2022
 * Time: 18:55
 */
namespace TP_Core\Traits\Comment;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\IXR\IXR_Client;
use TP_Core\Libs\HTTP\TP_HTTP_IXR_Client;
use TP_Core\Libs\IXR\IXR_Error;
if(ABSPATH){
    trait _comment_06{
        use _init_db;
        /**
         * @description Perform all trackbacks.
         */
        protected function _do_all_trackbacks():void{
            $trackbacks = $this->_get_posts(
                ['post_type' => $this->_get_post_types(),'suppress_filters' => false,
                    'no_paging' => true,'meta_key' => '_trackback_me','fields' => 'ids',]
            );
            foreach ( $trackbacks as $trackback ) {
                $this->_delete_post_meta( $trackback, '_trackback_me' );
                $this->_do_trackbacks( $trackback );
            }
        }//2868
        /**
         * @description Perform trackbacks.
         * @param $post_id
         * @return bool
         */
        protected function _do_trackbacks( $post_id ):bool{
            $tpdb = $this->_init_db();
            $post = $this->_get_post( $post_id );
            if ( ! $post ) return false;
            $to_ping = $this->_get_to_ping( $post );
            $pinged  = $this->_get_pung( $post );
            if ( empty( $to_ping ) ) {
                $tpdb->update( $tpdb->posts, array( 'to_ping' => '' ), array( 'ID' => $post->ID ) );
                return false;
            }
            if ( empty( $post->post_excerpt ) )
                $excerpt = $this->_apply_filters( 'the_content', $post->post_content, $post->ID );
            else $excerpt = $this->_apply_filters( 'the_excerpt', $post->post_excerpt );
            $excerpt = str_replace( ']]>', ']]&gt;', $excerpt );
            $excerpt = $this->_tp_html_excerpt( $excerpt, 252, '&#8230;' );
            $post_title = $this->_apply_filters( 'the_title', $post->post_title, $post->ID );
            $post_title = strip_tags( $post_title );
            if ( $to_ping ) {
                foreach ( (array) $to_ping as $tb_ping ) {
                    $tb_ping = trim( $tb_ping );
                    if ( ! in_array( $tb_ping, $pinged, true ) ) {
                        $this->_trackback( $tb_ping, $post_title, $excerpt, $post->ID );
                        $pinged[] = $tb_ping;
                    } else  $tpdb->query( $tpdb->prepare( TP_UPDATE . " $tpdb->posts SET to_ping = TRIM(REPLACE(to_ping, %s,'')) WHERE ID = %d", $tb_ping, $post->ID));
                }
            }
            return true;
        }//2895
        /**
         * @description Sends pings to all of the ping site services.
         * @param int $post_id
         * @return int
         */
        protected function _generic_ping( $post_id = 0 ):int{
            $services = $this->_get_option( 'ping_sites' );
            $services = explode( "\n", $services );
            foreach ($services as $service ) {
                $service = trim( $service );
                if ( '' !== $service ) $this->_weblog_ping( $service );
            }
            return $post_id;
        }//2952
        /**
         * @description Pings back the links found in a post.
         * @param $content
         * @param $post_id
         */
        protected function _pingback( $content, $post_id ):void{
            $post_links = array();
            $post = $this->_get_post( $post_id );
            if (!$post) return;
            $pung = $this->_get_pung( $post );
            if ( empty( $content ) ) $content = $post->post_content;
            $post_links_temp = $this->_tp_extract_urls( $content );
            foreach ( (array) $post_links_temp as $link_test ) {
                if ( ! in_array( $link_test, $pung, true ) && ( $this->_url_to_postid( $link_test ) !== $post->ID )
                    && ! $this->_is_local_attachment( $link_test )
                ) {
                    $test = parse_url( $link_test );
                    if ( $test ) {
                        if ( isset( $test['query'] ) ) $post_links[] = $link_test;
                        elseif ( isset( $test['path'] ) && ( '/' !== $test['path'] ) && ( '' !== $test['path'] ) )
                            $post_links[] = $link_test;
                    }
                }
            }
            $post_links = array_unique( $post_links );
            $this->_do_action_ref_array( 'pre_ping', [&$post_links, &$pung, $post->ID] );
            foreach ($post_links as $page_linked_to ) {
                $pingback_server_url = $this->_discover_pingback_server_uri( $page_linked_to );
                if ( $pingback_server_url ) {
                    set_time_limit( 60 );
                    // Now, the RPC call.
                    $page_linked_from = $this->_get_permalink( $post );
                    $_client = new TP_HTTP_IXR_Client( $pingback_server_url );
                    $client = null;
                    if($_client instanceof IXR_Client){
                        $client = $_client;
                    }
                    $client->timeout = 3;
                    $client->user_agent = $this->_apply_filters( 'pingback_useragent', $client->user_agent . ' -- TailoredPress/' . $this->_get_bloginfo( 'version' ), $client->user_agent, $pingback_server_url, $page_linked_to, $page_linked_from );
                    $client->debug = false;
                    $_client_error = $client->error;
                    $client_error = null;
                    if($_client_error instanceof IXR_Error){
                        $client_error = $_client_error;
                    }
                    if (( isset( $client_error->code ) && 48 === $client_error->code ) || $client->query( 'pingback.ping', $page_linked_to, $page_linked_from ))
                        $this->_add_ping( $post, $page_linked_to );
                }
            }
        }//2975
        /**
         * @description Check whether blog is public before returning sites.
         * @param $sites
         * @return string
         */
        protected function _privacy_ping_filter( $sites ):string{
            if ( '0' !== $this->_get_option( 'blog_public' ) )
                return $sites;
            else return '';
        }//3081
        /**
         * @description Send a Trackback.
         * @param $trackback_url
         * @param $title
         * @param $excerpt
         * @param $ID
         * @return bool|int
         */
        protected function _trackback( $trackback_url, $title, $excerpt, $ID ){
            $tpdb = $this->_init_db();
            if ( empty( $trackback_url ) ) return false;
            $options            = [];
            $options['timeout'] = 10;
            $options['body']    = [
                'title'     => $title,
                'url'       => $this->_get_permalink( $ID ),
                'blog_name' => $this->_get_option( 'blogname' ),
                'excerpt'   => $excerpt,
            ];
            $response = $this->_tp_safe_remote_post( $trackback_url, $options );
            if ( $this->_init_error( $response ) ) return false;
            $tpdb->query( $tpdb->prepare( TP_UPDATE . " $tpdb->posts SET pinged = CONCAT(pinged, '\n', %s) WHERE ID = %d", $trackback_url, $ID ) );
            return $tpdb->query( $tpdb->prepare( TP_UPDATE . " $tpdb->posts SET to_ping = TRIM(REPLACE(to_ping, %s, '')) WHERE ID = %d", $trackback_url, $ID ) );
        }//3104
        /**
         * @description Send a pingback.
         * @param string $server
         * @param string $path
         */
        protected function _weblog_ping( $server = '', $path = '' ):void{
            $client = new TP_HTTP_IXR_Client( $server, ( ( trim($path) === '' || ( '/' === $path ) ) ? false : $path ) );
            $client->timeout    = 3;
            $client->user_agent .= ' -- TailoredPress/' . $this->_get_bloginfo( 'version' );
            $client->debug = false;
            $home          = $this->_trailingslashit( $this->_home_url() );
            if ( ! $client->query( 'weblogUpdates.extendedPing', $this->_get_option( 'blogname' ), $home, $this->_get_bloginfo( 'rss2_url' ) ) ) // Then try a normal ping.
                $client->query( 'weblogUpdates.ping', $this->_get_option( 'blogname' ), $home );
        }//3138
        /**
         * @description Default filter attached to pingback_ping_source_uri to validate the pingback's Source URI
         * @param $source_uri
         * @return string
         */
        protected function _pingback_ping_source_uri( $source_uri ):string{
            return (string) $this->_tp_http_validate_url( $source_uri );
        }//3165
        /**
         * @description Default filter attached to xmlrpc_pingback_error.
         * @param $ixr_error
         * @return IXR_Error
         */
        protected function _xmlrpc_pingback_error( $ixr_error ): IXR_Error{
            if ( 48 === $ixr_error->code ) return $ixr_error;
            return new IXR_Error( 0, '' );
        }//3182
        /**
         * @description Removes a comment from the object cache.
         * @param $ids
         */
        protected function _clean_comment_cache( $ids ):void{
            $comment_ids = (array) $ids;
            $this->_tp_cache_delete_multiple( $comment_ids, 'comment' );
            foreach ( $comment_ids as $id )
                $this->_do_action( 'clean_comment_cache', $id );
            $this->_tp_cache_set( 'last_changed', microtime(), 'comment' );
        }//3200
    }
}else die;