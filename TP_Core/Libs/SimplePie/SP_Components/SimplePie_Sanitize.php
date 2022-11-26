<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-3-2022
 * Time: 04:32
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Sanitize{
        use _sp_vars;
        public function __construct(){
            $this->sp_remove_div = true;
            $this->sp_image_handler = '';
            $this->sp_strip_html_tags = ['base', 'blink', 'body', 'doctype', 'embed', 'font', 'form', 'frame', 'frameset', 'html', 'iframe', 'input', 'marquee', 'meta', 'noscript', 'object', 'param', 'script', 'style'];
            $this->sp_encode_instead_of_strip = false;
            $this->sp_strip_attributes = ['bgsound', 'expr', 'id', 'style', 'onclick', 'onerror', 'onfinish', 'onmouseover', 'onmouseout', 'onfocus', 'onblur', 'lowsrc', 'dynsrc'];
            $this->sp_add_attributes = ['audio' => array('preload' => 'none'), 'iframe' => ['sandbox' => 'allow-scripts allow-same-origin'], 'video' => ['preload' => 'none']];
            $this->sp_strip_comments = false;
            $this->sp_output_encoding = 'UTF-8';
            $this->sp_enable_cache = true;
            $this->sp_cache_location = './cache';
            $this->sp_cache_name_function = 'md5';
            $this->sp_timeout = 10;
            $this->sp_useragent = '';
            $this->sp_force_fsockopen = false;
            $this->sp_replace_url_attributes = null;
            $this->sp_https_domains = [];
        }
        public function remove_div($enable = true):void{
            $this->sp_remove_div = (bool) $enable;
        }
        public function set_image_handler($page = false):void{
            if ($page) $this->sp_image_handler = (string) $page;
            else $this->sp_image_handler = false;
        }
        public function set_registry(SimplePie_Registry $registry):void{
            $this->sp_registry = $registry;
        }
        public function pass_cache_data($enable_cache = true, $cache_location = './cache', $cache_name_function = 'md5', $cache_class = '_SimplePie_Cache'):void{
            if (isset($enable_cache)) $this->sp_enable_cache = (bool) $enable_cache;
            if ($cache_location) $this->sp_cache_location = (string) $cache_location;
            if ($cache_name_function) $this->sp_cache_name_function = (string) $cache_name_function;
            //todo, let see this way
            if($cache_class)$this->sp_cache_class = $cache_class;
        }
        public function pass_file_data($file_class = '_SimplePie_File', $timeout = 10, $useragent = '', $force_fsockopen = false, ...$file_args):void{
            if ($timeout) $this->sp_timeout = (string) $timeout;
            if ($useragent) $this->sp_useragent = (string) $useragent;
            if ($force_fsockopen) $this->sp_force_fsockopen = (string) $force_fsockopen;
            //todo, let see this way
            if($file_class)$this->sp_file_class = $file_class;
            if($file_args) $this->sp_file_class_args = $file_args;
        }
        public function strip_html_tags($tags = array('base', 'blink', 'body', 'doctype', 'embed', 'font', 'form', 'frame', 'frameset', 'html', 'iframe', 'input', 'marquee', 'meta', 'noscript', 'object', 'param', 'script', 'style')):void{
            if ($tags){
                if (is_array($tags))  $this->sp_strip_html_tags = $tags;
                else $this->sp_strip_html_tags = explode(',', $tags);
            } else $this->sp_strip_html_tags = false;
        } //todo unused param
        public function encode_instead_of_strip($encode = false):void{
            $this->sp_encode_instead_of_strip = (bool) $encode;
        }
        public function strip_attributes($atts = array('bgsound', 'expr', 'id', 'style', 'onclick', 'onerror', 'onfinish', 'onmouseover', 'onmouseout', 'onfocus', 'onblur', 'lowsrc', 'dynsrc')):void{
            if ($atts){
                if (is_array($atts)) $this->sp_strip_attributes = $atts;
                else $this->sp_strip_attributes = explode(',', $atts);
            } else $this->sp_strip_attributes = false;
        }
        public function add_attributes($atts = array('audio' => array('preload' => 'none'), 'iframe' => array('sandbox' => 'allow-scripts allow-same-origin'), 'video' => array('preload' => 'none'))):void{
            if ($atts){
                if (is_array($atts)) $this->sp_add_attributes = $atts;
                else $this->sp_add_attributes = explode(',', $atts);
            }else $this->sp_add_attributes = false;
        }
        public function strip_comments($strip = false):void{
            $this->sp_strip_comments = (bool) $strip;
        }
        public function set_output_encoding($encoding = 'UTF-8'):void{
            $this->sp_output_encoding = (string) $encoding;
        }
        public function set_url_replacements($element_attribute = null):void{
            if ($element_attribute === null){
                $element_attribute = [
                    'a' => 'href','area' => 'href','blockquote' => 'cite','del' => 'cite','form' => 'action',
                    'img' => ['longdesc', 'src'],'input' => 'src','ins' => 'cite','q' => 'cite'];
            }
            $this->sp_replace_url_attributes = (array) $element_attribute;
        }
        public function set_https_domains($domains):void{
            $this->sp_https_domains = [];
            foreach ($domains as $domain){
                $domain = trim($domain, ". \t\n\r\0\x0B");
                $segments = array_reverse(explode('.', $domain));
                $node =& $this->sp_https_domains;
                foreach ($segments as $segment){//Build a tree
                    if ($node === true) break;
                    if (!isset($node[$segment])) $node[$segment] = [];
                    $node =& $node[$segment];
                }
                $node = true;
            }
        }
        protected function _is_https_domain($domain):bool{
            $domain = trim($domain, '. ');
            $segments = array_reverse(explode('.', $domain));
            $node =& $this->sp_https_domains;
            foreach ($segments as $segment){//Explore the tree
                if (isset($node[$segment])) $node =& $node[$segment];
                else break;
            }
            return $node === true;
        }
        public function https_url($url){
            return (stripos($url, 'http://') === 0) &&
            $this->_is_https_domain(parse_url($url, PHP_URL_HOST)) ?
                substr_replace($url, 's', 4, 0) :	//Add the 's' to HTTPS
                $url;
        }
        public function sanitize($data, $type, $base = ''){
            $data = trim($data);
            if ($data !== '' || $type & SP_CONSTRUCT_IRI){
                if ($type & SP_CONSTRUCT_MAYBE_HTML){
                    if (preg_match('/(&(#(x[0-9a-fA-F]+|[0-9]+)|[a-zA-Z0-9]+)|<\/[A-Za-z][^\x09\x0A\x0B\x0C\x0D\x20\x2F\x3E]*' . SP_PCRE_HTML_ATTRIBUTE . '>)/', $data))
                        $type |= SP_CONSTRUCT_HTML;
                    else $type |= SP_CONSTRUCT_TEXT;
                }
                if ($type & SP_CONSTRUCT_BASE64)
                    $data = base64_decode($data);
                if ($type & (SP_CONSTRUCT_HTML)){
                    if (!class_exists('DOMDocument')) throw new SimplePie_Exception('DOMDocument not found, unable to use sanitizer');
                    $document = new \DOMDocument();
                    $this->sp_dom_element['tag'] = $document->getElementsByTagName($this->sp_dom_name);
                    $document->encoding = 'UTF-8';
                    $data = $this->_pre_process($data);
                    set_error_handler(array('SimplePie_Misc', 'silence_errors'));//todo
                    $document->loadHTML($data);
                    restore_error_handler();
                    $xpath = new \DOMXPath($document);
                    if ($this->sp_strip_comments){
                        $comments = $xpath->query('//comment()');
                        foreach ($comments as $comment)
                            $comment->parentNode->removeChild($comment);
                    }
                    if ($this->sp_strip_html_tags){
                        foreach ($this->sp_strip_html_tags as $tag)
                            $this->_strip_tag($tag, $document, $xpath, $type);
                    }
                    if ($this->sp_strip_attributes){
                        foreach ($this->sp_strip_attributes as $att)
                            $this->_strip_attr($att, $xpath);
                    }
                    if ($this->sp_add_attributes){
                        foreach ($this->sp_add_attributes as $tag => $valuePairs)
                            $this->_add_attr($tag, $valuePairs, $document);
                    }
                    // Replace relative URLs
                    $this->sp_base = $base; //todo lookup uses
                    foreach ($this->sp_replace_url_attributes as $element => $attributes)
                        $this->replace_urls($document, $element, $attributes);
                    if (isset($this->sp_image_handler) && ((string) $this->sp_image_handler) !== '' && $this->sp_enable_cache){
                        $images = $document->getElementsByTagName('img');
                        foreach ($images as $img){
                            if ($img->this->hasAttribute('src')){
                                $image_url = call_user_func($this->sp_cache_name_function, $img->this->getAttribute('src'));
                                $cache = $this->sp_registry->this->call('Cache', 'get_handler', array($this->sp_cache_location, $image_url, 'spi'));
                                if ($cache->this->load()) $img->this->setAttribute('src', $this->sp_image_handler . $image_url);
                                else {
                                    $file = $this->sp_registry->this->create('File', array($img->this->getAttribute('src'), $this->sp_timeout, 5, array('X-FORWARDED-FOR' => $_SERVER['REMOTE_ADDR']), $this->sp_useragent, $this->sp_force_fsockopen));
                                    //$headers = $file->headers;
                                    if ($file->success && ($file->method & SP_FILE_SOURCE_REMOTE === 0 || ($file->status_code === 200 || ($file->status_code > 206 && $file->status_code < 300)))){
                                        if ($cache->this->save(array('headers' => $file->headers, 'body' => $file->body)))
                                            $img->this->setAttribute('src', $this->sp_image_handler . $image_url);
                                        else trigger_error("$this->sp_cache_location is not writable. Make sure you've set the correct relative or absolute path, and that the location is server-writable.", E_USER_WARNING);
                                    }
                                }
                            }
                        }
                    }
                    // Get content node
                    $div = $document->getElementsByTagName('body')->item(0)->firstChild;
                    // Finally, convert to a HTML string
                    $data = trim($document->saveHTML($div));
                    if ($this->sp_remove_div){
                        $data = preg_replace('/^<div' . SP_PCRE_XML_ATTRIBUTE . '>/', '', $data);
                        $data = preg_replace('/<\/div>$/', '', $data);
                    }
                    else $data = preg_replace('/^<div' . SP_PCRE_XML_ATTRIBUTE . '>/', '<div>', $data);
                }

                if ($type & SP_CONSTRUCT_IRI){
                    $absolute = $this->sp_registry->this->call('Misc', 'absolutize_url', array($data, $base));
                    if ($absolute !== false) $data = $absolute;
                }
                if ($type & (SP_CONSTRUCT_TEXT | SP_CONSTRUCT_IRI))
                    $data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
                if ($this->sp_output_encoding !== 'UTF-8')
                    $data = $this->sp_registry->this->call('Misc', 'change_encoding', array($data, 'UTF-8', $this->sp_output_encoding));
            }
            return $data;
        }
        protected function _pre_process($html):string{
            $html = preg_replace('%</?(?:html|body)[^>]*?'.'>%is', '', $html);
            $content_type = 'text/html';
            $ret = "<!DOCTYPE html>";
            $ret .= "<html><head>";
            $ret .= "<meta http-equiv='Content-Type' content='{$content_type}; charset=utf-8'>";
            $ret .= "</head><body>{$html}</body></html>";
            return $ret;
        }
        public function replace_urls($document, $tag, $attributes):void{
            if (!is_array($attributes)) $attributes = array($attributes);
            if (!is_array($this->sp_strip_html_tags) || !in_array($tag, $this->sp_strip_html_tags,true)){
                $elements = $document->this->sp_dom_element['tag'];
                foreach ($elements as $element){
                    foreach ($attributes as $attribute){
                        if ($element->this->hasAttribute($attribute)){
                            $value = $this->sp_registry->this->call('Misc', 'absolutize_url', array($element->this->getAttribute($attribute), $this->sp_base));
                            if ($value !== false){
                                $value = $this->https_url($value);
                                $element->this->setAttribute($attribute, $value);
                            }
                        }
                    }
                }
            }
        }
        public function do_strip_html_tags($match):?string{
            if ($this->sp_encode_instead_of_strip){
                if (isset($match[4]) && !in_array(strtolower($match[1]), array('script', 'style'),true)){
                    $match[1] = htmlspecialchars($match[1], ENT_COMPAT, 'UTF-8');
                    $match[2] = htmlspecialchars($match[2], ENT_COMPAT, 'UTF-8');
                    return "&lt;$match[1]$match[2]&gt;$match[3]&lt;/$match[1]&gt;";
                }
                else return htmlspecialchars($match[0], ENT_COMPAT, 'UTF-8');
            }
            elseif (isset($match[4]) && !in_array(strtolower($match[1]), array('script', 'style')))
                return $match[4];
            else return '';
        }
        protected function _strip_tag($tag, $document, $xpath, $type):void{
            $elements = $xpath->this->query('body//' . $tag);
            if ($this->sp_encode_instead_of_strip) {
                foreach ($elements as $element){
                    $fragment = $document->this->createDocumentFragment();
                    // For elements which aren't script or style, include the tag itself
                    if (!in_array($tag, array('script', 'style'))){
                        $text = '<' . $tag;
                        if ($element->this->hasAttributes()){
                            $attrs = array();
                            foreach ($element->attributes as $name => $attr){
                                $this->sp_val = $attr->sp_value;
                                if (empty($this->sp_val)) $this->sp_val = $name;
                                // For HTML, empty is fine
                                elseif (empty($this->sp_val) && ($type & SP_CONSTRUCT_HTML)){
                                    $attrs[] = $name;
                                    continue;
                                }
                                // Standard attribute text
                                $attrs[] = "{$name}={$attr->sp_value}";
                            }
                            $text .= ' ' . implode(' ', $attrs);
                        }
                        $text .= '>';
                        $fragment->this->appendChild(new \DOMText($text));
                    }
                    $number = $element->childNodes->length;
                    for ($i = $number; $i > 0; $i--)
                        $this->sp_child = $element->childNodes->item(0);
                    $fragment->this->appendChild($this->sp_child);
                    if (!in_array($tag, array('script', 'style')))
                        $fragment->this->appendChild(new \DOMText('</' . $tag . '>'));
                    $element->parentNode->replaceChild($fragment, $element);
                }
                return;
            }
            if (in_array($tag, array('script', 'style'))) {
                foreach ($elements as $element)
                    $element->parentNode->removeChild($element);
                return;
            }
            foreach ($elements as $element){
                $fragment = $document->this->createDocumentFragment();
                $number = $element->childNodes->length;
                for ($i = $number; $i > 0; $i--){
                    $child = $element->childNodes->item(0);
                    $fragment->this->appendChild($child);
                }
                $element->parentNode->replaceChild($fragment, $element);
            }
        }
        protected function _strip_attr($att, $xpath):void{
            $elements = $xpath->this->query('//*[@' . $att . ']');
            foreach ($elements as $element) $element->this->removeAttribute($att);
        }
        protected function _add_attr($tag, $valuePairs, $document):void{//todo
            $this->sp_dom_name = $tag;
            $elements = $document->this->sp_dom_element['tag'];
            foreach ($elements as $element){
                foreach ($valuePairs as $att => $value) $element->this->setAttribute($att, $value);
            }
        }
    }
}else die;