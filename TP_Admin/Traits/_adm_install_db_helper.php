<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-9-2022
 * Time: 07:10
 */
namespace TP_Admin\Traits;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _adm_install_db_helper{
        use _init_db;
        public $tpdb;
        /**
         * @description Creates a table in the database if it doesn't already exist.
         * @param $table_name
         * @param $create_ddl
         * @return bool
         */
        protected function _maybe_create_table( $table_name, $create_ddl ):bool{
            $this->tpdb = $this->_init_db();
            foreach ( $this->tpdb->get_col( 'SHOW TABLES', 0 ) as $table ) {
                if($table === $table_name){return true;}
            }
            $this->tpdb->query( $create_ddl );
            foreach ( $this->tpdb->get_col( 'SHOW TABLES', 0 ) as $table ) {
                if($table === $table_name){return true;}
            }
            return false;
        }//52
        /**
         * @description Adds column to database table, if it doesn't already exist.
         * @param $table_name
         * @param $column_name
         * @param $create_ddl
         * @return bool
         */
        protected function _maybe_add_column( $table_name, $column_name, $create_ddl ):bool{
            $this->tpdb = $this->_init_db();
            foreach ( $this->tpdb->get_col( "DESC $table_name", 0 ) as $column ) {
                if ( $column === $column_name ){ return true;}
            }
            // Didn't find it, so try to create it.
            $this->tpdb->query( $create_ddl );
            foreach ( $this->tpdb->get_col( "DESC $table_name", 0 ) as $column ) {
                if ( $column === $column_name ) {return true;}
            }
            return false;
        }//88
        /**
         * @description Drops column from database table, if it exists.
         * @param $table_name
         * @param $column_name
         * @param $drop_ddl
         * @return bool
         */
        protected function _maybe_drop_column( $table_name, $column_name, $drop_ddl ):bool{
            $this->tpdb = $this->_init_db();
            foreach ( $this->tpdb->get_col( "DESC $table_name", 0 ) as $column ) {
                if ( $column === $column_name ) {
                    $this->tpdb->query( $drop_ddl );
                    foreach ( $this->tpdb->get_col( "DESC $table_name", 0 ) as $sub_column ) {
                        if ( $sub_column === $column_name ) {
                            return false;
                        }
                    }
                }
            }

            // Else didn't find it.
            return true;
        }//123
        /**
         * @description Checks that database table column matches the criteria.
         * @param $table_name
         * @param $col_name
         * @param $col_type
         * @param null $is_null
         * @param null $key
         * @param null $default
         * @param null $extra
         * @return bool
         */
        protected function _check_column( $table_name, $col_name, $col_type, $is_null = null, $key = null, $default = null, $extra = null ):bool{
            $this->tpdb = $this->_init_db();
            $diffs   = 0;
            $results = $this->tpdb->get_results( "DESC $table_name" );
            foreach ( $results as $sub_row ) {
                if ( $sub_row->Field === $col_name ) {
                    if(( null !== $col_type ) && ( $sub_row->Type !== $col_type )){++$diffs; }
                    if(( null !== $is_null ) && ( $sub_row->Null !== $is_null )){++$diffs;}
                    if(( null !== $key ) && ( $sub_row->Key !== $key )){++$diffs;}
                    if(( null !== $default ) && ( $sub_row->Default !== $default)){++$diffs;}
                    if(( null !== $extra ) && ( $sub_row->Extra !== $extra)){++$diffs;}
                    return $diffs <= 0;
                } // End if found our column.
            }
            return false;
        }//174
    }
}else{die;}
