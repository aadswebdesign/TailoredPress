<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 14:31
 */
namespace TP_Core\Libs\Diff\Components;
use TP_Core\Libs\Diff\TextDiff;
use TP_Core\Libs\Diff\Factory\_text_diff_op_copy;
if(ABSPATH){
    class text_diff_renderer{
        private $__x = [];
        private $__y = [];
        protected $_leading_context_lines = 0;
        protected $_trailing_context_lines = 0;
        public function __construct($params = []){
            foreach ($params as $param => $value) {
                $v = '_' . $param;
                if (isset($this->$v)) $this->$v = $value;
            }
        }
        public function getParams():array{
            $params = array();
            foreach (get_object_vars($this) as $k => $v) {
                if ($k[0] === '_') $params[substr($k, 1)] = $v;
            }
            return $params;
        }
        public function render(TextDiff $diff):string{
            $xi = $yi = 1;
            $block = false;
            $context = array();
            $new_lead = $this->_leading_context_lines;
            $new_trail = $this->_trailing_context_lines;
            $output = $this->_startDiff();
            $diffs = $diff->getDiff();
            foreach ($diffs as $i => $edit) {
                if (is_a($edit, '_text_diff_op_copy')) {
                    if (is_array($block)) {
                        $keep = $i === count($diffs) - 1 ? $new_trail : $new_lead + $new_trail;
                        if (count($edit->orig) <= $keep)$block[] = $edit;
                        else {
                            if ($new_trail) {
                                $context = array_slice($edit->orig, 0, $new_trail);
                                $block[] = new _text_diff_op_copy($context);
                            }
                            $output .= $this->_block($this->__x[0], $new_trail + $xi - $this->__x[0],
                                $this->__y[0], $new_trail + $yi - $this->__y[0],
                                $block);
                            $block = false;
                        }
                    }
                    $context = $edit->orig;
                }else{
                    if (!is_array($block)) {
                        $context = array_slice($context, count($context) - $new_lead);
                        $this->__x[0] = $xi - count($context);
                        $this->__y[0] = $yi - count($context);
                        $block = array();
                        if ($context) $block[] = new _text_diff_op_copy($context);
                    }
                    $block[] = $edit;
                }
                if ($edit->orig) $xi += count($edit->orig);
                if ($edit->final) $yi += count($edit->final);
            }
            if (is_array($block))
                $output .= $this->_block($this->__x[0], $xi - $this->__x[0], $this->__y[0], $yi - $this->__y[0], $block);
            return $output . $this->_endDiff();
        }
        protected function _block($x_beg, $x_len, $y_beg, $y_len, &$edits):string{
            $output = $this->_startBlock($this->_blockHeader($x_beg, $x_len, $y_beg, $y_len));
            foreach ($edits as $edit) {
                switch (strtolower(get_class($edit))) {
                    case 'text_diff_op_copy':
                        $output .= $this->_context($edit->orig);
                        break;
                    case 'text_diff_op_add':
                        $output .= $this->_added($edit->final);
                        break;
                    case 'text_diff_op_delete':
                        $output .= $this->_deleted($edit->orig);
                        break;
                    case 'text_diff_op_change':
                        $output .= $this->_changed($edit->orig, $edit->final);
                        break;
                }
            }
            return $output . $this->_endBlock();
        }//151
        protected function _startDiff():string{return '';}//178
        protected function _endDiff():string{return '';}//184
        protected function _blockHeader($x_beg, $x_len, $y_beg, $y_len):string{
            if ($x_len > 1) $x_beg .= ',' . ($x_beg + $x_len - 1);
            if ($y_len > 1) $y_beg .= ',' . ($y_beg + $y_len - 1);
            if ($x_len && !$y_len) $y_beg--;
            elseif (!$x_len) $x_beg--;
            $_y_len = ($y_len ? 'c' : 'd');
            return $x_beg . ($x_len ? $_y_len : 'a') . $y_beg;
        }//188
        protected function _startBlock($header):string{return $header . "\n";}//207
        protected function _endBlock():string{ return ''; }//212
        protected function _lines($lines, $prefix = ' '):string{
            return $prefix . implode("\n$prefix", $lines) . "\n";
        }
        protected function _context($lines):string{
            return $this->_lines($lines, '  ');
        }
        protected function _added($lines):string{
            return $this->_lines($lines, '> ');
        }
        protected function _deleted($lines):string{
            return $this->_lines($lines, '< ');
        }
        protected function _changed($orig, $final):string{
            return $this->_deleted($orig) . "---\n" . $this->_added($final);
        }
    }
}else die;