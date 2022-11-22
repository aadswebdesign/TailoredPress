<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-11-2022
 * Time: 22:41
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core32;
use TP_Core\Libs\PHP\SodiumCompat\lib\SodiumException;
use TypeError;
if(ABSPATH){
    class ParagonIE_32_HChaCha20 extends ParagonIE_32_ChaCha20{
        /**
         * @param string $in
         * @param string $key
         * @param string|null $c
         * @return string
         * @throws SodiumException
         * @throws TypeError
         */
        public static function hChaCha20($in = '', $key = '', $c = null):string
        {
            $ctx = array();
            if ($c === null) {
                $ctx[0] = new ParagonIE_32_Int32(array(0x6170, 0x7865));
                $ctx[1] = new ParagonIE_32_Int32(array(0x3320, 0x646e));
                $ctx[2] = new ParagonIE_32_Int32(array(0x7962, 0x2d32));
                $ctx[3] = new ParagonIE_32_Int32(array(0x6b20, 0x6574));
            } else {
                $ctx[0] = ParagonIE_32_Int32::fromReverseString(self::substr($c, 0, 4));
                $ctx[1] = ParagonIE_32_Int32::fromReverseString(self::substr($c, 4, 4));
                $ctx[2] = ParagonIE_32_Int32::fromReverseString(self::substr($c, 8, 4));
                $ctx[3] = ParagonIE_32_Int32::fromReverseString(self::substr($c, 12, 4));
            }
            $ctx[4]  = ParagonIE_32_Int32::fromReverseString(self::substr($key, 0, 4));
            $ctx[5]  = ParagonIE_32_Int32::fromReverseString(self::substr($key, 4, 4));
            $ctx[6]  = ParagonIE_32_Int32::fromReverseString(self::substr($key, 8, 4));
            $ctx[7]  = ParagonIE_32_Int32::fromReverseString(self::substr($key, 12, 4));
            $ctx[8]  = ParagonIE_32_Int32::fromReverseString(self::substr($key, 16, 4));
            $ctx[9]  = ParagonIE_32_Int32::fromReverseString(self::substr($key, 20, 4));
            $ctx[10] = ParagonIE_32_Int32::fromReverseString(self::substr($key, 24, 4));
            $ctx[11] = ParagonIE_32_Int32::fromReverseString(self::substr($key, 28, 4));
            $ctx[12] = ParagonIE_32_Int32::fromReverseString(self::substr($in, 0, 4));
            $ctx[13] = ParagonIE_32_Int32::fromReverseString(self::substr($in, 4, 4));
            $ctx[14] = ParagonIE_32_Int32::fromReverseString(self::substr($in, 8, 4));
            $ctx[15] = ParagonIE_32_Int32::fromReverseString(self::substr($in, 12, 4));

            return self::hChaCha20Bytes($ctx);
        }

        /**
         * @param array $ctx
         * @return string
         * @throws SodiumException
         * @throws TypeError
         */
        protected static function hChaCha20Bytes(array $ctx):string
        {
            /** @var ParagonIE_32_Int32 $x0 */
            /** @var ParagonIE_32_Int32 $x1 */
            /** @var ParagonIE_32_Int32 $x2 */
            /** @var ParagonIE_32_Int32 $x3 */
            /** @var ParagonIE_32_Int32 $x4 */
            /** @var ParagonIE_32_Int32 $x5 */
            /** @var ParagonIE_32_Int32 $x6 */
            /** @var ParagonIE_32_Int32 $x7 */
            /** @var ParagonIE_32_Int32 $x8 */
            /** @var ParagonIE_32_Int32 $x9 */
            /** @var ParagonIE_32_Int32 $x10 */
            /** @var ParagonIE_32_Int32 $x11 */
            /** @var ParagonIE_32_Int32 $x12 */
            /** @var ParagonIE_32_Int32 $x13 */
            /** @var ParagonIE_32_Int32 $x14 */
            /** @var ParagonIE_32_Int32 $x15 */

            @list($x0,$x1,$x2,$x3,$x4,$x5,$x6,$x7,$x8,$x9,$x10,$x11,$x12,$x13,$x14,$x15) =  $ctx;
            for ($i = 0; $i < 10; ++$i) {
                # QUARTERROUND( x0,  x4,  x8,  x12)
                @list($x0, $x4, $x8, $x12) = self::quarterRound($x0, $x4, $x8, $x12);

                # QUARTERROUND( x1,  x5,  x9,  x13)
                @list($x1, $x5, $x9, $x13) = self::quarterRound($x1, $x5, $x9, $x13);

                # QUARTERROUND( x2,  x6,  x10,  x14)
                @list($x2, $x6, $x10, $x14) = self::quarterRound($x2, $x6, $x10, $x14);

                # QUARTERROUND( x3,  x7,  x11,  x15)
                @list($x3, $x7, $x11, $x15) = self::quarterRound($x3, $x7, $x11, $x15);

                # QUARTERROUND( x0,  x5,  x10,  x15)
                @list($x0, $x5, $x10, $x15) = self::quarterRound($x0, $x5, $x10, $x15);

                # QUARTERROUND( x1,  x6,  x11,  x12)
                @list($x1, $x6, $x11, $x12) = self::quarterRound($x1, $x6, $x11, $x12);

                # QUARTERROUND( x2,  x7,  x8,  x13)
                @list($x2, $x7, $x8, $x13) = self::quarterRound($x2, $x7, $x8, $x13);

                # QUARTERROUND( x3,  x4,  x9,  x14)
                @list($x3, $x4, $x9, $x14) = self::quarterRound($x3, $x4, $x9, $x14);
            }

            return $x0->toReverseString() .
            $x1->toReverseString() .
            $x2->toReverseString() .
            $x3->toReverseString() .
            $x12->toReverseString() .
            $x13->toReverseString() .
            $x14->toReverseString() .
            $x15->toReverseString();
        }
    }
}else{die;}