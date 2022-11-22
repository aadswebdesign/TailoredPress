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
    trait SodiumMethods72{
        protected function _sd_constructs():void{
            foreach (array(
                         'BASE64_VARIANT_ORIGINAL',
                         'BASE64_VARIANT_ORIGINAL_NO_PADDING',
                         'BASE64_VARIANT_URLSAFE',
                         'BASE64_VARIANT_URLSAFE_NO_PADDING',
                         'CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES',
                         'CRYPTO_AEAD_CHACHA20POLY1305_NSECBYTES',
                         'CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES',
                         'CRYPTO_AEAD_CHACHA20POLY1305_ABYTES',
                         'CRYPTO_AEAD_AES256GCM_KEYBYTES',
                         'CRYPTO_AEAD_AES256GCM_NSECBYTES',
                         'CRYPTO_AEAD_AES256GCM_NPUBBYTES',
                         'CRYPTO_AEAD_AES256GCM_ABYTES',
                         'CRYPTO_AEAD_CHACHA20POLY1305_IETF_KEYBYTES',
                         'CRYPTO_AEAD_CHACHA20POLY1305_IETF_NSECBYTES',
                         'CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES',
                         'CRYPTO_AEAD_CHACHA20POLY1305_IETF_ABYTES',
                         'CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES',
                         'CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NSECBYTES',
                         'CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES',
                         'CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES',
                         'CRYPTO_AUTH_BYTES',
                         'CRYPTO_AUTH_KEYBYTES',
                         'CRYPTO_BOX_SEALBYTES',
                         'CRYPTO_BOX_SECRETKEYBYTES',
                         'CRYPTO_BOX_PUBLICKEYBYTES',
                         'CRYPTO_BOX_KEYPAIRBYTES',
                         'CRYPTO_BOX_MACBYTES',
                         'CRYPTO_BOX_NONCEBYTES',
                         'CRYPTO_BOX_SEEDBYTES',
                         'CRYPTO_KDF_BYTES_MIN',
                         'CRYPTO_KDF_BYTES_MAX',
                         'CRYPTO_KDF_CONTEXTBYTES',
                         'CRYPTO_KDF_KEYBYTES',
                         'CRYPTO_KX_BYTES',
                         'CRYPTO_KX_KEYPAIRBYTES',
                         'CRYPTO_KX_PRIMITIVE',
                         'CRYPTO_KX_SEEDBYTES',
                         'CRYPTO_KX_PUBLICKEYBYTES',
                         'CRYPTO_KX_SECRETKEYBYTES',
                         'CRYPTO_KX_SESSIONKEYBYTES',
                         'CRYPTO_GENERICHASH_BYTES',
                         'CRYPTO_GENERICHASH_BYTES_MIN',
                         'CRYPTO_GENERICHASH_BYTES_MAX',
                         'CRYPTO_GENERICHASH_KEYBYTES',
                         'CRYPTO_GENERICHASH_KEYBYTES_MIN',
                         'CRYPTO_GENERICHASH_KEYBYTES_MAX',
                         'CRYPTO_PWHASH_SALTBYTES',
                         'CRYPTO_PWHASH_STRPREFIX',
                         'CRYPTO_PWHASH_ALG_ARGON2I13',
                         'CRYPTO_PWHASH_ALG_ARGON2ID13',
                         'CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE',
                         'CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE',
                         'CRYPTO_PWHASH_MEMLIMIT_MODERATE',
                         'CRYPTO_PWHASH_OPSLIMIT_MODERATE',
                         'CRYPTO_PWHASH_MEMLIMIT_SENSITIVE',
                         'CRYPTO_PWHASH_OPSLIMIT_SENSITIVE',
                         'CRYPTO_PWHASH_SCRYPTSALSA208SHA256_SALTBYTES',
                         'CRYPTO_PWHASH_SCRYPTSALSA208SHA256_STRPREFIX',
                         'CRYPTO_PWHASH_SCRYPTSALSA208SHA256_MEMLIMIT_INTERACTIVE',
                         'CRYPTO_PWHASH_SCRYPTSALSA208SHA256_OPSLIMIT_INTERACTIVE',
                         'CRYPTO_PWHASH_SCRYPTSALSA208SHA256_MEMLIMIT_SENSITIVE',
                         'CRYPTO_PWHASH_SCRYPTSALSA208SHA256_OPSLIMIT_SENSITIVE',
                         'CRYPTO_SCALARMULT_BYTES',
                         'CRYPTO_SCALARMULT_SCALARBYTES',
                         'CRYPTO_SHORTHASH_BYTES',
                         'CRYPTO_SHORTHASH_KEYBYTES',
                         'CRYPTO_SECRETBOX_KEYBYTES',
                         'CRYPTO_SECRETBOX_MACBYTES',
                         'CRYPTO_SECRETBOX_NONCEBYTES',
                         'CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES',
                         'CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES',
                         'CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES',
                         'CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_PUSH',
                         'CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_PULL',
                         'CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_REKEY',
                         'CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL',
                         'CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_MESSAGEBYTES_MAX',
                         'CRYPTO_SIGN_BYTES',
                         'CRYPTO_SIGN_SEEDBYTES',
                         'CRYPTO_SIGN_PUBLICKEYBYTES',
                         'CRYPTO_SIGN_SECRETKEYBYTES',
                         'CRYPTO_SIGN_KEYPAIRBYTES',
                         'CRYPTO_STREAM_KEYBYTES',
                         'CRYPTO_STREAM_NONCEBYTES',
                         'CRYPTO_STREAM_XCHACHA20_KEYBYTES',
                         'CRYPTO_STREAM_XCHACHA20_NONCEBYTES',
                         'LIBRARY_MAJOR_VERSION',
                         'LIBRARY_MINOR_VERSION',
                         'LIBRARY_VERSION_MAJOR',
                         'LIBRARY_VERSION_MINOR',
                         'VERSION_STRING'
                     ) as $constant
            ) {
                if (!defined("SODIUM_$constant") && defined("ParagonIE_Compat::$constant")) {
                    define("SODIUM_$constant", constant("ParagonIE_Compat::$constant"));
                }
            }
        }//added
        protected function _sd_add(&$val, $addv):void{
            if (!is_callable('sodium_add')) {
                function sodium_add(&$val, $addv){
                    ParagonIE_Compat::add($val, $addv);
                }
            }
            ParagonIE_Compat::add($val, $addv);
        }//118 from php72compat.php
        protected function _sd_base642bin($string, $variant, $ignore =''):string{
            if (!is_callable('sodium_base642bin')) {
                function sodium_base642bin($string, $variant, $ignore){
                    return ParagonIE_Compat::base642bin($string, $variant, $ignore);
                }
            }
            return sodium_base642bin($string, $variant, $ignore);
        }//133 php72compat.php
        protected function _sd_bin2base64($string, $variant):string{
            if (!is_callable('sodium_bin2base64')) {
                function sodium_bin2base64($string, $variant){
                    return ParagonIE_Compat::bin2base64($string, $variant);
                }
            }
            return sodium_bin2base64($string, $variant);
        }//147 php72compat.php
        protected function _sd_bin2hex($string):string{
            if (!is_callable('sodium_bin2hex')) {
                function sodium_bin2hex($string){
                    return ParagonIE_Compat::bin2hex($string);
                }
            }
            return sodium_bin2hex($string);
        }//160 php72compat.php
        protected function _sd_compare($a, $b):int{
            if (!is_callable('sodium_compare')) {
                function sodium_compare($a, $b){
                    return ParagonIE_Compat::compare($a, $b);
                }
            }
            return sodium_compare($a, $b);
        }//174 php72compat.php
        protected function _sd_crypto_aead_aes256gcm_decrypt($message, $assocData, $nonce, $key):string{
            if (!is_callable('sodium_crypto_aead_aes256gcm_decrypt')) {
                function sodium_crypto_aead_aes256gcm_decrypt($message, $assocData, $nonce, $key){
                    try {
                        return ParagonIE_Compat::crypto_aead_aes256gcm_decrypt($message, $assocData, $nonce, $key);
                    } catch (\Error $ex) { return false;
                    } catch (\Exception $ex) {  return false;
                    }
                }
            }
            return sodium_crypto_aead_aes256gcm_decrypt($message, $assocData, $nonce, $key);
        }//188 php72compat.php
        protected function _sd_crypto_aead_aes256gcm_encrypt($message, $assocData, $nonce, $key):string{
            if (!is_callable('sodium_crypto_aead_aes256gcm_encrypt')) {
                function sodium_crypto_aead_aes256gcm_encrypt($message, $assocData, $nonce, $key){
                    return ParagonIE_Compat::crypto_aead_aes256gcm_encrypt($message, $assocData, $nonce, $key);
                }
            }
            return sodium_crypto_aead_aes256gcm_encrypt($message, $assocData, $nonce, $key);
        }//210 php72compat.php
        protected function _sd_crypto_aead_aes256gcm_is_available():bool{
            if (!is_callable('sodium_crypto_aead_aes256gcm_is_available')) {
                function sodium_crypto_aead_aes256gcm_is_available(){
                    return ParagonIE_Compat::crypto_aead_aes256gcm_is_available();
                }
            }
            return sodium_crypto_aead_aes256gcm_is_available();
        }//220 php72compat.php
        protected function _sd_crypto_aead_chacha20poly1305_decrypt($message, $assocData, $nonce, $key):string{
            if (!is_callable('sodium_crypto_aead_chacha20poly1305_decrypt')) {
                function sodium_crypto_aead_chacha20poly1305_decrypt($message, $assocData, $nonce, $key){
                    try { return ParagonIE_Compat::crypto_aead_chacha20poly1305_decrypt($message, $assocData, $nonce, $key);
                    } catch (\Error $ex) { return false;} catch (\Exception $ex) { return false;}
                }
            }
            return sodium_crypto_aead_chacha20poly1305_decrypt($message, $assocData, $nonce, $key);
        }//234 php72compat.php
        protected function _sd_crypto_aead_chacha20poly1305_encrypt($message, $assocData, $nonce, $key):string{
            if (!is_callable('sodium_crypto_aead_chacha20poly1305_encrypt')) {
                function sodium_crypto_aead_chacha20poly1305_encrypt($message, $assocData, $nonce, $key){
                    return ParagonIE_Compat::crypto_aead_chacha20poly1305_encrypt($message, $assocData, $nonce, $key);
                }
            }
            return sodium_crypto_aead_chacha20poly1305_encrypt($message, $assocData, $nonce, $key);
        }//256 php72compat.php
        protected function _sd_crypto_aead_chacha20poly1305_keygen():string{
            if (!is_callable('sodium_crypto_aead_chacha20poly1305_keygen')) {
                function sodium_crypto_aead_chacha20poly1305_keygen(){
                    return ParagonIE_Compat::crypto_aead_chacha20poly1305_keygen();
                }
            }
            return sodium_crypto_aead_chacha20poly1305_keygen();
        }//267 php72compat.php
        protected function _sd_crypto_aead_chacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key):string{
            if (!is_callable('sodium_crypto_aead_chacha20poly1305_ietf_decrypt')) {
                function sodium_crypto_aead_chacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key){
                    try { return ParagonIE_Compat::crypto_aead_chacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key);
                    } catch (\Error $ex) { return false; } catch (\Exception $ex) { return false;}
                }
            }
            return sodium_crypto_aead_chacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key);
        }//281 php72compat.php
        protected function _sd_crypto_aead_chacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key):string{
            if (!is_callable('sodium_crypto_aead_chacha20poly1305_ietf_encrypt')) {
                function sodium_crypto_aead_chacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key){
                    return ParagonIE_Compat::crypto_aead_chacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key);
                }
            }
            return sodium_crypto_aead_chacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key);
        }//303 php72compat.php
        protected function _sd_crypto_aead_chacha20poly1305_ietf_keygen():string{
            if (!is_callable('sodium_crypto_aead_chacha20poly1305_ietf_keygen')) {
                function sodium_crypto_aead_chacha20poly1305_ietf_keygen(){
                    return ParagonIE_Compat::crypto_aead_chacha20poly1305_ietf_keygen();
                }
            }
            return sodium_crypto_aead_chacha20poly1305_ietf_keygen();
        }//314 php72compat.php
        protected function _sd_crypto_aead_xchacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key):string{
            if (!is_callable('sodium_crypto_aead_xchacha20poly1305_ietf_decrypt')) {
                function sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key){
                    try { return ParagonIE_Compat::crypto_aead_xchacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key, true);
                    } catch (\Error $ex) { return false; } catch (\Exception $ex) { return false;}
                }
            }
            return sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($message, $assocData, $nonce, $key);
        }//328 php72compat.php
        protected function _sd_crypto_aead_xchacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key):string{
            if (!is_callable('sodium_crypto_aead_xchacha20poly1305_ietf_encrypt')) {
                function sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key){
                    return ParagonIE_Compat::crypto_aead_xchacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key, true);
                }
            }
            return sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($message, $assocData, $nonce, $key);
        }//339 php72compat.php
        protected function _sd_crypto_aead_xchacha20poly1305_ietf_keygen():string{
            if (!is_callable('sodium_crypto_aead_xchacha20poly1305_ietf_keygen')) {
                function sodium_crypto_aead_xchacha20poly1305_ietf_keygen(){
                    return ParagonIE_Compat::crypto_aead_xchacha20poly1305_ietf_keygen();
                }
            }
            return sodium_crypto_aead_xchacha20poly1305_ietf_keygen();
        }//361 php72compat.php
        protected function _sd_crypto_auth_wrapper($message, $key):string{
            if (!is_callable('sodium_crypto_auth')) {
                function sodium_crypto_auth($message, $key){
                    return ParagonIE_Compat::crypto_auth($message, $key);
                }
            }
            return sodium_crypto_auth($message, $key);
        }//375 php72compat.php
        protected function _sd_crypto_auth_keygen():string{
            if (!is_callable('sodium_crypto_auth_keygen')) {
                function sodium_crypto_auth_keygen(){
                    return ParagonIE_Compat::crypto_auth_keygen();
                }
            }
            return sodium_crypto_auth_keygen();
        }//386 php72compat.php
        protected function _sd_crypto_auth_verify($mac, $message, $key):bool{
            if (!is_callable('sodium_crypto_auth_verify')) {
                function sodium_crypto_auth_verify($mac, $message, $key){
                    return ParagonIE_Compat::crypto_auth_verify($mac, $message, $key);
                }
            }
            return sodium_crypto_auth_verify($mac, $message, $key);
        }//401 php72compat.php
        protected function _sd_crypto_box($message, $nonce, $kp):string{
            if (!is_callable('sodium_crypto_box')) {
                function sodium_crypto_box($message, $nonce, $kp){
                    return ParagonIE_Compat::crypto_box($message, $nonce, $kp);
                }
            }
            return sodium_crypto_box($message, $nonce, $kp);
        }//416 php72compat.php
        protected function _sd_crypto_box_keypair():string{
            if (!is_callable('sodium_crypto_box_keypair')) {
                function sodium_crypto_box_keypair(){
                    return ParagonIE_Compat::crypto_box_keypair();
                }
            }
            return sodium_crypto_box_keypair();
        }//428 php72compat.php
        protected function _sd_crypto_box_keypair_from_secretkey_and_publickey($sk, $pk):string{
            if (!is_callable('sodium_crypto_box_keypair_from_secretkey_and_publickey')) {
                function sodium_crypto_box_keypair_from_secretkey_and_publickey($sk, $pk){
                    return ParagonIE_Compat::crypto_box_keypair_from_secretkey_and_publickey($sk, $pk);
                }
            }
            return sodium_crypto_box_keypair_from_secretkey_and_publickey($sk, $pk);
        }//442 php72compat.php
        protected function _sd_crypto_box_open($message, $nonce, $kp):bool{
            if (!is_callable('sodium_crypto_box_open')) {
                function sodium_crypto_box_open($message, $nonce, $kp){
                    try { return ParagonIE_Compat::crypto_box_open($message, $nonce, $kp);
                    } catch (\Error $ex) { return false;} catch (\Exception $ex) { return false;}
                }
            }
            return sodium_crypto_box_open($message, $nonce, $kp);
        }//455 php72compat.php
        protected function _sd_crypto_box_publickey($keypair):string{
            if (!is_callable('sodium_crypto_box_publickey')) {
                function sodium_crypto_box_publickey($keypair) {
                    return ParagonIE_Compat::crypto_box_publickey($keypair);
                }
            }
            return sodium_crypto_box_publickey($keypair);
        }//474 php72compat.php
        protected function _sd_crypto_box_publickey_from_secretkey($sk):string{
            if (!is_callable('sodium_crypto_box_publickey_from_secretkey')) {
                function sodium_crypto_box_publickey_from_secretkey($sk){
                    return ParagonIE_Compat::crypto_box_publickey_from_secretkey($sk);
                }
            }
            return sodium_crypto_box_publickey_from_secretkey($sk);
        }//487 php72compat.php
        protected function _sd_crypto_box_seal($message, $publicKey):string{
            if (!is_callable('sodium_crypto_box_seal')) {
                function sodium_crypto_box_seal($message, $publicKey){
                    return ParagonIE_Compat::crypto_box_seal($message, $publicKey);
                }
            }
            return sodium_crypto_box_seal($message, $publicKey);
        }//501 php72compat.php
        protected function _sd_crypto_box_seal_open($message, $kp){
            if (!is_callable('sodium_crypto_box_seal_open')) {
                function sodium_crypto_box_seal_open($message, $kp){
                    try {
                        return ParagonIE_Compat::crypto_box_seal_open($message, $kp);
                    } catch (SodiumException $ex) {
                        if ($ex->getMessage() === 'Argument 2 must be CRYPTO_BOX_KEYPAIRBYTES long.') {
                            throw $ex;
                        }
                        return false;
                    }
                }
            }
            return sodium_crypto_box_seal_open($message, $kp);
        }//514 php72compat.php
        protected function _sd_crypto_box_secretkey($keypair):string{
            if (!is_callable('sodium_crypto_box_secretkey')) {
                function sodium_crypto_box_secretkey($keypair){
                    return ParagonIE_Compat::crypto_box_secretkey($keypair);
                }
            }
            return sodium_crypto_box_secretkey($keypair);
        }//536 php72compat.php
        protected function _sd_crypto_box_seed_keypair($seed):string{
            if (!is_callable('sodium_crypto_box_seed_keypair')) {
                function sodium_crypto_box_seed_keypair($seed){
                    return ParagonIE_Compat::crypto_box_seed_keypair($seed);
                }
            }
            return sodium_crypto_box_seed_keypair($seed);
        }//547 php72compat.php
        protected function _sd_crypto_generichash($message, $key = null, $outLen = 32):string{
            if (!is_callable('sodium_crypto_generichash')) {
                function sodium_crypto_generichash($message, $key, $outLen){
                    return ParagonIE_Compat::crypto_generichash($message, $key, $outLen);
                }
            }
            return sodium_crypto_generichash($message, $key, $outLen);
        }//562 php72compat.php
        protected function _sd_crypto_generichash_final(&$ctx, $outputLength = 32):string{
            if (!is_callable('sodium_crypto_generichash_final')) {
                function sodium_crypto_generichash_final($ctx, $outputLength){
                    return ParagonIE_Compat::crypto_generichash_final($ctx, $outputLength);
                }
            }
            return sodium_crypto_generichash_final($ctx, $outputLength);
        }//576 php72compat.php
        protected function _sd_crypto_generichash_init($key = null, $outLen = 32):string{
            if (!is_callable('sodium_crypto_generichash_init')) {
                function sodium_crypto_generichash_init($key, $outLen){
                    return ParagonIE_Compat::crypto_generichash_init($key, $outLen);
                }
            }
            return sodium_crypto_generichash_init($key, $outLen);
        }//590 php72compat.php
        protected function _sd_crypto_generichash_keygen():string{
            if (!is_callable('sodium_crypto_generichash_keygen')) {
                function sodium_crypto_generichash_keygen(){
                    return ParagonIE_Compat::crypto_generichash_keygen();
                }
            }
            return sodium_crypto_generichash_keygen();
        }//601 php72compat.php
        protected function _sd_crypto_generichash_update(&$ctx, $message = ''):void{
            if (!is_callable('sodium_crypto_generichash_update')) {
                function sodium_crypto_generichash_update($ctx, $message){
                    ParagonIE_Compat::crypto_generichash_update($ctx, $message);
                }
            }
            sodium_crypto_generichash_update($ctx, $message);
        }//615 php72compat.php
        protected function _sd_crypto_kdf_keygen():string{
            if (!is_callable('sodium_crypto_kdf_keygen')) {
                function sodium_crypto_kdf_keygen(){
                    return ParagonIE_Compat::crypto_kdf_keygen();
                }
            }
            return sodium_crypto_kdf_keygen();
        }//627 php72compat.php
        protected function _sd_crypto_kdf_derive_from_key($subkey_len, $subkey_id, $context, $key):string{
            if (!is_callable('sodium_crypto_kdf_derive_from_key')) {
                function sodium_crypto_kdf_derive_from_key($subkey_len, $subkey_id, $context, $key){
                    return ParagonIE_Compat::crypto_kdf_derive_from_key( $subkey_len, $subkey_id, $context, $key );
                }
            }
            return sodium_crypto_kdf_derive_from_key($subkey_len, $subkey_id, $context, $key);
        }//641 php72compat.php
        protected function _sd_crypto_kx($my_secret, $their_public, $client_public, $server_public):string{
            if (!is_callable('sodium_crypto_kx')) {
                function sodium_crypto_kx($my_secret, $their_public, $client_public, $server_public){
                    return ParagonIE_Compat::crypto_kx($my_secret,$their_public,$client_public,$server_public);
                }
            }
            return sodium_crypto_kx($my_secret, $their_public, $client_public, $server_public);
        }//662 php72compat.php
        protected function _sd_crypto_kx_seed_keypair($seed):string{
            if (!is_callable('sodium_crypto_kx_seed_keypair')) {
                function sodium_crypto_kx_seed_keypair($seed){
                    return ParagonIE_Compat::crypto_kx_seed_keypair($seed);
                }
            }
            return sodium_crypto_kx_seed_keypair($seed);
        }//678 php72compat.php
        protected function _sd_crypto_kx_keypair():string{
            if (!is_callable('sodium_crypto_kx_keypair')) {
                function sodium_crypto_kx_keypair(){
                    return ParagonIE_Compat::crypto_kx_keypair();
                }
            }
            return sodium_crypto_kx_keypair();
        }//688 php72compat.php
        protected function _sd_crypto_kx_client_session_keys($keypair, $serverPublicKey):string{
            if (!is_callable('sodium_crypto_kx_client_session_keys')) {
                function sodium_crypto_kx_client_session_keys($keypair, $serverPublicKey){
                    return ParagonIE_Compat::crypto_kx_client_session_keys($keypair, $serverPublicKey);
                }
            }
            return sodium_crypto_kx_client_session_keys($keypair, $serverPublicKey);
        }//700 php72compat.php
        protected function _sd_crypto_kx_server_session_keys($keypair, $clientPublicKey):string{
            if (!is_callable('sodium_crypto_kx_server_session_keys')) {
                function sodium_crypto_kx_server_session_keys($keypair, $clientPublicKey){
                    return ParagonIE_Compat::crypto_kx_server_session_keys($keypair, $clientPublicKey);
                }
            }
            return sodium_crypto_kx_server_session_keys($keypair, $clientPublicKey);
        }//712 php72compat.php
        protected function _sd_crypto_kx_secretkey($keypair):string{
            if (!is_callable('sodium_crypto_kx_secretkey')) {
                function sodium_crypto_kx_secretkey($keypair){
                    return ParagonIE_Compat::crypto_kx_secretkey($keypair);
                }
            }
            return sodium_crypto_kx_secretkey($keypair);
        }//723 php72compat.php
        protected function _sd_crypto_kx_publickey($keypair):string{
            if (!is_callable('sodium_crypto_kx_publickey')) {
                function sodium_crypto_kx_publickey($keypair){
                    return ParagonIE_Compat::crypto_kx_publickey($keypair);
                }
            }
            return sodium_crypto_kx_publickey($keypair);
        }//734 php72compat.php
        protected function _sd_crypto_pwhash($outlen, $passwd, $salt, $opslimit, $memlimit, $algo = null):string{
            if (!is_callable('sodium_crypto_pwhash')) {
                function sodium_crypto_pwhash($outlen, $passwd, $salt, $opslimit, $memlimit, $algo){
                    return ParagonIE_Compat::crypto_pwhash($outlen, $passwd, $salt, $opslimit, $memlimit, $algo);
                }
            }
            return sodium_crypto_pwhash($outlen, $passwd, $salt, $opslimit, $memlimit, $algo);
        }//752 php72compat.php
        protected function _sd_crypto_pwhash_str($passwd, $opslimit, $memlimit):string{
            if (!is_callable('sodium_crypto_pwhash_str')) {
                function sodium_crypto_pwhash_str($passwd, $opslimit, $memlimit){
                    return ParagonIE_Compat::crypto_pwhash_str($passwd, $opslimit, $memlimit);
                }
            }
            return sodium_crypto_pwhash_str($passwd, $opslimit, $memlimit);
        }//767 php72compat.php
        protected function _sd_crypto_pwhash_str_needs_rehash($hash, $opslimit, $memlimit):bool{
            if (!is_callable('sodium_crypto_pwhash_str_needs_rehash')) {
                function sodium_crypto_pwhash_str_needs_rehash($hash, $opslimit, $memlimit){
                    return ParagonIE_Compat::crypto_pwhash_str_needs_rehash($hash, $opslimit, $memlimit);
                }
            }
            return sodium_crypto_pwhash_str_needs_rehash($hash, $opslimit, $memlimit);
        }//782 php72compat.php
        protected function _sd_crypto_pwhash_str_verify($passwd, $hash):bool{
            if (!is_callable('sodium_crypto_pwhash_str_verify')) {
                function sodium_crypto_pwhash_str_verify($passwd, $hash){
                    return ParagonIE_Compat::crypto_pwhash_str_verify($passwd, $hash);
                }
            }
            return sodium_crypto_pwhash_str_verify($passwd, $hash);
        }//796 php72compat.php
        protected function _sd_crypto_pwhash_scryptsalsa208sha256($outlen, $passwd, $salt, $opslimit, $memlimit):string{
            if (!is_callable('sodium_crypto_pwhash_scryptsalsa208sha256')) {
                function sodium_crypto_pwhash_scryptsalsa208sha256($outlen, $passwd, $salt, $opslimit, $memlimit){
                    return ParagonIE_Compat::crypto_pwhash_scryptsalsa208sha256($outlen, $passwd, $salt, $opslimit, $memlimit);
                }
            }
            return sodium_crypto_pwhash_scryptsalsa208sha256($outlen, $passwd, $salt, $opslimit, $memlimit);
        }//813 php72compat.php
        protected function _sd_crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit):string{
            if (!is_callable('sodium_crypto_pwhash_scryptsalsa208sha256_str')) {
                function sodium_crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit){
                    return ParagonIE_Compat::crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit);
                }
            }
            return sodium_crypto_pwhash_scryptsalsa208sha256_str($passwd, $opslimit, $memlimit);
        }//828 php72compat.php
        protected function _sd_crypto_pwhash_scryptsalsa208sha256_str_verify($passwd, $hash):bool{
            if (!is_callable('sodium_crypto_pwhash_scryptsalsa208sha256_str_verify')) {
                function sodium_crypto_pwhash_scryptsalsa208sha256_str_verify($passwd, $hash){
                    return ParagonIE_Compat::crypto_pwhash_scryptsalsa208sha256_str_verify($passwd, $hash);
                }
            }
            return sodium_crypto_pwhash_scryptsalsa208sha256_str_verify($passwd, $hash);
        }//842 php72compat.php
        protected function _sd_crypto_scalarmult($n, $p):string{
            if (!is_callable('sodium_crypto_scalarmult')) {
                function sodium_crypto_scalarmult($n, $p){
                    return ParagonIE_Compat::crypto_scalarmult($n, $p);
                }
            }
            return sodium_crypto_scalarmult($n, $p);
        }//856 php72compat.php
        protected function _sd_crypto_scalarmult_base($n):string{
            if (!is_callable('sodium_crypto_scalarmult_base')) {
                function sodium_crypto_scalarmult_base($n){
                    return ParagonIE_Compat::crypto_scalarmult_base($n);
                }
            }
            return sodium_crypto_scalarmult_base($n);
        }//869 php72compat.php
        protected function _sd_crypto_secretbox($message, $nonce, $key):string{
            if (!is_callable('sodium_crypto_secretbox')) {
                function sodium_crypto_secretbox($message, $nonce, $key){
                    return ParagonIE_Compat::crypto_secretbox($message, $nonce, $key);
                }
            }
            return sodium_crypto_secretbox($message, $nonce, $key);
        }//884 php72compat.php
        protected function _sd_crypto_secretbox_keygen():string{
            if (!is_callable('sodium_crypto_secretbox_keygen')) {
                function sodium_crypto_secretbox_keygen(){
                    return ParagonIE_Compat::crypto_secretbox_keygen();
                }
            }
            return sodium_crypto_secretbox_keygen();
        }//895 php72compat.php
        protected function _sd_crypto_secretbox_open($message, $nonce, $key):string{
            if (!is_callable('sodium_crypto_secretbox_open')) {
                function sodium_crypto_secretbox_open($message, $nonce, $key){
                    try { return ParagonIE_Compat::crypto_secretbox_open($message, $nonce, $key);
                    } catch (\Error $ex) { return false;} catch (\Exception $ex) { return false;}
                }
            }
            return sodium_crypto_secretbox_open($message, $nonce, $key);
        }//908 php72compat.php
        protected function _sd_crypto_secretstream_xchacha20poly1305_init_push($key):array{
            if (!is_callable('sodium_crypto_secretstream_xchacha20poly1305_init_push')) {
                function sodium_crypto_secretstream_xchacha20poly1305_init_push($key){
                    return ParagonIE_Compat::crypto_secretstream_xchacha20poly1305_init_push($key);
                }
            }
            return sodium_crypto_secretstream_xchacha20poly1305_init_push($key);
        }//925 php72compat.php
        protected function _sd_crypto_secretstream_xchacha20poly1305_push(&$state, $msg, $aad = '', $tag = 0):string{
            if (!is_callable('sodium_crypto_secretstream_xchacha20poly1305_push')) {
                function sodium_crypto_secretstream_xchacha20poly1305_push($state, $msg, $aad, $tag) {
                    return ParagonIE_Compat::crypto_secretstream_xchacha20poly1305_push($state, $msg, $aad, $tag);
                }
            }
            return sodium_crypto_secretstream_xchacha20poly1305_push($state, $msg, $aad, $tag);
        }//939 php72compat.php
        protected function _sd_crypto_secretstream_xchacha20poly1305_init_pull($header, $key):string{
            if (!is_callable('sodium_crypto_secretstream_xchacha20poly1305_init_pull')) {
                function sodium_crypto_secretstream_xchacha20poly1305_init_pull($header, $key){
                    return ParagonIE_Compat::crypto_secretstream_xchacha20poly1305_init_pull($header, $key);
                }
            }
            return sodium_crypto_secretstream_xchacha20poly1305_init_pull($header, $key);
        }//951 php72compat.php
        protected function _sd_crypto_secretstream_xchacha20poly1305_pull(&$state, $cipher, $aad = ''){
            if (!is_callable('sodium_crypto_secretstream_xchacha20poly1305_pull')) {
                function sodium_crypto_secretstream_xchacha20poly1305_pull(&$state, $cipher, $aad){
                    return ParagonIE_Compat::crypto_secretstream_xchacha20poly1305_pull($state, $cipher, $aad);
                }
            }
            return sodium_crypto_secretstream_xchacha20poly1305_pull($state, $cipher, $aad);
        }//964 php72compat.php
        protected function _sd_crypto_secretstream_xchacha20poly1305_rekey(&$state):void{
            if (!is_callable('sodium_crypto_secretstream_xchacha20poly1305_rekey')) {
                function sodium_crypto_secretstream_xchacha20poly1305_rekey($state){
                    ParagonIE_Compat::crypto_secretstream_xchacha20poly1305_rekey($state);
                }
            }
            sodium_crypto_secretstream_xchacha20poly1305_rekey($state);
        }//975 php72compat.php
        protected function _sd_crypto_secretstream_xchacha20poly1305_keygen():string{
            if (!is_callable('sodium_crypto_secretstream_xchacha20poly1305_keygen')) {
                function sodium_crypto_secretstream_xchacha20poly1305_keygen(){
                    return ParagonIE_Compat::crypto_secretstream_xchacha20poly1305_keygen();
                }
            }
            return sodium_crypto_secretstream_xchacha20poly1305_keygen();
        }//985 php72compat.php
        protected function _sd_crypto_shorthash($message, $key = ''):string{
            if (!is_callable('sodium_crypto_shorthash')) {
                function sodium_crypto_shorthash($message, $key){
                    return ParagonIE_Compat::crypto_shorthash($message, $key);
                }
            }
            return sodium_crypto_shorthash($message, $key);
        }//999 php72compat.php
        protected function _sd_crypto_shorthash_keygen():string{
            if (!is_callable('sodium_crypto_shorthash_keygen')) {
                function sodium_crypto_shorthash_keygen(){
                    return ParagonIE_Compat::crypto_shorthash_keygen();
                }
            }
            return sodium_crypto_shorthash_keygen();
        }//1010 php72compat.php
        protected function _sd_crypto_sign($message, $sk):string{
            if (!is_callable('sodium_crypto_sign')) {
                function sodium_crypto_sign($message, $sk){
                    return ParagonIE_Compat::crypto_sign($message, $sk);
                }
            }
            return sodium_crypto_sign($message, $sk);
        }//1024 php72compat.php
        protected function _sd_crypto_sign_detached($message, $sk):string{
            if (!is_callable('sodium_crypto_sign_detached')) {
                function sodium_crypto_sign_detached($message, $sk){
                    return ParagonIE_Compat::crypto_sign_detached($message, $sk);
                }
            }
            return sodium_crypto_sign_detached($message, $sk);
        }//1038 php72compat.php
        protected function _sd_crypto_sign_keypair_from_secretkey_and_publickey($sk, $pk):string{
            if (!is_callable('sodium_crypto_sign_keypair_from_secretkey_and_publickey')) {
                function sodium_crypto_sign_keypair_from_secretkey_and_publickey($sk, $pk){
                    return ParagonIE_Compat::crypto_sign_keypair_from_secretkey_and_publickey($sk, $pk);
                }
            }
            return sodium_crypto_sign_keypair_from_secretkey_and_publickey($sk, $pk);
        }//1052 php72compat.php
        protected function _sd_crypto_sign_keypair():string{
            if (!is_callable('sodium_crypto_sign_keypair')) {
                function sodium_crypto_sign_keypair(){
                    return ParagonIE_Compat::crypto_sign_keypair();
                }
            }
            return sodium_crypto_sign_keypair();
        }//1064 php72compat.php
        protected function _sd_crypto_sign_open($signedMessage, $pk):string{
            if (!is_callable('sodium_crypto_sign_open')) {
                function sodium_crypto_sign_open($signedMessage, $pk){
                    try { return ParagonIE_Compat::crypto_sign_open($signedMessage, $pk);
                    } catch (\Error $ex) {return false;} catch (\Exception $ex) {return false;}
                }
            }
            return sodium_crypto_sign_open($signedMessage, $pk);
        }//1076 php72compat.php
        protected function _sd_crypto_sign_publickey($keypair):string{
            if (!is_callable('sodium_crypto_sign_publickey')) {
                function sodium_crypto_sign_publickey($keypair){
                    return ParagonIE_Compat::crypto_sign_publickey($keypair);
                }
            }
            return sodium_crypto_sign_publickey($keypair);
        }//1095 php72compat.php
        protected function _sd_crypto_sign_publickey_from_secretkey($sk):string{
            if (!is_callable('sodium_crypto_sign_publickey_from_secretkey')) {
                function sodium_crypto_sign_publickey_from_secretkey($sk){
                    return ParagonIE_Compat::crypto_sign_publickey_from_secretkey($sk);
                }
            }
            return sodium_crypto_sign_publickey_from_secretkey($sk);
        }//1108 php72compat.php
        protected function _sd_crypto_sign_secretkey($keypair):string{
            if (!is_callable('sodium_crypto_sign_secretkey')) {
                function sodium_crypto_sign_secretkey($keypair){
                    return ParagonIE_Compat::crypto_sign_secretkey($keypair);
                }
            }
            return sodium_crypto_sign_secretkey($keypair);
        }//1121 php72compat.php
        protected function _sd_crypto_sign_seed_keypair($seed):string{
            if (!is_callable('sodium_crypto_sign_seed_keypair')) {
                function sodium_crypto_sign_seed_keypair($seed){
                    return ParagonIE_Compat::crypto_sign_seed_keypair($seed);
                }
            }
            return sodium_crypto_sign_seed_keypair($seed);
        }//1134 php72compat.php
        protected function _sd_crypto_sign_verify_detached($signature, $message, $pk):bool{
            if (!is_callable('sodium_crypto_sign_verify_detached')) {
                function sodium_crypto_sign_verify_detached($signature, $message, $pk){
                    return ParagonIE_Compat::crypto_sign_verify_detached($signature, $message, $pk);
                }
            }
            return sodium_crypto_sign_verify_detached($signature, $message, $pk);
        }//1149 php72compat.php
        protected function _sd_crypto_sign_ed25519_pk_to_curve25519($pk):string{
            if (!is_callable('sodium_crypto_sign_ed25519_pk_to_curve25519')) {
                function sodium_crypto_sign_ed25519_pk_to_curve25519($pk) {
                    return ParagonIE_Compat::crypto_sign_ed25519_pk_to_curve25519($pk);
                }
            }
            return sodium_crypto_sign_ed25519_pk_to_curve25519($pk);
        }//1162 php72compat.php
        protected function _sd_crypto_sign_ed25519_sk_to_curve25519($sk):string{
            if (!is_callable('sodium_crypto_sign_ed25519_sk_to_curve25519')) {
                function sodium_crypto_sign_ed25519_sk_to_curve25519($sk){
                    return ParagonIE_Compat::crypto_sign_ed25519_sk_to_curve25519($sk);
                }
            }
            return sodium_crypto_sign_ed25519_sk_to_curve25519($sk);
        }//1175 php72compat.php
        protected function _sd_crypto_stream($len, $nonce, $key):string{
            if (!is_callable('sodium_crypto_stream')) {
                function sodium_crypto_stream($len, $nonce, $key){
                    return ParagonIE_Compat::crypto_stream($len, $nonce, $key);
                }
            }
            return sodium_crypto_stream($len, $nonce, $key);
        }//1190 php72compat.php
        protected function _sd_crypto_stream_keygen():string{
            if (!is_callable('sodium_crypto_stream_keygen')) {
                function sodium_crypto_stream_keygen(){
                    return ParagonIE_Compat::crypto_stream_keygen();
                }
            }
            return sodium_crypto_stream_keygen();
        }//1201 php72compat.php
        protected function _sd_crypto_stream_xor($message, $nonce, $key):string{
            if (!is_callable('sodium_crypto_stream_xor')) {
                function sodium_crypto_stream_xor($message, $nonce, $key){
                    return ParagonIE_Compat::crypto_stream_xor($message, $nonce, $key);
                }
            }
            return sodium_crypto_stream_xor($message, $nonce, $key);
        }//1216 php72compat.php
        use StreamXChacha20Methods;
        protected function _sd_hex2bin($string):string{
            if (!is_callable('sodium_hex2bin')) {
                function sodium_hex2bin($string){
                    return ParagonIE_Compat::hex2bin($string);
                }
            }
            return sodium_hex2bin($string);
        }//1230 php72compat.php
        protected function _sd_increment(&$string):void{
            if (!is_callable('sodium_increment')) {
                function sodium_increment($string){
                    ParagonIE_Compat::increment($string);
                }
            }
            sodium_increment($string);
        }//1243 php72compat.php
        protected function _sd_library_version_major():int{
            if (!is_callable('sodium_library_version_major')) {
                function sodium_library_version_major(){
                    return ParagonIE_Compat::library_version_major();
                }
            }
            return sodium_library_version_major();
        }//1253 php72compat.php
        protected function _sd_library_version_minor():int{
            if (!is_callable('sodium_library_version_minor')) {
                function sodium_library_version_minor(){
                    return ParagonIE_Compat::library_version_minor();
                }
            }
            return sodium_library_version_minor();
        }//1263 php72compat.php
        protected function _sd_version_string():string{
            if (!is_callable('sodium_version_string')) {
                function sodium_version_string(){
                    return ParagonIE_Compat::version_string();
                }
            }
            return sodium_version_string();

        }//1273 php72compat.php
        protected function _sd_memcmp($a, $b):int{
            if (!is_callable('sodium_memcmp')) {
                function sodium_memcmp($a, $b){
                    return ParagonIE_Compat::memcmp($a, $b);
                }
            }
            return sodium_memcmp($a, $b);
        }//1287 php72compat.php
        protected function _sd_memzero(&$str):void{
            if (!is_callable('sodium_memzero')) {
                function sodium_memzero($str){
                    ParagonIE_Compat::memzero($str);
                }
            }
            sodium_memzero($str);
        }//1300 php72compat.php
        protected function _sd_pad($unpadded, $blockSize):int{
            if (!is_callable('sodium_pad')) {
                function sodium_pad($unpadded, $blockSize){
                    return ParagonIE_Compat::pad($unpadded, $blockSize, true);
                }
            }
            return sodium_pad($unpadded, $blockSize);
        }//1314 php72compat.php
        protected function _sd_unpad($padded, $blockSize):int{
            if (!is_callable('sodium_unpad')) {
                function sodium_unpad($padded, $blockSize){
                    return ParagonIE_Compat::unpad($padded, $blockSize, true);
                }
            }
            return sodium_unpad($padded, $blockSize);
        }//1328 php72compat.php
        protected function _sd_randombytes_buf($amount):string{
            if (!is_callable('sodium_randombytes_buf')) {
                function sodium_randombytes_buf($amount){
                    return ParagonIE_Compat::randombytes_buf($amount);
                }
            }
            return sodium_randombytes_buf($amount);
        }//1340 php72compat.php
        protected function _sd_randombytes_uniform($upperLimit):int{
            if (!is_callable('sodium_randombytes_uniform')) {
                function sodium_randombytes_uniform($upperLimit){
                    return ParagonIE_Compat::randombytes_uniform($upperLimit);
                }
            }
            return sodium_randombytes_uniform($upperLimit);
        }//1353 php72compat.php
        protected function _sd_randombytes_random16():int{
            if (!is_callable('sodium_randombytes_random16')) {
                function sodium_randombytes_random16(){
                    return ParagonIE_Compat::randombytes_random16();
                }
            }
            return sodium_randombytes_random16();
        }//1365 php72compat.php
    }
    new SodiumConstants();
}else{die;}