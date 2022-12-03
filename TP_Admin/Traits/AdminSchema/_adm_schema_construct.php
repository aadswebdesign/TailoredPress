<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-9-2022
 * Time: 10:04
 */
namespace TP_Admin\Traits\AdminSchema;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Inits\_init_rewrite;
if(ABSPATH){
    trait _adm_schema_construct{
        use _init_db,_init_queries,_init_rewrite;
        public $tpdb,$tp_charset_collate,$tp_queries,$tp_rewrite;
        private function __schema_construct():void{
            $this->tpdb = $this->_init_db();
            $this->tp_queries = $this->_init_query();
            $this->tp_charset_collate = $this->tpdb->get_charset_collate();
            $this->tp_rewrite = $this->_init_rewrite();
        }//added
    }
}else{die;}

