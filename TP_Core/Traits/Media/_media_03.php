<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-3-2022
 * Time: 04:04
 */
namespace TP_Core\Traits\Media;
if(ABSPATH){
    trait _media_03 {
        /**
         * @description Get the image size as array from its meta data.
         * @param $size_name
         * @param $image_meta
         * @return array|bool
         */
        protected function _tp_get_image_size_from_meta( $size_name, $image_meta ){
            if ( 'full' === $size_name ) {
                return array(
                    $this->_abs_int( $image_meta['width'] ),
                    $this->_abs_int( $image_meta['height'] ),
                );
            }
            if ( ! empty( $image_meta['sizes'][ $size_name ] ) ) {
                return array(
                    $this->_abs_int( $image_meta['sizes'][ $size_name ]['width'] ),
                    $this->_abs_int( $image_meta['sizes'][ $size_name ]['height'] ),
                );
            }
            return false;
        }//1178
        /**
         * @description Retrieves the value for an image attachment's 'srcset' attribute.
         * @param $attachment_id
         * @param string $size
         * @param null $image_meta
         * @return bool|string
         */
        protected function _tp_get_attachment_image_srcset( $attachment_id, $size = 'medium', $image_meta = null ){
            $image = $this->_tp_get_attachment_image_src( $attachment_id, $size );
            if ( ! $image ) return false;
            if ( ! is_array( $image_meta ) )
                $image_meta = $this->_tp_get_attachment_metadata( $attachment_id );
            $image_src  = $image[0];
            $size_array = array(
                $this->_abs_int( $image[1] ),
                $this->_abs_int( $image[2] ),
            );
            return $this->_tp_calculate_image_srcset( $size_array, $image_src, $image_meta, $attachment_id );
        }//1208
        /**
         * @description A helper function to calculate the image sources to include in a 'srcset' attribute.
         * @param $size_array
         * @param $image_src
         * @param $image_meta
         * @param int $attachment_id
         * @return bool|string
         */
        protected function _tp_calculate_image_srcset( $size_array, $image_src, $image_meta, $attachment_id = 0 ){
           $image_meta = $this->_apply_filters( 'tp_calculate_image_srcset_meta', $image_meta, $size_array, $image_src, $attachment_id );
           if ( empty( $image_meta['sizes'] ) || ! isset( $image_meta['file'] ) || strlen( $image_meta['file'] ) < 4 )
               return false;
           $image_sizes = $image_meta['sizes'];
           $image_width  = (int) $size_array[0];
           $image_height = (int) $size_array[1];
           if ( $image_width < 1 ) return false;
           $image_basename = $this->_tp_basename( $image_meta['file'] );
           if ( ! isset( $image_sizes['thumbnail']['mime-type'] ) || 'image/gif' !== $image_sizes['thumbnail']['mime-type'] )
               $image_sizes[] = ['width' => $image_meta['width'],'height' => $image_meta['height'],'file' => $image_basename,];
           elseif ( strpos( $image_src, $image_meta['file'] ) ) return false;
           $dirname = $this->_tp_get_attachment_relative_path( $image_meta['file'] );
           if ( $dirname ) $dirname = $this->_trailingslashit( $dirname );
           $upload_dir    = $this->_tp_get_upload_dir();
           $image_baseurl = $this->_trailingslashit( $upload_dir['baseurl'] ) . $dirname;
           if ( $this->_is_ssl() && strpos($image_baseurl, 'https') !== 0 && parse_url( $image_baseurl, PHP_URL_HOST ) === $_SERVER['HTTP_HOST'] )
               $image_baseurl = $this->_set_url_scheme( $image_baseurl, 'https' );
           $image_edited = preg_match( '/-e\d{13}/', $this->_tp_basename( $image_src ), $image_edit_hash );
           $max_srcset_image_width = $this->_apply_filters( 'max_srcset_image_width', 2048, $size_array );
           $sources = [];
           $src_matched = false;
           foreach ( $image_sizes as $image ) {
               $is_src = false;
               if ( ! is_array( $image ) ) continue;
               if ( ! $src_matched && false !== strpos( $image_src, $dirname . $image['file'] ) ) {
                   $src_matched = true;
                   $is_src      = true;
               }
               if ( $image_edited && ! strpos( $image['file'], $image_edit_hash[0] ) ) continue;
               if ( $max_srcset_image_width && $image['width'] > $max_srcset_image_width && ! $is_src ) continue;
               if ( $this->_tp_image_matches_ratio( $image_width, $image_height, $image['width'], $image['height'] ) ) {
                   $source = ['url' => $image_baseurl . $image['file'],'descriptor' => 'w','value' => $image['width'],];
                   if ( $is_src )  $sources = array( $image['width'] => $source ) + $sources;
                   else  $sources[ $image['width'] ] = $source;
               }
           }
           $sources = $this->_apply_filters( 'tp_calculate_image_srcset', $sources, $size_array, $image_src, $image_meta, $attachment_id );
           if ( ! $src_matched || ! is_array( $sources ) || count( $sources ) < 2 )
               return false;
           $srcset = '';
           foreach ( $sources as $source )
               $srcset .= str_replace( ' ', '%20', $source['url'] ) . ' ' . $source['value'] . $source['descriptor'] . ', ';
           return rtrim( $srcset, ', ' );
       }//1244
        /**
         * @description Retrieves the value for an image attachment's 'sizes' attribute.
         * @param $attachment_id
         * @param string $size
         * @param null $image_meta
         * @return bool|string
         */
        protected function _tp_get_attachment_image_sizes( $attachment_id, $size = 'medium', $image_meta = null ){
            $image = $this->_tp_get_attachment_image_src( $attachment_id, $size );
            if ( ! $image )  return false;
            if ( ! is_array( $image_meta ) ) $image_meta = $this->_tp_get_attachment_metadata( $attachment_id );
            $image_src  = $image[0];
            $size_array = array(
                $this->_abs_int( $image[1] ),
                $this->_abs_int( $image[2] ),
            );
            return $this->_tp_calculate_image_sizes( $size_array, $image_src, $image_meta, $attachment_id );
        }//1449
        /**
         * @description Creates a 'sizes' attribute value for an image.
         * @param $size
         * @param null $image_src
         * @param null $image_meta
         * @param int $attachment_id
         * @return bool
         */
        protected function _tp_calculate_image_sizes( $size, $image_src = null, $image_meta = null, $attachment_id = 0 ):bool{
            $width = 0;
            if ( is_array( $size ) ) $width = $this->_abs_int( $size[0] );
            elseif ( is_string( $size ) ) {
                if ( ! $image_meta && $attachment_id )
                    $image_meta = $this->_tp_get_attachment_metadata( $attachment_id );
                if ( is_array( $image_meta ) ) {
                    $size_array = $this->_tp_get_image_size_from_meta( $size, $image_meta );
                    if ( $size_array ) $width = $this->_abs_int( $size_array[0] );
                }
            }
            if ( ! $width ) return false;
            $sizes = sprintf( '(max-width: %1$dpx) 100vw, %1$dpx', $width );
            return $this->_apply_filters( 'tp_calculate_image_sizes', $sizes, $size, $image_src, $image_meta, $attachment_id );
        }//1483
        /**
         * @description Determines if the image meta data is for the image source file.
         * @param $image_location
         * @param $image_meta
         * @param int $attachment_id
         * @return mixed
         */
        protected function _tp_image_file_matches_image_meta( $image_location, $image_meta, $attachment_id = 0 ){
            $match = false;
            if ( isset( $image_meta['file'] ) && strlen( $image_meta['file'] ) > 4 ) {
                @list( $image_location ) = explode( '?', $image_location );
                if ( strrpos( $image_location, $image_meta['file'] ) === strlen( $image_location ) - strlen( $image_meta['file'] ) ) {
                    $match = true;
                } else {
                    $dirname = $this->_tp_get_attachment_relative_path( $image_meta['file'] );
                    if ( $dirname ) $dirname = $this->_trailingslashit( $dirname );
                    if ( ! empty( $image_meta['original_image'] ) ) {
                        $relative_path = $dirname . $image_meta['original_image'];
                        if ( strrpos( $image_location, $relative_path ) === strlen( $image_location ) - strlen( $relative_path ) )
                            $match = true;
                    }
                    if ( ! $match && ! empty( $image_meta['sizes'] ) ) {
                        foreach ( $image_meta['sizes'] as $image_size_data ) {
                            $relative_path = $dirname . $image_size_data['file'];
                            if ( strrpos( $image_location, $relative_path ) === strlen( $image_location ) - strlen( $relative_path ) ) {
                                $match = true;
                                break;
                            }
                        }
                    }
                }
            }
            return $this->_apply_filters( 'tp_image_file_matches_image_meta', $match, $image_location, $image_meta, $attachment_id );
        }//1538
        /**
         * @description Determines an image's width and height dimensions based on the source file.
         * @param $image_src
         * @param $image_meta
         * @param int $attachment_id
         * @return mixed
         */
        protected function _tp_image_src_get_dimensions( $image_src, $image_meta, $attachment_id = 0 ){
            $dimensions = false;
            if ( isset( $image_meta['file'] ) && strpos( $image_src, $this->_tp_basename( $image_meta['file'] ) ) !== false)
                $dimensions = [(int) $image_meta['width'],(int) $image_meta['height'],];
            if ( ! $dimensions && ! empty( $image_meta['sizes'] ) ) {
                $src_filename = $this->_tp_basename( $image_src );
                foreach ( $image_meta['sizes'] as $image_size_data ) {
                    if ( $src_filename === $image_size_data['file'] ) {
                        $dimensions = [(int) $image_size_data['width'],(int) $image_size_data['height'],];
                        break;
                    }
                }
            }
            return $this->_apply_filters( 'tp_image_src_get_dimensions', $dimensions, $image_src, $image_meta, $attachment_id );
        }//1603
        /**
         * @description Adds 'srcset' and 'sizes' attributes to an existing 'img' element.
         * @param $image
         * @param $image_meta
         * @param $attachment_id
         * @return mixed
         */
        protected function _tp_image_add_srcset_and_sizes( $image, $image_meta, $attachment_id ){
            if ( empty( $image_meta['sizes'] ) ) return $image;
            $image_src         = preg_match("/ src='([^']+)'/" , $image, $match_src ) ? $match_src[1] : '';
            @list( $image_src ) = explode( '?', $image_src );//'/src="([^"]+)"/'
            if ( ! $image_src ) return $image;
            if ( preg_match( '/-e\d{13}/', $image_meta['file'], $img_edit_hash ) &&
                strpos( $this->_tp_basename( $image_src ), $img_edit_hash[0] ) === false ) {
                return $image;
            }
            $width  = preg_match( '/ width="(\d+)"/', $image, $match_width ) ? (int) $match_width[1] : 0;
            $height = preg_match( '/ height="(\d+)"/', $image, $match_height ) ? (int) $match_height[1] : 0;
            if ( $width && $height ) $size_array = [$width, $height];
            else {
                $size_array = $this->_tp_image_src_get_dimensions( $image_src, $image_meta, $attachment_id );
                if ( ! $size_array ) return $image;
            }
            $srcset = $this->_tp_calculate_image_srcset( $size_array, $image_src, $image_meta, $attachment_id );
            $sizes = '';
            if ( $srcset ) {
                $sizes = strpos( $image, ' sizes=' );
                if ( ! $sizes ) $sizes = $this->_tp_calculate_image_sizes( $size_array, $image_src, $image_meta, $attachment_id );
            }
            if ( $srcset && $sizes ) {
                $attr = sprintf( " srcset='%s'", $this->_esc_attr( $srcset ) );
                if ( is_string( $sizes ) ) $attr .= sprintf( " sizes='%s'", $this->_esc_attr( $sizes ) );
                //return preg_replace( '/<img ([^>]+?)[\/ ]*>/', '<img $1' . $attr . ' />', $image );
                return preg_replace( "/<img ([^>]+?)[\/ ]*>/", "<img $1 $attr />", $image );
            }
            return $image;
        }//1661
        /**
         * @note might not be needed anymore as there is now the 'loading = lazy' attribute?
         * @description Determines whether to add the `loading` attribute to the specified tag in the specified context.
         * @param $tag_name
         * @param $context
         * @return bool
         */
        protected function _tp_lazy_loading_enabled( $tag_name, $context ):bool{
            $default = ( 'img' === $tag_name || 'iframe' === $tag_name );
            return (bool) $this->_apply_filters( 'tp_lazy_loading_enabled', $default, $tag_name, $context );
        }//1731
        /**
         * @description Filters specific tags in post content and modifies their markup.
         * @param $content
         * @param null $context
         * @return mixed
         */
        protected function _tp_filter_content_tags( $content, $context = null ){
            if ( null === $context ) $context = $this->_current_filter();
            $add_img_loading_attr    = $this->_tp_lazy_loading_enabled( 'img', $context );
            $add_iframe_loading_attr = $this->_tp_lazy_loading_enabled( 'iframe', $context );
            if ( ! preg_match_all( '/<(img|iframe)\s[^>]+>/', $content, $matches, PREG_SET_ORDER ) )
                return $content;
            $images = [];
            $iframes = [];
            foreach ( $matches as $match ) {
                @list( $tag, $tag_name ) = $match;
                switch ( $tag_name ) {
                    case 'img':
                        if ( preg_match( '/tp-image-(\d+)/i', $tag, $class_id ) ) {
                            $attachment_id = $this->_abs_int( $class_id[1] );
                            if ( $attachment_id ) {
                                $images[ $tag ] = $attachment_id;
                                break;
                            }
                        }
                        $images[ $tag ] = 0;
                        break;
                    case 'iframe':
                        $iframes[ $tag ] = 0;
                        break;
                }
            }
            $attachment_ids = array_unique( array_filter( array_values( $images ) ) );
            if ( count( $attachment_ids ) > 1 ) $this->_prime_post_caches( $attachment_ids, false, true );
            foreach ( $matches as $match ) {
                if ( isset( $images[ $match[0] ] ) ) {
                    $filtered_image = $match[0];
                    $attachment_id  = $images[ $match[0] ];
                    if ( $attachment_id > 0 && false === strpos( $filtered_image, ' width=' ) && false === strpos( $filtered_image, ' height=' ) )
                        $filtered_image = $this->_tp_img_tag_add_width_and_height_attr( $filtered_image, $context, $attachment_id );
                    if ( $attachment_id > 0 && false === strpos( $filtered_image, ' srcset=' ) )
                        $filtered_image = $this->_tp_img_tag_add_srcset_and_sizes_attr( $filtered_image, $context, $attachment_id );
                    if ( $add_img_loading_attr && false === strpos( $filtered_image, ' loading=' ) )
                        $filtered_image = $this->_tp_img_tag_add_loading_attr( $filtered_image, $context );
                    if ( $filtered_image !== $match[0] ) $content = str_replace( $match[0], $filtered_image, $content );
                }
                if ( isset( $iframes[ $match[0] ] ) ) {
                    $filtered_iframe = $match[0];
                    if ( $add_iframe_loading_attr && false === strpos( $filtered_iframe, ' loading=' ) )
                        $filtered_iframe = $this->_tp_iframe_tag_add_loading_attr( $filtered_iframe, $context );
                    if ( $filtered_iframe !== $match[0] ) $content = str_replace( $match[0], $filtered_iframe, $content );
                }
            }
            return $content;
        }//1772
     }
}else die;