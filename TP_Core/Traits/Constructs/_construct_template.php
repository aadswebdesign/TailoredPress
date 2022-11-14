<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_template{
        public $tp_args = [];
        public $tp_author_data;
        public $tp_cat_id;
        public $tp_current_template_content;
        public $tp_default_headers;
        public $tp_embed;
        public $tp_list_block;
        /** @deprecated  */
        public $tp_list_table;
        public $tp_overridden_cpage;
        public $tp_sidebars_widgets;
        public $tp_tab;
        public $tp_tabs;
        public $tp_type;
        public $tp_with_comments;

        protected function _construct_template():void{
            $this->tp_author_data;
            $this->tp_cat_id;
            $this->tp_current_template_content;
            $this->tp_default_headers;
            $this->tp_embed;
            $this->tp_list_block;
            $this->tp_overridden_cpage;
            $this->tp_sidebars_widgets;
            $this->tp_tab;
            $this->tp_tabs;
            $this->tp_type;
            $this->tp_with_comments;
        }
    }
}else die;