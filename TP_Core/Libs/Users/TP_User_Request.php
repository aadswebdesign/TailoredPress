<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 6-6-2022
 * Time: 19:24
 */
namespace TP_Core\Libs\Users;
use TP_Core\Traits\Post\_post_05;
if(ABSPATH){
    class TP_User_Request{
        use _post_05;
        public $ID = 0;
        public $user_id = 0;
        public $email = '';
        public $action_name = '';
        public $status = '';
        public $created_timestamp;
        public $modified_timestamp;
        public $confirmed_timestamp;
        public $completed_timestamp;
        public $request_data = [];
        public $confirm_key = '';
        public function __construct( $post ) {
            $this->ID                  = $post->ID;
            $this->user_id             = $post->post_author;
            $this->email               = $post->post_title;
            $this->action_name         = $post->post_name;
            $this->status              = $post->post_status;
            $this->created_timestamp   = strtotime( $post->post_date_gmt );
            $this->modified_timestamp  = strtotime( $post->post_modified_gmt );
            $this->confirmed_timestamp = (int) $this->_get_post_meta( $post->ID, '_tp_user_request_confirmed_timestamp', true );
            $this->completed_timestamp = (int) $this->_get_post_meta( $post->ID, '_tp_user_request_completed_timestamp', true );
            $this->request_data        = json_decode( $post->post_content, true );
            $this->confirm_key         = $post->post_password;
        }
    }
}else die;