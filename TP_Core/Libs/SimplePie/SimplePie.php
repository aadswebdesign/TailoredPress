<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 10:06
 */
namespace TP_Core\Libs\SimplePie;
use TP_Core\Libs\SimplePie\Factory\_sp_consts;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\_encodings;
USE TP_Core\Libs\SimplePie\SP_Components\Cache\SP_Cache_File;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_Exception;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_File;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_Registry;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_Sanitize;
if(ABSPATH){
    class SimplePie{
        use _sp_consts;
        use _sp_vars;
        use _encodings;
        public function __construct(){
            $this->simple_pie_hooks();
            if (PHP_VERSION_ID < 50600){ //todo setting the min version
                trigger_error('Please upgrade to PHP 5.6 or newer.');
                die();
            }
            $this->_sp_sanitize = new SimplePie_Sanitize();
            $this->_sp_registry = new SimplePie_Registry();
        }
        public function __toString(){
            return (string)md5(serialize($this->__sp_data));
        }//741
        public function __destruct(){
            if (!gc_enabled()){
                if (!empty($this->__sp_data['items'])){
                    foreach ($this->__sp_data['items'] as $item)$item->this->__destruct();
                }
                if (!empty($this->__sp_data['ordered_items'])){
                    foreach ($this->__sp_data['ordered_items'] as $item)$item->this->__destruct();
                }
            }
        }//749
        public function sp_force_feed($enable = false): void
        {
            $this->__sp_force_feed = (bool) $enable;
            return null;
        }//781
        public function sp_set_feed_url($url): void
        {
            $this->__sp_multi_feed_url = [];
            if (is_array($url)){
                foreach ($url as $value) $this->__sp_multi_feed_url[] = $this->_sp_registry->call('Misc', 'fix_protocol', array($value, 1));
            }else{
                $this->__sp_feed_url = $this->_sp_registry->call('Misc', 'fix_protocol', array($url, 1));
                $this->__sp_permanent_url = $this->__sp_feed_url;
            }
            return null;
        }//801
        public function sp_set_file(&$file): bool
        {
            if ($file instanceof SimplePie_File){
                $this->__sp_feed_url = $file->sp_url;
                $this->__sp_permanent_url = $this->__sp_feed_url;
                $this->__sp_file =& $file;
                return true;
            }
            return false;
        }//824
        public function sp_set_raw_data($data): void
        {
            $this->__sp_raw_data = $data;
            return null;
        }//849
        public function sp_set_timeout($timeout = 10): void
        {
            $this->__sp_timeout = (int) $timeout;
            return null;
        }//863
        public function sp_set_curl_options(array $curl_options = []): void
        {
            $this->__sp_curl_options = $curl_options;
            return null;
        }//876
        public function sp_set_force_fsockopen($enable = false): void
        {
            $this->__sp_force_fsock_open = (bool) $enable;
            return null;
        }//887
        public function sp_set_enable_cache($enable = true): void
        {
            $this->__sp_cache = (bool) $enable;
            return null;
        }//901
        public function sp_force_cache_fallback($enable = false): void
        {
            $this->__sp_force_cache_fallback= (bool) $enable;
            return null;
        }//916
        public function sp_set_cache_duration($seconds = 3600): void
        {
            $this->__sp_cache_duration = (int) $seconds;
            return null;
        }//927
        public function sp_set_autodiscovery_cache_duration($seconds = 604800): void
        {
            $this->__sp_autodiscovery_cache_duration = (int) $seconds;
            return null;
        }//938
        //todo set the cache dir location
        public function sp_set_cache_location($location = './cache'): void
        {
            $this->__sp_cache_location = (string) $location;
            return null;
        }//948
        public function sp_get_cache_filename($url){
            // Append custom parameters to the URL to avoid cache pollution in case of multiple calls with different parameters.
            $url .= $this->__sp_force_feed ? '#force_feed' : '';
            $options = [];
            if ($this->__sp_timeout !== 10)
                $options[CURLOPT_TIMEOUT] = $this->__sp_timeout;
            if ($this->__sp_useragent !== SP_USERAGENT)
                $options[CURLOPT_USERAGENT] = $this->__sp_useragent;
            if (!empty($this->__sp_curl_options))
                foreach ($this->__sp_curl_options as $k => $v) $options[$k] = $v;
            if (!empty($options)){
                ksort($options);
                $url .= '#' . urlencode(var_export($options, true));
            }
            return call_user_func($this->__sp_cache_name_function, $url);
        }//958
        public function sp_enable_order_by_date($enable = true): void
        {
            $this->__sp_order_by_date = (bool) $enable;
            return null;
        }//991
        public function sp_set_input_encoding($encoding = false): void
        {
            if ($encoding) $this->__sp_input_encoding = (string) $encoding;
            else $this->__sp_input_encoding = false;
            return null;
        }//1004
        public function sp_set_autodiscovery_level($level = SP_LOCATOR_ALL): void
        {
            $this->__sp_autodiscovery = (int) $level;
            return null;
        }//1028
        public function &sp_get_registry(): SimplePie_Registry
        {
            return $this->_sp_registry;
        }//1040
        public function sp_set_cache_class($class = 'SimplePie_Cache'): bool
        {
            return $this->_sp_registry->register('Cache', $class, true);
        }//1056
        public function sp_set_locator_class($class = 'SimplePie_Locator'): bool
        {
            return $this->_sp_registry->register('Locator', $class, true);
        }//1064
        public function sp_set_parser_class($class = 'SimplePie_Parser'): bool
        {
            return $this->_sp_registry->register('Parser', $class, true);
        }//1072
        public function sp_set_file_class($class = 'SimplePie_File'): bool
        {
            return $this->_sp_registry->register('File', $class, true);
        }//1080
        public function sp_set_sanitize_class($class = 'SimplePie_Sanitize'): bool
        {
            return $this->_sp_registry->register('Sanitize', $class, true);
        }//1088
        public function sp_set_item_class($class = 'SimplePie_Item'): bool
        {
            return $this->_sp_registry->register('Item', $class, true);
        }//1096
        public function sp_set_author_class($class = 'SimplePie_Author'): bool
        {
            return $this->_sp_registry->register('Author', $class, true);
        }//1102
        public function sp_set_category_class($class = 'SimplePie_Category'): bool
        {
            return $this->_sp_registry->register('Category', $class, true);
        }//1112
        public function sp_set_enclosure_class($class = 'SimplePie_Enclosure'): bool
        {
            return $this->_sp_registry->register('Enclosure', $class, true);
        }//1120
        public function sp_set_caption_class($class = 'SimplePie_Caption'): bool
        {
            return $this->_sp_registry->register('Caption', $class, true);
        }//1128
        public function sp_set_copyright_class($class = 'SimplePie_Copyright'): bool
        {
            return $this->_sp_registry->register('Copyright', $class, true);
        }//1136
        public function sp_set_credit_class($class = 'SimplePie_Credit'): bool
        {
            return $this->_sp_registry->register('Credit', $class, true);
        }//1144
        public function sp_set_rating_class($class = 'SimplePie_Rating'): bool
        {
            return $this->_sp_registry->register('Rating', $class, true);
        }//1152
        public function sp_set_restriction_class($class = 'SimplePie_Restriction'): bool
        {
            return $this->_sp_registry->register('Restriction', $class, true);
        }//1160
        public function sp_set_content_type_sniffer_class($class = 'SimplePie_Content_Type_Sniffer'): bool
        {
            return $this->_sp_registry->register('Content_Type_Sniffer', $class, true);
        }//1168
        public function sp_set_source_class($class = 'SimplePie_Source'): bool
        {
            return $this->_sp_registry->register('Source', $class, true);
        }//1176
        public function sp_set_useragent($ua = SP_USERAGENT): void
        {
            $this->__sp_useragent = (string) $ua;
            return null;
        }//1187
        public function sp_set_cache_name_function($function = 'md5'): void
        {
            if (is_callable($function)) $this->__sp_cache_name_function = $function;
            return null;
        }//1198
        public function sp_set_stupidly_fast($set = false): void
        {
            if ($set){
                $this->sp_enable_order_by_date(false);
                $this->sp_set_remove_div(false);
                $this->sp_strip_comments(false);
                $this->sp_set_strip_html_tags(false);
                $this->sp_set_strip_attributes(false);
                $this->sp_set_add_attributes(false);
                $this->sp_set_image_handler(false);
                $this->sp_set_https_domains(array());
            }
            return null;
        }//1213
        public function sp_set_max_checked_feeds($max = 10): void
        {
            $this->__sp_max_checked_feeds = (int) $max;
            return null;
        }//1233
        public function sp_set_remove_div($enable = true): void
        {
            $this->_sp_sanitize->remove_div($enable);
            return null;
        }//1238
        public function sp_set_strip_html_tags($tags = '', $encode = null): mixed
        {
            if ($tags === '') $tags = implode(' ', $this->__sp_strip_html_tags);
            $this->_sp_sanitize->strip_html_tags($tags);
            if ($encode !== null) $this->_sp_sanitize->encode_instead_of_strip($tags);
            return null;
        }//1243
        public function sp_set_encode_instead_of_strip($enable = true): void
        {
            $this->_sp_sanitize->encode_instead_of_strip($enable);
            return null;
        }//1256
        public function sp_set_strip_attributes($atts = ''): void
        {
            if ($atts === '') $atts = implode(' ',$this->__sp_strip_attributes);
            $this->_sp_sanitize->strip_attributes($atts);
            return null;
        }//1261
        public function sp_set_add_attributes($atts = ''): void
        {
            if ($atts === '') $atts = implode(' ',$this->__sp_add_attributes);
            $this->_sp_sanitize->add_attributes($atts);
            return null;
        }//1270
        public function sp_set_output_encoding($encoding = 'UTF-8'): void
        {
            $this->_sp_sanitize->set_output_encoding($encoding);
            return null;
        }//1301
        public function sp_set_strip_comments($strip = false): void
        {
            $this->_sp_sanitize->strip_comments($strip);
            return null;
        }//1306
        public function sp_set_url_replacements($element_attribute = null): void
        {
            $this->_sp_sanitize->set_url_replacements($element_attribute);
            return null;
        }//1322
        public function sp_set_https_domains($domains = []): void
        {
            if (is_array($domains)) $this->_sp_sanitize->set_https_domains($domains);
            return null;
        }//1332
        public function sp_set_image_handler($page = false, $qs = 'i'): void
        {
            if ($page !== false) $this->_sp_sanitize->set_image_handler($page . '?' . $qs . '=');
            else $this->__sp_image_handler = '';
            return null;
        }//1346
        public function sp_set_item_limit($limit = 0): void
        {
            $this->__sp_item_limit = (int) $limit;
            return null;
        }//1363
        public function sp_enable_exceptions($enable = true): void
        {
            $this->__sp_enable_exceptions = $enable;
            return null;
        }//1373
        public function sp_init(): bool
        {
            if (!extension_loaded('xml') || !extension_loaded('pcre')){
                $this->__sp_error = 'XML or PCRE extensions not loaded!';
                return false;
            }
            if (!extension_loaded('xmlreader')){
                static $xml_is_sane = null;
                if ($xml_is_sane === null){
                    $parser_check = xml_parser_create();
                    xml_parse_into_struct($parser_check, '<foo>&amp;</foo>', $values);
                    xml_parser_free($parser_check);
                    $xml_is_sane = isset($values[0]['value']);
                }
                if (!$xml_is_sane) return false;
            }
            if ($this->_sp_registry->get_class('Sanitize') !== '_SimplePie_Sanitize')
                $this->_sp_sanitize = $this->_sp_registry->create('Sanitize');
            if (method_exists($this->__sp_sanitize, 'set_registry'))
                $this->_sp_sanitize->set_registry($this->sp_registry);
            $this->_sp_sanitize->pass_cache_data($this->__sp_cache, $this->__sp_cache_location, $this->__sp_cache_name_function, $this->_sp_registry->get_class('Cache'));
            $this->_sp_sanitize->pass_file_data($this->_sp_registry->get_class('File'), $this->__sp_timeout, $this->__sp_useragent, $this->__sp_force_fsock_open, $this->__sp_curl_options);
            if (!empty($this->__sp_multifeed_url)){
                $i = 0;
                $success = 0;
                $this->__sp_multi_feed_objects = [];
                $this->__sp_error = [];
                foreach ($this->__sp_multi_feed_url as $url){
                    $this->__sp_multi_feed_objects[$i] = clone $this;
                    $this->__sp_multi_feed_objects[$i]->set_feed_url($url);
                    $single_success = $this->__sp_multi_feed_objects[$i]->init();
                    $success |= $single_success;
                    if (!$single_success) $this->__sp_error[$i] = $this->__sp_multi_feed_objects[$i]->error();
                    $i++;
                }
                return (bool) $success;
            } elseif ($this->__sp_feed_url === null && $this->__sp_raw_data === null) return false;
            $this->__sp_error = null;
            $this->__sp_data = [];
            $this->__sp_check_modified = false;
            $this->__sp_multi_feed_objects = [];
            $cache = false;
            if ($this->__sp_feed_url !== null){
                $parsed_feed_url = $this->_sp_registry->call('Misc', 'parse_url', array($this->__sp_feed_url));
                 if ($this->__sp_cache && $parsed_feed_url['scheme'] !== ''){
                    $filename = $this->sp_get_cache_filename($this->__sp_feed_url);
                    $cache = $this->_sp_registry->call('Cache', 'get_handler', array($this->__sp_cache_location, $filename, 'spc'));
                }
                if (($fetched = $this->_sp_fetch_data($cache)) === true) return true;
                elseif ($fetched === false) return false;
                /** @noinspection ShortListSyntaxCanBeUsedInspection */
                list($headers, $sniffed) = $fetched;
            }
            if(empty($this->__sp_raw_data)){
                $this->__sp_error = "A feed could not be found at `$this->__sp_feed_url`. Empty body.";
                $this->_sp_registry->call('Misc', 'error', array($this->__sp_error, E_USER_NOTICE, __FILE__, __LINE__));
                return false;
            }
            $encodings = [];
            if ($this->__sp_input_encoding !== false) $encodings[] = strtoupper($this->__sp_input_encoding);
            $application_types = array('application/xml', 'application/xml-dtd', 'application/xml-external-parsed-entity');
            $text_types = array('text/xml', 'text/xml-external-parsed-entity');
            if (isset($sniffed)){
                if (in_array($sniffed, $application_types, true) || (strpos($sniffed,'application/') === 0 && substr($sniffed, -4) === '+xml')){
                    if (isset($headers['content-type']) && preg_match('/;\x20?charset=([^;]*)/i', $headers['content-type'], $charset))
                        $encodings[] = strtoupper($charset[1]);
                    $encodings = array_merge($encodings, $this->_sp_registry->call('Misc', 'xml_encoding', array($this->__sp_raw_data, &$this->sp_registry)));
                    $encodings[] = 'UTF-8';
                }elseif (in_array($sniffed, $text_types, true) || (strpos($sniffed,'text/') === 0 && substr($sniffed, -4) === '+xml')){
                    if (isset($headers['content-type']) && preg_match('/;\x20?charset=([^;]*)/i', $headers['content-type'], $charset))
                        $encodings[] = strtoupper($charset[1]);
                    $encodings[] = 'US-ASCII';
                } elseif (strpos($sniffed,'text/') === 0) $encodings[] = 'UTF-8';
            }
            $encodings = array_merge($encodings, $this->_sp_registry->call('Misc', 'xml_encoding', array($this->__sp_raw_data, &$this->sp_registry)));
            $encodings[] = 'UTF-8';
            $encodings[] = 'ISO-8859-1';
            // There's no point in trying an encoding twice
            $encodings = array_unique($encodings);
            foreach ($encodings as $encoding){
                // Change the encoding to UTF-8 (as we always use UTF-8 internally)
                if ($utf8_data = $this->_sp_registry->call('Misc', 'change_encoding', array($this->__sp_raw_data, $encoding, 'UTF-8'))){
                    // Create new parser
                    $parser = $this->_sp_registry->create('Parser');
                    // If it's parsed fine
                    if ($parser->parse($utf8_data, 'UTF-8', $this->__sp_permanent_url)){
                        $this->__sp_data = $parser->get_data();
                        if (!($this->sp_get_type() & ~SP_TYPE_NONE)){
                            $this->__sp_error = "A feed could not be found at `$this->__sp_feed_url`. This does not appear to be a valid RSS or Atom feed.";
                            $this->_sp_registry->call('Misc', 'error', array($this->__sp_error, E_USER_NOTICE, __FILE__, __LINE__));
                            return false;
                        }
                        if (isset($headers)) $this->__sp_data['headers'] = $headers;
                        $this->__sp_data['build'] = SP_BUILD;
                        // Cache the file if caching is enabled
                        if ($cache && !$cache->save($this))
                            trigger_error("$this->__sp_cache_location is not writable. Make sure you've set the correct relative or absolute path, and that the location is server-writable.", E_USER_WARNING);
                        return true;
                    }
                }
            }
            if (isset($parser)){
                $this->__sp_error = $this->__sp_feed_url;
                $this->__sp_error .= sprintf(' is invalid XML, likely due to invalid characters. XML error: %s at line %d, column %d', $parser->get_error_string(), $parser->get_current_line(), $parser->get_current_column());
            }else{
                $this->__sp_error = 'The data could not be converted to UTF-8.';
                if (!class_exists('\UConverter') && !extension_loaded('mbstring') && !extension_loaded('iconv')) {
                    $this->__sp_error .= ' You MUST have either the iconv, mbstring or intl (PHP 5.5+) extension installed and enabled.';
                } else {
                    $missingExtensions = [];
                    if (!extension_loaded('iconv')) $missingExtensions[] = 'iconv';
                    if (!extension_loaded('mbstring')) $missingExtensions[] = 'mbstring';
                    if (!class_exists('\UConverter')) $missingExtensions[] = 'intl (PHP 5.5+)';
                    $this->__sp_error .= ' Try installing/enabling the ' . implode(' or ', $missingExtensions) . ' extension.';
                }
            }
            $this->_sp_registry->call('Misc', 'error', array($this->__sp_error, E_USER_NOTICE, __FILE__, __LINE__));
            return false;
        }//1387
        protected function _sp_fetch_data(&$cache): mixed
        {
            if ($cache instanceof SP_Cache_File){ //added
                // Load the Cache
                $this->__sp_data = $cache->load();
                if (!empty($this->__sp_data)){
                    // If the cache is for an outdated build of SimplePie
                    if (!isset($this->__sp_data['build']) || $this->__sp_data['build'] !== SP_BUILD){
                        $cache->unlink();
                        $this->__sp_data = [];
                    } elseif (isset($this->__sp_data['url']) && $this->__sp_data['url'] !== $this->__sp_feed_url){
                        // If we've hit a collision just rerun it with caching disabled
                        $cache = false;
                        $this->__sp_data = [];
                    } elseif (isset($this->__sp_data['feed_url'])){
                        // If we've got a non feed_url stored (if the page isn't actually a feed, or is a redirect) use that URL.
                        // If the autodiscovery cache is still valid use it.
                        if ($cache->micro_time() + $this->__sp_autodiscovery_cache_duration > time()){
                            // Do not need to do feed autodiscovery yet.
                            if ($this->__sp_data['feed_url'] !== $this->__sp_data['url']){
                                $this->sp_set_feed_url($this->__sp_data['feed_url']);
                                return $this->sp_init();
                            }
                            $cache->unlink();
                            $this->__sp_data = array();
                        }
                    }
                    elseif ($cache->micro_time() + $this->__sp_cache_duration < time()){
                        $this->__sp_check_modified = true;
                        if (isset($this->__sp_data['headers']['last-modified']) || isset($this->__sp_data['headers']['etag'])){
                            $headers = array('Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',);
                            if (isset($this->__sp_data['headers']['last-modified']))
                                $headers['if-modified-since'] = $this->__sp_data['headers']['last-modified'];
                            if (isset($this->__sp_data['headers']['etag']))
                                $headers['if-none-match'] = $this->__sp_data['headers']['etag'];
                            $file = $this->_sp_registry->create('File', array($this->__sp_feed_url, $this->__sp_timeout/10, 5, $headers, $this->__sp_useragent, $this->__sp_force_fsock_open, $this->__sp_curl_options));
                            $this->__sp_status_code = $file->status_code;
                            if ($file->success){
                                if ($file->status_code === 304){
                                    $this->__sp_raw_data = false;
                                    $cache->touch();
                                    return true;
                                }
                            }
                            else{
                                $this->__sp_check_modified = false;
                                if($this->__sp_force_cache_fallback){
                                    $cache->touch();
                                    return true;
                                }
                                unset($file);
                            }
                        }
                    }else{ // If the cache is still valid, just return true
                        $this->__sp_raw_data = false;
                        return true;
                    }
                } else{ // If the cache is empty, delete it
                    $cache->unlink();
                    $this->__sp_data = array();
                }
            }
            // If we don't already have the file (it'll only exist if we've opened it to check if the cache has been modified), open it.
            if (!isset($file)){
                if ($this->__sp_file instanceof SimplePie_File && $this->__sp_file->sp_url === $this->__sp_feed_url)
                    $file =& $this->__sp_file;
                else{
                    $headers = array('Accept' => 'application/atom+xml, application/rss+xml, application/rdf+xml;q=0.9, application/xml;q=0.8, text/xml;q=0.8, text/html;q=0.7, unknown/unknown;q=0.1, application/unknown;q=0.1, */*;q=0.1',);
                    $file = $this->_sp_registry->create('File', array($this->__sp_feed_url, $this->__sp_timeout, 5, $headers, $this->__sp_useragent, $this->__sp_force_fsock_open, $this->__sp_curl_options));
                }
            }
            $this->__sp_status_code = $file->sp_status_code;
            // If the file connection has an error, set SimplePie::error to that and quit
            if (!$file->sp_success && !($file->method & SP_FILE_SOURCE_REMOTE === 0 || ($file->status_code === 200 || ($file->status_code > 206 && $file->status_code < 300)))){
                $this->__sp_error = $file->sp_error;
                return !empty($this->__sp_data);
            }
            if (!$this->__sp_force_feed){
                // Check if the supplied URL is a feed, if it isn't, look for it.
                $locate = $this->_sp_registry->create('Locator', array(&$file, $this->__sp_timeout, $this->__sp_useragent, $this->__sp_max_checked_feeds, $this->__sp_force_fsock_open, $this->__sp_curl_options));
                if (!$locate->is_feed($file)){
                    $copyStatusCode = $file->sp_status_code;
                    $copyContentType = $file->sp_headers['content-type'];
                    try{
                        $micro_formats = false;
                        if (class_exists('DOMXpath') && function_exists('Mf2\parse')) {
                            $doc = new \DOMDocument();
                            @$doc->loadHTML($file->body);
                            $xpath = new \DOMXpath($doc);
                            $query = '//*[contains(concat(" ", @class, " "), " h-feed ") or '.
                                'contains(concat(" ", @class, " "), " h-entry ")]';
                            $result = $xpath->query($query);
                            $micro_formats = $result->length !== 0;
                        }
                        $discovered = $locate->find($this->__sp_autodiscovery, $this->__sp_all_discovered_feeds);
                        if ($micro_formats){
                            if ($hub = $locate->get_rel_link('hub')){
                                $self = $locate->get_rel_link('self');
                                $this->__store_links($file, $hub, $self);
                            }
                            if (isset($this->__sp_all_discovered_feeds)) $this->__sp_all_discovered_feeds[] = $file;
                        }
                        elseif ($discovered) {
                                $file = $discovered;
                        }else{
                            // We need to unset this so that if SimplePie::set_file() has
                            // been called that object is untouched
                            unset($file);
                            $this->__sp_error = "A feed could not be found at `$this->__sp_feed_url`; the status code is `$copyStatusCode` and content-type is `$copyContentType`";
                            $this->_sp_registry->call('Misc', 'error', array($this->__sp_error, E_USER_NOTICE, __FILE__, __LINE__));
                            return false;
                        }
                    }
                    catch (SimplePie_Exception $e){
                        // We need to unset this so that if SimplePie::set_file() has been called that object is untouched
                        unset($file);
                        // This is usually because DOMDocument doesn't exist
                        $this->__sp_error = $e->getMessage();
                        $this->_sp_registry->call('Misc', 'error', array($this->__sp_error, E_USER_NOTICE, $e->getFile(), $e->getLine()));
                        return false;
                    }
                    if ($cache){
                        $this->__sp_data = array('url' => $this->__sp_feed_url, 'feed_url' => $file->sp_url, 'build' => SP_BUILD);
                        if (!$cache->save($this)) trigger_error("$this->__sp_cache_location is not writable. Make sure you've set the correct relative or absolute path, and that the location is server-writable.", E_USER_WARNING);
                        $cache = $this->_sp_registry->call('Cache', 'get_handler', array($this->__sp_cache_location, call_user_func($this->__sp_cache_name_function, $file->url), 'spc'));
                    }
                }
                $this->__sp_feed_url = $file->url;
                $locate = null;
            }
            $this->__sp_raw_data = $file->sp_body;
            $this->__sp_permanent_url = $file->sp_permanent_url;
            $headers = $file->sp_headers;
            $sniffer = $this->_sp_registry->create('Content_Type_Sniffer', array(&$file));
            $sniffed = $sniffer->get_type();
            return array($headers, $sniffed);
        }//1609
        public function sp_errors(){
            return $this->__sp_error;
        }//1827
        public function sp_status_code(){
            return $this->__sp_status_code;
        }//1837
        public function sp_get_raw_data(){
            return $this->__sp_raw_data;
        }//1850
        public function sp_get_encoding(){
            return $this->__sp_sanitize->sp_output_encoding;
        }//1861
        public function sp_handle_content_type($mime = 'text/html'): void
        {
            if (!headers_sent()){
                $header = "Content-type: $mime;";
                if ($this->sp_get_encoding()) $header .= ' charset=' . $this->sp_get_encoding();
                else $header .= ' charset=UTF-8';
                header($header);
            }
            return null;
        }//1885
        public function sp_get_type(){
            if (!isset($this->__sp_data['type'])){
                $this->__sp_data['type'] = SP_TYPE_ALL;
                if (isset($this->__sp_data['child'][SP_NS_ATOM_10]['feed']))
                    $this->__sp_data['type'] &= SP_TYPE_ATOM_10;
                elseif (isset($this->__sp_data['child'][SP_NS_ATOM_03]['feed']))
                    $this->__sp_data['type'] &= SP_TYPE_ATOM_03;
                elseif (isset($this->__sp_data['child'][SP_NS_RDF]['RDF'])){
                    if (isset($this->__sp_data['child'][SP_NS_RDF]['RDF'][0]['child'][SP_NS_RSS_10]['channel'])
                        || isset($this->__sp_data['child'][SP_NS_RDF]['RDF'][0]['child'][SP_NS_RSS_10]['image'])
                        || isset($this->__sp_data['child'][SP_NS_RDF]['RDF'][0]['child'][SP_NS_RSS_10]['item'])
                        || isset($this->__sp_data['child'][SP_NS_RDF]['RDF'][0]['child'][SP_NS_RSS_10]['textinput']))
                        $this->__sp_data['type'] &= SP_TYPE_RSS_10;
                    if (isset($this->__sp_data['child'][SP_NS_RDF]['RDF'][0]['child'][SP_NS_RSS_090]['channel'])
                        || isset($this->__sp_data['child'][SP_NS_RDF]['RDF'][0]['child'][SP_NS_RSS_090]['image'])
                        || isset($this->__sp_data['child'][SP_NS_RDF]['RDF'][0]['child'][SP_NS_RSS_090]['item'])
                        || isset($this->__sp_data['child'][SP_NS_RDF]['RDF'][0]['child'][SP_NS_RSS_090]['textinput']))
                        $this->__sp_data['type'] &= SP_TYPE_RSS_090;
                }elseif (isset($this->__sp_data['child'][SP_NS_RSS_20]['rss'])){
                    $this->__sp_data['type'] &= SP_TYPE_RSS_ALL;
                    if (isset($this->__sp_data['child'][SP_NS_RSS_20]['rss'][0]['atts']['']['version'])){
                        switch (trim($this->__sp_data['child'][SP_NS_RSS_20]['rss'][0]['atts']['']['version'])){
                            case '0.91':
                                $this->__sp_data['type'] &= SP_TYPE_RSS_091;
                                if (isset($this->__sp_data['child'][SP_NS_RSS_20]['rss'][0]['child'][SP_NS_RSS_20]['skiphours']['hour'][0]['data']))
                                {
                                    switch (trim($this->__sp_data['child'][SP_NS_RSS_20]['rss'][0]['child'][SP_NS_RSS_20]['skiphours']['hour'][0]['data']))
                                    {
                                        case '0':
                                            $this->__sp_data['type'] &= SP_TYPE_RSS_091_NETSCAPE;
                                            break;

                                        case '24':
                                            $this->__sp_data['type'] &= SP_TYPE_RSS_091_USERLAND;
                                            break;
                                    }
                                }
                                break;

                            case '0.92':
                                $this->__sp_data['type'] &= SP_TYPE_RSS_092;
                                break;

                            case '0.93':
                                $this->__sp_data['type'] &= SP_TYPE_RSS_093;
                                break;

                            case '0.94':
                                $this->__sp_data['type'] &= SP_TYPE_RSS_094;
                                break;

                            case '2.0':
                                $this->__sp_data['type'] &= SP_TYPE_RSS_20;
                                break;
                        }
                    }
                } else $this->__sp_data['type'] = SP_TYPE_NONE;
            }
            return $this->__sp_data['type'];
        }//1982
        public function sp_subscribe_url($permanent = false){
            if ($permanent){
                if ($this->__sp_permanent_url !== null){
                    // sanitize encodes ampersands which are required when used in a url.
                    return str_replace('&amp;', '&',
                        $this->sp_sanitized($this->__sp_permanent_url,
                            SP_CONSTRUCT_IRI));
                }
            }elseif ($this->__sp_feed_url !== null){
                return str_replace('&amp;', '&',
                    $this->sp_sanitized($this->__sp_feed_url,
                        SP_CONSTRUCT_IRI));
            }
            return null;
        }//2027
        public function sp_get_feed_tags($namespace, $tag){
            $type = $this->sp_get_type();
            if (($type & SP_TYPE_ATOM_10) && (isset($this->__sp_data['child'][SP_NS_ATOM_10]['feed'][0]['child'][$namespace][$tag]))){
                return $this->__sp_data['child'][SP_NS_ATOM_10]['feed'][0]['child'][$namespace][$tag];
            }
            if (($type & SP_TYPE_ATOM_03) && (isset($this->__sp_data['child'][SP_NS_ATOM_03]['feed'][0]['child'][$namespace][$tag]))){
                return $this->__sp_data['child'][SP_NS_ATOM_03]['feed'][0]['child'][$namespace][$tag];
            }
            if (($type & SP_TYPE_RSS_RDF) && (isset($this->__sp_data['child'][SP_NS_RDF]['RDF'][0]['child'][$namespace][$tag]))){
                return $this->__sp_data['child'][SP_NS_RDF]['RDF'][0]['child'][$namespace][$tag];
            }
            if (($type & SP_TYPE_RSS_SYNDICATION) && (isset($this->__sp_data['child'][SP_NS_RSS_20]['rss'][0]['child'][$namespace][$tag]))){
                return $this->__sp_data['child'][SP_NS_RSS_20]['rss'][0]['child'][$namespace][$tag];
            }
            return null;
        }//2082
        public function sp_get_channel_tags($namespace, $tag){
            $type = $this->sp_get_type();
            if (($type & SP_TYPE_ATOM_ALL)&& ($return = $this->sp_get_feed_tags($namespace, $tag))){
                return $return;
            }
            if (($type & SP_TYPE_RSS_10) && ($channel = $this->sp_get_feed_tags(SP_NS_RSS_10, 'channel'))&& (isset($channel[0]['child'][$namespace][$tag]))){
                return $channel[0]['child'][$namespace][$tag];            }
            if (($type & SP_TYPE_RSS_090)&&($channel = $this->sp_get_feed_tags(SP_NS_RSS_090, 'channel')) && (isset($channel[0]['child'][$namespace][$tag]))){
                return $channel[0]['child'][$namespace][$tag];
            }
            if (($type & SP_TYPE_RSS_SYNDICATION) && ($channel = $this->sp_get_feed_tags(SP_NS_RSS_20, 'channel')) && (isset($channel[0]['child'][$namespace][$tag]))){
                return $channel[0]['child'][$namespace][$tag];
            }
            return null;
        }//2130
        public function sp_get_image_tags($namespace, $tag){
            $type = $this->sp_get_type();
            if (($type & SP_TYPE_RSS_10) && ($image = $this->sp_get_feed_tags(SP_NS_RSS_10, 'image')) && (isset($image[0]['child'][$namespace][$tag]))){
                return $image[0]['child'][$namespace][$tag];
            }
            if (($type & SP_TYPE_RSS_090) && ($image = $this->sp_get_feed_tags(SP_NS_RSS_090, 'image'))&&(isset($image[0]['child'][$namespace][$tag]))) {
                return $image[0]['child'][$namespace][$tag];
            }
            if ($type & SP_TYPE_RSS_SYNDICATION && ($image = $this->sp_get_channel_tags(SP_NS_RSS_20, 'image'))&& (isset($image[0]['child'][$namespace][$tag]))){
                return $image[0]['child'][$namespace][$tag];
            }
            return null;
        }//2187
        public function sp_get_base($element = []){
            if (!empty($element['xml_base_explicit']) && isset($element['xml_base']) && !($this->sp_get_type() & SP_TYPE_RSS_SYNDICATION))
                return $element['xml_base'];
            elseif ($this->sp_get_link() !== null) return $this->sp_get_link();
            return $this->sp_subscribe_url();
        }//2235
        public function sp_sanitized($data, $type, $base = ''): string{
            try{
                return $this->_sp_sanitize->sanitize($data, $type, $base);
            }
            catch (SimplePie_Exception $e){
                if (!$this->__sp_enable_exceptions){
                    $this->__sp_error = $e->getMessage();
                    $this->_sp_registry->call('Misc', 'error', array($this->__sp_error, E_USER_WARNING, $e->getFile(), $e->getLine()));
                    return '';
                }
                throw $e;
            }
        }//2259
        public function sp_get_title(): ?string
        {
            if ($return = $this->sp_get_channel_tags(SP_NS_ATOM_10, 'title'))
                return $this->sp_sanitized($return[0]['data'], $this->_sp_registry->call('Misc', 'atom_10_construct_type', array($return[0]['atts'])), $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_ATOM_03, 'title'))
                return $this->sp_sanitized($return[0]['data'], $this->_sp_registry->call('Misc', 'atom_03_construct_type', array($return[0]['atts'])), $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_RSS_10, 'title'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_RSS_090, 'title'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_RSS_20, 'title'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_DC_11, 'title'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->sp_get_channel_tags(SP_NS_DC_10, 'title'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            return null;
        }//2286
        public function sp_get_category($key = 0){
            $categories = $this->sp_get_categories();
            if (isset($categories[$key])) return $categories[$key];
            return null;
        }//2327
        public function sp_get_categories(){
            $categories = [];
            foreach ((array) $this->sp_get_channel_tags(SP_NS_ATOM_10, 'category') as $category){
                $term = null;
                $scheme = null;
                $label = null;
                if (isset($category['atts']['']['term']))
                    $term = $this->sp_sanitized($category['atts']['']['term'], SP_CONSTRUCT_TEXT);
                if (isset($category['atts']['']['scheme']))
                    $scheme = $this->sp_sanitized($category['atts']['']['scheme'], SP_CONSTRUCT_TEXT);
                if (isset($category['atts']['']['label']))
                    $label = $this->sp_sanitized($category['atts']['']['label'], SP_CONSTRUCT_TEXT);
                $categories[] = $this->_sp_registry->create('Category', array($term, $scheme, $label));
            }
            foreach ((array) $this->sp_get_channel_tags(SP_NS_RSS_20, 'category') as $category){
                $term = $this->sp_sanitized($category['data'], SP_CONSTRUCT_TEXT);
                if (isset($category['atts']['']['domain'])) $scheme = $this->sp_sanitized($category['atts']['']['domain'], SP_CONSTRUCT_TEXT);
                else $scheme = null;
                $categories[] = $this->_sp_registry->create('Category', array($term, $scheme, null));
            }
            foreach ((array) $this->sp_get_channel_tags(SP_NS_DC_11, 'subject') as $category)
                $categories[] = $this->_sp_registry->create('Category', array($this->sp_sanitized($category['data'], SP_CONSTRUCT_TEXT), null, null));
            foreach ((array) $this->sp_get_channel_tags(SP_NS_DC_10, 'subject') as $category)
                $categories[] = $this->_sp_registry->create('Category', array($this->sp_sanitized($category['data'], SP_CONSTRUCT_TEXT), null, null));
            if (!empty($categories)) return array_unique($categories);
            return null;
        }//2346
        public function sp_get_author($key = 0){
            $authors = $this->sp_get_authors();
            if (isset($authors[$key])) return $authors[$key];
            return null;
        }//2408
        public function sp_get_authors(){
            $authors = [];
            foreach ((array) $this->sp_get_channel_tags(SP_NS_ATOM_10, 'author') as $author){
                $name = null;
                $uri = null;
                $email = null;
                if (isset($author['child'][SP_NS_ATOM_10]['name'][0]['data']))
                    $name = $this->sp_sanitized($author['child'][SP_NS_ATOM_10]['name'][0]['data'], SP_CONSTRUCT_TEXT);
                if (isset($author['child'][SP_NS_ATOM_10]['uri'][0]['data']))
                    $uri = $this->sp_sanitized($author['child'][SP_NS_ATOM_10]['uri'][0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($author['child'][SP_NS_ATOM_10]['uri'][0]));
                if (isset($author['child'][SP_NS_ATOM_10]['email'][0]['data']))
                    $email = $this->sp_sanitized($author['child'][SP_NS_ATOM_10]['email'][0]['data'], SP_CONSTRUCT_TEXT);
                if ($name !== null || $email !== null || $uri !== null)
                    $authors[] = $this->_sp_registry->create('Author', array($name, $uri, $email));
            }
            if ($author = $this->sp_get_channel_tags(SP_NS_ATOM_03, 'author')){
                $name = null;
                $url = null;
                $email = null;
                if (isset($author[0]['child'][SP_NS_ATOM_03]['name'][0]['data']))
                    $name = $this->sp_sanitized($author[0]['child'][SP_NS_ATOM_03]['name'][0]['data'], SP_CONSTRUCT_TEXT);
                if (isset($author[0]['child'][SP_NS_ATOM_03]['url'][0]['data']))
                    $url = $this->sp_sanitized($author[0]['child'][SP_NS_ATOM_03]['url'][0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($author[0]['child'][SP_NS_ATOM_03]['url'][0]));
                if (isset($author[0]['child'][SP_NS_ATOM_03]['email'][0]['data']))
                    $email = $this->sp_sanitized($author[0]['child'][SP_NS_ATOM_03]['email'][0]['data'], SP_CONSTRUCT_TEXT);
                if ($name !== null || $email !== null || $url !== null)
                    $authors[] = $this->_sp_registry->create('Author', array($name, $url, $email));
            }
            foreach ((array) $this->sp_get_channel_tags(SP_NS_DC_11, 'creator') as $author)
                $authors[] = $this->_sp_registry->create('Author', array($this->sp_sanitized($author['data'], SP_CONSTRUCT_TEXT), null, null));
            foreach ((array) $this->sp_get_channel_tags(SP_NS_DC_10, 'creator') as $author)
                $authors[] = $this->_sp_registry->create('Author', array($this->sp_sanitized($author['data'], SP_CONSTRUCT_TEXT), null, null));
            foreach ((array) $this->sp_get_channel_tags(SP_NS_I_TUNES, 'author') as $author)
                $authors[] = $this->_sp_registry->create('Author', array($this->sp_sanitized($author['data'], SP_CONSTRUCT_TEXT), null, null));
            if (!empty($authors)) return array_unique($authors);
            return null;
        }//2427
        public function sp_get_contributor($key = 0){
            $contributors = $this->sp_get_contributors();
            if (isset($contributors[$key])) return $contributors[$key];
            return null;
        }//2502
        public function sp_get_contributors(){
            $contributors = [];
            foreach ((array) $this->sp_get_channel_tags(SP_NS_ATOM_10, 'contributor') as $contributor){
                $name = null;
                $uri = null;
                $email = null;
                if (isset($contributor['child'][SP_NS_ATOM_10]['name'][0]['data']))
                    $name = $this->sp_sanitized($contributor['child'][SP_NS_ATOM_10]['name'][0]['data'], SP_CONSTRUCT_TEXT);
                if (isset($contributor['child'][SP_NS_ATOM_10]['uri'][0]['data']))
                    $uri = $this->sp_sanitized($contributor['child'][SP_NS_ATOM_10]['uri'][0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($contributor['child'][SP_NS_ATOM_10]['uri'][0]));
                if (isset($contributor['child'][SP_NS_ATOM_10]['email'][0]['data']))
                    $email = $this->sp_sanitized($contributor['child'][SP_NS_ATOM_10]['email'][0]['data'], SP_CONSTRUCT_TEXT);
                if ($name !== null || $email !== null || $uri !== null)
                    $contributors[] = $this->_sp_registry->create('Author', array($name, $uri, $email));
            }
            foreach ((array) $this->sp_get_channel_tags(SP_NS_ATOM_03, 'contributor') as $contributor){
                $name = null;
                $url = null;
                $email = null;
                if (isset($contributor['child'][SP_NS_ATOM_03]['name'][0]['data']))
                    $name = $this->sp_sanitized($contributor['child'][SP_NS_ATOM_03]['name'][0]['data'], SP_CONSTRUCT_TEXT);
                if (isset($contributor['child'][SP_NS_ATOM_03]['url'][0]['data']))
                    $url = $this->sp_sanitized($contributor['child'][SP_NS_ATOM_03]['url'][0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($contributor['child'][SP_NS_ATOM_03]['url'][0]));
                if (isset($contributor['child'][SP_NS_ATOM_03]['email'][0]['data']))
                    $email = $this->sp_sanitized($contributor['child'][SP_NS_ATOM_03]['email'][0]['data'], SP_CONSTRUCT_TEXT);
                if ($name !== null || $email !== null || $url !== null)
                    $contributors[] = $this->_sp_registry->create('Author', array($name, $url, $email));
            }
            if (!empty($contributors)) return array_unique($contributors);
            return null;
        }//2521
        public function sp_get_link($key = 0, $rel = 'alternate'){
            $links = $this->sp_get_links($rel);
            if (isset($links[$key])) return $links[$key];
            return null;
        }//2585
        public function sp_get_permalink(){
            return $this->sp_get_link(0);
        }//2607
        public function sp_get_links(/** @noinspection NotOptimalRegularExpressionsInspection */ $rel = 'alternate'){//todo
            if (!isset($this->__sp_data['links'])){
                $this->__sp_data['links'] = array();
                if ($links = $this->sp_get_channel_tags(SP_NS_ATOM_10, 'link')){
                    foreach ($links as $link){
                        if (isset($link['atts']['']['href'])){
                            $link_rel = (isset($link['atts']['']['rel'])) ?? (bool) 'alternate';
                            $this->__sp_data['links'][$link_rel][] = $this->sp_sanitized($link['attribs']['']['href'], SP_CONSTRUCT_IRI, $this->sp_get_base($link));
                        }
                    }
                }
                if ($links = $this->sp_get_channel_tags(SP_NS_ATOM_03, 'link')){
                    foreach ($links as $link){
                        if (isset($link['atts']['']['href'])){
                            $link_rel = (isset($link['atts']['']['rel'])) ?? (bool) 'alternate';
                            $this->__sp_data['links'][$link_rel][] = $this->sp_sanitized($link['atts']['']['href'], SP_CONSTRUCT_IRI, $this->sp_get_base($link));
                        }
                    }
                }
                if ($links = $this->sp_get_channel_tags(SP_NS_RSS_10, 'link'))
                    $this->__sp_data['links']['alternate'][] = $this->sp_sanitized($links[0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($links[0]));
                if ($links = $this->sp_get_channel_tags(SP_NS_RSS_090, 'link'))
                    $this->__sp_data['links']['alternate'][] = $this->sp_sanitized($links[0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($links[0]));
                if ($links = $this->sp_get_channel_tags(SP_NS_RSS_20, 'link'))
                    $this->__sp_data['links']['alternate'][] = $this->sp_sanitized($links[0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($links[0]));
                $keys = array_keys($this->__sp_data['links']);
                foreach ($keys as $key){
                    if ($this->_sp_registry->call('Misc', 'is_i_segment_nz_nc', array($key))) {
                        $links_data = $this->__sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key];
                        if (isset($links_data)){
                            $this->__sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key] = array_merge($this->__sp_data['links'][$key], $this->__sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key]);
                            $this->__sp_data['links'][$key] =& $this->__sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key];
                        }else $this->__sp_data['links'][SP_IANA_LINK_RELATIONS_REGISTRY . $key] =& $this->__sp_data['links'][$key];
                    } elseif (strpos($key,SP_IANA_LINK_RELATIONS_REGISTRY) === 0)
                        $this->__sp_data['links'][substr($key, 41)] =& $this->__sp_data['links'][$key];
                    $this->__sp_data['links'][$key] = array_unique($this->__sp_data['links'][$key]);
                }
            }
            if (isset($this->__sp_data['headers']['link'])){
                $link_headers = $this->__sp_data['headers']['link'];
                if (is_string($link_headers)) $link_headers = array($link_headers);
                $matches = preg_filter('/<([^>]+)>; rel='.preg_quote($rel,'/').'/', '$1', $link_headers);
                if (!empty($matches)) return $matches;
            }
            if (isset($this->__sp_data['links'][$rel])) return $this->__sp_data['links'][$rel];
            return null;
        }//2621
        public function sp_get_all_discovered_feeds(): array
        {
            return $this->__sp_all_discovered_feeds;
        }//2705
        public function sp_get_description(): ?string
        {
            if ($return = $this->sp_get_channel_tags(SP_NS_ATOM_10, 'subtitle'))
                return $this->sp_sanitized($return[0]['data'], $this->_sp_registry->call('Misc', 'atom_10_construct_type', array($return[0]['atts'])), $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_ATOM_03, 'tagline'))
                return $this->sp_sanitized($return[0]['data'], $this->_sp_registry->call('Misc', 'atom_03_construct_type', array($return[0]['atts'])), $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_RSS_10, 'description'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_RSS_090, 'description'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_MAYBE_HTML, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_RSS_20, 'description'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_HTML, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_DC_11, 'description'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->sp_get_channel_tags(SP_NS_DC_10, 'description'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->sp_get_channel_tags(SP_NS_I_TUNES, 'summary'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_HTML, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_I_TUNES, 'subtitle'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_HTML, $this->sp_get_base($return[0]));
            return null;
        }//2719
        public function sp_get_copyright(): ?string
        {
            if ($return = $this->sp_get_channel_tags(SP_NS_ATOM_10, 'rights'))
                return $this->sp_sanitized($return[0]['data'], $this->_sp_registry->call('Misc', 'atom_10_construct_type', array($return[0]['atts'])), $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_ATOM_03, 'copyright'))
                return $this->sp_sanitized($return[0]['data'], $this->_sp_registry->call('Misc', 'atom_03_construct_type', array($return[0]['atts'])), $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_RSS_20, 'copyright'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->sp_get_channel_tags(SP_NS_DC_11, 'rights'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->sp_get_channel_tags(SP_NS_DC_10, 'rights'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            return null;
        }//2769
        public function sp_get_language(): ?string
        {
            if ($return = $this->sp_get_channel_tags(SP_NS_RSS_20, 'language'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->sp_get_channel_tags(SP_NS_DC_11, 'language'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->sp_get_channel_tags(SP_NS_DC_10, 'language'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif (isset($this->__sp_data['child'][SP_NS_ATOM_10]['feed'][0]['xml_lang']))
                return $this->sp_sanitized($this->__sp_data['child'][SP_NS_ATOM_10]['feed'][0]['xml_lang'], SP_CONSTRUCT_TEXT);
            elseif (isset($this->__sp_data['child'][SP_NS_ATOM_03]['feed'][0]['xml_lang']))
                return $this->sp_sanitized($this->__sp_data['child'][SP_NS_ATOM_03]['feed'][0]['xml_lang'], SP_CONSTRUCT_TEXT);
            elseif (isset($this->__sp_data['child'][SP_NS_RDF]['RDF'][0]['xml_lang']))
                return $this->sp_sanitized($this->__sp_data['child'][SP_NS_RDF]['RDF'][0]['xml_lang'], SP_CONSTRUCT_TEXT);
            elseif (isset($this->__sp_data['headers']['content-language']))
                return $this->sp_sanitized($this->__sp_data['headers']['content-language'], SP_CONSTRUCT_TEXT);
            return null;
        }//2803
        public function sp_get_latitude(): ?float
        {
            if ($return = $this->sp_get_channel_tags(SP_NS_W3C_BASIC_GEO, 'lat')) return (float) $return[0]['data'];
            elseif (($return = $this->sp_get_channel_tags(SP_NS_GEORSS, 'point')) && preg_match('/^((?:-)?\d+(?:\.\d+)) ((?:-)?\d+(?:\.\d+))$/', trim($return[0]['data']), $match))
                return (float) $match[1];
            return null;
        }//2849
        public function sp_get_longitude(): ?float
        {
            if ($return = $this->sp_get_channel_tags(SP_NS_W3C_BASIC_GEO, 'long'))
                return (float) $return[0]['data'];
            elseif ($return = $this->sp_get_channel_tags(SP_NS_W3C_BASIC_GEO, 'lon'))
                return (float) $return[0]['data'];
            elseif (($return = $this->sp_get_channel_tags(SP_NS_GEORSS, 'point')) && preg_match('/^((?:-)?\d+(?:\.\d +)) ((?:-)?\d+(?:\.\d +))$/', trim($return[0]['data']), $match))
                return (float) $match[2];
            return null;
        }//2876
        public function sp_get_image_title(): ?string
        {
            if ($return = $this->sp_get_image_tags(SP_NS_RSS_10, 'title'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->sp_get_image_tags(SP_NS_RSS_090, 'title'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->sp_get_image_tags(SP_NS_RSS_20, 'title'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->sp_get_image_tags(SP_NS_DC_11, 'title'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            elseif ($return = $this->sp_get_image_tags(SP_NS_DC_10, 'title'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_TEXT);
            return null;
        }//2903
        public function sp_get_image_url(): ?string
        {
            if ($return = $this->sp_get_channel_tags(SP_NS_I_TUNES, 'image'))
                return $this->sp_sanitized($return[0]['attribs']['']['href'], SP_CONSTRUCT_IRI);
            elseif ($return = $this->sp_get_channel_tags(SP_NS_ATOM_10, 'logo'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_channel_tags(SP_NS_ATOM_10, 'icon'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_image_tags(SP_NS_RSS_10, 'url'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_image_tags(SP_NS_RSS_090, 'url'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_image_tags(SP_NS_RSS_20, 'url'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($return[0]));
            return null;
        }//2940
        public function sp_get_image_link(): ?string
        {
            if ($return = $this->sp_get_image_tags(SP_NS_RSS_10, 'link'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_image_tags(SP_NS_RSS_090, 'link'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($return[0]));
            elseif ($return = $this->sp_get_image_tags(SP_NS_RSS_20, 'link'))
                return $this->sp_sanitized($return[0]['data'], SP_CONSTRUCT_IRI, $this->sp_get_base($return[0]));
            return null;
        }//2982
        public function sp_get_image_width(){
            if ($return = $this->sp_get_image_tags(SP_NS_RSS_20, 'width'))
                return round($return[0]['data']);
            elseif ($this->sp_get_type() & SP_TYPE_RSS_SYNDICATION && $this->sp_get_image_tags(SP_NS_RSS_20, 'url'))
                return 88.0;
            return null;
        }//3010
        public function sp_get_image_height(){
            if ($return = $this->sp_get_image_tags(SP_NS_RSS_20, 'height'))
                return round($return[0]['data']);
            elseif ($this->sp_get_type() & SP_TYPE_RSS_SYNDICATION && $this->sp_get_image_tags(SP_NS_RSS_20, 'url'))
                return 31.0;
            return null;
        }//3034
        public function sp_get_item_quantity($max = 0): int
        {
            $max = (int) $max;
            $qty = count($this->sp_get_items());
            if ($max === 0) return $qty;
            return ($qty > $max) ? $max : $qty;
        }//3057
        public function sp_get_item($key = 0){
            $items = $this->sp_get_items();
            if (isset($items[$key])) return $items[$key];
            return null;
        }//3081
        public function sp_get_items($start = 0, $end = 0): array
        {
            if (!isset($this->__sp_data['items'])){
                if (!empty($this->__sp_multifeed_objects)){
                    $this->__sp_data['items'] = $this->sp_merge_items($this->__sp_multifeed_objects, $start, $end, $this->__sp_item_limit);
                    if (empty($this->__sp_data['items'])) return [];
                    return $this->__sp_data['items'];
                }
                $this->__sp_data['items'] = array();
                if ($items = $this->sp_get_feed_tags(SP_NS_ATOM_10, 'entry')){
                    $keys = array_keys($items);
                    foreach ($keys as $key) $this->__sp_data['items'][] = $this->_sp_registry->create('Item', array($this, $items[$key]));
                }
                if ($items = $this->sp_get_feed_tags(SP_NS_ATOM_03, 'entry')){
                    $keys = array_keys($items);
                    foreach ($keys as $key) $this->__sp_data['items'][] = $this->_sp_registry->create('Item', array($this, $items[$key]));
                }
                if ($items = $this->sp_get_feed_tags(SP_NS_RSS_10, 'item')){
                    $keys = array_keys($items);
                    foreach ($keys as $key) $this->__sp_data['items'][] = $this->_sp_registry->create('Item', array($this, $items[$key]));
                }
                if ($items = $this->sp_get_feed_tags(SP_NS_RSS_090, 'item')){
                    $keys = array_keys($items);
                    foreach ($keys as $key) $this->__sp_data['items'][] = $this->_sp_registry->create('Item', array($this, $items[$key]));
                }
                if ($items = $this->sp_get_channel_tags(SP_NS_RSS_20, 'item')){
                    $keys = array_keys($items);
                    foreach ($keys as $key) $this->__sp_data['items'][] = $this->_sp_registry->create('Item', array($this, $items[$key]));
                }
            }
            if (empty($this->__sp_data['items'])) return array();
            if ($this->__sp_order_by_date){
                if (!isset($this->__sp_data['ordered_items'])){
                    $this->__sp_data['ordered_items'] = $this->__sp_data['items'];
                    usort($this->__sp_data['ordered_items'], array(get_class($this), 'sort_items'));
                }
                $items = $this->__sp_data['ordered_items'];
            }else $items = $this->__sp_data['items'];
            // Slice the data as desired
            if ($end === 0) return array_slice($items, $start);
            return array_slice($items, $start, $end);
        }//3105
    }
}else die;