<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-5-2022
 * Time: 23:34
 */
namespace TP_Core\Libs\Embed;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\Queries\TP_Query;
if(ABSPATH){
    class TP_Embed extends Embed_Base {
        public function __construct() {}//30
        public function run_shortcode( $content ){
            $orig_shortcode_tags = $this->_tp_shortcode_tags;
            $this->_remove_all_shortcodes();
            $this->_add_shortcode( 'embed', [$this, 'shortcode'] );
            $content = $this->_do_shortcode( $content, true );
            $this->_tp_shortcode_tags = $orig_shortcode_tags;
            return $content;
        }//61
        public function maybe_run_ajax_cache(): void{
            //left empty, don't wanna use jQuery
        }//83
        public function register_handler( $id, $regex, $callback, $priority = 10 ): void{
            $this->handlers[ $priority ][ $id ] = ['regex' => $regex,'callback' => $callback,];
        }//112
        public function unregister_handler( $id, $priority = 10 ): void{
            unset( $this->handlers[ $priority ][ $id ] );
        }//127
        public function get_embed_handler_html( $attr, $url ){
            $rawattr = $attr;
            $attr    = $this->_tp_parse_args( $attr, $this->_tp_embed_defaults( $url ) );
            ksort( $this->handlers );
            foreach ( $this->handlers as $priority => $handlers ) {
                foreach ( $handlers as $id => $handler ) {
                    if ( is_callable( $handler['callback'] ) && preg_match( $handler['regex'], $url, $matches )) {
                        $return = call_user_func( $handler['callback'], $matches, $attr, $url, $rawattr );
                        if ( false !== $return )
                            return $this->_apply_filters( 'embed_handler_html', $return, $url, $attr );
                    }
                }
            }
            return false;
        }//148
        public function shortcode( $attr, $url = '' ){
            $post = $this->_get_post();
            if ( empty( $url ) && ! empty( $attr['src'] ) )
                $url = $attr['src'];
            $this->last_url = $url;
            if ( empty( $url ) ) {
                $this->last_attr = $attr;
                return '';
            }
            $rawattr = $attr;
            $attr    = $this->_tp_parse_args( $attr, $this->_tp_embed_defaults( $url ) );
            $this->last_attr = $attr;
            $url = str_replace( '&amp;', '&', $url );
            $embed_handler_html = $this->get_embed_handler_html( $rawattr, $url );
            if ( false !== $embed_handler_html ) return $embed_handler_html;
            $post_ID = ( ! empty( $post->ID ) ) ? $post->ID : null;
            if ( ! empty( $this->post_ID ) ) $post_ID = $this->post_ID;
            $key_suffix    = md5( $url . serialize( $attr ) );
            $cachekey      = 'obj_embed_' . $key_suffix;
            $cachekey_time = 'obj_embed_time_' . $key_suffix;
            $ttl = $this->_apply_filters( 'obj_embed_ttl', DAY_IN_SECONDS, $url, $attr, $post_ID );
            $cache      = '';
            $cache_time = 0;
            $cached_post_id = $this->find_obj_embed_post_id( $key_suffix );
            if ( $post_ID ) {
                $cache      = $this->_get_post_meta( $post_ID, $cachekey, true );
                $cache_time = $this->_get_post_meta( $post_ID, $cachekey_time, true );
                if ( ! $cache_time ) $cache_time = 0;
            } elseif ( $cached_post_id ) {
                $_cached_post = $this->_get_post( $cached_post_id );
                //if($cached_post instanceof \stdClass); //todo
                $cached_post = null;
                if($_cached_post instanceof \stdClass){
                    $cached_post = $_cached_post;
                }
                $cache      = $cached_post->post_content;
                $cache_time = strtotime( $cached_post->post_modified_gmt );
            }
            $cached_recently = ( time() - $cache_time ) < $ttl;
            if ( $this->use_cache || $cached_recently ) {
                if ( '{{unknown}}' === $cache )
                    return $this->maybe_make_link( $url );
                if ( ! empty( $cache ) )
                    return $this->_apply_filters( 'embed_obj_embed_html', $cache, $url, $attr, $post_ID );
            }
            $attr['discover'] = $this->_apply_filters( 'embed_obj_embed_discover', true );
            $html = $this->_tp_obj_embed_get( $url, $attr );
            if ( $post_ID ) {
                if ( $html ) {
                    $this->_update_post_meta( $post_ID, $cachekey, $html );
                    $this->_update_post_meta( $post_ID, $cachekey_time, time() );
                } elseif ( ! $cache )
                    $this->_update_post_meta( $post_ID, $cachekey, '{{unknown}}' );
            } else {
                $has_kses = false !== $this->_has_filter( 'content_save_pre', 'tp_filter_post_kses' );
                if ( $has_kses ) $this->_kses_remove_filters();
                $insert_post_args = ['post_name' => $key_suffix,'post_status' => 'publish','post_type' => 'obj_embed_cache',];
                if ( $html ) {
                    if ( $cached_post_id )
                        $this->_tp_update_post($this->_tp_slash(['ID'=> $cached_post_id, 'post_content' => $html,]));
                    else $this->_tp_insert_post($this->_tp_slash( array_merge($insert_post_args,['post_content' => $html,])));
                } elseif ( ! $cache )
                    $this->_tp_insert_post( $this->_tp_slash( array_merge( $insert_post_args,['post_content' => '{{unknown}}',]) ));
                if ( $has_kses ) $this->_kses_init_filters();
            }
            if ( $html ) return $this->_apply_filters( 'embed_obj_embed_html', $html, $url, $attr, $post_ID );
            return $this->maybe_make_link( $url );
        }//195
        public function delete_obj_embed_caches( $post_ID ): void{
            $post_metas = $this->_get_post_custom_keys( $post_ID );
            if ( empty( $post_metas ) ) return;
        }//383
        public function cache_obj_embed( $post_ID ): void{
            $_post = $this->_get_post( $post_ID );
            $post = null;
            if($_post instanceof TP_Post){
                $post = $_post;
            }
            $post_types = $this->_get_post_types( array( 'show_ui' => true ) );
            $cache_obj_embed_types = $this->_apply_filters( 'embed_cache_obj_embed_types', $post_types );
            if ( empty( $post->ID ) || ! in_array( $post->post_type, $cache_obj_embed_types, true ) )
                return;
            if ( ! empty( $post->post_content ) ) {
                $this->post_ID  = $post->ID;
                $this->use_cache = false;
                $content = $this->run_shortcode( $post->post_content );
                $this->auto_embed( $content );
                $this->use_cache = true;
            }
        }//401
        public function auto_embed( $content ){
            $content = $this->_tp_replace_in_html_tags( $content, array( "\n" => '<!-- tp-line-break -->' ) );
            if ( preg_match( '#(^|\s|>)https?://#i', $content ) ) {
                $content = preg_replace_callback( '|^(\s*)(https?://[^\s<>"]+)(\s*)$|im', array( $this, 'auto_embed_callback' ), $content );
                $content = preg_replace_callback( '|(<p(?: [^>]*)?>\s*)(https?://[^\s<>"]+)(\s*<\/p>)|i', array( $this, 'auto_embed_callback' ), $content );
            }
            return str_replace( '<!-- tp-line-break -->', "\n", $content );
        }//439
        public function auto_embed_callback( $matches ): string{
            $old_val = $this->link_if_unknown;
            $this->link_if_unknown = false;
            $return = $this->shortcode( [], $matches[2] );
            $this->link_if_unknown = $old_val;
            return $matches[1] . $return . $matches[3];
        }//460
        public function maybe_make_link( $url ){
            if ( $this->return_false_on_fail ) return false;
            $output = ( $this->link_if_unknown ) ? "<a href='{$this->_esc_url( $url )}'>{$this->_esc_html( $url )}</a>" : $url;
            return $this->_apply_filters( 'embed_maybe_make_link', $output, $url );
        }//475
        public function find_obj_embed_post_id( $cache_key ){
            $cache_group    = 'oembed_cache_post';
            $oembed_post_id = $this->_tp_cache_get( $cache_key, $cache_group );
            if ( $oembed_post_id && 'oembed_cache' === $this->_get_post_type( $oembed_post_id ) )
                return $oembed_post_id;
            $oembed_post_query = new TP_Query(
                ['post_type'  => 'obj_embed_cache','post_status' => 'publish','name' => $cache_key,'posts_per_page' => 1,
                    'no_found_rows' => true,'cache_results' => true,'update_post_meta_cache' => false,
                    'update_post_term_cache' => false,'lazy_load_term_meta' => false,]
            );
            if ( ! empty( $oembed_post_query->posts ) ) {
                $oembed_post_id = $oembed_post_query->posts[0]->ID;
                $this->_tp_cache_set( $cache_key, $oembed_post_id, $cache_group );
               return $oembed_post_id;
            }
            return null;
        }//501
    }
}else die;