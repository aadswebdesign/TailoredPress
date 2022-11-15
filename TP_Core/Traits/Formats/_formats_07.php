<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-2-2022
 * Time: 10:04
 */
namespace TP_Core\Traits\Formats;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_formats;
if(ABSPATH){
    trait _formats_07 {
        use _init_db;
        use _init_formats;
        private $__hours;
        /**
         * @description Given an ISO 8601 (Ymd\TH:i:sO) date, returns a MySQL DateTime (Y-m-d H:i:s) format used by post_date[_gmt].
         * @param $date_string
         * @param string $timezone
         * @return bool
         */
        protected function _iso8601_to_datetime( $date_string, $timezone = 'user' ){
            $timezone    = strtolower( $timezone );
            $tp_timezone = $this->_tp_timezone();
            $datetime    = date_create( $date_string, $tp_timezone ); // Timezone is ignored if input has one.
            if ( false === $datetime ) return false;
            if ( 'gmt' === $timezone ) return $datetime->setTimezone( new \DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
            if ( 'user' === $timezone ) return $datetime->setTimezone( $tp_timezone )->format( 'Y-m-d H:i:s' );
            return false;
        }//3633
        /**
         * @description Strips out all characters that are not allowable in an email.
         * @param $email
         * @return mixed
         */
        protected function _sanitize_email( $email ){
            if ( strlen( $email ) < 6 ) return $this->_apply_filters( 'sanitize_email', '', $email, 'email_too_short' );
            if ( strpos( $email, '@', 1 ) === false ) return $this->_apply_filters( 'sanitize_email', '', $email, 'email_no_at' );
            @list( $local, $domain ) = explode( '@', $email, 2 );
            $local = preg_replace( '/[^a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~\.-]/', '', $local );
            if ( '' === $local )return $this->_apply_filters( 'sanitize_email', '', $email, 'local_invalid_chars' );
            $domain = preg_replace( '/\.{2,}/', '', $domain );
            if ( '' === $domain ) return $this->_apply_filters( 'sanitize_email', '', $email, 'domain_period_sequence' );
            $domain = trim( $domain, " \t\n\r\0\x0B." );
            if ( '' === $domain ) return $this->_apply_filters( 'sanitize_email', '', $email, 'domain_period_limits' );
            $subs = explode( '.', $domain );
            if ( 2 > count( $subs ) ) return $this->_apply_filters( 'sanitize_email', '', $email, 'domain_no_periods' );
            $new_subs = [];
            foreach ( $subs as $sub ) {
                $sub = trim( $sub, " \t\n\r\0\x0B-" );
                $sub = preg_replace( '/[^a-z0-9-]+/i', '', $sub );
                if ( '' !== $sub ) $new_subs[] = $sub;
            }
            if ( 2 > count( $new_subs ) ) return $this->_apply_filters( 'sanitize_email', '', $email, 'domain_no_valid_subs' );
            $domain = implode( '.', $new_subs );
            $sanitized_email = $local . '@' . $domain;
            return $this->_apply_filters( 'sanitize_email', $sanitized_email, $email);
        }//3661
        /**
         * @description The difference is returned in a human readable format such as "1 hour",
         * @param $from
         * @param int $to
         * @return mixed
         */
        protected function _human_time_diff( $from, $to = 0 ){
            if ( empty( $to ) ) $to = time();
            $diff = (int) abs( $to - $from );
            $since = null;
            if ( $diff < MINUTE_IN_SECONDS ) {
                $secs = $diff;
                if ( $secs <= 1 )  $secs = 1;
                $since = sprintf( $this->_n( '%s second', '%s seconds', $secs ), $secs );
            } elseif ( $diff < DAY_IN_SECONDS && $diff >= HOUR_IN_SECONDS ) {
                $this->__hours = round( $diff / HOUR_IN_SECONDS );
                if ( $this->__hours <= 1 ) $this->__hours = 1;
            }elseif ( $diff < DAY_IN_SECONDS && $diff >= HOUR_IN_SECONDS ) {
                $this->__hours = round( $diff / HOUR_IN_SECONDS );
                if ( $this->__hours <= 1 ) $this->__hours = 1;
                $since = sprintf( $this->_n( '%s hour', '%s hours', $this->__hours ), $this->__hours );
            }elseif ( $diff < WEEK_IN_SECONDS && $diff >= DAY_IN_SECONDS ) {
                $days = round( $diff / DAY_IN_SECONDS );
                if ( $days <= 1 ) $days = 1;
                $since = sprintf( $this->_n( '%s day', '%s days', $days ), $days );
            } elseif ( $diff < MONTH_IN_SECONDS && $diff >= WEEK_IN_SECONDS ) {
                $weeks = round( $diff / WEEK_IN_SECONDS );
                if ( $weeks <= 1 ) $weeks = 1;
                $since = sprintf( $this->_n( '%s week', '%s weeks', $weeks ), $weeks );
            } elseif ( $diff < YEAR_IN_SECONDS && $diff >= MONTH_IN_SECONDS ) {
                $months = round( $diff / MONTH_IN_SECONDS );
                if ( $months <= 1 )  $months = 1;
                $since = sprintf( $this->_n( '%s month', '%s months', $months ), $months );
            } elseif ( $diff >= YEAR_IN_SECONDS ) {
                $years = round($diff / YEAR_IN_SECONDS);
                if ($years <= 1) $years = 1;
                $since = sprintf( $this->_n( '%s year', '%s years', $years ), $years );
            }
            return $this->_apply_filters( 'human_time_diff', $since, $diff, $from, $to );
        }//3768
        /**
         * @description Generates an excerpt from the content, if needed.
         * @param string $text
         * @param null $post
         * @return mixed
         */
        protected function _tp_trim_excerpt( $text = '', $post = null ){
            $raw_excerpt = $text;
            if ( '' === trim( $text ) ) {
                $post = $this->_get_post( $post );
                $text = $this->_get_the_content( '', false, $post );
                $text = $this->_strip_shortcodes( $text );
                $text = $this->_excerpt_remove_blocks( $text );
                /** This filter is documented in wp-includes/post-template.php */
                $text = $this->_apply_filters( 'the_content', $text );
                $text = (string) str_replace( ']]>', ']]&gt;', $text );
                $excerpt_length = (int) $this->_x( '55', 'excerpt_length' );
                $excerpt_length = (int) $this->_apply_filters( 'excerpt_length', $excerpt_length );
                $excerpt_more = $this->_apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
                $text         = $this->_tp_trim_words( $text, $excerpt_length, $excerpt_more );
            }
            return $this->_apply_filters( 'tp_trim_excerpt', $text, $raw_excerpt );
        }//3854
        /**
         * @description Trims text to a certain number of words.
         * @param $text
         * @param int $num_words
         * @param null $more
         * @return mixed
         */
        protected function _tp_trim_words( $text, $num_words = 55, $more = null ){
            if ( null === $more ) $more = $this->__( '&hellip;' );
            $original_text = $text;
            $text          = $this->_tp_strip_all_tags( $text );
            $num_words     = (int) $num_words;
            if ( strpos( $this->__( 'words', 'Word count type. Do not translate!' ), 'characters' ) === 0 && preg_match( '/^utf\-?8$/i', $this->_get_option( 'blog_charset' ) ) ) {
                $text = trim( preg_replace( "/[\n\r\t ]+/", ' ', $text ), ' ' );
                preg_match_all( '/./u', $text, $words_array );
                $words_array = array_slice( $words_array[0], 0, $num_words + 1 );
                $sep         = '';
            } else {
                $words_array = preg_split( "/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY );
                $sep         = ' ';
            }
            if ( count( $words_array ) > $num_words ) {
                array_pop( $words_array );
                $text = implode( $sep, $words_array );
                $text .= $more;
            } else
                $text = implode( $sep, $words_array );
            return $this->_apply_filters( 'tp_trim_words', $text, $num_words, $more, $original_text );
        }//3916
        /**
         * @description Converts named entities into numbered entities.
         * @param $text
         * @return mixed
         */
        protected function _ent2ncr( $text ){
            $filtered = $this->_apply_filters( 'pre_ent2ncr', null, $text );
            if ( null !== $filtered ) return $filtered;
            return str_replace( array_keys( $this->_chars_strings_to_numeric ), array_values( $this->_chars_strings_to_numeric ), $text );
        }//3969
        /**
         * @description Formats text for the editor.
         * @param $text
         * @param null $default_editor
         * @return mixed
         */
        protected function _format_for_editor( $text, $default_editor = null ){
            if ( $text ) $text = htmlspecialchars( $text, ENT_NOQUOTES, $this->_get_option( 'blog_charset' ) );
            return $this->_apply_filters( 'format_for_editor', $text, $default_editor );
        }//4266
        /**
         * @description Perform a deep string replace operation to ensure the values in $search are no longer present
         * @param $search
         * @param $subject
         * @return mixed
         */
        protected function _deep_replace( $search, $subject ){
            $subject = (string) $subject;
            $count = 1;
            while ( $count ) $subject = str_replace( $search, '', $subject, $count );
            return $subject;
        }//4298
        /**
         * @description Escapes data for use in a MySQL query.
         * @param $data
         * @return array
         */
        protected function _esc_sql( $data ){
            return $this->_init_db()->escape( $data );
        }//4328
        /**
         * @description Checks and cleans a URL.
         * @param $url
         * @param null $protocols
         * @param string $_context
         * @return mixed
         */
        protected function _esc_url( $url, $protocols = null, $_context = 'display' ){
            $original_url = $url;
            if ( '' === $url ) return $url;
            $url = str_replace( ' ', '%20', ltrim( $url ) );
            $url = preg_replace( '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url );
            if ( '' === $url ) return $url;
            if ( 0 !== stripos( $url, 'mailto:' ) ) {
                $strip = array( '%0d', '%0a', '%0D', '%0A' );
                $url   = $this->_deep_replace( $strip, $url );
            }
            $url = str_replace( ';//', '://', $url );
            if ( strpos( $url, ':' ) === false && ! in_array( $url[0], array( '/', '#', '?' ), true ) &&
                ! preg_match( '/^[a-z0-9-]+?\.php/i', $url ) ) {
                //todo implementing https
                //$protocol = 'https://' ?: 'http//';
                $url = 'http://' . $url;
            }
            if ( 'display' === $_context ) {
                $url = $this->_tp_kses_normalize_entities( $url );
                $url = str_replace(['&amp;', "'"],['&#038;', '&#039;'], $url);
            }
            if ( ( false !== strpos( $url, '[' ) ) || ( false !== strpos( $url, ']' ) ) ) {
                $parsed = $this->_tp_parse_url( $url );
                $front  = '';
                if ( isset( $parsed['scheme'] ) ) $front .= $parsed['scheme'] . '://';
                elseif ( '/' === $url[0] ) $front .= '//';
                if ( isset( $parsed['user'] ) ) $front .= $parsed['user'];
                if ( isset( $parsed['pass'] ) ) $front .= ':' . $parsed['pass'];
                if ( isset( $parsed['user'] ) || isset( $parsed['pass'] ) ) $front .= '@';
                if ( isset( $parsed['host'] ) ) $front .= $parsed['host'];
                if ( isset( $parsed['port'] ) ) $front .= ':' . $parsed['port'];
                $end_dirty = str_replace( $front, '', $url );
                $end_clean = str_replace( array( '[', ']' ), array( '%5B', '%5D' ), $end_dirty );
                $url       = str_replace( $end_dirty, $end_clean, $url );
            }
            if ( '/' === $url[0] ) $good_protocol_url = $url;
            else {
                if ( ! is_array( $protocols ) ) $protocols = $this->_tp_allowed_protocols();
                $good_protocol_url = $this->_tp_kses_bad_protocol( $url, $protocols );
                if ( strtolower( $good_protocol_url ) !== strtolower( $url ) ) return '';
            }
            return $this->_apply_filters( 'clean_url', $good_protocol_url, $original_url, $_context );
        }//4350
   }
}else die;