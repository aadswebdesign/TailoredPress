<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
if(ABSPATH){
    trait _methods_06{
        /**
         * @description Retrieves unvalidated referer from '_tp_http_referer' or HTTP referer.
         * @return bool
         */
        protected function _tp_get_raw_referer():bool{
            if ( ! empty( $_REQUEST['_tp_http_referer'] ) ) return $this->_tp_unslash( $_REQUEST['_tp_http_referer'] );
            elseif ( ! empty( $_SERVER['HTTP_REFERER'] ) ) return $this->_tp_unslash( $_SERVER['HTTP_REFERER'] );
            return false;
        }//1956
        /**
         * @description Retrieve original referer that was posted, if it exists.
         * @return bool
         */
        protected function _tp_get_original_referer():bool{
            if ( ! empty( $_REQUEST['_tp_original_http_referer'] ) && function_exists( 'tp_validate_redirect' ) )
                return $this->_tp_validate_redirect( $this->_tp_unslash( $_REQUEST['_tp_original_http_referer'] ), false );
            return false;
        }//1973
        /**
         * @description Recursive directory creation based on full path.
         * @param $target
         * @return bool
         */
        protected function _tp_mkdir_p( $target ):bool{
            $wrapper = null;
            // Strip the protocol.
            if ( $this->_tp_is_stream( $target ) )
                @list( $wrapper, $target ) = explode( '://', $target, 2 );
            $target = str_replace( '//', '/', $target );
            if ( null !== $wrapper ) $target = $wrapper . '://' . $target;
            $target = rtrim( $target, '/' );
            if ( empty( $target ) ) $target = '/';
            if ( file_exists( $target ) ) return @is_dir( $target );
            if ( false !== strpos( $target, '../' ) || false !== strpos( $target, '..' . DIRECTORY_SEPARATOR ) )
                return false;
            $target_parent = dirname( $target );
            while ( '.' !== $target_parent && ! is_dir( $target_parent ) && dirname( $target_parent ) !== $target_parent )
                $target_parent = dirname( $target_parent );
            $stat = @stat( $target_parent );
            if ( $stat ) $dir_perms = $stat['mode'] & 0007777;
            else $dir_perms = 0777;
            if (mkdir($target, $dir_perms, true) || is_dir($target)) {
                if ( ( $dir_perms & ~umask() ) !== $dir_perms ) {
                    $folder_parts = explode( '/', substr( $target, strlen( $target_parent ) + 1 ) );
                    for ( $i = 1, $c = count( $folder_parts ); $i <= $c; $i++ )
                        chmod( $target_parent . '/' . implode( '/', array_slice( $folder_parts, 0, $i ) ), $dir_perms );
                }
                return true;
            }
            return false;
        }//1991
        /**
         * @description Test if a given filesystem path is absolute.
         * @param $path
         * @return bool
         */
        protected function _path_is_absolute( $path ):bool{
            if ( $this->_tp_is_stream( $path ) && ( is_dir( $path ) || is_file( $path ) ) )
                return true;
            if ( realpath( $path ) === $path ) return true;
            if ( $path === '' || '.' === $path[0] )
                return false;
            if ( preg_match( '#^[a-zA-Z]:\\\\#', $path ) )
                return true;
            return ( '/' === $path[0] || '\\' === $path[0] );
        }
        /**
         * @description Join two filesystem paths together.
         * @param $base
         * @param $path
         * @return string
         */
        protected function _path_join( $base, $path ):string{
            if ( $this->_path_is_absolute( $path ) ) return $path;
            return rtrim( $base, '/' ) . '/' . ltrim( $path, '/' );
        }//2110
        /**
         * @description Normalize a filesystem path.
         * @param $path
         * @return string
         */
        protected function _tp_normalize_path( $path ):string{
            $wrapper = '';
            if ( $this->_tp_is_stream( $path ) ) {
                @list( $wrapper, $path ) = explode( '://', $path, 2 );
                $wrapper .= '://';
            }
            $path = str_replace( '\\', '/', $path );
            $path = preg_replace( '|(?<=.)/+|', '/', $path );
            if ( ':' === substr( $path, 1, 1 ) ) $path = ucfirst( $path );
            return $wrapper . $path;
        }//2134
        /**
         * @description Determine a writable directory for temporary files.
         * @return string
         */
        protected function _get_temp_dir():string{
            static $temp = '';
            if ( defined( 'TP_TEMP_DIR' ) ) return $this->_trailingslashit( TP_TEMP_DIR );
            if ( $temp ) return $this->_trailingslashit( $temp );
            if ( function_exists( 'sys_get_temp_dir' ) ) {
                $temp = sys_get_temp_dir();
                if ( @is_dir( $temp ) && $this->_tp_is_writable( $temp ) )
                    return $this->_trailingslashit( $temp );
            }
            $temp = ini_get( 'upload_tmp_dir' );
            if ( @is_dir( $temp ) && $this->_tp_is_writable( $temp ) )
                return $this->_trailingslashit( $temp );
            $temp = TP_CONTENT_DIR . '/';
            if ( is_dir( $temp ) && $this->_tp_is_writable( $temp ) )
                return $temp;
            return '/tmp/';
        }//2171
        /**
         * @description Determine if a directory is writable.
         * @param $path
         * @return bool
         */
        protected function _tp_is_writable( $path ):bool{
            if (stripos(PHP_OS, 'WIN') === 0)
                return $this->_win_is_writable( $path );
            else return @is_writable( $path );

        }//2214
        /**
         * @description Workaround for Windows bug in is_writable() function
         * @param $path
         * @return bool
         */
        protected function _win_is_writable( $path ):bool{
            if ( '/' === $path[ strlen( $path ) - 1 ] )
                return $this->_win_is_writable( $path . uniqid(mt_rand(), true) . '.tmp' );
            elseif ( is_dir( $path ) )
                return $this->_win_is_writable( $path . '/' . uniqid(mt_rand(), true) . '.tmp' );
            $should_delete_tmp_file = ! file_exists( $path );
            $f = @fopen( $path, 'ab');
            if ( false === $f ) return false;
            fclose( $f );
            if ( $should_delete_tmp_file ) unlink( $path );
            return true;
        }//2238
        /**
         * @description Retrieves uploads directory information.
         * @return mixed
         */
        protected function _tp_get_upload_dir(){
            return $this->_tp_upload_dir( null, false );
        }//2278
    }
}else die;