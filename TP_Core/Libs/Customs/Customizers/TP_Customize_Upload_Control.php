<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
use TP_Core\Traits\Media\_media_07;
use TP_Core\Traits\Media\_media_08;

if(ABSPATH){
    class TP_Customize_Upload_Control extends TP_Customize_Media_Control{
        use _media_07,_media_08;
        public $type = 'upload';
        public $mime_type;
        public $button_labels = array();
        public function to_json():void{
            parent::to_json();
            $value = $this->value();
            if ( $value ) {
                // Get the attachment model for the existing file.
                $attachment_id = $this->_attachment_url_to_post_id( $value );
                if ( $attachment_id ) {
                    $this->json['attachment'] = $this->_tp_prepare_attachment_for_js( $attachment_id );
                }
            }
        }
    }
}else die;