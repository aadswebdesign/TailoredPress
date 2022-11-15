<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-5-2022
 * Time: 07:51
 */
namespace TP_Core\Traits\Inits;
use TP_Core\Libs\TP_Comment;
if(ABSPATH){
    trait _init_comment{
        protected
            $_comment_alt,
            $_comment_depth,
            $_comment_thread_alt,
            $_comment_count,
            $_in_comment_loop,
            $_with_comments,
            $_tp_comment;
        protected function _init_comment(...$comment):TP_Comment{
            if(!($this->_tp_comment instanceof TP_Comment))
                $this->_tp_comment = new TP_Comment($comment);
            return $this->_tp_comment;
        }
    }
}else die;