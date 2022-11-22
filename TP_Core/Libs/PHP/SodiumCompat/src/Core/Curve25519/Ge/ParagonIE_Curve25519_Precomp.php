<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-11-2022
 * Time: 11:35
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core\Curve25519\Ge;
use TP_Core\Libs\PHP\SodiumCompat\src\Core\Curve25519\ParagonIE_Curve25519_Fe;
if(ABSPATH){
    class ParagonIE_Curve25519_Precomp{
        /**
         * @var ParagonIE_Curve25519_Fe
         */
        public $yplusx;

        /**
         * @var ParagonIE_Curve25519_Fe
         */
        public $yminusx;

        /**
         * @var ParagonIE_Curve25519_Fe
         */
        public $xy2d;

        /**
         * ParagonIE_Sodium_Core_Curve25519_Ge_Precomp constructor.
         *
         * @internal You should not use this directly from another application
         *
         * @param ParagonIE_Curve25519_Fe $yplusx
         * @param ParagonIE_Curve25519_Fe $yminusx
         * @param ParagonIE_Curve25519_Fe $xy2d
         */
        public function __construct(
            ParagonIE_Curve25519_Fe $yplusx = null,
            ParagonIE_Curve25519_Fe $yminusx = null,
            ParagonIE_Curve25519_Fe $xy2d = null
        ) {
            if ($yplusx === null) {
                $yplusx = new ParagonIE_Curve25519_Fe();
            }
            $this->yplusx = $yplusx;
            if ($yminusx === null) {
                $yminusx = new ParagonIE_Curve25519_Fe();
            }
            $this->yminusx = $yminusx;
            if ($xy2d === null) {
                $xy2d = new ParagonIE_Curve25519_Fe();
            }
            $this->xy2d = $xy2d;
        }
    }
}else{die;}