<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 11:47
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Serializer;
use TP_Core\Libs\SimplePie\Factory\_mm_vars;
if(ABSPATH){
    class Traverser{
        use _mm_vars;
        public function __construct($dom, $out, RulesInterface $rules, $options = array()){
            $this->_dom = $dom;
            $this->_out = $out;
            $this->_rules = $rules;
            $this->_options = $options;
            $this->_rules->setTraverser($this);
        }
        public function walk(){
            if ($this->_dom instanceof \DOMDocument)
                $this->_rules->document($this->_dom);
            elseif ($this->_dom instanceof \DOMDocumentFragment) {
                if ($this->_dom->hasChildNodes())
                    $this->children($this->_dom->childNodes);
            }
            elseif ($this->_dom instanceof \DOMNodeList)
                $this->children($this->_dom);
            else $this->node($this->_dom);
            return $this->_out;
        }
        public function node($node):void{
            switch ($node->nodeType) {
                case XML_ELEMENT_NODE:
                    $this->_rules->element($node);
                    break;
                case XML_TEXT_NODE:
                    $this->_rules->text($node);
                    break;
                case XML_CDATA_SECTION_NODE:
                    $this->_rules->cdata($node);
                    break;
                case XML_PI_NODE:
                    $this->_rules->processorInstruction($node);
                    break;
                case XML_COMMENT_NODE:
                    $this->_rules->comment($node);
                    break;
                default:
                    break;
            }
        }
        public function children($nl):void{
            foreach ($nl as $node)  $this->node($node);
        }
        public function isLocalElement($ele):bool{
            $uri = $ele->namespaceURI;
            if (empty($uri)) return false;
            return isset(static::$_local_ns[$uri]);
        }
    }
}else die;