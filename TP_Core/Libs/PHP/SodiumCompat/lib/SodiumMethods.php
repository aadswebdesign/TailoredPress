<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-11-2022
 * Time: 19:05
 */
namespace TP_Core\Libs\PHP\SodiumCompat\lib;
use TP_Core\Libs\PHP\SodiumCompat\src\ParagonIE_Compat;
if(ABSPATH){
    trait SodiumMethods{
        protected function _sodium_increment(string &$string): void {}//970 sodium.php
        protected function _bin2hex($string):int{
            if (!is_callable('\\Sodium\\bin2hex')) {
                function bin2hex($string){
                    return ParagonIE_Compat::bin2hex($string);
                }
            }
            return bin2hex($string);
        }//26 from sodium-compat
        protected function _compare($a, $b):int{
            if (!is_callable('\\Sodium\\compare')) {
                function compare($a, $b){
                    return ParagonIE_Compat::compare($a, $b);
                }
            }
            return compare($a, $b);
        }//37 from sodium-compat
        protected function _crypto_aead_aes256gcm_decrypt($message, $assocData, $nonce, $key){
            if (!is_callable('\\Sodium\\crypto_aead_aes256gcm_decrypt')) {
                function crypto_aead_aes256gcm_decrypt($message, $assocData, $nonce, $key){
                    try { return ParagonIE_Compat::crypto_aead_aes256gcm_decrypt($message, $assocData, $nonce, $key);
                    } catch (\TypeError $ex) { return false;} catch (SodiumException $ex) { return false;}
                }
            }
            return crypto_aead_aes256gcm_decrypt($message, $assocData, $nonce, $key);
        }//52 from sodium-compat
        protected function _crypto_aead_aes256gcm_encrypt($message, $assocData, $nonce, $key):string{
            if (!is_callable('\\Sodium\\crypto_aead_aes256gcm_encrypt')) {
                function crypto_aead_aes256gcm_encrypt($message, $assocData, $nonce, $key){
                    return ParagonIE_Compat::crypto_aead_aes256gcm_encrypt($message, $assocData, $nonce, $key);
                }
            }
            return crypto_aead_aes256gcm_encrypt($message, $assocData, $nonce, $key);
        }//73 from sodium-compat
        protected function _crypto_aead_aes256gcm_is_available():bool{
            if (!is_callable('\\Sodium\\crypto_aead_aes256gcm_is_available')) {
                function crypto_aead_aes256gcm_is_available(){
                    return ParagonIE_Compat::crypto_aead_aes256gcm_is_available();
                }
            }
            return crypto_aead_aes256gcm_is_available();
        }//83 from sodium-compat
        protected function _crypto_aead_chacha20poly1305_decrypt($message, $assocData, $nonce, $key){
            if (!is_callable('\\Sodium\\crypto_aead_chacha20poly1305_decrypt')) {
                function crypto_aead_chacha20poly1305_decrypt($message, $assocData, $nonce, $key){
                    try {  return ParagonIE_Compat::crypto_aead_chacha20poly1305_decrypt($message, $assocData, $nonce, $key);
                    } catch (\TypeError $ex) { return false;} catch (SodiumException $ex) { return false;}
                }
            }
            return crypto_aead_chacha20poly1305_decrypt($message, $assocData, $nonce, $key);
        }//97 from sodium-compat
        protected function _crypto_aead_chacha20poly1305_encrypt($message, $assocData, $nonce, $key):string{
            if (!is_callable('\\Sodium\\crypto_aead_chacha20poly1305_encrypt')) {
                function crypto_aead_chacha20poly1305_encrypt($message, $assocData, $nonce, $key){
                    return ParagonIE_Compat::crypto_aead_chacha20poly1305_encrypt($message, $assocData, $nonce, $key);
                }
            }
            return crypto_aead_chacha20poly1305_encrypt($message, $assocData, $nonce, $key);
        }//119 from sodium-compat
        protected function _crypto_aead_chacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key){
            if (!is_callable('\\Sodium\\crypto_aead_chacha20poly1305_ietf_decrypt')) {
                function crypto_aead_chacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key){
                    try { return ParagonIE_Compat::crypto_aead_chacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key);
                    } catch (\TypeError $ex) { return false;} catch (SodiumException $ex) { return false;}
                }
            }
            return crypto_aead_chacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key);
        }//133 from sodium-compat
        protected function _crypto_aead_chacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key):string{
            if (!is_callable('\\Sodium\\crypto_aead_chacha20poly1305_ietf_encrypt')) {
                function crypto_aead_chacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key){
                    return ParagonIE_Compat::crypto_aead_chacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key);
                }
            }
            return crypto_aead_chacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key);
        }//155 from sodium-compat
        protected function _crypto_auth($message, $key):string{
            if (!is_callable('\\Sodium\\crypto_auth')) {
                function crypto_auth($message, $key){
                    return ParagonIE_Compat::crypto_auth($message, $key);
                }
            }
            return crypto_auth($message, $key);
        }//169 compat.php
        protected function _crypto_auth_verify($mac, $message, $key):string{
            if (!is_callable('\\Sodium\\crypto_auth_verify')) {
                function crypto_auth_verify($mac, $message, $key){
                    return ParagonIE_Compat::crypto_auth_verify($mac, $message, $key);
                }
            }
            return crypto_auth_verify($mac, $message, $key);
        }//184 from sodium-compat
        protected function _crypto_box($message, $nonce, $kp):string{
            if (!is_callable('\\Sodium\\crypto_box')) {
                function crypto_box($message, $nonce, $kp){
                    return ParagonIE_Compat::crypto_box($message, $nonce, $kp);
                }
            }
            return crypto_box($message, $nonce, $kp);
        }//199 from sodium-compat
        protected function _crypto_box_keypair():string{
            if (!is_callable('\\Sodium\\crypto_box_keypair')) {
                function crypto_box_keypair(){
                    return ParagonIE_Compat::crypto_box_keypair();
                }
            }
            return crypto_box_keypair();
        }//211 from sodium-compat
        protected function _crypto_box_keypair_from_secretkey_and_publickey($sk, $pk):string{
            if (!is_callable('\\Sodium\\crypto_box_keypair_from_secretkey_and_publickey')) {
                function crypto_box_keypair_from_secretkey_and_publickey($sk, $pk){
                    return ParagonIE_Compat::crypto_box_keypair_from_secretkey_and_publickey($sk, $pk);
                }
            }
            return crypto_box_keypair_from_secretkey_and_publickey($sk, $pk);
        }//225 from sodium-compat
        protected function _crypto_box_open($message, $nonce, $kp){
            if (!is_callable('\\Sodium\\crypto_box_open')) {
                function crypto_box_open($message, $nonce, $kp){
                    try {
                        return ParagonIE_Compat::crypto_box_open($message, $nonce, $kp);
                    } catch (\TypeError $ex) {
                        return false;
                    } catch (SodiumException $ex) {
                        return false;
                    }
                }
            }
            return crypto_box_open($message, $nonce, $kp);
        }//238 from sodium-compat
        protected function _crypto_box_publickey($keypair):string{
            if (!is_callable('\\Sodium\\crypto_box_publickey')) {
                function crypto_box_publickey($keypair){
                    return ParagonIE_Compat::crypto_box_publickey($keypair);
                }
            }
            return crypto_box_publickey($keypair);
        }//257 from sodium-compat
        protected function _crypto_box_publickey_from_secretkey($sk):string{
            if (!is_callable('\\Sodium\\crypto_box_publickey_from_secretkey')) {
                function crypto_box_publickey_from_secretkey($sk){
                    return ParagonIE_Compat::crypto_box_publickey_from_secretkey($sk);
                }
            }
            return crypto_box_publickey_from_secretkey($sk);
        }//270 from sodium-compat
        protected function _crypto_box_seal($message, $publicKey):string{
            if (!is_callable('\\Sodium\\crypto_box_seal')) {
                function crypto_box_seal($message, $publicKey) {
                    return ParagonIE_Compat::crypto_box_seal($message, $publicKey);
                }
            }
            return crypto_box_seal($message, $publicKey);
        }//284 from sodium-compat
        protected function _crypto_box_seal_open($message, $kp){
            if (!is_callable('\\Sodium\\crypto_box_seal_open')) {
                function crypto_box_seal_open($message, $kp){
                    try { return ParagonIE_Compat::crypto_box_seal_open($message, $kp);
                    } catch (\TypeError $ex) { return false;} catch (SodiumException $ex) { return false;}
                }
            }
            return crypto_box_seal_open($message, $kp);
        }//296 from sodium-compat
        protected function _crypto_box_secretkey($keypair):string{
            if (!is_callable('\\Sodium\\crypto_box_secretkey')) {
                function crypto_box_secretkey($keypair){
                    return ParagonIE_Compat::crypto_box_secretkey($keypair);
                }
            }
            return crypto_box_secretkey($keypair);
        }//315 from sodium-compat
        protected function _crypto_generichash($message, $key = null, $outLen = 32):string{
            if (!is_callable('\\Sodium\\crypto_generichash')) {
                function crypto_generichash($message, $key, $outLen){
                    return ParagonIE_Compat::crypto_generichash($message, $key, $outLen);
                }
            }
            return crypto_generichash($message, $key, $outLen);
        }//330 from sodium-compat
        protected function _crypto_generichash_final(&$ctx, $outputLength = 32):string{
            if (!is_callable('\\Sodium\\crypto_generichash_final')) {
                function crypto_generichash_final($ctx, $outputLength){
                    return ParagonIE_Compat::crypto_generichash_final($ctx, $outputLength);
                }
            }
            return crypto_generichash_final($ctx, $outputLength);
        }//344 from sodium-compat
        protected function _crypto_generichash_init($key = null, $outLen = 32):string{
            if (!is_callable('\\Sodium\\crypto_generichash_init')) {
                function crypto_generichash_init($key = null, $outLen = 32){
                    return ParagonIE_Compat::crypto_generichash_init($key, $outLen);
                }
            }
            return ParagonIE_Compat::crypto_generichash_init($key, $outLen);
        }//358 from sodium-compat
        protected function _crypto_generichash_update(&$ctx, $message = ''):void{
            if (!is_callable('\\Sodium\\crypto_generichash_update')) {
                function crypto_generichash_update($ctx, $message){
                    ParagonIE_Compat::crypto_generichash_update($ctx, $message);
                }
            }
            crypto_generichash_update($ctx, $message);
        }//372 from sodium-compat
        protected function _crypto_kx($my_secret, $their_public, $client_public, $server_public):string{
            if (!is_callable('\\Sodium\\crypto_kx')) {
                function crypto_kx($my_secret, $their_public, $client_public, $server_public){
                    ParagonIE_Compat::crypto_kx($my_secret, $their_public,$client_public,$server_public,true);
                }
            }
            return crypto_kx($my_secret, $their_public, $client_public, $server_public);
        }//388 from sodium-compat
        protected function _crypto_pwhash($outlen, $passwd, $salt, $opslimit, $memlimit):string{
            if (!is_callable('\\Sodium\\crypto_pwhash')) {
                function crypto_pwhash($outlen, $passwd, $salt, $opslimit, $memlimit){
                    return ParagonIE_Compat::crypto_pwhash($outlen, $passwd, $salt, $opslimit, $memlimit);
                }
            }
            return crypto_pwhash($outlen, $passwd, $salt, $opslimit, $memlimit);
        }//411 from sodium-compat
        protected function _crypto_pwhash_str($passwd, $opslimit, $memlimit):string{
            if (!is_callable('\\Sodium\\crypto_pwhash_str')) {
                function crypto_pwhash_str($passwd, $opslimit, $memlimit){
                    return ParagonIE_Compat::crypto_pwhash_str($passwd, $opslimit, $memlimit);
                }
            }
            return crypto_pwhash_str($passwd, $opslimit, $memlimit);
        }//426 from sodium-compat
        protected function _crypto_pwhash_str_verify($passwd, $hash):string{
            if (!is_callable('\\Sodium\\crypto_pwhash_str_verify')) {
                function crypto_pwhash_str_verify($passwd, $hash){
                    return ParagonIE_Compat::crypto_pwhash_str_verify($passwd, $hash);
                }
            }
            return crypto_pwhash_str_verify($passwd, $hash);
        }//440 from sodium-compat
        protected function _crypto_pwhash_scryptsalsa208sha256($outlen, $passwd, $salt, $opslimit, $memlimit):string{
            if (!is_callable('\\Sodium\\crypto_pwhash_scryptsalsa208sha256')) {
                function crypto_pwhash_scryptsalsa208sha256($outlen, $passwd, $salt, $opslimit, $memlimit){
                    return ParagonIE_Compat::crypto_pwhash_scryptsalsa208sha256($outlen, $passwd, $salt, $opslimit, $memlimit);
                }
            }
            return crypto_pwhash_scryptsalsa208sha256($outlen, $passwd, $salt, $opslimit, $memlimit);
        }//457 from sodium-compat
        protected function _crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit):string{
            if (!is_callable('\\Sodium\\crypto_pwhash_scryptsalsa208sha256_str')) {
                function crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit){
                    return crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit);
                }
            }
            return ParagonIE_Compat::crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit);
        }//472 from sodium-compat
        protected function _crypto_pwhash_scryptsalsa208sha256_str_verify($passwd, $hash):string{
            if (!is_callable('\\Sodium\\crypto_pwhash_scryptsalsa208sha256_str_verify')) {
                function crypto_pwhash_scryptsalsa208sha256_str_verify($passwd, $hash){
                    return ParagonIE_Compat::crypto_pwhash_scryptsalsa208sha256_str_verify($passwd, $hash);
                }
            }
            return crypto_pwhash_scryptsalsa208sha256_str_verify($passwd, $hash);
        }//486 from sodium-compat
        protected function _crypto_scalarmult($n, $p):string{
            if (!is_callable('\\Sodium\\crypto_scalarmult')) {
                function crypto_scalarmult($n, $p){
                    return ParagonIE_Compat::crypto_scalarmult($n, $p);
                }
            }
            return crypto_scalarmult($n, $p);
        }//500 from sodium-compat
        protected function _crypto_scalarmult_base($n):string{
            if (!is_callable('\\Sodium\\crypto_scalarmult_base')) {
                function crypto_scalarmult_base($n){
                    return ParagonIE_Compat::crypto_scalarmult_base($n);
                }
            }
            return crypto_scalarmult_base($n);
        }//513 from sodium-compat
        protected function _crypto_secretbox($message, $nonce, $key):string{
            if (!is_callable('\\Sodium\\crypto_secretbox')) {
                function crypto_secretbox($message, $nonce, $key){
                    return ParagonIE_Compat::crypto_secretbox($message, $nonce, $key);
                }
            }
            return crypto_secretbox($message, $nonce, $key);
        }//528 from sodium-compat
        protected function _crypto_secretbox_open($message, $nonce, $key){
            if (!is_callable('\\Sodium\\crypto_secretbox_open')) {
                function crypto_secretbox_open($message, $nonce, $key){
                    try { return ParagonIE_Compat::crypto_secretbox_open($message, $nonce, $key);
                    } catch (\TypeError $ex) { return false;} catch (SodiumException $ex) { return false;}
                }
            }
            return crypto_secretbox_open($message, $nonce, $key);
        }//541 from sodium-compat
        protected function _crypto_shorthash($message, $key = ''):string{
            if (!is_callable('\\Sodium\\crypto_shorthash')) {
                function crypto_shorthash($message, $key){
                    return ParagonIE_Compat::crypto_shorthash($message, $key);
                }
            }
            return crypto_shorthash($message, $key);
        }//561 from sodium-compat
        protected function _crypto_sign($message, $sk):string{
            if (!is_callable('\\Sodium\\crypto_sign')) {
                function crypto_sign($message, $sk){
                    return ParagonIE_Compat::crypto_sign($message, $sk);
                }
            }
            return crypto_sign($message, $sk);
        }//575 from sodium-compat
        protected function _crypto_sign_detached($message, $sk):string{
            if (!is_callable('\\Sodium\\crypto_sign_detached')) {
                function crypto_sign_detached($message, $sk){
                    return ParagonIE_Compat::crypto_sign_detached($message, $sk);
                }
            }
            return crypto_sign_detached($message, $sk);
        }//589 from sodium-compat
        protected function _crypto_sign_keypair():string{
            if (!is_callable('\\Sodium\\crypto_sign_keypair')) {
                function crypto_sign_keypair(){
                    return ParagonIE_Compat::crypto_sign_keypair();
                }
            }
            return crypto_sign_keypair();
        }//601 from sodium-compat
        protected function _crypto_sign_open($signedMessage, $pk){
            if (!is_callable('\\Sodium\\crypto_sign_open')) {
                function crypto_sign_open($signedMessage, $pk){
                    try { return ParagonIE_Compat::crypto_sign_open($signedMessage, $pk);
                    } catch (\TypeError $ex) { return false; } catch (SodiumException $ex) { return false;}
                }
            }
            return crypto_sign_open($signedMessage, $pk);
        }//613 from sodium-compat
        protected function _crypto_sign_publickey($keypair):string{
            if (!is_callable('\\Sodium\\crypto_sign_publickey')) {
                function crypto_sign_publickey($keypair){
                    return ParagonIE_Compat::crypto_sign_publickey($keypair);
                }
            }
            return crypto_sign_publickey($keypair);
        }//632 from sodium-compat
        protected function _crypto_sign_publickey_from_secretkey($sk):string{
            if (!is_callable('\\Sodium\\crypto_sign_publickey_from_secretkey')) {
                function crypto_sign_publickey_from_secretkey($sk){
                    return ParagonIE_Compat::crypto_sign_publickey_from_secretkey($sk);
                }
            }
            return crypto_sign_publickey_from_secretkey($sk);
        }//645 from sodium-compat
        protected function _crypto_sign_secretkey($keypair):string{
            if (!is_callable('\\Sodium\\crypto_sign_secretkey')) {
                function crypto_sign_secretkey($keypair){
                    return ParagonIE_Compat::crypto_sign_secretkey($keypair);
                }
            }
            return crypto_sign_secretkey($keypair);
        }//658 from sodium-compat
        protected function _crypto_sign_seed_keypair($seed):string{
            if (!is_callable('\\Sodium\\crypto_sign_seed_keypair')) {
                function crypto_sign_seed_keypair($seed){
                    return ParagonIE_Compat::crypto_sign_seed_keypair($seed);
                }
            }
            return crypto_sign_seed_keypair($seed);
        }//671 from sodium-compat
        protected function _crypto_sign_verify_detached($signature, $message, $pk):bool{
            if (!is_callable('\\Sodium\\crypto_sign_verify_detached')) {
                function crypto_sign_verify_detached($signature, $message, $pk){
                    return ParagonIE_Compat::crypto_sign_verify_detached($signature, $message, $pk);
                }
            }
            return crypto_sign_verify_detached($signature, $message, $pk);
        }//686 from sodium-compat
        protected function _crypto_sign_ed25519_pk_to_curve25519($pk):string{
            return ParagonIE_Compat::crypto_sign_ed25519_pk_to_curve25519($pk);
        }//699 from sodium-compat
        protected function _crypto_sign_ed25519_sk_to_curve25519($sk):string{
            return ParagonIE_Compat::crypto_sign_ed25519_sk_to_curve25519($sk);
        }//712 from sodium-compat
        protected function _crypto_stream($len, $nonce, $key):string{
            if (!is_callable('\\Sodium\\crypto_stream')) {
                function crypto_stream($len, $nonce, $key){
                    return ParagonIE_Compat::crypto_stream($len, $nonce, $key);
                }
            }
            return crypto_stream($len, $nonce, $key);
        }//727 from sodium-compat
        protected function _crypto_stream_xor($message, $nonce, $key):string{
            if (!is_callable('\\Sodium\\crypto_stream_xor')) {
                function crypto_stream_xor($message, $nonce, $key){
                    return ParagonIE_Compat::crypto_stream_xor($message, $nonce, $key);
                }
            }
            return crypto_stream_xor($message, $nonce, $key);
        }//742 from sodium-compat
        protected function _hex2bin($string):string{
            if (!is_callable('\\Sodium\\hex2bin')) {
                function hex2bin($string){
                    return ParagonIE_Compat::hex2bin($string);
                }
            }
            return ParagonIE_Compat::hex2bin($string);
        }//755 from sodium-compat
        protected function _memcmp($a, $b):int{
            if (!is_callable('\\Sodium\\memcmp')) {
                function memcmp($a, $b){
                    return ParagonIE_Compat::memcmp($a, $b);
                }
            }
            return memcmp($a, $b);
        }//769 from sodium-compat
        protected function _memzero(&$str):void{
            if (!is_callable('\\Sodium\\memzero')) {
                function memzero($str){
                    ParagonIE_Compat::memzero($str);
                }
            }
            memzero($str);
        }//782 from sodium-compat
        protected function _randombytes_buf($amount):string{
            if (!is_callable('\\Sodium\\randombytes_buf')) {
                function randombytes_buf($amount){
                    return ParagonIE_Compat::randombytes_buf($amount);
                }
            }
            return randombytes_buf($amount);
        }//794 from sodium-compat
        protected function _randombytes_uniform($upperLimit):int{
            if (!is_callable('\\Sodium\\randombytes_uniform')) {
                function randombytes_uniform($upperLimit){
                    return ParagonIE_Compat::randombytes_uniform($upperLimit);
                }
            }
            return randombytes_uniform($upperLimit);
        }//808 from sodium-compat
        protected function _randombytes_random16():int{
            if (!is_callable('\\Sodium\\randombytes_random16')) {
                function randombytes_random16(){
                    return ParagonIE_Compat::randombytes_random16();
                }
            }
            return randombytes_random16();
        }//819 from sodium-compat
    }
    new SodiumConstants();
}else{die;}