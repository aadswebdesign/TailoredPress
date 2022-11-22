### TP_Core/Libs/PHP/Mailer

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md

**Files/ClassMethods and Vars:**  
- MailerException.php: 	
	* errorMessage():string 

- OAuth.php: 	
	* protected $_provider, $_oauthToken, $_oauthUserEmail, $_oauthClientSecret, $_oauthClientId, $_oauthRefreshToken 
	* __construct($options) 
	* _getGrant(): RefreshToken 
	* _getToken() 
	* getOauth64():string 

- OAuthTokenProvider.php: 	
	* getOauth64(); 

- PHPMailer.php: 	
	* public const CHARSET_ASCII, CHARSET_ISO88591, CHARSET_UTF8, const CONTENT_TYPE_PLAINTEXT,CONTENT_TYPE_TEXT_CALENDAR, 
	* public const CONTENT_TYPE_TEXT_HTML, CONTENT_TYPE_MULTIPART_ALTERNATIVE, CONTENT_TYPE_MULTIPART_MIXED 
	* public const CONTENT_TYPE_MULTIPART_RELATED, ENCODING_7BIT, ENCODING_8BIT, ENCODING_BASE64, ENCODING_BINARY 
	* public const ENCODING_QUOTED_PRINTABLE, ENCRYPTION_STARTTLS, ENCRYPTION_SMTPS, ICAL_METHOD_REQUEST, ICAL_METHOD_PUBLISH 
	* public const ICAL_METHOD_REPLY, ICAL_METHOD_ADD, ICAL_METHOD_CANCEL, ICAL_METHOD_REFRESH, ICAL_METHOD_COUNTER 
	* public const ICAL_METHOD_DECLINECOUNTER, VERSION, STOP_MESSAGE, STOP_CONTINUE, STOP_CRITICAL 
	* public const CRLF,FWS,MAIL_MAX_LINE_LENGTH, MAX_LINE_LENGTH, STD_LINE_LENGTH 
	* __construct($exceptions = null) 
	* __destruct() 
	* isHTML($isHtml = true): void 
	* isSMTP(): void 
	* isMail(): void 
	* isSendmail(): void 
	* isQmail(): void 
	* addAddress($address, $name = '') 
	* addCC($address, $name = '') 
	* addBCC($address, $name = '') 
	* addReplyTo($address, $name = '') 
	* setFrom($address, $name = '', $auto = true): bool 
	* getLastMessageID(): string 
	* punyEncodeAddress($address): string 
	* send() 
	* preSend(): ?bool 
	* postSend() 
	* getSMTPInstance(): SMTP 
	* setSMTPInstance(SMTP $smtp): SMTP 
	* smtpConnect($options = null): bool 
	* smtpClose(): void 
	* setLanguage($lang_code = 'en', $lang_path = ''): bool 
	* getTranslations(): array 
	* addrAppend($type, $addr): string 
	* addrFormat($addr): string 
	* wrapText($message, $length, $qp_mode = false) 
	* utf8CharBoundary($encodedText, $maxLength): int 
	* setWordWrap(): void 
	* createHeader(): string 
	* getMailMIME(): string 
	* getSentMIMEMessage(): string 
	* createBody(): string 
	* headerLine($name, $value): string 
	* textLine($value): string 
	* addAttachment($path,$name = '',$encoding = self::ENCODING_BASE64,$type = '',$disposition = 'attachment'): bool 
	* getAttachments(): array 
	* encodeString($str, $encoding = self::ENCODING_BASE64) 
	* encodeHeader($str, $position = 'text'): string 
	* hasMultiBytes($str): bool 
	* has8bitChars($text): bool 
	* base64EncodeWrapMB($str, $linebreak = null): string 
	* encodeQP($string) 
	* encodeQ($str, $position = 'text') 
	* addStringAttachment($string,$filename,$encoding = self::ENCODING_BASE64,$type = '',$disposition = 'attachment'): bool 
	* addEmbeddedImage($path,$cid,$name = '',$encoding = self::ENCODING_BASE64,$type = '',$disposition = 'inline'): bool 
	* addStringEmbeddedImage($string,$cid,$name = '',$encoding = self::ENCODING_BASE64,$type = '',$disposition = 'inline'): bool 
	* inlineImageExists(): bool 
	* attachmentExists(): bool 
	* alternativeExists(): bool 
	* clearQueuedAddresses($kind): void 
	* clearAddresses(): void 
	* clearCCs(): void 
	* clearBCCs(): void 
	* clearReplyTos(): void 
	* clearAllRecipients(): void 
	* clearAttachments(): void 
	* clearCustomHeaders(): void 
	* isError(): bool 
	* addCustomHeader($name, $value = null): bool 
	* getCustomHeaders(): array 
	* msgHTML($message, $basedir = '', $advanced = false) 
	* html2text($html, $advanced = false ?: '') 
	* set($name, $value = ''): bool 
	* secureHeader($str): string 
	* sign($cert_filename, $key_filename, $key_pass, $extra_certs_filename = ''): void 
	* DKIM_QP($txt): string 
	* DKIM_Sign($signHeader): string 
	* DKIM_HeaderC($signHeader): string 
	* DKIM_BodyC($body): string 
	* DKIM_Add($headers_line, $subject, $body) 
	* getToAddresses(): array 
	* getCcAddresses(): array 
	* getBccAddresses(): array 
	* getReplyToAddresses(): array 
	* getAllRecipientAddresses(): array 
	* getOAuth() 
	* setOAuth(OAuthTokenProvider $oauth): void 
	* parseAddresses($addrstr, $useimap = true, $charset = self::CHARSET_ISO88591): array static
	* validateAddress($address, $pattern_select = null): ?bool static
	* idnSupported(): bool static
	* rfcDate() static
	* isValidHost($host): bool static
	* _mime_types($ext = ''): string static
	* filenameToType($filename): string static 
	* mb_path_info($path, $options = null) static 
	* normalizeBreaks($text, $break_type = null) static 
	* stripTrailingWSP($text): string static 
	* getLE static 
	* hasLineLongerThanMax($str): bool static 
	* quotedString($str): string 
	* _emailDebug($str): void 
	* _addOrEnqueueAnAddress($kind, $address, $name) 
	* _addAnAddress($kind, $address, $name = ''): bool 
	* _sendmailSend($header, $body): bool 
	* _mailSend($header, $body): bool 
	* _smtpSend($header, $body): bool 
	* _generateId() 
	* _getBoundary($boundary, $charSet, $contentType, $encoding): string 
	* _endBoundary($boundary): string 
	* _setMessageType(): void 
	* _attachAll($disposition_type, $boundary): string 
	* _encodeFile($path, $encoding = self::ENCODING_BASE64) 
	* _validateEncoding($encoding): bool 
	* _cidExists($cid): bool 
	* _setError($msg): void 
	* _serverHostname() 
	* _lang($key): string 
	* _doCallback($isSent, $to, $cc, $bcc, $subject, $body, $from, $extra): void 
	* _isShellSafe($string): bool static 
	* _isPermittedPath($path): bool static
	* _fileIsAccessible($path): bool static 
	* _setLE($le): void static
	* __mailPassthru($to, $subject, $body, $header, $params): bool
	* __getSmtpErrorMessage($base_key): string 

