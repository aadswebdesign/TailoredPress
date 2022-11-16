<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
use TP_Core\Traits\Inits\_init_locale;
if(ABSPATH){
    trait _methods_01{
        use _init_locale;
        /**
         * @description Convert given MySQL date string into a different format.
         * @param $format
         * @param $date
         * @param bool $translate
         * @return bool|int|string
         */
        protected function _mysql2date( $format, $date, $translate = true ){
            if ( empty( $date ) )return false;
            $datetime = date_create( $date, $this->_tp_timezone() );
            if ( false === $datetime ) return false;
            if ( 'G' === $format || 'U' === $format )
                return $datetime->getTimestamp() + $datetime->getOffset();
            if ( $translate )
                return $this->_tp_date( $format, $datetime->getTimestamp() );
            return $datetime->format( $format );
        }//30
        /**
         * @description Retrieves the current time based on specified type.
         * @param $type
         * @param int $gmt
         * @return int|string
         */
        protected function _current_time( $type,$gmt = 0 ){
            if ( 'timestamp' === $type || 'U' === $type )
                return $gmt ? time() : time() + (int)(0 * HOUR_IN_SECONDS);//todo place this '$this->_get_option( 'gmt_offset')' back when db is set
                $timezone = $gmt ? new \DateTimeZone( 'UTC' ) : $this->_tp_timezone();
            $datetime = null;
            if(!($datetime instanceof \DateTime) ){
                $datetime = new \DateTime( 'now', $timezone );
            }
            return $datetime->format( $type );
        }//72
        /**
         * @description  Retrieves the current time as an object using the site's timezone.
         * @return \DateTimeImmutable
         */
        protected function _current_datetime():\DateTimeImmutable{
            return new \DateTimeImmutable( 'now', $this->_tp_timezone() );
        }//95
        /**
         * @description Retrieves the timezone of the site as a string.
         * @return string
         */
        protected function _tp_timezone_string():string{
            $timezone_string = $this->_get_option( 'timezone_string' );
            if ( $timezone_string ) return $timezone_string;
            $offset  = (float) $this->_get_option( 'gmt_offset' );
            $hours   = (int) $offset;
            $minutes = ( $offset - $hours );
            $sign      = ( $offset < 0 ) ? '-' : '+';
            $abs_hour  = abs( $hours );
            $abs_minutes  = abs( $minutes * 60 );
            return sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_minutes );
        }//118
        /**
         * @description Retrieves the timezone of the site as a `DateTimeZone` object.
         * @return \DateTimeZone
         */
        protected function _tp_timezone():\DateTimeZone{
            return new \DateTimeZone( $this->_tp_timezone_string() );
        }//146
        /**
         * @description Retrieves the date in localized format, based on a sum of Unix timestamp
         * @description . and timezone offset in seconds.
         * @param $format
         * @param bool $timestamp_with_offset
         * @param bool $gmt
         * @return int|string
         */
        protected function _date_i18n( $format, $timestamp_with_offset = false, $gmt = false ){
            $timestamp = $timestamp_with_offset;
            if ( ! is_numeric( $timestamp ) )
                $timestamp = $this->_current_time( 'timestamp', $gmt );
            if ( 'U' === $format )
                $date = $timestamp;
            elseif ( $gmt && false === $timestamp_with_offset )
                $date = $this->_tp_date( $format, null, new \DateTimeZone( 'UTC' ) );// Current time in UTC.
            elseif ( false === $timestamp_with_offset )
                $date = $this->_tp_date( $format );// Current time in site's timezone.
            else {
                $local_time = gmdate( 'Y-m-d H:i:s', $timestamp );
                $timezone   = $this->_tp_timezone();
                $datetime   = date_create( $local_time, $timezone );
                $date       = $this->_tp_date( $format, $datetime->getTimestamp(), $timezone );
            }
            $date = $this->_apply_filters( 'date_i18n', $date, $format, $timestamp, $gmt );
            return $date;
        }//175
        /**
         * @description Retrieves the date, in localized format.
         * @param $format
         * @param null $timestamp
         * @param null $timezone
         * @return bool|string
         */
        protected function _tp_date( $format, $timestamp = null, $timezone = null ){
            if ( null === $timestamp ) $timestamp = time();
            elseif ( ! is_numeric( $timestamp )) return false;
            if ( ! $timezone ) $timezone = $this->_tp_timezone();
            $datetime = date_create( '@' . $timestamp );
            $datetime->setTimezone( $timezone );
            if ( empty( $this->_init_locale()->month ) || empty( $this->_init_locale()->weekday ) )
                $date = $datetime->format( $format );
            else {
                $format = preg_replace('/(?<!\\\\)r/', DATE_RFC2822, $format);
                $new_format = '';
                $format_length = strlen($format);
                $month = $this->_init_locale()->get_month($datetime->format('m'));
                $weekday = $this->_init_locale()->get_weekday($datetime->format('w'));
                for ($i = 0; $i < $format_length; $i++) {
                    switch ($format[$i]) {
                        case 'D':
                            $new_format .= addcslashes($this->_init_locale()->get_weekday_abbrev($weekday), '\\A..Za..z');
                            break;
                        case 'F':
                            $new_format .= addcslashes($month, '\\A..Za..z');
                            break;
                        case 'l':
                            $new_format .= addcslashes($weekday, '\\A..Za..z');
                            break;
                        case 'M':
                            $new_format .= addcslashes($this->_init_locale()->get_month_abbrev($month), '\\A..Za..z');
                            break;
                        case 'a':
                            $new_format .= addcslashes($this->_init_locale()->get_meridiem($datetime->format('a')), '\\A..Za..z');
                            break;
                        case 'A':
                            $new_format .= addcslashes($this->_init_locale()->get_meridiem($datetime->format('A')), '\\A..Za..z');
                            break;
                        case '\\':
                            $new_format .= $format[$i];
                            if ($i < $format_length)  $new_format .= $format[++$i];
                            break;
                        default:
                            $new_format .= $format[$i];
                            break;
                    }
                }
                $date = $datetime->format($new_format);
                $date = $this->_tp_maybe_decline_date($date, $format);
            }
            $date = $this->_apply_filters( 'tp_date', $date, $format, $timestamp, $timezone );
            return $date;
        }//240
        /**
         * @description Determines if the date should be declined.
         * @param $date
         * @param string $format
         * @return mixed
         */
        protected function _tp_maybe_decline_date( $date, $format = '' ){
            if ( 'on' === $this->_x( 'off', 'decline months names: on or off' ) ) {
                $months          = $this->_init_locale()->month;
                $months_genitive = $this->_init_locale()->month_genitive;
                if ( $format ) $decline = preg_match( '#[dj]\.? F#', $format );
                else $decline = preg_match( '#\b\d{1,2}\.? [^\d ]+\b#u', $date );
                if ( $decline ) {
                    foreach ( $months as $key => $month )
                        $months[ $key ] = '# ' . preg_quote( $month, '#' ) . '\b#u';
                    foreach ( $months_genitive as $key => $month )
                        $months_genitive[ $key ] = ' ' . $month;
                    $date = preg_replace( $months, $months_genitive, $date );
                }
                if ( $format ) $decline = preg_match( '#F [dj]#', $format );
                else $decline = preg_match( '#\b[^\d ]+ \d{1,2}(st|nd|rd|th)?\b#u', trim( $date ) );
                if ( $decline ) {
                    foreach ( $months as $key => $month )
                        $months[ $key ] = '#\b' . preg_quote( $month, '#' ) . ' (\d{1,2})(st|nd|rd|th)?([-–]\d{1,2})?(st|nd|rd|th)?\b#u';
                    foreach ( $months_genitive as $key => $month ) $months_genitive[ $key ] = '$1$3 ' . $month;
                    $date = preg_replace( $months, $months_genitive, $date );
                }
            }
            $locale = $this->_get_locale();
            if ( 'ca' === $locale )  $date = preg_replace( '# de ([ao])#i', " d'\\1", $date );
            return $date;
        }//335
        /**
         * @description Convert float number to format based on the locale.
         * @param $number
         * @param int $decimals
         * @return mixed
         */
        protected function _number_format_i18n( $number, $decimals = 0 ){
            if ( function_exists('_init_locale') )
                $formatted = number_format( $number, $this->_abs_int( $decimals ), $this->_init_locale()->number_format['decimal_point'], $this->_init_locale()->number_format['thousands_sep'] );
            else $formatted = number_format((int) $number, $this->_abs_int( $decimals ) );
            return $this->_apply_filters( 'number_format_i18n', $formatted, $number, $decimals );
        }//421
        /**
         * @description Convert number of bytes largest unit bytes will fit into.
         * @param $bytes
         * @param int $decimals
         * @return bool|string
         */
        protected function _size_format( $bytes, $decimals = 0 ){
            $quantity =[
                /* translators: Unit symbol for terabyte. */
                $this->_x( 'TB', 'unit symbol' ) => TB_IN_BYTES,
                /* translators: Unit symbol for gigabyte. */
                $this->_x( 'GB', 'unit symbol' ) => GB_IN_BYTES,
                /* translators: Unit symbol for megabyte. */
                $this->_x( 'MB', 'unit symbol' ) => MB_IN_BYTES,
                /* translators: Unit symbol for kilobyte. */
                $this->_x( 'KB', 'unit symbol' ) => KB_IN_BYTES,
                /* translators: Unit symbol for byte. */
                $this->_x( 'B', 'unit symbol' )  => 1,
            ];
            if ( 0 === $bytes )
                return $this->_number_format_i18n( 0, $decimals ) . ' ' . $this->_x( 'B', 'unit symbol' );
            foreach ( $quantity as $unit => $mag ) {
                if ( (float) $bytes >= $mag )
                    return $this->_number_format_i18n( $bytes / $mag, $decimals ) . ' ' . $unit;
            }
            return false;
        }//463
    }
}else die;