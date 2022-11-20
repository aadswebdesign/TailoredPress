<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-7-2022
 * Time: 07:34
 */
namespace TP_Core\Libs\Customs\Customizers;
use TP_Core\Libs\Customs\TP_Customize_Manager;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Misc\tp_script;

if(ABSPATH){
    final class TP_Customize_Selective_Refresh{
        use _action_01,_filter_01,tp_script;
        protected $_partials = [];
        protected $_triggered_errors = [];
        protected $_current_partial_id;
        public const RENDER_QUERY_VAR = 'tp_customize_render_partials';
        public $manager;
        public function __construct(TP_Customize_Manager $manager){
            $this->manager = $manager;
            $this->_add_action( 'customize_preview_init',[$this, 'init_preview']);
        }
        public function partials():array {
            return $this->_partials;
        }
        public function add_partial( $id,array ...$args):TP_Customize_Partial {
            if ( $id instanceof TP_Customize_Partial ) {
                $partial = $id;
            }else{
                $class = 'TP_Customize_Partial';
                $args = $this->_apply_filters( 'customize_dynamic_partial_args', $args, $id );
                $class = $this->_apply_filters( 'customize_dynamic_partial_class', $class, $id, $args );
                $partial = new $class( $this, $id, $args );
            }
            $this->_partials[ $partial->id ] = $partial;
            return $partial;
        }
        public function get_partial( $id ) {
            return $this->_partials[$id] ?? null;
        }
        public function remove_partial( $id ):void {
            unset( $this->_partials[ $id ] );
        }
        public function init_preview():void {
            $this->_add_action( 'template_redirect',[$this, 'handle_render_partials_request']);
            $this->_add_action( 'tp_enqueue_assets',[$this, 'enqueue_preview_scripts']);
        }
        public function enqueue_preview_scripts():void {
            $this->tp_enqueue_script( 'customize-selective-refresh' );
            $this->_add_action( 'tp_footer', array( $this, 'export_preview_data' ), 1000 );
        }
        public function get_export_preview_data():string{return 'todo';}//165
        public function export_preview_data():void{}//165
        public function add_dynamic_partials( $partial_ids ):string{return 'todo';}//208
        public function is_render_partials_request():bool {
            return ! empty( $_POST[ self::RENDER_QUERY_VAR ] );
        }//270
        public function handle_error( $err_no, $err_str, $err_file = null, $err_line = null ):bool{return true;}//287
        public function handle_render_partials_request():void{}//303
    }
}else die;