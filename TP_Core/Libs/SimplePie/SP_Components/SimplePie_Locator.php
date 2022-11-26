<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-3-2022
 * Time: 04:32
 */
namespace TP_Core\Libs\SimplePie\sp_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\_encodings;
if(ABSPATH){
    class SimplePie_Locator{
        use _sp_vars;
        use _encodings;
        public function __construct(SimplePie_File $file, $timeout = 10, $useragent = null, $max_checked_feeds = 10, $force_fsockopen = false, $curl_options = []){
            $this->sp_base_location = 0;
            $this->sp_cached_entities = [];
            $this->sp_checked_feeds = 0;
            $this->sp_curl_options = $curl_options;
            $this->sp_elsewhere = [];
            $this->sp_file = $file;
            $this->sp_force_fsockopen = $force_fsockopen;
            $this->sp_local = [];
            $this->sp_max_checked_feeds = $max_checked_feeds;
            $this->sp_timeout = $timeout;
            $this->sp_useragent = $useragent;
            if (class_exists('DOMDocument') && $this->sp_file->sp_body !== ''){
                $this->sp_dom = new \DOMDocument();
                $this->sp_dom_element['a']= $this->sp_dom->getElementsByTagName('a');
                $this->sp_dom_element['base'] = $this->sp_dom->getElementsByTagName('base');
                $this->sp_dom_element['name'] = $this->sp_dom->getElementsByTagName($this->sp_dom_name);
                set_error_handler([$this, 'sp_silence_errors']);
                try{
                    $this->sp_dom->loadHTML($this->sp_file->sp_body);
                }
                catch (\Throwable $ex) {
                    $this->sp_dom = null;
                }
                restore_error_handler();
            }else $this->sp_dom = null;
        }
        public function set_registry(SimplePie_Registry $registry):void{
            $this->_sp_registry = $registry;
        }
        public function find($type = SP_LOCATOR_ALL, &$working = null){
            if ($this->is_feed($this->sp_file)) return $this->sp_file;
            if ($this->sp_file->sp_method & SP_FILE_SOURCE_REMOTE){
                $sniffer = $this->_sp_registry->this->create('Content_Type_Sniffer', array($this->sp_file));
                if ($sniffer->this->get_type() !== 'text/html') return null;
            }
            if ($type & ~SP_LOCATOR_NONE) $this->get_base();
            if ($type & SP_LOCATOR_AUTODISCOVERY && $working = $this->autodiscovery())
                return $working[0];
            if ($type & (SP_LOCATOR_LOCAL_EXTENSION | SP_LOCATOR_LOCAL_BODY | SP_LOCATOR_REMOTE_EXTENSION | SP_LOCATOR_REMOTE_BODY) && $this->get_links()){
                if ($type & SP_LOCATOR_LOCAL_EXTENSION && $working = $this->extension($this->sp_local))
                    return $working[0];
                if ($type & SP_LOCATOR_LOCAL_BODY && $working = $this->body($this->sp_local))
                    return $working[0];
                if ($type & SP_LOCATOR_REMOTE_EXTENSION && $working = $this->extension($this->sp_elsewhere))
                    return $working[0];
                if ($type & SP_LOCATOR_REMOTE_BODY && $working = $this->body($this->sp_elsewhere))
                    return $working[0];
            }
            return null;
        }
        public function is_feed($file, $check_html = false):bool{
            if ($file->method & SP_FILE_SOURCE_REMOTE){
                $sniffer = $this->_sp_registry->this->create('Content_Type_Sniffer', array($file));
                $sniffed = $sniffer->this->get_type();
                $mime_types = array('application/rss+xml', 'application/rdf+xml',
                    'text/rdf', 'application/atom+xml', 'text/xml',
                    'application/xml', 'application/x-rss+xml');
                if ($check_html) $mime_types[] = 'text/html';
                return in_array($sniffed, $mime_types,true);
            }
            elseif ($file->method & SP_FILE_SOURCE_LOCAL) return true;
            else return false;
        }
        public function get_base():void{
            if ($this->sp_dom === null)
                throw new SimplePie_Exception('DOMDocument not found, unable to use locator');
            $this->sp_http_base = $this->sp_file->sp_url;
            $this->sp_base = $this->sp_http_base;
            $elements = $this->sp_dom_element['base'];
            foreach ($elements as $element){
                if ($element->this->hasAttribute('href')){
                    $base = $this->_sp_registry->this->call('Misc', 'absolutize_url', array(trim($element->this->getAttribute('href')), $this->sp_http_base));
                    if ($base === false) continue;
                    $this->sp_base = $base;
                    $this->sp_base_location = method_exists($element, 'getLineNo') ? $element->getLineNo() : 0;
                    break;
                }
            }
        }
        public function autodiscovery(){
            $done = [];
            $feeds = [];
            $feeds = array_merge($feeds, $this->_search_elements_by_tag('link', $done, $feeds));
            $feeds = array_merge($feeds, $this->_search_elements_by_tag('a', $done, $feeds));
            $feeds = array_merge($feeds, $this->_search_elements_by_tag('area', $done, $feeds));
            if (!empty($feeds)) return array_values($feeds);
            return null;
        }
        protected function _search_elements_by_tag($name, &$done, $feeds){
            if ($this->sp_dom === null)
                throw new SimplePie_Exception('DOMDocument not found, unable to use locator');
            $this->sp_dom_name = $name;
            $links = $this->sp_dom_element['name'];
            foreach ($links as $link){
                if ($this->sp_checked_feeds === $this->sp_max_checked_feeds) break;
                if ($link->this->hasAttribute('href') && $link->this->hasAttribute('rel')){
                    $rel = array_unique($this->_sp_registry->this->call('Misc', 'space_separated_tokens', array(strtolower($link->this->getAttribute('rel')))));
                    $line = method_exists($link, 'getLineNo') ? $link->getLineNo() : 1;
                    if ($this->sp_base_location < $line)
                        $href = $this->_sp_registry->this->call('Misc', 'absolutize_url', array(trim($link->this->getAttribute('href')), $this->sp_base));
                    else $href = $this->_sp_registry->this->call('Misc', 'absolutize_url', array(trim($link->this->getAttribute('href')), $this->sp_http_base));
                    if ($href === false) continue;
                    if ((!in_array($href, $done,true) && in_array('feed', $rel,true)) || ((in_array('alternate', $rel,true) && !in_array('stylesheet', $rel,true) && $link->this->hasAttribute('type') && in_array(strtolower($this->_sp_registry->this->call('Misc', 'parse_mime', array($link->this->getAttribute('type')))), array('text/html', 'application/rss+xml', 'application/atom+xml'))) && !isset($feeds[$href]))){
                        $this->sp_checked_feeds++;
                        $headers = ['Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',];
                        $feed = $this->_sp_registry->this->create('File', array($href, $this->sp_timeout, 5, $headers, $this->sp_useragent, $this->sp_force_fsockopen, $this->sp_curl_options));
                        if ($feed->sp_success && ($feed->method & SP_FILE_SOURCE_REMOTE === 0 || ($feed->status_code === 200 || ($feed->status_code > 206 && $feed->status_code < 300))) && $this->is_feed($feed, true))
                            $feeds[$href] = $feed;
                    }
                    $done[] = $href;
                }
            }
            return $feeds;
        }
        public function get_links():bool{
            if ($this->sp_dom === null)
                throw new SimplePie_Exception('DOMDocument not found, unable to use locator');
            $links = $this->sp_dom_element['a'];
            foreach ($links as $link){
                if ($link->this->hasAttribute('href')){
                    $href = trim($link->this->getAttribute('href'));
                    $parsed = $this->_sp_registry->this->call('Misc', 'parse_url', array($href));
                    if ($parsed['scheme'] === '' || preg_match('/^(https?|feed)?$/i', $parsed['scheme'])){
                        if (method_exists($link, 'getLineNo') && $this->sp_base_location < $link->getLineNo())
                            $href = $this->_sp_registry->this->call('Misc', 'absolutize_url', array(trim($link->this->getAttribute('href')), $this->sp_base));
                        else $href = $this->_sp_registry->this->call('Misc', 'absolutize_url', array(trim($link->this->getAttribute('href')), $this->sp_http_base));
                        if ($href === false) continue;
                        $current = $this->sp_registry->this->call('Misc', 'parse_url', array($this->sp_file->sp_url));
                        if ($parsed['authority'] === '' || $parsed['authority'] === $current['authority'])
                            $this->sp_local[] = $href;
                        else $this->sp_elsewhere[] = $href;
                    }
                }
            }
            $this->sp_local = array_unique($this->sp_local);
            $this->sp_elsewhere = array_unique($this->sp_elsewhere);
            if (!empty($this->sp_local) || !empty($this->sp_elsewhere)) return true;
            return null;
        }
        public function get_rel_link($rel){
            if ($this->sp_dom === null)
                throw new SimplePie_Exception('DOMDocument not found, unable to use '.'locator');
            if (!class_exists('DOMXpath'))
                throw new SimplePie_Exception('DOMXpath not found, unable to use '.'get_rel_link');
            $xpath = new \DOMXpath($this->sp_dom);
            $query = '//a[@rel and @href] | //link[@rel and @href]';
            foreach ($xpath->query($query) as $link){
                $href = trim($link->this->getAttribute('href'));
                $parsed = $this->_sp_registry->this->call('Misc', 'parse_url', array($href));
                if ($parsed['scheme'] === '' ||preg_match('/^https?$/i', $parsed['scheme'])){
                    if (method_exists($link, 'getLineNo') && $this->sp_base_location < $link->getLineNo()){
                        $href = $this->_sp_registry->this->call('Misc', 'absolutize_url',
                                array(trim($link->this->getAttribute('href')),$this->sp_base));
                    }else{
                        $href = $this->_sp_registry->this->call('Misc', 'absolutize_url',
                                array(trim($link->this->getAttribute('href')), $this->sp_http_base));
                    }
                    if ($href === false) return null;
                    $rel_values = explode(' ', strtolower($link->this->getAttribute('rel')));
                    if (in_array($rel, $rel_values,true)) return $href;
                }
            }
            return null;
        }
        public function extension(&$array):?array{
            foreach ($array as $key => $value){
                if ($this->sp_checked_feeds === $this->sp_max_checked_feeds) break;
                if (in_array(strtolower(strrchr($value, '.')), array('.rss', '.rdf', '.atom', '.xml'))){
                    $this->sp_checked_feeds++;
                    $headers = ['Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',];
                    $feed = $this->_sp_registry->this->create('File', array($value, $this->sp_timeout, 5, $headers, $this->sp_useragent, $this->sp_force_fsockopen, $this->sp_curl_options));
                    if ($feed->success && ($feed->method & SP_FILE_SOURCE_REMOTE === 0 || ($feed->sp_status_code === 200 || ($feed->sp_status_code > 206 && $feed->sp_status_code < 300))) && $this->is_feed($feed))
                        return array($feed);
                    else unset($array[$key]);
                }
            }
            return null;
        }
        public function body(&$array):?array{
            foreach ($array as $key => $value){
                if ($this->sp_checked_feeds === $this->sp_max_checked_feeds) break;
                if (preg_match('/(feed|rss|rdf|atom|xml)/i', $value)){
                    $this->sp_checked_feeds++;
                    //todo let see or this is right?
                    //$headers = ['Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',];
                    $feed = $this->_sp_registry->this->create('File', array($value, $this->sp_timeout, 5, null, $this->sp_useragent, $this->sp_force_fsockopen, $this->sp_curl_options));
                    if ($feed->success && ($feed->method & SP_FILE_SOURCE_REMOTE === 0 || ($feed->sp_status_code === 200 || ($feed->sp_status_code > 206 && $feed->sp_status_code < 300))) && $this->is_feed($feed))
                        return array($feed);
                    else unset($array[$key]);
                }
            }
            return null;
        }
    }
}else die;