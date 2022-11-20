<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-5-2022
 * Time: 19:58
 */
namespace TP_Core\Libs\Editor;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Media\_media_02;
use TP_Core\Traits\Media\_media_06;
use TP_Core\Traits\Media\_media_08;
if(ABSPATH){
    class TP_Image_Editor_GD extends TP_Image_Editor {
        use _media_02, _media_06, _media_08;
        protected $_image;
        public function __destruct() {
            if ( $this->_image )
                imagedestroy( $this->_image );
        }//29
        /**
         * @param array ...$args
         * @return mixed
         */
        public static function test( ...$args) {
            if (! function_exists( 'gd_info' ) || ! extension_loaded( 'gd' ))
                return false;
            if (! function_exists( 'imagerotate' ) && isset( $args['methods'] ) && in_array( 'rotate', $args['methods'], true ))//todo
                return false;
            return true;
        }//39
        /**
         * @param $mime_type
         * @return mixed
         */
        public static function supports_mime_type( $mime_type ) {
            $image_types = imagetypes();
            switch ( $mime_type ) {
                case 'image/jpeg':
                    return ( $image_types & IMG_JPG ) !== 0;
                case 'image/png':
                    return ( $image_types & IMG_PNG ) !== 0;
                case 'image/gif':
                    return ( $image_types & IMG_GIF ) !== 0;
                case 'image/webp':
                    return ( $image_types & IMG_WEBP ) !== 0;
            }
            return false;
        }//63
        public function load() {
            if ( $this->_image ) return true;
            if ( ! is_file( $this->_file ) && ! preg_match( '|^https?://|', $this->_file ) )
                return new TP_Error( 'error_loading_image', $this->__( 'File doesn&#8217;t exist?' ), $this->_file );
            $this->_tp_raise_memory_limit( 'image' );
            $file_contents = @file_get_contents( $this->_file );
            if ( ! $file_contents )
                return new TP_Error( 'error_loading_image', $this->__( 'File doesn&#8217;t exist?' ), $this->_file );
            if ( function_exists( 'imagecreatefromwebp' ) && ( 'image/webp' === $this->_tp_get_image_mime( $this->_file ) ))
                $this->_image = @imagecreatefromwebp( $this->_file );
            else $this->_image = @imagecreatefromstring( $file_contents );
            if ( ! $this->_is_gd_image( $this->_image ) )
                return new TP_Error( 'invalid_image', $this->__( 'File is not an image.' ), $this->_file );
            $size = $this->_tp_get_image_size( $this->_file );
            if ( ! $size )
                return new TP_Error( 'invalid_image', $this->__( 'Could not read image size.' ), $this->_file );
            if ( function_exists( 'imagealphablending' ) && function_exists( 'imagesavealpha' ) ) {
                imagealphablending( $this->_image, false );
                imagesavealpha( $this->_image, true );
            }
            $this->_update_size( $size[0], $size[1] );
            $this->_mime_type = $size['mime'];
            return $this->set_quality();
        }//86
        protected function _update_size( $width = null, $height = null ):int {
            if ( ! $width )  $width = (int) imagesx($this->_image );
            if ( ! $height ) $height = (int) imagesy( $this->_image );
            return parent::_update_size( $width, $height );
        }//144
        public function resize( $max_w, $max_h, $crop = false ) {
            if ( ( $this->_size['width'] === $max_w ) && ( $this->_size['height'] === $max_h ) )
                return true;
            $resized = $this->_resize( $max_w, $max_h, $crop );
            if ( $this->_is_gd_image( $resized ) ) {
                imagedestroy( $this->_image );
                $this->_image = $resized;
                return true;
            } elseif ( $this->_init_error( $resized ) ) return $resized;
            return new TP_Error( 'image_resize_error', $this->__( 'Image resize failed.' ), $this->_file );
        }//171
        protected function _resize( $max_w, $max_h, $crop = false ) {
            $dims = $this->_image_resize_dimensions( $this->_size['width'], $this->_size['height'], $max_w, $max_h, $crop );
            if ( ! $dims ) return new TP_Error( 'error_getting_dimensions', $this->__( 'Could not calculate resized image dimensions' ), $this->_file );
            @list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;
            $resized = $this->_tp_image_create_true_color( $dst_w, $dst_h );
            imagecopyresampled( $resized, $this->_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h );
            if ( $this->_is_gd_image( $resized ) ) {
                $this->_update_size( $dst_w, $dst_h );
                return $resized;
            }
            return new TP_Error( 'image_resize_error', $this->__( 'Image resize failed.' ), $this->_file );
        }//196
        public function multi_resize( $sizes ):array {
            $metadata = array();
            foreach ( $sizes as $size => $size_data ) {
                $meta = $this->make_subsize( $size_data );
                if ( ! $this->_init_error( $meta ) ) $metadata[ $size ] = $meta;
            }
            return $metadata;
        }//246
        public function make_subsize( $size_data ) {
            if ( ! isset( $size_data['width'] ) && ! isset( $size_data['height'] ) )
                return new TP_Error( 'image_subsize_create_error', $this->__( 'Cannot resize the image. Both width and height are not set.' ) );
            $orig_size = $this->_size;
            if ( ! isset( $size_data['width'] ) ) $size_data['width'] = null;
            if ( ! isset( $size_data['height'] ) ) $size_data['height'] = null;
            if ( ! isset( $size_data['crop'] ) ) $size_data['crop'] = false;
            $resized = $this->_resize( $size_data['width'], $size_data['height'], $size_data['crop'] );
            if ( $this->_init_error( $resized ) ) $saved = $resized;
            else {
                $saved = $this->_save( $resized );
                imagedestroy( $resized );
            }
            $this->_size = $orig_size;
            if ( ! $this->_init_error( $saved ) ) unset( $saved['path'] );
            return $saved;
        }//275
        public function crop( $src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false ) {
            if ( ! $dst_w ) $dst_w = $src_w;
            if ( ! $dst_h ) $dst_h = $src_h;
            foreach ( array( $src_w, $src_h, $dst_w, $dst_h ) as $value ) {
                if ( ! is_numeric( $value ) || (int) $value <= 0 )
                    return new TP_Error( 'image_crop_error', $this->__( 'Image crop failed.' ), $this->_file );
            }
            $dst = $this->_tp_image_create_true_color( (int) $dst_w, (int) $dst_h );
            if ( $src_abs ) {
                $src_w -= $src_x;
                $src_h -= $src_y;
            }
            if ( function_exists( 'imageantialias' ) ) imageantialias( $dst, true );
            imagecopyresampled( $dst, $this->_image, 0, 0, (int) $src_x, (int) $src_y, (int) $dst_w, (int) $dst_h, (int) $src_w, (int) $src_h );
            if ( $this->_is_gd_image( $dst ) ) {
                imagedestroy( $this->_image );
                $this->_image = $dst;
                $this->_update_size();
                return true;
            }
            return new TP_Error( 'image_crop_error', $this->__( 'Image crop failed.' ), $this->_file );
        }//326
        public function rotate( $angle ) {
            if ( function_exists( 'imagerotate' ) ) {
                $transparency = imagecolorallocatealpha( $this->_image, 255, 255, 255, 127 );
                $rotated      = imagerotate( $this->_image, $angle, $transparency );
                if ( $this->_is_gd_image( $rotated ) ) {
                    imagealphablending( $rotated, true );
                    imagesavealpha( $rotated, true );
                    imagedestroy( $this->_image );
                    $this->_image = $rotated;
                    $this->_update_size();
                    return true;
                }
            }
            return new TP_Error( 'image_rotate_error', $this->__( 'Image rotate failed.' ), $this->_file );
        }//374
        public function flip( $horz, $vert ) {
            $w   = $this->_size['width'];
            $h   = $this->_size['height'];
            $dst = $this->_tp_image_create_true_color( $w, $h );
            if ( $this->_is_gd_image( $dst ) ) {
                $sx = $vert ? ( $w - 1 ) : 0;
                $sy = $horz ? ( $h - 1 ) : 0;
                $sw = $vert ? -$w : $w;
                $sh = $horz ? -$h : $h;
                if ( imagecopyresampled( $dst, $this->_image, 0, 0, $sx, $sy, $w, $h, $sw, $sh ) ) {
                    imagedestroy( $this->_image );
                    $this->_image = $dst;
                    return true;
                }
            }
            return new TP_Error( 'image_flip_error', $this->__( 'Image flip failed.' ), $this->_file );
        }//401
        public function save( $destfilename = null, $mime_type = null ) {
            $saved = $this->_save( $this->_image, $destfilename, $mime_type );
            if ( ! $this->_init_error( $saved ) ) {
                $this->_file      = $saved['path'];
                $this->_mime_type = $saved['mime-type'];
            }
            return $saved;
        }//433
        protected function _save( $image, $filename = null, $mime_type = null ) {
            @list( $filename, $extension, $mime_type ) = $this->_get_output_format( $filename, $mime_type );
            if ( ! $filename ) $filename = $this->generate_filename( null, null, $extension );
            if ( 'image/gif' === $mime_type ) {
                if ( ! $this->_make_image( $filename, 'imagegif', array( $image, $filename ) ) )
                    return new TP_Error( 'image_save_error', $this->__( 'Image Editor Save Failed' ) );
            } elseif ( 'image/png' === $mime_type ) {
                if ( function_exists( 'imageistruecolor' ) && ! imageistruecolor( $image ) )
                    imagetruecolortopalette( $image, false, imagecolorstotal( $image ) );
                if ( ! $this->_make_image( $filename, 'imagepng', array( $image, $filename ) ) )
                    return new TP_Error( 'image_save_error', $this->__( 'Image Editor Save Failed' ) );
            } elseif ( 'image/jpeg' === $mime_type ) {
                if ( ! $this->_make_image( $filename, 'imagejpeg', array( $image, $filename, $this->get_quality() ) ) )
                    return new TP_Error( 'image_save_error', $this->__( 'Image Editor Save Failed' ) );
            } elseif ( 'image/webp' === $mime_type ) {
                if ( ! function_exists( 'imagewebp' ) || ! $this->_make_image( $filename, 'imagewebp', array( $image, $filename, $this->get_quality() ) ) )
                    return new TP_Error( 'image_save_error', $this->__( 'Image Editor Save Failed' ) );
            } else  return new TP_Error( 'image_save_error', $this->__( 'Image Editor Save Failed' ) );
            $stat  = stat( dirname( $filename ) );
            $perms = $stat['mode'] & 0000666; // Same permissions as parent folder, strip off the executable bits.
            chmod( $filename, $perms );
            return ['path' => $filename,'file' => $this->_tp_basename( $this->_apply_filters( 'image_make_intermediate_size', $filename ) ),
                'width' => $this->_size['width'],'height' => $this->_size['height'],'mime-type' => $mime_type,];
        }//450
        public function stream( $mime_type = null) {
            @list($mime_type) = $this->_get_output_format( null, $mime_type );
            switch ( $mime_type ) { //todo $filename, $extension in list
                case 'image/png':
                    header( 'Content-Type: image/png' );
                    return imagepng( $this->_image );
                case 'image/gif':
                    header( 'Content-Type: image/gif' );
                    return imagegif( $this->_image );
                case 'image/webp':
                    if ( function_exists( 'imagewebp' ) ) {
                        header( 'Content-Type: image/webp' );
                        return imagewebp( $this->_image, null, $this->get_quality() );
                    }
                    break;
                default:
                    header( 'Content-Type: image/jpeg' );
                    return imagejpeg( $this->_image, null, $this->get_quality() );
            }
        }//511
        protected function _make_image( $filename, $function, $arguments ) {
            if ( $this->_tp_is_stream( $filename ) )
                $arguments[1] = null;
            return parent::_make_image( $filename, $function, $arguments );
        }//543
    }
}else die;