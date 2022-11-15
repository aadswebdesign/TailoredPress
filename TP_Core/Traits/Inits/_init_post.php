<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-4-2022
 * Time: 22:37
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\Post\TP_Post;
if(ABSPATH){
    trait _init_post{
        private $__post;
        private $__post_id;
        protected function _init_post(...$post): TP_Post{
            if(!($this->__post instanceof TP_Post))
                $this->__post = new TP_Post($post);
            return $this->__post;
        }
        protected function _init_post_ID(){
            $post = $this->_init_post();
            if($post !== null){
                $this->__post_id = $post->ID;
            }
            return $this->__post_id;
        }
    }
}else die;