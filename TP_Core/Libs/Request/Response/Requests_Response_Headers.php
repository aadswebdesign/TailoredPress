<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 15:03
 */
namespace TP_Core\Libs\Request\Response;
use TP_Core\Libs\Request\Utility\Requests_Utility_CaseInsensitiveDictionary;
use TP_Core\Libs\Request\Utility\Requests_Utility_FilteredIterator;
use TP_Core\Libs\Request\Exception\Requests_Exception;
if(ABSPATH){
    class Requests_Response_Headers extends Requests_Utility_CaseInsensitiveDictionary{
        public function offsetGet($key) {
            $key = strtolower($key);
            if (!isset($this->_data[$key]))return null;
            return $this->flatten($this->_data[$key]);
        }
        public function offsetSet($key, $value):bool {
            if ($key === null)
                throw new Requests_Exception('Object is a dictionary, not a list', 'invalidset');
            $key = strtolower($key);
            if (!isset($this->_data[$key])) $this->_data[$key] = array();
            $this->_data[$key][] = $value;
        }
        public function getValues($key) {
            $key = strtolower($key);
            if (!isset($this->_data[$key])) return null;
            return $this->_data[$key];
        }
        public function flatten($value) {
            if (is_array($value)) $value = implode(',', $value);
            return $value;
        }
        public function getIterator(): \ArrayIterator {
            return new Requests_Utility_FilteredIterator($this->_data,(string)[$this, 'flatten']);
        }
    }
}else die;