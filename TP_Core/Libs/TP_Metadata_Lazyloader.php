<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 11-5-2022
 * Time: 17:57
 */
namespace TP_Core\Libs;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Meta\_meta_02;
use TP_Core\Traits\Taxonomy\_taxonomy_03;
if(ABSPATH){
    class TP_Metadata_Lazyloader{
        use _action_01;
        use _filter_01;
        use _I10n_01;
        use _taxonomy_03;
        use _meta_02;
        protected $_pending_objects;
        protected $_settings = [];
        public function __construct() {
            $this->_settings = array(
                'term'    => ['filter'   => 'get_term_metadata','callback' => [$this,'lazyload_term_meta'],],
                'comment' => ['filter'   => 'get_comment_metadata','callback' => [$this, 'lazyload_comment_meta'],],
            );
        }//53
        public function queue_objects( $object_type, $object_ids ) {
            if ( ! isset( $this->_settings[ $object_type ] ) )
                return new TP_Error( 'invalid_object_type', $this->__( 'Invalid object type.' ) );
            $type_settings = $this->_settings[ $object_type ];
            if ( ! isset( $this->_pending_objects[ $object_type ] ) )
                $this->_pending_objects[ $object_type ] = [];
            foreach ( $object_ids as $object_id ) {
                if ( ! isset( $this->_pending_objects[ $object_type ][ $object_id ] ) )
                    $this->_pending_objects[ $object_type ][ $object_id ] = 1;
            }
            $this->_add_filter( $type_settings['filter'], $type_settings['callback'] );
            $this->_do_action( 'metadata_lazyloader_queued_objects', $object_ids, $object_type, $this );
            return true;
        }//75
        public function reset_queue( $object_type ) {
            if ( ! isset( $this->_settings[ $object_type ] ) )
                return new TP_Error( 'invalid_object_type', $this->__( 'Invalid object type.' ) );
            $type_settings = $this->_settings[ $object_type ];
            $this->_pending_objects[ $object_type ] = array();
            $this->_remove_filter( $type_settings['filter'], $type_settings['callback'] );
            return true;
        }//115
        public function lazyload_term_meta( $check ) {
            if ( ! empty( $this->_pending_objects['term'] ) ) {
                $this->_update_term_meta_cache( array_keys( $this->_pending_objects['term'] ) );
                $this->reset_queue( 'term' );
            }
            return $check;
        }//138
        public function lazyload_comment_meta( $check ) {
            if ( ! empty( $this->_pending_objects['comment'] ) ) {
                $this->_update_meta_cache( 'comment', array_keys( $this->_pending_objects['comment'] ) );
                $this->reset_queue( 'comment' );
            }
            return $check;
        }
    }
}else die;

