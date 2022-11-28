<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 12-8-2022
 * Time: 09:27
 */
namespace TP_Admin\Libs\AdmFTP;
if(ABSPATH){
    class FTP_Base{
        public function __construct($port_mode=false, $verbose=false, $local_echo=false){
            $this->__ftp_Consts();
            $this->auto_ascii_ext = ["ASP","BAT","C","CPP","CSS","CSV","JS","HTML","INI","LOG","PHTML","PL","PERL","SH","SQL","TXT"];
            $this->authorized_transfer_mode = [FTP_AUTO_ASCII, FTP_ASCII, FTP_BINARY];
            $this->local_echo = $local_echo;
            $this->OS_full_name = [FTP_OS_Unix => 'UNIX', FTP_OS_Windows => 'WINDOWS', FTP_OS_Mac => 'MACOS'];
            $this->OS_local = FTP_OS_Unix;
            $this->OS_remote = FTP_OS_Unix;
            $this->verbose = $verbose;
            //protected's
            $this->_error_array = [];
            $this->_features = [];
            $this->_settings = [
                'can_restore' => false,
                'connected' => false,
                'eol_code' => [FTP_OS_Unix=>"\n", FTP_OS_Mac=>"\r", FTP_OS_Windows=>"\r\n"],
                'ftp_buff_size' => 4096,
                'login' => "anonymous",
                'password' => "anon@ftp.com",
                'port_available' => ($port_mode===TRUE),
                'ready' => false,
            ];
            $this->_string = [
                'code' => null,
                'cur_type' => null,
                'data_host' => null,
                'data_port' => null,
                'dns_host' => null,
                'host' => null,
                'last_action' => null,
                'message' => "",
                'ftp_control_sock' => null,
                'ftp_data_sock' => null,
                'ftp_temp_sock' => null,
                'passive' => null,
                'port' => null,
                'mask' => null,
                'timeout' => 0,
            ];
            if(stripos(PHP_OS, 'WIN') === 0) $this->OS_local=FTP_OS_Windows;
            elseif(stripos(PHP_OS, 'MAC') === 0) $this->OS_local=FTP_OS_Mac;
        }
        private function __ftp_Consts():void{
            if(!defined('CRLF')) define('CRLF',"\r\n");
            if(!defined("FTP_ASCII")) define("FTP_ASCII", 0);
            if(!defined("FTP_AUTO_ASCII")) define("FTP_AUTO_ASCII", -1);
            if(!defined("FTP_BINARY")) define("FTP_BINARY", 1);
            if(!defined('FTP_FORCE')) define('FTP_FORCE', true);
            if(!defined('FTP_OS_Mac')) define('FTP_OS_Mac', 'm');
            if(!defined('FTP_OS_Unix')) define('FTP_OS_Unix', 'u');
            if(!defined('FTP_OS_Windows')) define('FTP_OS_Windows', 'w');
        }
    }
}else{die;}