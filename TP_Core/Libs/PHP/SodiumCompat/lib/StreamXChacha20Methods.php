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
    trait StreamXChacha20Methods{
        protected function _sd_crypto_stream_xchacha20($len, $nonce, $key):string{
            if (!is_callable('sodium_crypto_stream_xchacha20')) {
                function sodium_crypto_stream_xchacha20($len, $nonce, $key){
                    return ParagonIE_Compat::crypto_stream_xchacha20($len, $nonce, $key, true);
                }
            }
            return sodium_crypto_stream_xchacha20($len, $nonce, $key);
        }// stream-xchacha20.php
        protected function _sd_crypto_stream_xchacha20_keygen():string{
            if (!is_callable('sodium_crypto_stream_xchacha20_keygen')) {
                function sodium_crypto_stream_xchacha20_keygen(){
                    return ParagonIE_Compat::crypto_stream_xchacha20_keygen();
                }
            }
            return sodium_crypto_stream_xchacha20_keygen();
        }// stream-xchacha20.php
        protected function _sd_crypto_stream_xchacha20_xor($message, $nonce, $key):string{
            if (!is_callable('sodium_crypto_stream_xchacha20_xor')) {
                function sodium_crypto_stream_xchacha20_xor($message, $nonce, $key){
                    return ParagonIE_Compat::crypto_stream_xchacha20_xor($message, $nonce, $key, true);
                }
            }
            return sodium_crypto_stream_xchacha20_xor($message, $nonce, $key);
        }//39 stream-xchacha20.php
    }
}else{die;}