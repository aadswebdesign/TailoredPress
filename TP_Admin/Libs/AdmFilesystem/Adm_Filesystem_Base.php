<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-10-2022
 * Time: 06:59
 */
namespace TP_Admin\Libs\AdmFilesystem;
use TP_Admin\Traits\AdminFile\_adm_file_01;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_04;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\Methods\_methods_06;
use TP_Core\Traits\Methods\_methods_16;
use TP_Core\Traits\Methods\_methods_17;
use TP_Core\Traits\Theme\_theme_02;
if(ABSPATH){
    class Adm_Filesystem_Base{
        use _adm_file_01;
        use _filter_01,  _formats_04;
        use _I10n_01,_I10n_02;
        use _theme_02;
        use _methods_06,_methods_16,_methods_17;

        protected $_params;
        protected $_verbose = false;
        protected $_cache = [];
        protected $_method;
        /** @var TP_Error */
        protected $_errors;
        protected $_options = [];
        public function abspath():string {
            $folder = $this->find_folder( ABSPATH );
            if ( ! $folder && $this->is_dir( '/' . TP_CORE ) ) {
                $folder = '/';}
            return $folder;
        }//56
        public function tp_content_dir():string {
            return $this->find_folder( TP_CONTENT_DIR );
        }//77
        public function tp_library_dir():string {
            return $this->find_folder( TP_CONTENT_LIBS );
        }//88
        public function tp_themes_dir( $theme = false ):string{
            $theme_root = $this->_get_theme_root( $theme );
            // Account for relative theme roots.
            if ( '/themes' === $theme_root || ! is_dir( $theme_root ) ) {
                $theme_root = TP_CONTENT_DIR . $theme_root;
            }
            return $this->find_folder( $theme_root );
        }//99
        public function tp_lang_dir():string {
            return $this->find_folder( TP_THEMES_LANG );
        }//117
        public function find_folder( $folder ):string{
            if(isset($this->_cache[$folder])){return $this->_cache[$folder];}
            if ( stripos( $this->_method, 'ftp' ) !== false ) {
                $constant_overrides = ['FTP_BASE' => ABSPATH,'FTP_CONTENT_DIR' => TP_CONTENT_DIR,
                    'FTP_LIBRARY_DIR' => TP_CONTENT_LIBS,'FTP_LANG_DIR' => TP_THEMES_LANG,];
                foreach ( $constant_overrides as $constant => $dir ) {
                    if (!defined($constant)){ continue;}
                    if ($folder === $dir ){ return $this->_trailingslashit( constant( $constant ) ); }
                }
                foreach ( $constant_overrides as $constant => $dir ) {
                    if ( ! defined( $constant ) ) { continue;}
                    if ( 0 === stripos( $folder, $dir ) ) { // $folder starts with $dir.
                        $potential_folder = preg_replace( '#^' . preg_quote( $dir, '#' ) . '/#i', $this->_trailingslashit( constant( $constant ) ), $folder );
                        $potential_folder = $this->_trailingslashit( $potential_folder );
                        if ( $this->is_dir( $potential_folder ) ) {
                            $this->_cache[ $folder ] = $potential_folder;
                            return $potential_folder;
                        }
                    }
                }
            } elseif ( 'direct' === $this->_method ) {
                $folder = str_replace( '\\', '/', $folder );
                return $this->_trailingslashit( $folder );
            }
            $folder = preg_replace( '|^([a-z]{1}):|i', '', $folder ); // Strip out Windows drive letter if it's there.
            $folder = str_replace( '\\', '/', $folder ); // Windows path sanitization.
            if(isset( $this->_cache[ $folder ])){ return $this->_cache[ $folder ];}
            if ( $this->exists( $folder ) ) { // Folder exists at that absolute path.
                $folder                 = $this->_trailingslashit( $folder );
                $this->_cache[ $folder ] = $folder;
                return $folder;
            }
            $return = $this->search_for_folder( $folder );
            if ( $return ) {$this->_cache[ $folder ] = $return;}
            return $return;
        }//175
        public function search_for_folder( $folder, $base = '.', $loop = false ):string{
            if ( empty( $base ) || '.' === $base ) { $base = $this->_trailingslashit( $this->cwd());}
            $folder = $this->_untrailingslashit( $folder );
            if ( $this->_verbose ) {
                printf( "<small>{$this->__( 'Looking for %1$s in %2$s' )}</small>\n", $folder, $base );
            }
            $folder_parts = explode( '/', $folder );
            $folder_part_keys = array_keys( $folder_parts );
            $last_index = array_pop( $folder_part_keys );
            $last_path = $folder_parts[ $last_index ];
            $files = $this->dirlist( $base );
            foreach ( $folder_parts as $index => $key ) {
                if ( $index === $last_index ){ continue; }
                if ( isset( $files[ $key ] ) ) {
                    $new_dir = $this->_trailingslashit( $this->_path_join( $base, $key ) );
                    if ( $this->_verbose ) {
                        printf( "<small>{$this->__( 'Changing to %s' )}</small>\n", $new_dir );
                    }
                    $new_folder = implode( '/', array_slice( $folder_parts, $index + 1 ) );
                    $ret = $this->search_for_folder( $new_folder, $new_dir, $loop );
                     if ( $ret ) {  return $ret; }
                }
            }
            if ( isset( $files[ $last_path ] ) ) {
                if ( $this->_verbose ) {
                    printf( "<small>{$this->__( 'Found %s' )}</small>\n", $base . $last_path );
                }
                return $this->_trailingslashit( $base . $last_path );
            }
            if ($loop || '/' === $base ){ return false; }
            return $this->search_for_folder( $folder, '/', true );
        }//257
        public function get_head_ch_mods( $file ):string {
            $perms = intval( $this->get_ch_mod( $file ), 8 );
            if ( ( $perms & 0xC000 ) === 0xC000 ) { $info = 's';}
            elseif ( ( $perms & 0xA000 ) === 0xA000 ) { $info = 'l';}
            elseif ( ( $perms & 0x8000 ) === 0x8000 ) { $info = '-';}
            elseif ( ( $perms & 0x6000 ) === 0x6000 ) { $info = 'b';}
            elseif ( ( $perms & 0x4000 ) === 0x4000 ) { $info = 'd';}
            elseif ( ( $perms & 0x2000 ) === 0x2000 ) { $info = 'c';}
            elseif ( ( $perms & 0x1000 ) === 0x1000 ) { $info = 'p';}
            else { $info = 'u';}
            // Owner.
            $info .= ( ( $perms & 0x0100 ) ? 'r' : '-' );
            $info .= ( ( $perms & 0x0080 ) ? 'w' : '-' );
            $nested_s_x_800 = ( $perms & 0x0800 ) ? 's' : 'x';
            $nested_s_800 = ( $perms & 0x0800 ) ? 'S' : '-';
            $info .= ( ( $perms & 0x0040 ) ? ( $nested_s_x_800 ) : ( $nested_s_800 ) );
            // Group.
            $info .= ( ( $perms & 0x0020 ) ? 'r' : '-' );
            $info .= ( ( $perms & 0x0010 ) ? 'w' : '-' );
            $nested_s_x_400 = ( $perms & 0x0400 ) ? 's' : 'x';
            $nested_s_400 = ( $perms & 0x0400 ) ? 'S' : '-';
            $info .= ( ( $perms & 0x0008 ) ? ( $nested_s_x_400 ) :( $nested_s_400 ) );
            // World.
            $info .= ( ( $perms & 0x0004 ) ? 'r' : '-' );
            $info .= ( ( $perms & 0x0002 ) ? 'w' : '-' );
            $nested_t_x_200 = ( $perms & 0x0200 ) ? 't' : 'x';
            $nested_t_200 = ( $perms & 0x0200 ) ? 'T' : '-';
            $info .= ( ( $perms & 0x0001 ) ? ( $nested_t_x_200 ) :( $nested_t_200 ) );
            return $info;
        }//343
        public function get_ch_mod( $file ):string {
            $this->_params = [$file];
            return '777';
        }//396
        public function get_num_chmod_from_head( $mode ):string{
            $real_mode = '';
            $legal = ['', 'w', 'r', 'x', '-'];
            $att_array = preg_split( '//', $mode );
            foreach ($att_array as $iValue) {
                $key = array_search( $iValue, $legal, true );
                if ( $key ) {$real_mode .= $legal[ $key ];}
            }
            $mode  = str_pad( $real_mode, 10, '-', STR_PAD_LEFT );
            $trans = ['-' => '0','r' => '4','w' => '2','x' => '1',];
            $mode  = strtr( $mode, $trans );
            $new_mode  = $mode[0];
            $new_mode .= $mode[1] + $mode[2] + $mode[3];
            $new_mode .= $mode[4] + $mode[5] + $mode[6];
            $new_mode .= $mode[7] + $mode[8] + $mode[9];
            return $new_mode;
        }//413
        protected function _is_binary( $text ):bool {
            $this->_params = [$text];
            return (bool) preg_match( '|[^\x20-\x7E]|', $text ); // chr(32)..chr(127)
        }//451
        /**
         * @param $file
         * @param $owner
         * @param bool $recursive
         * @return mixed
         */
        public function ch_own( $file, $owner, $recursive = false ){
            $this->_params = [$file, $owner, $recursive];
            return false;
        }//468
        public function connect():bool {
            return true;
        }//480
        /**
         * @param $file
         * @return mixed
         */
        public function get_contents( $file ){
            $this->_params = [$file];
            return false;
        }//493
        /**
         * @param $file
         * @return mixed
         */
        protected function get_contents_array( $file ){
            $this->_params = [$file];
            return false;
        }//506
        /**
         * @param $file
         * @param $contents
         * @param bool $mode
         * @return mixed
         */
        public function put_contents( $file, $contents, $mode = false ){
            $this->_params = [$file, $contents, $mode];
            return false;
        }//522
        /**
         * @return mixed
         */
        public function cwd(){
            return false;
        }//534
        /**
         * @param $dir
         * @return mixed
         */
        public function chdir( $dir ){
            $this->_params = [$dir];
            return false;
        }//547
        /**
         * @param $file
         * @param $group
         * @param bool $recursive
         * @return mixed
         */
        public function ch_grp( $file, $group, $recursive = false ) {
            $this->_params = [$file, $group, $recursive];
            return false;
        }//563
        /**
         * @param $file
         * @param bool $mode
         * @param bool $recursive
         * @return mixed
         */
        public function chmod( $file, $mode = false, $recursive = false ) {
            $this->_params = [$file, $mode, $recursive];
            return false;
        }//580
        /**
         * @param $file
         * @return mixed
         */
        public function owner( $file ){
            $this->_params = [$file];
            return false;
        }//593
        /**
         * @param $file
         * @return mixed
         */
        public function group( $file ){
            $this->_params = [$file];
            return false;
        }//606
        /**
         * @param $source
         * @param $destination
         * @param bool $overwrite
         * @param bool $mode
         * @return mixed
         */
        public function copy( $source, $destination, $overwrite = false, $mode = false ){
            $this->_params = [$source, $destination, $overwrite, $mode];
            return false;
        }//624
        /**
         * @param $source
         * @param $destination
         * @param bool $overwrite
         * @return mixed
         */
        public function move( $source, $destination, $overwrite = false ){
            $this->_params = [$source, $destination, $overwrite];
            return false;
        }//640
        /**
         * @param $file
         * @param bool $recursive
         * @param bool $type
         * @return mixed
         */
        public function delete( $file, $recursive = false, $type = false ){
            $this->_params = [$file, $recursive, $type];
            return false;
        }//657
        /**
         * @param $file
         * @return mixed
         */
        public function exists( $file ){
            $this->_params = [$file];
            return false;
        }//670
        /**
         * @param $file
         * @return mixed
         */
        public function is_file( $file ){
            $this->_params = [$file];
            return false;
        }//683
        /**
         * @param $path
         * @return mixed
         */
        public function is_dir( $path ){
            $this->_params = [$path];
            return false;
        }//696
        /**
         * @param $file
         * @return mixed
         */
        public function is_readable( $file ){
            $this->_params = [$file];
            return false;
        }//709
        /**
         * @param $file
         * @return mixed
         */
        public function is_writable( $file ){
            $this->_params = [$file];
            return false;
        }//722
        /**
         * @param $file
         * @return mixed
         */
        public function a_time( $file ){
            $this->_params = [$file];
            return false;
        }//735
        /**
         * @param $file
         * @return mixed
         */
        public function mtime( $file ){
            $this->_params = [$file];
            return false;
        }//748
        /**
         * @param $file
         * @return mixed
         */
        public function size( $file ){
            $this->_params = [$file];
            return false;
        }//761
        /**
         * @param $file
         * @param int $time
         * @param int $a_time
         * @return mixed
         */
        public function touch( $file, $time = 0, $a_time = 0 ){
            $this->_params = [$file, $time, $a_time];
            return false;
        }//780
        /**
         * @param $path
         * @param bool $chmod
         * @param bool $ch_own
         * @param bool $ch_group
         * @return mixed
         */
        public function mkdir( $path, $chmod = false, $ch_own = false, $ch_group = false ){
            $this->_params = [$path, $chmod, $ch_own, $ch_group];
            return false;
        }//799
        /**
         * @param $path
         * @param bool $recursive
         * @return mixed
         */
        public function rmdir( $path, $recursive = false ):bool {
            $this->_params = [$path, $recursive];
            return false;
        }//418
        /**
         * @param $path
         * @param bool $include_hidden
         * @param bool $recursive
         * @return mixed
         */
        public function dirlist( $path, $include_hidden = true, $recursive = false ){
            $this->_params = [$path, $include_hidden, $recursive];
            return false;
        }//844
        protected function _tp_ssh2_sftp_chmod($link, $path, $chmod){
            if(function_exists('ssh2_sftp_chmod')){
                return ssh2_sftp_chmod( $link, $path, $chmod );
            }
            return false;
        }//added

    }
}else{die;}