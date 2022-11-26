<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-3-2022
 * Time: 04:02
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\SimplePie;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\_encodings;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Item{
        use _sp_vars;
        use _encodings;
        //protected $_parent;
        public function __construct($feed, $data){
            $this->__sp_feed = $feed;
            $this->__sp_data = $data;
        }
        public function set_registry(SimplePie_Registry $registry):void{
            $this->_sp_registry = $registry;
        }
        public function __toString(){
            return (string)md5(serialize($this->__sp_data));
        }
        public function __destruct(){
            if (!gc_enabled()) unset($this->__sp_feed);
        }
        public function get_item_tags($namespace, $tag){
            if (isset($this->__sp_data['child'][$namespace][$tag]))
                return $this->__sp_data['child'][$namespace][$tag];
            return null;
        }
        public function get_base($element = array()){
            $this->__sp_feed = $this->get_base($element);
            return $this->__sp_feed;
        }
        public function sanitize($data, $type, $base = ''){
            if($this->__sp_feed instanceof('SimplePie_Sanitize') )
                $this->__sp_feed = new SimplePie_Sanitize();
            return $this->__sp_feed->sanitize($data, $type, $base);
        }
        public function get_feed(){
            return $this->__sp_feed;
        }
        public function get_id($hash = false, $fn = 'md5'){
            if (!$hash){
                if ($return = $this->get_item_tags(SP_NS_ATOM_10, 'id'))
                    return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
                elseif ($return = $this->get_item_tags(SP_NS_ATOM_03, 'id'))
                    return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
                elseif ($return = $this->get_item_tags(SP_NS_RSS_20, 'guid'))
                    return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
                elseif ($return = $this->get_item_tags(SP_NS_DC_11, 'identifier'))
                    return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
                elseif ($return = $this->get_item_tags(SP_NS_DC_10, 'identifier'))
                    return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
                elseif (isset($this->__sp_data['atts'][SP_NS_RDF]['about']))
                    return $this->sanitize($this->__sp_data['atts'][SP_NS_RDF]['about'], SP_CONSTRUCT_TEXT);
            }
            if ($fn === false) return null;
            elseif (!is_callable($fn)){
                trigger_error('User-supplied function $fn must be callable', E_USER_WARNING);
                $fn = 'md5';
            }
            return $fn($this->get_permalink() . $this->get_title() . $this->get_content());
        }
        public function get_title(){
            if (!isset($this->__sp_data['title'])){
                if ($return = $this->get_item_tags(SP_NS_ATOM_10, 'title'))
                    $this->__sp_data['title'] = $this->sanitize($return[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_10_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
                elseif ($return = $this->get_item_tags(SP_NS_ATOM_03, 'title'))
                    $this->__sp_data['title'] = $this->sanitize($return[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_03_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
                elseif ($return = $this->get_item_tags(SP_NS_RSS_10, 'title'))
                    $this->__sp_data['title'] = $this->sanitize($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
                elseif ($return = $this->get_item_tags(SP_NS_RSS_090, 'title'))
                    $this->__sp_data['title'] = $this->sanitize($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
                elseif ($return = $this->get_item_tags(SP_NS_RSS_20, 'title'))
                    $this->__sp_data['title'] = $this->sanitize($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
                elseif ($return = $this->get_item_tags(SP_NS_DC_11, 'title'))
                    $this->__sp_data['title'] = $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
                elseif ($return = $this->get_item_tags(SP_NS_DC_10, 'title'))
                    $this->__sp_data['title'] = $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
                else $this->__sp_data['title'] = null;
            }
            return $this->__sp_data['title'];
        }
        public function get_description($description_only = false){
            if (($tags = $this->get_item_tags(SP_NS_ATOM_10, 'summary')) &&
                ($return = $this->sanitize($tags[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_10_construct_type', array($tags[0]['attribs'])), $this->get_base($tags[0]))))
                return $return;
            elseif (($tags = $this->get_item_tags(SP_NS_ATOM_03, 'summary')) &&
                ($return = $this->sanitize($tags[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_03_construct_type', array($tags[0]['attribs'])), $this->get_base($tags[0]))))
                return $return;
            elseif (($tags = $this->get_item_tags(SP_NS_RSS_10, 'description')) &&
                ($return = $this->sanitize($tags[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->get_base($tags[0]))))
                return $return;
            elseif (($tags = $this->get_item_tags(SP_NS_RSS_20, 'description')) &&
                ($return = $this->sanitize($tags[0]['data'], SP_CONSTRUCT_HTML, $this->get_base($tags[0]))))
                return $return;
            elseif (($tags = $this->get_item_tags(SP_NS_DC_11, 'description')) &&
                ($return = $this->sanitize($tags[0]['data'], SP_CONSTRUCT_TEXT)))
                return $return;
            elseif (($tags = $this->get_item_tags(SP_NS_DC_10, 'description')) &&
                ($return = $this->sanitize($tags[0]['data'], SP_CONSTRUCT_TEXT)))
                return $return;
            elseif (($tags = $this->get_item_tags(SP_NS_I_TUNES, 'summary')) &&
                ($return = $this->sanitize($tags[0]['data'], SP_CONSTRUCT_HTML, $this->get_base($tags[0]))))
                return $return;
            elseif (($tags = $this->get_item_tags(SP_NS_I_TUNES, 'subtitle')) &&
                ($return = $this->sanitize($tags[0]['data'], SP_CONSTRUCT_TEXT)))
                return $return;
            elseif (($tags = $this->get_item_tags(SP_NS_RSS_090, 'description')) &&
                ($return = $this->sanitize($tags[0]['data'], SP_CONSTRUCT_HTML)))
                return $return;
            elseif (!$description_only) return $this->get_content(true);
            return null;
        }
        public function get_content($content_only = false){
            if (($tags = $this->get_item_tags(SP_NS_ATOM_10, 'content')) &&
                ($return = $this->sanitize($tags[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_10_content_construct_type', array($tags[0]['attribs'])), $this->get_base($tags[0]))))
                return $return;
            elseif (($tags = $this->get_item_tags(SP_NS_ATOM_03, 'content')) &&
                ($return = $this->sanitize($tags[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_03_construct_type', array($tags[0]['attribs'])), $this->get_base($tags[0]))))
                return $return;
            elseif (($tags = $this->get_item_tags(SP_NS_RSS_10_MODULES_CONTENT, 'encoded')) &&
                ($return = $this->sanitize($tags[0]['data'], SP_CONSTRUCT_HTML, $this->get_base($tags[0]))))
                return $return;
            elseif (!$content_only) return $this->get_description(true);
            return null;
        }
        public function get_thumbnail(){
            if (!isset($this->__sp_data['thumbnail'])) {
                if ($return = $this->get_item_tags(SP_NS_MEDIA_RSS, 'thumbnail'))
                    $this->__sp_data['thumbnail'] = $return[0]['atts'][''];
                else $this->__sp_data['thumbnail'] = null;
            }
            return $this->sp_data['thumbnail'];
        }
        public function get_category($key = 0){
            $categories = $this->get_categories();
            if (isset($categories[$key])) return $categories[$key];
            return null;
        }
        public function get_categories(){
            $categories = [];
            $type = 'category';
            foreach ((array) $this->get_item_tags(SP_NS_ATOM_10, $type) as $category){
                $term = null;
                $scheme = null;
                $label = null;
                if (isset($category['atts']['']['term']))
                    $term = $this->sanitize($category['atts']['']['term'], SP_CONSTRUCT_TEXT);
                if (isset($category['atts']['']['scheme']))
                    $scheme = $this->sanitize($category['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                if (isset($category['atts']['']['label']))
                    $label = $this->sanitize($category['atts']['']['label'], SP_CONSTRUCT_TEXT);
                $categories[] = $this->_sp_registry->this->create('Category', array($term, $scheme, $label, $type));
            }
            foreach ((array) $this->get_item_tags(SP_NS_RSS_20, $type) as $category){
                // This is really the label, but keep this as the term also for BC.
                // Label will also work on retrieving because that falls back to term.
                $term = $this->sanitize($category['data'], SP_CONSTRUCT_TEXT);
                if (isset($category['atts']['']['domain']))
                    $scheme = $this->sanitize($category['atts']['']['domain'], SP_CONSTRUCT_TEXT);
                else $scheme = null;
                $categories[] = $this->_sp_registry->this->create('Category', array($term, $scheme, null, $type));
            }
            $type = 'subject';
            foreach ((array) $this->get_item_tags(SP_NS_DC_11, $type) as $category)
                $categories[] = $this->_sp_registry->this->create('Category', array($this->sanitize($category['data'], SP_CONSTRUCT_TEXT), null, null, $type));
            foreach ((array) $this->get_item_tags(SP_NS_DC_10, $type) as $category)
                $categories[] = $this->_sp_registry->this->create('Category', array($this->sanitize($category['data'], SP_CONSTRUCT_TEXT), null, null, $type));
            if (!empty($categories)) return array_unique($categories);
            return null;
        }
        public function get_author($key = 0){
            $authors = $this->get_authors();
            if (isset($authors[$key])) return $authors[$key];
            return null;
        }
        public function get_contributor($key = 0){
            $contributors = $this->get_contributors();
            if (isset($contributors[$key])) return $contributors[$key];
            return null;
        }
        public function get_contributors(){
            $contributors = array();
            foreach ((array) $this->get_item_tags(SP_NS_ATOM_10, 'contributor') as $contributor){
                $name = null;
                $uri = null;
                $email = null;
                if (isset($contributor['child'][SP_NS_ATOM_10]['name'][0]['data']))
                    $name = $this->sanitize($contributor['child'][SP_NS_ATOM_10]['name'][0]['data'], SP_CONSTRUCT_TEXT);
                if (isset($contributor['child'][SP_NS_ATOM_10]['uri'][0]['data']))
                    $uri = $this->sanitize($contributor['child'][SP_NS_ATOM_10]['uri'][0]['data'], SP_CONSTRUCT_IRI, $this->get_base($contributor['child'][SP_NS_ATOM_10]['uri'][0]));
                if (isset($contributor['child'][SP_NS_ATOM_10]['email'][0]['data']))
                    $email = $this->sanitize($contributor['child'][SP_NS_ATOM_10]['email'][0]['data'], SP_CONSTRUCT_TEXT);
                if ($name !== null || $email !== null || $uri !== null)
                    $contributors[] = $this->_sp_registry->this->create('Author', array($name, $uri, $email));
            }
            foreach ((array) $this->get_item_tags(SP_NS_ATOM_03, 'contributor') as $contributor){
                $name = null;
                $url = null;
                $email = null;
                if (isset($contributor['child'][SP_NS_ATOM_03]['name'][0]['data']))
                    $name = $this->sanitize($contributor['child'][SP_NS_ATOM_03]['name'][0]['data'], SP_CONSTRUCT_TEXT);
                if (isset($contributor['child'][SP_NS_ATOM_03]['url'][0]['data']))
                    $url = $this->sanitize($contributor['child'][SP_NS_ATOM_03]['url'][0]['data'], SP_CONSTRUCT_IRI, $this->get_base($contributor['child'][SP_NS_ATOM_03]['url'][0]));
                if (isset($contributor['child'][SP_NS_ATOM_03]['email'][0]['data']))
                    $email = $this->sanitize($contributor['child'][SP_NS_ATOM_03]['email'][0]['data'], SP_CONSTRUCT_TEXT);
                if ($name !== null || $email !== null || $url !== null)
                    $contributors[] = $this->_sp_registry->this->create('Author', array($name, $url, $email));
            }
            if (!empty($contributors)) return array_unique($contributors);
            return null;
        }
        public function get_authors(){
            if($this->_sp_registry instanceof('SimplePie_Registry') )
                $this->_sp_registry = new SimplePie_Registry();
            $authors = [];
            foreach ((array) $this->get_item_tags(SP_NS_ATOM_10, 'author') as $author){
                $name = null;
                $uri = null;
                $email = null;
                if (isset($author['child'][SP_NS_ATOM_10]['name'][0]['data']))
                    $name = $this->sanitize($author['child'][SP_NS_ATOM_10]['name'][0]['data'], SP_CONSTRUCT_TEXT);
                if (isset($author['child'][SP_NS_ATOM_10]['uri'][0]['data']))
                    $uri = $this->sanitize($author['child'][SP_NS_ATOM_10]['uri'][0]['data'], SP_CONSTRUCT_IRI, $this->get_base($author['child'][SP_NS_ATOM_10]['uri'][0]));
                if (isset($author['child'][SP_NS_ATOM_10]['email'][0]['data']))
                    $email = $this->sanitize($author['child'][SP_NS_ATOM_10]['email'][0]['data'], SP_CONSTRUCT_TEXT);
                if ($name !== null || $email !== null || $uri !== null)
                    $authors[] = $this->_sp_registry->create('Author', array($name, $uri, $email));
            }
            if ($author = $this->get_item_tags(SP_NS_ATOM_03, 'author')){
                $name = null;
                $url = null;
                $email = null;
                if (isset($author[0]['child'][SP_NS_ATOM_03]['name'][0]['data']))
                    $name = $this->sanitize($author[0]['child'][SP_NS_ATOM_03]['name'][0]['data'], SP_CONSTRUCT_TEXT);
                if (isset($author[0]['child'][SP_NS_ATOM_03]['url'][0]['data']))
                    $url = $this->sanitize($author[0]['child'][SP_NS_ATOM_03]['url'][0]['data'], SP_CONSTRUCT_IRI, $this->get_base($author[0]['child'][SP_NS_ATOM_03]['url'][0]));
                if (isset($author[0]['child'][SP_NS_ATOM_03]['email'][0]['data']))
                    $email = $this->sanitize($author[0]['child'][SP_NS_ATOM_03]['email'][0]['data'], SP_CONSTRUCT_TEXT);
                if ($name !== null || $email !== null || $url !== null)
                    $authors[] = $this->_sp_registry->create('Author', array($name, $url, $email));
            }
            if ($author = $this->get_item_tags(SP_NS_RSS_20, 'author'))
                $authors[] = $this->_sp_registry->create('Author', array(null, null, $this->sanitize($author[0]['data'], SP_CONSTRUCT_TEXT)));
            foreach ((array) $this->get_item_tags(SP_NS_DC_11, 'creator') as $author)
                $authors[] = $this->_sp_registry->create('Author', array($this->sanitize($author['data'], SP_CONSTRUCT_TEXT), null, null));
            foreach ((array) $this->get_item_tags(SP_NS_DC_10, 'creator') as $author)
                $authors[] = $this->_sp_registry->create('Author', array($this->sanitize($author['data'], SP_CONSTRUCT_TEXT), null, null));
            foreach ((array) $this->get_item_tags(SP_NS_I_TUNES, 'author') as $author)
                $authors[] = $this->_sp_registry->create('Author', array($this->sanitize($author['data'], SP_CONSTRUCT_TEXT), null, null));
            if (!empty($authors)) return array_unique($authors);
            elseif (($source = $this->get_source()) && ($authors = $source->get_authors()))
                return $authors;
            elseif ($authors = [$this->__sp_feed,'get_authors']) //todo this way?
                return $authors;
            return null;
        }
        public function get_copyright(){
            if ($return = $this->get_item_tags(SP_NS_ATOM_10, 'rights'))
                return $this->sanitize($return[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_10_construct_type', array($return[0]['atts'])), $this->get_base($return[0]));
            elseif ($return = $this->get_item_tags(SP_NS_DC_11, 'rights'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->get_item_tags(SP_NS_DC_10, 'rights'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
            return null;
        }
        public function get_date($date_format = 'j F Y, g:i a'){
            if (!isset($this->__sp_data['date'])){
                if ($return = $this->get_item_tags(SP_NS_ATOM_10, 'published'))
                    $this->__sp_data['date']['raw'] = $return[0]['data'];
                elseif ($return = $this->get_item_tags(SP_NS_RSS_20, 'pubDate'))
                    $this->__sp_data['date']['raw'] = $return[0]['data'];
                elseif ($return = $this->get_item_tags(SP_NS_DC_11, 'date'))
                    $this->__sp_data['date']['raw'] = $return[0]['data'];
                elseif ($return = $this->get_item_tags(SP_NS_DC_10, 'date'))
                    $this->__sp_data['date']['raw'] = $return[0]['data'];
                elseif ($return = $this->get_item_tags(SP_NS_ATOM_10, 'updated'))
                    $this->__sp_data['date']['raw'] = $return[0]['data'];
                elseif ($return = $this->get_item_tags(SP_NS_ATOM_03, 'issued'))
                    $this->__sp_data['date']['raw'] = $return[0]['data'];
                elseif ($return = $this->get_item_tags(SP_NS_ATOM_03, 'created'))
                    $this->__sp_data['date']['raw'] = $return[0]['data'];
                elseif ($return = $this->get_item_tags(SP_NS_ATOM_03, 'modified'))
                    $this->__sp_data['date']['raw'] = $return[0]['data'];
                if (!empty($this->__sp_data['date']['raw'])){
                    $parser = $this->_sp_registry->this->call('Parse_Date', 'get');
                    $this->__sp_data['date']['parsed'] = $parser->this->parse($this->__sp_data['date']['raw']);
                }
                else $this->__sp_data['date'] = null;
            }
            if ($this->__sp_data['date']){
                $date_format = (string) $date_format;
                switch ($date_format){
                    case '':
                        return $this->sanitize($this->__sp_data['date']['raw'], SP_CONSTRUCT_TEXT);
                    case 'U':
                        return $this->__sp_data['date']['parsed'];
                    default:
                        return date($date_format, $this->__sp_data['date']['parsed']);
                }
            }
            return null;
        }
        public function get_updated_date($date_format = 'j F Y, g:i a'){
            if (!isset($this->__sp_data['updated'])){
                if ($return = $this->get_item_tags(SP_NS_ATOM_10, 'updated'))
                    $this->__sp_data['updated']['raw'] = $return[0]['data'];
                if (!empty($this->sp_data['updated']['raw'])){
                    $parser = $this->_sp_registry->this->call('Parse_Date', 'get');
                    $this->__sp_data['updated']['parsed'] = $parser->this->parse($this->__sp_data['updated']['raw']);
                }
                else $this->__sp_data['updated'] = null;
            }if ($this->__sp_data['updated']){
                $date_format = (string) $date_format;
                switch ($date_format){
                    case '':
                        return $this->sanitize($this->__sp_data['updated']['raw'], SP_CONSTRUCT_TEXT);
                    case 'U':
                        return $this->__sp_data['updated']['parsed'];
                    default:
                        return date($date_format, $this->__sp_data['updated']['parsed']);
                }
            }
            return null;
        }
        public function get_local_date($date_format = '%c'){
            if (!$date_format)
                return $this->sanitize($this->get_date(''), SP_CONSTRUCT_TEXT);
            elseif (($date = $this->get_date('U')) !== null && $date !== false)
                return strftime($date_format, $date);
            return null;
        }
        public function get_gmdate($date_format = 'j F Y, g:i a'){
            $date = $this->get_date('U');
            if ($date === null)
                return null;
            return gmdate($date_format, $date);
        }
        public function get_updated_gmdate($date_format = 'j F Y, g:i a'){
            $date = $this->get_updated_date('U');
            if ($date === null) return null;
            return gmdate($date_format, $date);
        }
        public function get_permalink(){
            $link = $this->get_link();
            $enclosure = $this->get_enclosure(0);
            if ($link !== null) return $link;
            elseif ($enclosure !== null) return $enclosure->this->get_link();
            return null;
        }
        public function get_link($key = 0, $rel = 'alternate'){
            $links = $this->get_links($rel);
            if ($links && $links[$key] !== null)
                return $links[$key];
            return null;
        }
        public function get_links($rel = 'alternate'){
            if (!isset($this->__sp_data['links'])){
                $this->__sp_data['links'] = array();
                foreach ((array) $this->get_item_tags(SP_NS_ATOM_10, 'link') as $link){
                    if (isset($link['atts']['']['href'])){
                        $link_rel = $link['atts']['']['rel'] ?? 'alternate';
                        $this->__sp_data['links'][$link_rel][] = $this->sanitize($link['atts']['']['href'], SP_CONSTRUCT_IRI, $this->get_base($link));
                    }
                }
                foreach ((array) $this->get_item_tags(SP_NS_ATOM_03, 'link') as $link){
                    if (isset($link['atts']['']['href'])){
                        $link_rel = $link['atts']['']['rel'] ?? 'alternate';
                        $this->__sp_data['links'][$link_rel][] = $this->sanitize($link['atts']['']['href'], SP_CONSTRUCT_IRI, $this->get_base($link));
                    }
                }
                if ($links = $this->get_item_tags(SP_NS_RSS_10, 'link'))
                    $this->__sp_data['links']['alternate'][] = $this->sanitize($links[0]['data'], SP_CONSTRUCT_IRI, $this->get_base($links[0]));
                if ($links = $this->get_item_tags(SP_NS_RSS_090, 'link'))
                    $this->__sp_data['links']['alternate'][] = $this->sanitize($links[0]['data'], SP_CONSTRUCT_IRI, $this->get_base($links[0]));
                if ($links = $this->get_item_tags(SP_NS_RSS_20, 'link'))
                    $this->__sp_data['links']['alternate'][] = $this->sanitize($links[0]['data'], SP_CONSTRUCT_IRI, $this->get_base($links[0]));
                if ($links = $this->get_item_tags(SP_NS_RSS_20, 'guid')){
                    if (!isset($links[0]['atts']['']['isPermaLink']) || strtolower(trim($links[0]['atts']['']['isPermaLink'])) === 'true')
                        $this->__sp_data['links']['alternate'][] = $this->sanitize($links[0]['data'], SP_CONSTRUCT_IRI, $this->get_base($links[0]));
                }
                $keys = array_keys($this->__sp_data['links']);
                foreach ($keys as $key){
                    if ($this->_sp_registry->this->call('Misc', 'is_i_segment_nz_nc', array($key))){
                        $_sp_data = $this->__sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key];
                        if (isset($_sp_data))
                        {
                            $this->__sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key] = array_merge($this->sp_data['links'][$key], $this->__sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key]);
                            $this->__sp_data['links'][$key] =& $this->__sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key];
                        }
                        else $this->__sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key] =& $this->sp_data['links'][$key];

                    }elseif (strpos($key, SP_IANA_LINK_RELATIONS_REGISTRY) === 0)
                        $this->__sp_data['links'][substr($key, 41)] =& $this->__sp_data['links'][$key];
                    $this->__sp_data['links'][$key] = array_unique($this->__sp_data['links'][$key]);
                }
            }
            if (isset($this->__sp_data['links'][$rel])) return $this->__sp_data['links'][$rel];
            return null;
        }
        public function get_enclosure($key = 0, $prefer = null){
            $enclosures = $this->get_enclosures();
            if (isset($enclosures[$key]))
                return $enclosures[$key][$prefer];
            return null;
        }
        public function get_enclosures(){
            if($this->_sp_registry instanceof('SimplePie_Registry') )
                $this->_sp_registry = new SimplePie_Registry();
            if (!isset($this->__sp_data['enclosures'])){
                $this->__sp_data['enclosures'] = [];
                // Elements
                $captions_parent = null;
                $categories_parent = null;
                $copyrights_parent = null;
                $credits_parent = null;
                $description_parent = null;
                $duration_parent = null;
                $hashes_parent = null;
                $keywords_parent = null;
                $player_parent = null;
                $ratings_parent = null;
                $restrictions_parent = null;
                $thumbnails_parent = null;
                $title_parent = null;
                // Let's do the channel and item-level ones first, and just re-use them if we need to.
                //todo if this brew works? wow!
                static $_parent;
                if($_parent instanceof('SimplePie') )
                    $_parent = new SimplePie();
                $parent = $this->get_feed();
                // CAPTIONS
                if ($captions = $this->get_item_tags(SP_NS_MEDIA_RSS, 'text')){
                    foreach ($captions as $caption){
                        $caption_type = null;
                        $caption_lang = null;
                        $caption_startTime = null;
                        $caption_endTime = null;
                        $caption_text = null;
                        if (isset($caption['atts']['']['type']))
                            $caption_type = $this->sanitize($caption['atts']['']['type'], SP_CONSTRUCT_TEXT);
                        if (isset($caption['atts']['']['lang']))
                            $caption_lang = $this->sanitize($caption['atts']['']['lang'], SP_CONSTRUCT_TEXT);
                        if (isset($caption['atts']['']['start']))
                            $caption_startTime = $this->sanitize($caption['atts']['']['start'], SP_CONSTRUCT_TEXT);
                        if (isset($caption['atts']['']['end']))
                            $caption_endTime = $this->sanitize($caption['atts']['']['end'], SP_CONSTRUCT_TEXT);
                        if (isset($caption['data']))
                            $caption_text = $this->sanitize($caption['data'], SP_CONSTRUCT_TEXT);
                        $captions_parent[] = $this->_sp_registry->create('Caption', array($caption_type, $caption_lang, $caption_startTime, $caption_endTime, $caption_text));
                    }
                }
                elseif ($captions = $parent->_parent->sp_get_channel_tags(SP_NS_MEDIA_RSS, 'text')){
                    foreach ((array)$captions as $caption){
                        $caption_type = null;
                        $caption_lang = null;
                        $caption_startTime = null;
                        $caption_endTime = null;
                        $caption_text = null;
                        if (isset($caption['atts']['']['type']))
                            $caption_type = $this->sanitize($caption['atts']['']['type'], SP_CONSTRUCT_TEXT);
                        if (isset($caption['atts']['']['lang']))
                            $caption_lang = $this->sanitize($caption['atts']['']['lang'], SP_CONSTRUCT_TEXT);
                        if (isset($caption['atts']['']['start']))
                            $caption_startTime = $this->sanitize($caption['atts']['']['start'], SP_CONSTRUCT_TEXT);
                        if (isset($caption['atts']['']['end']))
                            $caption_endTime = $this->sanitize($caption['atts']['']['end'], SP_CONSTRUCT_TEXT);
                        if (isset($caption['data']))
                            $caption_text = $this->sanitize($caption['data'], SP_CONSTRUCT_TEXT);
                        $captions_parent[] = $this->_sp_registry->create('Caption', array($caption_type, $caption_lang, $caption_startTime, $caption_endTime, $caption_text));
                    }
                }
                if (is_array($captions_parent)) $captions_parent = array_values(array_unique($captions_parent));
                // CATEGORIES
                foreach ((array) $this->get_item_tags(SP_NS_MEDIA_RSS, 'category') as $category){
                    $term = null;
                    $scheme = null;
                    $label = null;
                    if (isset($category['data']))
                        $term = $this->sanitize($category['data'], SP_CONSTRUCT_TEXT);
                    if (isset($category['atts']['']['scheme']))
                        $scheme = $this->sanitize($category['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                    else $scheme = 'http://search.yahoo.com/mrss/category_schema';
                    if (isset($category['atts']['']['label']))
                        $label = $this->sanitize($category['atts']['']['label'], SP_CONSTRUCT_TEXT);
                    $categories_parent[] = $this->_sp_registry->create('Category', array($term, $scheme, $label));
                }
                foreach ((array) $parent->_parent->sp_get_channel_tags(SP_NS_MEDIA_RSS, 'category') as $category){
                    $term = null;
                    $scheme = null;
                    $label = null;
                    if (isset($category['data']))
                        $term = $this->sanitize($category['data'], SP_CONSTRUCT_TEXT);
                    if (isset($category['atts']['']['scheme']))
                        $scheme = $this->sanitize($category['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                    else $scheme = 'http://search.yahoo.com/mrss/category_schema';
                    if (isset($category['atts']['']['label']))
                        $label = $this->sanitize($category['atts']['']['label'], SP_CONSTRUCT_TEXT);
                    $categories_parent[] = $this->_sp_registry->create('Category', array($term, $scheme, $label));
                }
                foreach ((array) $parent->_parent->sp_get_channel_tags(SP_NS_I_TUNES, 'category') as $category){
                    $term = null;
                    $scheme = 'http://www.itunes.com/dtds/podcast-1.0.dtd';
                    $label = null;
                    if (isset($category['atts']['']['text']))
                        $label = $this->sanitize($category['atts']['']['text'], SP_CONSTRUCT_TEXT);
                    $categories_parent[] = $this->_sp_registry->create('Category', array($term, $scheme, $label));
                    if (isset($category['child'][SP_NS_I_TUNES]['category'])){
                        foreach ((array) $category['child'][SP_NS_I_TUNES]['category'] as $subcategory){
                            if (isset($subcategory['atts']['']['text']))
                                $label = $this->sanitize($subcategory['atts']['']['text'], SP_CONSTRUCT_TEXT);
                            $categories_parent[] = $this->_sp_registry->create('Category', array($term, $scheme, $label));
                        }
                    }
                }
                if (is_array($categories_parent))
                    $categories_parent = array_values(array_unique($categories_parent));
                // COPYRIGHT
                if ($copyright = $this->get_item_tags(SP_NS_MEDIA_RSS, 'copyright')){
                    $copyright_url = null;
                    $copyright_label = null;
                    if (isset($copyright[0]['atts']['']['url']))
                        $copyright_url = $this->sanitize($copyright[0]['atts']['']['url'], SP_CONSTRUCT_TEXT);
                    if (isset($copyright[0]['data']))
                        $copyright_label = $this->sanitize($copyright[0]['data'], SP_CONSTRUCT_TEXT);
                    $copyrights_parent = $this->_sp_registry->create('Copyright', array($copyright_url, $copyright_label));
                }elseif ($copyright = $parent->_parent->sp_get_channel_tags(SP_NS_MEDIA_RSS, 'copyright')){
                    $copyright_url = null;
                    $copyright_label = null;
                    if (isset($copyright[0]['atts']['']['url']))
                        $copyright_url = $this->sanitize($copyright[0]['atts']['']['url'], SP_CONSTRUCT_TEXT);
                    if (isset($copyright[0]['data']))
                        $copyright_label = $this->sanitize($copyright[0]['data'], SP_CONSTRUCT_TEXT);
                    $copyrights_parent = $this->_sp_registry->create('Copyright', array($copyright_url, $copyright_label));
                }
                // CREDITS
                if ($credits = $this->get_item_tags(SP_NS_MEDIA_RSS, 'credit')){
                    foreach ($credits as $credit){
                        $credit_role = null;
                        $credit_scheme = null;
                        $credit_name = null;
                        if (isset($credit['atts']['']['role']))
                            $credit_role = $this->sanitize($credit['atts']['']['role'], SP_CONSTRUCT_TEXT);
                        if (isset($credit['atts']['']['scheme']))
                            $credit_scheme = $this->sanitize($credit['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                        else
                            $credit_scheme = 'urn:ebu';
                        if (isset($credit['data']))
                            $credit_name = $this->sanitize($credit['data'], SP_CONSTRUCT_TEXT);
                        $credits_parent[] = $this->_sp_registry->create('Credit', array($credit_role, $credit_scheme, $credit_name));
                    }
                }elseif ($credits = $parent->_parent->sp_get_channel_tags(SP_NS_MEDIA_RSS, 'credit')){
                    foreach ((array)$credits as $credit){
                        $credit_role = null;
                        $credit_scheme = null;
                        $credit_name = null;
                        if (isset($credit['atts']['']['role']))
                            $credit_role = $this->sanitize($credit['atts']['']['role'], SP_CONSTRUCT_TEXT);
                        if (isset($credit['atts']['']['scheme']))
                            $credit_scheme = $this->sanitize($credit['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                        else $credit_scheme = 'urn:ebu';
                        if (isset($credit['data']))
                            $credit_name = $this->sanitize($credit['data'], SP_CONSTRUCT_TEXT);
                        $credits_parent[] = $this->_sp_registry->create('Credit', array($credit_role, $credit_scheme, $credit_name));
                    }
                }
                if (is_array($credits_parent)) $credits_parent = array_values(array_unique($credits_parent));
                // DESCRIPTION
                if ($description_parent = $this->get_item_tags(SP_NS_MEDIA_RSS, 'description')){
                    if (isset($description_parent[0]['data']))
                        $description_parent = $this->sanitize($description_parent[0]['data'], SP_CONSTRUCT_TEXT);
                } elseif ($description_parent = $parent->_parent->sp_get_channel_tags(SP_NS_MEDIA_RSS, 'description')){
                    if (isset($description_parent[0]['data']))
                        $description_parent = $this->sanitize($description_parent[0]['data'], SP_CONSTRUCT_TEXT);
                }
                // DURATION
                if ($duration_parent = $this->get_item_tags(SP_NS_I_TUNES, 'duration')){
                    $seconds = null;
                    $minutes = null;
                    $hours = null;
                    if (isset($duration_parent[0]['data'])){
                        $temp = explode(':', $this->sanitize($duration_parent[0]['data'], SP_CONSTRUCT_TEXT));
                        if (count($temp) > 0) $seconds = (int) array_pop($temp);
                        if (count($temp) > 0){
                            $minutes = (int) array_pop($temp);
                            $seconds += $minutes * 60;
                        }
                        if (count($temp) > 0){
                            $hours = (int) array_pop($temp);
                            $seconds += $hours * 3600;
                        }
                        unset($temp);
                        $duration_parent = $seconds;
                    }
                }
                // HASHES
                if ($hashes_iterator = $this->get_item_tags(SP_NS_MEDIA_RSS, 'hash')){
                    foreach ($hashes_iterator as $hash){
                        $value = null;
                        $algo = null;
                        if (isset($hash['data']))
                            $value = $this->sanitize($hash['data'], SP_CONSTRUCT_TEXT);
                        if (isset($hash['atts']['']['algo']))
                            $algo = $this->sanitize($hash['atts']['']['algo'], SP_CONSTRUCT_TEXT);
                        else $algo = 'md5';
                        $hashes_parent[] = $algo.':'.$value;
                    }
                }elseif ($hashes_iterator = $parent->_parent->sp_get_channel_tags(SP_NS_MEDIA_RSS, 'hash')){
                    foreach ((array)$hashes_iterator as $hash){
                        $value = null;
                        $algo = null;
                        if (isset($hash['data']))
                            $value = $this->sanitize($hash['data'], SP_CONSTRUCT_TEXT);
                        if (isset($hash['atts']['']['algo']))
                            $algo = $this->sanitize($hash['atts']['']['algo'], SP_CONSTRUCT_TEXT);
                        else $algo = 'md5';
                        $hashes_parent[] = $algo.':'.$value;
                    }
                }
                if (is_array($hashes_parent)) $hashes_parent = array_values(array_unique($hashes_parent));
                // KEYWORDS
                if ($keywords = $this->get_item_tags(SP_NS_MEDIA_RSS, 'keywords')){
                    if (isset($keywords[0]['data'])){
                        $temp = explode(',', $this->sanitize($keywords[0]['data'], SP_CONSTRUCT_TEXT));
                        foreach ($temp as $word) $keywords_parent[] = trim($word);
                    }
                    unset($temp);
                }
                elseif ($keywords = $this->get_item_tags(SP_NS_I_TUNES, 'keywords')){
                    if (isset($keywords[0]['data'])){
                        $temp = explode(',', $this->sanitize($keywords[0]['data'], SP_CONSTRUCT_TEXT));
                        foreach ($temp as $word) $keywords_parent[] = trim($word);
                    }
                    unset($temp);
                }elseif ($keywords = $parent->_parent->sp_get_channel_tags(SP_NS_MEDIA_RSS, 'keywords')){
                    if (isset($keywords[0]['data'])){
                        $temp = explode(',', $this->sanitize($keywords[0]['data'], SP_CONSTRUCT_TEXT));
                        foreach ($temp as $word) $keywords_parent[] = trim($word);
                    }
                    unset($temp);
                }elseif ($keywords = $parent->_parent->sp_get_channel_tags(SP_NS_I_TUNES, 'keywords')){
                    if (isset($keywords[0]['data'])){
                        $temp = explode(',', $this->sanitize($keywords[0]['data'], SP_CONSTRUCT_TEXT));
                        foreach ($temp as $word) $keywords_parent[] = trim($word);
                    }
                    unset($temp);
                }
                if (is_array($keywords_parent)) $keywords_parent = array_values(array_unique($keywords_parent));
                // PLAYER
                if ($player_parent = $this->get_item_tags(SP_NS_MEDIA_RSS, 'player')){
                    if (isset($player_parent[0]['atts']['']['url']))
                        $player_parent = $this->sanitize($player_parent[0]['atts']['']['url'], SP_CONSTRUCT_IRI);
                }elseif ($player_parent = $parent->_parent->sp_get_channel_tags(SP_NS_MEDIA_RSS, 'player')){
                    if (isset($player_parent[0]['atts']['']['url']))
                        $player_parent = $this->sanitize($player_parent[0]['atts']['']['url'], SP_CONSTRUCT_IRI);
                }
                // RATINGS
                if ($ratings = $this->get_item_tags(SP_NS_MEDIA_RSS, 'rating')){
                    foreach ($ratings as $rating){
                        $rating_scheme = null;
                        $rating_value = null;
                        if (isset($rating['atts']['']['scheme']))
                            $rating_scheme = $this->sanitize($rating['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                        else $rating_scheme = 'urn:simple';
                        if (isset($rating['data']))
                            $rating_value = $this->sanitize($rating['data'], SP_CONSTRUCT_TEXT);
                        $ratings_parent[] = $this->_sp_registry->create('Rating', array($rating_scheme, $rating_value));
                    }
                }elseif ($ratings = $this->get_item_tags(SP_NS_I_TUNES, 'explicit')){
                    foreach ($ratings as $rating){
                        $rating_scheme = 'urn:itunes';
                        $rating_value = null;
                        if (isset($rating['data']))
                            $rating_value = $this->sanitize($rating['data'], SP_CONSTRUCT_TEXT);
                        $ratings_parent[] = $this->_sp_registry->create('Rating', array($rating_scheme, $rating_value));
                    }
                }elseif ($ratings = $parent->_parent->sp_get_channel_tags(SP_NS_MEDIA_RSS, 'rating')){
                    foreach ((array)$ratings as $rating){
                        $rating_scheme = null;
                        $rating_value = null;
                        if (isset($rating['atts']['']['scheme']))
                            $rating_scheme = $this->sanitize($rating['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                        else
                            $rating_scheme = 'urn:simple';
                        if (isset($rating['data']))
                            $rating_value = $this->sanitize($rating['data'], SP_CONSTRUCT_TEXT);
                        $ratings_parent[] = $this->_sp_registry->create('Rating', array($rating_scheme, $rating_value));
                    }
                }elseif ($ratings = $parent->_parent->sp_get_channel_tags(SP_NS_I_TUNES, 'explicit')){
                    foreach ((array)$ratings as $rating){
                        $rating_scheme = 'urn:itunes';
                        $rating_value = null;
                        if (isset($rating['data']))
                            $rating_value = $this->sanitize($rating['data'], SP_CONSTRUCT_TEXT);
                        $ratings_parent[] = $this->_sp_registry->create('Rating', array($rating_scheme, $rating_value));
                    }
                }
                if (is_array($ratings_parent)) $ratings_parent = array_values(array_unique($ratings_parent));
                // RESTRICTIONS
                if ($restrictions = $this->get_item_tags(SP_NS_MEDIA_RSS, 'restriction')){
                    foreach ($restrictions as $restriction){
                        $restriction_relationship = null;
                        $restriction_type = null;
                        $restriction_value = null;
                        if (isset($restriction['atts']['']['relationship']))
                            $restriction_relationship = $this->sanitize($restriction['atts']['']['relationship'], SP_CONSTRUCT_TEXT);
                        if (isset($restriction['atts']['']['type']))
                            $restriction_type = $this->sanitize($restriction['atts']['']['type'], SP_CONSTRUCT_TEXT);
                        if (isset($restriction['data']))
                            $restriction_value = $this->sanitize($restriction['data'], SP_CONSTRUCT_TEXT);
                        $restrictions_parent[] = $this->_sp_registry->create('Restriction', array($restriction_relationship, $restriction_type, $restriction_value));
                    }
                }
                elseif ($restrictions = $this->get_item_tags(SP_NS_I_TUNES, 'block')){
                    foreach ($restrictions as $restriction){
                        $restriction_relationship = 'allow';
                        $restriction_type = null;
                        $restriction_value = 'itunes';
                        if (isset($restriction['data']) && strtolower($restriction['data']) === 'yes')
                            $restriction_relationship = 'deny';
                        $restrictions_parent[] = $this->_sp_registry->create('Restriction', array($restriction_relationship, $restriction_type, $restriction_value));
                    }
                }
                elseif ($restrictions = $parent->_parent->sp_get_channel_tags(SP_NS_MEDIA_RSS, 'restriction')) {
                    foreach ((array)$restrictions as $restriction){
                        $restriction_relationship = null;
                        $restriction_type = null;
                        $restriction_value = null;
                        if (isset($restriction['atts']['']['relationship']))
                            $restriction_relationship = $this->sanitize($restriction['atts']['']['relationship'], SP_CONSTRUCT_TEXT);
                        if (isset($restriction['atts']['']['type']))
                            $restriction_type = $this->sanitize($restriction['atts']['']['type'], SP_CONSTRUCT_TEXT);
                        if (isset($restriction['data']))
                            $restriction_value = $this->sanitize($restriction['data'], SP_CONSTRUCT_TEXT);
                        $restrictions_parent[] = $this->_sp_registry->create('Restriction', array($restriction_relationship, $restriction_type, $restriction_value));
                    }
                }
                elseif ($restrictions = $parent->_parent->sp_get_channel_tags(SP_NS_I_TUNES, 'block')){
                    foreach ((array)$restrictions as $restriction){
                        $restriction_relationship = 'allow';
                        $restriction_type = null;
                        $restriction_value = 'itunes';
                        if (isset($restriction['data']) && strtolower($restriction['data']) === 'yes')
                            $restriction_relationship = 'deny';
                        $restrictions_parent[] = $this->_sp_registry->create('Restriction', array($restriction_relationship, $restriction_type, $restriction_value));
                    }
                }
                if (is_array($restrictions_parent))
                    $restrictions_parent = array_values(array_unique($restrictions_parent));
                else $restrictions_parent = array(new SimplePie_Restriction('allow', null, 'default'));
                // THUMBNAILS
                if ($thumbnails = $this->get_item_tags(SP_NS_MEDIA_RSS, 'thumbnail')){
                    foreach ($thumbnails as $thumbnail){
                        if (isset($thumbnail['atts']['']['url']))
                            $thumbnails_parent[] = $this->sanitize($thumbnail['atts']['']['url'], SP_CONSTRUCT_IRI);
                    }
                }elseif ($thumbnails = $parent->_parent->sp_get_channel_tags(SP_NS_MEDIA_RSS, 'thumbnail')) {
                    foreach ((array)$thumbnails as $thumbnail){
                        if (isset($thumbnail['atts']['']['url']))
                            $thumbnails_parent[] = $this->sanitize($thumbnail['atts']['']['url'], SP_CONSTRUCT_IRI);
                    }
                }
                // TITLES
                if ($title_parent = $this->get_item_tags(SP_NS_MEDIA_RSS, 'title')){
                    if (isset($title_parent[0]['data']))
                        $title_parent = $this->sanitize($title_parent[0]['data'], SP_CONSTRUCT_TEXT);
                }elseif ($title_parent = $parent->_parent->sp_get_channel_tags(SP_NS_MEDIA_RSS, 'title')){
                    if (isset($title_parent[0]['data']))
                        $title_parent = $this->sanitize($title_parent[0]['data'], SP_CONSTRUCT_TEXT);
                }
                // Clear the memory
                unset($parent);
                // Attributes
                $bitrate = null;
                $channels = null;
                $duration = null;
                $expression = null;
                $framerate = null;
                $height = null;
                $javascript = null;
                $lang = null;
                $length = null;
                $medium = null;
                $samplingrate = null;
                $type = null;
                $url = null;
                $width = null;
                // Elements
                $captions = null;
                $categories = null;
                $copyrights = null;
                $credits = null;
                $description = null;
                $hashes = null;
                $keywords = null;
                $player = null;
                $ratings = null;
                $restrictions = null;
                $thumbnails = null;
                $title = null;
                // If we have media:group tags, loop through them.
                foreach ((array) $this->get_item_tags(SP_NS_MEDIA_RSS, 'group') as $group){
                    if(isset($group['child'][SP_NS_MEDIA_RSS]['content'])){
                        // If we have media:content tags, loop through them.
                        foreach ((array) $group['child'][SP_NS_MEDIA_RSS]['content'] as $content){
                            if (isset($content['atts']['']['url'])){
                                // Attributes
                                $bitrate = null;
                                $channels = null;
                                $duration = null;
                                $expression = null;
                                $framerate = null;
                                $height = null;
                                $javascript = null;
                                $lang = null;
                                $length = null;
                                $medium = null;
                                $samplingrate = null;
                                $type = null;
                                $url = null;
                                $width = null;
                                // Elements
                                $captions = null;
                                $categories = null;
                                $copyrights = null;
                                $credits = null;
                                $description = null;
                                $hashes = null;
                                $keywords = null;
                                $player = null;
                                $ratings = null;
                                $restrictions = null;
                                $thumbnails = null;
                                $title = null;
                                // Start checking the attributes of media:content
                                if (isset($content['atts']['']['bitrate']))
                                    $bitrate = $this->sanitize($content['atts']['']['bitrate'], SP_CONSTRUCT_TEXT);
                                if (isset($content['atts']['']['channels']))
                                    $channels = $this->sanitize($content['atts']['']['channels'], SP_CONSTRUCT_TEXT);
                                if (isset($content['atts']['']['duration']))
                                    $duration = $this->sanitize($content['atts']['']['duration'], SP_CONSTRUCT_TEXT);
                                else $duration = $duration_parent;
                                if (isset($content['atts']['']['expression']))
                                    $expression = $this->sanitize($content['atts']['']['expression'], SP_CONSTRUCT_TEXT);
                                if (isset($content['atts']['']['framerate']))
                                    $framerate = $this->sanitize($content['atts']['']['framerate'], SP_CONSTRUCT_TEXT);
                                if (isset($content['atts']['']['height']))
                                    $height = $this->sanitize($content['atts']['']['height'], SP_CONSTRUCT_TEXT);
                                if (isset($content['atts']['']['lang']))
                                    $lang = $this->sanitize($content['atts']['']['lang'], SP_CONSTRUCT_TEXT);
                                if (isset($content['atts']['']['fileSize']))
                                    $length = (int)($content['atts']['']['fileSize']);
                                if (isset($content['atts']['']['medium']))
                                    $medium = $this->sanitize($content['atts']['']['medium'], SP_CONSTRUCT_TEXT);
                                if (isset($content['atts']['']['samplingrate']))
                                    $samplingrate = $this->sanitize($content['atts']['']['samplingrate'], SP_CONSTRUCT_TEXT);
                                if (isset($content['atts']['']['type']))
                                    $type = $this->sanitize($content['atts']['']['type'], SP_CONSTRUCT_TEXT);
                                if (isset($content['atts']['']['width']))
                                    $width = $this->sanitize($content['atts']['']['width'], SP_CONSTRUCT_TEXT);
                                $url = $this->sanitize($content['atts']['']['url'], SP_CONSTRUCT_IRI);
                                // Checking the other optional media: elements. Priority: media:content, media:group, item, channel
                                // CAPTIONS
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['text'])){
                                    foreach ($content['child'][SP_NS_MEDIA_RSS]['text'] as $caption){
                                        $caption_type = null;
                                        $caption_lang = null;
                                        $caption_startTime = null;
                                        $caption_endTime = null;
                                        $caption_text = null;
                                        if (isset($caption['atts']['']['type']))
                                            $caption_type = $this->sanitize($caption['atts']['']['type'], SP_CONSTRUCT_TEXT);
                                        if (isset($caption['atts']['']['lang']))
                                            $caption_lang = $this->sanitize($caption['atts']['']['lang'], SP_CONSTRUCT_TEXT);
                                        if (isset($caption['atts']['']['start']))
                                            $caption_startTime = $this->sanitize($caption['atts']['']['start'], SP_CONSTRUCT_TEXT);
                                        if (isset($caption['atts']['']['end']))
                                            $caption_endTime = $this->sanitize($caption['atts']['']['end'], SP_CONSTRUCT_TEXT);
                                        if (isset($caption['data']))
                                            $caption_text = $this->sanitize($caption['data'], SP_CONSTRUCT_TEXT);
                                        $captions[] = $this->_sp_registry->create('Caption', array($caption_type, $caption_lang, $caption_startTime, $caption_endTime, $caption_text));
                                    }
                                    if (is_array($captions)) $captions = array_values(array_unique($captions));
                                }
                                elseif (isset($group['child'][SP_NS_MEDIA_RSS]['text'])){
                                    foreach ($group['child'][SP_NS_MEDIA_RSS]['text'] as $caption){
                                        $caption_type = null;
                                        $caption_lang = null;
                                        $caption_startTime = null;
                                        $caption_endTime = null;
                                        $caption_text = null;
                                        if (isset($caption['atts']['']['type']))
                                            $caption_type = $this->sanitize($caption['atts']['']['type'], SP_CONSTRUCT_TEXT);
                                        if (isset($caption['atts']['']['lang']))
                                            $caption_lang = $this->sanitize($caption['atts']['']['lang'], SP_CONSTRUCT_TEXT);
                                        if (isset($caption['atts']['']['start']))
                                            $caption_startTime = $this->sanitize($caption['atts']['']['start'], SP_CONSTRUCT_TEXT);
                                        if (isset($caption['atts']['']['end']))
                                            $caption_endTime = $this->sanitize($caption['atts']['']['end'], SP_CONSTRUCT_TEXT);
                                        if (isset($caption['data']))
                                            $caption_text = $this->sanitize($caption['data'], SP_CONSTRUCT_TEXT);
                                        $captions[] = $this->_sp_registry->create('Caption', array($caption_type, $caption_lang, $caption_startTime, $caption_endTime, $caption_text));
                                    }
                                    if (is_array($captions)) $captions = array_values(array_unique($captions));
                                }else $captions = $captions_parent;
                                // CATEGORIES
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['category'])){
                                    foreach ((array) $content['child'][SP_NS_MEDIA_RSS]['category'] as $category){
                                        $term = null;
                                        $scheme = null;
                                        $label = null;
                                        if (isset($category['data']))
                                            $term = $this->sanitize($category['data'], SP_CONSTRUCT_TEXT);
                                        if (isset($category['atts']['']['scheme']))
                                            $scheme = $this->sanitize($category['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                                        else $scheme = 'http://search.yahoo.com/mrss/category_schema';
                                        if (isset($category['atts']['']['label']))
                                            $label = $this->sanitize($category['atts']['']['label'], SP_CONSTRUCT_TEXT);
                                        $categories[] = $this->_sp_registry->create('Category', array($term, $scheme, $label));
                                    }
                                }
                                if (isset($group['child'][SP_NS_MEDIA_RSS]['category'])){
                                    foreach ((array) $group['child'][SP_NS_MEDIA_RSS]['category'] as $category){
                                        $term = null;
                                        $scheme = null;
                                        $label = null;
                                        if (isset($category['data']))
                                            $term = $this->sanitize($category['data'], SP_CONSTRUCT_TEXT);
                                        if (isset($category['atts']['']['scheme']))
                                            $scheme = $this->sanitize($category['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                                        else $scheme = 'http://search.yahoo.com/mrss/category_schema';
                                        if (isset($category['atts']['']['label']))
                                            $label = $this->sanitize($category['atts']['']['label'], SP_CONSTRUCT_TEXT);
                                        $categories[] = $this->_sp_registry->create('Category', array($term, $scheme, $label));
                                    }
                                }
                                if (is_array($categories) && is_array($categories_parent))
                                    $categories = array_values(array_unique(array_merge($categories, $categories_parent)));
                                elseif (is_array($categories))
                                    $categories = array_values(array_unique($categories));
                                elseif (is_array($categories_parent))
                                    $categories = array_values(array_unique($categories_parent));
                                // COPYRIGHTS
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['copyright'])){
                                    $copyright_url = null;
                                    $copyright_label = null;
                                    if (isset($content['child'][SP_NS_MEDIA_RSS]['copyright'][0]['atts']['']['url']))
                                        $copyright_url = $this->sanitize($content['child'][SP_NS_MEDIA_RSS]['copyright'][0]['atts']['']['url'], SP_CONSTRUCT_TEXT);
                                    if (isset($content['child'][SP_NS_MEDIA_RSS]['copyright'][0]['data']))
                                         $copyright_label = $this->sanitize($content['child'][SP_NS_MEDIA_RSS]['copyright'][0]['data'], SP_CONSTRUCT_TEXT);
                                    $copyrights = $this->_sp_registry->create('Copyright', array($copyright_url, $copyright_label));
                                }
                                elseif (isset($group['child'][SP_NS_MEDIA_RSS]['copyright'])){
                                    $copyright_url = null;
                                    $copyright_label = null;
                                    if (isset($group['child'][SP_NS_MEDIA_RSS]['copyright'][0]['atts']['']['url']))
                                        $copyright_url = $this->sanitize($group['child'][SP_NS_MEDIA_RSS]['copyright'][0]['atts']['']['url'], SP_CONSTRUCT_TEXT);
                                    if (isset($group['child'][SP_NS_MEDIA_RSS]['copyright'][0]['data']))
                                        $copyright_label = $this->sanitize($group['child'][SP_NS_MEDIA_RSS]['copyright'][0]['data'], SP_CONSTRUCT_TEXT);
                                    $copyrights = $this->_sp_registry->create('Copyright', array($copyright_url, $copyright_label));
                                } else $copyrights = $copyrights_parent;
                                // CREDITS
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['credit'])){
                                    foreach ($content['child'][SP_NS_MEDIA_RSS]['credit'] as $credit){
                                        $credit_role = null;
                                        $credit_scheme = null;
                                        $credit_name = null;
                                        if (isset($credit['atts']['']['role']))
                                            $credit_role = $this->sanitize($credit['atts']['']['role'], SP_CONSTRUCT_TEXT);
                                        if (isset($credit['atts']['']['scheme']))
                                            $credit_scheme = $this->sanitize($credit['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                                        else $credit_scheme = 'urn:ebu';
                                        if (isset($credit['data']))
                                            $credit_name = $this->sanitize($credit['data'], SP_CONSTRUCT_TEXT);
                                        $credits[] = $this->_sp_registry->create('Credit', array($credit_role, $credit_scheme, $credit_name));
                                    }
                                    if (is_array($credits)) $credits = array_values(array_unique($credits));
                                }
                                elseif (isset($group['child'][SP_NS_MEDIA_RSS]['credit'])){
                                    foreach ($group['child'][SP_NS_MEDIA_RSS]['credit'] as $credit){
                                        $credit_role = null;
                                        $credit_scheme = null;
                                        $credit_name = null;
                                        if (isset($credit['atts']['']['role']))
                                            $credit_role = $this->sanitize($credit['atts']['']['role'], SP_CONSTRUCT_TEXT);
                                        if (isset($credit['atts']['']['scheme']))
                                            $credit_scheme = $this->sanitize($credit['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                                        else $credit_scheme = 'urn:ebu';
                                        if (isset($credit['data']))
                                            $credit_name = $this->sanitize($credit['data'], SP_CONSTRUCT_TEXT);
                                        $credits[] = $this->_sp_registry->create('Credit', array($credit_role, $credit_scheme, $credit_name));
                                    }
                                    if (is_array($credits)) $credits = array_values(array_unique($credits));
                                }
                                else $credits = $credits_parent;
                                // DESCRIPTION
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['description']))
                                    $description = $this->sanitize($content['child'][SP_NS_MEDIA_RSS]['description'][0]['data'], SP_CONSTRUCT_TEXT);
                                elseif (isset($group['child'][SP_NS_MEDIA_RSS]['description']))
                                    $description = $this->sanitize($group['child'][SP_NS_MEDIA_RSS]['description'][0]['data'], SP_CONSTRUCT_TEXT);
                                else $description = $description_parent;
                                // HASHES
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['hash'])){
                                    foreach ($content['child'][SP_NS_MEDIA_RSS]['hash'] as $hash){
                                        $value = null;
                                        $algo = null;
                                        if (isset($hash['data'])) $value = $this->sanitize($hash['data'], SP_CONSTRUCT_TEXT);
                                        if (isset($hash['atts']['']['algo']))
                                            $algo = $this->sanitize($hash['atts']['']['algo'], SP_CONSTRUCT_TEXT);
                                        else $algo = 'md5';
                                        $hashes[] = $algo.':'.$value;
                                    }
                                    if (is_array($hashes)) $hashes = array_values(array_unique($hashes));
                                }
                                elseif (isset($group['child'][SP_NS_MEDIA_RSS]['hash'])){
                                    foreach ($group['child'][SP_NS_MEDIA_RSS]['hash'] as $hash){
                                        $value = null;
                                        $algo = null;
                                        if (isset($hash['data']))
                                            $value = $this->sanitize($hash['data'], SP_CONSTRUCT_TEXT);
                                        if (isset($hash['atts']['']['algo']))
                                            $algo = $this->sanitize($hash['atts']['']['algo'], SP_CONSTRUCT_TEXT);
                                        else $algo = 'md5';
                                        $hashes[] = $algo.':'.$value;
                                    }
                                    if (is_array($hashes)) $hashes = array_values(array_unique($hashes));
                                }
                                else $hashes = $hashes_parent;
                                // KEYWORDS
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['keywords'])){
                                    if (isset($content['child'][SP_NS_MEDIA_RSS]['keywords'][0]['data'])){
                                        $temp = explode(',', $this->sanitize($content['child'][SP_NS_MEDIA_RSS]['keywords'][0]['data'], SP_CONSTRUCT_TEXT));
                                        foreach ($temp as $word) $keywords[] = trim($word);
                                        unset($temp);
                                    }
                                    if (is_array($keywords)) $keywords = array_values(array_unique($keywords));
                                }
                                elseif (isset($group['child'][SP_NS_MEDIA_RSS]['keywords'])){
                                    if (isset($group['child'][SP_NS_MEDIA_RSS]['keywords'][0]['data'])){
                                        $temp = explode(',', $this->sanitize($group['child'][SP_NS_MEDIA_RSS]['keywords'][0]['data'], SP_CONSTRUCT_TEXT));
                                        foreach ($temp as $word) $keywords[] = trim($word);
                                        unset($temp);
                                    }
                                    if (is_array($keywords)) $keywords = array_values(array_unique($keywords));
                                }
                                else $keywords = $keywords_parent;
                                // PLAYER
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['player']))
                                    $player = $this->sanitize($content['child'][SP_NS_MEDIA_RSS]['player'][0]['atts']['']['url'], SP_CONSTRUCT_IRI);
                                elseif (isset($group['child'][SP_NS_MEDIA_RSS]['player']))
                                    $player = $this->sanitize($group['child'][SP_NS_MEDIA_RSS]['player'][0]['atts']['']['url'], SP_CONSTRUCT_IRI);
                                else $player = $player_parent;
                                // RATINGS
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['rating'])){
                                    foreach ($content['child'][SP_NS_MEDIA_RSS]['rating'] as $rating){
                                        $rating_scheme = null;
                                        $rating_value = null;
                                        if (isset($rating['atts']['']['scheme']))
                                            $rating_scheme = $this->sanitize($rating['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                                        else $rating_scheme = 'urn:simple';
                                        if (isset($rating['data']))
                                            $rating_value = $this->sanitize($rating['data'], SP_CONSTRUCT_TEXT);
                                        $ratings[] = $this->_sp_registry->create('Rating', array($rating_scheme, $rating_value));
                                    }
                                    if (is_array($ratings))
                                        $ratings = array_values(array_unique($ratings));
                                }elseif (isset($group['child'][SP_NS_MEDIA_RSS]['rating'])){
                                    foreach ($group['child'][SP_NS_MEDIA_RSS]['rating'] as $rating){
                                        $rating_scheme = null;
                                        $rating_value = null;
                                        if (isset($rating['atts']['']['scheme']))
                                            $rating_scheme = $this->sanitize($rating['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                                        else $rating_scheme = 'urn:simple';
                                        if (isset($rating['data']))
                                             $rating_value = $this->sanitize($rating['data'], SP_CONSTRUCT_TEXT);
                                        $ratings[] = $this->_sp_registry->create('Rating', array($rating_scheme, $rating_value));
                                    }
                                    if (is_array($ratings))
                                        $ratings = array_values(array_unique($ratings));
                                }
                                else $ratings = $ratings_parent;
                                // RESTRICTIONS
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['restriction'])){
                                    foreach ($content['child'][SP_NS_MEDIA_RSS]['restriction'] as $restriction){
                                        $restriction_relationship = null;
                                        $restriction_type = null;
                                        $restriction_value = null;
                                        if (isset($restriction['atts']['']['relationship']))
                                            $restriction_relationship = $this->sanitize($restriction['atts']['']['relationship'], SP_CONSTRUCT_TEXT);
                                        if (isset($restriction['atts']['']['type']))
                                            $restriction_type = $this->sanitize($restriction['atts']['']['type'], SP_CONSTRUCT_TEXT);
                                        if (isset($restriction['data']))
                                            $restriction_value = $this->sanitize($restriction['data'], SP_CONSTRUCT_TEXT);
                                        $restrictions[] = $this->_sp_registry->create('Restriction', array($restriction_relationship, $restriction_type, $restriction_value));
                                    }
                                    if (is_array($restrictions))
                                        $restrictions = array_values(array_unique($restrictions));
                                }
                                elseif (isset($group['child'][SP_NS_MEDIA_RSS]['restriction'])){
                                    foreach ($group['child'][SP_NS_MEDIA_RSS]['restriction'] as $restriction){
                                        $restriction_relationship = null;
                                        $restriction_type = null;
                                        $restriction_value = null;
                                        if (isset($restriction['atts']['']['relationship']))
                                            $restriction_relationship = $this->sanitize($restriction['atts']['']['relationship'], SP_CONSTRUCT_TEXT);
                                        if (isset($restriction['atts']['']['type']))
                                            $restriction_type = $this->sanitize($restriction['atts']['']['type'], SP_CONSTRUCT_TEXT);
                                        if (isset($restriction['data']))
                                            $restriction_value = $this->sanitize($restriction['data'], SP_CONSTRUCT_TEXT);
                                        $restrictions[] = $this->_sp_registry->create('Restriction', array($restriction_relationship, $restriction_type, $restriction_value));
                                    }
                                    if (is_array($restrictions))
                                        $restrictions = array_values(array_unique($restrictions));
                                }
                                else $restrictions = $restrictions_parent;
                                // THUMBNAILS
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['thumbnail'])){
                                    foreach ($content['child'][SP_NS_MEDIA_RSS]['thumbnail'] as $thumbnail)
                                        $thumbnails[] = $this->sanitize($thumbnail['atts']['']['url'], SP_CONSTRUCT_IRI);
                                    if (is_array($thumbnails))
                                        $thumbnails = array_values(array_unique($thumbnails));
                                }
                                elseif (isset($group['child'][SP_NS_MEDIA_RSS]['thumbnail'])){
                                    foreach ($group['child'][SP_NS_MEDIA_RSS]['thumbnail'] as $thumbnail)
                                        $thumbnails[] = $this->sanitize($thumbnail['atts']['']['url'], SP_CONSTRUCT_IRI);
                                    if (is_array($thumbnails))
                                        $thumbnails = array_values(array_unique($thumbnails));
                                } else $thumbnails = $thumbnails_parent;
                                // TITLES
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['title']))
                                    $title = $this->sanitize($content['child'][SP_NS_MEDIA_RSS]['title'][0]['data'], SP_CONSTRUCT_TEXT);
                                elseif (isset($group['child'][SP_NS_MEDIA_RSS]['title']))
                                    $title = $this->sanitize($group['child'][SP_NS_MEDIA_RSS]['title'][0]['data'], SP_CONSTRUCT_TEXT);
                                else $title = $title_parent;
                                $this->__sp_data['enclosures'][] = $this->_sp_registry->create('Enclosure', array($url, $type, $length, null, $bitrate, $captions, $categories, $channels, $copyrights, $credits, $description, $duration, $expression, $framerate, $hashes, $height, $keywords, $lang, $medium, $player, $ratings, $restrictions, $samplingrate, $thumbnails, $title, $width));
                            }
                        }
                    }
                }
                // If we have standalone media:content tags, loop through them.
                if (isset($this->__sp_data['child'][SP_NS_MEDIA_RSS]['content'])){
                    foreach ((array) $this->__sp_data['child'][SP_NS_MEDIA_RSS]['content'] as $content){
                        if (isset($content['atts']['']['url']) || isset($content['child'][SP_NS_MEDIA_RSS]['player'])){
                            // Attributes
                            $bitrate = null;
                            $channels = null;
                            $duration = null;
                            $expression = null;
                            $framerate = null;
                            $height = null;
                            $javascript = null;
                            $lang = null;
                            $length = null;
                            $medium = null;
                            $samplingrate = null;
                            $type = null;
                            $url = null;
                            $width = null;

                            // Elements
                            $captions = null;
                            $categories = null;
                            $copyrights = null;
                            $credits = null;
                            $description = null;
                            $hashes = null;
                            $keywords = null;
                            $player = null;
                            $ratings = null;
                            $restrictions = null;
                            $thumbnails = null;
                            $title = null;
                            // Start checking the attributes of media:content
                            if (isset($content['atts']['']['bitrate']))
                                $bitrate = $this->sanitize($content['atts']['']['bitrate'], SP_CONSTRUCT_TEXT);
                            if (isset($content['atts']['']['channels']))
                                $channels = $this->sanitize($content['atts']['']['channels'], SP_CONSTRUCT_TEXT);
                            if (isset($content['atts']['']['duration']))
                                $duration = $this->sanitize($content['atts']['']['duration'], SP_CONSTRUCT_TEXT);
                            else $duration = $duration_parent;
                            if (isset($content['atts']['']['expression']))
                                $expression = $this->sanitize($content['atts']['']['expression'], SP_CONSTRUCT_TEXT);
                            if (isset($content['atts']['']['framerate']))
                                $framerate = $this->sanitize($content['atts']['']['framerate'], SP_CONSTRUCT_TEXT);
                            if (isset($content['atts']['']['height']))
                                $height = $this->sanitize($content['atts']['']['height'], SP_CONSTRUCT_TEXT);
                            if (isset($content['atts']['']['lang']))
                                $lang = $this->sanitize($content['atts']['']['lang'], SP_CONSTRUCT_TEXT);
                            if (isset($content['atts']['']['fileSize']))
                                $length = (int)($content['atts']['']['fileSize']);
                            if (isset($content['atts']['']['medium']))
                                $medium = $this->sanitize($content['atts']['']['medium'], SP_CONSTRUCT_TEXT);
                            if (isset($content['atts']['']['samplingrate']))
                                $samplingrate = $this->sanitize($content['atts']['']['samplingrate'], SP_CONSTRUCT_TEXT);
                            if (isset($content['atts']['']['type']))
                                $type = $this->sanitize($content['atts']['']['type'], SP_CONSTRUCT_TEXT);
                            if (isset($content['atts']['']['width']))
                                $width = $this->sanitize($content['atts']['']['width'], SP_CONSTRUCT_TEXT);
                            if (isset($content['atts']['']['url']))
                                $url = $this->sanitize($content['atts']['']['url'], SP_CONSTRUCT_IRI);
                            // Checking the other optional media: elements. Priority: media:content, media:group, item, channel
                            // CAPTIONS
                            if (isset($content['child'][SP_NS_MEDIA_RSS]['text'])){
                                foreach ($content['child'][SP_NS_MEDIA_RSS]['text'] as $caption){
                                    $caption_type = null;
                                    $caption_lang = null;
                                    $caption_startTime = null;
                                    $caption_endTime = null;
                                    $caption_text = null;
                                    if (isset($caption['atts']['']['type']))
                                        $caption_type = $this->sanitize($caption['atts']['']['type'], SP_CONSTRUCT_TEXT);
                                    if (isset($caption['atts']['']['lang']))
                                        $caption_lang = $this->sanitize($caption['atts']['']['lang'], SP_CONSTRUCT_TEXT);
                                    if (isset($caption['atts']['']['start']))
                                        $caption_startTime = $this->sanitize($caption['atts']['']['start'], SP_CONSTRUCT_TEXT);
                                    if (isset($caption['atts']['']['end']))
                                        $caption_endTime = $this->sanitize($caption['atts']['']['end'], SP_CONSTRUCT_TEXT);
                                    if (isset($caption['data']))
                                        $caption_text = $this->sanitize($caption['data'], SP_CONSTRUCT_TEXT);
                                    $captions[] = $this->_sp_registry->create('Caption', array($caption_type, $caption_lang, $caption_startTime, $caption_endTime, $caption_text));
                                }
                                if (is_array($captions)) $captions = array_values(array_unique($captions));
                            }
                            else
                                $captions = $captions_parent;
                            // CATEGORIES
                            if (isset($content['child'][SP_NS_MEDIA_RSS]['category'])){
                                foreach ((array) $content['child'][SP_NS_MEDIA_RSS]['category'] as $category){
                                    $term = null;
                                    $scheme = null;
                                    $label = null;
                                    if (isset($category['data']))
                                        $term = $this->sanitize($category['data'], SP_CONSTRUCT_TEXT);
                                    if (isset($category['atts']['']['scheme']))
                                        $scheme = $this->sanitize($category['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                                    else $scheme = 'http://search.yahoo.com/mrss/category_schema';
                                    if (isset($category['atts']['']['label']))
                                        $label = $this->sanitize($category['atts']['']['label'], SP_CONSTRUCT_TEXT);
                                    $categories[] = $this->_sp_registry->create('Category', array($term, $scheme, $label));
                                }
                            }
                            if (is_array($categories) && is_array($categories_parent))
                                $categories = array_values(array_unique(array_merge($categories, $categories_parent)));
                            elseif (is_array($categories))
                                $categories = array_values(array_unique($categories));
                            elseif (is_array($categories_parent))
                                $categories = array_values(array_unique($categories_parent));
                            else $categories = null;
                            // COPYRIGHTS
                            if (isset($content['child'][SP_NS_MEDIA_RSS]['copyright'])){
                                $copyright_url = null;
                                $copyright_label = null;
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['copyright'][0]['atts']['']['url']))
                                    $copyright_url = $this->sanitize($content['child'][SP_NS_MEDIA_RSS]['copyright'][0]['atts']['']['url'], SP_CONSTRUCT_TEXT);
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['copyright'][0]['data']))
                                    $copyright_label = $this->sanitize($content['child'][SP_NS_MEDIA_RSS]['copyright'][0]['data'], SP_CONSTRUCT_TEXT);
                                $copyrights = $this->_sp_registry->create('Copyright', array($copyright_url, $copyright_label));
                            }
                            else $copyrights = $copyrights_parent;
                            // CREDITS
                            if (isset($content['child'][SP_NS_MEDIA_RSS]['credit'])){
                                foreach ($content['child'][SP_NS_MEDIA_RSS]['credit'] as $credit){
                                    $credit_role = null;
                                    $credit_scheme = null;
                                    $credit_name = null;
                                    if (isset($credit['atts']['']['role']))
                                        $credit_role = $this->sanitize($credit['atts']['']['role'], SP_CONSTRUCT_TEXT);
                                    if (isset($credit['atts']['']['scheme']))
                                        $credit_scheme = $this->sanitize($credit['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                                    else $credit_scheme = 'urn:ebu';
                                    if (isset($credit['data'])) $credit_name = $this->sanitize($credit['data'], SP_CONSTRUCT_TEXT);
                                    $credits[] = $this->_sp_registry->create('Credit', array($credit_role, $credit_scheme, $credit_name));
                                }
                                if (is_array($credits))
                                    $credits = array_values(array_unique($credits));
                            }
                            else $credits = $credits_parent;
                            // DESCRIPTION
                            if (isset($content['child'][SP_NS_MEDIA_RSS]['description']))
                                $description = $this->sanitize($content['child'][SP_NS_MEDIA_RSS]['description'][0]['data'], SP_CONSTRUCT_TEXT);
                            else $description = $description_parent;
                            // HASHES
                            if (isset($content['child'][SP_NS_MEDIA_RSS]['hash'])){
                                foreach ($content['child'][SP_NS_MEDIA_RSS]['hash'] as $hash){
                                    $value = null;
                                    $algo = null;
                                    if (isset($hash['data']))
                                        $value = $this->sanitize($hash['data'], SP_CONSTRUCT_TEXT);
                                    if (isset($hash['atts']['']['algo']))
                                        $algo = $this->sanitize($hash['atts']['']['algo'], SP_CONSTRUCT_TEXT);
                                    else $algo = 'md5';
                                    $hashes[] = $algo.':'.$value;
                                }
                                if (is_array($hashes))
                                    $hashes = array_values(array_unique($hashes));
                            }
                            else $hashes = $hashes_parent;
                            // KEYWORDS
                            if (isset($content['child'][SP_NS_MEDIA_RSS]['keywords'])){
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['keywords'][0]['data'])){
                                    $temp = explode(',', $this->sanitize($content['child'][SP_NS_MEDIA_RSS]['keywords'][0]['data'], SP_CONSTRUCT_TEXT));
                                    foreach ($temp as $word) $keywords[] = trim($word);
                                    unset($temp);
                                }
                                if (is_array($keywords))
                                    $keywords = array_values(array_unique($keywords));
                            }
                            else $keywords = $keywords_parent;
                            // PLAYER
                            if (isset($content['child'][SP_NS_MEDIA_RSS]['player'])){
                                if (isset($content['child'][SP_NS_MEDIA_RSS]['player'][0]['atts']['']['url']))
                                    $player = $this->sanitize($content['child'][SP_NS_MEDIA_RSS]['player'][0]['atts']['']['url'], SP_CONSTRUCT_IRI);
                            }
                            else $player = $player_parent;
                            // RATINGS
                            if (isset($content['child'][SP_NS_MEDIA_RSS]['rating'])){
                                foreach ($content['child'][SP_NS_MEDIA_RSS]['rating'] as $rating){
                                    $rating_scheme = null;
                                    $rating_value = null;
                                    if (isset($rating['atts']['']['scheme']))
                                        $rating_scheme = $this->sanitize($rating['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                                    else $rating_scheme = 'urn:simple';
                                    if (isset($rating['data']))
                                        $rating_value = $this->sanitize($rating['data'], SP_CONSTRUCT_TEXT);
                                    $ratings[] = $this->_sp_registry->create('Rating', array($rating_scheme, $rating_value));
                                }
                                if (is_array($ratings)) $ratings = array_values(array_unique($ratings));
                            }
                            else $ratings = $ratings_parent;
                            // RESTRICTIONS
                            if (isset($content['child'][SP_NS_MEDIA_RSS]['restriction'])){
                                foreach ($content['child'][SP_NS_MEDIA_RSS]['restriction'] as $restriction){
                                    $restriction_relationship = null;
                                    $restriction_type = null;
                                    $restriction_value = null;
                                    if (isset($restriction['atts']['']['relationship']))
                                        $restriction_relationship = $this->sanitize($restriction['atts']['']['relationship'], SP_CONSTRUCT_TEXT);
                                    if (isset($restriction['atts']['']['type']))
                                        $restriction_type = $this->sanitize($restriction['atts']['']['type'], SP_CONSTRUCT_TEXT);
                                    if (isset($restriction['data']))
                                        $restriction_value = $this->sanitize($restriction['data'], SP_CONSTRUCT_TEXT);
                                    $restrictions[] = $this->_sp_registry->create('Restriction', array($restriction_relationship, $restriction_type, $restriction_value));
                                }
                                if (is_array($restrictions))
                                    $restrictions = array_values(array_unique($restrictions));
                            }
                            else $restrictions = $restrictions_parent;
                            // THUMBNAILS
                            if (isset($content['child'][SP_NS_MEDIA_RSS]['thumbnail'])){
                                foreach ($content['child'][SP_NS_MEDIA_RSS]['thumbnail'] as $thumbnail){
                                    if (isset($thumbnail['atts']['']['url']))
                                        $thumbnails[] = $this->sanitize($thumbnail['atts']['']['url'], SP_CONSTRUCT_IRI);
                                }
                                if (is_array($thumbnails))
                                    $thumbnails = array_values(array_unique($thumbnails));
                            }
                            else $thumbnails = $thumbnails_parent;
                            // TITLES
                            if (isset($content['child'][SP_NS_MEDIA_RSS]['title']))
                                $title = $this->sanitize($content['child'][SP_NS_MEDIA_RSS]['title'][0]['data'], SP_CONSTRUCT_TEXT);
                            else $title = $title_parent;
                            $this->__sp_data['enclosures'][] = $this->_sp_registry->create('Enclosure', array($url, $type, $length, null, $bitrate, $captions, $categories, $channels, $copyrights, $credits, $description, $duration, $expression, $framerate, $hashes, $height, $keywords, $lang, $medium, $player, $ratings, $restrictions, $samplingrate, $thumbnails, $title, $width));
                        }
                    }
                }
                foreach ((array) $this->get_item_tags(SP_NS_ATOM_10, 'link') as $link){
                    if (isset($link['atts']['']['href']) && !empty($link['atts']['']['rel']) && $link['atts']['']['rel'] === 'enclosure'){
                        // Attributes
                        $bitrate = null;
                        $channels = null;
                        $duration = null;
                        $expression = null;
                        $framerate = null;
                        $height = null;
                        $javascript = null;
                        $lang = null;
                        $length = null;
                        $medium = null;
                        $samplingrate = null;
                        $type = null;
                        $url = null;
                        $width = null;
                        $url = $this->sanitize($link['atts']['']['href'], SP_CONSTRUCT_IRI, $this->get_base($link));
                        if (isset($link['atts']['']['type']))
                            $type = $this->sanitize($link['atts']['']['type'], SP_CONSTRUCT_TEXT);
                        if (isset($link['atts']['']['length']))
                            $length = (int)($link['atts']['']['length']);
                        if (isset($link['atts']['']['title']))
                            $title = $this->sanitize($link['atts']['']['title'], SP_CONSTRUCT_TEXT);
                        else $title = $title_parent;
                        // Since we don't have group or content for these, we'll just pass the '*_parent' variables directly to the constructor
                        $this->__sp_data['enclosures'][] = $this->_sp_registry->create('Enclosure', array($url, $type, $length, null, $bitrate, $captions_parent, $categories_parent, $channels, $copyrights_parent, $credits_parent, $description_parent, $duration_parent, $expression, $framerate, $hashes_parent, $height, $keywords_parent, $lang, $medium, $player_parent, $ratings_parent, $restrictions_parent, $samplingrate, $thumbnails_parent, $title, $width));
                    }
                }
                foreach ((array) $this->get_item_tags(SP_NS_ATOM_03, 'link') as $link){
                    if (isset($link['atts']['']['href']) && !empty($link['atts']['']['rel']) && $link['atts']['']['rel'] === 'enclosure'){
                        // Attributes
                        $bitrate = null;
                        $channels = null;
                        $duration = null;
                        $expression = null;
                        $framerate = null;
                        $height = null;
                        $javascript = null;
                        $lang = null;
                        $length = null;
                        $medium = null;
                        $samplingrate = null;
                        $type = null;
                        $url = null;
                        $width = null;
                        $url = $this->sanitize($link['atts']['']['href'], SP_CONSTRUCT_IRI, $this->get_base($link));
                        if (isset($link['atts']['']['type']))
                            $type = $this->sanitize($link['atts']['']['type'], SP_CONSTRUCT_TEXT);
                        if (isset($link['atts']['']['length']))
                            $length = (int)($link['atts']['']['length']);
                        // Since we don't have group or content for these, we'll just pass the '*_parent' variables directly to the constructor
                        $this->sp_data['enclosures'][] = $this->_sp_registry->create('Enclosure', array($url, $type, $length, null, $bitrate, $captions_parent, $categories_parent, $channels, $copyrights_parent, $credits_parent, $description_parent, $duration_parent, $expression, $framerate, $hashes_parent, $height, $keywords_parent, $lang, $medium, $player_parent, $ratings_parent, $restrictions_parent, $samplingrate, $thumbnails_parent, $title_parent, $width));
                    }
                }
                if (($enclosure = $this->get_item_tags(SP_NS_RSS_20, 'enclosure')) && isset($enclosure[0]['atts']['']['url'])) {
                    // Attributes
                    $bitrate = null;
                    $channels = null;
                    $duration = null;
                    $expression = null;
                    $framerate = null;
                    $height = null;
                    $javascript = null;
                    $lang = null;
                    $length = null;
                    $medium = null;
                    $samplingrate = null;
                    $type = null;
                    $url = null;
                    $width = null;
                    $url = $this->sanitize($enclosure[0]['atts']['']['url'], SP_CONSTRUCT_IRI, $this->get_base($enclosure[0]));
                    //todo
                    $url = $this->__sp_feed->__sp_sanitize->this->https_url($url);
                    if (isset($enclosure[0]['atts']['']['type']))
                        $type = $this->sanitize($enclosure[0]['atts']['']['type'], SP_CONSTRUCT_TEXT);
                    if (isset($enclosure[0]['atts']['']['length']))
                        $length = (int)($enclosure[0]['atts']['']['length']);
                    // Since we don't have group or content for these, we'll just pass the '*_parent' variables directly to the constructor
                    $this->__sp_data['enclosures'][] = $this->_sp_registry->create('Enclosure', array($url, $type, $length, null, $bitrate, $captions_parent, $categories_parent, $channels, $copyrights_parent, $credits_parent, $description_parent, $duration_parent, $expression, $framerate, $hashes_parent, $height, $keywords_parent, $lang, $medium, $player_parent, $ratings_parent, $restrictions_parent, $samplingrate, $thumbnails_parent, $title_parent, $width));
                }
                if (($url || $type || $length || $bitrate || $captions_parent || $categories_parent || $channels || $copyrights_parent || $credits_parent || $description_parent || $duration_parent || $expression || $framerate || $hashes_parent || $height || $keywords_parent || $lang || $medium || $player_parent || $ratings_parent || $restrictions_parent || $samplingrate || $thumbnails_parent || $title_parent || $width) || count($this->__sp_data['enclosures']) === 0)
                    $this->__sp_data['enclosures'][] = $this->_sp_registry->create('Enclosure', array($url, $type, $length, null, $bitrate, $captions_parent, $categories_parent, $channels, $copyrights_parent, $credits_parent, $description_parent, $duration_parent, $expression, $framerate, $hashes_parent, $height, $keywords_parent, $lang, $medium, $player_parent, $ratings_parent, $restrictions_parent, $samplingrate, $thumbnails_parent, $title_parent, $width));
                $this->__sp_data['enclosures'] = array_values(array_unique($this->__sp_data['enclosures']));
            }
            if (!empty($this->__sp_data['enclosures']))
                return $this->__sp_data['enclosures'];
            return null;
        }
        public function get_latitude():?float{
            if ($return = $this->get_item_tags(SP_NS_W3C_BASIC_GEO, 'lat'))
                return (float) $return[0]['data'];
            elseif (($return = $this->get_item_tags(SP_NS_GEORSS, 'point')) && preg_match('/^((?:-)?\d+(?:\.\d+)) ((?:-)?\d+(?:\.\d+))$/', trim($return[0]['data']), $match))
                return (float) $match[1];
            return null;
        }
        public function get_longitude():?float{
            if ($return = $this->get_item_tags(SP_NS_W3C_BASIC_GEO, 'long'))
                return (float) $return[0]['data'];
            elseif ($return = $this->get_item_tags(SP_NS_W3C_BASIC_GEO, 'lon'))
                return (float) $return[0]['data'];
            elseif (($return = $this->get_item_tags(SP_NS_GEORSS, 'point')) && preg_match('/^((?:-)?\d+(?:\.\d+)) ((?:-)?\d+(?:\.\d+))$/', trim($return[0]['data']), $match))
                return (float) $match[2];
            return null;
        }
        public function get_source(){
            if($this->_sp_registry instanceof('SimplePie_Registry') )
                $this->_sp_registry = new SimplePie_Registry();
            if ($return = $this->get_item_tags(SP_NS_ATOM_10, 'source'))
                return $this->_sp_registry->create('Source', array($this, $return[0]));
            return null;
        }
    }
}else die;