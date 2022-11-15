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
    trait _cron_01 {
        /**
         * @description Schedules an event to run only once.
         * @param $timestamp
         * @param $hook
         * @param array $args
         * @param bool $tp_error
         * @return bool|TP_Error
         */
        protected function _tp_schedule_single_event( $timestamp, $hook, $args = [], $tp_error = false ){
            if ( ! is_numeric( $timestamp ) || $timestamp <= 0 ) {
                if ( $tp_error ) {
                    return new TP_Error(
                        'invalid_timestamp',
                        $this->__( 'Event timestamp must be a valid Unix timestamp.' )
                    );
                }
                return false;
            }
            $event = (object) [
                'hook'      => $hook,
                'timestamp' => $timestamp,
                'schedule'  => false,
                'args'      => $args,
            ];
            $pre = $this->_apply_filters( 'pre_schedule_event', null, $event, $tp_error );
            if ( null !== $pre ) {
                if ( $tp_error && false === $pre ) {
                    return new TP_Error(
                        'pre_schedule_event_false',
                        $this->__( 'Some custom code prevented the event from being scheduled.' )
                    );
                }
                if ( ! $tp_error && $this->_init_error( $pre ) ) return false;
                return $pre;
            }
            $cron_s = $this->_get_cron_array();
            if ( ! is_array( $cron_s ) ) $cron_s = [];
            $key       = md5( serialize( $event->args ) );
            $duplicate = false;
            if ( $event->timestamp < time() + 10 * MINUTE_IN_SECONDS ) $min_timestamp = 0;
            else $min_timestamp = $event->timestamp - 10 * MINUTE_IN_SECONDS;
            if ( $event->timestamp < time() )  $max_timestamp = time() + 10 * MINUTE_IN_SECONDS;
            else $max_timestamp = $event->timestamp + 10 * MINUTE_IN_SECONDS;
            foreach ( $cron_s as $event_timestamp => $cron ) {
                if ( $event_timestamp < $min_timestamp ) continue;
                if ( $event_timestamp > $max_timestamp ) break;
                if ( isset( $cron[ $event->hook ][ $key ] ) ) {
                    $duplicate = true;
                    break;
                }
            }
            if ( $duplicate ) {
                if ( $tp_error ) {
                    return new TP_Error(
                        'duplicate_event',
                        $this->__( 'A duplicate event already exists.' )
                    );
                }
                return false;
            }
            $event = $this->_apply_filters( 'schedule_event', $event );
            if ( ! $event ) {
                if ( $tp_error ) {
                    return new TP_Error(
                        'schedule_event_false',
                        $this->__( 'A plugin disallowed this event.' )
                    );
                }
                return false;
            }
            $cron_s[ $event->timestamp ][ $event->hook ][ $key ] = array(
                'schedule' => $event->schedule,
                'args'     => $event->args,
            );
            uksort( $cron_s, 'strnatcasecmp' );
            return $this->_set_cron_array( $cron_s, $tp_error );
        }//39
        /**
         * @description Schedules a recurring event.
         * @param $timestamp
         * @param $recurrence
         * @param $hook
         * @param array $args
         * @param bool $tp_error
         * @return bool|TP_Error
         */
        protected function _tp_schedule_event( $timestamp, $recurrence, $hook, $args = [], $tp_error = false ){
            if ( ! is_numeric( $timestamp ) || $timestamp <= 0 ) {
                if ( $tp_error ) {
                    return new TP_Error(
                        'invalid_timestamp',
                        $this->__( 'Event timestamp must be a valid Unix timestamp.' )
                    );
                }
                return false;
            }
            $schedules = $this->_tp_get_schedules();
            if ( ! isset( $schedules[ $recurrence ] ) ) {
                if ( $tp_error ) {
                    return new TP_Error(
                        'invalid_schedule',
                        $this->__( 'Event schedule does not exist.' )
                    );
                }
                return false;
            }
            $event = (object) [
                'hook'      => $hook,
                'timestamp' => $timestamp,
                'schedule'  => $recurrence,
                'args'      => $args,
                'interval'  => $schedules[ $recurrence ]['interval'],
            ];
            $pre = $this->_apply_filters( 'pre_schedule_event', null, $event, $tp_error );
            if ( null !== $pre ) {
                if ( $tp_error && false === $pre ) {
                    return new TP_Error(
                        'pre_schedule_event_false',
                        $this->__( 'Some custom code prevented the event from being scheduled.' )
                    );
                }
                if ( ! $tp_error && $this->_init_error( $pre ) ) return false;
                return $pre;
            }
            /** This filter is documented in wp-includes/cron.php */
            $event = $this->_apply_filters( 'schedule_event', $event );

            if ( ! $event ) {
                if ( $tp_error ) {
                    return new TP_Error(
                        'schedule_event_false',
                        $this->__( 'Some custom code disallowed this event.' )
                    );
                }

                return false;
            }

            $key = md5( serialize( $event->args ) );

            $cron_s = $this->_get_cron_array();
            if ( ! is_array( $cron_s ) ) {
                $cron_s = [];
            }

            $cron_s[ $event->timestamp ][ $event->hook ][ $key ] = array(
                'schedule' => $event->schedule,
                'args'     => $event->args,
                'interval' => $event->interval,
            );
            uksort( $cron_s, 'strnatcasecmp' );

            return $this->_set_cron_array( $cron_s, $tp_error );
        }//239
        /**
         * @description Reschedules a recurring event.
         * @param $timestamp
         * @param $recurrence
         * @param $hook
         * @param array $args
         * @param bool $tp_error
         * @return bool|TP_Error|void
         */
        protected function _tp_reschedule_event( $timestamp, $recurrence, $hook, $args = [], $tp_error = false ){
            if ( ! is_numeric( $timestamp ) || $timestamp <= 0 ) {
                if ( $tp_error ) {
                    return new TP_Error(
                        'invalid_timestamp',
                        $this->__( 'Event timestamp must be a valid Unix timestamp.' )
                    );
                }
                return false;
            }
            $schedules = $this->_tp_get_schedules();
            $interval  = 0;
            if ( isset( $schedules[ $recurrence ] ) )
                $interval = $schedules[ $recurrence ]['interval'];
            if ( 0 === $interval ) {
                $scheduled_event = $this->_tp_get_scheduled_event( $hook, $args, $timestamp );
                if ( $scheduled_event && isset( $scheduled_event->interval ) )
                    $interval = $scheduled_event->interval;
            }
            $event = (object) [
                'hook'      => $hook,
                'timestamp' => $timestamp,
                'schedule'  => $recurrence,
                'args'      => $args,
                'interval'  => $interval,
            ];
            $pre = $this->_apply_filters( 'pre_reschedule_event', null, $event, $tp_error );
            if ( null !== $pre ) {
                if ( $tp_error && false === $pre ) {
                    return new TP_Error(
                        'pre_reschedule_event_false',
                        $this->__( 'Something prevented the event from being rescheduled.' )
                    );
                }
                if ( ! $tp_error && $this->_init_error( $pre ) ) return false;
                return $pre;
            }
            if ( 0 === $interval ) {
                if ( $tp_error ) {
                    return new TP_Error(
                        'invalid_schedule',
                        $this->__( 'Event schedule does not exist.' )
                    );
                }
                return false;
            }
            $now = time();
            if ( $timestamp >= $now ) $timestamp = $now + $interval;
            else $timestamp = $now + ( $interval - ( ( $now - $timestamp ) % $interval ) );
            return $this->_tp_schedule_event( $timestamp, $recurrence, $hook, $args, $tp_error );
        }//348
        /**
         * @description Un-schedule a previously scheduled event.
         * @param $timestamp
         * @param $hook
         * @param array $args
         * @param bool $tp_error
         * @return bool|TP_Error
         */
        protected function _tp_un_schedule_event( $timestamp, $hook, $args = [], $tp_error = false ){
            if ( ! is_numeric( $timestamp ) || $timestamp <= 0 ) {
                if ( $tp_error ) {
                    return new TP_Error(
                        'invalid_timestamp',
                        $this->__( 'Event timestamp must be a valid Unix timestamp.' )
                    );
                }
                return false;
            }
            $pre = $this->_apply_filters( 'pre_un_schedule_event', null, $timestamp, $hook, $args, $tp_error );
            if ( null !== $pre ) {
                if ( $tp_error && false === $pre ) {
                    return new TP_Error(
                        'pre_un_schedule_event_false',
                        $this->__( 'Something prevented the event from being unscheduled.' )
                    );
                }
                if ( ! $tp_error && $this->_init_error( $pre ) ) return false;
                return $pre;
            }
            $cron_s = $this->_get_cron_array();
            $key   = md5( serialize( $args ) );
            unset( $cron_s[ $timestamp ][ $hook ][ $key ] );
            if ( empty( $cron_s[ $timestamp ][ $hook ] ) )
                unset( $cron_s[ $timestamp ][ $hook ] );
            if ( empty( $cron_s[ $timestamp ] ) )
                unset( $cron_s[ $timestamp ] );
            return $this->_set_cron_array( $cron_s, $tp_error );
        }//469
        /**
         * @description Un-schedules all events attached to the hook
         * @description .with the specified arguments.
         * @param $hook
         * @param array $args
         * @param bool $tp_error
         * @return bool|int|TP_Error
         */
        protected function _tp_clear_scheduled_hook( $hook, $args = [], $tp_error = false ){
            $pre = $this->_apply_filters( 'pre_clear_scheduled_hook', null, $hook, $args, $tp_error );
            if ( null !== $pre ) {
                if ( $tp_error && false === $pre ) {
                    return new TP_Error(
                        'pre_clear_scheduled_hook_false',
                        $this->__( 'Something prevented the hook from being cleared.' )
                    );
                }
                if ( ! $tp_error && $this->_init_error( $pre ) ) return false;
                return $pre;
            }
            $cron_s = $this->_get_cron_array();
            if ( empty( $cron_s ) ) return 0;
            $results = [];
            $key     = md5( serialize( $args ) );
            foreach ( $cron_s as $timestamp => $cron ) {
                if ( isset( $cron[ $hook ][ $key ] ) )
                    $results[] = $this->_tp_un_schedule_event( $timestamp, $hook, $args, true );
            }
            $errors = array_filter( $results, 'is_tp_error' );
            $error  = new TP_Error();
            if ( $errors ) {
                if ( $tp_error ) {
                    array_walk( $errors, array( $error, 'merge_from' ) );
                    return $error;
                }
                return false;
            }
            return count( $results );
        }//553
        /**
         * @description Un-schedules all events attached to the hook.
         * @param $hook
         * @param bool $tp_error
         * @return bool|int|number|TP_Error
         */
        protected function _tp_un_schedule_hook( $hook, $tp_error = false ){
            $pre = $this->_apply_filters( 'pre_un_schedule_hook', null, $hook, $tp_error );
            if ( null !== $pre ) {
                if ( $tp_error && false === $pre ) {
                    return new TP_Error(
                        'pre_un_schedule_hook_false',
                        $this->__( 'Something prevented the hook from being cleared.' )
                    );
                }
                if ( ! $tp_error && $this->_init_error( $pre ) )
                    return false;
                return $pre;
            }
            $cron_s = $this->_get_cron_array();
            if ( empty( $cron_s ) ) return 0;
            $results = [];
            foreach ( $cron_s as $timestamp => $args ) {
                if ( ! empty( $cron_s[ $timestamp ][ $hook ] ) )
                    $results[] = count( $cron_s[ $timestamp ][ $hook ] );
                unset( $cron_s[ $timestamp ][ $hook ] );
                if ( empty( $cron_s[ $timestamp ] ) )
                    unset( $cron_s[ $timestamp ] );
            }
            if ( empty( $results ) ) return 0;
            $set = $this->_set_cron_array( $cron_s, $tp_error );
            if ( true === $set ) return array_sum( $results );
            return $set;
        }//651
        /**
         * @description Retrieve a scheduled event.
         * @param $hook
         * @param array $args
         * @param null $timestamp
         * @return bool|object
         */
        protected function _tp_get_scheduled_event( $hook, $args = array(), $timestamp = null ){
            $pre = $this->_apply_filters( 'pre_get_scheduled_event', null, $hook, $args, $timestamp );
            if ( null !== $pre ) return $pre;
            if ( null !== $timestamp && ! is_numeric( $timestamp ) )
                return false;
            $cron_s = $this->_get_cron_array();
            if ( empty( $cron_s ) )
                return false;
            $key = md5( serialize( $args ) );
            if ( ! $timestamp ) {
                $next = false;
                /** @noinspection SuspiciousLoopInspection */
                foreach ($cron_s as $timestamp => $cron ) {
                    if ( isset( $cron[ $hook ][ $key ] ) ) {
                        $next = $timestamp;
                        break;
                    }
                }
                if ( ! $next ) return false;
                $timestamp = $next;
            } elseif ( ! isset( $cron_s[ $timestamp ][ $hook ][ $key ] ) )
                return false;
            $event = (object) [
                'hook'      => $hook,
                'timestamp' => $timestamp,
                'schedule'  => $cron_s[ $timestamp ][ $hook ][ $key ]['schedule'],
                'args'      => $args,
            ];
            if ( isset( $cron_s[ $timestamp ][ $hook ][ $key ]['interval'] ) )
                $event->interval = $cron_s[ $timestamp ][ $hook ][ $key ]['interval'];
            return $event;
        }//737
        /**
         * @description Retrieve the next timestamp for an event.
         * @param $hook
         * @param array $args
         * @return bool
         */
        protected function _tp_next_scheduled( $hook, $args = [] ): bool{
            $next_event = $this->_tp_get_scheduled_event( $hook, $args );
            if ( ! $next_event )
                return false;
            return $next_event->timestamp;
        }//816
        /**
         * todo 's @description Sends a request to run cron through HTTP request that doesn't halt page loading.
         * @param int $gmt_time
         * @return bool
         */
        protected function _spawn_cron( $gmt_time = 0 ): bool{
            if ( !defined( 'TP_CRON_LOCK_TIMEOUT' )) define('TP_CRON_LOCK_TIMEOUT', '');
            if ( ! $gmt_time ) $gmt_time = $this->_tp_microtime( true );
            if ( defined( 'DOING_CRON' ) || isset( $_GET['doing_tp_cron'] ) )
                return false;
            $lock = $this->_get_transient( 'doing_cron' );
            if ( $lock > $gmt_time + 10 * MINUTE_IN_SECONDS ) $lock = 0;
            if ( $lock + TP_CRON_LOCK_TIMEOUT > $gmt_time ) return false;
            $cron_s = $this->_tp_get_ready_cron_jobs();
            if ( empty( $cron_s ) ) return false;
            $keys = array_keys( $cron_s );
            if ( isset( $keys[0] ) && $keys[0] > $gmt_time ) return false;
            if ( defined( 'ALTERNATE_TP_CRON' ) && ALTERNATE_TP_CRON ) {
                if ( 'GET' !== $_SERVER['REQUEST_METHOD'] || defined( 'DOING_AJAX' ) || defined('XML_RPC_REQUEST'))
                    return false;
                $doing_tp_cron = sprintf( '%.22F', $gmt_time );
                $this->_set_transient( 'doing_cron', $doing_tp_cron );
                ob_start();
                $this->_tp_redirect( $this->_add_query_arg( 'doing_tp_cron', $doing_tp_cron, $this->_tp_unslash( $_SERVER['REQUEST_URI'] ) ) );
                echo ' ';
                $this->_tp_ob_end_flush_all();
                flush();
                //todo include_once ABSPATH . 'wp-cron.php';
                return true;
            }
            $doing_tp_cron = sprintf( '%.22F', $gmt_time );
            $this->_set_transient( 'doing_cron', $doing_tp_cron );
            $cron_request = $this->_apply_filters(
                'cron_request',
                [
                    'url'  => $this->_add_query_arg( 'doing_tp_cron', $doing_tp_cron, $this->_site_url( 'wp-cron.php' ) ),
                    'key'  => $doing_tp_cron,
                    'args' => [
                        'timeout'   => 0.01,
                        'blocking'  => false,
                        'ssl_verify' => $this->_apply_filters( 'https_local_ssl_verify', false ),
                    ],
                ],
                $doing_tp_cron
            );
            $result = $this->_tp_remote_post( $cron_request['url'], $cron_request['args'] );
            return ! $this->_init_error( $result );
        }//834
    }
}else die;