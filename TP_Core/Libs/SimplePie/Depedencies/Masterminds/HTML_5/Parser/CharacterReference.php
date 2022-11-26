<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 12:13
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Parser;
use TP_Core\Libs\SimplePie\Factory\_sp_chars;
if(ABSPATH){
    class CharacterReference {
        use _sp_chars;
        protected static $numeric_mask = [0x0,0x2FFFF,0,0xFFFF,];
        public static function lookupName($name){
            return self::$names_to_chars[$name] ?? null;
        }
        public static function lookupDecimal($int):string{
            $entity = '&#' . $int . ';';
            return mb_decode_numericentity($entity, static::$numeric_mask, 'utf-8');
        }
        public static function lookupHex($hexdec):string{
            return static::lookupDecimal(hexdec($hexdec));
        }
    }
}else die;