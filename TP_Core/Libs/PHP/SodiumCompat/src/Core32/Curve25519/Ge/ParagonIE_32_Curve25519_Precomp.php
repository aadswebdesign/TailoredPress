<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-11-2022
 * Time: 15:30
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core32\Curve25519\Ge;
use TP_Core\Libs\PHP\SodiumCompat\src\Core32\Curve25519\ParagonIE_32_Curve25519_Fe;
use TP_Core\Libs\PHP\SodiumCompat\src\Core32\ParagonIE_32_Curve25519;
use TP_Core\Libs\PHP\SodiumCompat\lib\SodiumException;
use TypeError;
if(ABSPATH){
    class ParagonIE_32_Curve25519_Precomp{
        /**
         * @var ParagonIE_32_Curve25519_Fe
         */
        public $yplusx;

        /**
         * @var ParagonIE_32_Curve25519_Fe
         */
        public $yminusx;

        /**
         * @var ParagonIE_32_Curve25519_Fe
         */
        public $xy2d;

        /**
         * ParagonIE_Sodium_Core32_Curve25519_Ge_Precomp constructor.
         *
         * @internal You should not use this directly from another application
         *
         * @param ParagonIE_32_Curve25519_Fe $yplusx
         * @param ParagonIE_32_Curve25519_Fe $yminusx
         * @param ParagonIE_32_Curve25519_Fe $xy2d
         * @throws SodiumException
         * @throws TypeError
         */
        public function __construct(
            ParagonIE_32_Curve25519_Fe $yplusx = null,
            ParagonIE_32_Curve25519_Fe $yminusx = null,
            ParagonIE_32_Curve25519_Fe $xy2d = null
        ) {
            if ($yplusx === null) {
                $yplusx = ParagonIE_32_Curve25519::fe_0();
            }
            $this->yplusx = $yplusx;
            if ($yminusx === null) {
                $yminusx = ParagonIE_32_Curve25519::fe_0();
            }
            $this->yminusx = $yminusx;
            if ($xy2d === null) {
                $xy2d = ParagonIE_32_Curve25519::fe_0();
            }
            $this->xy2d = $xy2d;
        }
    }
}else{die;}