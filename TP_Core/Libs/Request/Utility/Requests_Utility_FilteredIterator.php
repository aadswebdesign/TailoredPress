<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 14:57
 */
namespace TP_Core\Libs\Request\Utility;
if(ABSPATH){
    class Requests_Utility_FilteredIterator extends \ArrayIterator{
        protected $_callback;
        public function __construct($data, $callback) {
            parent::__construct($data);
            $this->_callback = $callback;
        }
        public function current() {
            $value = parent::current();
            if (is_callable((string)$this->_callback))
                $value = call_user_func((string)$this->_callback, $value);
            return $value;
        }
        public function unserialize($serialized) {}//todo
        public function __unserialize($serialized): void{}//todo
        public function __wake_up(): void{
            unset($this->callback);
        }
    }
}else die;