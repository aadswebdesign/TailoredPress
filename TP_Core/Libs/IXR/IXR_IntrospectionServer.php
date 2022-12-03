<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 15:26
 */
namespace TP_Core\Libs\IXR;
if(ABSPATH){
    class IXR_IntrospectionServer extends IXR_Server{
        public $signatures;
        public $help;
        public function __construct(){
            parent::__construct();
            $this->setCallbacks();
            $this->setCapabilities();
            $this->capabilities['introspection'] = array(
                'specUrl' => 'http://xmlrpc.usefulinc.com/doc/reserved.html',
                'specVersion' => 1
            );
            $this->addCallback(
                'system.methodSignature',
                'this:methodSignature',
                array('array', 'string'),
                'Returns an array describing the return type and required parameters of a method'
            );
            $this->addCallback(
                'system.getCapabilities',
                'this:getCapabilities',
                array('struct'),
                'Returns a struct describing the XML-RPC specifications supported by this server'
            );
            $this->addCallback(
                'system.listMethods',
                'this:listMethods',
                array('array'),
                'Returns an array of available methods on this server'
            );
            $this->addCallback(
                'system.methodHelp',
                'this:methodHelp',
                array('string', 'string'),
                'Returns a documentation string for the specified method'
            );
        }
        public function addCallback($method, $callback, $help, ...$args): void{
            $this->callbacks[$method] = $callback;
            $this->signatures[$method] = $args;
            $this->help[$method] = $help;
        }
        public function call($methodname, $args) {
            if ($args && !is_array($args)) $args = [$args];
            if (!$this->hasMethod($methodname))
                return new IXR_Error(-32601, 'server error. requested method "'.$this->message->methodName.'" not specified.');
            $method = $this->callbacks[$methodname];
            $signature = $this->signatures[$method];
            $returnType = array_shift($signature);
            if (count($args) !== count($returnType))
                return new IXR_Error(-32602, 'server error. wrong number of method parameters');
            $ok = true;
            $argsbackup = $args;
            for ($i = 0, $j = count($args); $i < $j; $i++) {
                $arg = array_shift($args);
                $type = array_shift($signature);
                switch ($type) {
                    case 'int':
                    case 'i4':
                        if (is_array($arg) || !is_int($arg)) {
                            $ok = false;
                        }
                        break;
                    case 'base64':
                    case 'string':
                        if (!is_string($arg)) {
                            $ok = false;
                        }
                        break;
                    case 'boolean':
                        if ($arg !== false && $arg !== true) {
                            $ok = false;
                        }
                        break;
                    case 'float':
                    case 'double':
                        if (!is_float($arg)) $ok = false;
                        break;
                    case 'date':
                    case 'dateTime.iso8601':
                        if (!is_a($arg, 'IXR_Date')) $ok = false;
                        break;
                }
                if (!$ok) return new IXR_Error(-32602, 'server error. invalid method parameters');
            }
            return parent::call($methodname, $argsbackup);
        }
        public function methodSignature($method): mixed{
            if (!$this->hasMethod($method))
                return new IXR_Error(-32601, 'server error. requested method "'.$method.'" not specified.');
            $types = $this->signatures[$method];
            $return = array();
            foreach ($types as $type) {
                switch ($type) {
                    case 'string':
                        $return[] = 'string';
                        break;
                    case 'int':
                    case 'i4':
                        $return[] = 42;
                        break;
                    case 'double':
                        $return[] = 3.1415;
                        break;
                    case 'dateTime.iso8601':
                        $return[] = new IXR_Date(time());
                        break;
                    case 'boolean':
                        $return[] = true;
                        break;
                    case 'base64':
                        $return[] = new IXR_Base64('base64');
                        break;
                    case 'array':
                        $return[] = array('array');
                        break;
                    case 'struct':
                        $return[] = array('struct' => 'struct');
                        break;
                }
            }
            return $return;
        }
        public function methodHelp($method){
            return $this->help[$method];
        }
    }
}else{die;}