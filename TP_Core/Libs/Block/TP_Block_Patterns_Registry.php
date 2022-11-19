<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-4-2022
 * Time: 19:25
 */
declare(strict_types=1);
namespace TP_Core\Libs\Block;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\I10n\_I10n_01;
if(ABSPATH){
    final class TP_Block_Patterns_Registry implements _block_interface{
        use _methods_12;
        use _I10n_01;
        private $__registered_patterns = [];
        private static $__instance;
        public function register( $pattern_name, $pattern_properties ):bool {
            if ( ! isset( $pattern_name ) || ! is_string( $pattern_name ) ) {
                $this->_doing_it_wrong(__METHOD__,$this->__( 'Pattern name must be a string.' ),'0.0.1');
                return false;
            }
            if ( ! isset( $pattern_properties['title'] ) || ! is_string( $pattern_properties['title'] ) ) {
                $this->_doing_it_wrong(__METHOD__,$this->__( 'Pattern title must be a string.' ),'0.0.1');
                return false;
            }
            if ( ! isset( $pattern_properties['content'] ) || ! is_string( $pattern_properties['content'] ) ) {
                $this->_doing_it_wrong( __METHOD__,$this->__( 'Pattern content must be a string.' ),'0.0.1');
                return false;
            }
            $this->__registered_patterns[ $pattern_name ] = array_merge($pattern_properties,['name' => $pattern_name]);
            return true;
        }
        public function unregister( $pattern_name, $property = null ):bool {
            if ( ! $this->is_registered( $pattern_name ) ) {
                $this->_doing_it_wrong( __METHOD__, sprintf( $this->__( 'Pattern "%s" not found.' ), $pattern_name ),'5.5.0');
                return false;
            }
            unset( $this->__registered_patterns[ $pattern_name ] );
            return true;
        }
        public function registered( $pattern_name, $property = null ) {
            if ( ! $this->is_registered( $pattern_name ) ) return null;
            return $this->__registered_patterns[ $pattern_name ];
        }
        public function get_all_registered():array {
            return array_values( $this->__registered_patterns );
        }
        public function is_registered( $pattern_name, $property = null ):bool {
            return isset( $this->__registered_patterns[ $pattern_name ] );
        }
        public static function get_instance(): TP_Block_Patterns_Registry{
            if ( null === self::$__instance )  self::$__instance = new self();
            return self::$__instance;
        }
    }
}else die;

