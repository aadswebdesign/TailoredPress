<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 21:18
 */
declare(strict_types=1);
namespace TP_Core\Libs\Block;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\I10n\_I10n_01;
if(ABSPATH){
    final class TP_Block_Pattern_Categories_Registry implements _block_interface {
        use _methods_12;
        use _I10n_01;
        private $__registered_categories = [];
        private static $__instance;
        public function register( $category_name, $category_properties ):bool {
            if ( ! isset( $category_name ) || ! is_string( $category_name ) ) {
                $this->_doing_it_wrong(__METHOD__,$this->__( 'Block pattern category name must be a string.' ),'0.0.1');
                return false;
            }
            $this->__registered_categories[ $category_name ] = array_merge(['name' => $category_name],$category_properties);
            return true;
        }
        public function unregister( $category_name, $property = null ):bool {
            if ( ! $this->is_registered( $category_name ) ) {
                $this->_doing_it_wrong( __METHOD__,sprintf( $this->__( 'Block pattern category "%s" not found.' ), $category_name ),'0.0.1');
                return false;
            }
            unset( $this->__registered_categories[ $category_name ] );
            return true;
        }
        public function registered( $category_name, $property = null ) {
            if ( ! $this->is_registered( $category_name ) ) return null;
            return $this->__registered_categories[ $category_name ];
        }
        public function get_all_registered():array {
            return array_values( $this->__registered_categories );
        }
        public function is_registered( $category_name, $property = null ):bool {
            return isset( $this->__registered_categories[ $category_name ] );
        }
        public static function get_instance(): ?TP_Block_Pattern_Categories_Registry{
            if ( null === self::$__instance ) self::$__instance = new self();
            return self::$__instance;
        }
    }
}else die;