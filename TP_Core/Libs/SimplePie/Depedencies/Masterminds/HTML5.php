<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 12:38
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Masterminds;
use TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Parser\DOMTreeBuilder;
use TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Parser\Scanner;
use TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Parser\Tokenizer;
use TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Serializer\OutputRules;
use TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Serializer\Traverser;
use TP_Core\Libs\SimplePie\Factory\_mm_vars;
if(ABSPATH){
    class HTML5{
        use _mm_vars;
        public function __construct(array $added_options = []){
            $this->__default_options = array_merge($this->__default_options, $added_options);
        }
        public function getOptions():array{
            return $this->__default_options;
        }
        public function load($file, array $options = []): \DOMDocument{
            if (is_resource($file))
                return $this->parse(stream_get_contents($file), $options);
            return $this->parse(file_get_contents($file), $options);
        }
        public function loadHTML($string, array $options = []): \DOMDocument{
            return $this->parse($string, $options);
        }
        public function loadHTMLFile($file, array $options = []): \DOMDocument{
            return $this->load($file, $options);
        }
        public function loadHTMLFragment($string, array $options = []): \DOMDocumentFragment{
            return $this->parseFragment($string, $options);
        }
        public function getErrors(){
            return $this->_errors;
        }
        public function hasErrors():bool{
            return count($this->_errors) > 0;
        }
        public function parse($input, array $options = []): \DOMDocument{
            $this->_errors = array();
            $options = array_merge($this->__default_options, $options);
            $events = new DOMTreeBuilder(false, $options);
            $scanner = new Scanner($input, !empty($options['encoding']) ? $options['encoding'] : 'UTF-8');
            $parser = new Tokenizer($scanner, $events, !empty($options['xmlNamespaces']) ? Tokenizer::CONFORMANT_XML : Tokenizer::CONFORMANT_HTML);
            $parser->parse();
            $this->_errors = $events->getErrors();
            return $events->get_document();
        }
        public function parseFragment($input, array $options = []): \DOMDocumentFragment{
            $options = array_merge($this->__default_options, $options);
            $events = new DOMTreeBuilder(true, $options);
            $scanner = new Scanner($input, !empty($options['encoding']) ? $options['encoding'] : 'UTF-8');
            $parser = new Tokenizer($scanner, $events, !empty($options['xmlNamespaces']) ? Tokenizer::CONFORMANT_XML : Tokenizer::CONFORMANT_HTML);
            $parser->parse();
            $this->_errors = $events->getErrors();
            return $events->get_fragment();
        }
        public function save($dom,$file, $options = []):void{
            $close = true;
            if (is_resource($file)) {
                $stream = $file;
                $close = false;
            } else $stream = fopen($file, 'wb');
            $options = array_merge($this->__default_options, $options);
            $output =  new OutputRules($stream, $options);
            $traverse = new Traverser($dom,$output,$options);
            $traverse->walk();
            $output->unsetTraverser();
            if ($close) fclose($stream);
        }
        public function saveHTML($dom, $options = []):string{
            $stream = fopen('php://temp', 'wb');
            $this->save($dom, $stream, array_merge($this->__default_options, $options));
            $html = stream_get_contents($stream, -1, 0);
            fclose($stream);
            return $html;
        }
    }
}else die;