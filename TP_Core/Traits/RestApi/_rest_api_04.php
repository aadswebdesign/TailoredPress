<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-4-2022
 * Time: 08:35
 */
namespace TP_Core\Traits\RestApi;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Libs\RestApi\TP_REST_Request;
use TP_Core\Libs\Request\Requests_IPv6;
if(ABSPATH){
    trait _rest_api_04{
        use _init_error;
        /**
         * @description Retrieves the pixel sizes for avatars.
         * @return mixed
         */
        protected function _rest_get_avatar_sizes(){
            return $this->_apply_filters( 'rest_avatar_sizes', array( 24, 48, 96 ) );
        }//1206
        /**
         * @description Parses an RFC3339 time into a Unix timestamp.
         * @param $date
         * @param bool $force_utc
         * @return bool|false|int
         */
        protected function _rest_parse_date( $date, $force_utc = false ){
            if ( $force_utc ) $date = preg_replace( '/[+-]\d+:?\d+$/', '+00:00', $date );
            $regex = '#^\d{4}-\d{2}-\d{2}[Tt ]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}(?::\d{2})?)?$#';
            if ( ! preg_match( $regex, $date, $matches ) ) return false;
            return strtotime( $date );
        }//1231
        /**
         * @description Parses a 3 or 6 digit hex color (with #).
         * @param $color
         * @return bool
         */
        protected function _rest_parse_hex_color( $color ):bool{
            $regex = '|^#([A-Fa-f0-9]{3}){1,2}$|';
            if ( ! preg_match( $regex, $color, $matches ) ) return false;
            return $color;
        }//1253
        /**
         * @description Parses a date into both its local and UTC equivalent, in MySQL datetime format.
         * @param $date
         * @param bool $is_utc
         * @return array|null
         */
        protected function _rest_get_date_with_gmt( $date, $is_utc = false ):array{
            $has_timezone = preg_match( '#(Z|[+-]\d{2}(:\d{2})?)$#', $date );
            $date = $this->_rest_parse_date( $date );
            if ( empty( $date ) ) return null;
            if ( ! $is_utc && ! $has_timezone ) {
                $local = gmdate( 'Y-m-d H:i:s', $date );
                $utc   = $this->_get_gmt_from_date( $local );
            } else {
                $utc   = gmdate( 'Y-m-d H:i:s', $date );
                $local = $this->_get_date_from_gmt( $utc );
            }
            return array( $local, $utc );
        }//1274
        /**
         * @description Returns a contextual HTTP error code for authorization failure.
         * @return int
         */
        protected function _rest_authorization_required_code():int{
            return $this->_is_user_logged_in() ? 403 : 401;
        }//1311
        /**
         * @description Validate a request argument based on details registered to the route.
         * @param $value
         * @param TP_REST_Request $request
         * @param $param
         * @return bool
         */
        protected function _rest_validate_request_arg( $value, TP_REST_Request $request, $param ):bool{
            $attributes = $request->get_attributes();
            if ( ! isset( $attributes['args'][ $param ] ) || ! is_array( $attributes['args'][ $param ] ) )
                return true;
            $args = $attributes['args'][ $param ];
            return $this->_rest_validate_value_from_schema( $value, $args, $param );
        }//1325
        /**
         * @description Sanitize a request argument based on details registered to the route.
         * @param $value
         * @param TP_REST_Request $request
         * @param $param
         * @return mixed
         */
        protected function _rest_sanitize_request_arg( $value,TP_REST_Request $request, $param ){
            $attributes = $request->get_attributes();
            if ( ! isset( $attributes['args'][ $param ] ) || ! is_array( $attributes['args'][ $param ] ) )
                return $value;
            $args = $attributes['args'][ $param ];
            return $this->_rest_sanitize_value_from_schema( $value, $args, $param );
        }//1345
        /**
         * @description Parse a request argument based on details registered to the route.
         * @param $value
         * @param $request
         * @param $param
         * @return mixed
         */
        protected function _rest_parse_request_arg( $value, $request, $param ){
            $is_valid = $this->_rest_validate_request_arg( $value, $request, $param );
            if ( $this->_init_error( $is_valid ) ) return $is_valid;
            $value = $this->_rest_sanitize_request_arg( $value, $request, $param );
            return $value;
        }//1368
        /**
         * @description Determines if an IP address is valid.
         * @param $ip
         * @return bool
         */
        protected function _rest_is_ip_address( $ip ):bool{
            $ipv4_pattern = '/^(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?)$/';
            if ( ! preg_match( $ipv4_pattern, $ip ) && ! Requests_IPv6::check_ipv6( $ip ) )
                return false;
            return $ip;
        }//1390
        /**
         * @description Changes a boolean-like value into the proper boolean value.
         * @param $value
         * @return bool
         */
        protected function _rest_sanitize_boolean( $value ):bool{
            if ( is_string( $value ) ) {
                $value = strtolower( $value );
                if ( in_array( $value, ['false', '0'], true ) ) $value = false;
            }
            return (bool) $value;
        }//1408
    }
}else die;