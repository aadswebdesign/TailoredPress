<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-11-2022
 * Time: 11:47
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core;
use TP_Core\Libs\PHP\SodiumCompat\src\Core\Poly1305\ParagonIE_Poly1305_State;
use TP_Core\Libs\PHP\SodiumCompat\lib\SodiumException;
if(ABSPATH){
    abstract class ParagonIE_Poly1305 extends ParagonIE_Util{
        public const BLOCK_SIZE = 16;

        /**
         * @internal You should not use this directly from another application
         *
         * @param string $m
         * @param string $key
         * @return string
         * @throws SodiumException
         * @throws \TypeError
         */
        public static function onetimeauth($m, $key):string
        {
            if (self::strlen($key) < 32) {
                throw new \InvalidArgumentException(
                    'Key must be 32 bytes long.'
                );
            }
            $state = new ParagonIE_Poly1305_State(
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
         * @throws \TypeError
         */
        public static function onetimeauth_verify($mac, $m, $key):bool
        {
            if (self::strlen($key) < 32) {
                throw new \InvalidArgumentException(
                    'Key must be 32 bytes long.'
                );
            }
            $state = new ParagonIE_Poly1305_State(
                self::substr($key, 0, 32)
            );
            $calc = $state
                ->update($m)
                ->finish();
            return self::verify_16($calc, $mac);
        }
    }
}else{die;}