<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-8-2022
 * Time: 14:39
 */
namespace TP_Libs;
if(ABSPATH){
    class TP_Config{
        public $table_prefix;
        public function __construct(){
            echo "<p>TP_Config.php</p>";
           $this->__tp_configs();
        }
        private function __tp_configs():void{
            define( 'TP_DB_NAME', 'database_name_here' );
            define( 'TP_DB_USER', 'username_here' );
            define( 'TP_DB_PASSWORD', 'password_here' );
            define( 'TP_DB_HOST', 'localhost' );
            define( 'TP_DB_CHARSET', 'utf8' );
            define( 'TP_DB_COLLATE', '' );

            define( 'AUTH_KEY',         'put your unique phrase here' );
            define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
            define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
            define( 'NONCE_KEY',        'put your unique phrase here' );
            define( 'AUTH_SALT',        'put your unique phrase here' );
            define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
            define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
            define( 'NONCE_SALT',       'put your unique phrase here' );
            $this->table_prefix = 'tp_';
            define( 'TP_DEBUG', false );
            if ( ! defined( 'ABSPATH' ) ) {
                define( 'ABSPATH', __DIR__ . '/' );
            }
            //echo ABSPATH .' (from TP_Config!)<br/>';
            /**
             * @description As this is a methods package and auto loaded,
             * @description . there is just a single include used and nothing more to do from here.
             */

        }



    }
}else{die;}