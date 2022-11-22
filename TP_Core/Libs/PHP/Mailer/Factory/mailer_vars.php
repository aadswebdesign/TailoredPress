<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-5-2022
 * Time: 10:34
 */

namespace TP_Core\Libs\PHP\Mailer\Factory;
if(ABSPATH){
    trait mailer_vars{
        protected static $_IcalMethods = [
            self::ICAL_METHOD_REQUEST,
            self::ICAL_METHOD_PUBLISH,
            self::ICAL_METHOD_REPLY,
            self::ICAL_METHOD_ADD,
            self::ICAL_METHOD_CANCEL,
            self::ICAL_METHOD_REFRESH,
            self::ICAL_METHOD_COUNTER,
            self::ICAL_METHOD_DECLINECOUNTER,
        ];
        protected static $_LE = self::CRLF;
        protected $_all_recipients = [];
        protected $_bcc = [];
        protected $_cc = [];
        protected $_mailHeader = '';
        protected $_MIMEBody = '';
        protected $_MIMEHeader = '';
        protected $_oauth;
        protected $_RecipientsQueue = [];
        protected $_ReplyTo = [];
        protected $_ReplyToQueue = [];
        protected $_SingleToArray = [];
        protected $_smtp;
        protected $_to = [];
        //alphabetic to here
        protected $_attachment = [];
        protected $_CustomHeader = [];
        protected $_lastMessageID = '';
        protected $_message_type = '';
        protected $_boundary = [];
        protected $_language = [];
        protected $_error_count = 0;
        protected $_sign_cert_file = '';
        protected $_sign_key_file = '';
        protected $_sign_extracerts_file = '';
        protected $_sign_key_pass = '';
        protected $_exceptions = false;
        protected $_uniqueid = '';
        //added
        protected $_altBodyCharSet;
        protected $_altBodyEncoding;
        public $action_function = '';
        public $AllowEmpty = false;
        public $AltBody = '';
        public $AuthType = '';
        public $Body = '';
        public $CharSet = self::CHARSET_ISO88591;
        public $ConfirmReadingTo = '';
        public $ContentType = self::CONTENT_TYPE_PLAINTEXT;
        public $Debugoutput = 'echo';
        public $DKIM_copyHeaderFields = true;
        public $DKIM_domain = '';
        public $DKIM_extraHeaders = [];
        public $DKIM_identity = '';
        public $DKIM_passphrase = '';
        public $DKIM_private = '';
        public $DKIM_private_string = '';
        public $DKIM_selector = '';
        public $do_verp = false;
        public $dsn = '';
        public $Encoding = self::ENCODING_8BIT;
        public $ErrorInfo = '';
        public $From = '';
        public $FromName = '';
        public $Helo = '';
        public $Host = 'localhost';
        public $Hostname = '';
        public $Ical = '';
        public $Mailer = 'mail';
        public $MessageDate = '';
        public $MessageID = '';
        public $Password = '';
        public $Port = 25;
        public $Priority;
        public $Sendmail = '/usr/sbin/sendmail';
        public $Sender = '';
        public $SingleTo = false;
        public $SMTPAuth = false;
        public $SMTPAutoTLS = true;
        public $SMTPDebug = 0;
        public $SMTPKeepAlive = false;
        public $SMTPOptions = [];
        public $SMTPSecure = '';
        public $Subject = '';
        public $Timeout = 300;
        public $Username = '';
        public $UseSendmailOptions = true;
        public $WordWrap = 0;
        public $XMailer = '';
        public static $validator = 'php';
        public static $php_mailer = [
            'authenticate' => 'SMTP Error: Could not authenticate.',
            'buggy_php' => 'Your version of PHP is affected by a bug that may result in corrupted messages.' .
                ' To fix it, switch to sending using SMTP, disable the mail.add_x_header option in' .
                ' your php.ini, switch to MacOS or Linux, or upgrade your PHP to version 7.0.17+ or 7.1.3+.',
            'connect_host' => 'SMTP Error: Could not connect to SMTP host.',
            'data_not_accepted' => 'SMTP Error: data not accepted.',
            'empty_message' => 'Message body empty',
            'encoding' => 'Unknown encoding: ',
            'execute' => 'Could not execute: ',
            'extension_missing' => 'Extension missing: ',
            'file_access' => 'Could not access file: ',
            'file_open' => 'File Error: Could not open file: ',
            'from_failed' => 'The following From address failed: ',
            'instantiate' => 'Could not instantiate mail function.',
            'invalid_address' => 'Invalid address: ',
            'invalid_header' => 'Invalid header name or value',
            'invalid_hostentry' => 'Invalid hostentry: ',
            'invalid_host' => 'Invalid host: ',
            'mailer_not_supported' => ' mailer is not supported.',
            'provide_address' => 'You must provide at least one recipient email address.',
            'recipients_failed' => 'SMTP Error: The following recipients failed: ',
            'signing' => 'Signing Error: ',
            'smtp_code' => 'SMTP code: ',
            'smtp_code_ex' => 'Additional SMTP info: ',
            'smtp_connect_failed' => 'SMTP connect() failed.',
            'smtp_detail' => 'Detail: ',
            'smtp_error' => 'SMTP server error: ',
            'variable_set' => 'Cannot set or reset variable: ',
        ];
        public static $mimes_list = [
            'xl' => 'application/excel',
            'js' => 'application/javascript',
            'hqx' => 'application/mac-binhex40',
            'cpt' => 'application/mac-compactpro',
            'bin' => 'application/macbinary',
            'doc' => 'application/msword',
            'word' => 'application/msword',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'class' => 'application/octet-stream',
            'dll' => 'application/octet-stream',
            'dms' => 'application/octet-stream',
            'exe' => 'application/octet-stream',
            'lha' => 'application/octet-stream',
            'lzh' => 'application/octet-stream',
            'psd' => 'application/octet-stream',
            'sea' => 'application/octet-stream',
            'so' => 'application/octet-stream',
            'oda' => 'application/oda',
            'pdf' => 'application/pdf',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            'smi' => 'application/smil',
            'smil' => 'application/smil',
            'mif' => 'application/vnd.mif',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'wbxml' => 'application/vnd.wap.wbxml',
            'wmlc' => 'application/vnd.wap.wmlc',
            'dcr' => 'application/x-director',
            'dir' => 'application/x-director',
            'dxr' => 'application/x-director',
            'dvi' => 'application/x-dvi',
            'gtar' => 'application/x-gtar',
            'php3' => 'application/x-httpd-php',
            'php4' => 'application/x-httpd-php',
            'php' => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            'phps' => 'application/x-httpd-php-source',
            'swf' => 'application/x-shockwave-flash',
            'sit' => 'application/x-stuffit',
            'tar' => 'application/x-tar',
            'tgz' => 'application/x-tar',
            'xht' => 'application/xhtml+xml',
            'xhtml' => 'application/xhtml+xml',
            'zip' => 'application/zip',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mp2' => 'audio/mpeg',
            'mp3' => 'audio/mpeg',
            'm4a' => 'audio/mp4',
            'mpga' => 'audio/mpeg',
            'aif' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'ram' => 'audio/x-pn-realaudio',
            'rm' => 'audio/x-pn-realaudio',
            'rpm' => 'audio/x-pn-realaudio-plugin',
            'ra' => 'audio/x-realaudio',
            'wav' => 'audio/x-wav',
            'mka' => 'audio/x-matroska',
            'bmp' => 'image/bmp',
            'gif' => 'image/gif',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'webp' => 'image/webp',
            'avif' => 'image/avif',
            'heif' => 'image/heif',
            'heifs' => 'image/heif-sequence',
            'heic' => 'image/heic',
            'heics' => 'image/heic-sequence',
            'eml' => 'message/rfc822',
            'css' => 'text/css',
            'html' => 'text/html',
            'htm' => 'text/html',
            'shtml' => 'text/html',
            'log' => 'text/plain',
            'text' => 'text/plain',
            'txt' => 'text/plain',
            'rtx' => 'text/richtext',
            'rtf' => 'text/rtf',
            'vcf' => 'text/vcard',
            'vcard' => 'text/vcard',
            'ics' => 'text/calendar',
            'xml' => 'text/xml',
            'xsl' => 'text/xml',
            'wmv' => 'video/x-ms-wmv',
            'mpeg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mp4' => 'video/mp4',
            'm4v' => 'video/mp4',
            'mov' => 'video/quicktime',
            'qt' => 'video/quicktime',
            'rv' => 'video/vnd.rn-realvideo',
            'avi' => 'video/x-msvideo',
            'movie' => 'video/x-sgi-movie',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
        ];
    }
}else die;