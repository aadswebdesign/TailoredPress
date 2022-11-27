<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-10-2022
 * Time: 20:47
 */
namespace TP_Admin\Traits;
use TP_Core\Libs\TP_Error;

if(ABSPATH){
    trait _adm_class_loaders{
        /**
         * $class_name
         * @param $class_name
         * @param array|null $args
         * @return bool|object
         */
        public function get_adm_component_class($class_name,$args = null){
            if($class_name === null){
                new TP_Error('Please provide a Class Name, make sure your class is namespaced!');
                return false;
            }
            return $this->_tp_load_class($class_name,TP_NS_ADMIN_COMPONENTS,$class_name,$args);
        }
        /**
         * $class_name
         * @param $class_name
         * @param array|null $args
         * @return bool|object
         */
        public function get_adm_modules_class($class_name,$args = null){
            if($class_name === null){
                new TP_Error('Please provide a Class Name, make sure your class is namespaced!');
                return false;
            }
            return $this->_tp_load_class($class_name,TP_NS_ADMIN_MODULES,$class_name,$args);
        }


    }
}else{die;}