- SMTP.php: 	
	* public const VERSION, LE, DEFAULT_PORT, MAX_LINE_LENGTH, MAX_REPLY_LENGTH
	* public const DEBUG_OFF, DEBUG_CLIENT, DEBUG_SERVER, DEBUG_CONNECTION, DEBUG_LOW_LEVEL 
	* protected $_error, $_hello_reply, $_last_reply, $_last_smtp_transaction_id, $_server_caps, $_smtp_conn, $_smtp_transaction_id_patterns 
	* public $do_debug, $debug_output, $do_ver_p, $time_out, $time_limit
	* connect($host, $port = null, $timeout = 30, $options = []): bool 
	* startTLS(): bool 
	* authenticate($username,$password,$auth_type = null,$OAuth = null): bool 
	* connected(): bool 
	* close(): void 
	* data($msg_data): bool 
	* hello($host = ''): bool 
	* mail($from): bool 
	* quit($close_on_error = true): bool 
	* recipient($address, $dsn = ''): bool 
	* reset(): bool 
	* sendAndMail($from): bool 
	* verify($name): bool 
	* noop(): bool 
	* turn(): bool 
	* client_send($data, $command = ''): int 
	* getError(): array 
	* getServerExtList() 
	* getServerExt($name) 
	* getLastReply(): string 
	* setVer_p($enabled = false): void 
	* getVer_p(): bool 
	* setDebugOutput($method = 'echo'): void 
	* getDebugOutput(): string 
	* setDebugLevel($level = 0): void 
	* getDebugLevel(): int 
	* setTimeout($timeout = 0): void 
	* getTimeout(): int 
	* getLastTransactionID() 
	* _getSMTPConnection($host, $port = null, $timeout = 30, $options = []) 
	* _email_debug($str, $level = 0): void 
	* _hmac($data, $key) 
	* _sendHello($hello, $host): bool 
	* _parseHelloFields($type): void 
	* _sendCommand($command, $command_string, $expect): bool 
	* _get_lines(): string 
	* _setError($message, $detail = '', $smtp_code = '', $smtp_code_ex = ''): void 
	* _errorHandler($err_no, $err_msg, $err_file = '', $err_line = 0): void 
	* _recordLastTransactionID() 
