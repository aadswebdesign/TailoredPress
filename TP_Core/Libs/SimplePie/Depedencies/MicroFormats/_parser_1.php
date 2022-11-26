<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-4-2022
 * Time: 20:01
 */
namespace TP_Core\Libs\SimplePie\Depedencies\MicroFormats;
if(ABSPATH){
    trait _parser_1{
        private $__hh;
        public function mf_parse($input, $url = null, $convertClassic = true):string {
            $parser = new Parser($input, $url);
            $parse = null;
            if($parser instanceof Parser){
                $parse = $parser->parse($convertClassic);
            }
            return $parse;
        }//47
        public function fetch($url, $convert_classic = true, &$curl_info=null):string{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Accept: text/html'
            ));
            $html = curl_exec($ch);
            $info = $curl_info = curl_getinfo($ch);
            curl_close($ch);
            if (stripos($info['content_type'], 'html') === false) return null;
            $url = $info['url'];
            return $this->mf_parse($html, $url, $convert_classic);
        }//65
        public function unicodeToHtmlEntities($input):string {
            return mb_convert_encoding($input, 'HTML-ENTITIES', mb_detect_encoding($input));
        }//95
        public function unicodeTrim($str):string {
            // this is cheating. TODO: find a better way if this causes any problems
            $str = str_replace(mb_convert_encoding('&nbsp;', 'UTF-8', 'HTML-ENTITIES'), ' ', $str);
            $str = ltrim($str);
            return rtrim($str);
        }//113
        public function classHasMf2RootClassname($class):bool {
            return count($this->__mfNamesFromClass($class, 'h-')) > 0;
        }//154
        public function mfNamesFromElement(\DOMElement $e, $prefix = 'h-'):array {
            $class = $e->getAttribute('class');
            return $this->__mfNamesFromClass($class, $prefix);
        }//194
        public function nestedMfPropertyNamesFromElement(\DOMElement $e):string {
            $class = $e->getAttribute('class');
            return $this->__nestedMfPropertyNamesFromClass($class);
        }//205
        public function convertTimeFormat($time):?string {
            $this->__hh = $mm = $ss = '';
            preg_match('/(\d{1,2}):?(\d{2})?:?(\d{2})?(a\.?m\.?|p\.?m\.?)?/i', $time, $matches);
            if (empty($matches[4])) return $time;// If no am/pm is specified:
            else {
                $meridiem = strtolower(str_replace('.', '', $matches[4]));// Otherwise, am/pm is specified.
                $hh = $matches[1]; // Hours.
                if ($meridiem === 'pm' && ($this->__hh < 12)) $this->__hh += 12; // Add 12 to hours if pm applies.
                $this->__hh = str_pad($hh, 2, '0', STR_PAD_LEFT);
                $mm = (empty($matches[2]) ) ? '00' : $matches[2]; // Minutes.
                if (!empty($matches[3])) $ss = $matches[3]; // Seconds, only if supplied.
                if (empty($ss)) return sprintf('%s:%s', $this->__hh, $mm);
                else return sprintf('%s:%s:%s', $this->__hh, $mm, $ss);
            }
        }//212
        public function normalizeOrdinalDate($dtValue) {
            @list($year, $day) = explode('-', $dtValue, 2);
            $day = (int)($day);
            if ($day < 367 && $day > 0) {
                $date = \DateTime::createFromFormat('Y-z', $dtValue);
                $date->modify('-1 day'); # 'z' format is zero-based so need to adjust
                if ($date->format('Y') === $year) return $date->format('Y-m-d');
            }
            return '';
        }//257
        public function normalizeTimezoneOffset(&$dtValue) {
            preg_match('/Z|[+-]\d{1,2}:?(\d{2})?$/i', $dtValue, $matches);
            if (empty($matches))return null;
            $timezoneOffset = null;
            if ( $matches[0] !== 'Z' ) {
                $timezoneString = str_replace(':', '', $matches[0]);
                $plus_minus = substr($timezoneString, 0, 1);
                $timezoneOffset = substr($timezoneString, 1);
                if ( strlen($timezoneOffset) <= 2 ) $timezoneOffset .= '00';
                $timezoneOffset = str_pad($timezoneOffset, 4, 0, STR_PAD_LEFT);
                $timezoneOffset = $plus_minus . $timezoneOffset;
                $dtValue = preg_replace('/Z?[+-]\d{1,2}:?(\d{2})?$/i', $timezoneOffset, $dtValue);
            }
            return $timezoneOffset;
        }//275
        public function applySrcsetUrlTransformation($srcset, $transformation): string{
            return implode(', ', array_filter(array_map(static function ($srcsetPart) use ($transformation) {
                $parts = explode(" \t\n\r\0\x0B", trim($srcsetPart), 2);
                $parts[0] = rtrim($parts[0]);
                if (empty($parts[0])) { return false; }
                $parts[0] = $transformation($parts[0]);
                return $parts[0] . (empty($parts[1]) ? '' : ' ' . $parts[1]);
            }, explode(',', trim($srcset)))));
        }//299
        private function __mfNamesFromClass($class, $prefix='h-'):array {
            $class = str_replace(array(' ', '	', "\n"), ' ', $class);
            $classes = explode(' ', $class);
            $classes = preg_grep('#^(h|p|u|dt|e)-([a-z0-9]+-)?[a-z]+(-[a-z]+)*$#', $classes);
            $matches = array();
            foreach ($classes as $classname) {
                $compare_classname = ' ' . $classname;
                $compare_prefix = ' ' . $prefix;
                if (($compare_classname !== $compare_prefix) && strpos($compare_classname, $compare_prefix) !== false)
                    $matches[] = ($prefix === 'h-') ? $classname : substr($classname, strlen($prefix));
            }
            return $matches;
        }//130
        private function __nestedMfPropertyNamesFromClass($class):array {
            $prefixes = array('p-', 'u-', 'dt-', 'e-');
            $propertyNames = array();
            $class = str_replace(array(' ', '	', "\n"), ' ', $class);
            foreach (explode(' ', $class) as $classname) {
                foreach ($prefixes as $prefix) {
                    // Check if $classname is a valid property classname for $prefix.
                    if ($classname !== $prefix && mb_strpos($classname, $prefix) === 0) {
                        $propertyName = mb_substr($classname, mb_strlen($prefix));
                        $propertyNames[$propertyName][] = $prefix;
                    }
                }
            }
            foreach ($propertyNames as $property => $prefixes)
                $propertyNames[$property] = array_unique($prefixes);
            return $propertyNames;
        }//165
    }
}else die;