<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 23:09
 */
namespace TP_Core\Traits\K_Ses;
if(ABSPATH){
    trait _k_ses_01 {
        /**
         * @description Filters text content and strips out disallowed HTML.
         * @param $string
         * @param $allowed_html
         * @param array $allowed_protocols
         * @return mixed
         */
        protected function _tp_kses( $string, $allowed_html, $allowed_protocols = [] ){
            if ( empty( $allowed_protocols ) )
                $allowed_protocols = $this->_tp_allowed_protocols();
            $string = $this->_tp_kses_no_null( $string, array( 'slash_zero' => 'keep' ) );
            $string = $this->_tp_kses_normalize_entities( $string );
            $string = $this->_tp_kses_hook( $string, $allowed_html, $allowed_protocols );
            return $this->_tp_kses_split( $string, $allowed_html, $allowed_protocols );
        }//762
        /**
         * @description Filters one HTML attribute and ensures its value is allowed.
         * @param $string
         * @param $element
         * @return string
         */
        protected function _tp_kses_one_attr( $string, $element ):string{
            $uris              = $this->_tp_kses_uri_attributes();
            $allowed_html      = $this->_tp_kses_allowed_html( 'post' );
            $allowed_protocols = $this->_tp_allowed_protocols();
            $string            = $this->_tp_kses_no_null( $string, array( 'slash_zero' => 'keep' ) );
            $matches = array();
            preg_match( '/^\s*/', $string, $matches );
            $lead = $matches[0];
            preg_match( '/\s*$/', $string, $matches );
            $trail = $matches[0];
            if ( empty( $trail ) ) $string = substr( $string, strlen( $lead ) );
            else  $string = substr( $string, strlen( $lead ), -strlen( $trail ) );
            $split = preg_split( '/\s*=\s*/', $string, 2 );
            $name  = $split[0];
            if ( count( $split ) === 2 ) {
                $value = $split[1];
                if ( '' === $value ) $quote = '';
                else $quote = $value[0];
                if ( '"' === $quote || "'" === $quote ) {
                    if ( substr( $value, -1 ) !== $quote ) return '';
                    $value = substr( $value, 1, -1 );
                } else $quote = '"';
                $value = $this->_esc_attr( $value );
                if ( in_array( strtolower( $name ), $uris, true ) )
                    $value = $this->_tp_kses_bad_protocol( $value, $allowed_protocols );
                $string = "$name=$quote$value$quote";
                $vless  = 'n';
            } else {
                $value = '';
                $vless = 'y';
            }
            $this->_tp_kses_attr_check( $name, $value, $string, $vless, $element, $allowed_html );
            // Restore whitespace.
            return $lead . $string . $trail;
        }//785

        /**
         * @description Returns an array of allowed HTML tags and attributes for a given context.
         * @param \mixed ...$context
         * @return array
         */
        protected function _tp_kses_allowed_html(mixed ...$context):?array{
            if ( is_array( $context ) ) {
                $html    = $context;
                $context = 'explicit';
                return $this->_apply_filters( 'tp_kses_allowed_html', $html, $context );
            }
            switch ( $context ) {
                case 'post':
                    $tags = $this->_apply_filters( 'tp_kses_allowed_html', $this->allowed_post_tags, $context );
                    if ( ! KSES_CUSTOM_TAGS && ! isset( $tags['form'] ) && ( isset( $tags['input'] ) || isset( $tags['select'] ) ) ) {
                        $tags = $this->allowed_post_tags;
                        $tags['form'] = [
                            'action' => true,'accept' => true,'accept-charset' => true,'enctype' => true,
                            'method' => true,'name' => true,'target' => true,
                        ];
                        $tags = $this->_apply_filters( 'tp_kses_allowed_html', $tags, $context );
                    }
                    return $tags;
                case 'user_description':
                case 'pre_user_description':
                    $tags             = $this->allowed_tags;
                    $tags['a']['rel'] = true;
                    return $this->_apply_filters( 'tp_kses_allowed_html', $tags, $context );
                case 'strip':
                    return $this->_apply_filters( 'tp_kses_allowed_html', array(), $context );
                case 'entities':
                    return $this->_apply_filters( 'tp_kses_allowed_html', $this->allowed_entity_names, $context );
                case 'data':
                default:
                    return $this->_apply_filters( 'tp_kses_allowed_html', $this->allowed_tags, $context );
            }
        }//862
        /**
         * @description You add any KSES hooks here.
         * @param $string
         * @param $allowed_html
         * @param $allowed_protocols
         * @return mixed
         */
        protected function _tp_kses_hook( $string, $allowed_html, $allowed_protocols ){
            return $this->_apply_filters( 'pre_kses', $string, $allowed_html, $allowed_protocols );
        }//943
        /**
         * @description Returns the version number of KSES.
         * @return string
         */
        protected function _tp_kses_version():string{
            return '0.0.1';
        }//965
        /**
         * @description Searches for HTML tags, no matter how malformed.
         * @param $string
         * @param $allowed_html
         * @param $allowed_protocols
         * @return mixed
         */
        protected function _tp_kses_split( $string, $allowed_html, $allowed_protocols ){
            $this->pass_allowed_html      = $allowed_html;
            $this->pass_allowed_protocols = $allowed_protocols;
            return preg_replace_callback( '%(<!--.*?(-->|$))|(<[^>]*(>|$)|>)%', [$this,'_tp_kses_split_callback'], $string );
        }//987
        /**
         * @description Returns an array of HTML attribute names whose value contains a URL.
         * @return string
         */
        protected function _tp_kses_uri_attributes():string{
            $uri_attributes = array(
                'action','archive','background','cite','classid','codebase',
                'data','formaction','href','icon','longdesc','manifest',
                'poster','profile','src','usemap','xmlns',
            );
            $uri_attributes = $this->_apply_filters( 'tp_kses_uri_attributes', $uri_attributes );
            return $uri_attributes;
        }//1010
        /**
         * @description Callback for `tp_kses_split()`.
         * @param $match
         * @return mixed
         */
        protected function _tp_kses_split_callback( $match ){
            return $this->_tp_kses_split2( $match[0], $this->pass_allowed_html, $this->pass_allowed_protocols );
        }//1060
    }
}else die;