<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-11-2022
 * Time: 06:58
 */
namespace TP_Core\Libs\PHP\SodiumCompat\lib;
use TP_Core\Libs\PHP\SodiumCompat\src\ParagonIE_Compat;
if(ABSPATH){
    class SodiumConstants{
        public const CRYPTO_AEAD_AES256GCM_KEYBYTES = ParagonIE_Compat::CRYPTO_AEAD_AES256GCM_KEYBYTES;
        public const CRYPTO_AEAD_AES256GCM_NSECBYTES = ParagonIE_Compat::CRYPTO_AEAD_AES256GCM_NSECBYTES;
        public const CRYPTO_AEAD_AES256GCM_NPUBBYTES = ParagonIE_Compat::CRYPTO_AEAD_AES256GCM_NPUBBYTES;
        public const CRYPTO_AEAD_AES256GCM_ABYTES = ParagonIE_Compat::CRYPTO_AEAD_AES256GCM_ABYTES;
        public const CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES = ParagonIE_Compat::CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES;
        public const CRYPTO_AEAD_CHACHA20POLY1305_NSECBYTES = ParagonIE_Compat::CRYPTO_AEAD_CHACHA20POLY1305_NSECBYTES;
        public const CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES = ParagonIE_Compat::CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES;
        public const CRYPTO_AEAD_CHACHA20POLY1305_ABYTES = ParagonIE_Compat::CRYPTO_AEAD_CHACHA20POLY1305_ABYTES;
        public const CRYPTO_AEAD_CHACHA20POLY1305_IETF_KEYBYTES = ParagonIE_Compat::CRYPTO_AEAD_CHACHA20POLY1305_IETF_KEYBYTES;
        public const CRYPTO_AEAD_CHACHA20POLY1305_IETF_NSECBYTES = ParagonIE_Compat::CRYPTO_AEAD_CHACHA20POLY1305_IETF_NSECBYTES;
        public const CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES = ParagonIE_Compat::CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES;
        public const CRYPTO_AEAD_CHACHA20POLY1305_IETF_ABYTES = ParagonIE_Compat::CRYPTO_AEAD_CHACHA20POLY1305_IETF_ABYTES;
        public const CRYPTO_AUTH_BYTES = ParagonIE_Compat::CRYPTO_AUTH_BYTES;
        public const CRYPTO_AUTH_KEYBYTES = ParagonIE_Compat::CRYPTO_AUTH_KEYBYTES;
        public const CRYPTO_BOX_SEALBYTES = ParagonIE_Compat::CRYPTO_BOX_SEALBYTES;
        public const CRYPTO_BOX_SECRETKEYBYTES = ParagonIE_Compat::CRYPTO_BOX_SECRETKEYBYTES;
        public const CRYPTO_BOX_PUBLICKEYBYTES = ParagonIE_Compat::CRYPTO_BOX_PUBLICKEYBYTES;
        public const CRYPTO_BOX_KEYPAIRBYTES = ParagonIE_Compat::CRYPTO_BOX_KEYPAIRBYTES;
        public const CRYPTO_BOX_MACBYTES = ParagonIE_Compat::CRYPTO_BOX_MACBYTES;
        public const CRYPTO_BOX_NONCEBYTES = ParagonIE_Compat::CRYPTO_BOX_NONCEBYTES;
        public const CRYPTO_BOX_SEEDBYTES = ParagonIE_Compat::CRYPTO_BOX_SEEDBYTES;
        public const CRYPTO_KX_BYTES = ParagonIE_Compat::CRYPTO_KX_BYTES;
        public const CRYPTO_KX_SEEDBYTES = ParagonIE_Compat::CRYPTO_KX_SEEDBYTES;
        public const CRYPTO_KX_PUBLICKEYBYTES = ParagonIE_Compat::CRYPTO_KX_PUBLICKEYBYTES;
        public const CRYPTO_KX_SECRETKEYBYTES = ParagonIE_Compat::CRYPTO_KX_SECRETKEYBYTES;
        public const CRYPTO_GENERICHASH_BYTES = ParagonIE_Compat::CRYPTO_GENERICHASH_BYTES;
        public const CRYPTO_GENERICHASH_BYTES_MIN = ParagonIE_Compat::CRYPTO_GENERICHASH_BYTES_MIN;
        public const CRYPTO_GENERICHASH_BYTES_MAX = ParagonIE_Compat::CRYPTO_GENERICHASH_BYTES_MAX;
        public const CRYPTO_GENERICHASH_KEYBYTES = ParagonIE_Compat::CRYPTO_GENERICHASH_KEYBYTES;
        public const CRYPTO_GENERICHASH_KEYBYTES_MIN = ParagonIE_Compat::CRYPTO_GENERICHASH_KEYBYTES_MIN;
        public const CRYPTO_GENERICHASH_KEYBYTES_MAX = ParagonIE_Compat::CRYPTO_GENERICHASH_KEYBYTES_MAX;
        public const CRYPTO_SCALARMULT_BYTES = ParagonIE_Compat::CRYPTO_SCALARMULT_BYTES;
        public const CRYPTO_SCALARMULT_SCALARBYTES = ParagonIE_Compat::CRYPTO_SCALARMULT_SCALARBYTES;
        public const CRYPTO_SHORTHASH_BYTES = ParagonIE_Compat::CRYPTO_SHORTHASH_BYTES;
        public const CRYPTO_SHORTHASH_KEYBYTES = ParagonIE_Compat::CRYPTO_SHORTHASH_KEYBYTES;
        public const CRYPTO_SECRETBOX_KEYBYTES = ParagonIE_Compat::CRYPTO_SECRETBOX_KEYBYTES;
        public const CRYPTO_SECRETBOX_MACBYTES = ParagonIE_Compat::CRYPTO_SECRETBOX_MACBYTES;
        public const CRYPTO_SECRETBOX_NONCEBYTES = ParagonIE_Compat::CRYPTO_SECRETBOX_NONCEBYTES;
        public const CRYPTO_SIGN_BYTES = ParagonIE_Compat::CRYPTO_SIGN_BYTES;
        public const CRYPTO_SIGN_SEEDBYTES = ParagonIE_Compat::CRYPTO_SIGN_SEEDBYTES;
        public const CRYPTO_SIGN_PUBLICKEYBYTES = ParagonIE_Compat::CRYPTO_SIGN_PUBLICKEYBYTES;
        public const CRYPTO_SIGN_SECRETKEYBYTES = ParagonIE_Compat::CRYPTO_SIGN_SECRETKEYBYTES;
        public const CRYPTO_SIGN_KEYPAIRBYTES = ParagonIE_Compat::CRYPTO_SIGN_KEYPAIRBYTES;
        public const CRYPTO_STREAM_KEYBYTES = ParagonIE_Compat::CRYPTO_STREAM_KEYBYTES;
        public const CRYPTO_STREAM_NONCEBYTES = ParagonIE_Compat::CRYPTO_STREAM_NONCEBYTES;
    }
}else{die;}