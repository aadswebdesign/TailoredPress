<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 10:04
 */
namespace TP_Core\Traits\Formats;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _formats_09 {
        use _init_db;
        use _init_error;
        /**
         * @description Sanitises various option values based on the nature of the option.
         * @param $option
         * @param $value
         * @return mixed
         */
        protected function _sanitize_option( $option, mixed $value ){
            $tpdb = $this->_init_db();
            $original_value = $value;
            $error          = null;
            switch ( $option ) {
                case 'admin_email':
                case 'new_admin_email':
                    $value = $tpdb->strip_invalid_text_for_column( $tpdb->options, 'option_value', $value );
                    if ($value instanceof TP_Error && $this->_init_error( $value ) ) $error = $value->get_error_message();
                    else {
                        $value = $this->_sanitize_email( $value );
                        if ( ! $this->_is_email( $value ) )
                            $error = $this->__( 'The email address entered did not appear to be a valid email address. Please enter a valid email address.' );
                    }
                    break;
                case 'thumbnail_size_w':
                case 'thumbnail_size_h':
                case 'medium_size_w':
                case 'medium_size_h':
                case 'medium_large_size_w':
                case 'medium_large_size_h':
                case 'large_size_w':
                case 'large_size_h':
                case 'mailserver_port':
                case 'comment_max_links':
                case 'page_on_front':
                case 'page_for_posts':
                case 'rss_excerpt_length':
                case 'default_category':
                case 'default_email_category':
                case 'default_link_category':
                case 'close_comments_days_old':
                case 'comments_per_page':
                case 'thread_comments_depth':
                case 'users_can_register':
                case 'start_of_week':
                case 'site_icon':
                    $value = $this->_abs_int( $value );
                    break;
                case 'posts_per_page':
                case 'posts_per_rss':
                    $value = (int) $value;
                    if ( empty( $value ) ) $value = 1;
                    if ( $value < -1 ) $value = abs( $value );
                    break;
                case 'default_ping_status':
                case 'default_comment_status':
                    if ( '0' === $value || '' === $value ) $value = 'closed';
                    break;
                case 'blogdescription':
                case 'blogname': //emoji left out
                    $value = $tpdb->strip_invalid_text_for_column( $tpdb->options, 'option_value', $value );
                    if ( $this->_init_error( $value ) ) $error = $value->get_error_message();
                    else $value = $this->_esc_html( $value );
                    break;
                case 'blog_charset':
                    $value = preg_replace( '/[^a-zA-Z0-9_-]/', '', $value ); // Strips slashes.
                    break;
                case 'blog_public':
                    if ( null === $value ) $value = 1;
                    else $value = (int) $value;
                    break;
                case 'date_format':
                case 'time_format':
                case 'mailserver_url':
                case 'mailserver_login':
                case 'mailserver_pass':
                case 'upload_path':
                    $value = $tpdb->strip_invalid_text_for_column( $tpdb->options, 'option_value', $value );
                    if ( $this->_init_error( $value ) )  $error = $value->get_error_message();
                    else {
                        $value = strip_tags( $value );
                        $value = $this->_tp_kses_data( $value );
                    }
                    break;
                case 'ping_sites':
                    $value = explode( "\n", $value );
                    $value = array_filter( array_map( 'trim', $value ) );
                    $value = array_filter( array_map( 'esc_url_raw', $value ) );
                    $value = implode( "\n", $value );
                    break;
                case 'gmt_offset':
                    $value = preg_replace( '/[^0-9:.-]/', '', $value ); // Strips slashes.
                    break;
                case 'siteurl':
                    $value = $tpdb->strip_invalid_text_for_column( $tpdb->options, 'option_value', $value );
                    if ( $this->_init_error( $value ) ) $error = $value->get_error_message();
                    else if ( preg_match( '#http(s?)://(.+)#i', $value ) ) $value = $this->_esc_url_raw( $value );
                    else $error = $this->__( 'The TailoredPress address you entered did not appear to be a valid URL. Please enter a valid URL.' );
                    break;
                case 'home':
                    $value = $tpdb->strip_invalid_text_for_column( $tpdb->options, 'option_value', $value );
                    if ( $this->_init_error( $value ) ) $error = $value->get_error_message();
                    else if ( preg_match( '#http(s?)://(.+)#i', $value ) ) $value = $this->_esc_url_raw( $value );
                    else $error = $this->__( 'The Site address you entered did not appear to be a valid URL. Please enter a valid URL.' );
                    break;
                case 'TPLANG':
                    $allowed = $this->_get_available_languages();
                    if ( defined( 'TPLANG' ) && '' !== TPLANG && 'en_US' !== TPLANG && ! $this->_is_multisite() )
                        $allowed[] = TPLANG;
                    if (! empty( $value ) && ! in_array( $value, $allowed, true )) $value = $this->_get_option( $option );
                    break;
                case 'illegal_names':
                    $value = $tpdb->strip_invalid_text_for_column( $tpdb->options, 'option_value', $value );
                    if ( $this->_init_error( $value ) ) $error = $value->get_error_message();
                    else {
                        if ( ! is_array( $value ) ) $value = explode( ' ', $value );
                        $value = array_values( array_filter( array_map( 'trim', $value ) ) );
                        if ( ! $value ) $value = '';
                    }
                    break;
                case 'limited_email_domains':
                case 'banned_email_domains':
                    $value = $tpdb->strip_invalid_text_for_column( $tpdb->options, 'option_value', $value );
                    if ( $this->_init_error( $value ) ) $error = $value->get_error_message();
                    else {
                        if ( ! is_array( $value ) ) $value = explode( "\n", $value );
                        $domains = array_values( array_filter( array_map( 'trim', $value ) ) );
                        $new_value   = [];
                        foreach ( $domains as $domain ) {
                            if ( ! preg_match( '/(--|\.\.)/', $domain ) && preg_match( '|^([a-zA-Z0-9-\.])+$|', $domain ) )
                                $new_value[] = $domain;
                        }
                        $value = array_merge($value,$new_value);
                        if ( ! $value ) $value = '';
                    }
                    break;
                case 'timezone_string':
                    $allowed_zones = timezone_identifiers_list();
                    if ( ! empty( $value ) && ! in_array( $value, $allowed_zones, true ))
                        $error = $this->__( 'The timezone you have entered is not valid. Please select a valid timezone.' );
                    break;
                case 'permalink_structure':
                case 'category_base':
                case 'tag_base':
                    $value = $tpdb->strip_invalid_text_for_column( $tpdb->options, 'option_value', $value );
                    if ( $this->_init_error( $value ) ) $error = $value->get_error_message();
                    else {
                        $value = $this->_esc_url_raw( $value );
                        $value = str_replace( 'http://', '', $value );
                    }
                    if ( 'permalink_structure' === $option && null === $error
                        && '' !== $value && ! preg_match( '/%[^\/%]+%/', $value )
                    )   $error = sprintf(
                        /* translators: %s: Documentation URL. */
                            $this->__( 'A structure tag is required when using custom permalinks. <a href="%s">Learn more</a>' ),
                            $this->__( 'https://wordpress.org/support/article/using-permalinks/#choosing-your-permalink-structure' )
                        );
                    break;
                case 'default_role':
                    if ( ! $this->_get_role( $value ) && $this->_get_role( 'subscriber' ) ) $value = 'subscriber';
                    break;
                case 'moderation_keys':
                case 'disallowed_keys':
                    $value = $tpdb->strip_invalid_text_for_column( $tpdb->options, 'option_value', $value );
                    if ( $this->_init_error( $value ) ) $error = $value->get_error_message();
                    else {
                        $value .= explode( "\n", $value );
                        $value .= array_filter( array_map( 'trim', $value ) );
                        $value .= array_unique( $value );
                        $value = implode( "\n", $value );
                    }
                    break;
            }
            if ( null !== $error ) {
                if ( '' === $error && $this->_init_error( $value ) ) /* translators: 1: Option name, 2: Error code. */
                    $error = sprintf( $this->__( 'Could not sanitize the %1$s option. Error code: %2$s' ), $option, $value->get_error_code() );
                $value = $this->_get_option( $option );
                if ( function_exists( 'add_settings_error' ) )
                    add_settings_error( $option, "invalid_{$option}", $error );
            }
            return $this->_apply_filters( "sanitize_option_{$option}", $value, $option, $original_value );
        }//4711
        /**
         * @description Maps a function to all non-iterable elements of an array or an object.
         * @note This is similar to `array_walk_recursive()` but acts upon objects too.
         * @since
         * @param mixed    $value    The array, object, or scalar.
         * @param callable $callback The function to map onto $value.
         * @return mixed The value with the callback applied to all non-arrays and non-objects inside it.
         */
        protected function _map_deep( $value, $callback ){
            if ( is_array( $value ) ) {
                foreach ( $value as $index => $item ) $value[ $index ] = $this->_map_deep( $item, $callback );
            } elseif ( is_object( $value ) ) {
                $object_vars = get_object_vars( $value );
                foreach ( $object_vars as $property_name => $property_value )
                    $value->$property_name = $this->_map_deep( $property_value, $callback );
            } else $value = $callback($value);
            return $value;
        }//4990
        /**
         * @description Parses a string into variables to be stored in an array.
         * @since 2.2.1
         * @param string $string The string to be parsed.
         * @param array  $array  Variables will be stored in this array.
         */
        protected function _tp_parse_str( $string, &$array ):void{
            parse_str( (string) $string, $array );
            $array = $this->_apply_filters( 'tp_parse_str', $array );
        }//5015
        /**
         * @description  Convert lone less than signs.
         * @param $text
         * @return mixed
         */
        protected function _tp_pre_kses_less_than( $text ){
            return preg_replace_callback( '%<[^>]*?((?=<)|>|$)%', 'tp_pre_kses_less_than_callback', $text );
        }//5038
        /**
         * @description Callback function used by preg_replace.
         * @param $matches
         * @return mixed
         */
        protected function _tp_pre_kses_less_than_callback( $matches ){
            if ( false === strpos( $matches[0], '>' ) )
                return $this->_esc_html( $matches[0] );
            return $matches[0];
        }//5050
        /**
         * @description Remove non-allowable HTML from parsed block attribute values when filtering in the post context.
         * @param $string
         * @param $allowed_html
         * @param $allowed_protocols
         * @return mixed
         */
        protected function _tp_pre_kses_block_attributes( $string, $allowed_html, $allowed_protocols ){
            $this->_remove_filter( 'pre_kses', 'tp_pre_kses_block_attributes', 10 );
            $string = $this->_filter_block_content( $string, $allowed_html, $allowed_protocols );
            $this->_add_filter( 'pre_kses', 'wp_pre_kses_block_attributes', 10, 3 );
            return $string;
        }//5070
        /**
         * @description TailoredPress implementation of PHP sprintf() with filters.
         * @param $pattern
         * @param array ...$args
         * @return string
         */
        protected function _tp_sprintf( $pattern, ...$args ):string{
            $len       = strlen( $pattern );
            $start     = 0;
            $result    = '';
            $arg_index = 0;
            while ( $len > $start ) {
                if ( strlen( $pattern ) - 1 === $start ) {
                    $result .= substr( $pattern, -1 );
                    break;
                }
                if ( '%%' === substr( $pattern, $start, 2 ) ) {
                    $start  += 2;
                    $result .= '%';
                    continue;
                }
                $end = strpos( $pattern, '%', $start + 1 );
                if ( false === $end ) $end = $len;
                $fragment = substr( $pattern, $start, $end - $start );
                if ( '%' === $pattern[ $start ] ) {
                    if ( preg_match( '/^%(\d+)\$/', $fragment, $matches ) ) {
                        $index    = $matches[1] - 1; // 0-based array vs 1-based sprintf() arguments.
                        $arg      = $args[ $index ] ?? '';
                        $fragment = str_replace( "%{$matches[1]}$", '%', $fragment );
                    }else {
                        $arg = $args[ $arg_index ] && '';
                        ++$arg_index;
                    }
                    $_fragment = $this->_apply_filters( 'tp_sprintf', $fragment, $arg );
                    if ( $_fragment !== $fragment )  $fragment = $_fragment;
                    else $fragment = sprintf( $fragment, (string) $arg );
                }
                $result .= $fragment;
                $start   = $end;
            }
            return $result;
        }//5095
        /**
         * @description  Localize list items before the rest of the content.
         * @param $pattern
         * @param $args
         * @return string
         */
        protected function _tp_sprintf_list( $pattern, $args ):string{
            if (strpos($pattern, '%l') !== 0)  return $pattern;
            if ( empty( $args ) )  return '';
            $sprint_list = [
                'between' => sprintf( $this->__( '%1$s, %2$s' ), '', '' ),
                'between_last_two' => sprintf( $this->__( '%1$s, and %2$s' ), '', '' ),
                'between_only_two' => sprintf( $this->__( '%1$s and %2$s' ), '', '' ),
            ];
            $_list = $this->_apply_filters('tp_sprint_list', $sprint_list);
            $args   = (array) $args;
            $result = array_shift( $args );
            if ( count( $args ) === 1 ) $result .= $_list['between_only_two'] . array_shift( $args );
            // Loop when more than two args.
            $i = count( $args );
            while ( $i ) {
                $arg = array_shift( $args );
                $i--;
                if ( 0 === $i ) $result .= $_list['between_last_two'] . $arg;
                else $result .= $_list['between'] . $arg;

            }
            return $result . substr( $pattern, 2 );
        }//5172
        /**
         * @description Safely extracts not more than the first $count characters from HTML string.
         * @param $str
         * @param $count
         * @param null $more
         * @return mixed|string
         */
        protected function _tp_html_excerpt( $str, $count, $more = null ){
            if ( null === $more ) $more = '';
            $str     = $this->_tp_strip_all_tags( $str, true );
            $excerpt = mb_substr( $str, 0, $count );
            $excerpt = preg_replace( '/&[^;\s]{0,6}$/', '', $excerpt );
            if ( $str !== $excerpt )
                $excerpt = trim( $excerpt ) . $more;
            return $excerpt;
        }//5241
        /**
         * @description Safely extracts not more than the first $count characters from HTML string.
         * @param $content
         * @param $base
         * @param mixed $attrs
         * @return mixed
         */
        protected function _links_add_base_url( $content, $base, $attrs = ['src', 'href'] ){
            $this->_links_add_base = $base;
            $attrs = implode( '|', $attrs );
            return preg_replace_callback( "!($attrs)=(['\"])(.+?)\\2!i", '_links_add_base', $content );
        }//5273
    }
}else die;