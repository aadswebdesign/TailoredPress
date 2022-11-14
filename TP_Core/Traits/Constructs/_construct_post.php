<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 11:38
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_post{
        public $tp_avail_post_mime_types;
        public $tp_avail_post_stati;
        public $tp_cat;
        public $tp_get_posts;
        public $tp_locked_post_status;
        public $tp_post;
        public $tp_post_default_category;
        public $tp_post_default_title;
        public $tp_post_id;
        public $tp_post_mime_types;
        public $tp_post_statuses;
        public $tp_post_type;
        public $tp_post_type_features;
        public $tp_post_type_meta_caps;
        public $tp_post_type_object;
        public $tp_post_types;
        public $tp_posts;
        public $tp_posts_per_page;
        protected function _construct_post():void{
            $this->tp_avail_post_mime_types;
            $this->tp_avail_post_stati;
            $this->tp_cat;
            $this->tp_get_posts;
            $this->tp_locked_post_status;
            $this->tp_post;
            $this->tp_post_default_category;
            $this->tp_post_default_title;
            $this->tp_post_id;
            $this->tp_post_mime_types;
            $this->tp_post_statuses;
            $this->tp_post_type;
            $this->tp_post_type_features;
            $this->tp_post_type_meta_caps;
            $this->tp_posts;
            $this->tp_post_types;
        }
    }
}else die;