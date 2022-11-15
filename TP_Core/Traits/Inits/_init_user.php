<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 15-4-2022
 * Time: 19:41
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\Users\TP_User;
use TP_Core\Libs\Users\TP_Roles;
if(ABSPATH){
    trait _init_user{
        protected $_tp_user;
        protected $_tp_role;
        protected function _init_user($id = 0, $name = '', $site_id = '' ):TP_User{
            if(!($this->_tp_user instanceof TP_User))
                $this->_tp_user = new TP_User($id, $name, $site_id );
            return $this->_tp_user;
        }
        protected function _init_roles($site_id = null): TP_Roles{
            if(!($this->_tp_role instanceof TP_Roles))
                $this->_tp_role = new TP_Roles($site_id);
            return $this->_tp_role;
        }
    }
}else die;