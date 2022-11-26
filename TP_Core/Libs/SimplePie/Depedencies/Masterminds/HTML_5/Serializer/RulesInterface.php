<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-3-2022
 * Time: 11:49
 */
namespace TP_Core\Libs\SimplePie\Depedencies\Masterminds\HTML_5\Serializer;
if(ABSPATH){
    interface RulesInterface{
        public function __construct($output, $options = array());
        public function setTraverser(Traverser $traverser);
        public function document($dom);
        public function element($ele);
        public function text($ele);
        public function cdata($ele);
        public function comment($ele);
        public function processorInstruction($ele);
    }
}else die;