<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 14:22
 */
namespace TP_Core\Libs\Diff\Components;
use TP_Core\Libs\Diff\Factory\_text_diff_op_add;
use TP_Core\Libs\Diff\Factory\_text_diff_op_copy;
use TP_Core\Libs\Diff\Factory\_text_diff_op_delete;
if(ABSPATH){
    class text_diff_engine_xdiff{
        public function diff($from_lines, $to_lines):string{
            array_walk($from_lines, array('Text_Diff', 'trimNewlines'));
            array_walk($to_lines, array('Text_Diff', 'trimNewlines'));
            $from_string = implode("\n", $from_lines);
            $to_string = implode("\n", $to_lines);
            $diff = xdiff_string_diff($from_string, $to_string, count($to_lines));
            $diff = explode("\n", $diff);
            $edits = array();
            foreach ($diff as $line) {
                if ($line === '') continue;
                switch ($line[0]) {
                    case ' ':
                        $edits[] = new _text_diff_op_copy(array(substr($line, 1)));
                        break;
                    case '+':
                        $edits[] = new _text_diff_op_add(array(substr($line, 1)));
                        break;
                    case '-':
                        $edits[] = new _text_diff_op_delete(array(substr($line, 1)));
                        break;
                }
            }
            return $edits;
        }
    }
}else die;