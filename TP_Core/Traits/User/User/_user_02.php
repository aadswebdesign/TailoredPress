<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 14:50
 */
namespace TP_Core\Traits\User;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Libs\Queries\TP_User_Query;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_user;

if(ABSPATH){
    trait _user_02 {
        use _init_db,_init_queries,_init_user;
        /**
         * @description Get the current user's ID
         * @return int
         */
        protected function _get_current_user_id():int{
            if ( ! function_exists('_tp_get_current_user') )return 0;
            $user = $this->_tp_get_user_current();
            return ( isset( $user->ID ) ? (int) $user->ID : 0 );
        }//624
        /**
         * @description Retrieve user option that can be either per Site or per Network.
         * @param $option
         * @param mixed $user
         * @return bool
         */
        protected function _get_user_option($option, $user=0):bool{
            $this->tpdb = $this->_init_db();
            if ( empty( $user ) ) $user = $this->_get_current_user_id();
            $user = $this->_get_user_data( $user );
            if ( ! $user ) return false;
            $prefix = $this->tpdb->get_blog_prefix();
            if ($user  instanceof TP_User &&  $user->has_property( $prefix . $option ) ) $result = $user->get( $prefix . $option );
            elseif ( $user->has_property( $option ) )  $result = $user->get( $option );
            else $result = false;
            return $this->_apply_filters( "get_user_option_{$option}", $result, $option, $user );
        }//651
        /**
         * @description Update user option with global blog capability.
         * @param $user_id
         * @param $option_name
         * @param $new_value
         * @param bool $global
         * @return mixed
         */
        protected function _update_user_option( $user_id, $option_name, $new_value, $global = false ){
            $this->tpdb = $this->_init_db();
            if ( ! $global ) $option_name = $this->tpdb->get_blog_prefix() . $option_name;
            return $this->_update_user_meta( $user_id, $option_name, $new_value );
        }//711
        /**
         * @description Delete user option with global blog capability.
         * @param $user_id
         * @param $option_name
         * @param bool $global
         * @return string
         */
        protected function _delete_user_option( $user_id, $option_name, $global = false ):string{
            $this->tpdb = $this->_init_db();
            if ( ! $global ) $option_name = $this->tpdb->get_blog_prefix() . $option_name;
            return $this->_delete_user_meta( $user_id, $option_name );
        }//738
        /**
         * @description Retrieve list of users matching criteria.
         * @param array[] ...$args
         * @return array
         */
        protected function _get_users(...$args):array{
            $args = $this->_tp_parse_args( $args );
            $args['count_total'] = false;
            $user_search = new TP_User_Query( $args );
            return (array) $user_search->get_results();
        }//758

        /**
         * @description List all the users of the site, with several options available.
         * @param array ...$args
         * @return string
         */
        protected function _tp_get_list_users(...$args):string{
            $defaults = ['orderby' => 'name','order' => 'ASC','number' => '','exclude_admin' => true,
                'show_full_name' => false,'feed' => '','feed_image' => '','feed_type' => '',
                'style' => 'list','html' => true,'exclude' => '','include' => '',];
            $args = $this->_tp_parse_args( $args, $defaults );
            $return = '';
            $query_args           = $this->_tp_array_slice_assoc( $args, array( 'orderby', 'order', 'number', 'exclude', 'include' ) );
            $query_args['fields'] = 'ids';
            $users                = $this->_get_users( $query_args );
            foreach ( $users as $user_id ) {
                $user = $this->_get_user_data( $user_id );
                if ( $args['exclude_admin'] && 'admin' === $user->display_name ) continue;
                if ( $args['show_full_name'] && '' !== $user->first_name && '' !== $user->last_name )
                    $name = "$user->first_name $user->last_name";
                else $name = $user->display_name;
                if ( ! $args['html'] ) {
                    $return .= $name . ', ';
                    continue; // No need to go further to process HTML.
                }
                if ( 'list' === $args['style'] ) $return .= '<li>';
                $row = $name;
                if ( ! empty( $args['feed_image'] ) || ! empty( $args['feed'] ) ) {
                    $row .= ' ';
                    if ( empty( $args['feed_image'] ) ) $row .= '(';
                    $row .= "<a href='{$this->_get_author_feed_link( $user->ID, $args['feed_type'] )}'>";
                    $alt = '';
                    if ( ! empty( $args['feed'] ) ) {
                        $alt  = " alt='{$this->_esc_attr( $args['feed'] )}'";
                        $name = $args['feed'];
                    }
                    $row .= '>';
                    if ( ! empty( $args['feed_image'] ) )
                        $row .= "<img src='{$this->_esc_url( $args['feed_image'] )}' style='border: none;' $alt/>";
                    else $row .= $name;
                    $row .= '</a>';
                    if ( empty( $args['feed_image'] ) ) $row .= ')';
                }
                $return .= $row;
                $return .= ( 'list' === $args['style'] ) ? '</li>' : ', ';
            }
            $return = rtrim( $return, ', ' );
            return $return;
        }//797
        protected function _tp_list_users(array ...$args):void{
            echo $this->_tp_get_list_users($args);
        }
        /**
         * @description Get the sites a user belongs to.
         * @param $user_id
         * @param bool $all
         * @return array
         */
        protected function _get_blogs_of_user( $user_id, $all = false ):array{
            $this->tpdb = $this->_init_db();
            $user_id = (int) $user_id;
            if ( empty( $user_id ) ) return [];
            $sites = $this->_apply_filters( 'pre_get_blogs_of_user', null, $user_id, $all );
            if ( null !== $sites ) return $sites;
            $keys = $this->_get_user_meta( $user_id );
            if ( empty( $keys ) ) return [];
            if ( ! $this->_is_multisite() ) {
                $site_id                        = $this->_get_current_blog_id();
                $sites                          = [$site_id => new \stdClass];
                $sites[ $site_id ]->userblog_id = $site_id;
                $sites[ $site_id ]->blogname    = $this->_get_option( 'blogname' );
                $sites[ $site_id ]->domain      = '';
                $sites[ $site_id ]->path        = '';
                $sites[ $site_id ]->site_id     = 1;
                $sites[ $site_id ]->siteurl     = $this->_get_option( 'siteurl' );
                $sites[ $site_id ]->archived    = 0;
                $sites[ $site_id ]->spam        = 0;
                $sites[ $site_id ]->deleted     = 0;
                return $sites;
            }
            $site_ids = [];
            $_base_prefix = $keys[ $this->tpdb->base_prefix . 'capabilities' ];
            if ( isset($_base_prefix) && defined( 'MULTISITE' ) ) {
                $site_ids[] = 1;
                unset( $keys[ $this->tpdb->base_prefix . 'capabilities' ] );
            }
            $keys = array_keys( $keys );
            foreach ( $keys as $key ) {
                if ( 'capabilities' !== substr( $key, -12 ) ) continue;
                if ( $this->tpdb->base_prefix && 0 !== strpos( $key, $this->tpdb->base_prefix ) ) continue;
                $site_id = str_replace( array( $this->tpdb->base_prefix, '_capabilities' ), '', $key );
                if ( ! is_numeric( $site_id ) ) continue;
                $site_ids[] = (int) $site_id;
            }
            $sites = [];
            if ( ! empty( $site_ids ) ) {
                $args = ['number' => '','site__in' => $site_ids,'update_site_meta_cache' => false,];
                if ( ! $all ) {
                    $args['archived'] = 0;
                    $args['spam']     = 0;
                    $args['deleted']  = 0;
                }
                $_sites = $this->_get_sites( $args );
                foreach ( $_sites as $site )
                    $sites[ $site->id ] = (object) ['user_blog_id' => $site->id,'blogname' => $site->blogname,
                        'domain' => $site->domain,'path' => $site->path,'site_id' => $site->network_id,
                        'siteurl' => $site->siteurl,'archived' => $site->archived,'mature' => $site->mature,
                        'spam' => $site->spam,'deleted' => $site->deleted,];
            }
            return $this->_apply_filters( 'get_blogs_of_user', $sites, $user_id, $all );
        }//902
        /**
         * @description Find out whether a user is a member of a given blog.
         * @param int $user_id
         * @param int $blog_id
         * @return bool
         */
        protected function _is_user_member_of_blog( $user_id = 0, $blog_id = 0 ):bool{
            $this->tpdb = $this->_init_db();
            $user_id = (int) $user_id;
            $blog_id = (int) $blog_id;
            if ( empty( $user_id ) ) $user_id = $this->_get_current_user_id();
            if ( empty( $user_id ) ) return false;
            else {
                $user = $this->_get_user_data( $user_id );
                if ( ! $user instanceof TP_User ) return false;
            }
            if ( ! $this->_is_multisite() ) return true;
            if ( empty( $blog_id ) ) $blog_id = $this->_get_current_blog_id();
            $blog = $this->_get_site( $blog_id );
            if ( ! $blog || ! isset( $blog->domain ) || $blog->archived || $blog->spam || $blog->deleted )
                return false;
            $keys = $this->_get_user_meta( $user_id );
            if ( empty( $keys ) ) return false;
            $base_capabilities_key = $this->tpdb->base_prefix . 'capabilities';
            $site_capabilities_key = $this->tpdb->base_prefix . $blog_id . '_capabilities';
            if ( isset( $keys[ $base_capabilities_key ] ) && 1 === $blog_id ) return true;
            if ( isset( $keys[ $site_capabilities_key ] ) ) return true;
            return false;
        }//1031
        /**
         * @description Adds meta data to a user.
         * @param $user_id
         * @param $meta_key
         * @param $meta_value
         * @param bool $unique
         * @return mixed
         */
        protected function _add_user_meta( $user_id, $meta_key, $meta_value, $unique = false ){
            return $this->_add_metadata( 'user', $user_id, $meta_key, $meta_value, $unique );
        }//1098
        /**
         * @description Remove metadata matching criteria from a user.
         * @param $user_id
         * @param $meta_key
         * @param string $meta_value
         * @return mixed
         */
        protected function _delete_user_meta( $user_id, $meta_key, $meta_value = '' ){
            return $this->_delete_metadata( 'user', $user_id, $meta_key, $meta_value );
        }//1120
    }
}else die;