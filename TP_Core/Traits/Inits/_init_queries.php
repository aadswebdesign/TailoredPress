<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-4-2022
 * Time: 12:43
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\Queries\TP_Network_Query;
use TP_Core\Libs\Queries\TP_Query;
use TP_Core\Libs\Queries\TP_Comment_Query;
use TP_Core\Libs\Queries\TP_Date_Query;
use TP_Core\Libs\Queries\TP_Meta_Query;
use TP_Core\Libs\Queries\TP_Site_Query;
if(ABSPATH){
    trait _init_queries{
        protected $_tp_comment_query;
        protected $_tp_date_query;
        protected $_tp_meta_query;
        protected $_tp_network_query;
        protected $_tp_site_query;
        protected $_tp_query;
        protected function _init_date_query($query = ''): TP_Date_Query{
            if(!($this->_tp_date_query instanceof TP_Date_Query))
                $this->_tp_date_query = new TP_Date_Query($query);
            return $this->_tp_date_query;
        }
        protected function _init_meta_query($meta_query = false): TP_Meta_Query{
            if(!($this->_tp_meta_query instanceof TP_Meta_Query))
                $this->_tp_meta_query = new TP_Meta_Query($meta_query);
            return $this->_tp_meta_query;
        }
        protected function _init_query($query = ''): TP_Query{
            if(!($this->_tp_query instanceof TP_Query))
                $this->_tp_query = new TP_Query($query);
            return $this->_tp_query;
        }
        public function getTpTheQuery($args = ''):TP_Query{
            return new TP_Query($args);
        }
        protected function _init_comment_query($query = ''):TP_Comment_Query{
            if(!($this->_tp_comment_query instanceof TP_Comment_Query)){
                $this->_tp_comment_query = new TP_Comment_Query($query);
            }
            return $this->_tp_comment_query;
        }
        protected function _init_network_query($query = ''):TP_Network_Query{
            if(!($this->_tp_network_query instanceof TP_Network_Query)){
                $this->_tp_network_query = new TP_Network_Query($query);
            }
            return $this->_tp_network_query;
        }
        protected function _init_site_query($query = ''):TP_Site_Query{
            if(!($this->_tp_site_query instanceof TP_Site_Query)){
                $this->_tp_site_query = new TP_Site_Query($query);
            }
            return $this->_tp_site_query;
        }

    }
}else die;