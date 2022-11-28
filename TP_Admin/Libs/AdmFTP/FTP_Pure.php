<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-10-2022
 * Time: 04:15
 */
namespace TP_Admin\Libs\AdmFTP;
if(ABSPATH){
    class FTP_Pure extends FTP_Base  {
        public $LocalEcho;
        public $Verbose;
        public $OS_local;
        public $OS_remote;
        public $AutoAsciiExt;
        public $AuthorizedTransferMode;
        public $OS_FullName;
        protected $_base;
        protected $_lastaction;
        protected $_errors;
        protected $_type;
        protected $_umask;
        protected $_timeout;
        protected $_passive;
        protected $_host;
        protected $_fullhost;
        protected $_port;
        protected $_datahost;
        protected $_dataport;
        protected $_ftp_control_sock;
        protected $_ftp_data_sock;
        protected $_ftp_temp_sock;
        protected $_ftp_buff_size;
        protected $_login;
        protected $_password;
        protected $_connected;
        protected $_ready;
        protected $_code;
        protected $_message;
        protected $_can_restore;
        protected $_port_available;
        protected $_curtype;
        protected $_features;
        protected $_error_array;
        protected $_eol_code;
        use FTP_Base_Trait;
        public function __construct($verb=false, $le=false) {
            parent::__construct(false, $verb, $le);
            $this->send_msg("Looking at FTP client class({$this->_settings['port_available']} ?:  without PORT mode support)");
            $this->set_mask();
            $this->passive($this->_settings['port_available']);

        }
        public function set_timeout($sock):bool{
            if(!@stream_set_timeout($sock, $this->_string['timeout'])) {
                $this->push_error('__set_timeout','socket set send timeout');
                $this->quit();
                return false;
            }
            return true;
        }//ftp_pure, 38
        public function connect($host, $port){
            $this->send_msg("Creating socket");
            $sock = @fsockopen($host, $port, $err_no, $err_str, $this->_string['timeout']);
            if (!$sock) {
                $this->push_error('_connect','socket connect failed', "{$err_str} ({$err_no})");
                return false;
            }
            $this->_settings['connected'] = true;
            return $sock;
        }//ftp_pure, 47
        public function read_msg($function = "_read_msg"):bool{
            $regs = [0];
            if(!$this->_settings['connected']) {
                $this->push_error($function, 'Connect first');
                return false;
            }
            $result=true;
            $this->_string['message']="";
            $this->_string['code']=0;
            $go=true;
            do {
                $tmp=@fgets($this->_string['ftp_control_sock'], 512);
                if($tmp===false) {
                    $go=$result=false;
                    $this->push_error($function,'Read failed');
                } else {
                    $this->_string['message'].=$tmp;
                    if(preg_match("/^([0-9]{3})(-(.*[".CRLF."]{1,2})+\\1)? [^".CRLF."]+[".CRLF."]{1,2}$/", $this->_string['message'], $regs)) $go=false;
                }
            } while($go);
            if($this->local_echo) echo "GET < ".rtrim($this->_string['message'], CRLF).CRLF;
            $this->_string['code']=(int)$regs[1]; //todo looking up $regs!
            return $result;

        }//ftp_pure, 58
        public function exec($cmd, $function="_exec"):bool{
            if($this->_settings['ready']){
                $this->push_error($function,'Connect first');
                return false;
            }
            if($this->local_echo) echo "PUT > ",$cmd,CRLF;
            $status=@fwrite($this->_string['ftp_control_sock'], $cmd.CRLF);
            if($status===false) {
                $this->push_error($function,'socket write failed');
                return false;
            }
            $this->_string['last_action']= time();
            if(!$this->read_msg($function)) return false;
            return true;
        }//ftp_pure, 82
        public function data_prepare($mode=FTP_ASCII):bool{
            if(!$this->set_type($mode)) return false;
            if($this->_string['passive']){
                if(!$this->exec("PASSIVE", "passive")) {
                    $this->data_close();
                    return false;
                }
                if(!$this->check_code()) {
                    $this->data_close();
                    return false;
                }
                $ip_port = explode(",", preg_replace("/^.+ \\(?(\d{1,3},\d{1,3},\d{1,3},\d{1,3},\d+,\d+)\\)?.*$/s", "\\1", $this->_string['message']));
                $this->_string['data_host']=$ip_port[0].".".$ip_port[1].".".$ip_port[2].".".$ip_port[3];
                $this->_string['data_host'].=(((int)$ip_port[4])<<8) + ((int)$ip_port[5]);
                $this->send_msg("Connecting to {$this->_string['data_host']}:{$this->_string['data_port']}");
                $this->_string['ftp_data_sock']=@fsockopen($this->_string['data_host'], $this->_string['data_port'], $err_no, $err_str, $this->_string['timeout']);
                if($this->_string['ftp_data_sock']){
                    $this->push_error("_data_prepare","fsockopen fails", "{$err_str} ({$err_no})");
                    $this->data_close();
                    return false;
                }else $this->_string['ftp_data_sock'];
            }else{
                $this->send_msg("Only passive connections available!");
                return false;
            }
            return true;
        }//98
        public function data_read($mode=FTP_ASCII, $fp=null){
            if(is_resource($fp)) $out=0;
            else $out="";
            if(!$this->_string['passive']) {
                $this->send_msg("Only passive connections available!");
                return false;
            }
            while (!feof($this->_string['ftp_data_sock'])) {
                $block=fread($this->_string['ftp_data_sock'], $this->_settings['ftp_buff_size']);
                if($mode!==FTP_BINARY) $block=preg_replace("/\r\n|\r|\n/", $this->_settings['eol_code'][$this->OS_local], $block);
                if(is_resource($fp)) $out+=fwrite($fp, $block, strlen($block));
                else $out.=$block;
            }
            return $out;
        }//127
        public function data_write($mode=FTP_ASCII, $fp=null):bool{
            //if(is_resource($fp)) $out=0;//todo
            //else $out="";
            if(!$this->_string['passive']) {
                $this->send_msg("Only passive connections available!");
                return false;
            }
            if(is_resource($fp)) {
                while(!feof($fp)) {
                    $block=fread($fp, $this->_settings['ftp_buff_size']);
                    if(!$this->__data_write_block($mode, $block)) return false;
                }
            } elseif(!$this->__data_write_block($mode, $fp)) return false;
            return true;
        }//143
        private function __data_write_block($mode, $block):bool{
            if($mode!==FTP_BINARY) $block=preg_replace("/\r\n|\r|\n/", $this->_settings['eol_code'][$this->OS_remote], $block);
            do {
                if(($t=@fwrite($this->_settings['ftp_data_sock'], $block))===false) {
                    $this->push_error("_data_write","Can't write to socket");
                    return false;
                }
                $block=substr($block, $t);
            } while(!empty($block));
            return true;
        }//159
        public function data_close():bool{
            @fclose($this->_settings['ftp_data_sock']);
            $this->send_msg("Disconnected data from remote host");
            return true;
        }//171
        public function quit($force= false): void{
            if($this->_settings['connected'] || $force) {
                @fclose($this->_settings['ftp_control_sock']);
                $this->_settings['connected']=false;
                $this->send_msg("Socket closed");
            }
        }//ftp_pure, 177
    }
}else{die;}