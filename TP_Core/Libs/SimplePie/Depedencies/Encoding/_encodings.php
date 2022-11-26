<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 21-4-2022
 * Time: 04:42
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Encoding;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\Factory\_encoding_01;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\Factory\_encoding_02;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\Factory\_encoding_03;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\Factory\_encoding_04;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\Factory\_encoding_05;
if(ABSPATH){
    trait _encodings{
        use _encoding_01,_encoding_02,_encoding_03,_encoding_04,_encoding_05;
    }
}else die;