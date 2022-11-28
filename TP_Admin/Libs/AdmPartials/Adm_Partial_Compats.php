<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 31-10-2022
 * Time: 20:33
 */
namespace TP_Admin\Libs\AdmPartials;
use TP_Admin\Libs\Adm_Screen;
if(ABSPATH){
    class Adm_Partial_Compats extends Adm_Partials {
        public $adm_segment_blocks;
        public $adm_segment_screen;
        /**
         * Adm_Segment_List_Compat constructor.
         * @param Adm_Screen|string $screen
         * @param array $columns
         */
        public function __construct(Adm_Screen $screen, $columns = [] ) {
            parent::__construct();
            if(is_string($screen)){ $screen = $this->_convert_to_screen($screen);}
            $this->adm_segment_screen = $screen;
            if ( ! empty( $columns ) ) {
                $this->adm_segment_blocks = $columns;
                $this->_add_filter( 'manage_' . $screen->id . '_columns',[$this, 'get_columns'], 0 );
            }
        }
        protected function _get_block_info():array {
            $columns  = $this->_get_column_headers( $this->adm_segment_screen );
            $hidden   = $this->_get_hidden_columns( $this->adm_segment_screen );
            $sortable = [];
            $primary  = $this->_get_default_primary_name();
            return array( $columns, $hidden, $sortable, $primary );
        }
        public function get_blocks() {
            return $this->adm_segment_blocks;
        }
    }
}else{die;}

