<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 09:25
 */
namespace TP_Core\Libs\Diff\Components;
use TP_Core\Libs\Diff\TextDiff;
use TP_Core\Libs\Diff\Factory\_text_diff_op_add;
use TP_Core\Libs\Diff\Factory\_text_diff_op_change;
use TP_Core\Libs\Diff\Factory\_text_diff_op_copy;
use TP_Core\Libs\Diff\Factory\_text_diff_op_delete;
if(ABSPATH){
    class text_diff_engine_shell{
        protected $_diff_command = 'diff';
        public function diff($from_lines, $to_lines):array{
            array_walk($from_lines, array('Text_Diff', 'trimNewlines'));
            array_walk($to_lines, array('Text_Diff', 'trimNewlines'));
            $temp_dir = TextDiff::getTempDir();
            $from_file = tempnam($temp_dir, 'Text_Diff');
            $to_file = tempnam($temp_dir, 'Text_Diff');
            $fp = fopen($from_file, 'wb');
            fwrite($fp, implode("\n", $from_lines));
            fclose($fp);
            $fp = fopen($to_file, 'wb');
            fwrite($fp, implode("\n", $to_lines));
            fclose($fp);
            $diff = shell_exec($this->_diff_command . ' ' . $from_file . ' ' . $to_file);
            unlink($from_file);
            unlink($to_file);
            if (is_null($diff)) return array(new _text_diff_op_copy($from_lines));
            $from_line_no = 1;
            $to_line_no = 1;
            $edits = [];
            preg_match_all('#^(\d+)(?:,(\d+))?([adc])(\d+)(?:,(\d+))?$#m', $diff, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                if (!isset($match[5])) $match[5] = false;
                if ($match[3] === 'a')  $from_line_no--;
                if ($match[3] === 'd')  $to_line_no--;
                if ($from_line_no < $match[1] || $to_line_no < $match[4]) {
                    // copied lines
                    assert($match[1] - $from_line_no === $match[4] - $to_line_no);
                    $edits[] = new _text_diff_op_copy($this->__getLines($from_lines, $from_line_no, $match[1] - 1), $this->__getLines($to_lines, $to_line_no, $match[4] - 1));
                }
                switch ($match[3]) {
                    case 'd':
                        $edits[] = new _text_diff_op_delete(
                            $this->__getLines($from_lines, $from_line_no, $match[2]));
                        $to_line_no++;
                        break;
                    case 'c':
                        $edits[] = new _text_diff_op_change(
                            $this->__getLines($from_lines, $from_line_no, $match[2]),
                            $this->__getLines($to_lines, $to_line_no, $match[5]));
                        break;
                    case 'a':
                        // added lines
                        $edits[] = new _text_diff_op_add(
                            $this->__getLines($to_lines, $to_line_no, $match[5]));
                        $from_line_no++;
                        break;
                }
            }
            if (!empty($from_lines)) {
                $edits[] = new _text_diff_op_copy(
                    $this->__getLines($from_lines, $from_line_no,
                        $from_line_no + count($from_lines) - 1),
                    $this->__getLines($to_lines, $to_line_no,
                        $to_line_no + count($to_lines) - 1));
            }
            return $edits;
        }
        private function __getLines(&$text_lines, &$line_no, $end = false):array{
            if (!empty($end)) {
                $lines = [];
                while ($line_no <= $end) {
                    $lines[] = array_shift($text_lines);
                    $line_no++;
                }
            } else {
                $lines = array(array_shift($text_lines));
                $line_no++;
            }
            return $lines;
        }//145
    }
}else die;