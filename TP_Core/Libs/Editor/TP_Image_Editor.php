<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-5-2022
 * Time: 18:50
 */
namespace TP_Core\Libs\Editor;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    abstract class TP_Image_Editor extends Editor_base {
        protected $_default_quality = 82;
        public function __construct( $file ) {
            $this->_file = $file;
        }//32
        /**
         * @param array ...$args
         * @return mixed
         */
        public static function test( ... $args){
            return $args ?: false;
        }//50
        /**
         * @param $mime_type
         * @return mixed
         */
        public static function supports_mime_type( $mime_type ){
            return $mime_type ?: false;
        }//60
        abstract public function load();//72
        abstract public function save( $destfilename = null, $mime_type = null );//84
        abstract public function resize( $max_w, $max_h, $crop = false );//101
        abstract public function multi_resize( $sizes );//120
        abstract public function crop( $src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false );//137
        abstract public function rotate( $angle );
        abstract public function flip( $horz, $vert );
        abstract public function stream( $mime_type = null );
        public function get_size() {
            return $this->_size;
        }//185
        protected function _update_size( $width = null, $height = null ):int {
            $this->_size = ['width' => (int) $width,'height' => (int) $height,];
            return true;
        }//198
        public function get_quality(): bool{
            if ( ! $this->_quality ) $this->set_quality();
            return $this->_quality;
        }//213
        public function set_quality( $quality = null ) {
            $mime_type = ! empty( $this->_output_mime_type ) ? $this->_output_mime_type : $this->_mime_type;
            $default_quality = $this->_get_default_quality( $mime_type );
            if ( null === $quality ) {
                $quality = $this->_apply_filters( 'tp_editor_set_quality', $default_quality, $mime_type );
                if ( 'image/jpeg' === $mime_type )
                    $quality = $this->_apply_filters( 'jpeg_quality', $quality, 'image_resize' );
                if ( $quality < 0 || $quality > 100 ) $quality = $default_quality;
            }
            if ( 0 === $quality ) $quality = 1;
            if ( ( $quality >= 1 ) && ( $quality <= 100 ) ) {
                $this->_quality = $quality;
                return true;
            } else return new TP_Error( 'invalid_image_quality', $this->__( 'Attempted to set image quality outside of the range [1,100].' ) );
        }//229
        protected function _get_default_quality( $mime_type ): int{
            switch ( $mime_type ) {
                case 'image/webp':
                    $quality = 86;
                    break;
                case 'image/jpeg':
                default:
                    $quality = $this->_default_quality;
            }
            return $quality;
        }//308
        protected function _get_output_format( $filename = null, $mime_type = null ): array{
            $new_ext = null;
            if ( $mime_type )  $new_ext = self::_get_extension( $mime_type );
            if ( $filename ) {
                $file_ext  = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
                $file_mime = self::_get_mime_type( $file_ext );
            } else {
                $file_ext  = strtolower( pathinfo( $this->_file, PATHINFO_EXTENSION ) );
                $file_mime = $this->_mime_type;
            }
            if ( ! $mime_type || ( $file_mime === $mime_type ) ) {
                $mime_type = $file_mime;
                $new_ext   = $file_ext;
            }
            $output_format = $this->_apply_filters( 'image_editor_output_format', array(), $filename, $mime_type );
            if ( isset( $output_format[ $mime_type ] )
                && self::supports_mime_type( $output_format[ $mime_type ] )
            ) {
                $mime_type = $output_format[ $mime_type ];
                $new_ext   = self::_get_extension( $mime_type );
            }
            if ( ! self::supports_mime_type( $mime_type ) ) {
                $mime_type = $this->_apply_filters( 'image_editor_default_mime_type', $this->_default_mime_type );
                $new_ext   = self::_get_extension( $mime_type );
            }
            if ( $filename && $new_ext ) {
                $dir = pathinfo( $filename, PATHINFO_DIRNAME );
                $ext = pathinfo( $filename, PATHINFO_EXTENSION );
                $filename = $this->_trailingslashit( $dir ) . $this->_tp_basename( $filename, ".$ext" ) . ".{$new_ext}";
            }
            if ( $mime_type && ( $mime_type !== $this->_mime_type ) ) {
                if ( $mime_type !== $this->_output_mime_type ) {
                    $this->_output_mime_type = $mime_type;
                    $this->set_quality();
                }
            } elseif ( ! empty( $this->_output_mime_type ) ) {
                $this->_output_mime_type = null;
                $this->set_quality();
            }
            return array( $filename, $new_ext, $mime_type );
        }//324
        public function generate_filename( $suffix = null, $dest_path = null, $extension = null ): string{
            if ( ! $suffix ) $suffix = $this->get_suffix();
            $dir = pathinfo( $this->_file, PATHINFO_DIRNAME );
            $ext = pathinfo( $this->_file, PATHINFO_EXTENSION );
            $name    = $this->_tp_basename( $this->_file, ".$ext" );
            $new_ext = strtolower( $extension ?: $ext );
            if ( ! is_null( $dest_path ) ) {
                if ( ! $this->_tp_is_stream( $dest_path ) ) {
                    $_dest_path = realpath( $dest_path );
                    if ( $_dest_path ) $dir = $_dest_path;
                } else  $dir = $dest_path;
            }
            return $this->_trailingslashit( $dir ) . "{$name}-{$suffix}.{$new_ext}";
        }//427
        public function get_suffix() {
            if ( ! $this->get_size() ) return false;
            return "{$this->_size['width']}x{$this->_size['height']}";
        }//460
        public function maybe_exif_rotate() {
            $result = null;
            $orientation = null;
            if ( is_callable( 'exif_read_data' ) && 'image/jpeg' === $this->_mime_type ) {
                $exif_data = @exif_read_data( $this->_file );
                if ( ! empty( $exif_data['Orientation'] ) ) $orientation = (int) $exif_data['Orientation'];
            }
            $orientation = $this->_apply_filters( 'tp_image_maybe_exif_rotate', $orientation, $this->_file );
            if ( ! $orientation || 1 === $orientation ) return false;
            switch ( $orientation ) {
                case 2:
                    $result = $this->flip( true, false );
                    break;
                case 3:
                    $result = $this->flip( true, true );
                    break;
                case 4:
                    $result = $this->flip( false, true );
                    break;
                case 5:
                    $result = $this->rotate( 90 );
                    if ( ! $this->_init_error( $result ) ) $result = $this->flip( false, true );
                    break;
                case 6:
                    // Rotate 90 degrees clockwise (270 counter-clockwise).
                    $result = $this->rotate( 270 );
                    break;
                case 7:
                    // Rotate 90 degrees counter-clockwise and flip horizontally.
                    $result = $this->rotate( 90 );
                    if ( ! $this->_init_error( $result ) )  $result = $this->flip( true, false );
                    break;
                case 8:
                    $result = $this->rotate( 90 );
                    break;
            }
            return $result;
        }//467
        protected function _make_image( $filename, $function, $arguments ) {
            $stream = $this->_tp_is_stream( $filename );
            if ( $stream ) {
                ob_start();
            } else  $this->_tp_mkdir_p( dirname( $filename ) );
            $result = call_user_func_array( $function, $arguments );
            if ( $result && $stream ) {
                $contents = ob_get_contents();
                $fp = fopen( $filename, 'wb' );
                if ( ! $fp ) {
                    ob_end_clean();
                    return false;
                }
                fwrite( $fp, $contents );
                fclose( $fp );
            }
            if ( $stream )
                ob_end_clean();
            return $result;
        }//566
        protected static function _get_mime_type( $extension = null ) {
            if ( ! $extension ) return false;
            $mime_types = (new static($extension))->_tp_get_mime_types();
            $extensions = array_keys( $mime_types );
            foreach ( $extensions as $_extension ) {
                if ( preg_match( "/{$extension}/i", $_extension ) )
                    return $mime_types[ $_extension ];
            }
            return false;
        }//597
        protected static function _get_extension( $mime_type = null ) {
            if ( empty( $mime_type ) ) return false;
            return (new static($mime_type))->_tp_get_default_extension_for_mime_type( $mime_type );
        }//629
    }
}else die;