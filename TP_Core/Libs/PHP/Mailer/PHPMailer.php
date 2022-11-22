<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 20:45
 */
namespace TP_Core\Libs\PHP\Mailer;
use TP_Core\Libs\PHP\Mailer\Factory\FakeFunctions;
use TP_Core\Libs\PHP\Mailer\Factory\mailer_vars;
use TP_Core\Libs\PHP\Libs\Psr\Log\LoggerInterface;
if(ABSPATH){
    class PHPMailer{
        public const CHARSET_ASCII = 'us-ascii';
        public const CHARSET_ISO88591 = 'iso-8859-1';
        public const CHARSET_UTF8 = 'utf-8';
        public const CONTENT_TYPE_PLAINTEXT = 'text/plain';
        public const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
        public const CONTENT_TYPE_TEXT_HTML = 'text/html';
        public const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
        public const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
        public const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';
        public const ENCODING_7BIT = '7bit';
        public const ENCODING_8BIT = '8bit';
        public const ENCODING_BASE64 = 'base64';
        public const ENCODING_BINARY = 'binary';
        public const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';
        public const ENCRYPTION_STARTTLS = 'tls';
        public const ENCRYPTION_SMTPS = 'ssl';
        public const ICAL_METHOD_REQUEST = 'REQUEST';
        public const ICAL_METHOD_PUBLISH = 'PUBLISH';
        public const ICAL_METHOD_REPLY = 'REPLY';
        public const ICAL_METHOD_ADD = 'ADD';
        public const ICAL_METHOD_CANCEL = 'CANCEL';
        public const ICAL_METHOD_REFRESH = 'REFRESH';
        public const ICAL_METHOD_COUNTER = 'COUNTER';
        public const ICAL_METHOD_DECLINECOUNTER = 'DECLINECOUNTER';
        public const VERSION = '6.6.0';
        public const STOP_MESSAGE = 0;
        public const STOP_CONTINUE = 1;
        public const STOP_CRITICAL = 2;
        public const CRLF = "\r\n";
        public const FWS = ' ';
        public const MAIL_MAX_LINE_LENGTH = 63;
        public const MAX_LINE_LENGTH = 998;
        public const STD_LINE_LENGTH = 76;
        use mailer_vars;
        public function __construct($exceptions = null){
            if (null !== $exceptions) $this->_exceptions = (bool) $exceptions;
            $this->Debugoutput = (strpos(PHP_SAPI, 'cli') !== false ? 'echo' : 'html');
        }//826
        public function __destruct(){
            //Close any open SMTP connection nicely
            $this->smtpClose();
        }//838
        public function isHTML($isHtml = true): void
        {
            if ($isHtml) $this->ContentType = static::CONTENT_TYPE_TEXT_HTML;
            else $this->ContentType = static::CONTENT_TYPE_PLAINTEXT;
        }//947
        public function isSMTP(): void
        {
            $this->Mailer = 'smtp';
        }//959
        public function isMail(): void
        {
            $this->Mailer = 'mail';
        }//970
        public function isSendmail(): void
        {
            $ini_sendmail_path = ini_get('sendmail_path');
            if (false === stripos($ini_sendmail_path, 'sendmail'))
                $this->Sendmail = '/usr/sbin/sendmail';
            else $this->Sendmail = $ini_sendmail_path;
            $this->Mailer = 'sendmail';
        }//975
        public function isQmail(): void
        {
            $ini_sendmail_path = ini_get('sendmail_path');
            if (false === stripos($ini_sendmail_path, 'qmail'))
                $this->Sendmail = '/var/qmail/bin/qmail-inject';
            else $this->Sendmail = $ini_sendmail_path;
            $this->Mailer = 'qmail';
        }//990
        public function addAddress($address, $name = ''){
            return $this->_addOrEnqueueAnAddress('to', $address, $name);
        }//1012
        public function addCC($address, $name = ''){
            return $this->_addOrEnqueueAnAddress('cc', $address, $name);
        }//1027
        public function addBCC($address, $name = ''){
            return $this->_addOrEnqueueAnAddress('bcc', $address, $name);
        }//1042
        public function addReplyTo($address, $name = ''){
            return $this->_addOrEnqueueAnAddress('Reply-To', $address, $name);
        }//1057
        public function setFrom($address, $name = '', $auto = true): bool
        {
            $address = trim($address);
            $name = trim(preg_replace('/[\r\n]+/', '', $name));
            $pos = strrpos($address, '@');
            if ((false === $pos) || ((!$this->has8bitChars(substr($address, ++$pos)) || !static::idnSupported())
                    && !static::validateAddress($address))){
                $error_message = sprintf(
                    '%s (From): %s',
                    $this->_lang('invalid_address'),
                    $address
                );
                $this->_setError($error_message);
                $this->_emailDebug($error_message);
                if ($this->_exceptions) throw new \RuntimeException($error_message);
                return false;
            }
            $this->From = $address;
            $this->FromName = $name;
            if ($auto && empty($this->Sender))
                $this->Sender = $address;
            return true;
        }//1289
        public function getLastMessageID(): string
        {
            return $this->_lastMessageID;
        }//1330
        public function punyEncodeAddress($address): string
        {
            $pos = strrpos($address, '@');
            if (!empty($this->CharSet) && false !== $pos &&static::idnSupported()){
                $domain = substr($address, ++$pos);
                if ($this->has8bitChars($domain) && @mb_check_encoding($domain, $this->CharSet)) {
                    $domain = mb_convert_encoding($domain, self::CHARSET_UTF8, $this->CharSet);
                    $error_code = 0;
                    static $puny_code;
                    if (defined('INTL_IDNA_VARIANT_UTS46')) {
                        $puny_setup['UTS46'] = [$domain,\IDNA_DEFAULT | \IDNA_USE_STD3_RULES | \IDNA_CHECK_BIDI|\IDNA_CHECK_CONTEXTJ|\IDNA_NONTRANSITIONAL_TO_ASCII,\INTL_IDNA_VARIANT_UTS46];
                        /** @noinspection PhpParamsInspection */
                        $puny_code = FakeFunctions\idn_to_ascii($puny_setup['UTS46']) ?: idn_to_ascii($puny_setup['UTS46']);
                    }elseif (defined('INTL_IDNA_VARIANT_2003')) {
                        $puny_setup['2003']= [$domain, $error_code, \INTL_IDNA_VARIANT_2003];
                        /** @noinspection PhpParamsInspection */
                        $puny_code = FakeFunctions\idn_to_ascii($puny_setup['2003']) ?: idn_to_ascii($puny_setup['2003']);
                    }else
                        /** @noinspection PhpParamsInspection */
                        $puny_code = FakeFunctions\idn_to_ascii($domain, $error_code) ?: idn_to_ascii($domain, $error_code);
                    if (false !== $puny_code)
                        return substr($address, 0, $pos) . $puny_code;
                }
            }
            return $address;
        }//1445
        public function send(){
            try {
                if (!$this->preSend())
                    return false;
                return $this->postSend();
            } catch (\Exception $exc) {
                $this->_mailHeader = '';
                $this->_setError($exc->getMessage());
                if ($this->_exceptions) throw $exc;
                return false;
            }
        }//1493
        public function preSend(): ?bool
        {
            if ('smtp' === $this->Mailer || ('mail' === $this->Mailer && (\PHP_VERSION_ID >= 80000 || stripos(PHP_OS,'WIN')=== 0)))
                static::_setLE(self::CRLF);
            else static::_setLE(PHP_EOL);
            if ('mail' === $this->Mailer
                && ((\PHP_VERSION_ID >= 70000 && \PHP_VERSION_ID < 70017) || (\PHP_VERSION_ID >= 70100 && \PHP_VERSION_ID < 70103))
                && ini_get('mail.add_x_header') === '1'&& stripos(PHP_OS, 'WIN') === 0)
                trigger_error($this->_lang('buggy_php'), E_USER_WARNING);
            try{
                $this->_error_count = 0; //Reset errors
                $this->_mailHeader = '';
                foreach (array_merge($this->_RecipientsQueue, $this->_ReplyToQueue) as $params) {
                    $params[1] = $this->punyEncodeAddress($params[1]);
                    call_user_func_array([$this, 'addAnAddress'], $params);
                }
                if (count($this->_to) + count($this->_cc) + count($this->_bcc) < 1)
                    throw new \RuntimeException($this->_lang('provide_address'), self::STOP_CRITICAL);
                foreach (['From', 'Sender', 'ConfirmReadingTo'] as $address_kind) {
                    $this->$address_kind = trim($this->$address_kind);
                    if (empty($this->$address_kind)) continue;
                    $this->$address_kind = $this->punyEncodeAddress($this->$address_kind);
                    if (!static::validateAddress($this->$address_kind)) {
                        $error_message = sprintf('%s (%s): %s',$this->_lang('invalid_address'),$address_kind,$this->$address_kind);
                        $this->_setError($error_message);
                        $this->_emailDebug($error_message);
                        if ($this->_exceptions) throw new \RuntimeException($error_message);
                        return false;
                    }
                }
                if ($this->alternativeExists())
                    $this->ContentType = static::CONTENT_TYPE_MULTIPART_ALTERNATIVE;
                $this->_setMessageType();
                if (!$this->AllowEmpty && empty($this->Body))
                    throw new \RuntimeException($this->_lang('empty_message'), self::STOP_CRITICAL);
                $this->Subject = trim($this->Subject);
                $this->_MIMEHeader = '';
                $this->_MIMEBody = $this->createBody();
                $temp_headers = $this->_MIMEHeader;
                $this->_MIMEHeader = $this->createHeader();
                $this->_MIMEHeader .= $temp_headers;
                if ('mail' === $this->Mailer) {
                    if (count($this->_to) > 0)
                        $this->_mailHeader .= $this->addrAppend('To', $this->_to);
                    else $this->_mailHeader .= $this->headerLine('To', 'undisclosed-recipients:;');
                    $this->_mailHeader .= $this->headerLine(
                        'Subject',
                        $this->encodeHeader($this->secureHeader($this->Subject))
                    );
                }
                if (!empty($this->DKIM_domain)&& !empty($this->DKIM_selector)
                    && (!empty($this->DKIM_private_string)|| (!empty($this->DKIM_private)
                    && static::_isPermittedPath($this->DKIM_private) && file_exists($this->DKIM_private)
                    ))){
                    $header_dkim = $this->DKIM_Add(
                        $this->_MIMEHeader . $this->_mailHeader,
                        $this->encodeHeader($this->secureHeader($this->Subject)),
                        $this->_MIMEBody
                    );
                    $this->_MIMEHeader = static::stripTrailingWSP($this->_MIMEHeader) . static::$_LE .
                        static::normalizeBreaks($header_dkim) . static::$_LE;
                }
                return true;
            }catch(\Exception $exc){
                $this->_setError($exc->getMessage());
                if ($this->_exceptions) throw $exc;
                return false;
            }
        }//1519
        public function postSend(){
            $smtp = null;
            if($this->_smtp instanceof SMTP){
                $smtp = $this->_smtp;
            }
            try {
                switch ($this->Mailer) {
                    case 'sendmail':
                    case 'qmail':
                        return $this->_sendmailSend($this->_MIMEHeader, $this->_MIMEBody);
                    case 'smtp':
                        return $this->_smtpSend($this->_MIMEHeader, $this->_MIMEBody);
                    case 'mail':
                        return $this->_mailSend($this->_MIMEHeader, $this->_MIMEBody);
                    default:
                        $sendMethod = $this->Mailer . 'Send';
                        if (method_exists($this, $sendMethod))
                            return $this->$sendMethod($this->_MIMEHeader, $this->_MIMEBody);
                        return $this->_mailSend($this->_MIMEHeader, $this->_MIMEBody);
                }
            } catch (\Exception $exc) {
                if ($this->Mailer === 'smtp' && $this->SMTPKeepAlive === true)
                    $smtp->reset();
                $this->_setError($exc->getMessage());
                $this->_emailDebug($exc->getMessage());
                if ($this->_exceptions)throw $exc;
            }
            return false;
        }//1653
        public function getSMTPInstance(): SMTP
        {
            if (!is_object($this->_smtp))
                $this->_smtp = new SMTP();
            return $this->_smtp;
        }//1956
        public function setSMTPInstance(SMTP $smtp): SMTP
        {
            $this->_smtp = $smtp;
            return $this->_smtp;
        }//1970
        public function smtpConnect($options = null): bool
        {
            $smtp = null;
            if($this->_smtp instanceof SMTP){
                $smtp = $this->_smtp;
            }
            if (null === $smtp) $smtp = $this->getSMTPInstance();
            if (null === $options) $options = $this->SMTPOptions;
            if ($smtp->connected()) return true;
            $smtp->setTimeout($this->Timeout);
            $smtp->setDebugLevel($this->SMTPDebug);
            $smtp->setDebugOutput($this->Debugoutput);
            $smtp->setVer_p($this->do_verp);
            $hosts = explode(';', $this->Host);
            $last_exception = null;
            foreach ($hosts as $host_entry){
                $host_info = [];
                if (!preg_match('/^(?:(ssl|tls):\/\/)?(.+?)(?::(\d+))?$/',trim($host_entry),$host_info)){
                    $this->_emailDebug($this->_lang('invalid_hostentry') . ' ' . trim($host_entry));
                    continue;
                }
                if (!static::isValidHost($host_info[2])) {
                    $this->_emailDebug($this->_lang('invalid_host') . ' ' . $host_info[2]);
                    continue;
                }
                $prefix = '';
                $secure = $this->SMTPSecure;
                $tls = (static::ENCRYPTION_STARTTLS === $this->SMTPSecure);
                if ('ssl' === $host_info[1] || ('' === $host_info[1] && static::ENCRYPTION_SMTPS === $this->SMTPSecure)) {
                    $prefix = 'ssl://';
                    $tls = false;
                    $secure = static::ENCRYPTION_SMTPS;
                } elseif ('tls' === $host_info[1]) {
                    $tls = true;
                    $secure = static::ENCRYPTION_STARTTLS;
                }
                $ssl_ext = defined('OPENSSL_ALGO_SHA256');
                if (static::ENCRYPTION_STARTTLS === $secure || static::ENCRYPTION_SMTPS === $secure) {
                    if (!$ssl_ext) throw new \RuntimeException($this->_lang('extension_missing') . 'openssl', self::STOP_CRITICAL);
                }
                $host = $host_info[2];
                $port = $this->Port;
                if ( array_key_exists(3, $host_info) && is_numeric($host_info[3]) && $host_info[3] > 0 && $host_info[3] < 65536)
                    $port = (int) $host_info[3];
                if ($smtp->connect($prefix . $host, $port, $this->Timeout, $options)) {
                    try {
                        if ($this->Helo) $hello = $this->Helo;
                        else $hello = $this->_serverHostname();
                        $smtp->hello($hello);
                        if ($this->SMTPAutoTLS && $ssl_ext && 'ssl' !== $secure && $smtp->getServerExt('STARTTLS'))
                            $tls = true;
                        if ($tls) {
                            if (!$smtp->startTLS()) {
                                $message = $this->__getSmtpErrorMessage('connect_host');
                                throw new \RuntimeException($message);
                            }
                            $smtp->hello($hello);
                        }
                        if ($this->SMTPAuth && !$smtp->authenticate($this->Username, $this->Password,$this->AuthType,$this->_oauth))
                            throw new \RuntimeException($this->_lang('authenticate'));
                        return true;
                    }catch(\Exception $exc){
                        $last_exception = $exc;
                        $this->_emailDebug($exc->getMessage());
                        $smtp->quit();
                    }
                }
            }
            $this->_smtp->close();
            if ($this->_exceptions && null !== $last_exception)
                throw $last_exception;
            elseif ($this->_exceptions) {
                $message = $this->__getSmtpErrorMessage('connect_host');
                throw new \RuntimeException($message);
            }
            return false;
        }//2077
        public function smtpClose(): void
        {
            $smtp = null;
            if($this->_smtp instanceof SMTP){
                $smtp = $this->_smtp;
            }
            if ((null !== $this->_smtp) && $smtp->connected()){
                $smtp->quit();
                $smtp->close();
            }
        }//2215
        public function setLanguage($lang_code = 'en', $lang_path = ''): bool
        {
            $renamed_lang_codes = ['br' => 'pt_br','cz' => 'cs','dk' => 'da','no' => 'nb','se' => 'sv','rs' => 'sr','tg' => 'tl','am' => 'hy',];
            if (array_key_exists($lang_code, $renamed_lang_codes))
                $lang_code = $renamed_lang_codes[$lang_code];
            $PHP_MAILER_LANG = self::$php_mailer;
            if (empty($lang_path))  $lang_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR;
            $found_lang = true;
            $lang_code  = strtolower($lang_code);
            if ($lang_code !== 'en' && !preg_match('/^(?P<lang>[a-z]{2})(?P<script>_[a-z]{4})?(?P<country>_[a-z]{2})?$/', $lang_code, $matches)){
                $found_lang = false;
                $lang_code = 'en';
            }
            if ('en' !== $lang_code) {
                $lang_codes = [];
                if (!empty($matches['script']) && !empty($matches['country']))
                    $lang_codes[] = $matches['lang'] . $matches['script'] . $matches['country'];
                if (!empty($matches['country']))
                    $lang_codes[] = $matches['lang'] . $matches['country'];
                if (!empty($matches['script']))
                    $lang_codes[] = $matches['lang'] . $matches['script'];
                $lang_codes[] = $matches['lang'];
                $found_file = false;
                static $lang_file;
                foreach ($lang_codes as $code) {
                    $lang_file = $lang_path . 'phpmailer.lang-' . $code . '.php';
                    if (static::_fileIsAccessible($lang_file)) {
                        $found_file = true;
                        break;
                    }
                }
                if ($found_file === false) $found_lang = false;
                else {
                    $lines = file($lang_file);
                    foreach ($lines as $line) {
                        $matches = [];
                        if ( preg_match(
                                '/^\$PHP_MAILER_LANG\[\'([a-z\d_]+)\'\]\s*=\s*(["\'])(.+)*?\2;/',
                                $line, $matches ) &&
                            array_key_exists($matches[1], $PHP_MAILER_LANG)
                        ) $PHP_MAILER_LANG[$matches[1]] = (string)$matches[3];
                    }
                }
            }
            $this->_language = $PHP_MAILER_LANG;
            return $found_lang;
        }//2235
        public function getTranslations(): array
        {
            if (empty($this->_language)) $this->setLanguage(); // Set the default language.
            return $this->_language;
        }//2359
        public function addrAppend($type, $addr): string
        {
            $addresses = [];
            foreach ($addr as $address)
                $addresses[] = $this->addrFormat($address);
            return $type . ': ' . implode(', ', $addresses) . static::$_LE;
        }//2379
        public function addrFormat($addr): string
        {
            if (empty($addr[1]))  return $this->secureHeader($addr[0]);
            return $this->encodeHeader($this->secureHeader($addr[1]), 'phrase') .
            ' <' . $this->secureHeader($addr[0]) . '>';
        }//2397
        public function wrapText($message, $length, $qp_mode = false){
            if ($qp_mode) $soft_break = sprintf(' =%s', static::$_LE);
            else $soft_break = static::$_LE;
            $is_utf8 = static::CHARSET_UTF8 === strtolower($this->CharSet);
            $le_len = strlen(static::$_LE);
            $crlf_len = strlen(static::$_LE);
            $message = static::normalizeBreaks($message);
            //Remove a trailing line break
            if (substr($message, $le_len) === static::$_LE)
                $message = substr($message, 0, $le_len);
            $lines = explode(static::$_LE, $message);
            $message = '';
            foreach ($lines as $line) {
                $words = explode(' ', $line);
                $buf = '';
                $first_word = true;
                foreach ($words as $word){
                    if ($qp_mode && (strlen($word) > $length)) {
                        $space_left = $length - strlen($buf) - $crlf_len;
                        if (!$first_word) {
                            if ($space_left > 20) {
                                $len = $space_left;
                                if ($is_utf8) $len = $this->utf8CharBoundary($word, $len);
                                 elseif ('=' === substr($word, $len - 1, 1)) --$len;
                                elseif ('=' === substr($word, $len - 2, 1)) $len -= 2;
                                $part = substr($word, 0, $len);
                                $word = substr($word, $len);
                                $buf .= ' ' . $part;
                                $message .= $buf . sprintf('=%s', static::$_LE);
                            } else $message .= $buf . $soft_break;
                            $buf = '';
                        }
                        while ($word !== '') {
                            if ($length <= 0) break;
                            $len = $length;
                            if ($is_utf8) $len = $this->utf8CharBoundary($word, $len);
                            elseif ('=' === substr($word[$len - 1], 1)) --$len;
                            elseif ('=' === substr($word[$len - 2], 1)) $len -= 2;
                            $part = substr($word, 0, $len);
                            $word = (string) substr($word, $len);
                            if ($word !== '') $message .= $part . sprintf('=%s', static::$_LE);
                            else $buf = $part;
                        }
                    }else{
                        $buf_o = $buf;
                        if (!$first_word) $buf .= ' ';
                        $buf .= $word;
                        if ('' !== $buf_o && strlen($buf) > $length) {
                            $message .= $buf_o . $soft_break;
                            $buf = $word;
                        }
                    }
                    $first_word = false;
                }
                $message .= $buf . static::$_LE;
            }
            return $message;
        }//2419
        public function utf8CharBoundary($encodedText, $maxLength): int
        {
            $foundSplitPos = false;
            $lookBack = 3;
            while (!$foundSplitPos) {
                $lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
                $encodedCharPos = strpos($lastChunk, '=');
                if (false !== $encodedCharPos) {
                    $hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
                    $dec = hexdec($hex);
                    if ($dec < 128) {
                        if ($encodedCharPos > 0) $maxLength -= $lookBack - $encodedCharPos;
                        $foundSplitPos = true;
                    } elseif ($dec >= 192) {
                        $maxLength -= $lookBack - $encodedCharPos;
                        $foundSplitPos = true;
                    } elseif ($dec < 192) $lookBack += 3;

                } else $foundSplitPos = true;
            }
            return $maxLength;
        }//2519
        public function setWordWrap(): void
        {
            if ($this->WordWrap < 1) return;
            switch ($this->_message_type) {
                case 'alt':
                case 'alt_inline':
                case 'alt_attach':
                case 'alt_inline_attach':
                    $this->AltBody = $this->wrapText($this->AltBody, $this->WordWrap);
                    break;
                default:
                    $this->Body = $this->wrapText($this->Body, $this->WordWrap);
                    break;
            }
        }//2563
        public function createHeader(): string
        {
            $result = '';
            $result .= $this->headerLine('Date', '' === $this->MessageDate ? self::rfcDate() : $this->MessageDate);
            if ('mail' !== $this->Mailer) {
                if ($this->SingleTo) {
                    foreach ($this->_to as $to_addr)
                        $this->_SingleToArray[] = $this->addrFormat($to_addr);
                } elseif (count($this->_to) > 0)
                    $result .= $this->addrAppend('To', $this->_to);
                elseif (count($this->_cc) === 0)
                    $result .= $this->headerLine('To', 'undisclosed-recipients:;');
            }
            $result .= $this->addrAppend('From', [[trim($this->From), $this->FromName]]);
            if (count($this->_cc) > 0)
                $result .= $this->addrAppend('Cc', $this->_cc);
            if (('sendmail' === $this->Mailer || 'qmail' === $this->Mailer || 'mail' === $this->Mailer)&& count($this->_bcc) > 0)
                $result .= $this->addrAppend('Bcc', $this->_bcc);
            if (count($this->_ReplyTo) > 0)
                $result .= $this->addrAppend('Reply-To', $this->_ReplyTo);
            if ('mail' !== $this->Mailer)
                $result .= $this->headerLine('Subject', $this->encodeHeader($this->secureHeader($this->Subject)));
            if (
                '' !== $this->MessageID &&
                preg_match(
                    '/^<((([a-z\d!#$%&\'*+\/=?^_`{|}~-]+(\.[a-z\d!#$%&\'*+\/=?^_`{|}~-]+)*)' .
                    '|("(([\x01-\x08\x0B\x0C\x0E-\x1F\x7F]|[\x21\x23-\x5B\x5D-\x7E])' .
                    '|(\\[\x01-\x09\x0B\x0C\x0E-\x7F]))*"))@(([a-z\d!#$%&\'*+\/=?^_`{|}~-]+' .
                    '(\.[a-z\d!#$%&\'*+\/=?^_`{|}~-]+)*)|(\[(([\x01-\x08\x0B\x0C\x0E-\x1F\x7F]' .
                    '|[\x21-\x5A\x5E-\x7E])|(\\[\x01-\x09\x0B\x0C\x0E-\x7F]))*\])))>$/Di',
                    $this->MessageID
                )
            ) $this->_lastMessageID = $this->MessageID;
            else  $this->_lastMessageID = sprintf('<%s@%s>', $this->_uniqueid, $this->_serverHostname());
            $result .= $this->headerLine('Message-ID', $this->_lastMessageID);
            if (null !== $this->Priority)
                $result .= $this->headerLine('X-Priority', $this->Priority);
            if ('' === $this->XMailer) {
                $result .= $this->headerLine(
                    'X-Mailer',
                    'PHPMailer ' . self::VERSION . ' (https://github.com/PHPMailer/PHPMailer)'
                );
            } elseif (is_string($this->XMailer) && trim($this->XMailer) !== '')
                $result .= $this->headerLine('X-Mailer', trim($this->XMailer));
            if ('' !== $this->ConfirmReadingTo)
                $result .= $this->headerLine('Disposition-Notification-To', '<' . $this->ConfirmReadingTo . '>');
            foreach ($this->_CustomHeader as $header) {
                $result .= $this->headerLine(
                    trim($header[0]),
                    $this->encodeHeader(trim($header[1]))
                );
            }
            if (!$this->_sign_key_file) {
                $result .= $this->headerLine('MIME-Version', '1.0');
                $result .= $this->getMailMIME();
            }
            return $result;
        }//2587
        public function getMailMIME(): string
        {
            $result = '';
            $is_multipart = true;
            switch ($this->_message_type) {
                case 'inline':
                    $result .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                    $result .= $this->textLine(' boundary="' . $this->_boundary[1] . '"');
                    break;
                case 'attach':
                case 'inline_attach':
                case 'alt_attach':
                case 'alt_inline_attach':
                    $result .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_MIXED . ';');
                    $result .= $this->textLine(' boundary="' . $this->_boundary[1] . '"');
                    break;
                case 'alt':
                case 'alt_inline':
                    $result .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                    $result .= $this->textLine(' boundary="' . $this->_boundary[1] . '"');
                    break;
                default:
                    $result .= $this->textLine('Content-Type: ' . $this->ContentType . '; charset=' . $this->CharSet);
                    $is_multipart = false;
                    break;
            }
            if (static::ENCODING_7BIT !== $this->Encoding) {
                if ($is_multipart) {
                    if (static::ENCODING_8BIT === $this->Encoding)
                        $result .= $this->headerLine('Content-Transfer-Encoding', static::ENCODING_8BIT);
                } else  $result .= $this->headerLine('Content-Transfer-Encoding', $this->Encoding);

            }
            return $result;
        }//2687
        public function getSentMIMEMessage(): string
        {
            return static::stripTrailingWSP($this->_MIMEHeader . $this->_mailHeader) .
            static::$_LE . static::$_LE . $this->_MIMEBody;
        }//2737
        public function createBody(): string
        {
            $body = '';
            $this->_uniqueid = $this->_generateId();
            $this->_boundary[1] = 'b1_' . $this->_uniqueid;
            $this->_boundary[2] = 'b2_' . $this->_uniqueid;
            $this->_boundary[3] = 'b3_' . $this->_uniqueid;
            if ($this->_sign_key_file)
                $body .= $this->getMailMIME() . static::$_LE;
            $this->setWordWrap();
            $bodyEncoding = $this->Encoding;
            $bodyCharSet = $this->CharSet;
            if (static::ENCODING_8BIT === $bodyEncoding && !$this->has8bitChars($this->Body)) {
                $bodyEncoding = static::ENCODING_7BIT;
                $bodyCharSet = static::CHARSET_ASCII;
            }
            if (static::ENCODING_BASE64 !== $this->Encoding && static::hasLineLongerThanMax($this->Body))
                $bodyEncoding = static::ENCODING_QUOTED_PRINTABLE;
            $mime_pre = 'This is a multi-part message in MIME format.' . static::$_LE . static::$_LE;
            switch ($this->_message_type) {
                case 'inline':
                    $body .= $mime_pre;
                    $body .= $this->_getBoundary($this->_boundary[1], $bodyCharSet, '', $bodyEncoding);
                    $body .= $this->encodeString($this->Body, $bodyEncoding);
                    $body .= static::$_LE;
                    $body .= $this->_attachAll('inline', $this->_boundary[1]);
                    break;
                case 'attach':
                    $body .= $mime_pre;
                    $body .= $this->_getBoundary($this->_boundary[1], $bodyCharSet, '', $bodyEncoding);
                    $body .= $this->encodeString($this->Body, $bodyEncoding);
                    $body .= static::$_LE;
                    $body .= $this->_attachAll('attachment', $this->_boundary[1]);
                    break;
                case 'inline_attach':
                    $body .= $mime_pre;
                    $body .= $this->textLine('--' . $this->_boundary[1]);
                    $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                    $body .= $this->textLine(' boundary="' . $this->_boundary[2] . '";');
                    $body .= $this->textLine(' type="' . static::CONTENT_TYPE_TEXT_HTML . '"');
                    $body .= static::$_LE;
                    $body .= $this->_getBoundary($this->_boundary[2], $bodyCharSet, '', $bodyEncoding);
                    $body .= $this->encodeString($this->Body, $bodyEncoding);
                    $body .= static::$_LE;
                    $body .= $this->_attachAll('inline', $this->_boundary[2]);
                    $body .= static::$_LE;
                    $body .= $this->_attachAll('attachment', $this->_boundary[1]);
                    break;
                case 'alt':
                    $body .= $mime_pre;
                    $body .= $this->_getBoundary(
                        $this->_boundary[1],
                        $this->_altBodyCharSet,
                        static::CONTENT_TYPE_PLAINTEXT,
                        $this->_altBodyEncoding
                    );
                    $body .= $this->encodeString($this->AltBody, $this->_altBodyEncoding);
                    $body .= static::$_LE;
                    $body .= $this->_getBoundary(
                        $this->_boundary[1],
                        $bodyCharSet,
                        static::CONTENT_TYPE_TEXT_HTML,
                        $bodyEncoding
                    );
                    $body .= $this->encodeString($this->Body, $bodyEncoding);
                    $body .= static::$_LE;
                    if (!empty($this->Ical)) {
                        $method = static::ICAL_METHOD_REQUEST;
                        foreach (static::$_IcalMethods as $i_method) {
                            if (stripos($this->Ical, 'METHOD:' . $i_method) !== false) {
                                $method = $i_method;
                                break;
                            }
                        }
                        $body .= $this->_getBoundary(
                            $this->_boundary[1],
                            '',
                            static::CONTENT_TYPE_TEXT_CALENDAR . '; method=' . $method,
                            ''
                        );
                        $body .= $this->encodeString($this->Ical, $this->Encoding);
                        $body .= static::$_LE;
                    }
                    $body .= $this->_endBoundary($this->_boundary[1]);
                    break;
                case 'alt_inline':
                    $body .= $mime_pre;
                    $body .= $this->_getBoundary(
                        $this->_boundary[1],
                        $this->_altBodyCharSet,
                        static::CONTENT_TYPE_PLAINTEXT,
                        $this->_altBodyEncoding
                    );
                    $body .= $this->encodeString($this->AltBody, $this->_altBodyEncoding);
                    $body .= static::$_LE;
                    $body .= $this->textLine('--' . $this->_boundary[1]);
                    $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                    $body .= $this->textLine(' boundary="' . $this->_boundary[2] . '";');
                    $body .= $this->textLine(' type="' . static::CONTENT_TYPE_TEXT_HTML . '"');
                    $body .= static::$_LE;
                    $body .= $this->_getBoundary(
                        $this->_boundary[2],
                        $bodyCharSet,
                        static::CONTENT_TYPE_TEXT_HTML,
                        $bodyEncoding
                    );
                    $body .= $this->encodeString($this->Body, $bodyEncoding);
                    $body .= static::$_LE;
                    $body .= $this->_attachAll('inline', $this->_boundary[2]);
                    $body .= static::$_LE;
                    $body .= $this->_endBoundary($this->_boundary[1]);
                    break;
                case 'alt_attach':
                    $body .= $mime_pre;
                    $body .= $this->textLine('--' . $this->_boundary[1]);
                    $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                    $body .= $this->textLine(' boundary="' . $this->_boundary[2] . '"');
                    $body .= static::$_LE;
                    $body .= $this->_getBoundary(
                        $this->_boundary[2],
                        $this->_altBodyCharSet,
                        static::CONTENT_TYPE_PLAINTEXT,
                        $this->_altBodyEncoding
                    );
                    $body .= $this->encodeString($this->AltBody, $this->_altBodyEncoding);
                    $body .= static::$_LE;
                    $body .= $this->_getBoundary(
                        $this->_boundary[2],
                        $bodyCharSet,
                        static::CONTENT_TYPE_TEXT_HTML,
                        $bodyEncoding
                    );
                    $body .= $this->encodeString($this->Body, $bodyEncoding);
                    $body .= static::$_LE;
                    if (!empty($this->Ical)) {
                        $method = static::ICAL_METHOD_REQUEST;
                        foreach (static::$_IcalMethods as $i_method) {
                            if (stripos($this->Ical, 'METHOD:' . $i_method) !== false) {
                                $method = $i_method;
                                break;
                            }
                        }
                        $body .= $this->_getBoundary(
                            $this->_boundary[2],
                            '',
                            static::CONTENT_TYPE_TEXT_CALENDAR . '; method=' . $method,
                            ''
                        );
                        $body .= $this->encodeString($this->Ical, $this->Encoding);
                    }
                    $body .= $this->_endBoundary($this->_boundary[2]);
                    $body .= static::$_LE;
                    $body .= $this->_attachAll('attachment', $this->_boundary[1]);
                    break;
                case 'alt_inline_attach':
                    $body .= $mime_pre;
                    $body .= $this->textLine('--' . $this->_boundary[1]);
                    $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_ALTERNATIVE . ';');
                    $body .= $this->textLine(' boundary="' . $this->_boundary[2] . '"');
                    $body .= static::$_LE;
                    $body .= $this->_getBoundary(
                        $this->_boundary[2],
                        $this->_altBodyCharSet,
                        static::CONTENT_TYPE_PLAINTEXT,
                        $this->_altBodyEncoding
                    );
                    $body .= $this->encodeString($this->AltBody, $this->_altBodyEncoding);
                    $body .= static::$_LE;
                    $body .= $this->textLine('--' . $this->_boundary[2]);
                    $body .= $this->headerLine('Content-Type', static::CONTENT_TYPE_MULTIPART_RELATED . ';');
                    $body .= $this->textLine(' boundary="' . $this->_boundary[3] . '";');
                    $body .= $this->textLine(' type="' . static::CONTENT_TYPE_TEXT_HTML . '"');
                    $body .= static::$_LE;
                    $body .= $this->_getBoundary(
                        $this->_boundary[3],
                        $bodyCharSet,
                        static::CONTENT_TYPE_TEXT_HTML,
                        $bodyEncoding
                    );
                    $body .= $this->encodeString($this->Body, $bodyEncoding);
                    $body .= static::$_LE;
                    $body .= $this->_attachAll('inline', $this->_boundary[3]);
                    $body .= static::$_LE;
                    $body .= $this->_endBoundary($this->_boundary[2]);
                    $body .= static::$_LE;
                    $body .= $this->_attachAll('attachment', $this->_boundary[1]);
                    break;
                default:
                    $this->Encoding = $bodyEncoding;
                    $body .= $this->encodeString($this->Body, $this->Encoding);
                    break;
            }
            if ($this->isError()) {
                $body = '';
                if ($this->_exceptions) throw new \RuntimeException($this->_lang('empty_message'), self::STOP_CRITICAL);
            }elseif ($this->_sign_key_file) {
                try {
                    if (!defined('PKCS7_TEXT'))
                        throw new \RuntimeException($this->_lang('extension_missing') . 'openssl');
                    $file = tempnam(sys_get_temp_dir(), 'srcsign');
                    $signed = tempnam(sys_get_temp_dir(), 'mailsign');
                    file_put_contents($file, $body);
                    if (empty($this->sign_extracerts_file))
                        $sign = @openssl_pkcs7_sign(
                            $file,$signed,
                            'file://' . realpath($this->_sign_cert_file),
                            ['file://' . realpath($this->_sign_key_file), $this->_sign_key_pass],
                            []
                        );
                    else $sign = @openssl_pkcs7_sign(
                            $file,$signed,
                            'file://' . realpath($this->_sign_cert_file),
                            ['file://' . realpath($this->_sign_key_file), $this->_sign_key_pass],
                            [],
                            PKCS7_DETACHED,
                            $this->_sign_extracerts_file
                        );
                    @unlink($file);
                    if ($sign) {
                        $body = file_get_contents($signed);
                        @unlink($signed);
                        $parts = explode("\n\n", $body, 2);
                        $this->_MIMEHeader .= $parts[0] . static::$_LE . static::$_LE;
                        $body = $parts[1];
                    } else {
                        @unlink($signed);
                        throw new \RuntimeException($this->_lang('signing') . openssl_error_string());
                    }
                } catch (\Exception $exc) {
                    $body = '';
                    if ($this->_exceptions) throw $exc;
                }
            }
            return $body;
        }//2782
        public function headerLine($name, $value): string
        {
            return $name . ': ' . $value . static::$_LE;
        }//3138
        public function textLine($value): string
        {
            return $value . static::$_LE;
        }//3150
        public function addAttachment($path,$name = '',$encoding = self::ENCODING_BASE64,$type = '',$disposition = 'attachment'): bool
        {
            try {
                if (!static::_fileIsAccessible($path))
                    throw new \RuntimeException($this->_lang('file_access') . $path, self::STOP_CONTINUE);
                if ('' === $type) $type = static::filenameToType($path);
                $filename = (string) static::mb_path_info($path, PATHINFO_BASENAME);
                if ('' === $name) $name = $filename;
                if (!$this->_validateEncoding($encoding))
                    throw new \RuntimeException($this->_lang('encoding') . $encoding);
                $this->_attachment[] = [
                    0 => $path,1 => $filename,2 => $name,3 => $encoding,
                    4 => $type,5 => false,6 => $disposition,7 => $name,
                ];
            } catch (\Exception $exc) {
                $this->_setError($exc->getMessage());
                $this->_emailDebug($exc->getMessage());
                if ($this->_exceptions) throw $exc;
                return false;
            }
            return true;
        }//3172
        public function getAttachments(): array
        {
            return $this->_attachment;
        }//3225
        public function encodeString($str, $encoding = self::ENCODING_BASE64){
            $encoded = '';
            switch (strtolower($encoding)) {
                case static::ENCODING_BASE64:
                    $encoded = chunk_split(base64_encode($str),static::STD_LINE_LENGTH,static::$_LE);
                    break;
                case static::ENCODING_7BIT:
                case static::ENCODING_8BIT:
                    $encoded = static::normalizeBreaks($str);
                    //Make sure it ends with a line break
                    if (substr($encoded, -(strlen(static::$_LE))) !== static::$_LE)
                        $encoded .= static::$_LE;
                    break;
                case static::ENCODING_BINARY:
                    $encoded = $str;
                    break;
                case static::ENCODING_QUOTED_PRINTABLE:
                    $encoded = $this->encodeQP($str);
                    break;
                default:
                    $this->_setError($this->_lang('encoding') . $encoding);
                    if ($this->_exceptions)
                        throw new \RuntimeException($this->_lang('encoding') . $encoding);
                    break;
            }
            return $encoded;
        }//3386
        public function encodeHeader($str, $position = 'text'): string
        {
            $match_count = 0;
            switch (strtolower($position)) {
                case 'phrase':
                    if (!preg_match('/[\200-\377]/', $str)) {
                        $encoded = addcslashes($str, "\0..\37\177\\\"");
                        if (($str === $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str))
                            return $encoded;
                        return "\"$encoded\"";
                    }
                    $match_count = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
                    break;
                /* @noinspection PhpMissingBreakStatementInspection */
                case 'comment':
                    $match_count = preg_match_all('/[()"]/', $str, $matches);
                case 'text':
                default:
                    $match_count += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
                    break;
            }
            if ($this->has8bitChars($str)) $charset = $this->CharSet;
            else $charset = static::CHARSET_ASCII;
            $overhead = 8 + strlen($charset);
            if ('mail' === $this->Mailer) $max_len = static::MAIL_MAX_LINE_LENGTH - $overhead;
            else $max_len = static::MAX_LINE_LENGTH - $overhead;
            if ($match_count > strlen($str) / 3) $encoding = 'B';
            elseif ($match_count > 0) $encoding = 'Q';
            elseif (strlen($str) > $max_len) $encoding = 'Q';
            else $encoding = false;
            switch ($encoding) {
                case 'B':
                    if ($this->hasMultiBytes($str))
                        $encoded = $this->base64EncodeWrapMB($str, "\n");
                    else {
                        $encoded = base64_encode($str);
                        $max_len -= $max_len % 4;
                        $encoded = trim(chunk_split($encoded, $max_len, "\n"));
                    }
                    $encoded = preg_replace('/^(.*)$/m', ' =?' . $charset . "?$encoding?\\1?=", $encoded);
                    break;
                case 'Q':
                    $encoded = $this->encodeQ($str, $position);
                    $encoded = $this->wrapText($encoded, $max_len, true);
                    $encoded = str_replace('=' . static::$_LE, "\n", trim($encoded));
                    $encoded = preg_replace('/^(.*)$/m', ' =?' . $charset . "?$encoding?\\1?=", $encoded);
                    break;
                default:
                    return $str;
            }
            return trim(static::normalizeBreaks($encoded));
        }//3432
        public function hasMultiBytes($str): bool
        {
            if (function_exists('mb_strlen'))
                return strlen($str) > mb_strlen($str, $this->CharSet);
            return false;
        }//3521
        public function has8bitChars($text): bool
        {
            return (bool) preg_match('/[\x80-\xFF]/', $text);
        }//3538
        public function base64EncodeWrapMB($str, $linebreak = null): string
        {
            $start = '=?' . $this->CharSet . '?B?';
            $end = '?=';
            $encoded = '';
            if (null === $linebreak)
                $linebreak = static::$_LE;
            $mb_length = mb_strlen($str, $this->CharSet);
            $length = 75 - strlen($start) - strlen($end);
            $ratio = $mb_length / strlen($str);
            $avgLength = floor($length * $ratio * .75);
            for ($i = 0; $i < $mb_length; $i += $offset) {
                $lookBack = 0;
                do {
                    $offset = $avgLength - $lookBack;
                    $chunk = mb_substr($str, $i, $offset, $this->CharSet);
                    $chunk = base64_encode($chunk);
                    ++$lookBack;
                } while (strlen($chunk) > $length);
                $encoded .= $chunk . $linebreak;
            }
            return substr($encoded, 0, -strlen($linebreak));
        }//3555
        public function encodeQP($string){
            return static::normalizeBreaks(quoted_printable_encode($string));
        }//3596
        public function encodeQ($str, $position = 'text'){
            $pattern = '';
            $encoded = str_replace(["\r", "\n"], '', $str);
            switch (strtolower($position)) {
                case 'phrase':
                    $pattern = '^A-Za-z0-9!*+\/ -';
                    break;
                /* @noinspection PhpMissingBreakStatementInspection */
                case 'comment':
                    $pattern = '\(\)"';
                case 'text':
                default:
                    $pattern = '\000-\011\013\014\016-\037\075\077\137\177-\377' . $pattern;
                    break;
            }
            $matches = [];
            if (preg_match_all("/[{$pattern}]/", $encoded, $matches)) {
                $equal_key = array_search('=', $matches[0], true);
                if (false !== $equal_key) {
                    unset($matches[0][$equal_key]);
                    array_unshift($matches[0], '=');
                }
                foreach (array_unique($matches[0]) as $char)
                    $encoded = str_replace($char, '=' . sprintf('%02X', ord($char)), $encoded);
            }
            return str_replace(' ', '_', $encoded);
        }//3611
        public function addStringAttachment($string,$filename,$encoding = self::ENCODING_BASE64,$type = '',$disposition = 'attachment'): bool
        {
            try {
                if ('' === $type) $type = static::filenameToType($filename);
                if (!$this->_validateEncoding($encoding))
                    throw new \RuntimeException($this->_lang('encoding') . $encoding);
                $this->_attachment[] = [0 => $string,1 => $filename,2 => static::mb_path_info($filename, PATHINFO_BASENAME),
                    3 => $encoding,4 => $type,5 => true, 6 => $disposition,7 => 0,];
            } catch (\Exception $exc) {
                $this->_setError($exc->getMessage());
                $this->_emailDebug($exc->getMessage());
                if ($this->_exceptions) throw $exc;
                return false;
            }
            return true;
        }//3669
        public function addEmbeddedImage($path,$cid,$name = '',$encoding = self::ENCODING_BASE64,$type = '',$disposition = 'inline'): bool
        {
            try {
                if (!static::_fileIsAccessible($path))
                    throw new \RuntimeException($this->_lang('file_access') . $path, self::STOP_CONTINUE);
                if ('' === $type) $type = static::filenameToType($path);
                if (!$this->_validateEncoding($encoding))
                    throw new \RuntimeException($this->_lang('encoding') . $encoding);
                $filename = (string) static::mb_path_info($path, PATHINFO_BASENAME);
                if ('' === $name) $name = $filename;
                $this->_attachment[] = [0 => $path,1 => $filename,2 => $name,3 => $encoding,
                    4 => $type,5 => false, 6 => $disposition,7 => $cid,];
            } catch (\Exception $exc) {
                $this->_setError($exc->getMessage());
                $this->_emailDebug($exc->getMessage());
                if ($this->_exceptions) throw $exc;
                return false;
            }
            return true;
        }//3731
        public function addStringEmbeddedImage($string,$cid,$name = '',$encoding = self::ENCODING_BASE64,$type = '',$disposition = 'inline'): bool
        {
            try {
                if ('' === $type && !empty($name)) $type = static::filenameToType($name);
                if (!$this->_validateEncoding($encoding))
                    throw new \RuntimeException($this->_lang('encoding') . $encoding);
                $this->_attachment[] = [0 => $string,1 => $name,2 => $name,3 => $encoding,
                    4 => $type,5 => true,6 => $disposition,7 => $cid,];
            } catch (\Exception $exc) {
                $this->_setError($exc->getMessage());
                $this->_emailDebug($exc->getMessage());
                if ($this->_exceptions)
                    throw $exc;
                return false;
            }
            return true;
        }//3801
        public function inlineImageExists(): bool
        {
            foreach ($this->_attachment as $attachment) {
                if ('inline' === $attachment[6])
                    return true;
            }
            return false;
        }//3888
        public function attachmentExists(): bool
        {
            foreach ($this->_attachment as $attachment){
                if ('attachment' === $attachment[6]) return true;
            }
            return false;
        }//3904
        public function alternativeExists(): bool
        {
            return !empty($this->AltBody);
        }//3920
        public function clearQueuedAddresses($kind): void
        {
            $this->_RecipientsQueue = array_filter(
                $this->_RecipientsQueue,
                static function ($params) use ($kind) {
                    return $params[0] !== $kind;
                }
            );
        }//3930
        public function clearAddresses(): void
        {
            foreach ($this->_to as $to)
                unset($this->_all_recipients[strtolower($to[0])]);
            $this->_to = [];
            $this->clearQueuedAddresses('to');
        }//3943
        public function clearCCs(): void
        {
            foreach ($this->_cc as $cc)
                unset($this->_all_recipients[strtolower($cc[0])]);
            $this->_cc = [];
            $this->clearQueuedAddresses('cc');
        }//3955
        public function clearBCCs(): void
        {
            foreach ($this->_bcc as $bcc)
                unset($this->_all_recipients[strtolower($bcc[0])]);
            $this->_bcc = [];
            $this->clearQueuedAddresses('bcc');
        }//3967
        public function clearReplyTos(): void
        {
            $this->_ReplyTo = [];
            $this->_ReplyToQueue = [];
        }//3979
        public function clearAllRecipients(): void
        {
            $this->_to = [];
            $this->_cc = [];
            $this->_bcc = [];
            $this->_all_recipients = [];
            $this->_RecipientsQueue = [];
        }//3988
        public function clearAttachments(): void
        {
            $this->_attachment = [];
        }//4000
        public function clearCustomHeaders(): void
        {
            $this->_CustomHeader = [];
        }//4008
        public function isError(): bool
        {
            return $this->_error_count > 0;
        }//4186
        public function addCustomHeader($name, $value = null): bool
        {
            if (null === $value && strpos($name, ':') !== false)
                @list($name, $value) = explode(':', $name, 2);
            $name = trim($name);
            $value = (null === $value) ? '' : trim($value);
            if (empty($name) || strpbrk($name . $value, "\r\n") !== false) {
                if ($this->_exceptions)
                    throw new \RuntimeException($this->_lang('invalid_header'));
                return false;
            }
            $this->_CustomHeader[] = [$name, $value];
            return true;
        }//4183
        public function getCustomHeaders(): array
        {
            return $this->_CustomHeader;
        }//4209
        public function msgHTML($message, $basedir = '', $advanced = false){
            preg_match_all('/(?<!-)(src|background)=["\'](.*)["\']/Ui', $message, $images);
            if (array_key_exists(2, $images)) {
                if (strlen($basedir) > 1 && '/' !== substr($basedir, -1)) $basedir .= '/';
                foreach ($images[2] as $img_index => $url) {
                    $match = [];
                    if (preg_match('#^data:(image/(?:jpe?g|gif|png));?(base64)?,(.+)#', $url, $match)) {
                        if (count($match) === 4 && static::ENCODING_BASE64 === $match[2])
                            $data = base64_decode($match[3]);
                        elseif ('' === $match[2]) $data = rawurldecode($match[3]);
                        else continue;
                        $cid = substr(hash('sha256', $data), 0, 32) . '@phpmailer.0'; //RFC2392 S 2
                        if (!$this->_cidExists($cid))
                            $this->addStringEmbeddedImage($data,$cid,'embed' . $img_index,static::ENCODING_BASE64,$match[1]);
                        $message = str_replace($images[0][$img_index],$images[1][$img_index] . '="cid:' . $cid . '"',$message);
                        continue;
                    }
                    if (!empty($basedir) && (strpos($url, '..') === false) && 0 !== strpos($url, 'cid:') && !preg_match('#^[a-z][a-z0-9+.-]*:?//#i', $url)){
                        $filename = static::mb_path_info($url, PATHINFO_BASENAME);
                        $directory = dirname($url);
                        if ('.' === $directory) $directory = '';
                        $cid = substr(hash('sha256', $url), 0, 32) . '@phpmailer.0';
                        if (strlen($basedir) > 1 && '/' !== substr($basedir, -1)) $basedir .= '/';
                        if (strlen($directory) > 1 && '/' !== substr($directory, -1)) $directory .= '/';
                        if (
                        $this->addEmbeddedImage($basedir . $directory . $filename,$cid,$filename, static::ENCODING_BASE64,static::_mime_types((string) static::mb_path_info($filename, PATHINFO_EXTENSION))))
                            $message = preg_replace( '/' . $images[1][$img_index] . '=["\']' . preg_quote($url, '/') . '["\']/Ui',$images[1][$img_index] . '="cid:' . $cid . '"',$message);
                    }
                }
            }
            $this->isHTML();
            $this->Body = static::normalizeBreaks($message);
            $this->AltBody = static::normalizeBreaks($this->html2text($message, $advanced));
            if (!$this->alternativeExists())
                $this->AltBody = 'This is an HTML-only message. To view it, activate HTML in your email application.' . static::$_LE;
            return $this->Body;
        }//4235
        public function html2text($html, $advanced = false ?: ''){
            if (is_callable($advanced)) return call_user_func($advanced[$html]);
            return html_entity_decode(
                trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/si', '', $html))),
                ENT_QUOTES, $this->CharSet);
        }//4353
        public function set($name, $value = ''): bool
        {
            if (property_exists($this, $name)) {
                $this->$name = $value;
                return true;
            }
            $this->_setError($this->_lang('variable_set') . $name);
            return false;
        }//4579
        public function secureHeader($str): string
        {
            return trim(str_replace(["\r", "\n"], '', $str));
        }//4598
        public function sign($cert_filename, $key_filename, $key_pass, $extra_certs_filename = ''): void
        {
            $this->_sign_cert_file = $cert_filename;
            $this->_sign_key_file = $key_filename;
            $this->_sign_key_pass = $key_pass;
            $this->_sign_extracerts_file = $extra_certs_filename;
        }//4668
        public function DKIM_QP($txt): string
        {
            $line = '';
            $len = strlen($txt);
            for ($i = 0; $i < $len; ++$i) {
                $ord = ord($txt[$i]);
                if (((0x21 <= $ord) && ($ord <= 0x3A)) || $ord === 0x3C || ((0x3E <= $ord) && ($ord <= 0x7E)))
                    $line .= $txt[$i];
                else $line .= '=' . sprintf('%02X', $ord);
            }
            return $line;
        }//4683
        public function DKIM_Sign($signHeader): string
        {
            if (!defined('PKCS7_TEXT')) {
                if ($this->_exceptions)
                    throw new \RuntimeException($this->_lang('extension_missing') . 'openssl');
                return '';
            }
            $privateKeyStr = !empty($this->DKIM_private_string) ?
                $this->DKIM_private_string :
                file_get_contents($this->DKIM_private);
            if ('' !== $this->DKIM_passphrase)
                $privateKey = openssl_pkey_get_private($privateKeyStr, $this->DKIM_passphrase);
            else $privateKey = openssl_pkey_get_private($privateKeyStr);
            if (openssl_sign($signHeader, $signature, $privateKey, 'sha256WithRSAEncryption')) {
                if (\PHP_MAJOR_VERSION < 8)openssl_pkey_free($privateKey);
                return base64_encode($signature);
            }
            if (\PHP_MAJOR_VERSION < 8)
                openssl_pkey_free($privateKey);
            return '';
        }//4708
        public function DKIM_HeaderC($signHeader): string
        {
            $signHeader = static::normalizeBreaks($signHeader, self::CRLF);
            $signHeader = preg_replace('/\r\n[ \t]+/', ' ', $signHeader);
            $lines = explode(self::CRLF, $signHeader);
            foreach ($lines as $key => $line) {
                if (strpos($line, ':') === false) continue;
                @list($heading, $value) = explode(':', $line, 2);
                $heading = strtolower($heading);
                $value = preg_replace('/[ \t]+/', ' ', $value);
                $lines[$key] = trim($heading, " \t") . ':' . trim($value, " \t");
            }
            return implode(self::CRLF, $lines);
        }//4750
        public function DKIM_BodyC($body): string
        {
            if (empty($body)) return self::CRLF;
            $body = static::normalizeBreaks($body, self::CRLF);
            return static::stripTrailingWSP($body) . self::CRLF;
        }//4794
        public function DKIM_Add($headers_line, $subject, $body){
            $DKIM_signatureType = 'rsa-sha256';
            $DKIM_canonicalliasation = 'relaxed/simple';
            $DKIM_query = 'dns/txt'; //Query method
            $DKIM_time = time();
            $autoSignHeaders = ['from','to','cc','date','subject','reply-to',
                'message-id','content-type','mime-version','x-mailer',];
            if (stripos($headers_line, 'Subject') === false)
                $headers_line .= 'Subject: ' . $subject . static::$_LE;
            $headerLines = explode(static::$_LE, $headers_line);
            $currentHeaderLabel = '';
            $currentHeaderValue = '';
            $parsedHeaders = [];
            $headerLineIndex = 0;
            $headerLineCount = count($headerLines);
            foreach ($headerLines as $headerLine) {
                $matches = [];
                if (preg_match('/^([^ \t]*?)(?::[ \t]*)(.*)$/', $headerLine, $matches)) {
                    if ($currentHeaderLabel !== '')
                        $parsedHeaders[] = ['label' => $currentHeaderLabel, 'value' => $currentHeaderValue];
                    $currentHeaderLabel = $matches[1];
                    @list($currentHeaderValue) = $matches[2];
                } elseif (preg_match('/^[ \t]+(.*)$/', $headerLine, $matches))
                    $currentHeaderValue .= ' ' . $matches[1];
                ++$headerLineIndex;
                if ($headerLineIndex >= $headerLineCount)
                    $parsedHeaders[] = ['label' => $currentHeaderLabel, 'value' => $currentHeaderValue];
            }
            $copiedHeaders = [];
            $headersToSignKeys = [];
            $headersToSign = [];
            foreach ($parsedHeaders as $header) {
                if (in_array(strtolower($header['label']), $autoSignHeaders, true)) {
                    $headersToSignKeys[] = $header['label'];
                    $headersToSign[] = $header['label'] . ': ' . $header['value'];
                    if ($this->DKIM_copyHeaderFields) {
                        $copiedHeaders[] = $header['label'] . ':' . //Note no space after this, as per RFC
                            str_replace('|', '=7C', $this->DKIM_QP($header['value']));
                    }
                    continue;
                }
                if (in_array($header['label'], $this->DKIM_extraHeaders, true)) {
                    foreach ($this->_CustomHeader as $customHeader) {
                        if ($customHeader[0] === $header['label']) {
                            $headersToSignKeys[] = $header['label'];
                            $headersToSign[] = $header['label'] . ': ' . $header['value'];
                            if ($this->DKIM_copyHeaderFields)
                                $copiedHeaders[] = $header['label'] . ':' . str_replace('|', '=7C', $this->DKIM_QP($header['value']));
                            continue 2;
                        }
                    }
                }
            }
            $copiedHeaderFields = '';
            if ($this->DKIM_copyHeaderFields && count($copiedHeaders) > 0) {
                $copiedHeaderFields = ' z=';
                $first = true;
                foreach ($copiedHeaders as $copiedHeader) {
                    if (!$first) $copiedHeaderFields .= static::$_LE . ' |';
                    if (strlen($copiedHeader) > self::STD_LINE_LENGTH - 3)
                        $copiedHeaderFields .= substr( chunk_split($copiedHeader, self::STD_LINE_LENGTH - 3, static::$_LE . self::FWS), 0, -strlen(static::$_LE . self::FWS));
                    else $copiedHeaderFields .= $copiedHeader;
                    $first = false;
                }
                $copiedHeaderFields .= ';' . static::$_LE;
            }
            $headerKeys = ' h=' . implode(':', $headersToSignKeys) . ';' . static::$_LE;
            $headerValues = implode(static::$_LE, $headersToSign);
            $body = $this->DKIM_BodyC($body);
            $DKIMb64 = base64_encode(pack('H*', hash('sha256', $body)));
            $identify = '';
            if ('' !== $this->DKIM_identity)
                $identify = ' i=' . $this->DKIM_identity . ';' . static::$_LE;
            $dkimSignatureHeader = 'DKIM-Signature: v=1;' .
                ' d=' . $this->DKIM_domain . ';' .
                ' s=' . $this->DKIM_selector . ';' . static::$_LE .
                ' a=' . $DKIM_signatureType . ';' .
                ' q=' . $DKIM_query . ';' .
                ' t=' . $DKIM_time . ';' .
                ' c=' . $DKIM_canonicalliasation . ';' . static::$_LE .
                $headerKeys .
                $identify .
                $copiedHeaderFields .
                ' bh=' . $DKIMb64 . ';' . static::$_LE .
                ' b=';
            $canonicalizedHeaders = $this->DKIM_HeaderC(
                $headerValues . static::$_LE . $dkimSignatureHeader
            );
            $signature = $this->DKIM_Sign($canonicalizedHeaders);
            $signature = trim(chunk_split($signature, self::STD_LINE_LENGTH - 3, static::$_LE . self::FWS));
            return static::normalizeBreaks($dkimSignatureHeader . $signature);
        }//4817
        public function getToAddresses(): array
        {
            return $this->_to;
        }//4998
        public function getCcAddresses(): array
        {
            return $this->_cc;
        }//5005
        public function getBccAddresses(): array
        {
            return $this->_bcc;
        }//5016
        public function getReplyToAddresses(): array
        {
            return $this->_ReplyTo;
        }//5027
        public function getAllRecipientAddresses(): array
        {
            return $this->_all_recipients;
        }//5038
        public function getOAuth(){
            return $this->_oauth;
        }//5067
        public function setOAuth(OAuthTokenProvider $oauth): void
        {
            $this->_oauth = $oauth;
        }//5075
        public static function parseAddresses($addrstr, $useimap = true, $charset = self::CHARSET_ISO88591): array
        {
            $addresses = [];
            if ($useimap && function_exists('imap_rfc822_parse_adrlist')) {
                $list = imap_rfc822_parse_adrlist($addrstr, '');
                imap_errors();
                foreach ($list as $address) {
                    if ('.SYNTAX-ERROR.' !== $address->host && static::validateAddress($address->mailbox . '@' . $address->host)){
                        if (property_exists($address, 'personal') && defined('MB_CASE_UPPER') && preg_match('/^=\?.*\?=$/s', $address->personal)){
                            $origCharset = mb_internal_encoding();
                            mb_internal_encoding($charset);
                            $address->personal = str_replace('_', '=20', $address->personal);
                            $address->personal = mb_decode_mimeheader($address->personal);
                            mb_internal_encoding($origCharset);
                        }
                        $addresses[] = [
                            'name' => (property_exists($address, 'personal') ? $address->personal : ''),
                            'address' => $address->mailbox . '@' . $address->host,
                        ];
                    }
                }
            } else {
                $list = explode(',', $addrstr);
                foreach ($list as $address) {
                    $address = trim($address);
                    if (strpos($address, '<') === false) {
                        if (static::validateAddress($address))
                            $addresses[] = ['name' => '','address' => $address,];
                    } else {
                        @list($name, $email) = explode('<', $address);
                        $email = trim(str_replace('>', '', $email));
                        $name = trim($name);
                        if (static::validateAddress($email)) {
                            if (defined('MB_CASE_UPPER') && preg_match('/^=\?.*\?=$/s', $name)) {
                                $origCharset = mb_internal_encoding();
                                mb_internal_encoding($charset);
                                $name = str_replace('_', '=20', $name);
                                $name = mb_decode_mimeheader($name);
                                mb_internal_encoding($origCharset);
                            }
                            $addresses[] = [
                                'name' => trim($name, '\'" '),
                                'address' => $email,
                            ];
                        }
                    }
                }
            }
            return $addresses;
        }//1200
        public static function validateAddress($address, $pattern_select = null): ?bool
        {
            if (null === $pattern_select)
            $pattern_select = static::$validator;
            if (is_callable($pattern_select) && !is_string($pattern_select))
                return call_user_func($pattern_select[$address]);
            if (strpos($address, "\n") !== false || strpos($address, "\r") !== false)
                return false;
            switch ($pattern_select) {
                case 'pcre': //Kept for BC
                case 'pcre8':
                return (bool) preg_match(
                    '/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)' .
                    '((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)?[\t ]+)?)(\((?>(?2)' .
                    '(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)' .
                    '([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*' .
                    '(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' .
                    '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' .
                    '|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:' .
                    '|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
                    '|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD',
                    $address
                );
                case 'html5':
                    return (bool) preg_match(
                        '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' .
                        '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD',
                        $address
                    );
                case 'php':
                default:
                    return filter_var($address, FILTER_VALIDATE_EMAIL) !== false;
            }
        }//1359
        public static function idnSupported(): bool
        {
            return function_exists('idn_to_ascii') && function_exists('mb_convert_encoding');
        }//1426
        public static function rfcDate(){
            date_default_timezone_set(@date_default_timezone_get());
            return date('D, j M Y H:i:s O');
        }//4044
        public static function isValidHost($host): bool
        {
            if (empty($host) || !is_string($host) || strlen($host) > 256 || !preg_match('/^([a-zA-Z\d.-]*|\[[a-fA-F\d:]+\])$/', $host))
                return false;
            if (strlen($host) > 2 && $host[0] === '[' && $host[strlen($host)-1] === ']')
                return filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
            if (is_numeric(str_replace('.', '', $host)))
                return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
            if (filter_var('http://' . $host, FILTER_VALIDATE_URL) !== false) return true;
            return false;
        }//4086
        public static function _mime_types($ext = ''): string
        {
            $mimes = self::$mimes_list;
            $ext = strtolower($ext);
            if (array_key_exists($ext, $mimes))
                return $mimes[$ext];
            return 'application/octet-stream';
        }//4373
        public static function filenameToType($filename): string
        {
            $q_pos = strpos($filename, '?');
            if (false !== $q_pos) $filename = substr($filename, 0, $q_pos);
            $ext = static::mb_path_info($filename, PATHINFO_EXTENSION);
            return static::_mime_types($ext);
        }//4505
        public static function mb_path_info($path, $options = null){
            $ret = ['dirname' => '', 'basename' => '', 'extension' => '', 'filename' => ''];
            $path_info = [];
            if (preg_match('#^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^.\\\\/]+?)|))[\\\\/.]*$#m', $path, $pathinfo)) {
                if (array_key_exists(1, $path_info)) $ret['dirname'] = $path_info[1];
                if (array_key_exists(2, $path_info)) $ret['basename'] = $path_info[2];
                if (array_key_exists(5, $path_info)) $ret['extension'] = $path_info[5];
                if (array_key_exists(3, $path_info)) $ret['filename'] = $path_info[3];
            }
            switch ($options) {
                case PATHINFO_DIRNAME:
                case 'dirname':
                    return $ret['dirname'];
                case PATHINFO_BASENAME:
                case 'basename':
                    return $ret['basename'];
                case PATHINFO_EXTENSION:
                case 'extension':
                    return $ret['extension'];
                case PATHINFO_FILENAME:
                case 'filename':
                    return $ret['filename'];
                default:
                    return $ret;
            }
        }//4529
        public static function normalizeBreaks($text, $break_type = null){
            if (null === $break_type) $break_type = static::$_LE;
            $text = str_replace([self::CRLF, "\r"], "\n", $text);
            if ("\n" !== $break_type) $text = str_replace("\n", $break_type, $text);
            return $text;
        }//4613
        public static function stripTrailingWSP($text): string
        {
            return rtrim($text, " \r\n\t");
        }//4635
        public static function getLE(){
            return static::$_LE;
        }//4645
        public static function hasLineLongerThanMax($str): bool
        {
            return (bool) preg_match('/^(.{' . (self::MAX_LINE_LENGTH + strlen(static::$_LE)) . ',})/m', $str);
        }//4961
        public static function quotedString($str): string
        {
            if (preg_match('/[ ()<>@,;:"\/\[\]?=]/', $str))
                return '"' . str_replace('"', '\\"', $str) . '"';
            return $str;
        }//4967
        protected function _emailDebug($str): void
        {
            if ($this->SMTPDebug <= 0) return;
            if ($this->Debugoutput instanceof LoggerInterface) {
                $this->Debugoutput->debug($str);
                return;
            }
            if (is_callable($this->Debugoutput) && !in_array($this->Debugoutput, ['error_log', 'html', 'echo'])) {
                call_user_func($this->Debugoutput, $str, $this->SMTPDebug);
                return;
            }
            switch ($this->Debugoutput) {
                case 'error_log':
                    /** @noinspection ForgottenDebugOutputInspection */
                    error_log($str);
                    break;
                case 'html':
                    echo htmlentities(
                        preg_replace('/[\r\n]+/', '', $str),
                        ENT_QUOTES,
                        'UTF-8'
                    ), "<br>\n";
                    break;
                case 'echo':
                default:
                    $str = preg_replace('/\r\n|\r/m', "\n", $str);
                    echo gmdate('Y-m-d H:i:s'),
                    "\t",
                    trim(
                        str_replace(
                            "\n",
                            "\n                   \t                  ",
                            trim($str)
                        )
                    ),
                    "\n";
            }
        }//892
        protected function _addOrEnqueueAnAddress($kind, $address, $name){
            $pos = false;
            if ($address !== null) {
                $address = trim($address);
                $pos = strrpos($address, '@');
            }
            if (false === $pos) {
                $error_message = sprintf('%s (%s): %s',$this->_lang('invalid_address'),$kind,$address);
                $this->_setError($error_message);
                $this->_emailDebug($error_message);
                if ($this->_exceptions) {
                    throw new \RuntimeException($error_message);
                }
                return false;
            }
            if ($name !== null) $name = trim(preg_replace('/[\r\n]+/', '', $name));
            else $name = '';

            $params = [$kind, $address, $name];
            if (static::idnSupported() && $this->has8bitChars(substr($address, ++$pos))) {
                if ('Reply-To' !== $kind) {
                    if (!array_key_exists($address, $this->_RecipientsQueue)) {
                        $this->_RecipientsQueue[$address] = $params;
                        return true;
                    }
                } elseif (!array_key_exists($address, $this->_ReplyToQueue)) {
                    $this->_ReplyToQueue[$address] = $params;
                    return true;
                }
                return false;
            }
            return call_user_func_array([$this, 'addAnAddress'], $params);
        }//1076
        protected function _addAnAddress($kind, $address, $name = ''): bool
        {
            if (!in_array($kind, ['to', 'cc', 'bcc', 'Reply-To'])) {
                $error_message = sprintf('%s: %s', $this->_lang('Invalid recipient kind'),$kind);
                $this->_setError($error_message);
                $this->_emailDebug($error_message);
                if ($this->_exceptions) throw new \RuntimeException($error_message);
                return false;
            }
            if (!static::validateAddress($address)) {
                $error_message = sprintf('%s (%s): %s', $this->_lang('invalid_address'), $kind, $address);
                $this->_setError($error_message);
                $this->_emailDebug($error_message);
                if ($this->_exceptions)throw new \RuntimeException($error_message);
                return false;
            }
            if ('Reply-To' !== $kind) {
                if (!array_key_exists(strtolower($address), $this->_all_recipients)) {
                    $this->{$kind}[] = [$address, $name];
                    $this->_all_recipients[strtolower($address)] = true;
                    return true;
                }
            } elseif (!array_key_exists(strtolower($address), $this->_ReplyTo)) {
                $this->_ReplyTo[strtolower($address)] = [$address, $name];
                return true;
            }
            return false;
        }//1139
        protected function _sendmailSend($header, $body): bool
        {
            if ($this->Mailer === 'qmail') $this->_emailDebug('Sending with qmail');
            else $this->_emailDebug('Sending with sendmail');
            $header = static::stripTrailingWSP($header) . static::$_LE . static::$_LE;
            $sendmail_from_value = ini_get('sendmail_from');
            if (empty($this->Sender) && !empty($sendmail_from_value))
                $this->Sender = ini_get('sendmail_from');
            if (!empty($this->Sender) && static::validateAddress($this->Sender) && self::_isShellSafe($this->Sender)) {
                if ($this->Mailer === 'qmail') $sendmailFmt = '%s -f%s';
                 else $sendmailFmt = '%s -oi -f%s -t';
            } else $sendmailFmt = '%s -oi -t';
            $sendmail = sprintf($sendmailFmt, escapeshellcmd($this->Sendmail), $this->Sender);
            $this->_emailDebug('Sendmail path: ' . $this->Sendmail);
            $this->_emailDebug('Sendmail command: ' . $sendmail);
            $this->_emailDebug('Envelope sender: ' . $this->Sender);
            $this->_emailDebug("Headers: {$header}");
            if ($this->SingleTo) {
                foreach ($this->_SingleToArray as $toAddr) {
                    $mail = @popen($sendmail, 'w');
                    if (!$mail) throw new \RuntimeException($this->_lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
                    $this->_emailDebug("To: {$toAddr}");
                    fwrite($mail, 'To: ' . $toAddr . "\n");
                    fwrite($mail, $header);
                    fwrite($mail, $body);
                    $result = pclose($mail);
                    $address_info = static::parseAddresses($toAddr, true, $this->CharSet);
                    $this->_doCallback(($result === 0),[[$address_info['address'], $address_info['name']]],
                        $this->_cc,$this->_bcc,$this->Subject,$body,$this->From,[]);
                    $this->_emailDebug("Result: " . ($result === 0 ? 'true' : 'false'));
                    if (0 !== $result) throw new \RuntimeException($this->_lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
                }
            } else {
                $mail = @popen($sendmail, 'w');
                if (!$mail) throw new \RuntimeException($this->_lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
                fwrite($mail, $header);
                fwrite($mail, $body);
                $result = pclose($mail);
                $this->_doCallback(
                    ($result === 0),$this->_to,$this->_cc,$this->_bcc,$this->Subject,$body,$this->From,[]);
                $this->_emailDebug("Result: " . ($result === 0 ? 'true' : 'false'));
                if (0 !== $result)
                    throw new \RuntimeException($this->_lang('execute') . $this->Sendmail, self::STOP_CRITICAL);
            }
            return true;
        }//1699
        protected function _mailSend($header, $body): bool
        {
            $header = static::stripTrailingWSP($header) . static::$_LE . static::$_LE;
            $to_Arr = [];
            foreach ($this->_to as $to_addr)
                $to_Arr[] = $this->addrFormat($to_addr);
            $to = implode(', ', $to_Arr);
            $params = null;
            $sendmail_from_value = ini_get('sendmail_from');
            if (empty($this->Sender) && !empty($sendmail_from_value))
                $this->Sender = ini_get('sendmail_from');
            if (!empty($this->Sender) && static::validateAddress($this->Sender)) {
                if (self::_isShellSafe($this->Sender))
                    $params = sprintf('-f%s', $this->Sender);
                $old_from = ini_get('sendmail_from');
                ini_set('sendmail_from', $this->Sender);
            }
            $result = false;
            if ($this->SingleTo && count($to_Arr) > 1) {
                foreach ($to_Arr as $toAddr) {
                    $result = $this->__mailPassthru($toAddr, $this->Subject, $body, $header, $params);
                    $addr_info = static::parseAddresses($toAddr, true, $this->CharSet);
                    $this->_doCallback($result,[[$addr_info['address'], $addr_info['name']]],
                        $this->_cc,$this->_bcc,$this->Subject,$body,$this->From,[]);
                }
            } else {
                $result = $this->__mailPassthru($to, $this->Subject, $body, $header, $params);
                $this->_doCallback($result, $this->_to, $this->_cc, $this->_bcc, $this->Subject, $body, $this->From, []);
            }
            if (isset($old_from)) ini_set('sendmail_from', $old_from);
            if (!$result) throw new \RuntimeException($this->_lang('instantiate'), self::STOP_CRITICAL);
            return true;
        }//1886
        protected function _smtpSend($header, $body): bool
        {
            $smtp = null;
            if($this->_smtp instanceof SMTP){
                $smtp = $this->_smtp;
            }
            $header = static::stripTrailingWSP($header) . static::$_LE . static::$_LE;
            $bad_rcpt = [];
            if (!$this->smtpConnect($this->SMTPOptions))
                throw new \RuntimeException($this->_lang('smtp_connect_failed'), self::STOP_CRITICAL);
            if ('' === $this->Sender) $smtp_from = $this->From;
            else $smtp_from = $this->Sender;
            if (!$smtp->mail($smtp_from)) {
                $this->_setError($this->_lang('from_failed') . $smtp_from . ' : ' . implode(',', $smtp->getError()));
                throw new \RuntimeException($this->ErrorInfo, self::STOP_CRITICAL);
            }
            $callbacks = [];
            foreach ([$this->_to, $this->_cc, $this->_bcc] as $to_group) {
                foreach ($to_group as $to) {
                    if (!$smtp->recipient($to[0], $this->dsn)) {
                        $error = $this->_smtp->getError();
                        $bad_rcpt[] = ['to' => $to[0], 'error' => $error['detail']];
                        $isSent = false;
                    } else $isSent = true;
                    $callbacks[] = ['issent' => $isSent, 'to' => $to[0], 'name' => $to[1]];
                }
            }
            if (!$smtp->data($header . $body) && (count($this->_all_recipients) > count($bad_rcpt)))
                throw new \RuntimeException($this->_lang('data_not_accepted'), self::STOP_CRITICAL);
            $smtp_transaction_id = $smtp->getLastTransactionID();
            if ($this->SMTPKeepAlive) $smtp->reset();
            else {
                $smtp->quit();
                $smtp->close();
            }
            foreach ($callbacks as $cb)
                $this->_doCallback($cb['issent'],[[$cb['to'], $cb['name']]],[],[],$this->Subject,$body,$this->From,['smtp_transaction_id' => $smtp_transaction_id]);
            if (count($bad_rcpt) > 0) {
                $err_str = '';
                foreach ($bad_rcpt as $bad) $err_str .= $bad['to'] . ': ' . $bad['error'];
                throw new \RuntimeException($this->_lang('recipients_failed') . $err_str, self::STOP_CONTINUE);
            }
            return true;
        }//1992
        protected function _generateId(){
            $len = 32; //32 bytes = 256 bits
            $bytes = '';
            if (function_exists('random_bytes')) {
                try {
                    $bytes = random_bytes($len);
                } catch (\Exception $e) {}//Do nothing
            } elseif (function_exists('openssl_random_pseudo_bytes'))
                /** @noinspection CryptographicallySecureRandomnessInspection */
                $bytes = openssl_random_pseudo_bytes($len);
            if ($bytes === '') $bytes = hash('sha256', uniqid((string) mt_rand(), true), true);
            return str_replace(['=', '+', '/'], '', base64_encode(hash('sha256', $bytes, true)));
        }//2750
        protected function _getBoundary($boundary, $charSet, $contentType, $encoding): string
        {
            $result = '';
            if ('' === $charSet) $charSet = $this->CharSet;
            if ('' === $contentType) $contentType = $this->ContentType;
            if ('' === $encoding) $encoding = $this->Encoding;
            $result .= $this->textLine('--' . $boundary);
            $result .= sprintf('Content-Type: %s; charset=%s', $contentType, $charSet);
            $result .= static::$_LE;
            if (static::ENCODING_7BIT !== $encoding)
                $result .= $this->headerLine('Content-Transfer-Encoding', $encoding);
            $result .= static::$_LE;
            return $result;
        }//3071
        protected function _endBoundary($boundary): string
        {
            return static::$_LE . '--' . $boundary . '--' . static::$_LE;
        }//3102
        protected function _setMessageType(): void
        {
            $type = [];
            if ($this->alternativeExists()) $type[] = 'alt';
            if ($this->inlineImageExists()) $type[] = 'inline';
            if ($this->attachmentExists()) $type[] = 'attach';
            $this->_message_type = implode('_', $type);
            if ('' === $this->_message_type) $this->_message_type = 'plain';
        }//3111
        protected function _attachAll($disposition_type, $boundary): string
        {
            $mime = [];
            $cid_Unique = [];
            $incl = [];
            //Add all attachments
            foreach ($this->_attachment as $attachment) {
                if ($attachment[6] === $disposition_type) {
                    $string = '';
                    $path = '';
                    $bString = $attachment[5];
                    if ($bString) $string = $attachment[0];
                    else $path = $attachment[0];
                    $incl_hash = hash('sha256', serialize($attachment));
                    if (in_array($incl_hash, $incl, true)) continue;
                    $incl[] = $incl_hash;
                    $name = $attachment[2];
                    @list($encoding) = $attachment[3];
                    @list($type) = $attachment[4];
                    $disposition = $attachment[6];
                    @list($cid) = $attachment[7];
                    if ('inline' === $disposition && array_key_exists($cid, $cid_Unique))
                        continue;
                    $cid_Unique[$cid] = true;
                    $mime[] = sprintf('--%s%s', $boundary, static::$_LE);
                    if (!empty($name))
                        $mime[] = sprintf('Content-Type: %s; name=%s%s',$type,
                            static::quotedString($this->encodeHeader($this->secureHeader($name))),
                            static::$_LE);
                    else $mime[] = sprintf('Content-Type: %s%s',$type,static::$_LE);
                    if (static::ENCODING_7BIT !== $encoding)
                        $mime[] = sprintf('Content-Transfer-Encoding: %s%s', $encoding, static::$_LE);
                    if ((string) $cid !== '' && $disposition === 'inline')
                        $mime[] = 'Content-ID: <' . $this->encodeHeader($this->secureHeader($cid)) . '>' . static::$_LE;
                    if (!empty($disposition)) {
                        $encoded_name = $this->encodeHeader($this->secureHeader($name));
                        if (!empty($encoded_name))
                            $mime[] = sprintf('Content-Disposition: %s; filename=%s%s',$disposition,static::quotedString($encoded_name),static::$_LE . static::$_LE);
                        else $mime[] = sprintf('Content-Disposition: %s%s',$disposition,static::$_LE . static::$_LE);
                    } else $mime[] = static::$_LE;
                    if ($bString) $mime[] = $this->encodeString($string, $encoding);
                    else $mime[] = $this->_encodeFile($path, $encoding);
                    if ($this->isError()) return '';
                    $mime[] = static::$_LE;
                }
            }
            $mime[] = sprintf('--%s--%s', $boundary, static::$_LE);
            return implode('', $mime);
        }//3241
        protected function _encodeFile($path, $encoding = self::ENCODING_BASE64){
            try {
                if (!static::_fileIsAccessible($path))
                    throw new \OutOfRangeException($this->_lang('file_open') . $path, self::STOP_CONTINUE);
                $file_buffer = file_get_contents($path);
                if (false === $file_buffer)
                    throw new \OutOfBoundsException($this->_lang('file_open') . $path, self::STOP_CONTINUE);
                $file_buffer = $this->encodeString($file_buffer, $encoding);
                return $file_buffer;
            } catch (\Exception $exc) {
                $this->_setError($exc->getMessage());
                $this->_emailDebug($exc->getMessage());
                if ($this->_exceptions) throw $exc;
                return '';
            }
        }//3351
        protected function _validateEncoding($encoding): bool
        {
            return in_array(
                $encoding,
                [
                    self::ENCODING_7BIT,
                    self::ENCODING_QUOTED_PRINTABLE,
                    self::ENCODING_BASE64,
                    self::ENCODING_8BIT,
                    self::ENCODING_BINARY,
                ],
                true
            );
        }//3850
        protected function _cidExists($cid): bool
        {
            foreach ($this->_attachment as $attachment) {
                if ('inline' === $attachment[6] && $cid === $attachment[7])
                    return true;
            }
            return false;
        }//3872
        protected function _setError($msg): void
        {
            $smtp = null;
            if($this->_smtp instanceof SMTP){
                $smtp = $this->_smtp;
            }
            ++$this->_error_count;
            if ('smtp' === $this->Mailer && null !== $smtp) {
                $last_error = $this->_smtp->getError();
                if (!empty($last_error['error'])) {
                    $msg .= $this->_lang('smtp_error') . $last_error['error'];
                    if (!empty($last_error['detail']))
                        $msg .= ' ' . $this->_lang('smtp_detail') . $last_error['detail'];
                    if (!empty($last_error['smtp_code']))
                        $msg .= ' ' . $this->_lang('smtp_code') . $last_error['smtp_code'];
                    if (!empty($last_error['smtp_code_ex']))
                        $msg .= ' ' . $this->_lang('smtp_code_ex') . $last_error['smtp_code_ex'];
                }
            }
            $this->ErrorInfo = $msg;
        }//4018
        protected function _serverHostname(){
            $result = '';
            if (!empty($this->Hostname)) $result = $this->Hostname;
            elseif (isset($_SERVER) && array_key_exists('SERVER_NAME', $_SERVER))
                $result = $_SERVER['SERVER_NAME'];
            elseif (function_exists('gethostname') && gethostname() !== false)
                $result = gethostname();
            elseif (php_uname('n') !== false)
                $result = php_uname('n');
            if (!static::isValidHost($result))
                return 'localhost.localdomain';
            return $result;
        }//4059
        protected function _lang($key): string
        {
            if (count($this->_language) < 1) $this->setLanguage();
            if (array_key_exists($key, $this->_language)) {
                if ('smtp_connect_failed' === $key)
                    return $this->_language[$key] . ' https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting';
                return $this->_language[$key];
            }
            return $key;
        }//4122
        protected function _doCallback($isSent, $to, $cc, $bcc, $subject, $body, $from, $extra): void
        {
            if (!empty($this->action_function) && is_callable($this->action_function))
                call_user_func($this->action_function, $isSent, $to, $cc, $bcc, $subject, $body, $from, $extra);
        }
        protected static function _isShellSafe($string): bool
        {
            if (!function_exists('escapeshellarg') || !function_exists('escapeshellcmd'))
                return false;
            if (escapeshellcmd($string) !== $string || !in_array(escapeshellarg($string), ["'$string'", "\"$string\""]))
                return false;
            $length = strlen($string);
            for ($i = 0; $i < $length; ++$i) {
                $c = $string[$i];
                if (!ctype_alnum($c) && strpos('@_-.', $c) === false)
                    return false;
            }
            return true;
        }//1807
        protected static function _isPermittedPath($path): bool
        {
            return !preg_match('#^[a-z][a-z\d+.-]*://#i', $path);
        }//1848
        protected static function _fileIsAccessible($path): bool
        {
            if (!static::_isPermittedPath($path))
                return false;
            $readable = file_exists($path);
            if (strpos($path, '\\\\') !== 0) $readable = $readable && is_readable($path);
            return  $readable;
        }//1861
        protected static function _setLE($le): void
        {
            static::$_LE = $le;
        }
        private function __mailPassthru($to, $subject, $body, $header, $params): bool
        {
            if (ini_get('mbstring.func_overload') & 1)
                $subject = $this->secureHeader($subject);
            else $subject = $this->encodeHeader($this->secureHeader($subject));
            $this->_emailDebug('Sending with mail()');
            $this->_emailDebug('Sendmail path: ' . ini_get('sendmail_path'));
            $this->_emailDebug("Envelope sender: {$this->Sender}");
            $this->_emailDebug("To: {$to}");
            $this->_emailDebug("Subject: {$subject}");
            $this->_emailDebug("Headers: {$header}");
            if (!$this->UseSendmailOptions || null === $params)
                $result = @mail($to, $subject, $body, $header);
            else {
                $this->_emailDebug("Additional params: {$params}");
                $result = @mail($to, $subject, $body, $header, $params);
            }
            $this->_emailDebug('Result: ' . ($result ? 'true' : 'false'));
            return $result;
        }//858
        private function __getSmtpErrorMessage($base_key): string
        {
            $smtp = null;
            if($this->_smtp instanceof SMTP){
                $smtp = $this->_smtp;
            }
            $message = $this->_lang($base_key);
            $error = $smtp->getError();
            if (!empty($error['error'])) {
                $message .= ' ' . $error['error'];
                if (!empty($error['detail']))
                    $message .= ' ' . $error['detail'];
            }
            return $message;
        }//4149
    }
}else die;