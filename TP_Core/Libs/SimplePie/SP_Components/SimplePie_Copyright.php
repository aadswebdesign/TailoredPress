<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 11:59
 */

namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Copyright{
        use _sp_vars;
        public function __construct($url = null, $label = null){
            $this->sp_url = $url;
            $this->sp_label = $label;
        }
        public function __toString(){
            // There is no $this->data here
            return (string)md5(serialize($this));
        }
        public function get_url(){
            if ($this->sp_url !== null) return $this->sp_url;
            return null;
        }
        public function get_attribution(){
            if ($this->sp_label !== null) return $this->sp_label;
            return null;
        }
    }
}else die;