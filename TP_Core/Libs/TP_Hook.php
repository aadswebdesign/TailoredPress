<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 31-1-2022
 * Time: 04:03
 */
declare(strict_types=1);
namespace TP_Core\Libs;
use \Iterator;
use \ArrayAccess;
use TP_Core\Traits\Constructs\_construct_filter;
use TP_Core\Traits\Filters\_filter_01;
if(ABSPATH){
    final class TP_Hook implements Iterator, ArrayAccess  {
        use _filter_01;
        use _construct_filter;
        private $__iterations = [];
        private $__current_priority = [];
        private $__nesting_level = 0;
        private $__doing_action = false;
        public $callbacks = [];
        public function add_filter( $hook_name, $callback, $priority, $accepted_args ) :void{
            $idx = $this->_tp_filter_build_unique_id( $hook_name, $callback, $priority );
            $priority_existed = isset( $this->callbacks[ $priority ] );
            $this->callbacks[ $priority ][ $idx ] = [
                'function'      => $callback,
                //'string'        => $callback,
                'accepted_args' => $accepted_args,
            ];//todo accepting strings too
            // If adding a new priority to the list, put them back in sorted order.
            if (!$priority_existed && count($this->callbacks)>1){
                ksort( $this->callbacks, SORT_NUMERIC );
            }
            //todo class-wp-hook.php line 88
            if ( $this->__nesting_level > 0 ){
                $this->_resort_active_iterations( $priority, $priority_existed );
            }
        }//73
        protected function _resort_active_iterations( $new_priority = false, $priority_existed = false ) {
            $new_priorities = array_keys( $this->callbacks );
            if ( ! $new_priorities ) {
                foreach ( $this->__iterations as $index => $iteration ) {
                    $this->__iterations[ $index ] = $new_priorities;
                }
                return;
            }
            $min = min( $new_priorities );
            foreach ( $this->__iterations as $index => &$iteration ) {
                $current = current( $iteration );
                // If we're already at the end of this iteration, just leave the array pointer where it is.
                if ( false === $current ) continue;
                $iteration = $new_priorities;
                if ( $current < $min ) {
                    array_unshift( $iteration, $current );
                    continue;
                }
                while ( current( $iteration ) < $current ) {
                    if(false === next($iteration)) break;
                }
                // If we have a new priority that didn't exist, but ::apply_filters() or ::do_action() thinks it's the current priority...
                if ( $new_priority === $this->__current_priority[ $index ] && ! $priority_existed ) {
                    // If we've already moved off the end of the array, go back to the last element.
                    if ( false === current( $iteration ) ) $prev = end( $iteration );
                    else $prev = prev( $iteration );
                    // Start of the array. Reset, and go about our day.
                    if ( false === $prev ) reset( $iteration );
                    // Previous wasn't the same. Move forward again.
                    elseif ( $new_priority !== $prev ) next( $iteration );
                }
            }
            unset( $iteration );
        }//103
        //Removes a callback function from a filter hook.
        public function remove_filter( $hook_name, $callback, $priority ) {
            $function_key = $this->_tp_filter_build_unique_id( $hook_name, $callback, $priority );
            $exists = isset( $this->callbacks[ $priority ][ $function_key ] );
            if ( $exists ) {
                unset( $this->callbacks[ $priority ][ $function_key ] );
                if ( ! $this->callbacks[ $priority ] ) {
                    unset( $this->callbacks[ $priority ] );
                    if ( $this->__nesting_level > 0 ) $this->_resort_active_iterations();
                }
            }
            return $exists;
        }//178
        //Checks if a specific callback has been registered for this hook.
        public function has_filter( $hook_name = '', $callback = false ) {
            if ( false === $callback ) return $this->has_filters();
            $function_key = $this->_tp_filter_build_unique_id( $hook_name, $callback, false );
            if ( ! $function_key )return false;
            foreach ( $this->callbacks as $priority => $callbacks ) {
                if ( isset( $callbacks[ $function_key ] ) ) return $priority;
            }
            return false;
        }//214
        public function has_filters() {
            foreach ( $this->callbacks as $callbacks ) {
                if ( $callbacks ) return true;
            }
            return false;
        }//241
        public function remove_all_filters( $priority = false ) {
            if ( ! $this->callbacks ) return;
            if ( false === $priority )  $this->callbacks = [];
            elseif ( isset( $this->callbacks[ $priority ])) unset( $this->callbacks[ $priority ] );
            if ( $this->__nesting_level > 0 ) $this->_resort_active_iterations();
        }//258
        public function apply_filters( $value, ...$args ) {
            if ( ! $this->callbacks )  return $value;
            $nesting_level = $this->__nesting_level++;
            $this->__iterations[ $nesting_level ] = array_keys( $this->callbacks );
            $num_args = count( $args );
            do {
                $this->__current_priority[ $nesting_level ] = current( $this->__iterations[ $nesting_level ] );
                $priority = $this->__current_priority[ $nesting_level ];
                foreach ( $this->callbacks[ $priority ] as $the_ ) {
                    if ( ! $this->__doing_action ) $args[0] = $value;
                    // Avoid the array_slice() if possible.
                    if ( 0 === $the_['accepted_args'] ) $value = call_user_func( $the_['function'] );
                    elseif ( $the_['accepted_args'] >= $num_args )$value = call_user_func_array( $the_['function'], $args );
                    else $value = call_user_func_array( $the_['function'], array_slice( $args, 0, (int) $the_['accepted_args'] ) );
                }
            } while ( false !== next( $this->__iterations[ $nesting_level ] ) );
            unset( $this->__iterations[ $nesting_level ], $this->__current_priority[ $nesting_level ] );
            $this->__nesting_level--;
            return $value;
        }//284
        public function do_action( ...$args) {
            $this->__doing_action = true;
            $this->apply_filters( '', $args);
            if ( ! $this->__nesting_level ) $this->__doing_action = false;
        }//329
        public function get_action(...$args){
            $this->__doing_action = true;
            if ( ! $this->__nesting_level ) $this->__doing_action = false;
            return $this->apply_filters( '', $args);
        }
        public function do_all_hook( &$args ) {
            $nesting_level = $this->__nesting_level++;
            $this->__iterations[ $nesting_level ] = array_keys( $this->callbacks );
            do {
                $priority = current( $this->__iterations[ $nesting_level ] );
                foreach ( $this->callbacks[ $priority ] as $the_ ) {
                    call_user_func_array( $the_['function'], $args );
                }
            } while ( false !== next( $this->__iterations[ $nesting_level ] ) );
            unset( $this->__iterations[ $nesting_level ] );
            $this->__nesting_level--;
        }//346
        public function current_priority() {
            if ( false === current( $this->__iterations ) )
                return false;
            return current( current( $this->__iterations ) );
        }//370
        public static function build_pre_initialized_hooks( $filters ) {
            $normalized = [];
            foreach ( $filters as $hook_name => $callback_groups ) {
                if ( is_object( $callback_groups ) && $callback_groups instanceof self ) {
                    $normalized[ $hook_name ] = $callback_groups;
                    continue;
                }
                $hook = new self();
                // Loop through callback groups.
                foreach ( $callback_groups as $priority => $callbacks ) {
                    // Loop through callbacks.
                    foreach ( $callbacks as $cb ) $hook->add_filter( $hook_name, $cb['function'], $priority, $cb['accepted_args'] );
                }
                $normalized[ $hook_name ] = $hook;
            }
            return $normalized;
        }//407
        #[ReturnTypeWillChange]
        public function offsetExists( $offset ): bool{
            return isset( $this->callbacks[ $offset ] );
        }//447
        public function offsetGet( $offset ) {
            return $this->callbacks[ $offset ] ?? null;
        }//460
        public function offsetSet( $offset, $value ): void {
            if ( is_null( $offset ) ) $this->callbacks[] = $value;
             else $this->callbacks[ $offset ] = $value;
        }//475
        public function offsetUnset( $offset ): void{
            unset( $this->callbacks[ $offset ] );
        }//495
        public function current() {
            return current( $this->callbacks );
        }//507
        public function next() {
            return next( $this->callbacks );
        }//521
        public function key(){
            return key( $this->callbacks );
        }//537
        public function valid(): bool{
            return key( $this->callbacks ) !== null;
        }//549
        public function rewind(): void{
            reset( $this->callbacks );
        }//561
    }
}else die;