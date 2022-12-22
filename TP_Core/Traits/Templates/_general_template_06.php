<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 17:17
 */
namespace TP_Core\Traits\Templates;
use TP_Core\Traits\Inits\_init_locale;
if(ABSPATH){
    trait _general_template_06 {
        use _init_locale;
        /**
         * @description Retrieve the time at which the post was written.
         * @param string $format
         * @param null $post
         * @return bool
         */
        protected function _get_the_time( $format = '', $post = null ):bool{
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $_format = ! empty( $format ) ? $format : $this->_get_option( 'time_format' );
            $the_time = $this->_get_post_time( $_format, false, $post, true );
            return $this->_apply_filters( 'get_the_time', $the_time, $format, $post );
        }//2686 from general-template
        /**
         * @description Retrieve the time at which the post was written.
         * @param string $format
         * @param bool $gmt
         * @param null $post
         * @param bool $translate
         * @return bool
         */
        protected function _get_post_time( $format = 'U', $gmt = false, $post = null, $translate = false ):bool{
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $source   = ( $gmt ) ? 'gmt' : 'local';
            $datetime = $this->_get_post_datetime( $post, 'date', $source );
            if ( false === $datetime ) return false;
            if ( 'U' === $format || 'G' === $format ) {
                $time = $datetime->getTimestamp();
                if ( ! $gmt ) $time += $datetime->getOffset();
            } elseif ( $translate )
                $time = $this->_tp_date( $format, $datetime->getTimestamp(), $gmt ? new \DateTimeZone( 'UTC' ) : null );
            else {
                if ( $gmt ) $datetime = $datetime->setTimezone( new \DateTimeZone( 'UTC' ) );
                $time = $datetime->format( $format );
            }
            return $this->_apply_filters( 'get_post_time', $time, $format, $gmt );
        }//2723 from general-template
        /**
         * @description Retrieve post published or modified time as a `DateTimeImmutable` object instance.
         * @param null $post
         * @param string $field
         * @param string $source
         * @return bool|\DateTimeImmutable
         */
        protected function _get_post_datetime( $post = null, $field = 'date', $source = 'local'  ){
            $post = $this->_get_post( $post );
            if ( ! $post ) return false;
            $tp_timezone = $this->_tp_timezone();//todo
            if ( 'gmt' === $source ) {
                $time     = ( 'modified' === $field ) ? $post->post_modified_gmt : $post->post_date_gmt;
                $timezone = new \DateTimeZone( 'UTC' );
            } else {
                $time     = ( 'modified' === $field ) ? $post->post_modified : $post->post_date;
                $timezone = $tp_timezone;
            }
            if ( empty( $time ) || '0000-00-00 00:00:00' === $time ) return false;
            $datetime = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $time, $timezone );
            if ( false === $datetime ) return false;
            return $datetime->setTimezone( $tp_timezone );
        }//2786 from general-template
        /**
         * @description Retrieve post published or modified time as a Unix timestamp.
         * @param null $post
         * @param string $field
         * @return bool|int
         */
        protected function _get_post_timestamp( $post = null, $field = 'date' ){
            $datetime = $this->_get_post_datetime( $post, $field );
            if ( false === $datetime ) return false;
            return $datetime->getTimestamp();
        }//2829 from general-template
        /**
         * @description Display the time at which the post was last modified.
         * @param string $format
         */
        public function get_modified_time( $format = '' ){
            return $this->_apply_filters( 'the_modified_time',[$this,'_get_the_modified_time'] , $format );//$this->_get_the_modified_time( $format )
        }//2848 from general-template //todo renamed
        /**
         * @description Retrieve the time at which the post was last modified.
         * @param string $format
         * @param null $post
         * @return mixed
         */
        protected function _get_the_modified_time( $format = '', $post = null ){
            $post = $this->_get_post( $post );
            $_format = ! empty( $format ) ? $format : $this->_get_option( 'time_format' );
            $the_time = $this->_get_post_modified_time( $_format, false, $post, true );
            return $this->_apply_filters( 'get_the_modified_time', $the_time, $format, $post );
        }//2873 from general-template
        /**
         * @description Retrieve the time at which the post was last modified.
         * @param string $format
         * @param bool $gmt
         * @param null $post
         * @param bool $translate
         * @return mixed
         */
        protected function _get_post_modified_time( $format = 'U', $gmt = false, $post = null, $translate = false ){
            $post = $this->_get_post( $post );
            $_format = ! empty( $format ) ? $format : $this->_get_option( 'time_format' );
            $the_time = $this->_get_post_modified_time( $_format, false, $post, true );
            return $this->_apply_filters( 'get_the_modified_time', $the_time, $format, $gmt, $post, $translate );
        }//2912 from general-template
        /**
         * @description Display the weekday on which the post was written.
         * @return string
         */
        protected function _get_the_weekday():string{
            $tp_locale = $this->_init_locale();
            $post = $this->_get_post();
            if ( ! $post ) return false;
            $the_weekday = $tp_locale->get_weekday( $this->_get_post_time( 'w', false, $post ) );
            return $this->_apply_filters( 'the_weekday', $the_weekday );
        }//2963 from general-template
        /**
         * @description Display the day of the weekday on which the post was written.
         * @param string $before
         * @param string $after
         * @return string
         */
        protected function _get_the_weekday_date( $before = '', $after = '' ):string{
            $tp_locale = $this->_init_locale();
            $post = $this->_get_post();
            if ( ! $post ) return false;
            $the_weekday_date = '';
            if ( $this->tp_current_day !== $this->tp_previous_weekday ) {
                $the_weekday_date .= $before;
                $the_weekday_date .= $tp_locale->get_weekday( $this->_get_post_time( 'w', false, $post ) );
                $the_weekday_date .= $after;
                $this->tp_previous_weekday   = $this->tp_current_day;
            }
            return $this->_apply_filters( 'the_weekday_date', $the_weekday_date, $before, $after );
        }//2999 from general-template
        /**
         * @description Fire the tp_head action.
         */
        protected function _tp_get_head(){
           return $this->_get_action( 'tp_head' );
        }//3036 from general-template //todo edited
    }
}else die;