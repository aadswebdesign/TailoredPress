<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-5-2022
 * Time: 08:49
 */
namespace TP_Core\Traits\Block;
use TP_Core\Libs\Block\TP_Block_Patterns_Registry;
use TP_Core\Libs\Block\TP_Block_Pattern_Categories_Registry;
if(ABSPATH){
    trait _block_registries{
        /**
         * @param $pattern_name
         * @param $pattern_properties
         * @return bool
         */
        protected function _register_block_pattern( $pattern_name, $pattern_properties ): bool{
            $registry = TP_Block_Patterns_Registry::get_instance();
            $register = null;
            if($registry !== null){
                $register = $registry->register( $pattern_name, $pattern_properties );
            }
            return $register;
        }
        /**
         * @param $pattern_name
         * @return bool
         */
        protected function _unregister_block_pattern( $pattern_name ): bool{
            $registry = TP_Block_Patterns_Registry::get_instance();
            $unregister = null;
            if($registry !== null){
                $unregister = $registry->unregister( $pattern_name);
            }
            return $unregister;
        }
        /**
         * @param $category_name
         * @param $category_properties
         * @return bool
         */
        protected function _register_block_pattern_category( $category_name, $category_properties ): bool{
            $registry = TP_Block_Pattern_Categories_Registry::get_instance();
            $register = null;
            if($registry !== null){
                $register = $registry->register( $category_name, $category_properties);
            }
            return $register;
        }
        /**
         * @param $category_name
         * @return bool
         */
        protected function _unregister_block_pattern_category( $category_name ): bool{
            $registry = TP_Block_Pattern_Categories_Registry::get_instance();
            $unregister = null;
            if($registry !== null){
                $unregister = $registry->unregister( $category_name);
            }
            return $unregister;
        }
    }
}else die;