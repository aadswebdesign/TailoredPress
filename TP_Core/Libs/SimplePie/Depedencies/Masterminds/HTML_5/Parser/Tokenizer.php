<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 11:45
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Parser;
use TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Elements;
use TP_Core\Libs\SimplePie\Factory\_mm_vars;
if(ABSPATH){
    class Tokenizer {
        public const CONFORMANT_XML = 'xml';
        public const CONFORMANT_HTML = 'html';
        use _mm_vars;
        public function __construct(Scanner $scanner,EventHandler $eventHandler, $mode = self::CONFORMANT_HTML){
            $this->_scanner = $scanner;
            $this->_events = $eventHandler;
            $this->_mode = $mode;
        }
        public function parse():bool{
            do {
                $this->_consumeData();
                // FIXME: Add infinite loop protection.
                if($this->_carry_on === null) return false; //this way?
            } while ($this->_carry_on);
        }
        public function setTextMode($text_mode, $until_tag = null):void{
            $this->_text_mode = $text_mode & (Elements::TEXT_RAW | Elements::TEXT_RCDATA);
            $this->_until_tag = $until_tag;
        }
        protected function _consumeData():bool{
            $tok = $this->_scanner->current();
            if ('&' === $tok) {
                $ref = $this->_decodeCharacterReference();
                $this->_buffer($ref);
                $tok = $this->_scanner->current();
            }
            if ('<' === $tok) {
                // Any buffered text data can go out now.
                $this->_flushBuffer();
                $tok = $this->_scanner->next();
                if ('!' === $tok) $this->_markupDeclaration();
                elseif ('/' === $tok) $this->_endTag();
                elseif ('?' === $tok)  $this->_processingInstruction();
                elseif (ctype_alpha($tok))  $this->_tagName();
                else {
                    $this->_parseError('Illegal tag opening');
                    $this->_characterData();
                }
                $tok = $this->_scanner->current();
            }
            if (false === $tok) {
                $this->_eof();
            } else {
                switch ($this->_text_mode) {
                    case Elements::TEXT_RAW:
                        $this->_rawText($tok);
                        break;
                    case Elements::TEXT_RCDATA:
                        $this->_rc_data($tok);
                        break;
                    default:
                        if ('<' === $tok || '&' === $tok) break;
                        if ("\00" === $tok) {
                            $this->_parseError('Received null character.');
                            $this->_text .= $tok;
                            $this->_scanner->consume();
                            break;
                        }
                        $this->_text .= $this->_scanner->charsUntil("<&\0");
                }
            }
            return $this->_carry_on;
        }
        protected function _characterData(){
            $tok = $this->_scanner->current();
            if (false === $tok) return false;
            switch ($this->_text_mode) {
                case Elements::TEXT_RAW:
                    return $this->_rawText($tok);
                case Elements::TEXT_RCDATA:
                    return $this->_rc_data($tok);
                default:
                    if ('<' === $tok || '&' === $tok) return false;
                    return $this->_text($tok);
            }
        }
        protected function _text($tok):bool{
            if (false === $tok) return false;
            if ("\00" === $tok) $this->_parseError('Received null character.');
            $this->_buffer($tok);
            $this->_scanner->consume();
            return true;
        }
        protected function _rawText($tok):bool{
            if (is_null($this->_until_tag)) return $this->_text($tok);
            $sequence = '</' . $this->_until_tag . '>';
            $txt = $this->_readUntilSequence($sequence);
            $this->_events->text($txt);
            $this->setTextMode(0);
            return $this->_endTag();
        }
        protected function _rc_data($tok):bool{
            if (is_null($this->_until_tag)) return $this->_text($tok);
            $sequence = '</' . $this->_until_tag;
            $txt = '';
            $caseSensitive = !Elements::isHtml5Element($this->_until_tag);
            while (false !== $tok && !('<' === $tok && ($this->_scanner->sequenceMatches($sequence, $caseSensitive)))) {
                if ('&' === $tok) {
                    $txt .= $this->_decodeCharacterReference();
                    $tok = $this->_scanner->current();
                } else {
                    $txt .= $tok;
                    $tok = $this->_scanner->next();
                }
            }
            $len = strlen($sequence);
            $this->_scanner->consume($len);
            $len += $this->_scanner->whitespace();
            if ('>' !== $this->_scanner->current())
                $this->_parseError('Unclosed RCDATA end tag');
            $this->_scanner->un_consume($len);
            $this->_events->text($txt);
            $this->setTextMode(0);
            return $this->_endTag();
        }
        protected function _eof():void{
            $this->_flushBuffer();
            $this->_events->eof();
            $this->_carry_on = false;
        }
        protected function _markupDeclaration():bool{
            $tok = $this->_scanner->next();
            if ('-' === $tok && '-' === $this->_scanner->peek()) {
                $this->_scanner->consume(2);
                return $this->_comment();
            }elseif ('D' === $tok || 'd' === $tok) return $this->_doctype();
            elseif ('[' === $tok)  return $this->_cdataSection();
            $this->_parseError('Expected <!--, <![CDATA[, or <!DOCTYPE. Got <!%s', $tok);
            $this->_bogusComment('<!');
            return true;
        }
        protected function _endTag():bool{
            if ('/' !== $this->_scanner->current())
                return false;
            $tok = $this->_scanner->next();
            if (!ctype_alpha($tok)) {
                $this->_parseError("Expected tag name, got '%s'", $tok);
                if ("\0" === $tok || false === $tok) return false;
                return $this->_bogusComment('</');
            }
            $name = $this->_scanner->charsUntil("\n\f \t>");
            $name = self::CONFORMANT_XML === $this->_mode ? $name : strtolower($name);
            $this->_scanner->whitespace();
            $tok = $this->_scanner->current();
            if ('>' !== $tok) {
                $this->_parseError("Expected >, got '%s'", $tok);
                $this->_scanner->charsUntil('>');
            }
            $this->_events->endTag($name);
            $this->_scanner->consume();
            return true;
        }
        protected function _tagName():bool{
            $name = $this->_scanner->charsWhile(':_-0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
            $name = self::CONFORMANT_XML === $this->_mode ? $name : strtolower($name);
            $attributes = array();
            $selfClose = false;
            try {
                do {
                    $this->_scanner->whitespace();
                    $this->_attribute($attributes);
                } while (!$this->_isTagEnd($selfClose));
            } catch (ParseError $e) {
                $selfClose = false;
            }
            $mode = $this->_events->startTag($name, $attributes, $selfClose);
            if (is_int($mode)) $this->setTextMode($mode, $name);
            $this->_scanner->consume();
            return true;
        }
        protected function _isTagEnd(&$self_close):bool{
            $tok = $this->_scanner->current();
            if ('/' === $tok) {
                $this->_scanner->consume();
                $this->_scanner->whitespace();
                $tok = $this->_scanner->current();
                if ('>' === $tok) {
                    $self_close = true;
                    return true;
                }
                if (false === $tok) {
                    $this->_parseError('Unexpected EOF inside of tag.');
                    return true;
                }
                $this->_parseError("Unexpected '%s' inside of a tag.", $tok);
                return false;
            }
            if ('>' === $tok) return true;
            if (false === $tok) {
                $this->_parseError('Unexpected EOF inside of tag.');
                return true;
            }
            return false;
        }
        protected function _attribute(&$attributes):bool{
            $tok = $this->_scanner->current();
            if ('/' === $tok || '>' === $tok || false === $tok) return false;
            if ('<' === $tok) {
                $this->_parseError("Unexpected '<' inside of attributes list.");
                // Push the < back onto the stack.
                $this->_scanner->un_consume();
                throw new ParseError('Start tag inside of attribute.');
            }
            $name = strtolower($this->_scanner->charsUntil("/>=\n\f\t "));
            if ($name === '') {
                $tok = $this->_scanner->current();
                $this->_parseError('Expected an attribute name, got %s.', $tok);
                $name = $tok;
                $this->_scanner->consume();
            }
            $isValidAttribute = true;
            if (preg_match("/[\x1-\x2C\\/\x3B-\x40\x5B-\x5E\x60\x7B-\x7F]/u", $name)) {
                $this->_parseError('Unexpected characters in attribute name: %s', $name);
                $isValidAttribute = false;
            }
            elseif (preg_match('/^[0-9.-]/u', $name)) {
                $this->_parseError('Unexpected character at the begining of attribute name: %s', $name);
                $isValidAttribute = false;
            }
            $this->_scanner->whitespace();
            $val = $this->_attributeValue();
            if ($isValidAttribute) $attributes[$name] = $val;
            return true;
        }
        protected function _attributeValue(){
            if ('=' !== $this->_scanner->current()) return null;
            $this->_scanner->consume();
            $this->_scanner->whitespace();
            $tok = $this->_scanner->current();
            switch ($tok) {
                case "\n":
                case "\f":
                case ' ':
                case "\t":
                    // Whitespace here indicates an empty value.
                    return null;
                case '"':
                case "'":
                    $this->_scanner->consume();
                    return $this->_quotedAttributeValue($tok);
                case '>':
                    // case '/': // 8.2.4.37 seems to allow foo=/ as a valid attr.
                    $this->_parseError('Expected attribute value, got tag end.');
                    return null;
                case '=':
                case '`':
                    $this->_parseError('Expecting quotes, got %s.', $tok);
                    return $this->_unquotedAttributeValue();
                default:
                    return $this->_unquotedAttributeValue();
            }
        }
        protected function _quotedAttributeValue($quote):string {
            $stop_list = "\f" . $quote;
            $val = '';
            while (true) {
                $tokens = $this->_scanner->charsUntil($stop_list . '&');
                if (false !== $tokens) $val .= $tokens;
                else  break;
                $tok = $this->_scanner->current();
                if ('&' === $tok) {
                    $val .= $this->_decodeCharacterReference(true);
                    continue;
                }
                break;
            }
            $this->_scanner->consume();
            return $val;
        }
        protected function _unquotedAttributeValue():string{
            $val = '';
            $tok = $this->_scanner->current();
            while (false !== $tok) {
                switch ($tok) {
                    case "\n":
                    case "\f":
                    case ' ':
                    case "\t":
                    case '>':
                        break 2;
                    case '&':
                        $val .= $this->_decodeCharacterReference(true);
                        $tok = $this->_scanner->current();
                        break;
                    case "'":
                    case '"':
                    case '<':
                    case '=':
                    case '`':
                        $this->_parseError('Unexpected chars in unquoted attribute value %s', $tok);
                        $val .= $tok;
                        $tok = $this->_scanner->next();
                        break;
                    default:
                        $val .= $this->_scanner->charsUntil("\t\n\f >&\"'<=`");
                        $tok = $this->_scanner->current();
                }
            }
            return $val;
        }
        protected function _bogusComment($leading = ''):bool{
            $comment = $leading;
            $tokens = $this->_scanner->charsUntil('>');
            if (false !== $tokens) $comment .= $tokens;
            $tok = $this->_scanner->current();
            if (false !== $tok) $comment .= $tok;
            $this->_flushBuffer();
            $this->_events->comment($comment);
            $this->_scanner->consume();
            return true;
        }
        protected function _comment():bool{
            $tok = $this->_scanner->current();
            $comment = '';
            if ('>' === $tok) {
                $this->_parseError("Expected comment data, got '>'");
                $this->_events->comment('');
                $this->_scanner->consume();
                return true;
            }
            if ("\0" === $tok) $tok = UTF8Utils::FFFD;
            while (!$this->_isCommentEnd()) {
                $comment .= $tok;
                $tok = $this->_scanner->next();
            }
            $this->_events->comment($comment);
            $this->_scanner->consume();
            return true;
        }
        protected function _isCommentEnd():bool{
            $tok = $this->_scanner->current();
            if (false === $tok) {
                $this->_parseError('Unexpected EOF in a comment.');
                return true;
            }
            if ('-' !== $tok) return false;
            // Advance one, and test for '->'
            if ('-' === $this->_scanner->next() && '>' === $this->_scanner->peek()) {
                $this->_scanner->consume(); // Consume the last '>'
                return true;
            }
            $this->_scanner->un_consume(1);
            return false;
        }
        protected function _doctype():bool{
            if ($this->_scanner->sequenceMatches('DOCTYPE', false)) {
                $this->_scanner->consume(7);
            } else {
                $chars = $this->_scanner->charsWhile('DOCTYPEdoctype');
                $this->_parseError('Expected DOCTYPE, got %s', $chars);
                return $this->_bogusComment('<!' . $chars);
            }
            $this->_scanner->whitespace();
            $tok = $this->_scanner->current();
            if (false === $tok) {
                $this->_events->doctype('html5', EventHandler::DOCTYPE_NONE, '', true);
                $this->_eof();
                return true;
            }
            // NULL char: convert.
            if ("\0" === $tok) $this->_parseError('Unexpected null character in DOCTYPE.');
            $stop = " \n\f>";
            $doctypeName = $this->_scanner->charsUntil($stop);
            $doctypeName = strtolower(strtr($doctypeName, "\0", UTF8Utils::FFFD));
            $tok = $this->_scanner->current();
            if (false === $tok) {
                $this->_parseError('Unexpected EOF in DOCTYPE declaration.');
                $this->_events->doctype($doctypeName, EventHandler::DOCTYPE_NONE, null, true);
                return true;
            }
            if ('>' === $tok) {
                if ($doctypeName === '') {
                    $this->_parseError('Expected a DOCTYPE name. Got nothing.');
                    $this->_events->doctype($doctypeName, 0, null, true);
                    $this->_scanner->consume();
                    return true;
                }
                $this->_events->doctype($doctypeName);
                $this->_scanner->consume();
                return true;
            }
            $this->_scanner->whitespace();
            $pub = strtoupper($this->_scanner->getAsciiAlpha());
            $white = $this->_scanner->whitespace();
            if (('PUBLIC' === $pub || 'SYSTEM' === $pub) && $white > 0) {
                // Get the sys ID.
                $type = 'PUBLIC' === $pub ? EventHandler::DOCTYPE_PUBLIC : EventHandler::DOCTYPE_SYSTEM;
                $id = $this->_quotedString("\0>");
                if (false === $id) {
                    $this->_events->doctype($doctypeName, $type, $pub, false);
                    return true;
                }
                if (false === $this->_scanner->current()) {
                    $this->_parseError('Unexpected EOF in DOCTYPE');
                    $this->_events->doctype($doctypeName, $type, $id, true);
                    return true;
                }
                $this->_scanner->whitespace();
                if ('>' === $this->_scanner->current()) {
                    $this->_events->doctype($doctypeName, $type, $id, false);
                    $this->_scanner->consume();
                    return true;
                }
                $this->_scanner->charsUntil('>');
                $this->_parseError('Malformed DOCTYPE.');
                $this->_events->doctype($doctypeName, $type, $id, true);
                $this->_scanner->consume();
                return true;
            }
            $this->_scanner->charsUntil('>');
            $this->_parseError('Expected PUBLIC or SYSTEM. Got %s.', $pub);
            $this->_events->doctype($doctypeName, 0, null, true);
            $this->_scanner->consume();
            return true;
        }
        protected function _quotedString($stop_chars) {
            $tok = $this->_scanner->current();
            if ('"' === $tok || "'" === $tok) {
                $this->_scanner->consume();
                $ret = $this->_scanner->charsUntil($tok . $stop_chars);
                if ($this->_scanner->current() === $tok) {
                    $this->_scanner->consume();
                } else $this->_parseError('Expected %s, got %s', $tok, $this->_scanner->current());
                return $ret;
            }
            return false;
        }
        protected function _cdataSection():bool{
            $cdata = '';
            $this->_scanner->consume();
            $chars = $this->_scanner->charsWhile('CDAT');
            if ('CDATA' !== $chars || '[' !== $this->_scanner->current()) {
                $this->_parseError('Expected [CDATA[, got %s', $chars);
                return $this->_bogusComment('<![' . $chars);
            }
            $tok = $this->_scanner->next();
            do {
                if (false === $tok) {
                    $this->_parseError('Unexpected EOF inside CDATA.');
                    $this->_bogusComment('<![CDATA[' . $cdata);
                    return true;
                }
                $cdata .= $tok;
                $tok = $this->_scanner->next();
            } while (!$this->_scanner->sequenceMatches(']]>'));
            $this->_scanner->consume(3);
            $this->_events->cdata($cdata);
            return true;
        }
        protected function _processingInstruction():bool{
            if ('?' !== $this->_scanner->current())
                return false;
            $tok = $this->_scanner->next();
            $procName = $this->_scanner->getAsciiAlpha();
            $white = $this->_scanner->whitespace();
            if ($procName === '' || 0 === $white || false === $this->_scanner->current()) {
                $this->_parseError("Expected processing instruction name, got $tok");
                $this->_bogusComment('<?' . $tok . $procName);
                return true;
            }
            $data = '';
            // As long as it's not the case that the next two chars are ? and >.
            while (!('?' === $this->_scanner->current() && '>' === $this->_scanner->peek())) {
                $data .= $this->_scanner->current();
                $tok = $this->_scanner->next();
                if (false === $tok) {
                    $this->_parseError('Unexpected EOF in processing instruction.');
                    $this->_events->processingInstruction($procName, $data);
                    return true;
                }
            }
            $this->_scanner->consume(2); // Consume the closing tag
            $this->_events->processingInstruction($procName, $data);
            return true;
        }
        protected function _readUntilSequence($sequence):string{
            $buffer = '';
            $first = substr($sequence, 0, 1);
            while (false !== $this->_scanner->current()) {
                $buffer .= $this->_scanner->charsUntil($first);
                if ($this->_scanner->sequenceMatches($sequence, false))
                    return $buffer;
                $buffer .= $this->_scanner->current();
                $this->_scanner->consume();
            }
            $this->_parseError('Unexpected EOF during text read.');
            return $buffer;
        }
        protected function _flushBuffer():void{
            if ('' === $this->_text) return;
            $this->_events->text($this->_text);
            $this->_text = '';
        }
        protected function _buffer($str):void{
            $this->_text .= $str;
        }
        protected function _parseError($msg):bool{
            $args = func_get_args();
            if (count($args) > 1) {
                array_shift($args);
                $msg = vsprintf($msg, $args);
            }
            $line = $this->_scanner->currentLine();
            $col = $this->_scanner->columnOffset();
            $this->_events->parseError($msg, $line, $col);
            return false;
        }
        protected function _decodeCharacterReference($inAttribute = false){
            $tok = $this->_scanner->next();
            $start = $this->_scanner->position();
            if (false === $tok) return '&';
            if ("\t" === $tok || "\n" === $tok || "\f" === $tok || ' ' === $tok || '&' === $tok || '<' === $tok)
                return '&';
            if ('#' === $tok) {
                $tok = $this->_scanner->next();
                if (false === $tok) {
                    $this->_parseError('Expected &#DEC; &#HEX;, got EOF');
                    $this->_scanner->un_consume(1);
                    return '&';
                }
                if ('x' === $tok || 'X' === $tok) {
                    $tok = $this->_scanner->next(); // Consume x
                    $hex = $this->_scanner->getHex();
                    if (empty($hex)) {
                        $this->_parseError('Expected &#xHEX;, got &#x%s', $tok);
                        $this->_scanner->un_consume(2);
                        return '&';
                    }
                    $entity = CharacterReference::lookupHex($hex);
                }             // Decimal encoding.
                // [0-9]+;
                else {
                    $numeric = $this->_scanner->getNumeric();
                    if (false === $numeric) {
                        $this->_parseError('Expected &#DIGITS;, got &#%s', $tok);
                        $this->_scanner->un_consume(2);
                        return '&';
                    }
                    $entity = CharacterReference::lookupDecimal($numeric);
                }
            }elseif ('=' === $tok && $inAttribute) {
                return '&';
            } else { // String entity.
                // Attempt to consume a string up to a ';'.
                // [a-zA-Z0-9]+;
                $c_name = $this->_scanner->getAsciiAlphaNum();
                $entity = CharacterReference::lookupName($c_name);
                if (null === $entity) {
                    if (!$inAttribute || '' === $c_name)
                        $this->_parseError("No match in entity table for '%s'", $c_name);
                    $this->_scanner->un_consume($this->_scanner->position() - $start);
                    return '&';
                }
            }
            // The scanner has advanced the cursor for us.
            $tok = $this->_scanner->current();
            if (';' === $tok) {
                $this->_scanner->consume();
                return $entity;
            }
            $this->_scanner->un_consume($this->_scanner->position() - $start);
            $this->_parseError('Expected &ENTITY;, got &ENTITY%s (no trailing ;) ', $tok);
            return '&';
        }
    }
}else die;