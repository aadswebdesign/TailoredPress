<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 11:47
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Serializer;
use TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Elements;
use TP_Core\Libs\SimplePie\Factory\_sp_chars;
use TP_Core\Libs\SimplePie\Factory\_mm_vars;
if(ABSPATH){
    class OutputRules{
        use _sp_chars;
        use _mm_vars;
        public const IM_IN_HTML = 1;
        public const IM_IN_SVG = 2;
        public const IM_IN_MATHML = 3;
        public const DOCTYPE = '<!DOCTYPE html>';
        public function __construct($output, $options = array()){
            $this->_implicit_namespaces = array(
                W3_XHTML,
                W3_SVG,
                W3_MATHML,
                W3_XML_NS,
                W3_XMLNS,
            );
            if (isset($options['encode_entities'])) $this->_encode = $options['encode_entities'];
            $this->_output_mode = static::IM_IN_HTML;
            $this->_out = $output;
            $this->_has_html_5 = defined('ENT_HTML5');
        }
        public function addRule(array $rule):void{
            $this->_non_boolean_attributes[] = $rule;
        }
        public function setTraverser(Traverser $traverser):string{
            $this->_until_tag = $traverser;
            return $this;
        }
        public function unsetTraverser():string{
            $this->_until_tag = null;
            return $this;
        }
        public function document(\DOMDocument $dom,Traverser $traverser):void{
            $this->doctype();
            if ($dom->documentElement) {
                foreach ($dom->childNodes as $node) $traverser->node($node);
                $this->nl();
            }
        }
        protected function doctype():void {
            $this->wr(static::DOCTYPE);
            $this->nl();
        }
        public function element(\DOMElement $ele,Traverser $traverser):void{
            $name = $ele->tagName;
            if ($traverser->isLocalElement($ele)) $name = $ele->localName;
            if ('svg' === $name) {
                $this->_output_mode = static::IM_IN_SVG;
                $name = Elements::normalizeSvgElement($name);
            } elseif ('math' === $name) $this->_output_mode = static::IM_IN_MATHML;
            $this->openTag($ele, $traverser);
            if (Elements::isA($name, Elements::TEXT_RAW)) {
                foreach ($ele->childNodes as $child) {
                    if ($child instanceof \DOMCharacterData) $this->wr($child->data);
                    elseif ($child instanceof \DOMElement)  $this->element($child,$traverser);
                }
            } else {
                if ($ele->hasChildNodes()) $traverser->children($ele->childNodes);
                if ('svg' === $name || 'math' === $name) $this->_output_mode = static::IM_IN_HTML;
            }
            if (!Elements::isA($name, Elements::VOID_TAG)) $this->closeTag($ele,$traverser);
        }
        public function text(\DOMElement $ele):void {
            if (isset($ele->parentNode,$ele->parentNode->tagName) && Elements::isA($ele->parentNode->localName, Elements::TEXT_RAW)) {
                $this->wr($ele['data']);
                return;
            }
            // FIXME: This probably needs some flags set.
            $this->wr($this->enc($ele['data']));
        }
        public function cdata($ele):void{
            $this->wr($ele->ownerDocument->saveXML($ele));
        }
        public function comment($ele):void{
            $this->wr($ele->ownerDocument->saveXML($ele));
        }
        public function processorInstruction($ele):void{
            $this->wr('<?')->wr($ele->target)->wr(' ')->wr($ele->data)->wr('?>');
        }
        protected function namespaceAttrs($ele):void{
            if (!$this->_xpath || $this->_xpath->document !== $ele->ownerDocument)
                $this->_xpath = new \DOMXPath($ele->ownerDocument);
            foreach ($this->_xpath->query('namespace::*[not(.=../../namespace::*)]', $ele) as $nsNode) {
                if (!in_array($nsNode->nodeValue, $this->_implicit_namespaces,true))
                    $this->wr(' ')->wr($nsNode->nodeName)->wr('="')->wr($nsNode->nodeValue)->wr('"');
            }
        }
        protected function openTag(\DOMElement $ele,Traverser $traverser):void{
            $this->wr('<')->wr($traverser->isLocalElement($ele) ? $ele->localName : $ele->tagName);
            $this->attrs($ele,$traverser);
            $this->namespaceAttrs($ele);
            if ($this->_output_mode === static::IM_IN_HTML) $this->wr('>');
            else if ($ele->hasChildNodes()) $this->wr('>');
            else $this->wr(' />');
        }

        protected function attrs(\DOMElement $ele,Traverser $traverser){
            // FIXME: Needs support for xml, xmlns, xlink, and namespaced elements.
            if (!$ele->hasAttributes()) return $this;
            // TODO: Currently, this always writes name="value", and does not do value-less attributes.
            $map = $ele->attributes;
            $len = $map->length;
            for ($i = 0; $i < $len; ++$i) {
                $node = $map->item($i);
                $val = $this->enc($traverser->node($node)->value, true); //$node->value
                $name = $node->nodeName;
                if ($this->_output_mode === static::IM_IN_SVG)
                   $name = Elements::normalizeSvgAttribute($name);
                elseif ($this->_output_mode === static::IM_IN_MATHML)
                    $name = Elements::normalizeMathMlAttribute($name);
                $this->wr(' ')->wr($name);
                if ((isset($val) && '' !== $val) || $this->nonBooleanAttribute($name)) {
                    $this->wr('="')->wr($val)->wr('"');
                }
            }
            return '';
        }
        protected function nonBooleanAttribute(\DOMAttr $attr):bool{
            $ele = $attr->ownerElement;
            foreach ($this->_non_boolean_attributes as $rule) {
                if (isset($rule['nodeNamespace']) && $rule['nodeNamespace'] !== $ele->namespaceURI) continue;
                if (isset($rule['attNamespace']) && $rule['attNamespace'] !== $attr->namespaceURI) continue;
                if (isset($rule['nodeName']) && !is_array($rule['nodeName']) && $rule['nodeName'] !== $ele->localName) continue;
                if (isset($rule['nodeName']) && is_array($rule['nodeName']) && !in_array($ele->localName, $rule['nodeName'], true)) continue;
                if (isset($rule['attrName']) && !is_array($rule['attrName']) && $rule['attrName'] !== $attr->localName) continue;
                if (isset($rule['attrName']) && is_array($rule['attrName']) && !in_array($attr->localName, $rule['attrName'], true)) continue;
                if (isset($rule['xpath'])) {
                    $xp = $this->getXPath($attr);
                    if (isset($rule['prefixes'])) {
                        foreach ($rule['prefixes'] as $nsPrefix => $ns) $xp->registerNamespace($nsPrefix, $ns);
                    }
                    if (!$xp->evaluate($rule['xpath'], $attr)) continue;
                }
                return true;
            }
            return false;
        }
        private function getXPath(\DOMNode $node): \DOMXPath{
            if (!$this->_xpath) $this->_xpath = new \DOMXPath($node->ownerDocument);
            return $this->_xpath;
        }
        protected function closeTag(\DOMElement $ele,Traverser $traverser):void{
            if ($this->_output_mode === static::IM_IN_HTML || $ele->hasChildNodes())
                $this->wr('</')->wr($traverser->isLocalElement($ele) ? $ele->localName : $ele->tagName)->wr('>');//todo
        }
        protected function wr(string $text):mixed{
            fwrite($this->_out, $text);
            return $this;
        }
        protected function nl():mixed {
            fwrite($this->_out, PHP_EOL);
            return $this;
        }
        protected function enc($text, $attribute = false){
            if (!$this->_encode) return $this->escape($text, $attribute);
            if ($this->_has_html_5) return htmlentities($text, ENT_HTML5 | ENT_SUBSTITUTE | ENT_QUOTES, 'UTF-8', false);
            else return strtr($text, self::$chars_to_names);
        }
        protected function escape($text, $attribute = false):string{
            if ($attribute) {
                $replace = array(
                    '"' => '&quot;',
                    '&' => '&amp;',
                    "\xc2\xa0" => '&nbsp;',
                );
            } else {
                $replace = array(
                    '<' => '&lt;',
                    '>' => '&gt;',
                    '&' => '&amp;',
                    "\xc2\xa0" => '&nbsp;',
                );
            }
            return strtr($text, $replace);
        }
    }
}else die;