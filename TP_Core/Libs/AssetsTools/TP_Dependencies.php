<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-5-2022
 * Time: 09:23
 */
declare(strict_types=1);
namespace TP_Core\Libs\AssetsTools;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Methods\_methods_03;
use TP_Core\Traits\Methods\_methods_09;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_03;
use TP_Core\Traits\Filters\_filter_01;
if(ABSPATH){
    class TP_Dependencies implements DependenciesInterface {
        use _action_01;
        use _formats_08,_formats_07;
        use _filter_01;
        use _methods_03, _methods_09,_methods_12;
        use _I10n_01,_I10n_03;
        private $__all_queued_deps;
        private $__queued_before_register = [];
        public $args = [];
        public $done = [];
        public $group = 0;
        public $groups = [];
        public $queue;
        public $registered = [];
        public $to_do = [];
        public function do_items( $handles = false, $group = false ): array{
            $handles = false === $handles ?: $this->queue;
            $this->all_deps( $handles );
            foreach ((array) $this->to_do as $key => $handle ) {
                if (isset($this->registered[$handle ]) && !in_array($handle,$this->done,true)){
                    if ($this->do_item( $handle, $group ) ) $this->done[] = $handle;
                    unset( $this->to_do[ $key ] );
                }
            }
            return $this->done;
        }//120
        public function do_item( $handle, $group = false ): bool{
            return isset( $this->registered[ $handle ],$group );//todo looks good this way but or it is working?
        }//158
        public function all_deps( $handles, $recursion = false, $group = false ): bool{
            $handles = (array) $handles;
            if (!$handles) return false;
            foreach ($handles as $handle){
                $handle_parts = explode( '?', $handle );
                $handle       = $handle_parts[0];
                $queued       = in_array( $handle, $this->to_do, true );
                if(in_array( $handle, $this->done, true ) ) continue;
                $moved     = $this->set_group( $handle, $recursion, $group );
                $new_group = $this->groups[ $handle ];
                if ( $queued && ! $moved ) continue;
                $keep_going = true;
                if(!isset($this->registered[ $handle])) $keep_going = false;
                elseif($this->registered[ $handle ]->deps && array_diff( $this->registered[ $handle ]->deps, array_keys($this->registered)))
                    $keep_going = false;
                elseif($this->registered[ $handle ]->deps && ! $this->all_deps( $this->registered[ $handle ]->deps, true, $new_group))
                    $keep_going = false;
                if (!$keep_going){
                    if($recursion) return false;
                    else continue;
                }
                if ($queued) continue;
                if (isset($handle_parts[1])) $this->args[ $handle ] = $handle_parts[1];
                $this->to_do[] = $handle;
            }
            return true;
        }//179
        public function add( $handle, ...$data ): bool {
            if(isset($this->registered[$handle])) return false;
            $this->registered[ $handle ] = new Dependency($handle, $data);
            if ( array_key_exists( $handle, $this->__queued_before_register )){
                if ( ! is_null( $this->__queued_before_register[ $handle ] ) )
                    $this->enqueue( $handle . '?' . $this->__queued_before_register[ $handle ] );
                else $this->enqueue( $handle );
                unset( $this->__queued_before_register[ $handle ] );
            }
            return true;
        }//255
        public function add_data( $handle,...$data ):bool{
            if (!isset($this->registered[$handle])) return false;
            return $this->registered[ $handle ]->add_data( $data);//or $args['key'],$args['value']?
        }//287
        public function get_data( $handle, $key ){
            if (!isset($this->registered[$handle])) return false;
            if ( ! isset( $this->registered[$handle]->extra[$key])) return false;
            return $this->registered[ $handle ]->extra[ $key ];
        }//306
        public function remove( $handles ): void{
            foreach ((array)$handles as $handle) unset($this->registered[$handle]);
        }//326
        public function enqueue( $handles ): void{
            foreach ( (array) $handles as $handle ){
                $handle = explode( '?', $handle );
                if(isset($this->registered[$handle[0]]) && !in_array( $handle[0],$this->queue,true)){
                    $this->queue[] = $handle[0];
                    $this->__all_queued_deps = null;
                    if(isset($handle[1])) $this->args[$handle[0]] = $handle[1];
                }elseif (!isset($this->registered[$handle[0]])){
                    $this->__queued_before_register[ $handle[0] ] = null; // $args
                    if(isset($handle[1])) $this->__queued_before_register[ $handle[0] ] = $handle[1];
                }
            }
        }//345
        public function dequeue( $handles ): void{
            foreach ( (array) $handles as $handle ) {
                $handle = explode( '?', $handle );
                $key    = array_search( $handle[0], $this->queue, true );
                if ( false !== $key ) {
                    $this->__all_queued_deps = null;
                    unset( $this->queue[ $key ] ,$this->args[ $handle[0] ]);
                } elseif ( array_key_exists( $handle[0], $this->__queued_before_register ) )
                    unset( $this->__queued_before_register[ $handle[0] ] );
            }
        }//379
        public function query( $handle, $list = 'registered' ){
            switch ( $list ) {
                case 'registered':
                case 'scripts': // Back compat.
                    if ( isset( $this->registered[$handle])) return $this->registered[ $handle ];
                    return false;
                case 'enqueued':
                case 'queue':
                    if ( in_array( $handle, $this->queue, true ) ) return true;
                    return $this->_recurse_deps( $this->queue, $handle );
                case 'to_do':
                case 'to_print': // Back compat.
                    return in_array( $handle, $this->to_do, true );
                case 'done':
                case 'printed': // Back compat.
                    return in_array( $handle, $this->done, true );
            }
            return false;
        }//443
        public function set_group( $handle, $recursion = false, $group ): bool{
            $group = (int) $group;
            $recursion = (bool) $recursion;
            if ( isset( $this->groups[ $handle ] ) && $this->groups[ $handle ] <= $group )
                return $recursion; //todo looks good this way but or it is working?
            $this->groups[ $handle ] = $group;
            return true;
        }//480
        protected function _recurse_deps( $queue, $handle ): bool{
            if ( isset( $this->__all_queued_deps ) ) return isset( $this->__all_queued_deps[ $handle ] );
            $all_deps = array_fill_keys( $queue, true );
            $queues   = [];
            $done     = [];
            while ( $queue ) {
                foreach ( $queue as $queued ) {
                    if ( ! isset( $done[ $queued ] ) && isset( $this->registered[ $queued ] ) ) {
                        $deps = $this->registered[ $queued ]->deps;
                        if ( $deps ) {
                            $all_deps += array_fill_keys( $deps, true );
                            $queues[] = $deps;
                        }
                        $done[ $queued ] = true;
                    }
                }
                $queue = array_pop( $queues );
            }
            $this->__all_queued_deps = $all_deps;
            return isset( $this->__all_queued_deps[ $handle ] );
        }//405
    }
}else die;