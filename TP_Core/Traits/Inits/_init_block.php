<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-8-2022
 * Time: 23:48
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\Block\TP_Block;
use TP_Core\Libs\Block\TP_Block_Parser;
use TP_Core\Libs\Block\TP_Block_Scripts_Registry;
use TP_Core\Libs\Block\TP_Block_Styles_Registry;//this one
use TP_Core\Libs\Block\TP_Block_Type_Registry;
if(ABSPATH){
    trait _init_block{
        /** @deprecated */
        protected $_block;
        protected $_tp_block;
        protected $_tp_block_parser;
        protected $_tp_block_script_registry;
        protected $_tp_block_style_registry;
        protected $_tp_block_type_registry;
        protected function _init_block(...$block): TP_Block{
            if(!$this->_tp_block instanceof  TP_Block ){
                $this->_tp_block = new TP_Block($block);
            }
            return $this->_tp_block;
        }
        protected function _init_block_parser(): TP_Block_Parser{
            if(!($this->_tp_block_parser instanceof  TP_Block_Parser)){
                $this->_tp_block_parser = new TP_Block_Parser();
            }
            return $this->_tp_block_parser;
        }
        protected function _init_block_type_registry():TP_Block_Type_Registry{
            if(!($this->_tp_block_type_registry instanceof TP_Block_Type_Registry)){
                $this->_tp_block_type_registry = new TP_Block_Type_Registry();
            }
            return $this->_tp_block_type_registry;
        }
        protected function _init_block_type_instance(): ?TP_Block_Type_Registry{
            return TP_Block_Type_Registry::get_instance();
        }

        protected function _init_block_style_registry():TP_Block_Styles_Registry{
            if(!($this->_tp_block_style_registry instanceof TP_Block_Styles_Registry)){
                $this->_tp_block_style_registry = new TP_Block_Styles_Registry();
            }
            return $this->_tp_block_style_registry;
        }
        protected function _init_block_style_get_instance(): ?TP_Block_Styles_Registry{
            return TP_Block_Styles_Registry::get_instance();
        }
        protected function _init_block_script_registry():TP_Block_Scripts_Registry{
            if(!($this->_tp_block_script_registry instanceof TP_Block_Scripts_Registry)){
                $this->_tp_block_script_registry = new TP_Block_Scripts_Registry();
            }
            return $this->_tp_block_script_registry;
        }
    }
}else{die;}