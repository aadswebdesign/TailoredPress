<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-6-2022
 * Time: 09:01
 */
namespace TP_Admin\Traits\AdminFile;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Theme;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _adm_file_01{
        use _init_error;
        /**
         * @description Gets the description for standard TailoredPress theme files. not needed?
         * @return string
         */
        protected function _get_file_description():string{
            return [
                //'functions.php'         => __( 'Theme Functions' ),
                //'header.php'            => __( 'Theme Header' ),
                //'footer.php'            => __( 'Theme Footer' ),
                //'sidebar.php'           => __( 'Sidebar' ),
                //'comments.php'          => __( 'Comments' ),
                //'searchform.php'        => __( 'Search Form' ),
                //'404.php'               => __( '404 Template' ),
                //'link.php'              => __( 'Links Template' ),
                // Archives.
                'index.php'             => $this->__( 'Main Index Template' ),
                'archive.php'           => $this->__( 'Archives' ),
                'author.php'            => $this->__( 'Author Template' ),
                'taxonomy.php'          => $this->__( 'Taxonomy Template' ),
                'category.php'          => $this->__( 'Category Template' ),
                'tag.php'               => $this->__( 'Tag Template' ),
                'home.php'              => $this->__( 'Posts Page' ),
                'search.php'            => $this->__( 'Search Results' ),
                'date.php'              => $this->__( 'Date Template' ),
                // Content.
                'singular.php'          => $this->__( 'Singular Template' ),
                'single.php'            => $this->__( 'Single Post' ),
                'page.php'              => $this->__( 'Single Page' ),
                'front-page.php'        => $this->__( 'Homepage' ),
                'privacy-policy.php'    => $this->__( 'Privacy Policy Page' ),
                // Attachments.
                'attachment.php'        => $this->__( 'Attachment Template' ),
                'image.php'             => $this->__( 'Image Attachment Template' ),
                'video.php'             => $this->__( 'Video Attachment Template' ),
                'audio.php'             => $this->__( 'Audio Attachment Template' ),
                'application.php'       => $this->__( 'Application Attachment Template' ),
                // Embeds.
                'embed.php'             => $this->__( 'Embed Template' ),
                'embed-404.php'         => $this->__( 'Embed 404 Template' ),
                'embed-content.php'     => $this->__( 'Embed Content Template' ),
                'header-embed.php'      => $this->__( 'Embed Header Template' ),
                'footer-embed.php'      => $this->__( 'Embed Footer Template' ),
                // Stylesheets.
                'style.css'             => $this->__( 'Stylesheet' ),
                'editor-style.css'      => $this->__( 'Visual Editor Stylesheet' ),
                'editor-style-rtl.css'  => $this->__( 'Visual Editor RTL Stylesheet' ),
                'rtl.css'               => $this->__( 'RTL Stylesheet' ),
                // Other.
                //'my-hacks.php'          => __( 'my-hacks.php (legacy hacks support)' ),
                '.htaccess'             => $this->__( '.htaccess (for rewrite rules )' ),
                // Deprecated files.
                'tp-layout.css'         => $this->__( 'Stylesheet' ),
                'tp-comments.php'       => $this->__( 'Comments Template' ),
                'tp-comments-popup.php' => $this->__( 'Popup Comments Template' ),
                'comments-popup.php'    => $this->__( 'Popup Comments' ),
            ];
        }//79 //todo needs edits
        /**
         * @description Gets the absolute filesystem path to the root of the TailoredPress installation.
         * @return string
         */
        protected function _get_home_path():string{
            $protocol = 'https' ?: 'http';
            $home    = $this->_set_url_scheme( $this->_get_option( 'home' ), $protocol );
            $siteurl = $this->_set_url_scheme( $this->_get_option( 'siteurl' ), $protocol );
            if ( ! empty( $home ) && 0 !== strcasecmp( $home, $siteurl ) ) {
                $tp_path_rel_to_home = str_ireplace( $home, '', $siteurl ); /* $siteurl - $home */
                $pos                 = strripos( str_replace( '\\', '/', $_SERVER['SCRIPT_FILENAME'] ), $this->_trailingslashit( $tp_path_rel_to_home ) );
                $home_path           = substr( $_SERVER['SCRIPT_FILENAME'], 0, $pos );
                $home_path           = $this->_trailingslashit( $home_path );
            } else { $home_path = ABSPATH;}
            return str_replace( '\\', '/', $home_path );
        }//106
        /**
         * @description Returns a listing of all files in the specified folder,
         * @description . and all subdirectories up to 100 levels deep.
         * @param string $folder
         * @param int $levels
         * @param array $exclusions
         * @return string
         */
        protected function _list_files( $folder = '', $levels = 100, $exclusions = []):string{
            if ( empty( $folder ) ) { return false; }
            $folder = $this->_trailingslashit( $folder );
            if ( ! $levels ){ return false;}
            $files = [];
            $dir = @opendir( $folder );
            if ( $dir ) {
                while ( ( $file = readdir( $dir ) ) !== false ) {
                    if ( in_array( $file, array( '.', '..' ), true )){ continue;}
                    if ( '.' === $file[0] || in_array( $file, $exclusions, true )){ continue;}
                    if ( is_dir( $folder . $file ) ) {
                        $files2 = $this->_list_files( $folder . $file, $levels - 1 );
                        if ( $files2 ) { $files = $this->_tp_array_merge( $files, $files2 );}
                        else { $files[] = $folder . $file . '/';}
                    } else { $files[] = $folder . $file;}
                }
                closedir( $dir );
            }
            return $files;
        }//135
        /**
         * @description Gets the list of file extensions that are editable for a given theme..
         * @param $theme
         * @return string
         */
        protected function _tp_get_theme_file_editable_extensions( $theme ):string{
            $default_types = ['bash','conf','css','diff','htm','html','http','https','inc','include','js','json','jsx','less','md',
                'patch','php','php7','phps','phtml', 'sass','scss','sh','sql','svg','text','txt','xml','yaml','yml',];
            $file_types = $this->_apply_filters( 'tp_theme_editor_file_types', $default_types, $theme );
            return array_unique( array_merge( $file_types, $default_types ) );
        }//247
        /**
         * @description Prints file editor templates (for themes).
         * @return mixed
         */
        protected function _tp_print_file_editor_templates(){
            $print_file = static function() {
                $output  = "";
                ob_start();
                ?>
                <script id='print_file_editor_templates'>
                    document.body.innerHTML = `<div class='notice inline notice-'>TODO:Is for later.</div>`;
                </script>
                <?php
                $output .= ob_get_clean();
                $output .= "";
                $output .= "";
                $output .= "";
                $output .= "";
                return $output;
            };
            return $print_file();
        }//303
        /**
         * @description Attempts to edit a file for a theme.
         * @param array|null $args
         * @return mixed
         */
        protected function _tp_edit_theme_file( $args =null ){
            if ( empty( $args['file'] ) ) {
                return new TP_Error('missing_file' );
            }
            if ( 0 !== $this->_validate_file( $args['file'] ) ) {
                return (string) new TP_Error( 'bad_file' );
            }
            if ( ! isset( $args['new_content'] ) ) {
                return (string) new TP_Error( 'missing_content' );
            }
            if ( ! isset( $args['nonce'] ) ) {
                return (string) new TP_Error( 'missing_nonce' );
            }
            $file    = $args['file'];
            $content = $args['new_content'];
            $theme     = null;
            $real_file = null;
            if ( ! empty( $args['theme'] ) ) {
                $stylesheet = $args['theme'];

                if ( 0 !== $this->_validate_file( $stylesheet ) ) {
                    return (string) new TP_Error( 'bad_theme_path' );
                }
                if ( ! $this->_current_user_can( 'edit_themes' ) ) {
                    return (string)  new TP_Error( 'unauthorized', $this->__( 'Sorry, you are not allowed to edit templates for this site.' ) );
                }
                $theme = $this->_tp_get_theme( $stylesheet );
                if ($theme instanceof TP_Theme && ! $theme->exists() ) {
                    return (string)  new TP_Error( 'non_existent_theme', $this->__( 'The requested theme does not exist.' ) );
                }
                if ( ! $this->_tp_verify_nonce( $args['nonce'], 'edit-theme_' . $stylesheet . '_' . $file ) ) {
                    return (string)  new TP_Error( 'nonce_failure' );
                }
                if ( $theme->errors() && 'theme_no_stylesheet' === $theme->errors()->get_error_code() ) {
                    return (string)  new TP_Error(
                        'theme_no_stylesheet',
                        $this->__( 'The requested theme does not exist.' ) . ' ' . $theme->errors()->get_error_message()
                    );
                }
                $editable_extensions = $this->_tp_get_theme_file_editable_extensions( $theme );
                $allowed_files = [];
                foreach ((array)$editable_extensions as $type ) {
                    switch ( $type ) {
                        case 'php':
                            $allowed_files = array_merge( $allowed_files, $theme->get_files( 'php', -1 ) );
                            break;
                        case 'css':
                            $style_files                = $theme->get_files( 'css', -1 );
                            $allowed_files['style.css'] = $style_files['style.css'];
                            $allowed_files              = array_merge( $allowed_files, $style_files );
                            break;
                        default:
                            $allowed_files = array_merge( $allowed_files, $theme->get_files( $type, -1 ) );
                            break;
                    }
                }
                // Compare based on relative paths.
                if ( 0 !== $this->_validate_file( $file, array_keys( $allowed_files ) ) ) {
                    return new TP_Error( 'disallowed_theme_file', $this->__( 'Sorry, that file cannot be edited.' ) );
                }
                $real_file = $theme->get_stylesheet_directory() . '/' . $file;
                $is_active = ( $this->_get_stylesheet() === $stylesheet || $this->_get_template() === $stylesheet );
            } else {
                return (string)  new TP_Error( 'missing_theme' );
            }
            // Ensure file is real.
            if ( ! is_file( $real_file ) ) {
                return (string)  new TP_Error( 'file_does_not_exist', $this->__( 'File does not exist! Please double check the name and try again.' ) );
            }
            // Ensure file extension is allowed.
            $extension = null;
            if ( preg_match( '/\.([^.]+)$/', $real_file, $matches ) ) {
                $extension = strtolower( $matches[1] );
                if ( ! in_array( $extension, $editable_extensions, true ) ) {
                    return (string)  new TP_Error( 'illegal_file_type', $this->__( 'Files of this type are not editable.' ) );
                }
            }
            $previous_content = file_get_contents( $real_file );
            if ( ! is_writable( $real_file ) ) {
                return (string)  new TP_Error( 'file_not_writable' );
            }
            $f = fopen( $real_file, 'wb+' );
            if ( false === $f ) {
                return (string)  new TP_Error( 'file_not_writable' );
            }
            $written = fwrite( $f, $content );
            fclose( $f );
            if ( false === $written ) {
                return (string)  new TP_Error( 'unable_to_write', $this->__( 'Unable to write to file.' ) );
            }
            $this->_tp_opcache_invalidate( $real_file, true );
            if ( $is_active && 'php' === $extension ) {
                $scrape_key   = md5( mt_rand() );
                $transient    = 'scrape_key_' . $scrape_key;
                $scrape_nonce = (string) mt_rand();
                // It shouldn't take more than 60 seconds to make the two loopback requests.
                $this->_set_transient( $transient, $scrape_nonce, 60 );
                $cookies       = $this->_tp_unslash( $_COOKIE );
                $scrape_params = array(
                    'tp_scrape_key'   => $scrape_key,
                    'tp_scrape_nonce' => $scrape_nonce,
                );
                $headers       = array(
                    'Cache-Control' => 'no-cache',
                );
                /** This filter is documented in wp-includes/class-wp-http-streams.php */
                $ssl_verify = $this->_apply_filters( 'https_local_ssl_verify', false );
                // Include Basic auth in loopback requests.
                if ( isset( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ) ) {
                    $headers['Authorization'] = 'Basic ' . base64_encode( $this->_tp_unslash( $_SERVER['PHP_AUTH_USER'] ) . ':' . $this->_tp_unslash( $_SERVER['PHP_AUTH_PW'] ) );
                }
                // Make sure PHP process doesn't die before loopback requests complete.
                set_time_limit( 300 );
                // Time to wait for loopback requests to finish.
                $timeout = 100;
                $needle_start = "###### tp_scraping_result_start:$scrape_key ######";
                $needle_end   = "###### tp_scraping_result_end:$scrape_key ######";
                // Attempt loopback request to editor to see if user just whitescreened themselves.
                if ( isset( $stylesheet ) ) {
                    $url = $this->_add_query_arg(
                        array(
                            'theme' => $stylesheet,
                            'file'  => $file,
                        ),
                        $this->_admin_url( 'theme_editor.php' )
                    );
                } else {
                    $url = $this->_admin_url();
                }
                if ( function_exists( 'session_status' ) && PHP_SESSION_ACTIVE === session_status() ) {
                    // Close any active session to prevent HTTP requests from timing out
                    // when attempting to connect back to the site.
                    session_write_close();
                }
                $url                    = $this->_add_query_arg( $scrape_params, $url );
                $r                      = $this->_tp_remote_get( $url, compact( 'cookies', 'headers', 'timeout', 'ssl_verify' ) );
                $body                   = $this->_tp_remote_retrieve_body( $r );
                $scrape_result_position = strpos( $body, $needle_start );
                $loopback_request_failure = array(
                    'code'    => 'loopback_request_failed',
                    'message' => $this->__( 'Unable to communicate back with site to check for fatal errors, so the PHP change was reverted. You will need to upload your PHP file change by some other means, such as by using SFTP.' ),
                );
                $json_parse_failure       = array(
                    'code' => 'json_parse_error',
                );
                $result = null;
                if ( false === $scrape_result_position ) {
                    $result = $loopback_request_failure;
                } else {
                    $error_output = substr( $body, $scrape_result_position + strlen( $needle_start ) );
                    $error_output = substr( $error_output, 0, strpos( $error_output, $needle_end ) );
                    $result       = json_decode( trim( $error_output ), true );
                    if ( empty( $result ) ) {
                        $result = $json_parse_failure;
                    }
                }
                // Try making request to homepage as well to see if visitors have been whitescreened.
                if ( true === $result ) {
                    $url                    = $this->_home_url( '/' );
                    $url                    = $this->_add_query_arg( $scrape_params, $url );
                    $r                      = $this->_tp_remote_get( $url, compact( 'cookies', 'headers', 'timeout', 'ssl_verify' ) );
                    $body                   = $this->_tp_remote_retrieve_body( $r );
                    $scrape_result_position = strpos( $body, $needle_start );
                    if ( false === $scrape_result_position ) {
                        $result = $loopback_request_failure;
                    } else {
                        $error_output = substr( $body, $scrape_result_position + strlen( $needle_start ) );
                        $error_output = substr( $error_output, 0, strpos( $error_output, $needle_end ) );
                        $result       = json_decode( trim( $error_output ), true );
                        if ( empty( $result ) ) {
                            $result = $json_parse_failure;
                        }
                    }
                }
                $this->_delete_transient( $transient );
                if ( true !== $result ) {
                    // Roll-back file change.
                    file_put_contents( $real_file, $previous_content );
                    $this->_tp_opcache_invalidate( $real_file, true );
                    if ( ! isset( $result['message'] ) ) {
                        $message = $this->__( 'Something went wrong.' );
                    } else {
                        $message = $result['message'];
                        unset( $result['message'] );
                    }
                    return (string)  new TP_Error( 'php_error', $message, $result );
                }
            }
            if ( $theme instanceof TP_Theme ) {
                $theme->cache_delete();
            }
            return true;
        }//369 _tp_edit_theme_file
        /**
         * @description Returns a filename of a temporary unique file.
         * @param string $filename
         * @param string $dir
         * @return string
         */
        protected function _tp_temp_name( $filename = '', $dir = '' ):string{
            if ( empty( $dir ) ) { $dir = $this->_get_temp_dir(); }
            if ( empty( $filename ) || in_array( $filename, array( '.', '/', '\\' ), true ) ) {
                $bytes = random_bytes(16);
                $filename = bin2hex($bytes);
            }
            // Use the basename of the given file without the extension as the name for the temporary directory.
            $temp_filename = basename( $filename );
            $temp_filename = preg_replace( '|\.[^.]*$|', '', $temp_filename );
            if ( ! $temp_filename ) {
                return $this->_tp_temp_name( dirname( $filename ), $dir );
            }
            $temp_filename .= '_' . $this->_tp_generate_password( 6, false );
            //$temp_filename .= '.tmp';
            $temp_filename  = $dir . $this->_tp_unique_filename( $dir, $temp_filename );
            $fp = @fopen( $temp_filename, 'xb' );
            if ( ! $fp && is_writable( $dir ) && file_exists( $temp_filename ) ) {
                return $this->_tp_temp_name( $filename, $dir );
            }
            if ( $fp ) { fclose( $fp );}
            return $temp_filename;
        }//658
        /**
         * @description Makes sure that the file that was requested to be edited is allowed to be edited.
         * @param $file
         * @param array $allowed_files
         * @return string
         */
        protected function _validate_file_to_edit( $file, $allowed_files = [] ):string{
            $code = $this->_validate_file( $file, $allowed_files );
            if ( ! $code ) { return $file;}
            switch ( $code ) {
                case 1:
                    $this->_tp_die( $this->__( 'Sorry, that file cannot be edited.' ) );
                    return false;
                // case 2 :
                // wp_die( __('Sorry, can&#8217;t call files with their real path.' ));

                case 3:
                    $this->_tp_die( $this->__( 'Sorry, that file cannot be edited.' ) );
                    return false;
            }
        }//706
        protected function _tp_handle_upload_error( &$file, $message ):array{
            return array('file' => $file, 'error' => $message );
        }//from 771 inner
        /**
         * @description Handles PHP uploads in TailoredPress.
         * @param $file
         * @param $overrides
         * @param $time
         * @param $action
         * @return string|object
         */
        protected function _tp_handle_upload_action( &$file, $overrides, $time, $action ){
            $file = $this->_apply_filters( "{$action}_prefilter", $file );
            $overrides = $this->_apply_filters( "{$action}_overrides", $overrides, $file );
            $upload_handle = [$this,'_tp_handle_upload_error'];
            $upload_error_handler = $overrides['upload_error_handler'] ?? (string) $upload_handle;
            if ( isset( $file['error'] ) && ! is_numeric( $file['error'] ) && $file['error'] ) {
                return call_user_func_array( $upload_error_handler, array( &$file, $file['error'] ) );
            }
            $unique_filename_callback = $overrides['unique_filename_callback'] ?? null;
            $upload_error_strings = $overrides['upload_error_strings'] ?? [
                    false,
                    sprintf($this->__('The uploaded file exceeds the %1$s directive in %2$s.'), 'upload_max_filesize', 'php.ini'),
                    sprintf($this->__('The uploaded file exceeds the %s directive that was specified in the HTML form.'), 'MAX_FILE_SIZE'),
                    $this->__('The uploaded file was only partially uploaded.'),
                    $this->__('No file was uploaded.'),
                    '',
                    $this->__('Missing a temporary folder.'),
                    $this->__('Failed to write file to disk.'),
                    $this->__('File upload stopped by extension.'),
                ];
            $test_form = $overrides['test_form'] ?? true;
            $test_size = $overrides['test_size'] ?? true;
            $test_type = $overrides['test_type'] ?? true;
            $mimes     = $overrides['mimes'] ?? false;
            if ( $test_form && ( ! isset( $_POST['action'] ) || $_POST['action'] !== $action ) ) {
                return call_user_func_array( [$this,'_tp_handle_upload_error'], array( &$file, $this->__( 'Invalid form submission.' ) ) );
            }
            if ( isset( $file['error'] ) && $file['error'] > 0 ) {
                return call_user_func_array( [$this,'_tp_handle_upload_error'], array( &$file, $upload_error_strings[ $file['error'] ] ) );
            }
            $test_uploaded_file = 'tp_handle_upload' === $action ? is_uploaded_file( $file['tmp_name'] ) : @is_readable( $file['tmp_name'] );
            if ( ! $test_uploaded_file ) {
                return call_user_func_array([$this,'_tp_handle_upload_error'], array( &$file, $this->__( 'Specified file failed upload test.' ) ) );
            }
            $test_file_size = 'tp_handle_upload' === $action ? $file['size'] : filesize( $file['tmp_name'] );
            // A non-empty file will pass this test.
            if ( $test_size && ! ( $test_file_size > 0 ) ) {
                if ( $this->_is_multisite() ) {
                    $error_msg = $this->__( 'File is empty. Please upload something more substantial.' );
                } else {
                    $error_msg = sprintf(
                    /* translators: 1: php.ini, 2: post_max_size, 3: upload_max_filesize */
                        $this->__( 'File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your %1$s file or by %2$s being defined as smaller than %3$s in %1$s.' ),
                        'php.ini','post_max_size','upload_max_filesize');
                }
                return call_user_func_array( $upload_error_handler, array( &$file, $error_msg ) );
            }
            if ( $test_type ) {
                $tp_filetype     = $this->_tp_check_file_type_and_ext( $file['tmp_name'], $file['name'], $mimes );
                $ext             = empty( $tp_filetype['ext'] ) ? '' : $tp_filetype['ext'];
                $type            = empty( $tp_filetype['type'] ) ? '' : $tp_filetype['type'];
                $proper_filename = empty( $tp_filetype['proper_filename'] ) ? '' : $tp_filetype['proper_filename'];
                if ( $proper_filename ) {
                    $file['name'] = $proper_filename;
                }
                if ( ( ! $type || ! $ext ) && ! $this->_current_user_can( 'unfiltered_upload' ) ) {
                    return call_user_func_array( $upload_error_handler, array( &$file, $this->__( 'Sorry, you are not allowed to upload this file type.' ) ) );
                }
                if ( ! $type ) {
                    $type = $file['type'];
                }
            } else {
                $type = '';
            }
            $uploads = $this->_tp_upload_dir( $time );
            if ( ! ( $uploads && false === $uploads['error'] ) ) {
                return call_user_func_array($upload_error_handler, array( &$file, $uploads['error'] ) );
            }
            $filename = $this->_tp_unique_filename( $uploads['path'], $file['name'], $unique_filename_callback );
            $new_file = $uploads['path'] . "/$filename";
            $move_new_file = $this->_apply_filters( 'pre_move_uploaded_file', null, $file, $new_file, $type );
            if ( null === $move_new_file ) {
                if ( 'tp_handle_upload' === $action ) {
                    $move_new_file = @move_uploaded_file( $file['tmp_name'], $new_file );
                } else {
                    $move_new_file = @copy( $file['tmp_name'], $new_file );
                    unlink( $file['tmp_name'] );
                }
                if ( false === $move_new_file ) {
                    if ( 0 === strpos( $uploads['basedir'], ABSPATH ) ) {
                        $error_path = str_replace( ABSPATH, '', $uploads['basedir'] ) . $uploads['subdir'];
                    } else {
                        $error_path = basename( $uploads['basedir'] ) . $uploads['subdir'];
                    }
                    return $upload_error_handler(
                        $file,
                        sprintf(
                        /* translators: %s: Destination file path. */
                            $this->__( 'The uploaded file could not be moved to %s.' ),
                            $error_path
                        )
                    );
                }
            }
            $stat  = stat( dirname( $new_file ) );
            $perms = $stat['mode'] & 0000666;
            chmod( $new_file, $perms );
            $url = $uploads['url'] . "/$filename";
            if ( $this->_is_multisite() ) {
                $this->_clean_dir_size_cache( $new_file );
            }
            return $this->_apply_filters(
                'tp_handle_upload',
                array(
                    'file' => $new_file,
                    'url'  => $url,
                    'type' => $type,
                ),
                'tp_handle_sideload' === $action ? 'sideload' : 'upload'
            );
        }//771
    }
}else die;