<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-11-2022
 * Time: 16:59
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core32;
use TP_Core\Libs\PHP\SodiumCompat\src\Core32\Poly1305\ParagonIE_32_Poly1305_State;
use TP_Core\Libs\PHP\SodiumCompat\lib\SodiumException;
use TypeError;
use InvalidArgumentException;
if(ABSPATH){
    abstract class ParagonIE_32_Poly1305 extends ParagonIE_32_Util {
        public const BLOCK_SIZE = 16;

        /**
         * @internal You should not use this directly from another application
         *
         * @param string $m
         * @param string $key
         * @return string
         * @throws SodiumException
         * @throws TypeError
         */
        public static function onetimeauth($m, $key):string
        {
            if (self::strlen($key) < 32) {
                throw new InvalidArgumentException(
                    'Key must be 32 bytes long.'
                );
            }
            $state = new ParagonIE_32_Poly1305_State(
                self::substr($key, 0, 32)
            );
            return $state
                ->update($m)
                ->finish();
        }

        /**
         * @internal You should not use this directly from another application
         *
         * @param string $mac
         * @param string $m
         * @param string $key
         * @return bool
         * @throws SodiumException
         * @throws TypeError
         */
        public static function onetimeauth_verify($mac, $m, $key):bool
        {
            if (self::strlen($key) < 32) {
                throw new InvalidArgumentException(
                    'Key must be 32 bytes long.'
                );
            }
            $state = new ParagonIE_32_Poly1305_State(
                self::substr($key, 0, 32)
            );
            $calc = $state
                ->update($m)
                ->finish();
            return self::verify_16($calc, $mac);
        }
    }
}else{die;}