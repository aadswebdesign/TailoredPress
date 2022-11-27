<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-10-2022
 * Time: 06:59
 */
namespace TP_Admin\Libs\AdmFilesystem;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class Adm_Filesystem_FTP_Ext extends Adm_Filesystem_Base {
        public $link;
        public function __construct( $opt = '' ) {
            $this->_method = 'ftp_ext';
            $this->_errors = new TP_Error();
            if ( ! extension_loaded( 'ftp' ) ) {
                $this->_errors->add( 'no_ftp_ext', $this->__( 'The ftp PHP extension is not available' ) );
                return;
            }
            if ( ! defined( 'FS_CONNECT_TIMEOUT' ) ) { define( 'FS_CONNECT_TIMEOUT', 240 );}
            if ( empty( $opt['port'] ) ) { $this->_options['port'] = 21;}
            else { $this->_options['port'] = $opt['port'];}
            if ( empty( $opt['hostname'] ) ) {
                $this->_errors->add( 'empty_hostname', $this->__( 'FTP hostname is required' ) );
            } else { $this->_options['hostname'] = $opt['hostname'];}
            if ( empty( $opt['username'] ) ) {
                $this->_errors->add( 'empty_username', $this->__( 'FTP username is required' ) );
            } else { $this->_options['username'] = $opt['username'];}
            if ( empty( $opt['password'] ) ) {
                $this->_errors->add( 'empty_password', $this->__( 'FTP password is required' ) );
            } else { $this->_options['password'] = $opt['password'];}
            $this->_options['ssl'] = false;
            if ( isset( $opt['connection_type'] ) && 'ftps' === $opt['connection_type'] ) {
                $this->_options['ssl'] = true;}
        }//31
        public function connect():bool {
            if ( isset( $this->_options['ssl'] ) && $this->_options['ssl'] && function_exists( 'ftp_ssl_connect' ) ) {
                $this->link = @ftp_ssl_connect( $this->_options['hostname'], $this->_options['port'], FS_CONNECT_TIMEOUT );
            } else {
                $this->link = @ftp_connect( $this->_options['hostname'], $this->_options['port'], FS_CONNECT_TIMEOUT );
            }
            if ( ! $this->link ) {
                $this->_errors->add(
                    'connect',
                    sprintf( $this->__( 'Failed to connect to FTP Server %s' ), $this->_options['hostname'] . ':' . $this->_options['port']));
                return false;
            }
            if ( ! @ftp_login( $this->link, $this->_options['username'], $this->_options['password'] ) ) {
                $this->_errors->add('auth', sprintf( $this->__( 'Username/Password incorrect for %s' ), $this->_options['username'] ));
                return false;
            }
            ftp_pasv( $this->link, true );
            if ( @ftp_get_option( $this->link, FTP_TIMEOUT_SEC ) < FS_CONNECT_TIMEOUT ) {
                @ftp_set_option( $this->link, FTP_TIMEOUT_SEC, FS_CONNECT_TIMEOUT );
            }
            return true;
        }//85
        public function get_contents( $file ) {
            $temp_file   = $this->_tp_temp_name( $file );
            $temp_handle = fopen( $temp_file, 'wb+' );
            if ( ! $temp_handle ) {
                unlink( $temp_file );
                return false;
            }
            if ( ! ftp_fget( $this->link, $temp_handle, $file, FTP_BINARY ) ) {
                fclose( $temp_handle );
                unlink( $temp_file );
                return false;
            }
            fseek( $temp_handle, 0 ); // Skip back to the start of the file being written to.
            $contents = '';
            while ( ! feof( $temp_handle )){ $contents .= fread( $temp_handle, 8 * KB_IN_BYTES );}
            fclose( $temp_handle );
            unlink( $temp_file );
            return $contents;
        }//137
        public function get_contents_array( $file ) {
            return explode( "\n", $this->get_contents( $file ) );
        }
        public function put_contents( $file, $contents, $mode = false ) {
            $temp_file   = $this->_tp_temp_name( $file );
            $temp_handle = fopen( $temp_file, 'wb+' );
            if ( ! $temp_handle ) {
                unlink( $temp_file );
                return false;
            }
            $this->_mb_string_binary_safe_encoding();
            $data_length   = strlen( $contents );
            $bytes_written = fwrite( $temp_handle, $contents );
            $this->_reset_mb_string_encoding();
            if ( $data_length !== $bytes_written ) {
                fclose( $temp_handle );
                unlink( $temp_file );
                return false;
            }
            fseek( $temp_handle, 0 ); // Skip back to the start of the file being written to.
            $ret = ftp_fput( $this->link, $file, $temp_handle, FTP_BINARY );
            fclose( $temp_handle );
            unlink( $temp_file );
            $this->chmod( $file, $mode );
            return $ret;
        }
        public function cwd() {
            $cwd = ftp_pwd( $this->link );
            if ( $cwd ) { $cwd = $this->_trailingslashit( $cwd );}
            return $cwd;
        }
        public function chdir( $dir ) {
            return @ftp_chdir( $this->link, $dir );
        }
        public function chmod( $file, $mode = false, $recursive = false ) {
            if ( ! $mode ) {
                if ( $this->is_file( $file )){ $mode = (bool)FS_CH_MOD_FILE;}
                elseif($this->is_dir( $file)){ $mode = (bool)FS_CH_MOD_DIR;}
                else { return false;}
            }
            if ( $recursive && $this->is_dir( $file ) ) {
                $filelist = $this->dirlist( $file );
                foreach ( (array) $filelist as $filename => $file_meta ) {
                    $this->chmod( $file . '/' . $filename, $mode, $recursive );
                }
            }
            if ( ! function_exists( 'ftp_chmod' ) ) {
                return (bool) ftp_site( $this->link, sprintf( 'CHMOD %o %s', $mode, $file ) );
            }
            return (bool) ftp_chmod( $this->link, $mode, $file );
        }
        public function owner( $file ) {
            $dir = $this->dirlist( $file );
            return $dir[ $file ]['owner'];
        }
        public function get_ch_mod( $file ):string {
            $dir = $this->dirlist( $file );
            return $dir[ $file ]['permsn'];
        }
        public function group( $file ) {
            $dir = $this->dirlist( $file );
            return $dir[ $file ]['group'];
        }
        public function copy( $source, $destination, $overwrite = false, $mode = false ) {
            if ( ! $overwrite && $this->exists( $destination ) ) {
                return false;}
            $content = $this->get_contents( $source );
            if ( false === $content ) { return false;}
            return $this->put_contents( $destination, $content, $mode );
        }
        public function move( $source, $destination, $overwrite = false ) {
            return ftp_rename( $this->link, $source, $destination );
        }
        public function delete( $file, $recursive = false, $type = false ) {
            if ( empty( $file ) ) { return false;}
            if ( 'f' === $type || $this->is_file( $file )){ return ftp_delete( $this->link, $file );}
            if ( ! $recursive ) { return ftp_rmdir( $this->link, $file );}
            $filelist = $this->dirlist( $this->_trailingslashit( $file ) );
            if ( ! empty( $filelist ) ) {
                foreach ( $filelist as $delete_file ) {
                    $this->delete( $this->_trailingslashit( $file ) . $delete_file['name'], $recursive, $delete_file['type'] );
                }
            }
            return ftp_rmdir( $this->link, $file );
        }
        public function exists( $file ) {
            $list = ftp_nlist( $this->link, $file );
            if ( empty( $list ) && $this->is_dir( $file ) ) {
                return true;
            }
            return ! empty( $list );
        }
        public function is_file( $file ) {
            return $this->exists( $file ) && ! $this->is_dir( $file );
        }
        public function is_dir( $path ) {
            $cwd    = $this->cwd();
            $result = @ftp_chdir( $this->link, $this->_trailingslashit( $path ) );
            if ( ($result && $path === $this->cwd()) || $this->cwd() !== $cwd ) {
                @ftp_chdir( $this->link, $cwd );
                return true;
            }
            return false;
        }
        public function is_readable( $file ) {
            return true;
        }
        public function is_writable( $file ) {
            return true;
        }
        public function a_time( $file ) {
            return false;
        }
        public function mtime( $file ) {
            return ftp_mdtm( $this->link, $file );
        }
        public function size( $file ) {
            return ftp_size( $this->link, $file );
        }
        public function touch( $file, $time = 0, $a_time = 0 ){
            return false;
        }
        public function mkdir( $path, $chmod = false, $ch_own = false, $ch_grp = false ) {
            $path = $this->_untrailingslashit( $path );
            if ( empty( $path )){ return false;}
            if ( ! ftp_mkdir( $this->link, $path ) ) { return false;}
            $this->chmod( $path, $chmod );
            return true;
        }
        public function rm_dir( $path, $recursive = false ) {
            return $this->delete( $path, $recursive );
        }
        /**
         * @param string $line
         * @return array
         */
        public function parse_listing( $line ):array {
            static $is_windows = null;
            if ( is_null( $is_windows ) ) {
                $is_windows = stripos( ftp_systype( $this->link ), 'win' ) !== false;
            }
            if ( $is_windows && preg_match( '/(\d{2})-(\d{2})-(\d{2}) +(\d{2}):(\d{2})(AM|PM) +(\d+|<DIR>) +(.+)/', $line, $lucifer ) ) {
                $b = [];
                if ( $lucifer[3] < 70 ) { $lucifer[3] += 2000;}
                else { $lucifer[3] += 1900; }
                $b['isdir'] = ( __DIR__ === $lucifer[7] );
                if ( $b['isdir']){ $b['type'] = 'd';}
                else { $b['type'] = 'f';}
                $b['size']   = $lucifer[7];
                $b['month']  = $lucifer[1];
                $b['day']    = $lucifer[2];
                $b['year']   = $lucifer[3];
                $b['hour']   = $lucifer[4];
                $b['minute'] = $lucifer[5];
                $b['time']   = mktime( $lucifer[4] + ( strcasecmp( $lucifer[6], 'PM' ) === 0 ? 12 : 0 ), $lucifer[5], 0, $lucifer[1], $lucifer[2], $lucifer[3] );
                $b['am/pm']  = $lucifer[6];
                $b['name']   = $lucifer[8];
            } elseif ( ! $is_windows ) {
                $lucifer = preg_split( '/[ ]/', $line, 9, PREG_SPLIT_NO_EMPTY );
                if ( $lucifer ) {
                    $lcount = count( $lucifer );
                    if ( $lcount < 8 ) { return '';}
                    $b           = [];
                    $b['isdir']  = 'd' === $lucifer[0][0];
                    $b['islink'] = 'l' === $lucifer[0][0];
                    if ( $b['isdir'] ) { $b['type'] = 'd';}
                    elseif ( $b['islink']){ $b['type'] = 'l';}
                    else { $b['type'] = 'f';}
                    $b['perms']  = $lucifer[0];
                    $b['permsn'] = $this->get_num_chmod_from_head( $b['perms'] );
                    $b['number'] = $lucifer[1];
                    $b['owner']  = $lucifer[2];
                    $b['group']  = $lucifer[3];
                    $b['size']   = $lucifer[4];
                    if ( 8 === $lcount ) {
                        sscanf( $lucifer[5], '%d-%d-%d', $b['year'], $b['month'], $b['day'] );
                        sscanf( $lucifer[6], '%d:%d', $b['hour'], $b['minute'] );
                        $b['time'] = mktime( $b['hour'], $b['minute'], 0, $b['month'], $b['day'], $b['year'] );
                        $b['name'] = $lucifer[7];
                    } else {
                        $b['month'] = $lucifer[5];
                        $b['day']   = $lucifer[6];
                        if ( preg_match( '/(\d{2}):(\d{2})/', $lucifer[7], $l2 ) ) {
                            $b['year']   = gmdate( 'Y' );
                            $b['hour']   = $l2[1];
                            $b['minute'] = $l2[2];
                        } else {
                            $b['year']   = $lucifer[7];
                            $b['hour']   = 0;
                            $b['minute'] = 0;
                        }
                        $b['time'] = strtotime( sprintf( '%d %s %d %02d:%02d', $b['day'], $b['month'], $b['year'], $b['hour'], $b['minute'] ) );
                        $b['name'] = $lucifer[8];
                    }
                }
            }
            if ( isset( $b['islink'] ) && $b['islink'] ) {
                $b['name'] = preg_replace( '/(\s*->\s*.*)$/', '', $b['name'] );
            }
            return $b;
        }
        public function dirlist( $path = '.', $include_hidden = true, $recursive = false ) {
            if ( $this->is_file( $path ) ) {
                $limit_file = basename( $path );
                $path       = dirname( $path ) . '/';
            } else { $limit_file = false;}
            $pwd = ftp_pwd( $this->link );
            if ( ! @ftp_chdir( $this->link, $path ) ) { // Can't change to folder = folder doesn't exist.
                return false;
            }
            $list = ftp_rawlist( $this->link, '-a', false );
            @ftp_chdir( $this->link, $pwd );
            if( empty( $list )){  return false;}
            $dirlist = [];
            foreach ( $list as $k => $v ) {
                $entry = $this->parse_listing( $v );
                if ( empty( $entry)){ continue;}
                if ( '.' === $entry['name'] || '..' === $entry['name'] ){ continue;}
                if ( ! $include_hidden && '.' === $entry['name'][0] ){ continue;}
                if ($limit_file && $entry['name'] !== $limit_file){ continue;}
                $dirlist[ $entry['name'] ] = $entry;
            }
            $ret = [];
            foreach ($dirlist as $structure ) {
                if ( 'd' === $structure['type'] ) {
                    if ( $recursive ) { $structure['files'] = $this->dirlist( $path . '/' . $structure['name'], $include_hidden, $recursive );
                    } else { $structure['files'] = [];}
                }
                $ret[ $structure['name'] ] = $structure;
            }
            return $ret;
        }
        public function __destruct(){
            if ($this->link) { ftp_close($this->link);}
        }//777
    }
}else{die;}