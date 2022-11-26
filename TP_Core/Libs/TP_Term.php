<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-4-2022
 * Time: 13:33
 */
namespace TP_Core\Libs;
//use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Inits\_init_user;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Taxonomy\_taxonomy_01;
use TP_Core\Traits\Taxonomy\_taxonomy_04;
if(ABSPATH){
    class TP_Term{
        use _cache_01;
        use _init_db;
        use _init_error;
        use _init_user;
        use _I10n_01;
        use _taxonomy_01;
        use _taxonomy_04;
        public $count = 0;
        public $description = '';
        public $filter = 'raw';
        public $name = '';
        public $parent = 0;
        public $slug = '';
        public $taxonomy = '';
        public $term_group = '';
        public $term_id;
        public $term_taxonomy_id = 0;
        /**
         * @description Retrieve TP_Term instance.
         * @param $term_id
         * @param null $taxonomy
         * @return bool|TP_Error|TP_Term
         */
        public static function get_instance( $term_id, $taxonomy = null ){
            $term_id = (int) $term_id;
            $tpdb = (new static('term'))->_init_db();
            if ( ! $term_id ) return false;
            $_term = (new static('term'))->_tp_cache_get( $term_id, 'terms' );
            if ( ! $_term || ( $taxonomy && $taxonomy !== $_term->taxonomy ) ) {
                $_term = false;
                $terms = (new static('term'))->$tpdb->get_results( (new static('term'))->$tpdb->prepare( TP_SELECT ." t.*, tt.* FROM ". $tpdb->terms ." AS t INNER JOIN " . $tpdb->term_taxonomy . " AS tt ON t.term_id = tt.term_id WHERE t.term_id = %d", $term_id ) );
                if ( ! $terms ) return false;
                if ( $taxonomy ) {
                    foreach ( $terms as $match ) {
                        if ( $taxonomy === $match->taxonomy ) {
                            $_term = $match;
                            break;
                        }
                    }
                }elseif ( 1 === count( $terms ) ) $_term = reset( $terms );
                else{
                    foreach ( $terms as $t ) {
                        if ( ! (new static('term'))->_taxonomy_exists($t->taxonomy)) continue;
                        if ( $_term ) return new TP_Error( 'ambiguous_term_id', (new static('term'))->__( 'Term ID is shared between multiple taxonomies' ), $term_id );
                        $_term = $t;
                    }
                }
                if ( ! $_term ) return false;
                if ( ! (new static('term'))->_taxonomy_exists( $_term->taxonomy ) )
                    return new TP_Error( 'invalid_taxonomy', (new static('term'))->__( 'Invalid taxonomy.' ) );
                $_term = (new static('term'))->_sanitize_term( $_term, $_term->taxonomy, 'raw' );
                if ( 1 === count( $terms ) ) (new static('term'))->_tp_cache_add( $term_id, $_term, 'terms' );
            }
            $term_obj = new self( $_term );
            $term_obj->filter( $term_obj->filter );
            return $term_obj;
        }//115
        public function __construct( $term ){
            foreach ( get_object_vars( $term ) as $key => $value )
                $this->$key = $value;
        }//196
        /**
         * @description Sanitizes term fields, according to the filter type provided.
         * @param $filter
         */
        public function filter( $filter ): void{
            $this->_sanitize_term( $this, $this->taxonomy, $filter );
        }//209
        /**
         * @description Converts an object to array.
         * @return array
         */
        public function to_array():array{
            return get_object_vars( $this );
        }//220
        /**
         * @description Getter.
         * @param $key
         * @return null|string
         */
        public function __get( $key ){
            $return = null;
            if ($key === 'data') {
                $data = new \stdClass();
                $columns = ['term_id', 'name', 'slug', 'term_group', 'term_taxonomy_id', 'taxonomy', 'description', 'parent', 'count'];
                foreach ($columns as $column) $data->{$column} = $this->{$column} ?? null;
                $return = $this->_sanitize_term($data, $data->taxonomy, 'raw');
            }
            return $return;
        }//232
    }
}else die;