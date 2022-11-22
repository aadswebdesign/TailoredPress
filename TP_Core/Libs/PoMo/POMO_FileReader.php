<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-2-2022
 * Time: 03:23
 */
namespace TP_Core\Libs\PoMo;
if(ABSPATH){
    class POMO_FileReader extends POMO_Reader {
        protected $_f_open;
        public function __construct( $filename ) {
            parent::__construct();
            $this->_f_open = fopen( $filename, 'rb' );
        }
        public function read( $bytes ) {
            return fread( $this->_f_open, $bytes );
        }
        public function seekto( $pos ): bool
        {
            if ( -1 === fseek( $this->_f_open, $pos, SEEK_SET ) ) {
                return false;
            }
            $this->_pos = $pos;
            return true;
        }
        public function is_resource():bool {
            return is_resource( $this->_f_open );
        }
        public function fe_of(): string {
            return feof( $this->_f_open );
        }
        public function close():bool {
            return fclose( $this->_f_open );
        }
        public function read_all(): string
        {
            $all = '';
            while ( ! $this->fe_of() ) {
                $all .= $this->read( 4096 );
            }
            return $all;
        }
    }
}else die;