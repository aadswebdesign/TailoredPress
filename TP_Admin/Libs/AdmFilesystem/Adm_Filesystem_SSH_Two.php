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
    class Adm_Filesystem_SSH_Two extends Adm_Filesystem_Base {
        public $link;
        public $sftp_link;
        public $keys = false;
        public function __construct( $opt = '' ) {
            $this->_method = 'ssh2';
            $this->_errors = new TP_Error();
            if ( ! extension_loaded( 'ssh2' ) ) {
                $this->_errors->add( 'no_ssh2_ext', $this->__( 'The ssh2 PHP extension is not available' ) );
                return;
            }
            if ( empty( $opt['port'] ) ) { $this->_options['port'] = 22; }
            else {$this->_options['port'] = $opt['port'];}
            if ( empty( $opt['hostname'] ) ) {
                $this->_errors->add( 'empty_hostname', $this->__( 'SSH2 hostname is required' ) );
            } else { $this->_options['hostname'] = $opt['hostname'];}
            if ( ! empty( $opt['public_key'] ) && ! empty( $opt['private_key'] ) ) {
                $this->_options['public_key']  = $opt['public_key'];
                $this->_options['private_key'] = $opt['private_key'];
                $this->_options['hostkey'] = array( 'hostkey' => 'ssh-rsa,ssh-ed25519' );
                $this->keys = true;
            } elseif ( empty( $opt['username'] ) ) {
                $this->_errors->add( 'empty_username', $this->__( 'SSH2 username is required' ) );
            }
            if ( ! empty( $opt['username'] ) ) { $this->_options['username'] = $opt['username'];}
            if ( empty( $opt['password'] ) ) {
                if ( ! $this->keys ) {
                    $this->_errors->add( 'empty_password', $this->__( 'SSH2 password is required' ) );
                }
            } else { $this->_options['password'] = $opt['password'];}
        }//63
        public function connect():bool {
            if ( ! $this->keys ) {
                $this->link = @ssh2_connect( $this->_options['hostname'], $this->_options['port'] );
            } else {
                $this->link = @ssh2_connect( $this->_options['hostname'], $this->_options['port'], $this->_options['hostkey'] );
            }
            if ( ! $this->link ) {
                $this->_errors->add('connect', sprintf( $this->__( 'Failed to connect to SSH2 Server %s' ),$this->_options['hostname'] . ':' . $this->_options['port']));
                return false;
            }
            if ( ! $this->keys ) {
                if ( ! @ssh2_auth_password( $this->link, $this->_options['username'], $this->_options['password'] ) ) {
                    $this->_errors->add('auth',sprintf($this->__( 'Username/Password incorrect for %s' ),$this->_options['username']));
                    return false;
                }
            } else if ( ! @ssh2_auth_pubkey_file( $this->link, $this->_options['username'], $this->_options['public_key'], $this->_options['private_key'], $this->_options['password'] ) ) {
                $this->_errors->add('auth', sprintf( $this->__( 'Public and Private keys incorrect for %s' ), $this->_options['username']));
                return false;
            }
            $this->sftp_link = ssh2_sftp( $this->link );
            if ( ! $this->sftp_link ) {
                $this->_errors->add('connect',sprintf( $this->__( 'Failed to initialize a SFTP subsystem session with the SSH2 Server %s' ),
                        $this->_options['hostname'] . ':' . $this->_options['port']));
                return false;
            }
            return true;
        }//119
        public function sftp_path( $path ) :string{
            if ( '/' === $path ) {$path = '/./';}
            return 'ssh2.sftp://' . $this->sftp_link . '/' . ltrim( $path, '/' );
        }//198
        public function run_command( $command, $return_bool = false ) {
            if ( ! $this->link ) { return false;}
            $stream = ssh2_exec($this->link, $command );
            if ( ! $stream ) {
                $this->_errors->add('command', sprintf( $this->__('Unable to perform command: %s'), $command));
            } else {
                stream_set_blocking( $stream, true );
                stream_set_timeout( $stream, FS_CONNECT_TIMEOUT );
                $data = stream_get_contents( $stream );
                fclose( $stream );
                if($return_bool) { return (false === $data ) ? false : '' !== trim( $data);}
                return $data;
            }
            return false;
        }//214
        public function get_contents( $file ) {
            return file_get_contents( $this->sftp_path( $file ) );
        }//255
        public function get_contents_array( $file ) {
            return file( $this->sftp_path( $file ) );
        }//267
        public function put_contents( $file, $contents, $mode = false ) {
            $ret = file_put_contents( $this->sftp_path( $file ), $contents );
            if ( strlen( $contents ) !== $ret ){ return false;}
            $this->chmod( $file, $mode );
            return true;
        }//282
        public function cwd() {
            $cwd = ssh2_sftp_realpath( $this->sftp_link, '.' );
            if ( $cwd ) { $cwd = $this->_trailingslashit( trim( $cwd ) );}
            return $cwd;
        }//301
        public function chdir( $dir ) {
            return $this->run_command( 'cd ' . $dir, true );
        }//319
        public function ch_grp( $file, $group, $recursive = false ) {
            if (!$this->exists( $file )) { return false;}
            if ( ! $recursive || ! $this->is_dir( $file ) ) {
                return $this->run_command( sprintf( 'chgrp %s %s', escapeshellarg( $group ), escapeshellarg( $file ) ), true );
            }
            return $this->run_command( sprintf( 'chgrp -R %s %s', escapeshellarg( $group ), escapeshellarg( $file ) ), true );
        }//334
        public function chmod( $file, $mode = false, $recursive = false ) {
            if (!$this->exists( $file )){return false;}
            if ( ! $mode ) {
                if ( $this->is_file( $file ) ) { $mode = (bool)FS_CH_MOD_FILE;}
                elseif ( $this->is_dir( $file ) ) { $mode = (bool) FS_CH_MOD_DIR;}
                else { return false;}
            }
            if ( ! $recursive || ! $this->is_dir( $file ) ) {
                return $this->run_command( sprintf( 'chmod %o %s', $mode, escapeshellarg( $file ) ), true );
            }
            return $this->run_command( sprintf( 'chmod -R %o %s', $mode, escapeshellarg( $file ) ), true );
        }//358
        public function ch_own( $file, $owner, $recursive = false ) {
            if ( ! $this->exists( $file)){ return false;}
            if ( ! $recursive || ! $this->is_dir( $file ) ) {
                return $this->run_command( sprintf( 'chown %s %s', escapeshellarg( $owner ), escapeshellarg( $file ) ), true );
            }
            return $this->run_command( sprintf( 'chown -R %s %s', escapeshellarg( $owner ), escapeshellarg( $file ) ), true );
        }//391
        public function owner( $file ) {
            $owner_uid = @fileowner( $this->sftp_path( $file ) );
            if ( ! $owner_uid ){ return false;}
            if ( ! function_exists( 'posix_getpwuid')){ return $owner_uid;}
            $owner_array = posix_getpwuid( $owner_uid );
            if ( ! $owner_array ){ return false;}
            return $owner_array['name'];
        }//411
        public function get_ch_mod( $file ):string {
            return substr( decoct( @fileperms( $this->sftp_path( $file ) ) ), -3 );
        }//439
        public function group( $file ) {
            $gid = @filegroup( $this->sftp_path( $file ) );
            if ( ! $gid ) { return false;}
            if ( ! function_exists( 'posix_getgrgid')){ return $gid;}
            $group_array = posix_getgrgid( $gid );
            if ( ! $group_array ){ return false;}
            return $group_array['name'];
        }//451
        public function copy( $source, $destination, $overwrite = false, $mode = false ) {
            if (!$overwrite && $this->exists( $destination )){ return false;}
            $content = $this->get_contents( $source );
            if ( false === $content ){ return false;}
            return $this->put_contents( $destination, $content, $mode );
        }//484
        public function move( $source, $destination, $overwrite = false ) {
            if ( $this->exists( $destination ) ) {
                if ( $overwrite ) { $this->delete( $destination, false, 'f' );}
                else { return false;}
            }
            return ssh2_sftp_rename( $this->sftp_link, $source, $destination );
        }//509
        public function delete( $file, $recursive = false, $type = false ) {
            if ( 'f' === $type || $this->is_file( $file ) ) {
                return ssh2_sftp_unlink( $this->sftp_link, $file );
            }
            if ( ! $recursive ) { return ssh2_sftp_rmdir( $this->sftp_link, $file );}
            $filelist = $this->dirlist( $file );
            if ( is_array( $filelist ) ) {
                foreach ( $filelist as $filename => $file_info ) {
                    $this->delete( $file . '/' . $filename, $recursive, $file_info['type'] );
                }
            }
            return ssh2_sftp_rmdir( $this->sftp_link, $file );
        }//535
        public function exists( $file ) {
            return file_exists( $this->sftp_path( $file ) );
        }//563
        public function is_file( $file ) {
            return is_file( $this->sftp_path( $file ) );
        }//575
        public function is_dir( $path ) {
            return is_dir( $this->sftp_path( $path ) );
        }//587
        public function is_readable( $file ) {
            return is_readable( $this->sftp_path( $file ) );
        }//599
        public function is_writable( $file ) {
            return true;
        }//611
        public function a_time( $file ) {
            return fileatime( $this->sftp_path( $file ) );
        }//624
        public function mtime( $file ) {
            return filemtime( $this->sftp_path( $file ) );
        }//636
        public function size( $file ) {
            return filesize( $this->sftp_path( $file ) );
        }//638
        public function touch( $file, $time = 0, $a_time = 0 ) {
            // Not implemented.
        }//665
        public function mkdir( $path, $chmod = false, $chown = false, $chgrp = false ) {
            $path = $this->_untrailingslashit( $path );
            if ( empty( $path ) ) { return false;}
            if ( ! $chmod ) { $chmod = (bool)FS_CH_MOD_DIR;}
            if ( ! ssh2_sftp_mkdir( $this->sftp_link, $path, $chmod, true ) ){return false;}
            $this->_tp_ssh2_sftp_chmod( $this->sftp_link, $path, $chmod ); //todo
            if ( $chown ) { $this->ch_own( $path, $chown );}
            if ( $chgrp ) { $this->ch_grp( $path, $chgrp );}
            return true;
        }//683
        public function rmdir( $path, $recursive = false ):bool {
            return $this->delete( $path, $recursive );
        }//722
        public function dirlist( $path, $include_hidden = true, $recursive = false ) {
            if ( $this->is_file( $path ) ) {
                $limit_file = basename( $path );
                $path       = dirname( $path );
            } else { $limit_file = false;}
            if(! $this->is_dir($path) || !$this->is_readable($path)){ return false;}
            $ret = [];
            $dir = dir( $this->sftp_path( $path ) );
            if ( ! $dir ) { return false;}
            while ( false !== ( $entry = $dir->read() ) ) {
                $struc         = [];
                $struc['name'] = $entry;
                if ('.' === $struc['name'] || '..' === $struc['name']){ continue; }
                if ( ! $include_hidden && '.' === $struc['name'][0] ){ continue;}
                if ( $limit_file && $struc['name'] !== $limit_file ) { continue;}
                $struc['perms']       = $this->get_head_ch_mods( $path . '/' . $entry );
                $struc['permsn']      = $this->get_num_chmod_from_head( $struc['perms'] );
                $struc['number']      = false;
                $struc['owner']       = $this->owner( $path . '/' . $entry );
                $struc['group']       = $this->group( $path . '/' . $entry );
                $struc['size']        = $this->size( $path . '/' . $entry );
                $struc['lastmodunix'] = $this->mtime( $path . '/' . $entry );
                $struc['lastmod']     = gmdate( 'M j', $struc['lastmodunix'] );
                $struc['time']        = gmdate( 'h:i:s', $struc['lastmodunix'] );
                $struc['type']        = $this->is_dir( $path . '/' . $entry ) ? 'd' : 'f';
                if ( 'd' === $struc['type'] ) {
                    if ( $recursive ) {
                        $struc['files'] = $this->dirlist( $path . '/' . $struc['name'], $include_hidden, $recursive );
                    } else { $struc['files'] = [];}
                }
                $ret[ $struc['name'] ] = $struc;
            }
            $dir->close();
            unset( $dir );
            return $ret;
        }//751
    }
}else{die;}