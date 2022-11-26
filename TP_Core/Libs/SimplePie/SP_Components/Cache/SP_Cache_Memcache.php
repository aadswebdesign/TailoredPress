<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 10:24
 */
namespace TP_Core\Libs\SimplePie\SP_Components\Cache;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_Cache;
use TP_Core\Libs\SimplePie\SimplePie;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\_encodings;
if(ABSPATH){
    class SP_Cache_Memcache implements SP_Cache_Base{
        use _sp_vars;
        use _encodings;
        public function __construct($location, $name, $type){
            $this->_sp_options = array(
                'host' => '127.0.0.1',//todo host address might has to be changed
                'port' => 11211,
                'extras' => array(
                    'timeout' => 3600, // one hour
                    'prefix' => 'simple_pie_',
                ),
            );
            $this->_sp_options = $this->sp_array_merge_recursive($this->_sp_options, SimplePie_Cache::parse_URL($location));
            $this->_sp_name = $this->_sp_options['extras']['prefix'] . md5("$name:$type");
            $this->_sp_cache = new \Memcache();
            $this->_sp_cache->addServer($this->_sp_options['host'], (int) $this->_sp_options['port']);
        }
        public function save($data):bool{
            if ($data instanceof SimplePie) $data = '';//todo $data->sp_data;
            return $this->_sp_cache->set($this->_sp_name, serialize($data), MEMCACHE_COMPRESSED, (int) $this->_sp_options['extras']['timeout']);
        }
        public function load(){
            $data = $this->_sp_cache->get($this->_sp_name);
            if ($data !== false){
                /** @noinspection UnserializeExploitsInspection *///todo important
                return unserialize($data);
            }
            return false;
        }
        public function micro_time(){
            $data = $this->_sp_cache->get($this->_sp_name);
            if ($data !== false) return time();
            return false;
        }
        public function touch():bool{
            $data = $this->_sp_cache->get($this->_sp_name);
            if ($data !== false) return $this->_sp_cache->set($this->_sp_name, $data, MEMCACHE_COMPRESSED, (int) $this->_sp_options['extras']['timeout']);
            return false;
        }
        public function unlink():bool{
            return $this->_sp_cache->delete($this->_sp_name, 0);
        }
    }
}else die;