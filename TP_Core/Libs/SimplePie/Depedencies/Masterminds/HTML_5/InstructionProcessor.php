<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 12:25
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5;
if(ABSPATH){
    interface InstructionProcessor{
        public function process(\DOMElement $element, $name, $data);
    }
}else die;