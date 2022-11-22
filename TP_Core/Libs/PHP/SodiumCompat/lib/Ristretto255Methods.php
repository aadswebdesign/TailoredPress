<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-11-2022
 * Time: 18:18
 */
namespace TP_Core\Libs\PHP\SodiumCompat\lib;
use TP_Core\Libs\PHP\SodiumCompat\src\ParagonIE_Compat;
if(ABSPATH){
    trait Ristretto255Methods{
        protected function _sd_ristretto_construct():string{
            if (!defined('SODIUM_CRYPTO_CORE_RISTRETTO255_BYTES')) {
                define(
                    'SODIUM_CRYPTO_CORE_RISTRETTO255_BYTES',
                    ParagonIE_Compat::CRYPTO_CORE_RISTRETTO255_BYTES
                );
                define('SODIUM_COMPAT_POLYFILLED_RISTRETTO255', true);
            }
            if (!defined('SODIUM_CRYPTO_CORE_RISTRETTO255_HASHBYTES')) {
                define(
                    'SODIUM_CRYPTO_CORE_RISTRETTO255_HASHBYTES',
                    ParagonIE_Compat::CRYPTO_CORE_RISTRETTO255_HASHBYTES
                );
            }
            if (!defined('SODIUM_CRYPTO_CORE_RISTRETTO255_SCALARBYTES')) {
                define(
                    'SODIUM_CRYPTO_CORE_RISTRETTO255_SCALARBYTES',
                    ParagonIE_Compat::CRYPTO_CORE_RISTRETTO255_SCALARBYTES
                );
            }
            if (!defined('SODIUM_CRYPTO_CORE_RISTRETTO255_NONREDUCEDSCALARBYTES')) {
                define(
                    'SODIUM_CRYPTO_CORE_RISTRETTO255_NONREDUCEDSCALARBYTES',
                    ParagonIE_Compat::CRYPTO_CORE_RISTRETTO255_NONREDUCEDSCALARBYTES
                );
            }
            if (!defined('SODIUM_CRYPTO_SCALARMULT_RISTRETTO255_SCALARBYTES')) {
                define(
                    'SODIUM_CRYPTO_SCALARMULT_RISTRETTO255_SCALARBYTES',
                    ParagonIE_Compat::CRYPTO_SCALARMULT_RISTRETTO255_SCALARBYTES
                );
            }
            if (!defined('SODIUM_CRYPTO_SCALARMULT_RISTRETTO255_BYTES')) {
                define(
                    'SODIUM_CRYPTO_SCALARMULT_RISTRETTO255_BYTES',
                    ParagonIE_Compat::CRYPTO_SCALARMULT_RISTRETTO255_BYTES
                );
            }



        }// ristretto255.php
        protected function _sd_crypto_core_ristretto255_add($p, $q):string{
            if (!is_callable('sodium_crypto_core_ristretto255_add')) {
                function sodium_crypto_core_ristretto255_add($p, $q){
                    return ParagonIE_Compat::ristretto255_add($p, $q, true);
                }
            }
            return sodium_crypto_core_ristretto255_add($p, $q);
        }//50 ristretto255.php
        protected function _sd_crypto_core_ristretto255_from_hash($r):string{
            if (!is_callable('sodium_crypto_core_ristretto255_from_hash')) {
                function sodium_crypto_core_ristretto255_from_hash($r){
                    return ParagonIE_Compat::ristretto255_from_hash($r, true);
                }
            }
            return sodium_crypto_core_ristretto255_from_hash($r);
        }//63 ristretto255.php
        protected function _sd_crypto_core_ristretto255_is_valid_point($p):string{
            if (!is_callable('sodium_crypto_core_ristretto255_is_valid_point')) {
                function sodium_crypto_core_ristretto255_is_valid_point($p){
                    return ParagonIE_Compat::ristretto255_is_valid_point($p, true);
                }
            }
            return sodium_crypto_core_ristretto255_is_valid_point($p);
        }//76 ristretto255.php
        protected function _sd_crypto_core_ristretto255_random():string{
            if (!is_callable('sodium_crypto_core_ristretto255_random')) {
                function sodium_crypto_core_ristretto255_random(){
                    return ParagonIE_Compat::ristretto255_random(true);
                }
            }
            return sodium_crypto_core_ristretto255_random();
        }//88 ristretto255.php
        protected function _sd_crypto_core_ristretto255_scalar_add($p, $q):string{
            if (!is_callable('sodium_crypto_core_ristretto255_scalar_add')) {
                function sodium_crypto_core_ristretto255_scalar_add($p, $q){
                    return ParagonIE_Compat::ristretto255_scalar_add($p, $q, true);
                }
            }
            return sodium_crypto_core_ristretto255_scalar_add($p, $q);
        }//102 ristretto255.php
        protected function _sd_crypto_core_ristretto255_scalar_complement($p):string{
            if (!is_callable('sodium_crypto_core_ristretto255_scalar_complement')) {
                function sodium_crypto_core_ristretto255_scalar_complement($p){
                    return ParagonIE_Compat::ristretto255_scalar_complement($p, true);
                }
            }
            return sodium_crypto_core_ristretto255_scalar_complement($p);
        }//115 ristretto255.php
        protected function _sd_crypto_core_ristretto255_scalar_invert($p):string{
            if (!is_callable('sodium_crypto_core_ristretto255_scalar_invert')) {
                function sodium_crypto_core_ristretto255_scalar_invert($p){
                    return ParagonIE_Compat::ristretto255_scalar_invert($p, true);
                }
            }
            return sodium_crypto_core_ristretto255_scalar_invert($p);
        }//128 ristretto255.php
        protected function _sd_crypto_core_ristretto255_scalar_mul($p,$q):string{//todo
            if (!is_callable('sodium_crypto_core_ristretto255_scalar_negate')) {
                function sodium_crypto_core_ristretto255_scalar_negate($p,$q){
                    return ParagonIE_Compat::ristretto255_scalar_negate($p,$q);
                }
            }
            return sodium_crypto_core_ristretto255_scalar_negate($p,$q);
        }//142 ristretto255.php
        protected function _sd_crypto_core_ristretto255_scalar_negate($p):string{
            if (!is_callable('sodium_crypto_core_ristretto255_scalar_negate')) {
                function sodium_crypto_core_ristretto255_scalar_negate($p){
                    return ParagonIE_Compat::ristretto255_scalar_negate($p, true);
                }
            }
            return sodium_crypto_core_ristretto255_scalar_negate($p);
        }//155 ristretto255.php
        protected function _sd_crypto_core_ristretto255_scalar_random():string{
            if (!is_callable('sodium_crypto_core_ristretto255_scalar_random')) {
                function sodium_crypto_core_ristretto255_scalar_random(){
                    return ParagonIE_Compat::ristretto255_scalar_random(true);
                }
            }
            return sodium_crypto_core_ristretto255_scalar_random();
        }//167 ristretto255.php
        protected function _sd_crypto_core_ristretto255_scalar_reduce($p):string{
            if (!is_callable('sodium_crypto_core_ristretto255_scalar_reduce')) {
                function sodium_crypto_core_ristretto255_scalar_reduce($p){
                    return ParagonIE_Compat::ristretto255_scalar_reduce($p, true);
                }
            }
            return sodium_crypto_core_ristretto255_scalar_reduce($p);
        }//180 ristretto255.php
        protected function _sd_crypto_core_ristretto255_scalar_sub($p, $q):string{
            if (!is_callable('sodium_crypto_core_ristretto255_scalar_sub')) {
                function sodium_crypto_core_ristretto255_scalar_sub($p, $q){
                    return ParagonIE_Compat::ristretto255_scalar_sub($p, $q, true);
                }
            }
            return sodium_crypto_core_ristretto255_scalar_sub($p, $q);
        }//194 ristretto255.php
        protected function _sd_crypto_core_ristretto255_sub($p, $q):string{
            if (!is_callable('sodium_crypto_core_ristretto255_sub')) {
                function sodium_crypto_core_ristretto255_sub($p, $q) {
                    return ParagonIE_Compat::ristretto255_sub($p, $q, true);
                }
            }
            return sodium_crypto_core_ristretto255_sub($p, $q);
        }//208 ristretto255.php
        protected function _sd_crypto_scalarmult_ristretto255($n, $p):string{
            if (!is_callable('sodium_crypto_scalarmult_ristretto255')) {
                function sodium_crypto_scalarmult_ristretto255($n, $p){
                    return ParagonIE_Compat::scalarmult_ristretto255($n, $p, true);
                }
            }
            return sodium_crypto_scalarmult_ristretto255($n, $p);
        }//222 ristretto255.php
        protected function _sd_crypto_scalarmult_ristretto255_base($n):string{
            if (!is_callable('sodium_crypto_scalarmult_ristretto255_base')) {
                function sodium_crypto_scalarmult_ristretto255_base($n){
                    return ParagonIE_Compat::scalarmult_ristretto255_base($n, true);
                }
            }
            return sodium_crypto_scalarmult_ristretto255_base($n);
        }//235 ristretto255.php
    }
}else{die;}