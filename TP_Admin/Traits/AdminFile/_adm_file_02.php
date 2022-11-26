<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-6-2022
 * Time: 09:01
 */
namespace TP_Admin\Traits\AdminFile;
use TP_Admin\Traits\AdminInits\_adm_init_files;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\PHP\SodiumCompat\src\ParagonIE_Compat as PC;
use TP_Core\Libs\PHP\SodiumCompat\lib\SodiumConstants72 as SC72;
use TP_Admin\Libs\AdmFilesystem\Adm_Filesystem_Direct;
use TP_Admin\Libs\Adm_Zip;
if(ABSPATH){
    trait _adm_file_02{
        use _init_error;
        use _adm_init_files;
        /**
         * @description Wrapper for _tp_handle_upload().
         * @param $file
         * @param bool $overrides
         * @param null $time
         * @return mixed
         */
        protected function _tp_handle_upload( &$file, $overrides = false, $time = null){
            $action = $overrides['action'] ?? '_tp_handle_upload';
            return $this->_tp_handle_upload_action( $file, $overrides, $time, $action );
        }//1064
        /**
         * @description Wrapper for _tp_handle_upload().
         * @param $file
         * @param bool $overrides
         * @param null $time
         * @return mixed
         */
        protected function _tp_handle_sideload( &$file, $overrides = false, $time = null ){
            $action = $overrides['action'] ?? 'tp_handle_sideload';
            return $this->_tp_handle_upload_action( $file, $overrides, $time, $action );
        }//1095
        /**
         * @description Downloads a URL to a local temporary file using the TailoredPress HTTP API.
         * @param $url
         * @param int $timeout
         * @param bool $signature_verification
         * @return mixed
         */
        protected function _download_url( $url, $timeout = 300, $signature_verification = false ){
            if ( ! $url ) {
                return new TP_Error( 'http_no_url', $this->__( 'Invalid URL Provided.' ) );
            }
            $url_path     = parse_url( $url, PHP_URL_PATH );
            $url_filename = '';
            if ( is_string( $url_path ) && '' !== $url_path ){ $url_filename = basename( $url_path );}
            $tmpfname = $this->_tp_temp_name( $url_filename );
            if (!$tmpfname){ return new TP_Error( 'http_no_file', $this->__('Could not create temporary file.'));}
            $response = $this->_tp_safe_remote_get( $url, ['timeout'=> $timeout,'stream'=> true,'filename' => $tmpfname,]);
            if ( $this->_init_error( $response ) ) {
                unlink( $tmpfname );
                return $response;
            }
            $response_code = $this->_tp_remote_retrieve_response_code( $response );
            if ( 200 !== $response_code ) {
                $data = ['code' => $response_code,];
                $tmpf = fopen( $tmpfname, 'rb' );
                if ( $tmpf ) {
                    $response_size = $this->_apply_filters( 'download_url_error_max_body_size', KB_IN_BYTES );
                    $data['body'] = fread( $tmpf, $response_size );
                    fclose( $tmpf );
                }
                unlink( $tmpfname );
                return new TP_Error( 'http_404', trim( $this->_tp_remote_retrieve_response_message( $response ) ), $data );
            }
            $content_disposition = $this->_tp_remote_retrieve_header( $response, 'content-disposition' );
            if ( $content_disposition ) {
                $content_disposition = strtolower( $content_disposition );
                if ( 0 === strpos( $content_disposition, 'attachment; filename=' ) ) {
                    $tmpfname_disposition = $this->_sanitize_file_name( substr( $content_disposition, 21 ) );
                } else { $tmpfname_disposition = '';}
                if ( $tmpfname_disposition && is_string( $tmpfname_disposition ) && ( 0 === $this->_validate_file( $tmpfname_disposition ))){
                    $tmpfname_disposition = dirname( $tmpfname ) . '/' . $tmpfname_disposition;
                    if (rename( $tmpfname, $tmpfname_disposition)){ $tmpfname = $tmpfname_disposition;}
                    if ( ( $tmpfname !== $tmpfname_disposition ) && file_exists( $tmpfname_disposition ) ) {
                        unlink( $tmpfname_disposition );
                    }
                }
            }
            $content_md5 = $this->_tp_remote_retrieve_header( $response, 'content-md5' );
            if ( $content_md5 ) {
                $md5_check = $this->_verify_file_md5( $tmpfname, $content_md5 );
                if ( $this->_init_error( $md5_check ) ) {
                    unlink( $tmpfname );
                    return $md5_check;
                }
            }
            if ( $signature_verification ) {
                $signed_host_names = $this->_apply_filters( 'tp_signature_hosts', array( 'aadswebdesign.nl', 's.w.org' ) );
                $signature_verification = in_array( parse_url( $url, PHP_URL_HOST ), $signed_host_names, true );
            }
            if ( $signature_verification ) {
                $signature = $this->_tp_remote_retrieve_header( $response, 'x-content-signature' );
                if ( ! $signature ) {
                    $signature_url = false;
                    if ( is_string( $url_path ) && ( '.zip' === substr( $url_path, -4 ) || '.tar.gz' === substr( $url_path, -7 ) ) ) {
                        $signature_url = str_replace( $url_path, $url_path . '.sig', $url );
                    }
                    $signature_url = $this->_apply_filters( 'tp_signature_url', $signature_url, $url );
                    if ( $signature_url ) {
                        $signature_request = $this->_tp_safe_remote_get( $signature_url,['limit_response_size' => 10 * KB_IN_BYTES,]);
                        if ( ! $this->_init_error( $signature_request ) && 200 === $this->_tp_remote_retrieve_response_code( $signature_request ) ) {
                            $signature = explode( "\n", $this->_tp_remote_retrieve_body( $signature_request ) );
                        }
                    }
                }
                $signature_verification = $this->_verify_file_signature( $tmpfname, $signature, $url_filename );
            }
            if ( $this->_init_error( $signature_verification ) ) {
                if ( $this->_apply_filters( 'tp_signature_soft_fail', true, $url )) {
                    $signature_verification->add_data( $tmpfname, 'soft_fail-filename' );
                } else { unlink( $tmpfname );}
                return $signature_verification;
            }
            return $tmpfname;
        }//1124
        /**
         * @description Calculates and compares the MD5 of a file to its expected value.
         * @param $filename
         * @param $expected_md5
         * @return bool|TP_Error
         */
        protected function _verify_file_md5( $filename, $expected_md5 ){
            if ( 32 === strlen( $expected_md5 ) ) { $expected_raw_md5 = pack( 'H*', $expected_md5 );
            } elseif ( 24 === strlen( $expected_md5 ) ) { $expected_raw_md5 = base64_decode( $expected_md5 );
            } else { return false;}
            $file_md5 = md5_file( $filename, true );
            if ( $file_md5 === $expected_raw_md5 ) {return true;}
            return new TP_Error('md5_mismatch',
                sprintf($this->__( 'The checksum of the file (%1$s) does not match the expected checksum value (%2$s).' ),
                    bin2hex( $file_md5 ), bin2hex( $expected_raw_md5 )));
        }//1317
        /**
         * @param $filename
         * @param $signatures
         * @param bool $filename_for_errors
         * @return mixed
         */
        protected function _verify_file_signature( $filename, $signatures, $filename_for_errors = false ){
            if ( ! $filename_for_errors ) { $filename_for_errors = (bool)$this->_tp_basename( $filename );}
            if ( ! function_exists( 'sodium_crypto_sign_verify_detached' ) || ! in_array( 'sha384', array_map( 'strtolower', hash_algos() ), true ) ) {
                return new TP_Error('signature_verification_unsupported',
                    sprintf( $this->__( 'The authenticity of %s could not be verified as signature verification is unavailable on this system.' ),
                        "<span class='code'>{$this->_esc_html( $filename_for_errors )}</span>"
                    ),( ! function_exists( 'sodium_crypto_sign_verify_detached' ) ? 'sodium_crypto_sign_verify_detached' : 'sha384' ) );
            }
            if ( ! extension_loaded( 'sodium' ) && in_array( PHP_VERSION_ID, array( 70200, 70201, 70202 ), true ) && extension_loaded( 'opcache' )) {
                return new TP_Error('signature_verification_unsupported',
                    sprintf( $this->__( 'The authenticity of %s could not be verified as signature verification is unavailable on this system.' ),
                        "<span class='code'>{$this->_esc_html( $filename_for_errors )}</span>"
                    ),['php' => PHP_VERSION,'sodium' => defined( 'SODIUM_LIBRARY_VERSION' ) ?: SC72::SODIUM_LIBRARY_VERSION ,]
                );
            }
            if ( ! extension_loaded( 'sodium' ) && ! PC::polyfill_is_fast() ) {
                $sodium_compat_is_fast = false;
                if ( ! $sodium_compat_is_fast ) {
                    $_pc_version = ( defined( 'ParagonIE_Sodium_Compat::VERSION_STRING' ) ? PC::VERSION_STRING : false );
                    return new TP_Error('signature_verification_unsupported',
                        sprintf( $this->__( 'The authenticity of %s could not be verified as signature verification is unavailable on this system.' ),
                            "<span class='code'>{$this->_esc_html( $filename_for_errors )}</span>"
                        ),['php' => PHP_VERSION,'sodium' => defined( 'SODIUM_LIBRARY_VERSION' ) ? SODIUM_LIBRARY_VERSION : $_pc_version,
                            'polyfill_is_fast' => false, 'max_execution_time' => ini_get( 'max_execution_time' ),]);
                }
            }
            if ( ! $signatures ) {
                return new TP_Error('signature_verification_no_signature',
                    sprintf($this->__( 'The authenticity of %s could not be verified as no signature was found.' ),
                        "<span class='code'>{$this->_esc_html( $filename_for_errors )}</span>"
                    ),['filename' => $filename_for_errors,]);
            }
            $trusted_keys = $this->_tp_trusted_keys();
            $file_hash    = hash_file( 'sha384', $filename, true );
            $this->_mb_string_binary_safe_encoding();
            $skipped_key       = 0;
            $skipped_signature = 0;
            foreach ( (array) $signatures as $signature ) {
                $signature_raw = base64_decode( $signature );
                if ( SC72::SODIUM_CRYPTO_SIGN_BYTES !== strlen( $signature_raw ) ) {
                    $skipped_signature++;
                    continue;
                }
                foreach ( (array) $trusted_keys as $key ) {
                    $key_raw = base64_decode( $key );
                    if ( SC72::SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES !== strlen( $key_raw ) ) {
                        $skipped_key++;
                        continue;
                    }
                    if ( sodium_crypto_sign_verify_detached( $signature_raw, $file_hash, $key_raw ) ) {
                        $this->_reset_mb_string_encoding();
                        return true;
                    }
                }
            }
            $this->_reset_mb_string_encoding();
            $_pc_version = ( defined( 'ParagonIE_Sodium_Compat::VERSION_STRING' ) ? PC::VERSION_STRING : false );
            return new TP_Error('signature_verification_failed',
                sprintf( $this->__( 'The authenticity of %s could not be verified.' ),
                    "<span class='code'>{$this->_esc_html( $filename_for_errors )}</span>"
                ),['filename' => $filename_for_errors,'keys' => $trusted_keys,'signatures' => $signatures,
                    'hash' => bin2hex( $file_hash ),'skipped_key' => $skipped_key,'skipped_sig' => $skipped_signature,
                    'php' => PHP_VERSION, 'sodium' => defined( 'SODIUM_LIBRARY_VERSION' ) ? SODIUM_LIBRARY_VERSION : $_pc_version,]);
        }//1354
        /**
         * @description Retrieves the list of signing keys trusted by TailoredPress.
         * @return mixed
         */
        protected function _tp_trusted_keys(){
            $trusted_keys = [];
            if ( time() < 1617235200 ) {
                $trusted_keys[] = 'fRPyrxb/MvVLbdsYi+OOEv4xc+Eqpsj+kkAS6gNOkI0=';
            }
            // TODO: Add key #2 with longer expiration.
            return $this->_apply_filters( 'tp_trusted_keys', $trusted_keys );
        }//1509
        /**
         * @descriptionUnzips a specified ZIP file to a location on the filesystem via the TailoredPress
         * @descriptionUnzips Filesystem Abstraction.
         * @param $file
         * @param $to
         * @return string|TP_Error
         */
        protected function _unzip_file( $file, $to ){
            //$this->tp_file_system = $this->_init_files();
            $file_system = $this->_init_files();
            if ( ! $file_system || ! is_object( $file_system ) ) {
                return new TP_Error( 'fs_unavailable', $this->__( 'Could not access filesystem.' ) );
            }
            $this->_tp_raise_memory_limit( 'admin' );
            $needed_dirs = [];
            $to          = $this->_trailingslashit( $to );
            if (! $file_system->is_dir( $to ) ) { // Only do parents if no children exist.
                $path = preg_split( '![/\\\]!', $this->_untrailingslashit( $to ) );
                for ( $i = count( $path ); $i >= 0; $i-- ) {
                    if ( empty( $path[ $i ] ) ) { continue;}
                    $dir = implode( '/', array_slice( $path, 0, $i + 1 ) );
                    if ( preg_match( '!^[a-z]:$!i', $dir ) ) {  continue;}
                    if ( ! $file_system->is_dir( $dir ) ) {$needed_dirs[] = $dir;}
                    else { break; }
                }
            }
            if ( class_exists( 'ZipArchive', false ) && $this->_apply_filters( 'unzip_file_use_ziparchive', true ) ) {
                $result = $this->__unzip_file_ziparchive( $file, $to, $needed_dirs );
                if (true === $result) {return $result;}
                if ($result instanceof TP_Error && $this->_init_error($result) && 'incompatible_archive' !== $result->get_error_code()) {
                    return $result;
                }
            }
            return $this->__unzip_file_pclzip( $file, $to, $needed_dirs );
        }//1547
        /**
         * @description Attempts to unzip an archive using the ZipArchive class.
         * @param $file
         * @param $to
         * @param array $needed_dirs
         * @return bool|TP_Error
         */
        private function __unzip_file_ziparchive( $file, $to, $needed_dirs = array() ){
            $z = new \ZipArchive();
            $zopen = $z->open( $file, \ZipArchive::CHECKCONS );
            if ( true !== $zopen ) {
                return new TP_Error( 'incompatible_archive', $this->__( 'Incompatible Archive.' ), array( 'ziparchive_error' => $zopen ) );
            }
            $uncompressed_size = 0;
            for ( $i = 0; $i < $z->numFiles; $i++ ) {
                $info = $z->statIndex( $i );
                if ( ! $info ) {
                    return new TP_Error( 'stat_failed_ziparchive', $this->__( 'Could not retrieve file from archive.' ) );
                }
                if (strpos($info['name'], '__MACOSX/') === 0) {  continue;}// Skip the OS X-created __MACOSX directory.
                // Don't extract invalid files:
                if ( 0 !== $this->_validate_file( $info['name'] ) ) { continue;}
                $uncompressed_size += $info['size'];
                $dirname = dirname( $info['name'] );
                if ( '/' === substr( $info['name'], -1 ) ) {$needed_dirs[] = $to . $this->_untrailingslashit( $info['name'] );
                } elseif ( '.' !== $dirname ) { $needed_dirs[] = $to . $this->_untrailingslashit( $dirname );}
            }
            if ( $this->_tp_doing_cron() ) {
                $available_space = @disk_free_space( TP_CONTENT_DIR );
                if ( $available_space && ( $uncompressed_size * 2.1 ) > $available_space ) {
                    return new TP_Error('disk_full_unzip_file',
                        $this->__( 'Could not copy files. You may have run out of disk space.' ),
                        compact( 'uncompressed_size', 'available_space' ));
                }
            }
            $needed_dirs = array_unique( $needed_dirs );
            foreach ( $needed_dirs as $dir ) {
                if ( $this->_untrailingslashit( $to ) === $dir ) { continue;}
                if ( strpos( $dir, $to ) === false ) {  continue;}
                $parent_folder = dirname( $dir );
                while ( ! empty( $parent_folder ) && $this->_untrailingslashit( $to ) !== $parent_folder  && ! in_array( $parent_folder, $needed_dirs, true )) {
                    $needed_dirs[] = $parent_folder;
                    $parent_folder = dirname( $parent_folder );
                }
            }
            asort( $needed_dirs );
            foreach ( $needed_dirs as $_dir ) {
                if ($this->tp_file_system instanceof Adm_Filesystem_Direct &&  ! $this->tp_file_system->mkdir( $_dir, FS_CH_MOD_DIR ) && ! $this->tp_file_system->is_dir( $_dir ) ) {
                    return new TP_Error( 'mkdir_failed_ziparchive', $this->__( 'Could not create directory.' ), substr( $_dir, strlen( $to ) ) );
                }
            }
            for ( $i = 0; $i < $z->numFiles; $i++ ) {
                $info = $z->statIndex( $i );
                if ( ! $info ) { return new TP_Error( 'stat_failed_ziparchive', $this->__( 'Could not retrieve file from archive.' ) );}
                if ( '/' === substr( $info['name'], -1 ) ) {  continue;}
                if (strpos($info['name'], '__MACOSX/') === 0) {  continue;}// Don't extract the OS X-created __MACOSX directory files.
                if ( 0 !== $this->_validate_file( $info['name'] ) ) { continue; }
                $contents = $z->getFromIndex( $i );
                if ( false === $contents ) {
                    return new TP_Error( 'extract_failed_ziparchive', $this->__( 'Could not extract file from archive.' ), $info['name'] );
                }
                if ($this->tp_file_system instanceof Adm_Filesystem_Direct &&   ! $this->tp_file_system->put_contents( $to . $info['name'], $contents, FS_CH_MOD_FILE ) ) {
                    return new TP_Error( 'copy_failed_ziparchive', $this->__( 'Could not copy file.' ), $info['name'] );
                }
            }
            $z->close();
            return true;
        }//1621
        /**
         * @description Attempts to unzip an archive using the PclZip library.
         * @param $file
         * @param $to
         * @param array $needed_dirs
         * @return bool|TP_Error
         */
        private function __unzip_file_pclzip( $file, $to, $needed_dirs = array() ){
            //$this->tp_file_system = $this->_init_files();
            $file_system = $this->_init_files();
            $this->_mb_string_binary_safe_encoding();
            $archive = new Adm_Zip( $file );
            $archive_files = $archive->extractZip( PCLZIP_OPT_EXTRACT_AS_STRING );
            $this->_reset_mb_string_encoding();
            if ( ! is_array( $archive_files ) ) {
                return new TP_Error( 'incompatible_archive', $this->__( 'Incompatible Archive.' ), $archive->error_info( true ) );
            }
            if ( 0 === count( $archive_files ) ) { return new TP_Error( 'empty_archive_pclzip', $this->__( 'Empty archive.' ) );}
            $uncompressed_size = 0;
            // Determine any children directories needed (From within the archive).
            /** @noinspection SuspiciousLoopInspection *///todo
            foreach ($archive_files as $file ) {
                if (strpos($file['filename'], '__MACOSX/') === 0) {  continue;}// Skip the OS X-created __MACOSX directory.
                $uncompressed_size += $file['size'];
                $needed_dirs[] = $to . $this->_untrailingslashit( $file['folder'] ? $file['filename'] : dirname( $file['filename'] ) );
            }
            if ( $this->_tp_doing_cron() ) {
                $available_space = @disk_free_space( TP_CONTENT_DIR );
                if ( $available_space && ( $uncompressed_size * 2.1 ) > $available_space ) {
                    return new TP_Error('disk_full_unzip_file',
                        $this->__( 'Could not copy files. You may have run out of disk space.' ),
                        compact( 'uncompressed_size', 'available_space' ));
                }
            }
            $needed_dirs = array_unique( $needed_dirs );
            foreach ( $needed_dirs as $dir ) {
                // Check the parent folders of the folders all exist within the creation array.
                if ( $this->_untrailingslashit( $to ) === $dir ) {  continue;}// Skip over the working directory, we know this exists (or will exist).
                if ( strpos( $dir, $to ) === false ) {  continue;}// If the directory is not within the working directory, skip it.
                $parent_folder = dirname( $dir );
                while ( ! empty( $parent_folder ) && $this->_untrailingslashit( $to ) !== $parent_folder  && ! in_array( $parent_folder, $needed_dirs, true )) {
                    $needed_dirs[] = $parent_folder;
                    $parent_folder = dirname( $parent_folder );
                }
            }
            asort( $needed_dirs );
            // Create those directories if need be:
            foreach ( $needed_dirs as $_dir ) {
                if (! $file_system->mkdir( $_dir, FS_CH_MOD_DIR ) && ! $file_system->is_dir( $_dir ) ) {
                    return new TP_Error( 'mkdir_failed_pclzip', $this->__( 'Could not create directory.' ), substr( $_dir, strlen( $to ) ) );
                }
            }
            // Extract the files from the zip.
            /** @noinspection SuspiciousLoopInspection *///todo
            foreach ( $archive_files as $file ) {
                if ( $file['folder'] ) { continue;}
                if (strpos($file['filename'], '__MACOSX/') === 0) {  continue;}// Don't extract the OS X-created __MACOSX directory files.
                // Don't extract invalid files:
                if ( 0 !== $this->_validate_file( $file['filename'] ) ) { continue;}
                if (! $file_system->put_contents( $to . $file['filename'], $file['content'], FS_CH_MOD_FILE ) ) {
                    return new TP_Error( 'copy_failed_pclzip', $this->__( 'Could not copy file.' ), $file['filename'] );
                }
            }
            return true;
        }//1769
        /**
         * @descriptionCopies a directory from one location to another via the TailoredPress Filesystem* Abstraction.
         * @param $from
         * @param $to
         * @param array $skip_list
         * @return bool|TP_Error
         */
        protected function _copy_dir( $from, $to, $skip_list = array() ){
            //$this->tp_file_system = $this->_init_files();
            $file_system = $this->_init_files();
            $dirlist = $file_system->dirlist( $from );
            if ( false === $dirlist ) {
                return new TP_Error( 'dirlist_failed_copy_dir', $this->__( 'Directory listing failed.' ), basename( $to ) );
            }
            $from = $this->_trailingslashit( $from );
            $to   = $this->_trailingslashit( $to );
            foreach ( (array) $dirlist as $filename => $fileinfo ) {
                if ( in_array( $filename, $skip_list, true ) ) { continue; }
                if ( 'f' === $fileinfo['type'] ) {
                    if ( ! $file_system->copy( $from . $filename, $to . $filename, true, FS_CH_MOD_FILE ) ) {
                        $file_system->chmod( $to . $filename, FS_CH_MOD_FILE );
                        if ( ! $file_system->copy( $from . $filename, $to . $filename, true, FS_CH_MOD_FILE ) ) {
                            return new TP_Error( 'copy_failed_copy_dir', $this->__( 'Could not copy file.' ), $to . $filename );
                        }
                    }
                    $this->_tp_opcache_invalidate( $to . $filename );
                } elseif ( 'd' === $fileinfo['type'] ) {
                    if (!$file_system->is_dir($to . $filename) && !$file_system->mkdir($to . $filename, FS_CH_MOD_DIR)) {
                        return new TP_Error( 'mkdir_failed_copy_dir', $this->__( 'Could not create directory.' ), $to . $filename );
                    }
                    $sub_skip_list = array();
                    foreach ( $skip_list as $skip_item ) {
                        if ( 0 === strpos( $skip_item, $filename . '/' ) ) {
                            $sub_skip_list[] = preg_replace( '!^' . preg_quote( $filename, '!' ) . '/!i', '', $skip_item );
                        }
                    }
                    $result = $this->_copy_dir( $from . $filename, $to . $filename, $sub_skip_list );
                    if ( $this->_init_error( $result ) ) { return $result;}
                }
            }
            return true;
        }//1893
    }
}else die;