<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-5-2022
 * Time: 16:22
 */
namespace TP_Core\Libs;
if(ABSPATH){
    class TP_MatchesMapRegex{
        private $__matches;
        private $__subject;
        public $output;
        public $pattern = '(\$matches\[\d+\d*\])';
        public function __construct( $subject, $matches ){
            $this->__subject = $subject;
            $this->__matches = $matches;
            $this->output   = $this->__map();
        }//49
        public static function apply( $subject, $matches ){
            return (new self($subject, $matches))->output;
        }//64
        private function __map(){
            $callback = array( $this, 'callback' );
            return preg_replace_callback( $this->pattern, $callback, $this->__subject );
        }//74
        public function callback( $matches ): string{
            $index = (int) substr( $matches[0], 9, -1 );
            return ( isset( $this->__matches[ $index ] ) ? urlencode( $this->__matches[ $index ] ) : '' );
        }
    }
}else die;