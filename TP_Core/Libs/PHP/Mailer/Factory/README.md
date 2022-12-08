### TP_Core/Libs/PHP/Mailer/Factory

**Note:** For what it is now and subject to change. 

**Files:** 
- README.md
- FakeFunctions.php //function idn_to_ascii(string $domain,int $flags = IDNA_DEFAULT,int $variant = INTL_IDNA_VARIANT_UTS46, array &$idna_info = null)
**Files/ClassMethods and Vars:**  
- mailer_vars.php: 	
	- protected static $_IcalMethods,$_LE 
	- protected $_all_recipients, $_bcc, $_cc, $_mailHeader, $_MIMEBody, $_MIMEHeader 
	- protected $_oauth, $_RecipientsQueue, $_ReplyTo, $_ReplyToQueue, $_SingleToArray, $_smtp, $_to
	- protected $_attachment, $_CustomHeader, $_lastMessageID, $_message_type, $_boundary, $_language
	- protected $_error_count, $_sign_cert_file, $_sign_key_file, $_sign_extracerts_file, $_sign_key_pass 
	- protected $_exceptions, $_uniqueid, $_altBodyCharSet, $_altBodyEncoding 
	- public $action_function, $AllowEmpty, $AltBody, $AuthType, $Body, $CharSet,$ConfirmReadingTo  
	- public $ContentType, $Debugoutput, $DKIM_copyHeaderFields, $DKIM_domain,$DKIM_extraHeaders
	- public $DKIM_identity, $DKIM_passphrase, $DKIM_private, $DKIM_private_string, $DKIM_selector, $do_verp, $dsn 
	- public $Encoding, $ErrorInfo, $From , $FromName, $Helo, $Host $Hostname, $Ical, $Mailer
	- public $MessageDate, $MessageID, $Password, $Port, $Priority, $Sendmail, $Sender, $SingleTo 
	- public $SMTPAuth, $SMTPAutoTLS, $SMTPDebug, $SMTPKeepAlive, $SMTPOptions, $SMTPSecure 
	- public $Subject, $Timeout, $Username, $UseSendmailOptions, $WordWrap, $XMailer
	- public static $validator, static $php_mailer,$mimes_list 
