<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 10:49
 */
namespace TP_Core\Libs\SimplePie\SP_Components\Cache;
use TP_Core\Libs\SimplePie\SimplePie;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_Cache;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\_encodings;
if(ABSPATH){
    class SP_Cache_Memcached implements SP_Cache_Base{
        use _sp_vars;
        use _encodings;
        public function __construct($location, $name, $type){
            $this->_sp_options = array(
                'host'   => '127.0.0.1',
                'port'   => 11211,
                'extras' => array(
                    'timeout' => 3600, // one hour
                    'prefix'  => 'simple_pie_',
                ),
            );
            $this->_sp_options = $this->sp_array_merge_recursive($this->_sp_options, SimplePie_Cache::parse_URL($location));
            $this->_sp_name = $this->_sp_options['extras']['prefix'] . md5("$name:$type");
            $this->_sp_cache = new \Memcached();
            $this->_sp_cache->addServer($this->_sp_options['host'], (int)$this->_sp_options['port']);
        }
        public function save($data){
            if ($data instanceof SimplePie) $data = '';//todo $data->sp_data;
            return $this->__set_data(serialize($data));
        }
        public function load(){
            $data = $this->_sp_cache->get($this->_sp_name);
            if ($data !== false) {
                /** @noinspection UnserializeExploitsInspection *///todo important
                return unserialize($data);
            }
            return false;
        }
        public function micro_time():int{
            $data = $this->_sp_cache->get($this->_sp_name . '_micro_time');
            return (int) $data;
        }
        public function touch(){
            $data = $this->_sp_cache->get($this->_sp_name);
            return $this->__set_data($data);
        }
        public function unlink():bool{
            return $this->_sp_cache->delete($this->_sp_name, 0);
        }
        private function __set_data($data) {
            if ($data !== false) {
                $this->_sp_cache->set($this->_sp_name . '_micro_time', time(), (int)$this->_sp_options['extras']['timeout']);
                return $this->_sp_cache->set($this->_sp_name, $data, (int)$this->_sp_options['extras']['timeout']);
            }
            return false;
        }

    }
}else die;