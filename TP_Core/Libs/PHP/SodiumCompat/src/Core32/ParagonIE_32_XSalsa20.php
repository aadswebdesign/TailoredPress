<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-11-2022
 * Time: 02:34
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core32;
use TP_Core\Libs\PHP\SodiumCompat\lib\SodiumException;
use TypeError;
if(ABSPATH){
    abstract class ParagonIE_32_XSalsa20 extends ParagonIE_32_HSalsa20{
        /**
         * Expand a key and nonce into an xsalsa20 keystream.
         *
         * @internal You should not use this directly from another application
         *
         * @param int $len
         * @param string $nonce
         * @param string $key
         * @return string
         * @throws SodiumException
         * @throws TypeError
         */
        public static function xsalsa20($len, $nonce, $key):string
        {
            $ret = self::salsa20(
                $len,
                self::substr($nonce, 16, 8),
                self::hsalsa20($nonce, $key)
            );
            return $ret;
        }

        /**
         * Encrypt a string with XSalsa20. Doesn't provide integrity.
         *
         * @internal You should not use this directly from another application
         *
         * @param string $message
         * @param string $nonce
         * @param string $key
         * @return string
         * @throws SodiumException
         * @throws TypeError
         */
        public static function xsalsa20_xor($message, $nonce, $key):string
        {
            return self::xorStrings(
                $message,
                self::xsalsa20(
                    self::strlen($message),
                    $nonce,
                    $key
                )
            );
        }
    }
}else{die;}

