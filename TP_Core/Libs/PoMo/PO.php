<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-2-2022
 * Time: 03:18
 */
namespace TP_Core\Libs\PoMo;
if(ABSPATH){
    class PO extends Gettext_Translations{
        public $comments_before_headers = '';
        public function __construct(){
            if ( ! defined( 'PO_MAX_LINE_LEN' ) ) {
                define( 'PO_MAX_LINE_LEN', 79 );
            }
        }
        public function export_headers() {
            $header_string = '';
            foreach ( $this->headers as $header => $value ) {
                $header_string .= "$header: $value\n";
            }
            $poified = self::po_ify( $header_string );
            if ( $this->comments_before_headers ) {
                $before_headers = self::prepend_each_line( rtrim( $this->comments_before_headers ) . "\n", '# ' );
            } else {
                $before_headers = '';
            }
            return rtrim( "{$before_headers}msgid \"\"\nmsgstr $poified" );
        }
        public function export_entries(): string
        {
            // TODO: Sorting.
            return implode( "\n\n", array_map( array( 'PO', 'export_entry' ), $this->entries ) );
        }
        public function export( $include_headers = true ): string
        {
            $res = '';
            if ( $include_headers ) {
                $res .= $this->export_headers();
                $res .= "\n\n";
            }
            $res .= $this->export_entries();
            return $res;
        }
        public function export_to_file( $filename, $include_headers = true ) {
            $fh = fopen( $filename, 'wb' );
            if ( false === $fh ) {
                return false;
            }
            $export = $this->export( $include_headers );
            $res    = fwrite( $fh, $export );
            if ( false === $res ) {
                return false;
            }
            return fclose( $fh );
        }
        public function set_comment_before_headers( $text ): void
        {
            $this->comments_before_headers = $text;
            return null;
        }
        public static function po_ify( $string ) {
            $quote   = '"';
            $slash   = '\\';
            $newline = "\n";
            $replaces = array(
                ($slash) => "$slash$slash",
                ($quote) => "$slash$quote",
                "\t"     => '\t',
            );
            $string = str_replace( array_keys( $replaces ), array_values( $replaces ), $string );
            $po = $quote . implode( "${slash}n$quote$newline$quote", explode( $newline, $string ) ) . $quote;
            // Add empty string on first line for readbility.
            if ( false !== strpos( $string, $newline ) &&
                ( substr_count( $string, $newline ) > 1 || substr( $string, -strlen( $newline ) ) !== $newline ) ) {
                $po = "$quote$quote$newline$po";
            }
            // Remove empty strings.
            $po = str_replace( "$newline$quote$quote", '', $po );
            return $po;
        }
        public static function un_po_ify( $string ) {
            $escapes               = array(
                't'  => "\t",
                'n'  => "\n",
                'r'  => "\r",
                '\\' => '\\',
            );
            $lines                 = array_map( 'trim', explode( "\n", $string ) );
            $lines                 = array_map( array( 'PO', 'trim_quotes' ), $lines );
            $unpoified             = '';
            $previous_is_backslash = false;
            foreach ( $lines as $line ) {
                preg_match_all( '/./u', $line, $chars );
                $chars = $chars[0];
                foreach ( $chars as $char ) {
                    if ( ! $previous_is_backslash ) {
                        if ( '\\' === $char ) {
                            $previous_is_backslash = true;
                        } else {
                            $unpoified .= $char;
                        }
                    } else {
                        $previous_is_backslash = false;
                        $unpoified            .= isset( $escapes[ $char ] ) ?? $char;
                    }
                }
            }
            $unpoified = str_replace( array( "\r\n", "\r" ), "\n", $unpoified );
            return $unpoified;
        }
        public static function prepend_each_line( $string, $with ): string
        {
            $lines  = explode( "\n", $string );
            $append = '';
            if ( "\n" === substr( $string, -1 ) && '' === end( $lines ) ) {
                /*
                 * Last line might be empty because $string was terminated
                 * with a newline, remove it from the $lines array,
                 * we'll restore state by re-terminating the string at the end.
                 */
                array_pop( $lines );
                $append = "\n";
            }
            foreach ( $lines as &$line ) {
                $line = $with . $line;
            }
            unset( $line );
            return implode( "\n", $lines ) . $append;
        }
        public static function comment_block( $text, $char = ' ' ) {
            $text = wordwrap( $text, PO_MAX_LINE_LEN - 3 );
            return self::prepend_each_line( $text, "#$char " );
        }
        public static function export_entry( $entry ) {
            if ( null === $entry->singular || '' === $entry->singular ) {
                return false;
            }
            $po = array();
            if ( ! empty( $entry->translator_comments ) ) {
                $po[] = self::comment_block( $entry->translator_comments );
            }
            if ( ! empty( $entry->extracted_comments ) ) {
                $po[] = self::comment_block( $entry->extracted_comments, '.' );
            }
            if ( ! empty( $entry->references ) ) {
                $po[] = self::comment_block( implode( ' ', $entry->references ), ':' );
            }
            if ( ! empty( $entry->flags ) ) {
                $po[] = self::comment_block( implode( ', ', $entry->flags ), ',' );
            }
            if ( $entry->context ) {
                $po[] = 'msgctxt ' . self::po_ify( $entry->context );
            }
            $po[] = 'msgid ' . self::po_ify( $entry->singular );
            if ( ! $entry->is_plural ) {
                $translation = empty( $entry->translations ) ? '' : $entry->translations[0];
                $translation = self::match_begin_and_end_newlines( $translation, $entry->singular );
                $po[]        = 'msgstr ' . self::po_ify( $translation );
            } else {
                $po[]         = 'msgid_plural ' . self::po_ify( $entry->plural );
                $translations = empty( $entry->translations ) ? array( '', '' ) : $entry->translations;
                foreach ( $translations as $i => $translation ) {
                    $translation = self::match_begin_and_end_newlines( $translation, $entry->plural );
                    $po[]        = "msgstr[$i] " . self::po_ify( $translation );
                }
            }
            return implode( "\n", $po );
        }
        public static function match_begin_and_end_newlines( $translation, $original ) {
            if ( '' === $translation ) {
                return $translation;
            }
            $original_begin    = strpos( $translation,"\n" ) === 0;
            $original_end      = "\n" === substr( $original, -1 );
            $translation_begin = strpos( $translation,"\n" ) === 0;
            $translation_end   = "\n" === substr( $translation, -1 );
            if ( $original_begin ) {
                if ( ! $translation_begin ) {
                    $translation = "\n" . $translation;
                }
            } elseif ( $translation_begin ) {
                $translation = ltrim( $translation, "\n" );
            }
            if ( $original_end ) {
                if ( ! $translation_end ) {
                    $translation .= "\n";
                }
            } elseif ( $translation_end ) {
                $translation = rtrim( $translation, "\n" );
            }
            return $translation;
        }
        public function import_from_file( $filename ): bool {
            $f = fopen( $filename, 'rb' );
            if ( ! $f ) {
                return false;
            }
            $lineno = 0;
            $_res = null;
            while ( true ) {
                $res = $this->read_entry( $f, $lineno );
                if ( ! $res ) {
                    break;
                }
                if ( '' === $res['entry']->singular ) {
                    $this->set_headers( $this->make_headers( $res['entry']->translations[0] ) );
                } else {
                    $this->add_entry( $res['entry'] );
                }

                $_res = $res;
            }
            $this->read_line( $f, 'clear' );
            if ( false === $_res ) {
                return false;
            }
            if ( ! $this->headers && ! $this->entries ) {
                return false;
            }
            return true;
        }
        protected static function _is_final( $context ): bool {
            return ( 'msgstr' === $context ) || ( 'msgstr_plural' === $context );
        }
        public function read_entry( $f, $line_no = 0 ) {
            $entry = new TP_Translation_Entry();
            // Where were we in the last step.
            // Can be: comment, msgctxt, msgid, msgid_plural, msgstr, msgstr_plural.
            $context      = '';
            $msg_str_index = 0;
            while ( true ) {
                $line_no++;
                $line = $this->read_line( $f );
                if ( ! $line ) {
                    if ( feof( $f ) ) {
                        if ( self::_is_final( $context ) ) {
                            break;
                        }
                        if ( ! $context ) { // We haven't read a line and EOF came.
                            return null;
                        }
                        return false;
                    }
                    return false;
                }
                if ( "\n" === $line ) {
                    continue;
                }
                $line = trim( $line );
                if ( preg_match( '/^#/', $line, $m ) ) {
                    // The comment is the start of a new entry.
                    if ( self::_is_final( $context ) ) {
                        $this->read_line( $f, 'put-back' );
                        $line_no--;
                        break;
                    }
                    // Comments have to be at the beginning.
                    if ( $context && 'comment' !== $context ) {
                        return false;
                    }
                    // Add comment.
                    $this->add_comment_to_entry( $entry, $line );
                } elseif ( preg_match( '/^msgctxt\s+(".*")/', $line, $m ) ) {
                    if ( self::_is_final( $context ) ) {
                        $this->read_line( $f, 'put-back' );
                        $line_no--;
                        break;
                    }
                    if ( $context && 'comment' !== $context ) {
                        return false;
                    }
                    $context         = 'msgctxt';
                    $entry->context .= self::un_po_ify( $m[1] );
                } elseif ( preg_match( '/^msgid\s+(".*")/', $line, $m ) ) {
                    if ( self::_is_final( $context ) ) {
                        $this->read_line( $f, 'put-back' );
                        $line_no--;
                        break;
                    }
                    if ( $context && 'msgctxt' !== $context && 'comment' !== $context ) {
                        return false;
                    }
                    $context          = 'msgid';
                    $entry->singular .= self::un_po_ify( $m[1] );
                } elseif ( preg_match( '/^msgid_plural\s+(".*")/', $line, $m ) ) {
                    if ( 'msgid' !== $context ) {
                        return false;
                    }
                    $context          = 'msgid_plural';
                    $entry->is_plural = true;
                    $entry->plural   .= self::un_po_ify( $m[1] );
                } elseif ( preg_match( '/^msgstr\s+(".*")/', $line, $m ) ) {
                    if ( 'msgid' !== $context ) {
                        return false;
                    }
                    $context             = 'msgstr';
                    $entry->translations = array( self::un_po_ify( $m[1] ) );
                } elseif ( preg_match( '/^msgstr\[(\d+)\]\s+(".*")/', $line, $m ) ) {
                    if ( 'msgid_plural' !== $context && 'msgstr_plural' !== $context ) {
                        return false;
                    }
                    $context                      = 'msgstr_plural';
                    $msg_str_index                 = $m[1];
                    $entry->translations[ $m[1] ] = self::un_po_ify( $m[2] );
                } elseif ( preg_match( '/^".*"$/', $line ) ) {
                    $unpoified = self::un_po_ify( $line );
                    switch ( $context ) {
                        case 'msgid':
                            $entry->singular .= $unpoified;
                            break;
                        case 'msgctxt':
                            $entry->context .= $unpoified;
                            break;
                        case 'msgid_plural':
                            $entry->plural .= $unpoified;
                            break;
                        case 'msgstr':
                            $entry->translations[0] .= $unpoified;
                            break;
                        case 'msgstr_plural':
                            $entry->translations[ $msg_str_index ] .= $unpoified;
                            break;
                        default:
                            return false;
                    }
                } else {
                    return false;
                }
            }

            $have_translations = false;
            foreach ( $entry->translations as $t ) {
                if ( $t || ( '0' === $t ) ) {
                    $have_translations = true;
                    break;
                }
            }
            if ( false === $have_translations ) {
                $entry->translations = array();
            }

            return array(
                'entry'  => $entry,
                'lineno' => $line_no,
            );
        }
        public function read_line( $f, $action = 'read' ): bool {
            static $last_line     = '';
            static $use_last_line = false;
            if ( 'clear' === $action ) {
                $last_line = '';
                return true;
            }
            if ( 'put-back' === $action ) {
                $use_last_line = true;
                return true;
            }
            $line          = $use_last_line ? $last_line : fgets( $f );
            $line          = ( "\r\n" === substr( $line, -2 ) ) ? rtrim( $line, "\r\n" ) . "\n" : $line;
            $last_line     = $line;
            $use_last_line = false;
            return $line;
        }
        public function add_comment_to_entry( &$entry, $po_comment_line ): void
        {
            $first_two = substr( $po_comment_line, 0, 2 );
            $comment   = trim( substr( $po_comment_line, 2 ) );
            if ( '#:' === $first_two ) {
                $entry->references = array_merge( $entry->references, preg_split( '/\s+/', $comment ) );
            } elseif ( '#.' === $first_two ) {
                $entry->extracted_comments = trim( $entry->extracted_comments . "\n" . $comment );
            } elseif ( '#,' === $first_two ) {
                $entry->flags = array_merge( $entry->flags, preg_split( '/,\s*/', $comment ) );
            } else {
                $entry->translator_comments = trim( $entry->translator_comments . "\n" . $comment );
            }
            return null;
        }
        public static function trim_quotes( $s ): string
        {
            if ( strpos($s, '"') === 0 ) {
                $s = substr( $s, 1 );
            }
            if ( '"' === $s[strlen($s)-1] ) {
                $s = substr( $s, 0, -1 );
            }
            return $s;
        }
    }
}else die;

