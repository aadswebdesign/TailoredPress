<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-3-2022
 * Time: 04:04
 */
namespace TP_Core\Traits\Media;
use TP_Core\Traits\Inits\_init_images;
if(ABSPATH){
    trait _media_01 {
        use _init_images;
        /**
         * @description Retrieve additional image sizes.
         * @return array
         */
        protected function _tp_get_additional_image_sizes():array{
            if ( ! $this->tp_additional_img_sizes )
                $this->tp_additional_img_sizes = [];
            return $this->tp_additional_img_sizes;
        }//18
        /**
         * @description Scale down the default size of an image.
         * @param $width
         * @param $height
         * @param mixed $size
         * @param null $context
         * @return string
         */
        protected function _image_constrain_size_for_editor( $width, $height, $size = 'medium', $context = null ):string{
            $this->tp_additional_img_sizes = $this->_tp_get_additional_image_sizes();
            if ( ! $context ) $context = $this->_is_admin() ? 'edit' : 'display';
            if ( is_array( $size ) ) {
                @list($max_width,$max_height) = $size;
            } elseif ( 'thumb' === $size || 'thumbnail' === $size ) {
                $max_width  = (int) $this->_get_option( 'thumbnail_size_w' );
                $max_height = (int) $this->_get_option( 'thumbnail_size_h' );
                if ( ! $max_width && ! $max_height ) {
                    $max_width  = 128;
                    $max_height = 96;
                }
            } elseif ( 'medium' === $size ) {
                $max_width  = (int) $this->_get_option( 'medium_size_w' );
                $max_height = (int) $this->_get_option( 'medium_size_h' );
            } elseif ( 'medium_large' === $size ) {
                $max_width  = (int) $this->_get_option( 'medium_large_size_w' );
                $max_height = (int) $this->_get_option( 'medium_large_size_h' );
                if ( (int) $this->tp_content_width > 0 )
                    $max_width = min( (int) $this->tp_content_width, $max_width );
            } elseif ( 'large' === $size ) {
                $max_width  = (int) $this->_get_option( 'large_size_w' );
                $max_height = (int) $this->_get_option( 'large_size_h' );
                if ( (int) $this->tp_content_width > 0 )
                    $max_width = min( (int) $this->tp_content_width, $max_width );
            } elseif ( ! empty( $this->tp_additional_img_sizes ) && array_key_exists($size, $this->tp_additional_img_sizes)) {
                $max_width  = (int) $this->tp_additional_img_sizes[(string)$size ]['width'];
                $max_height = (int) $this->tp_additional_img_sizes[(string) $size ]['height'];
                // Only in admin. Assume that theme authors know what they're doing.
                if ( (int) $this->tp_content_width > 0 && 'edit' === $context )
                    $max_width = min( (int) $this->tp_content_width, $max_width );
            } else { // $size === 'full' has no constraint.
                $max_width  = $width;
                $max_height = $height;
            }
            @list( $max_width, $max_height ) = $this->_apply_filters( 'editor_max_image_size', array( $max_width, $max_height ), $size, $context );
            return $this->_tp_constrain_dimensions( $width, $height, $max_width, $max_height );
        }//60
        /**
         * @description Retrieve width and height attributes using given width and height values.
         * @param $width
         * @param $height
         * @return string
         */
        protected function _image_hwstring( $width, $height ):string{
            $out = '';
            if ((int) $width ) $out .= " width='$width'";
            if ((int) $height ) $out .= " height='$height'";
            return $out;
        }//155
        /**
         * @description Scale an image to fit a particular size (such as 'thumb' or 'medium').
         * @param $id
         * @param string $size
         * @return array|bool
         */
        protected function _image_downsize( $id, $size = 'medium' ){
            $is_image = $this->_tp_attachment_is_image( $id );
            $out = $this->_apply_filters( 'image_downsize', false, $id, $size );
            if ( $out ) return $out;
            $img_url          = $this->_tp_get_attachment_url( $id );
            $meta             = $this->_tp_get_attachment_metadata( $id );
            $width            = 0;
            $height           = 0;
            $is_intermediate  = false;
            $img_url_basename = $this->_tp_basename( $img_url );
            if ( ! $is_image ) {
                if ( ! empty( $meta['sizes']['full'] ) ) {
                    $img_url          = str_replace( $img_url_basename, $meta['sizes']['full']['file'], $img_url );
                    $img_url_basename = $meta['sizes']['full']['file'];
                    $width            = $meta['sizes']['full']['width'];
                    $height           = $meta['sizes']['full']['height'];
                } else return false;
            }
            $intermediate = $this->_image_get_intermediate_size( $id, $size );
            if ( $intermediate ) {
                $img_url         = str_replace( $img_url_basename, $intermediate['file'], $img_url );
                $width           = $intermediate['width'];
                $height          = $intermediate['height'];
                $is_intermediate = true;
            } elseif ( 'thumbnail' === $size ) {
                $thumb_file = $this->_tp_get_attachment_thumb_file( $id );
                $info       = null;
                if ( $thumb_file ) $info = $this->_tp_get_image_size( $thumb_file );
                if ( $thumb_file && $info ) {
                    $img_url         = str_replace( $img_url_basename, $this->_tp_basename( $thumb_file ), $img_url );
                    @list($width,$height) = $info;
                    $is_intermediate = true;
                }
            }
            if ( ! $width && ! $height && isset( $meta['width'], $meta['height'] ) ) {
                $width  = $meta['width'];
                $height = $meta['height'];
            }
            if ( $img_url ) {
                @list( $width, $height ) = $this->_image_constrain_size_for_editor( $width, $height, $size );
                return array( $img_url, $width, $height, $is_intermediate );
            }
            return false;
        }//191
        /**
         * @description Register a new image size.
         * @param $name
         * @param int $width
         * @param int $height
         * @param bool $crop
         * @return bool
         */
        protected function _add_image_size( $name, $width = 0, $height = 0, $crop = false ):bool{
            $this->tp_additional_img_sizes[ $name ] = ['width' => $this->_abs_int( $width ),
                'height' => $this->_abs_int( $height ),'crop' => $crop,];
        }//291
        /**
         * @param $name
         * @return bool@description Check if an image size exists.
         */
        protected function _has_image_size( $name ):bool{
            $sizes = $this->_tp_get_additional_image_sizes();
            return isset( $sizes[ $name ] );
        }//309
        /**
         * @description Remove a new image size.
         * @param $name
         * @return bool
         */
        protected function _remove_image_size( $name ):bool{
            if ( isset( $this->tp_additional_img_sizes[ $name ] ) ) {
                unset( $this->tp_additional_img_sizes[ $name ] );
                return true;
            }
            return false;
        }//324
        /**
         * @description Registers an image size for the post thumbnail.
         * @param int $width
         * @param int $height
         * @param bool $crop
         * @return bool
         */
        protected function _set_post_thumbnail_size( $width = 0, $height = 0, $crop = false ):bool{
            $this->_add_image_size( 'post-thumbnail', $width, $height, $crop );
        }//347
        /**
         * @description Gets an img tag for an image attachment, scaling it down if requested.
         * @param $id
         * @param $alt
         * @param $title
         * @param $align
         * @param mixed $size
         * @param bool $loading_lazy
         * @return mixed
         */
        protected function _get_image_tag( $id, $alt, $title, $align, $size = 'medium' , $loading_lazy = false){
            @list( $img_src, $width, $height ) = $this->_image_downsize( $id, $size );
            $hwstring                         = $this->_image_hwstring( $width, $height );
            $title = $title ? " title='{$this->_esc_attr( $title )}'" : '';
            $size_class = is_array( $size ) ? implode( 'x', $size ) : $size;
            $class      = "align-{$this->_esc_attr( $align )} size-{$this->_esc_attr( $size_class )} tp-image-$id";
            $loading = '';
            if($loading_lazy === true) $loading = "loading='lazy'";
            $class = $this->_apply_filters( 'get_image_tag_class', $class, $id, $align, $size );
            $html = "<img src='{$this->_esc_attr( $img_src )}' alt='{$this->_esc_attr( $alt )}' $title $hwstring class='$class' $loading />";
            return $this->_apply_filters( 'get_image_tag', $html, $id, $alt, $title, $align, $size, $loading );
        }//373
        /**
         * @description Calculates the new dimensions for a down-sampled image.
         * @param $current_width
         * @param $current_height
         * @param int $max_width
         * @param int $max_height
         * @return array
         */
        protected function _tp_constrain_dimensions( $current_width, $current_height, $max_width = 0, $max_height = 0 ):array{
            if ( ! $max_width && ! $max_height ) return [$current_width, $current_height];
            $width_ratio  = 1.0;
            $height_ratio = 1.0;
            $did_width    = false;
            $did_height   = false;
            if ( $max_width > 0 && $current_width > 0 && $current_width > $max_width ) {
                $width_ratio = $max_width / $current_width;
                $did_width   = true;
            }
            if ( $max_height > 0 && $current_height > 0 && $current_height > $max_height ) {
                $height_ratio = $max_height / $current_height;
                $did_height   = true;
            }
            $smaller_ratio = min( $width_ratio, $height_ratio );
            $larger_ratio  = max( $width_ratio, $height_ratio );
            if ( (int) round( $current_width * $larger_ratio ) > $max_width || (int) round( $current_height * $larger_ratio ) > $max_height )
                $ratio = $smaller_ratio;
            else $ratio = $larger_ratio;
            $w = max( 1, (int) round( $current_width * $ratio ) );
            $h = max( 1, (int) round( $current_height * $ratio ) );
            // Note: $did_width means it is possible $smaller_ratio == $width_ratio.
            if ( $did_width && $w === $max_width - 1 ) $w = $max_width; // Round it up.
            if ( $did_height && $h === $max_height - 1 ) $h = $max_height; // Round it up.
            return $this->_apply_filters( 'tp_constrain_dimensions', [$w, $h], $current_width, $current_height, $max_width, $max_height );
        }//433
    }
}else die;