<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
use TP_Core\Traits\Inits\_init_post;
if(ABSPATH){
    trait _methods_17{
        use _init_post;
        /**
         * @description Reset the mbstring internal encoding to a users previously set encoding.
         */
        protected function _reset_mb_string_encoding():void{
            $this->_mb_string_binary_safe_encoding( true );
        }//7244
        /**
         * @description Filter/validate a variable as a boolean.
         * @param $var
         * @return bool
         */
        protected function _tp_validate_boolean( $var ):bool{
            if ( is_bool( $var ) )  return $var;
            if ( is_string( $var ) && 'false' === strtolower( $var ) )
                return false;
            return (bool) $var;
        }//7258
        /**
         * @param $file
         */
        protected function _tp_delete_file( $file ):void{
            $delete = $this->_apply_filters( 'wp_delete_file', $file );
            if ( ! empty( $delete ) )  @unlink( $delete );
        }//7270
        /**
         * @description Deletes a file if its path is within the given directory.
         * @param $file
         * @param $directory
         * @return bool
         */
        protected function _tp_delete_file_from_directory( $file, $directory ):bool{
            if ( $this->_tp_is_stream( $file ) ) {
                $real_file      = $file;
                $real_directory = $directory;
            } else {
                $real_file      = realpath( $this->_tp_normalize_path( $file ) );
                $real_directory = realpath( $this->_tp_normalize_path( $directory ) );
            }
            if ( false !== $real_file )
                $real_file = $this->_tp_normalize_path( $real_file );
            if ( false !== $real_directory )
                $real_directory = $this->_tp_normalize_path( $real_directory );
            if ( false === $real_file || false === $real_directory || strpos( $real_file, $this->_trailingslashit( $real_directory ) ) !== 0 )
                return false;
            $this->_tp_delete_file( $file );
            return true;
        }//7293
        /**
         * @description Outputs a small JS snippet on preview tabs/windows to remove `window.name` on unload.
         */
        protected function _tp_post_preview_js():void{
            $post = $this->_init_post();
            if ($post === null || ! $this->_is_preview()) return;
            $name = 'tp_preview_' . (int) $post->ID;
            ob_start();
            ?>
            <script>
                //todo for now,will setup a module/function/rest for this
                const  query = document.location.search;
                if ( query && query.indexOf( 'preview=true' ) !== -1 )
                    window.name = '<?php echo $name; ?>';
                if ( window.addEventListener )
                    window.addEventListener( 'unload', function() { window.name = ''; }, false );
            </script>
            <?php
            $script_setup = ob_get_clean();
            $this->_esc_html($script_setup);
        }//7335
        /**
         * @description Parses and formats a MySQL datetime (Y-m-d H:i:s) for ISO8601 (Y-m-d\TH:i:s).
         * @param $date_string
         * @return mixed
         */
        protected function _mysql_to_rfc3339( $date_string ){
            return $this->_mysql2date( 'Y-m-d\TH:i:s', $date_string, false );
        }//7369
        /**
         * @description Attempts to raise the PHP memory limit for memory intensive processes.
         * @param string $context
         * @return bool|string
         */
        protected function _tp_raise_memory_limit( $context = 'admin' ){
            if ( false === $this->_tp_is_ini_value_changeable( 'memory_limit' ) )
                return false;
            $current_limit     = ini_get( 'memory_limit' );
            $current_limit_int = $this->_tp_convert_hr_to_bytes( $current_limit );
            if ( -1 === $current_limit_int ) return false;
            $tp_max_limit     = TP_MAX_MEMORY_LIMIT;
            $tp_max_limit_int = $this->_tp_convert_hr_to_bytes( $tp_max_limit );
            $filtered_limit   = $tp_max_limit;
            switch ( $context ) {
                case 'admin':
                    $filtered_limit = $this->_apply_filters('admin_memory_limit', $filtered_limit);
                    break;
                case 'image':
                    $filtered_limit = $this->_apply_filters('image_memory_limit', $filtered_limit);
                    break;
                default:
                    $filtered_limit = $this->_apply_filters("{$context}_memory_limit", $filtered_limit);
                    break;
            }
            $filtered_limit_int = $this->_tp_convert_hr_to_bytes( $filtered_limit );
            if ( -1 === $filtered_limit_int || ( $filtered_limit_int > $tp_max_limit_int && $filtered_limit_int > $current_limit_int ) ) {
                if ( false !== ini_set( 'memory_limit', $filtered_limit ) ) return $filtered_limit;
                 else return false;
            } elseif ( -1 === $tp_max_limit_int || $tp_max_limit_int > $current_limit_int ) {
                if ( false !== ini_set( 'memory_limit', $tp_max_limit ) ) return $tp_max_limit;
                else return false;
            }
            return false;
        }//7386
        /**
         * @description Generate a random UUID (version 4).
         * @return string
         */
        protected function _tp_generate_uuid4():string{
            return sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                random_int( 0, 0xffff ), random_int( 0, 0xffff ),random_int( 0, 0xffff ),random_int( 0, 0x0fff ) | 0x4000,
                random_int( 0, 0x3fff ) | 0x8000,random_int( 0, 0xffff ), random_int( 0, 0xffff ), random_int( 0, 0xffff ));
        }//7487
        /**
         * @description Validates that a UUID is valid.
         * @param $uuid
         * @param null $version
         * @return bool
         */
        protected function _tp_is_uuid( $uuid, $version = null ):bool{
            if ( ! is_string( $uuid ) ) return false;
            if ( is_numeric( $version ) ) {
                if ( 4 !== (int) $version ) {
                    $this->_doing_it_wrong( __FUNCTION__, $this->__( 'Only UUID V4 is supported at this time.' ), '0.0.1' );
                    return false;
                }
                $regex = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
            } else $regex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';
            return (bool) preg_match( $regex, $uuid );
        }//7511
        /**
         * @description Gets unique ID.
         * @param string $prefix
         * @return string
         */
        protected function _tp_unique_id( $prefix = '' ):string{
            static $id_counter = 0;
            return $prefix . ++$id_counter;
        }//7550
    }
}else die;