<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-5-2022
 * Time: 18:11
 */
namespace TP_Core\Libs\Editor;
use TP_Core\Traits\Media\_media_02;
use TP_Core\Traits\Media\_media_09;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_Image_Editor_Imagick extends TP_Image_Editor{
        use _media_02, _media_09;
        protected $_image;
        public function __destruct() {
            if ( $this->_image instanceof \Imagick ) {
                $this->_image->clear();
                $this->_image->destroy();
            }
        }//24
        /**
         * @param array ...$args
         * @return mixed
         */
        public static function test(...$args) {
            if (! class_exists( 'ImagickPixel', false ) || ! class_exists( 'Imagick', false ) || ! extension_loaded( 'imagick' ))
                return false;
            if ( version_compare( phpversion( 'imagick' ), '2.2.0', '<' ) ) return false;
            $required_methods = [
                'clear','destroy','valid','getimage','writeimage','getimageblob','getimagegeometry',
                'getimageformat','setimageformat','setimagecompression','setimagecompressionquality',
                'setimagepage','setoption','scaleimage','cropimage','rotateimage','flipimage',
                'flopimage','readimage','readimageblob',];
            if ( ! defined( 'imagick::COMPRESSION_JPEG' ) ) return false;
            $class_methods = array_map( 'strtolower', get_class_methods( 'Imagick' ) );
            if ( array_diff( $required_methods, $class_methods ) ) return false;
            return true;
        }//43
        /**
         * @param $mime_type
         * @return mixed
         */
        public static function supports_mime_type( $mime_type ) {
            $imagick_extension = strtoupper( self::_get_extension( $mime_type ) );
            if ( ! $imagick_extension ) return false;
            if ('image/jpeg' !== $mime_type && ! method_exists( 'Imagick', 'setIteratorIndex' ))
                return false;
            try {
                return ( (bool) @\Imagick::queryFormats( $imagick_extension ) );
            } catch ( \Exception $e ) {
                return false;
            }
        }//98
        public function load(){
            if ( $this->_image instanceof \Imagick ) return true;
            if ( ! is_file( $this->_file ) && ! $this->_tp_is_stream( $this->_file ) )
                return new TP_Error( 'error_loading_image', $this->__( 'File doesn&#8217;t exist?' ), $this->_file );
            $this->_tp_raise_memory_limit( 'image' );
            try {
                $this->_image    = new \Imagick();
                $file_extension = strtolower( pathinfo( $this->_file, PATHINFO_EXTENSION ) );
                if ( 'pdf' === $file_extension ) {
                    $pdf_loaded = $this->_pdf_load_source();
                    if ( $this->_init_error( $pdf_loaded ) ) return $pdf_loaded;
                } else if ( $this->_tp_is_stream( $this->_file ) )
                    $this->_image->readImageBlob( file_get_contents( $this->_file ), $this->_file );
                else $this->_image->readImage( $this->_file );
                if ( ! $this->_image->valid() )
                    return new TP_Error( 'invalid_image', $this->__( 'File is not an image.' ), $this->_file );
                if ( is_callable( array( $this->_image, 'setIteratorIndex' ) ) )
                    $this->_image->setIteratorIndex( 0 );
                $this->_mime_type = self::_get_mime_type( $this->_image->getImageFormat() );
            } catch ( \Exception $e ) {
                return new TP_Error( 'invalid_image', $e->getMessage(), $this->_file );
            }
            $updated_size = $this->_update_size();
            if ( $this->_init_error( $updated_size ) )
                return $updated_size;
            return $this->set_quality();
        }//126
        public function set_quality( $quality = null ){
            $quality_result = parent::set_quality( $quality );
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image;
            }
            if ( $this->_init_error( $quality_result ) ) return $quality_result;
            else $quality = $this->get_quality();
            try {
                switch ( $this->_mime_type ) {
                    case 'image/jpeg':
                        $img->setImageCompressionQuality( $quality );
                        $img->setImageCompression( \Imagick::COMPRESSION_JPEG );
                        break;
                    case 'image/webp':
                        $webp_info = $this->_tp_get_webp_info( $this->_file );
                        if ( 'lossless' === $webp_info['type'] ) {
                            $img->setImageCompressionQuality( 100 );
                            $img->setOption( 'webp:lossless', 'true' );
                        } else $img->setImageCompressionQuality( $quality );
                        break;
                    default:
                        $img->setImageCompressionQuality( $quality );
                }
            } catch ( \Exception $e ) {
                return new TP_Error( 'image_quality_error', $e->getMessage() );
            }
            return true;
        }//191
        protected function _update_size( $width = null, $height = null ):int{
            $size = null;
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image;
            }
            if ( ! $width || ! $height ) {
                try {
                    $size = $img->getImageGeometry();
                } catch ( \Exception $e ) {
                    return (string) new TP_Error( 'invalid_image', $this->__( 'Could not read image size.' ), $this->_file );
                }
            }
            if ( ! $width ) $width = $size['width'];
            if ( ! $height )  $height = $size['height'];
            return parent::_update_size( $width, $height );
        }//235
        public function resize( $max_w, $max_h, $crop = false ){
            if ( ( $this->_size['width'] === $max_w ) && ( $this->_size['height'] === $max_h ) )
                return true;
            $dims = $this->_image_resize_dimensions( $this->_size['width'], $this->_size['height'], $max_w, $max_h, $crop );
            if ( ! $dims )
                return new TP_Error( 'error_getting_dimensions', $this->__( 'Could not calculate resized image dimensions' ) );
            @list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;
            if ( $crop )
                return $this->crop( $src_x, $src_y, $src_w, $src_h, $dst_w, $dst_h );
            $thumb_result = $this->_thumbnail_image( $dst_x, $dst_y,$dst_w, $dst_h );
            if ( $this->_init_error( $thumb_result ) ) return $thumb_result;
            return $this->_update_size( $dst_w, $dst_h );
        }//270
        protected function _thumbnail_image( $dst_w, $dst_h, $filter_name = 'FILTER_TRIANGLE', $strip_meta = true ){
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image;
            }
            $allowed_filters = [
                'FILTER_POINT','FILTER_BOX','FILTER_TRIANGLE','FILTER_HERMITE','FILTER_HANNING',
                'FILTER_HAMMING','FILTER_BLACKMAN','FILTER_GAUSSIAN','FILTER_QUADRATIC','FILTER_CUBIC',
                'FILTER_CATROM','FILTER_MITCHELL','FILTER_LANCZOS','FILTER_BESSEL','FILTER_SINC',
            ];
            if (defined( 'Imagick::' . $filter_name )  && in_array( $filter_name, $allowed_filters, true ))
                $filter = constant( 'Imagick::' . $filter_name );
            else $filter = defined( 'Imagick::FILTER_TRIANGLE' ) ? \Imagick::FILTER_TRIANGLE : false;
            if ( $this->_apply_filters( 'image_strip_meta', $strip_meta ) )
                $this->_strip_meta(); // Fail silently if not supported.
            try {
                if ( is_callable( array( $img, 'sampleImage' ) ) ) {
                    $resize_ratio  = ( $dst_w / $this->_size['width'] ) * ( $dst_h / $this->_size['height'] );
                    $sample_factor = 5;
                    if ( $resize_ratio < .111 && ( $dst_w * $sample_factor > 128 && $dst_h * $sample_factor > 128 ) )
                        $img->sampleImage( $dst_w * $sample_factor, $dst_h * $sample_factor );
                }
                if ( is_callable( array( $img, 'resizeImage' ) ) && $filter ) {
                    $img->setOption( 'filter:support', '2.0' );
                    $img->resizeImage( $dst_w, $dst_h, $filter, 1 );
                } else $img->scaleImage( $dst_w, $dst_h );
                if ( 'image/jpeg' === $this->_mime_type ) {
                    if ( is_callable( array( $img, 'unsharpMaskImage' ) ) )
                        $img->unsharpMaskImage( 0.25, 0.25, 8, 0.065 );
                    $img->setOption( 'jpeg:fancy-upsampling', 'off' );
                }
                if ( 'image/png' === $this->_mime_type ) {
                    $img->setOption( 'png:compression-filter', '5' );
                    $img->setOption( 'png:compression-level', '9' );
                    $img->setOption( 'png:compression-strategy', '1' );
                    $img->setOption( 'png:exclude-chunk', 'all' );
                }
                if (is_callable(array($img, 'getImageAlphaChannel'))
                    && is_callable(array($img, 'setImageAlphaChannel'))
                    && defined('Imagick::ALPHACHANNEL_UNDEFINED')
                    && defined('Imagick::ALPHACHANNEL_OPAQUE') && $img->getImageAlphaChannel() === \Imagick::ALPHACHANNEL_UNDEFINED
                ) $img->setImageAlphaChannel( \Imagick::ALPHACHANNEL_OPAQUE );
                if (is_callable(array($img, 'getImageDepth')) && is_callable(array($img, 'setImageDepth')) && 8 < $img->getImageDepth()) $img->setImageDepth( 8 );
                if ( is_callable( array( $img, 'setInterlaceScheme' ) ) && defined( 'Imagick::INTERLACE_NO' ) )
                    $img->setInterlaceScheme( \Imagick::INTERLACE_NO );
            }catch( \Exception $e ) {
                return new TP_Error( 'image_resize_error', $e->getMessage() );
            }
            return true;
        }//309
        public function multi_resize( $sizes ):string{
            $metadata = [];
            foreach ( $sizes as $size => $size_data ) {
                $meta = $this->make_subsize( $size_data );
                if ( ! $this->_init_error( $meta ) ) $metadata[ $size ] = $meta;
            }
            return $metadata;
        }//457
        public function make_subsize( $size_data ){//todo
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image;
            }
            if ( ! isset( $size_data['width'] ) && ! isset( $size_data['height'] ) )
                return new TP_Error( 'image_subsize_create_error', $this->__( 'Cannot resize the image. Both width and height are not set.' ) );
            $orig_size  = $this->_size;
            $orig_image = $img->getImage();
            if ( ! isset( $size_data['width'] ) ) $size_data['width'] = null;
            if ( ! isset( $size_data['height'] ) ) $size_data['height'] = null;
            if ( ! isset( $size_data['crop'] ) ) $size_data['crop'] = false;
            $resized = $this->resize( $size_data['width'], $size_data['height'], $size_data['crop'] );
            if ( $this->_init_error( $resized ) ) $saved = $resized;
            else {
                $saved = $this->_save( $img );
                $img->clear();
                $img->destroy();
                $img = null;
            }
            $this->_size  = $orig_size;
            $this->_image = $img = $orig_image;
            if ( ! $this->_init_error( $saved ) ) unset( $saved['path'] );
            return $saved;
        }//486
        public function crop( $src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false ){
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image;
            }
            if ( $src_abs ) {
                $src_w -= $src_x;
                $src_h -= $src_y;
            }
            try {
                $img->cropImage( $src_w, $src_h, $src_x, $src_y );
                $img->setImagePage( $src_w, $src_h, 0, 0 );
                if ( $dst_w || $dst_h ) {
                    if ( ! $dst_w ) $dst_w = $src_w;
                    if ( ! $dst_h ) $dst_h = $src_h;
                    $thumb_result = $this->_thumbnail_image( $dst_w, $dst_h );
                    if ( $this->_init_error( $thumb_result ) ) return $thumb_result;
                    return $this->_update_size();
                }
            } catch ( \Exception $e ) {
                return new TP_Error( 'image_crop_error', $e->getMessage() );
            }
            return $this->_update_size();
        }//542
        public function rotate( $angle ){
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image;
            }
            try {
                $img->rotateImage( new \ImagickPixel( 'none' ), 360 - $angle );
                if ( is_callable( array( $img, 'setImageOrientation' ) ) && defined( 'Imagick::ORIENTATION_TOPLEFT' ) )
                    $img->setImageOrientation( \Imagick::ORIENTATION_TOPLEFT );
                $result = $this->_update_size();
                if ( $this->_init_error( $result ) ) return $result;
                $img->setImagePage( $this->_size['width'], $this->_size['height'], 0, 0 );
            } catch ( \Exception $e ) {
                return new TP_Error( 'image_rotate_error', $e->getMessage() );
            }
            return true;
        }//584
        public function flip( $horz, $vert ){
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image;
            }
            try {
                if ( $horz ) $img->flipImage();
                if ( $vert ) $img->flopImage();
                if ( is_callable( array( $img, 'setImageOrientation' ) ) && defined( 'Imagick::ORIENTATION_TOPLEFT' ) )
                    $img->setImageOrientation( \Imagick::ORIENTATION_TOPLEFT );

            } catch ( \Exception $e ) {
                return new TP_Error( 'image_flip_error', $e->getMessage() );
            }
            return true;
        }//620
        public function maybe_exif_rotate(){
            if ( is_callable( array( $this->_image, 'setImageOrientation' ) ) && defined( 'Imagick::ORIENTATION_TOPLEFT' ) )
                return parent::maybe_exif_rotate();
            else return new TP_Error( 'write_exif_error', $this->__( 'The image cannot be rotated because the embedded meta data cannot be updated.' ) );
        }//652
        public function save( $destfilename = null, $mime_type = null ){
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image;
            }
            $saved = $this->_save( $img, $destfilename, $mime_type );
            if ( ! $this->_init_error( $saved ) ) {
                $this->_file      = $saved['path'];
                $this->_mime_type = $saved['mime-type'];
                try {
                    $img->setImageFormat( strtoupper( self::_get_extension( $this->_mime_type ) ) );
                } catch ( \Exception $e ) {
                    return new TP_Error( 'image_save_error', $e->getMessage(), $this->_file );
                }
            }
            return $saved;
        }//669
        protected function _save( $image, $filename = null, $mime_type = null ){
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image[$image];
            }
            @list( $filename, $extension, $mime_type ) = $this->_get_output_format( $filename, $mime_type );
            if ( ! $filename )  $filename = $this->generate_filename( null, null, $extension );
            try {
                $orig_format = $img->getImageFormat();
                $img->setImageFormat( strtoupper( self::_get_extension( $mime_type ) ) );
            } catch ( \Exception $e ) {
                return new TP_Error( 'image_save_error', $e->getMessage(), $filename );
            }
            $write_image_result = $this->__write_image( $img, $filename );
            if ( $this->_init_error( $write_image_result ) )
                return $write_image_result;
            try {
                $img->setImageFormat( $orig_format );
            } catch ( \Exception $e ) {
                return new TP_Error( 'image_save_error', $e->getMessage(), $filename );
            }
            $stat  = stat( dirname( $filename ) );
            $perms = $stat['mode'] & 0000666; // Same permissions as parent folder, strip off the executable bits.
            chmod( $filename, $perms );
            return array(
                'path'      => $filename,
                'file'      => $this->_tp_basename( $this->_apply_filters( 'image_make_intermediate_size', $filename ) ),
                'width'     => $this->_size['width'],
                'height'    => $this->_size['height'],
                'mime-type' => $mime_type,
            );
        }//692
        private function __write_image(\Imagick $image, $filename ){
            if ( $this->_tp_is_stream( $filename ) ) {
                if ( file_put_contents( $filename, $image->getImageBlob() ) === false ) {
                    /* translators: %s: PHP function name. */
                    return new TP_Error('image_save_error',
                        sprintf($this->__( '%s failed while writing image to stream.' ),
                            '<code>file_put_contents()</code>'), $filename);
                } else return true;
            } else {
                $dirname = dirname( $filename );
                if ( ! $this->_tp_mkdir_p( $dirname ) )
                    /* translators: %s: Directory path. */
                    return new TP_Error('image_save_error',
                        sprintf($this->__( 'Unable to create directory %s. Is its parent directory writable by the server?' ),
                            $this->_esc_html( $dirname )));
                try {
                    return $image->writeImage( $filename );
                } catch ( \Exception $e ) {
                    return new TP_Error( 'image_save_error', $e->getMessage(), $filename );
                }
            }
        }//744
        public function stream( $mime_type = null ){
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image;
            }
            @list( $filename, $extension, $mime_type ) = $this->_get_output_format( null, $mime_type );
            try {
                $img->setImageFormat( strtoupper( $extension ) );
                $img->setFilename($filename);
                header( "Content-Type: $mime_type" );
                print $img->getImageBlob();
                $this->_image->setImageFormat( self::_get_extension( $this->_mime_type ) );
            } catch ( \Exception $e ) {
                return new TP_Error( 'image_stream_error', $e->getMessage() );
            }
            return true;
        }//793
        protected function _strip_meta(){
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image;
            }
            if ( ! is_callable( array( $img, 'getImageProfiles' ) ) ) {
                /* translators: %s: ImageMagick method name. */
                return new TP_Error('image_strip_meta_error',
                    sprintf($this->__( '%s is required to strip image meta.' ),
                        '<code>Imagick::getImageProfiles()</code>'));
            }
            if ( ! is_callable( array( $img, 'removeImageProfile' ) ) ) {
                return new TP_Error('image_strip_meta_error',
                    sprintf($this->__( '%s is required to strip image meta.' ),
                        '<code>Imagick::removeImageProfile()</code>'));
            }
            $protected_profiles = ['icc','icm','iptc','exif','xmp',];
            try {
                foreach ( $this->_image->getImageProfiles( '*', true ) as $key => $value ) {
                    if ( ! in_array( $key, $protected_profiles, true ) )
                        $this->_image->removeImageProfile( $key );
                }
            } catch ( \Exception $e ) {
                return new TP_Error( 'image_strip_meta_error', $e->getMessage() );
            }
            return true;
        }//820
        protected function _pdf_setup(){
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image;
            }
            try {
                $img->setResolution( 128, 128 );
                return $this->_file . '[0]';
            } catch ( \Exception $e ) {
                return new TP_Error( 'pdf_setup_failed', $e->getMessage(), $this->_file );
            }
        }//883
        protected function _pdf_load_source(){
            $img = null;
            if( $this->_image instanceof \Imagick ){
                $img = $this->_image;
            }
            $filename = $this->_pdf_setup();
            if ( $this->_init_error( $filename ) )
                return $filename;
            try {
                $img->setOption( 'pdf:use-cropbox', true );
                $img->readImage( $filename );
            } catch ( \Exception $e ) {
                $img->setOption( 'pdf:use-cropbox', false );
                $img->readImage( $filename );
            }
            return true;
        }
    }
}else die;

