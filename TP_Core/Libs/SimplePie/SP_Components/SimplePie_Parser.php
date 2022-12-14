<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 08:44
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
use TP_Core\Libs\SimplePie\Depedencies\MicroFormats\_parser_1;
if(ABSPATH){
    class SimplePie_Parser{
        //todo make all strings json ready aka "att=''"
        use _sp_vars;
        use _parser_1;
        public function __construct(){
            $this->sp_data_s[] = [];
            $this->sp_elements[] = '';
            $this->sp_namespace[] = '';
            $this->sp_separator = ' ';
            $this->sp_xml_base[] = '';
            $this->sp_xml_base_explicit[] = false;
            $this->sp_xml_lang[] = '';
        }
        public function set_registry(SimplePie_Registry $registry):void{
            $this->_sp_registry = $registry;// ? $registry : new SimplePie_Registry();
        }
        public function parse(&$data, $encoding, $url = ''):bool{
            if($this->_sp_registry instanceof('SimplePie_Registry') )
                $this->_sp_registry = new SimplePie_Registry();
            if (class_exists('DOMXpath') && function_exists('Mf2\parse')) {
                $doc = new \DOMDocument();
                @$doc->loadHTML($data);
                $xpath = new \DOMXpath($doc);
                // Check for both h-feed and h-entry, as both a feed with no entries
                // and a list of entries without an h-feed wrapper are both valid.
                $query = '//*[contains(concat(" ", @class, " "), " h-feed ") or '.
                    'contains(concat(" ", @class, " "), " h-entry ")]';
                $result = $xpath->query($query);
                if ($result->length !== 0) return $this->__parse_micro_formats($data, $url);
            }
            if (strtoupper($encoding) === 'US-ASCII') $this->sp_encoding = 'UTF-8';
            else $this->sp_encoding = $encoding;
            // Strip BOM:
            // UTF-32 Big Endian BOM
            if (strpos($data, "\x00\x00\xFE\xFF") === 0)
                $data = substr($data, 4);
            // UTF-32 Little Endian BOM
            elseif (strpos($data, "\xFF\xFE\x00\x00") === 0)
                $data = substr($data, 4);
            // UTF-16 Big Endian BOM
            elseif (strpos($data, "\xFE\xFF") === 0)
                $data = substr($data, 2);
            // UTF-16 Little Endian BOM
            elseif (strpos($data, "\xFF\xFE") === 0)
                $data = substr($data, 2);
            // UTF-8 BOM
            elseif (strpos($data, "\xEF\xBB\xBF") === 0)
                $data = substr($data, 3);
            if (strpos($data, '<?xml') === 0 && strspn($data[5], "\x09\x0A\x0D\x20") && ($pos = strpos($data, '?>')) !== false){
                $declaration = $this->_sp_registry->create('XML_Declaration_Parser', array(substr($data, 5, $pos - 5)));
                if ($declaration->parse()){
                    $data = substr($data, $pos + 2);
                    $data = '<?xml version="' . $declaration->version . '" encoding="' . $encoding . '" standalone="' . (($declaration->standalone) ? 'yes' : 'no') . '"?>' ."\n". $this->__declare_html_entities() . $data;
                }else{
                    $this->sp_error_string = 'SimplePie bug! Please report this!';
                    return false;
                }
            }
            $return = true;
            static $xml_is_sane = null;
            if ($xml_is_sane === null){
                $parser_check = xml_parser_create();
                xml_parse_into_struct($parser_check, '<foo>&amp;</foo>', $values);
                xml_parser_free($parser_check);
                $xml_is_sane = isset($values[0]['value']);
            }
            if ($xml_is_sane){
                $xml = xml_parser_create_ns($this->sp_encoding, $this->sp_separator);
                xml_parser_set_option($xml, XML_OPTION_SKIP_WHITE, 1);
                xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, 0);
                xml_set_object($xml, $this);
                xml_set_character_data_handler($xml, 'cdata');
                xml_set_element_handler($xml, 'tag_open', 'tag_close');
                // Parse!
                $wrapper = @is_writable(sys_get_temp_dir()) ? 'php://temp' : 'php://memory';
                if (($stream = fopen($wrapper, 'rb+')) &&
                    fwrite($stream, $data) &&
                    rewind($stream)){
                    //Parse by chunks not to use too much memory
                    do{
                        $stream_data = fread($stream, 1048576);
                        if (!xml_parse($xml, $stream_data === false ? '' : $stream_data, feof($stream))){
                            $this->sp_error_code = xml_get_error_code($xml);
                            $this->sp_error_string = xml_error_string($this->sp_error_code);
                            $return = false;
                            break;
                        }
                    } while (!feof($stream));
                    fclose($stream);
                }
                else $return = false;
                $this->sp_current_line = xml_get_current_line_number($xml);
                $this->sp_current_column = xml_get_current_column_number($xml);
                $this->sp_current_byte = xml_get_current_byte_index($xml);
                xml_parser_free($xml);
                return $return;
            }
            libxml_clear_errors();
            $xml = new \XMLReader();
            /** @noinspection PhpMethodOrClassCallIsNotCaseSensitiveInspection */
            $xml->xml($data);
            while (@$xml->read()){
                switch ($xml->nodeType){
                    case constant('XMLReader::END_ELEMENT'):
                        if ($xml->namespaceURI !== '')
                            $tagName = $xml->namespaceURI . $this->sp_separator . $xml->localName;
                        else $tagName = $xml->localName;
                        $this->tag_close($tagName);
                        break;
                    case constant('XMLReader::ELEMENT'):
                        $empty = $xml->isEmptyElement;
                        if ($xml->namespaceURI !== '')
                            $tagName = $xml->namespaceURI . $this->sp_separator . $xml->localName;
                        else $tagName = $xml->localName;
                        $attributes = array();
                        while ($xml->moveToNextAttribute()){
                            if ($xml->namespaceURI !== '')
                                $attrName = $xml->namespaceURI . $this->sp_separator . $xml->localName;
                            else $attrName = $xml->localName;
                            $attributes[$attrName] = $xml->value;
                        }
                        $this->tag_open($tagName, $attributes);
                        if ($empty) $this->tag_close($tagName);
                        break;
                    case constant('XMLReader::TEXT'):
                    case constant('XMLReader::CDATA'):
                        $this->cdata($xml->value);
                        break;
                }
            }
            if ($error = libxml_get_last_error()){
                $this->sp_error_code = $error->code;
                $this->sp_error_string = $error->message;
                $this->sp_current_line = $error->line;
                $this->sp_current_column = $error->column;
                return false;
            }
            return true;
        }//77
        public function get_error_code(){
            return $this->sp_error_code;
        }//265
        public function get_error_string(){
            return $this->sp_error_string;
        }//270
        public function get_current_line(){
            return $this->sp_current_line;
        }//275
        public function get_current_column(){
            return $this->sp_current_column;
        }//280
        public function get_current_byte(){
            return $this->sp_current_byte;
        }//285
        public function get_data():array{
            return $this->sp_data;
        }//290
        public function tag_open($tag, $attributes):void{
            if($this->_sp_registry instanceof('SimplePie_Registry') )
                $this->_sp_registry = new SimplePie_Registry();
            @list($this->sp_namespace[], $this->sp_elements[]) = $this->split_ns($tag);
            $atts = [];
            foreach ($attributes as $name => $value){
                @list($att_namespace, $attribute) = $this->split_ns($name);
                $atts[$att_namespace][$attribute] = $value;
            }
            if (isset($atts[SP_NS_XML]['base'])){
                $base = $this->_sp_registry->call('Misc', 'absolutize_url', array($atts[SP_NS_XML]['base'], end($this->sp_xml_base)));
                if ($base !== false){
                    $this->sp_xml_base[] = $base;
                    $this->sp_xml_base_explicit[] = true;
                }
            }
            else{
                $this->sp_xml_base[] = end($this->sp_xml_base);
                $this->sp_xml_base_explicit[] = end($this->sp_xml_base_explicit);
            }
            if (isset($atts[SP_NS_XML]['lang']))
                $this->sp_xml_lang[] = $atts[SP_NS_XML]['lang'];
            else $this->sp_xml_lang[] = end($this->sp_xml_lang);
            $this->sp_data_s[] =& $this->sp_data;
            $this->sp_data =& $this->sp_data['child'][end($this->sp_namespace)][end($this->sp_elements)][];
            $this->sp_data = array('data' => '', 'atts' => $atts, 'xml_base' => end($this->sp_xml_base), 'xml_base_explicit' => end($this->sp_xml_base_explicit), 'xml_lang' => end($this->sp_xml_lang));
        }//295
        public function cdata($cdata):void{
                $this->sp_data['data'] .= $cdata;
        }//362
        public function tag_close($tag):void{
            $this->sp_elements = $tag;
            $this->sp_data['data'] .= '</' . end($this->sp_elements) . '>';
            array_pop($this->sp_elements);
            array_pop($this->sp_namespace);
            array_pop($this->sp_xml_base);
            array_pop($this->sp_xml_base_explicit);
            array_pop($this->sp_xml_lang);
        }//374
        public function split_ns($string){
            static $cache = [];
            if (!isset($cache[$string])){
                if ($pos = strpos($string, $this->sp_separator)){
                    static $separator_length;
                    if (!$separator_length)
                        $separator_length = strlen($this->sp_separator);
                    $namespace = substr($string, 0, $pos);
                    $local_name = substr($string, $pos + $separator_length);
                    if (strtolower($namespace) === SP_NS_I_TUNES)
                        $namespace = SP_NS_I_TUNES;
                    // Normalize the Media RSS namespaces
                    if ($namespace === SP_NS_MEDIA_RSS_WRONG ||
                        $namespace === SP_NS_MEDIA_RSS_WRONG2 ||
                        $namespace === SP_NS_MEDIA_RSS_WRONG3 ||
                        $namespace === SP_NS_MEDIA_RSS_WRONG4 ||
                        $namespace === SP_NS_MEDIA_RSS_WRONG5 )
                        $namespace = SP_NS_MEDIA_RSS;
                    $cache[$string] = array($namespace, $local_name);
                }else $cache[$string] = array('', $string);
            }
            return $cache[$string];
        }//397
        private function __parse_h_card($data, $category = false):string {
            $name = '';
            if (isset($data['type']) && in_array('h-card', $data['type'],true)) {
                if (isset($data['properties']['name'][0])) {
                    $name = $data['properties']['name'][0];
                }
                if (isset($data['properties']['url'][0])) {
                    $link = $data['properties']['url'][0];
                    if ($name === '') {
                        $name = $link;
                    } else $name = str_replace(',', '', $name);// can't have commas in categories.
                    $person_tag = $category ? "<span class='person-tag'></span>" : '';
                    return "<a href='$link' class='h-card'>{$person_tag}{$name}</a>";
                }
            }
            return $data['value'] ?? '';
        }//435
        private function __parse_micro_formats(&$data, $url):bool{
            $feed_title = '';
            $feed_author = NULL;
            $author_cache = array();
            $items = array();
            $entries = array();
            $mf = $this->mf_parse($data, $url);
            // First look for an h-feed.
            $h_feed = array();
            foreach ($mf['items'] as $mf_item) {
                if (in_array('h-feed', $mf_item['type'],true)) {
                    $h_feed = $mf_item;
                    break;
                }
                // Also look for h-feed or h-entry in the children of each top level item.
                if (!isset($mf_item['children'][0]['type'])) continue;
                if (in_array('h-feed', $mf_item['children'][0]['type'],true)) {
                    $h_feed = $mf_item['children'][0];
                    // In this case the parent of the h-feed may be an h-card, so use it as
                    // the feed_author.
                    if (in_array('h-card', $mf_item['type'],true)) $feed_author = $mf_item;
                    break;
                }
                if (in_array('h-entry', $mf_item['children'][0]['type'],true)) {
                    $entries = $mf_item['children'];
                    // In this case the parent of the h-entry list may be an h-card, so use
                    // it as the feed_author.
                    if (in_array('h-card', $mf_item['type'], true)) $feed_author = $mf_item;
                    break;
                }
            }
            if (isset($h_feed['children'])) {
                $entries = $h_feed['children'];
                if (isset($mf['items'][0]['properties']['name'][0])) {
                    $feed_title = $mf['items'][0]['properties']['name'][0];
                }
                if (isset($mf['items'][0]['properties']['author'][0])) {
                    $feed_author = $mf['items'][0]['properties']['author'][0];
                }
            }
            else if (count($entries) === 0) {
                $entries = $mf['items'];
            }
            foreach ($entries as $iValue) {
                $entry = $iValue;
                if (in_array('h-entry', $entry['type'],true)) {
                    $item = array();
                    $title = '';
                    $description = '';
                    if (isset($entry['properties']['url'][0])) {
                        $link = $entry['properties']['url'][0];
                        if (isset($link['value'])) $link = $link['value'];
                        $item['link'] = array(array('data' => $link));
                    }
                    if (isset($entry['properties']['uid'][0])) {
                        $guid = $entry['properties']['uid'][0];
                        if (isset($guid['value'])) $guid = $guid['value'];
                        $item['guid'] = array(array('data' => $guid));
                    }
                    if (isset($entry['properties']['name'][0])) {
                        $title = $entry['properties']['name'][0];
                        if (isset($title['value'])) $title = $title['value'];
                        $item['title'] = array(array('data' => $title));
                    }
                    if (isset($entry['properties']['author'][0]) || isset($feed_author)) {
                        $author = $entry['properties']['author'][0] ?? $feed_author;
                        if (!is_string($author))
                            $author = $this->__parse_h_card($author);
                        else if (strpos($author, 'http') === 0) {
                            if (isset($author_cache[$author])) $author = $author_cache[$author];
                            else {
                                $mf = $this->fetch($author);
                                foreach ($mf['items'] as $h_card) {
                                    if (!in_array('h-card', $h_card['type'],true)) continue;
                                    if (!isset($h_card['properties']['url']) ||
                                        !(in_array($author, $h_card['properties']['url'],true))) {
                                        continue;
                                    }
                                    $h_card['properties']['url'][0] = $author;
                                    $author_cache[$author] = $this->__parse_h_card($h_card);
                                    $author = $author_cache[$author];
                                    break;
                                }
                            }
                        }
                        $item['author'] = array(array('data' => $author));
                    }
                    if (isset($entry['properties']['photo'][0])) {
                        // If a photo is also in content, don't need to add it again here.
                        $content = '';
                        if (isset($entry['properties']['content'][0]['html']))
                            $content = $entry['properties']['content'][0]['html'];
                        $photo_list = array();
                        foreach ($entry['properties']['photo'] as $jValue) {
                            $photo = $jValue;
                            if (!empty($photo) && strpos($content, $photo) === false)
                                $photo_list[] = $photo;
                        }
                        $count = count($photo_list);
                        if ($count > 1) {
                            $image_set_id = preg_replace('/[[:^alnum:]]/', '', $photo_list[0]);
                            $description = '<p>';
                            foreach ($photo_list as $j => $jValue) {
                                $hidden = $j === 0 ? '' : 'class="hidden" ';
                                $description .= '<a href="'. $jValue .'" '.$hidden.
                                    'data-lightbox="image-set-'.$image_set_id.'">'.
                                    '<img src="'. $jValue .'"></a>';
                            }
                            $description .= '<br><b>'.$count.' photos</b></p>';
                        }
                        else if ($count === 1) {
                            $description = '<p><img src="'.$photo_list[0].'"></p>';
                        }
                    }
                    if (isset($entry['properties']['content'][0]['html'])) {
                        if ($entry['properties']['content'][0]['value'] === $title) {
                            $title = strip_tags($entry['properties']['content'][0]['html']);
                            $item['title'] = array(array('data' => $title));
                        }
                        $description .= $entry['properties']['content'][0]['html'];
                        if (isset($entry['properties']['in-reply-to'][0])) {
                            $in_reply_to = '';
                            if (is_string($entry['properties']['in-reply-to'][0]))
                                $in_reply_to = $entry['properties']['in-reply-to'][0];
                            else if (isset($entry['properties']['in-reply-to'][0]['value']))
                                $in_reply_to = $entry['properties']['in-reply-to'][0]['value'];
                            if ($in_reply_to !== '')
                                $description .= '<p><span class="in-reply-to"></span><a href="'.$in_reply_to.'">'.$in_reply_to.'</a><p>';
                        }
                        $item['description'] = array(array('data' => $description));
                    }
                    if (isset($entry['properties']['category'])) {
                        $category_csv = '';
                        // Categories can also contain h-cards.
                        foreach ($entry['properties']['category'] as $category) {
                            if ($category_csv !== '') $category_csv .= ', ';
                            if (is_string($category)) {
                                 $category_csv .= str_replace(',', '', $category);
                            } else  $category_csv .= $this->__parse_h_card($category, true);
                        }
                        $item['category'] = array(array('data' => $category_csv));
                    }
                    if (isset($entry['properties']['published'][0])) {
                        $timestamp = strtotime($entry['properties']['published'][0]);
                        $pub_date = date('F j Y g:ia', $timestamp).' GMT';
                        $item['pubDate'] = array(array('data' => $pub_date));
                    }
                    if (isset($entry['properties']['deleted'][0])) {
                        $item['title'] = array(array('data' => ''));
                        $item['description'] = array(array('data' => ''));
                    }
                    $items[] = array('child' => array('' => $item));
                }
            }
            $link = array(array('data' => $url));
            $image = '';
            if (!is_string($feed_author) && isset($feed_author['properties']['photo'][0])) {
                $image = array(array('child' => array('' => array('url' =>
                    array(array('data' => $feed_author['properties']['photo'][0]))))));
            }
            if ($feed_title !== '')
                $feed_title = array(array('data' => htmlspecialchars($feed_title)));
            else if ($position = strpos($data, '<title>')) {
                $start = $position < 200 ? 0 : $position - 200;
                $check = substr($data, $start, 400);
                $matches = array();
                if (preg_match('/<title>(.+)<\/title>/', $check, $matches))
                    $feed_title = array(array('data' => htmlspecialchars($matches[1])));
            }
            $channel = array('channel' => array(array('child' => array('' => array('link' => $link, 'image' => $image, 'title' => $feed_title,'item' => $items)))));
            $rss = array(array('atts' => array('' => array('version' => '2.0')),'child' => array('' => $channel)));
            $this->sp_data = array('child' => array('' => array('rss' => $rss)));
            return true;
        }//459
        private function __declare_html_entities():string {
             return '<!DOCTYPE html [ <!ENTITY nbsp "&#x00A0;"> <!ENTITY iexcl "&#x00A1;"> <!ENTITY cent "&#x00A2;"> <!ENTITY pound "&#x00A3;"> <!ENTITY curren "&#x00A4;"> <!ENTITY yen "&#x00A5;"> <!ENTITY brvbar "&#x00A6;"> <!ENTITY sect "&#x00A7;"> <!ENTITY uml "&#x00A8;"> <!ENTITY copy "&#x00A9;"> <!ENTITY ordf "&#x00AA;"> <!ENTITY laquo "&#x00AB;"> <!ENTITY not "&#x00AC;"> <!ENTITY shy "&#x00AD;"> <!ENTITY reg "&#x00AE;"> <!ENTITY macr "&#x00AF;"> <!ENTITY deg "&#x00B0;"> <!ENTITY plusmn "&#x00B1;"> <!ENTITY sup2 "&#x00B2;"> <!ENTITY sup3 "&#x00B3;"> <!ENTITY acute "&#x00B4;"> <!ENTITY micro "&#x00B5;"> <!ENTITY para "&#x00B6;"> <!ENTITY middot "&#x00B7;"> <!ENTITY cedil "&#x00B8;"> <!ENTITY sup1 "&#x00B9;"> <!ENTITY ordm "&#x00BA;"> <!ENTITY raquo "&#x00BB;"> <!ENTITY frac14 "&#x00BC;"> <!ENTITY frac12 "&#x00BD;"> <!ENTITY frac34 "&#x00BE;"> <!ENTITY iquest "&#x00BF;"> <!ENTITY Agrave "&#x00C0;"> <!ENTITY Aacute "&#x00C1;"> <!ENTITY Acirc "&#x00C2;"> <!ENTITY Atilde "&#x00C3;"> <!ENTITY Auml "&#x00C4;"> <!ENTITY Aring "&#x00C5;"> <!ENTITY AElig "&#x00C6;"> <!ENTITY Ccedil "&#x00C7;"> <!ENTITY Egrave "&#x00C8;"> <!ENTITY Eacute "&#x00C9;"> <!ENTITY Ecirc "&#x00CA;"> <!ENTITY Euml "&#x00CB;"> <!ENTITY Igrave "&#x00CC;"> <!ENTITY Iacute "&#x00CD;"> <!ENTITY Icirc "&#x00CE;"> <!ENTITY Iuml "&#x00CF;"> <!ENTITY ETH "&#x00D0;"> <!ENTITY Ntilde "&#x00D1;"> <!ENTITY Ograve "&#x00D2;"> <!ENTITY Oacute "&#x00D3;"> <!ENTITY Ocirc "&#x00D4;"> <!ENTITY Otilde "&#x00D5;"> <!ENTITY Ouml "&#x00D6;"> <!ENTITY times "&#x00D7;"> <!ENTITY Oslash "&#x00D8;"> <!ENTITY Ugrave "&#x00D9;"> <!ENTITY Uacute "&#x00DA;"> <!ENTITY Ucirc "&#x00DB;"> <!ENTITY Uuml "&#x00DC;"> <!ENTITY Yacute "&#x00DD;"> <!ENTITY THORN "&#x00DE;"> <!ENTITY szlig "&#x00DF;"> <!ENTITY agrave "&#x00E0;"> <!ENTITY aacute "&#x00E1;"> <!ENTITY acirc "&#x00E2;"> <!ENTITY atilde "&#x00E3;"> <!ENTITY auml "&#x00E4;"> <!ENTITY aring "&#x00E5;"> <!ENTITY aelig "&#x00E6;"> <!ENTITY ccedil "&#x00E7;"> <!ENTITY egrave "&#x00E8;"> <!ENTITY eacute "&#x00E9;"> <!ENTITY ecirc "&#x00EA;"> <!ENTITY euml "&#x00EB;"> <!ENTITY igrave "&#x00EC;"> <!ENTITY iacute "&#x00ED;"> <!ENTITY icirc "&#x00EE;"> <!ENTITY iuml "&#x00EF;"> <!ENTITY eth "&#x00F0;"> <!ENTITY ntilde "&#x00F1;"> <!ENTITY ograve "&#x00F2;"> <!ENTITY oacute "&#x00F3;"> <!ENTITY ocirc "&#x00F4;"> <!ENTITY otilde "&#x00F5;"> <!ENTITY ouml "&#x00F6;"> <!ENTITY divide "&#x00F7;"> <!ENTITY oslash "&#x00F8;"> <!ENTITY ugrave "&#x00F9;"> <!ENTITY uacute "&#x00FA;"> <!ENTITY ucirc "&#x00FB;"> <!ENTITY uuml "&#x00FC;"> <!ENTITY yacute "&#x00FD;"> <!ENTITY thorn "&#x00FE;"> <!ENTITY yuml "&#x00FF;"> <!ENTITY OElig "&#x0152;"> <!ENTITY oelig "&#x0153;"> <!ENTITY Scaron "&#x0160;"> <!ENTITY scaron "&#x0161;"> <!ENTITY Yuml "&#x0178;"> <!ENTITY fnof "&#x0192;"> <!ENTITY circ "&#x02C6;"> <!ENTITY tilde "&#x02DC;"> <!ENTITY Alpha "&#x0391;"> <!ENTITY Beta "&#x0392;"> <!ENTITY Gamma "&#x0393;"> <!ENTITY Epsilon "&#x0395;"> <!ENTITY Zeta "&#x0396;"> <!ENTITY Eta "&#x0397;"> <!ENTITY Theta "&#x0398;"> <!ENTITY Iota "&#x0399;"> <!ENTITY Kappa "&#x039A;"> <!ENTITY Lambda "&#x039B;"> <!ENTITY Mu "&#x039C;"> <!ENTITY Nu "&#x039D;"> <!ENTITY Xi "&#x039E;"> <!ENTITY Omicron "&#x039F;"> <!ENTITY Pi "&#x03A0;"> <!ENTITY Rho "&#x03A1;"> <!ENTITY Sigma "&#x03A3;"> <!ENTITY Tau "&#x03A4;"> <!ENTITY Upsilon "&#x03A5;"> <!ENTITY Phi "&#x03A6;"> <!ENTITY Chi "&#x03A7;"> <!ENTITY Psi "&#x03A8;"> <!ENTITY Omega "&#x03A9;"> <!ENTITY alpha "&#x03B1;"> <!ENTITY beta "&#x03B2;"> <!ENTITY gamma "&#x03B3;"> <!ENTITY delta "&#x03B4;"> <!ENTITY epsilon "&#x03B5;"> <!ENTITY zeta "&#x03B6;"> <!ENTITY eta "&#x03B7;"> <!ENTITY theta "&#x03B8;"> <!ENTITY iota "&#x03B9;"> <!ENTITY kappa "&#x03BA;"> <!ENTITY lambda "&#x03BB;"> <!ENTITY mu "&#x03BC;"> <!ENTITY nu "&#x03BD;"> <!ENTITY xi "&#x03BE;"> <!ENTITY omicron "&#x03BF;"> <!ENTITY pi "&#x03C0;"> <!ENTITY rho "&#x03C1;"> <!ENTITY sigmaf "&#x03C2;"> <!ENTITY sigma "&#x03C3;"> <!ENTITY tau "&#x03C4;"> <!ENTITY upsilon "&#x03C5;"> <!ENTITY phi "&#x03C6;"> <!ENTITY chi "&#x03C7;"> <!ENTITY psi "&#x03C8;"> <!ENTITY omega "&#x03C9;"> <!ENTITY thetasym "&#x03D1;"> <!ENTITY upsih "&#x03D2;"> <!ENTITY piv "&#x03D6;"> <!ENTITY ensp "&#x2002;"> <!ENTITY emsp "&#x2003;"> <!ENTITY thinsp "&#x2009;"> <!ENTITY zwnj "&#x200C;"> <!ENTITY zwj "&#x200D;"> <!ENTITY lrm "&#x200E;"> <!ENTITY rlm "&#x200F;"> <!ENTITY ndash "&#x2013;"> <!ENTITY mdash "&#x2014;"> <!ENTITY lsquo "&#x2018;"> <!ENTITY rsquo "&#x2019;"> <!ENTITY sbquo "&#x201A;"> <!ENTITY ldquo "&#x201C;"> <!ENTITY rdquo "&#x201D;"> <!ENTITY bdquo "&#x201E;"> <!ENTITY dagger "&#x2020;"> <!ENTITY Dagger "&#x2021;"> <!ENTITY bull "&#x2022;"> <!ENTITY hellip "&#x2026;"> <!ENTITY permil "&#x2030;"> <!ENTITY prime "&#x2032;"> <!ENTITY Prime "&#x2033;"> <!ENTITY lsaquo "&#x2039;"> <!ENTITY rsaquo "&#x203A;"> <!ENTITY oline "&#x203E;"> <!ENTITY frasl "&#x2044;"> <!ENTITY euro "&#x20AC;"> <!ENTITY image "&#x2111;"> <!ENTITY weierp "&#x2118;"> <!ENTITY real "&#x211C;"> <!ENTITY trade "&#x2122;"> <!ENTITY alefsym "&#x2135;"> <!ENTITY larr "&#x2190;"> <!ENTITY uarr "&#x2191;"> <!ENTITY rarr "&#x2192;"> <!ENTITY darr "&#x2193;"> <!ENTITY harr "&#x2194;"> <!ENTITY crarr "&#x21B5;"> <!ENTITY lArr "&#x21D0;"> <!ENTITY uArr "&#x21D1;"> <!ENTITY rArr "&#x21D2;"> <!ENTITY dArr "&#x21D3;"> <!ENTITY hArr "&#x21D4;"> <!ENTITY forall "&#x2200;"> <!ENTITY part "&#x2202;"> <!ENTITY exist "&#x2203;"> <!ENTITY empty "&#x2205;"> <!ENTITY nabla "&#x2207;"> <!ENTITY isin "&#x2208;"> <!ENTITY notin "&#x2209;"> <!ENTITY ni "&#x220B;"> <!ENTITY prod "&#x220F;"> <!ENTITY sum "&#x2211;"> <!ENTITY minus "&#x2212;"> <!ENTITY lowast "&#x2217;"> <!ENTITY radic "&#x221A;"> <!ENTITY prop "&#x221D;"> <!ENTITY infin "&#x221E;"> <!ENTITY ang "&#x2220;"> <!ENTITY and "&#x2227;"> <!ENTITY or "&#x2228;"> <!ENTITY cap "&#x2229;"> <!ENTITY cup "&#x222A;"> <!ENTITY int "&#x222B;"> <!ENTITY there4 "&#x2234;"> <!ENTITY sim "&#x223C;"> <!ENTITY cong "&#x2245;"> <!ENTITY asymp "&#x2248;"> <!ENTITY ne "&#x2260;"> <!ENTITY equiv "&#x2261;"> <!ENTITY le "&#x2264;"> <!ENTITY ge "&#x2265;"> <!ENTITY sub "&#x2282;"> <!ENTITY sup "&#x2283;"> <!ENTITY nsub "&#x2284;"> <!ENTITY sube "&#x2286;"> <!ENTITY supe "&#x2287;"> <!ENTITY oplus "&#x2295;"> <!ENTITY otimes "&#x2297;"> <!ENTITY perp "&#x22A5;"> <!ENTITY sdot "&#x22C5;"> <!ENTITY lceil "&#x2308;"> <!ENTITY rceil "&#x2309;"> <!ENTITY lfloor "&#x230A;"> <!ENTITY rfloor "&#x230B;"> <!ENTITY lang "&#x2329;"> <!ENTITY rang "&#x232A;"> <!ENTITY loz "&#x25CA;"> <!ENTITY spades "&#x2660;"> <!ENTITY clubs "&#x2663;"> <!ENTITY hearts "&#x2665;"> <!ENTITY diams "&#x2666;"> ]>';
        }//674
    }
}else die;