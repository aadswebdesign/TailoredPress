<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:23
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Libs\Post\TP_Post_Type;
if(ABSPATH){
    trait _link_template_10 {
        /**
         * @description Retrieves the home URL for the current network.
         * @param string $path
         * @param null $scheme
         * @return mixed
         */
        protected function _network_home_url( $path = '', $scheme = null ){
            if ( ! $this->_is_multisite() ) return $this->_home_url( $path, $scheme );
            $current_network = $this->_get_network();
            $orig_scheme     = $scheme;
            if ( ! in_array( $scheme, array( 'http', 'https', 'relative' ), true ) )
                $scheme = $this->_is_ssl() ? 'https' : 'http';
            if ( 'relative' === $scheme ) $url = $current_network->path;
            else $url = $this->_set_url_scheme( 'http://' . $current_network->domain . $current_network->path, $scheme );
            if ( $path && is_string( $path ) ) {
                $url .= ltrim( $path, '/' );
            }
            return $this->_apply_filters( 'network_home_url', $url, $path, $orig_scheme );
        }//3629 from link-template
        /**
         * @description Retrieves the URL to the admin area for the network.
         * @param string $path
         * @param string $scheme
         * @return mixed
         */
        protected function _network_admin_url( $path = '', $scheme = 'admin' ){
            if ( ! $this->_is_multisite() ) return $this->_admin_url( $path, $scheme );
            $url = $this->_network_site_url( 'tp-admin/network/', $scheme );//todo
            if ( $path && is_string( $path ) ) $url .= ltrim( $path, '/' );
            return $this->_apply_filters( 'network_admin_url', $url, $path, $scheme );
        }//3675 from link-template
        /**
         * @description Retrieves the URL to the admin area for the current user.
         * @param string $path
         * @param string $scheme
         * @return mixed
         */
        protected function _user_admin_url( $path = '', $scheme = 'admin' ){
            $url = $this->_network_site_url( 'tp-admin/user/', $scheme );//todo
            if ( $path && is_string( $path ) ) $url .= ltrim( $path, '/' );
            return $this->_apply_filters( 'user_admin_url', $url, $path, $scheme );
        }//3711 from link-template
        /**
         * @description Retrieves the URL to the admin area for either,
         * @description . the current site or the network depending on context.
         * @param string $path
         * @param string $scheme
         * @return mixed
         */
        protected function _self_admin_url( $path = '', $scheme = 'admin' ){
            if ( $this->_is_network_admin() ) $url = $this->_network_admin_url( $path, $scheme );
            elseif ( $this->_is_user_admin() ) $url = $this->_user_admin_url( $path, $scheme );
            else $url = $this->_admin_url( $path, $scheme );
            return $this->_apply_filters( 'self_admin_url', $url, $path, $scheme );
        }//3743 from link-template
        /**
         * @description Sets the scheme for a URL.
         * @param $url
         * @param null $scheme
         * @return mixed
         */
        protected function _set_url_scheme( $url, $scheme = null ){
            $orig_scheme = $scheme;
            if ( ! $scheme )  $scheme = $this->_is_ssl() ? 'https' : 'http';
            elseif ( 'admin' === $scheme || 'login' === $scheme || 'login_post' === $scheme || 'rpc' === $scheme )
                $scheme = $this->_is_ssl() || $this->_force_ssl_admin() ? 'https' : 'http';
            elseif ( 'http' !== $scheme && 'https' !== $scheme && 'relative' !== $scheme )
                $scheme = $this->_is_ssl() ? 'https' : 'http';
            $url = trim( $url );
            if (strpos($url, '//') === 0) $url = 'http:' . $url;
            if ( 'relative' === $scheme ) {
                $url = ltrim( preg_replace( '#^\w+://[^/]*#', '', $url ) );
                if ( '' !== $url && '/' === $url[0] ) $url = '/' . ltrim( $url, "/ \t\n\r\0\x0B" );
            } else $url = preg_replace( '#^\w+://#', $scheme . '://', $url );
            return $this->_apply_filters( 'set_url_scheme', $url, $scheme, $orig_scheme );
        }//3775 from link-template
        /**
         * @description Retrieves the URL to the user's dashboard.
         * @param int $user_id
         * @param string $path
         * @param string $scheme
         * @return mixed
         */
        protected function _get_dashboard_url( $user_id = 0, $path = '', $scheme = 'admin' ){
            $user_id = $user_id ? (int) $user_id : $this->_get_current_user_id();
            $blogs = $this->_get_blogs_of_user( $user_id );
            if (empty( $blogs ) && $this->_is_multisite() && ! $this->_user_can( $user_id, 'manage_network' ))
                $url = $this->_user_admin_url( $path, $scheme );
            elseif ( ! $this->_is_multisite() ) $url = $this->_admin_url( $path, $scheme );
            else {
                $current_blog = $this->_get_current_blog_id();
                if ( $current_blog && ( $this->_user_can( $user_id, 'manage_network' ) || array_key_exists($current_blog, $blogs)) )
                    $url = $this->_admin_url( $path, $scheme );
                else {
                    $active = $this->_get_active_blog_for_user( $user_id );
                    if ( $active ) $url = $this->_get_admin_url( $active->blog_id, $path, $scheme );
                    else $url = $this->_user_admin_url( $path, $scheme );
                }
            }
            return $this->_apply_filters( 'user_dashboard_url', $url, $user_id, $path, $scheme );
        }//3829 from link-template
        /**
         * @description Retrieves the URL to the user's profile editor.
         * @param int $user_id
         * @param string $scheme
         * @return mixed
         */
        protected function _get_edit_profile_url( $user_id = 0, $scheme = 'admin' ){
            $user_id = $user_id ? (int) $user_id : $this->_get_current_user_id();
            if ( $this->_is_user_admin() )
                $url = $this->_user_admin_url( 'profile.php', $scheme );
            elseif ( $this->_is_network_admin() )
                $url = $this->_network_admin_url( 'profile.php', $scheme );
            else $url = $this->_get_dashboard_url( $user_id, 'profile.php', $scheme );
            return $this->_apply_filters( 'edit_profile_url', $url, $user_id, $scheme );
        }//3877 from link-template
        /**
         * @description Returns the canonical URL for a post.
         * @param null $post
         * @return bool
         */
        protected function _tp_get_canonical_url( $post = null ):bool{
            $post = $this->_get_post( $post );
            if ( ! $post )return false;
            if ( 'publish' !== $post->post_status ) return false;
            $canonical_url = $this->_get_permalink( $post );
            if ( $this->_get_queried_object_id() === $post->ID ) {
                $page = $this->_get_query_var( 'page', 0 );
                if ( $page >= 2 ) {
                    if ( ! $this->_get_option( 'permalink_structure' ) )
                        $canonical_url = $this->_add_query_arg( 'page', $page, $canonical_url );
                    else $canonical_url = $this->_trailingslashit( $canonical_url ) . $this->_user_trailingslashit( $page, 'single_paged' );
                }
                $cpage = $this->_get_query_var( 'cpage', 0 );
                if ( $cpage ) $canonical_url = $this->_get_comments_page_num_link( $cpage );
            }
            return $this->_apply_filters( 'get_canonical_url', $canonical_url, $post );
        }//3913 from link-template
        //@description Outputs rel=canonical for singular queries.
        protected function _get_rel_canonical(){
            $return = null;
            if ( ! $this->_is_singular() ) $return = false;
            $id = $this->_get_queried_object_id();
            if ( 0 === $id ) $return = false;
            $url = $this->_tp_get_canonical_url( $id );
            if ( ! empty( $url ) )$return = "<link rel='canonical' href='{$this->_esc_url( $url )}' />\n";
            return $return;
        }//3960 from link-template
        protected function _rel_canonical():void{
            echo $this->_get_rel_canonical();
        }//added
        /**
         * @description Returns a short link for a post, page, attachment, or site.
         * @param int $id
         * @param string $context
         * @param bool $allow_slugs
         * @return string
         */
        protected function _tp_get_short_link( $id = 0, $context = 'post', $allow_slugs = true ):string{
            $shortlink = $this->_apply_filters( 'pre_get_shortlink', false, $id, $context, $allow_slugs );
            $post = null;
            if ( false !== $shortlink ) return $shortlink;
            $post_id = 0;
            if ( 'query' === $context && $this->_is_singular() ) {
                $post_id = $this->_get_queried_object_id();
                $post    = $this->_get_post( $post_id );
            } elseif ( 'post' === $context ) {
                $post = $this->_get_post( $id );
                if ( ! empty( $post->ID ) ) $post_id = $post->ID;
            }
            $shortlink = '';
            if ($post instanceof TP_Post_Type && ! empty( $post_id ) ) {
                $post_type = $this->_get_post_type_object( $post->post_type );
                if ( 'page' === $post->post_type && $this->_get_option( 'page_on_front' ) === $post->ID && 'page' === $this->_get_option( 'show_on_front' ) )
                    $shortlink = $this->_home_url( '/' );
                elseif ( $post_type && $post_type->public ) $shortlink = $this->_home_url( '?p=' . $post_id );
            }
            return $this->_apply_filters( 'get_shortlink', $shortlink, $id, $context, $allow_slugs );
        }//3998 from link-template
    }
}else die;