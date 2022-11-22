<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-11-2022
 * Time: 15:30
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core32\Curve25519\Ge;
use TP_Core\Libs\PHP\SodiumCompat\src\Core32\Curve25519\ParagonIE_32_Curve25519_Fe;
if(ABSPATH){
    class ParagonIE_32_Curve25519_Cached{
        /**
         * @var ParagonIE_32_Curve25519_Fe
         */
        public $YplusX;

        /**
         * @var ParagonIE_32_Curve25519_Fe
         */
        public $YminusX;

        /**
         * @var ParagonIE_32_Curve25519_Fe
         */
        public $Z;

        /**
         * @var ParagonIE_32_Curve25519_Fe
         */
        public $T2d;

        /**
         * ParagonIE_Sodium_Core32_Curve25519_Ge_Cached constructor.
         *
         * @internal You should not use this directly from another application
         *
         * @param ParagonIE_32_Curve25519_Fe|null $YplusX
         * @param ParagonIE_32_Curve25519_Fe|null $YminusX
         * @param ParagonIE_32_Curve25519_Fe|null $Z
         * @param ParagonIE_32_Curve25519_Fe|null $T2d
         */
        public function __construct(
            ParagonIE_32_Curve25519_Fe $YplusX = null,
            ParagonIE_32_Curve25519_Fe $YminusX = null,
            ParagonIE_32_Curve25519_Fe $Z = null,
            ParagonIE_32_Curve25519_Fe $T2d = null
        ) {
            if ($YplusX === null) {
                $YplusX = new ParagonIE_32_Curve25519_Fe();
            }
            $this->YplusX = $YplusX;
            if ($YminusX === null) {
                $YminusX = new ParagonIE_32_Curve25519_Fe();
            }
            $this->YminusX = $YminusX;
            if ($Z === null) {
                $Z = new ParagonIE_32_Curve25519_Fe();
            }
            $this->Z = $Z;
            if ($T2d === null) {
                $T2d = new ParagonIE_32_Curve25519_Fe();
            }
            $this->T2d = $T2d;
        }
    }
}else{die;}

