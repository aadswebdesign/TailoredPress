<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 23:34
 */
namespace TP_Admin\Traits\AdminImage;
if(ABSPATH){
    trait _adm_image_02{
        /**
         * @description Validate that file is an image.
         * @param $path
         * @return bool
         */
        protected function _file_is_valid_image( $path ):bool{
            $size = $this->_tp_get_image_size( $path );
            return ! empty( $size );
        }//957
        /**
         * @description Validate that file is suitable for displaying within a web page.
         * @param $path
         * @return mixed
         */
        protected function _file_is_displayable_image( $path ){
            $displayable_image_types = array( IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP, IMAGETYPE_ICO, IMAGE_TYPE_WEBP );
            $info = $this->_tp_get_image_size( $path );
            if ( empty( $info ) ) { $result = false;
            } elseif ( ! in_array( $info[2], $displayable_image_types, true ) ) {
                $result = false;}
            else {$result = true;}
            return $this->_apply_filters( 'file_is_displayable_image', $result, $path );
        }//970
        /**
         * @description Load an image resource for editing.
         * @param $attachment_id
         * @param $mime_type
         * @param string $size
         * @return bool|resource
         */
        protected function _load_image_to_edit( $attachment_id, $mime_type, $size = 'full' ){
            $filepath = $this->_load_image_to_edit_path( $attachment_id, $size );
            if ( empty( $filepath ) ) {return false;}
            switch ( $mime_type ) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg( $filepath );
                    break;
                case 'image/png':
                    $image = imagecreatefrompng( $filepath );
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif( $filepath );
                    break;
                case 'image/webp':
                    $image = false;
                    if ( function_exists( 'imagecreatefromwebp' ) ) {
                        $image = imagecreatefromwebp( $filepath );
                    }
                    break;
                default:
                    $image = false;
                    break;
            }

            if ( $this->_is_gd_image( $image ) ) {
                $image = $this->_apply_filters( 'load_image_to_edit', $image, $attachment_id, $size );

                if ( function_exists( 'imagealphablending' ) && function_exists( 'imagesavealpha' ) ) {
                    imagealphablending( $image, false );
                    imagesavealpha( $image, true );
                }
            }

            return $image;
        }//1005
        /**
         * @description Retrieve the path or URL of an attachment's attached file.
         * @param $attachment_id
         * @param string $size
         * @return mixed
         */
        protected function _load_image_to_edit_path( $attachment_id, $size = 'full' ){
            $filepath = $this->_get_attached_file( $attachment_id );
            if ( $filepath && file_exists( $filepath ) ) {
                if ( 'full' !== $size ) {
                    $data = $this->_image_get_intermediate_size( $attachment_id, $size );
                    if ( $data ) {
                        $filepath = $this->_path_join( dirname( $filepath ), $data['file'] );
                        $filepath = $this->_apply_filters( 'load_image_to_edit_filesystem_path', $filepath, $attachment_id, $size );
                    }
                }
            } elseif ( function_exists( 'fopen' ) && ini_get( 'allow_url_fopen' ) ) {
                $filepath = $this->_apply_filters( 'load_image_to_edit_attachment_url', $this->_tp_get_attachment_url( $attachment_id ), $attachment_id, $size );
            }
            return $this->_apply_filters( 'load_image_to_edit_path', $filepath, $attachment_id, $size );
        }//1068
        /**
         * @description Copy an existing image file.
         * @param $attachment_id
         * @return mixed
         */
        protected function _copy_image_file( $attachment_id ){
            $dst_file = $this->_get_attached_file( $attachment_id );
            $src_file = $dst_file;
            if ( ! file_exists( $src_file ) ) { $src_file = $this->_load_image_to_edit_path( $attachment_id );}
            if ( $src_file ) {
                $dst_file = str_replace( $this->_tp_basename( $dst_file ), 'copy-' . $this->_tp_basename( $dst_file ), $dst_file );
                $dst_file = dirname( $dst_file ) . '/' . $this->_tp_unique_filename( dirname( $dst_file ), $this->_tp_basename( $dst_file ) );
                $this->_tp_mkdir_p( dirname( $dst_file ) );
                if ( ! copy( $src_file, $dst_file ) ) { $dst_file = false;}
            } else { $dst_file = false;}
            return $dst_file;
        }//1131
    }
}else die;