<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-2-2022
 * Time: 02:32
 */
namespace TP_Core\Traits\Post;
if(ABSPATH){
    trait _post_14{
        /**
         * @description Retrieves the path to an uploaded image file.
         * @param $attachment_id
         * @param bool $unfiltered
         * @return bool
         */
        protected function _tp_get_original_image_path( $attachment_id, $unfiltered = false ):bool{
            if ( ! $this->_tp_attachment_is_image( $attachment_id ) ) return false;
            $image_meta = $this->_tp_get_attachment_metadata( $attachment_id );
            $image_file = $this->_get_attached_file( $attachment_id, $unfiltered );
            if ( empty( $image_meta['original_image'] ) ) $original_image = $image_file;
            else  $original_image = $this->_path_join( dirname( $image_file ), $image_meta['original_image'] );
            return $this->_apply_filters( 'tp_get_original_image_path', $original_image, $attachment_id );
        }//8000
        /**
         * @description Retrieve the URL to an original attachment image.
         * @param $attachment_id
         * @return bool
         */
        protected function _tp_get_original_image_url( $attachment_id ):bool{
            if ( ! $this->_tp_attachment_is_image( $attachment_id ) ) return false;
            $image_url = $this->_tp_get_attachment_url( $attachment_id );
            if ( ! $image_url ) return false;
            $image_meta = $this->_tp_get_attachment_metadata( $attachment_id );
            if ( empty( $image_meta['original_image'] ) ) $original_image_url = $image_url;
            else $original_image_url = $this->_path_join( dirname( $image_url ), $image_meta['original_image'] );
            return $this->_apply_filters( 'tp_get_original_image_url', $original_image_url, $attachment_id );

        }//8037
        /**
         * @description Filter callback which sets the status of an untrashed post to its previous status.
         * @param $previous_status
         * @return mixed
         */
        protected function _tp_untrash_post_set_previous_status($previous_status ){
            //todo not used  $new_status, $post_id,
            return $previous_status;
        }//8079
    }
}else die;