<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-2-2022
 * Time: 03:14
 */
namespace TP_Core\Libs\PoMo;
if(ABSPATH){
    class TP_Translation_Entry{
        public $is_plural = false;
        public $context;
        public $singular;
        public $plural;
        public $translations        = [];
        public $translator_comments = '';
        public $extracted_comments  = '';
        public $references          = [];
        public $flags               = [];
        public function __construct( $args = [] ) {
            if ( ! isset( $args['singular'] ) ) {
                return;
            }
            foreach ( $args as $varname => $value ) {
                $this->$varname = $value;
            }
            if ( isset( $args['plural'] ) && $args['plural'] ) {
                $this->is_plural = true;
            }
            if ( ! is_array( $this->translations ) ) {
                $this->translations = array();
            }
            if ( ! is_array( $this->references ) ) {
                $this->references = array();
            }
            if ( ! is_array( $this->flags ) ) {
                $this->flags = array();
            }
        }
        public function key() {
            if ( null === $this->singular || '' === $this->singular ) {
                return false;
            }

            // Prepend context and EOT, like in MO files.
            $key = ! $this->context ? $this->singular : $this->context . "\4" . $this->singular;
            // Standardize on \n line endings.
            $key = str_replace( array( "\r\n", "\r" ), "\n", $key );

            return $key;
        }
        /**
         * @param object $other
         */
        public function merge_with( &$other ): void
        {
            $this->flags      = array_unique( array_merge( $this->flags, $other->flags ) );
            $this->references = array_unique( array_merge( $this->references, $other->references ) );
            if ( $this->extracted_comments !== $other->extracted_comments ) {
                $this->extracted_comments .= $other->extracted_comments;
            }
            return null;
        }
    }
}else die;


