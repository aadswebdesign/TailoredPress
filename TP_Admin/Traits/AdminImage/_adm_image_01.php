<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-6-2022
 * Time: 23:34
 */
namespace TP_Admin\Traits\AdminImage;
use TP_Core\Libs\Editor\TP_Image_Editor;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_error;

if(ABSPATH){
    trait _adm_image_01{
        use _init_error;
        /**
         * @description File contains all the administration image manipulation functions.
         * @param string|bool $src
         * @param $src_x
         * @param $src_y
         * @param $src_w
         * @param $src_h
         * @param $dst_w
         * @param $dst_h
         * @param bool $src_abs
         * @param string|bool $dst_file
         * @return bool|mixed|null|string|TP_Image_Editor
         */
        protected function _tp_crop_image($src, $src_x, $src_y, $src_w, $src_h, $dst_w, $dst_h, $src_abs = false, $dst_file = false ){
            $src_file = $src;
            if ( is_numeric( $src ) ) { // Handle int as attachment ID.
                $src_file = $this->_get_attached_file( $src );
                if ( ! file_exists( $src_file ) ) {
                    $src = $this->_load_image_to_edit_path( $src, 'full' );
                } else {
                    $src = $src_file;
                }
            }
            $_editor = $this->_tp_get_image_editor( $src );
            $editor = null;
            if($_editor instanceof TP_Image_Editor ){
                $editor = $_editor;
            }
            if ( $this->_init_error( $editor ) ) {
                return $editor;
            }
            $src = $editor->crop( $src_x, $src_y, $src_w, $src_h, $dst_w, $dst_h, $src_abs );
            if ( $this->_init_error( $src ) ) {
                return $src;
            }
            if ( ! $dst_file ) {
                $_basename = $this->_tp_basename( $src_file );
                $_basename_cropped =  'cropped-' . $this->_tp_basename( $src_file );
                $_src = (string)$src_file;
                $dst_file = str_replace( $_basename,$_basename_cropped,$_src );
            }
            $this->_tp_mkdir_p( dirname( $dst_file ) );
            $dst_file = dirname( $dst_file ) . '/' . $this->_tp_unique_filename(dirname( $dst_file ), $this->_tp_basename( $dst_file ) );
            $result = $editor->save( $dst_file );
            if ( $this->_init_error( $result ) ) {
                return $result;
            }
            return $dst_file;
        }//25
        /**
         * @description Compare the existing image sub-sizes (as saved in the attachment meta)
         * @description . to the currently registered image sub-sizes, and return the difference.
         * @param $attachment_id
         * @return array
         */
        protected function _tp_get_missing_image_subsizes( $attachment_id ):array{
            if ( ! $this->_tp_attachment_is_image( $attachment_id ) ) {
                return [];
            }
            $registered_sizes = $this->_tp_get_registered_image_sub_sizes();
            $image_meta       = $this->_tp_get_attachment_metadata( $attachment_id );
            if ( empty( $image_meta ) ) { return $registered_sizes;}
            if ( ! empty( $image_meta['original_image'] ) ) {
                $image_file = $this->_tp_get_original_image_path( $attachment_id );
                $image_size  = $this->_tp_get_image_size( $image_file );
            }
            if ( ! empty( $image_size ) ) {
                $full_width  = $image_size[0];
                /** @noinspection MultiAssignmentUsageInspection */ //todo for later
                $full_height = $image_size[1];
            } else {
                $full_width  = (int) $image_meta['width'];
                $full_height = (int) $image_meta['height'];
            }
            $possible_sizes = [];
            foreach ( $registered_sizes as $size_name => $size_data ) {
                if ( $this->_image_resize_dimensions( $full_width, $full_height, $size_data['width'], $size_data['height'], $size_data['crop'] ) ) {
                    $possible_sizes[ $size_name ] = $size_data;
                }
            }
            if ( empty( $image_meta['sizes'] ) ) {
                $image_meta['sizes'] = array();
            }
            $missing_sizes = array_diff_key( $possible_sizes, $image_meta['sizes'] );
            return $this->_apply_filters( 'tp_get_missing_image_subsizes', $missing_sizes, $image_meta, $attachment_id );
        }//85
        /**
         * @description If any of the currently registered image sub-sizes are missing,
         * @description . create them and update the image meta data.
         * @param $attachment_id
         * @return string|TP_Error
         */
        protected function _tp_update_image_subsizes( $attachment_id ){
            $image_meta = $this->_tp_get_attachment_metadata( $attachment_id );
            $image_file = $this->_tp_get_original_image_path( $attachment_id );
            if ( empty( $image_meta ) || ! is_array( $image_meta ) ) {
                if ( ! empty( $image_file ) ) {
                    $image_meta = $this->_tp_create_image_subsizes( $image_file, $attachment_id );
                } else {
                    return new TP_Error( 'invalid_attachment', $this->__( 'The attached file cannot be found.' ) );
                }
            } else {
                $missing_sizes = $this->_tp_get_missing_image_subsizes( $attachment_id );
                if ( empty( $missing_sizes ) ) {
                    return $image_meta;
                }
                $image_meta = $this->_tp_make_subsizes( $missing_sizes, $image_file, $image_meta, $attachment_id );
            }
            $image_meta = $this->_apply_filters( 'tp_generate_attachment_metadata', $image_meta, $attachment_id, 'update' );
            $this->_tp_update_attachment_metadata( $attachment_id, $image_meta );
            return $image_meta;
        }//157
        /**
         * @description Updates the attached file and image meta data when the original image was edited.
         * @param $saved_data
         * @param $original_file
         * @param $image_meta
         * @param $attachment_id
         * @return mixed
         */
        protected function _tp_image_meta_replace_original( $saved_data, $original_file, $image_meta, $attachment_id ){
            $new_file = $saved_data['path'];
            $this->_update_attached_file( $attachment_id, $new_file );
            $image_meta['width']  = $saved_data['width'];
            $image_meta['height'] = $saved_data['height'];
            $image_meta['file'] = $this->_tp_relative_upload_path( $new_file );
            $image_meta['original_image'] = $this->_tp_basename( $original_file );
            return $image_meta;
        }//201
        /**
         * @description  Creates image sub-sizes, adds the new data to the image
         * @description  . meta `sizes` array, and updates the image metadata.
         * @param $file
         * @param $attachment_id
         * @return array|string
         */
        protected function _tp_create_image_subsizes( $file, $attachment_id ){
            $image_size = $this->_tp_get_image_size( $file );
            if ( empty( $image_size ) ) {return [];}
            $image_meta = ['width' => $image_size[0],'height' => $image_size[1],'file' => $this->_tp_relative_upload_path( $file ),'sizes' => [],];
            $exif_meta = $this->_tp_read_image_metadata( $file );
            if ( $exif_meta ) { $image_meta['image_meta'] = $exif_meta;}
            if ( 'image/png' !== $image_size['mime'] ) {
                $threshold = (int) $this->_apply_filters( 'big_image_size_threshold', 2560, $image_size, $file, $attachment_id );
                if ( $threshold && ( $image_meta['width'] > $threshold || $image_meta['height'] > $threshold ) ) {
                    $_editor = $this->_tp_get_image_editor( $file );
                    $editor = null;
                    if($_editor instanceof TP_Image_Editor ){ $editor = $_editor;}
                    if ( $this->_init_error( $editor ) ) { return $image_meta;}
                    $resized = $editor->resize( $threshold, $threshold );
                    $rotated = null;
                    if (is_array( $exif_meta ) && ! $this->_init_error( $resized )) {
                        $resized = $editor->maybe_exif_rotate();
                        $rotated = $resized;
                    }
                    if ( ! $this->_init_error( $resized ) ) {
                        $saved = $editor->save( $editor->generate_filename( 'scaled' ) );
                        if ( ! $this->_init_error( $saved ) ) {
                            $image_meta = $this->_tp_image_meta_replace_original( $saved, $file, $image_meta, $attachment_id );
                            if ( true === $rotated && ! empty( $image_meta['image_meta']['orientation'] ) ) {
                                $image_meta['image_meta']['orientation'] = 1;
                            }
                        } else {
                            // TODO: Log errors.
                        }
                    } else {
                        // TODO: Log errors.
                    }
                } elseif ( ! empty( $exif_meta['orientation'] ) && 1 !== (int) $exif_meta['orientation'] ) {
                    $_editor = $this->_tp_get_image_editor( $file );
                    $editor = null;
                    if($_editor instanceof TP_Image_Editor ){ $editor = $_editor;}
                    if ( $this->_init_error( $editor ) ) {
                        return $image_meta;
                    }
                    $rotated = $editor->maybe_exif_rotate();
                    if ( true === $rotated ) {
                        $saved = $editor->save( $editor->generate_filename( 'rotated' ) );
                        if ( ! $this->_init_error( $saved ) ) {
                            $image_meta = $this->_tp_image_meta_replace_original( $saved, $file, $image_meta, $attachment_id );
                            if ( ! empty( $image_meta['image_meta']['orientation'] ) ) {
                                $image_meta['image_meta']['orientation'] = 1;
                            }
                        } else {
                            // TODO: Log errors.
                        }
                    }
                }
            }
            $this->_tp_update_attachment_metadata( $attachment_id, $image_meta );
            $new_sizes = $this->_tp_get_registered_image_sub_sizes();
            $new_sizes = $this->_apply_filters( 'intermediate_image_sizes_advanced', $new_sizes, $image_meta, $attachment_id );
            return $this->_tp_make_subsizes( $new_sizes, $file, $image_meta, $attachment_id );
        }//235
        /**
         * @description Low-level function to create image sub-sizes.
         * @param $new_sizes
         * @param $file
         * @param $image_meta
         * @param $attachment_id
         * @return array
         */
        protected function _tp_make_subsizes( $new_sizes, $file, $image_meta, $attachment_id ):array{
            if ( empty( $image_meta ) || ! is_array( $image_meta ) ) {
                return [];
            }
            if ( isset( $image_meta['sizes'] ) && is_array( $image_meta['sizes'] ) ) {
                foreach ( $image_meta['sizes'] as $size_name => $size_meta ) {
                    if ( array_key_exists( $size_name, $new_sizes ) ) { unset( $new_sizes[ $size_name ] );}
                }
            } else { $image_meta['sizes'] = [];}
            if ( empty( $new_sizes ) ) { return $image_meta;}
            $priority = ['medium' => null,'large' => null,'thumbnail' => null,'medium_large' => null,];
            $new_sizes = array_filter( array_merge( $priority, $new_sizes ) );
            $_editor = $this->_tp_get_image_editor( $file );
            $editor = null;
            if($_editor instanceof TP_Image_Editor ){ $editor = $_editor;}
            if ( $this->_init_error( $editor ) ) {
                // The image cannot be edited.
                return $image_meta;
            }
            if ( ! empty( $image_meta['image_meta'] ) ) {
                $rotated = $editor->maybe_exif_rotate();
                if ( $this->_init_error( $rotated ) ) {
                    // TODO: Log errors.
                }
            }
            if ( method_exists( $editor, 'make_subsize' ) ) {
                foreach ( $new_sizes as $new_size_name => $new_size_data ) {
                    $new_size_meta = $editor->make_subsize( $new_size_data );
                    if ( $this->_init_error( $new_size_meta ) ) {
                        // TODO: Log errors.
                    } else {
                        $image_meta['sizes'][ $new_size_name ] = $new_size_meta;
                        $this->_tp_update_attachment_metadata( $attachment_id, $image_meta );
                    }
                }
            } else {
                $created_sizes = $editor->multi_resize( $new_sizes );
                if ( ! empty( $created_sizes ) ) {
                    $image_meta['sizes'] = array_merge( $image_meta['sizes'], $created_sizes );
                    $this->_tp_update_attachment_metadata( $attachment_id, $image_meta );
                }
            }
            return $image_meta;
        }//394
        /**
         * @description Generate attachment meta data and create image sub-sizes for images.
         * @param $attachment_id
         * @param $file
         * @return mixed
         */
        protected function _tp_generate_attachment_metadata( $attachment_id, $file ){
            $attachment = $this->_get_post( $attachment_id );
            $metadata  = [];
            $support   = false;
            $mime_type = $this->_get_post_mime_type( $attachment );
            if ( preg_match( '!^image/!', $mime_type ) && $this->_file_is_displayable_image( $file ) ) {
                // Make thumbnails and other intermediate sizes.
                $metadata = $this->_tp_create_image_subsizes( $file, $attachment_id );
            } elseif ( $this->_tp_attachment_is( 'video', $attachment ) ) {
                $metadata = $this->_tp_read_video_metadata( $file );
                $support  = $this->_current_theme_supports( 'post-thumbnails', 'attachment:video' ) || $this->_post_type_supports( 'attachment:video', 'thumbnail' );
            } elseif ( $this->_tp_attachment_is( 'audio', $attachment ) ) {
                $metadata = $this->_tp_read_audio_metadata( $file );
                $support  = $this->_current_theme_supports( 'post-thumbnails', 'attachment:audio' ) || $this->_post_type_supports( 'attachment:audio', 'thumbnail' );
            }
            if ( ! is_array( $metadata ) ) {$metadata = [];}
            if ( $support && ! empty( $metadata['image']['data'] ) ) {
                $hash   = md5( $metadata['image']['data'] );
                $posts  = $this->_get_posts(['fields' => 'ids','post_type' => 'attachment','post_mime_type' => $metadata['image']['mime'],
                    'post_status' => 'inherit','posts_per_page' => 1,'meta_key' => '_cover_hash','meta_value' => $hash,]);
                $exists = reset( $posts );
                if ( ! empty( $exists ) ) {
                    $this->_update_post_meta( $attachment_id, '_thumbnail_id', $exists );
                } else {
                    $ext = '.jpg';
                    switch ( $metadata['image']['mime'] ) {
                        case 'image/gif':
                            $ext = '.gif';
                            break;
                        case 'image/png':
                            $ext = '.png';
                            break;
                        case 'image/webp':
                            $ext = '.webp';
                            break;
                    }
                    $basename = str_replace( '.', '-', $this->_tp_basename( $file ) ) . '-image' . $ext;
                    $uploaded = $this->_tp_upload_bits( $basename, '', $metadata['image']['data'] );
                    if ( false === $uploaded['error'] ) {
                        $image_attachment = ['post_mime_type' => $metadata['image']['mime'], 'post_type' => 'attachment','post_content' => '',];
                        $image_attachment = $this->_apply_filters( 'attachment_thumbnail_args', $image_attachment, $metadata, $uploaded );
                        $sub_attachment_id = $this->_tp_insert_attachment( $image_attachment, $uploaded['file'] );
                        $this->_add_post_meta( $sub_attachment_id, '_cover_hash', $hash );
                        $attach_data = $this->_tp_generate_attachment_metadata( $sub_attachment_id, $uploaded['file'] );
                        $this->_tp_update_attachment_metadata( $sub_attachment_id, $attach_data );
                        $this->_update_post_meta( $attachment_id, '_thumbnail_id', $sub_attachment_id );
                    }
                }
            } elseif ( 'application/pdf' === $mime_type ) {
                $fallback_sizes = ['thumbnail','medium','large',];
                $fallback_sizes = $this->_apply_filters( 'fallback_intermediate_image_sizes', $fallback_sizes, $metadata );
                $registered_sizes = $this->_tp_get_registered_image_sub_sizes();
                $merged_sizes     = array_intersect_key( $registered_sizes, array_flip( $fallback_sizes ) );
                if ( isset( $merged_sizes['thumbnail'] ) && is_array( $merged_sizes['thumbnail'] ) ) {
                    $merged_sizes['thumbnail']['crop'] = false;
                }
                if ( ! empty( $merged_sizes ) ) {
                    $_editor = $this->_tp_get_image_editor( $file );
                    $editor = null;
                    if($_editor instanceof TP_Image_Editor ){ $editor = $_editor;}
                    if ( ! $this->_init_error( $editor ) ) { // No support for this type of file.
                        $dirname      = dirname( $file ) . '/';
                        $ext          = '.' . pathinfo( $file, PATHINFO_EXTENSION );
                        $preview_file = $dirname . $this->_tp_unique_filename( $dirname, $this->_tp_basename( $file, $ext ) . '-pdf.jpg' );
                        $uploaded = $editor->save( $preview_file, 'image/jpeg' );
                        unset( $editor );
                        if ( ! $this->_init_error( $uploaded ) ) {
                            $image_file = $uploaded['path'];
                            unset( $uploaded['path'] );
                            $metadata['sizes'] = array(
                                'full' => $uploaded,
                            );
                            $this->_tp_update_attachment_metadata( $attachment_id, $metadata );
                            $metadata = $this->_tp_make_subsizes( $merged_sizes, $image_file, $metadata, $attachment_id );
                        }
                    }
                }
            }
            unset( $metadata['image']['data'] );
            return $this->_apply_filters( 'tp_generate_attachment_metadata', $metadata, $attachment_id, 'create' );
        }//485
        /**
         * @description Convert a fraction string to a decimal.
         * @param $str
         * @return float|int
         */
        protected function _tp_exif_frac2dec( $str ){
            if ( ! is_scalar( $str ) || is_bool( $str ) ) { return 0;}
            if ( ! is_string( $str ) ) { return $str; }
            if ( substr_count( $str, '/' ) !== 1 ) {
                if ( is_numeric( $str ) ) {return (float) $str;}
                return 0;
            }
            @list( $numerator, $denominator ) = explode( '/', $str );
            if ( ! is_numeric( $numerator ) || ! is_numeric( $denominator ) ) {
                return 0;}
            if ( 0 === $denominator ) {  return 0;}
            return $numerator / $denominator;
        }//667
        /**
         * @description Convert the exif date format to a unix timestamp.
         * @param $str
         * @return false|int
         */
        protected function _tp_exif_date2ts( $str ){
            @list( $date, $time ) = explode( ' ', trim( $str ) );
            @list( $y, $m, $d )   = explode( ':', $date );
            return strtotime( "{$y}-{$m}-{$d} {$time}" );
        }//708
        /**
         * @description Get extended image metadata, exif or iptc as available.
         * @param $file
         * @return bool
         */
        protected function _tp_read_image_metadata( $file ):bool{
            if ( ! file_exists( $file ) ) {  return false;}
            @list( , , $image_type ) = $this->_tp_get_image_size( $file );
            $meta = ['aperture' => 0,'credit' => '','camera' => '','caption' => '','created_timestamp' => 0,'copyright' => '',
                'focal_length' => 0,'iso' => 0,'shutter_speed' => 0,'title' => '','orientation' => 0,'keywords' => [],];
            $iptc = [];
            $info = [];
            if ( is_callable( 'iptcparse' ) ) {
                $this->_tp_get_image_size( $file, $info );
                if ( ! empty( $info['APP13'] ) ) {
                    if ( defined( 'TP_DEBUG' ) && TP_DEBUG && ! defined( 'TP_RUN_CORE_TESTS' )) {
                        $iptc = iptcparse( $info['APP13'] );
                    } else { $iptc = @iptcparse( $info['APP13'] );}
                    if ( ! empty( $iptc['2#105'][0] ) ) {
                        $meta['title'] = trim( $iptc['2#105'][0] );
                    } elseif ( ! empty( $iptc['2#005'][0] ) ) {
                        $meta['title'] = trim( $iptc['2#005'][0] );
                    }
                    if ( ! empty( $iptc['2#120'][0] ) ) { // Description / legacy caption.
                        $caption = trim( $iptc['2#120'][0] );
                        $this->_mb_string_binary_safe_encoding();
                        $caption_length = strlen( $caption );
                        $this->_reset_mb_string_encoding();
                        if ( empty( $meta['title'] ) && $caption_length < 80 ) {
                            $meta['title'] = $caption;
                        }
                        $meta['caption'] = $caption;
                    }
                    if ( ! empty( $iptc['2#110'][0] ) ) { // Credit.
                        $meta['credit'] = trim( $iptc['2#110'][0] );
                    } elseif ( ! empty( $iptc['2#080'][0] ) ) { // Creator / legacy byline.
                        $meta['credit'] = trim( $iptc['2#080'][0] );
                    }
                    if ( ! empty( $iptc['2#055'][0] ) && ! empty( $iptc['2#060'][0] ) ) { // Created date and time.
                        $meta['created_timestamp'] = strtotime( $iptc['2#055'][0] . ' ' . $iptc['2#060'][0] );
                    }
                    if ( ! empty( $iptc['2#116'][0] ) ) { // Copyright.
                        $meta['copyright'] = trim( $iptc['2#116'][0] );
                    }
                    if ( ! empty( $iptc['2#025'][0] ) ) { // Keywords array.
                        $meta['keywords'] = array_values( $iptc['2#025'] );
                    }
                }
            }
            $exif = [];
            $exif_image_types = $this->_apply_filters( 'tp_read_image_metadata_types', array( IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM ) );
            if ( is_callable( 'exif_read_data' ) && in_array( $image_type, $exif_image_types, true ) ) {
                if ( defined( 'TP_DEBUG' ) && TP_DEBUG && ! defined( 'TP_RUN_CORE_TESTS' )) {
                    $exif = exif_read_data( $file );
                } else {
                    $exif = @exif_read_data( $file );
                }
                if ( ! empty( $exif['ImageDescription'] ) ) {
                    $this->_mb_string_binary_safe_encoding();
                    $description_length = strlen( $exif['ImageDescription'] );
                    $this->_reset_mb_string_encoding();
                    if ( empty( $meta['title'] ) && $description_length < 80 ) {
                        // Assume the title is stored in ImageDescription.
                        $meta['title'] = trim( $exif['ImageDescription'] );
                    }
                    if ( empty( $meta['caption'] ) && ! empty( $exif['COMPUTED']['UserComment'] ) ) {
                        $meta['caption'] = trim( $exif['COMPUTED']['UserComment'] );
                    }
                    if ( empty( $meta['caption'] ) ) {
                        $meta['caption'] = trim( $exif['ImageDescription'] );
                    }
                } elseif ( empty( $meta['caption'] ) && ! empty( $exif['Comments'] ) ) {
                    $meta['caption'] = trim( $exif['Comments'] );
                }
                if ( empty( $meta['credit'] ) ) {
                    if ( ! empty( $exif['Artist'] ) ) {
                        $meta['credit'] = trim( $exif['Artist'] );
                    } elseif ( ! empty( $exif['Author'] ) ) {
                        $meta['credit'] = trim( $exif['Author'] );
                    }
                }
                if ( empty( $meta['copyright'] ) && ! empty( $exif['Copyright'] ) ) {
                    $meta['copyright'] = trim( $exif['Copyright'] );
                }
                if ( ! empty( $exif['FNumber'] ) && is_scalar( $exif['FNumber'] ) ) {
                    $meta['aperture'] = round( $this->_tp_exif_frac2dec( $exif['FNumber'] ), 2 );
                }
                if ( ! empty( $exif['Model'] ) ) {
                    $meta['camera'] = trim( $exif['Model'] );
                }
                if ( empty( $meta['created_timestamp'] ) && ! empty( $exif['DateTimeDigitized'] ) ) {
                    $meta['created_timestamp'] = $this->_tp_exif_date2ts( $exif['DateTimeDigitized'] );
                }
                if ( ! empty( $exif['FocalLength'] ) ) {
                    $meta['focal_length'] = (string) $exif['FocalLength'];
                    if ( is_scalar( $exif['FocalLength'] ) ) {
                        $meta['focal_length'] = (string) $this->_tp_exif_frac2dec( $exif['FocalLength'] );
                    }
                }
                if ( ! empty( $exif['ISOSpeedRatings'] ) ) {
                    $meta['iso'] = is_array( $exif['ISOSpeedRatings'] ) ? reset( $exif['ISOSpeedRatings'] ) : $exif['ISOSpeedRatings'];
                    $meta['iso'] = trim( $meta['iso'] );
                }
                if ( ! empty( $exif['ExposureTime'] ) ) {
                    $meta['shutter_speed'] = (string) $exif['ExposureTime'];
                    if ( is_scalar( $exif['ExposureTime'] ) ) {
                        $meta['shutter_speed'] = (string) $this->_tp_exif_frac2dec( $exif['ExposureTime'] );
                    }
                }
                if ( ! empty( $exif['Orientation'] ) ) {
                    $meta['orientation'] = $exif['Orientation'];
                }
            }
            foreach ( array( 'title', 'caption', 'credit', 'copyright', 'camera', 'iso' ) as $key ) {
                if ( $meta[ $key ] && ! $this->_seems_utf8( $meta[ $key ] ) ) {
                    $meta[ $key ] = utf8_encode( $meta[ $key ] );
                }
            }
            foreach ( $meta['keywords'] as $key => $keyword ) {
                if ( ! $this->_seems_utf8( $keyword ) ) {
                    $meta['keywords'][ $key ] = utf8_encode( $keyword );
                }
            }
            $meta = $this->_tp_kses_post_deep( $meta );
            return $this->_apply_filters( 'tp_read_image_metadata', $meta, $file, $image_type, $iptc, $exif );
        }//731
    }
}else die;