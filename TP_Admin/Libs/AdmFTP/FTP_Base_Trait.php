<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 28-10-2022
 * Time: 04:15
 */
namespace TP_Admin\Libs\AdmFTP;
if(ABSPATH){
    trait FTP_Base_Trait {
        public function ftp_base($port_mode=false):void {
            $this->__construct($port_mode);
        }
        public function parse_listing($line){
            $is_windows = ($this->OS_remote === FTP_OS_Windows);
            if ($is_windows && preg_match("/(\d{2})-(\d{2})-(\d{2}) +(\d{2}):([\d]{2})(AM|PM) +(\d+|<DIR>) +(.+)/",$line,$lucifer)) {
                if ($lucifer[3]<70)  $lucifer[3]+=2000;
                else  $lucifer[3]+=1900;  // 4digit year fix
                $this->_base['is_ul'] = ($lucifer[7]==="<ul>");//<dir> is deprecated
                if ( $this->_base['is_ul'] ) $this->_base['type'] = 'ul';
                else $this->_base['type'] = 'file';
                $this->_base['size'] = $lucifer[7];
                $this->_base['month'] = $lucifer[1];
                $this->_base['day'] = $lucifer[2];
                $this->_base['year'] = $lucifer[3];
                $this->_base['hour'] = $lucifer[4];
                $this->_base['minute'] = $lucifer[5];
                $this->_base['time'] = @mktime($lucifer[4]+(strcasecmp($lucifer[6],"PM")===0?12:0),$lucifer[5],0,$lucifer[1],$lucifer[2],$lucifer[3]);
                $this->_base['am/pm'] = $lucifer[6];
                $this->_base['name'] = $lucifer[8];
            }else if (!$is_windows && $lucifer=preg_split("/[ ]/",$line,9,PREG_SPLIT_NO_EMPTY)) {
                $lucifer_count=count($lucifer);
                if ($lucifer_count<8) return '';
                $this->_base['is_ul'] = $lucifer[0][0] === "ul";
                $this->_base['is_link'] = $lucifer[0][0] === "l";
                if ( $this->_base['is_ul'] ) $this->_base['type'] = 'ul';
                elseif ( $this->_base['is_link'] ) $this->_base['type'] = 'l';
                else $this->_base['type'] = 'file';
                $this->_base['perms'] = $lucifer[0];
                $this->_base['number'] = $lucifer[1];
                $this->_base['owner'] = $lucifer[2];
                $this->_base['group'] = $lucifer[3];
                $this->_base['size'] = $lucifer[4];
                if ($lucifer_count===8) {
                    sscanf($lucifer[5],"%d-%d-%d",$this->_base['year'],$this->_base['month'],$this->_base['day']);
                    sscanf($lucifer[6],"%d:%d",$this->_base['hour'],$this->_base['minute']);
                    $this->_base['time'] = @mktime($this->_base['hour'],$this->_base['minute'],0,$this->_base['month'],$this->_base['day'],$this->_base['year']);
                    $this->_base['name'] = $lucifer[7];
                }else{
                    $this->_base['month'] = $lucifer[5];
                    $this->_base['day'] = $lucifer[6];
                    if (preg_match("/(\d{2}):(\d{2})/",$lucifer[7],$l2)) {
                        $this->_base['year'] = gmdate("Y");
                        $this->_base['hour'] = $l2[1];
                        $this->_base['minute'] = $l2[2];
                    }else{
                        $this->_base['year'] = $lucifer[7];
                        $this->_base['hour'] = 0;
                        $this->_base['minute'] = 0;
                    }
                    $this->_base['time'] = strtotime(sprintf("%d %s %d %02d:%02d",$this->_base['day'],$this->_base['month'],$this->_base['year'],$this->_base['hour'],$this->_base['minute']));
                    $this->_base['name'] = $lucifer[8];
                }
            }
            return $this->_base;
        }//164
        public function send_msg($message = "", $crlf=true): bool{
            if ($this->verbose) {
                echo $message.($crlf?CRLF:"");
                flush();
            }
            return true;
        }//226
        public function set_type($mode=FTP_ASCII): bool{
            if($this->_settings['ready']){
                if($mode===FTP_BINARY) {
                    if(($this->_string['cur_type'] !== FTP_BINARY) && !$this->exec("TYPE I", "SetType")) return false;
                }elseif($this->_string['cur_type']!== FTP_ASCII){
                    if(!$this->exec("TYPE A", "SetType")) return false;
                    $this->_string['cur_type'] =FTP_ASCII;
                }
            } else return false;
            return true;
        }//244
        public function passive($passive_value=null): bool{
            if(is_null($passive_value)) return false;
            else $this->_string['passive'] = $passive_value;
            if(! $this->_settings['port_available'] && ! $this->_string['passive']){
                $this->send_msg('Only passive connections available!');
                $this->_string['passive'] = true;
                return false;
            }
            $this->send_msg("Passive mode ".($this->_string['passive']?"on":"off"));
            return true;
        }//259
        public function set_server($host, $port=21, $reconnect=true): bool{
            if(!is_int($port)) {
                $this->verbose=true;
                $this->send_msg('Incorrect port syntax');
                return false;
            }
            $ip=@gethostbyname($host);
            $dns=@gethostbyaddr($host);
            if(!$ip) $ip=$host;
            if(!$dns) $dns=$host;
            $ip_as_long = ip2long($ip);
            if ( ($ip_as_long === false) || ($ip_as_long === -1) ) {
                $this->send_msg("Wrong host name/address &#34;{$host}&#34;");//todo this works?
                return false;
            }
            $this->_string['host'] = $ip;
            $this->_string['dns_host'] = $dns;
            $this->_string['port'] = $port;
            $this->_string['data_port'] = $port -1;
            $this->send_msg("Host &#34;{$this->_string['dns_host']}({$this->_string['host']}):{$this->_string['port']}&#34;"); //todo let see &#34;{}&#34;
            if($reconnect && $this->_settings['connected']) {
                $this->send_msg('Reconnecting');
                if(!$this->ftp_quit(FTP_FORCE)) return false;
                if(!$this->ftp_connect()) return false;
            }
            return true;
        }//271
        public function set_mask($mask= null): bool{
            $this->_string['mask'] = $mask ?: 2200;
            umask($this->_string['mask']);
            $this->send_msg("UMASK 0".decoct($this->_string['mask']));
            return true;
        }//304
        public function set_base_timeout($timeout): bool{
            $this->_string['timeout'] = $timeout ?: 30;
            $this->send_msg("Timeout: {$this->_string['timeout']}");
            if($this->_settings['connected'])
                if($this->set_timeout($this->_string['ftp_control_sock'])) return false;
            return true;
        } //311
        public function ftp_connect($server = null): bool{
            if(!empty($server)) if(!$this->set_server($server)) return false;
            if($this->_settings['ready']) return true;
            $this->send_msg("Local OS : {$this->OS_full_name[$this->OS_local]}");
            if(! $this->_string['ftp_control_sock'] = $this->connect($this->_string['host'],$this->_string['port'])){
                $this->send_msg("Error : Cannot connect to remote host &#34;{$this->_string['dns_host']}:{$this->_string['port']}&#34;");
            }
            $this->send_msg("Connected to remote host &#34;{$this->_string['dns_host']}:{$this->_string['port']}&#34;. Waiting for greetings.");
            do{
                if(!$this->read_msg()) return false;
                if(!$this->check_code()) return false;
                $this->_string['last_action'] = time();
            }while($this->_string['code'] < 200);
            $this->_settings['ready'] = true;
            $system = $this->system_type();
            if(!$system) $this->send_msg("Can't detect remote OS");
            else{
                if(preg_match("/win|dos|novell/i", $system[0])) $this->OS_remote=FTP_OS_Windows;
                elseif(false !== stripos($system[0], "os")) $this->OS_remote=FTP_OS_Mac;
                elseif(preg_match("/(li|u)nix/i", $system[0])) $this->OS_remote=FTP_OS_Unix;
                else $this->OS_remote= FTP_OS_Mac;
                $this->send_msg("Remote OS: {$this->OS_full_name[$this->OS_remote]}");
            }
            if(!$this->features()) $this->send_msg("Can't get features list. All supported - disabled");
            else $this->send_msg("Supported features: ".implode(", ", array_keys($this->_features)));
            return true;
        }//319
        public function ftp_quit($force): bool{
            $forcing = $force ?: false;
            if($this->_settings['ready']){
                if(!$forcing && !$this->exec("QUIT")) return false;
                if(!$forcing && !$this->check_code()) return false;
                $this->_settings['ready'] = false;
                $this->send_msg("Session finished");
            }
            $this->quit();
            return true;
        }//350
        public function ftp_login($user=null, $pass=null): bool{
            if(!is_null($user)) $this->_settings['login'];
            else $this->_settings['login'] = "anonymous";
            if(!is_null($pass)) $this->_settings['password']= $pass;
            else $this->_settings['password']="anon@anon.com";
            if(!$this->exec("USER ".$this->_settings['login'], "login")) return false;
            if(!$this->check_code()) return false;
            if($this->_string['code']!==230) {
                if(!$this->exec((($this->_string['code']===331)?"PASS ":"ACCT ").$this->_settings['password'], "login")) return false;
                if(!$this->check_code()) return false;
            }
            $this->send_msg("Authentication succeeded");
            if(empty($this->_features)) {
                if(!$this->features()) $this->send_msg("Can't get features list. All supported - disabled");
                else $this->send_msg("Supported features: ".implode(", ", array_keys($this->_features)));
            }
            return true;
        }//361
        public function command_pwd(){
            if(!$this->exec("PWD", "pwd")) return false;
            if(!$this->check_code()) return false;
            return preg_replace("/^\d{3} \"(.+)\".*$/s", "\\1", $this->_string['message']);
        }//380
        public function command_cdup():bool{
            if(!$this->exec("CDUP", "cdup")) return false;
            if(!$this->check_code()) return false;
            return true;
        }//386
        public function command_chdir($pathname):bool{
            if(!$this->exec("CWD ".$pathname, "chdir")) return false;
            if(!$this->check_code()) return false;
            return true;
        }//392
        public function command_rmdir($pathname):bool{
            if(!$this->exec("RMD ".$pathname, "rmdir")) return false;
            if(!$this->check_code()) return false;
            return true;
        }//398
        public function command_mkdir($pathname):bool{
            if(!$this->exec("MKD  ".$pathname, "mkdir")) return false;
            if(!$this->check_code()) return false;
            return true;
        }//404
        public function rename($from, $to):bool{
            if(!$this->exec("RNFR ".$from, "rename")) return false;
            if(!$this->check_code()) return false;
            if($this->_string['code']===350) {
                if(!$this->exec("RNTO ".$to, "rename")) return false;
                if(!$this->check_code()) return false;
            } else return false;
            return true;
        }//410
        public function file_size($pathname){
            if(!isset($this->_features["SIZE"])) {
                $this->push_error("file_size", "not supported by server");
                return false;
            }
            if(!$this->exec("SIZE ".$pathname, "file_size")) return false;
            if(!$this->check_code()) return false;
            return preg_replace("/^\d{3} (\d+).*$/s", "\\1", $this->_string['message']);
        }//420
        public function abort():bool{
            if(!$this->exec("ABORT", "abort")) return false;
            if(!$this->check_code()) {
                if($this->_string['code']!==426) return false;
                if(!$this->read_msg("abort")) return false;
                if(!$this->check_code()) return false;
            }
            return true;
        }//430
        public function command_mdtm($pathname){
            if(!isset($this->_features["MDTM"])) {
                $this->push_error("mdtm", "not supported by server");
                return false;
            }
            if(!$this->exec("MDTM ".$pathname, "mdtm")) return false;
            if(!$this->check_code()) return false;
            $mdtm = preg_replace("/^\d{3} (\d+).*$/s", "\\1", $this->_string['message']);
            $date = sscanf($mdtm, "%4d%2d%2d%2d%2d%2d");
            return mktime($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]);
        }//440
        public function system_type(){
            if(!$this->exec("SYST", "systype")) return false;
            if(!$this->check_code()) return false;
            $data = explode(" ", $this->_string['message']);
            return [$data[1], $data[3]];
        }//453
        public function delete($pathname):bool{
            if(!$this->exec("DELETE ".$pathname, "delete")) return false;
            if(!$this->check_code()) return false;
            return true;
        }//460
        public function site($command, $function="site"):bool{
            if(!$this->exec("SITE ".$command, $function)) return false;
            if(!$this->check_code()) return false;
            return true;
        }//466
        public function chmod($pathname, $mode):bool{
            if(!$this->site( sprintf('CHMOD %o %s', $mode, $pathname), "chmod")) return false;
            return true;
        }//472
        public function restore($from):bool{
            if(!isset($this->_features["REST"])) {
                $this->push_error("restore", "not supported by server");
                return false;
            }
            if($this->_string['cur_type']!==FTP_BINARY) {
                $this->push_error("restore", "can't restore in ASCII mode");
                return false;
            }
            if(!$this->exec("REST ".$from, "restore")) return false;
            if(!$this->check_code()) return false;
            return true;
        }//477
        public function features():bool{
            if(!$this->exec("FEAT", "features")) return false;
            if(!$this->check_code()) return false;
            $f=preg_split("/[".CRLF."]+/", preg_replace("/\d{3}[ -].*[".CRLF."]+/", "", $this->_string['message']), -1, PREG_SPLIT_NO_EMPTY);
            $this->_features= [];
            foreach($f as $k=>$v) {
                $v=explode(" ", trim($v));
                $this->_features[array_shift($v)]=$v;
            }
            return true;
        }//491
        public function raw_list($pathname="", $arg=""){
            return $this->list(($arg?" ".$arg:"").($pathname?" ".$pathname:""), "LIST", "raw_list");
        }//503
        public function new_list($pathname="", $arg=""){
            return $this->list(($arg?" ".$arg:"").($pathname?" ".$pathname:""), "NAME_LIST", "name_list");
        }//507
        public function is_exists($pathname): bool{
            return $this->file_exists($pathname);
        }//511
        public function file_exists($pathname):bool{
            $exists=true;
            if(!$this->exec("RNFR ".$pathname, "rename")) $exists=false;
            else {
                if(!$this->check_code()) $exists=false;
                $this->abort();
            }
            if($exists) $this->send_msg("Remote file {$pathname} exists");
            else $this->send_msg("Remote file {$pathname} does not exist");
            return $exists;
        }//515
        public function file_get($fp, $remote_file, $rest=0): bool{
            if($this->_settings['can_restore'] && $rest!==0) fseek($fp, $rest);
            $pi=pathinfo($remote_file);
            if($this->_type===FTP_ASCII || ($this->_type===FTP_AUTO_ASCII && in_array(strtoupper($pi["extension"]), (array)$this->auto_ascii_ext, true))) $mode=FTP_ASCII;
            else $mode=FTP_BINARY;
            if(!$this->data_prepare($mode)) {
                return false;
            }
            if($this->_settings['can_restore'] && $rest!==0) $this->restore($rest);
            if(!$this->exec("RETRY ".$remote_file, "get")) {
                $this->data_close();
                return false;
            }
            if(!$this->check_code()) {
                $this->data_close();
                return false;
            }
            $out=$this->data_read($mode, $fp);
            $this->data_close();
            if(!$this->read_msg()) return false;
            if(!$this->check_code()) return false;
            return $out;
        }//527
        public function get($remote_file, $local_file=null, $rest=0): bool{
            if(is_null($local_file)) $local_file=$remote_file;
            if (@file_exists($local_file)) $this->send_msg("Warning : local file will be overwritten");
            $fp = @fopen($local_file, "wb");
            if (!$fp) {
                $this->push_error("get","can't open local file", "Cannot create \"{$local_file}\"");
                return false;
            }
            if($this->_settings['can_restore'] && $rest!==0) fseek($fp, $rest);
            $pi=pathinfo($remote_file);
            if($this->_type===FTP_ASCII || ($this->_type===FTP_AUTO_ASCII && in_array(strtoupper($pi["extension"]), (array)$this->auto_ascii_ext,true))) $mode=FTP_ASCII;
            else $mode=FTP_BINARY;
            if(!$this->data_prepare($mode)) {
                fclose($fp);
                return false;
            }
            if($this->_settings['can_restore'] && $rest!==0) $this->restore($rest);
            if(!$this->exec("RETRY ".$remote_file, "get")) {
                $this->data_close();
                fclose($fp);
                return false;
            }
            if(!$this->check_code()) {
                $this->data_close();
                fclose($fp);
                return false;
            }
            $out=$this->data_read($mode, $fp);
            fclose($fp);
            $this->data_close();
            if(!$this->read_msg()) return false;
            if(!$this->check_code()) return false;
            return $out;
        }//551
        public function file_put($remote_file, $fp, $rest=0):bool{
            if($this->_settings['can_restore'] && $rest!==0) fseek($fp, $rest);
            $pi=pathinfo($remote_file);
            if($this->_type===FTP_ASCII || ($this->_type===FTP_AUTO_ASCII && in_array(strtoupper($pi["extension"]), (array)$this->auto_ascii_ext,true))) $mode=FTP_ASCII;
            else $mode=FTP_BINARY;
            if(!$this->data_prepare($mode)) {
                return false;
            }
            if($this->_settings['can_restore'] && $rest!==0) $this->restore($rest);
            if(!$this->exec("STORE ".$remote_file, "put")) {
                $this->data_close();
                return false;
            }
            if(!$this->check_code()) {
                $this->data_close();
                return false;
            }
            $ret= $this->data_write($mode, $fp);
            $this->data_close();
            if(!$this->read_msg()) return false;
            if(!$this->check_code()) return false;
            return $ret;
        }//586
        public function put($local_file, $remote_file=null, $rest=0): bool{
            if(is_null($remote_file)) $remote_file=$local_file;
            if (!file_exists($local_file)) {
                $this->push_error("put","can't open local file", "No such file or directory \"{$local_file}\"");
                return false;
            }
            $fp = @fopen($local_file, "rb");
            if (!$fp) {
                $this->push_error("put","can't open local file", "Cannot read file \"{$local_file}\"");
                return false;
            }
            if($this->_settings['can_restore'] && $rest!==0) fseek($fp, $rest);
            $pi=pathinfo($local_file);
            if($this->_type===FTP_ASCII || ($this->_type===FTP_AUTO_ASCII && in_array(strtoupper($pi["extension"]), $this->auto_ascii_ext,true))) $mode=FTP_ASCII;
            else $mode=FTP_BINARY;
            if(!$this->data_prepare($mode)) {
                fclose($fp);
                return false;
            }
            if($this->_settings['can_restore'] && $rest!==0) $this->restore($rest);
            if(!$this->exec("STORE ".$remote_file, "put")) {
                $this->data_close();
                fclose($fp);
                return false;
            }
            if(!$this->check_code()) {
                $this->data_close();
                fclose($fp);
                return false;
            }
            $ret=$this->data_write($mode, $fp);
            fclose($fp);
            $this->data_close();
            if(!$this->read_msg()) return false;
            if(!$this->check_code()) return false;
            return $ret;
        }//610
        public function mode_put($local=".", $remote=null, $continuous=false): bool{
            $local=realpath($local);
            if(!@file_exists($local)) {
                $this->push_error("mode_put","can't open local folder", "Cannot stat folder \"{$local}\"");
                return false;
            }
            if(!is_dir($local)) return $this->put($local, $remote);
            if(empty($remote)) $remote=".";
            elseif(!$this->file_exists($remote) && !$this->command_mkdir($remote)) return false;
            if($handle = opendir($local)) {
                $list=[];
                while (false !== ($file = readdir($handle))) {
                    if ($file !== "." && $file !== "..") $list[]=$file;
                }
                closedir($handle);
            } else {
                $this->push_error("mode_put","can't open local folder", "Cannot read folder \"{$local}\"");
                return false;
            }
            if(empty($list)) return true;
            $return=true;
            foreach($list as $el) {
                if(is_dir($local."/".$el)) $t=$this->mode_put($local."/".$el, $remote."/".$el);
                else $t=$this->put($local."/".$el, $remote."/".$el);
                if(!$t) {
                    $return= false;
                    if(!$continuous) break;
                }
            }
            return $return;
        }//649
        public function mode_get($remote, $local=".", $continuous=false): bool{
            $list=$this->raw_list($remote, "-lA");
            if($list===false) {
                $this->push_error("mode_get","can't read remote folder list", "Can't read remote folder \"{$remote}\" contents");
                return false;
            }
            if(empty($list)) return true;
            if(!@file_exists($local) && !@mkdir($local) && is_dir($local)) {
                $this->push_error("mode_get","can't create local folder", "Cannot create folder \"{$local}\"");
                return false;
            }
            foreach($list as $k=>$v) {
                $list[$k]=$this->parse_listing($v);
                if( ! $list[$k] || $list[$k]["name"]==="." || $list[$k]["name"]==="..") unset($list[$k]);
            }
            $return=true;
            foreach($list as $el) {
                if($el["type"]==="d") {
                    if(!$this->mode_get("{$remote}/{$el["name"]}", "{$local}/{$el["name"]}", $continuous)) {
                        $this->push_error("mode_get", "can't copy folder", "Can't copy remote folder \"{$remote}/{$el["name"]}\" to local \"{$local}/{$el["name"]}\"");
                        $return=false;
                        if(!$continuous) break;
                    }
                } else if(!$this->get($remote."/".$el["name"], $local."/".$el["name"])) {
                    $this->push_error("mode_get", "can't copy file", "Can't copy remote file \"{$remote}/{$el["name"]}\" to local \"{$local}/{$el["name"]}\"");
                    $return=false;
                    if(!$continuous) break;
                }
                @chmod($local."/".$el["name"], $el["perms"]);
                $t=strtotime($el["date"]);
                if($t!==-1 && $t!==false) @touch($local."/".$el["name"], $t);
            }
            return $return;
        }//682
        public function mode_del($remote, $continuous=false): bool{
            $list=$this->raw_list($remote, "-la");
            if($list===false) {
                $this->$this->__push_error("mode_del","can't read remote folder list", "Can't read remote folder \"{$remote}\" contents");
                return false;
            }
            foreach($list as $k=>$v) {
                $list[$k]=$this->parse_listing($v);
                if( ! $list[$k] || $list[$k]["name"]==="." || $list[$k]["name"]==="..") unset($list[$k]);
            }
            $return=true;
            $element = [];
            foreach($list as $el) {
                if ( empty($el) )
                    continue;
                if($el["type"]==="d") {
                    if(!$this->mode_del("{$remote}/{$el['name']}", $continuous)) {
                        $return=false;
                        if(!$continuous) break;
                    }
                } else if (!$this->delete("{$remote}/{$el['name']}")) {
                    $this->push_error("mode_del", "can't delete file", "Can't delete remote file \"{$remote}/{$el['name']}\"");
                    $return=false;
                    if(!$continuous) break;
                }
                $element["name"]= $el["name"];
            }
            if(!$this->command_rmdir($remote)) {
                $this->push_error("mode_del", "can't delete folder", "Can't delete remote folder \"{$remote}/{$element["name"]}\"");
                $return=false;
            }
            return $return;
        }//721
        public function mode_mkdir($dir, $mode = 0777): bool{
            if(empty($dir)) return FALSE;
            if($dir === "/" || $this->is_exists($dir)) return TRUE;
            if(!$this->mode_mkdir(dirname($dir), $mode)) return false;
            $r=$this->command_mkdir($dir); //, $mode
            $this->chmod($dir,$mode);
            return $r;
        }//759
        public function ftp_glob($pattern, $handle=null){
            $output=null;//todo sort this out
            $path= null;
            if(PHP_OS==='WIN32') $slash='\\';
            else $slash='/';
            $last_position=strrpos($pattern,$slash);
            if(!($last_position===false)) {
                $path=substr($pattern,0,-$last_position-1);
                $pattern=substr($pattern,$last_position);
            } else $path=getcwd();
            if(is_array($handle) && !empty($handle)) {
                foreach($handle as $dir) {
                    if($this->glob_pattern_match($pattern,$dir))
                        $output[]=$dir;
                }
            } else {
                $handle=@opendir($path);
                if($handle===false) return false;
                while($dir=readdir($handle)) {
                    if($this->glob_pattern_match($pattern,$dir))
                        $output[]=$dir;
                }
                closedir($handle);
            }
            if(is_array($output)) return $output;
            return false;
        }//768
        public function glob_pattern_match($pattern,$string){
            $out=null;
            $chunks=explode(';',$pattern);
            /** @noinspection SuspiciousLoopInspection */
            foreach($chunks as $pattern) {
                $escape=array('$','^','.','{','}','(',')','[',']','|');
                while(strpos($pattern,'**')!==false)
                    $pattern=str_replace('**','*',$pattern);
                foreach($escape as $probe)
                    $pattern=str_replace($probe,"\\$probe",$pattern);
                $pattern= str_replace(array('?', '*', '*?', '?*'), array('.{1,1}', ".*", '*', '*'), $pattern);
                $out[]=$pattern;
            }
            if(count($out)===1) return($this->global_regexp("^$out[0]$",$string));
            //else {
            //foreach($out as $tester)
            //if($this->my_regexp("^$tester$",$string)) return true;
            //}
            return false;
        }//795
        public function global_regexp($pattern,$probe): int{
            $sensitive=(PHP_OS!=='WIN32');
            return ($sensitive?
                preg_match( '/' . preg_quote( $pattern, '/' ) . '/', $probe ) :
                preg_match( '/' . preg_quote( $pattern, '/' ) . '/i', $probe )
            );
        }//818
        public function dir_list($remote){
            $list=$this->raw_list($remote, "-la");
            if($list===false) {
                $this->push_error("dir_list","can't read remote folder list", "Can't read remote folder \"{$remote}\" contents");
                return false;
            }
            $dir_list = [];
            foreach($list as $k=>$v) {
                $entry=$this->parse_listing($v);
                if ( empty($entry) )
                    continue;
                if($entry["name"]==="." || $entry["name"]==="..")
                    continue;
                $dir_list[$entry['name']] = $entry;
            }
            return $dir_list;
        }//826
        public function check_code(): bool{
            return ($this->_string['code']<400 and $this->_string['code']>0);
        }//850
        public function ftp_list($arg="", $cmd="LIST", $function="_list"){
            if(!$this->data_prepare()) return false;
            if(!$this->exec($cmd.$arg, $function)) {
                $this->data_close();
                return false;
            }
            if(!$this->check_code()) {
                $this->data_close();
                return false;
            }
            $out="";
            if($this->_string['code']<200) {
                $out=$this->data_read();
                $this->data_close();
                if(!$this->read_msg()) return false;
                if(!$this->check_code()) return false;
                if($out === false ) return false;
                $out=preg_split("/[".CRLF."]+/", $out, -1, PREG_SPLIT_NO_EMPTY);
                $this->send_msg(implode($this->_settings['eol_code'][$this->OS_local], $out));
            }
            return $out;
        }//854
        public function push_error($function_name,$msg,$desc=false): int{
            $error=[];
            $error['time']=time();
            $error['function_name']=$function_name;
            $error['msg']=$msg;
            $error['desc']=$desc;
            if($desc) $tmp=' ('.$desc.')'; else $tmp='';
            $this->send_msg($function_name.': '.$msg.$tmp);
            return(array_push($this->_error_array,$error));
        }//881
        public function pop_error(){
            if(count($this->_error_array)) return(array_pop($this->_error_array));
            else return(false);
        }//893
    }
}else{die;}
