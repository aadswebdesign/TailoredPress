<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-11-2022
 * Time: 02:27
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core32;
use TP_Core\Libs\PHP\SodiumCompat\lib\SodiumException;
use TP_Core\Libs\PHP\SodiumCompat\src\Core32\ChaCha20\ParagonIE_32_ChaCha20_Ctx;
use TypeError;
if(ABSPATH){
    class ParagonIE_32_XChaCha20 extends ParagonIE_32_HChaCha20{
        /**
         * @internal You should not use this directly from another application
         *
         * @param int $len
         * @param string $nonce
         * @param string $key
         * @return string
         * @throws SodiumException
         * @throws TypeError
         */
        public static function stream($len = 64, $nonce = '', $key = ''):string
        {
            if (self::strlen($nonce) !== 24) {
                throw new SodiumException('Nonce must be 24 bytes long');
            }
            return self::encryptBytes(
                new ParagonIE_32_ChaCha20_Ctx(
                    self::hChaCha20(
                        self::substr($nonce, 0, 16),
                        $key
                    ),
                    self::substr($nonce, 16, 8)
                ),
                str_repeat("\x00", $len)
            );
        }

        /**
         * @internal You should not use this directly from another application
         *
         * @param string $message
         * @param string $nonce
         * @param string $key
         * @param string $ic
         * @return string
         * @throws SodiumException
         * @throws TypeError
         */
        public static function streamXorIc($message, $nonce = '', $key = '', $ic = ''):string
        {
            if (self::strlen($nonce) !== 24) {
                throw new SodiumException('Nonce must be 24 bytes long');
            }
            return self::encryptBytes(
                new ParagonIE_32_ChaCha20_Ctx(
                    self::hChaCha20(self::substr($nonce, 0, 16), $key),
                    self::substr($nonce, 16, 8),
                    $ic
                ),
                $message
            );
        }
    }
}else{die;}