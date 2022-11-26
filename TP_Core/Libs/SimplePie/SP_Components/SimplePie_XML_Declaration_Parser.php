<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-3-2022
 * Time: 04:37
 */
namespace TP_Core\Libs\SimplePie\SP_Components;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SimplePie_XML_Declaration_Parser{
        use _sp_vars;
        public function __construct($data){
            $this->__sp_data = $data;
            $this->__sp_data_length = strlen($this->__sp_data);
        }
        /**
         * Parse the input data
         * @access public
         * @return bool true on success, false on failure
         */
        public function parse():bool{
            while ($this->__sp_state && $this->__sp_state !== 'emit' && $this->has_data()){
                $state = $this->__sp_state;
                $this->$state();
            }
            $this->__sp_data = '';
            if ($this->__sp_state === 'emit') return true;
            $this->sp_version = '';
            $this->sp_encoding = '';
            $this->sp_standalone = '';
            return false;
        }
        /**
         * Check whether there is data beyond the pointer
         * @access private
         * @return bool true if there is further data, false if not
         */
        public function has_data():bool{
            return ($this->__sp_position < $this->__sp_data_length);
        }
        /**
         * Advance past any whitespace
         * @return int Number of whitespace characters passed
         */
        public function skip_whitespace():int{
            $whitespace = strspn($this->__sp_data, "\x09\x0A\x0D\x20", $this->__sp_position);
            $this->__sp_position += $whitespace;
            return $whitespace;
        }
        /**
         * Read value
         */
        public function get_value(){
            $quote = substr($this->__sp_data, $this->__sp_position, 1);
            if ($quote === '"' || $quote === "'"){
                $this->__sp_position++;
                $len = strcspn($this->__sp_data, $quote, $this->__sp_position);
                if ($this->has_data()){
                    $value = substr($this->__sp_data, $this->__sp_position, $len);
                    $this->__sp_position += $len + 1;
                    return $value;
                }
            }
            return false;
        }
        public function before_version_name():void{
            if ($this->skip_whitespace())
                $this->__sp_state = 'version_name';
            else $this->__sp_state = false;
        }
        public function version_name():void{
            if (substr($this->__sp_data, $this->__sp_position, 7) === 'version'){
                $this->__sp_position += 7;
                $this->skip_whitespace();
                $this->__sp_state = 'version_equals';
            } else $this->__sp_state = false;
        }
        public function version_equals():void{
            if (substr($this->__sp_data, $this->__sp_position, 1) === '='){
                $this->__sp_position++;
                $this->skip_whitespace();
                $this->__sp_state = 'version_value';
            } else $this->__sp_state = false;
        }
        public function version_value():void{
            if ($this->sp_version = $this->get_value()) {
                $this->skip_whitespace();
                if ($this->has_data()) $this->__sp_state = 'encoding_name';
                else $this->__sp_state = 'emit';
            } else $this->__sp_state = false;
        }
        public function encoding_name():void{
            if (substr($this->__sp_data, $this->__sp_position, 8) === 'encoding'){
                $this->__sp_position += 8;
                $this->skip_whitespace();
                $this->__sp_state = 'encoding_equals';
            }
            else $this->__sp_state = 'standalone_name';
        }
        public function encoding_equals():void{
            if (substr($this->__sp_data, $this->__sp_position, 1) === '='){
                $this->__sp_position++;
                $this->skip_whitespace();
                $this->__sp_state = 'encoding_value';
            } else $this->__sp_state = false;
        }
        public function encoding_value():void{
            if ($this->sp_encoding = $this->get_value()){
                $this->skip_whitespace();
                if ($this->has_data()) $this->__sp_state = 'standalone_name';
                else $this->__sp_state = 'emit';
            } else $this->__sp_state = false;
        }
        public function standalone_name():void{
            if (substr($this->__sp_data, $this->__sp_position, 10) === 'standalone'){
                $this->__sp_position += 10;
                $this->skip_whitespace();
                $this->__sp_state = 'standalone_equals';
            } else $this->__sp_state = false;
        }
        public function standalone_equals():void{
            if (substr($this->__sp_data, $this->__sp_position, 1) === '='){
                $this->__sp_position++;
                $this->skip_whitespace();
                $this->__sp_state = 'standalone_value';
            } else $this->__sp_state = false;
        }
        public function standalone_value():void{
            if ($standalone = $this->get_value()){
                switch ($standalone){
                    case 'yes':
                        $this->sp_standalone = true;
                        break;
                    case 'no':
                        $this->sp_standalone = false;
                        break;
                    default:
                        $this->__sp_state = false;
                        return;
                }
                $this->skip_whitespace();
                if ($this->has_data()) $this->__sp_state = false;
                else $this->__sp_state = 'emit';
            }
            else $this->__sp_state = false;
        }

    }
}else die;