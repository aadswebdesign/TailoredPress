<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-8-2022
 * Time: 14:22
 */
declare(strict_types=1);
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\IXR\IXR_Server;
if(ABSPATH){
    trait _init_ixr{
        protected $_tp_ixr_server;
        protected function _init_ixr_server($callbacks = false, $data = false, $wait = false ): IXR_Server{
            if(!($this->_tp_ixr_server instanceof IXR_Server))
                $this->_tp_ixr_server = new IXR_Server($callbacks, $data, $wait );
            return $this->_tp_ixr_server;
        }
    }
}else{die;}