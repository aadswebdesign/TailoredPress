<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-2-2022
 * Time: 03:49
 */
namespace TP_Core\Libs\PoMo;
if(ABSPATH){
    class TP_Translations {
        public $entries = array();
        public $headers = array();
        public function add_entry( $entry ): bool
        {
            if ( is_array( $entry ) ) {
                $entry = new TP_Translation_Entry( $entry );
            }
            $key = $entry->key();
            if ( false === $key ) {
                return false;
            }
            $this->entries[ $key ] = &$entry;
            return true;
        }
        public function add_entry_or_merge( $entry ): bool
        {
            if ( is_array( $entry ) ) {
                $entry = new TP_Translation_Entry( $entry );
            }
            $key = $entry->key();
            if ( false === $key ) {
                return false;
            }
            if ( isset( $this->entries[ $key ] ) ) {
                $this->entries[ $key ]->merge_with( $entry );
            } else {
                $this->entries[ $key ] = &$entry;
            }
            return true;
        }
        /**
         * TODO: this should be out of this class, it is gettext specific
         * @param $header
         * @param $value
         * @return string
         */
        public function set_header( $header, $value ): string {
            $this->headers[ $header ] = $value;
            return null;
        }
        public function set_headers( $headers ): void
        {
            foreach ( $headers as $header => $value ) {
                $this->set_header( $header, $value );
            }
            return null;
        }
        public function get_header( $header ): bool
        {
            return isset( $this->headers[ $header ] ) ?: false;
        }
        public function translate_entry(TP_Translation_Entry $entry ): bool
        {
            $key = $entry->key();
            return isset( $this->entries[ $key ] ) ?: false;
        }
        public function translate( $singular, $context = null ) {
            $entry      = new TP_Translation_Entry(
                array(
                    'singular' => $singular,
                    'context'  => $context,
                )
            );
            $translated = $this->translate_entry( $entry );
            return ( $translated && ! empty( $translated->translations ) ) ? $translated->translations[0] : $singular;
        }
        public function select_plural_form( $count ):int {
            return 1 === $count ? 0 : 1;
        }
        public function get_plural_forms_count():int {
            return 2;
        }
        public function translate_plural( $singular, $plural, $count, $context = null ) {
            $entry              = new TP_Translation_Entry(
                array(
                    'singular' => $singular,
                    'plural'   => $plural,
                    'context'  => $context,
                )
            );
            $translated         = $this->translate_entry( $entry );
            $index              = $this->select_plural_form( $count );
            $total_plural_forms = $this->get_plural_forms_count();
            if ( $translated && 0 <= $index && $index < $total_plural_forms &&
                is_array( $translated->translations ) &&
                isset( $translated->translations[ $index ] ) ) {
                return $translated->translations[ $index ];
            }
            return 1 === $count ? $singular : $plural;
        }
        public function merge_with( &$other ): void
        {
            foreach ((array) $other->entries as $entry )
                $this->entries[ $entry->key() ] = $entry;
            return null;
        }
        public function merge_originals_with( &$other ): void
        {
            foreach ((array) $other->entries as $entry ) {
                if ( ! isset( $this->entries[ $entry->key() ] ) )
                    $this->entries[ $entry->key() ] = $entry;
                else
                    $this->entries[ $entry->key() ]->merge_with( $entry );
            }
            return null;
        }
    }
}else die;