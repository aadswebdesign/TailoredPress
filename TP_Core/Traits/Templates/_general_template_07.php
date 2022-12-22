<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 17:17
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Libs\AssetsTools\TP_Dependencies;
if(ABSPATH){
    trait _general_template_07 {
        /**
         * @description Fire the tp_footer action.
         * @return string
         */
        protected function _tp_get_footer():string{
            return $this->_get_action( 'tp_footer' );
        }//3052 from general-template
        protected function _tp_footer():void{
            $this->_tp_get_footer();
        }
        /**
         *  @description Fire the tp_body_open action.
         * @return mixed
         */
        protected function _tp_get_body_open(){
            return $this->_get_action( 'tp_body_open' );
        }//3068 from general-template
        protected function _tp_body_open():void{
            $this->_tp_get_body_open();
        }
        /**
         * @description Display the links to the general feeds.
         * @param array $args
         * @return string|void
         */
        protected function _get_feed_links( ...$args):string{
            $output  = "";
            if ( ! $this->_current_theme_supports( 'automatic-feed-links' ) ) return false;
            $defaults = [
                'separator' => $this->_x( '&raquo;', 'feed link' ),
                'feed_title' => $this->__( '%1$s %2$s Feed' ),
                'comments_title' => $this->__( '%1$s %2$s Comments Feed' ),
            ];
            $args = $this->_tp_parse_args( $args, $defaults );
            $href_assembly_1 = $this->_esc_url( $this->_get_feed_link()).'&#92;';
            $href_assembly_2 = $this->_esc_url( $this->_get_feed_link('comments_' . $this->_get_default_feed())).'&#92;';
            if ( $this->_apply_filters( 'feed_links_show_posts_feed', true ) )
                $output .= "<link rel='alternate' type='{$this->_feed_content_type()}' title='{$this->_esc_attr(sprintf($args['feed_title'], $this->_get_bloginfo( 'name' ), $args['separator']))}' href='{$href_assembly_1}' />\n";
            if ( $this->_apply_filters( 'feed_links_show_comments_feed', true ) )
                $output .= "<link rel='alternate' type='{$this->_feed_content_type()}' title='{$this->_esc_attr(sprintf($args['comments_title'], $this->_get_bloginfo( 'name' ), $args['separator']))}' href='{$href_assembly_2}'/>\n";
            return $output;
        }//3084 from general-template
        protected function _feed_links( ...$args):void{
            echo $this->_get_feed_links($args);
        }//3084 from general-template
        /**
         * @description  Display the links to the extra feeds such as category feeds.
         * @param array ...$args
         * @return string
         */
        protected function _feed_get_links_extra(...$args):string{
            $defaults = [
                /* translators: Separator between blog name and feed type in feed links. */
                'separator'     => $this->_x( '&raquo;', 'feed link' ),
                'single_title'   => $this->__( '%1$s %2$s %3$s Comments Feed' ),
                /* translators: 1: Blog name, 2: Separator (raquo), 3: Category name. */
                'cat_title'      => $this->__( '%1$s %2$s %3$s Category Feed' ),
                /* translators: 1: Blog name, 2: Separator (raquo), 3: Tag name. */
                'tag_title'      => $this->__( '%1$s %2$s %3$s Tag Feed' ),
                /* translators: 1: Blog name, 2: Separator (raquo), 3: Term name, 4: Taxonomy singular name. */
                'tax_title'      => $this->__( '%1$s %2$s %3$s %4$s Feed' ),
                /* translators: 1: Blog name, 2: Separator (raquo), 3: Author name. */
                'author_title'   => $this->__( '%1$s %2$s Posts by %3$s Feed' ),
                /* translators: 1: Blog name, 2: Separator (raquo), 3: Search query. */
                'search_title'   => $this->__( '%1$s %2$s Search Results for &#8220;%3$s&#8221; Feed' ),
                /* translators: 1: Blog name, 2: Separator (raquo), 3: Post type name. */
                'post_type_title' => $this->__( '%1$s %2$s %3$s Feed' ),
            ];
            $args = $this->_tp_parse_args( $args, $defaults );
            if ( $this->_is_singular() ) {
                $id   = 0;
                $post = $this->_get_post( $id );
                if ($post->comment_count > 0 || $this->_comments_open() || $this->_pings_open()) {
                    $title = sprintf( $args['single_title'], $this->_get_bloginfo( 'name' ), $args['separator'], $this->_the_title_attribute( array( 'echo' => false ) ) );
                    $href  = $this->_get_post_comments_feed_link( $post->ID );
                }
            } elseif ( $this->_is_post_type_archive() ) {
                $post_type = $this->_get_query_var( 'post_type' );
                if ( is_array( $post_type ) ) {
                    $post_type = reset( $post_type );
                }
                $post_type_obj = $this->_get_post_type_object( $post_type );
                $title         = sprintf( $args['post_type_title'], $this->_get_bloginfo( 'name' ), $args['separator'], $post_type_obj->labels->name );
                $href          = $this->_get_post_type_archive_feed_link( $post_type_obj->name );
            } elseif ( $this->_is_category() ) {
                $term = $this->_get_queried_object();
                if ( $term ) {
                    $title = sprintf( $args['cat_title'], $this->_get_bloginfo( 'name' ), $args['separator'], $term->name );
                    $href  = $this->_get_category_feed_link( $term->term_id );
                }
            } elseif ( $this->_is_tag() ) {
                $term = $this->_get_queried_object();
                if ( $term ) {
                    $title = sprintf( $args['tag_title'], $this->_get_bloginfo( 'name' ), $args['separator'], $term->name );
                    $href  = $this->_get_tag_feed_link( $term->term_id );
                }
            } elseif ( $this->_is_tax() ) {
                $term = $this->_get_queried_object();
                if ( $term ) {
                    $tax   = $this->_get_taxonomy( $term->taxonomy );
                    $title = sprintf( $args['tax_title'], $this->_get_bloginfo( 'name' ), $args['separator'], $term->name, $tax->labels->singular_name );
                    $href  = $this->_get_term_feed_link( $term->term_id, $term->taxonomy );
                }
            } elseif ( $this->_is_author() ) {
                $author_id = (int) $this->_get_query_var( 'author' );
                $title = sprintf( $args['author_title'], $this->_get_bloginfo( 'name' ), $args['separator'], $this->_get_the_author_meta( 'display_name', $author_id ) );
                $href  = $this->_get_author_feed_link( $author_id );
            } elseif ( $this->_is_search() ) {
                $title = sprintf( $args['search_title'], $this->_get_bloginfo( 'name' ), $args['separator'], $this->_get_search_query( false ) );
                $href  = $this->_get_search_feed_link();
            }
            $output  = "";
            if ( isset( $title, $href ) ){
                $output .= "<link rel='alternate' type='{$this->_feed_content_type()}' title='{$this->_esc_attr( $title )}' href='{$this->_esc_url( $href )}'/>\n";//{$this->__('')}
            }
            return $output;
        }//3130 from general-template
        protected function _feed_links_extra(...$args):void{
            echo $this->_feed_get_links_extra($args);
        }
        /**
         * @description Display the link to the Really Simple Discovery service endpoint.
         * @return string
         */
        protected function _get_rsd_link():string{
            return "<link rel='EditURI' type='application/rsd+xml' title='RSD' href='{$this->_esc_url( $this->_site_url( 'xmlrpc.php?rsd', 'rpc' ) )}' />\n";
        }//3212 from general-template
        protected function _rsd_link():void{
            echo $this->_get_rsd_link();
        }
        /**
         * @description Display the link to the Windows Live Writer manifest file.
         * @return string
         */
        protected function _get_window_live_writer_manifest_link():string{
            return "<link rel='wlwmanifest' type='application/wlwmanifest+xml' title='Windows Live Writer Manifest' href='{$this->_includes_url( 'wlw_manifest.xml' )}' />\n";
        }//3222 from general-template
        protected function _window_live_writer_manifest_link():void{
            echo $this->_get_window_live_writer_manifest_link();
        }
        /**
         * @description Displays a referrer strict-origin-when-cross-origin meta tag.
         * @return string
         */
        protected function _tp_get_strict_cross_origin_referrer():string{
            return "<meta name='referer' content='strict-origin-when-cross-origin'/>";
        }//3236 from general-template
        protected function _tp_strict_cross_origin_referrer():void{
            echo $this->_tp_get_strict_cross_origin_referrer();
        }
        /**
         * @description Display site icon meta tags.
         * @return bool|string
         */
        protected function _tp_get_site_icon(){
            if (!$this->_has_site_icon()&&!$this->_is_customize_preview()) return false;
            $meta_tags = [];
            $icon_32   = $this->_get_site_icon_url( 32 );
            if ( empty( $icon_32 ) && $this->_is_customize_preview() )
                $icon_32 = '/favicon.ico';
            if ( $icon_32 )
                $meta_tags[] = sprintf("<link rel='icon' href='%s' sizes='32x32' />", $this->_esc_url( $icon_32 ));
            $icon_180 = $this->_get_site_icon_url( 180 );
            if ( $icon_180 )
                $meta_tags[] = sprintf("<link rel='icon' href='%s' sizes='180x180' />", $this->_esc_url( $icon_180 ));
            $icon_192 = $this->_get_site_icon_url( 192 );
            if ( $icon_192 )
                $meta_tags[] = sprintf("<link rel='icon' href='%s' sizes='192x192' />", $this->_esc_url( $icon_192 ));
            $icon_270 = $this->_get_site_icon_url( 270 );
            if ( $icon_270 )
                $meta_tags[] = sprintf("<link rel='icon' href='%s' sizes='270x270' />", $this->_esc_url( $icon_270 ));
            $meta_tags = $this->_apply_filters( 'site_icon_meta_tags', $meta_tags );
            $meta_tags = array_filter( $meta_tags );
            $output  = "";
            foreach ( $meta_tags as $meta_tag ) $output .= "$meta_tag\n";
            return $output;
        }//3249 from general-template
        protected function _tp_site_icon():void{
            echo $this->_tp_get_site_icon();
        }//3249
        /**
         * @description Prints resource hints to browsers for pre-fetching, pre-rendering
         * @return string
         */
        protected function _tp_get_resource_hints():string{
            $hints = [
                'dns_prefetch' => $this->_tp_dependencies_unique_hosts(),
                'pre_connect' => [],
                'pre_fetch' => [],
                'pre_render' => [],
            ];
            $html = '';
            $hints['dns-prefetch'][] = $this->_apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/13.0.0/svg/' );
            /** @noinspection LoopWhichDoesNotLoopInspection *///todo
            foreach ($hints as $relation_type => $urls ) {
                $unique_urls = [];
                $urls = $this->_apply_filters( 'tp_resource_hints', $urls, $relation_type );
                foreach ( $urls as $key => $url ) {
                    $atts = [];
                    if ( is_array( $url ) ) {
                        if ( isset( $url['href'] ) ) {
                            $atts = $url;
                            $url  = $url['href'];
                        } else continue;
                    }
                    $url = $this->_esc_url( $url, array( 'http', 'https' ) );
                    if ( ! $url ) continue;
                    if ( isset( $unique_urls[ $url ] ) ) continue;
                    if ( in_array( $relation_type, ['pre_connect','dns_prefetch'],true )){
                        $parsed = $this->_tp_parse_url( $url );
                        if ( empty( $parsed['host'] ) ) continue;
                        if ( 'pre_connect' === $relation_type && ! empty( $parsed['scheme'] ) )
                            $url = $parsed['scheme'] . '://' . $parsed['host'];
                        else $url = '//' . $parsed['host']; // Use protocol-relative URLs for dns-prefetch or if scheme is missing.
                    }
                    $atts['rel']  = $relation_type;
                    $atts['href'] = $url;
                    $unique_urls[ $url ] = $atts;
                }

                foreach ( $unique_urls as $atts ) {
                    foreach ( $atts as $attr => $value ) {
                        if ( ! is_scalar($value)||(!in_array( $attr, array( 'as', 'crossorigin', 'href', 'pr', 'rel', 'type' ), true ) && ! is_numeric( $attr ) )
                        ) continue;
                        $value = ( 'href' === $attr ) ? $this->_esc_url( $value ) : $this->_esc_attr( $value );
                        if ( ! is_string( $attr ) ) $html .= " $value";
                        else $html .= " $attr='$value'";
                    }
                    $trim_html = trim( $html );
                    $html .= "<link $trim_html />\n";
                }
                return $html;
            }
        }//3302 from general-template
        protected function _tp_resource_hints():void{
            echo $this->_tp_get_resource_hints();
        }
        /**
         * @description Retrieves a list of unique hosts of all enqueued scripts and styles.
         * @return array
         */
        protected function _tp_dependencies_unique_hosts():array{
            $unique_hosts = [];
            foreach ( array(  $this->tp_scripts,  $this->tp_styles ) as $dependencies ) {
                if ( $dependencies instanceof TP_Dependencies && ! empty( $dependencies->queue ) ) {
                    foreach ( $dependencies->queue as $handle ) {
                        if ( ! isset($dependencies->registered[$handle])){ continue;}
                        $dependency = $dependencies->registered[ $handle ];
                        $parsed     = $this->_tp_parse_url( $dependency->src );
                        if ($parsed['host'] !== $_SERVER['SERVER_NAME'] && ! empty( $parsed['host'] ) && ! in_array( $parsed['host'], $unique_hosts, true )){
                            $unique_hosts[] = $parsed['host'];
                        }
                    }
                }
            }
            return $unique_hosts;
        }//3423 from general-template
    }
}else die;