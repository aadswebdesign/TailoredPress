<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 18:15
 */
declare(strict_types=1);
namespace TP_Core\Libs\Block;
use TP_Core\Traits\Compat\_compat_01;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Methods\_methods_21;
if(ABSPATH){
    final class TP_Block_Styles_Registry implements _block_interface{
        use _compat_01, _methods_12,_methods_21, _I10n_01;
        private $__registered_block_styles = [];
        private static $__instance;
        public function register( $block_name, $style_properties ):bool {
            if ( ! isset( $block_name ) || ! is_string( $block_name ) ) {
                $this->_doing_it_wrong( __METHOD__,$this->__( 'Block name must be a string.' ),'0.0.1');
                return false;
            }
            if ( ! isset( $style_properties['name'] ) || ! is_string( $style_properties['name'] ) ) {
                $this->_doing_it_wrong( __METHOD__,$this->__( 'Block style name must be a string.' ),'0.0.1');
                return false;
            }
            if ( $this->_tp_str_contains( $style_properties['name'], ' ' ) ) {
                $this->_doing_it_wrong( __METHOD__, $this->__( 'Block style name must not contain any spaces.' ),'0.0.1');
                return false;
            }
            $block_style_name = $style_properties['name'];
            if ( ! isset( $this->__registered_block_styles[ $block_name ] ) )
                $this->__registered_block_styles[ $block_name ] = [];
            $this->__registered_block_styles[ $block_name ][ $block_style_name ] = $style_properties;
            return true;
        }
        public function unregister( $block_name, $block_style_name ):bool {
            if ( ! $this->is_registered( $block_name, $block_style_name ) ) {
                $this->_doing_it_wrong(__METHOD__, sprintf( $this->__( 'Block "%1$s" does not contain a style named "%2$s".' ), $block_name, $block_style_name ),'0.0.1');
                return false;
            }
            unset( $this->__registered_block_styles[ $block_name ][ $block_style_name ] );
            return true;
        }
        public function registered( $block_name, $block_style_name ):void {
            if ( ! $this->is_registered( $block_name, $block_style_name ) ) return null;
            return $this->__registered_block_styles[ $block_name ][ $block_style_name ];
        }
        public function get_all_registered():array {
            return $this->__registered_block_styles;
        }
        public function get_registered_styles_for_block( $block_name ) {
            if ( isset( $this->__registered_block_styles[ $block_name ] ) )
                return $this->__registered_block_styles[ $block_name ];
            return [];
        }
        public function is_registered( $block_name, $block_style_name ):bool {
            return isset( $this->__registered_block_styles[ $block_name ][ $block_style_name ] );
        }
        public static function get_instance(): ?TP_Block_Styles_Registry{
            if ( null === self::$__instance ) self::$__instance = new self();
            return self::$__instance;
        }
    }
}else die;