<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-4-2022
 * Time: 15:50
 */
namespace TP_Core\Libs;
if(ABSPATH){
    class TP_List_Util{
        private $__input;
        private $__output;
        private $__orderby = [];
        public function __construct( $input ) {
            $this->__output = $input;
            $this->__input  = $input;
        }
        public function get_input(): array{
            return $this->__input;
        }
        public function get_output(): array{
            return $this->__output;
        }
        public function filter( $args = array(), $operator = 'AND' ): array{
            if ( empty( $args ) ) return $this->__output;
            $operator = strtoupper( $operator );
            if ( ! in_array( $operator, array( 'AND', 'OR', 'NOT' ), true ) ) {
                $this->__output = array();
                return $this->__output;
            }
            $count    = count( $args );
            $filtered = array();
            foreach ( $this->__output as $key => $obj ) {
                $matched = 0;
                foreach ( $args as $m_key => $m_value ) {
                    if ( is_array( $obj ) ) {
                        if ( array_key_exists( $m_key, $obj ) && ( $m_value === $obj[ $m_key ] ) ) $matched++;
                    } elseif ( is_object( $obj ) ) {
                        if ( isset( $obj->{$m_key} ) && ( $m_value === $obj->{$m_key} ) ) $matched++;
                    }
                }
                if ( ( 'AND' === $operator && $matched === $count )|| ( 'OR' === $operator && $matched > 0 )|| ( 'NOT' === $operator && 0 === $matched ))
                    $filtered[ $key ] = $obj;
            }
            $this->__output = $filtered;
            return $this->__output;
        }
        public function pluck( $field, $index_key = null ): array{
            $newlist = array();
            if ( ! $index_key ) {
                foreach ((array) $this->__output as $key => $value ) {
                    if ( is_object( $value ) ) $newlist[ $key ] = $value->$field;
                    else $newlist[ $key ] = $value[ $field ];
                }
                $this->__output = $newlist;
                return $this->__output;
            }
            foreach ( $this->__output as $value ) {
                if ( is_object( $value ) ) {
                    if ( isset( $value->$index_key ) )
                        $newlist[ $value->$index_key ] = $value->$field;
                    else $newlist[] = $value->$field;
                } else if ( isset( $value[ $index_key ] ) )
                    $newlist[ $value[ $index_key ] ] = $value[ $field ];
                else $newlist[] = $value[ $field ];
            }
            $this->__output = $newlist;
            return $this->__output;
        }
        public function sort( $orderby = array(), $order = 'ASC', $preserve_keys = false ): array {
            if ( empty( $orderby ) ) return $this->__output;
            if ( is_string( (string)$orderby ) ) $orderby = array( (string)$orderby => $order );
            foreach ( $orderby as $field => $direction )
                $orderby[ $field ] = 'DESC' === strtoupper( $direction ) ? 'DESC' : 'ASC';
            $this->__orderby = $orderby;
            if ( $preserve_keys ) uasort( $this->__output, array( $this, '__sort_callback' ) );
            else usort( $this->__output, array( $this, '__sort_callback' ) );
            $this->__orderby = [];
            return $this->__output;
        }
        private function __sort_callback( $a, $b ) {
            if ( empty( $this->__orderby ) )return 0;
            $a = (array) $a;
            $b = (array) $b;
            foreach ( $this->__orderby as $field => $direction ) {
                if ( ! isset( $a[ $field ],$b[ $field ])) continue;
                if ( $a[ $field ] === $b[ $field ] ) continue;
                $results = 'DESC' === $direction ? array( 1, -1 ) : array( -1, 1 );
                if ( is_numeric( $a[ $field ] ) && is_numeric( $b[ $field ] ) )
                    return ( $a[ $field ] < $b[ $field ] ) ? $results[0] : $results[1];
                return 0 > strcmp( $a[ $field ], $b[ $field ] ) ? $results[0] : $results[1];
            }
            return 0;
        }
    }
}else die;