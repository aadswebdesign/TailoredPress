<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-4-2022
 * Time: 17:07
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\DB\TP_Db;
if(ABSPATH){
    trait _init_db{
        protected $_tpdb;
        protected function _init_db(): TP_Db{
            if(!($this->_tpdb instanceof TP_Db)){
                $db_user     = defined( 'DB_USER' ) ? DB_USER : 'awd_sites';
                $db_password = defined( 'DB_PASSWORD' ) ? DB_PASSWORD : '2r7DuG3M6f6bOhIY';
                $db_name     = defined( 'DB_NAME' ) ? DB_NAME : 'tp_test_base';
                $db_host     = defined( 'DB_HOST' ) ? DB_HOST : 'localhost';
                $this->_tpdb = new TP_Db( $db_user, $db_password, $db_name, $db_host );
            }
            //if (! isset( $this->_tpdb ) ) return false;
            return $this->_tpdb;
        }
    }
}else die;