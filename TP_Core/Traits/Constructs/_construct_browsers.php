<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-8-2022
 * Time: 23:48
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_browsers{
        public $tp_is_lynx;
        public $tp_is_gecko;
        public $tp_is_opera;
        public $tp_is_safari;
        public $tp_is_chrome;
        public $tp_is_iphone;
        public $tp_is_edge;
        public $tp_is_apache;
        public $tp_is_IIS;
        public $tp_is_iis7;
        public $tp_is_nginx;
        public $tp_is_winIE;
        //public $tp_;
        protected function _construct_browsers():void{
            if($this->_is_admin()){
                if ( $this->_is_network_admin() ) { //todo underscore?
                    preg_match( '#/tp_admin/network/?(.*?)$#i', $_SERVER['PHP_SELF'], $self_matches );
                } elseif ( $this->_is_user_admin() ) {
                    preg_match( '#/tp_admin/user/?(.*?)$#i', $_SERVER['PHP_SELF'], $self_matches );
                } else {preg_match( '#/tp_admin/?(.*?)$#i', $_SERVER['PHP_SELF'], $self_matches );}
                $this->tp_pagenow = ! empty( $self_matches[1] ) ? $self_matches[1] : '';
                $this->tp_pagenow = trim( $this->tp_pagenow, '/' );
                $this->tp_pagenow = preg_replace( '#\?.*?$#', '', $this->tp_pagenow );
                if ( '' === $this->tp_pagenow || 'index' === $this->tp_pagenow || 'index.php' === $this->tp_pagenow ) {
                    $this->tp_pagenow = 'index.php';
                } else {
                    preg_match( '#(.*?)(/|$)#', $this->tp_pagenow, $self_matches );
                    $this->tp_pagenow = strtolower( $self_matches[1] );
                    if ( '.php' !== substr( $this->tp_pagenow,-4,4)){$this->tp_pagenow .= '.php';}// For `Options +Multiviews`: /wp-admin/themes/index.php (themes.php is queried).
                }
            }else if ( preg_match( '#([^/]+\.php)([?/].*?)?$#i', $_SERVER['PHP_SELF'], $self_matches ) ) {
                $this->tp_pagenow = strtolower( $self_matches[1] );
            } else {$this->tp_pagenow = 'index.php';}
            unset( $self_matches );
            $this->tp_is_lynx   = false;
            $this->tp_is_gecko  = false;
            $this->tp_is_opera  = false;
            $this->tp_is_safari = false;
            $this->tp_is_chrome = false;
            $this->tp_is_iphone = false;
            $this->tp_is_edge   = false;
            $this->tp_is_winIE  = false;
            if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
                if ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Lynx' ) !== false ) {
                    $this->tp_is_lynx = true;
                }elseif ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Edg' ) !== false ) {//or Edge?
                    $this->tp_is_edge = true;
                }elseif ( stripos( $_SERVER['HTTP_USER_AGENT'], 'chrome' ) !== false ) {
                    if ( stripos( $_SERVER['HTTP_USER_AGENT'], 'chromeframe' ) !== false ) {
                        $is_admin = $this->_is_admin();
                        $this->tp_is_chrome = $this->_apply_filters( 'use_google_chrome_frame', $is_admin );
                        if ( $this->tp_is_chrome ) {
                            header( 'X-UA-Compatible: chrome=1' );
                        }
                    }else{$this->tp_is_chrome = true;}
                }elseif ( stripos( $_SERVER['HTTP_USER_AGENT'], 'safari' ) !== false ) {
                    $this->tp_is_safari = true;
                } elseif ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Gecko' ) !== false ) {
                    $this->tp_is_gecko = true;
                } elseif ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Opera' ) !== false ) {
                    $this->tp_is_opera = true;
                } //elseif ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Nav' ) !== false && strpos( $_SERVER['HTTP_USER_AGENT'], 'Mozilla/4.' ) !== false ) {
                    //$this->tp_is_NS4 = true;
                //}
            }
            if ( $this->tp_is_safari && stripos( $_SERVER['HTTP_USER_AGENT'], 'mobile' ) !== false ) {
                $this->tp_is_iphone = true;
            }
            $this->tp_is_apache = ( strpos( $_SERVER['SERVER_SOFTWARE'], 'Apache' ) !== false || strpos( $_SERVER['SERVER_SOFTWARE'], 'LiteSpeed' ) !== false );
            $this->tp_is_nginx = ( strpos( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) !== false );
            $this->tp_is_IIS = ! $this->tp_is_apache && ( strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS' ) !== false || strpos( $_SERVER['SERVER_SOFTWARE'], 'ExpressionDevServer' ) !== false );
            $this->tp_is_iis7 = $this->tp_is_IIS && (int) substr( $_SERVER['SERVER_SOFTWARE'], strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS/' ) + 14 ) >= 7;
        }//end method
    }
}else{die;}