<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 12-8-2022
 * Time: 10:30
 */
namespace TP_Admin\Libs\AdmFTP;
if(ABSPATH){
    class FTP_Sockets extends FTP_Base {
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
        protected $_stream;
        protected $_go;
        use FTP_Base_Trait;
        public function __construct($verb=false, $le=false) {
            parent::__construct(true, $verb, $le);
            $this->send_msg("Looking at FTP client class({$this->_settings['port_available']} ?:  without PORT mode support)");
            $this->set_mask();
            $this->passive($this->_settings['port_available']);
        }
        public function set_timeout($sock):bool{
            if(!@socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$this->_string['timeout'], "usec"=>0))) {
                $this->push_error('_connect','socket set receive timeout',socket_strerror(socket_last_error($sock)));
                @socket_close($sock);
                return false;
            }
            if(!@socket_set_option($sock, SOL_SOCKET , SO_SNDTIMEO, array("sec"=>$this->_string['timeout'], "usec"=>0))) {
                $this->push_error('_connect','socket set send timeout',socket_strerror(socket_last_error($sock)));
                @socket_close($sock);
                return false;
            }
            return true;
        }//ftp_sockets, 38
        public function connect($host ='', $port=''){
            $this->send_msg("Creating socket");
            if(!($sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP))) {
                $this->push_error('_connect','socket create failed',socket_strerror(socket_last_error($sock)));
                return false;
            }
            if(!$this->set_timeout($sock)) return false;
            $this->send_msg("Connecting to \"{$host}:{$port}\"");
            if (!(@socket_connect($sock, $host, $port))) { //not used $res =
                $this->push_error('_connect','socket connect failed',socket_strerror(socket_last_error($sock)));
                @socket_close($sock);
                return FALSE;
            }
            $this->_settings['connected'] = true;
            return $sock;
        }//ftp_sockets, 52
        public function read_msg($function = "_read_msg"):bool{
            $regs = [0];
            if(!$this->_settings['connected']) {
                $this->push_error($function,'Connect first');
                return false;
            }
            $result=true;
            $this->_string['message']="";
            $this->_string['code']=0;
            $this->_go=true;
            do{
                $tmp=@socket_read($this->_string['ftp_control_sock'], 4096, PHP_BINARY_READ);
                if($tmp===false) {
                    $this->_go=$result=false;
                    $this->push_error($function,'Read failed', socket_strerror(socket_last_error($this->_settings['ftp_control_sock'])));
                } else {
                    $this->_string['message'].=$tmp;
                    $this->_go = !preg_match("/^([0-9]{3})(-.+\\1)? [^".CRLF."]+".CRLF."$/Us", $this->_string['message'], $regs);
                }
            }while($this->_go);
            if($this->local_echo) echo "GET < ".rtrim($this->_string['message'], CRLF).CRLF;
            $this->_string['code']=(int)$regs[1]; //todo looking up $regs!
            return $result;
        }//ftp_sockets, 69
        public function exec($cmd, $function="__exec"):bool{
            if($this->_settings['ready']){
                $this->push_error($function,'Connect first');
                return false;
            }
            if($this->local_echo) echo "PUT > ",$cmd,CRLF;
            $status=@socket_write($this->_string['ftp_control_sock'], $cmd.CRLF);
            if($status===false) {
                $this->push_error($function,'socket write failed', socket_strerror(socket_last_error($this->_stream)));
                return false;
            }
            $this->_string['last_action']= time();
            if(!$this->read_msg($function)) return false;
            return true;
        }//ftp_socket, 93
        public function data_prepare($mode=FTP_ASCII):bool{
            if(!$this->set_type($mode)) return false;
            $this->send_msg("Creating data socket");
            $this->_string['ftp_data_sock'] = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if($this->_string['ftp_data_sock'] < 0){
                $this->push_error('_data_prepare','socket create failed',socket_strerror(socket_last_error($this->_string['ftp_data_sock'])));
                return false;
            }
            if(!$this->set_timeout($this->_string['ftp_data_sock'])) {
                $this->data_close();
                return false;
            }
            if($this->_string['passive']){
                if(!$this->exec("PASSIVE", "passive")) {
                    $this->data_close();
                    return false;
                }
                if(!$this->check_code()) {
                    $this->data_close();
                    return false;
                }
                $ip_port = explode(",", preg_replace("/^.+ \\(?(\d{1,3},\d{1,3},\d{1,3},\d{1,3},\d+,\d+)\\)?.*$/s", "\\1",  $this->_string['message']));
                $this->_string['data_host']=$ip_port[0].".".$ip_port[1].".".$ip_port[2].".".$ip_port[3];
                $this->_string['data_host'].=(((int)$ip_port[4])<<8) + ((int)$ip_port[5]);
                $this->send_msg("Connecting to {$this->_string['data_host']}:{$this->_string['data_port']}");
                if(!@socket_connect($this->_string['ftp_data_sock'], $this->_string['data_host'], $this->_string['data_port'])) {
                    $this->push_error("_data_prepare","socket_connect", socket_strerror(socket_last_error($this->_string['ftp_data_sock'])));
                    $this->data_close();
                    return false;
                }
                else $this->_string['ftp_temp_sock']=$this->_string['ftp_data_sock'];
            }else{
                if(!@socket_getsockname($this->_string['ftp_control_sock'], $addr, $port)) {
                    $this->push_error("_data_prepare","can't get control socket information", socket_strerror(socket_last_error($this->_string['ftp_temp_sock'])));
                    $this->data_close();
                    return false;
                }
                if(!@socket_bind($this->_string['ftp_data_sock'],$addr)){
                    $this->push_error("_data_prepare","can't bind data socket", socket_strerror(socket_last_error($this->_string['ftp_data_sock'])));
                    $this->data_close();
                    return false;
                }
                if(!@socket_listen($this->_string['ftp_data_sock'])) {
                    $this->push_error("_data_prepare","can't listen data socket", socket_strerror(socket_last_error($this->_string['ftp_data_sock'])));
                    $this->data_close();
                    return false;
                }
                if(!@socket_getsockname($this->_string['ftp_data_sock'], $this->_string['data_host'], $this->_string['data_port'])) {
                    $this->push_error("_data_prepare","can't get data socket information", socket_strerror(socket_last_error($this->_string['ftp_data_sock'])));
                    $this->data_close();
                    return false;
                }
                if(!$this->exec('PORT '.str_replace('.',',',$this->_string['data_host'].'.'.($this->_string['data_port']>>8).'.'.($this->_string['data_port']&0x00FF)), "_port")) {
                    $this->data_close();
                    return false;
                }
                if(!$this->check_code()) {
                    $this->data_close();
                    return false;
                }
            }
            return true;
        }//109
        public function data_read($mode=FTP_ASCII, $fp=null){
            $new_line=$this->_settings['eol_code'][$this->OS_local];
            if(is_resource($fp)) $out=0;
            else $out="";
            if(!$this->_string['passive']) {
                $this->send_msg("Connecting to ".$this->_string['data_host'].":".$this->_string['data_port']);
                $this->_string['ftp_temp_sock']=socket_accept($this->_string['ftp_data_sock']);
                if($this->_string['ftp_temp_sock']===false) {
                    $this->push_error("_data_read","socket_accept", socket_strerror(socket_last_error($this->_string['ftp_temp_sock'])));
                    $this->data_close();
                    return false;
                }
            }
            while(($block=@socket_read($this->_string['ftp_temp_sock'], $this->_settings['ftp_buff_size'], PHP_BINARY_READ))!==false) {
                if($block==="") break;
                if($mode!==FTP_BINARY) $block=preg_replace("/\r\n|\r|\n/", $new_line, $block);
                if(is_resource($fp)) $out+=fwrite($fp, $block, strlen($block));
                else $out.=$block;
            }
            return $out;
        }//173
        public function data_write($mode=FTP_ASCII, $fp=null):bool{
            //$new_line=$this->_settings['eol_code'][$this->OS_local]; //todo
            //if(is_resource($fp)) $out=0;
            //else $out="";
            if(!$this->_string['passive']) {
                $this->send_msg("Connecting to ".$this->_string['data_host'].":".$this->_string['data_port']);
                $this->_string['ftp_temp_sock']=socket_accept($this->_string['ftp_data_sock']);
                if($this->_string['ftp_temp_sock']===false) {
                    $this->push_error("_data_read","socket_accept", socket_strerror(socket_last_error($this->_string['ftp_temp_sock'])));
                    $this->data_close();
                    return false;
                }
            }
            if(is_resource($fp)) {
                while(!feof($fp)) {
                    $block=fread($fp, $this->_settings['ftp_buff_size']);
                    if(!$this->__data_write_block($mode, $block)) return false;
                }
            } elseif(!$this->__data_write_block($mode, $fp)) return false;
            return true;
        }//196
        private function __data_write_block($mode, $block):bool{
            if($mode!==FTP_BINARY) $block=preg_replace("/\r\n|\r|\n/", $this->_settings['eol_code'][$this->OS_remote], $block);
            do{
                if(($t=@socket_write($this->_string['ftp_temp_sock'], $block))===false) {
                    $this->push_error("_data_write","socket_write", socket_strerror(socket_last_error($this->_string['ftp_temp_sock'])));
                    return false;
                }
                $block=substr($block, $t);
            } while(!empty($block));
            return true;
        }//218
        public function data_close():bool{
            @socket_close($this->_string['ftp_temp_sock']);
            @socket_close($this->_string['ftp_data_sock']);
            $this->send_msg("Disconnected data from remote host");
            return true;
        }//231
        public function quit(): void {
            if($this->_settings['connected']) {
                @socket_close($this->_settings['ftp_control_sock']);
                $this->_settings['connected']=false;
                $this->send_msg("Socket closed");
            }
        }//ftp_sockets, 238
    }
}else{die;}