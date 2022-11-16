<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
use TP_Core\Traits\Inits\_init_list_util;
if(ABSPATH){
    trait _methods_11{
        use _init_list_util;
        /**
         * @description Cleans up an array, comma- or space-separated list of slugs.
         * @param $list
         * @return array
         */
        protected function _tp_parse_slug_list( $list ):array{
            $list = $this->_tp_parse_list( $list );
            return array_unique( array_map( 'sanitize_title', $list ) );
        }//4794
        /**
         * @description Extract a slice of an array, given a list of keys.
         * @param $array
         * @param $keys
         * @return array
         */
        protected function _tp_array_slice_assoc( $array, $keys ):array{
            $slice = [];
            foreach ( $keys as $key ) {
                if ( isset( $array[ $key ] ) )  $slice[ $key ] = $array[ $key ];
            }
            return $slice;
        }//4809
        /**
         * @description Accesses an array in depth based on a path of keys.
         * @param $array
         * @param $path
         * @param null $default
         * @return string
         */
        protected function _tp_array_get( $array, $path, $default = null ):string{
            if ( ! is_array( $path ) || 0 === count( $path ) ) return $default;
            foreach ( $path as $path_element ) {
                if ( ! is_array( $array ) ||
                    ( ! is_string( $path_element ) && ! is_int( $path_element ) && ! is_null( $path_element ) ) ||
                    ! array_key_exists( $path_element, $array )
                )  return $default;
                $array = $array[ $path_element ];
            }
            return $array;
        }//4849
        /**
         * @description Sets an array in depth based on a path of keys.
         * @param $array
         * @param $path
         * @param null $value
         */
        protected function _tp_array_set( &$array, $path, $value = null ):void{
            if ( ! is_array( $array ) )  return;
            if ( ! is_array( $path ) ) return;
            $path_length = count( $path );
            if ( 0 === $path_length ) return;
            foreach ( $path as $path_element ) {
                if (! is_string( $path_element ) && ! is_int( $path_element ) && ! is_null( $path_element ))
                    return;
            }
            for ( $i = 0; $i < $path_length - 1; ++$i ) {
                $path_element = $path[ $i ];
                if (! array_key_exists( $path_element, $array )||! is_array( $array[ $path_element ] ))
                    $array[ $path_element ] = array();
                $array = &$array[ $path_element ];
            }
            $array[ $path[ $i ] ] = $value;
        }//4898
        /**
         * @description This function is trying to replicate what * lodash's kebabCase (JS library) does in the client.
         * @param $string
         * @return string
         */
        protected function _tp_to_kebab_case( $string ):string{
            $rsLowerRange       = 'a-z\\xdf-\\xf6\\xf8-\\xff';
            $rsNonCharRange     = '\\x00-\\x2f\\x3a-\\x40\\x5b-\\x60\\x7b-\\xbf';
            $rsPunctuationRange = '\\x{2000}-\\x{206f}';
            $rsSpaceRange       = ' \\t\\x0b\\f\\xa0\\x{feff}\\n\\r\\x{2028}\\x{2029}\\x{1680}\\x{180e}\\x{2000}\\x{2001}\\x{2002}\\x{2003}\\x{2004}\\x{2005}\\x{2006}\\x{2007}\\x{2008}\\x{2009}\\x{200a}\\x{202f}\\x{205f}\\x{3000}';
            $rsUpperRange       = 'A-Z\\xc0-\\xd6\\xd8-\\xde';
            $rsBreakRange       = $rsNonCharRange . $rsPunctuationRange . $rsSpaceRange;
            /** Used to compose unicode capture groups. */
            $rsBreak  = '[' . $rsBreakRange . ']';
            $rsDigits = '\\d+'; // The last lodash version in GitHub uses a single digit here and expands it when in use.
            $rsLower  = '[' . $rsLowerRange . ']';
            $rsMisc   = '[^' . $rsBreakRange . $rsDigits . $rsLowerRange . $rsUpperRange . ']';
            $rsUpper  = '[' . $rsUpperRange . ']';
            /** Used to compose unicode regexes. */
            $rsMiscLower = '(?:' . $rsLower . '|' . $rsMisc . ')';
            $rsMiscUpper = '(?:' . $rsUpper . '|' . $rsMisc . ')';
            $rsOrdLower  = '\\d*(?:1st|2nd|3rd|(?![123])\\dth)(?=\\b|[A-Z_])';
            $rsOrdUpper  = '\\d*(?:1ST|2ND|3RD|(?![123])\\dTH)(?=\\b|[a-z_])';
            $regexp = '/' . implode(
                    '|',
                    array(
                        $rsUpper . '?' . $rsLower . '+' . '(?=' . implode( '|', array( $rsBreak, $rsUpper, '$' ) ) . ')',
                        $rsMiscUpper . '+' . '(?=' . implode( '|', array( $rsBreak, $rsUpper . $rsMiscLower, '$' ) ) . ')',
                        $rsUpper . '?' . $rsMiscLower . '+',$rsUpper . '+',$rsOrdUpper,$rsOrdLower,$rsDigits,
                    )
                ) . '/u';
            preg_match_all( $regexp, str_replace( "'", '', $string ), $matches );
            return strtolower( implode( '-', $matches[0] ) );
        }//4963
        /**
         * @description Determines if the variable is a numeric-indexed array.
         * @param $data
         * @return bool
         */
        protected function _tp_is_numeric_array( $data ):bool{
            if ( ! is_array( $data ) ) return false;
            $keys        = array_keys( $data );
            $string_keys = array_filter( $keys, 'is_string' );
            return count( $string_keys ) === 0;
        }//5023
        /**
         * @description Filters a list of objects, based on a set of key => value arguments.
         * @param $list
         * @param array $args
         * @param string $operator
         * @param bool $field
         * @return array
         */
        protected function _tp_filter_object_list( $list, $args = [], $operator = 'and', $field = false ):array{
            if ( ! is_array( $list ) ) return array();
            $util = $this->_init_list_util( $list );
            $util->filter( $args, $operator );
            if ( $field ) $util->pluck( $field );
            return $util->get_output();
        }//5062
        /**
         * @description Filters a list of objects, based on a set of key => value arguments.
         * @param $list
         * @param array $args
         * @param string $operator
         * @return array
         */
        protected function _tp_list_filter( $list, $args = [], $operator = 'AND' ):array{
            return $this->_tp_filter_object_list( $list, $args, $operator );
        }//5104
        /**
         * @description Plucks a certain field out of each object or array in an array.
         * @param $list
         * @param $field
         * @param null $index_key
         * @return array
         */
        protected function _tp_list_pluck( $list, $field, $index_key = null ):array{
            $this->tp_util = $this->_init_list_util( $list );
            return $this->tp_util->pluck( $field, $index_key );
        }//5126
        /**
         * @description Sorts an array of objects or arrays based on one or more orderby arguments.
         * @param $list
         * @param array $orderby
         * @param string $order
         * @param bool $preserve_keys
         * @return array
         */
        protected function _tp_list_sort( $list, $orderby = [], $order = 'ASC', $preserve_keys = false ):array{
            if ( ! is_array( $list ) ) return [];
            $this->tp_util = $this->_init_list_util( $list );
            return $this->tp_util->sort( $orderby, $order, $preserve_keys );
        }//5145
    }
}else die;