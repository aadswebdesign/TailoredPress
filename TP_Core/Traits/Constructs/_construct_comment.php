<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-4-2022
 * Time: 16:44
 */
namespace TP_Core\Traits\Constructs;
if(ABSPATH){
    trait _construct_comment{
        public $tp_comment;
        public $tp_comment_alt;
        public $tp_comment_depth;
        public $tp_comment_thread_alt;
        public $tp_in_comment_loop;
        public $tp_comment_status, $tp_comment_type;
        protected function _construct_comment():void{
            $this->tp_comment;
            $this->tp_comment_alt;
            $this->tp_comment_depth;
            $this->tp_comment_thread_alt;
            $this->tp_in_comment_loop;
            $this->tp_comment_status;
            $this->tp_comment_type;

        }
    }
}else die;