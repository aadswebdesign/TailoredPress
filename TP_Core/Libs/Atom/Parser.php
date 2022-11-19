<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-5-2022
 * Time: 19:21
 */
namespace TP_Core\Libs\Atom;
use TP_Core\Traits\I10n\_I10n_01;
if(ABSPATH){
    class Parser{
        use _I10n_01;
        public const NS = 'http://www.w3.org/2005/Atom';
        public const ATOM_CONTENT_ELEMENTS = ['content','summary','title','subtitle','rights'];
        public const ATOM_SIMPLE_ELEMENTS = ['id','updated','published','draft'];
        public const FILE = "php://input";
        protected $_debug = false;
        protected $_depth = 0;
        protected $_indent = 2;
        protected static $_file = self::FILE;
        protected static $_ns = self::NS;
        protected static $_ace = self::ATOM_CONTENT_ELEMENTS;
        protected static $_ase = self::ATOM_SIMPLE_ELEMENTS;
        public $error =[];
        public $content;
        public $in_content;
        public $ns_contexts = [];
        public $ns_decls = [];
        public $content_ns_decls = [];
        public $content_ns_contexts = [];
        public $is_xhtml = false;
        public $is_html = false;
        public $is_text = true;
        public $skipped_div = false;
        public $feed;
        public $current;
        public $map_attrs_func;
        public $map_xmlns_func;
        public function __construct() {
            $this->feed = new Feed();
            $this->current = null;
            $this->map_attrs_func = array( __CLASS__, 'map_attrs' );
            $this->map_xmlns_func = array( __CLASS__, 'map_xmlns' );
        }
        public static function map_attrs($k, $v): string{
            return "$k=\"$v\"";
        }
        public static function map_xmlns($n): string{ //$p,
            $xd = "xmlns";
            if( 0 < strlen($n[0]) )
                $xd .= ":{$n[0]}";
            return "{$xd}=\"{$n[1]}\"";
        }
        public function error_handler($log_level, $log_text, $error_file, $error_line): string {
            $this->error['text'] = $log_text;
            $this->error['level'] = $log_level;
            $this->error['file'] = $error_file;
            $this->error['line'] = $error_line;
        }
        public function parse(): string {
            set_error_handler(array(&$this, 'error_handler'));
            array_unshift($this->ns_contexts, array());
            if ( ! function_exists( 'xml_parser_create_ns' ) ) {
                trigger_error( $this->__( "PHP's XML extension is not available. Please contact your hosting provider to enable PHP's XML extension." ) );
                return false;
            }
            $parser = xml_parser_create_ns();
            xml_set_object($parser, $this);
            xml_set_element_handler($parser, "start_element", "end_element");
            xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
            xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
            xml_set_character_data_handler($parser, "cdata");
            xml_set_default_handler($parser, "_default");
            xml_set_start_namespace_decl_handler($parser, "start_ns");
            xml_set_end_namespace_decl_handler($parser, "end_ns");
            $this->content = '';
            $ret = true;
            $fp = fopen(self::$_file, "rb");
            while ($data = fread($fp, 4096)) {
                if($this->_debug) $this->content .= $data;
                if(!xml_parse($parser, $data, feof($fp))) {
                    trigger_error(sprintf($this->__('XML Error: %1$s at line %2$s')."\n",
                        xml_error_string(xml_get_error_code($parser)),
                        xml_get_current_line_number($parser)));
                    $ret = false;
                    break;
                }
            }
            fclose($fp);
            xml_parser_free($parser);
            unset($parser);
            restore_error_handler();
            return $ret;
        }
        public function start_element($name, $attrs):string { //$parser,
            $name_parts = explode(":", $name);
            $tag        = array_pop($name_parts);
            switch($name) {
                case self::$_ns . ':feed':
                    $this->current = $this->feed;
                    break;
                case self::$_ns . ':entry':
                    $this->current = new Entry();
                    break;
            }
            $this->_p("start_element('$name')");
            #$this->_p(print_r($this->ns_contexts,true));
            #$this->_p('current(' . $this->current . ')');
            array_unshift($this->ns_contexts, $this->ns_decls);
            $this->_depth++;
            if(!empty($this->in_content)) {
                $this->content_ns_decls = array();
                if($this->is_html || $this->is_text)
                    trigger_error("Invalid content in element found. Content must not be of type text or html if it contains markup.");
                $attrs_prefix = array();
                foreach($attrs as $key => $value) {
                    $with_prefix = $this->ns_to_prefix($key, true);
                    $attrs_prefix[$with_prefix[1]] = $this->xml_escape($value);
                }
                $attrs_str = implode(' ', array_map($this->map_attrs_func, array_keys($attrs_prefix), array_values($attrs_prefix)));
                if($attrs_str !== '') $attrs_str = " " . $attrs_str;
                $with_prefix = $this->ns_to_prefix($name);
                if(!$this->is_declared_content_ns($with_prefix[0]))
                    $this->content_ns_decls[] = $with_prefix[0];
                $xmlns_str = '';
                if(count($this->content_ns_decls) > 0) {
                    array_unshift($this->content_ns_contexts, $this->content_ns_decls);
                    $xmlns_str .= implode(' ', array_map($this->map_xmlns_func, array_keys($this->content_ns_contexts[0]), array_values($this->content_ns_contexts[0])));
                    if($xmlns_str !== '')  $xmlns_str = " " . $xmlns_str;
                }
                $this->in_content[] = array($tag, $this->_depth, "<" . $with_prefix[1] . "{$xmlns_str}{$attrs_str}" . ">");

            } else if(in_array($tag, self::$_ace, true) || in_array($tag, self::$_ase, true)) {
                $this->in_content = array();
                $this->is_xhtml = $attrs['type'] === 'xhtml';
                $this->is_html = $attrs['type'] === 'html' || $attrs['type'] === 'text/html';
                $this->is_text = !array_key_exists('type', $attrs) || $attrs['type'] === 'text';
                $text_type = $this->is_text ? 'TEXT' : $attrs['type'];
                $is_html = $this->is_html ? 'HTML' : $text_type;
                $type = $this->is_xhtml ? 'XHTML' : $is_html;
                if(array_key_exists('src', $attrs)) $this->current->$tag = $attrs;
                else  $this->in_content[] = array($tag, $this->_depth, $type);
            } else if($tag === 'link') $this->current->links[] = $attrs;
            else if($tag === 'category')  $this->current->categories[] = $attrs;
            $this->ns_decls = array();
        }
        public function end_element($name) :string{//not used  $parser,
            $name_parts = explode(":", $name);
            $tag        = array_pop($name_parts);
            $count = count($this->in_content);
            if(!empty($this->in_content)) {
                if($this->in_content[0][0] === $tag &&
                    $this->in_content[0][1] === $this->_depth) {
                    $orig_type = $this->in_content[0][2];
                    array_shift($this->in_content);
                    $new_content = [];
                    foreach($this->in_content as $c) {
                        if(count($c) === 3) $new_content[] = $c[2];
                        else if($this->is_xhtml || $this->is_text)
                            $new_content[] = $this->xml_escape($c);
                        else $new_content[] = $c;
                    }
                    if(in_array($tag, self::$_ace, true))
                        $this->current->$tag = array($orig_type, implode('',$new_content));
                    else $this->current->$tag = implode('',$new_content);
                    $this->in_content = array();
                } else if($this->in_content[$count-1][0] === $tag &&
                    $this->in_content[$count-1][1] === $this->_depth) {
                    $this->in_content[$count-1][2] = substr($this->in_content[$count-1][2],0,-1) . "/>";
                } else {
                    # else, just finalize the current element's content
                    $endtag = $this->ns_to_prefix($name);
                    $this->in_content[] = array($tag, $this->_depth, "</$endtag[1]>");
                }
            }
            array_shift($this->ns_contexts);
            $this->_depth--;
            if($name === (self::$_ns . ':entry')) {
                $this->feed->entries[] = $this->current;
                $this->current = null;
            }
            $this->_p("end_element('$name')");
        }
        public function start_ns($prefix, $uri):string {//not used  $parser,
            $this->_p("starting: " . $prefix . ":" . $uri);
            $this->ns_decls[] = array($prefix, $uri);
        }
        public function end_ns(/** @noinspection PhpUnusedParameterInspection */$parser, $prefix):string {
            $this->_p("ending: #" . $prefix . "#");
        }
        public function cdata(/** @noinspection PhpUnusedParameterInspection */$parser, $data):string {
            $this->_p("data: #" . str_replace(array("\n"), array("\\n"), trim($data)) . "#");
            if(!empty($this->in_content)) {
                $this->in_content[] = $data;
            }
        }
        public function ns_to_prefix($q_name, $attr=false) {
            $components = explode(":", $q_name);
            $name = array_pop($components);
            if(!empty($components)) {
                $ns = implode(":",$components);
                foreach($this->ns_contexts as $context) {
                    foreach($context as $mapping) {
                        if($mapping[1] === $ns && $mapping[0] !== '') {
                            return array($mapping, "$mapping[0]:$name");
                        }
                    }
                }
            }
            if($attr) {
                return array(null, $name);
            }
            foreach($this->ns_contexts as $context) {
                foreach($context as $mapping) {
                    if($mapping[0] === '')
                        return array($mapping, $name);
                }
            }
            return false;
        }
        public function is_declared_content_ns($new_mapping):string {
            foreach($this->content_ns_contexts as $context) {
                foreach($context as $mapping) {
                    if($new_mapping === $mapping) return true;
                }
            }
            return false;
        }
        public function xml_escape($content){
            return str_replace(array('&','"',"'",'<','>'),
                array('&amp;','&quot;','&apos;','&lt;','&gt;'),
                $content );
        }
        protected function _default($parser, $data):string {}
        protected function _p($msg):string {
            if($this->_debug) {
                print str_repeat(" ", $this->_depth * $this->_indent) . $msg ."\n";
            }
        }
    }
}else die;