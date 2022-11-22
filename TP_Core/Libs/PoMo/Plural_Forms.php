<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-2-2022
 * Time: 03:11
 */
namespace TP_Core\Libs\PoMo;
if(ABSPATH){
    class Plural_Forms
    {
        public const OP_CHARS = '|&><!=%?:';
        public const NUM_CHARS = '0123456789';
        protected static $_op_precedence = array(
            '%'  => 6,'<'  => 5,'<=' => 5,'>'  => 5,'>=' => 5,'==' => 4,'!=' => 4,'&&' => 3,'||' => 2,'?:' => 1,'?'  => 1,'('  => 0,')'  => 0,
        );
        protected $_tokens = [];
        protected $_cache = [];
        public function __construct( $str ) {
            $this->_parse( $str );
        }
        protected function _parse( $str ): void
        {
            $pos = 0;
            $len = strlen( $str );
            $output = array();
            $stack  = array();
            while ( $pos < $len ) {
                $next = substr( $str, $pos, 1 );
                switch ( $next ) {
                    case ' ':
                    case "\t":
                        $pos++;
                        break;
                    case 'n':
                        $output[] = array( 'var' );
                        $pos++;
                        break;
                    case '(':
                        $stack[] = $next;
                        $pos++;
                        break;
                    case ')':
                        $found = false;
                        while ( ! empty( $stack ) ) {
                            $o2 = $stack[ count( $stack ) - 1 ];
                            if ( '(' !== $o2 ) {
                                $output[] = array( 'op', array_pop( $stack ) );
                                continue;
                            }
                            array_pop( $stack );
                            $found = true;
                            break;
                        }
                        if ( ! $found ) {
                            throw new \InvalidArgumentException( 'Mismatched parentheses' );
                        }
                        $pos++;
                        break;
                    case '|':
                    case '&':
                    case '>':
                    case '<':
                    case '!':
                    case '=':
                    case '%':
                    case '?':
                        $end_operator = strspn( $str, self::OP_CHARS, $pos );
                        $operator     = substr( $str, $pos, $end_operator );
                        if ( ! array_key_exists( $operator, self::$_op_precedence ) ) {
                            throw new \InvalidArgumentException( sprintf( 'Unknown operator "%s"', $operator ) );
                        }

                        while ( ! empty( $stack ) ) {
                            $o2 = $stack[ count( $stack ) - 1 ];
                            if ( '?:' === $operator || '?' === $operator ) {
                                if ( self::$_op_precedence[ $operator ] >= self::$_op_precedence[ $o2 ] ) {
                                    break;
                                }
                            } elseif ( self::$_op_precedence[ $operator ] > self::$_op_precedence[ $o2 ] ) {
                                break;
                            }
                        }
                        $stack[] = $operator;
                        $pos += $end_operator;
                        break;
                    case ':':
                        $found = false;
                        $s_pos = count( $stack ) - 1;
                        while ( $s_pos >= 0 ) {
                            $o2 = $stack[ $s_pos ];
                            if ( '?' !== $o2 ) {
                                $output[] = array( 'op', array_pop( $stack ) );
                                $s_pos--;
                                continue;
                            }
                            $stack[ $s_pos ] = '?:';
                            $found           = true;
                            break;
                        }
                        if ( ! $found ) {
                            throw new \InvalidArgumentException( 'Missing starting "?" ternary operator' );
                        }
                        $pos++;
                        break;

                    // Default - number or invalid.
                    default:
                        if ( $next >= '0' && $next <= '9' ) {
                            $span     = strspn( $str, self::NUM_CHARS, $pos );
                            $output[] = array( 'value', (int) substr( $str, $pos, $span ) );
                            $pos     += $span;
                            break;
                        }

                        throw new \InvalidArgumentException( sprintf( 'Unknown symbol "%s"', $next ) );
                }
            }

            while ( ! empty( $stack ) ) {
                $o2 = array_pop( $stack );
                if ( '(' === $o2 || ')' === $o2 ) {
                    throw new \InvalidArgumentException( 'Mismatched parentheses' );
                }

                $output[] = array( 'op', $o2 );
            }

            $this->_tokens = $output;
            return null;
        }
        public function get( $num ) {
            if ( isset( $this->_cache[ $num ] ) ) {
                return $this->_cache[ $num ];
            }
            $this->_cache[ $num ] = $this->execute( $num );
            return $this->_cache[ $num ];
        }
        /**
         * Execute the plural form function.
         *
         * @since 4.9.0
         *
         * @throws \Exception If the plural form value cannot be calculated.
         *
         * @param int $n Variable "n" to substitute.
         * @return int Plural form value.
         */
        public function execute( $n ): int
        {
            $stack = array();
            $i     = 0;
            $total = count( $this->_tokens );
            while ( $i < $total ) {
                $next = $this->_tokens[ $i ];
                $i++;
                if ( 'var' === $next[0] ) {
                    $stack[] = $n;
                    continue;
                }
                if ( 'value' === $next[0] ) {
                    $stack[] = $next[1];
                    continue;
                }

                // Only operators left.
                switch ( $next[1] ) {
                    case '%':
                        $v2      = array_pop( $stack );
                        $v1      = array_pop( $stack );
                        $stack[] = $v1 % $v2;
                        break;

                    case '||':
                        $v2      = array_pop( $stack );
                        $v1      = array_pop( $stack );
                        $stack[] = $v1 || $v2;
                        break;

                    case '&&':
                        $v2      = array_pop( $stack );
                        $v1      = array_pop( $stack );
                        $stack[] = $v1 && $v2;
                        break;

                    case '<':
                        $v2      = array_pop( $stack );
                        $v1      = array_pop( $stack );
                        $stack[] = $v1 < $v2;
                        break;

                    case '<=':
                        $v2      = array_pop( $stack );
                        $v1      = array_pop( $stack );
                        $stack[] = $v1 <= $v2;
                        break;

                    case '>':
                        $v2      = array_pop( $stack );
                        $v1      = array_pop( $stack );
                        $stack[] = $v1 > $v2;
                        break;

                    case '>=':
                        $v2      = array_pop( $stack );
                        $v1      = array_pop( $stack );
                        $stack[] = $v1 >= $v2;
                        break;

                    case '!=':
                        $v2      = array_pop( $stack );
                        $v1      = array_pop( $stack );
                        $stack[] = $v1 !== $v2;
                        break;

                    case '==':
                        $v2      = array_pop( $stack );
                        $v1      = array_pop( $stack );
                        $stack[] = $v1 === $v2;
                        break;

                    case '?:':
                        $v3      = array_pop( $stack );
                        $v2      = array_pop( $stack );
                        $v1      = array_pop( $stack );
                        $stack[] = $v1 ? $v2 : $v3;
                        break;

                    default:
                        throw new \InvalidArgumentException( sprintf( 'Unknown operator "%s"', $next[1] ) );
                }
            }

            if ( count( $stack ) !== 1 ) {
                throw new \InvalidArgumentException( 'Too many values remaining on the stack' );
            }

            return (int) $stack[0];
        }
    }
}else die;

