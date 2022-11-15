<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-8-2022
 * Time: 23:48
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\Editor\TP_Editor;
if(ABSPATH){
    trait _init_editor{
        protected $_tp_editor;
        protected function _init_editor($content, $editor_id,array ...$settings):TP_Editor{
            if(!($this->_tp_editor instanceof TP_Editor)){
                $this->_tp_editor = TP_Editor::editor($content, $editor_id,$settings);
            }
            return $this->_tp_editor;
        }
    }
}else{die;}