<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-11-2022
 * Time: 11:23
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core\Curve25519;
if(ABSPATH){
    class ParagonIE_Curve25519_Fe implements \ArrayAccess {
        /**
         * @var array<int, int>
         */
        protected $container = array();

        /**
         * @var int
         */
        protected $size = 10;

        /**
         * @internal You should not use this directly from another application
         *
         * @param array<int, int> $array
         * @param bool $save_indexes
         * @return self
         */
        public static function fromArray($array, $save_indexes = null):self
        {
            $count = count($array);
            if ($save_indexes) {
                $keys = array_keys($array);
            } else {
                $keys = range(0, $count - 1);
            }
            $array = array_values($array);
            /** @var array<int, int> $keys */

            $obj = new self();
            if ($save_indexes) {
                for ($i = 0; $i < $count; ++$i) {
                    $obj->offsetSet($keys[$i], $array[$i]);
                }
            } else {
                foreach ($array as $i => $iValue) {
                    $obj->offsetSet($i, $iValue);
                }
            }
            return $obj;
        }

        /**
         * @internal You should not use this directly from another application
         *
         * @param int|null $offset
         * @param int $value
         * @return void
         * @psalm-suppress MixedArrayOffset
         */
        #[ReturnTypeWillChange]
        public function offsetSet($offset, $value):void
        {
            if (!is_int($value)) {
                throw new \InvalidArgumentException('Expected an integer');
            }
            if (is_null($offset)) {
                $this->container[] = $value;
            } else {
                $this->container[$offset] = $value;
            }
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
         * @return int
         * @psalm-suppress MixedArrayOffset
         */
        #[ReturnTypeWillChange]
        public function offsetGet($offset):int
        {
            if (!isset($this->container[$offset])) {
                $this->container[$offset] = 0;
            }
            return (int) ($this->container[$offset]);
        }

        /**
         * @internal You should not use this directly from another application
         *
         * @return array
         */
        public function __debugInfo()
        {
            return array(implode(', ', $this->container));
        }
    }
}else{die;}