<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
if(ABSPATH){
    trait _methods_10{
        /**
         * @description Checks that a JSONP callback is a valid JavaScript callback name.
         * @param $callback
         * @return bool
         */
        protected function _tp_jsonp_check_callback( $callback ):bool{
            if ( ! is_string( $callback ) ) return false;
            preg_replace( '/[^\w\.]/', '', $callback, -1, $illegal_char_count );
            return 0 === $illegal_char_count;
        }//4447
        /**
         * @description Reads and decodes a JSON file.
         * @param $filename
         * @param array $options
         * @return mixed|null
         */
        protected function _tp_json_file_decode( $filename, $options = array() ){
            $result   = null;
            $filename = $this->_tp_normalize_path( realpath( $filename ) );
            if ( ! file_exists( $filename ) ) {
                trigger_error( sprintf( $this->__( "File %s doesn't exist!" ), $filename));
                return $result;
            }
            $options      = $this->_tp_parse_args( $options, array( 'associative' => false ) );
            $decoded_file = json_decode( file_get_contents( $filename ), $options['associative'] );
            if ( JSON_ERROR_NONE !== json_last_error() ) {
                trigger_error(sprintf( $this->__( 'Error when decoding a JSON file at path %1$s: %2$s' ),$filename,json_last_error_msg()));
                return $result;
            }
            return $decoded_file;
        }//4473
        /**
         * @description Retrieve the TailoredPress home page URL.
         * @param string $url
         * @return string
         */
        protected function _tp_config_home( $url = '' ):string{
            if ( defined( 'TP_HOME' ) ) return $this->_untrailingslashit( TP_HOME );
            return $url;
        }//4520 _config_wp_home
        /**
         * @description Retrieve the TailoredPress site URL.
         * @param string $url
         * @return string
         */
        protected function _tp_config_siteurl( $url = '' ):string{
            if ( defined( 'TP_SITEURL' ) ) return $this->_untrailingslashit( TP_SITEURL );
            return $url;
        }//4542
        /**
         * @description Delete the fresh site option.
         */
        protected function _delete_option_fresh_site():void{
            $this->_update_option( 'fresh_site', '0' );
        }//4555
        /**
         * todo @description Set the localized direction for MCE (editor)plugin.
         * @param $mce_init
         * @return mixed
         */
        protected function _mce_set_direction( $mce_init ){
            if ( $this->_is_rtl() ) {
                $mce_init['directionality'] = 'rtl';
                $mce_init['rtl_ui']         = true;
                if ( ! empty( $mce_init['plugins'] ) && strpos( $mce_init['plugins'], 'directionality' ) === false )
                    $mce_init['plugins'] .= ',directionality';
                if ( ! empty( $mce_init['toolbar1'] ) && ! preg_match( '/\bltr\b/', $mce_init['toolbar1'] ) )
                    $mce_init['toolbar1'] .= ',ltr';
            }
            return $mce_init;
        }//4576
        //todo might not use this @description Convert smiley code to the icon graphic file equivalent.
        //protected function _smilies_init(){}//4616
        /**
         * @description Merges user defined arguments into defaults array.
         * @param array $args
         * @param array $defaults
         * @return array
         */
        protected function _tp_parse_args( $defaults = [],$args = [] ): array{
            if ( is_object( $args ) ) $parsed_args = get_object_vars( $args );
            elseif ( is_array( $args ) ) $parsed_args =& $args;
            else $this->_tp_parse_str( $args, $parsed_args );
            if ( is_array( $defaults ) && $defaults )
                return array_merge( $defaults, $parsed_args );
            return $parsed_args;
        }//4739
        /**
         * @description Converts a comma- or space-separated list of scalar values to an array.
         * @param $list
         * @return array
         */
        protected function _tp_parse_list( $list ):array{
            if ( ! is_array( $list ) ) return preg_split( '/[\s,]+/', $list, -1, PREG_SPLIT_NO_EMPTY );
            return $list;
        }//4762
        /**
         * @description Cleans up an array, comma- or space-separated list of IDs.
         * @param $list
         * @return array
         */
        protected function _tp_parse_id_list( $list ):array{
            $list = $this->_tp_parse_list( $list );
            return array_unique( array_map( 'absint', $list ) );
        }//4779
    }
}else die;