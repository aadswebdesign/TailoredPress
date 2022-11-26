<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 11:35
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Author{
        use _sp_vars;
        public function __construct($name = null, $link = null, $email = null){
            $this->__sp_name = $name;
            $this->__sp_link = $link;
            $this->__sp_email = $email;
        }
        public function __toString(){
            return (string)md5(serialize($this));
        }
        public function get_name(){
            if ($this->__sp_name !== null) return $this->__sp_name;
            return null;
        }
        public function get_link(){
            if ($this->__sp_link !== null) return $this->__sp_link;
            return null;
        }
        public function get_email() {
            if ($this->__sp_email !== null) return $this->__sp_email;
            return null;
        }
    }
}else die;