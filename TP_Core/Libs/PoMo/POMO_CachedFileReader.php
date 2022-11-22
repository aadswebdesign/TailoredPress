<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-2-2022
 * Time: 03:25
 */
namespace TP_Core\Libs\PoMo;
if(ABSPATH){
    class POMO_CachedFileReader extends POMO_StringReader {
        public function __construct( $filename ) {
            parent::__construct();
            $this->_str = file_get_contents( $filename );
            if ( false === $this->_str ) {
                return false;
            }
            $this->_pos = 0;
            return $this->_str;
        }
    }
}else die;