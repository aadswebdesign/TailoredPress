<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 23:09
 */
namespace TP_Core\Traits\K_Ses;
if(ABSPATH){
    trait _k_ses_05 {
        /**
         * @description
         */
        protected function _kses_init_filters():void{
            // Normal filtering.
            $this->_add_filter( 'title_save_pre', [$this,'_tp_filter_kses'] );
            // Comment filtering.
            if ( $this->__( 'unfiltered_html' ) )
                $this->_add_filter( 'pre_comment_content', [$this,'_tp_filter_post_kses'] );
            else $this->_add_filter( 'pre_comment_content', [$this,'_tp_filter_kses'] );
            // Post filtering.
            $this->_add_filter( 'content_save_pre', [$this,'_tp_filter_post_kses']);
            $this->_add_filter( 'content_save_pre', [$this,'_tp_filter_global_styles_post'] );
            $this->_add_filter( 'excerpt_save_pre', [$this,'_tp_filter_post_kses'] );
            $this->_add_filter( 'content_filtered_save_pre', [$this,'_tp_filter_post_kses'] );
            $this->_add_filter( 'content_filtered_save_pre', [$this,'_tp_filter_global_styles_post'] );
        }//2193
        /**
         * @description Removes all KSES input form content filters.
         */
        protected function _kses_remove_filters():void{
            $this->_remove_filter( 'title_save_pre', [$this,'_tp_filter_kses'] );
            // Comment filtering.
            $this->_remove_filter( 'pre_comment_content', [$this,'_tp_filter_post_kses'] );
            $this->_remove_filter( 'pre_comment_content', [$this,'_tp_filter_kses'] );
            // Post filtering.
            $this->_remove_filter( 'content_save_pre', [$this,'_tp_filter_post_kses'] );
            $this->_remove_filter( 'content_save_pre', [$this,'_tp_filter_global_styles_post'] );
            $this->_remove_filter( 'excerpt_save_pre', [$this,'_tp_filter_post_kses'] );
            $this->_remove_filter( 'content_filtered_save_pre', [$this,'_tp_filter_post_kses'] );
            $this->_remove_filter( 'content_filtered_save_pre', [$this,'_tp_filter_global_styles_post'] );

        }//2224
        /**
         * @description Sets up most of the KSES filters for input form content.
         */
        protected function _kses_init():void{
            $this->_kses_remove_filters();
            if ( ! $this->_current_user_can( 'unfiltered_html' ) )
                $this->_kses_init_filters();
        }//2249
        /**
         * @description Filters an inline style attribute and removes disallowed rules.
         * @param $css
         * @return mixed|string
         */
        protected function _safe_css_filter_attr( $css ){
            $css = $this->_tp_kses_no_null( $css );
            $css = str_replace( ["\n", "\r", "\t"], '', $css );
            $allowed_protocols = $this->_tp_allowed_protocols();
            $css_array = explode( ';', trim( $css ) );
            $css_rules = [
                'background','background-color','background-image','background-position','background-size',
                'background-attachment','background-blend-mode',
                'border','border-radius','border-width','border-color','border-style',
                'border-right','border-right-color','border-right-style','border-right-width',
                'border-bottom','border-bottom-color','border-bottom-left-radius','border-bottom-right-radius','border-bottom-style',
                'border-bottom-width','border-bottom-right-radius','border-bottom-left-radius','border-left','border-left-color',
                'border-left-style','border-left-width','border-top','border-top-color','border-top-left-radius',
                'border-top-right-radius','border-top-style','border-top-width','border-top-left-radius','border-top-right-radius',
                'border-spacing','border-collapse','caption-side',
                'columns','column-count','column-fill','column-gap','column-rule','column-span','column-width',
                'color',
                'filter','font','font-family','font-size','font-style','font-variant','font-weight',
                'letter-spacing','line-height','text-align','text-decoration','text-indent','text-transform',
                'height','min-height','max-height',
                'width','min-width','max-width',
                'margin','margin-right','margin-bottom','margin-left','margin-top',
                'padding','padding-right','padding-bottom','padding-left','padding-top',
                'flex','flex-basis','flex-direction','flex-flow','flex-grow','flex-shrink',
                'grid-template-columns','grid-auto-columns','grid-column-start','grid-column-end','grid-column-gap',
                'grid-template-rows','grid-auto-rows','grid-row-start','grid-row-end','grid-row-gap','grid-gap',
                'justify-content','justify-items','justify-self',
                'align-content','align-items','align-self',
                'clear',
                'cursor',
                'direction',
                'float',
                'list-style-type',
                'object-position',
                'overflow',
                'vertical-align',
                'gap', //todo adding the latest css rules and the houdini rules
            ];
            $allowed_attr = $this->_apply_filters('safe_style_css',$css_rules);
            $css_url_data_types = ['background','background-image','cursor','list-style','list-style-image',];
            $css_gradient_data_types = ['background','background-image',];
            if ( empty( $allowed_attr ) ) return $css;
            $css = '';
            $parts = null;
            foreach ( $css_array as $css_item ) {
                if ( '' === $css_item ) continue;
                $css_item        = trim( $css_item );
                $css_test_string = $css_item;
                $found           = false;
                $url_attr        = false;
                $gradient_attr   = false;
                if ( strpos( $css_item, ':' ) === false ) $found = true;
                else {
                    $parts = explode( ':', $css_item, 2 );
                    $css_selector = trim( $parts[0] );
                    if ( in_array( $css_selector, $allowed_attr, true ) ) {
                        $found         = true;
                        $url_attr      = in_array( $css_selector, $css_url_data_types, true );
                        $gradient_attr = in_array( $css_selector, $css_gradient_data_types, true );
                    }
                }
                if ( $found && $url_attr ) {
                    // Simplified: matches the sequence `url(*)`.
                    preg_match_all( '/url\([^)]+\)/', $parts[1], $url_matches );
                    foreach ( $url_matches[0] as $url_match ) {
                        // Clean up the URL from each of the matches above.
                        preg_match( '/^url\(\s*([\'\"]?)(.*)(\g1)\s*\)$/', $url_match, $url_pieces );
                        if ( empty( $url_pieces[2] ) ) {
                            $found = false;
                            break;
                        }
                        $url = trim( $url_pieces[2] );
                        if ( empty( $url ) || $this->_tp_kses_bad_protocol( $url, $allowed_protocols ) !== $url ) {
                            $found = false;
                            break;
                        } else $css_test_string = str_replace( $url_match, '', $css_test_string );
                    }
                }
                if ( $found && $gradient_attr ) {
                    $css_value = trim( $parts[1] );
                    if ( preg_match( '/^(repeating-)?(linear|radial|conic)-gradient\(([^()]|rgb[a]?\([^()]*\))*\)$/', $css_value ) ) {
                        // Remove the whole `gradient` bit that was matched above from the CSS.
                        $css_test_string = str_replace( $css_value, '', $css_test_string );
                    }
                }
                if ( $found ) {
                    $css_test_string = preg_replace( '/calc\(((?:\([^()]*\)?|[^()])*)\)/', '', $css_test_string );
                    $css_test_string = preg_replace( '/\(?var\(--[a-zA-Z0-9_-]*\)/', '', $css_test_string );
                    $allow_css = ! preg_match( '%[\\\(&=}]|/\*%', $css_test_string );
                    $allow_css = $this->_apply_filters( 'safe_css_filter_attr_allow_css', $allow_css, $css_test_string );
                    if ( $allow_css ) {
                        if ( '' !== $css ) $css .= ';';
                        $css .= $css_item;
                    }
                }
            }
            return $css;
        }//2276
        /**
         * @description Helper function to add global attributes to a tag in the allowed HTML list.
         * @param $value
         * @return array
         */
        protected function _tp_add_global_attributes( $value ):array{
            $global_attributes = [
                'aria-described-by' => true,'aria-details' => true,'aria-label' => true,
                'aria-labelled-by'  => true,'aria-hidden' => true,'class' => true,'id' => true,
                'style' => true,'title' => true,'role' => true,'data-*' => true,
            ];
            if ( true === $value ) $value = [];
            if ( is_array( $value ) ) return array_merge( $value, $global_attributes );
            return $value;
        }//2558
        /**
         * @description Helper function to check if this is a safe PDF URL.
         * @param $url
         * @return bool
         */
        protected function _tp_kses_allow_pdf_objects( $url ):bool{
            if ( $this->_tp_str_contains( $url, '?' ) || $this->_tp_str_contains( $url, '#' ) )
            if ( ! $this->_tp_str_ends_with( $url, '.pdf' ) ) return false;
            $upload_info = $this->_tp_upload_dir( null, false );
            $parsed_url  = $this->_tp_parse_url( $upload_info['url'] );
            $upload_host = $parsed_url['host'] ?? '';
            $upload_port = ':' . $parsed_url['port'] ?? '';
            if ( $this->_tp_str_starts_with( $url, "http://$upload_host$upload_port/" )|| $this->_tp_str_starts_with( $url, "https://$upload_host$upload_port/" ))
                return true;
            return false;
        }//2594
    }
}else die;