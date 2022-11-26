<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-4-2022
 * Time: 11:05
 */
namespace TP_Core\Libs\SimplePie\SP_Components\Cache;
use TP_Core\Libs\SimplePie\SimplePie;
use TP_Core\Libs\SimplePie\SP_Components\SimplePie_Cache;
use TP_Core\Libs\SimplePie\Factory\_sp_vars;
use TP_Core\Libs\SimplePie\Depedencies\Encoding\_encodings;
if(ABSPATH){
    class SP_Cache_MySQL implements SP_Cache_Base{
        use _sp_vars;
        use _encodings;
        public function __construct($location, $name, $type){
            $db = [];
            $this->_sp_options = [
                'user' => null,
                'pass' => null,
                'host' => '127.0.0.1',
                'port' => '3306',
                'path' => '',
                'extras' => ['prefix' => '','cache_purge_time' => 2592000]
            ];
            $this->_sp_options = $this->sp_array_merge_recursive($this->_sp_options, SimplePie_Cache::parse_URL($location));
            $this->_sp_options['dbname'] = substr($this->_sp_options['path'], 1);
            try{
                $this->_sp_mysql = new \PDO("mysql:dbname={$this->_sp_options['dbname']};host={$this->_sp_options['host']};port={$this->_sp_options['port']}", $this->_sp_options['user'], $this->_sp_options['pass'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
            }catch (\PDOException $e){
                $this->_sp_mysql = null;
                return;
            }
            $this->_sp_id = $name . $type;
            if (!$query = $this->_sp_mysql->query('SHOW TABLES')){
                $this->_sp_mysql = null;
                return;
            }
            while ($row = $query->fetchColumn())$db[] = $row;
            $create_tb = TP_CREATE_TABLE;
            if (!in_array($this->_sp_options['extras']['prefix'] . 'cache_data', $db, true)){
                $query = $this->_sp_mysql->exec("{$create_tb}`{$this->_sp_options['extras']['prefix']}cache_data` (`id` TEXT CHARACTER SET utf8 NOT NULL,`items` SMALLINT NOT NULL DEFAULT 0,`data` BLOB NOT NULL, `micro_time` INT UNSIGNED NOT NULL, UNIQUE (`id`(125)) )");
                if ($query === false){
                    trigger_error("Can't create " . $this->_sp_options['extras']['prefix'] . "cache_data table, check permissions", E_USER_WARNING);
                    $this->_sp_mysql = null;
                    return;
                }
            }
            if (!in_array($this->_sp_options['extras']['prefix'] . 'items', $db, true)){
                $query = $this->_sp_mysql->exec("{$create_tb}`{$this->_sp_options['extras']['prefix']}items` (`feed_id` TEXT CHARACTER SET utf8 NOT NULL, `id` TEXT CHARACTER SET utf8 NOT NULL, `data` MEDIUMBLOB NOT NULL, `posted` INT UNSIGNED NOT NULL, INDEX `feed_id` (`feed_id`(125)))");
                if ($query === false){
                    trigger_error("Can't create " . $this->_sp_options['extras']['prefix'] . "items table, check permissions", E_USER_WARNING);
                    $this->_sp_mysql = null;
                    return;
                }
            }
        }
        public function save($data):bool{
            if ($this->_sp_mysql === null) return false;
            $sql = TP_DELETE . ' i, cd FROM `' . $this->_sp_options['extras']['prefix'] . 'cache_data` cd, ' .
                '`' . $this->_sp_options['extras']['prefix'] . 'items` i ' .
                'WHERE cd.id = i.feed_id ' .
                'AND cd.micro_time < (unix_timestamp() - :purge_time)';
            $query = $this->sp_prepare($this->_sp_mysql,$sql);
            $query->this->bindValue(':purge_time', $this->_sp_options['extras']['cache_purge_time']);
            if (!$query->this->execute()) return false;
            if ($data instanceof SimplePie){
                $data = clone $data;
                $prepared = SP_Cache_DB::prepare_sp_object_for_cache($data);
                $sql = TP_SELECT . ' COUNT(*) FROM `' . $this->_sp_options['extras']['prefix'] . 'cache_data` WHERE `id` = :feed';
                $query = $this->sp_prepare($this->_sp_mysql,$sql);
                $query->bindValue(':feed', $this->_sp_id);
                if ($query->execute()){
                    if ($query->fetchColumn() > 0){
                        $items = count($prepared[1]);
                        if ($items){
                            $sql = TP_UPDATE . ' `' . $this->_sp_options['extras']['prefix'] . 'cache_data` SET `items` = :items, `data` = :data, `micro_time` = :time WHERE `id` = :feed';
                            $query = $this->sp_prepare($this->_sp_mysql,$sql);   //$this->_sp_mysql->prepare($sql);
                            $query->bindValue(':items', $items);
                        }else{
                            $sql = TP_UPDATE . ' `' . $this->_sp_options['extras']['prefix'] . 'cache_data` SET `data` = :data, `micro_time` = :time WHERE `id` = :feed';
                            $query =  $this->sp_prepare($this->_sp_mysql,$sql);
                        }
                        $query->bindValue(':data', $prepared[0]);
                        $query->bindValue(':time', time());
                        $query->bindValue(':feed', $this->_sp_id);
                        if (!$query->execute()) return false;
                    }else{
                        $sql = TP_INSERT . ' INTO `' . $this->_sp_options['extras']['prefix'] . 'cache_data` (`id`, `items`, `data`, `micro_time`) VALUES(:feed, :count, :data, :time)';
                        $query = $this->sp_prepare($this->_sp_mysql,$sql);
                        $query->bindValue(':feed', $this->_sp_id);
                        $query->bindValue(':count', count($prepared[1]));
                        $query->bindValue(':data', $prepared[0]);
                        $query->bindValue(':time', time());
                        if (!$query->execute()) return false;
                    }
                    $ids = array_keys($prepared[1]);
                    if (!empty($ids)){
                        foreach ($ids as $id)
                            $this->_sp_database_ids[] = $this->sp_quote($this->_sp_mysql, $id);
                        $sql = TP_SELECT . ' `id` FROM `' . $this->_sp_options['extras']['prefix'] . 'items` WHERE `id` = ' . implode(' OR `id` = ', $this->_sp_database_ids) . ' AND `feed_id` = :feed';
                        $query =  $this->sp_prepare($this->_sp_mysql,$sql);
                        $query->bindValue(':feed', $this->_sp_id);
                        if ($query->execute()){
                            $existing_ids = [];
                            while ($row = $query->fetchColumn())
                                $existing_ids[] = $row;
                            $new_ids = array_diff($ids, $existing_ids);
                            foreach ($new_ids as $new_id){
                                if (!($date = $prepared[1][$new_id]->this->get_date('U')))
                                    $date = time();
                                $sql = TP_INSERT . ' INTO `' . $this->_sp_options['extras']['prefix'] . 'items` (`feed_id`, `id`, `data`, `posted`) VALUES(:feed, :id, :data, :date)';
                                $query =  $this->sp_prepare($this->_sp_mysql,$sql);
                                $query->bindValue(':feed', $this->_sp_id);
                                $query->bindValue(':id', $new_id);
                                $query->bindValue(':data', serialize($prepared[1][$new_id]->data));
                                $query->bindValue(':date', $date);
                                if (!$query->execute()) return false;
                            }
                            return true;
                        }
                    }else return true;
                }
            }else{
                $sql = TP_SELECT . ' `id` FROM `' . $this->_sp_options['extras']['prefix'] . 'cache_data` WHERE `id` = :feed';
                $query = $this->sp_prepare($this->_sp_mysql,$sql);
                $query->bindValue(':feed', $this->_sp_id);
                if ($query->execute()){
                    if ($query->rowCount() > 0){
                        $sql = TP_UPDATE . ' `' . $this->_sp_options['extras']['prefix'] . 'cache_data` SET `items` = 0, `data` = :data, `micro_time` = :time WHERE `id` = :feed';
                        $query = $this->sp_prepare($this->_sp_mysql,$sql);
                        $query->bindValue(':data', serialize($data));
                        $query->bindValue(':time', time());
                        $query->bindValue(':feed', $this->_sp_id);
                        if ($query->execute()) return true;
                    }else{
                        $sql = TP_INSERT . ' INTO `' . $this->_sp_options['extras']['prefix'] . 'cache_data` (`id`, `items`, `data`, `mtime`) VALUES(:id, 0, :data, :time)';
                        $query = $this->sp_prepare($this->_sp_mysql,$sql);
                        $query->bindValue(':id', $this->_sp_id);
                        $query->bindValue(':data', serialize($data));
                        $query->bindValue(':time', time());
                        if ($query->execute()) return true;
                    }
                }
            }
            return false;
        }
        public function load(){
            if ($this->_sp_mysql === null) return false;
            $sel_from = "SELECT";
            $sql = $sel_from . ' `items`, `data` FROM `' . $this->_sp_options['extras']['prefix'] . 'cache_data` WHERE `id` = :id';
            $query = $this->sp_prepare($this->_sp_mysql,$sql);
            $query->bindValue(':id', $this->_sp_id);
            if ($query->execute() && ($row = $query->fetch())){
                /** @noinspection UnserializeExploitsInspection *///todo important
                $data = unserialize($row[1]);
                if (isset($this->_sp_options['items'][0]))
                    $items = (int) $this->_sp_options['items'][0];
                else $items = (int) $row[0];
                if ($items !== 0){
                    if (isset($data['child'][SP_NS_ATOM_10]['feed'][0]))
                        $feed =& $data['child'][SP_NS_ATOM_10]['feed'][0];
                    elseif (isset($data['child'][SP_NS_ATOM_03]['feed'][0]))
                        $feed =& $data['child'][SP_NS_ATOM_03]['feed'][0];
                    elseif (isset($data['child'][SP_NS_RDF]['RDF'][0]))
                        $feed =& $data['child'][SP_NS_RDF]['RDF'][0];
                    elseif (isset($data['child'][SP_NS_RSS_20]['rss'][0]))
                        $feed =& $data['child'][SP_NS_RSS_20]['rss'][0];
                    else $feed = null;
                    if ($feed !== null){
                        $sel_from = "SELECT";
                        $sql = $sel_from.' `data` FROM `' . $this->_sp_options['extras']['prefix'] . 'items` WHERE `feed_id` = :feed ORDER BY `posted` DESC';
                        if ($items > 0) $sql .= ' LIMIT ' . $items;
                        $query = $this->sp_prepare($this->_sp_mysql,$sql);
                        $query->bindValue(':feed', $this->_sp_id);
                        if ($query->execute()){
                            while ($row = $query->fetchColumn()){
                                /** @noinspection UnserializeExploitsInspection *///todo important
                                $feed['child'][SP_NS_ATOM_10]['entry'][] = unserialize($row);
                            }
                        } else return false;
                    }
                }
                return $data;
            }
            return false;
        }
        public function micro_time(){
            if ($this->_sp_mysql === null) return false;
            $sel_from = "SELECT";
            $sql = $sel_from .' `micro_time` FROM `' . $this->_sp_options['extras']['prefix'] . 'cache_data` WHERE `id` = :id';
            $query = $this->sp_prepare($this->_sp_mysql,$sql);
            $query->bindValue(':id', $this->_sp_id);
            if ($query->execute() && ($time = $query->fetchColumn()))
                return $time;
            return false;
        }
        public function touch():bool{
            if ($this->_sp_mysql === null)
                return false;
            $sql = TP_UPDATE . ' `' . $this->_sp_options['extras']['prefix'] . 'cache_data` SET `micro_time` = :time WHERE `id` = :id';
            $query = $this->sp_prepare($this->_sp_mysql,$sql);
            $query->bindValue(':time', time());
            $query->bindValue(':id', $this->_sp_id);
            return $query->execute() && $query->rowCount() > 0;
        }
        public function unlink():bool{
            if ($this->_sp_mysql === null) return false;
            $sql = TP_DELETE .' FROM`' . $this->_sp_options['extras']['prefix'] . 'cache_data` WHERE `id` = :id';
            $query = $this->sp_prepare($this->_sp_mysql,$sql);
            $query->bindValue(':id', $this->_sp_id);
            $sql2 = TP_DELETE .' FROM`' . $this->_sp_options['extras']['prefix'] . 'items` WHERE `feed_id` = :id';
            $query2 = $this->sp_prepare($this->_sp_mysql,$sql2);
            $query2->bindValue(':id', $this->_sp_id);
            return $query->execute() && $query2->execute();
        }
    }
}else die;