<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-9-2022
 * Time: 16:36
 * getID3() by James Heinrich <info@getid3.org>
 */
namespace TP_Core\Libs\ID3;
if(ABSPATH){
    class getid3_lib{
        public static function PrintHexBytes($string, $hex=true, $spaces=true, $htmlencoding='UTF-8'):string {
            $returnstring = '';
            for ($i = 0, $iMax = strlen($string); $i < $iMax; $i++) {
                if ($hex) {
                    $returnstring .= str_pad(dechex(ord($string[$i])), 2, '0', STR_PAD_LEFT);
                } else {
                    $returnstring .= ' '.(preg_match("#[\x20-\x7E]#", $string[$i]) ? $string[$i] : '¤');
                }
                if ($spaces) {
                    $returnstring .= ' ';
                }
            }
            if (!empty($htmlencoding)) {
                if ($htmlencoding === true) {
                    $htmlencoding = 'UTF-8'; // prior to getID3 v1.9.0 the function's 4th parameter was boolean
                }
                $returnstring = htmlentities($returnstring, ENT_QUOTES, $htmlencoding);
            }
            return $returnstring;
        }//25
        public static function trunc($floatnumber) {
            if ($floatnumber >= 1) {
                $truncatednumber = floor($floatnumber);
            } elseif ($floatnumber <= -1) {
                $truncatednumber = ceil($floatnumber);
            } else {
                $truncatednumber = 0;
            }
            if (self::intValueSupported($truncatednumber)) {
                $truncatednumber = (int) $truncatednumber;
            }
            return $truncatednumber;
        }//65
        public static function safe_inc(&$variable, $increment=1):bool {
            if (isset($variable)) {
                $variable += $increment;
            } else {
                $variable = $increment;
            }
            return true;
        }//73
        public static function CastAsInt($floatnum) {
            $floatnum = (float) $floatnum;
            if ((self::trunc($floatnum) === $floatnum) && self::intValueSupported($floatnum)) {
                // it's within int range
                $floatnum = (int) $floatnum;
            }
            return $floatnum;
        }
        public static function intValueSupported($num) :int{
            // check if integers are 64-bit
            static $hasINT64 = null;
            if ($hasINT64 === null) { // 10x faster than is_null()
                $hasINT64 = is_int(2 ** 31); // 32-bit int are limited to (2^31)-1
                if (!$hasINT64 && !defined('PHP_INT_MIN')) {
                    define('PHP_INT_MIN', ~PHP_INT_MAX);
                }
            }
            return $hasINT64 || (($num <= PHP_INT_MAX) && ($num >= PHP_INT_MIN));
        }//107
        public static function DecimalizeFraction($fraction) {
            @list($numerator, $denominator) = explode('/', $fraction);
            return $numerator / ($denominator ?: 1);
        }//128
        public static function DecimalBinary2Float($binarynumerator):int {
            $numerator   = self::Bin2Dec($binarynumerator);
            $denominator = self::Bin2Dec('1'.str_repeat('0', strlen($binarynumerator)));
            return ($numerator / $denominator);
        }//138
        public static function NormalizeBinaryPoint($binarypointnumber, $maxbits=52):array {
            if (strpos($binarypointnumber, '.') === false) {
                $binarypointnumber = '0.'.$binarypointnumber;
            } elseif ($binarypointnumber[0] === '.') {
                $binarypointnumber = '0'.$binarypointnumber;
            }
            $exponent = 0;
            while (($binarypointnumber[0] !== '1') || ($binarypointnumber[1] !== '.')) {
                if ($binarypointnumber[1] === '.') {
                    $exponent--;
                    $binarypointnumber = $binarypointnumber[2] .'.'.substr($binarypointnumber, 3);
                } else {
                    $pointpos = strpos($binarypointnumber, '.');
                    $exponent += ($pointpos - 1);
                    $binarypointnumber = str_replace('.', '', $binarypointnumber);
                    $binarypointnumber = $binarypointnumber[0].'.'.substr($binarypointnumber, 1);
                }
            }
            $binarypointnumber = str_pad(substr($binarypointnumber, 0, $maxbits + 2), $maxbits + 2, '0', STR_PAD_RIGHT);
            return array('normalized'=>$binarypointnumber, 'exponent'=>(int) $exponent);
        }//152
        public static function Float2BinaryDecimal($floatvalue):string{
            $maxbits = 128; // to how many bits of precision should the calculations be taken?
            $intpart   = self::trunc($floatvalue);
            $floatpart = abs($floatvalue - $intpart);
            $pointbitstring = '';
            while (($floatpart !== 0) && (strlen($pointbitstring) < $maxbits)) {
                $floatpart *= 2;
                $pointbitstring .= self::trunc($floatpart);
                $floatpart -= self::trunc($floatpart);
            }
            return decbin($intpart).'.'.$pointbitstring;
        }
        public static function Float2String($floatvalue, $bits):int {
            $exponentbits = null;
            $fractionbits = null;
            switch ($bits) {
                case 32:
                    $exponentbits = 8;
                    $fractionbits = 23;
                    break;
                case 64:
                    $exponentbits = 11;
                    $fractionbits = 52;
                    break;
                default:
                    return false;
            }
            if ($floatvalue >= 0) {$signbit = '0';}
            else {$signbit = '1';}
            $normalizedbinary  = self::NormalizeBinaryPoint(self::Float2BinaryDecimal($floatvalue), $fractionbits);
            $biasedexponent    = (2 ** ($exponentbits - 1)) - 1 + $normalizedbinary['exponent']; // (127 or 1023) +/- exponent
            $exponentbitstring = str_pad(decbin($biasedexponent), $exponentbits, '0', STR_PAD_LEFT);
            $fractionbitstring = str_pad(substr($normalizedbinary['normalized'], 2), $fractionbits, '0', STR_PAD_RIGHT);

            return self::BigEndian2String(self::Bin2Dec($signbit.$exponentbitstring.$fractionbitstring), $bits % 8, false);
        }//203
        public static function LittleEndian2Float($byteword) {
            return self::BigEndian2Float(strrev($byteword));
        }//238
        /**
         * @param $byteword
         * @return float|bool
         */
        public static function BigEndian2Float($byteword):float {
            $bitword = self::BigEndian2Bin($byteword);
            if (!$bitword) {
                return 0;
            }
            $signbit = $bitword[0];
            $floatvalue = null;
            $exponentbits = null;
            $fractionbits = null;
            switch (strlen($byteword) * 8) {
                case 32:
                    $exponentbits = 8;
                    $fractionbits = 23;
                    break;
                case 64:
                    $exponentbits = 11;
                    $fractionbits = 52;
                    break;
                case 80:
                    // 80-bit Apple SANE format
                    // http://www.mactech.com/articles/mactech/Vol.06/06.01/SANENormalized/
                    $exponentstring = substr($bitword, 1, 15);
                    $isnormalized = (int)$bitword[16];
                    $fractionstring = substr($bitword, 17, 63);
                    $exponent = 2 ** (self::Bin2Dec($exponentstring) - 16383);
                    $fraction = $isnormalized + self::DecimalBinary2Float($fractionstring);
                    $floatvalue = $exponent * $fraction;
                    if ($signbit === '1') {
                        $floatvalue *= -1;
                    }
                    return $floatvalue;
                default:
                    return false;
            }
            $exponentstring = substr($bitword, 1, $exponentbits);
            $fractionstring = substr($bitword, $exponentbits + 1, $fractionbits);
            $exponent = self::Bin2Dec($exponentstring);
            $fraction = self::Bin2Dec($fractionstring);
            if (($exponent === ((2 ** $exponentbits) - 1)) && ($fraction !== 0)) {
                // Not a Number
                $floatvalue = NAN;
            } elseif (($exponent === ((2 ** $exponentbits) - 1)) && ($fraction === 0)) {
                if ($signbit === '1') {
                    $floatvalue = -INF;
                } else {
                    $floatvalue = INF;
                }
            } elseif (($exponent === 0) && ($fraction === 0)) {
                $floatvalue = $signbit === '1' ? -0 : 0;
                $floatvalue .= ($signbit ? 0 : -0);
            } elseif (($exponent === 0) && ($fraction !== 0)) {
                $floatvalue = (2 ** (-1 * ((2 ** ($exponentbits - 1)) - 2))) * self::DecimalBinary2Float($fractionstring);
                if ($signbit === '1') {
                    $floatvalue *= -1;
                }
            } elseif ($exponent !== 0) {
                $floatvalue = (2 ** ($exponent - ((2 ** ($exponentbits - 1)) - 1))) * (1 + self::DecimalBinary2Float($fractionstring));
                if ($signbit === '1') {
                    $floatvalue *= -1;
                }
            }
            return (float) $floatvalue;
        }//252
        public static function BigEndian2Int($byteword, $synchsafe=false, $signed=false) {
            $intvalue = 0;
            $bytewordlen = strlen($byteword);
            if ($bytewordlen === 0) {
                return false;
            }
            for ($i = 0; $i < $bytewordlen; $i++) {
                if ($synchsafe) { // disregard MSB, effectively 7-bit bytes
                    $intvalue += (ord($byteword[$i]) & 0x7F) * (2 ** (($bytewordlen - 1 - $i) * 7));
                } else {
                    $intvalue += ord($byteword[$i]) * (256 ** ($bytewordlen - 1 - $i));
                }
            }
            if ($signed && !$synchsafe) {
                if ($bytewordlen <= PHP_INT_SIZE) {
                    $signMaskBit = 0x80 << (8 * ($bytewordlen - 1));
                    if ($intvalue & $signMaskBit) {
                        $intvalue = 0 - ($intvalue & ($signMaskBit - 1));
                    }
                } else {
                    throw new \RuntimeException('ERROR: Cannot have signed integers larger than '.(8 * PHP_INT_SIZE).'-bits ('.strlen($byteword).') in self::BigEndian2Int()');
                }
            }
            return self::CastAsInt($intvalue);
        }//334
        public static function LittleEndian2Int($byteword, $signed=false) {
            return self::BigEndian2Int(strrev($byteword), false, $signed);
        }//368
        public static function LittleEndian2Bin($byteword):int{
            return self::BigEndian2Bin(strrev($byteword));
        }//377
        public static function BigEndian2Bin($byteword):int{
            $binvalue = '';
            $bytewordlen = strlen($byteword);
            for ($i = 0; $i < $bytewordlen; $i++) {
                $binvalue .= str_pad(decbin(ord($byteword[$i])), 8, '0', STR_PAD_LEFT);
            }
            return $binvalue;
        }//386
        public static function BigEndian2String($number, $minbytes=1, $synchsafe=false, $signed=false):float {
            if ($number < 0) {
                throw new \RuntimeException('ERROR: self::BigEndian2String() does not support negative numbers');
            }
            $maskbyte = (($synchsafe || $signed) ? 0x7F : 0xFF);
            $intstring = '';
            if ($signed) {
                if ($minbytes > PHP_INT_SIZE) {
                    throw new \RuntimeException('ERROR: Cannot have signed integers larger than '.(8 * PHP_INT_SIZE).'-bits in self::BigEndian2String()');
                }
                $number &= (0x80 << (8 * ($minbytes - 1)));
            }
            while ($number !== 0) {
                $quotient = ($number / ($maskbyte + 1));
                $intstring = chr(ceil(($quotient - floor($quotient)) * $maskbyte)).$intstring;
                $number = floor($quotient);
            }
            return str_pad($intstring, $minbytes, "\x00", STR_PAD_LEFT);
        }//404
        public static function Dec2Bin($number):int {
            if (!is_numeric($number)) {
                // https://github.com/JamesHeinrich/getID3/issues/299
                trigger_error('TypeError: Dec2Bin(): Argument #1 ($number) must be numeric, '.gettype($number).' given', E_USER_WARNING);
                return '';
            }
            $bytes = [];
            while ($number >= 256) {
                $bytes[] = (int) (($number / 256) - (floor($number / 256))) * 256;
                $number = floor($number / 256);
            }
            $bytes[] = (int) $number;
            $binstring = '';
            foreach ($bytes as $i => $byte) {
                $binstring = (($i === count($bytes) - 1) ? decbin($byte) : str_pad(decbin($byte), 8, '0', STR_PAD_LEFT)).$binstring;
            }
            return $binstring;
        }//429
        public static function Bin2Dec($binstring, $signed=false) {
            $signmult = 1;
            if ($signed) {
                if ($binstring[0] === '1') {
                    $signmult = -1;
                }
                $binstring = substr($binstring, 1);
            }
            $decvalue = 0;
            for ($i = 0, $iMax = strlen($binstring); $i < $iMax; $i++) {
                $decvalue += ((int)$binstring[strlen($binstring) - $i - 1]) * (2 ** $i);
            }
            return self::CastAsInt($decvalue * $signmult);
        }//454
        public static function Bin2String($binstring):string {
            $string = '';
            $binstringreversed = strrev($binstring);
            for ($i = 0, $iMax = strlen($binstringreversed); $i < $iMax; $i += 8) {
                $string = chr(self::Bin2Dec(strrev(substr($binstringreversed, $i, 8)))).$string;
            }
            return $string;
        }//474
        public static function LittleEndian2String($number, $minbytes=1, $synchsafe=false):string {
            $intstring = '';
            while ($number > 0) {
                if ($synchsafe) {
                    $intstring .= chr($number & 127);
                    $number >>= 7;
                } else {
                    $intstring .= chr($number & 255);
                    $number >>= 8;
                }
            }
            return str_pad($intstring, $minbytes, "\x00", STR_PAD_RIGHT);
        }//491
        public static function array_merge_clobber($array1, $array2) {
            if (!is_array($array1) || !is_array($array2)) {
                return false;
            }
            $newarray = $array1;
            foreach ($array2 as $key => $val) {
                if (is_array($val) && isset($newarray[$key]) && is_array($newarray[$key])) {
                    $newarray[$key] = self::array_merge_clobber($newarray[$key], $val);
                } else {
                    $newarray[$key] = $val;
                }
            }
            return $newarray;
        }//511
        public static function array_merge_noclobber($array1, $array2) {
            if (!is_array($array1) || !is_array($array2)) {
                return false;
            }
            $newarray = $array1;
            foreach ($array2 as $key => $val) {
                if (is_array($val) && isset($newarray[$key]) && is_array($newarray[$key])) {
                    $newarray[$key] = self::array_merge_noclobber($newarray[$key], $val);
                } elseif (!isset($newarray[$key])) {
                    $newarray[$key] = $val;
                }
            }
            return $newarray;
        }//534
        public static function flipped_array_merge_noclobber($array1, $array2) {
            if (!is_array($array1) || !is_array($array2)) {
                return false;
            }
            $newarray = array_flip($array1);
            foreach (array_flip($array2) as $key => $val) {
                if (!isset($newarray[$key])) {
                    $newarray[$key] = count($newarray);
                }
            }
            return array_flip($newarray);
        }//555
        public static function ksort_recursive(&$theArray):bool {
            ksort($theArray);
            foreach ($theArray as $key => $value) {
                if (is_array($value)) {
                    self::ksort_recursive($theArray[$key]);
                }
            }
            return true;
        }//574
        public static function fileextension($filename, $numextensions=1):string {
            if (strpos($filename, '.') !== false) {
                $reversedfilename = strrev($filename);
                $offset = 0;
                for ($i = 0; $i < $numextensions; $i++) {
                    $offset = strpos($reversedfilename, '.', $offset + 1);
                    if ($offset === false) {
                        return '';
                    }
                }
                return strrev(substr($reversedfilename, 0, $offset));
            }
            return '';
        }//590
        public static function PlaytimeString($seconds):float {
            $sign = (($seconds < 0) ? '-' : '');
            $seconds = round(abs($seconds));
            $H = (int) floor( $seconds                            / 3600);
            $M = (int) floor(($seconds - (3600 * $H)            ) /   60);
            $S = (int) round( $seconds - (3600 * $H) - (60 * $M)        );
            return $sign.($H ? $H.':' : '').($H ? str_pad($M, 2, '0', STR_PAD_LEFT) : $M).':'.str_pad($S, 2, 0, STR_PAD_LEFT);
        }//610
        public static function DateMac2Unix($macdate) {
            return self::CastAsInt($macdate - 2082844800);
        }//624
        public static function FixedPoint8_8($rawdata) {
            return self::BigEndian2Int(substr($rawdata, 0, 1)) + (self::BigEndian2Int(substr($rawdata, 1, 1)) / (2 ** 8));
        }//635
        public static function FixedPoint16_16($rawdata) {
            return self::BigEndian2Int(substr($rawdata, 0, 2)) + (self::BigEndian2Int(substr($rawdata, 2, 2)) / (2 ** 16));
        }//644
        public static function FixedPoint2_30($rawdata) {
            $binarystring = self::BigEndian2Bin($rawdata);
            return self::Bin2Dec(substr($binarystring, 0, 2)) + (self::Bin2Dec(substr($binarystring, 2, 30)) / (2 ** 30));
        }//653
        public static function CreateDeepArray($ArrayPath, $Separator, $Value):array {
            $ArrayPath = ltrim($ArrayPath, $Separator);
            $ReturnedArray = array();
            if (($pos = strpos($ArrayPath, $Separator)) !== false) {
                $ReturnedArray[substr($ArrayPath, 0, $pos)] = self::CreateDeepArray(substr($ArrayPath, $pos + 1), $Separator, $Value);
            } else {
                $ReturnedArray[$ArrayPath] = $Value;
            }
            return $ReturnedArray;
        }//666
        public static function array_max($arraydata, $returnkey=false) {
            $maxvalue = false;
            $maxkey   = false;
            foreach ($arraydata as $key => $value) {
                if (!is_array($value)) {
                    if (($maxvalue === false) || ($value > $maxvalue)) {
                        $maxvalue = $value;
                        $maxkey = $key;
                    }
                }
            }
            return ($returnkey ? $maxkey : $maxvalue);
        }//689
        public static function array_min($arraydata, $returnkey=false) {
            $minvalue = false;
            $minkey   = false;
            foreach ($arraydata as $key => $value) {
                if (!is_array($value)) {
                    if (($minvalue === false) || ($value < $minvalue)) {
                        $minvalue = $value;
                        $minkey = $key;
                    }
                }
            }
            return ($returnkey ? $minkey : $minvalue);
        }//709
        /**
         * @param $XMLstring
         * @return bool|array
         */
        public static function XML2array($XMLstring):bool {
            if (function_exists('simplexml_load_string') && function_exists('libxml_disable_entity_loader')) {
                $loader = @libxml_disable_entity_loader(true);
                $XMLobject = simplexml_load_string($XMLstring, 'SimpleXMLElement', LIBXML_NOENT);
                $return = self::SimpleXMLelement2array($XMLobject);
                @libxml_disable_entity_loader($loader);
                return $return;
            }
            return false;
        }//728
        public static function SimpleXMLelement2array($XMLobject):array {
            if (!is_object($XMLobject) && !is_array($XMLobject)) {
                return $XMLobject;
            }
            $XMLarray = $XMLobject instanceof \SimpleXMLElement ? get_object_vars($XMLobject) : $XMLobject;
            foreach ($XMLarray as $key => $value) {
                $XMLarray[$key] = self::SimpleXMLelement2array($value);
            }
            return $XMLarray;
        }//748
        public static function hash_data($file, $offset, $end, $algorithm) {
            if (!self::intValueSupported($end)) {
                return false;
            }
            if (!in_array($algorithm, array('md5', 'sha1'))) {
                throw new getid3_exception('Invalid algorithm ('.$algorithm.') in self::hash_data()');
            }
            $size = $end - $offset;
            $fp = fopen($file, 'rb');
            fseek($fp, $offset);
            $ctx = hash_init($algorithm);
            while ($size > 0) {
                $buffer = fread($fp, min($size, getID3::FREAD_BUFFER_SIZE));
                hash_update($ctx, $buffer);
                $size -= getID3::FREAD_BUFFER_SIZE;
            }
            $hash = hash_final($ctx);
            fclose($fp);
            return $hash;
        }//770
        public static function CopyFileParts($filename_source, $filename_dest, $offset, $length):bool {
            if (!self::intValueSupported($offset + $length)) {
                throw new \RuntimeException('cannot copy file portion, it extends beyond the '.round(PHP_INT_MAX / 1073741824).'GB limit');
            }
            if (is_readable($filename_source) && is_file($filename_source) && ($fp_src = fopen($filename_source, 'rb'))) {
                if (($fp_dest = fopen($filename_dest, 'wb'))) {
                    if (fseek($fp_src, $offset) === 0) {
                        $byteslefttowrite = $length;
                        while (($byteslefttowrite > 0) && ($buffer = fread($fp_src, min($byteslefttowrite, getID3::FREAD_BUFFER_SIZE)))) {
                            $byteswritten = fwrite($fp_dest, $buffer, $byteslefttowrite);
                            $byteslefttowrite -= $byteswritten;
                        }
                        fclose($fp_dest);
                        return true;
                    }
                    fclose($fp_src);
                    throw new \RuntimeException('failed to seek to offset '.$offset.' in '.$filename_source);
                }
                throw new \RuntimeException('failed to create file for writing '.$filename_dest);
            }
            throw new \RuntimeException('failed to open file for reading '.$filename_source);
        }//805
        public static function iconv_fallback_int_utf8($charval): string{
            if ($charval < 128) {
                // 0bbbbbbb
                $newcharstring = chr($charval);
            } elseif ($charval < 2048) {
                // 110bbbbb 10bbbbbb
                $newcharstring  = chr(($charval >>   6) | 0xC0);
                $newcharstring .= chr(($charval & 0x3F) | 0x80);
            } elseif ($charval < 65536) {
                // 1110bbbb 10bbbbbb 10bbbbbb
                $newcharstring  = chr(($charval >>  12) | 0xE0);
                $newcharstring .= chr(($charval >>   6) | 0xC0);
                $newcharstring .= chr(($charval & 0x3F) | 0x80);
            } else {
                // 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
                $newcharstring  = chr(($charval >>  18) | 0xF0);
                $newcharstring .= chr(($charval >>  12) | 0xC0);
                $newcharstring .= chr(($charval >>   6) | 0xC0);
                $newcharstring .= chr(($charval & 0x3F) | 0x80);
            }
            return $newcharstring;
        }//836
        public static function iconv_fallback_iso88591_utf8($string, $bom=false):string{
            if (function_exists('utf8_encode')) {
                return utf8_encode($string);
            }
            // utf8_encode() unavailable, use getID3()'s iconv_fallback() conversions (possibly PHP is compiled without XML support)
            $newcharstring = '';
            if ($bom) {
                $newcharstring .= "\xEF\xBB\xBF";
            }
            for ($i = 0, $iMax = strlen($string); $i < $iMax; $i++) {
                $charval = ord($string[$i]);
                $newcharstring .= self::iconv_fallback_int_utf8($charval);
            }
            return $newcharstring;
        }//867
        public static function iconv_fallback_iso88591_utf16be($string, $bom=false):string{
            $newcharstring = '';
            if ($bom) {
                $newcharstring .= "\xFE\xFF";
            }
            for ($i = 0, $iMax = strlen($string); $i < $iMax; $i++) {
                $newcharstring .= "\x00".$string[$i];
            }
            return $newcharstring;
        }//891
        public static function iconv_fallback_iso88591_utf16le($string, $bom=false):string{
            $newcharstring = '';
            if ($bom) {
                $newcharstring .= "\xFF\xFE";
            }
            for ($i = 0, $iMax = strlen($string); $i < $iMax; $i++) {
                $newcharstring .= $string[$i]."\x00";
            }
            return $newcharstring;
        }//910
        public static function iconv_fallback_iso88591_utf16($string):string{
            return self::iconv_fallback_iso88591_utf16le($string, true);
        }//930
        public static function iconv_fallback_utf8_iso88591($string):string {
            if (function_exists('utf8_decode')) {
                return utf8_decode($string);
            }
            // utf8_decode() unavailable, use getID3()'s iconv_fallback() conversions (possibly PHP is compiled without XML support)
            $newcharstring = '';
            $offset = 0;
            $stringlength = strlen($string);
            while ($offset < $stringlength) {
                if ((ord($string[$offset]) | 0x07) === 0xF7) {
                    // 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
                    $charval = ((ord($string[($offset + 0)]) & 0x07) << 18) &
                        ((ord($string[($offset + 1)]) & 0x3F) << 12) &
                        ((ord($string[($offset + 2)]) & 0x3F) <<  6) &
                        (ord($string[($offset + 3)]) & 0x3F);
                    $offset += 4;
                } elseif ((ord($string[$offset]) | 0x0F) === 0xEF) {
                    // 1110bbbb 10bbbbbb 10bbbbbb
                    $charval = ((ord($string[($offset + 0)]) & 0x0F) << 12) &
                        ((ord($string[($offset + 1)]) & 0x3F) <<  6) &
                        (ord($string[($offset + 2)]) & 0x3F);
                    $offset += 3;
                } elseif ((ord($string[$offset]) | 0x1F) === 0xDF) {
                    // 110bbbbb 10bbbbbb
                    $charval = ((ord($string[($offset + 0)]) & 0x1F) <<  6) &
                        (ord($string[($offset + 1)]) & 0x3F);
                    $offset += 2;
                } elseif ((ord($string[$offset]) | 0x7F) === 0x7F) {
                    // 0bbbbbbb
                    $charval = ord($string[$offset]);
                    ++$offset;
                } else {
                    // error? throw some kind of warning here?
                    $charval = false;
                    ++$offset;
                }
                if ($charval !== false) {
                    $newcharstring .= (($charval < 256) ? chr($charval) : '?');
                }
            }
            return $newcharstring;
        }//939
        public static function iconv_fallback_utf8_utf16be($string, $bom=false):string {
            $newcharstring = '';
            if ($bom) {
                $newcharstring .= "\xFE\xFF";
            }
            $offset = 0;
            $stringlength = strlen($string);
            while ($offset < $stringlength) {
                if ((ord($string[$offset]) | 0x07) === 0xF7) {
                    // 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
                    $charval = ((ord($string[($offset + 0)]) & 0x07) << 18) &
                        ((ord($string[($offset + 1)]) & 0x3F) << 12) &
                        ((ord($string[($offset + 2)]) & 0x3F) <<  6) &
                        (ord($string[($offset + 3)]) & 0x3F);
                    $offset += 4;
                } elseif ((ord($string[$offset]) | 0x0F) === 0xEF) {
                    // 1110bbbb 10bbbbbb 10bbbbbb
                    $charval = ((ord($string[($offset + 0)]) & 0x0F) << 12) &
                        ((ord($string[($offset + 1)]) & 0x3F) <<  6) &
                        (ord($string[($offset + 2)]) & 0x3F);
                    $offset += 3;
                } elseif ((ord($string[$offset]) | 0x1F) === 0xDF) {
                    // 110bbbbb 10bbbbbb
                    $charval = ((ord($string[($offset + 0)]) & 0x1F) <<  6) &
                        (ord($string[($offset + 1)]) & 0x3F);
                    $offset += 2;
                } elseif ((ord($string[$offset]) | 0x7F) === 0x7F) {
                    // 0bbbbbbb
                    $charval = ord($string[$offset]);
                    ++$offset;
                } else {
                    // error? throw some kind of warning here?
                    $charval = false;
                    ++$offset;
                }
                if ($charval !== false) {
                    $newcharstring .= (($charval < 65536) ? self::BigEndian2String($charval, 2) : "\x00".'?');
                }
            }
            return $newcharstring;
        }//990
        public static function iconv_fallback_utf8_utf16le($string, $bom=false):string {
            $newcharstring = '';
            if ($bom) {
                $newcharstring .= "\xFF\xFE";
            }
            $offset = 0;
            $stringlength = strlen($string);
            while ($offset < $stringlength) {
                if ((ord($string[$offset]) | 0x07) === 0xF7) {
                    // 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
                    $charval = ((ord($string[($offset + 0)]) & 0x07) << 18) &
                        ((ord($string[($offset + 1)]) & 0x3F) << 12) &
                        ((ord($string[($offset + 2)]) & 0x3F) <<  6) &
                        (ord($string[($offset + 3)]) & 0x3F);
                    $offset += 4;
                } elseif ((ord($string[$offset]) | 0x0F) === 0xEF) {
                    // 1110bbbb 10bbbbbb 10bbbbbb
                    $charval = ((ord($string[($offset + 0)]) & 0x0F) << 12) &
                        ((ord($string[($offset + 1)]) & 0x3F) <<  6) &
                        (ord($string[($offset + 2)]) & 0x3F);
                    $offset += 3;
                } elseif ((ord($string[$offset]) | 0x1F) === 0xDF) {
                    // 110bbbbb 10bbbbbb
                    $charval = ((ord($string[($offset + 0)]) & 0x1F) <<  6) &
                        (ord($string[($offset + 1)]) & 0x3F);
                    $offset += 2;
                } elseif ((ord($string[$offset]) | 0x7F) === 0x7F) {
                    // 0bbbbbbb
                    $charval = ord($string[$offset]);
                    ++$offset;
                } else {
                    // error? maybe throw some warning here?
                    $charval = false;
                    ++$offset;
                }
                if ($charval !== false) {
                    $newcharstring .= (($charval < 65536) ? self::LittleEndian2String($charval, 2) : '?'."\x00");
                }
            }
            return $newcharstring;
        }//1040
        public static function iconv_fallback_utf8_utf16($string):string {
            return self::iconv_fallback_utf8_utf16le($string, true);
        }//1089
        public static function iconv_fallback_utf16be_utf8($string):string {
            if (strpos($string, "\xFE\xFF") === 0) {
                // strip BOM
                $string = substr($string, 2);
            }
            $newcharstring = '';
            for ($i = 0, $iMax = strlen($string); $i < $iMax; $i += 2) {
                $charval = self::BigEndian2Int(substr($string, $i, 2));
                $newcharstring .= self::iconv_fallback_int_utf8($charval);
            }
            return $newcharstring;
        }//1100
        public static function iconv_fallback_utf16le_utf8($string):string {
            if (strpos($string, "\xFF\xFE") === 0) {
                // strip BOM
                $string = substr($string, 2);
            }
            $newcharstring = '';
            for ($i = 0, $iMax = strlen($string); $i < $iMax; $i += 2) {
                $charval = self::LittleEndian2Int(substr($string, $i, 2));
                $newcharstring .= self::iconv_fallback_int_utf8($charval);
            }
            return $newcharstring;
        }//1120
        public static function iconv_fallback_utf16be_iso88591($string):string {
            if (strpos($string, "\xFE\xFF") === 0) {
                $string = substr($string, 2);
            }
            $newcharstring = '';
            for ($i = 0, $iMax = strlen($string); $i < $iMax; $i += 2) {
                $charval = self::BigEndian2Int(substr($string, $i, 2));
                $newcharstring .= (($charval < 256) ? chr($charval) : '?');
            }
            return $newcharstring;
        }//1140
        public static function iconv_fallback_utf16le_iso88591($string):string {
            if (strpos($string, "\xFF\xFE") === 0) {
                $string = substr($string, 2);
            }
            $newcharstring = '';
            for ($i = 0, $iMax = strlen($string); $i < $iMax; $i += 2) {
                $charval = self::LittleEndian2Int(substr($string, $i, 2));
                $newcharstring .= (($charval < 256) ? chr($charval) : '?');
            }
            return $newcharstring;
        }//1160
        public static function iconv_fallback_utf16_iso88591($string):string {
            $bom = substr($string, 0, 2);
            if ($bom === "\xFE\xFF") {
                return self::iconv_fallback_utf16be_iso88591(substr($string, 2));
            }
            if ($bom === "\xFF\xFE") {
                return self::iconv_fallback_utf16le_iso88591(substr($string, 2));
            }
            return $string;
        }//1180
        public static function iconv_fallback_utf16_utf8($string):string {
            $bom = substr($string, 0, 2);
            if ($bom === "\xFE\xFF") {
                return self::iconv_fallback_utf16be_utf8(substr($string, 2));
            }
            if ($bom === "\xFF\xFE") {
                return self::iconv_fallback_utf16le_utf8(substr($string, 2));
            }
            return $string;
        }//1197
        public static function iconv_fallback($in_charset, $out_charset, $string):string {
            if ($in_charset === $out_charset) {
                return $string;
            }
            if (function_exists('mb_convert_encoding')) {
                if ((strtoupper($in_charset) === 'UTF-16') && (strpos($string, "\xFE\xFF") !== 0) && (strpos($string, "\xFF\xFE") !== 0)) {
                    // if BOM missing, mb_convert_encoding will mishandle the conversion, assume UTF-16BE and prepend appropriate BOM
                    $string = "\xFF\xFE".$string;
                }
                if ((strtoupper($in_charset) === 'UTF-16') && (strtoupper($out_charset) === 'UTF-8')) {
                    if (($string === "\xFF\xFE") || ($string === "\xFE\xFF")) {
                        // if string consists of only BOM, mb_convert_encoding will return the BOM unmodified
                        return '';
                    }
                }
                if ($converted_string = @mb_convert_encoding($string, $out_charset, $in_charset)) {
                    if ($out_charset === 'ISO-8859-1') {
                        $converted_string = rtrim($converted_string, "\x00");
                    }
                    return $converted_string;
                }
                return $string;
            }
            if (function_exists('iconv')) {
                if ($converted_string = @iconv($in_charset, $out_charset.'//TRANSLIT', $string)) {
                    if ($out_charset === 'ISO-8859-1') {
                        $converted_string = rtrim($converted_string, "\x00");
                    }
                    return $converted_string;
                }
                return $string;
            }
            static $ConversionFunctionList = array();
            if (empty($ConversionFunctionList)) {
                $ConversionFunctionList['ISO-8859-1']['UTF-8']    = 'iconv_fallback_iso88591_utf8';
                $ConversionFunctionList['ISO-8859-1']['UTF-16']   = 'iconv_fallback_iso88591_utf16';
                $ConversionFunctionList['ISO-8859-1']['UTF-16BE'] = 'iconv_fallback_iso88591_utf16be';
                $ConversionFunctionList['ISO-8859-1']['UTF-16LE'] = 'iconv_fallback_iso88591_utf16le';
                $ConversionFunctionList['UTF-8']['ISO-8859-1']    = 'iconv_fallback_utf8_iso88591';
                $ConversionFunctionList['UTF-8']['UTF-16']        = 'iconv_fallback_utf8_utf16';
                $ConversionFunctionList['UTF-8']['UTF-16BE']      = 'iconv_fallback_utf8_utf16be';
                $ConversionFunctionList['UTF-8']['UTF-16LE']      = 'iconv_fallback_utf8_utf16le';
                $ConversionFunctionList['UTF-16']['ISO-8859-1']   = 'iconv_fallback_utf16_iso88591';
                $ConversionFunctionList['UTF-16']['UTF-8']        = 'iconv_fallback_utf16_utf8';
                $ConversionFunctionList['UTF-16LE']['ISO-8859-1'] = 'iconv_fallback_utf16le_iso88591';
                $ConversionFunctionList['UTF-16LE']['UTF-8']      = 'iconv_fallback_utf16le_utf8';
                $ConversionFunctionList['UTF-16BE']['ISO-8859-1'] = 'iconv_fallback_utf16be_iso88591';
                $ConversionFunctionList['UTF-16BE']['UTF-8']      = 'iconv_fallback_utf16be_utf8';
            }
            if (isset($ConversionFunctionList[strtoupper($in_charset)][strtoupper($out_charset)])) {
                $ConversionFunction = $ConversionFunctionList[strtoupper($in_charset)][strtoupper($out_charset)];
                return self::$ConversionFunction($string);
            }
            throw new \RuntimeException('PHP does not has mb_convert_encoding() or iconv() support - cannot convert from '.$in_charset.' to '.$out_charset);
        }//1215
        /**
         * @param $data
         * @param string $charset
         * @return mixed
         */
        public static function recursiveMultiByteCharString2HTML($data, $charset='ISO-8859-1'){
            if (is_string($data)) {
                return self::MultiByteCharString2HTML($data, $charset);
            }
            if (is_array($data)) {
                $return_data = [];
                foreach ($data as $key => $value) {
                    $return_data[$key] = self::recursiveMultiByteCharString2HTML($value, $charset);
                }
                return $return_data;
            }
            // integer, float, objects, resources, etc
            return $data;
        }//1291
        public static function MultiByteCharString2HTML($string, $charset='ISO-8859-1'):string {
            $string = (string) $string; // in case trying to pass a numeric (float, int) string, would otherwise return an empty string
            $HTMLstring = '';
            switch (strtolower($charset)) {
                case '1251':
                case '1252':
                case '866':
                case '932':
                case '936':
                case '950':
                case 'big5':
                case 'big5-hkscs':
                case 'cp1251':
                case 'cp1252':
                case 'cp866':
                case 'euc-jp':
                case 'eucjp':
                case 'gb2312':
                case 'ibm866':
                case 'iso-8859-1':
                case 'iso-8859-15':
                case 'iso8859-1':
                case 'iso8859-15':
                case 'koi8-r':
                case 'koi8-ru':
                case 'koi8r':
                case 'shift_jis':
                case 'sjis':
                case 'win-1251':
                case 'windows-1251':
                case 'windows-1252':
                    $HTMLstring = htmlentities($string, ENT_COMPAT, $charset);
                    break;
                case 'utf-8':
                    $strlen = strlen($string);
                    for ($i = 0; $i < $strlen; $i++) {
                        $char_ord_val = ord($string[$i]);
                        $charval = 0;
                        if ($char_ord_val < 0x80) {
                            $charval = $char_ord_val;
                        } elseif ((($char_ord_val & 0xF0) >> 4) === 0x0F  &&  $i+3 < $strlen) {
                            $charval  = (($char_ord_val & 0x07) << 18);
                            $charval += ((ord($string[++$i]) & 0x3F) << 12);
                            $charval += ((ord($string[++$i]) & 0x3F) << 6);
                            $charval +=  (ord($string[++$i]) & 0x3F);
                        } elseif ((($char_ord_val & 0xE0) >> 5) === 0x07  &&  $i+2 < $strlen) {
                            $charval  = (($char_ord_val & 0x0F) << 12);
                            $charval += ((ord($string[++$i]) & 0x3F) << 6);
                            $charval +=  (ord($string[++$i]) & 0x3F);
                        } elseif ((($char_ord_val & 0xC0) >> 6) === 0x03  &&  $i+1 < $strlen) {
                            $charval  = (($char_ord_val & 0x1F) << 6);
                            $charval += (ord($string[++$i]) & 0x3F);
                        }
                        if (($charval >= 32) && ($charval <= 127)) {
                            $HTMLstring .= htmlentities(chr($charval));
                        } else {
                            $HTMLstring .= '&#'.$charval.';';
                        }
                    }
                    break;
                case 'utf-16le':
                    for ($i = 0, $iMax = strlen($string); $i < $iMax; $i += 2) {
                        $charval = self::LittleEndian2Int(substr($string, $i, 2));
                        if (($charval >= 32) && ($charval <= 127)) {
                            $HTMLstring .= chr($charval);
                        } else {
                            $HTMLstring .= '&#'.$charval.';';
                        }
                    }
                    break;
                case 'utf-16be':
                    for ($i = 0, $iMax = strlen($string); $i < $iMax; $i += 2) {
                        $charval = self::BigEndian2Int(substr($string, $i, 2));
                        if (($charval >= 32) && ($charval <= 127)) {
                            $HTMLstring .= chr($charval);
                        } else {
                            $HTMLstring .= '&#'.$charval.';';
                        }
                    }
                    break;

                default:
                    $HTMLstring = 'ERROR: Character set "'.$charset.'" not supported in MultiByteCharString2HTML()';
                    break;
            }
            return $HTMLstring;
        }//1311
        public static function RGADnameLookup($namecode) {
            static $RGADname = [];
            if (empty($RGADname)) {
                $RGADname[0] = 'not set';
                $RGADname[1] = 'Track Gain Adjustment';
                $RGADname[2] = 'Album Gain Adjustment';
            }
            return ($RGADname[$namecode] ?? '');
        }//1408
        public static function RGADoriginatorLookup($originatorcode) {
            static $RGADoriginator = array();
            if (empty($RGADoriginator)) {
                $RGADoriginator[0] = 'unspecified';
                $RGADoriginator[1] = 'pre-set by artist/producer/mastering engineer';
                $RGADoriginator[2] = 'set by user';
                $RGADoriginator[3] = 'determined automatically';
            }
            return ($RGADoriginator[$originatorcode] ?? '');
        }//1424
        public static function RGADadjustmentLookup($rawadjustment, $signbit):float {
            $adjustment = (float) $rawadjustment / 10;
            if ($signbit === 1) {
                $adjustment *= -1;
            }
            return $adjustment;
        }//1442
        public static function RGADgainString($namecode, $originatorcode, $replaygain):float {
            if ($replaygain < 0) {$signbit = '1';
            } else {$signbit = '0';}
            $storedreplaygain = (int)round($replaygain * 10);
            $gainstring  = str_pad(decbin($namecode), 3, '0', STR_PAD_LEFT);
            $gainstring .= str_pad(decbin($originatorcode), 3, '0', STR_PAD_LEFT);
            $gainstring .= $signbit;
            $gainstring .= str_pad(decbin($storedreplaygain), 9, '0', STR_PAD_LEFT);
            return $gainstring;
        }//1457
        public static function RGADamplitude2dB($amplitude):float {
            return 20 * log10($amplitude);
        }//1477
        public static function GetDataImageSize($imgData, &$imageinfo=[]) {
            if (PHP_VERSION_ID >= 50400) {
                $GetDataImageSize = @getimagesizefromstring($imgData, $imageinfo);
                if ($GetDataImageSize === false || !isset($GetDataImageSize[0], $GetDataImageSize[1])) {
                    return false;
                }
                $GetDataImageSize['height'] = $GetDataImageSize[0];
                $GetDataImageSize['width'] = $GetDataImageSize[1];
                return $GetDataImageSize;
            }
            static $tempdir = '';
            if (empty($tempdir)) {
                if (function_exists('sys_get_temp_dir')) {
                    $tempdir = sys_get_temp_dir(); // https://github.com/JamesHeinrich/getID3/issues/52
                }
                // yes this is ugly, feel free to suggest a better way
                $getid3_temp = new getID3();//todo instanceof
                if ($getid3_temp_tempdir = $getid3_temp->tempdir) {
                    $tempdir = $getid3_temp_tempdir;
                }
                unset($getid3_temp, $getid3_temp_tempdir);


            }
            $GetDataImageSize = false;
            if ($tempfilename = tempnam($tempdir, 'gI3')) {
                if (is_writable($tempfilename) && is_file($tempfilename) && ($tmp = fopen($tempfilename, 'wb'))) {
                    fwrite($tmp, $imgData);
                    fclose($tmp);
                    $GetDataImageSize = @getimagesize($tempfilename, $imageinfo);
                    if (($GetDataImageSize === false) || !isset($GetDataImageSize[0],$GetDataImageSize[1])) {
                        return false;
                    }
                    $GetDataImageSize['height'] = $GetDataImageSize[0];
                    $GetDataImageSize['width']  = $GetDataImageSize[1];
                }
                unlink($tempfilename);
            }
            return $GetDataImageSize;
        }//1487
        public static function ImageExtFromMime($mime_type) {
            // temporary way, works OK for now, but should be reworked in the future
            return str_replace(array('image/', 'x-', 'jpeg'), array('', '', 'jpg'), $mime_type);
        }//1534
        public static function CopyTagsToComments(&$ThisFileInfo, $option_tags_html=true):bool {
            if (!empty($ThisFileInfo['tags'])) {
                $processLastTagTypes = array('id3v1','riff');
                foreach ($processLastTagTypes as $processLastTagType) {
                    if (isset($ThisFileInfo['tags'][$processLastTagType])) {
                        $temp = $ThisFileInfo['tags'][$processLastTagType];
                        unset($ThisFileInfo['tags'][$processLastTagType]);
                        $ThisFileInfo['tags'][$processLastTagType] = $temp;
                        unset($temp);
                    }
                }
                foreach ($ThisFileInfo['tags'] as $tagtype => $tagarray) {
                    foreach ($tagarray as $tagname => $tagdata) {
                        foreach ($tagdata as $key => $value) {
                            if (!empty($value)) {
                                if (empty($ThisFileInfo['comments'][$tagname])) {
                                } elseif ($tagtype === 'id3v1') {
                                    $newvaluelength = strlen(trim($value));
                                    foreach ($ThisFileInfo['comments'][$tagname] as $existingkey => $existingvalue) {
                                        $oldvaluelength = strlen(trim($existingvalue));
                                        if (($newvaluelength <= $oldvaluelength) && (strpos($existingvalue, trim($value)) === 0)) {
                                            break 2;
                                        }
                                        if (function_exists('mb_convert_encoding') && trim($value) === trim(substr(mb_convert_encoding($existingvalue, $ThisFileInfo['id3v1']['encoding'], $ThisFileInfo['encoding']), 0, 30))) {
                                            break 2;
                                        }
                                    }

                                } elseif (!is_array($value)) {
                                    $newvaluelength   =    strlen(trim($value));
                                    $newvaluelengthMB = mb_strlen(trim($value));
                                    foreach ($ThisFileInfo['comments'][$tagname] as $existingkey => $existingvalue) {
                                        $oldvaluelength   =    strlen(trim($existingvalue));
                                        $oldvaluelengthMB = mb_strlen(trim($existingvalue));
                                        if (($newvaluelengthMB === $oldvaluelengthMB) && ($existingvalue === self::iconv_fallback('UTF-8', 'ASCII', $value))) {
                                            $ThisFileInfo['comments'][$tagname][$existingkey] = trim($value);
                                            break;
                                        }
                                        if (($newvaluelength > $oldvaluelength) && (strlen($existingvalue) > 10) && (strpos(trim($value), $existingvalue) === 0)) {
                                            $ThisFileInfo['comments'][$tagname][$existingkey] = trim($value);
                                            break;
                                        }
                                    }
                                }
                                if (is_array($value) || empty($ThisFileInfo['comments'][$tagname]) || !in_array(trim($value), $ThisFileInfo['comments'][$tagname], true)) {
                                    $value = (is_string($value) ? trim($value) : $value);
                                    if (!is_int($key) && !ctype_digit($key)) {
                                        $ThisFileInfo['comments'][$tagname][$key] = $value;
                                    } else if (!isset($ThisFileInfo['comments'][$tagname])) {
                                        $ThisFileInfo['comments'][$tagname] = array($value);
                                    } else {
                                        $ThisFileInfo['comments'][$tagname][] = $value;
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($ThisFileInfo['comments'])) {
                    $StandardizeFieldNames = array(
                        'tracknumber' => 'track_number',
                        'track'       => 'track_number',
                    );
                    foreach ($StandardizeFieldNames as $badkey => $goodkey) {
                        if (array_key_exists($badkey, $ThisFileInfo['comments']) && !array_key_exists($goodkey, $ThisFileInfo['comments'])) {
                            $ThisFileInfo['comments'][$goodkey] = $ThisFileInfo['comments'][$badkey];
                            unset($ThisFileInfo['comments'][$badkey]);
                        }
                    }
                }
                // Copy ['comments'] to ['comments_html']
                if ($option_tags_html && !empty($ThisFileInfo['comments'])) {
                    foreach ($ThisFileInfo['comments'] as $field => $values) {
                        if ($field === 'picture') {
                            continue;
                        }
                        foreach ($values as $index => $value) {
                            if (is_array($value)) {
                                $ThisFileInfo['comments_html'][$field][$index] = $value;
                            } else {
                                $ThisFileInfo['comments_html'][$field][$index] = str_replace('&#0;', '', self::MultiByteCharString2HTML($value, $ThisFileInfo['encoding']));
                            }
                        }
                    }
                }
            }
            return true;
        }//1545
        public static function EmbeddedLookup($key, $begin, $end, $file, $name):string {
            static $cache;
            if (isset($cache[$file][$name])) {
                return ($cache[$file][$name][$key] ?? '');
            }
            //$keylength  = strlen($key);//todo ?
            $line_count = $end - $begin - 7;
            // Open php file
            $r = 'r';
            $fp = fopen($file, (string)$r);
            // Discard $begin lines
            for ($i = 0; $i < ($begin + 3); $i++) {
                fgets($fp, 1024);
            }
            // Loop thru line
            while (0 < $line_count--) {
                $line = ltrim(fgets($fp, 1024), "\t ");
                $explodedLine = explode("\t", $line, 2);
                $ThisKey   = ($explodedLine[0] ?? '');
                $ThisValue = ($explodedLine[1] ?? '');
                $cache[$file][$name][$ThisKey] = trim($ThisValue);
            }
            fclose($fp);
            return ($cache[$file][$name][$key] ?? '');
        }//1676
        public static function IncludeDependency($filename, $sourcefile, $DieOnFailure=false) { }//1730 todo
        public static function trimNullByte($string):string {
            return trim($string, "\x00");
        }//1755
        public static function getFileSizeSyscall($path) {
            $commandline = null;
            $filesize = false;
            if (GETID3_OS_ISWINDOWS) {
                    $commandline = 'for %I in ('.escapeshellarg($path).') do @echo %~zI';
            } else {
                $commandline = 'ls -l '.escapeshellarg($path).' | awk \'{print $5}\'';
            }
            if (isset($commandline)) {
                $output = trim(shell_exec($commandline));
                if (ctype_digit($output)) {
                    $filesize = (float) $output;
                }
            }
            return $filesize;
        }//1764
        public static function truepath($filename) {
            // 2017-11-08: this could use some improvement, patches welcome
            if (preg_match('#^(\\\\\\\\|//)[a-z0-9]#i', $filename, $matches)) {
                $goodpath = [];
                foreach (explode('/', str_replace('\\', '/', $filename)) as $part) {
                    if ($part === '.') {
                        continue;
                    }
                    if ($part === '..') {
                        if (count($goodpath)) {
                            array_pop($goodpath);
                        } else {
                            return false;
                        }
                    } else {
                        $goodpath[] = $part;
                    }
                }
                return implode(DIRECTORY_SEPARATOR, $goodpath);
            }
            return realpath($filename);
        }//1794
        public static function mb_basename($path, $suffix = ''):string {
            $splited = explode("/", rtrim($path, '/ '));
            return substr(basename('X'.$splited[count($splited) - 1], $suffix), 1);
        }
    }
}else{die;}