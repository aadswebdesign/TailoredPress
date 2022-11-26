<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 21:44
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Parse_Date{
        use _sp_vars;
        use _parse_date;
        public function __construct(){
            $this->_sp_day_pcre = '(' . implode('|', array_keys($this->_sp_day)) . ')';
            $this->_sp_month_pcre = '(' . implode('|', array_keys($this->_sp_month)) . ')';
            if (!isset($this->_sp_cache[get_class($this)])) {
                $all_methods = get_class_methods($this);
                foreach ($all_methods as $method){
                    if (stripos($method, 'date_') === 0)
                        $this->_sp_cache[get_class($this)][] = $method;
                }
            }
            foreach ($this->_sp_cache[get_class($this)] as $method)
                $this->__sp_built_in[] = $method;
        }
        /**
         * @description Parse a date
         * @final
         * @access public
         * @param string $date Date to parse
         * @return int Timestamp corresponding to date string, or false on failure
         */
        public function parse($date):int {
            foreach ($this->__sp_user as $method){
                if (($returned = $method($date)) !== false)
                    return $returned;
            }
            foreach ($this->__sp_built_in as $method){
                if (($returned = $this->$method($date)) !== false)
                    return $returned;
            }
            return false;
        }
        /**
         * @descriptionAdd a callback method to parse a date
         * @final
         * @access public
         * @param callback $callback
         */
        public function add_callback($callback):void{
            if (is_callable($callback)) $this->__sp_user[] = $callback;
            else trigger_error('User-supplied function must be a valid callback', E_USER_WARNING);
        }
        /*
            Capturing sub_patterns:
            1: Year
            2: Month
            3: Day
            4: Hour
            5: Minute
            6: Second
            7: Decimal fraction of a second
            8: Zulu
            9: Timezone ±
            10: Timezone hours
            11: Timezone minutes
        */
        /**
         * @description Parse a superset of W3C-DTF (allows hyphens and colons to be omitted, as
         * @description . well as allowing any of upper or lower case "T", horizontal tabs, or
         * @description . spaces to be used as the time separator (including more than one))
         * @param $date
         * @access protected
         * @return int Timestamp
         */
        public function date_w3c_dtf($date):int{
            if (!$this->sp_pcre){
                $year = '([0-9]{4})';
                $month = $day = $hour = $minute = $second = '([0-9]{2})';
                $decimal = '([0-9]*)';
                $zone = '(?:(Z)|([+\-])([0-9]{1,2}):?([0-9]{1,2}))';
                $this->sp_pcre = '/^' . $year . '(?:-?' . $month . '(?:-?' . $day . '(?:[Tt\x09\x20]+' . $hour . '(?::?' . $minute . '(?::?' . $second . '(?:.' . $decimal . ')?)?)?' . $zone . ')?)?)?$/';
            }
            if (preg_match($this->sp_pcre, $date, $match)) {
                for ($i = count($match); $i <= 3; $i++) $match[$i] = '1';
                for ($i = count($match); $i <= 7; $i++) $match[$i] = '0';
                if (isset($match[9]) && $match[9] !== '') {
                    $timezone = $match[10] * 3600;
                    $timezone += $match[11] * 60;
                    if ($match[9] === '-') $timezone = 0 - $timezone;
                } else $timezone = 0;
                $second = round((int)$match[6] + (int)$match[7] / (10 ** strlen($match[7])));
                return gmmktime($match[4], $match[5], $second, $match[2], $match[3], $match[1]) - $timezone;
            }
            return false;
        }//691
        /**
         * @description Remove RFC822 comments
         * @access protected
         * @param string $string
         * @return string Comment stripped string
         */
        protected function _remove_rfc2822_comments($string):string{
            $string = (string) $string;
            $position = 0;
            $length = strlen($string);
            $depth = 0;
            $output = '';
            while ($position < $length && ($pos = strpos($string, '(', $position)) !== false){
                $output .= substr($string, $position, $pos - $position);
                $position = $pos + 1;
                if ($pos === 0 || $string[$pos - 1] !== '\\'){
                    $depth++;
                    while ($depth && $position < $length){
                        $position += strcspn($string, '()', $position);
                        if ($string[$position - 1] === '\\') {
                            $position++;
                            continue;
                        }
                        if (isset($string[$position])) {
                            switch ($string[$position]){
                                case '(':
                                    $depth++;
                                    break;
                                case ')':
                                    $depth--;
                                    break;
                            }
                            $position++;
                        } else break;
                    }
                }
                else $output .= '(';
            }
            $output .= substr($string, $position);
            return $output;
        }
        /**
         * @description Parse RFC2822's date format
         * @param $date
         * @access protected
         * @return int Timestamp
         */
        protected function _date_rfc2822($date):int{
            if (!$this->sp_pcre){
                $wsp = '[\x09\x20]';
                $fws = '(?:' . $wsp . '+|' . $wsp . '*(?:\x0D\x0A' . $wsp . '+)+)';
                $optional_fws = $fws . '?';
                $day_name = $this->_sp_day_pcre;
                $month = $this->_sp_month_pcre;
                $day = '([0-9]{1,2})';
                $hour = $minute = $second = '([0-9]{2})';
                $year = '([0-9]{2,4})';
                $num_zone = '([+\-])([0-9]{2})([0-9]{2})';
                $character_zone = '([A-Z]{1,5})';
                $zone = '(?:' . $num_zone . '|' . $character_zone . ')';
                $this->sp_pcre = '/(?:' . $optional_fws . $day_name . $optional_fws . ',)?' . $optional_fws . $day . $fws . $month . $fws . $year . $fws . $hour . $optional_fws . ':' . $optional_fws . $minute . '(?:' . $optional_fws . ':' . $optional_fws . $second . ')?' . $fws . $zone . '/i';
            }
            if (preg_match($this->sp_pcre, $this->_remove_rfc2822_comments($date), $match)){
                // Find the month number
                $month = $this->_sp_month[strtolower($match[3])];
                // Numeric timezone
                if ($match[8] !== ''){
                    $timezone = $match[9] * 3600;
                    $timezone += $match[10] * 60;
                    if ($match[8] === '-') $timezone = 0 - $timezone;
                } elseif (isset($this->_sp_timezone[strtoupper($match[11])]))
                    $timezone = $this->_sp_timezone[strtoupper($match[11])];
                else $timezone = 0;
                // Deal with 2/3 digit years
                if ($match[4] < 50) $match[4] += 2000;
                elseif ($match[4] < 1000) $match[4] += 1900;
                if ($match[7] !== '') $second = $match[7];
                else $second = 0;
                return gmmktime($match[5], $match[6], $second, $month, $match[2], $match[4]) - $timezone;
            }
            return false;
        }
        /**
         * @description Parse RFC850's date format
         * @param $date
         * @access protected
         * @return int Timestamp
         */
        protected function _date_rfc850($date):int{
            if (!$this->sp_pcre){
                $space = '[\x09\x20]+';
                $day_name = $this->_sp_day_pcre;
                $month = $this->_sp_month_pcre;
                $day = '([0-9]{1,2})';
                $year = $hour = $minute = $second = '([0-9]{2})';
                $zone = '([A-Z]{1,5})';
                $this->sp_pcre = '/^' . $day_name . ',' . $space . $day . '-' . $month . '-' . $year . $space . $hour . ':' . $minute . ':' . $second . $space . $zone . '$/i';
            }
            if (preg_match($this->sp_pcre, $date, $match)){
                $month = $this->_sp_month[strtolower($match[3])];
                if (isset($this->_sp_timezone[strtoupper($match[8])]))
                    $timezone = $this->_sp_timezone[strtoupper($match[8])];
                else $timezone = 0; // Assume everything else to be -0000
                if ($match[4] < 50)  $match[4] += 2000; // Deal with 2 digit year
                else $match[4] += 1900;
                return gmmktime($match[5], $match[6], $match[7], $month, $match[2], $match[4]) - $timezone;
            }
            return false;
        }//912
        /**
         * @description Parse C99's asc_time()'s date format
         * @param $date
         * @access protected
         * @return int Timestamp
         */
        protected function _date_asc_time($date):int{
            if (!$this->sp_pcre){
                $space = '[\x09\x20]+';
                $weekday_name = $this->_sp_day_pcre;
                $month_name = $this->_sp_month_pcre;
                $day = '([0-9]{1,2})';
                $hour = $sec = $min = '([0-9]{2})';
                $year = '([0-9]{4})';
                $terminator = '\x0A?\x00?';
                $this->sp_pcre = '/^' . $weekday_name . $space . $month_name . $space . $day . $space . $hour . ':' . $min . ':' . $sec . $space . $year . $terminator . '$/i';
            }
            if (preg_match($this->sp_pcre, $date, $match)) {
                $month = $this->_sp_month[strtolower($match[2])];
                return gmmktime($match[4], $match[5], $match[6], $month, $match[3], $match[7]);
            }
            return false;
        }//975
        /**
         * @description Parse dates using strtotime()
         * @param $date
         * @access protected
         * @return int Timestamp
         */
        public function date_str_to_time($date):int{
            $str_to_time = strtotime($date);
            if ($str_to_time === -1 || $str_to_time === false)
                return false;
            return $str_to_time;
        }//1015
    }
}else die;