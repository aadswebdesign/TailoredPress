<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 14:49
 */
namespace TP_Core\Libs\Request\Utility;
use TP_Core\Libs\Request\Exception\Requests_Exception;
if(ABSPATH){
    class Requests_Utility_CaseInsensitiveDictionary{
        protected $_data = array();
        public function __construct(array $data = array()) {
            foreach ($data as $key => $value)
                $this->offsetSet($key, $value);
        }
        public function offsetExists($key): bool{
            $key = strtolower($key);
            return isset($this->_data[$key]);
        }
        public function offsetGet($key) {
            $key = strtolower($key);
            if (!isset($this->_data[$key]))
                return null;
            return $this->_data[$key];
        }
        public function offsetSet($key, $value):bool {
            if ($key === null)
                throw new Requests_Exception('Object is a dictionary, not a list', 'invalidset');
            $key              = strtolower($key);
            $this->_data[$key] = $value;
        }
        public function offsetUnset($key): void
        {
            unset($this->_data[strtolower($key)]);
        }
        public function getIterator(): \ArrayIterator {
            return new \ArrayIterator($this->_data);
        }
        public function getAll(): array{
            return $this->_data;
        }
    }
}else die;