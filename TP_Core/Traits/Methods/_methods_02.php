<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
if(ABSPATH){
    trait _methods_02{
        /**
         * @description Convert a duration to human readable format.
         * @param string $duration
         * @return bool|string
         */
        protected function _human_readable_duration( $duration = '' ){
            if ( ( empty( $duration ) || ! is_string( $duration ) ) )
                return false;
            $duration = trim( $duration );
            if ( '-' === $duration[0]) $duration = substr( $duration, 1 );
            // Extract duration parts.
            $duration_parts = array_reverse( explode( ':', $duration ) );
            $duration_count = count( $duration_parts );
            $hour   = null;
            $minute = null;
            $second = null;
            if ( 3 === $duration_count ) {
                // Validate HH:ii:ss duration format.
                if ( ! ( (bool) preg_match( '/^(\d+):([0-5]?\d):([0-5]?\d)$/', $duration ) ) )
                    return false;
                // Three parts: hours, minutes & seconds.
                @list( $second, $minute, $hour ) = $duration_parts;
            } elseif ( 2 === $duration_count ) {
                // Validate ii:ss duration format.
                if ( ! ( (bool) preg_match( '/^([0-5]?\d):([0-5]?\d)$/', $duration ) ) ) {
                    return false;
                }
                @list( $second, $minute ) = $duration_parts;
            } else  return false;
            $human_readable_duration = [];
            // Add the hour part to the string.
            if ( is_numeric( $hour ) ) $human_readable_duration[] = sprintf( $this->_n( '%s hour', '%s hours', $hour ), (int) $hour );
            // Add the minute part to the string.
            if ( is_numeric( $minute ) ) $human_readable_duration[] = sprintf( $this->_n( '%s minute', '%s minutes', $minute ), (int) $minute );
            // Add the second part to the string.
            if ( is_numeric( $second ) ) $human_readable_duration[] = sprintf( $this->_n( '%s second', '%s seconds', $second ), (int) $second );
            return implode( ', ', $human_readable_duration );
        }//500
        /**
         * @description Get the week start and end from the datetime or date string from MySQL.
         * @param $mysql_string
         * @param string $start_of_week
         * @return array
         */
        protected function _get_week_start_end( $mysql_string, $start_of_week = '' ):array{
            $my = substr( $mysql_string, 0, 4 );
            // MySQL string month.
            $mm = substr( $mysql_string, 8, 2 );
            // MySQL string day.
            $md = substr( $mysql_string, 5, 2 );
            // The timestamp for MySQL string day.
            $day = mktime( 0, 0, 0, $md, $mm, $my );
            // The day of the week from the timestamp.
            $weekday = gmdate( 'w', $day );
            if ( ! is_numeric( $start_of_week ) )
                $start_of_week = $this->_get_option( 'start_of_week' );
            if ( $weekday < $start_of_week ) $weekday += 7;
            // The most recent week start day on or before $day.
            $start = $day - DAY_IN_SECONDS * ( $weekday - $start_of_week );
            // $start + 1 week - 1 second.
            $end = $start + WEEK_IN_SECONDS - 1;
            return compact( 'start', 'end' );
        }//575
        /**
         * @description Serialize data, if needed.
         * @param $data
         * @return string
         */
        protected function _maybe_serialize( $data ):string{
            if ( is_array( $data ) || is_object( $data ) ) return serialize( $data );
            if ( $this->_is_serialized( $data, false ) )
                return serialize( $data );
            return $data;
        }//615
        /**
         * @description Unserialize data only if it was serialized.
         * @param $data
         * @param array ...$classes
         * @return mixed
         */
        protected function _maybe_unserialize( $data, ...$classes ){
            if ( $this->_is_serialized( $data ) ) return $this->_tp_unserialize( trim( $data),['allowed_classes' => [$classes]]);
            return $data;
        }//640
        /**
         * @description Check value to find if it was serialized.
         * @param $data
         * @param bool $strict
         * @return bool
         */
        protected function _is_serialized( $data, $strict = true ):bool{
            // If it isn't a string, it isn't serialized.
            if ( ! is_string( $data ) ) return false;
            $data = trim( $data );
            if ( 'N;' === $data ) return true;
            if ( strlen( $data ) < 4 ) return false;
            if ( ':' !== $data[1] ) return false;
            if ( $strict ) {
                $last_c = substr( $data, -1 );
                if ( ';' !== $last_c && '}' !== $last_c ) return false;
            } else {
                $semicolon = strpos( $data, ';' );
                $brace     = strpos( $data, '}' );
                // Either ; or } must exist.
                if ( false === $semicolon && false === $brace ) return false;
                if ( false !== $semicolon && $semicolon < 3 )return false;
                if ( false !== $brace && $brace < 4 ) return false;
            }
            $token = $data[0];
            switch ( $token ) { //added break
                case 's':
                    if ( $strict ) {
                        if ( '"' !== $data[strlen($data) - 2]) return false;
                    } elseif ( false === strpos( $data, '"' ) ) return false;
                break;
                case 'a':
                case 'O':
                    return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
                break;
                case 'b':
                case 'i':
                case 'd':
                    $end = $strict ? '$' : '';
                    return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$end/", $data );
            }
            return false;
        }//660
        /**
         * @description Check whether serialized data is of string type.
         * @param $data
         * @return bool
         */
        protected function _is_serialized_string( $data ):bool{
            if ( ! is_string( $data ) ) return false;
            $data = trim( $data );
            if ( strlen( $data ) < 4 ) return false;
            elseif ( ':' !== $data[1] )return false;
            elseif ( ';' !== substr( $data, -1 ) ) return false;
            elseif ( 's' !== $data[0] )return false;
            elseif ( '"' !== $data[strlen($data) - 2]) return false;
            else return true;
        }//727
        /**
         * @description Retrieve post title from XMLRPC XML.
         * @param $content
         * @return mixed
         */
        protected function _xml_rpc_get_post_title( $content ){
            if ( preg_match( '/<title>(.+?)<\/title>/is', $content, $match_title ) )
                $post_title = $match_title[1];
            else $post_title = $this->tp_post_default_title;
            return $post_title;
        }//760
        /**
         * @description Retrieve the post category or categories from XMLRPC XML.
         * @param $content
         * @return array|string
         */
        protected function _xml_rpc_get_post_category( $content ){
            if ( preg_match( '/<category>(.+?)<\/category>/is', $content, $match_cat ) ) {
                $post_category = trim( $match_cat[1], ',' );
                $post_category = explode( ',', $post_category );
            } else $post_category = $this->tp_post_default_category;
            return $post_category;
        }//784
        /**
         * @description XMLRPC XML content without title and category elements.
         * @param $content
         * @return mixed|string
         */
        protected function _xml_rpc_remove_post_data( $content ){
            $content = preg_replace( '/<title>(.+?)<\/title>/si', '', $content );
            $content = preg_replace( '/<category>(.+?)<\/category>/si', '', $content );
            $content = trim( $content );
            return $content;
        }//803
        /**
         * @description Use RegEx to extract URLs from arbitrary content.
         * @param $content
         * @return array
         */
        protected function _tp_extract_urls( $content ):array{
            $preg_match = "/#([\"']?)(/";
            $preg_match .= '(?:([\w-]+:)?//?)';
            $preg_match .= '[^\s()<>]+[.](?:';
            $preg_match .= '\([\w\d]+\)|(?:';
            $preg_match .= "[^`!()\[\]{};:'\".,<>«»“”‘’\s]|";
            $preg_match .= '(?:[:]\d+)?/?';
            $preg_match .=")+))\\1#";
            preg_match_all($preg_match,$content,$post_links);
            $post_links = array_unique( array_map( 'html_entity_decode', $post_links[2] ) );
            return array_values( $post_links );
        }//818
    }
}else die;