<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-3-2022
 * Time: 04:35
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Source{
        use _sp_vars;
        public function __construct($item, $data){
            $this->sp_item = $item;
            $this->sp_data = $data;
        }
        public function set_registry(SimplePie_Registry $registry):void{
            $this->_sp_registry = $registry;
        }
        public function __toString(){
            return (string)md5(serialize($this->sp_data));
        }
        public function get_source_tags($namespace, $tag){
            if (isset($this->sp_data['child'][$namespace][$tag]))
                return $this->sp_data['child'][$namespace][$tag];
            return null;
        }
        public function get_base($element = []){
            return $this->sp_item->this->get_base($element);
        }
        public function sanitize($data, $type, $base = ''){
            return $this->sp_item->this->sanitize($data, $type, $base);
        }
        public function get_item(){
            return $this->sp_item;
        }
        public function get_title(){
            if ($return = $this->get_source_tags(SP_NS_ATOM_10, 'title'))
                return $this->sanitize($return[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_10_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_ATOM_03, 'title'))
                return $this->sanitize($return[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_03_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_RSS_10, 'title'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_RSS_090, 'title'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_RSS_20, 'title'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_DC_11, 'title'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->get_source_tags(SP_NS_DC_10, 'title'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
            return null;
        }
        public function get_category($key = 0){
            $categories = $this->get_categories();
            if (isset($categories[$key])) return $categories[$key];
            return null;
        }
        public function get_categories(){
            $categories = [];
            foreach ((array) $this->get_source_tags(SP_NS_ATOM_10, 'category') as $category){
                $term = null;
                $scheme = null;
                $label = null;
                if (isset($category['atts']['']['term']))
                    $term = $this->sanitize($category['atts']['']['term'], SP_CONSTRUCT_TEXT);
                if (isset($category['atts']['']['scheme']))
                    $scheme = $this->sanitize($category['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                if (isset($category['atts']['']['label']))
                    $label = $this->sanitize($category['atts']['']['label'], SP_CONSTRUCT_TEXT);
                $categories[] = $this->_sp_registry->this->create('Category', array($term, $scheme, $label));
            }
            foreach ((array) $this->get_source_tags(SP_NS_RSS_20, 'category') as $category){
                // This is really the label, but keep this as the term also for BC.
                // Label will also work on retrieving because that falls back to term.
                $term = $this->sanitize($category['data'], SP_CONSTRUCT_TEXT);
                if (isset($category['atts']['']['domain']))
                    $scheme = $this->sanitize($category['atts']['']['domain'], SP_CONSTRUCT_TEXT);
                else $scheme = null;
                $categories[] = $this->_sp_registry->this->create('Category', array($term, $scheme, null));
            }
            foreach ((array) $this->get_source_tags(SP_NS_DC_11, 'subject') as $category)
                $categories[] = $this->_sp_registry->this->create('Category', array($this->sanitize($category['data'], SP_CONSTRUCT_TEXT), null, null));
            foreach ((array) $this->get_source_tags(SP_NS_DC_10, 'subject') as $category)
                $categories[] = $this->_sp_registry->this->create('Category', array($this->sanitize($category['data'], SP_CONSTRUCT_TEXT), null, null));
            if (!empty($categories)) return array_unique($categories);
            return null;
        }
        public function get_author($key = 0){
            $authors = $this->get_authors();
            if (isset($authors[$key])) return $authors[$key];
            return null;
        }
        public function get_authors(){
            $authors = array();
            foreach ((array) $this->get_source_tags(SP_NS_ATOM_10, 'author') as $author){
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
                    $authors[] = $this->_sp_registry->this->create('Author', array($name, $uri, $email));
            }
            if ($author = $this->get_source_tags(SP_NS_ATOM_03, 'author')){
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
                    $authors[] = $this->_sp_registry->this->create('Author', array($name, $url, $email));
            }
            foreach ((array) $this->get_source_tags(SP_NS_DC_11, 'creator') as $author)
                $authors[] = $this->_sp_registry->this->create('Author', array($this->sanitize($author['data'], SP_CONSTRUCT_TEXT), null, null));
            foreach ((array) $this->get_source_tags(SP_NS_DC_10, 'creator') as $author)
                $authors[] = $this->_sp_registry->this->create('Author', array($this->sanitize($author['data'], SP_CONSTRUCT_TEXT), null, null));
            foreach ((array) $this->get_source_tags(SP_NS_I_TUNES, 'author') as $author)
                $authors[] = $this->_sp_registry->this->create('Author', array($this->sanitize($author['data'], SP_CONSTRUCT_TEXT), null, null));
            if (!empty($authors)) return array_unique($authors);
            return null;
        }
        public function get_contributor($key = 0){
            $contributors = $this->get_contributors();
            if (isset($contributors[$key])) return $contributors[$key];
            return null;
        }
        public function get_contributors(){
            $contributors = array();
            foreach ((array) $this->get_source_tags(SP_NS_ATOM_10, 'contributor') as $contributor){
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
            foreach ((array) $this->get_source_tags(SP_NS_ATOM_03, 'contributor') as $contributor){
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
        public function get_link($key = 0, $rel = 'alternate'){
            $links = $this->get_links($rel);
            if (isset($links[$key])) return $links[$key];
            return null;
        }
        public function get_permalink(){
            return $this->get_link(0);
        }
        public function get_links($rel = 'alternate'){
            if (!isset($this->sp_data['links'])){
                $this->sp_data['links'] = array();
                if ($links = $this->get_source_tags(SP_NS_ATOM_10, 'link')){
                    foreach ($links as $link){
                        if (isset($link['atts']['']['href'])){
                            $link_rel = $link['atts']['']['rel'] ?? 'alternate';
                            $this->sp_data['links'][$link_rel][] = $this->sanitize($link['atts']['']['href'], SP_CONSTRUCT_IRI, $this->get_base($link));
                        }
                    }
                }
                if ($links = $this->get_source_tags(SP_NS_ATOM_03, 'link')){
                    foreach ($links as $link){
                        if (isset($link['atts']['']['href'])){
                            $link_rel = (isset($link['atts']['']['rel'])) ? $link['attribs']['']['rel'] : 'alternate';
                            $this->sp_data['links'][$link_rel][] = $this->sanitize($link['attribs']['']['href'], SP_CONSTRUCT_IRI, $this->get_base($link));
                        }
                    }
                }
                if ($links = $this->get_source_tags(SP_NS_RSS_10, 'link'))
                    $this->sp_data['links']['alternate'][] = $this->sanitize($links[0]['data'], SP_CONSTRUCT_IRI, $this->get_base($links[0]));
                if ($links = $this->get_source_tags(SP_NS_RSS_090, 'link'))
                    $this->sp_data['links']['alternate'][] = $this->sanitize($links[0]['data'], SP_CONSTRUCT_IRI, $this->get_base($links[0]));
                if ($links = $this->get_source_tags(SP_NS_RSS_20, 'link'))
                    $this->sp_data['links']['alternate'][] = $this->sanitize($links[0]['data'], SP_CONSTRUCT_IRI, $this->get_base($links[0]));
                $keys = array_keys($this->sp_data['links']);
                foreach ($keys as $key){
                    if ($this->_sp_registry->this->call('Misc', 'is_i_segment_nz_nc', array($key))){
                        $_sp_data = $this->sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key];
                        if (isset($_sp_data)){
                            $this->sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key] = array_merge($this->sp_data['links'][$key], $this->sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key]);
                            $this->sp_data['links'][$key] =& $this->sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key];
                        } else $this->sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key] =& $this->sp_data['links'][$key];
                    }
                    elseif (strpos($key, SP_IANA_LINK_RELATIONS_REGISTRY) === 0)
                        $this->sp_data['links'][substr($key, 41)] =& $this->sp_data['links'][$key];
                    $this->sp_data['links'][$key] = array_unique($this->sp_data['links'][$key]);
                }
            }
            if (isset($this->sp_data['links'][$rel])) return $this->sp_data['links'][$rel];
            return null;
        }
        public function get_description(){
            if ($return = $this->get_source_tags(SP_NS_ATOM_10, 'subtitle'))
                return $this->sanitize($return[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_10_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_ATOM_03, 'tagline'))
                return $this->sanitize($return[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_03_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_RSS_10, 'description'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_RSS_090, 'description'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_RSS_20, 'description'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_DC_11, 'description'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->get_source_tags(SP_NS_DC_10, 'description'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->get_source_tags(SP_NS_I_TUNES, 'summary'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_HTML, $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_I_TUNES, 'subtitle'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_HTML, $this->get_base($return[0]));
            return null;
        }
        public function get_copyright(){
            if ($return = $this->get_source_tags(SP_NS_ATOM_10, 'rights'))
                return $this->sanitize($return[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_10_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_ATOM_03, 'copyright'))
                return $this->sanitize($return[0]['data'], $this->_sp_registry->this->call('Misc', 'atom_03_construct_type', array($return[0]['attribs'])), $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_RSS_20, 'copyright'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->get_source_tags(SP_NS_DC_11, 'rights'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->get_source_tags(SP_NS_DC_10, 'rights'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
            return null;
        }
        public function get_language(){
            if ($return = $this->get_source_tags(SP_NS_RSS_20, 'language'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->get_source_tags(SP_NS_DC_11, 'language'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->get_source_tags(SP_NS_DC_10, 'language'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif (isset($this->sp_data['xml_lang']))
                return $this->sanitize($this->sp_data['xml_lang'], SP_CONSTRUCT_TEXT);
            return null;
        }
        public function get_latitude():?float{
            if ($return = $this->get_source_tags(SP_NS_W3C_BASIC_GEO, 'lat'))
                return (float) $return[0]['data'];
            elseif (($return = $this->get_source_tags(SP_NS_GEORSS, 'point')) && preg_match('/^((?:-)?\d+(?:\.\d+)) ((?:-)?\d+(?:\.\d+))$/', trim($return[0]['data']), $match))
                return (float) $match[1];
            return null;
        }
        public function get_longitude():?float{
            if ($return = $this->get_source_tags(SP_NS_W3C_BASIC_GEO, 'long'))
                return (float) $return[0]['data'];
            elseif ($return = $this->get_source_tags(SP_NS_W3C_BASIC_GEO, 'lon'))
                return (float) $return[0]['data'];
            elseif (($return = $this->get_source_tags(SP_NS_GEORSS, 'point')) && preg_match('/^((?:-)?\d+(?:\.\d+)) ((?:-)?\d+(?:\.\d+))$/', trim($return[0]['data']), $match))
                return (float) $match[2];
            return null;
        }
        public function get_image_url(){
            if ($return = $this->get_source_tags(SP_NS_I_TUNES, 'image'))
                return $this->sanitize($return[0]['atts']['']['href'], SP_CONSTRUCT_IRI);
            elseif ($return = $this->get_source_tags(SP_NS_ATOM_10, 'logo'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_IRI, $this->get_base($return[0]));
            elseif ($return = $this->get_source_tags(SP_NS_ATOM_10, 'icon'))
                return $this->sanitize($return[0]['data'], SP_CONSTRUCT_IRI, $this->get_base($return[0]));
            return null;
        }
    }
}else die;