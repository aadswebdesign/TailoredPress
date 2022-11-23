<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-4-2022
 * Time: 16:15
 */
namespace TP_Core\Libs\Request;
if(ABSPATH){
    class Requests_Hooks implements Requests_Hooker{
        protected $_hooks = [];
        /**
         * @param $hook
         * @param $callback
         * @param int $priority
         */
        public function register($hook, $callback, $priority = 0):void {
            if (!isset($this->_hooks[$hook]))
                $this->_hooks[$hook] = array();
            if (!isset($this->_hooks[$hook][$priority]))
                $this->_hooks[$hook][$priority] = array();
            $this->_hooks[$hook][$priority][] = $callback;
        }
        /**
         * @param $hook
         * @param $parameters
         * @return mixed
         */
        public function dispatch($hook,$parameters) {
            if (empty($this->_hooks[$hook]))
                return false;
            foreach ($this->_hooks[$hook] as $priority => $hooked) {
                foreach ($hooked as $callback)
                    call_user_func_array($callback, $parameters);
            }
            return true;
        }
    }
}else die;