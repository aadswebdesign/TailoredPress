<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-3-2022
 * Time: 20:23
 */
namespace TP_Core\Traits\Templates;
if(ABSPATH){
    trait _link_template_12 {
        /**
         * @description Retrieves the URL to the privacy policy page.
         * @return mixed
         */
        protected function _get_privacy_policy_url(){
            $url            = '';
            $policy_page_id = (int) $this->_get_option( 'tp_page_for_privacy_policy' );
            if ( ! empty( $policy_page_id ) && $this->_get_post_status( $policy_page_id ) === 'publish' )
                $url = (string) $this->_get_permalink( $policy_page_id );
            return $this->_apply_filters( 'privacy_policy_url', $url, $policy_page_id );
        }//4542 from link-template
        /**
         * @description Displays the privacy policy link with formatting, when applicable.
         * @param string $before
         * @param string $after
         */
        protected function _the_privacy_policy_link( $before = '', $after = '' ):void{
            echo $this->_get_the_privacy_policy_link( $before, $after );
        }//4570 from link-template
        /**
         * @description Returns the privacy policy link with formatting, when applicable.
         * @param string $before
         * @param string $after
         * @return string
         */
        protected function _get_the_privacy_policy_link( $before = '', $after = '' ):string{
            $link               = '';
            $privacy_policy_url = $this->_get_privacy_policy_url();
            $policy_page_id     = (int) $this->_get_option( 'tp_page_for_privacy_policy' );
            $page_title         = ( $policy_page_id ) ? $this->_get_the_title( $policy_page_id ) : '';
            if ( $privacy_policy_url && $page_title )
                $link = sprintf("<a class='privacy-policy-link' href='%s'>%s</a>",$this->_esc_url( $privacy_policy_url ),$this->_esc_html( $page_title ));
            $link = $this->_apply_filters( 'the_privacy_policy_link', $link, $privacy_policy_url );
            if ( $link ) return $before . $link . $after;
            return '';
        }//4584 from link-template
    }
}else die;