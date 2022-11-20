<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 07:22
 */
namespace TP_Core\Libs\Diff\Components;
use TP_Core\Libs\Diff\Factory\_text_diff_op_add;
use TP_Core\Libs\Diff\Factory\_text_diff_op_copy;
use TP_Core\Libs\Diff\Factory\_text_diff_op_change;
use TP_Core\Libs\Diff\Factory\_text_diff_op_delete;
if(ABSPATH){
    class text_diff_engine_native{
        protected $_flip = false;
        protected $_in_seq;
        protected $_lcs;
        protected $_seq;
        protected $_x_changed;
        protected $_x_ind;
        protected $_x_value;
        protected $_y_changed;
        protected $_y_ind;
        protected $_y_value;
        public function diff($from_lines, $to_lines): array{
            array_walk($from_lines, array('Text_Diff', 'trimNewlines'));
            array_walk($to_lines, array('Text_Diff', 'trimNewlines'));
            $n_from = count($from_lines);
            $n_to = count($to_lines);
            $this->_x_changed = $this->_y_changed = [];
            $this->_x_value = $this->_y_value = [];
            $this->_x_ind = $this->_y_ind = [];
            unset($this->_seq,$this->_in_seq,$this->_lcs);
            for ($skip = 0; $skip < $n_from && $skip < $n_to; $skip++){
                if ($from_lines[$skip] !== $to_lines[$skip])break;
            }
            $xi = $n_from; $yi = $n_to;
            for ($end_skip = 0; --$xi > $skip && --$yi > $skip; $end_skip++){
                if ($from_lines[$xi] !== $to_lines[$yi])break;
            }
            for ($xi = $skip; $xi < $n_from - $end_skip; $xi++) $x_hash[$from_lines[$xi]] = 1;
            for ($yi = $skip; $yi < $n_to - $end_skip; $yi++) {
                $line = $to_lines[$yi];
                if (($this->_y_changed[$yi] = empty($x_hash[$line]))) continue;
                $y_hash[$line] = 1;
                $this->_y_value[] = $line;
                $this->_y_ind[] = $yi;
            }
            for ($xi = $skip; $xi < $n_from - $end_skip; $xi++) {
                $line = $from_lines[$xi];
                if (($this->_x_changed[$xi] = empty($y_hash[$line]))) continue;
                $this->_x_value[] = $line;
                $this->_x_ind[] = $xi;
            }
            $this->_compare_seq(0, count($this->_x_value), 0, count($this->_y_value));
            $this->_shift_boundaries($from_lines, $this->_x_changed, $this->_y_changed);
            $this->_shift_boundaries($to_lines, $this->_y_changed, $this->_x_changed);
            $edits = array();
            $xi = $yi = 0;
            while ($xi < $n_from || $yi < $n_to) {
                assert($yi < $n_to || $this->_x_changed[$xi]);
                assert($xi < $n_from || $this->_y_changed[$yi]);
                $copy = [];
                while ($xi < $n_from && $yi < $n_to && !$this->_x_changed[$xi] && !$this->_y_changed[$yi]) {
                    $copy[] = $from_lines[$xi++];
                    ++$yi;
                }
                if ($copy) $edits[] = new _text_diff_op_copy($copy);
                $delete = [];
                while ($xi < $n_from && $this->_x_changed[$xi]) $delete[] = $from_lines[$xi++];
                $add = [];
                while ($yi < $n_to && $this->_y_changed[$yi]) $add[] = $to_lines[$yi++];
                if ($delete && $add) $edits[] = new _text_diff_op_change($delete, $add);
                elseif ($delete) $edits[] = new _text_diff_op_delete($delete);
                elseif ($add) $edits[] = new _text_diff_op_add($add);
            }
            return $edits;
        }//31
        public function _lcs_pos($y_pos):int{
            $end = $this->_lcs;
            if ($end === 0 || $y_pos > $this->_seq[$end]) {
                $this->_seq[++$this->_lcs] = $y_pos;
                $this->_in_seq[$y_pos] = 1;
                return $this->_lcs;
            }
            $begin = 1;
            while ($begin < $end) {
                $mid = (int)(($begin + $end) / 2);
                if ($y_pos > $this->_seq[$mid]) $begin = $mid + 1;
                else $end = $mid;
            }
            assert($y_pos !== $this->_seq[$end]);
            $this->_in_seq[$this->_seq[$end]] = false;
            $this->_seq[$end] = $y_pos;
            $this->_in_seq[$y_pos] = 1;
            return $end;
        }//232
        protected function _diagnose ($x_off, $x_lim, $y_off, $y_lim, $n_chunks):array{
            if ($x_lim - $x_off > $y_lim - $y_off) {
                $this->_flip = true;
                @list ($x_off, $x_lim, $y_off, $y_lim) = array($y_off, $y_lim, $x_off, $x_lim);
            }
            if ($this->_flip) {
                for ($i = $y_lim - 1; $i >= $y_off; $i--) $y_matches[$this->_x_value[$i]][] = $i;
            } else {
                for ($i = $y_lim - 1; $i >= $y_off; $i--) $y_matches[$this->_y_value[$i]][] = $i;
            }
            $this->_lcs = 0;
            $this->_seq[0]= $y_off - 1;
            $this->_in_seq = [];
            $y_midst[0] = [];
            $number = $x_lim - $x_off + $n_chunks - 1;
            $x = $x_off;
            for ($chunk = 0; $chunk < $n_chunks; $chunk++){
                if ($chunk > 0) {
                    for ($i = 0; $i <= $this->_lcs; $i++) $y_midst[$i][$chunk - 1] = $this->_seq[$i];
                }
                $x1 = $x_off + (int)(($number + ($x_lim - $x_off) * $chunk) / $n_chunks);
                $k = null;
                for (; $x < $x1; $x++){
                    $line = $this->_flip ? $this->_y_value[$x] : $this->_x_value[$x];
                    if (empty($y_matches[$line])) continue;
                    $matches = $y_matches[$line];
                    reset($matches);
                    while ($y = current($matches)) {
                        if (empty($this->_in_seq[$y])) {
                            $k = $this->_lcs_pos($y);
                            assert($k > 0);
                            $y_midst[$k] = $y_midst[$k - 1];
                            break;
                        }
                        next($matches);
                    }
                    while ($y = current($matches)) {
                        if ($y > $this->_seq[$k - 1]) {
                            assert($y <= $this->_seq[$k]);
                            $this->_in_seq[$this->_seq[$k]] = false;
                            $this->_seq[$k] = $y;
                            $this->_in_seq[$y] = 1;
                        } elseif (empty($this->_in_seq[$y])) {
                            $k = $this->_lcs_pos($y);
                            assert($k > 0);
                            $y_midst[$k] = $y_midst[$k - 1];
                        }
                        next($matches);
                    }
                }
            }
            $sep_s[] = $this->_flip ? array($y_off, $x_off) : array($x_off, $y_off);
            $y_mid = $y_midst[$this->_lcs];
            for ($n = 0; $n < $n_chunks - 1; $n++) {
                $x1 = $x_off + (int)(($number + ($x_lim - $x_off) * $n) / $n_chunks);
                $y1 = $y_mid[$n] + 1;
                $sep_s[] = $this->_flip ? array($y1, $x1) : array($x1, $y1);
            }
            $sep_s[] = $this->_flip ? array($y_lim, $x_lim) : array($x_lim, $y_lim);
            return array($this->_lcs, $sep_s);
        }//150
        protected function _compare_seq ($x_off, $x_lim, $y_off, $y_lim):void{
            while ($x_off < $x_lim && $y_off < $y_lim && $this->_x_value[$x_off] === $this->_y_value[$y_off]) {
                ++$x_off;
                ++$y_off;
            }
            while ($x_lim > $x_off && $y_lim > $y_off && $this->_x_value[$x_lim - 1] === $this->_y_value[$y_lim - 1]) {
                --$x_lim;
                --$y_lim;
            }
            if ($x_off === $x_lim || $y_off === $y_lim) $lcs = 0;
            else {
                $n_chunks = min(7, $x_lim - $x_off, $y_lim - $y_off) + 1;
                @list($lcs, $seps) = $this->_diagnose($x_off, $x_lim, $y_off, $y_lim, $n_chunks);
            }
            if ($lcs === 0) {
                while ($y_off < $y_lim) $this->_y_changed[$this->_y_ind[$y_off++]] = 1;
                while ($x_off < $x_lim) $this->_x_changed[$this->_x_ind[$x_off++]] = 1;
            } else {
                reset($seps);
                $pt1 = $seps[0];
                while ($pt2 = next($seps)) {
                    $this->_compare_seq ($pt1[0], $pt2[0], $pt1[1], $pt2[1]);
                    $pt1 = $pt2;
                }
            }
        }//271
        protected function _shift_boundaries($lines, &$changed, $other_changed): void{
            $i = 0;
            $j = 0;
            assert(count($lines) === count($changed));
            $len = count($lines);
            $other_len = count($other_changed);
            while (1){
                while ($j < $other_len && $other_changed[$j])$j++;
                while ($i < $len && ! $changed[$i]) {
                    assert($j < $other_len && ! $other_changed[$j]);
                    $i++; $j++;
                    while ($j < $other_len && $other_changed[$j]) $j++;
                }
                if ($i === $len) break;
                $start = $i;
                while (++$i < $len && $changed[$i]) continue;
                do {
                    $run_length = $i - $start;
                    while ($start > 0 && $lines[$start - 1] === $lines[$i - 1]) {
                        $changed[--$start] = 1;
                        $changed[--$i] = false;
                        while ($start > 0 && $changed[$start - 1]) $start--;
                        assert($j > 0);
                        while ($other_changed[--$j]) continue;
                        assert($j >= 0 && !$other_changed[$j]);
                    }
                    $corresponding = $j < $other_len ? $i : $len;
                    while ($i < $len && $lines[$start] === $lines[$i]) {
                        $changed[$start++] = false;
                        $changed[$i++] = 1;
                        while ($i < $len && $changed[$i]) $i++;
                        assert($j < $other_len && ! $other_changed[$j]);
                        $j++;
                        if ($j < $other_len && $other_changed[$j]) {
                            $corresponding = $i;
                            while ($j < $other_len && $other_changed[$j]) $j++;
                        }
                    }
                } while ($run_length !== $i - $start);
                while ($corresponding < $i) {
                    $changed[--$start] = 1;
                    $changed[--$i] = 0;
                    assert($j > 0);
                    while ($other_changed[--$j]) continue;
                    assert($j >= 0 && !$other_changed[$j]);
                }
            }
        }//330
    }
}else die;