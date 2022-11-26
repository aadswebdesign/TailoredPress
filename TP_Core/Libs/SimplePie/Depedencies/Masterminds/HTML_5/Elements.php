<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 11:53
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5;
use TP_Core\Libs\SimplePie\Factory\_mm_vars;
if(ABSPATH){
    class Elements{
        public const KNOWN_ELEMENT = 1;
        public const TEXT_RAW = 2;
        public const TEXT_RCDATA = 4;
        public const VOID_TAG = 8;
        public const AUTOCLOSE_P = 16;
        public const TEXT_PLAINTEXT = 32;
        public const BLOCK_TAG = 64;
        public const BLOCK_ONLY_INLINE = 128;
        use _mm_vars;
        public static function isA($name, $mask):bool{
            return (static::element($name) & $mask) === $mask;
        }
        public static function isHtml5Element($name):bool{
            return isset(static::$html5[strtolower($name)]);
        }
        public static function isMathMLElement($name):bool{
            return isset(static::$mathml[$name]);
        }
        public static function isSvgElement($name):bool{
            return isset(static::$svg[$name]);
        }
        public static function isElement($name):bool{
            return static::isHtml5Element($name) || static::isMathMLElement($name) || static::isSvgElement($name);
        }
        public static function element($name){
            if (isset(static::$html5[$name])) return static::$html5[$name];
            if (isset(static::$svg[$name])) return static::$svg[$name];
            if (isset(static::$mathml[$name])) return static::$mathml[$name];
            return 0;
        }
        public static function normalizeSvgElement($name){
            $name = strtolower($name);
            if (isset(static::$svgCaseSensitiveElementMap[$name]))
                $name = static::$svgCaseSensitiveElementMap[$name];
            return $name;
        }
        public static function normalizeSvgAttribute($name){
            $name = strtolower($name);
            if (isset(static::$svgCaseSensitiveAttributeMap[$name]))
                $name = static::$svgCaseSensitiveAttributeMap[$name];
            return $name;
        }
        public static function normalizeMathMlAttribute($name):string{
            $name = strtolower($name);
            if ('definition_url' === $name) $name = 'definitionURL';
            return $name;
        }
    }
}else die;