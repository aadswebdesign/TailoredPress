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
    class ParagonIE_Curve25519_P2{
        /**
         * @var ParagonIE_Curve25519_Fe
         */
        public $X;

        /**
         * @var ParagonIE_Curve25519_Fe
         */
        public $Y;

        /**
         * @var ParagonIE_Curve25519_Fe
         */
        public $Z;

        /**
         * ParagonIE_Sodium_Core_Curve25519_Ge_P2 constructor.
         *
         * @internal You should not use this directly from another application
         *
         * @param ParagonIE_Curve25519_Fe|null $x
         * @param ParagonIE_Curve25519_Fe|null $y
         * @param ParagonIE_Curve25519_Fe|null $z
         */
        public function __construct(
            ParagonIE_Curve25519_Fe $x = null,
            ParagonIE_Curve25519_Fe $y = null,
            ParagonIE_Curve25519_Fe $z = null
        ) {
            if ($x === null) {
                $x = new ParagonIE_Curve25519_Fe();
            }
            $this->X = $x;
            if ($y === null) {
                $y = new ParagonIE_Curve25519_Fe();
            }
            $this->Y = $y;
            if ($z === null) {
                $z = new ParagonIE_Curve25519_Fe();
            }
            $this->Z = $z;
        }
    }
}else{die;}