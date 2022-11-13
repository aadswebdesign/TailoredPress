<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 5-3-2022
 * Time: 19:26
 */
declare(strict_types=1);
namespace TP_Core\Traits\Actions;
use TP_Core\Traits\Filters\_all_hooks_helper;
use TP_Core\Libs\TP_Hook;
if(ABSPATH){
    trait _action_01 {
        use _all_hooks_helper;
        //taken from plugins.php
        protected function _add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ){
            return $this->_add_filter($hook_name, $callback, $priority, $accepted_args);
        }//398
        protected function _has_action( $hook_name, $callback = false ){
            return $this->_has_filter( $hook_name, $callback );
        }//545
        /**
         * @description Calls the callback functions that have been added to an action hook.
         * @param $hook_name
         * @param array ...$arg
         */
        protected function _do_action( $hook_name, ...$arg ): void{
            //global $tp_filter, $tp_actions, $tp_current_filter;
            if ( ! isset( $this->tp_actions[ $hook_name ] ) ) $this->tp_actions[ $hook_name ] = 1;
            else ++$this->tp_actions[ $hook_name ];
            if ( isset( $this->tp_filter['all'] ) ) {
                $tp_current_filter[] = $hook_name;
                $all_args = func_get_args();
                $this->_tp_call_all_hook( $all_args );
            }
            if ( ! isset( $this->tp_filter[ $hook_name ] ) ) {
                if ( isset( $this->tp_filter['all'] ) ) array_pop( $this->tp_current_filter );
                return;
            }
            if (!isset( $this->tp_filter['all'] ) ) $this->tp_current_filter[] = $hook_name;
            if ( empty( $arg ) ) $arg[] = '';
            elseif ($this->tp_filter instanceof TP_Hook && is_array( $arg[0] ) && 1 === count( $arg[0] ) && isset( $arg[0][0] ) && is_object( $arg[0][0] ) )$arg[0] = $arg[0][0];
                $this->tp_filter[ $hook_name ]->do_action($arg);
            array_pop( $this->tp_current_filter );
        }//439
        /**
         * @description Returns the callback functions that have been added to an action hook.
         * @param $hook_name
         * @param array ...$arg
         */
        protected function _get_action( $hook_name, ...$arg){
            if ( ! isset( $this->tp_actions[ $hook_name ] ) ) $this->tp_actions[ $hook_name ] = 1;
            else ++$this->tp_actions[ $hook_name ];
            if ( isset( $this->tp_filter['all'] ) ) {
                $tp_current_filter[] = $hook_name;
                $all_args = func_get_args();
                $this->_tp_call_all_hook( $all_args );
            }
            if ( ! isset( $this->tp_filter[ $hook_name ] ) ) {
                if ( isset( $this->tp_filter['all'] ) ) array_pop( $this->tp_current_filter );
                return;
            }
            if (!isset( $this->tp_filter['all'] ) ) $this->tp_current_filter[] = $hook_name;
            if ( empty( $arg ) ) $arg[] = '';
            elseif ($this->tp_filter instanceof TP_Hook && is_array( $arg[0] ) && 1 === count( $arg[0] ) && isset( $arg[0][0] ) && is_object( $arg[0][0] ) )$arg[0] = $arg[0][0];
            array_pop( $this->tp_current_filter );
            return $this->tp_filter[ $hook_name ]->get_action($arg);
        }//added proud on this
        /**
         * @description Calls the callback functions that have been added to an action hook, specifying arguments in an array.
         * @param $hook_name
         * @param $args
         */
        protected function _do_action_ref_array( $hook_name, $args ): void{
            global $tp_filter, $tp_actions, $tp_current_filter;
            $tp_filter = null;
            //if($tp_filter instanceof TP_Hook){
                //$tp_filter = (array) $tp_filter;
            //}
            if ( ! isset( $tp_actions[ $hook_name ] ) ) $tp_actions[ $hook_name ] = 1;
            else ++$tp_actions[ $hook_name ];
            if ( isset( $tp_filter['all'] ) ) {
                $tp_current_filter[] = $hook_name;
                $all_args = func_get_args();
                $this->_tp_call_all_hook( $all_args );
            }
            if ( ! isset( $tp_filter[ $hook_name ] ) ) {
                if ( isset( $tp_filter['all'] ) ) array_pop( $tp_current_filter );
                return;
            }
            if ($tp_filter instanceof TP_Hook && ! isset( $tp_filter['all'] ) ) $tp_current_filter[] = $hook_name;
            $tp_filter[ $hook_name ]->do_action($args);
            array_pop( $tp_current_filter );
        }//494
        protected function _remove_action( $hook_name, $callback, $priority = 10 ){
            return $this->_remove_filter( $hook_name, $callback, $priority );
        }//569
        protected function _remove_all_actions( $hook_name, $priority = false ){
            return $this->_remove_all_filters( $hook_name, $priority );
        }//585
        /**
         * @description Retrieves the name of the current action hook.
         * @return mixed
         */
        protected function _current_action(){
            return $this->_current_filter();
        }//596
        protected function _doing_action( $hook_name = null ): string{
            return $this->_doing_filter( $hook_name );
        }//607
        protected function _did_action( $hook_name ): int{
            global $tp_actions;
            if ( ! isset( $tp_actions[ $hook_name ] ) ) return 0;
            return $tp_actions[ $hook_name ];
        }//621
    }
}else die;