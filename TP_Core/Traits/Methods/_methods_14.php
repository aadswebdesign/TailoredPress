<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _methods_14{
        use _init_db;
        /**
         * @description Determines whether site meta is enabled.
         * @return bool
         */
        protected function _is_site_meta_supported():bool{
            $this->tpdb = $this->_init_db();
            if ( ! $this->_is_multisite() ) return false;
            $network_id = $this->_get_main_network_id();
            $supported = $this->_get_network_option( $network_id, 'site_meta_supported', false );
            if ( false === $supported ) {
                $supported = $this->tpdb->get_var( "SHOW TABLES LIKE '{$this->tpdb->blog_meta}'" ) ? 1 : 0;
                $this->_update_network_option( $network_id, 'site_meta_supported', $supported );
            }
            return (bool) $supported;
        }//6206
        /**
         * @description gmt_offset modification for smart timezone handling.
         * @return bool|float
         */
        protected function _tp_timezone_override_offset(){
            $timezone_string = $this->_get_option( 'timezone_string' );
            if ( ! $timezone_string ) return false;
            $timezone_object = timezone_open( $timezone_string );
            $datetime_object = date_create();
            if ( false === $timezone_object || false === $datetime_object ) return false;
            return round( timezone_offset_get( $timezone_object, $datetime_object ) / HOUR_IN_SECONDS, 2 );
        }//6234
        /**
         * @description Sort-helper for timezones.
         * @param $a
         * @param $b
         * @return int
         */
        protected function _tp_timezone_choice_u_sort_callback( $a, $b ):int{
            if ( 'Etc' === $a['continent'] && 'Etc' === $b['continent'] ) {
                // Make the order of these more like the old dropdown.
                if ( strpos($a['city'], 'GMT+') === 0 && strpos($b['city'], 'GMT+') === 0)
                    return -1 * ( strnatcasecmp( $a['city'], $b['city'] ) );
                if ( 'UTC' === $a['city'] ) {
                    if (strpos($b['city'], 'GMT+') === 0) return 1;
                    return -1;
                }
                if ( 'UTC' === $b['city'] ) {
                    if (strpos($a['city'], 'GMT+') === 0) return -1;
                    return 1;
                }
                return strnatcasecmp( $a['city'], $b['city'] );
            }
            if ( $a['t_continent'] === $b['t_continent'] ) {
                if ( $a['t_city'] === $b['t_city'] )
                    return strnatcasecmp( $a['t_sub_city'], $b['t_sub_city'] );
                return strnatcasecmp( $a['t_city'], $b['t_city'] );
            }
            if ( 'Etc' === $a['continent'] ) return 1;
            if ( 'Etc' === $b['continent'] ) return -1;
            return strnatcasecmp( $a['t_continent'], $b['t_continent'] );
        }//6258
        /**
         * @description Gives a nicely-formatted list of timezone strings.
         * @param $selected_zone
         * @param null $locale
         * @return string
         */
        protected function _tp_timezone_choice( $selected_zone, $locale = null ):string{
            static $mo_loaded = false, $locale_loaded = null;
            $continents = array( 'Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific' );
            // Load translations for continents and cities.
            if ( ! $mo_loaded || $locale !== $locale_loaded ) {
                $locale_loaded = $locale ?: $this->_get_locale();
                $mo_file        = TP_ADMIN_LANG . '/continents-cities-' . $locale_loaded . '.mo';
                $this->_unload_textdomain( 'continents-cities' );
                $this->_load_textdomain( 'continents-cities', $mo_file );
                $mo_loaded = true;
            }
            $zones = [];
            foreach ( timezone_identifiers_list() as $zone ) {
                $zone = explode( '/', $zone );
                if ( ! in_array( $zone[0], $continents, true ) )  continue;
                // This determines what gets set and translated - we don't translate Etc/* strings here, they are done later.
                $exists    = array(
                    0 => ( isset( $zone[0] ) && $zone[0] ),1 => ( isset( $zone[1] ) && $zone[1] ),2 => ( isset( $zone[2] ) && $zone[2] ),
                );
                $exists[3] = ( $exists[0] && 'Etc' !== $zone[0] );
                $exists[4] = ( $exists[1] && $exists[3] );
                $exists[5] = ( $exists[2] && $exists[3] );
                $zones[] = array(
                    'continent'   => ( $exists[0] ? $zone[0] : '' ),
                    'city'        => ( $exists[1] ? $zone[1] : '' ),
                    'sub_city'     => ( $exists[2] ? $zone[2] : '' ),
                    't_continent' => ( $exists[3] ? $this->_translate( str_replace( '_', ' ', $zone[0] ), 'continents-cities' ) : '' ),
                    't_city'      => ( $exists[4] ? $this->_translate( str_replace( '_', ' ', $zone[1] ), 'continents-cities' ) : '' ),
                    't_sub_city'   => ( $exists[5] ? $this->_translate( str_replace( '_', ' ', $zone[2] ), 'continents-cities' ) : '' ),
                );
            }
            usort( $zones, [$this,'_tp_timezone_choice_u_sort_callback'] );
            $structure = [];
            if ( empty( $selected_zone ) )
                $structure[] = "<option selected='selected' value=''>{$this->__( 'Select a city' )}</option>";
            foreach ( $zones as $key => $zone ) {
                $value = array($zone['continent']);
                if (empty($zone['city'])) $display = $zone['t_continent'];
                else {
                    if (!isset($zones[$key - 1]) || $zones[$key - 1]['continent'] !== $zone['continent']) {
                        $label = $zone['t_continent'];
                        $structure[] = "<optgroup label='{$this->_esc_attr($label)}'>";
                    }
                    // Add the city to the value.
                    $value[] = $zone['city'];
                    $display = $zone['t_city'];
                    if (!empty($zone['sub_city'])) {
                        // Add the sub_city to the value.
                        $value[] = $zone['sub_city'];
                        $display .= ' - ' . $zone['t_sub_city'];
                    }
                }
                // Build the value.
                $value = implode('/', $value);
                $selected = '';
                if ($value === $selected_zone) $selected = 'selected="selected" ';
                $structure[] = "<option {$selected} value='{$this->_esc_attr($value)}'>{$this->_esc_html($display)}</option>";
                // Close continent optgroup.
                if (!empty($zone['city']) && (!isset($zones[$key + 1]) || (isset($zones[$key + 1]) && $zones[$key + 1]['continent'] !== $zone['continent'])))
                    $structure[] = "</optgroup>";
            }
            // Do UTC.
            $structure[] = "<optgroup label='{$this->_esc_attr__( 'UTC' )}'>";
            $selected    = '';
            if ( 'UTC' === $selected_zone ) $selected = 'selected="selected" ';
            $structure[] = "<option {$selected} value='{$this->_esc_attr( 'UTC' )}'>{$this->_esc_attr( 'UTC' )}</option>";
            $structure[] = "</optgroup>";
            // Do manual UTC offsets.
            $structure[]  = "<optgroup label='{$this->_esc_attr__( 'Manual Offsets' )}'>";
            $offset_range = array(
                -12, -11.5, -11, -10.5, -10, -9.5, -9, -8.5, -8, -7.5, -7, -6.5, -6, -5.5, -5, -4.5, -4, -3.5, -3, -2.5, -2, -1.5, -1, -0.5,
                0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, 5.5, 5.75, 6, 6.5, 7, 7.5, 8, 8.5, 8.75, 9, 9.5, 10, 10.5, 11, 11.5, 12, 12.75, 13, 13.75, 14,
            );
            foreach ( $offset_range as $offset ) {
                if ( 0 <= $offset ) $offset_name = '+' . $offset;
                else  $offset_name = (string) $offset;
                $offset_value = $offset_name;
                $offset_name  = str_replace( array( '.25', '.5', '.75' ), array( ':15', ':30', ':45' ), $offset_name );
                $offset_name  = 'UTC' . $offset_name;
                $offset_value = 'UTC' . $offset_value;
                $selected     = '';
                if ( $offset_value === $selected_zone ) $selected = 'selected="selected" ';
                $structure[] = "<option {$selected} value='{$this->_esc_attr( $offset_value )}'>{$this->_esc_html( $offset_name )}</option>";
            }
            $structure[] = "</optgroup>";
            return implode( "\n", $structure );
        }//6305
        /**
         * @description Strip close comment and close php tags from file headers used by TP.
         * @param $str
         * @return string
         */
        protected function _cleanup_header_comment( $str ):string{
            return trim( preg_replace( '/\s*(?:\*\/|\?>).*/', '', $str ) );
        }//6492
        /**
         * @description Permanently delete comments or posts of any type that have held a status * of 'trash' for the number of days defined in EMPTY_TRASH_DAYS.
         */
        protected function _tp_scheduled_delete():void{
            $this->tpdb = $this->_init_db();
            $delete_timestamp = time() - ( DAY_IN_SECONDS * EMPTY_TRASH_DAYS );
            $posts_to_delete = $this->tpdb->get_results( $this->tpdb->prepare( TP_SELECT . " post_id FROM " . $this->tpdb->post_meta . " WHERE meta_key = '_tp_trash_meta_time' AND meta_value < %d", $delete_timestamp ), ARRAY_A );
            foreach ( (array) $posts_to_delete as $post ) {
                $post_id = (int) $post['post_id'];
                if ( ! $post_id ) continue;
                $del_post = $this->_get_post( $post_id );
                if ( ! $del_post || 'trash' !== $del_post->post_status ) {
                    $this->_delete_post_meta( $post_id, '_tp_trash_meta_status' );
                    $this->_delete_post_meta( $post_id, '_tp_trash_meta_time' );
                } else $this->_tp_delete_post( $post_id );
            }
            $comments_to_delete = $this->tpdb->get_results( $this->tpdb->prepare( TP_SELECT . " comment_id FROM " . $this->tpdb->comment_meta . " WHERE meta_key = '_tp_trash_meta_time' AND meta_value < %d", $delete_timestamp ), ARRAY_A );
            foreach ( (array) $comments_to_delete as $comment ) {
                $comment_id = (int) $comment['comment_id'];
                if ( ! $comment_id ) continue;
                $del_comment = $this->_get_comment( $comment_id );
                if ( ! $del_comment || 'trash' !== $del_comment->comment_approved ) {
                    $this->_delete_comment_meta( $comment_id, '_tp_trash_meta_time' );
                    $this->_delete_comment_meta( $comment_id, '_tp_trash_meta_status' );
                } else $this->_tp_delete_comment( $del_comment );
            }
        }//6513
        /**
         * @param $file
         * @param $default_headers
         * @param string $context
         * @return array
         */
        protected function _get_file_data( $file, $default_headers, $context = '' ):array{
            // We don't need to write to the file, so just open for reading.
            $fp = fopen( $file, 'rb' );
            if ( $fp ) {
                $file_data = fread( $fp, 8 * KB_IN_BYTES ); // Pull only the first 8 KB of the file in.
                fclose( $fp );// PHP will close file handle, but we are good citizens.
            } else $file_data = '';
            $file_data = str_replace( "\r", "\n", $file_data );
            $extra_headers = $context ? $this->_apply_filters( "extra_{$context}_headers", array() ) : array();
            if ( $extra_headers ) {
                $extra_headers = array_combine( $extra_headers, $extra_headers ); // Keys equal values.
                $all_headers   = array_merge( $extra_headers, (array) $default_headers );
            } else $all_headers = $default_headers;
            foreach ( $all_headers as $field => $regex ) {
                if ( preg_match( '/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] )
                    $all_headers[ $field ] = $this->_cleanup_header_comment( $match[1] );
                else $all_headers[ $field ] = '';
            }
            return $all_headers;
        }//6575
        protected function _return_true():bool{return true;}//6632
        protected function _return_false():bool{return false;}//6647
        protected function _return_zero():bool{return 0;}//6660
    }
}else die;