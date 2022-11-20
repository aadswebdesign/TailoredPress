<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-5-2022
 * Time: 17:25
 */
namespace TP_Core\Libs\Diff;
use TP_Core\Libs\Diff\Components\text_diff_renderer;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\I10n\_I10n_01;
if(ABSPATH){
    class TP_TextDiff_Renderer_Table extends text_diff_renderer {
        use _filter_01;
        use _I10n_01;
        protected $_diff_threshold = 0.6;
        protected $_inline_diff_renderer = 'TP_Text_Diff_Renderer_inline';
        protected $_show_split_view = true;
        protected $_compat_fields = ['_show_split_view', 'inline_diff_renderer', '_diff_threshold'];
        protected $_count_cache = [];
        protected $_difference_cache =[];
        public $leading_context_lines = 10000;
        public $trailing_context_lines = 10000;
        public function __construct( $params = array() ) {
            parent::__construct( $params );
            if ( isset( $params['show_split_view'] ) ) $this->_show_split_view = $params['show_split_view'];
        }//83
        public function startBlock( $header ) {
            return $header;
        }//96
        public function lines( $lines, $prefix = ' ' ):void {}//106 todo
        public function addedLine( $line ): string {
            return "<td class='diff-addedline'><span aria-hidden='true' class='dashicons dashicons-plus'></span><span class='screen-reader-text'>" . $this->__( 'Added:' ) . " </span>{$line}</td>";
        }//115
        public function deletedLine( $line ): string {
            return "<td class='diff-deletedline'><span aria-hidden='true' class='dashicons dashicons-minus'></span><span class='screen-reader-text'>" . $this->__( 'Deleted:' ) . " </span>{$line}</td>";
        }//126
        public function contextLine( $line ): string {
            return "<td class='diff-context'><span class='screen-reader-text'>" . $this->__( 'Unchanged:' ) . " </span>{$line}</td>";
        }//136
        public function emptyLine(): string {
            return '<td>&nbsp;</td>';
        }
        public function addedLines( $lines, $encode = true ): string{
            $r = '';
            foreach ( $lines as $line ) {
                if ( $encode ) {
                    $processed_line = htmlspecialchars( $line );
                    $line = $this->_apply_filters( 'process_text_diff_html', $processed_line, $line, 'added' );
                }
                if ( $this->_show_split_view )  $r .= '<tr>' . $this->emptyLine() . $this->addedLine( $line ) . "</tr>\n";
                else $r .= '<tr>' . $this->addedLine( $line ) . "</tr>\n";
            }
            return $r;
        }
        public function deletedLines( $lines, $encode = true ): string{
            $r = '';
            foreach ( $lines as $line ) {
                if ( $encode ) {
                    $processed_line = htmlspecialchars( $line );
                    $line = $this->_apply_filters( 'process_text_diff_html', $processed_line, $line, 'deleted' );
                }
                if ( $this->_show_split_view )  $r .= '<tr>' . $this->deletedLine( $line ) . $this->emptyLine() . "</tr>\n";
                else $r .= '<tr>' . $this->deletedLine( $line ) . "</tr>\n";
            }
            return $r;
        }
        public function contextLines( $lines, $encode = true ): string{
            $r = '';
            foreach ( $lines as $line ) {
                if ( $encode ) {
                    $processed_line = htmlspecialchars( $line );
                    $line = $this->_apply_filters( 'process_text_diff_html', $processed_line, $line, 'unchanged' );
                }
                if ( $this->_show_split_view ) $r .= '<tr>' . $this->contextLine( $line ) . $this->contextLine( $line ) . "</tr>\n";
                else  $r .= '<tr>' . $this->contextLine( $line ) . "</tr>\n";
            }
            return $r;
        }
        public function changed( $orig, $final ): string{
            $r = ''; //not used in list , $final_matches
            @list($orig_matches, $orig_rows, $final_rows) = $this->interleave_changed_lines( $orig, $final );
            $orig_diffs  = [];
            $final_diffs = [];
            foreach ( $orig_matches as $o => $f ) {
                if ( is_numeric( $o ) && is_numeric( $f ) ) {
                    $text_diff = new TextDiff( 'auto', array( array( $orig[ $o ] ), array( $final[ $f ] ) ) );
                    $renderer  = new $this->_inline_diff_renderer;
                    $diff = null;
                    if($renderer  instanceof  text_diff_renderer){
                        $diff = $renderer->render( $text_diff );
                    }
                    if ( preg_match_all( '!(<ins>.*/s?</ins>|<del>.*/s?</del>)!', $diff, $diff_matches ) ) {
                        $stripped_matches = strlen( strip_tags( implode( ' ', $diff_matches[0] ) ) );
                        $stripped_diff = strlen( strip_tags( $diff ) ) * 2 - $stripped_matches;
                        $diff_ratio    = $stripped_matches / $stripped_diff;
                        if ( $diff_ratio > $this->_diff_threshold ) continue;
                    }
                    $orig_diffs[ $o ]  = preg_replace( '|<ins>./s*?</ins>|', '', $diff );
                    $final_diffs[ $f ] = preg_replace( '|<del>./s*?</del>|', '', $diff );
                }
            }
            foreach ( array_keys( $orig_rows ) as $row ) {
                // Both columns have blanks. Ignore them.
                if ( $orig_rows[ $row ] < 0 && $final_rows[ $row ] < 0 ) {
                    continue;
                }
                // If we have a word based diff, use it. Otherwise, use the normal line.
                if ( isset( $orig_diffs[ $orig_rows[ $row ] ] ) ) {
                    $orig_line = $orig_diffs[ $orig_rows[ $row ] ];
                } elseif ( isset( $orig[ $orig_rows[ $row ] ] ) ) {
                    $orig_line = htmlspecialchars( $orig[ $orig_rows[ $row ] ] );
                } else {
                    $orig_line = '';
                }
                if ( isset( $final_diffs[ $final_rows[ $row ] ] ) ) {
                    $final_line = $final_diffs[ $final_rows[ $row ] ];
                } elseif ( isset( $final[ $final_rows[ $row ] ] ) ) {
                    $final_line = htmlspecialchars( $final[ $final_rows[ $row ] ] );
                } else {
                    $final_line = '';
                }
                if ( $orig_rows[ $row ] < 0 ) { // Orig is blank. This is really an added row.
                    $r .= $this->addedLines( array( $final_line ), false );
                } elseif ( $final_rows[ $row ] < 0 ) { // Final is blank. This is really a deleted row.
                    $r .= $this->deletedLines( array( $orig_line ), false );
                } else if ( $this->_show_split_view ) {
                    $r .= '<tr>' . $this->deletedLine( $orig_line ) . $this->addedLine( $final_line ) . "</tr>\n";
                } else {
                    $r .= '<tr>' . $this->deletedLine( $orig_line ) . '</tr><tr>' . $this->addedLine( $final_line ) . "</tr>\n";
                }
            }
            return $r;
        }
        public function interleave_changed_lines( $orig, $final ): array{
            $matches = [];
            foreach ( array_keys( $orig ) as $o ) {
                foreach ( array_keys( $final ) as $f )
                    $matches[ "$o,$f" ] = $this->compute_string_distance( $orig[ $o ], $final[ $f ] );
            }
            asort( $matches ); // Order by string distance.
            $orig_matches  = [];
            $final_matches = [];
            foreach ( $matches as $keys => $difference ) {
                @list($o, $f) = explode( ',', $keys );
                $o           = (int) $o;
                $f           = (int) $f;
                if ( isset( $orig_matches[ $o ], $final_matches[ $f ] ) )
                    continue;
                if ( ! isset( $orig_matches[ $o ], $final_matches[ $f ] ) ) {
                    $orig_matches[ $o ]  = $f;
                    $final_matches[ $f ] = $o;
                    continue;
                }
                if ( isset( $orig_matches[ $o ] ) )  $final_matches[ $f ] = 'x';
                elseif ( isset( $final_matches[ $f ] ) ) $orig_matches[ $o ] = 'x';
            }
            ksort( $orig_matches );
            ksort( $final_matches );
            $orig_rows      = array_keys( $orig_matches );
            $orig_rows_copy = $orig_rows;
            $final_rows     = array_keys( $final_matches );
            foreach ( $orig_rows_copy as $orig_row ) {
                $final_pos = array_search( $orig_matches[ $orig_row ], $final_rows, true );
                $orig_pos  = (int) array_search( $orig_row, $orig_rows, true );
                if ( false === $final_pos ) {
                    array_splice( $final_rows, $orig_pos, 0, -1 );
                } elseif ( $final_pos < $orig_pos ) {
                    $diff_array = range( -1, $final_pos - $orig_pos );
                    array_splice( $final_rows, $orig_pos, 0, $diff_array );
                } elseif ( $final_pos > $orig_pos ) {
                    $diff_array = range( -1, $orig_pos - $final_pos );
                    array_splice( $orig_rows, $orig_pos, 0, $diff_array );
                }
            }
            $diff_count = count( $orig_rows ) - count( $final_rows );
            if ( $diff_count < 0 ) {
                while ( $diff_count < 0 ) {
                    $orig_rows[] = $diff_count++;
                }
            } elseif ( $diff_count > 0 ) {
                $diff_count = -1 * $diff_count;
                while ( $diff_count < 0 ) {
                    $final_rows[] = $diff_count++;
                }
            }

            return array( $orig_matches, $final_matches, $orig_rows, $final_rows );
        }
        public function compute_string_distance( $string1, $string2 ) {
            $count_key1 = md5( $string1 );
            $count_key2 = md5( $string2 );
            if ( ! isset( $this->_count_cache[ $count_key1 ] ) )
                $this->_count_cache[ $count_key1 ] = count_chars( $string1 );
            if ( ! isset( $this->_count_cache[ $count_key2 ] ) )
                $this->_count_cache[ $count_key2 ] = count_chars( $string2 );
            $chars1 = $this->_count_cache[ $count_key1 ];
            $chars2 = $this->_count_cache[ $count_key2 ];
            $difference_key = md5( implode( ',', $chars1 ) . ':' . implode( ',', $chars2 ) );
            if ( ! isset( $this->_difference_cache[ $difference_key ] ) )
                $this->_difference_cache[ $difference_key ] = array_sum( array_map( array( $this, 'difference' ), $chars1, $chars2 ) );
            $difference = $this->_difference_cache[ $difference_key ];
            if ( ! $string1 )  return $difference;
            return $difference / strlen( $string1 );
        }
        public function difference( $a, $b ) {
            return abs( $a - $b );
        }
    }
}else die;