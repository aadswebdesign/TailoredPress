<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 19:12
 */
declare(strict_types=1);
namespace TP_Core\Traits\Filters;
use TP_Core\Traits\Inits\_init_filter;
use TP_Core\Libs\TP_Hook;
if(ABSPATH){
    trait _filter_01 {
        use _init_filter;
        use _all_hooks_helper;
        // from plugins.php
        /**
         * @description Adds a callback function to a filter hook.
         * @param $hook_name
         * @param $callback
         * @param int $priority
         * @param int $accepted_args
         * @return bool:string
         */
        protected function _add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1  ):bool{
            //global $tp_filter;
            if($this->tp_filter instanceof TP_Hook || !isset($this->tp_filter[$hook_name])) $this->tp_filter[$hook_name] = new TP_Hook();
            //$this->tp_filter = null;
            if($this->tp_filter !== null){
                $this->tp_filter[$hook_name]->add_filter($hook_name, $callback, $priority, $accepted_args);
            }
            return true;
        }//114
        /**
         * @description Calls the callback functions that have been added to a filter hook.
         * @param $hook_name
         * @param $value
         * @param $args
         * @return mixed
         */
        protected function _apply_filters( $hook_name, $value, ...$args ){
            //global $tp_filter, $tp_current_filter;
            $func_args = array_merge(func_get_args(), $args);
            if ( isset( $this->tp_filter['all'] ) ) {
                $this->tp_current_filter[] = $hook_name;
                $this->_tp_call_all_hook($func_args);
            }
            if ( ! isset( $this->tp_filter[$hook_name] ) ) {
                if ( isset( $this->tp_filter['all'] ) ) array_pop( $this->tp_current_filter );
                return $value;
            }
            if (!isset( $this->tp_filter['all'] ) ) $this->tp_current_filter[] = $hook_name;
            array_shift( $func_args );
            $filtered = null;
            if($this->tp_filter instanceof TP_Hook){
                $filtered = $this->tp_filter[ $hook_name ]->apply_filters( $value, $func_args );
            }
            array_pop( $this->tp_current_filter );
            return $filtered;
        }//163
        /**
         * @description Calls the callback functions that have been added to a filter hook, specifying arguments in an array.
         * @param $hook_name
         * @param $args
         * @return mixed
         */
        protected function _apply_filters_ref_array( $hook_name, $args ){
            //global $tp_filter, $tp_current_filter;
            if ( isset( $this->tp_filter['all'] ) ) {
                $this->tp_current_filter[] = $hook_name;
                $this->_tp_call_all_hook($args);
            }
            if ( ! isset( $this->tp_filter[$hook_name] ) ) {
                if ( isset( $this->tp_filter['all'] ) ) array_pop( $this->tp_current_filter );
                return $args[0];
            }
            if ( ! isset( $this->tp_filter['all'] ) )
                $this->tp_current_filter[] = $hook_name;
            $filtered = null;
            if($this->tp_filter instanceof TP_Hook){
                $filtered = $this->tp_filter[ $hook_name ]->apply_filters( $args[0], $args );
            }
            array_pop( $this->tp_current_filter );
            return $filtered;
        }//211
        /**
         * @description Checks if any filter has been registered for a hook.
         * @param $hook_name
         * @param $callback
         * @return mixed
         */
        protected function _has_filter( $hook_name, $callback = false){
            //global $tp_filter;
            if (! $this->tp_filter instanceof TP_Hook &&  ! isset( $this->tp_filter[$hook_name] ) ) return false;
            return $this->tp_filter[ $hook_name ]->has_filter( $hook_name, $callback);
        }//258
        /**
         * @description Removes a callback function from a filter hook.
         * @param $hook_name
         * @param $callback
         * @param int $priority
         * @return bool
         */
        protected function _remove_filter( $hook_name, $callback, $priority = 10 ): bool{
            //global $tp_filter;
            $r = false;
            if ($this->tp_filter instanceof TP_Hook &&  isset( $this->tp_filter[$hook_name] ) ){
                $r = $this->tp_filter[$hook_name]->remove_filter( $hook_name, $callback, $priority );
                if ( ! $this->tp_filter[ $hook_name ]->callbacks ) unset( $this->tp_filter[ $hook_name ] );
            }
            return $r;
        }//290
        /**
         *  @description Removes all of the callback functions from a filter hook.
         * @param $hook_name
         * @param bool $priority
         * @return bool
         */
        protected function _remove_all_filters( $hook_name, $priority = false): bool{
            //global $tp_filter;
            if ($this->tp_filter instanceof TP_Hook && isset( $this->tp_filter[ $hook_name ] ) ) {
                $this->tp_filter[ $hook_name ]->remove_all_filters( $priority );
                if ( ! $this->tp_filter[ $hook_name ]->has_filters() ) unset( $this->tp_filter[ $hook_name ] );
            }
            return true;
        }//318
        /**
         * @return mixed
         */
        protected function _current_filter(){
            //global $tp_current_filter;
            return end( $this->tp_current_filter );
        }//341
        /**
         * @param null $hook_name
         * @return bool
         */
        protected function _doing_filter( $hook_name = null ): bool{
            //global $tp_current_filter;
            if ( null === $hook_name ) return !empty($this->tp_current_filter);
            return in_array( $hook_name, $this->tp_current_filter, true );
        }//368

        /**
         * @description Builds Unique ID for storage and retrieval.
         * @param $hook_name
         * @param $callback
         * @param $priority
         * @return null|string
         */
        protected function _tp_filter_build_unique_id( $hook_name, $callback, $priority): ?string{
            if(is_string($callback)){ return $callback;}
            if(is_object( $callback )){$callback = [$callback, ''];}
            else{ $callback = (array) $callback;}
            if(is_object( $callback[0])){ return spl_object_hash( $callback[0] ) . $callback[1];}
            if ( is_string( $callback[0] ) ) { return $callback[0] . '::' . $callback[1];}
        } //945 todo needs testing/lookup
        /**
         * @description Fires functions attached to a deprecated filter hook.
         * @param $hook_name
         * @param $args
         * @param $version
         * @param string $replacement
         * @param string $message
         * @return mixed
         */
        protected function _apply_filters_deprecated( $hook_name, $args, $version, $replacement = '', $message = '' ){
            if ( ! $this->_has_filter( $hook_name ) ) return $args[0];
            $this->_deprecated_hook( $hook_name, $version, $replacement, $message );
            return $this->_apply_filters_ref_array( $hook_name, $args );
        }//657
    }
}else die;