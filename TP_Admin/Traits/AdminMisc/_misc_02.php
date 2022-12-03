<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-8-2022
 * Time: 22:27
 */
namespace TP_Admin\Traits\AdminMisc;
use TP_Admin\Libs\Adm_Privacy_Policy_Content;
if(ABSPATH){
    trait _misc_02{
        /**
         * @description Remove single-use URL parameters and create canonical link based on new URL.
         * @return \Closure|void
         */
        protected function _tp_get_admin_canonical_url(){
            $removable_query_args = $this->_tp_removable_query_args();
            if ( empty( $removable_query_args)){return;}
            $current_url  = $this->_set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
            $filtered_url = $this->_remove_query_arg( $removable_query_args, $current_url );
            $_canonical_string = static function()use($filtered_url){
                $html = "<link id='tp_admin_canonical' rel='canonical' href='{(new self)->_esc_url( $filtered_url )}' />";
                ob_start();
                ?>
                <script>
                    if ( window.history.replaceState ) {
                        window.history.replaceState( null, document.getElementById( 'tp_admin_canonical' ).href + window.location.hash );
                    }
                </script>
                <?php
                $html .= ob_get_clean();
                return $html;
            };
            return $_canonical_string;
        }//1270
        protected function _tp_admin_canonical_url():void{
            echo $this->_tp_get_admin_canonical_url();
        }//1270
        /**
         * @description Send a referrer policy header so referrers are
         *  not sent externally from administration screens.
         */
        protected function _tp_admin_headers():void{
            $policy = 'strict-origin-when-cross-origin';
            $policy = $this->_apply_filters( 'admin_referrer_policy', $policy );
            header( sprintf( 'Referrer-Policy: %s', $policy ) );
        }//1295
        /**
         * @description Outputs JS that reloads the page if the user navigated to it,
         * with the Back or Forward button.
         * @return string
         */
        protected function _tp_get_page_reload_on_back_button_js():string{
            ob_start();
            ?>
            <script>
                //noinspection JSUnresolvedVariable
                if ( typeof performance !== 'undefined' && performance.navigation && performance.navigation.type === 2 ) {
                    document.location.reload( true );
                }
            </script>
            <?php
            return ob_get_clean();
        }//1321
        protected function _tp_page_reload_on_back_button_js():void{
            echo $this->_tp_get_page_reload_on_back_button_js();
        }//1321
        /**
         * @description Send a confirmation request email when a change,
         *  of site admin email address is attempted.
         * @param $old_value
         * @param $value
         */
        protected function _update_option_new_admin_email( $old_value, $value ):void{
            if ( $this->_get_option( 'admin_email' ) === $value || ! $this->_is_email( $old_value)){return;}
            $hash            = md5( $value . time() . $this->_tp_rand() );
            $new_admin_email = ['hash' => $hash,'new_email' => $value,];
            $this->_update_option( 'admin_hash', $new_admin_email );
            $switched_locale = $this->_switch_to_locale( $this->_get_user_locale() );
            $email_function = static function(){
                $email_text = (new self)->__('Howdy ###USERNAME###,');
                $email_text .= (new self)->__('You recently requested to have the administration email address on your site changed.');
                $email_text .= (new self)->__('If this is correct, please click on the following link to change it: ###ADMIN_URL###');
                $email_text .= (new self)->__('You can safely ignore and delete this email if you do not want to take this action.');
                $email_text .= (new self)->__('This email has been sent to ###EMAIL###');
                $email_text .= (new self)->__('Regards, All at ###SITENAME### ###SITEURL###');
                echo $email_text;
            };
            $content = $this->_apply_filters( 'new_admin_email_content', $email_function, $new_admin_email );
            $current_user = $this->_tp_get_current_user();
            if($current_user !== null)
            $content      .= str_replace( '###USERNAME###', $current_user->user_login, $content );
            $content      .= str_replace( '###ADMIN_URL###', $this->_esc_url( $this->_self_admin_url( 'options.php?admin_hash=' . $hash ) ), $content );//todo
            $content      .= str_replace( '###EMAIL###', $value, $content );
            $content      .= str_replace( '###SITENAME###', $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES ), $content );
            $content      .= str_replace( '###SITEURL###', $this->_home_url(), $content );
            $this->_tp_mail($value,sprintf($this->__( '[%s] New Admin Email Address' ),
                    $this->_tp_special_chars_decode( $this->_get_option( 'blogname' ), ENT_QUOTES )
                ),$content);/* translators: New admin email address notification email subject. %s: Site title. */
            if ( $switched_locale ) {$this->_restore_previous_locale();}
        }//1342
        /**
         * @description Appends '(Draft)' to draft page titles in the privacy page dropdown,
         * so that unpublished content is obvious.
         * @param $title
         * @param $page
         * @return string
         */
        protected function _tp_privacy_settings_filter_draft_page_titles( $title, $page ):string{
            if ( 'draft' === $page->post_status && 'privacy' === $this->_get_current_screen()->id ) {
                $title = sprintf( $this->__( '%s (Draft)' ), $title ); /* translators: %s: Page title. */
            }
            return $title;
        }//1432
        /**
         * @description Checks if the user needs to update PHP.
         * @return bool
         */
        protected function _tp_check_php_version():bool{
            $version = PHP_VERSION;
            $key     = md5( $version );
            $response =  $this->_get_site_transient( 'php_check_' . $key );
            if ( false === $response ) {
                $url = 'http://api.wordpress.org/core/serve-happy/1.0/';
                if ( $this->_tp_http_supports( array( 'ssl' ) ) ) {
                    $url = $this->_set_url_scheme( $url, 'https' );
                }
                $url = $this->_add_query_arg( 'php_version', $version, $url );
                $response = $this->_tp_remote_get( $url );
                if ( $this->_init_error( $response ) || 200 !== $this->_tp_remote_retrieve_response_code( $response ) ) {
                    return false;
                }
            }
            if ( isset( $response['is_acceptable'] ) && $response['is_acceptable'] ) {
                $response['is_acceptable'] = (bool) $this->_apply_filters( 'tp_is_php_version_acceptable', true, $version );
            }
            return $response;
        }//1449
        /**
         * @description Declares a helper function for adding content to the Privacy Policy Guide.
         * @param $module_name
         * @param $policy_text
         */
        protected function _tp_add_privacy_policy_content( $module_name, $policy_text ):void{
            if ( ! $this->_is_admin() ) { return;}
            if ( ! $this->_doing_action( 'admin_init' ) && ! $this->_did_action( 'admin_init' ) ) { return;}
            Adm_Privacy_Policy_Content::add( $module_name, $policy_text );
        }//2317 admin/from plugins.php
    }
}else{die;}

