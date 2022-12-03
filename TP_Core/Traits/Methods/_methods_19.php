<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
if(ABSPATH){
    trait _methods_19{
        /**
         * @description Prints the default annotation for the web host altering the "Update PHP" page URL.
         * @param string $before
         * @param string $after
         */
        protected function _tp_update_php_annotation( $before = "<p class='description'>", $after = '</p>' ):void{
            $annotation = $this->_tp_get_update_php_annotation();
            if ( $annotation ) echo $before . $annotation . $after;
        }//7967
        /**
         * @description Returns the default annotation for the web hosting altering the "Update PHP" page URL.
         * @return string
         */
        protected function _tp_get_update_php_annotation():string{
            $update_url  = $this->_tp_get_update_php_url();
            $default_url = $this->_tp_get_default_update_php_url();
            if ( $update_url === $default_url ) return '';
            $annotation = "<p>";
            $annotation .= sprintf($this->__( 'This resource is provided by your web host, and is specific to your site. For more information, <a href="%s" target="_blank">see the official WordPress documentation</a>.' ),
            $this->_esc_url( $default_url ));/* translators: %s: Default Update PHP page URL. */
            $annotation .= "</p>";
                return $annotation;
        }//7985
        /**
         * @description Gets the URL for directly updating the PHP version the site is running on.
         * @return string
         */
        protected function _tp_get_direct_php_update_url():string{
            $direct_update_url = '';
            if ( false !== getenv( 'TP_DIRECT_UPDATE_PHP_URL' ) )
                $direct_update_url = getenv( 'TP_DIRECT_UPDATE_PHP_URL' );
            $direct_update_url = $this->_apply_filters( 'tp_direct_php_update_url', $direct_update_url );
            return $direct_update_url;
        }//8013
        /**
         * @description Display a button directly linking to a PHP update process.
         * @return string|void
         */
        protected function _tp_get_direct_php_update_button(){
            $direct_update_url = $this->_tp_get_direct_php_update_url();
            if ( empty( $direct_update_url ) ) return;
            $print_btn = "<p class='button-container'>";
            $print_btn .= sprintf("<a class='button button-primary' href='%1\$s' target='_blank' rel='noopener'>%2\$s <span class='screen-reader-text'>%3\$s</span><span aria-hidden='true' class='todo'></span></a>",
                    $this->_esc_url( $direct_update_url ), $this->__( 'Update PHP' ), /* translators: Accessibility text. */$this->__( '(opens in a new tab)' ));
            $print_btn .= "</p>";
            return $print_btn;
        }//8041
        protected function _tp_direct_php_update_button():void{
            echo $this->_tp_get_direct_php_update_button();
        }
        /**
         * todo 1-1
         * @description Gets the URL to learn more about updating the site to use HTTPS.
         * @return string|void
         */
        protected function _tp_get_update_https_url(){
            $default_url = $this->_tp_get_default_update_https_url();
            $update_url = $default_url;
            if ( false !== getenv( 'TP_UPDATE_HTTPS_URL' ) )
                $update_url = getenv( 'TP_UPDATE_HTTPS_URL' );
            $update_url = $this->_apply_filters( 'tp_update_https_url', $update_url );
            if ( empty( $update_url ) ) $update_url = $default_url;
            return $update_url;
        }//8071
        /**
         * @description Gets the default URL to learn more about updating the site to use HTTPS.
         * @return string
         */
        protected function _tp_get_default_update_https_url():string{
            return $this->__( 'https://wordpress.org/support/article/why-should-i-use-https/' );
        }//8109
        /**
         * @description Gets the URL for directly updating the site to use HTTPS.
         * @return string
         */
        protected function _tp_get_direct_update_https_url():string{
            $direct_update_url = '';
            if ( false !== getenv( 'TP_DIRECT_UPDATE_HTTPS_URL' ) )
                $direct_update_url = getenv( 'TP_DIRECT_UPDATE_HTTPS_URL' );
            $direct_update_url = $this->_apply_filters( 'tp_direct_update_https_url', $direct_update_url );
            return $direct_update_url;
        }//8118
        /**
         * @description Get the size of a directory.
         * @param $directory
         * @param null $max_execution_time
         * @return bool|int|null
         */
        protected function _get_dir_size( $directory, $max_execution_time = null ){
            if ( $this->_is_multisite() && $this->_is_main_site() )
                $size = $this->_recurse_dir_size( $directory, $directory . '/sites', $max_execution_time );
            else $size = $this->_recurse_dir_size( $directory, null, $max_execution_time );
            return $size;
        }//8158
        /**
         * @description Get the size of a directory recursively.
         * @param $directory
         * @param null $exclude
         * @param null $max_execution_time
         * @param null $directory_cache
         * @return bool|int|null
         */
        protected function _recurse_dir_size( $directory, $exclude = null, $max_execution_time = null, &$directory_cache = null ){
            $directory  = $this->_untrailingslashit( $directory );
            $save_cache = false;
            if ( ! isset( $directory_cache ) ) {
                $directory_cache = $this->_get_transient( 'dirsize_cache' );
                $save_cache      = true;
            }
            if ( isset( $directory_cache[ $directory ] ) && is_int( $directory_cache[ $directory ] ) )
                return $directory_cache[ $directory ];
            if ( ! file_exists( $directory ) || ! is_dir( $directory ) || ! is_readable( $directory ) )
                return false;
            if (( is_string( $exclude ) && $directory === $exclude ) ||( is_array( $exclude ) && in_array( $directory, $exclude,true)))
                return false;
            if ( null === $max_execution_time ) {
                // Keep the previous behavior but attempt to prevent fatal errors from timeout if possible.
                if (function_exists('ini_get')) $max_execution_time = ini_get('max_execution_time');
                else $max_execution_time = 0;
                // Leave 1 second "buffer" for other operations if $max_execution_time has reasonable value.
                if ($max_execution_time > 10) --$max_execution_time;
            }
            $size = $this->_apply_filters( 'pre_recurse_dirsize', false, $directory, $exclude, $max_execution_time, $directory_cache );
            if ( false === $size ) {
                $size = 0;
                $handle = opendir( $directory );
                if ( $handle ) {
                    while ( ( $file = readdir( $handle ) ) !== false ) {
                        $path = $directory . '/' . $file;
                        if ( '.' !== $file && '..' !== $file ) {
                            if ( is_file( $path ) ) $size += filesize( $path );
                            elseif ( is_dir( $path ) ) {
                                $handle_size = $this->_recurse_dir_size( $path, $exclude, $max_execution_time, $directory_cache );
                                if ( $handle_size > 0 ) $size += $handle_size;
                            }
                            if ( $max_execution_time > 0 && ( microtime( true ) - TP_START_TIMESTAMP ) > $max_execution_time) {
                                // Time exceeded. Give up instead of risking a fatal timeout.
                                $size = null;
                                break;
                            }
                        }
                    }
                    closedir( $handle );
                }
            }
            if ( ! is_array( $directory_cache ) ) $directory_cache = array();
            $directory_cache[ $directory ] = $size;
            if ( $save_cache ) $this->_set_transient( 'dirsize_cache', $directory_cache );
            return $size;
        }//8191
        /**
         * @description Cleans directory size cache used by recurse_dirsize().
         * @param $path
         */
        protected function _clean_dir_size_cache( $path ):void{
            if ( ! is_string( $path ) || empty( $path ) ) {
                trigger_error(
                    sprintf(
                    /* translators: 1: Function name, 2: A variable type, like "boolean" or "integer". */
                        $this->__( '%1$s only accepts a non-empty path string, received %2$s.' ),
                        '<code>clean_dirsize_cache()</code>', '<code>' . gettype( $path ) . '</code>'));
                return;
            }
            $directory_cache = $this->_get_transient( 'dirsize_cache' );
            if ( empty( $directory_cache ) ) return;
            if ( strpos( $path, '/' ) === false && strpos( $path, '\\' ) === false ) {
                unset( $directory_cache[ $path ] );
                $this->_set_transient( 'dirsize_cache', $directory_cache );
                return;
            }
            $last_path = null;
            $path      = $this->_untrailingslashit( $path );
            unset( $directory_cache[ $path ] );
            while ( $last_path !== $path && DIRECTORY_SEPARATOR !== $path && '.' !== $path && '..' !== $path) {
                $last_path = $path;
                $path      = dirname( $path );
                unset( $directory_cache[ $path ] );
            }
            $this->_set_transient( 'dirsize_cache', $directory_cache );
        }//8301
    }
}else die;