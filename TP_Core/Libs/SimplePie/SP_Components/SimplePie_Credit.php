<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 11:00
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Credit{
        use _sp_vars;
        public function __construct($role = null, $scheme = null, $name = null){
            $this->sp_role = $role;
            $this->sp_scheme = $scheme;
            $this->sp_name = $name;
        }
        public function __toString(){
            // There is no $this->data here
            return (string)md5(serialize($this));
        }
        public function get_role(){
            if ($this->sp_role !== null) return $this->sp_role;
            return null;
        }
        public function get_scheme(){
            if ($this->sp_scheme !== null) return $this->sp_scheme;
            return null;
        }
        public function get_name(){
            if ($this->sp_name !== null) return $this->sp_name;
            return null;
        }
    }
}else die;