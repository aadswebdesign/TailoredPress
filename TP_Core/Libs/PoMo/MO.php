<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-2-2022
 * Time: 17:42
 */
namespace TP_Core\Libs\PoMo;
if(ABSPATH){
    class MO extends Gettext_Translations{
        public $_nplurals = 2;
        private $__filename = '';
        public function get_filename(): string{
            return $this->__filename;
        }
        public function import_from_file( $filename ) {
            $reader = new POMO_FileReader( $filename );
            if (!$reader->is_resource()) return false;
            $this->__filename = (string) $filename;
            return $this->import_from_reader( $reader );
        }
        public function export_to_file( $filename ) {
            $fh = fopen( $filename, 'wb' );
            if ( ! $fh ) return false;
            $res = $this->export_to_file_handle( $fh );
            fclose( $fh );
            return $res;
        }
        public function export() {
            $tmp_fh = fopen( 'php://temp', 'rb+' );
            if ( ! $tmp_fh ) return false;
            $this->export_to_file_handle( $tmp_fh );
            rewind( $tmp_fh );
            return stream_get_contents( $tmp_fh );
        }
        public function is_entry_good_for_export( $entry ): bool {
            if ( empty( $entry->translations ) )  return false;
            if ( ! array_filter( $entry->translations ) ) return false;
            return true;
        }
        public function export_to_file_handle( $fh ): bool
        {
            $entries = array_filter( $this->entries, array( $this, 'is_entry_good_for_export' ) );
            ksort( $entries );
            $magic                     = 0x950412de;
            $revision                  = 0;
            $total                     = count( $entries ) + 1; // All the headers are one entry.
            $originals_lengths_addr    = 28;
            $translations_lengths_addr = $originals_lengths_addr + 8 * $total;
            $size_of_hash              = 0;
            $hash_addr                 = $translations_lengths_addr + 8 * $total;
            $current_addr              = $hash_addr;
            fwrite(
                $fh,
                pack(
                    'V*',
                    $magic,
                    $revision,
                    $total,
                    $originals_lengths_addr,
                    $translations_lengths_addr,
                    $size_of_hash,
                    $hash_addr
                )
            );
            fseek( $fh, $originals_lengths_addr );
            // Headers' msg_id is an empty string.
            fwrite( $fh, pack( 'VV', 0, $current_addr ) );
            $current_addr++;
            $originals_table = "\0";
            $reader = new POMO_Reader();
            foreach ( $entries as $entry ) {
                $originals_table .= $this->export_original( $entry ) . "\0";
                $length           = $reader->strlen( $this->export_original( $entry ) );
                fwrite( $fh, pack( 'VV', $length, $current_addr ) );
                $current_addr += $length + 1; // Account for the NULL byte after.
            }
            $exported_headers = $this->export_headers();
            fwrite( $fh, pack( 'VV', $reader->strlen( $exported_headers ), $current_addr ) );
            $current_addr      += strlen( $exported_headers ) + 1;
            $translations_table = $exported_headers . "\0";
            foreach ( $entries as $entry ) {
                $translations_table .= $this->export_translations( $entry ) . "\0";
                $length              = $reader->strlen( $this->export_translations( $entry ) );
                fwrite( $fh, pack( 'VV', $length, $current_addr ) );
                $current_addr += $length + 1;
            }
            fwrite( $fh, $originals_table );
            fwrite( $fh, $translations_table );
            return true;
        }
        public function export_original( $entry ) {
            // TODO: Warnings for control characters.
            $exported = $entry->singular;
            if ( $entry->is_plural ) $exported .= "\0" . $entry->plural;
            if ( $entry->context ) $exported = $entry->context . "\4" . $exported;
            return $exported;
        }
        public function export_translations( $entry ) {
            // TODO: Warnings for control characters.
            return $entry->is_plural ? implode( "\0", $entry->translations ) : $entry->translations[0];
        }
        public function export_headers(): string
        {
            $exported = '';
            foreach ( $this->headers as $header => $value )
                $exported .= "$header: $value\n";
            return $exported;
        }
        public function get_byteorder( $magic ) {
            // The magic is 0x950412de.
            // bug in PHP 5.0.2, see https://savannah.nongnu.org/bugs/?func=detailitem&item_id=10565
            $magic_little    = - 1794895138;
            $magic_little_64 = 2500072158;
            // 0xde120495
            $magic_big = ( - 569244523 ) & 0xFFFFFFFF;
            if ( $magic_little === $magic || $magic_little_64 === $magic ) return 'little';
            elseif ( $magic_big === $magic ) return 'big';
            else return false;
        }
        public function import_from_reader(POMO_Reader $reader ): bool
        {
            $endian_string = $this->get_byteorder( $reader->readint32() );
            if ( false === $endian_string )  return false;
            $reader->setEndian( $endian_string );
            $_reader = null;
            if( $reader instanceof POMO_FileReader ){
                $_reader = $reader;
            }
            $endian = ( 'big' === $endian_string ) ? 'N' : 'V';
            $header = $_reader->read( 24 );
            if ( $_reader->strlen( $header ) !== 24 )  return false;
            // Parse header.
            $header = unpack( "{$endian}revision/{$endian}total/{$endian}originals_lengths_addr/{$endian}translations_lengths_addr/{$endian}hash_length/{$endian}hash_addr", $header );
            if ( ! is_array( $header ) )  return false;
            // Support revision 0 of MO format specs, only.
            if ( 0 !== $header['revision'] ) return false;
            // Seek to data blocks.
            $_reader->seekto( $header['originals_lengths_addr'] );
            // Read originals' indices.
            $originals_lengths_length = $header['translations_lengths_addr'] - $header['originals_lengths_addr'];
            if ( $originals_lengths_length !== $header['total'] * 8 ) return false;
            $originals = $_reader->read( $originals_lengths_length );
            if ( $_reader->strlen( $originals ) !== $originals_lengths_length ) return false;
            // Read translations' indices.
            $translations_lengths_length = $header['hash_addr'] - $header['translations_lengths_addr'];
            if ( $translations_lengths_length !== $header['total'] * 8 ) return false;
            $translations = $_reader->read( $translations_lengths_length );
            if ( $_reader->strlen( $translations ) !== $translations_lengths_length ) return false;
            // Transform raw data into set of indices.
            $originals    = $_reader->str_split( $originals, 8 );
            $translations = $_reader->str_split( $translations, 8 );
            // Skip hash table.
            $strings_addr = $header['hash_addr'] + $header['hash_length'] * 4;
            $_reader->seekto( $strings_addr );
            $strings = $_reader->read_all();
            $_reader->close();
            for ( $i = 0; $i < $header['total']; $i++ ) {
                $o = unpack( "{$endian}length/{$endian}pos", $originals[ $i ] );
                $t = unpack( "{$endian}length/{$endian}pos", $translations[ $i ] );
                if ( ! $o || ! $t ) return false;
                // Adjust offset due to reading strings to separate space before.
                $o['pos'] -= $strings_addr;
                $t['pos'] -= $strings_addr;
                $original    = $_reader->substr( $strings, $o['pos'], $o['length'] );
                $translation = $_reader->substr( $strings, $t['pos'], $t['length'] );
                if ( '' === $original ) $this->set_headers( $this->make_headers( $translation ) );
                else {
                    $entry                          = &$this->make_entry( $original, $translation );
                    $this->entries[ $entry->key() ] = &$entry;
                }
            }
            return true;
        }
        public function &make_entry( $original, $translation ): TP_Translation_Entry
        {
            $entry = new TP_Translation_Entry();
            // Look for context, separated by \4.
            $parts = explode( "\4", $original );
            if ( isset( $parts[1] ) ) {
                $original       = $parts[1];
                $entry->context = $parts[0];
            }
            // Look for plural original.
            $parts           = explode( "\0", $original );
            $entry->singular = $parts[0];
            if ( isset( $parts[1] ) ) {
                $entry->is_plural = true;
                $entry->plural    = $parts[1];
            }
            // Plural translations are also separated by \0.
            $entry->translations = explode( "\0", $translation );
            return $entry;
        }
        public function select_plural_form( $count ): int {
            return $this->gettext_select_plural_form( $count );
        }
        public function get_plural_forms_count(): int {
            return $this->_nplurals;
        }
    }
}else die;
