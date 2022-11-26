<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 09:52
 */
namespace TP_Core\Libs\SimplePie\SP_Components\Cache;
use TP_Core\Libs\SimplePie\SimplePie;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
if(ABSPATH){
    class SP_Cache_File implements SP_Cache_Base {
        use _sp_vars;
        /**
         * _SimplePie_Cache_File constructor.
         * @description Create a new cache object
         * @param $location
         * @param $name
         * @param $type
         */
        public function __construct($location, $name, $type){
            $this->_sp_location = $location;
            $this->_sp_filename = $name;
            $this->_sp_extension = $type;
            $this->_sp_name = "$this->_sp_location/$this->_sp_filename.$this->_sp_extension";
        }
        /**
         * @description Save data to the cache
         * @param $data
         * @return bool
         */
        public function save($data):bool{
            if ((file_exists($this->_sp_name) && is_writable($this->_sp_name)) || (file_exists($this->_sp_location) && is_writable($this->_sp_location))){
                $sp_data = 'data';
                if ($data instanceof SimplePie)
                    $data = $data->$sp_data;
                $data = serialize($data);
                return (bool) file_put_contents($this->_sp_name, $data);
            }
            return false;
        }
        /**
         * @description Retrieve the data saved to the cache
         * @return bool|mixed
         */
        public function load(){
            if (file_exists($this->_sp_name) && is_readable($this->_sp_name))
                /** @noinspection UnserializeExploitsInspection *///todo important
                return unserialize(file_get_contents($this->_sp_name));
            return false;
        }
        /**
         * @description Retrieve the last modified time for the cache
         * @return int int Timestamp
         */
        public function micro_time():int{
            return @filemtime($this->_sp_name);
        }
        /**
         * @description Set the last modified time to the current time
         * @return bool Success status
         */
        public function touch():bool{
            return @touch($this->_sp_name);
        }
        /**
         * @description Remove the cache
         * @return bool Success status
         */
        public function unlink():bool{
            if (file_exists($this->_sp_name))
                return unlink($this->_sp_name);
            return false;
        }
    }
}else die;