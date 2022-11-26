<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 07:24
 */
namespace TP_Core\Libs\SimplePie\Factory;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\Factory\_encoding_04;
if(ABSPATH){
    trait _sp_consts{
        use _encoding_04;
        public function simple_pie_hooks(): void
        {
            define('SP_NAME', 'SimplePie');
            define('SP_VERSION', '1.5.8');
            define('SP_BUILD', gmdate('YmdHis', $this->sp_get_build()));
            define('SP_URL', 'https://simplepie.org'); //todo or I'm going to use this?
            define('SP_USERAGENT', SP_NAME . '/' . SP_VERSION . ' (Feed Parser; ' . SP_URL . '; Allow like Gecko) Build/');//todo place this back ' . SIMPLEPIE_BUILD'
            define('SP_LINKBACK', '<a href="' . SP_URL . '" title="' . SP_NAME . ' ' . SP_VERSION . '">' . SP_NAME . '</a>');
            define('SP_LOCATOR_NONE', 0);
            define('SP_LOCATOR_AUTODISCOVERY', 1);
            define('SP_LOCATOR_LOCAL_EXTENSION', 2);
            define('SP_LOCATOR_LOCAL_BODY', 4);
            define('SP_LOCATOR_REMOTE_EXTENSION', 8);
            define('SP_LOCATOR_REMOTE_BODY', 16);
            define('SP_LOCATOR_ALL', 31);
            define('SP_TYPE_NONE', 0);
            define('SP_TYPE_RSS_090', 1);
            define('SP_TYPE_RSS_091_NETSCAPE', 2);
            define('SP_TYPE_RSS_091_USERLAND', 4);
            define('SP_TYPE_RSS_091', 6);
            define('SP_TYPE_RSS_092', 8);
            define('SP_TYPE_RSS_093', 16);
            define('SP_TYPE_RSS_094', 32);
            define('SP_TYPE_RSS_10', 64);
            define('SP_TYPE_RSS_20', 128);
            define('SP_TYPE_RSS_RDF', 65);
            define('SP_TYPE_RSS_SYNDICATION', 190);
            define('SP_TYPE_RSS_ALL', 255);
            define('SP_TYPE_ATOM_03', 256);
            define('SP_TYPE_ATOM_10', 512);
            define('SP_TYPE_ATOM_ALL', 768);
            define('SP_TYPE_ALL', 1023);
            define('SP_CONSTRUCT_NONE', 0);
            define('SP_CONSTRUCT_TEXT', 1);
            define('SP_CONSTRUCT_HTML', 2);
            define('SP_CONSTRUCT_BASE64', 8);
            define('SP_CONSTRUCT_IRI', 16);
            define('SP_CONSTRUCT_MAYBE_HTML', 32);
            define('SP_CONSTRUCT_ALL', 63);
            define('SP_SAME_CASE', 1);
            define('SP_LOWERCASE', 2);
            define('SP_UPPERCASE', 4);
            define('SP_PCRE_HTML_ATTRIBUTE', '((?:[\x09\x0A\x0B\x0C\x0D\x20]+[^\x09\x0A\x0B\x0C\x0D\x20\x2F\x3E][^\x09\x0A\x0B\x0C\x0D\x20\x2F\x3D\x3E]*(?:[\x09\x0A\x0B\x0C\x0D\x20]*=[\x09\x0A\x0B\x0C\x0D\x20]*(?:"(?:[^"]*)"|\'(?:[^\']*)\'|(?:[^\x09\x0A\x0B\x0C\x0D\x20\x22\x27\x3E][^\x09\x0A\x0B\x0C\x0D\x20\x3E]*)?))?)*)[\x09\x0A\x0B\x0C\x0D\x20]*');
            define('SP_PCRE_XML_ATTRIBUTE', '((?:\s+(?:(?:[^\s:]+:)?[^\s:]+)\s*=\s*(?:"(?:[^"]*)"|\'(?:[^\']*)\'))*)\s*');
            define('SP_NS_XML', 'http://www.w3.org/XML/1998/namespace');
            define('SP_NS_ATOM_10', 'http://www.w3.org/2005/Atom');
            define('SP_NS_ATOM_03', 'http://purl.org/atom/ns#');
            define('SP_NS_RDF', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
            define('SP_NS_RSS_090', 'http://my.netscape.com/rdf/simple/0.9/');
            define('SP_NS_RSS_10', 'http://purl.org/rss/1.0/');
            define('SP_NS_RSS_10_MODULES_CONTENT', 'http://purl.org/rss/1.0/modules/content/');
            define('SP_NS_RSS_20', '');
            define('SP_NS_DC_10', 'http://purl.org/dc/elements/1.0/');
            define('SP_NS_DC_11', 'http://purl.org/dc/elements/1.1/');
            define('SP_NS_W3C_BASIC_GEO', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
            define('SP_NS_GEORSS', 'http://www.georss.org/georss');
            define('SP_NS_MEDIA_RSS', 'http://search.yahoo.com/mrss/');
            define('SP_NS_MEDIA_RSS_WRONG', 'http://search.yahoo.com/mrss');
            define('SP_NS_MEDIA_RSS_WRONG2', 'http://video.search.yahoo.com/mrss');
            define('SP_NS_MEDIA_RSS_WRONG3', 'http://video.search.yahoo.com/mrss/');
            define('SP_NS_MEDIA_RSS_WRONG4', 'http://www.rssboard.org/media-rss');
            define('SP_NS_MEDIA_RSS_WRONG5', 'http://www.rssboard.org/media-rss/');
            define('SP_NS_I_TUNES', 'http://www.itunes.com/dtds/podcast-1.0.dtd');
            define('SP_IANA_LINK_RELATIONS_REGISTRY', 'http://www.iana.org/assignments/relation/');
            define('SP_FILE_SOURCE_NONE', 0);
            define('SP_FILE_SOURCE_REMOTE', 1);
            define('SP_FILE_SOURCE_LOCAL', 2);
            define('SP_FILE_SOURCE_FSOCKOPEN', 4);
            define('SP_FILE_SOURCE_CURL', 8);
            define('SP_FILE_SOURCE_FILE_GET_CONTENTS', 16);
            return null;
        }
    }
}else die;