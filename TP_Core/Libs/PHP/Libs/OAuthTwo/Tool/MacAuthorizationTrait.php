<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 25-4-2022
 * Time: 04:50
 */
namespace TP_Core\Libs\PHP\Libs\OAuthTwo\Tool;
use TP_Core\Libs\PHP\Libs\OAuthTwo\Token\AccessToken;
//use TP_Core\Libs\PHP\Libs\OAuthTwo\Token\AccessTokenInterface;
if(ABSPATH){
    trait MacAuthorizationTrait{
        abstract protected function _getTokenId(AccessToken $token);
        abstract protected function _getMacSignature($id, $ts, $nonce);
        abstract protected function _getRandomState($length = 32);
        protected function _getAuthorizationHeaders($token = null): array
        {
            if ($token === null) return [];
            $ts    = time();
            $id    = $this->_getTokenId($token);
            $nonce = $this->_getRandomState(16);
            $mac   = $this->_getMacSignature($id, $ts, $nonce);
            $parts = [];
            foreach (compact('id', 'ts', 'nonce', 'mac') as $key => $value) {
                $parts[] = sprintf('%s="%s"', $key, $value);
            }
            return ['Authorization' => 'MAC ' . implode(', ', $parts)];
        }
    }
}else die;