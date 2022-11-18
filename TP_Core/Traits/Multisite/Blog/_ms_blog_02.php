<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-2-2022
 * Time: 14:42
 */
namespace TP_Core\Traits\Multisite\Blog;
use TP_Core\Libs\TP_Object_Cache;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_cache;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _ms_blog_02 {
        use _init_db;
        use _init_cache;
        /**
         * @description Removes option by name for a given blog ID. Prevents removal of protected TailoredPress options.
         * @param $id
         * @param $option
         * @return mixed
         */
        protected function _delete_blog_option( $id, $option ){
            $id = (int) $id;
            if ( empty( $id ) ) $id = $this->_get_current_blog_id();
            if ( $this->_get_current_blog_id() === $id ) return $this->_delete_option( $option );
            $this->_switch_to_blog( $id );
            $return = $this->_delete_option( $option );
            $this->_restore_current_blog();
            return $return;
        }//430
        /**
         * @description Update an option for a particular blog.
         * @param $id
         * @param $option
         * @param $value
         * @return mixed
         */
        protected function _update_blog_option( $id, $option, $value){
            $id = (int) $id;
            if ( $this->_get_current_blog_id() === $id )
                return $this->_update_option( $option, $value );
            $this->_switch_to_blog( $id );
            $return = $this->_update_option( $option, $value );
            $this->_restore_current_blog();
            return $return;
        }//459
        /**
         * @description Switch the current blog.
         * @param $new_blog_id
         * @return bool
         */
        protected function _switch_to_blog( $new_blog_id):bool{
            $tpdb = $this->_init_db();
            $prev_blog_id = $this->_get_current_blog_id();
            if ( empty( $new_blog_id ) )
                $new_blog_id = $prev_blog_id;
            $this->tp_ms_blog['_tp_switched_stack'] = $prev_blog_id;
            if ( $new_blog_id === $prev_blog_id ) {
                $this->_do_action( 'switch_blog', $new_blog_id, $prev_blog_id, 'switch' );
                $this->tp_ms_blog['switched']    = true;
                return true;
            }
            $tpdb->set_blog_id( $new_blog_id );
            $this->tp_ms_blog['table_prefix'] = $tpdb->get_blog_prefix();
            $this->tp_ms_blog['blog_id'] = $new_blog_id;
            if ( function_exists( [$this, '_tp_cache_switch_to_blog'] ) ) $this->_tp_cache_switch_to_blog( $new_blog_id );
            else{
                $tp_object_cache = $this->_init_object_cache();
                if ( is_object( $tp_object_cache ) && isset( $tp_object_cache->global_groups ) )
                    $global_groups = $tp_object_cache->global_groups;
                else $global_groups = false;
                $this->_init_object_cache();
                if ( function_exists( [$this,'_tp_cache_add_global_groups'] ) ) {
                    if ( is_array( $global_groups ) )
                        $this->_tp_cache_add_global_groups( $global_groups );
                    else  $this->_tp_cache_add_global_groups(['users','userlogins','usermeta','user_meta','useremail','userslugs','site-transient','site-options','blog-lookup','blog-details','rss','global-posts','blog-id-cache','networks','sites','site-details','blog_meta']);
                    $this->_tp_cache_add_non_persistent_groups(['counts']);
                }
            }
            $this->tp_ms_blog['switched']    = true;
            return true;
        }//500 todo
        /**
         * @description Restore the current blog, after calling switch_to_blog().
         * @return bool
         */
        protected function _restore_current_blog():bool{
            $tpdb = $this->_init_db();
            if ( empty( $this->tp_ms_blog['_tp_switched_stack'] ) ) return false;
            $new_blog_id  = array_pop( $this->tp_ms_blog['_tp_switched_stack'] );
            $prev_blog_id = $this->_get_current_blog_id();
            if ( $new_blog_id === $prev_blog_id ) {
                $this->_do_action( 'switch_blog', $new_blog_id, $prev_blog_id, 'restore' );
                $this->tp_ms_blog['switched'] = ! empty( $this->tp_ms_blog['_tp_switched_stack'] );
                return true;
            }
            $tpdb->set_blog_id( $new_blog_id );
            $this->tp_ms_blog['blog_id']      = $new_blog_id;
            $this->tp_ms_blog['table_prefix'] = $tpdb->get_blog_prefix();
            if ( function_exists([$this,'_tp_cache_switch_to_blog'] ) )
                $this->_tp_cache_switch_to_blog( $new_blog_id );
            else {
                $tp_object_cache = $this->_init_object_cache();
                if ($tp_object_cache instanceof TP_Object_Cache && is_object( $tp_object_cache ) && isset( $tp_object_cache->global_groups ) )
                    $global_groups = $tp_object_cache->global_groups;
                 else $global_groups = false;
                $this->_init_object_cache();
                if ( function_exists([$this, '_tp_cache_add_global_groups'] ) ) {
                    if ( is_array( $global_groups ) )
                        $this->_tp_cache_add_global_groups( $global_groups );
                    else $this->_tp_cache_add_global_groups(['users','userlogins','usermeta','user_meta','useremail','userslugs','site-transient','site-options','blog-lookup','blog-details','rss','global-posts','blog-id-cache','networks','sites','site-details','blog_meta']);
                    $this->_tp_cache_add_non_persistent_groups(['counts']);
                }
            }
            $this->_do_action( 'switch_blog', $new_blog_id, $prev_blog_id, 'restore' );
            $this->tp_ms_blog['switched'] = ! empty( $this->tp_ms_blog['_tp_switched_stack'] );
            return true;
        }//585
        /**
         * @description Switches the initialized roles and current user capabilities to another site.
         * @param $new_site_id
         * @param $old_site_id
         */
        protected function _tp_switch_roles_and_user( $new_site_id, $old_site_id ):void{
            if ( $new_site_id === $old_site_id ) return;
            if ( ! $this->_did_action( 'init' ) ) return;
            $_roles = $this->_tp_roles();
            if( $_roles instanceof TP_User ){}
            $_roles->for_site( $new_site_id );
            $current_user = $this->_tp_get_user_current();
            if( $current_user instanceof TP_User ){}
            $current_user->for_site( $new_site_id );
        }//650
        /**
         * @description Determines if switch_to_blog() is in effect
         * @return mixed
         */
        protected function _ms_is_switched(){
            return $this->tp_ms_blog['_tp_switched_stack'];
        }//672
        /**
         * @description Check if a particular blog is archived.
         * @param $id
         * @return string
         */
        protected function _is_archived( $id ):string{
            return $this->_get_blog_status( $id, 'archived' );
        }//684
        /**
         * @description Update the 'archived' status of a particular blog.
         * @param $id
         * @param $archived
         * @return mixed
         */
        protected function _update_archived( $id, $archived ){
            $this->_update_blog_status( $id, 'archived', $archived );
            return $archived;
        }//697
        /**
         * @description Update a blog details field.
         * @param $blog_id
         * @param $pref
         * @param $value
         * @return bool
         */
        protected function _update_blog_status( $blog_id, $pref, $value):bool{
            $allowed_field_names = array( 'site_id', 'domain', 'path', 'registered', 'last_updated', 'public', 'archived', 'mature', 'spam', 'deleted', 'lang_id' );
            if ( ! in_array( $pref, $allowed_field_names, true ) ) return $value;
            $result = $this->_tp_update_site( $blog_id,[ $pref => $value,] );
            if ( $this->_init_error( $result ) ) return false;
            return $value;
        }//716
        /**
         * @description Get a blog details field.
         * @param $id
         * @param $pref
         * @return null
         */
        protected function _get_blog_status( $id, $pref ){
            $this->tpdb = $this->_init_db();
            $details = $this->_get_site( $id );
            if ( $details ) return $details->$pref;
            return $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " %s FROM {$this->tpdb->blogs} WHERE blog_id = %d", $pref, $id ) );
        }//754
    }
}else die;