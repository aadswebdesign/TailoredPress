<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-3-2022
 * Time: 04:04
 */
namespace TP_Core\Traits\Media;
if(ABSPATH){
    trait _media_06 {
        /**
         * @description Gets the next image link that has the same post parent.
         * @param string $size
         * @param bool $text
         * @return string
         */
        protected function _get_next_image_link( $size = 'thumbnail', $text = false ):string{
            return $this->_get_adjacent_image_link( false, $size, $text );
        }//3436
        /**
         * @description Displays next image link that has the same post parent.
         * @param string $size
         * @param bool $text
         */
        public function next_image_link( $size = 'thumbnail', $text = false ):void{
            echo $this->_get_next_image_link( $size, $text );
        }//3449
        /**
         * @description Gets the next or previous image link that has the same post parent.
         * @param bool $prev
         * @param string $size
         * @param bool $text
         * @return mixed
         */
        protected function _get_adjacent_image_link( $prev = true, $size = 'thumbnail', $text = false ){
            $post        = $this->_get_post();
            $attachments = array_values(
                $this->_get_children(
                    ['post_parent' => $post->post_parent,'post_status' => 'inherit','post_type' => 'attachment',
                        'post_mime_type' => 'image','order' => 'ASC','orderby' => 'menu_order ID',]
                )
            );
            foreach ( $attachments as $k => $attachment ) {
                if ( (int) $attachment->ID === (int) $post->ID ) break;
            }
            $output        = '';
            $attachment_id = 0;
            $k = null;
            if ( $attachments ) {
                $k = $prev ? $k - 1 : $k + 1;
                if ( isset( $attachments[ $k ] ) ) {
                    $attachment_id = $attachments[ $k ]->ID;
                    $attr          = array( 'alt' => $this->_get_the_title( $attachment_id ) );
                    $output        = $this->_tp_get_attachment_link( $attachment_id, $size, true, false, $text, $attr );
                }
            }
            $adjacent = $prev ? 'previous' : 'next';
            return $this->_apply_filters( "{$adjacent}_image_link", $output, $attachment_id, $size, $text );
        }//3466
        /**
         * @description Displays next or previous image link that has the same post parent.
         * @param bool $prev
         * @param string $size
         * @param bool $text
         */
        public function adjacent_image_link( $prev = true, $size = 'thumbnail', $text = false ):void{
            echo $this->_get_adjacent_image_link( $prev, $size, $text );
        }//3536
        /**
         * @description Retrieves taxonomies attached to given the attachment.
         * @param $attachment
         * @param string $output
         * @return array
         */
        protected function _get_attachment_taxonomies( $attachment, $output = 'names' ):array{
            if ( is_int( $attachment ) ) $attachment = $this->_get_post( $attachment );
            elseif ( is_array( $attachment ) ) $attachment = (object) $attachment;
            if ( ! is_object( $attachment ) ) return array();
            $file     = $this->_get_attached_file( $attachment->ID );
            $filename = $this->_tp_basename( $file );
            $objects = ['attachment'];
            if ( false !== strpos( $filename, '.' ) )
                $objects[] = 'attachment:' . substr( $filename, strrpos( $filename, '.' ) + 1 );
            if ( ! empty( $attachment->post_mime_type ) ) {
                $objects[] = 'attachment:' . $attachment->post_mime_type;
                if ( false !== strpos( $attachment->post_mime_type, '/' ) ) {
                    foreach ( explode( '/', $attachment->post_mime_type ) as $token ) {
                        if ( ! empty( $token ) ) $objects[] = "attachment:$token";
                    }
                }
            }
            $taxonomies = array();
            foreach ( $objects as $object ) {
                $taxes = $this->_get_object_taxonomies( $object, $output );
                if ( $taxes ) $taxonomies = $this->_tp_array_merge($taxonomies, $taxes);
            }
            if ( 'names' === $output )  $taxonomies = array_unique( $taxonomies );
            return $taxonomies;
        }//3552
        /**
         * @description Retrieves all of the taxonomies that are registered for attachments.
         * @param string $output
         * @return array
         */
        protected function _get_taxonomies_for_attachments( $output = 'names' ):array{
            $taxonomies = [];
            foreach ( $this->_get_taxonomies( array(), 'objects' ) as $taxonomy ) {
                foreach ( $taxonomy->object_type as $object_type ) {
                    if ( 'attachment' === $object_type || 0 === strpos( $object_type, 'attachment:' ) ) {
                        if ( 'names' === $output )  $taxonomies[] = $taxonomy->name;
                        else $taxonomies[ $taxonomy->name ] = $taxonomy;
                        break;
                    }
                }
            }
            return $taxonomies;
        }//3614
        /**
         * @description Determines whether the value is an acceptable type for GD image functions.
         * @param $image
         * @return bool
         */
        protected function _is_gd_image( $image ):bool{
            /** @noinspection PhpUndefinedConstantInspection */
            $gd_image = \GdImage;
            if ((is_object( $image ) && $image instanceof $gd_image) || (is_resource( $image ) && 'gd' === get_resource_type( $image )))
                return true;
            return false;
        }//3646
        /**
         * @description Create new Good image resource with transparency support
         * @param $width
         * @param $height
         * @return resource
         */
        protected function _tp_image_create_true_color( $width, $height ){
            $img = imagecreatetruecolor( $width, $height );
            if (function_exists( 'imagealphablending' ) && function_exists( 'imagesavealpha' ) &&  $this->_is_gd_image( $img )) {
                imagealphablending( $img, false );
                imagesavealpha( $img, true );
            }
            return $img;
        }//3668
        /**
         * @description Based on a supplied width/height example,
         * @description . return the biggest possible dimensions based on the max width/height.
         * @param $example_width
         * @param $example_height
         * @param $max_width
         * @param $max_height
         * @return mixed
         */
        protected function _tp_expand_dimensions( $example_width, $example_height, $max_width, $max_height ){
            $example_width  = (int) $example_width;
            $example_height = (int) $example_height;
            $max_width      = (int) $max_width;
            $max_height     = (int) $max_height;
            return $this->_tp_constrain_dimensions( $example_width * 1000000, $example_height * 1000000, $max_width, $max_height );
        }//3699
        /**
         * @description Determines the maximum upload size allowed in php.ini.
         * @return mixed
         */
        protected function _tp_max_upload_size(){
            $u_bytes = $this->_tp_convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
            $p_bytes = $this->_tp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );
            return $this->_apply_filters( 'upload_size_limit', min( $u_bytes, $p_bytes ), $u_bytes, $p_bytes );
        }//3715
     }
}else die;