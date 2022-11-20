<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 09:31
 */
namespace TP_Core\Libs\Diff;
if(ABSPATH){
    class TextDiff{
        //todo things to sort out
        protected $_edits;
        public function __construct( $engine, $params ){
            if ($engine === 'auto') $engine = extension_loaded('xdiff') ? 'xdiff' : 'native';
            else $engine = [
                'native' => 'TP_Managers\Diff_Manager\Components\text_diff_engine_native',
                'shell' => 'TP_Managers\Diff_Manager\Components\text_diff_engine_shell',
            ];
            $class = 'Text_Diff_Engine_' . $engine;
            $diff_engine = new $class();
            $this->_edits = call_user_func_array(array($diff_engine, 'diff'), $params);
        }//36
        public function getDiff(){
            return $this->_edits;
        }//68
        public function countAddedLines(): int{
            $count = 0;
            foreach ((array)$this->_edits as $edit) {
                if (is_a($edit, '_text_diff_op_add') ||
                    is_a($edit, '_text_diff_op_change')) {
                    $count += $edit->newfinal();
                }
            }
            return $count;
        }//90
        public function countDeletedLines(): int{
            $count = 0;
            foreach ((array)$this->_edits as $edit) {
                if (is_a($edit, '_text_diff_op_delete') ||
                    is_a($edit, '_text_diff_op_change')) {
                    $count += $edit->no_rig();
                }
            }
            return $count;
        }//99
        public function reverse(): TextDiff{
            if (version_compare(zend_version(), '2', '>'))
                $rev = clone($this);
            else $rev = $this;
            $rev->_edits = array();
            foreach ((array)$this->_edits as $edit)
                $rev->_edits[] = $edit->reverse();
            return $rev;
        }//125
        public function isEmpty(): bool{
            foreach ($this->_edits as $edit) {
                if (!is_a($edit, '_text_diff_op_copy'))return false;
            }
            return true;
        }//144
        public function lcs(): int{
            $lcs = 0;
            foreach ($this->_edits as $edit) {
                if (is_a($edit, '_text_diff_op_copy')) $lcs += count($edit->orig);
            }
            return $lcs;
        }//161
        public function getOriginal(): array{
            $lines = array();
            foreach ($this->_edits as $edit) {
                if ($edit->orig) array_splice($lines, count($lines), 0, $edit->orig);
            }
            return $lines;
        }//179
        public function getFinal(): array{
            $lines = array();
            foreach ($this->_edits as $edit) {
                if ($edit->final) {
                    array_splice($lines, count($lines), 0, $edit->final);
                }
            }
            return $lines;
        }//197
        public static function trimNewlines(&$line): void { //not used , $key
            $line = str_replace(array("\n", "\r"), '', $line);
        }//215
        public static function getTempDir(){
            $tmp_locations = array('/tmp', '/var/tmp', 'c:\WUTemp', 'c:\temp','c:\windows\temp', 'c:\winnt\temp');
            $tmp = ini_get('upload_tmp_dir');
            if ($tmp === '') $tmp = getenv('TMPDIR');
            while ($tmp === '' && count($tmp_locations)) {
                $tmp_check = array_shift($tmp_locations);
                if (@is_dir($tmp_check)) $tmp = $tmp_check;
            }
            return $tmp !== '' ? $tmp : false;
        }//228
        protected function _check($from_lines, $to_lines): bool {
            if (serialize($from_lines) !== serialize($this->getOriginal()))
                trigger_error("Reconstructed original does not match", E_USER_ERROR);
            if (serialize($to_lines) !== serialize($this->getFinal()))
                trigger_error("Reconstructed final does not match", E_USER_ERROR);
            $rev = $this->reverse();
            if (serialize($to_lines) !== serialize($rev->getOriginal()))
                trigger_error("Reversed original does not match", E_USER_ERROR);
            if (serialize($from_lines) !== serialize($rev->getFinal()))
                trigger_error("Reversed final does not match", E_USER_ERROR);
            $prev_type = null;
            foreach ($this->_edits as $edit) {
                if ($edit instanceof $prev_type)
                    trigger_error("Edit sequence is non-optimal", E_USER_ERROR);
                $prev_type = get_class($edit);
            }
            return true;
        }//260
    }
}else die;