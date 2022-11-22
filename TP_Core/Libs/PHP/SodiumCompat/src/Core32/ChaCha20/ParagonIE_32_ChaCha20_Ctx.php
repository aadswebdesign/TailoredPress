<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-11-2022
 * Time: 10:58
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core32\ChaCha20;
use TP_Core\Libs\PHP\SodiumCompat\lib\SodiumException;
use TP_Core\Libs\PHP\SodiumCompat\src\Core32\ParagonIE_32_Util;
use TP_Core\Libs\PHP\SodiumCompat\src\Core32\ParagonIE_32_Int32;
use InvalidArgumentException;
use SplFixedArray;
use TypeError;
if(ABSPATH){
    class ParagonIE_32_ChaCha20_Ctx extends ParagonIE_32_Util implements \ArrayAccess {
        /**
         * @var SplFixedArray internally, <int, ParagonIE_Sodium_Core32_Int32>
         */
        protected $container;

        /**
         * ParagonIE_Sodium_Core_ChaCha20_Ctx constructor.
         *
         * @internal You should not use this directly from another application
         *
         * @param string $key     ChaCha20 key.
         * @param string $iv      Initialization Vector (a.k.a. nonce).
         * @param string $counter The initial counter value.
         *                        Defaults to 8 0x00 bytes.
         * @throws InvalidArgumentException
         * @throws SodiumException
         * @throws TypeError
         */
        public function __construct($key = '', $iv = '', $counter = '')
        {
            if (strlen($key) !== 32) {
                throw new InvalidArgumentException('ChaCha20 expects a 256-bit key.');
            }
            if (strlen($iv) !== 8) {
                throw new InvalidArgumentException('ChaCha20 expects a 64-bit nonce.');
            }
            $this->container = new SplFixedArray(16);

            /* "expand 32-byte k" as per ChaCha20 spec */
            $this->container[0]  = new ParagonIE_32_Int32(array(0x6170, 0x7865));
            $this->container[1]  = new ParagonIE_32_Int32(array(0x3320, 0x646e));
            $this->container[2]  = new ParagonIE_32_Int32(array(0x7962, 0x2d32));
            $this->container[3]  = new ParagonIE_32_Int32(array(0x6b20, 0x6574));

            $this->container[4]  = ParagonIE_32_Int32::fromReverseString(substr($key, 0, 4));
            $this->container[5]  = ParagonIE_32_Int32::fromReverseString(substr($key, 4, 4));
            $this->container[6]  = ParagonIE_32_Int32::fromReverseString(substr($key, 8, 4));
            $this->container[7]  = ParagonIE_32_Int32::fromReverseString(substr($key, 12, 4));
            $this->container[8]  = ParagonIE_32_Int32::fromReverseString(substr($key, 16, 4));
            $this->container[9]  = ParagonIE_32_Int32::fromReverseString(substr($key, 20, 4));
            $this->container[10] = ParagonIE_32_Int32::fromReverseString(substr($key, 24, 4));
            $this->container[11] = ParagonIE_32_Int32::fromReverseString(substr($key, 28, 4));

            if (empty($counter)) {
                $this->container[12] = new ParagonIE_32_Int32();
                $this->container[13] = new ParagonIE_32_Int32();
            } else {
                $this->container[12] = ParagonIE_32_Int32::fromReverseString(substr($counter, 0, 4));
                $this->container[13] = ParagonIE_32_Int32::fromReverseString(substr($counter, 4, 4));
            }
            $this->container[14] = ParagonIE_32_Int32::fromReverseString(substr($iv, 0, 4));
            $this->container[15] = ParagonIE_32_Int32::fromReverseString(substr($iv, 4, 4));
        }

        /**
         * @internal You should not use this directly from another application
         *
         * @param int $offset
         * @param int|ParagonIE_32_Int32 $value
         * @return void
         */
        #[ReturnTypeWillChange]
        public function offsetSet($offset, $value):void
        {
            if (!is_int($offset)) {
                throw new InvalidArgumentException('Expected an integer');
            }
            if ($value instanceof ParagonIE_32_Int32) {
            } else {
                throw new InvalidArgumentException('Expected an integer');
            }
            $this->container[$offset] = $value;
        }

        /**
         * @internal You should not use this directly from another application
         *
         * @param int $offset
         * @return bool
         * @psalm-suppress MixedArrayOffset
         */
        #[ReturnTypeWillChange]
        public function offsetExists($offset):bool
        {
            return isset($this->container[$offset]);
        }

        /**
         * @internal You should not use this directly from another application
         *
         * @param int $offset
         * @return void
         * @psalm-suppress MixedArrayOffset
         */
        #[ReturnTypeWillChange]
        public function offsetUnset($offset):void
        {
            unset($this->container[$offset]);
        }

        /**
         * @internal You should not use this directly from another application
         *
         * @param int $offset
         * @return mixed|null
         * @psalm-suppress MixedArrayOffset
         */
        #[ReturnTypeWillChange]
        public function offsetGet($offset)
        {
            return  $this->container[$offset] ?? null;
        }
    }
}else{die;}