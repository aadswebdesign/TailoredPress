<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 19:09
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Promise;
if(ABSPATH){
    class RejectionException extends \RuntimeException{
        private $__reason;
        public function __construct($reason, $description = null){
            $this->__reason = $reason;
            $message = 'The promise was rejected';
            if ($description) {
                $message .= ' with reason: ' . $description;
            } elseif (is_string($reason) && method_exists($reason, '__toString'))
            {$message .= ' with reason: ' . $this->__reason;}
            elseif ($reason instanceof \JsonSerializable) {
                $message .= ' with reason: ' . json_encode($this->__reason, JSON_PRETTY_PRINT);
            }
            parent::__construct($message);
        }
        public function getReason():string{
            return $this->__reason;
        }
    }
}else{die;}