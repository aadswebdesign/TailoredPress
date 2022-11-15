<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-5-2022
 * Time: 10:19
 */
namespace TP_Core\Traits\Embed;
use TP_Core\Traits\Inits\_init_embed;
use TP_Core\Libs\Embed\TP_ObjEmbed_Controller;
if(ABSPATH){
    trait _embed_02{
        use _init_embed;
        /**
         * @description Video embed handler callback.
         * @param $attr
         * @param $url
         * @param $rawattr
         * @return mixed
         */
        protected function _tp_embed_handler_video( $attr, $url, $rawattr ){
            $dimensions = '';
            if ( ! empty( $rawattr['width'] ) && ! empty( $rawattr['height'] ) ) {
                $dimensions .= sprintf( " width='%d'", (int) $rawattr['width'] );
                $dimensions .= sprintf( " height='%d'", (int) $rawattr['height'] );
            }
            $video = sprintf( "[video %s src='%s']", $dimensions, $this->_esc_url( $url ) );
            return $this->_apply_filters( 'tp_embed_handler_video', $video, $attr, $url, $rawattr );
        }//299
        /**
         * @description Registers the oEmbed REST API route.
         */
        protected function _tp_obj_embed_register_route():void{
            $controller = new TP_ObjEmbed_Controller();
            $controller->register_routes();
        }//325
        /**
         * @description Registers the oEmbed REST API route.
         * @return mixed
         */
        protected function _tp_obj_embed_get_discovery_links(){
            $output = '';
            if ( $this->_is_singular() ) {
                $output .= "<link rel='alternate' type='application/json+oembed' href='{$this->_esc_url( $this->_get_obj_embed_endpoint_url( $this->_get_permalink() ) )}'/>\n";
                if ( class_exists( 'SimpleXMLElement' ) )
                    $output .= "<link rel='alternate' type='text/xml+oembed' href='{$this->_esc_url( $this->_get_obj_embed_endpoint_url( $this->_get_permalink(), 'xml' ) )}'/>\n";
            }
            return $this->_apply_filters( 'oembed_discovery_links', $output );
        }//335
        public function tp_obj_embed_add_discovery_links():void{
            echo $this->_tp_obj_embed_get_discovery_links();
        }
        /**
         * @description Enqueue the tp-embed script if the provided oEmbed HTML contains a post embed.
         * @param $html
         * @return mixed
         */
        protected function _tp_maybe_enqueue_oembed_host_js( $html ){
            if ( $this->_has_action( 'tp_head', 'tp_oembed_add_host_js' )
                && preg_match( '/<blockquote\s[^>]*?tp-embedded-content/', $html )
            ) $this->tp_enqueue_script( 'tp-embed' );
            return $html;
        }//388
        /**
         * @description Retrieves the URL to embed a specific post in an iframe.
         * @param null $post
         * @return bool
         */
        protected function _get_post_embed_url( $post = null ):bool{
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $embed_url     = $this->_trailingslashit( $this->_get_permalink( $post ) ) . $this->_user_trailingslashit( 'embed' );
            $path_conflict = $this->_get_page_by_path( str_replace( $this->_home_url(), '', $embed_url ), OBJECT, $this->_get_post_types( array( 'public' => true ) ) );
            if ($path_conflict || ! $this->_get_option( 'permalink_structure' ))
                $embed_url = $this->_add_query_arg( array( 'embed' => 'true' ), $this->_get_permalink( $post ) );
            return $this->_esc_url_raw( $this->_apply_filters( 'post_embed_url', $embed_url, $post ) );
        }//407
        /**
         * @description Retrieves the obj_Embed endpoint URL for a given permalink.
         * @param string $permalink
         * @param string $format
         * @return mixed
         */
        protected function _get_obj_embed_endpoint_url( $permalink = '', $format = 'json' ){
            $url = $this->_rest_url( 'oembed/1.0/embed' );
            if ( '' !== $permalink )
                $url = $this->_add_query_arg(['url' => urlencode( $permalink ),'format' => ( 'json' !== $format ) ? $format : false,],$url);
            return $this->_apply_filters( 'oembed_endpoint_url', $url, $permalink, $format );
        }//443
        /**
         * @description Retrieves the embed code for a specific post.
         * @param $width
         * @param $height
         * @param null $post
         * @return bool
         */
        protected function _get_post_embed_html( $width, $height, $post = null ):bool{
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $embed_url = $this->_get_post_embed_url( $post );
            $secret     = $this->_tp_generate_password( 10, false );
            $embed_url .= "#?secret={$secret}";
            $percent =['one' => '%1$s','two' => '%2$s','three' => '%3$s','four' => '%4$s','five' => '%5$s', ];
            $output = sprintf(
                "<blockquote class='tp-embedded-content' data-secret='{$percent['one']}'><a href='{$percent['two']}'>{$percent['three']}</a></blockquote>",
                $this->_esc_attr( $secret ),
                $this->_esc_url( $this->_get_permalink( $post ) ),
                $this->_get_the_title( $post )
            );
            $iframe_basics = " marginwidth ='0' marginheight='0' scrolling='no'";
            $output .= sprintf(
                "<iframe class='tp-embedded-content' sandbox='allow-scripts' security='restricted' src='{$percent['one']}' width='{$percent['two']}' height='{$percent['three']}' title='{$percent['four']}' data-secret='{$percent['five']}$iframe_basics'></iframe>",
                $this->_esc_url( $embed_url ),
                $this->_abs_int( $width ),
                $this->_abs_int( $height ),
                $this->_esc_attr(/* translators: 1: Post title, 2: Site title. */
                    sprintf($this->__( '&#8220;%1$s&#8221; &#8212; %2$s' ),$this->_get_the_title( $post ),$this->_get_bloginfo( 'name' ))
                ),
                $this->_esc_attr( $secret )
            );
            $output .= $this->_tp_get_inline_script_tag(
                file_get_contents( TP_LIBS_ASSETS . '/js/tp_embed' . $this->_tp_scripts_get_suffix() . '.js' )
            );
            return $this->_apply_filters( 'embed_html', $output, $post, $width, $height );
        }//478
        /**
         * @description Retrieves the obj_Embed response data for a given post.
         * @param $post
         * @param $width
         * @return bool
         */
        protected function _get_obj_embed_response_data( $post, $width ):bool{
            $post  = $this->_get_post( $post );
            $width = $this->_abs_int( $width );
            if ( ! $post ) return false;
            if ( ! $this->_is_post_publicly_viewable( $post ) ) return false;
            $min_max_width = $this->_apply_filters('oembed_min_max_width',['min' => 200,'max' => 600,]);
            $width  = min( max( $min_max_width['min'], $width ), $min_max_width['max'] );
            $height = max( ceil( $width / 16 * 9 ), 200 );
            $data = ['version' => '1.0','provider_name' => $this->_get_bloginfo( 'name' ),
                'provider_url' => $this->_get_home_url(),'author_name' => $this->_get_bloginfo( 'name' ),
                'author_url' => $this->_get_home_url(),'title' => $this->_get_the_title( $post ),'type' => 'link',];
            $author = $this->_get_user_data( $post->post_author );
            if ( $author ) {
                $data['author_name'] = $author->display_name;
                $data['author_url']  = $this->_get_author_posts_url( $author->ID );
            }
            return $this->_apply_filters( 'oembed_response_data', $data, $post, $width, $height );
        }//545
        /**
         * @description Retrieves the obj_Embed response data for a given URL.
         * @param $url
         * @param $args
         * @return bool|object
         */
        protected function _get_obj_embed_response_data_for_url( $url, $args ){
            $switched_blog = false;
            if ( $this->_is_multisite() ) {
                $url_parts =$this->_tp_parse_args($this->_tp_parse_url( $url ),['host' => '','path' => '/',]);
                $qv = ['domain' => $url_parts['host'],'path' => '/','update_site_meta_cache' => false,];
                if ( ! $this->_is_subdomain_install() ) {
                    $path = explode( '/', ltrim( $url_parts['path'], '/' ) );
                    $path = reset( $path );
                    if ( $path ) $qv['path'] = $this->_get_network()->path . $path . '/';
                }
                $sites = $this->_get_sites( $qv );
                $site  = reset( $sites );
                if ( ! empty( $site->deleted ) || ! empty( $site->spam ) || ! empty( $site->archived ) )
                    return false;
                if ( $site && $this->_get_current_blog_id() !== (int) $site->blog_id ) {
                    $this->_switch_to_blog( $site->blog_id );
                    $switched_blog = true;
                }
            }
            $post_id = $this->_url_to_postid( $url );
            $post_id = $this->_apply_filters( 'oembed_request_post_id', $post_id, $url );
            if ( ! $post_id ) {
                if ( $switched_blog ) $this->_restore_current_blog();
                return false;
            }
            $width = $args['width'] ?? 0;
            $data = $this->_get_obj_embed_response_data( $post_id, $width );
            if ( $switched_blog ) $this->_restore_current_blog();
            return $data ? (object) $data : false;
        }//620
        /**
         * @description Filters the obj_Embed response data to return an iframe embed code.
         * @param $data
         * @param $post
         * @param $width
         * @param $height
         * @return mixed
         */
        protected function _get_obj_embed_response_data_rich( $data, $post, $width, $height ){
            $data['width']  = $this->_abs_int( $width );
            $data['height'] = $this->_abs_int( $height );
            $data['type']   = 'rich';
            $data['html']   = $this->_get_post_embed_html( $width, $height, $post );
            $thumbnail_id = false;
            if ( $this->_has_post_thumbnail( $post->ID ) )
                $thumbnail_id = $this->_get_post_thumbnail_id( $post->ID );
            if ( 'attachment' === $this->_get_post_type( $post ) ) {
                if ( $this->_tp_attachment_is_image( $post ) ) $thumbnail_id = $post->ID;
                elseif ( $this->_tp_attachment_is( 'video', $post ) ) {
                    $thumbnail_id = $this->_get_post_thumbnail_id( $post );
                    $data['type'] = 'video';
                }
            }
            if ( $thumbnail_id ) {
                @list( $thumbnail_url, $thumbnail_width, $thumbnail_height ) = $this->_tp_get_attachment_image_src( $thumbnail_id, [$width, 99999] );
                $data['thumbnail_url'] = $thumbnail_url;
                $data['thumbnail_width'] = $thumbnail_width;
                $data['thumbnail_height'] = $thumbnail_height;
            }
            return $data;
        }//698
    }
}else die;