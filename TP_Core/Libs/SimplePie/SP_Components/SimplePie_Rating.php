<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 12:28
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Rating{
        use _sp_vars;
        public function __construct($scheme = null, $value = null){
            $this->sp_scheme = $scheme;
            $this->sp_value = $value;
        }
        /**
         * Stringified version
         * @return string
         */
        public function __toString(){
            // There is no $this->data here
            return (string)md5(serialize($this));
        }
        /**
         * Get the organizational scheme for the rating
         * @return string|null
         */
        public function get_scheme():?string {
            if ($this->sp_scheme !== null) return $this->sp_scheme;
            return null;
        }
        /**
         * Get the value of the rating
         * @return string|null
         */
        public function get_value():?string{
            if ($this->sp_value !== null) return $this->sp_value;
            return null;
        }
    }
}else die;