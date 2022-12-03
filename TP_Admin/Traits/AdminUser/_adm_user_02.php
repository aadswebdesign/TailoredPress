<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-6-2022
 * Time: 06:47
 */
namespace TP_Admin\Traits\AdminUser;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _adm_user_02{
        protected function _get_delete_users_add_js():string{
            ob_start();
            ?>
            <script id="get_delete_users_add_js">console.log('get_delete_users_add_js','todo: no jQuery here')</script>
            <?php
            return ob_get_clean();
        }//537
        protected function _delete_users_add_js():void{
            echo $this->_get_delete_users_add_js();
        }//537
        /**
         * @description Optional SSL preference that can be turned on by hooking to the 'personal_options' action.
         * @param $user
         * @return string
         */
        protected function _get_use_ssl_preference( $user ):string{
            $output  = "<ul class='user-use-ssl-wrap'><li><p>{$this->__('Use https')}</p></li><li>";
            $output .= "<dt><label for='use_ssl'>{$this->__('Always use https when visiting the admin!')}</label></dt>";
            $output .= "<dd><input name='use_ssl' id='use_ssl' type='checkbox' value='1' {$this->_get_checked( '1', $user->use_ssl )}/></dd>";
            $output .= "</li></ul>";
            return $output;
        }//562
        protected function _use_ssl_preference( $user ):void{}//562
        protected function _admin_created_user_email( $text ):string{
            $roles = $this->_get_editable_roles();
            $role  = $roles[ $_REQUEST['role'] ];
            $output  = 'Hi,You\'ve been invited to join \'%1$s\' at % 2$s with the role of % 3$s .';
            $output .= 'If you do not want to join this site please ignore this email. This invitation will expire in a few days.';
            $output .= 'Please click the following link to activate your user account:%%s';
            $mail_custom_text = $this->_esc_html($text ?? null);

            return sprintf($this->__($output).$mail_custom_text,$this->_tp_special_chars_decode( $this->_get_bloginfo( 'name' ), ENT_QUOTES ),
                $this->_home_url(),$this->_tp_special_chars_decode( $this->_translate_user_role( $role['name'] ) ));
        }//577
        //@description Checks if the Authorize Application Password request is valid.
        protected function _tp_is_authorize_application_password_request_valid( $request, $user ):bool{
            $error = new TP_Error();
            if ( ! empty( $request['success_url'] ) ) {
                $scheme = $this->_tp_parse_url( $request['success_url'], PHP_URL_SCHEME );
                if ( 'http' === $scheme ) {
                    $error->add('invalid_redirect_scheme',$this->__( 'The success URL must be served over a secure connection.' ));
                }
            }
            if ( ! empty( $request['reject_url'] ) ) {
                $scheme = $this->_tp_parse_url( $request['reject_url'], PHP_URL_SCHEME );
                if ( 'http' === $scheme ) {
                    $error->add( 'invalid_redirect_scheme', $this->__( 'The rejection URL must be served over a secure connection.' ));
                }
            }
            if ( ! empty( $request['app_id'] ) && ! $this->_tp_is_uuid( $request['app_id'] ) ) {
                $error->add('invalid_app_id',$this->__( 'The application ID must be a UUID.' ));
            }
            $this->_do_action( 'tp_authorize_application_password_request_errors', $error, $request, $user );
            if ( $error->has_errors() ) { return (bool)$error;}
            return true;
        }//621
    }
}else die;