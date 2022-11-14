<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-2-2022
 * Time: 19:51
 */
namespace TP_Core\Traits\Capabilities;
use TP_Core\Libs\Users\TP_User;
if(ABSPATH){
    trait _capability_02 {
        /**
         * @description Determines whether user is a site admin.
         * @param bool $user_id
         * @return bool
         */
        protected function _is_super_admin( $user_id = false ):bool{
            if ( ! $user_id ) $_user = $this->_tp_get_user_current();
            else $_user = $this->_get_user_data( $user_id );
            $user = null;
            if($_user instanceof TP_User){
                $user = $_user;
            }
            if ( ! $user || ! $user->exists() ) return false;
            if ( $this->_is_multisite() ) {
                $super_admins = $this->_get_super_admins();
                if ( is_array( $super_admins ) && in_array( $user->user_login, $super_admins, true ) )
                    return true;
            } else if ( $user->has_cap( 'delete_users' ) ) return true;
            return false;
        }//890
        /**
         * @description Grants Super Admin privileges.
         * @param $user_id
         * @return bool
         */
        protected function _grant_super_admin( $user_id ):bool{
            if ( isset($this->__tp_super_admins) || ! $this->_is_multisite() )
                return false;
            $this->_do_action( 'grant_super_admin', $user_id );
            $this->__tp_super_admins =  $this->_get_site_option( 'site_admins', array( 'admin' ) );
            $user = $this->_get_user_data( $user_id );
            if ( $user && ! in_array( $user->user_login, $this->__tp_super_admins, true ) ) {
                $super_admins[] = $user->user_login;
                $this->_update_site_option( 'site_admins', $this->__tp_super_admins );
                $this->_do_action( 'granted_super_admin', $user_id );
                return true;
            }
            return false;
        }//926
        /**
         * @description Revokes Super Admin privileges.
         * @param $user_id
         * @return bool
         */
        protected function _revoke_super_admin( $user_id ):bool{
            if ( isset($this->__tp_super_admins) || ! $this->_is_multisite() )
                return false;
            $this->_do_action( 'revoke_super_admin', $user_id );
            $super_admins = $this->_get_site_option( 'site_admins', array( 'admin' ) );
            $user = $this->_get_user_data( $user_id );
            if ( $user && 0 !== strcasecmp( $user->user_email, $this->_get_site_option( 'admin_email' ) ) ) {
                $key = array_search( $user->user_login, $super_admins, true );
                if ( false !== $key ) {
                    unset( $super_admins[ $key ] );
                    $this->_update_site_option( 'site_admins', $super_admins );
                    $this->_do_action( 'revoked_super_admin', $user_id );
                    return true;
                }
            }
            return false;
        }//973
        /**
         * @description Filters the user capabilities to grant the 'install_languages' capability as necessary.
         * @param $all_caps
         * @return mixed
         */
        protected function _tp_maybe_grant_install_languages_cap( $all_caps ){
            if ( ! empty( $all_caps['update_core'] ) || ! empty( $all_caps['install_themes'] ) )
                $all_caps['install_languages'] = true;
            return $all_caps;
        }//1023
        /**
         * @description Filters the user capabilities to grant
         * @description . the 'resume_themes' capabilities as necessary.
         * @param $all_caps
         * @return mixed
         */
        protected function _tp_maybe_grant_resume_extensions_caps( $all_caps ){
            if ( ! empty( $all_caps['switch_themes'] ) )
                $all_caps['resume_themes'] = true;
            return $all_caps;
        }//1039
        /**
         * @description Filters the user capabilities to grant
         * @description . the 'view_site_health_checks' capabilities as necessary.
         * @param $all_caps
         * @param $user
         * @return mixed
         */
        protected function _tp_maybe_grant_site_health_caps( $all_caps, $user){
            if ( ! $this->_is_multisite() || $this->_is_super_admin( $user->ID ))
                $all_caps['view_site_health_checks'] = true;
            return $all_caps;
        }//1070
        public function dummy_user_roles():void{
            // Dummy gettext calls to get strings in the catalog.
            /* translators: User role for administrators. */
            $this->_x( 'Administrator', 'User role' );
            /* translators: User role for editors. */
            $this->_x( 'Editor', 'User role' );
            /* translators: User role for authors. */
            $this->_x( 'Author', 'User role' );
            /* translators: User role for contributors. */
            $this->_x( 'Contributor', 'User role' );
            /* translators: User role for subscribers. */
            $this->_x( 'Subscriber', 'User role' );
        }//new function
        protected function _capability_hooks():void{
            $this->dummy_user_roles();
        }//new function
    }
}else die;