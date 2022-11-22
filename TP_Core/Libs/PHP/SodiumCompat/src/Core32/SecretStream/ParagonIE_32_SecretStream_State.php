<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-11-2022
 * Time: 18:52
 */
namespace TP_Core\Libs\PHP\SodiumCompat\src\Core32\SecretStream;
use TP_Core\Libs\PHP\SodiumCompat\src\Core32\ParagonIE_32_Util;
if(ABSPATH){
    class ParagonIE_32_SecretStream_State{
        /** @var string $key */
        protected $key;

        /** @var int $counter */
        protected $counter;

        /** @var string $nonce */
        protected $nonce;

        /** @var string $_pad */
        protected $_pad;

        /**
         * ParagonIE_Sodium_Core32_SecretStream_State constructor.
         * @param string $key
         * @param string|null $nonce
         */
        public function __construct($key, $nonce = null)
        {
            $this->key = $key;
            $this->counter = 1;
            if (is_null($nonce)) {
                $nonce = str_repeat("\0", 12);
            }
            $this->nonce = str_pad($nonce, 12, "\0", STR_PAD_RIGHT);
            $this->_pad = str_repeat("\0", 4);
        }

        /**
         * @return self
         */
        public function counterReset():self
        {
            $this->counter = 1;
            $this->_pad = str_repeat("\0", 4);
            return $this;
        }

        /**
         * @return string
         */
        public function getKey():string
        {
            return $this->key;
        }

        /**
         * @return string
         */
        public function getCounter():string
        {
            return ParagonIE_32_Util::store32_le($this->counter);
        }

        /**
         * @return string
         */
        public function getNonce():string
        {
            if (!is_string($this->nonce)) {
                $this->nonce = str_repeat("\0", 12);
            }
            if (ParagonIE_32_Util::strlen($this->nonce) !== 12) {
                $this->nonce = str_pad($this->nonce, 12, "\0", STR_PAD_RIGHT);
            }
            return $this->nonce;
        }

        /**
         * @return string
         */
        public function getCombinedNonce():string
        {
            return $this->getCounter() .
            ParagonIE_32_Util::substr($this->getNonce(), 0, 8);
        }

        /**
         * @return self
         */
        public function incrementCounter():self
        {
            ++$this->counter;
            return $this;
        }

        /**
         * @return bool
         */
        public function needsRekey():bool
        {
            return ($this->counter & 0xffff) === 0;
        }

        /**
         * @param string $newKeyAndNonce
         * @return self
         */
        public function rekey($newKeyAndNonce):self
        {
            $this->key = ParagonIE_32_Util::substr($newKeyAndNonce, 0, 32);
            $this->nonce = str_pad(
                ParagonIE_32_Util::substr($newKeyAndNonce, 32),
                12,
                "\0",
                STR_PAD_RIGHT
            );
            return $this;
        }

        /**
         * @param string $str
         * @return self
         */
        public function xorNonce($str):self
        {
            $this->nonce = ParagonIE_32_Util::xorStrings(
                $this->getNonce(),
                str_pad(
                    ParagonIE_32_Util::substr($str, 0, 8),
                    12,
                    "\0",
                    STR_PAD_RIGHT
                )
            );
            return $this;
        }

        /**
         * @param string $string
         * @return self
         */
        public static function fromString($string):self
        {
            $state = new ParagonIE_32_SecretStream_State(
                ParagonIE_32_Util::substr($string, 0, 32)
            );
            $state->counter = ParagonIE_32_Util::load_4(
                ParagonIE_32_Util::substr($string, 32, 4)
            );
            $state->nonce = ParagonIE_32_Util::substr($string, 36, 12);
            $state->_pad = ParagonIE_32_Util::substr($string, 48, 8);
            return $state;
        }

        /**
         * @return string
         */
        public function toString():string
        {
            return $this->key .
            $this->getCounter() .
            $this->nonce .
            $this->_pad;
        }
    }
}else{die;}