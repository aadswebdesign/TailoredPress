<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-2-2022
 * Time: 03:52
 */
namespace TP_Core\Libs\PoMo;
if(ABSPATH){
    class Gettext_Translations extends TP_Translations {
        protected $_gettext_select_plural_form,$_nplurals;
        /**
         * @param $count
         * @return mixed
         */
        public function gettext_select_plural_form( $count ) {
            if ( ! isset( $this->_gettext_select_plural_form ) || is_null( $this->_gettext_select_plural_form ) ) {
                @list( $nplurals, $expression )     = $this->nplurals_and_expression_from_header( $this->get_header( 'Plural-Forms' ) );
                $this->_nplurals                   = $nplurals;
                $this->_gettext_select_plural_form = $this->make_plural_form_function( $nplurals, $expression );
            }
            return call_user_func( $this->_gettext_select_plural_form, $count );
        }
        /**
         * @param string $header
         * @return array
         */
        public function nplurals_and_expression_from_header( $header ):array {
            if ( preg_match( '/^\s*nplurals\s*=\s*(\d+)\s*;\s+plural\s*=\s*(.+)$/', $header, $matches ) ) {
                $nplurals   = (int) $matches[1];
                $expression = trim( $matches[2] );
                return array( $nplurals, $expression );
            }
            return array( 2, 'n != 1' );
        }
        /**
         * @param $nplurals
         * @param $expression
         * @return array
         */
        public function make_plural_form_function( $nplurals = 2, $expression ):?array {
            try {
                $handler = new Plural_Forms( rtrim( $expression, ';' ) );
                return array( $handler, 'get' );
            } catch ( \Exception $e ) {
                return $this->make_plural_form_function( $nplurals, 'n != 1' );
            }
        }
        /**
         * Adds parentheses to the inner parts of ternary operators in
         * plural expressions, because PHP evaluates ternary oerators from left to right
         * @param string $expression the expression without parentheses
         * @return string the expression with parentheses added
         */
        public function parenthesize_plural_expression( $expression ):string{
            $expression .= ';';
            $res         = '';
            $depth       = 0;
            for ($i = 0, $iMax = strlen($expression); $i < $iMax; ++$i ) {
                $char = $expression[ $i ];
                switch ( $char ) {
                    case '?':
                        $res .= ' ? (';
                        $depth++;
                        break;
                    case ':':
                        $res .= ') : (';
                        break;
                    case ';':
                        $res  .= str_repeat( ')', $depth ) . ';';
                        $depth = 0;
                        break;
                    default:
                        $res .= $char;
                }
            }
            return rtrim( $res, ';' );
        }
        /**
         * @param string $translation
         * @return array
         */
        public function make_headers( $translation ):array{
            $headers = array();
            // Sometimes \n's are used instead of real new lines.
            $translation = str_replace( '\n', "\n", $translation );
            $lines       = explode( "\n", $translation );
            foreach ( $lines as $line ) {
                $parts = explode( ':', $line, 2 );
                if ( ! isset( $parts[1] ) ) {
                    continue;
                }
                $headers[ trim( $parts[0] ) ] = trim( $parts[1] );
            }
            return $headers;
        }
        /**
         * @param $header
         * @param $value
         * @return string
         */
        public function set_header( $header, $value ):string {
            parent::set_header( $header, $value );
            if ( 'Plural-Forms' === $header ) {
                @list( $nplurals, $expression )     = $this->nplurals_and_expression_from_header( $this->get_header( 'Plural-Forms' ) );
                $this->_nplurals                   = $nplurals;
                $this->_gettext_select_plural_form = $this->make_plural_form_function( $nplurals, $expression );
            }
        }
    }
}else die;