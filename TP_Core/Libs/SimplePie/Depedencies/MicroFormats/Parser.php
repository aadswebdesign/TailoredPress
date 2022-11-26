<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-4-2022
 * Time: 20:00
 */
namespace TP_Core\Libs\SimplePie\Depedencies\MicroFormats;
use TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML5;
use TP_Core\Traits\Methods\_methods_20;
if(ABSPATH){
    class Parser{
        use _methods_20;
        use _parser_1,_parser_2;
        protected $_mf_parsed;
        protected $_mf_upgraded;
        public $mf_baseurl;
        public $mf_convert_classic;
        public $mf_doc;
        public $mf_enable_alternates = false;
        public $mf_json_mode;
        public $mf_lang = false;
        public $mf_xpath;
        public $mf_classic_root_map = [
            'v_card' => 'h-card',
            'h_feed' => 'h-feed',
            'h_entry' => 'h-entry',
            'h_recipe' => 'h-recipe',
            'h_resume' => 'h-resume',
            'v_event' => 'h-event',
            'h_review' => 'h-review',
            'h_product' => 'h-product',
            'adr' => 'h-adr',
        ];//1756
        public $mf_classic_property_map = [
            'v_card' =>[
                'fn' => ['replace' => 'p-name'],
                'honorific-prefix' => ['replace' => 'p-honorific-prefix'],
                'given-name' => ['replace' => 'p-given-name'],
                'additional-name' => ['replace' => 'p-additional-name'],
                'family-name'=>['replace' => 'p-family-name'],
                'honorific-suffix' => ['replace' => 'p-honorific-suffix'],
                'nickname' => ['replace' => 'p-nickname'],
                'email' => ['replace' => 'u-email'],
                'logo' => ['replace' => 'u-logo'],
                'photo' => ['replace' => 'u-photo'],
                'url' => ['replace' => 'u-url'],
                'uid' => ['replace' => 'u-uid'],
                'category' => ['replace' => 'p-category'],
                'adr' => ['replace' => 'p-adr'],
                'extended-address' => ['replace' => 'p-extended-address'],
                'street-address' => ['replace' => 'p-street-address'],
                'locality' => ['replace' => 'p-locality'],
                'region' => ['replace' => 'p-region'],
                'postal-code' => ['replace' => 'p-postal-code'],
                'country-name' => ['replace' => 'p-country-name'],
                'label' => ['replace' => 'p-label'],
                'geo' => ['replace' => 'p-geo h-geo','context' => 'geo'],//double
                'latitude' => ['replace' => 'p-latitude'],
                'longitude' => ['replace' => 'p-longitude'],
                'tel' => ['replace' => 'p-tel'],
                'note' => ['replace' => 'p-note'],
                'birthday' => ['replace' => 'dt-birthday'],
                'key' => ['replace' => 'u-key'],
                'organization' => ['replace' => 'p-organization'],
                'organization-name' => ['replace' => 'p-organization-name'],
                'organization-unit' => ['replace' => 'p-organization-unit'],
                'job-title' => ['replace' => 'p-job-title'],
                'role' => ['replace' => 'p-role'],
                'tz' => ['replace' => 'p-tz'],
                'rev' => ['replace' => 'p-rev'],
            ],
            'h_feed' => [], //not used
            'h_entry' => [
                'entry-title' => ['replace' => 'p-name'],
                'entry-summary' => ['replace' => 'p-summary'],
                'entry-content' => ['replace' => 'e-content'],
                'published' => ['replace' => 'dt-published'],
                'updated' => ['replace' => 'dt-updated'],
                'author' => ['replace' => 'p-author h-card','context' => 'v-card',],
                'category' => ['replace' => 'p-category'],
            ],
            'h_recipe' => [//has issues
                'fn' => ['replace' => 'p-name'],
                'ingredient' => ['replace' => 'p-ingredient'],
                'yield' => ['replace' => 'p-yield'],
                'instructions' => ['replace' => 'e-instructions'],
                'duration' => ['replace' => 'dt-duration'],
                'photo' => ['replace' => 'u-photo'],
                'summary' => ['replace' => 'p-summary'],
                'author' => ['replace' => 'p-author h-card','context' => 'v-card'],
                'nutrition' => ['replace' => 'p-nutrition'],
                'category' => ['replace' => 'p-category'],
            ],
            'h_resume' => [
                'summary' => ['replace' => 'p-summary'],
                'contact' => ['replace' => 'p-contact h-card','context' => 'v-card'],
                'education' => ['replace' => 'p-education h-event','context' => 'v-event'],
                'experience' => ['replace' => 'p-experience h-event','context' => 'v-event'],
                'skill' => ['replace' => 'p-skill'],
                'affiliation' => ['replace' => 'p-affiliation h-card','context' => 'v-card'],
            ],
            'v_event' =>[
                'summary' => ['replace' => 'p-name'],
                'dtstart' => ['replace' => 'dt-start'],
                'dtend' => ['replace' => 'dt-end'],
                'duration' => ['replace' => 'dt-duration'],
                'description' => ['replace' => 'p-description'],
                'url' => ['replace' => 'u-url'],
                'category' => ['replace' => 'p-category'],
                'location' => ['replace' => 'h-card','context' => 'v-card'],
                'geo' => ['replace' => 'p-location h-geo'],
            ],
            'h_review' => [
                'summary' => ['replace' => 'p-name'],
                'item' => ['replace' => 'p-item h-item','context' => 'item'],
                'reviewer' => ['replace' => 'p-author h-card','context' => 'v-card'],
                'dt_reviewed' => ['replace' => 'pt-published'],
                'rating' => ['replace' => 'p-rating'],
                'best' => ['replace' => 'p-best'],
                'worst' => ['replace' => 'p-worst'],
                'description' => ['replace' => 'e-description'],
                'category' => ['replace' => 'p-category'],
            ],
            'h_product' => [
                'fn' => ['replace' => 'p-name'],
                'photo' => ['replace' => 'u-photo'],
                'brand' => ['replace' => 'p-brand'],
                'category' => ['replace' => 'p-category'],
                'description' => ['replace' => 'p-description'],
                'identifier' => ['replace' => 'u-identifier'],
                'url' => ['replace' => 'u-url'],
                'review' => ['replace' => 'p-review h-review'],
                'price' => ['replace' => 'p-price'],
            ],
            'item' => [
                'fn' => ['replace' => 'p-name'],
                'url' => ['replace' => 'u-url'],
                'photo' => ['replace' => 'u-photo'],
            ],
            'adr' => [
                'post-office-box' => ['replace' => 'p-post-office-box'],
                'extended-address' => ['replace' => 'p-extended-address'],
                'street-address' => ['replace' => 'p-street-address'],
                'locality' => ['replace' => 'p-locality'],
                'region' => ['replace' => 'p-region'],
                'postal-code' => ['replace' => 'p-postal-code'],
                'country-name' => ['replace' => 'p-country-name'],
            ],
            'geo' => [
                'latitude' => ['replace' => 'p-latitude'],
                'longitude' => ['replace' => 'p-longitude'],
            ],
        ];
        public function __construct($input, $url = null, $jsonMode = false){
            libxml_use_internal_errors(true);
            if (is_string($input)) {
                if (class_exists('Masterminds\\HTML5')) {
                    $doc = new HTML5(array('disable_html_ns' => true));
                    $doc = $doc->loadHTML($input);
                } else {
                    $doc = new \DOMDocument();
                    @$doc->loadHTML($this->unicodeToHtmlEntities($input), \LIBXML_NOWARNING);
                }
            } elseif ($input instanceof \DOMDocument) $doc = clone $input;
            else {
                $doc = new \DOMDocument();
                @$doc->loadHTML('');
            }
            $this->mf_xpath = new \DOMXPath($doc);
            $this->mf_xpath->registerNamespace('php', 'http://php.net/xpath');
            $this->mf_xpath->registerPhpFunctions('\\Mf2\\classHasMf2RootClassname');
            $baseurl = $url;
            /** @noinspection LoopWhichDoesNotLoopInspection */
            foreach ((array)$this->mf_xpath->query('//base[@href]') as $base) {
                $baseElementUrl = $base->getAttribute('href');
                if (parse_url($baseElementUrl, PHP_URL_SCHEME) === null)
                    $baseurl = $this->__resolveUrl($url, $baseElementUrl);
                else  $baseurl = $baseElementUrl;
                break;
            }
            foreach ($this->mf_xpath->query('//template') as $templateEl)
                $templateEl->parentNode->removeChild($templateEl);
            $this->mf_baseurl = $baseurl;
            $this->mf_doc = $doc;
            $this->_mf_parsed = new \SplObjectStorage();
            $this->_mf_upgraded = new \SplObjectStorage();
            $this->mf_json_mode = $jsonMode;
        }//366
        public function textContent(\DOMElement $element, $implied=false){
            return preg_replace(
                '/(^[\t\n\f\r ]+| +(?=\n)|(?<=\n) +| +(?= )|[\t\n\f\r ]+$)/',
                '', $this->__elementToString($element, $implied)
            );
        }//484
        public function parseImg(\DOMElement $el){
            if ($el->hasAttribute('alt'))
                return ['value' => $this->resolveUrl($el->getAttribute('src')),'alt' => $el->getAttribute('alt')];
            return $el->getAttribute('src');
        }//526
        public function language(\DOMElement $el):string{
            if ($el->hasAttribute('lang'))
                return $this->unicodeTrim($el->getAttribute('lang'));
            if ($el->tagName === 'html') {
                foreach ((array)$this->mf_xpath->query('.//meta[@http-equiv]') as $node) {
                    if ($node->hasAttribute('http-equiv') && $node->hasAttribute('content') && strtolower($node->getAttribute('http-equiv')) === 'content-language')
                        return $this->unicodeTrim($node->getAttribute('content'));
                }
            } elseif ($el->parentNode instanceof \DOMElement)
                return $this->language($el->parentNode);// check the parent node
            return '';
        }//542
        public function resolveUrl($url){
            if (!is_string($url)) return $url;
            if (parse_url($url) === false) return $url;
            $url = trim($url);
            $scheme = parse_url($url, PHP_URL_SCHEME);
            if (empty($scheme) && !empty($this->mf_baseurl)) return $this->__resolveUrl($this->mf_baseurl, $url);
            else return $url;
        }//566
        public function parseValueClassTitle(\DOMElement $e, $separator = ''):?string{
            $valueClassElements = $this->mf_xpath->query('./*[contains(concat(" ", @class, " "), " value ")]', $e);
            if ($valueClassElements->length !== 0) { // Process value-class stuff
                $val = '';
                foreach ($valueClassElements as $el) $val .= $this->textContent($el) . $separator;
                return $this->unicodeTrim($val);
            }
            $valueTitleElements = $this->mf_xpath->query('./*[contains(concat(" ", @class, " "), " value-title ")]', $e);
            if ($valueTitleElements->length !== 0) {
                $val = '';
                foreach ((array)$valueTitleElements as $el) $val .= $el->getAttribute('title') . $separator;
                return $this->unicodeTrim($val);
            }
            return null;
        }//599
        public function parseP(\DOMElement $p){
            $classTitle = $this->parseValueClassTitle($p, ' ');
            if ($classTitle !== null) return $classTitle;
            $this->__resolveChildUrls($p);
            if ($p->tagName === 'img' && $p->hasAttribute('alt'))
                $pValue = $p->getAttribute('alt');
            elseif ($p->tagName === 'area' && $p->hasAttribute('alt'))
                $pValue = $p->getAttribute('alt');
            elseif (($p->tagName === 'abbr' || $p->tagName === 'link') && $p->hasAttribute('title'))
                $pValue = $p->getAttribute('title');
            elseif (in_array($p->tagName, array('data', 'input')) && $p->hasAttribute('value'))
                $pValue = $p->getAttribute('value');
            else $pValue = $this->textContent($p);
            return $pValue;
        }//635
        public function parseU(\DOMElement $u){
            if (($u->tagName === 'a' || $u->tagName === 'area' || $u->tagName === 'link') && $u->hasAttribute('href'))
                $uValue = $u->getAttribute('href');
            elseif ($u->tagName === 'img' & $u->hasAttribute('src'))
                $uValue = $this->parseImg($u);
            elseif(in_array($u->tagName, array('audio', 'video', 'source', 'iframe')) && $u->hasAttribute('src'))
                $uValue = $u->getAttribute('src');
            elseif ($u->tagName === 'video' & !$u->hasAttribute('src') && $u->hasAttribute('poster'))
                $uValue = $u->getAttribute('poster');
            elseif ($u->tagName === 'object' & $u->hasAttribute('data'))
                $uValue = $u->getAttribute('data');
            elseif (($classTitle = $this->parseValueClassTitle($u)) !== null)
                $uValue = $classTitle;
            elseif (($u->tagName === 'abbr' || $u->tagName === 'link') && $u->hasAttribute('title'))
                $uValue = $u->getAttribute('title');
            elseif (in_array($u->tagName, array('data', 'input')) && $u->hasAttribute('value'))
                $uValue = $u->getAttribute('value');
            else $uValue = $this->textContent($u);
            return $this->resolveUrl($uValue);
        }//666
        public function parseDT(\DOMElement $dt, &$dates = array(), &$impliedTimezone = null){
            $valueClassChildren = $this->mf_xpath->query('./*[contains(concat(" ", @class, " "), " value ") or contains(concat(" ", @class, " "), " value-title ")]', $dt);
            $dtValue = false;
            if ($valueClassChildren->length > 0) {
                // They’re using value-class
                $dateParts = [];
                foreach ((array)$valueClassChildren as $e) {
                    if (strpos(' ' . $e->getAttribute('class') . ' ', ' value-title ') !== false) {
                        $title = $e->getAttribute('title');
                        if (!empty($title))  $dateParts[] = $title;
                    }elseif ($e->tagName === 'img' || $e->tagName === 'area') {
                        $alt = $e->getAttribute('alt');
                        if (!empty($alt)) $dateParts[] = $alt;
                    }elseif ($e->tagName === 'data') {
                        $value = $e->hasAttribute('value') ? $e->getAttribute('value') : $this->unicodeTrim($e->nodeValue);
                        if (!empty($value)) $dateParts[] = $value;
                    }elseif ($e->tagName === 'abbr') {
                        // Use @title, otherwise innertext
                        $title = $e->hasAttribute('title') ? $e->getAttribute('title') : $this->unicodeTrim($e->nodeValue);
                        if (!empty($title)) $dateParts[] = $title;
                    }elseif ($e->tagName === 'del' || $e->tagName === 'ins' || $e->tagName === 'time') {
                        // Use @datetime if available, otherwise innertext
                        $dtAttr = ($e->hasAttribute('datetime')) ? $e->getAttribute('datetime') : $this->unicodeTrim($e->nodeValue);
                        if (!empty($dtAttr)) $dateParts[] = $dtAttr;
                    }else if (!empty($e->nodeValue))  $dateParts[] = $this->unicodeTrim($e->nodeValue);
                }
                $datePart = '';
                $timePart = '';
                $timezonePart = '';
                foreach ($dateParts as $part) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2})?(Z|[+-]\d{2}:?\d{2})?$/', $part)) {
                        $dtValue = $part;
                        break;
                    }
                    // Is the current part a valid time(+TZ?) AND no other time representation has been found?
                    if (empty($timePart) && (preg_match('/^\d{1,2}:\d{2}(:\d{2})?(Z|[+-]\d{1,2}:?\d{2})?$/', $part) || preg_match('/^\d{1,2}(:\d{2})?(:\d{2})?[ap]\.?m\.?$/i', $part))) {
                        $timePart = $part;
                        $timezoneOffset = $this->normalizeTimezoneOffset($timePart);
                        if (!$impliedTimezone && $timezoneOffset) $impliedTimezone = $timezoneOffset;
                        // Is the current part a valid date AND no other date representation has been found?
                    } elseif (empty($datePart) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $part)) {
                        $datePart = $part;
                        // Is the current part a valid ordinal date AND no other date representation has been found?
                    } elseif (empty($datePart) && preg_match('/^\d{4}-\d{3}$/', $part)) {
                        $datePart = $this->normalizeOrdinalDate($part);
                        // Is the current part a valid timezone offset AND no other timezone part has been found?
                    } elseif (empty($timezonePart) && preg_match('/^(Z|[+-]\d{1,2}:?(\d{2})?)$/', $part)) {
                        $timezonePart = $part;
                        $timezoneOffset = $this->normalizeTimezoneOffset($timezonePart);
                        if (!$impliedTimezone && $timezoneOffset)
                            $impliedTimezone = $timezoneOffset;
                    } else continue;
                    if ( !empty($datePart) && !in_array($datePart, $dates,true) )
                        $dates[] = $datePart;
                    if (!empty($timezonePart) && !empty($timePart))
                        $timePart .= $timezonePart;
                    if ( empty($datePart) && !empty($timePart) ) {
                        $timePart = $this->convertTimeFormat($timePart);
                        $dtValue = $this->unicodeTrim($timePart);
                    }else if ( !empty($datePart) && empty($timePart) )
                        $dtValue = rtrim($datePart, 'T');
                    else {
                        $timePart = $this->convertTimeFormat($timePart);
                        $dtValue = rtrim($datePart, 'T') . ' ' . $this->unicodeTrim($timePart);
                    }
                }
            } else {
                if ($dt->tagName === 'img' || $dt->tagName === 'area') {
                    $alt = $dt->getAttribute('alt');
                    if (!empty($alt)) $dtValue = $alt;
                } elseif ($dt->tagName === 'data') {
                    $value = $dt->getAttribute('value');
                    if (!empty($value)) $dtValue = $value;
                    else  $dtValue = $this->textContent($dt);
                } elseif ($dt->tagName === 'abbr') {
                    $title = $dt->getAttribute('title');
                    if (!empty($title)) $dtValue = $title;
                    else $dtValue = $this->textContent($dt);
                } elseif ($dt->tagName === 'del' || $dt->tagName === 'ins' || $dt->tagName === 'time') {
                    $dtAttr = $dt->getAttribute('datetime');
                    if (!empty($dtAttr)) $dtValue = $dtAttr;
                    else $dtValue = $this->textContent($dt);
                } else $dtValue = $this->textContent($dt);
                if (!preg_match('/^(\d{4}-\d{2}-\d{2})$/', $dtValue)) {
                    preg_match('/Z|[+-]\d{1,2}:?(\d{2})?$/i', $dtValue, $matches);
                    if (!$impliedTimezone && !empty($matches[0])) $impliedTimezone = $matches[0];
                }
                $dtValue = $this->unicodeTrim($dtValue);
                // Store the date part so that we can use it when assembling the final timestamp if the next one is missing a date part
                if (preg_match('/(\d{4}-\d{2}-\d{2})/', $dtValue, $matches))  $dates[] = $matches[0];
            }
            if (!empty($dates)||(preg_match('/^\d{1,2}:\d{2}(:\d{2})?(Z|[+-]\d{2}:?\d{2}?)?$/', $dtValue) || preg_match('/^\d{1,2}(:\d{2})?(:\d{2})?[ap]\.?m\.?$/i', $dtValue))) {
                $timezoneOffset = $this->normalizeTimezoneOffset($dtValue);
                if (!$impliedTimezone && $timezoneOffset) $impliedTimezone = $timezoneOffset;
                $dtValue = $this->convertTimeFormat($dtValue);
                $dtValue = end($dates) . ' ' . $this->unicodeTrim($dtValue);
            }
            return $dtValue;

        }//698
        public function parseE(\DOMElement $e){
            $html = '';
            $classTitle = $this->parseValueClassTitle($e);
            if ($classTitle !== null) return $classTitle;
            if ($innerNodes = $e->ownerDocument->createDocumentFragment()) {
                while ($e->hasChildNodes()) $innerNodes->appendChild($e->firstChild);
                $html = $e->ownerDocument->saveHTML($innerNodes);
                if ($innerNodes->hasChildNodes()) $e->appendChild($innerNodes);
            }
            $return = array('html' => $this->unicodeTrim($html), 'value' => $this->textContent($e),);
            if($this->mf_lang && $html_lang = $this->language($e)) $return['lang'] = $html_lang;
            return $return;
        }//896
        public function parseH(\DOMElement $e, $is_backcompat = false, $has_nested_mf = false):array{
            if ($this->_mf_parsed->contains($e)) return null;
            // Get current µf name
            $mfTypes = $this->mfNamesFromElement($e, 'h-');
            if (!$mfTypes) return null;
            // Initialise var to store the representation in
            $return = [];
            $children = [];
            $dates = [];
            $prefixes = [];
            $impliedTimezone = null;
            if($e->tagName === 'area') {
                $coords = $e->getAttribute('coords');
                $shape = $e->getAttribute('shape');
            }
            foreach ($this->mf_xpath->query('.//*[contains(concat(" ", @class) ," p-")]', $e) as $p) {
                if ($this->__isElementParsed($p, 'p')) continue;
                else if ( $is_backcompat && empty($this->_mf_upgraded[$p]) ) {
                    $this->__elementPrefixParsed($p, 'p');
                    continue;
                }
                $prefixes[] = 'p-';
                $pValue = $this->parseP($p);
                foreach ($this->mfNamesFromElement($p, 'p-') as $propName) {
                    if (!empty($propName)) $return[$propName][] = $pValue;
                }
                $this->__elementPrefixParsed($p, 'p');
            }
            foreach ($this->mf_xpath->query('.//*[contains(concat(" ",  @class)," u-")]', $e) as $u) {
                // element is already parsed
                if ($this->__isElementParsed($u, 'u'))continue;
                else if ( $is_backcompat && empty($this->_mf_upgraded[$u]) ) {
                    $this->__elementPrefixParsed($u, 'u');
                    continue;
                }
                $prefixes[] = 'u-';
                $uValue = $this->parseU($u);
                foreach ($this->mfNamesFromElement($u, 'u-') as $propName)
                    $return[$propName][] = $uValue;
                $this->__elementPrefixParsed($u, 'u');
            }
            $temp_dates = [];
            foreach ($this->mf_xpath->query('.//*[contains(concat(" ", @class), " dt-")]', $e) as $dt) {
                // element is already parsed
                if ($this->__isElementParsed($dt, 'dt'))continue;
                else if ( $is_backcompat && empty($this->_mf_upgraded[$dt]) ) {
                    $this->__elementPrefixParsed($dt, 'dt');
                    continue;
                }
                $prefixes[] = 'dt-';
                $dtValue = $this->parseDT($dt, $dates, $impliedTimezone);
                if ($dtValue) {
                    foreach ($this->mfNamesFromElement($dt, 'dt-') as $propName)
                        $temp_dates[$propName][] = $dtValue;
                }
                $this->__elementPrefixParsed($dt, 'dt');
            }
            foreach ($temp_dates as $propName => $data) {
                foreach ( $data as $dtValue ) {
                    if ( $impliedTimezone && preg_match('/(Z|[+-]\d{2}:?(\d{2})?)$/i', $dtValue, $matches) === 0 )
                        $dtValue .= $impliedTimezone;
                    $return[$propName][] = $dtValue;
                }
            }
            foreach ($this->mf_xpath->query('.//*[contains(concat(" ", @class)," e-")]', $e) as $em) {
                if ($this->__isElementParsed($em, 'e')) continue;
                else if ( $is_backcompat && empty($this->_mf_upgraded[$em]) ) {
                    $this->__elementPrefixParsed($em, 'e');
                    continue;
                }
                $prefixes[] = 'e-';
                $eValue = $this->parseE($em);
                if ($eValue) {
                    foreach ($this->mfNamesFromElement($em, 'e-') as $propName) $return[$propName][] = $eValue;
                }
                $this->__elementPrefixParsed($em, 'e');
            }
            if (!array_key_exists('name', $return) && empty($this->_mf_upgraded[$e]) && !$has_nested_mf && !$is_backcompat && !in_array('p-', $prefixes,true)
                && !in_array('e-', $prefixes,true)) {
                $name = false;
                if (($e->tagName === 'img' || $e->tagName === 'area') && $e->hasAttribute('alt'))
                    $name = $e->getAttribute('alt');
                elseif ($e->tagName === 'abbr' && $e->hasAttribute('title')) $name = $e->getAttribute('title');
                else {
                    $xpaths = [
                        // .h-x>img:only-child[alt]:not([alt=""]):not[.h-*]
                        './img[not(contains(concat(" ", @class), " h-")) and count(../*) = 1 and @alt and string-length(@alt) != 0]',
                        // .h-x>area:only-child[alt]:not([alt=""]):not[.h-*]
                        './area[not(contains(concat(" ", @class), " h-")) and count(../*) = 1 and @alt and string-length(@alt) != 0]',
                        // .h-x>abbr:only-child[title]:not([title=""]):not[.h-*]
                        './abbr[not(contains(concat(" ", @class), " h-")) and count(../*) = 1 and @title and string-length(@title) != 0]',
                        // .h-x>:only-child:not[.h-*]>img:only-child[alt]:not([alt=""]):not[.h-*]
                        './*[not(contains(concat(" ", @class), " h-")) and count(../*) = 1 and count(*) = 1]/img[not(contains(concat(" ", @class), " h-")) and @alt and string-length(@alt) != 0]',
                        // .h-x>:only-child:not[.h-*]>area:only-child[alt]:not([alt=""]):not[.h-*]
                        './*[not(contains(concat(" ", @class), " h-")) and count(../*) = 1 and count(*) = 1]/area[not(contains(concat(" ", @class), " h-")) and @alt and string-length(@alt) != 0]',
                        // .h-x>:only-child:not[.h-*]>abbr:only-child[title]:not([title=""]):not[.h-*]
                        './*[not(contains(concat(" ", @class), " h-")) and count(../*) = 1 and count(*) = 1]/abbr[not(contains(concat(" ", @class), " h-")) and @title and string-length(@title) != 0]'
                    ];
                    foreach ($xpaths as $xpath) {
                        $nameElement = $this->mf_xpath->query($xpath, $e);
                        if ($nameElement !== false && $nameElement->length === 1) {
                            $nameElement = $nameElement->item(0);
                            if ($nameElement->tagName === 'img' || $nameElement->tagName === 'area')
                                $name = $nameElement[$e->getAttribute('alt')];
                            else $name = $nameElement[$e->getAttribute('title')];//should be the right way
                            break;
                        }
                    }
                }
                if ($name === false) $name = $this->textContent($e, true);
                $return['name'][] = $this->unicodeTrim($name);
            }
            if (!array_key_exists('photo', $return) && !$has_nested_mf && !$is_backcompat && !in_array('u-', $prefixes,true)) {
                $photo = $this->parseImpliedPhoto($e);
                if ($photo !== false) $return['photo'][] = $photo;
            }
            if (!array_key_exists('url', $return) && !$has_nested_mf && !$is_backcompat && !in_array('u-', $prefixes,true)) {
                if (($e->tagName === 'a' || $e->tagName === 'area') && $e->hasAttribute('href')) {
                    $return['url'][] = $this->resolveUrl($e->getAttribute('href'));
                } else {
                    $xpaths = array(
                        // .h-x>a[href]:only-of-type:not[.h-*]
                        './a[not(contains(concat(" ", @class), " h-")) and count(../a) = 1 and @href]',
                        // .h-x>area[href]:only-of-type:not[.h-*]
                        './area[not(contains(concat(" ", @class), " h-")) and count(../area) = 1 and @href]',
                        // .h-x>:only-child:not[.h-*]>a[href]:only-of-type:not[.h-*]
                        './*[not(contains(concat(" ", @class), " h-")) and count(../*) = 1 and count(a) = 1]/a[not(contains(concat(" ", @class), " h-")) and @href]',
                        // .h-x>:only-child:not[.h-*]>area[href]:only-of-type:not[.h-*]
                        './*[not(contains(concat(" ", @class), " h-")) and count(../*) = 1 and count(area) = 1]/area[not(contains(concat(" ", @class), " h-")) and @href]'
                    );
                    foreach ($xpaths as $xpath) {//$e->getAttribute('title')
                        $url = $this->mf_xpath->query($xpath, $e);
                        if ($url !== false && $url->length === 1) {
                            $url = $url->item(0);
                            $return['url'][] = $this->resolveUrl($url[$e->getAttribute('href')]);
                            break;
                        }
                    }
                }
            }
            $mfTypes = array_unique($mfTypes);
            sort($mfTypes);
            if (empty($return) && $this->mf_json_mode) $return = new \stdClass();
            $parsed = array('type' => $mfTypes,'properties' => $return );
            if(trim($e->getAttribute('id')) !== '') $parsed['id'] = trim($e->getAttribute("id"));
            if($this->mf_lang && $html_lang = $this->language($e)) $parsed['lang'] = $html_lang;
            if (!empty($shape)) $parsed['shape'] = $shape;
            if (!empty($coords))  $parsed['coords'] = $coords;
            if (!empty($children)) $parsed['children'] = array_values(array_filter($children));
            return $parsed;
        }//950
        public function parseImpliedPhoto(\DOMElement $e){
            if ($e->tagName === 'img')
                return $this->resolveUrl($this->parseImg($e));
            if ($e->tagName === 'object' && $e->hasAttribute('data'))
                return $this->resolveUrl($e->getAttribute('data'));
            $xpaths = array(
                // .h-x>img[src]:only-of-type:not[.h-*]
                './img[not(contains(concat(" ", @class), " h-")) and count(../img) = 1 and @src]',
                // .h-x>object[data]:only-of-type:not[.h-*]
                './object[not(contains(concat(" ", @class), " h-")) and count(../object) = 1 and @data]',
                // .h-x>:only-child:not[.h-*]>img[src]:only-of-type:not[.h-*]
                './*[not(contains(concat(" ", @class), " h-")) and count(../*) = 1 and count(img) = 1]/img[not(contains(concat(" ", @class), " h-")) and @src]',
                // .h-x>:only-child:not[.h-*]>object[data]:only-of-type:not[.h-*]
                './*[not(contains(concat(" ", @class), " h-")) and count(../*) = 1 and count(object) = 1]/object[not(contains(concat(" ", @class), " h-")) and @data]',
            );
            foreach ($xpaths as $path) {
                $els = $this->mf_xpath->query($path, $e);
                if ($els !== false && $els->length === 1) {
                    $el = $els->item(0);
                    if ($el->tagName === 'img') {
                        $return = $this->parseImg($el);
                        return $this->resolveUrl($return);
                    }
                    if ($el->tagName === 'object')
                        return $this->resolveUrl($el[$e->getAttribute('data')]);
                }
            }
            return false;
        }//1208
        public function parseRelsAndAlternates():array{
            $rels = [];
            $rel_urls = [];
            $alternates = [];
            // Iterate through all a, area and link elements with rel attributes
            foreach ((array)$this->mf_xpath->query('//a[@rel and @href] | //link[@rel and @href] | //area[@rel and @href]') as $hyperlink) {
                // Parse the set of rels for the current link
                $linkRels = array_unique(array_filter(preg_split('/[\t\n\f\r ]/', $hyperlink->getAttribute('rel'))));
                if (count($linkRels) === 0) continue;
                $href = $this->resolveUrl($hyperlink->getAttribute('href'));
                $rel_attributes = array();
                if ($hyperlink->hasAttribute('media'))
                    $rel_attributes['media'] = $hyperlink->getAttribute('media');
                if ($hyperlink->hasAttribute('hreflang'))
                    $rel_attributes['hreflang'] = $hyperlink->getAttribute('hreflang');
                if ($hyperlink->hasAttribute('title'))
                    $rel_attributes['title'] = $hyperlink->getAttribute('title');
                if ($hyperlink->hasAttribute('type'))
                    $rel_attributes['type'] = $hyperlink->getAttribute('type');
                if ($hyperlink->textContent !== '')
                    $rel_attributes['text'] = $hyperlink->textContent;
                // If 'alternate' in rels, create 'alternates' structure, append
                if ($this->mf_enable_alternates && in_array('alternate', $linkRels, true)) {
                    $alternates[] = array_merge(
                        $rel_attributes,
                        array(
                            'url' => $href,
                            'rel' => implode(' ', array_diff($linkRels, array('alternate')))
                        )
                    );
                }
                foreach ($linkRels as $rel) {
                    if (!array_key_exists($rel, $rels)) $rels[$rel] = array($href);
                    elseif (!in_array($href, $rels[$rel], true)) $rels[$rel][] = $href;
                }
                if (!array_key_exists($href, $rel_urls))$rel_urls[$href] = array('rels' => array());
                $rel_urls[$href] = $this->_tp_array_merge($rel_attributes,$rel_urls[$href]);
                $rel_urls[$href]['rels'] = array_merge($rel_urls[$href]['rels'],$linkRels);
            }
            foreach ($rel_urls as $href => $object) {
                $rel_urls[$href]['rels'] = array_unique($rel_urls[$href]['rels']);
                sort($rel_urls[$href]['rels']);
            }
            if (empty($rels) && $this->mf_json_mode)
                $rels = new \stdClass();
            if (empty($rel_urls) && $this->mf_json_mode)
                $rel_urls = new \stdClass();
            return array($rels, $rel_urls, $alternates);
        }//1259
        public function upgradeRelTagToCategory(\DOMElement $el):void{
            $rel_tag = $this->mf_xpath->query('.//a[contains(concat(" ",normalize-space(@rel)," ")," tag ") and not(contains(concat(" ", normalize-space(@class), " "), " category ")) and @href]', $el);
            if ( $rel_tag->length ) {
                foreach ( (array)$rel_tag as $tempEl ) {
                    $path = trim(parse_url($tempEl->getAttribute('href'), PHP_URL_PATH), ' /');
                    $segments = explode('/', $path);
                    $value = array_pop($segments);
                    $dataEl = $tempEl->ownerDocument->createElement('data');
                    if($dataEl){
                        $dataEl[$tempEl->setAttribute('class', 'category')];
                        $dataEl[$tempEl->setAttribute('value', $value)];
                    }
                    $el->appendChild($dataEl);
                }
            }
        }//1359
        public function parse($convert_classic = true, \DOMElement $context = null):array{
            $this->mf_classic_root_map = $convert_classic;
            $mfs = $this->parse_recursive($context);
            @list($rels, $rel_urls, $alternates) = $this->parseRelsAndAlternates();
            $top = ['items' => array_values(array_filter($mfs)),'rels' => $rels,'rel-urls' => $rel_urls,];
            if ($this->mf_enable_alternates && count($alternates))
                $top['alternates'] = $alternates;
            return $top;
        }//1385
        public function parse_recursive(\DOMElement $context = null, $depth = 0):array{
            $mfs = array();
            $mfElements = $this->getRootMF($context);
            foreach ($mfElements as $node) {
                $is_backcompat = !$this->hasRootMf2($node);
                if ($this->mf_convert_classic && $is_backcompat)
                    $this->backcompat($node);
                $recurse = $this->parse_recursive($node, $depth + 1);
                $has_nested_mf = (bool) $recurse;
                $result = $this->parseH($node, $is_backcompat, $has_nested_mf);
                $this->__elementPrefixParsed($node, 'h');
                $this->__elementPrefixParsed($node, 'p');
                $this->__elementPrefixParsed($node, 'u');
                $this->__elementPrefixParsed($node, 'dt');
                $this->__elementPrefixParsed($node, 'e');
                if ($result) {
                    if ($recurse) $result = $this->_tp_array_merge($result, $recurse);
                    if ($depth > 0) {
                        $temp_properties = $this->nestedMfPropertyNamesFromElement($node);
                        if (!empty($temp_properties)) {
                            foreach ((array)$temp_properties as $property => $prefixes) {
                                $prefixSpecificResult = $result;
                                if (in_array('p-', $prefixes,true))
                                    $prefixSpecificResult['value'] = (!is_array($prefixSpecificResult['properties']) || empty($prefixSpecificResult['properties']['name'][0])) ? $this->parseP($node) : $prefixSpecificResult['properties']['name'][0];
                                elseif (in_array('e-', $prefixes,true)) {
                                    $eParsedResult = $this->parseE($node);
                                    $prefixSpecificResult['html'] = $eParsedResult['html'];
                                    $prefixSpecificResult['value'] = $eParsedResult['value'];
                                } elseif (in_array('u-', $prefixes,true))
                                    $prefixSpecificResult['value'] = (!is_array($result['properties']) || empty($result['properties']['url'])) ? $this->parseU($node) : reset($result['properties']['url']);
                                elseif (in_array('dt-', $prefixes,true)) {
                                    $parsed_property = $this->parseDT($node);
                                    /** @noinspection ElvisOperatorCanBeUsedInspection */
                                    $prefixSpecificResult['value'] = ($parsed_property) ? $parsed_property : '';
                                }
                                $prefixSpecificResult['value'] = is_array($prefixSpecificResult['value']) ? $prefixSpecificResult['value']['value'] : $prefixSpecificResult['value'];
                                $mfs['properties'][$property][] = $prefixSpecificResult;
                            }
                            // otherwise, set up in 'children'
                        } else  $mfs['children'][] = $result;
                    } else  $mfs[] = $result;
                }
            }
            return $mfs;
        }//1413
        public function parseFromId($id, $convert_classic=true):array{
            $matches = $this->mf_xpath->query("//*[@id='{$id}']");
            if ($matches === null) return array('items' => array(), 'rels' => array(), 'alternates' => array());
            return $this->parse($convert_classic, $matches->item(0));
        }//1504
        public function getRootMF(\DOMElement $context = null): \DOMNodeList{
            $xpaths = array('(php:function("\\Mf2\\classHasMf2RootClassname", normalize-space(@class)))');
            foreach ( $this->mf_classic_root_map as $old => $new )
                $xpaths[] = '( contains(concat(" ",normalize-space(@class), " "), " ' . $old . ' ") )';
            $xpath = '//*[' . implode(' or ', $xpaths) . ']';
            $mfElements = (null === $context)? $this->mf_xpath->query($xpath) : $this->mf_xpath->query('.' . $xpath, $context);
            return $mfElements;

        }//1518
        public function backcompat(\DOMElement $el, $context = '', $is_parent_mf2 = false):void{
            if ( $context ) $mf1_classes = array($context);
            else {
                $class = str_replace(array("\t", "\n"), ' ', $el->getAttribute('class'));
                $classes = array_filter(explode(' ', $class));
                $mf1_classes = array_intersect($classes, array_keys($this->mf_classic_root_map));
            }
            $elHasMf2 = $this->hasRootMf2($el);
            foreach ($mf1_classes as $classname) {
                switch ( $classname ){
                    case 'h_entry':
                        $this->upgradeRelTagToCategory($el);
                        $rel_bookmark = $this->mf_xpath->query('.//a[contains(concat(" ",normalize-space(@rel)," ")," bookmark ") and @href]', $el);
                        if ( $rel_bookmark->length ) {
                            foreach ( $rel_bookmark as $tempEl ) {
                                $this->addMfClasses($tempEl, 'u-url');
                                $this->addUpgraded($tempEl, array('bookmark'));
                            }
                        }
                        break;
                    case 'h_review':
                        $item_and_vcard = $this->mf_xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " item ") and contains(concat(" ", normalize-space(@class), " "), " vcard ")]', $el);
                        if ( $item_and_vcard->length ) {
                            foreach ( $item_and_vcard as $tempEl ) {
                                if ( !$this->hasRootMf2($tempEl) ) {
                                    $this->backcompat($tempEl, 'v-card');
                                    $this->addMfClasses($tempEl, 'p-item h-card');
                                    $this->addUpgraded($tempEl, array('item', 'vcard'));
                                }
                            }
                        }
                        $item_and_vevent = $this->mf_xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " item ") and contains(concat(" ", normalize-space(@class), " "), " vevent ")]', $el);
                        if ( $item_and_vevent->length ) {
                            foreach ( $item_and_vevent as $tempEl ) {
                                if ( !$this->hasRootMf2($tempEl) ) {
                                    $this->addMfClasses($tempEl, 'p-item h-event');
                                    $this->backcompat($tempEl, 'v_event');
                                    $this->addUpgraded($tempEl, array('item', 'v_event'));
                                }
                            }
                        }
                        $item_and_hproduct = $this->mf_xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " item ") and contains(concat(" ", normalize-space(@class), " "), " hproduct ")]', $el);
                        if ( $item_and_hproduct->length ) {
                            foreach ( $item_and_hproduct as $tempEl ) {
                                if ( !$this->hasRootMf2($tempEl) ) {
                                    $this->addMfClasses($tempEl, 'p-item h-product');
                                    $this->backcompat($tempEl, 'v_event');
                                    $this->addUpgraded($tempEl, array('item', 'h_product'));
                                }
                            }
                        }
                        $this->upgradeRelTagToCategory($el);
                        break;
                    case 'v_event':
                        $location = $this->mf_xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " location ")]', $el);
                        if ( $location->length ) {
                            foreach ( $location as $tempEl ) {
                                if ( !$this->hasRootMf2($tempEl) ) {
                                    $this->addMfClasses($tempEl, 'h-card');
                                    $this->backcompat($tempEl, 'v-card');
                                }
                            }
                        }
                        break;
                }
                if ( isset($this->mf_classic_property_map[$classname]) ) {
                    foreach ( $this->mf_classic_property_map[$classname] as $property => $data ) {
                        $propertyElements = $this->mf_xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " ' . $property . ' ")]', $el);
                        foreach ( $propertyElements as $propertyEl ) {
                            $hasRootMf2 = $this->hasRootMf2($propertyEl);
                            if (!$is_parent_mf2 && !$this->__isElementUpgraded($propertyEl, $property)){
                                $temp_context = $data['context'] ?? null;
                                $this->backcompat($propertyEl, $temp_context, $hasRootMf2);
                                $this->addMfClasses($propertyEl, $data['replace']);
                            }
                            $this->addUpgraded($propertyEl, $property);
                        }
                    }
                }
                if ( empty($context) && isset($this->mf_classic_property_map[$classname]) && !$elHasMf2 )
                    $this->addMfClasses($el, $this->mf_classic_property_map[$classname]);
            }
        }//1547
        public function addUpgraded(\DOMElement $el, $property):void{
            if ( !is_array($property) ) $property = array($property);
            if ( !$this->_mf_upgraded->contains($el) ) $this->_mf_upgraded->attach($el, $property);
            else $this->_mf_upgraded[$el] = array_merge($this->_mf_upgraded[$el], $property);
        }//1666
        public function addMfClasses(\DOMElement $el, $classes):void {
            $existingClasses = str_replace(array("\t", "\n"), ' ', $el->getAttribute('class'));
            $existingClasses = array_filter(explode(' ', $existingClasses));
            $addClasses = array_diff(explode(' ', $classes), $existingClasses);
            if ( $addClasses )
                $el->setAttribute('class', $el->getAttribute('class') . ' ' . implode(' ', $addClasses));
        }//1685
        public function hasRootMf2(\DOMElement $el):bool{
            $class = str_replace(array("\t", "\n"), ' ', $el->getAttribute('class'));
            return count($this->__mfNamesFromClass($class, 'h-')) > 0;
        }//1700
        public function convertLegacy():string{
            $doc = $this->mf_doc;
            $xp = new \DOMXPath($doc);
            foreach ($this->mf_classic_root_map as $old => $new) {
                foreach ((array)$xp->query('//*[contains(concat(" ", @class, " "), " ' . $old . ' ") and not(contains(concat(" ", @class, " "), " ' . $new . ' "))]') as $el)
                    $el->setAttribute('class', $el->getAttribute('class') . ' ' . $new);
            }
            foreach ($this->mf_classic_root_map as $oldRoot => $properties) {
                $newRoot = $this->mf_classic_root_map[$oldRoot];
                foreach ($properties as $old => $data) {
                    foreach ((array)$xp->query('//*[contains(concat(" ", @class, " "), " ' . $newRoot . ' ")]//*[contains(concat(" ", @class, " "), " ' . $old . ' ") and not(contains(concat(" ", @class, " "), " ' . $data['replace'] . ' "))]') as $el)
                        $el->setAttribute('class', $el->getAttribute('class') . ' ' . $data['replace']);
                }
            }
            return $this;
        }//1715
        public function query($expression, $context = null): \DOMNodeList{
            return $this->mf_xpath->query($expression, $context);
        }//1748
        private function __elementPrefixParsed(\DOMElement $e, $prefix):bool{
            if (!$this->_mf_parsed->contains($e)) return false;
            $prefixes = $this->_mf_parsed[$e];
            if (!in_array($prefix, $prefixes, true)) return false;
            return true;
        }//418
        private function __isElementParsed(\DOMElement $e, $prefix):bool{
            if (!$this->_mf_parsed->contains($e)) return false;
            $prefixes = $this->_mf_parsed[$e];
            if (!in_array($prefix, $prefixes, true)) return false;
            return true;
        }//433
        private function __isElementUpgraded(\DOMElement $el, $property):bool{
            if ($this->_mf_upgraded->contains($el))
                if (in_array($property, $this->_mf_upgraded[$el], true)) return true;
            return false;
        }//453
        private function __resolveChildUrls(\DOMElement $el):void{
            $hyperlink_children = $this->mf_xpath->query('.//*[@src or @href or @data]', $el);
            foreach ((array)$hyperlink_children as $child) {
                if ($child->hasAttribute('href'))
                    $child->setAttribute('href', $this->resolveUrl($child->getAttribute('href')));
                if ($child->hasAttribute('src'))
                    $child->setAttribute('src', $this->resolveUrl($child->getAttribute('src')));
                if ($child->hasAttribute('srcset'))
                    $child->setAttribute('srcset', $this->applySrcsetUrlTransformation($child->getAttribute('href'), array($this, 'resolveUrl')));
                if ($child->hasAttribute('data'))
                    $child->setAttribute('data', $this->resolveUrl($child->getAttribute('data')));
            }
        }//463
        private function __elementToString(\DOMElement $input, $implied = false):string{
            $output = '';
            foreach ((array)$input->childNodes as $child) {
                if ($child->nodeType === XML_TEXT_NODE)
                    $output .= str_replace(array("\t", "\n", "\r"), ' ', $child->textContent);
                else if ($child->nodeType === XML_ELEMENT_NODE) {
                    $tagName = strtoupper($child->tagName);
                    if (in_array($tagName, array('SCRIPT', 'STYLE')))
                        continue;
                    else if ($tagName === 'IMG') {
                        if ($child->hasAttribute('alt'))
                            $output .= ' ' . trim($child->getAttribute('alt'), "\t\n\f\r ") . ' ';
                        else if (!$implied && $child->hasAttribute('src'))
                            $output .= ' ' . $this->resolveUrl(trim($child->getAttribute('src'), "\t\n\f\r ")) . ' ';
                    } else if ($tagName === 'BR')
                        $output .= "\n";
                    else if ($tagName === 'P')
                        $output .= "\n" . $this->__elementToString($child);
                    else $output .= $this->__elementToString($child);
                }
            }
            return $output;
        }//492
        private function __removeTags(\DOMElement $e, $tagName):\DOMElement{
            while (($r = $e->getElementsByTagName($tagName)) && $r->length)
                $r->item(0)->parentNode->removeChild($r->item(0));
        }//936
        protected function _removeTags(\DOMElement $e, $tagName):\DOMElement{
            $this->__removeTags($e, $tagName);
        }
    }
}else die;