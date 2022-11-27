<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-4-2022
 * Time: 11:32
 */
namespace TP_Admin\Traits\AdminRewrite;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _adm_rewrite_02{
        use _init_error;
        // not used _tp_print_plugin_file_tree //461
        //@description Flushes rewrite rules if siteurl, home or page_on_front changed.
        /**
         * @description Flushes rewrite rules if siteurl, home or page_on_front changed.
         */
        protected function _update_home_siteurl():void{ //not using $old_value, $value
            if ( $this->_tp_installing() ) {return;}
            if ( $this->_is_multisite() && $this->_ms_is_switched() ) {$this->_delete_option( 'rewrite_rules' );
            } else {$this->_flush_rewrite_rules();}
        }//518 adm/misc
        /**
         * @description Resets global variables based on $_GET and $_POST
         * @param $vars
         */
        protected function _tp_reset_vars( $vars ):void{
            foreach ( $vars as $var ) {
                if ( empty( $_POST[ $var ] ) ) {
                    if ( empty( $_GET[ $var ] ) ) {$GLOBALS[ $var ] = '';
                    } else {$GLOBALS[ $var ] = $_GET[ $var ];}
                } else {$GLOBALS[ $var ] = $_POST[ $var ];}
            }
        }//542 adm/misc
        /**
         * @description Displays the given administration message.
         * @param $message
         */
        protected function _show_message($message ):void{
            if ( $this->_init_error( $message ) ) {
                if ($message instanceof TP_Error && $message->get_error_data() && is_string( $message->get_error_data() ) ) {
                    $message = $message->get_error_message() . ': ' . $message->get_error_data();
                } else {$message = $message->get_error_message();}
            }
            echo "<p>$message</p>\n";
            $this->_tp_ob_end_flush_all();
            flush();
        }//563 adm/misc
        /**
         * @param $content
         * @return string
         */
        protected function _tp_doc_link_parse( $content ):string{
            if ( ! is_string( $content ) || empty( $content ) ) {return [];}
            if ( ! function_exists( 'token_get_all' ) ) { return [];}
            $tokens           = token_get_all( $content );
            $count            = count( $tokens );
            $functions        = [];
            $ignore_functions = [];
            for ( $t = 0; $t < $count - 2; $t++ ) {
                if ( ! is_array( $tokens[ $t ])){continue;}
                if ( T_STRING === $tokens[ $t ][0] && ( '(' === $tokens[ $t + 1 ] || '(' === $tokens[ $t + 2 ] ) ) {
                    if ( ( isset( $tokens[ $t - 2 ][1] ) && in_array( $tokens[ $t - 2 ][1], array( 'function', 'class' ), true ) )
                        || ( isset( $tokens[ $t - 2 ][0] ) && T_OBJECT_OPERATOR === $tokens[ $t - 1 ][0] )
                    ) { $ignore_functions[] = $tokens[ $t ][1];}
                    $functions[] = $tokens[ $t ][1];
                }
            }
            $functions = array_unique( $functions );
            sort( $functions );
            $ignore_functions = $this->_apply_filters( 'documentation_ignore_functions', $ignore_functions );
            $ignore_functions = array_unique( $ignore_functions );
            $out = [];
            foreach ( $functions as $function ) {
                if ( in_array( $function, $ignore_functions, true ) ) {continue;}
                $out[] = $function;
            }
            return $out;
        }//582 adm/misc
        /**
         * @description Saves option for number of rows when listing posts, pages, comments, etc.
         */
        protected function _set_screen_options():void{
            if ( isset( $_POST['tp_screen_options'] ) && is_array( $_POST['tp_screen_options'] ) ) {
                $this->_check_admin_referer( 'screen-options-nonce', 'screenoptionnonce' );
                $user = $this->_tp_get_current_user();
                if ( ! $user ) { return;}
                $option = $_POST['tp_screen_options']['option'];
                $value  = $_POST['tp_screen_options']['value'];
                if ( $this->_sanitize_key( $option ) !== $option ) {return;}
                $map_option = $option;
                $type       = str_replace( 'edit_', '', $map_option );
                $type       .= str_replace( '_per_page', '', $type );
                if ( in_array( $type, $this->_get_taxonomies(), true ) ) {$map_option = 'edit_tags_per_page';}
                elseif ( in_array( $type, $this->_get_post_types(), true ) ) {$map_option = 'edit_per_page';}
                else {$option = str_replace( '-', '_', $option );}
                switch ( $map_option ) {
                    case 'edit_per_page':
                    case 'users_per_page':
                    case 'edit_comments_per_page':
                    case 'upload_per_page':
                    case 'edit_tags_per_page':
                    case 'plugins_per_page':
                    case 'export_personal_data_requests_per_page':
                    case 'remove_personal_data_requests_per_page':
                        // Network admin.
                    case 'sites_network_per_page':
                    case 'users_network_per_page':
                    case 'site_users_network_per_page':
                    case 'plugins_network_per_page':
                    case 'themes_network_per_page':
                    case 'site_themes_network_per_page':
                        $value = (int) $value;
                        if ( $value < 1 || $value > 999 ) {return;}
                        break;
                    default:
                        $screen_option = false;
                        if ('layout_columns' === $option || '_page' === substr( $option, -5 )) {
                            $screen_option = $this->_apply_filters( 'set-screen-option', $screen_option, $option, $value ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
                        }
                        $value = $this->_apply_filters( "set_screen_option_{$option}", $screen_option, $option, $value );
                        if ( false === $value ) { return;}
                        break;
                }
                $this->_update_user_meta( $user->ID, $option, $value );
                $url = $this->_remove_query_arg( array( 'pagenum', 'apage', 'paged' ), $this->_tp_get_referer() );
                if ( isset( $_POST['mode'] ) ) { $url = $this->_add_query_arg( array( 'mode' => $_POST['mode'] ), $url );}
                $this->_tp_safe_redirect( $url );
                exit;
            }
        }//642 adm/misc
        /**
         * @description Check if rewrite rule for TailoredPress already exists in the IIS 7+ configuration file
         * @param $filename
         * @return bool
         */
        protected function _iis7_rewrite_rule_exists( $filename ):bool{
            if ( ! file_exists( $filename ) ) { return false;}
            if ( ! class_exists( 'DOMDocument', false)){return false;}
            $doc = new \DOMDocument();
            if ( $doc->load( $filename ) === false ) { return false;}
            $xpath = new \DOMXPath( $doc );
            $rules = $xpath->query( '/configuration/system.webServer/rewrite/rules/rule[starts-with(@name,\'tailoredpress\')] | /configuration/system.webServer/rewrite/rules/rule[starts-with(@name,\'TailoredPress\')]' );
            return 0 !== $rules->length;
        }//760 adm/misc
        /**
         * @description Delete TailoredPress rewrite rule from web.config file if it exists there
         * @param $filename
         * @return bool
         */
        protected function _iis7_delete_rewrite_rule( $filename ):bool {
            if ( ! file_exists( $filename )){ return true;}
            if ( ! class_exists( 'DOMDocument', false ) ) { return false;}
            $doc                     = new \DOMDocument();
            $doc->preserveWhiteSpace = false;
            if ( $doc->load( $filename ) === false ) { return false;}
            $xpath = new \DOMXPath( $doc );
            $rules = $xpath->query( '/configuration/system.webServer/rewrite/rules/rule[starts-with(@name,\'tailoredpress\')] | /configuration/system.webServer/rewrite/rules/rule[starts-with(@name,\'TailoredPress\')]' );
            if ( $rules->length > 0 ) {
                $child  = $rules->item( 0 );
                $parent = $child->parentNode;
                $parent->removeChild( $child );
                $doc->formatOutput = true;
                $this->_saveDomDocument( $doc, $filename );
            }
            return true;
        }//789 adm/misc
        /**
         * @description Add TailoredPress rewrite rule to the IIS 7+ configuration file.
         * @param $filename
         * @param $rewrite_rule
         * @return bool|string
         */
        protected function _iis7_add_rewrite_rule( $filename, $rewrite_rule ){
            if ( ! class_exists( 'DOMDocument', false ) ) {return false;}
            if ( ! file_exists( $filename ) ) {
                $fp = fopen( $filename, 'wb' );
                fwrite( $fp, '<configuration/>' );
                fclose( $fp );
            }
            $doc                     = new \DOMDocument();
            $doc->preserveWhiteSpace = false;
            if ( $doc->load( $filename ) === false ) { return false;}
            $xpath = new \DOMXPath( $doc );
            $tailored_press_rules = $xpath->query( '/configuration/system.webServer/rewrite/rules/rule[starts-with(@name,\'tailoredpress\')] | /configuration/system.webServer/rewrite/rules/rule[starts-with(@name,\'TailoredPress\')]' );
            if ( $tailored_press_rules->length > 0 ) { return true;}
            $xmlnodes = $xpath->query( '/configuration/system.webServer/rewrite/rules' );
            if ( $xmlnodes->length > 0 ) { $rules_node = $xmlnodes->item( 0 );
            } else {
                $rules_node = $doc->createElement( 'rules' );
                $xmlnodes = $xpath->query( '/configuration/system.webServer/rewrite' );
                if ( $xmlnodes->length > 0 ) {
                    $rewrite_node = $xmlnodes->item( 0 );
                    $rewrite_node->appendChild( $rules_node );
                } else {
                    $rewrite_node = $doc->createElement( 'rewrite' );
                    $rewrite_node->appendChild( $rules_node );
                    $xmlnodes = $xpath->query( '/configuration/system.webServer' );
                    if ( $xmlnodes->length > 0 ) {
                        $system_webServer_node = $xmlnodes->item( 0 );
                        $system_webServer_node->appendChild( $rewrite_node );
                    } else {
                        $system_webServer_node = $doc->createElement( 'system.webServer' );
                        $system_webServer_node->appendChild( $rewrite_node );
                        $xmlnodes = $xpath->query( '/configuration' );
                        if ( $xmlnodes->length > 0 ) {
                            $config_node = $xmlnodes->item( 0 );
                            $config_node->appendChild( $system_webServer_node );
                        } else {
                            $config_node = $doc->createElement( 'configuration' );
                            $doc->appendChild( $config_node );
                            $config_node->appendChild( $system_webServer_node );
                        }
                    }
                }
            }
            $rule_fragment = $doc->createDocumentFragment();
            $rule_fragment->appendXML( $rewrite_rule );
            $rules_node->appendChild( $rule_fragment );
            $doc->encoding     = 'UTF-8';
            $doc->formatOutput = true;
            $this->_saveDomDocument( $doc, $filename );
            return true;
        }//826 adm/misc
        /**
         * @description Saves the XML document into a file
         * @param \DOMDocument $doc
         * @param $filename
         */
        protected function _saveDomDocument(\DOMDocument $doc, $filename ):void{
            $config = $doc->saveXML();
            $config = preg_replace( "/([^\r])\n/", "$1\r\n", $config );
            $fp     = fopen( $filename, 'wb' );
            fwrite( $fp, $config );
            fclose( $fp );
        }//908 adm/misc
    }
}else die;