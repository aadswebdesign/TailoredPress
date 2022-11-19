<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 17:17
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Templates\TP_Default_Footer;
use TP_Core\Templates\TP_Default_Header;
use TP_Core\Forms\TP_Default_Search_Form;
use TP_Core\Templates\TP_Default_Sidebar;
use TP_Core\Templates\TP_Partial_One;
if(ABSPATH){
    trait _general_template_01 {
        /**
         * @description Load header template.
         * @param $args
         * @param array|null $header_args
         * @return null|string
         */
        protected function _get_header( $args, $header_args = null):?string{
            $name = $header_args['name'];
            $theme_name = $header_args['theme_name'];
            $class_name = $header_args['class_name'];
            $output  = $this->_do_action( 'get_header', $name, $args );
            $template = null;
            if ( $name !== null || $theme_name !== null || $class_name !== null ){
                $template = $this->_tp_load_class($name,TP_NS_THEMES. $theme_name .TP_NS_TEMPLATE_PATH, $class_name,$args);
            }else{ $template =  new TP_Default_Header($args);}
            $output .= $template;
            return $output;
        }//27 from general-template
        protected function _print_header($args, $header_args = null):void{
            echo $this->_get_header( $args, $header_args);
        }//27|added
        /**
         * @description Load footer template.
         * @param $args
         * @param array|null $footer_args
         * @return string
         */
        protected function _get_footer($args, $footer_args = null):string{
            $name = $footer_args['name'];
            $theme_name = $footer_args['theme_name'];
            $class_name = $footer_args['class_name'];
            $output  = $this->_do_action( 'get_footer', $name, $args );
            //var_dump('< $name >', $name,'<br><br>');
            //var_dump('< $theme_name >', $theme_name,'<br><br>');
            //var_dump('< $class_name >', $class_name,'<br><br>');
            $template = null;
            if ( $name !== null || $theme_name !== null || $class_name !== null ){
                $template = $this->_tp_load_class($name,TP_NS_THEMES. $theme_name .TP_NS_TEMPLATE_PATH, $class_name,$args);
            }else{ $template =  new TP_Default_Footer($args);}
            $output .= $template;
            return $output;
        }//71 from general-template
        protected function _print_footer($args, $footer_args = null):void{
            echo $this->_get_footer( $args, $footer_args);
        }//71|added
        /**
         * @description Load sidebar template.
         * @param $args
         * @param array|null $sidebar_args
         * @return string
         */
        protected function _get_sidebar($args, $sidebar_args = null):string{
            $name = $sidebar_args['name'];
            $theme_name = $sidebar_args['theme_name'];
            $class_name = $sidebar_args['class_name'];
            $output  = $this->_do_action( 'get_footer', $name, $args );
            $template = null;
            if ( $name !== null || $theme_name !== null || $class_name !== null ){
                $template = $this->_tp_load_class($name,TP_NS_THEMES. $theme_name .TP_NS_TEMPLATE_PATH, $class_name,$args);
            }else{ $template =  new TP_Default_Sidebar($args);}
            $output .= $template;
            return $output;
        }//115
        protected function _print_sidebar($args, $sidebar_args = null):void{
            echo $this->_get_sidebar($args, $sidebar_args);
        }//115|added
        /**
         * @description Loads a template part into a template.
         * @param $args
         * @param array|null $partial_args
         * @return string
         */
        protected function _get_partial($args, $partial_args = null):string{
            $name = $partial_args['name'];
            $theme_name = $partial_args['theme_name'];
            $class_name = $partial_args['class_name'];
            $output  = $this->_do_action( 'get_partial', $name, $args );
            $template = null;
            if ( $name !== null || $theme_name !== null || $class_name !== null ){
                $template = $this->_tp_load_class($name,TP_NS_THEMES. $theme_name .TP_NS_TEMPLATE_PATH, $class_name,$args);
            }else{ $template =  new TP_Partial_One($args);}
            $output .= $template;
            return $output;
        }//167 from general-template todo
        protected function _print_partial($args, $partial_args = null):void{
            echo $this->_get_partial($args, $partial_args);
        }//115|added
        /**
         * @param $args
         * @param array|null $search_args
         * @return null|string
         */
        protected function _get_search_form($args,$search_args = null):?string{
            $name = $search_args['name'];
            $theme_name = $search_args['theme_name'];
            $class_name = $search_args['class_name'];
            $form = $this->_do_action( 'pre_get_search_form', $args );
            $defaults = ['aria_label' => '',];
            $args = $this->_tp_parse_args( $args, $defaults );
            $args = $this->_apply_filters( 'search_form_args', $args );
            $args = array_merge( $defaults, $args );
            if ($args['aria_label'] ) {
                $aria_label = " aria-label='{$this->_esc_attr( $args['aria_label'] )}'";
            } else { $aria_label = '';}
            $args['attr_aria_label'] = $aria_label;
            $template = null;
            if ( $name !== null || $theme_name !== null || $class_name !== null ){
                $template = $this->_tp_load_class($name,TP_NS_THEMES. $theme_name .TP_NS_TEMPLATE_PATH, $class_name,$args);
            }else{ $template =  new TP_Default_Search_Form($args);}
            $form .= $template;
            return $this->_apply_filters( 'get_search_form', $form, $args );
        }
        protected function _print_search_form($args, ...$class_args):void{
            echo $this->_get_search_form($args, $class_args);
        }
        /**
         * @description Display the Log In/Out link.
         * @param string $redirect
         * @param bool $echo
         * @return mixed
         */
        protected function _tp_login_logout( $redirect = '', $echo = true ){
            if ( ! $this->_is_user_logged_in() ) $link ="<a href='{$this->_esc_url($this->_tp_login_url($redirect))}'>{$this->__('Log in')}</a>";
            else $link ="<a href='{$this->_esc_url($this->_tp_logout_url($redirect))}'>{$this->__('Log out')}</a>";
            if ( $echo ){
                echo $this->_apply_filters( 'login_logout', $link );
                return null;
            }
            return $this->_apply_filters( 'login_logout', $link );
        }//376 from general-template
        /**
         * @description Retrieves the logout URL.
         * @param string $redirect
         * @return mixed
         */
        protected function _tp_logout_url( $redirect='' ) {
            $args = [];
            if ( ! empty( $redirect ) )$args['redirect_to'] = urlencode( $redirect );
            $logout_url = $this->_add_query_arg( $args, $this->_site_url( 'tp_login.php?action=logout', 'login' ) );
            $logout_url = $this->_tp_nonce_url( $logout_url, 'log-out' );
            return $this->_apply_filters( 'logout_url', $logout_url, $redirect );
        }//408
    }
}else die;