<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 17:17
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_locale;
use TP_Core\Libs\TP_Locale;
if(ABSPATH){
    trait _general_template_05 {
        use _init_db;
        use _init_locale;
        /**
         * @description Get number of days since the start of the week.
         * @param $num
         * @return mixed
         */
        protected function _calendar_week_mod( $num ){
            $base = 7;
            return ( $num - $base * floor( $num / $base ) );
        }//2202 from general-template
        /**
         * @description Display calendar with days that have posts as links.
         * @param bool $initial
         * @return string
         */
        protected function _get_calendar( $initial = true):string{
            $tpdb = $this->_init_db();
            $tp_locale = $this->_init_locale();
            $key   = md5( $this->tp_month . $this->tp_month_number . $this->tp_year ); //todo looking for a safer way as md5
            $cache = $this->_tp_cache_get( 'get_calendar', 'calendar' );
            if ( $cache && is_array( $cache ) && isset( $cache[ $key ] ) ) {
                return $this->_apply_filters( 'get_calendar', $cache[ $key ] );
            }
            if ( ! is_array( $cache ) ) $cache = [];
            if ( ! $this->tp_posts ) {
                $got_some = $tpdb->get_var( TP_SELECT . " 1 as test FROM $tpdb->posts WHERE post_type = 'post' AND post_status = 'publish' LIMIT 1" );
                if ( ! $got_some ) {
                    $cache[ $key ] = '';
                    $this->_tp_cache_set( 'get_calendar', $cache, 'calendar' );
                    return null;
                }
            }
            if ( isset( $_GET['w'] ) ) $w = (int) $_GET['w'];
            $week_begins = (int) $this->_get_option( 'start_of_week' );
            if ( ! empty( $this->tp_month_number ) && ! empty( $this->tp_year ) ) {
                $this_month = $this->_zero_ise( (int) $this->tp_month_number, 2 );
                $this_year  = (int) $this->tp_year;
            } elseif ( ! empty( $w ) ) {
                $this_year = (int) substr( $this->tp_month, 0, 4 );
                $d         = ( ( $w - 1 ) * 7 ) + 6;
                $this_month = $tpdb->get_var( TP_SELECT . " DATE_FORMAT((DATE_ADD('{$this_year}0101', INTERVAL $d DAY) ), '%m')" );
            } elseif ( ! empty( $this->tp_month ) ) {
                $this_year = (int) substr( $this->tp_month, 0, 4 );
                if ( strlen( $this->tp_month ) < 6 ) {
                    $this_month = '01';
                } else {
                    $this_month = $this->_zero_ise( (int) substr( $this->tp_month, 4, 2 ), 2 );
                }
            } else {
                $this_year  = $this->_current_time( 'Y' );
                $this_month = $this->_current_time( 'm' );
            }
            $unix_month = mktime( 0, 0, 0, $this_month, 1, $this_year );
            $last_day  = gmdate( 't', $unix_month );
            // Get the next and previous month and year with at least one post.
            $previous = $tpdb->get_row(TP_SELECT . " MONTH(post_date) AS month, YEAR(post_date) AS year FROM $tpdb->posts WHERE post_date < '$this_year-$this_month-01' AND post_type = 'post' AND post_status = 'publish' ORDER BY post_date DESC LIMIT 1" );
            $next     = $tpdb->get_row(TP_SELECT . " MONTH(post_date) AS month, YEAR(post_date) AS year FROM $tpdb->posts WHERE post_date > '$this_year-$this_month-{$last_day} 23:59:59' AND post_type = 'post' AND post_status = 'publish' ORDER BY post_date ASC LIMIT 1");
            //todo modify this as a nice details/summary block
            $calendar_caption = $this->_x( '%1$s %2$s', 'calendar caption' );
            $calendar_output  = "<table id='tp_calendar' class='tp-calendar table'><caption></caption>" . sprintf(
                    $calendar_caption, $tp_locale->get_month( $this_month ), gmdate( 'Y', $unix_month )) . '</caption><thead><tr>';
            $my_week = [];
            for ( $week_count = 0; $week_count <= 6; $week_count++ )
                $my_week[] = $tp_locale->get_weekday( ( $week_count + $week_begins ) % 7 );
            foreach ( $my_week as $week_day ) {
                $day_name         = $initial ? $tp_locale->get_weekday_initial( $week_day ) : $tp_locale->get_weekday_abbrev( $week_day );
                $week_day         = $this->_esc_attr( $week_day );
                $calendar_output .= "\n\t\t<th scope='col' title='$week_day'>$day_name</th>";
            }
            $nbsp = '&nbsp;';
            $laquo = '&laquo;';
            $raquo = '&raquo;';
            $calendar_output .= "</tr></thead><tbody><tr>'";
            $day_with_post = [];
            $days_with_posts = $tpdb->get_results(TP_SELECT . " DISTINCT DAYOFMONTH(post_date) FROM $tpdb->posts WHERE post_date >= '{$this_year}-{$this_month}-01 00:00:00' AND post_type = 'post' AND post_status = 'publish' AND post_date <= '{$this_year}-{$this_month}-{$last_day} 23:59:59'", ARRAY_N );
            if ( $days_with_posts ) {
                foreach ( (array) $days_with_posts as $day_with ) $day_with_post[] = (int) $day_with[0];
            }
            $pad = $this->_calendar_week_mod( gmdate( 'w', $unix_month ) - $week_begins );
            if ( 0 !== $pad ) $calendar_output .= "\n\t\t<td colspan='{$this->_esc_attr( $pad )}' class='pad'>{$nbsp}</td>";
            $new_row      = false;
            $days_in_month = (int) gmdate( 't', $unix_month );
            for ( $day = 1; $day <= $days_in_month; ++$day ) {
                if ( isset( $new_row ) && $new_row ) $calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
                $new_row = false;
                if ( $this->_current_time( 'j' ) === $day &&
                    $this->_current_time( 'm' ) === $this_month &&
                    $this->_current_time( 'Y' ) === $this_year ) {
                    $calendar_output .= "<td id='today'>";
                } else $calendar_output .= '<td>';
                if ( in_array( $day, $day_with_post, true ) ) {
                    $date_format = gmdate( $this->_x( 'F j, Y', 'daily archives date format' ), strtotime( "{$this_year}-{$this_month}-{$day}" ) );
                    $label            = sprintf( $this->__( 'Posts published on %s' ), $date_format );
                    $calendar_output .= sprintf("<a href='%s' aria-label='%s'>%s</a>",
                        $this->_get_day_link( $this_year, $this_month, $day ),$this->_esc_attr( $label ),$day);
                } else  $calendar_output .= $day;
                $calendar_output .= '</td>';
                if ( 6 === $this->_calendar_week_mod( gmdate( 'w', mktime( 0, 0, 0, $this_month, $day, $this_year ) ) - $week_begins ) )
                    $new_row = true;
            }
            $pad = 7 - $this->_calendar_week_mod( gmdate( 'w', mktime( 0, 0, 0, $this_month, $day, $this_year ) ) - $week_begins );
            if ( 0 !== $pad && 7 !== $pad )
                $calendar_output .= "\n\t\t<td class='pad' colspan='{$this->_esc_attr( $pad )}'>{$nbsp}</td>";
            $calendar_output .= "\n\t</tr>\n\t</tbody>";
            $calendar_output .= "\n\t</table>";
            $calendar_output .= "<nav class='tp-calendar-nav' aria-label='{$this->__( 'Previous and next months' )}'>";
            if ( $previous && ($previous instanceof TP_Locale)) {
                $calendar_output .= "\n\t\t<span class='tp-calendar-nav-prev'><a href='{$this->_get_month_link( $previous->year, $previous->month )}'>$laquo{$tp_locale->get_month_abbrev( $tp_locale->get_month( $previous->month ) )}</a></span>";
            } else $calendar_output .= "\n\t\t<span class='tp-calendar-nav-prev'>$nbsp</span>";
            $calendar_output .= "\n\t\t<span class='pad'>$nbsp</span>";
            if ( $next && ($next instanceof TP_Locale) ) {
                $calendar_output .= "\n\t\t<span class='tp-calendar-nav-next'><a href='{$this->_get_month_link( $next->year, $next->month )}'>{$tp_locale->get_month_abbrev( $tp_locale->get_month( $next->month ) )}$raquo</a></span>";
            } else $calendar_output .= "\n\t\t<span class='tp-calendar-nav-next'>$nbsp</span>";
            $calendar_output .= "</nav>";
            $cache[ $key ] = $calendar_output;
            $this->_tp_cache_set( 'get_calendar', $cache, 'calendar' );
            return $this->_apply_filters( 'get_calendar', $calendar_output );
        }//2226 from general-template
        protected function _print_calendar( $initial = true):void{
            echo $this->_get_calendar( $initial);
        }//2226 from general-template
        protected function _delete_get_calendar_cache():void{
            $this->_tp_cache_delete( 'get_calendar', 'calendar' );
        }//2454 from general-template
        /**
         * @description Display all of the allowed tags in HTML format with attributes.
         * @return string
         */
        protected function _allowed_tags():string{
            $allowed = '';
            foreach ( (array) $this->allowed_tags as $tag => $attributes ) {
                $allowed .= "<{$tag}";
                if ( 0 < count( $attributes ) ) {
                    foreach ( $attributes as $attribute => $limits ) $allowed .= " {$attribute}=''";
                }
                $allowed .= '> ';
            }
            return htmlentities( $allowed );
        }//2470 from general-template
        /**
         * @description Outputs the date in iso8601 format for xml files.
         * @return string
         */
        protected function _get_the_date_xml():string{
            return $this->_mysql2date( 'Y-m-d', $this->_get_post()->post_date, false );
        }//2492 from general-template
        protected function _the_date_xml():void{
            echo $this->_get_the_date_xml();
        }
        /**
         * @description Display or Retrieve the date the current post was written (once per date)
         * @param string $format
         * @param string $before
         * @param string $after
         * @return string
         */
        protected function _get_the_assembled_date( $format = '', $before = '', $after = ''):string{
            $the_date = '';
            if ( $this->_is_new_day() ) {
                $the_date    = $before . $this->_get_the_date( $format ) . $after;
                $this->tp_previous_day = $this->tp_current_day;
            }
            $the_date = $this->_apply_filters( 'the_date', $the_date, $format, $before, $after );
            return $the_date;
        }//2519 from general-template
        protected function _print_the_assembled_date( $format = '', $before = '', $after = ''):void{
            echo $this->_get_the_assembled_date( $format, $before, $after);
        }//2519 from general-template
        /**
         * @description Retrieve the date on which the post was written.
         * @param string $format
         * @param null $post
         * @return bool
         */
        protected function _get_the_date($format = '', $post = null):bool{
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $_format = ! empty( $format ) ? $format : $this->_get_option( 'date_format' );
            $the_date = $this->_get_post_time( $_format, false, $post, true );
            return $this->_apply_filters( 'get_the_date', $the_date, $format, $post );
        }//2560 from general-template
        /**
         * @description Display the date on which the post was last modified.
         * @param string $format
         * @param string $before
         * @param string $after
         * @return string
         */
        protected function _get_the_assembled_modified_date( $format = '', $before = '', $after = ''):string{
            $the_modified_date = $before . $this->_get_the_modified_date( $format ) . $after;
            return $this->_apply_filters( 'the_modified_date',$the_modified_date, $format, $before, $after );
        }//2594 from general-template
        protected function _print_the_assembled_modified_date( $format = '', $before = '', $after = ''):void{
            echo $this->_get_the_assembled_modified_date( $format, $before, $after);
        }//2594 from general-template
        /**
         * @description Retrieve the date on which the post was last modified.
         * @param string $format
         * @param null $post
         * @return mixed
         */
        protected function _get_the_modified_date( $format = '', $post = null ){
            $post = $this->_get_post( $post );
            $_format = ! empty( $format ) ? $format : $this->_get_option( 'date_format' );
            $the_time = $this->_get_post_modified_time( $_format, false, $post, true );
            return $this->_apply_filters( 'get_the_modified_date', $the_time, $format, $post );
        }//2627 from general-template
        /**
         * @description Display the time at which the post was written.
         * @param string $format
         */
        public function the_time( $format = '' ):void{
            echo $this->_apply_filters( 'the_time', $this->_get_the_time( $format ), $format );
        }//2661 from general-template
    }
}else die;