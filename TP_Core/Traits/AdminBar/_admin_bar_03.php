<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-6-2022
 * Time: 06:46
 */
namespace TP_Core\Traits\AdminBar;
if(ABSPATH){
    trait _admin_bar_03{
        /**
         * @description Prints default admin bar callback.
         * @return string
         */
        protected function _get_admin_bar_bump_cb():string{
            ob_start();
            ?>
            <style media="screen">
                html{ margin-top: 32px; }
                @media screen and( max-width:782px ) {
                    html{margin-top:46px;}
                }
            </style>
            <?php
            return ob_get_clean();
        }//1210
        protected function _admin_bar_bump_cb():void{
            echo $this->_get_admin_bar_bump_cb();
        }//1210
        /**
         * @description Sets the display status of the admin bar.
         * @param $show
         */
        protected function _show_admin_bar( $show ):void{
            $this->tp_show_admin_bar = (bool) $show;
        }//1234
        /**
         * @description Determines whether the admin bar should be showing.
         * @return bool
         */
        protected function _is_admin_bar_showing():bool{
            if(defined( 'XMLRPC_REQUEST' ) || defined( 'DOING_ASYNC' ) || defined( 'IFRAME_REQUEST' ) || $this->_tp_is_json_request() ) {
                return false;}
            if($this->_is_embed()){ return false;}
            if ( $this->_is_admin()){ return true;}
            if ( ! isset( $this->tp_show_admin_bar ) ) {
                if ('tp_login.php' === $this->tp_pagenow || ! $this->_is_user_logged_in()) {
                    $this->tp_show_admin_bar = false;
                } else { $this->tp_show_admin_bar = $this->_get_admin_bar_pref();}
            }
            $this->tp_show_admin_bar = $this->_apply_filters( 'show_admin_bar', $this->tp_show_admin_bar );
            return $this->tp_show_admin_bar;
        }//1253
        /**
         * @description Retrieves the admin bar display preference of a user.
         * @param string $context
         * @param int $user
         * @return bool
         */
        protected function _get_admin_bar_pref( $context = 'front', $user = 0 ):bool{
            $pref = $this->_get_user_option( "show_admin_bar_{$context}", $user );
            if(false === $pref ){ return true;}
            return 'true' === $pref;
        }//1304
    }
}else die;