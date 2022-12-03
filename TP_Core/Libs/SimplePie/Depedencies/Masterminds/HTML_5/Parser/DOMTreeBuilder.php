<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 11:43
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Parser;
use TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Elements;
use TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\InstructionProcessor;
use TP_Core\Libs\SimplePie\Factory\_mm_vars;
if(ABSPATH){
    class DOMTreeBuilder implements EventHandler{
        public const OPT_DISABLE_HTML_NS = 'disable_html_ns';
        public const OPT_TARGET_DOC = 'target_document';
        public const OPT_IMPLICIT_NS = 'implicit_namespaces';
        public const IM_INITIAL = 0;
        public const IM_BEFORE_HTML = 1;
        public const IM_BEFORE_HEAD = 2;
        public const IM_IN_HEAD = 3;
        public const IM_IN_HEAD_NOSCRIPT = 4;
        public const IM_AFTER_HEAD = 5;
        public const IM_IN_BODY = 6;
        public const IM_TEXT = 7;
        public const IM_IN_TABLE = 8;
        public const IM_IN_TABLE_TEXT = 9;
        public const IM_IN_CAPTION = 10;
        public const IM_IN_COLUMN_GROUP = 11;
        public const IM_IN_TABLE_BODY = 12;
        public const IM_IN_ROW = 13;
        public const IM_IN_CELL = 14;
        public const IM_IN_SELECT = 15;
        public const IM_IN_SELECT_IN_TABLE = 16;
        public const IM_AFTER_BODY = 17;
        public const IM_IN_FRAMESET = 18;
        public const IM_AFTER_FRAMESET = 19;
        public const IM_AFTER_AFTER_BODY = 20;
        public const IM_AFTER_AFTER_FRAMESET = 21;
        public const IM_IN_SVG = 22;
        public const IM_IN_MATHML = 23;
        use _mm_vars;
        public function __construct($isFragment = false, array $options = array()){
            $this->_implicit_namespaces = array(
                'xml' => W3_XML_NS,
                'xmlns' => W3_XMLNS,
                'xlink' => W3_XLINK,
            );
            $this->_options = $options;
            if (isset($options[self::OPT_TARGET_DOC]))
                $this->_doc = $options[self::OPT_TARGET_DOC];
            else {
                $impl = new \DOMImplementation();
                $dt = $impl->createDocumentType('html');
                $this->_doc = $impl->createDocument(null, '', $dt);
                $this->_doc->encoding = !empty($options['encoding']) ? $options['encoding'] : 'UTF-8';
            }
            $this->_errors = array();
            $this->_current = $this->_doc; // ->documentElement;
            $this->_rules = new TreeBuildingRules();
            $implicitNS = array();
            if (isset($this->_options[self::OPT_IMPLICIT_NS]))
                $implicitNS = $this->_options[self::OPT_IMPLICIT_NS];
            elseif (isset($this->_options['implicitNamespaces']))
                $implicitNS = $this->_options['implicitNamespaces'];
            array_unshift($this->_ns_stack, $implicitNS + array('' => W3_XHTML) + $this->_implicit_namespaces);
            if ($isFragment) {
                $this->_insert_mode = static::IM_IN_BODY;
                $this->_frag = $this->_doc->createDocumentFragment(); //
                $this->_current = $this->_frag;
                $this->_parent_current = $this->_doc->createDocument();
            }
        }
        public function get_document(): \DOMDocument{
            return $this->_doc;
        }
        public function get_fragment(): \DOMDocumentFragment{
            return $this->_frag;
        }
        public function setInstructionProcessor(InstructionProcessor $process):void{
            $this->_processor = $process;
        }
        public function doctype($name, $idType = 0, $id = null, $quirks = false):void{
            $this->_quirks = $quirks;
            if ($this->_insert_mode > static::IM_INITIAL) {
                $this->parseError('Illegal placement of DOCTYPE tag. Ignoring: ' . $name);
                return;
            }
            $this->_insert_mode = static::IM_BEFORE_HTML;
        }
        public function startTag($name, $attributes = array(), $self_closing = false){
            $l_name = $this->_normalizeTagName($name);
            if (!$this->_doc->documentElement && 'html' !== $name && !$this->_frag)
                $this->startTag('html');
            if ($this->_insert_mode === static::IM_INITIAL) {
                $this->_quirks = true;
                $this->parseError('No DOCTYPE specified.');
            }
            if ('image' === $name && !($this->_insert_mode === static::IM_IN_SVG || $this->_insert_mode === static::IM_IN_MATHML))
                $name = 'img';
            if ($this->_insert_mode >= static::IM_IN_BODY && Elements::isA($name, Elements::AUTOCLOSE_P))
                $this->_auto_close('p');
            switch ($name) {
                case 'html':
                    $this->_insert_mode = static::IM_BEFORE_HEAD;
                    break;
                case 'head':
                    if ($this->_insert_mode > static::IM_BEFORE_HEAD)
                        $this->parseError('Unexpected head tag outside of head context.');
                    else $this->_insert_mode = static::IM_IN_HEAD;
                    break;
                case 'body':
                    $this->_insert_mode = static::IM_IN_BODY;
                    break;
                case 'svg':
                    $this->_insert_mode = static::IM_IN_SVG;
                    break;
                case 'math':
                    $this->_insert_mode = static::IM_IN_MATHML;
                    break;
                case 'noscript':
                    if ($this->_insert_mode === static::IM_IN_HEAD)
                        $this->_insert_mode = static::IM_IN_HEAD_NOSCRIPT;
                    break;
            }
            if ($this->_insert_mode === static::IM_IN_SVG)
                $l_name = Elements::normalizeSvgElement($l_name);
            $pushes = 0;
            if (isset($this->_ns_roots[$l_name]) && $this->_ns_stack[0][''] !== $this->_ns_roots[$l_name]) {
                array_unshift($this->_ns_stack, array(
                        '' => $this->_ns_roots[$l_name],
                    ) + $this->_ns_stack[0]);
                ++$pushes;
            }
            $needsWorkaround = false;
            if (isset($this->_options['xmlNamespaces']) && $this->_options['xmlNamespaces']) {
                foreach ($attributes as $aName => $aVal) {
                    if ('xmlns' === $aName) {
                        $needsWorkaround = $aVal;
                        array_unshift($this->_ns_stack, array(
                                '' => $aVal,
                            ) + $this->_ns_stack[0]);
                        ++$pushes;
                    } elseif ('xmlns' === (($pos = strpos($aName, ':')) ? substr($aName, 0, $pos) : '')) {
                        array_unshift($this->_ns_stack, array(
                                substr($aName, $pos + 1) => $aVal,
                            ) + $this->_ns_stack[0]);
                        ++$pushes;
                    }
                }
            }
            if ($this->_only_inline && Elements::isA($l_name, Elements::BLOCK_TAG)) {
                $this->_auto_close($this->_only_inline);
                $this->_only_inline = null;
            }
            try {
                $prefix = ($pos = strpos($l_name, ':')) ? substr($l_name, 0, $pos) : '';
                if (false !== $needsWorkaround) {
                    $xml = "<$l_name xmlns=\"$needsWorkaround\" " . ($prefix !== '' && isset($this->_ns_stack[0][$prefix]) ? ("xmlns:$prefix=\"" . $this->_ns_stack[0][$prefix] . '"') : '') . '/>';
                    $frag = new \DOMDocument();
                    $frag->loadXML($xml);
                    $ele = $this->_doc->importNode($frag->documentElement, true);
                } else if (!isset($this->_ns_stack[0][$prefix]) || ('' === $prefix && isset($this->_options[self::OPT_DISABLE_HTML_NS]) && $this->_options[self::OPT_DISABLE_HTML_NS]))
                    $ele = $this->_doc->createElement($l_name);
                else $ele = $this->_doc->createElementNS($this->_ns_stack[0][$prefix], $l_name);
            } catch (\DOMException $e) {
                $this->parseError("Illegal tag name: <$l_name>. Replaced with <invalid>.");
                $ele = $this->_doc->createElement('invalid');
            }
            if (Elements::isA($l_name, Elements::BLOCK_ONLY_INLINE)) $this->_only_inline = $l_name;
            if ($pushes > 0 && !Elements::isA($name, Elements::VOID_TAG))
                $this->_pushes[spl_object_hash($ele)] = array($pushes, $ele);
            foreach ($attributes as $aName => $aVal) {
                if ('xmlns' === $aName) continue;
                if ($this->_insert_mode === static::IM_IN_SVG)
                    $aName = Elements::normalizeSvgAttribute($aName);
                elseif ($this->_insert_mode === static::IM_IN_MATHML)
                    $aName = Elements::normalizeMathMlAttribute($aName);
                $aVal = (string) $aVal;
                try {
                    $prefix = ($pos = strpos($aName, ':')) ? substr($aName, 0, $pos) : false;
                    if ('xmlns' === $prefix)
                        $ele->setAttributeNS(W3_XML_NS, $aName, $aVal);
                    elseif (false !== $prefix && isset($this->_ns_stack[0][$prefix]))
                        $ele->setAttributeNS($this->_ns_stack[0][$prefix], $aName, $aVal);
                    else $ele->setAttribute($aName, $aVal);
                } catch (\DOMException $e) {
                    $this->parseError("Illegal attribute name for tag $name. Ignoring: $aName");
                    continue;
                }
                if ('id' === $aName) $ele->setIdAttribute('id', true);
            }
            if ($this->_frag !== $this->_current && $this->_rules->hasRules($name))
                $this->_current = $this->_rules->evaluate($ele, $this->_current);
            else {
                $this->_current->appendChild($ele);
                if (!Elements::isA($name, Elements::VOID_TAG)) $this->_current = $ele;
                if (Elements::isHtml5Element($name)) $self_closing = false;
            }
            if ($this->_insert_mode <= static::IM_BEFORE_HEAD && 'head' !== $name && 'html' !== $name)
                $this->_insert_mode = static::IM_IN_BODY;
            if ($pushes > 0 && Elements::isA($name, Elements::VOID_TAG)) {
                for ($i = 0; $i < $pushes; ++$i) array_shift($this->_ns_stack);
            }
            if ($self_closing) $this->endTag($name);
            return Elements::element($name);
        }
        public function endTag($name):void{
            $l_name = $this->_normalizeTagName($name);
            if ('br' === $name) {
                $this->parseError('Closing tag encountered for void element br.');
                $this->startTag('br');
            }
            elseif (Elements::isA($name, Elements::VOID_TAG))return;
            if ($this->_insert_mode <= static::IM_BEFORE_HTML) {
                if (in_array($name, array('html','br','head','title',))) {
                    $this->startTag('html');
                    $this->endTag($name);
                    $this->_insert_mode = static::IM_BEFORE_HEAD;
                    return;
                }
                $this->parseError('Illegal closing tag at global scope.');
                return;
            }
            if ($this->_insert_mode === static::IM_IN_SVG) $l_name = Elements::normalizeSvgElement($l_name);
            $cid = spl_object_hash($this->_current);
            if ('html' === $l_name) return;
            if (isset($this->_pushes[$cid])) {
                for ($i = 0; $i < $this->_pushes[$cid][0]; ++$i)
                    array_shift($this->_ns_stack);
                unset($this->_pushes[$cid]);
            }
            if (!$this->_auto_close($l_name))
                $this->parseError('Could not find closing tag for ' . $l_name);
            switch ($l_name) {
                case 'head':
                    $this->_insert_mode = static::IM_AFTER_HEAD;
                    break;
                case 'body':
                    $this->_insert_mode = static::IM_AFTER_BODY;
                    break;
                case 'svg':
                case 'mathml':
                    $this->_insert_mode = static::IM_IN_BODY;
                    break;
            }
        }
        public function comment($cdata):void{
            $node = $this->_doc->createComment($cdata);
            $this->_current->appendChild($node);
        }
        public function text($data):void{
            if ($this->_insert_mode < static::IM_IN_HEAD) {
                $dataTmp = trim($data, " \t\n\r\f");
                if (!empty($dataTmp))   $this->parseError('Unexpected text. Ignoring: ' . $dataTmp);
                return;
            }
            $node = $this->_doc->createTextNode($data);
            $this->_current->appendChild($node);
        }
        public function eof():void{
            // If the $current isn't the $root, do we need to do anything?
        }
        public function parseError($msg, $line = 0, $col = 0):void {
            $this->_errors[] = sprintf('Line %d, Col %d: %s', $line, $col, $msg);
        }
        public function getErrors():array{
            return $this->_errors;
        }
        public function cdata($data):void{
            $node = $this->_doc->createCDATASection($data);
            $this->_current->appendChild($node);
        }
        public function processingInstruction($name, $data = null):void{
            if ($this->_insert_mode === static::IM_INITIAL && 'xml' === strtolower($name))
                return;
            if ($this->_processor instanceof InstructionProcessor) {
                $res = $this->_processor->process($this->_parent_current, $name, $data);
                if (!empty($res)) $this->_current = $res;
                return;
            }
            $node = $this->_doc->createProcessingInstruction($name, $data);

            $this->_current->appendChild($node);
        }
        protected function _normalizeTagName($tagName){
            return $tagName;
        }
        protected function _quirksTreeResolver($name):void{
            throw new \InvalidArgumentException($name . ',Not implemented.');
        }
        protected function _auto_close($tagName):bool{
            $working = $this->_parent_current;
            do {
                if (XML_ELEMENT_NODE !== $working->nodeType) {
                    return false;
                }
                if ($working->tagName === $tagName) {
                    $this->_current = $working->parentNode;
                    return true;
                }
            } while ($working = $working->parentNode);
            return false;
        }
        protected function _is_ancestor($tagName):bool{
            $candidate = $this->_parent_current;
            while (XML_ELEMENT_NODE === $candidate->nodeType) {
                if ($candidate->tagName === $tagName)
                    return true;
                $candidate = $candidate->parentNode;
            }
            return false;
        }
        protected function is_parent($tagName):bool{
            return $this->_parent_current->tagName === $tagName;
        }
    }
}else die;