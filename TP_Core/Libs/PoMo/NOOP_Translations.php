<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-2-2022
 * Time: 03:53
 */
namespace TP_Core\Libs\PoMo;
if(ABSPATH){
    class NOOP_Translations {
        public $entries = [];
        public $headers = [];
        public function add_entry( $entry ): bool{
            if(!empty($entry)){
                return false;
            }
            return true;
        }
        public function set_header( $header, $value): bool{
            if(empty($header) || empty($value)){
                return false;
            }
            return null;
        }
        public function set_headers( $headers ): bool{
            if(!empty($headers)){
            return $headers;
        }
            return null;
        }
        public function get_header( $header ): bool
        {
            if(empty($header)){}
            return false;
        }
        public function translate_entry( &$entry ): bool
        {
            if(empty($entry)){}
            return false;
        }
        public function translate( $singular, $context = null ) {
            if(!empty($context)){
                return $singular;
            }
            return null;
        }
        public function select_plural_form( $count ): int {
            return 1 === $count ? 0 : 1;
        }
        public function get_plural_forms_count(): int
        {
            return 2;
        }
        public function translate_plural( $singular, $plural, $count, $context = null ) {//todo not used , $context = null
            if(!empty($context)){
                return 1 === $count ? $singular : $plural;
            }
            return null;
        }
        public function merge_with( &$other ): bool
        {
            if(!empty($other)){
                return $other;
            }
            return null;
        }
    }
}else die;


