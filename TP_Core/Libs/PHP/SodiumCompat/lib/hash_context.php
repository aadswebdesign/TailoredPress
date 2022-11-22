<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-11-2022
 * Time: 15:44
 */
namespace TP_Core\Libs\PHP\SodiumCompat\lib;
if(ABSPATH){
    class hash_context{
        private function __construct(){}
        public function __serialize(): array{}
        public function __unserialize(array $data): void{}
    }
}else{die;}

