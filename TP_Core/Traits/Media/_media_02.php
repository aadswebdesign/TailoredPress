<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-3-2022
 * Time: 04:04
 */
namespace TP_Core\Traits\Media;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_images;
use TP_Core\Libs\Editor\TP_Image_Editor_GD;
if(ABSPATH){
    trait _media_02 {
        use _init_images;
        use _init_error;
        /**
         * @description Retrieves calculated resize dimensions for use in WP_Image_Editor.
         * @param $orig_w
         * @param $orig_h
         * @param $dest_w
         * @param $dest_h
         * @param mixed $crop
         * @return array|bool
         */
        protected function _image_resize_dimensions( $orig_w, $orig_h, $dest_w, $dest_h, $crop = false ){
            if ( $orig_w <= 0 || $orig_h <= 0 ) return false;
            if ( $dest_w <= 0 && $dest_h <= 0 ) return false;
            $output = $this->_apply_filters( 'image_resize_dimensions', null, $orig_w, $orig_h, $dest_w, $dest_h, $crop );
            if ( null !== $output ) return $output;
            if ( empty( $dest_h ) ) {
                if ( $orig_w < $dest_w ) return false;
            } elseif ( empty( $dest_w ) ) {
                if ( $orig_h < $dest_h ) return false;
            } else if ( $orig_w < $dest_w && $orig_h < $dest_h ) return false;
            if ( $crop ) {
                $aspect_ratio = $orig_w / $orig_h;
                $new_w        = min( $dest_w, $orig_w );
                $new_h        = min( $dest_h, $orig_h );
                if ( ! $new_w ) $new_w = (int) round( $new_h * $aspect_ratio );
                if ( ! $new_h ) $new_h = (int) round( $new_w / $aspect_ratio );
                $size_ratio = max( $new_w / $orig_w, $new_h / $orig_h );
                $crop_w = round( $new_w / $size_ratio );
                $crop_h = round( $new_h / $size_ratio );
                if ( ! is_array( $crop ) || count( $crop ) !== 2 ) $crop = ['center', 'center'];
                @list( $x, $y ) = $crop;
                if ( 'left' === $x ) $s_x = 0;
                 elseif ( 'right' === $x )  $s_x = $orig_w - $crop_w;
                 else $s_x = floor( ( $orig_w - $crop_w ) / 2 );
                if ( 'top' === $y ) $s_y = 0;
                elseif ( 'bottom' === $y ) $s_y = $orig_h - $crop_h;
                else $s_y = floor( ( $orig_h - $crop_h ) / 2 );
            } else {
                $crop_w = $orig_w;
                $crop_h = $orig_h;
                $s_x = 0;
                $s_y = 0;
                @list( $new_w, $new_h ) = $this->_tp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
            }
            if ( $this->_tp_fuzzy_number_match( $new_w, $orig_w ) && $this->_tp_fuzzy_number_match( $new_h, $orig_h ) ) {
                $proceed = (bool) $this->_apply_filters( 'tp_image_resize_identical_dimensions', false, $orig_w, $orig_h );
                if ( ! $proceed )  return false;
            }
            return [ 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h];
        }//530
        /**
         * @description Resizing an image to make a thumbnail or intermediate size.
         * @param $file
         * @param $width
         * @param $height
         * @param bool $crop
         * @return array|bool|\TP_Core\Libs\TP_Error
         */
        protected function _image_make_intermediate_size( $file, $width, $height, $crop = false ){
            if ( $width || $height ) {
                $editor = $this->_tp_get_image_editor( $file );
                if( $editor instanceof TP_Image_Editor_GD ){}
                if ( $this->_init_error( $editor ) || $this->_init_error( $editor->resize( $width, $height, $crop ) ) )
                    return false;
                $resized_file = $editor->save();
                if ($resized_file && ! $this->_init_error( $resized_file )) {
                    unset( $resized_file['path'] );
                    return $resized_file;
                }
            }
            return false;
        }//676
        /**
         * @description Helper function to test if aspect ratios for two images match
         * @param $source_width
         * @param $source_height
         * @param $target_width
         * @param $target_height
         * @return bool
         */
        protected function _tp_image_matches_ratio( $source_width, $source_height, $target_width, $target_height ):bool{
            if ( $source_width > $target_width ) {
                $constrained_size = $this->_tp_constrain_dimensions( $source_width, $source_height, $target_width );
                $expected_size    = [$target_width, $target_height];
            } else {
                $constrained_size = $this->_tp_constrain_dimensions( $target_width, $target_height, $source_width );
                $expected_size    = [$source_width, $source_height];
            }
            $matched = ( $this->_tp_fuzzy_number_match( $constrained_size[0], $expected_size[0] ) && $this->_tp_fuzzy_number_match( $constrained_size[1], $expected_size[1] ) );
            return $matched;
        }//705
        /**
         * @description Retrieves the image's intermediate size (resized) path, width, and height.
         * @param $post_id
         * @param mixed $size
         * @return bool
         */
        protected function _image_get_intermediate_size( $post_id, $size = 'thumbnail' ):bool{
            $imagedata = $this->_tp_get_attachment_metadata( $post_id );
            if ( ! $size || ! is_array( $imagedata ) || empty( $imagedata['sizes'] ) )
                return false;
            $data = [];
            if ( is_array( $size ) ) {
                $candidates = [];
                if ( ! isset( $imagedata['file'] ) && isset( $imagedata['sizes']['full'] ) ) {
                    $imagedata['height'] = $imagedata['sizes']['full']['height'];
                    $imagedata['width']  = $imagedata['sizes']['full']['width'];
                }
                foreach ( $imagedata['sizes'] as $_size => $data ) {
                    if ( (int) $data['width'] === (int) $size[0] && (int) $data['height'] === (int) $size[1] ) {
                        $candidates[ $data['width'] * $data['height'] ] = $data;
                        break;
                    }
                    if ( $data['width'] >= $size[0] && $data['height'] >= $size[1] ) {
                        if ( 0 === $size[0] || 0 === $size[1] )
                            $same_ratio = $this->_tp_image_matches_ratio( $data['width'], $data['height'], $imagedata['width'], $imagedata['height'] );
                        else $same_ratio = $this->_tp_image_matches_ratio( $data['width'], $data['height'], $size[0], $size[1] );
                        if ( $same_ratio ) $candidates[ $data['width'] * $data['height'] ] = $data;
                     }
                }
                if ( ! empty( $candidates ) ) {
                    if ( 1 < count( $candidates ) ) ksort( $candidates );
                    $data = array_shift( $candidates );
                } elseif ( ! empty( $imagedata['sizes']['thumbnail'] ) && $imagedata['sizes']['thumbnail']['width'] >= $size[0] && $imagedata['sizes']['thumbnail']['width'] >= $size[1] )
                    $data = $imagedata['sizes']['thumbnail'];
                else return false;
                @list( $data['width'], $data['height'] ) = $this->_image_constrain_size_for_editor( $data['width'], $data['height'], $size );
            } elseif ( ! empty( $imagedata['sizes'][ $size ] ) ) $data = $imagedata['sizes'][ $size ];
            if ( empty( $data ) ) return false;
            if ( empty( $data['path'] ) && ! empty( $data['file'] ) && ! empty( $imagedata['file'] ) ) {
                $file_url     = $this->_tp_get_attachment_url( $post_id );
                $data['path'] = $this->_path_join( dirname( $imagedata['file'] ), $data['file'] );
                $data['url']  = $this->_path_join( dirname( $file_url ), $data['file'] );
            }
            return $this->_apply_filters( 'image_get_intermediate_size', $data, $post_id, $size );
        }//759
        /**
         * @description Gets the available intermediate image size names.
         * @return mixed
         */
        protected function _get_intermediate_image_sizes(){
            $default_sizes    = array( 'thumbnail', 'medium', 'medium_large', 'large' );
            $additional_sizes = $this->_tp_get_additional_image_sizes();
            if ( ! empty( $additional_sizes ) )
                $default_sizes = array_merge( $default_sizes, array_keys( $additional_sizes ) );
            return $this->_apply_filters( 'intermediate_image_sizes', $default_sizes );
        }//859
        /**
         * @description Returns a normalized list of all currently registered image sub-sizes.
         * @return array
         */
        protected function _tp_get_registered_image_sub_sizes():array{
            $additional_sizes = $this->_tp_get_additional_image_sizes();
            $all_sizes        = [];
            foreach ((array) $this->_get_intermediate_image_sizes() as $size_name ) {
                $size_data = ['width'  => 0,'height' => 0,'crop' => false,];
                if ( isset( $additional_sizes[ $size_name ]['width'] ) )
                    $size_data['width'] = (int) $additional_sizes[ $size_name ]['width'];
                else $size_data['width'] = (int) $this->_get_option( "{$size_name}_size_w" );
                if ( isset( $additional_sizes[ $size_name ]['height'] ) )
                    $size_data['height'] = (int) $additional_sizes[ $size_name ]['height'];
                else $size_data['height'] = (int) $this->_get_option( "{$size_name}_size_h" );
                if ( empty( $size_data['width'] ) && empty( $size_data['height'] ) )
                    continue;
                if ( isset( $additional_sizes[ $size_name ]['crop'] ) )
                    $size_data['crop'] = $additional_sizes[ $size_name ]['crop'];
                else $size_data['crop'] = $this->_get_option( "{$size_name}_crop" );
                if ( ! is_array( $size_data['crop'] ) || empty( $size_data['crop'] ) )
                    $size_data['crop'] = (bool) $size_data['crop'];
                $all_sizes[ $size_name ] = $size_data;
            }
            return $all_sizes;
        }//888
        /**
         * @description Retrieves an image to represent an attachment.
         * @param $attachment_id
         * @param string $size
         * @param bool $icon
         * @return mixed
         */
        protected function _tp_get_attachment_image_src( $attachment_id, $size = 'thumbnail', $icon = false ){
            $image = $this->_image_downsize( $attachment_id, $size );
            $width = '';
            $height = '';
            if ( ! $image ) {
                $src = false;
                if ( $icon ) {
                    $src = $this->_tp_mime_type_icon( $attachment_id );
                    if ( $src ) {
                        $icon_dir = $this->_apply_filters( 'icon_dir', TP_CORE_ASSETS . '/images/media' );
                        $src_file               = $icon_dir . '/' . $this->_tp_basename( $src );
                        @list( $width, $height ) = $this->_tp_get_image_size( $src_file );
                    }
                }
                if ( $src && $width && $height ) {
                    $image = array( $src, $width, $height, false );
                }
            }
            return $this->_apply_filters( 'tp_get_attachment_image_src', $image, $attachment_id, $size, $icon );
        }//952
        /**
         * @description Get an HTML img element representing an image attachment.
         * @param $attachment_id
         * @param string $size
         * @param bool $icon
         * @param array ...$attr
         * @return mixed
         */
        protected function _tp_get_attachment_image($attachment_id, $size='thumbnail',$icon = false, ...$attr){
            $html  = '';
            $image = $this->_tp_get_attachment_image_src( $attachment_id, $size, $icon );
            if ( $image ) {
                @list( $src, $width, $height ) = $image;
                $attachment = $this->_get_post( $attachment_id );
                $hwstring   = $this->_image_hwstring( $width, $height );
                $size_class = $size;
                if ( is_array( $size_class ) ) $size_class = implode( 'x', $size_class );
                $default_attr = [
                    'src'   => $src,
                    'class' => "attachment-$size_class size-$size_class",
                    'alt'   => trim( strip_tags( $this->_get_post_meta( $attachment_id, '_tp_attachment_image_alt', true ) ) ),
                ];
                if ( $this->_tp_lazy_loading_enabled( 'img', 'tp_get_attachment_image' ) )
                    $default_attr['loading'] = $this->_tp_get_loading_attr_default( 'tp_get_attachment_image' );
                $attr = $this->_tp_parse_args( $attr, $default_attr );
                if ( array_key_exists( 'loading', $attr ) && ! $attr['loading'] )
                    unset( $attr['loading'] );
                if ( empty( $attr['srcset'] ) ) {
                    $image_meta = $this->_tp_get_attachment_metadata( $attachment_id );
                    if ( is_array( $image_meta ) ) {
                        $size_array = array( $this->_abs_int( $width ), $this->_abs_int( $height ) );
                        $srcset     = $this->_tp_calculate_image_srcset( $size_array, $src, $image_meta, $attachment_id );
                        $sizes      = $this->_tp_calculate_image_sizes( $size_array, $src, $image_meta, $attachment_id );
                        if ( $srcset && ( $sizes || ! empty( $attr['sizes'] ) ) ) {
                            $attr['srcset'] = $srcset;
                            if ( empty( $attr['sizes'] ) ) $attr['sizes'] = $sizes;
                        }
                    }
                }
                $attr = $this->_apply_filters( 'tp_get_attachment_image_attributes', $attr, $attachment, $size );
                $attr = array_map( 'esc_attr', $attr );
                $html = rtrim( "<img $hwstring" );
                foreach ( $attr as $name => $value )
                    $html .= " $name='$value' ";
                 $html .= ' />';
            }
            return $this->_apply_filters( 'tp_get_attachment_image', $html, $attachment_id, $size, $icon, $attr );
        }//1027
        /**
         * @description Get the URL of an image attachment.
         * @param $attachment_id
         * @param string $size
         * @param bool $icon
         * @return bool
         */
        protected function _tp_get_attachment_image_url( $attachment_id, $size='thumbnail',$icon = false ):bool{
            $image = $this->_tp_get_attachment_image_src( $attachment_id, $size, $icon );
            return $image[0] ?? false;
        }//1131
        /**
         * @description Get the attachment path relative to the upload directory.
         * @param $file
         * @return string
         */
        protected function _tp_get_attachment_relative_path( $file ):string{
            $dirname = dirname( $file );
            if ( '.' === $dirname ) return '';
            if ( false !== strpos( $dirname, 'tp-content/uploads' ) ) {
                $dirname = substr( $dirname, strpos( $dirname, 'tp-content/uploads' ) + 18 );
                $dirname = ltrim( $dirname, '/' );
            }
            return $dirname;
        }//1145
    }
}else die;