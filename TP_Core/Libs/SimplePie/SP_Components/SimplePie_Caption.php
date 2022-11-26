<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 11:42
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_Caption{
        use _sp_vars;
        public function __construct($type = null, $lang = null, $startTime = null, $endTime = null, $text = null){
            $this->sp_type = $type;
            $this->sp_lang = $lang;
            //todo maybe to edit this
            $this->sp_time['start'] = $startTime;
            $this->sp_time['end'] = $endTime;
            $this->sp_text = $text;
        }
        public function __toString(){
            return (string)md5(serialize($this));
        }
        public function get_end_time(){
            if ($this->sp_time['end'] !== null) return $this->sp_time['end'];
            return null;
        }
        public function get_language(){
            if ($this->sp_lang !== null) return $this->sp_lang;
            return null;
        }
        public function get_start_time(){
            if ($this->sp_time['start'] !== null) return $this->sp_time['start'];
            return null;
        }
        public function get_text(){
            if ($this->sp_text !== null) return $this->sp_text;
            return null;
        }
        public function get_type(){
            if ($this->sp_type !== null) return $this->sp_type;
            return null;
        }
    }
}else die;