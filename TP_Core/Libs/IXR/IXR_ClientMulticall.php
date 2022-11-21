<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 7-8-2022
 * Time: 15:34
 */
namespace TP_Core\Libs\IXR;
if(ABSPATH){
    class IXR_ClientMulticall extends IXR_Client{
        public $calls = [];
        public function __construct( $server, $path = false, $port = 80 ){
            parent::__construct($server, $path, $port);
            $this->user_agent = 'The Incutio XML-RPC PHP Library (multicall client)';
        }
        public function addCall( ...$args ): void{
            $methodName = array_shift($args);
            $struct = array(
                'methodName' => $methodName,
                'params' => $args
            );
            $this->calls[] = $struct;
        }
        public function query( ...$args ): string{
            return parent::query('system.multicall', $this->calls);
        }
    }
}else{die;}