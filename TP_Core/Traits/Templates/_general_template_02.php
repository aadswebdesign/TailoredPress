<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 17:17
 */
namespace TP_Core\Traits\Templates;
if(ABSPATH){
    trait _general_template_02 {
        /**
         * @description Retrieves the login URL.
         * @param string $redirect
         * @param bool $force_re_auth
         * @return mixed
         */
        protected function _tp_login_url( $redirect='',$force_re_auth = false ){
            $login_url = $this->_site_url( 'tp_login.php', 'login' );
            if ( ! empty( $redirect ) )
                $login_url = $this->_add_query_arg( 'redirect_to', urlencode( $redirect ), $login_url );
            if ( $force_re_auth )
                $login_url = $this->_add_query_arg( 're_auth', '1', $login_url );
            return $this->_apply_filters( 'login_url', $login_url, $redirect, $force_re_auth );
        }//438
        /**
         * @description Returns the URL that allows the user to register on the site.
         * @return mixed
         */
        protected function _tp_registration_url(){
            return $this->_apply_filters( 'register_url', $this->_site_url( '', 'login' ) );  //todo
        }//469 from general-template
        /**
         * @description Provides a simple login form for use anywhere within TailoredPress.
         * @param array $args
         * @return string
         */
        protected function _tp_get_login_form( ...$args):string{
            $defaults = ['redirect' => ( $this->_is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
                'form_id' => 'login_form','label_username' => $this->__( 'Username or Email Address' ),'label_password' => $this->__( 'Password' ),
                'label_remember' => $this->__( 'Remember Me' ),'label_log_in' => $this->__( 'Log In' ),'id_username' => 'user_login','id_password' => 'user_pass',
                'id_remember' => 'remember_me','id_submit' => 'tp-submit','remember' => true,'value_username' => '','value_remember' => false,];
            $args = $this->_tp_parse_args( $args, $this->_apply_filters( 'login_form_defaults', $defaults ) );
            $login_form_top = $this->_apply_filters( 'login_form_top', '', $args );
            $login_form_middle = $this->_apply_filters( 'login_form_middle', '', $args );
            $login_form_bottom = $this->_apply_filters( 'login_form_bottom', '', $args );
            $form = $login_form_top;
            $form .= $login_form_middle;
            $form .= $login_form_bottom;
            if ( ! $form) { return false;}
            return $form;
        }//511 from general-template
        protected function _tp_login_form( $args = [] ):void{
            echo $this->_tp_get_login_form( ...$args);
        }
        /**
         * @description Returns the URL that allows the user to retrieve the lost password
         * @param string $redirect
         * @return string
         */
        protected function _tp_lost_password_url( $redirect = '' ):string{
            $args = ['action' => 'lost_password',];
            if ( ! empty( $redirect ) )
                $args['redirect_to'] = urlencode( $redirect );
            if ($this->_is_multisite() ){
                $blog_details  = $this->_get_blog_details();
                $tp_login_path = $blog_details->path . 'tp_login.php';
            } else
                $tp_login_path = 'tp_login.php';
            $lost_password_url = $this->_add_query_arg( $args, $this->_network_site_url( $tp_login_path, 'login' ) );
            return $this->_apply_filters( 'lost_password_url', $lost_password_url, $redirect );
        }//639 from general-template
        /**
         * @description Display the Registration or Admin link.
         * @param string $before
         * @param string $after
         * @return null|string
         */
        protected function _tp_get_register( $before = '<li>', $after = '</li>'):string{
            if ( ! $this->_is_user_logged_in() ) {
                if ( $this->_get_option( 'users_can_register' ) )
                    $link = "{$before}<a href='{$this->_esc_url( $this->_tp_registration_url() )}'>{$this->__( 'Register' )}</a>{$after}";
                else $link = '';
            } elseif ( $this->_current_user_can( 'read' ) )
                $link = "{$before}<a href='{$this->_admin_url()}'>{$this->__( 'Site Admin' )}</a>{$after}";
            else $link = '';
            $link = $this->_apply_filters( 'register', $link );
            if ( !$link ) { return false;}
            return $link;
        }//682 from general-template
        protected function _tp_register( $before = '<li>', $after = '</li>'):void{
            echo $this->_tp_get_register( $before, $after);
        }
        /**
         * @description Theme container function for the 'tp_meta' action.
         * @return string
         */
        protected function _tp_get_meta():string{
            return $this->_do_action( 'tp_meta' );
        }//724 from general-template
        protected function _tp_meta():void{
            $this->_tp_get_meta();
        }
        /**
         * @description Displays information about the current site.
         * @param string $show
         */
        protected function _bloginfo( $show = '' ):void {
            echo $this->_get_bloginfo( $show, 'display' );
        }//742 from general-template
        /**
         * @description Retrieves information about the current site.
         * @param string $show
         * @param string $filter
         * @return string
         */
        protected function _get_bloginfo( $show = '', $filter = 'raw' ):string{
            $output_bg = '';
            switch ( $show ) {
                case 'url':
                    $output_bg .= $this->_home_url();
                    break;
                case 'tp_url':
                    $output_bg .= $this->_site_url();
                    break;
                case 'description':
                    $output_bg .= $this->_get_option( 'blogdescription' );
                    break;
                case 'rdf_url':
                    $output_bg .= $this->_get_feed_link( 'rdf' );
                    break;
                case 'rss_url':
                    $output_bg .= $this->_get_feed_link( 'rss' );
                    break;
                case 'rss2_url':
                    $output_bg .= $this->_get_feed_link( 'rss2' );
                    break;
                case 'atom_url':
                    $output_bg .= $this->_get_feed_link( 'atom' );
                    break;
                case 'comments_atom_url':
                    $output_bg .= $this->_get_feed_link( 'comments_atom' );
                    break;
                case 'comments_rss2_url':
                    $output_bg .= $this->_get_feed_link( 'comments_rss2' );
                    break;
                case 'pingback_url':
                    $output_bg .= $this->_site_url( 'xmlrpc.php' );
                    break;
                case 'stylesheet_url':
                    $output_bg .= $this->_get_stylesheet_uri(); //242 theme.php
                    break;
                case 'stylesheet_directory':
                    $output_bg .= $this->_get_stylesheet_directory_uri();  //215 theme.php
                    break;
                case 'template_directory':
                case 'template_url':
                $output_bg .= $this->_get_template_directory_uri(); //349 theme.php
                    break;
                case 'admin_email':
                    $output_bg .= $this->_get_option( 'admin_email' );
                    break;
                case 'charset':
                    $output_bg .= $this->_get_option( 'blog_charset' );
                    if ( '' === $output_bg ) $output_bg .= 'UTF-8';
                    break;
                case 'html_type':
                    $output_bg .= $this->_get_option( 'html_type' );
                    break;
                case 'version':
                    $output_bg .= $this->tp_version;
                    break;
                case 'language':
                    $output_bg .= $this->__( 'html_lang_attribute' );
                    if ( 'html_lang_attribute' === $output_bg || preg_match( '/[^a-zA-Z0-9-]/', $output_bg ) ) {
                        $output_bg .= $this->_determine_locale();
                        $output_bg .= str_replace( '_', '-', $output_bg );
                    }
                    break;
                case 'name':
                default:
                $output_bg .= $this->_get_option( 'blogname' );
                    break;
            }
            $url = true;
            if ( strpos( $show, 'url' ) === false &&
                strpos( $show, 'directory' ) === false &&
                strpos( $show, 'home' ) === false ) {
                $url = false;
            }
            $output  = "";
            if ( 'display' === $filter ){
                if ( $url ) $output .= $this->_apply_filters( 'bloginfo_url', $output_bg, $show );
                else $output .= $this->_apply_filters( 'bloginfo', $output_bg, $show );
            }
            return $output;

        }//793 from general-template
        /**
         * @description Returns the Site Icon URL.
         * @param int $size
         * @param string $url
         * @param int $blog_id
         * @return mixed
         */
        protected function _get_site_icon_url( $size = 512, $url = '', $blog_id = 0 ){
            $switched_blog = false;
            if ( ! empty( $blog_id ) && $this->_is_multisite() && $this->_get_current_blog_id() !== (int) $blog_id ) {
                $this->_switch_to_blog( $blog_id );
                $switched_blog = true;
            }
            $site_icon_id = $this->_get_option( 'site_icon' );
            if ( $site_icon_id ) {
                if ( $size >= 512 ) $size_data = 'full';
                else $size_data = array( $size, $size );
                $url = (string)$this->_tp_get_attachment_image_url( $site_icon_id, $size_data );
            }
            if ( $switched_blog ) $this->_restore_current_blog();
            return $this->_apply_filters( 'get_site_icon_url', $url, $size, $blog_id );
        }//945 from general-template
        /**
         * @description Displays the Site Icon URL.
         * @param int $size
         * @param string $url
         * @param int $blog_id
         */
        public function site_icon_url( $size = 512, $url = '', $blog_id = 0 ):void{
            echo $this->_esc_url( $this->_get_site_icon_url( $size, $url, $blog_id ) );
        }//989 from general-template
    }
}else die;