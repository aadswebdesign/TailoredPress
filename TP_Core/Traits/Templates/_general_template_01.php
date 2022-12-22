<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 17:17
 */
namespace TP_Core\Traits\Templates;

if(ABSPATH){
    trait _general_template_01 {
        /**
         * @description Load header template.
         * @param null|array $args
         * @param null|array $header_args
         * @return null|string
         */
        protected function _get_header($header_args = null, $args = null):?string{ //TP_NS_THEME_TEMPLATE TP_NS_THEMES
            $name = $header_args['name'] ?? null;
            $theme_name = $header_args['theme_name'] ?? null;
            $class_name = $header_args['class_name'] ?? null;
            $output  = "";
            $template = null;
            if(null !== $theme_name && null === $class_name){
                $template = $this->_tp_load_class('theme_header',TP_NS_THEMES.$theme_name.TP_NS_THEME_COMPONENTS, 'Header',$args);
            }elseif( null !== $class_name ){
                $template = $this->_tp_load_class('theme_header',TP_NS_THEMES.$theme_name.TP_NS_THEME_COMPONENTS, $class_name . '_Header',$args);
            }
            $output .= $this->_get_action( 'get_header', $name, $args );
            $output .= $template;
            return $output;
        }//27 from general-template
        /**
         * @description Load footer template.
         * @param $args
         * @param array|null $footer_args
         * @return string
         */
        protected function _get_footer($footer_args = null, $args = null):string{
            $name = $footer_args['name'] ?? null;
            $theme_name = $footer_args['theme_name'] ?? null;
            $class_name = $footer_args['class_name'] ?? null;
            $output  = "";
            $template = null;
            if(null !== $theme_name && null === $class_name){
                $template = $this->_tp_load_class('theme_footer',TP_NS_THEMES.$theme_name.TP_NS_THEME_COMPONENTS, 'Footer',$args);
            }elseif( null !== $class_name ){
                $template = $this->_tp_load_class('theme_footer',TP_NS_THEMES.$theme_name.TP_NS_THEME_COMPONENTS, $class_name . '_Footer',$args);
            }
            $output .= $this->_get_action( 'get_footer', $name, $args );
            $output .= $template;
            return $output;
        }//71 from general-template
        /**
         * @description Load sidebar template.
         * @param $args
         * @param array|null $sidebar_args
         * @return string
         */
        protected function _get_sidebar($sidebar_args = null, $args = null):string{
            $name = $sidebar_args['name'] ?? null;
            $theme_name = $sidebar_args['theme_name'] ?? null;
            $class_name = $sidebar_args['class_name'] ?? null;
            $output  = "";
            $template = null;
            if(null !== $theme_name && null === $class_name){
                $template = $this->_tp_load_class('theme_sidebar',TP_NS_THEMES.$theme_name.TP_NS_THEME_COMPONENTS, 'Sidebar',$args);
            }elseif( null !== $class_name ){
                $template = $this->_tp_load_class('theme_sidebar',TP_NS_THEMES.$theme_name.TP_NS_THEME_COMPONENTS, $class_name . '_Sidebar',$args);
            }
            $output .= $this->_get_action( 'get_sidebar', $name, $args );
            $output .= $template;
            return $output;
        }//115
        /**
         * @description Loads a template part into a template.
         * @param $args
         * @param array|null $partial_args
         * @return string
         */
        protected function _get_partial($partial_args = null, $args = null):string{
            $name = $partial_args['name'] ?? null;
            $theme_name = $partial_args['theme_name'] ?? null;
            $class_name = $partial_args['class_name'] ?? null;
            $output  = "";
            $template = null;
            if(null !== $theme_name && null === $class_name){
                $template = $this->_tp_load_class('theme_partial',TP_NS_THEMES.$theme_name.TP_NS_THEME_COMPONENTS, 'Partial',$args);
            }elseif( null !== $class_name ){
                $template = $this->_tp_load_class('theme_partial',TP_NS_THEMES.$theme_name.TP_NS_THEME_COMPONENTS, $class_name . '_Partial',$args);
            }
            $output .= $this->_get_action( 'get_partial', $name, $args );
            $output .= $template;
            return $output;
        }//167 from general-template todo
        /**
         * @param $args
         * @param array|null $search_args
         * @return null|string
         */
        protected function _get_search_form($search_args = null, $args = null):?string{
            //$name = $search_args['name'] ?? null;
            $theme_name = $search_args['theme_name'] ?? null;
            $class_name = $search_args['class_name'] ?? null;
            $output = "";
            $defaults = ['aria_label' => '',];
            $args = $this->_tp_parse_args( $args, $defaults );
            $args = $this->_apply_filters( 'search_form_args', $args );
            $args = array_merge( $defaults, $args );
            if ($args['aria_label'] ) {
                $aria_label = " aria-label='{$this->_esc_attr( $args['aria_label'] )}'";
            } else { $aria_label = '';}
            $args['attr_aria_label'] = $aria_label;
            $output .= $this->_get_action( 'pre_get_search_form', $args );
            $template = null;
            if(null !== $theme_name && null === $class_name){
                $template = $this->_tp_load_class('theme_search',TP_NS_THEMES.$theme_name.TP_NS_THEME_COMPONENTS, 'SearchForm',$args);
            }
            $output .= $template;
            return $this->_apply_filters( 'get_search_form', $output, $args );
        }
        /**
         * @description Display the Log In/Out link.
         * @param string $redirect
         * @return mixed
         */
        protected function _tp_get_login_logout( $redirect = ''){
            if ( ! $this->_is_user_logged_in() ) $link ="<a href='{$this->_esc_url($this->_tp_login_url($redirect))}'>{$this->__('Log in')}</a>";
            else $link ="<a href='{$this->_esc_url($this->_tp_logout_url($redirect))}'>{$this->__('Log out')}</a>";
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