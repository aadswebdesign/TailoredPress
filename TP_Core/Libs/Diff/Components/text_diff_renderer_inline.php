<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 16:13
 */
namespace TP_Core\Libs\Diff\Components;
use TP_Core\Libs\Diff\TextDiff;
if(ABSPATH){
    class text_diff_renderer_inline extends text_diff_renderer{
        protected const DEL_PREFIX = '<del>';
        protected const DEL_SUFFIX = '</del>';
        protected const INS_PREFIX = '<ins>';
        protected const INS_SUFFIX = '</ins>';
        protected $_block_header = '';
        protected $_leading_context_lines = 10000;
        protected $_split_characters = false;
        protected $_split_level = 'lines';
        protected $_trailing_context_lines = 10000;
        protected function _blockHeader($x_beg, $x_len, $y_beg, $y_len):string{
            return $this->_block_header;
        }
        protected function _startBlock($header):string{
            return $header;
        }
        protected function _lines($lines, $prefix = ' ', $encode = true):string{
            if ($encode) array_walk($lines, array(&$this, '_encode'));
            if ($this->_split_level === 'lines') return implode("\n", $lines) . "\n";
            else return implode('', $lines);
        }
        protected function _added($lines):string{
            array_walk($lines, array(&$this, '_encode'));
            $lines[0] = self::INS_PREFIX . $lines[0];
            $lines[count($lines) - 1] .= self::INS_SUFFIX;
            return $this->_lines($lines, ' ', false);
        }
        protected function _deleted($lines, $words = false):string{
            array_walk($lines, array(&$this, '_encode'));
            $lines[0] = self::DEL_PREFIX . $lines[0];
            $lines[count($lines) - 1] .= self::DEL_SUFFIX;
            return $this->_lines($lines, ' ', false);
        }
        protected function _changed($orig, $final):string{
            if ($this->_split_level === 'characters') {
                return $this->_deleted($orig)
                . $this->_added($final);
            }
            if ($this->_split_level === 'words') {
                $prefix = '';
                while ($orig[0] !== false && $final[0] !== false &&
                    strpos($orig[0], ' ') === 0 &&
                    strpos($final[0], ' ') === 0) {
                    $prefix .= substr($orig[0], 0, 1);
                    $orig[0] = substr($orig[0], 1);
                    $final[0] = substr($final[0], 1);
                }
                return $prefix . $this->_deleted($orig) . $this->_added($final);
            }
            $text1 = implode("\n", $orig);
            $text2 = implode("\n", $final);
            /* Non-printing newline marker. */
            $nl = "\0";
            if ($this->_split_characters) {
                $diff = new TextDiff('native',
                    array(preg_split('//', $text1),
                        preg_split('//', $text2)));
            } else $diff = new TextDiff('native', array($this->_splitOnWords($text1, $nl), $this->_splitOnWords($text2, $nl)));
            $renderer = new self();
            (array_merge($this->getParams(),
                array('split_level' => $this->_split_characters ? 'characters' : 'words')));
            return str_replace($nl, "\n", $renderer->render($diff)) . "\n";
        }
        protected function _splitOnWords($string, $newlineEscape = "\n"):string{
            $string = str_replace("\0", '', $string);
            $words = array();
            $length = strlen($string);
            $pos = 0;
            while ($pos < $length) {
                // Eat a word with any preceding whitespace.
                $spaces = strspn(substr($string, $pos), " \n");
                $next_pos = strcspn(substr($string, $pos + $spaces), " \n");
                $words[] = str_replace("\n", $newlineEscape, substr($string, $pos, $spaces + $next_pos));
                $pos += $spaces + $next_pos;
            }
            return $words;
        }
        protected function _encode(&$string):string{
            $string = htmlspecialchars($string);
        }
    }
}else die;