<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 24-4-2022
 * Time: 16:47
 */
namespace TP_Core\Libs\PHP\Mailer;
use TP_Core\Libs\PHP\Libs\Psr\Log\LoggerInterface;
if(ABSPATH){
    class SMTP{
        public const VERSION = '6.6.0';
        public const LE = "\r\n";
        public const DEFAULT_PORT = 25;
        public const MAX_LINE_LENGTH = 998;
        public const MAX_REPLY_LENGTH = 512;
        public const DEBUG_OFF = 0;
        public const DEBUG_CLIENT = 1;
        public const DEBUG_SERVER = 2;
        public const DEBUG_CONNECTION = 3;
        public const DEBUG_LOW_LEVEL = 4;
        protected $_error = [
            'error' => '',
            'detail' => '',
            'smtp_code' => '',
            'smtp_code_ex' => '',
        ];
        protected $_hello_reply;
        protected $_last_reply = '';
        protected $_last_smtp_transaction_id;
        protected $_server_caps;
        protected $_smtp_conn;
        protected $_smtp_transaction_id_patterns = [
            'exim' => '/[\d]{3} OK id=(.*)/',
            'sendmail' => '/[\d]{3} 2.0.0 (.*) Message/',
            'postfix' => '/[\d]{3} 2.0.0 Ok: queued as (.*)/',
            'Microsoft_ESMTP' => '/[0-9]{3} 2.[\d].0 (.*)@(?:.*) Queued mail for delivery/',
            'Amazon_SES' => '/[\d]{3} Ok (.*)/',
            'SendGrid' => '/[\d]{3} Ok: queued as (.*)/',
            'CampaignMonitor' => '/[\d]{3} 2.0.0 OK:([a-zA-Z\d]{48})/',
            'Haraka' => '/[\d]{3} Message Queued \((.*)\)/',
            'Mailjet' => '/[\d]{3} OK queued as (.*)/',
        ];
        public $do_debug = self::DEBUG_OFF;
        public $debug_output = 'echo';
        public $do_ver_p = false;
        public $time_out = 300;
        public $time_limit = 300;
        public function connect($host, $port = null, $timeout = 30, $options = []): bool
        {
            $this->_setError('');
            if ($this->connected()) {
                $this->_setError('Already connected to a server');
                return false;
            }
            if (empty($port)) $port = self::DEFAULT_PORT;
            $this->_email_debug(
                "Connection: opening to $host:$port, timeout=$timeout, options=" .
                (count($options) > 0 ? var_export($options, true) : 'array()'),
                self::DEBUG_CONNECTION
            );
            $this->_smtp_conn = $this->_getSMTPConnection($host, $port, $timeout, $options);
            if ($this->_smtp_conn === false) return false;
            $this->_email_debug('Connection: opened', self::DEBUG_CONNECTION);
            $this->_last_reply = $this->_get_lines();
            $this->_email_debug('SERVER -> CLIENT: ' . $this->_last_reply, self::DEBUG_SERVER);
            $responseCode = (int)substr($this->_last_reply, 0, 3);
            if ($responseCode === 220) return true;
            if ($responseCode === 554) $this->quit();
            $this->_email_debug('Connection: closing due to error', self::DEBUG_CONNECTION);
            $this->close();
            return false;
        }//315
        public function startTLS(): bool
        {
            if (!$this->_sendCommand('STARTTLS', 'STARTTLS', 220)) return false;
            $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                $crypto_method |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
            }
            set_error_handler([$this, 'errorHandler']);
            $crypto_ok = stream_socket_enable_crypto($this->_smtp_conn,true, $crypto_method);
            restore_error_handler();
            return (bool) $crypto_ok;
        }//449
        public function authenticate($username,$password,$auth_type = null,$OAuth = null): bool
        {
            if (!$this->_server_caps) {
                $this->_setError('Authentication is not allowed before HELO/EHLO');
                return false;
            }
            if (array_key_exists('EHLO', $this->_server_caps)) {
               if (!array_key_exists('AUTH', $this->_server_caps)) {
                    $this->_setError('Authentication is not allowed at this stage');
                    return false;
                }
                $this->_email_debug('Auth method requested: ' . ($auth_type ?: 'UNSPECIFIED'), self::DEBUG_LOW_LEVEL);
                $this->_email_debug(
                    'Auth methods available on the server: ' . implode(',', $this->_server_caps['AUTH']),
                    self::DEBUG_LOW_LEVEL
                );
                if (null !== $auth_type && !in_array($auth_type, $this->_server_caps['AUTH'], true)) {
                    $this->_email_debug('Requested auth method not available: ' . $auth_type, self::DEBUG_LOW_LEVEL);
                    $auth_type = null;
                }
                if (empty($auth_type)) {
                    foreach (['CRAM-MD5', 'LOGIN', 'PLAIN', 'XOAUTH2'] as $method) {
                        if (in_array($method, $this->_server_caps['AUTH'], true)) {
                            $auth_type = $method;
                            break;
                        }
                    }
                    if (empty($auth_type)) {
                        $this->_setError('No supported authentication methods found');
                        return false;
                    }
                    $this->_email_debug('Auth method selected: ' . $auth_type, self::DEBUG_LOW_LEVEL);
                }
                if (!in_array($auth_type, $this->_server_caps['AUTH'], true)) {
                    $this->_setError("The requested authentication method \"$auth_type\" is not supported by the server");
                    return false;
                }
            } elseif (empty($auth_type))$auth_type = 'LOGIN';
            switch ($auth_type) {
                case 'PLAIN':
                    if (!$this->_sendCommand('AUTH', 'AUTH PLAIN', 334))
                        return false;
                    if (!$this->_sendCommand('User & Password',base64_encode("\0" . $username . "\0" . $password),235))
                        return false;
                    break;
                case 'LOGIN':
                    //Start authentication
                    if (!$this->_sendCommand('AUTH', 'AUTH LOGIN', 334))
                        return false;
                    if (!$this->_sendCommand('Username', base64_encode($username), 334))
                        return false;
                    if (!$this->_sendCommand('Password', base64_encode($password), 235))
                        return false;
                    break;
                case 'CRAM-MD5':
                    //Start authentication
                    if (!$this->_sendCommand('AUTH CRAM-MD5', 'AUTH CRAM-MD5', 334)) {
                        return false;
                    }
                    $challenge = base64_decode(substr($this->_last_reply, 4));
                    $response = $username . ' ' . $this->_hmac($challenge, $password);
                    return $this->_sendCommand('Username', base64_encode($response), 235);
                case 'XOAUTH2':
                    //The OAuth instance must be set up prior to requesting auth.
                    if (null === $OAuth) return false;
                    $oauth = null;
                    if($OAuth instanceof OAuth){
                        $oauth = $OAuth->getOauth64();
                    }
                    if (!$this->_sendCommand('AUTH', 'AUTH XOAUTH2 ' . $oauth, 235)) return false;
                    break;
                default:
                    $this->_setError("Authentication method \"$auth_type\" is not supported");
                    return false;
            }
            return true;
        }//490
        public function connected(): bool
        {
            if (is_resource($this->_smtp_conn)) {
                $sock_status = stream_get_meta_data($this->_smtp_conn);
                if ($sock_status['eof']) {
                    $this->_email_debug(
                        'SMTP NOTICE: EOF caught while checking if connected',
                        self::DEBUG_CLIENT
                    );
                    $this->close();
                    return false;
                }
                return true; //everything looks good
            }
            return false;
        }//656
        public function close(): void
        {
            $this->_setError('');
            $this->_server_caps = null;
            $this->_hello_reply = null;
            if (is_resource($this->_smtp_conn)) {
                //Close the connection and cleanup
                fclose($this->_smtp_conn);
                $this->_smtp_conn = null; //Makes for cleaner serialization
                $this->_email_debug('Connection: closed', self::DEBUG_CONNECTION);
            }
        }//683
        public function data($msg_data): bool
        {
            if (!$this->_sendCommand('DATA', 'DATA', 354)) return false;
            $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $msg_data));
            $field = substr($lines[0], 0, strpos($lines[0], ':'));
            $in_headers = false;
            if (!empty($field) && strpos($field, ' ') === false) $in_headers = true;
            foreach ($lines as $line) {
                $lines_out = [];
                if ($in_headers && $line === '')$in_headers = false;
                while (isset($line[self::MAX_LINE_LENGTH])) {
                    $pos = strrpos(substr($line, 0, self::MAX_LINE_LENGTH), ' ');
                    if (!$pos) {
                        $pos = self::MAX_LINE_LENGTH - 1;
                        $lines_out[] = substr($line, 0, $pos);
                        $line = substr($line, $pos);
                    } else {
                        $lines_out[] = substr($line, 0, $pos);
                        $line = substr($line, $pos + 1);
                    }
                    if ($in_headers) $line = "\t" . $line;
                }
                $lines_out[] = $line;
                foreach ($lines_out as $line_out) {
                    if (!empty($line_out) && $line_out[0] === '.')
                        $line_out = '.' . $line_out;
                    $this->client_send($line_out . static::LE, 'DATA');
                }
            }
            $save_time_limit = $this->time_limit;
            $this->time_limit *= 2;
            $result = $this->_sendCommand('DATA END', '.', 250);
            $this->_recordLastTransactionID();
            $this->time_limit = $save_time_limit;
            return $result;
        }//709
        public function hello($host = ''): bool
        {
            if ($this->_sendHello('EHLO', $host)) return true;
            //Some servers shut down the SMTP service here (RFC 5321)
            if (strpos($this->_hello_reply,'421') === 0) return false;
            return $this->_sendHello('HELLO', $host);
        }//802
        public function mail($from): bool
        {
            $useVerp = ($this->do_ver_p ? ' XVERP' : '');
            return $this->_sendCommand('MAIL FROM','MAIL FROM:<' . $from . '>' . $useVerp,250);
        }//895
        public function quit($close_on_error = true): bool
        {
            $no_error = $this->_sendCommand('QUIT', 'QUIT', 221);
            $err = $this->_error; //Save any error
            if ($no_error || $close_on_error) {
                $this->close();
                $this->_error = $err; //Restore any error from the quit command
            }
            return $no_error;
        }//915
        public function recipient($address, $dsn = ''): bool
        {
            if (empty($dsn)) $rcpt = 'RCPT TO:<' . $address . '>';
            else {
                $dsn = strtoupper($dsn);
                $notify = [];
                if (strpos($dsn, 'NEVER') !== false) $notify[] = 'NEVER';
                else {
                    foreach (['SUCCESS', 'FAILURE', 'DELAY'] as $value) {
                        if (strpos($dsn, $value) !== false) $notify[] = $value;
                    }
                }
                $rcpt = 'RCPT TO:<' . $address . '> NOTIFY=' . implode(',', $notify);
            }
            return $this->_sendCommand('RCPT TO',$rcpt,[250, 251]);
        }//939
        public function reset(): bool
        {
            return $this->_sendCommand('RSET', 'RSET', 250);
        }//974
        public function sendAndMail($from): bool
        {
            return $this->_sendCommand('SAML', "SAML FROM:$from", 250);
        }//1059
        public function verify($name): bool
        {
            return $this->_sendCommand('VRFY', "VRFY $name", [250, 251]);
        }//1071
        public function noop(): bool
        {
            return $this->_sendCommand('NOOP', 'NOOP', 250);
        }//1082
        public function turn(): bool
        {
            $this->_setError('The SMTP TURN command is not implemented');
            $this->_email_debug('SMTP NOTICE: ' . $this->_error['error'], self::DEBUG_CLIENT);
            return false;
        }//1096
        public function client_send($data, $command = ''): int
        {
            if (
                self::DEBUG_LOW_LEVEL > $this->do_debug &&
                in_array($command, ['User & Password', 'Username', 'Password'], true)
            ) $this->_email_debug('CLIENT -> SERVER: [credentials hidden]', self::DEBUG_CLIENT);
            else $this->_email_debug('CLIENT -> SERVER: ' . $data, self::DEBUG_CLIENT);
            set_error_handler([$this, 'errorHandler']);
            $result = fwrite($this->_smtp_conn, $data);
            restore_error_handler();
            return $result;
        }//1112
        public function getError(): array
        {
            return $this->_error;
        }//1136
        public function getServerExtList(){
            return $this->_server_caps;
        }//1146
        public function getServerExt($name){
            if (!$this->_server_caps) {
                $this->_setError('No HELO/EHLO was sent');
                return null;
            }
            if (!array_key_exists($name, $this->_server_caps)) {
                if ('HELO' === $name) return $this->_server_caps['EHLO'];
                if ('EHLO' === $name || array_key_exists('EHLO', $this->_server_caps))
                    return false;
                $this->_setError('HELO handshake was used; No information about server extensions available');
                return null;
            }
            return $this->_server_caps[$name];
        }//1168
        public function getLastReply(): string
        {
            return $this->_last_reply;
        }//1196
        public function setVer_p($enabled = false): void
        {
            $this->do_ver_p = $enabled;
        }//1299
        public function getVer_p(): bool
        {
            return $this->do_ver_p;
        }//1309
        public function setDebugOutput($method = 'echo'): void
        {
            $this->debug_output = $method;
        }//1337
        public function getDebugOutput(): string
        {
            return $this->debug_output;
        }//1347
        public function setDebugLevel($level = 0): void
        {
            $this->do_debug = $level;
        }//1357
        public function getDebugLevel(): int
        {
            return $this->do_debug;
        }//1367
        public function setTimeout($timeout = 0): void
        {
            $this->time_out = $timeout;
        }//1377
        public function getTimeout(): int
        {
            return $this->time_out;
        }//1387
        public function getLastTransactionID(){
            return $this->_last_smtp_transaction_id;
        }//1452
        protected function _getSMTPConnection($host, $port = null, $timeout = 30, $options = []){
            static $stream_ok;
            if (null === $stream_ok) $stream_ok = function_exists('stream_socket_client');
            $err_no = 0;
            $err_str = '';
            if ($stream_ok) {
                $socket_context = stream_context_create($options);
                set_error_handler([$this, 'errorHandler']);
                $connection = stream_socket_client($host . ':' . $port,$err_no,$err_str,$timeout,STREAM_CLIENT_CONNECT,$socket_context);
            } else {
                $this->_email_debug(
                    'Connection: stream_socket_client not available, falling back to fsockopen',
                    self::DEBUG_CONNECTION
                );
                set_error_handler([$this, 'errorHandler']);
                $connection = fsockopen($host,$port,$err_no,$err_str,$timeout);
            }
            restore_error_handler();
            if (!is_resource($connection)) {
                $this->_setError('Failed to connect to server','',(string) $err_no,$err_str);
                $this->_email_debug('SMTP ERROR: ' . $this->_error['error']. ": $err_str ($err_no)",self::DEBUG_CLIENT);
                return false;
            }
            if (strpos(PHP_OS, 'WIN') !== 0) {
                $max = (int)ini_get('max_execution_time');
                if (0 !== $max && $timeout > $max && strpos(ini_get('disable_functions'), 'set_time_limit') === false)
                    @set_time_limit($timeout);
                stream_set_timeout($connection, $timeout, 0);
            }
            return $connection;
        }//374
        protected function _email_debug($str, $level = 0): void
        {
            if ($level > $this->do_debug) return;
            if ($this->debug_output instanceof LoggerInterface) {
                $this->debug_output->debug($str);
                return;
            }
            if (is_callable($this->debug_output) && !in_array($this->debug_output, ['error_log', 'html', 'echo'])) {
                call_user_func($this->debug_output, $str, $level);
                return;
            }
            switch ($this->debug_output) {
                case 'error_log':
                    /** @noinspection ForgottenDebugOutputInspection *///todo
                    error_log($str);
                    break;
                case 'html':
                    echo gmdate('Y-m-d H:i:s'), ' ', htmlentities(
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
        }
        protected function _hmac($data, $key){
            if (function_exists('hash_hmac')) return hash_hmac('md5', $data, $key);
            $byte_length = 64; //byte length for md5
            if (strlen($key) > $byte_length) $key = pack('H*', md5($key));
            $key = str_pad($key, $byte_length, chr(0x00));
            $ipad = str_pad('', $byte_length, chr(0x36));
            $opad = str_pad('', $byte_length, chr(0x5c));
            $k_ipad = $key ^ $ipad;
            $k_opad = $key ^ $opad;
            return md5($k_opad . pack('H*', md5($k_ipad . $data)));
        }//624
        protected function _sendHello($hello, $host): bool
        {
            $no_error = $this->_sendCommand($hello, $hello . ' ' . $host, 250);
            $this->_hello_reply = $this->_last_reply;
            if ($no_error) $this->_parseHelloFields($hello);
            else $this->_server_caps = null;
            return $no_error;
        }//828
        protected function _parseHelloFields($type): void
        {
            $this->_server_caps = [];
            $lines = explode("\n", $this->_hello_reply);
            foreach ($lines as $n => $s) {
                //First 4 chars contain response code followed by - or space
                $s = trim(substr($s, 4));
                if (empty($s)) continue;
                $fields = explode(' ', $s);
                if (!empty($fields)) {
                    if (!$n) {
                        $name = $type;
                        $fields = $fields[0];
                    } else {
                        $name = array_shift($fields);
                        switch ($name) {
                            case 'SIZE':
                                $fields = ($fields ? $fields[0] : 0);
                                break;
                            case 'AUTH':
                                if (!is_array($fields)) $fields = [];
                                break;
                            default:
                                $fields = true;
                        }
                    }
                    $this->_server_caps[$name] = $fields;
                }
            }
        }//847
        protected function _sendCommand($command, $command_string, $expect): bool
        {
            if (!$this->connected()) {
                $this->_setError("Called $command without being connected");
                return false;
            }
            if ((strpos($command_string, "\n") !== false) || (strpos($command_string, "\r") !== false)) {
                $this->_setError("Command '$command' contained line breaks");
                return false;
            }
            $this->client_send($command_string . static::LE, $command);
            $this->_last_reply = $this->_get_lines();
            $matches = [];
            if (preg_match('/^([\d]{3})[ -](?:([\d]\\.[\d]\\.[\d]{1,2}) )?/', $this->_last_reply, $matches)) {
                $code = (int) $matches[1];
                $code_ex = (count($matches) > 2 ? $matches[2] : null);
                $detail = preg_replace(
                    "/{$code}[ -]" .
                    ($code_ex ? str_replace('.', '\\.', $code_ex) . ' ' : '') . '/m',
                    '',
                    $this->_last_reply
                );
            } else {
                $code = (int) substr($this->_last_reply, 0, 3);
                $code_ex = null;
                $detail = substr($this->_last_reply, 4);
            }
            $this->_email_debug('SERVER -> CLIENT: ' . $this->_last_reply, self::DEBUG_SERVER);
            if (!in_array($code, (array) $expect, true)) {
                $this->_setError("$command command failed",$detail,$code,$code_ex);
                $this->_email_debug(
                    'SMTP ERROR: ' . $this->_error['error'] . ': ' . $this->_last_reply,
                    self::DEBUG_CLIENT
                );
                return false;
            }
            $this->_setError('');
            return true;
        }//988
        protected function _get_lines(): string
        {
            if (!is_resource($this->_smtp_conn)) return '';
            $data = '';
            $endtime = 0;
            stream_set_timeout($this->_smtp_conn, $this->time_out);
            if ($this->time_limit > 0) $endtime = time() + $this->time_limit;
            $selR = [$this->_smtp_conn];
            $selW = null;
            while (is_resource($this->_smtp_conn) && !feof($this->_smtp_conn)) {
                set_error_handler([$this, 'errorHandler']);
                $n = stream_select($selR, $selW, $selW, $this->time_limit);
                restore_error_handler();
                if ($n === false) {
                    $message = $this->getError()['detail'];
                    $this->_email_debug(
                        'SMTP -> get_lines(): select failed (' . $message . ')',
                        self::DEBUG_LOW_LEVEL
                    );
                    if (stripos($message, 'interrupted system call') !== false) {
                        $this->_email_debug(
                            'SMTP -> get_lines(): retrying stream_select',
                            self::DEBUG_LOW_LEVEL
                        );
                        $this->_setError('');
                        continue;
                    }
                    break;
                }
                if (!$n) {
                    $this->_email_debug(
                        'SMTP -> get_lines(): select timed-out in (' . $this->time_limit . ' sec)',
                        self::DEBUG_LOW_LEVEL
                    );
                    break;
                }
                $str = @fgets($this->_smtp_conn, self::MAX_REPLY_LENGTH);
                $this->_email_debug('SMTP INBOUND: "' . trim($str) . '"', self::DEBUG_LOW_LEVEL);
                $data .= $str;
                if (!isset($str[3]) || $str[3] === ' ' || $str[3] === "\r" || $str[3] === "\n") {
                    break;
                }
                $info = stream_get_meta_data($this->_smtp_conn);
                if ($info['timed_out']) {
                    $this->_email_debug(
                        'SMTP -> get_lines(): stream timed-out (' . $this->time_out . ' sec)',
                        self::DEBUG_LOW_LEVEL
                    );
                    break;
                }
                if ($endtime && time() > $endtime) {
                    $this->_email_debug(
                        'SMTP -> get_lines(): timelimit reached (' .
                        $this->time_limit . ' sec)',
                        self::DEBUG_LOW_LEVEL
                    );
                    break;
                }
            }
            return $data;
        }//1210
        protected function _setError($message, $detail = '', $smtp_code = '', $smtp_code_ex = ''): void
        {
            $this->_error = [
                'error' => $message,
                'detail' => $detail,
                'smtp_code' => $smtp_code,
                'smtp_code_ex' => $smtp_code_ex,
            ];
        }//1322
        protected function _errorHandler($err_no, $err_msg, $err_file = '', $err_line = 0): void
        {
            $notice = 'Connection failed.';
            $this->_setError($notice,$err_msg,(string) $err_no);
            $this->_email_debug(
                "$notice Error #$err_no: $err_msg [$err_file line $err_line]",
                self::DEBUG_CONNECTION
            );
        }//1400
        protected function _recordLastTransactionID(){
            $reply = $this->getLastReply();
            if (empty($reply)) $this->_last_smtp_transaction_id = null;
            else {
                $this->_last_smtp_transaction_id = false;
                foreach ($this->_smtp_transaction_id_patterns as $smtp_transaction_id_pattern) {
                    $matches = [];
                    if (preg_match($smtp_transaction_id_pattern, $reply, $matches)) {
                        $this->_last_smtp_transaction_id = trim($matches[1]);
                        break;
                    }
                }
            }
            return $this->_last_smtp_transaction_id;
        }//1423
    }
}else die;