<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-10-2022
 * Time: 06:59
 */
namespace TP_Admin\Libs\AdmFilesystem;
use TP_Core\Libs\TP_Error;
use /** @noinspection PhpUndefinedClassInspection */ TP_Admin\Libs\AdmFTP\TP_FTP;
if(ABSPATH){
    class Adm_Filesystem_FTP_Socket extends Adm_Filesystem_Base {
        public $ftp;
        public function __construct( $opt = '' ) {
            $this->_method = 'ftpsockets';
            $this->_errors = new TP_Error();
            /** @noinspection PhpUndefinedClassInspection */
            $this->ftp = new TP_FTP();
            if ( empty( $opt['port'] ) ) { $this->_options['port'] = 21;}
            else { $this->_options['port'] = (int) $opt['port'];}
            if ( empty( $opt['hostname'] ) ) {
                $this->_errors->add( 'empty_hostname', $this->__( 'FTP hostname is required' ) );
            } else { $this->_options['hostname'] = $opt['hostname'];}
            if ( empty( $opt['username'] ) ) {
                $this->_errors->add( 'empty_username', $this->__( 'FTP username is required' ) );
            } else { $this->_options['username'] = $opt['username'];}
            if ( empty( $opt['password'] ) ) {
                $this->_errors->add( 'empty_password', $this->__( 'FTP password is required' ) );
            } else { $this->_options['password'] = $opt['password'];}
        }
        public function connect():bool{
            if ( ! $this->ftp ) { return false;}
            $this->ftp->set_timeout( FS_CONNECT_TIMEOUT );
            if ( ! $this->ftp->set_server( $this->_options['hostname'], $this->_options['port'] ) ) {
                $this->_errors->add('connect', sprintf( $this->__( 'Failed to connect to FTP Server %s' ), $this->_options['hostname'] . ':' . $this->_options['port']));
                return false;
            }
            if ( ! $this->ftp->connect() ) {
                $this->_errors->add('connect', sprintf($this->__( 'Failed to connect to FTP Server %s' ),$this->_options['hostname'] . ':' . $this->_options['port']));
                return false;
            }
            if ( ! $this->ftp->ftp_login( $this->_options['username'], $this->_options['password'] ) ) {
                $this->_errors->add('auth', sprintf( $this->__( 'Username/Password incorrect for %s' ), $this->_options['username']));
                return false;
            }
            $this->ftp->set_type( FTP_BINARY );
            $this->ftp->passive( true );
            $this->ftp->set_timeout( FS_CONNECT_TIMEOUT );
            return true;
        }
        public function get_contents( $file ) {
            if ( ! $this->exists( $file ) ) {return false;}
            $temp_file   = $this->_tp_temp_name( $file );
            $temp_handle = fopen( $temp_file, 'wb+' );
            if ( ! $temp_handle ) {
                unlink( $temp_file );
                return false;
            }
            $this->_mb_string_binary_safe_encoding();
            if ( ! $this->ftp->file_get( $temp_handle, $file ) ) {
                fclose( $temp_handle );
                unlink( $temp_file );
                $this->_reset_mb_string_encoding();
                return ''; // Blank document. File does exist, it's just blank.
            }
            $this->_reset_mb_string_encoding();
            fseek( $temp_handle, 0 ); // Skip back to the start of the file being written to.
            $contents = '';
            while ( ! feof( $temp_handle ) ) { $contents .= fread( $temp_handle, 8 * KB_IN_BYTES );}
            fclose( $temp_handle );
            unlink( $temp_file );
            return $contents;
        }
        public function get_contents_array( $file ) {
            return explode( "\n", $this->get_contents( $file ) );
        }
        public function put_contents( $file, $contents, $mode = false ) {
            $temp_file   = $this->_tp_temp_name( $file );
            $temp_handle = @fopen( $temp_file, 'wb+' );
            if ( ! $temp_handle ) {
                unlink( $temp_file );
                return false;
            }
            $this->_mb_string_binary_safe_encoding();
            $bytes_written = fwrite( $temp_handle, $contents );
            if ( false === $bytes_written || strlen( $contents ) !== $bytes_written ) {
                fclose( $temp_handle );
                unlink( $temp_file );
                $this->_reset_mb_string_encoding();
                return false;
            }
            fseek( $temp_handle, 0 ); // Skip back to the start of the file being written to.
            $ret = $this->ftp->file_put( $file, $temp_handle );
            $this->_reset_mb_string_encoding();
            fclose( $temp_handle );
            unlink( $temp_file );
            $this->chmod( $file, $mode );
            return $ret;
        }
        public function cwd() {
            $cwd = $this->ftp->command_pwd();
            if ( $cwd ) {  $cwd = $this->_trailingslashit( $cwd );}
            return $cwd;
        }
        public function chdir( $dir ) {
            return $this->ftp->command_chdir( $dir );
        }
        public function chmod( $file, $mode = false, $recursive = false ) {
            if ( ! $mode ) {
                if ( $this->is_file( $file ) ) { $mode = (bool)FS_CH_MOD_FILE;}
                elseif ( $this->is_dir( $file ) ) { $mode = (bool)FS_CH_MOD_DIR;}
                else {return false;}
            }
            if ( $recursive && $this->is_dir( $file ) ) {
                $filelist = $this->dirlist( $file );
                foreach ( (array) $filelist as $filename => $file_meta ) {
                    $this->chmod( $file . '/' . $filename, $mode, $recursive );
                }
            }
            return $this->ftp->chmod( $file, $mode );
        }
        public function owner( $file ) {
            $dir = $this->dirlist( $file );
            return $dir[ $file ]['owner'];
        }
        public function get_chmod( $file ) {
            $dir = $this->dirlist( $file );
            return $dir[ $file ]['permsn'];
        }
        public function group( $file ) {
            $dir = $this->dirlist( $file );
            return $dir[ $file ]['group'];
        }
        public function copy( $source, $destination, $overwrite = false, $mode = false ) {
            if ( ! $overwrite && $this->exists($destination)){return false;}
            $content = $this->get_contents( $source );
            if ( false === $content ) { return false;}
            return $this->put_contents( $destination, $content, $mode );
        }
        public function move( $source, $destination, $overwrite = false ) {
            return $this->ftp->rename( $source, $destination );
        }
        public function delete( $file, $recursive = false, $type = false ) {
            if ( empty( $file ) ) { return false;}
            if ( 'f' === $type || $this->is_file( $file)){ return $this->ftp->delete( $file );}
            if ( ! $recursive ) { return $this->ftp->command_rmdir( $file );}
            return $this->ftp->mode_del( $file );
        }
        public function exists( $file ) {
            $list = $this->ftp->new_list( $file );
            if ( empty( $list ) && $this->is_dir( $file ) ) { return true; }
            return ! empty( $list );
            // Return $this->ftp->is_exists($file); has issues with ABOR+426 responses on the ncFTPd server.
        }
        public function is_file( $file ) {
            if ( $this->is_dir( $file )){ return false;}
            if ( $this->exists( $file ) ){ return true;}
            return false;
        }
        public function is_dir( $path ) {
            $cwd = $this->cwd();
            if ( $this->chdir( $path ) ) {
                $this->chdir( $cwd );
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
            return $this->ftp->command_mdtm( $file );
        }
        public function size( $file ) {
            return $this->ftp->file_size( $file );
        }
        public function touch( $file, $time = 0, $a_time = 0 ) {
            return false;
        }
        public function mkdir( $path, $chmod = false, $ch_own = false, $ch_grp = false ) {
            $path = $this->_untrailingslashit( $path );
            if(empty( $path )){ return false;}
            if( ! $this->ftp->command_mkdir($path)){ return false;}
            if ( ! $chmod ) { $chmod = (bool)FS_CH_MOD_DIR;}
            $this->chmod( $path, $chmod );
            return true;
        }
        public function rmdir( $path, $recursive = false ):bool {
            return $this->delete( $path, $recursive );
        }
        public function dirlist( $path = '.', $include_hidden = true, $recursive = false ) {
            if ( $this->is_file( $path ) ) {
                $limit_file = basename( $path );
                $path       = dirname( $path ) . '/';
            } else { $limit_file = false; }
            $this->_mb_string_binary_safe_encoding();
            $list = $this->ftp->dir_list( $path );
            if ( empty( $list ) && ! $this->exists( $path ) ) {
                $this->_reset_mb_string_encoding();
                return false;
            }
            $ret = [];
            foreach ( $list as $structure ) {
                if ( '.' === $structure['name'] || '..' === $structure['name'] ) { continue;}
                if ( ! $include_hidden && '.' === $structure['name'][0] ) { continue;}
                if ( $limit_file && $structure['name'] !== $limit_file ) { continue;}
                if ( 'd' === $structure['type'] ) {
                    if ( $recursive ) {
                        $structure['files'] = $this->dirlist( $path . '/' . $structure['name'], $include_hidden, $recursive );
                    } else { $structure['files'] = [];}
                }
                if ( $structure['islink'] ) { $structure['name'] = preg_replace( '/(\s*->\s*.*)$/', '', $structure['name'] );}
                $structure['permsn'] = $this->get_num_chmod_from_head( $structure['perms'] );
                $ret[ $structure['name'] ] = $structure;
            }
            $this->_reset_mb_string_encoding();
            return $ret;
        }
        /**
         * Destructor.
         *
         * @since 2.5.0
         */
        public function __destruct() {
            $this->ftp->quit();
        }
    }
}else{die;}