<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 17-4-2022
 * Time: 17:39
 */
namespace TP_Core\Libs\HTTP;
use TP_Core\Traits\Filters\_filter_01;
if(ABSPATH){
    class TP_Http_Encoding {
        use _filter_01;
        public static function compress( $raw, $level = 9, $supports = null ): string{
            return gzdeflate( $raw, $level, $supports );
        }//32
        public static function decompress( $compressed, $length = null ) {
            if ( empty( $compressed ) )return $compressed;
            $decompressed = @gzinflate( $compressed ,$length);
            if ( false !== $decompressed )
                return $decompressed;
            $decompressed = self::compatible_gzinflate( $compressed );
            if ( false !== $decompressed )
                return $decompressed;
            $decompressed = @gzuncompress( $compressed );
            if ( false !== $decompressed )
                return $decompressed;
            if ( function_exists( 'gzdecode' ) ) {
                $decompressed = @gzdecode( $compressed );
                if ( false !== $decompressed ) return $decompressed;
            }
            return $compressed;
        }//80
        public static function compatible_gzinflate( $gz_data ) {
            if (strpos($gz_data, "\x1f\x8b\x08") === 0) {
                $i   = 10;
                $flg = ord( substr( $gz_data, 3, 1 ) );
                if ( $flg > 0 ) {
                    if ( $flg & 4 ) {
                        @list($x_len) = unpack( 'v', substr( $gz_data, $i, 2 ) );
                        $i += 2 + $x_len;
                    }
                    if ( $flg & 8 ) $i = strpos( $gz_data, "\0", $i ) + 1;
                    if ( $flg & 16 ) $i = strpos( $gz_data, "\0", $i ) + 1;
                    if ( $flg & 2 ) $i += 2;
                }
                $decompressed = @gzinflate( substr( $gz_data, $i, -8 ) );
                if ( false !== $decompressed ) return $decompressed;
            }
            $decompressed = @gzinflate( substr( $gz_data, 2 ) );
            if ( false !== $decompressed ) return $decompressed;
            return false;
        }//103
        public static function accept_encoding( $url, $args ): string{
            $type                = [];
            $compression_enabled = self::is_available();
            if ( ! $args['decompress'] )  $compression_enabled = false;
            elseif ( $args['stream'] ) $compression_enabled = false;
            elseif ( isset( $args['limit_response_size'] ) )  $compression_enabled = false;
            if ( $compression_enabled ) {
                if ( function_exists( 'gzinflate' ) )  $type[] = 'deflate;q=1.0';
                if ( function_exists( 'gzuncompress' ) )  $type[] = 'compress;q=0.5';
                if ( function_exists( 'gzdecode' ) )  $type[] = 'gzip;q=0.5';
            }
            $type = (new static)->_apply_filters( 'tp_http_accept_encoding', $type, $url, $args );
            return implode( ', ', $type );
        }//148
        public static function content_encoding(): string{
            return 'deflate';
        }//195
        public static function should_decode( $headers ): bool{
            if ( is_array( $headers ) ) {
                if ( array_key_exists( 'content-encoding', $headers ) && ! empty( $headers['content-encoding'] ) )
                    return true;
            } elseif ( is_string( $headers ) )
                return ( stripos( $headers, 'content-encoding:' ) !== false );
            return false;
        }//207
        public static function is_available(): bool{
            return ( function_exists( 'gzuncompress' ) || function_exists( 'gzdeflate' ) || function_exists( 'gzinflate' ) );
        }//230
    }
}else die;