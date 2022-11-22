<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-4-2022
 * Time: 11:30
 */
declare(strict_types=1);
namespace TP_Core\Libs\PHP\Libs\GuzzleHttp\Psr7;
if(ABSPATH){
    final class Rfc7230{
        public const HEADER_REGEX = "(^([^()<>@,;:\\\"/[\]?={}\x01-\x20\x7F]++):[ \t]*+((?:[ \t]*+[\x21-\x7E\x80-\xFF]++)*+)[ \t]*+\r?\n)m";
        public const HEADER_FOLD_REGEX = "(\r?\n[ \t]++)";
    }
}else die;
