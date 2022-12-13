<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 30-10-2022
 * Time: 22:25
 */
namespace TP_Admin\Libs\AdmPanels;
use TP_Admin\AdminSettings;
if(ABSPATH){
    class Adm_Upgrade_Panel extends AdminSettings {
        protected $_args;
        public function __construct($args = null){
            parent::__construct();
            $this->_args = $args;
        }
        private function __to_string():string{
            $output  = "<p>Adm_Upgrade_Panel</p>";
            $output .= "";
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}

