<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-11-2022
 * Time: 11:20
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core\Curve25519\Ge;
use TP_Core\Libs\PHP\SodiumCompat\src\Core\Curve25519\ParagonIE_Curve25519_Fe;
if(ABSPATH){
    class ParagonIE_Curve25519_Cached{
        /**
         * @var ParagonIE_Curve25519_Fe
         */
        public $YplusX;

        /**
         * @var ParagonIE_Curve25519_Fe
         */
        public $YminusX;

        /**
         * @var ParagonIE_Curve25519_Fe
         */
        public $Z;

        /**
         * @var ParagonIE_Curve25519_Fe
         */
        public $T2d;

        /**
         * ParagonIE_Sodium_Core_Curve25519_Ge_Cached constructor.
         *
         * @internal You should not use this directly from another application
         *
         * @param ParagonIE_Curve25519_Fe|null $YplusX
         * @param ParagonIE_Curve25519_Fe|null $YminusX
         * @param ParagonIE_Curve25519_Fe|null $Z
         * @param ParagonIE_Curve25519_Fe|null $T2d
         */
        public function __construct(
            ParagonIE_Curve25519_Fe $YplusX = null,
            ParagonIE_Curve25519_Fe $YminusX = null,
            ParagonIE_Curve25519_Fe $Z = null,
            ParagonIE_Curve25519_Fe $T2d = null
        ) {
            if ($YplusX === null) {
                $YplusX = new ParagonIE_Curve25519_Fe();
            }
            $this->YplusX = $YplusX;
            if ($YminusX === null) {
                $YminusX = new ParagonIE_Curve25519_Fe();
            }
            $this->YminusX = $YminusX;
            if ($Z === null) {
                $Z = new ParagonIE_Curve25519_Fe();
            }
            $this->Z = $Z;
            if ($T2d === null) {
                $T2d = new ParagonIE_Curve25519_Fe();
            }
            $this->T2d = $T2d;
        }
    }
}else{die;}