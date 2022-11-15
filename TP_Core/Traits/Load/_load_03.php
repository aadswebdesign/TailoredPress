<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 10:04
 */
namespace TP_Core\Traits\Load;
use TP_Admin\Traits\AdminInits\_adm_init_screen;
use TP_Core\Libs\Recovery\TP_Recovery_Mode;
if(ABSPATH){
    trait _load_03 {
        use _adm_init_screen;
        /**
         * @description Is TailoredPress in Recovery Mode.
         * @return mixed
         */
        protected function _tp_is_recovery_mode(){
            $_recovery_mode = $this->_tp_recovery_mode();
            $recovery_mode = null;
            if($_recovery_mode instanceof TP_Recovery_Mode ){
                $recovery_mode = $_recovery_mode;
            }
            return $recovery_mode->is_active();
        }//960
        /**
         * todo needs edits
         * @description Determines whether we are currently on an endpoint that should be protected against WSODs.
         * @return bool
         */
        protected function _is_protected_endpoint():bool{
            if ( isset( $this->pagenow ) && 'tp_login.php' === $this->pagenow ) //todo
                return true;
            // Protect the admin backend.
            if ( $this->_is_admin() && ! $this->_tp_doing_async() ) return true;
            if ( $this->_is_protected_async_action() ) return true;
            return (bool) $this->_apply_filters( 'is_protected_endpoint', false );
        }//973
        /**
         * @description Determines whether we are currently handling an,
         * @description . Ajax action that should be protected against WSODs.
         * @return bool
         */
        protected function _is_protected_async_action():bool{
            if ( ! $this->_tp_doing_async() ) return false;
            if ( ! isset( $_REQUEST['action'] ) )
                return false;
            $actions_to_protect = array(
                'heartbeat',              // Keep the heart beating.
                'install-theme',          // Installing a new theme.
                'update-theme',           // Update an existing theme.
            );
            $actions_to_protect = (array) $this->_apply_filters( 'tp_protected_async_actions', $actions_to_protect );
            if ( ! in_array( $_REQUEST['action'], $actions_to_protect, true ) )
                return false;
            return true;
        }//1011
        /**
         * @description Set internal encoding.
         */
        protected function _tp_set_internal_encoding():void{
            if ( function_exists( 'mb_internal_encoding' ) ) {
                $charset = $this->_get_option( 'blog_charset' );
                if ( ! $charset || ! @mb_internal_encoding( $charset ) ) {
                    mb_internal_encoding( 'UTF-8' );
                }
            }
        }//1058
        /**
         * @description Add magic quotes to `$_GET`, `$_POST`, `$_COOKIE`, and `$_SERVER`.
         */
        protected function _tp_magic_quotes():void{
            $_GET    = $this->_add_magic_quotes( $_GET );
            $_POST   = $this->_add_magic_quotes( $_POST );
            $_COOKIE = $this->_add_magic_quotes( $_COOKIE );
            $_SERVER = $this->_add_magic_quotes( $_SERVER );
            // Force REQUEST to be GET + POST.
            $_REQUEST = array_merge( $_GET, $_POST );
        }//1077
        /**
         * @description
         */
        protected function _shutdown_action_hook():void{
            $this->_do_action('shutdown');
            $this->_tp_cache_close();
        }//1094
        /**
         * @description Copy an object.
         * @param $object
         * @return mixed
         */
        protected function _tp_clone( $object ){
            return clone( $object );
        }//1114
        /**
         * @description Determines whether the current request is for an administrative interface page.
         * @return bool
         */
        protected function _is_admin():bool{
            $this->tp_current_screen = $this->_init_get_screen();
            if (isset( $this->tp_current_screen )) {
                return $this->tp_current_screen->get_in_admin();
            } elseif ( defined( 'TP_ADMIN' ) )
                return TP_ADMIN;
            return false;
        }//1135
        /**
         * @description Whether the current request is for a site's administrative interface.
         * @return bool
         */
        protected function _is_blog_admin():bool{
            $this->tp_current_screen = $this->_init_get_screen();
            if ( isset( $this->tp_current_screen ) ) {
                return $this->tp_current_screen->get_in_admin('site');
            } elseif ( defined( 'TP_BLOG_ADMIN' ) )
                return TP_BLOG_ADMIN;
            return false;
        }//1159
        /**
         * @description Whether the current request is for the network administrative interface.
         * @return bool
         */
        protected function _is_network_admin():bool{
            $this->tp_current_screen = $this->_init_get_screen();
            if ( isset( $this->tp_current_screen ) ) {
                return $this->tp_current_screen->get_in_admin('network');
            } elseif ( defined( 'TP_NETWORK_ADMIN' ) )
                return TP_NETWORK_ADMIN;
            return false;
        }//1186
    }
}else die;