<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 11:35
 */
namespace TP_Core\Libs\Diff\Components;
use TP_Core\Libs\Diff\Factory\_text_diff_op_add;
use TP_Core\Libs\Diff\Factory\_text_diff_op_change;
use TP_Core\Libs\Diff\Factory\_text_diff_op_copy;
use TP_Core\Libs\Diff\Factory\_text_diff_op_delete;
if(ABSPATH){
    class text_diff_engine_string{
        public function diff($diff, $mode = 'autodetect'){
            $line_break = "\n";
            if (strpos($diff, "\r\n") !== false) $line_break = "\r\n";
            elseif (strpos($diff, "\r") !== false) $line_break = "\r";
            if (substr($diff, -strlen($line_break)) !== $line_break) $diff .= $line_break;
            if ($mode !== 'autodetect' && $mode !== 'context' && $mode !== 'unified')
                /** @noinspection PhpUndefinedClassInspection */
                return PEAR::raiseError('Type of diff is unsupported');
            if ($mode === 'autodetect') {
                $context = strpos($diff, '***');
                $unified = strpos($diff, '---');
                if ($context === $unified)
                    /** @noinspection PhpUndefinedClassInspection */
                    return PEAR::raiseError('Type of diff could not be detected');
                elseif ($context === false || $unified === false)
                    $mode = $context !== false ? 'context' : 'unified';
                else $mode = $context < $unified ? 'context' : 'unified';
            }
            $diff = explode($line_break, $diff);
            if (($mode === 'context' && strpos($diff[0], '***') === 0) ||
                ($mode === 'unified' && strpos($diff[0], '---') === 0)) {
                array_shift($diff);
                array_shift($diff);
            }
            if ($mode === 'context')
                return $this->parseContextDiff($diff);
            else return $this->parseUnifiedDiff($diff);
        }
        public function parseUnifiedDiff($diff):array{
            $edits = array();
            $end = count($diff) - 1;
            for ($i = 0; $i < $end;) {
                $diff1 = array();
                switch (substr($diff[$i], 0, 1)) {
                    case ' ':
                        do {
                            $diff1[] = substr($diff[$i], 1);
                        } while (++$i < $end && strpos($diff[$i], ' ') === 0);
                        $edits[] = new _text_diff_op_copy($diff1);
                        break;
                    case '+':
                        do {
                            $diff1[] = substr($diff[$i], 1);
                        } while (++$i < $end && strpos($diff[$i], '+') === 0);
                        $edits[] = new _text_diff_op_add($diff1);
                        break;
                    case '-':
                        $diff2 = array();
                        do {
                            $diff1[] = substr($diff[$i], 1);
                        } while (++$i < $end && strpos($diff[$i], '-') === 0);
                        while ($i < $end && strpos($diff[$i], '+') === 0)
                            $diff2[] = substr($diff[$i++], 1);
                        if (count($diff2) === 0) $edits[] = new _text_diff_op_delete($diff1);
                        else $edits[] = new _text_diff_op_change($diff1, $diff2);
                        break;
                    default:
                        $i++;
                        break;
                }
            }
            return $edits;
        }
        public function parseContextDiff(&$diff):array{
            $edits = array();
            $i = $max_i = $j = $max_j = 0;
            $end = count($diff) - 1;
            while ($i < $end && $j < $end) {
                while ($i >= $max_i && $j >= $max_j) {
                    /** @noinspection LoopWhichDoesNotLoopInspection */
                    for ($i = $j;
                         $i < $end && strpos($diff[$i], '***') === 0;
                         $i++){}
                    /** @noinspection LoopWhichDoesNotLoopInspection */
                    for ($max_i = $i;
                         $max_i < $end && strpos($diff[$max_i], '---') !== 0;
                         $max_i++){}
                    /** @noinspection LoopWhichDoesNotLoopInspection */
                    for ($j = $max_i;
                         $j < $end && strpos($diff[$j], '---') === 0;
                         $j++){}
                    /** @noinspection LoopWhichDoesNotLoopInspection */
                    for ($max_j = $j;
                         $max_j < $end && strpos($diff[$max_j], '***') !== 0;
                         $max_j++){}
                }
                $array = array();
                while ($i < $max_i &&
                    $j < $max_j &&
                    strcmp($diff[$i], $diff[$j]) === 0) {
                    $array[] = substr($diff[$i], 2);
                    $i++;
                    $j++;
                }
                while ($i < $max_i && ($max_j-$j) <= 1) {
                    if ($diff[$i] !== '' && strpos($diff[$i], ' ') !== 0) break;
                    $array[] = substr($diff[$i++], 2);
                }
                while ($j < $max_j && ($max_i-$i) <= 1) {
                    if ($diff[$j] !== '' && strpos($diff[$j], ' ') !== 0) break;
                    $array[] = substr($diff[$j++], 2);
                }
                if (count($array) > 0) {
                    $edits[] = new _text_diff_op_copy($array);
                }
                if ($i < $max_i) {
                    $diff1 = array();
                    switch (substr($diff[$i], 0, 1)) {
                        case '!':
                            $diff2 = array();
                            do {
                                $diff1[] = substr($diff[$i], 2);
                                if ($j < $max_j && strpos($diff[$j], '!') === 0)
                                    $diff2[] = substr($diff[$j++], 2);
                            } while (++$i < $max_i && strpos($diff[$i], '!') === 0);
                            $edits[] = new _text_diff_op_change($diff1, $diff2);
                            break;

                        case '+':
                            do {
                                $diff1[] = substr($diff[$i], 2);
                            } while (++$i < $max_i && strpos($diff[$i], '+') === 0);
                            $edits[] = new _text_diff_op_add($diff1);
                            break;

                        case '-':
                            do {
                                $diff1[] = substr($diff[$i], 2);
                            } while (++$i < $max_i && strpos($diff[$i], '-') === 0);
                            $edits[] = new _text_diff_op_delete($diff1);
                            break;
                    }
                }
                if ($j < $max_j) {
                    $diff2 = array();
                    switch (substr($diff[$j], 0, 1)) {
                        case '+':
                            do {
                                $diff2[] = substr($diff[$j++], 2);
                            } while ($j < $max_j && strpos($diff[$j], '+') === 0);
                            $edits[] = new _text_diff_op_add($diff2);
                            break;

                        case '-':
                            do {
                                $diff2[] = substr($diff[$j++], 2);
                            } while ($j < $max_j && strpos($diff[$j], '-') === 0);
                            $edits[] = new _text_diff_op_delete($diff2);
                            break;
                    }
                }
            }
            return $edits;
        }
    }
}else die;