<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-3-2022
 * Time: 03:34
 */
namespace TP_Core\Libs\SimplePie\Depedencies;
use TP_Core\Libs\SimplePie\Factory\_idna_vars;
if(ABSPATH){
    class idna_convert{
        use _idna_vars;
        public function __construct(...$options){
            $this->_set_last = $this->_sbase + $this->_lcount * $this->_vcount * $this->_tcount;
            if (is_array($options)) $this->set_parameter($options);
            if (self::$_mb_string_overload === null)
                self::$_mb_string_overload = (extension_loaded('mbstring')&&(ini_get('mbstring.func_overload') & 0x02) === 0x02);
        }
        public function set_parameter($option, $value = false):bool{
            if (!is_array($option)) $option = array($option => $value);
            foreach ($option as $k => $v) {
                switch ($k) {
                    case 'encoding':
                        switch ($v) {
                            case 'utf8':
                            case 'ucs4_string':
                            case 'ucs4_array':
                                $this->_api_encoding = $v;
                                break;
                            default:
                                $this->_error('Set Parameter: Unknown parameter '.$v.' for option '.$k);
                                return false;
                        }
                        break;
                    case 'overlong':
                        $this->_allow_overlong = ($v) ? true : false;
                        break;
                    case 'strict':
                        $this->_strict_mode = ($v) ? true : false;
                        break;
                    case 'idn_version':
                        if (in_array($v, array('2003', '2008'),true)) $this->_idn_version = $v;
                        else $this->_error('Set Parameter: Unknown parameter '.$v.' for option '.$k);
                        break;
                    case 'encode_german_sz': // Deprecated
                        if (!$v) self::$_idna_chars['replace_maps'][0xDF] = array(0x73, 0x73);
                        else unset(self::$_idna_chars['replace_maps'][0xDF]);
                        break;
                    default:
                        $this->_error('Set Parameter: Unknown option '.$k);
                        return false;
                }
            }
            return true;
        }
        public function decode($input, $one_time_encoding = false){
            if ($one_time_encoding) {
                switch ($one_time_encoding) {
                    case 'utf8':
                    case 'ucs4_string':
                    case 'ucs4_array':
                        break;
                    default:
                        $this->_error('Unknown encoding '.$one_time_encoding);
                        return false;
                }
                $input = trim($input);
            }
            if (strpos($input, '@')) { // Maybe it is an email address
                // No no in strict mode
                if ($this->_strict_mode) {
                    $this->_error('Only simple domain name parts can be handled in strict mode');
                    return false;
                }
                @list ($email_pref, $input) = explode('@', $input, 2);
                $arr = explode('.', $input);
                foreach ($arr as $k => $v) {
                    if (preg_match('!^'.preg_quote($this->_puny_code_prefix, '!').'!', $v)) {
                        $convert = $this->_decode($v);
                        if ($convert) $arr[$k] = $convert;
                    }
                }
                $input = implode('.', $arr);
                $arr = explode('.', $email_pref);
                foreach ($arr as $k => $v) {
                    if (preg_match('!^'.preg_quote($this->_puny_code_prefix, '!').'!', $v)) {
                        $convert = $this->_decode($v);
                        if ($convert) $arr[$k] = $convert;
                    }
                }
                $email_pref = implode('.', $arr);
                $return = $email_pref . '@' . $input;
            }
            elseif (preg_match('![:\./]!', $input)) { // Or a complete domain name (with or without paths / parameters)
                if ($this->_strict_mode) {
                    $this->_error('Only simple domain name parts can be handled in strict mode');
                    return false;
                }
                $parsed = parse_url($input);
                if (isset($parsed['host'])) {
                    $arr = explode('.', $parsed['host']);
                    foreach ($arr as $k => $v) {
                        $convert = $this->_decode($v);
                        if ($convert) $arr[$k] = $convert;
                    }
                    $parsed['host'] = implode('.', $arr);
                    $return =
                        (empty($parsed['scheme']) ? '' : $parsed['scheme'].(strtolower($parsed['scheme']) === 'mailto' ? ':' : '://'))
                        .(empty($parsed['user']) ? '' : $parsed['user'].(empty($parsed['pass']) ? '' : ':'.$parsed['pass']).'@')
                        .$parsed['host']
                        .(empty($parsed['port']) ? '' : ':'.$parsed['port'])
                        .(empty($parsed['path']) ? '' : $parsed['path'])
                        .(empty($parsed['query']) ? '' : '?'.$parsed['query'])
                        .(empty($parsed['fragment']) ? '' : '#'.$parsed['fragment']);
                } else {
                    $arr = explode('.', $input);
                    foreach ($arr as $k => $v) {
                        $convert = $this->_decode($v);
                        /** @noinspection ElvisOperatorCanBeUsedInspection */
                        $arr[$k] = ($convert) ? $convert : $v;
                    }
                    $return = implode('.', $arr);
                }
            } else {
                $return = $this->_decode($input);
                if (!$return) $return = $input;
            }
            switch (($one_time_encoding) ?: $this->_api_encoding) {
                case 'utf8':
                    return $return;
                    break;
                case 'ucs4_string':
                    return $this->_ucs4_to_ucs4_string($this->_utf8_to_ucs4($return));
                    break;
                case 'ucs4_array':
                    return $this->_utf8_to_ucs4($return);
                    break;
                default:
                    $this->_error('Unsupported output format');
                    return false;
            }
        }
        public function encode($decoded, $one_time_encoding = false){
            switch ($one_time_encoding ?: $this->_api_encoding) {
                case 'utf8':
                    $decoded = $this->_utf8_to_ucs4($decoded);
                    continue;
                case 'ucs4_string':
                    $decoded = $this->_ucs4_string_to_ucs4($decoded);
                    continue;
                case 'ucs4_array':
                    break;
                default:
                    $this->_error('Unsupported input format: '.($one_time_encoding ?: $this->_api_encoding));
                    return false;
            }
            if (empty($decoded)) return '';
            $last_begin = 0;
            $output = '';
            foreach ($decoded as $k => $v) {
                switch($v) {
                    case 0x3002:
                    case 0xFF0E:
                    case 0xFF61:
                        $decoded[$k] = 0x2E;
                        continue 2;
                    case 0x2E:
                    case 0x2F:
                    case 0x3A:
                    case 0x3F:
                    case 0x40:
                        if ($this->_strict_mode) {
                            $this->_error('Neither email addresses nor URLs are allowed in strict mode.');
                            return false;
                        }
                    if ($k) {
                        $encoded = $this->_encode(array_slice($decoded, $last_begin, (($k)-$last_begin)));
                        if ($encoded) $output .= $encoded;
                        else $output .= $this->_ucs4_to_utf8(array_slice($decoded, $last_begin, (($k)-$last_begin)));
                        $output .= chr($decoded[$k]);
                    }
                    $last_begin = $k + 1;
                }
            }
            if ($last_begin) {
                $inp_len = count($decoded);
                $encoded = $this->_encode(array_slice($decoded, $last_begin, (($inp_len)-$last_begin)));
                if ($encoded) $output .= $encoded;
                else $output .= $this->_ucs4_to_utf8(array_slice($decoded, $last_begin, (($inp_len)-$last_begin)));
                return $output;
            }
            if ($output = $this->_encode($decoded)) return $output;
            else return $this->_ucs4_to_utf8($decoded);
        }
        public function encode_uri($uri){
            $parsed = parse_url($uri);
            if (!isset($parsed['host'])) {
                $this->_error('The given string does not look like a URI');
                return false;
            }
            $arr = explode('.', $parsed['host']);
            foreach ($arr as $k => $v) {
                $con_v = $this->encode($v, 'utf8');
                if ($con_v) $arr[$k] = $con_v;
            }
            $parsed['host'] = implode('.', $arr);
            $return =
                (empty($parsed['scheme']) ? '' : $parsed['scheme'].(strtolower($parsed['scheme']) === 'mailto' ? ':' : '://'))
                .(empty($parsed['user']) ? '' : $parsed['user'].(empty($parsed['pass']) ? '' : ':'.$parsed['pass']).'@')
                .$parsed['host']
                .(empty($parsed['port']) ? '' : ':'.$parsed['port'])
                .(empty($parsed['path']) ? '' : $parsed['path'])
                .(empty($parsed['query']) ? '' : '?'.$parsed['query'])
                .(empty($parsed['fragment']) ? '' : '#'.$parsed['fragment']);
            return $return;
        }
        public function get_last_error():bool{
            return $this->_error;
        }
        protected function _decode($encoded){
            if (!preg_match('!^'.preg_quote($this->_puny_code_prefix, '!').'!', $encoded)) {
                $this->_error('This is not a puny_code string');
                return false;
            }
            $decoded = array();
            $encode_test = preg_replace('!^'.preg_quote($this->_puny_code_prefix, '!').'!', '', $encoded);
            if (!$encode_test) {
                $this->_error('The given encoded string was empty');
                return false;
            }
            $del_im_pos = strrpos($encoded, '-');
            if ($del_im_pos > self::_byte_length($this->_puny_code_prefix)) {
                for ($k = self::_byte_length($this->_puny_code_prefix); $k < $del_im_pos; ++$k)
                    $decoded[] = ord($encoded{$k});
            }
            $deco_len = count($decoded);
            $en_co_len = self::_byte_length($encoded);
            $is_first = true;
            $bias = $this->_initial_bias;
            $idx = 0;
            $char = $this->_initial_n;
            for ($en_co_idx = ($del_im_pos) ? ($del_im_pos + 1) : 0; $en_co_idx < $en_co_len; ++$deco_len) {
                for ($old_idx = $idx, $w = 1, $k = $this->_base; 1 ; $k += $this->_base) {
                    $digit = $this->_decode_digit($encoded{$en_co_idx++});
                    $idx += $digit * $w;
                    $_k1 = ($k >= $bias + $this->_tmax) ? $this->_tmax : ($k - $bias);
                    $t = ($k <= $bias) ? $this->_tmin : $_k1;
                    if ($digit < $t) break;
                    $w *= ($this->_base - $t);
                }
                $bias = $this->_adapt($idx - $old_idx, $deco_len + 1, $is_first);
                $is_first = false;
                $char += (int) ($idx / ($deco_len + 1));
                $idx %= ($deco_len + 1);
                if ($deco_len > 0) {
                    for ($i = $deco_len; $i > $idx; $i--) $decoded[$i] = $decoded[($i - 1)];
                }
                $decoded[$idx++] = $char;
            }
            return $this->_ucs4_to_utf8($decoded);
        }
        protected function _encode($decoded):string{
            $extract = self::_byte_length($this->_puny_code_prefix);
            $check_pref = $this->_utf8_to_ucs4($this->_puny_code_prefix);
            $check_deco = array_slice($decoded, 0, $extract);
            if ($check_pref === $check_deco) {
                $this->_error('This is already a puny_code string');
                return false;
            }
            $encode_able = false;
            foreach ($decoded as $k => $v) {
                if ($v > 0x7a) {
                    $encode_able = true;
                    break;
                }
            }
            if (!$encode_able) {
                $this->_error('The given string does not contain encodable chars');
                return false;
            }
            $decoded = $this->_name_prep($decoded);
            if (!$decoded || !is_array($decoded)) return false;
            $deco_len  = count($decoded);
            if (!$deco_len) return false;
            $code_count = 0;
            $encoded = '';
            foreach ($decoded as $iValue) {
                $test = $iValue;
                // Will match [-0-9a-zA-Z]
                if ((0x2F < $test && $test < 0x40) || (0x40 < $test && $test < 0x5B)
                    || (0x60 < $test && $test <= 0x7B) || (0x2D === $test)) {
                    $encoded .= chr($iValue);
                    $code_count++;
                }
            }
            if ($code_count === $deco_len) return $encoded;
            $encoded = $this->_puny_code_prefix.$encoded;
            if ($code_count) $encoded .= '-';//or an underscore
            $is_first = true;
            $cur_code = $this->_initial_n;
            $bias = $this->_initial_bias;
            $delta = 0;
            $next_code = [];
            while ($code_count < $deco_len) {
                foreach ($decoded as $iValue) {
                    if ($iValue >= $cur_code && $iValue <= $next_code)
                        $next_code = $iValue;
                }
                $delta += ($next_code - $cur_code) * ($code_count + 1);
                $cur_code = $next_code;
                foreach ($decoded as $iValue) {
                    if ($iValue < $cur_code) {
                        $delta++;
                    } elseif ($iValue === $cur_code) {
                        for ($q = $delta, $k = $this->_base; 1; $k += $this->_base) {
                            $_k1 = ($k >= $bias + $this->_tmax) ? $this->_tmax : $k - $bias;
                            $t = ($k <= $bias) ? $this->_tmin : $_k1;
                            if ($q < $t) break;
                            $encoded .= $this->_encode_digit(($t + (($q - $t) % ($this->_base - $t)))); //v0.4.5 Changed from ceil() to intval()
                            $q = (int) (($q - $t) / ($this->_base - $t));
                        }
                        $encoded .= $this->_encode_digit($q);
                        $bias = $this->_adapt($delta, $code_count+1, $is_first);
                        $code_count++;
                        $delta = 0;
                        $is_first = false;
                    }
                }
                $delta++;
                $cur_code++;
            }
            return $encoded;
        }
        protected function _adapt($delta, $new_points, $is_first):int{
            $delta = (int)($is_first ? ($delta / $this->_damp) : ($delta / 2));
            $delta += (int)($delta / $new_points);
            for ($k = 0; $delta > (($this->_base - $this->_tmin) * $this->_tmax) / 2; $k += $this->_base)
                $delta = (int)($delta / ($this->_base - $this->_tmin));
            return (int)($k + ($this->_base - $this->_tmin + 1) * $delta / ($delta + $this->_skew));
        }
        protected function _encode_digit($d):string{
            return chr($d + 22 + 75 * ($d < 26));
        }
        protected function _decode_digit($cp):int{
            $cp = ord($cp);
            $_cp1 = ($cp - 97 < 26)? $cp - 97 : $this->_base;
            $_cp2 = ($cp - 65 < 26) ? $cp - 65 : $_cp1;
            return ($cp - 48 < 10) ? $cp - 22 : $_cp2;
        }
        protected function _error($error = ''):void{
            $this->_error = $error;
        }
        protected function _name_prep($input): mixed{
            $output = [];
            foreach ($input as $v) {
                if (in_array($v, self::$_idna_chars['map_nothing'],true)) continue;
                if (in_array($v, self::$_idna_chars['prohibit'],true) || in_array($v, self::$_idna_chars['general_prohibited'],true)) {
                    $this->_error('NAME_PREP: Prohibited input U+'.sprintf('%08X', $v));
                    return false;
                }
                foreach (self::$_idna_chars['prohibit_ranges'] as $range) {
                    if ($range[0] <= $v && $v <= $range[1]) {
                        $this->_error('NAME_PREP: Prohibited input U+'.sprintf('%08X', $v));
                        return false;
                    }
                }
                if (0xAC00 <= $v && $v <= 0xD7AF) {
                    foreach ($this->_hangul_decompose($v) as $out) $output[] = (int) $out;
                } elseif (($this->_idn_version === '2003') && isset(self::$_idna_chars['replace_maps'][$v])) {
                    foreach ($this->_apply_canonical_ordering(self::$_idna_chars['replace_maps'][$v]) as $out)
                        $output[] = (int) $out;
                } else $output[] = (int) $v;
            }
            $output = $this->_hangul_compose($output);
            $last_class = 0;
            $last_starter = 0;
            $out_len = count($output);
            foreach ($output as $i => $iValue) {
                $class = $this->_get_combining_class($iValue);
                if ((!$last_class || $last_class > $class) && $class) {
                    $seq_len = $i - $last_starter;
                    $out = $this->_combine(array_slice($output, $last_starter, $seq_len));
                    if ($out) {
                        $output[$last_starter] = $out;
                        if (count($out) !== $seq_len) {
                            for ($j = $i+1; $j < $out_len; ++$j) $output[$j-1] = $output[$j];
                            unset($output[$out_len]);
                        }
                        $i--;
                        $out_len--;
                        $last_class = ($i === $last_starter) ? 0 : $this->_get_combining_class($output[$i-1]);
                        continue;
                    }
                }
                if (!$class) $last_starter = $i; $last_class = $class;
            }
            return $output;
        }
        protected function _hangul_decompose($char): array{
            $set_index = (int) $char - $this->_sbase;
            if ($set_index < 0 || $set_index >= $this->_scount) return array($char);
            $result = [];
            $result[] = (int) $this->_lbase + $set_index / $this->_ncount;
            $result[] = (int) $this->_vbase + ($set_index % $this->_ncount) / $this->_tcount;
            $T = ($this->_tbase + $set_index % $this->_tcount);
            if ($T !== $this->_tbase) $result[] = $T;
            return $result;
        }
        protected function _hangul_compose($input):array{
            $inp_len = count($input);
            if (!$inp_len) return array();
            $result = array();
            $last = (int) $input[0];
            $result[] = $last; // copy first char from input to output
            for ($i = 1; $i < $inp_len; ++$i) {
                $char = (int) $input[$i];
                $s_index = $last - $this->_sbase;
                $l_index = $last - $this->_lbase;
                $v_index = $char - $this->_vbase;
                $t_index = $char - $this->_tbase;
                if (0 <= $s_index && $s_index < $this->_scount && ($s_index % $this->_tcount === 0)
                    && 0 <= $t_index && $t_index <= $this->_tcount) {
                    $last += $t_index;
                    $result[(count($result) - 1)] = $last; // reset last
                    continue;
                }
                if (0 <= $l_index && $l_index < $this->_lcount && 0 <= $v_index && $v_index < $this->_vcount) {
                    $last = (int) $this->_sbase + ($l_index * $this->_vcount + $v_index) * $this->_tcount;
                    $result[(count($result) - 1)] = $last; // reset last
                    continue;
                }
                $last = $char;
                $result[] = $char;
            }
            return $result;
        }
        protected function _get_combining_class($char){
            return self::$_idna_chars['norm_combines'][$char] ?? 0;
        }
        protected function _apply_canonical_ordering($input){
            $swap = true;
            $size = count($input);
            while ($swap) {
                $swap = false;
                $last = $this->_get_combining_class((int)($input[0]));
                for ($i = 0; $i < $size-1; ++$i) {
                    $next = $this->_get_combining_class((int)($input[$i+1]));
                    if ($next !== 0 && $last > $next) {
                        // Move item leftward until it fits
                        for ($j = $i + 1; $j > 0; --$j) {
                            if ($this->_get_combining_class((int)($input[$j-1])) <= $next) break;
                            $t = (int)($input[$j]);
                            $input[$j] = (int)($input[$j-1]);
                            $input[$j-1] = $t;
                            $swap = true;
                        }
                        $next = $last;
                    }
                    $last = $next;
                }
            }
            return $input;
        }
        protected function _combine($input){
            $inp_len = count($input);
            if (0 === $inp_len) return false;
            foreach (self::$_idna_chars['replace_maps'] as $np_src => $np_target) {
                if ($np_target[0] !== $input[0]) continue;
                if (count($np_target) !== $inp_len) continue;
                $hit = false;
                foreach ($input as $k2 => $v2) {
                    if ($v2 === $np_target[$k2]) $hit = true;
                    else {
                        $hit = false;
                        break;
                    }
                }
                if ($hit) return $np_src;
            }
            return false;
        }
        protected function _utf8_to_ucs4($input){
            $output = [];
            $out_len = 0;
            $inp_len = self::_byte_length($input);
            $mode = 'next';
            $test = 'none';
            $start_byte = null;
            $next_byte = null;
            for ($k = 0; $k < $inp_len; ++$k){
                $v = ord($input{$k});
                if ($v < 128) {
                    $output[$out_len] = $v;
                    ++$out_len;
                    if ('add' === $mode) {
                        $this->_error('Conversion from UTF-8 to UCS-4 failed: malformed input at byte '.$k);
                        return false;
                    }
                    continue;
                }
                if ('next' === $mode) { // Try to find the next start byte; determine the width of the Unicode char
                    $start_byte = $v;
                    $mode = 'add';
                    $test = 'range';
                    if ($v >> 5 === 6) { // &110xxxxx 10xxxxx
                        $next_byte = 0; // Tells, how many times subsequent bitmasks must rotate 6bits to the left
                        $v = ($v - 192) << 6;
                    } elseif ($v >> 4 === 14) { // &1110xxxx 10xxxxxx 10xxxxxx
                        $next_byte = 1;
                        $v = ($v - 224) << 12;
                    } elseif ($v >> 3 === 30) { // &11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
                        $next_byte = 2;
                        $v = ($v - 240) << 18;
                    } elseif ($v >> 2 === 62) { // &111110xx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
                        $next_byte = 3;
                        $v = ($v - 248) << 24;
                    } elseif ($v >> 1 === 126) { // &1111110x 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx 10xxxxxx
                        $next_byte = 4;
                        $v = ($v - 252) << 30;
                    } else {
                        $this->_error('This might be UTF-8, but I don\'t understand it at byte '.$k);
                        return false;
                    }
                    if ('add' === $mode) {
                        $output[$out_len] = $v;
                        ++$out_len;
                        continue;
                    }
                }
                if ('add' === $mode) {
                    if (!$this->_allow_overlong && $test === 'range') {
                        $test = 'none';
                        if (($v < 0xA0 && $start_byte === 0xE0) || ($v < 0x90 && $start_byte === 0xF0) || ($v > 0x8F && $start_byte === 0xF4)) {
                            $this->_error('Bogus UTF-8 character detected (out of legal range) at byte '.$k);
                            return false;
                        }
                    }
                    if ($v >> 6 === 2) { // Bit mask must be 10xxxxxx
                        $v = ($v - 128) << ($next_byte * 6);
                        $output[($out_len - 1)] += $v;
                        --$next_byte;
                    } else {
                        $this->_error('Conversion from UTF-8 to UCS-4 failed: malformed input at byte '.$k);
                        return false;
                    }
                    if ($next_byte < 0) $mode = 'next';
                }
            }
            return $output;
        }
        protected function _ucs4_to_utf8($input){
            $output = '';
            foreach ($input as $k => $v) {
                if ($v < 128) $output .= chr($v);
                elseif ($v < (1 << 11))
                    $output .= chr(192+($v >> 6)).chr(128+($v & 63));
                elseif ($v < (1 << 16))
                    $output .= chr(224+($v >> 12)).chr(128+(($v >> 6) & 63)).chr(128+($v & 63));
                elseif ($v < (1 << 21))
                    $output .= chr(240+($v >> 18)).chr(128+(($v >> 12) & 63)).chr(128+(($v >> 6) & 63)).chr(128+($v & 63));
                else {
                    $this->_error('Conversion from UCS-4 to UTF-8 failed: malformed input at byte '.$k);
                    return false;
                }
            }
            return $output;
        }
        protected function _ucs4_to_ucs4_string($input):string{
            $output = '';
            foreach ($input as $v)  $output .= chr(($v >> 24) & 255).chr(($v >> 16) & 255).chr(($v >> 8) & 255).chr($v & 255);
            return $output;
        }
        protected function _ucs4_string_to_ucs4($input){
            $output = array();
            $inp_len = self::_byte_length($input);
            if ($inp_len % 4) {
                $this->_error('Input UCS4 string is broken');
                return false;
            }
            if (!$inp_len) return $output;
            for ($i = 0, $out_len = -1; $i < $inp_len; ++$i) {
                // Increment output position every 4 input bytes
                if (!($i % 4)) {
                    $out_len++;
                    $output[$out_len] = 0;
                }
                $output[$out_len] += ord($input{$i}) << (8 * (3 - ($i % 4) ) );
            }
            return $output;
        }
        protected static function _byte_length($string){
            if (self::$_mb_string_overload)
                return mb_strlen($string, '8bit');
            return strlen((binary) $string);
        }
        public function getInstance($params = array()): idna_convert{
            return new idna_convert($params);
        }
        public function singleton($params = array()){
            static $instances;
            if (!isset($instances)) $instances = array();
            $signature = serialize($params);
            if (!isset($instances[$signature]))
                $instances[$signature] = $this->getInstance($params);
            return $instances[$signature];
        }
    }
}else die;