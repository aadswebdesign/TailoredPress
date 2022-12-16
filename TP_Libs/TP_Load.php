<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-12-2022
 * Time: 13:14
 */
namespace TP_Libs;
if(ABSPATH){
    class TP_Load{//might not be needed?
        protected $_args;
        public function __construct($args = null){
            $this->_args = $args;
        }

    }
}else{die;}
