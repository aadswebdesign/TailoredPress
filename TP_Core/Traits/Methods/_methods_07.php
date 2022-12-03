<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
if(ABSPATH){
    trait _methods_07{
        /**
         * @description Returns an array containing the current upload directory's path and URL.
         * @param null $time
         * @param bool $create_dir
         * @param bool $refresh_cache
         * @return mixed
         */
        protected function _tp_upload_dir($time = null, $create_dir = true, $refresh_cache = false){
            static $cache = [], $tested_paths = [];
            $key = sprintf('%d-%s', $this->_get_current_blog_id(), (string)$time);

            if ($refresh_cache || empty($cache[$key]))
                $cache[$key] = $this->_raw_upload_dir($time);
            $uploads = $this->_apply_filters('upload_dir', $cache[$key]);
            if ($create_dir) {
                $path = $uploads['path'];
                if (array_key_exists($path, $tested_paths))
                    $uploads['error'] = $tested_paths[$path];
                else {
                    if (!$this->_tp_mkdir_p($path)) {
                        if (0 === strpos($uploads['basedir'], ABSPATH))
                            $error_path = str_replace(ABSPATH, '', $uploads['basedir']) . $uploads['subdir'];
                        else $error_path = $this->_tp_basename($uploads['basedir']) . $uploads['subdir'];
                        $uploads['error'] = sprintf(
                            $this->__('Unable to create directory %s. Is its parent directory writable by the server?'),
                            $this->_esc_html($error_path)
                        );
                    }
                    $tested_paths[$path] = $uploads['error'];
                }
            }
            return $uploads;
        }//2317
        /**
         * @description A non-filtered, non-cached version of tp_upload_dir() that doesn't check the path.
         * @param null $time
         * @return array
         */
        protected function _raw_upload_dir($time = null):array{
            $siteurl = $this->_get_option('siteurl');
            $upload_path = trim($this->_get_option('upload_path'));
            if (empty($upload_path) || 'TP_Content/uploads' === $upload_path)
                $dir = TP_CONTENT_DIR . '/uploads';
            elseif (0 !== strpos($upload_path, ABSPATH))
                $dir = $this->_path_join(ABSPATH, $upload_path);
            else $dir = $upload_path;
            $url = $this->_get_option('upload_url_path');
            if (!$url) {
                if (empty($upload_path) || ('TP_Content/uploads' === $upload_path) || ($upload_path === $dir))
                    $url = TP_CONTENT_URL . '/uploads';
                else $url = $this->_trailingslashit($siteurl) . $upload_path;
            }
            if (defined('UPLOADS') && !($this->_is_multisite() && $this->_get_site_option('ms_files_rewriting'))) {
                $dir = ABSPATH . UPLOADS;
                $url = $this->_trailingslashit($siteurl) . UPLOADS;
            }
            if ($this->_is_multisite() && !($this->_is_main_network() && $this->_is_main_site() && defined('MULTISITE'))) {
                if (!$this->_get_site_option('ms_files_rewriting')) {
                    if (defined('MULTISITE'))
                        $ms_dir = '/sites/' . $this->_get_current_blog_id();
                    else $ms_dir = '/' . $this->_get_current_blog_id();
                    $dir .= $ms_dir;
                    $url .= $ms_dir;
                } elseif (defined('UPLOADS') && !$this->_ms_is_switched()) {
                    if (defined('BLOG_UPLOAD_DIR'))
                        $dir = $this->_untrailingslashit(BLOG_UPLOAD_DIR);
                    else $dir = ABSPATH . UPLOADS;
                    $url = $this->_trailingslashit($siteurl) . 'files';
                }
            }
            $basedir = $dir;
            $baseurl = $url;
            $subdir = '';
            if ($this->_get_option('uploads_use_year_month_folders')) {
                if (!$time) $time = $this->_current_time('mysql');
                $y = substr($time, 0, 4);
                $m = substr($time, 5, 2);
                $subdir = "/$y/$m";
            }
            $dir .= $subdir;
            $url .= $subdir;
            return ['path' => $dir, 'url' => $url, 'subdir' => $subdir, 'basedir' => $basedir, 'baseurl' => $baseurl, 'error' => false,];
        }//2380 _wp_upload_dir
        /**
         * @description Get a filename that is sanitized and unique for the given directory.
         * @param $dir
         * @param $filename
         * @param null $unique_filename_callback
         * @return mixed
         */
        protected function _tp_unique_filename($dir, $filename, $unique_filename_callback = null){
            $this->tp_filename = $this->_sanitize_file_name( $filename );
            $ext2     = null;
            $number        = '';
            $alt_filenames = [];
            $ext  = pathinfo( $this->tp_filename, PATHINFO_EXTENSION );
            $name = pathinfo( $this->tp_filename, PATHINFO_BASENAME );
            if ( $ext ) $ext = '.' . $ext;
            if ( $name === $ext ) $name = '';
            if ( $unique_filename_callback && is_callable( $unique_filename_callback ) )
                $this->tp_filename = $unique_filename_callback($dir, $name, $ext);
            else{
                $f_name = pathinfo( $this->tp_filename, PATHINFO_FILENAME );
                if ( $f_name && preg_match( '/-(?:\d+x\d+|scaled|rotated)$/', $f_name ) ) {
                    $number = 1;
                    $this->tp_filename = str_replace( "{$f_name}{$ext}", "{$f_name}-{$number}{$ext}", $this->tp_filename );
                }
                $file_type = $this->_tp_check_file_type( $this->tp_filename );
                $mime_type = $file_type['type'];
                $is_image    = ( ! empty( $mime_type ) && 0 === strpos( $mime_type, 'image/' ) );
                $upload_dir  = $this->_tp_get_upload_dir();
                $lc_filename = null;
                $lc_ext = strtolower( $ext );
                $_dir   = $this->_trailingslashit( $dir );
                if ( $ext && $lc_ext !== $ext )
                    $lc_filename = preg_replace( '|' . preg_quote( $ext,'$|' ), $lc_ext, $this->tp_filename );
                while ( file_exists( $_dir . $this->tp_filename ) || ( $lc_filename && file_exists( $_dir . $lc_filename ) ) ) {
                    $new_number = (int) $number + 1;
                    if ( $lc_filename )
                        $lc_filename = str_replace(
                            ["_{$number}{$lc_ext}", "{$number}{$lc_ext}"],
                            "_{$new_number}{$lc_ext}",$lc_filename);
                    if ( '' === "{$number}{$ext}" ) $this->tp_filename = "{$this->tp_filename}_{$new_number}";
                    else $this->tp_filename = str_replace(["_{$number}{$ext}", "{$number}{$ext}"],"_{$new_number}{$ext}",$filename);
                    $number = $new_number;
                }
                if ( $lc_filename ) $this->tp_filename = $lc_filename;
                $files = array();
                $count = 10000;
                if ( $name && $ext && @is_dir( $dir ) && false !== strpos( $dir, $upload_dir['basedir'] ) ) {
                    $files = $this->_apply_filters( 'pre_tp_unique_filename_file_list', null, $dir, $this->tp_filename );
                    if ( null === $files ) $files = @scandir( $dir );
                    if ( ! empty( $files ) ) $files = array_diff( $files, array( '.', '..' ) );
                    if ( ! empty( $files ) ) {
                        $count = count( $files );
                        $i = 0;
                        while ( $i <= $count && $this->_tp_check_existing_file_names( $this->tp_filename, $files ) ) {
                            $new_number = (int) $number + 1;
                            $filename = str_replace(["_{$number}{$lc_ext}", "{$number}{$lc_ext}"], "_{$new_number}{$lc_ext}",$filename);
                            $number = $new_number;
                            $i++;
                        }                    }
                }
                if ( $is_image ) {
                    $output_formats = $this->_apply_filters( 'image_editor_output_format', [], $_dir . $this->tp_filename, $mime_type );
                    $alt_types = [];
                    if ( ! empty( $output_formats[ $mime_type ] ) ) {
                        $alt_mime_type = $output_formats[ $mime_type ];
                        $alt_types   = array_keys( array_intersect( $output_formats,[$mime_type, $alt_mime_type]));
                        $alt_types[] = $alt_mime_type;
                    } elseif ( ! empty( $output_formats ) )
                        $alt_types = array_keys( array_intersect( $output_formats,[$mime_type]));
                    $alt_types = array_unique( array_diff( $alt_types,[$mime_type]));
                    foreach ( $alt_types as $alt_type ) {
                        $alt_ext = $this->_tp_get_default_extension_for_mime_type( $alt_type );
                        if ( ! $alt_ext )continue;
                        $alt_ext = ".{$alt_ext}";
                        $alt_filename = preg_replace( '|' . preg_quote( $lc_ext,'$|' ) , $alt_ext, $this->tp_filename );
                        $alt_filenames[ $alt_ext ] = $alt_filename;
                    }
                    if ( ! empty( $alt_filenames ) ) {
                        $alt_filenames[ $lc_ext ] = $this->tp_filename;
                        $i = 0;
                        while ( $i <= $count && $this->_tp_check_alternate_file_names( $alt_filenames, $_dir, $files ) ) {
                            $new_number = (int) $number + 1;
                            foreach ( $alt_filenames as $alt_ext => $alt_filename )
                                $alt_filenames[ $alt_ext ] = str_replace(
                                    ["_{$number}{$alt_ext}", "{$number}{$alt_ext}"],
                                    "_{$new_number}{$alt_ext}",$alt_filename);
                            $this->tp_filename = str_replace(
                                ["_{$number}{$lc_ext}", "{$number}{$lc_ext}"],
                                "_{$new_number}{$lc_ext}",$this->tp_filename);
                            $number = $new_number;
                            $i++;
                        }
                    }
                }
            }
            return $this->_apply_filters( 'tp_unique_filename', $this->tp_filename, $ext, $dir, $unique_filename_callback, $alt_filenames, $number );
        }//2502
        /**
         * @description Helper function to test if each of an array of file names could conflict with existing files.
         * @param $file_names
         * @param $dir
         * @param $files
         * @return bool
         */
        protected function _tp_check_alternate_file_names( $file_names, $dir, $files ):bool{
            foreach ( $file_names as $filename ) {
                if ( file_exists( $dir . $filename ) ) return true;
                if ( ! empty( $files ) && $this->_tp_check_existing_file_names( $filename, $files ) )
                    return true;
            }
            return false;
        }//2761
        /**
         * @description Helper function to check if a file name could match an existing image sub-size file name.
         * @param $filename
         * @param $files
         * @return bool
         */
        protected function _tp_check_existing_file_names( $filename, $files ):bool{
            $f_name = pathinfo( $filename, PATHINFO_FILENAME );
            $ext   = pathinfo( $filename, PATHINFO_EXTENSION );
            if ( empty( $f_name ) ) return false;
            if ( $ext ) $ext = ".$ext";
            $regex = '/^' . preg_quote( $f_name,'_(?:\d+x\d+|scaled|rotated)' ) . preg_quote( $ext,'$/i');
            foreach ( $files as $file ) { if ( preg_match( $regex, $file ) ) return true;}
            return false;
        }//2785
        /**
         * @description Create a file in the upload folder with given content.
         * @param $name
         * @param $bits
         * @param null $time
         * @return array|mixed
         */
        protected function _tp_upload_bits( $name, $bits, $time = null ){
            if ( empty( $name ) ) return array( 'error' => $this->__( 'Empty filename' ) );
            $tp_file_type = $this->_tp_check_file_type( $name );
            if ( ! $tp_file_type['ext'] && ! $this->_current_user_can( 'unfiltered_upload' ) )
                return array( 'error' => $this->__( 'Sorry, you are not allowed to upload this file type.' ) );
            $upload = $this->_tp_upload_dir( $time );
            if ( false !== $upload['error'] ) return $upload;
            $upload_array = ['name' => $name,'bits' => $bits,'time' => $time,];
            $upload_bits_error = $this->_apply_filters('tp_upload_bits',$upload_array);
            if ( ! is_array( $upload_bits_error ) ) {
                $upload['error'] = $upload_bits_error;
                return $upload;
            }
            $filename = $this->_tp_unique_filename( $upload['path'], $name );
            $new_file = $upload['path'] . "/$filename";
            if ( ! $this->_tp_mkdir_p( dirname( $new_file ) ) ) {
                if ( 0 === strpos( $upload['basedir'], ABSPATH ) )
                    $error_path = str_replace( ABSPATH, '', $upload['basedir'] ) . $upload['subdir'];
                else $error_path = $this->_tp_basename( $upload['basedir'] ) . $upload['subdir'];
                $message = sprintf(
                    $this->__( 'Unable to create directory %s. Is its parent directory writable by the server?' ),
                    $error_path
                );
                return array( 'error' => $message );
            }
            $ifp = @fopen( $new_file, 'tb' );
            if ( ! $ifp )  return ['error' => sprintf( $this->__( 'Could not write file %s' ), $new_file ),];
            fwrite( $ifp, $bits );
            fclose( $ifp );
            clearstatcache();
            $stat  = @ stat( dirname( $new_file ) );
            $perms = $stat['mode'] & 0007777;
            $perms &= 0000666;
            chmod( $new_file, $perms );
            clearstatcache();
            $url = $upload['url'] . "/$filename";
            if ( $this->_is_multisite() ) $this->_clean_dir_size_cache( $new_file );
            $upload_handle_array = ['file' => $new_file,'url' => $url,'type' => $this->tp_filetype['type'],'error' => false,];
            return $this->_apply_filters('tp_handle_upload', $upload_handle_array,'side_load');
        }//2839
        /**
         * @description Retrieve the file type based on the extension name.
         * @param $ext
         * @return int|null|string
         */
        protected function _tp_ext2type( $ext ){
            $ext = strtolower( $ext );
            $ext2type = $this->_tp_get_ext_types();
            foreach ( $ext2type as $type => $exts ) {
                if ( in_array( $ext, $exts, true ) ) return $type;
            }
            return null;
        }//2947
        /**
         * @description Returns first matched extension for the mime-type,
         * @description . as mapped from wp_get_mime_types().
         * @param $mime_type
         * @return bool
         */
        protected function _tp_get_default_extension_for_mime_type( $mime_type ):bool{
            $extensions = explode( '|', array_search( $mime_type, $this->_tp_get_mime_types(), true ) );
            if ( empty( $extensions[0] ) ) return false;
            return $extensions[0];
        }//2968
        /**
         * @description Retrieve the file type from the file name.
         * @param $filename
         * @param null $mimes
         * @return array
         */
        protected function _tp_check_file_type( $filename, $mimes = null ):array{
            if ( empty( $mimes ) ) $mimes = $this->_get_allowed_mime_types();
            $type = false;
            $ext  = false;
            foreach ( $mimes as $ext_preg => $mime_match ) {
                $ext_preg = '!\.(' . $ext_preg . ')$!i';
                if ( preg_match( $ext_preg, $filename, $ext_matches ) ) {
                    $type = $mime_match;
                    $ext  = $ext_matches[1];
                    break;
                }
            }
            return compact( 'ext', 'type' );
        }//2994
        /**
         * @description Attempt to determine the real file type of a file.
         * @param $file
         * @param $filename
         * @param null $mimes
         * @return array
         */
        protected function _tp_check_file_type_and_ext( $file, $filename, $mimes = null ):array{
            $proper_filename = false;
            $tp_file_type = $this->_tp_check_file_type( $filename, $mimes );
            $ext         = $tp_file_type['ext'];
            $type        = $tp_file_type['type'];
            if ( ! file_exists( $file ) ) return compact( 'ext', 'type', 'proper_filename' );
            $real_mime = false;
            if ( $type && 0 === strpos( $type, 'image/' ) ) {
                $real_mime = $this->_tp_get_image_mime( $file );
                if ( $real_mime && $real_mime !== $type ) {
                    $mime_to_ext = $this->_apply_filters(
                        'get_image_size_mimes_to_exts',
                        ['image/jpeg' => 'jpg','image/png' => 'png','image/gif' => 'gif',
                            'image/bmp' => 'bmp','image/tiff' => 'tif','image/webp' => 'webp',]
                    );
                    if ( ! empty( $mime_to_ext[ $real_mime ] ) ) {
                        $filename_parts = explode( '.', $filename );
                        array_pop( $filename_parts );
                        $filename_parts[] = $mime_to_ext[ $real_mime ];
                        $new_filename     = implode( '.', $filename_parts );
                        if ( $new_filename !== $filename )$proper_filename = $new_filename;
                        $tp_file_type = $this->_tp_check_file_type( $new_filename, $mimes );
                        $ext         = $tp_file_type['ext'];
                        $type        = $tp_file_type['type'];
                    } else $real_mime = false;
                }
            }
            if ( $type && ! $real_mime && extension_loaded( 'file_info' ) ) {
                $f_info     = finfo_open( FILEINFO_MIME_TYPE );
                $real_mime = finfo_file( $f_info, $file );
                finfo_close( $f_info );
                $nonspecific_types = ['application/octet-stream','application/encrypted','application/CDFV2-encrypted','application/zip',];
                if ( in_array( $real_mime, $nonspecific_types, true ) ) {
                    if ( ! in_array( substr( $type, 0, strcspn( $type, '/' ) ), array( 'application', 'video', 'audio' ), true ) ) {
                        $type = false;
                        $ext  = false;
                    }
                } elseif ( 0 === strpos( $real_mime, 'video/' ) || 0 === strpos( $real_mime, 'audio/' ) ) {
                    if ( substr( $real_mime, 0, strcspn( $real_mime, '/' ) ) !== substr( $type, 0, strcspn( $type, '/' ) ) ) {
                        $type = false;
                        $ext  = false;
                    }
                } elseif ( 'text/plain' === $real_mime ) {
                    if ( ! in_array($type,['text/plain','text/csv','application/csv','text/rich_text','text/tsv','text/vtt',],true)) {
                        $type = false;
                        $ext  = false;
                    }
                } elseif ( 'application/csv' === $real_mime ) {
                    // Special casing for CSV files.
                    if ( ! in_array($type,['text/csv','text/plain','application/csv',],true)) {
                        $type = false;
                        $ext  = false;
                    }
                } elseif ( 'text/rtf' === $real_mime ) {
                    if ( ! in_array($type,['text/rtf','text/plain','application/rtf',],true)){
                        $type = false;
                        $ext  = false;
                    }
                } else if ( $type !== $real_mime ) {
                    $type = false;
                    $ext  = false;
                }
            }
            if ( $type ) {
                $allowed = $this->_get_allowed_mime_types();
                if ( ! in_array( $type, $allowed, true ) ) {
                    $type = false;
                    $ext  = false;
                }
            }
            return $this->_apply_filters( 'tp_check_filetype_and_ext', compact( 'ext', 'type', 'proper_filename' ), $file, $filename, $mimes, $real_mime );
        }//3037
    }
}else die;