<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 21:04
 */
namespace TP_Core\Libs\Block;
if(ABSPATH){
    //todo might become an interface with standard functions
    class TP_Block_Template{
        public $type;
        public $theme;
        public $slug;
        public $id;
        public $title = '';
        public $content = '';
        public $description = '';
        public $source = 'theme';
        public $origin;
        public $tp_id;
        public $status;
        public $has_theme_file;
        public $is_custom = true;
        public $author;
        public $post_types;
        public $area;
    }
}else die;