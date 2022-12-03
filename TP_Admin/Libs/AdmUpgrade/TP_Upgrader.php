<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-9-2022
 * Time: 05:28
 */
namespace TP_Admin\Libs\AdmUpgrade;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Options\_option_01;
if(ABSPATH){
    class TP_Upgrader{
        use _init_db;
        use _option_01;
        public $strings = [];
        public $skin;
        public $result = [];
        public $update_count = 0;
        public $update_current = 0;
        public function __construct( $skin = null ){}//122
        public function init():void{}//138
        public function generic_strings():void{}//148
        public function fs_connect(array $directories, $allow_relaxed_file_ownership = false ):bool{}//185
        public function unpack_package( $package, $delete_package = true ):string{}//305
        protected function _flatten_dir_list( $nested_files, $path = '' ):string{}
        public function clear_destination( $remote_destination ):bool{}//385
        public function install_package(array $args):string{}//453
        public function run( $options ):string{}//669
        public function maintenance_mode( $enable = false ):void{}//877
        public static function create_lock( $lock_name, $release_timeout = null ):bool {
            $tpdb = (new self)->_init_db();
            if ( ! $release_timeout ) {$release_timeout = HOUR_IN_SECONDS;}
            $lock_option = $lock_name . '.lock';
            $lock_result = $tpdb->query( $tpdb->prepare( TP_INSERT ." IGNORE INTO `$tpdb->options` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'no') /* LOCK */", $lock_option, time() ) );
            if ( ! $lock_result ) {
                $lock_result = (new self)->_get_option( $lock_option );
                if ( ! $lock_result ) { return false;}
                if ( $lock_result > ( time() - $release_timeout ) ) { return false;}
                self::release_lock( $lock_name );
                return self::create_lock( $lock_name, $release_timeout );
            }
            (new self)->_update_option( $lock_option, time() );
            return true;
        }//902
        public static function release_lock( $lock_name ):string {
            return (new self)->_delete_option( $lock_name . '.lock' );
        }//947
    }
}else{die;}