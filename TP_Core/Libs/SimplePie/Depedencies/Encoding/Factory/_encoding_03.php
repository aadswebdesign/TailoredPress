<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 16:15
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Encoding\Factory;
if(ABSPATH){
    trait _encoding_03 {
        public function sp_uncomment_rfc822($string):string{
            $string = (string) $string;
            $position = 0;
            $length = strlen($string);
            $depth = 0;
            $output = '';
            while ($position < $length && ($pos = strpos($string, '(', $position)) !== false){
                $output .= substr($string, $position, $pos - $position);
                $position = $pos + 1;
                if ($string[$pos - 1] !== '\\'){
                    $depth++;
                    while ($depth && $position < $length){
                        $position += strcspn($string, '()', $position);
                        if ($string[$position - 1] === '\\'){
                            $position++;
                            continue;
                        }
                        if (isset($string[$position])){
                            switch ($string[$position]){
                                case '(':
                                    $depth++;
                                    break;
                               case ')':
                                    $depth--;
                                    break;
                            }
                            $position++;
                        }
                        else break;
                    }
                }
                else $output .= '(';
            }
            $output .= substr($string, $position);
            return $output;
        }//1796 from SimplePie_Misc
        public function sp_parse_mime($mime): string{
            if (($pos = strpos($mime, ';')) === false)
                return trim($mime);
            return trim(substr($mime, 0, $pos));
        }//1850 from SimplePie_Misc
        public function sp_atom_03_construct_type($atts): int{
            if (isset($atts['']['mode']) && strtolower(trim($atts['']['mode']) && 'base64'))
                $mode = SP_CONSTRUCT_BASE64;
            else $mode = SP_CONSTRUCT_NONE;
            if (isset($atts['']['type'])){
                switch (strtolower(trim($atts['']['type']))){
                    case 'text':
                    case 'text/plain':
                        return SP_CONSTRUCT_TEXT | $mode;
                    case 'html':
                    case 'text/html':
                        return SP_CONSTRUCT_HTML | $mode;
                    default:
                        return SP_CONSTRUCT_NONE | $mode;
                }
            }
            return SP_CONSTRUCT_TEXT | $mode;
        }//1860 from SimplePie_Misc
        public function sp_atom_10_construct_type($atts){
            if (isset($atts['']['type'])){
                switch (strtolower(trim($atts['']['type']))){
                    case 'text':
                        return SP_CONSTRUCT_TEXT;
                    case 'html':
                        return SP_CONSTRUCT_HTML;
                    default:
                        return SP_CONSTRUCT_NONE;
                }
            }
            return SP_CONSTRUCT_TEXT;
        }//1894 from SimplePie_Misc
        public function sp_atom_10_content_construct_type($atts){
            if (isset($atts['']['type'])){
                $type = strtolower(trim($atts['']['type']));
                switch ($type){
                    case 'text':
                        return SP_CONSTRUCT_TEXT;
                    case 'html':
                        return SP_CONSTRUCT_HTML;
                }
                if (strpos($type, 'text/') === 0 || in_array(substr($type, -4), array('+xml', '/xml')))
                    return SP_CONSTRUCT_NONE;
                else return SP_CONSTRUCT_BASE64;
            }
            return SP_CONSTRUCT_TEXT;
        }//1916 from SimplePie_Misc
        public function sp_is_i_segment_nz_nc($string):bool{
            return (bool) preg_match('/^([A-Za-z0-9\-._~\x{A0}-\x{D7FF}\x{F900}-\x{FDCF}\x{FDF0}-\x{FFEF}\x{10000}-\x{1FFFD}\x{20000}-\x{2FFFD}\x{30000}-\x{3FFFD}\x{40000}-\x{4FFFD}\x{50000}-\x{5FFFD}\x{60000}-\x{6FFFD}\x{70000}-\x{7FFFD}\x{80000}-\x{8FFFD}\x{90000}-\x{9FFFD}\x{A0000}-\x{AFFFD}\x{B0000}-\x{BFFFD}\x{C0000}-\x{CFFFD}\x{D0000}-\x{DFFFD}\x{E1000}-\x{EFFFD}!$&\'()*+,;=@]|(%[0-9ABCDEF]{2}))+$/u', $string);
        }//1945 from SimplePie_Misc
        public function sp_space_separated_tokens($string):array{
            $space_characters = "\x20\x09\x0A\x0B\x0C\x0D";
            $string_length = strlen($string);
            $position = strspn($string, $space_characters);
            $tokens = [];
            while ($position < $string_length){
                $len = strcspn($string, $space_characters, $position);
                $tokens[] = substr($string, $position, $len);
                $position += $len;
                $position += strspn($string, $space_characters, $position);
            }
            return $tokens;
        }//1950 from SimplePie_Misc
        public function sp_code_point_to_utf8($code_point):string{
            $code_point = (int) $code_point;
            if ($code_point < 0) return false;
            else if ($code_point <= 0x7f)
                return chr($code_point);
            else if ($code_point <= 0x7ff)
                return chr(0xc0 | ($code_point >> 6)) . chr(0x80 | ($code_point & 0x3f));
            else if ($code_point <= 0xffff)
                return chr(0xe0 | ($code_point >> 12)) . chr(0x80 | (($code_point >> 6) & 0x3f)) . chr(0x80 | ($code_point & 0x3f));
            else if ($code_point <= 0x10ffff)
                return chr(0xf0 | ($code_point >> 18)) . chr(0x80 | (($code_point >> 12) & 0x3f)) . chr(0x80 | (($code_point >> 6) & 0x3f)) . chr(0x80 | ($code_point & 0x3f));
            // U+FFFD REPLACEMENT CHARACTER
            return "\xEF\xBF\xBD";
        }//1976 from SimplePie_Misc
        public function sp_parse_str($str):array{
            $return = [];
            $str = explode('&', $str);
            foreach ($str as $section){
                if (strpos($section, '=') !== false){
                    @list($name, $value) = explode('=', $section, 2);
                    $return[urldecode($name)][] = urldecode($value);
                }else $return[urldecode($section)][] = null;
            }
            return $return;
        }//2014 from SimplePie_Misc
        public function sp_xml_encoding($data, $registry):array{
            // UTF-32 Big End
            if (strpos($data, "\x00\x00\xFE\xFF") === 0)
                $encoding[] = 'UTF-32BE';
            // UTF-32 Little End
            elseif (strpos($data, "\xFF\xFE\x00\x00") === 0)
                $encoding[] = 'UTF-32LE';
            // UTF-16 Big Endian BOM
            elseif (strpos($data, "\xFE\xFF") === 0)
                $encoding[] = 'UTF-16BE';
            // UTF-16 Little End
            elseif (strpos($data, "\xFF\xFE") === 0)
                $encoding[] = 'UTF-16LE';
            // UTF-8 BOM
            elseif (strpos($data, "\xEF\xBB\xBF") === 0)
                $encoding[] = 'UTF-8';
            // UTF-32 Big End Without BOM
            elseif (strpos($data, "\x00\x00\x00\x3C\x00\x00\x00\x3F\x00\x00\x00\x78\x00\x00\x00\x6D\x00\x00\x00\x6C") === 0){
                if ($pos = strpos($data, "\x00\x00\x00\x3F\x00\x00\x00\x3E")){
                    $parser = $registry->this->create('XML_Declaration_Parser', array($this->sp_change_encoding(substr($data, 20, $pos - 20), 'UTF-32BE', 'UTF-8')));
                    if ($parser->this->parse()) $encoding[] = $parser->encoding;
                }
                $encoding[] = 'UTF-32BE';
            }
            // UTF-32 Little End Without BOM
            elseif (strpos($data, "\x3C\x00\x00\x00\x3F\x00\x00\x00\x78\x00\x00\x00\x6D\x00\x00\x00\x6C\x00\x00\x00") === 0){
                if ($pos = strpos($data, "\x3F\x00\x00\x00\x3E\x00\x00\x00")){
                    $parser = $registry->this->create('XML_Declaration_Parser', array($this->sp_change_encoding(substr($data, 20, $pos - 20), 'UTF-32LE', 'UTF-8')));
                    if ($parser->this->parse()) $encoding[] = $parser->encoding;
                }
                $encoding[] = 'UTF-32LE';
            }
            // UTF-16 Big End Without BOM
            elseif (strpos($data, "\x00\x3C\x00\x3F\x00\x78\x00\x6D\x00\x6C") === 0){
                if ($pos = strpos($data, "\x00\x3F\x00\x3E")){
                    $parser = $registry->this->create('XML_Declaration_Parser', array($this->sp_change_encoding(substr($data, 20, $pos - 10), 'UTF-16BE', 'UTF-8')));
                    if ($parser->this->parse()) $encoding[] = $parser->encoding;
                }
                $encoding[] = 'UTF-16BE';
            }
            // UTF-16 Little End Without BOM
            elseif (strpos($data, "\x3C\x00\x3F\x00\x78\x00\x6D\x00\x6C\x00") === 0){
                if ($pos = strpos($data, "\x3F\x00\x3E\x00")){
                    $parser = $registry->this->create('XML_Declaration_Parser', array($this->sp_change_encoding(substr($data, 20, $pos - 10), 'UTF-16LE', 'UTF-8')));
                    if ($parser->this->parse()) $encoding[] = $parser->encoding;
                }
                $encoding[] = 'UTF-16LE';
            }
            // US-ASCII (or superset)
            elseif (strpos($data, "\x3C\x3F\x78\x6D\x6C") === 0){
                if ($pos = strpos($data, "\x3F\x3E")){
                    $parser = $registry->this->create('XML_Declaration_Parser', array(substr($data, 5, $pos - 5)));
                    if ($parser->this->parse()) $encoding[] = $parser->encoding;
                }
                $encoding[] = 'UTF-8';
            }
            // Fallback to UTF-8
            else $encoding[] = 'UTF-8';
            return $encoding;
        }//2043 from SimplePie_Misc
    }
}else die;