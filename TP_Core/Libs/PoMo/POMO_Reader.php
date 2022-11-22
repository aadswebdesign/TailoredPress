<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-2-2022
 * Time: 03:21
 */

namespace TP_Core\Libs\PoMo;

if(ABSPATH){
    class POMO_Reader{
        public $endian = 'little';
        public $_post  = '';
        public function __construct() {
            if ( function_exists( 'mb_substr' ) && ( (int) ini_get( 'mbstring.func_overload' ) & 2 ) )
                $this->is_overloaded = true;
            else $this->is_overloaded = false;
            $this->_pos = 0;
        }
        public function setEndian( $endian ): void
        {
            $this->endian = $endian;
            return null;
        }
        public function readint32($filename = '') {
            $file_reader = new POMO_FileReader($filename);
            $bytes = $file_reader->read( 4 );
            if ( 4 !== $this->strlen( $bytes ) ) {
                return false;
            }
            $endian_letter = ( 'big' === $this->endian ) ? 'N' : 'V';
            $int           = unpack( $endian_letter, $bytes );
            return reset( $int );
        }
        public function readint32array( $count,$filename = '' ) {
            $file_reader = new POMO_FileReader($filename);
            $bytes = $file_reader->read( 4 * $count );
            if ( 4 * $count !== $this->strlen( $bytes ) ) {
                return false;
            }
            $endian_letter = ( 'big' === $this->endian ) ? 'N' : 'V';
            return unpack( $endian_letter . $count, $bytes );
        }
        public function substr( $string, $start, $length ) {
            if ( $this->is_overloaded ) {
                return mb_substr( $string, $start, $length, 'ascii' );
            }
            return substr( $string, $start, $length );
        }
        public function strlen( $string ) {
            if ( $this->is_overloaded ) {
                return mb_strlen( $string, 'ascii' );
            }
            return strlen( $string );
        }
        public function str_split( $string, $chunk_size ): array
        {
            if ( ! function_exists( 'str_split' ) ) {
                $length = $this->strlen( $string );
                $out    = array();
                for ( $i = 0; $i < $length; $i += $chunk_size ) {
                    $out[] = $this->substr( $string, $i, $chunk_size );
                }
                return $out;
            }
            return str_split( $string, $chunk_size );
        }
        public function pos():bool {
            return $this->_pos;
        }
        public function is_resource():bool {
            return true;
        }
        public function close():bool {
            return true;
        }
    }
}else die;
