<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-3-2022
 * Time: 12:26
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Restriction{
        use _sp_vars;
        public function __construct($relationship = null, $type = null, $value = null){
            $this->sp_relationship = $relationship;
            $this->sp_type = $type;
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
         * Get the relationship
         * @return string|null Either 'allow' or 'deny'
         */
        public function get_relationship():?string{
            if ($this->sp_relationship !== null) return $this->sp_relationship;
            return null;
        }
        /**
         * Get the type
         * @return string|null
         */
        public function get_type():?string{
            if ($this->sp_type !== null) return $this->sp_type;
            return null;
        }
        /**
         * Get the list of restricted things
         * @return string|null
         */
        public function get_value():?string{
            if ($this->sp_value !== null) return $this->sp_value;
            return null;
        }
    }
}else die;