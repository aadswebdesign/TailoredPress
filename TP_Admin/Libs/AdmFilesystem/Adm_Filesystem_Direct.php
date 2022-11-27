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
    class Adm_Filesystem_Direct extends Adm_Filesystem_Base {
        public function __construct( $arg ) {
            $this->_method = 'direct';
            $this->_errors = new TP_Error();
            if(! defined('FS_CH_MOD_FILE')) define('FS_CH_MOD_FILE',( fileperms( ABSPATH . 'index.php' ) & 0777 | 0644 ));
            if(! defined('FS_CH_MOD_DIR')) define('FS_CH_MOD_DIR',( fileperms( ABSPATH ) & 0777 | 0755 ));
        }//25
        public function get_contents( $file ) {
            return @file_get_contents( $file );
        }//38
        public function get_contents_array( $file ) {
            return @file( $file );
        }//50
        public function put_contents( $file, $contents, $mode = false ):bool {
            $fp = @fopen( $file, 'wb' );
            if ( ! $fp ) { return false;}
            $this->_mb_string_binary_safe_encoding();
            $data_length = strlen( $contents );
            $bytes_written = fwrite( $fp, $contents );
            $this->_reset_mb_string_encoding();
            fclose( $fp );
            if ( $data_length !== $bytes_written ) { return false;}
            $this->chmod( $file, $mode );
            return true;
        }//65
        public function cwd() {
            return getcwd();
        }//98
        public function chdir( $dir ) {
            return @chdir( $dir );
        }//110
        public function ch_grp( $file, $group, $recursive = false ) {
            if ( ! $this->exists( $file ) ) { return false;}
            if ( ! $recursive ){return chgrp( $file, $group );}
            if ( ! $this->is_dir( $file)){ return chgrp( $file, $group );}
            $file     = $this->_trailingslashit( $file );
            $filelist = $this->dirlist( $file );
            foreach ( $filelist as $filename ) {
                $this->ch_grp( $file . $filename, $group, $recursive );
            }
            return true;
        }//125
        public function chmod( $file, $mode = false, $recursive = false ) {
            if ( ! $mode ) {
                if ( $this->is_file( $file ) ) { $mode = (bool)FS_CH_MOD_FILE;}
                elseif ( $this->is_dir( $file ) ) { $mode = (bool)FS_CH_MOD_DIR;}
                else { return false;}
            }
            if ( ! $recursive || ! $this->is_dir($file)){ return chmod( $file, $mode );}
            $file     = $this->_trailingslashit( $file );
            $filelist = $this->dirlist( $file );
            foreach ( (array) $filelist as $filename => $file_meta ) {
                $this->chmod( $file . $filename, $mode, $recursive );
            }
            return true;
        }//161
        public function ch_own( $file, $owner, $recursive = false ) {
            if (!$this->exists($file)){ return false;}
            if ( ! $recursive ) { return chown( $file, $owner );}
            if ( ! $this->is_dir( $file ) ) { return chown( $file, $owner );}
            $filelist = $this->dirlist( $file );
            foreach ( $filelist as $filename ) {
                $this->ch_own( $file . '/' . $filename, $owner, $recursive );
            }
            return true;
        }//198
        public function owner( $file ) {
            $owner_uid = @fileowner( $file );
            if (!$owner_uid){ return false;}
            if ( ! function_exists('posix_get_pw_uid')){ return $owner_uid;}
            $owner_array = posix_getpwuid( $owner_uid );
            if ( ! $owner_array ){ return false;}
            return $owner_array['name'];
        }//229
        public function get_chmod( $file ) {
            return substr( decoct( @fileperms( $file ) ), -3 );
        }//259
        public function group( $file ) {
            $gid = @filegroup( $file );
            if ( ! $gid ) { return false;}
            if ( ! function_exists( 'posix_get_gr_gid')){ return $gid;}
            $group_array = posix_getgrgid( $gid );
            if ( ! $group_array ) { return false; }
            return $group_array['name'];
        }//271
        public function copy( $source, $destination, $overwrite = false, $mode = false ) {
            if ( ! $overwrite && $this->exists( $destination ) ) { return false; }
            $rt_val = copy( $source, $destination );
            if ( $mode ) {$this->chmod( $destination, $mode ); }
            return $rt_val;
        }//304
        public function move( $source, $destination, $overwrite = false ) {
            if ( ! $overwrite && $this->exists( $destination ) ) { return false;}
            if ( @rename( $source, $destination ) ) { return true; }
            if ( $this->copy( $source, $destination, $overwrite ) && $this->exists( $destination ) ) {
                $this->delete( $source );
                return true;
            }
            return false;
        }//329
        public function delete( $file, $recursive = false, $type = false ) {
            if ( empty( $file ) ) { return false;}
            $file = str_replace( '\\', '/', $file );
            if ( 'f' === $type || $this->is_file( $file ) ) { return @unlink( $file );}
            if ( ! $recursive && $this->is_dir( $file ) ) { return @rmdir( $file );}
            $file     = $this->_trailingslashit( $file );
            $filelist = $this->dirlist( $file, true );
            $retval = true;
            if ( is_array( $filelist ) ) {
                foreach ( $filelist as $filename => $file_info ) {
                    if (!$this->delete( $file . $filename,$recursive,$file_info['type'])){ $retval = false;}
                }
            }
            if ( file_exists( $file ) && ! @rmdir( $file ) ) { $retval = false;}
            return $retval;
        }//360
        public function exists( $file ) {
            return @file_exists( $file );
        }//407
        public function is_file( $file ) {
            return @is_file( $file );
        }//417
        public function is_dir( $path ) {
            return @is_dir( $path );
        }//429
        public function is_readable( $file ) {
            return @is_readable( $file );
        }//441
        public function is_writable( $file ) {
            return @is_writable( $file );
        }//453
        public function a_time( $file ) {
            return @fileatime( $file );
        }//465
        public function mtime( $file ) {
            return @filemtime( $file );
        }//477
        public function size( $file ) {
            return @filesize( $file );
        }//489
        public function touch( $file, $time = 0, $a_time = 0 ) {
            if ( 0 === $time ) { $time = time();}
            if ( 0 === $a_time ) { $a_time = time();}
            $_file = __DIR__ . $file;
            /** @noinspection PotentialMalwareInspection */
            return touch($_file, $time, $a_time );//todo this needs further attention
        }//507
        public function mkdir( $path, $chmod = false, $ch_own = false, $ch_grp = false ) {
            $path = $this->_untrailingslashit( $path );
            if ( empty( $path ) ) { return false;}
            if ( ! $chmod ) { $chmod = (bool)FS_CH_MOD_DIR;}
            if (!mkdir($path) && !is_dir($path)) { return false;}
            $this->chmod( $path, $chmod );
            if ( $ch_own ) { $this->ch_own( $path, $ch_own );}
            if ( $ch_grp ) { $this->ch_grp( $path, $ch_grp );}
            return true;
        }//533
        public function rm_dir( $path, $recursive = false ) {
            return $this->delete( $path, $recursive );
        }//572
        public function dirlist( $path, $include_hidden = true, $recursive = false ) {
            if ( $this->is_file( $path ) ) {
                $limit_file = basename( $path );
                $path       = dirname( $path );
            } else { $limit_file = false;}
            if ( ! $this->is_dir( $path ) || ! $this->is_readable( $path ) ) {
                return false;
            }
            $dir = dir( $path );
            if ( ! $dir ) { return false;}
            $ret = [];
            while ( false !== ( $entry = $dir->read() ) ) {
                $structure = [];
                $structure['name'] = $entry;
                if ( '.' === $structure['name'] || '..' === $structure['name'] ){ continue;}
                if ( ! $include_hidden && '.' === $structure['name'][0] ){ continue;}
                if ( $limit_file && $structure['name'] !== $limit_file ){ continue;}
                $structure['perms']       = $this->get_head_ch_mods( $path . '/' . $entry );
                $structure['permsn']      = $this->get_num_chmod_from_head( $structure['perms'] );
                $structure['number']      = false;
                $structure['owner']       = $this->owner( $path . '/' . $entry );
                $structure['group']       = $this->group( $path . '/' . $entry );
                $structure['size']        = $this->size( $path . '/' . $entry );
                $structure['lastmodunix'] = $this->mtime( $path . '/' . $entry );
                $structure['lastmod']     = gmdate( 'M j', $structure['lastmodunix'] );
                $structure['time']        = gmdate( 'h:i:s', $structure['lastmodunix'] );
                $structure['type']        = $this->is_dir( $path . '/' . $entry ) ? 'd' : 'f';
                if ( 'd' === $structure['type'] ) {
                    if ( $recursive ) {
                        $structure['files'] = $this->dirlist( $path . '/' . $structure['name'], $include_hidden, $recursive );
                    } else { $structure['files'] = [];}
                }
                $ret[ $structure['name'] ] = $structure;
            }
            $dir->close();
            unset( $dir );
            return $ret;
        }
    }
}else{die;}