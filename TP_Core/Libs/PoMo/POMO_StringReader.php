<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-2-2022
 * Time: 03:24
 */

namespace TP_Core\Libs\PoMo;
if(ABSPATH){
    class POMO_StringReader extends POMO_Reader {

        protected $_str = '';
        public function __construct( $str = '' ) {
            parent::__construct();
            $this->_str = $str;
            $this->_pos = 0;
        }
        public function read( $bytes ) {
            $data        = $this->substr( $this->_str, $this->_pos, $bytes );
            $this->_pos += $bytes;
            if ( $this->strlen( $this->_str ) < $this->_pos ) {
                $this->_pos = $this->strlen( $this->_str );
            }
            return $data;
        }
        public function seekto( $pos ) {
            $this->_pos = $pos;
            if ( $this->strlen( $this->_str ) < $this->_pos ) {
                $this->_pos = $this->strlen( $this->_str );
            }
            return $this->_pos;
        }
        public function length() {
            return $this->strlen( $this->_str );
        }
        public function read_all() {
            return $this->substr( $this->_str, $this->_pos, $this->strlen( $this->_str ) );
        }

    }
}else die;


