<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 11:54
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
if(ABSPATH){
    class SimplePie_Content_Type_Sniffer{
        protected $_sp_body;
        protected $_sp_file;
        protected $_sp_headers;
        public function __construct($file){
            $this->_sp_file = $file;
        }
        public function get_type(){
            if (isset($this->_sp_file->_headers['content-type'])){
                if (!isset($this->_sp_file->_sp_headers['content-encoding'])
                    && ($this->_sp_file->_headers['content-type'] === 'text/plain'
                        || $this->_sp_file->_sp_headers['content-type'] === 'text/plain; charset=ISO-8859-1'
                        || $this->_sp_file->_sp_headers['content-type'] === 'text/plain; charset=iso-8859-1'
                        || $this->_sp_file->_sp_headers['content-type'] === 'text/plain; charset=UTF-8'))
                    return $this->text_or_binary();
                if (($pos = strpos($this->_sp_file->_->sp_headers['content-type'], ';')) !== false)
                    $official = substr($this->_sp_file->_sp_headers['content-type'], 0, $pos);
                else $official = $this->_sp_file->_sp_headers['content-type'];
                $official = strtolower(trim($official));
                if ($official === 'unknown/unknown'
                    || $official === 'application/unknown')
                    return $this->unknown();
                elseif ($official === 'text/xml' || $official === 'application/xml' || substr($official, -4) === '+xml')
                    return $official;
                elseif (strpos($official, 'image/') === 0){
                    if ($return = $this->image()) return $return;
                    return $official;
                }elseif ($official === 'text/html')  return $this->feed_or_html();
                return $official;
            }
            return $this->unknown();
        }
        public function text_or_binary():string{
            if (strpos($this->_sp_file->_sp_body, "\xFE\xFF") === 0
                || strpos($this->_sp_file->_sp_body, "\xFF\xFE") === 0
                || strpos($this->_sp_file->_sp_body, "\x00\x00\xFE\xFF") === 0
                || strpos($this->_sp_file->_sp_body, "\xEF\xBB\xBF") === 0
            )
                return 'text/plain';
            elseif (preg_match('/[\x00-\x08\x0E-\x1A\x1C-\x1F]/', $this->_sp_file->_sp_body))
                return 'application/octet-stream';
            return 'text/plain';
        }
        public function unknown():string{
            $ws = strspn($this->_sp_file->_sp_body, "\x09\x0A\x0B\x0C\x0D\x20");
            if (strtolower(substr($this->_sp_file->_sp_body, $ws, 14)) === '<!doctype html'
                || strtolower(substr($this->_sp_file->_sp_body, $ws, 5)) === '<html'
                || strtolower(substr($this->_sp_file->_sp_body, $ws, 7)) === '<script')
                return 'text/html';
            elseif (strpos($this->_sp_file->_sp_body, '%PDF-') === 0)
                return 'application/pdf';
            elseif (strpos($this->_sp_file->_sp_body, 'GIF87a') === 0 || strpos($this->_sp_file->_sp_body, 'GIF89a') === 0)
                return 'image/gif';
            elseif (strpos($this->_sp_file->_sp_body, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") === 0)
                return 'image/png';
            elseif (strpos($this->_sp_file->_sp_body, "\xFF\xD8\xFF") === 0)
                return 'image/jpeg';
            elseif (strpos($this->_sp_file->_sp_body, "\x42\x4D") === 0)
                return 'image/bmp';
            elseif (strpos($this->_sp_file->_sp_body, "\x00\x00\x01\x00") === 0)
                return 'image/vnd.microsoft.icon';
            return $this->text_or_binary();
        }
        public function image(){
            if (strpos($this->_sp_file->_sp_body, 'GIF87a') === 0 || strpos($this->_sp_file->_sp_body, 'GIF89a') === 0)
                return 'image/gif';
            elseif (strpos($this->_sp_file->_sp_body, "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") === 0)
                return 'image/png';
            elseif (strpos($this->_sp_file->_sp_body, "\xFF\xD8\xFF") === 0)
                return 'image/jpeg';
            elseif (strpos($this->_sp_file->_sp_body, "\x42\x4D") === 0)
                return 'image/bmp';
            elseif (strpos($this->_sp_file->_sp_body, "\x00\x00\x01\x00") === 0)
                return 'image/vnd.microsoft.icon';
            return false;
        }
        public function feed_or_html():string{
            $len = strlen($this->_sp_file->_sp_body);
            $pos = strspn($this->_sp_file->_sp_body, "\x09\x0A\x0D\x20\xEF\xBB\xBF");
            while ($pos < $len){
                switch ($this->_sp_file->sp_body[$pos]) {
                    case "\x09":
                    case "\x0A":
                    case "\x0D":
                    case "\x20":
                        $pos += strspn($this->_sp_file->_sp_body, "\x09\x0A\x0D\x20", $pos);
                        continue 2;
                    case '<':
                        $pos++;
                        break;

                    default:
                        return 'text/html';
                }
                if (substr($this->_sp_file->_sp_body, $pos, 3) === '!--'){
                    $pos += 3;
                    if ($pos < $len && ($pos = strpos($this->_sp_file->_sp_body, '-->', $pos)) !== false) $pos += 3;
                    else return 'text/html';
                } elseif (substr($this->_sp_file->_sp_body, $pos, 1) === '!'){
                    if ($pos < $len && ($pos = strpos($this->_sp_file->_sp_body, '>', $pos)) !== false) $pos++;
                    else return 'text/html';
                }
                elseif (substr($this->_sp_file->_sp_body, $pos, 1) === '?'){
                    if ($pos < $len && ($pos = strpos($this->_sp_file->_sp_body, '?>', $pos)) !== false) $pos += 2;
                    else return 'text/html';
                } elseif (substr($this->_sp_file->_sp_body, $pos, 3) === 'rss' || substr($this->_sp_file->_sp_body, $pos, 7) === 'rdf:RDF')
                    return 'application/rss+xml';
                elseif (substr($this->_sp_file->_sp_bodyy, $pos, 4) === 'feed') return 'application/atom+xml';
                else return 'text/html';
            }
            return 'text/html';
        }
    }
}else die;