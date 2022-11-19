<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 13:27
 */
declare(strict_types=1);
namespace TP_Core\Libs\Block;
if(ABSPATH){
    class TP_Block_List implements \Iterator, \ArrayAccess, \Countable {
        protected $_blocks;
        protected $_available_context;
        protected $_registry;
        public function __construct( $blocks, $available_context = array(), $registry = null ) {
            if ( ! $registry instanceof TP_Block_Type_Registry )  $registry = TP_Block_Type_Registry::get_instance();
            $this->_blocks            = $blocks;
            $this->_available_context = $available_context;
            $this->_registry          = $registry;
        }
        public function offsetExists( $index ) {
            return isset( $this->_blocks[ $index ] );
        }
        public function offsetGet( $index ) {
            $block = $this->_blocks[ $index ];
            if ( isset( $block ) && is_array( $block ) ) {
                $block                  = new TP_Block( $block, $this->_available_context, $this->_registry );
                $this->_blocks[ $index ] = $block;
            }
            return $block;
        }
        public function offsetSet( $index, $value ) {
            if ( is_null( $index ) ) $this->_blocks[] = $value;
            else $this->_blocks[ $index ] = $value;
        }
        public function offsetUnset( $index ) {
            unset( $this->_blocks[ $index ] );
        }
        public function rewind() {
            reset( $this->_blocks );
        }
        public function current() {
            return $this->offsetGet( $this->key() );
        }
        public function key() {
            return key( $this->_blocks );
        }
        public function next() {
            next( $this->_blocks );
        }
        public function valid() {
            return null !== key( $this->_blocks );
        }
        public function count() {
            return count( $this->_blocks );
        }
    }
}else die;