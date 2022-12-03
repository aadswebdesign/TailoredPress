<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 11:48
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Category{
        use _sp_vars;
        public function __construct($term = null, $scheme = null, $label = null, $type = null){
            $this->sp_term = $term;
            $this->sp_scheme = $scheme;
            $this->sp_label = $label;
            $this->sp_type = $type;
        }
        public function __toString(){
            // There is no $this->data here
            return (string)md5(serialize($this));
        }
        public function get_term(){
            return $this->sp_term;
        }
        public function get_scheme(){
            return $this->sp_scheme;
        }
        public function get_label($strict = false){
            if ($this->sp_label === null && $strict !== true)
                return $this->get_term();
            return $this->sp_label;
        }
        public function get_type()        {
            return $this->sp_type;
        }
    }
}else die;