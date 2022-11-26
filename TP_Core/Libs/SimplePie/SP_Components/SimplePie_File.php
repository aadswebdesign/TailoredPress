<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 13:18
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\_encodings;
use TP_Core\Libs\SimplePie\Depedencies\idna_convert;
if(ABSPATH){
    class SimplePie_File{
        use _sp_vars;
        use _encodings;
        public function __construct($url, $timeout = 10, $redirects = 5, $headers = null, $useragent = null, $force_fsockopen = false, $curl_options = []){
            if (class_exists(idna_convert::class)){
                $idn = new idna_convert();
                $parsed = $this->sp_parse_url($url);
                $url = $this->sp_compress_parse_url($parsed['scheme'], $idn->encode($parsed['authority']), $parsed['path'], $parsed['query'], NULL);
            }
            $this->sp_url = $url;
            $this->sp_permanent_url = $url;
            $this->sp_useragent = $useragent;
            if (preg_match('/^http(s)?:\/\//i', $url)){
                if ($useragent === null){
                    $useragent = ini_get('user_agent');
                    $this->sp_useragent = $useragent;
                }
                if (!is_array($headers)) $headers = [];
                if (!$force_fsockopen && function_exists('curl_exec')){
                    $this->sp_method = SP_FILE_SOURCE_REMOTE | SP_FILE_SOURCE_CURL;
                    $fp = curl_init();
                    $headers2 = [];
                    foreach ($headers as $key => $value) $headers2[] = "$key: $value";
                    if (version_compare($this->sp_get_curl_version(), '7.10.5', '>='))
                        curl_setopt($fp, CURLOPT_ENCODING, '');
                    curl_setopt($fp, CURLOPT_URL, $url);
                    curl_setopt($fp, CURLOPT_HEADER, 1);
                    curl_setopt($fp, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($fp, CURLOPT_FAILONERROR, 1);
                    curl_setopt($fp, CURLOPT_TIMEOUT, $timeout);
                    curl_setopt($fp, CURLOPT_CONNECTTIMEOUT, $timeout);
                    curl_setopt($fp, CURLOPT_REFERER, $this->sp_url_remove_credentials($url));
                    curl_setopt($fp, CURLOPT_USERAGENT, $useragent);
                    curl_setopt($fp, CURLOPT_HTTPHEADER, $headers2);
                    foreach ($curl_options as $curl_param => $curl_value)
                        curl_setopt($fp, $curl_param, $curl_value);
                    $this->sp_headers = curl_exec($fp);
                    if (curl_errno($fp) === 23 || curl_errno($fp) === 61){
                        curl_setopt($fp, CURLOPT_ENCODING, 'none');
                        $this->sp_headers = curl_exec($fp);
                    }
                    $this->sp_status_code = curl_getinfo($fp, CURLINFO_HTTP_CODE);
                    if (curl_errno($fp)) {
                        $this->sp_error = 'cURL error ' . curl_errno($fp) . ': ' . curl_error($fp);
                        $this->sp_success = false;
                    }else{
                        if ($info = curl_getinfo($fp))  $this->sp_url = $info['url'];
                        curl_close($fp);
                        $this->sp_headers = $this->prepareHeaders($this->sp_headers, $info['redirect_count'] + 1);
                        $parser = new SimplePie_HTTP_Parser($this->sp_headers);
                        if ($parser->parse()){
                            $this->sp_headers = $parser->sp_headers;
                            $this->sp_body = trim($parser->sp_body);
                            $this->sp_status_code = $parser->sp_status_code;
                            if (isset($this->sp_headers['location']) && $this->sp_redirects < $redirects && (($this->sp_status_code > 307 && $this->sp_status_code < 400) || in_array($this->sp_status_code, array(300, 301, 302, 303, 307),true))){
                                $this->sp_redirects++;
                                $location = $this->sp_absolutize_url($this->sp_headers['location'], $url);
                                $previousStatusCode = $this->sp_status_code;
                                $this->__construct($location, $timeout, $redirects, $headers, $useragent, $force_fsockopen, $curl_options);
                                $this->sp_permanent_url = ($previousStatusCode === 301) ? $location : $url;
                                return;
                            }
                        }
                    }
                }else{
                    $this->sp_method = SP_FILE_SOURCE_REMOTE | SP_FILE_SOURCE_FSOCKOPEN;
                    $url_parts = parse_url($url);
                    $socket_host = $url_parts['host'];
                    if (isset($url_parts['scheme']) && strtolower($url_parts['scheme']) === 'https'){
                        $socket_host = "ssl://$url_parts[host]";
                        $url_parts['port'] = 443;
                    }
                    if (!isset($url_parts['port'])) $url_parts['port'] = 80;
                    $fp = @fsockopen($socket_host, $url_parts['port'], $err_no, $err_str, $timeout);
                    if (!$fp){
                        $this->sp_error = 'fsockopen error: ' . $err_str;
                        $this->sp_success = false;
                    }else{
                        stream_set_timeout($fp, $timeout);
                        if (isset($url_parts['path'])){
                            if (isset($url_parts['query']))
                                $get = "$url_parts[path]?$url_parts[query]";
                            else $get = $url_parts['path'];
                        } else $get = '/';
                        $out = "GET $get HTTP/1.1\r\n";
                        $out .= "Host: $url_parts[host]\r\n";
                        $out .= "User-Agent: $useragent\r\n";
                        if (extension_loaded('zlib')) $out .= "Accept-Encoding: x-gzip,gzip,deflate\r\n";
                        if (isset($url_parts['user'], $url_parts['pass']))
                            $out .= "Authorization: Basic " . base64_encode("$url_parts[user]:$url_parts[pass]") . "\r\n";
                        foreach ($headers as $key => $value) $out .= "$key: $value\r\n";
                        $out .= "Connection: Close\r\n\r\n";
                        fwrite($fp, $out);
                        $info = stream_get_meta_data($fp);
                        $this->sp_headers = '';
                        while (!$info['eof'] && !$info['timed_out']){
                            $this->sp_headers .= fread($fp, 1160);
                            $info = stream_get_meta_data($fp);
                        }
                        if (!$info['timed_out']){
                            $parser = new SimplePie_HTTP_Parser($this->sp_headers);
                            if ($parser->parse()){
                                $this->sp_headers = $parser->sp_headers;
                                $this->sp_body = $parser->sp_body;
                                $this->sp_status_code = $parser->sp_status_code;
                                if (($this->sp_redirects < $redirects && $this->sp_status_code > 307 && isset($this->sp_headers['location']) && $this->sp_status_code < 400) || (in_array($this->sp_status_code, array(300, 301, 302, 303, 307),true))){
                                    $this->sp_redirects++;
                                    $location = $this->sp_absolutize_url($this->sp_headers['location'], $url);
                                    $previousStatusCode = $this->sp_status_code;
                                    $this->__construct($location, $timeout, $redirects, $headers, $useragent, $force_fsockopen, $curl_options);
                                    $this->sp_permanent_url = ($previousStatusCode === 301) ? $location : $url;
                                    return;
                                }
                                if (isset($this->sp_headers['content-encoding'])){
                                    // Hey, we act dumb elsewhere, so let's do that here too
                                    switch (strtolower(trim($this->sp_headers['content-encoding'], "\x09\x0A\x0D\x20"))){
                                        case 'gzip':
                                        case 'x-gzip':
                                            $decoder = new SimplePie_GZ_Decode($this->sp_body);
                                            if (!$decoder->parse()){
                                                $this->sp_error = 'Unable to decode HTTP "gzip" stream';
                                                $this->sp_success = false;
                                            }else $this->sp_body = trim($decoder->sp_data);
                                            break;
                                        case 'deflate':
                                            if (($decompressed = gzinflate($this->sp_body)) !== false)
                                                $this->sp_body = $decompressed;
                                            else if (($decompressed = gzuncompress($this->sp_body)) !== false)
                                                $this->sp_body = $decompressed;
                                            else if (function_exists('gzdecode') && ($decompressed = gzdecode($this->sp_body)) !== false)
                                                $this->sp_body = $decompressed;
                                            else {
                                                $this->sp_error = 'Unable to decode HTTP "deflate" stream';
                                                $this->sp_success = false;
                                            }
                                            break;
                                        default:
                                            $this->sp_error = 'Unknown content coding';
                                            $this->sp_success = false;
                                    }
                                }
                            }
                        }else{
                            $this->sp_error = 'fsocket timed out';
                            $this->sp_success = false;
                        }
                        fclose($fp);
                    }
                }
            }else{
                $this->sp_method = SP_FILE_SOURCE_LOCAL | SP_FILE_SOURCE_FILE_GET_CONTENTS;
                if (empty($url) || !($this->sp_body = trim(file_get_contents($url)))){
                    $this->sp_error = 'file_get_contents could not read the file';
                    $this->sp_success = false;
                }
            }
        }
        public function prepareHeaders($headers, $count = 1){
            $data = explode("\r\n\r\n", $headers, $count);
            $data = array_pop($data);
            if (false !== stripos($data, "HTTP/1.0 200 Connection established\r\n")) {
                $exploded = explode("\r\n\r\n", $data, 2);
                $data = end($exploded);
            }
            if (false !== stripos($data, "HTTP/1.1 200 Connection established\r\n")) {
                $exploded = explode("\r\n\r\n", $data, 2);
                $data = end($exploded);
            }
            return $data;
        }
    }
}else die;