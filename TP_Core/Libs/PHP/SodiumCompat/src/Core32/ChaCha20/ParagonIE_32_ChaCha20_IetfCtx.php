<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-11-2022
 * Time: 11:03
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core32\ChaCha20;
use TP_Core\Libs\PHP\SodiumCompat\src\Core32\ParagonIE_32_Int32;
use TP_Core\Libs\PHP\SodiumCompat\lib\SodiumException;
use InvalidArgumentException;
use TypeError;
if(ABSPATH){
    class ParagonIE_32_ChaCha20_IetfCtx extends ParagonIE_32_ChaCha20_Ctx{
        /**
         * ParagonIE_Sodium_Core_ChaCha20_IetfCtx constructor.
         *
         * @internal You should not use this directly from another application
         *
         * @param string $key     ChaCha20 key.
         * @param string $iv      Initialization Vector (a.k.a. nonce).
         * @param string $counter The initial counter value.
         *                        Defaults to 4 0x00 bytes.
         * @throws InvalidArgumentException
         * @throws SodiumException
         * @throws TypeError
         */
        public function __construct($key = '', $iv = '', $counter = '')
        {
            if (strlen($iv) !== 12) {
                throw new InvalidArgumentException('ChaCha20 expects a 96-bit nonce in IETF mode.');
            }
            parent::__construct($key, substr($iv, 0, 8), $counter);

            if (!empty($counter)) {
                $this->container[12] = ParagonIE_32_Int32::fromReverseString(substr($counter, 0, 4));
            }
            $this->container[13] = ParagonIE_32_Int32::fromReverseString(substr($iv, 0, 4));
            $this->container[14] = ParagonIE_32_Int32::fromReverseString(substr($iv, 4, 4));
            $this->container[15] = ParagonIE_32_Int32::fromReverseString(substr($iv, 8, 4));
        }
    }
}else{die;}