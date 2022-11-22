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
    class ParagonIE_32_Curve25519_P2{
        /**
         * @var ParagonIE_32_Curve25519_Fe
         */
        public $X;

        /**
         * @var ParagonIE_32_Curve25519_Fe
         */
        public $Y;

        /**
         * @var ParagonIE_32_Curve25519_Fe
         */
        public $Z;

        /**
         * ParagonIE_Sodium_Core32_Curve25519_Ge_P2 constructor.
         *
         * @internal You should not use this directly from another application
         *
         * @param ParagonIE_32_Curve25519_Fe|null $x
         * @param ParagonIE_32_Curve25519_Fe|null $y
         * @param ParagonIE_32_Curve25519_Fe|null $z
         */
        public function __construct(
            ParagonIE_32_Curve25519_Fe $x = null,
            ParagonIE_32_Curve25519_Fe $y = null,
            ParagonIE_32_Curve25519_Fe $z = null
        ) {
            if ($x === null) {
                $x = new ParagonIE_32_Curve25519_Fe();
            }
            $this->X = $x;
            if ($y === null) {
                $y = new ParagonIE_32_Curve25519_Fe();
            }
            $this->Y = $y;
            if ($z === null) {
                $z = new ParagonIE_32_Curve25519_Fe();
            }
            $this->Z = $z;
        }
    }
}else{die;}

