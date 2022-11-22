<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-4-2022
 * Time: 16:26
 */
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp;
use TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7\Message;
use TP_Core\Libs\PHP\Libs\HTTP_Message\MessageInterface;
if(ABSPATH){
    final class BodySummarizer implements BodySummarizerInterface{
        private $__truncateAt;
        public function __construct(int $truncateAt = null){
            $this->__truncateAt = $truncateAt;
        }
        public function summarize(MessageInterface $message): ?string {
            return $this->__truncateAt === null
                ? Message::bodySummary($message)
                : Message::bodySummary($message, $this->__truncateAt);
        }
    }
}else die;