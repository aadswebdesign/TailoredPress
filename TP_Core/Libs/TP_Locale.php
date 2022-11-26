<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-4-2022
 * Time: 13:34
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\I10n\_I10n_05;
if(ABSPATH){
    class TP_Locale{
        use _formats_04;
        use _I10n_01,_I10n_02,_I10n_03,_I10n_04;
        use _I10n_05;
        public $meridiem;
        public $month;
        public $month_abbrev;
        public $month_genitive;
        public $number_format;
        public $text_direction = 'ltr';
        public $weekday;
        public $weekday_abbrev;
        public $weekday_initial;
        //added todo give it a meaning
        public $year;
        public function __construct() {
            $this->init();
        }//103
        public function init():void {
            // The weekdays. /* translators: Weekday. */
            $this->weekday[0] = $this->__( 'Sunday' );
            $this->weekday[1] = $this->__( 'Monday' );
            $this->weekday[2] = $this->__( 'Tuesday' );
            $this->weekday[3] = $this->__( 'Wednesday' );
            $this->weekday[4] = $this->__( 'Thursday' );
            $this->weekday[5] = $this->__( 'Friday' );
            $this->weekday[6] = $this->__( 'Saturday' );
            // The first letter of each day. /* translators: One-letter abbreviation of the weekday. */
            $this->weekday_initial[ $this->__( 'Sunday' ) ]    = $this->_x( 'S', 'Sunday initial' );
            $this->weekday_initial[ $this->__( 'Monday' ) ]    = $this->_x( 'M', 'Monday initial' );
            $this->weekday_initial[ $this->__( 'Tuesday' ) ]   = $this->_x( 'T', 'Tuesday initial' );
            $this->weekday_initial[ $this->__( 'Wednesday' ) ] = $this->_x( 'W', 'Wednesday initial' );
            $this->weekday_initial[ $this->__( 'Thursday' ) ]  = $this->_x( 'T', 'Thursday initial' );
            $this->weekday_initial[ $this->__( 'Friday' ) ]    = $this->_x( 'F', 'Friday initial' );
            $this->weekday_initial[ $this->__( 'Saturday' ) ]  = $this->_x( 'S', 'Saturday initial' );

            // Abbreviations for each day. /* translators: Three-letter abbreviation of the weekday. */
            $this->weekday_abbrev[ $this->__( 'Sunday' ) ]    = $this->__( 'Sun' );
            $this->weekday_abbrev[ $this->__( 'Monday' ) ]    = $this->__( 'Mon' );
            $this->weekday_abbrev[ $this->__( 'Tuesday' ) ]   = $this->__( 'Tue' );
            $this->weekday_abbrev[ $this->__( 'Wednesday' ) ] = $this->__( 'Wed' );
            $this->weekday_abbrev[ $this->__( 'Thursday' ) ]  = $this->__( 'Thu' );
            $this->weekday_abbrev[ $this->__( 'Friday' ) ]    = $this->__( 'Fri' );
            $this->weekday_abbrev[ $this->__( 'Saturday' ) ]  = $this->__( 'Sat' );

            // The months.  /* translators: Month name. */
            $this->month['01'] = $this->__( 'January' );
            $this->month['02'] = $this->__( 'February' );
            $this->month['03'] = $this->__( 'March' );
            $this->month['04'] = $this->__( 'April' );
            $this->month['05'] = $this->__( 'May' );
            $this->month['06'] = $this->__( 'June' );
            $this->month['07'] = $this->__( 'July' );
            $this->month['08'] = $this->__( 'August' );
            $this->month['09'] = $this->__( 'September' );
            $this->month['10'] = $this->__( 'October' );
            $this->month['11'] = $this->__( 'November' );
            $this->month['12'] = $this->__( 'December' );

            // The months, genitive. /* translators: Month name, genitive. */
            $this->month_genitive['01'] = $this->_x( 'January', 'genitive' );
            $this->month_genitive['02'] = $this->_x( 'February', 'genitive' );
            $this->month_genitive['03'] = $this->_x( 'March', 'genitive' );
            $this->month_genitive['04'] = $this->_x( 'April', 'genitive' );
            $this->month_genitive['05'] = $this->_x( 'May', 'genitive' );
            $this->month_genitive['06'] = $this->_x( 'June', 'genitive' );
            $this->month_genitive['07'] = $this->_x( 'July', 'genitive' );
            $this->month_genitive['08'] = $this->_x( 'August', 'genitive' );
            $this->month_genitive['09'] = $this->_x( 'September', 'genitive' );
            $this->month_genitive['10'] = $this->_x( 'October', 'genitive' );
            $this->month_genitive['11'] = $this->_x( 'November', 'genitive' );
            $this->month_genitive['12'] = $this->_x( 'December', 'genitive' );
            // Abbreviations for each month. /* translators: Three-letter abbreviation of the month. */
            $this->month_abbrev[ $this->__( 'January' ) ]   = $this->_x( 'Jan', 'January abbreviation' );
            $this->month_abbrev[ $this->__( 'February' ) ]  = $this->_x( 'Feb', 'February abbreviation' );
            $this->month_abbrev[ $this->__( 'March' ) ]     = $this->_x( 'Mar', 'March abbreviation' );
            $this->month_abbrev[ $this->__( 'April' ) ]     = $this->_x( 'Apr', 'April abbreviation' );
            $this->month_abbrev[ $this->__( 'May' ) ]       = $this->_x( 'May', 'May abbreviation' );
            $this->month_abbrev[ $this->__( 'June' ) ]      = $this->_x( 'Jun', 'June abbreviation' );
            $this->month_abbrev[ $this->__( 'July' ) ]      = $this->_x( 'Jul', 'July abbreviation' );
            $this->month_abbrev[ $this->__( 'August' ) ]    = $this->_x( 'Aug', 'August abbreviation' );
            $this->month_abbrev[ $this->__( 'September' ) ] = $this->_x( 'Sep', 'September abbreviation' );
            $this->month_abbrev[ $this->__( 'October' ) ]   = $this->_x( 'Oct', 'October abbreviation' );
            $this->month_abbrev[ $this->__( 'November' ) ]  = $this->_x( 'Nov', 'November abbreviation' );
            $this->month_abbrev[ $this->__( 'December' ) ]  = $this->_x( 'Dec', 'December abbreviation' );
            // The meridiem-s.
            $this->meridiem['am'] = $this->__( 'am' );
            $this->meridiem['pm'] = $this->__( 'pm' );
            $this->meridiem['AM'] = $this->__( 'AM' );
            $this->meridiem['PM'] = $this->__( 'PM' );
            // Numbers formatting.
            // See https://www.php.net/number_format
            /* translators: $thousands_sep argument for https://www.php.net/number_format, default is ',' */
            $thousands_sep = $this->__( 'number_format_thousands_sep' );
            // Replace space with a non-breaking space to avoid wrapping.
            $thousands_sep = str_replace( ' ', '&nbsp;', $thousands_sep );
            $this->number_format['thousands_sep'] = ( 'number_format_thousands_sep' === $thousands_sep ) ? ',' : $thousands_sep;
            /* translators: $dec_point argument for https://www.php.net/number_format, default is '.' */
            $decimal_point = $this->__( 'number_format_decimal_point' );
            $this->number_format['decimal_point'] = ( 'number_format_decimal_point' === $decimal_point ) ? '.' : $decimal_point;
            // Set text direction.
            if ( isset( $GLOBALS['text_direction'] ) )
                $this->text_direction = $GLOBALS['text_direction'];
            elseif ( 'rtl' === $this->_x( 'ltr', 'text direction' ) )
                $this->text_direction = 'rtl';
        }//120
        public function get_weekday( $weekday_number ) {
            return $this->weekday[ $weekday_number ];
        }//234
        public function get_weekday_initial( $weekday_name ) {
            return $this->weekday_initial[ $weekday_name ];
        }//251
        public function get_weekday_abbrev( $weekday_name ) {
            return $this->weekday_abbrev[ $weekday_name ];
        }//268
        public function get_month( $month_number ) {
            return $this->month[ $this->_zero_ise( $month_number, 2 ) ];
        }//286
        public function get_month_abbrev( $month_name ) {
            return $this->month_abbrev[ $month_name ];
        }//301
        public function get_meridiem( $meridiem ) {
            return $this->meridiem[ $meridiem ];
        }//315
        public function is_rtl():bool {
            return 'rtl' === $this->text_direction;
        }//348
        public function _strings_for_pot():void {
            /* translators: Localized date format, see https://www.php.net/manual/datetime.format.php */
            $this->__( 'F j, Y' );
            /* translators: Localized time format, see https://www.php.net/manual/datetime.format.php */
            $this->__( 'g:i a' );
            /* translators: Localized date and time format, see https://www.php.net/manual/datetime.format.php */
            $this->__( 'F j, Y g:i a' );
        }//368
    }
}else die;