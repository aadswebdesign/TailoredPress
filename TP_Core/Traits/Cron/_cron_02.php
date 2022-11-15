<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 10-3-2022
 * Time: 16:26
 */
namespace TP_Core\Traits\Cron;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    trait _cron_02 {
        /**
         * @description Register _tp_cron() to run on the {@see 'tp_loaded'} action.
         * @return bool|int|null
         */
        protected function _tp_cron(){
            if ( $this->_did_action( 'tp_loaded' ) )
                return $this->_construct_cron();
            $this->_add_action( 'tp_loaded', [$this,'__construct_cron'], 20 );
            return null;
        }//955
        /**
         * todo lookup request url
         * @description Run scheduled callbacks or spawn cron for all scheduled events.
         * @return bool|int
         */
        protected function _construct_cron(){
            if (( defined( 'DISABLE_TP_CRON' ) && DISABLE_TP_CRON ) || strpos( $_SERVER['REQUEST_URI'], '/tp_cron.php' ) !== false)
                return 0;
            $cron_s = $this->_tp_get_ready_cron_jobs();
            if ( empty( $cron_s ) ) return 0;
            $gmt_time = microtime( true );
            $keys     = array_keys( $cron_s );
            if ( isset( $keys[0] ) && $keys[0] > $gmt_time ) return 0;
            $schedules = $this->_tp_get_schedules();
            $results   = array();
            foreach ( $cron_s as $timestamp => $cron_hooks ) {
                if ( $timestamp > $gmt_time ) break;
                foreach ( (array) $cron_hooks as $hook => $args ) {
                    if ( isset( $schedules[ $hook ]['callback'] ) && ! call_user_func( $schedules[ $hook ]['callback'] ) )
                        continue;
                    $results[] = $this->_spawn_cron( $gmt_time );
                    break 2;
                }
            }
            if ( in_array( false, $results, true ) ) return false;
            return count( $results );
        }//977
        /**
         * @description Retrieve supported event recurrence schedules.
         * @return bool|int
         */
        protected function _tp_get_schedules(){
            $schedules = [
                'hourly'     => [
                    'interval' => HOUR_IN_SECONDS,
                    'display'  => $this->__( 'Once Hourly' ),
                ],
                'twice_daily' => [
                    'interval' => 12 * HOUR_IN_SECONDS,
                    'display'  => $this->__( 'Twice Daily' ),
                ],
                'daily'      => [
                    'interval' => DAY_IN_SECONDS,
                    'display'  => $this->__( 'Once Daily' ),
                ],
                'weekly'     => [
                    'interval' => WEEK_IN_SECONDS,
                    'display'  => $this->__( 'Once Weekly' ),
                ],
            ];
            return array_merge( $this->_apply_filters( 'cron_schedules', array() ), $schedules );
        }//1045
        /**
         * @description Retrieve the recurrence schedule for an event.
         * @param $hook
         * @param array $args
         * @return array
         */
        protected function _tp_get_schedule( $hook, $args = [] ):array{
            $schedule = false;
            $event    = $this->_tp_get_scheduled_event( $hook, $args );
            if ( $event ) $schedule = $event->schedule;
            return $this->_apply_filters( 'get_schedule', $schedule, $hook, $args );
        }//1088
        /**
         * @description Retrieve cron jobs ready to be run.
         * @return array
         */
        protected function _tp_get_ready_cron_jobs():array{
            $pre = $this->_apply_filters( 'pre_get_ready_cron_jobs', null );
            if ( null !== $pre ) return $pre;
            $cron_s = $this->_get_cron_array();
            if ( ! is_array( $cron_s ) ) return [];
            $gmt_time = microtime( true );
            $keys     = array_keys( $cron_s );
            if ( isset( $keys[0] ) && $keys[0] > $gmt_time ) return [];
            $results = [];
            foreach ( $cron_s as $timestamp => $cron_hooks ) {
                if ( $timestamp > $gmt_time ) break;
                $results[ $timestamp ] = $cron_hooks;
            }
            return $results;
        }//1118
        /**
         * @description Retrieve cron info array option.
         * @return array|bool
         */
        protected function _get_cron_array(){
            $cron = $this->_get_option( 'cron' );
            if ( ! is_array( $cron ) ) return false;
            if ( ! isset( $cron['version'] ) )
                $cron = $this->_upgrade_cron_array( $cron );
            unset( $cron['version'] );
            return $cron;
        }//1169
        /**
         * @description Updates the cron option with the new cron array.
         * @param $cron
         * @param bool $tp_error
         * @return TP_Error
         */
        protected function _set_cron_array( $cron, $tp_error = false ):TP_Error{
            if ( ! is_array( $cron ) ) $cron = [];
            $cron['version'] = 2;
            $result          = $this->_update_option( 'cron', $cron );
            if ( $tp_error && ! $result ) {
                return new TP_Error(
                    'could_not_set',
                    $this->__( 'The cron event list could not be saved.' )
                );
            }
            return $result;
        }//1197
        /**
         * @description Upgrade a Cron info array.
         * @param $cron
         * @return array
         */
        protected function _upgrade_cron_array( $cron ):array{
            if ( isset( $cron['version'] ) && 2 === $cron['version'] ) return $cron;
            $new_cron = [];
            foreach ( (array) $cron as $timestamp => $hooks ) {
                foreach ( (array) $hooks as $hook => $args ) {
                    $key                                     = md5( serialize( $args['args'] ) );
                    $new_cron[ $timestamp ][ $hook ][ $key ] = $args;
                }
            }
            $new_cron['version'] = 2;
            $this->_update_option( 'cron', $new_cron );
            return $new_cron;
        }//1226
    }
}else die;