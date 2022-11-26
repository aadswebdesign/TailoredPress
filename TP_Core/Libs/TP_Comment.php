<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 9-5-2022
 * Time: 00:09
 */
namespace TP_Core\Libs;
use TP_Core\Libs\DB\TP_Db;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Methods\_methods_10;
use TP_Core\Traits\Comment\_comment_01;
if(ABSPATH){
    class TP_Comment{
        use _cache_01;
        use _post_01;
        use _methods_10;
        use _init_db;
        use _comment_01;
        public static $tpdb;
        public $comment_ID;
        public $comment_post_ID = 0;
        public $comment_author = '';
        public $comment_author_email = '';
        public $comment_author_url = '';
        public $comment_author_IP = '';
        public $comment_date = '0000-00-00 00:00:00';
        public $comment_date_gmt = '0000-00-00 00:00:00';
        public $comment_content;
        public $comment_karma = 0;
        public $comment_approved = '1';
        public $comment_agent = '';
        public $comment_type = 'comment';
        public $comment_parent = 0;
        public $user_id = 0;
        protected $_children;
        protected $_populated_children = false;
        protected $_post_fields = array( 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count' );
        public static function get_instance( $id ) {
            $_tpdb = (new static($id))->_init_db();
            $comment_id = (int) $id;
            if ( ! $comment_id ) return false;
            $_comment = (new static($id))->_tp_cache_get( $comment_id, 'comment' );
            if ( ! $_comment && ($_tpdb instanceof TP_Db)) {
                $_comment = $_tpdb->get_row( $_tpdb->prepare( TP_SELECT ." * FROM $_tpdb->comments WHERE comment_ID = %d LIMIT 1", $comment_id ) );
                if ( ! $_comment ) return false;
                (new static($id))->_tp_cache_add( $_comment->comment_ID, $_comment, 'comment' );
            }
            return new self( $_comment );
        }//182
        public function __construct( $comment ) {
            foreach ((array) get_object_vars((object) $comment ) as $key => $value )
                $this->$key = $value;
        }//214
        public function to_array(): array{
            return get_object_vars( $this );
        }//229
        public function get_children( $args = []){
            $defaults = ['format' => 'tree','status' => 'all','hierarchical' => 'threaded','orderby' => '',];
            $_args           = $this->_tp_parse_args( $args, $defaults );
            $_args['parent'] = $this->comment_ID;
            if ( is_null( $this->_children ) ) {
                if ( $this->_populated_children ) $this->_children = array();
                else  $this->_children = $this->_get_comments( $_args );
            }
            if ( 'flat' === $_args['format'] ) {
                $children = [];
                $_child = [];
                $_get_children = [];
                foreach ( $this->_children as $child ) {
                    $child_args           = $_args;
                    $child_args['format'] = 'flat';
                    $_child[] = $child;
                    $_get_children[] = $child->get_children( $child_args );
                    unset( $child_args['parent'] );
                }
                $children = array_merge( $children, array( $_child ),$_get_children );
            } else $children = $this->_children;
            return $children;
        }//267
        public function add_child( TP_Comment $child ): void{
            $this->_children[ $child->comment_ID ] = $child;
        }//312
        public function get_child( $child_id ): bool{
            if ( isset( $this->_children[ $child_id ] ) )
                return $this->_children[ $child_id ];
            return false;
        }//324
        public function populated_children( $set ): void{
            $this->_populated_children = (bool) $set;
        }//342
        public function __isset( $name ) {
            if ((0 !== (int) $this->comment_post_ID) && in_array( $name, $this->_post_fields, true )) {
                $post = $this->_get_post( $this->comment_post_ID );
                return property_exists( $post, $name );
            }
            return false;
        }//356
        public function __get( $name ) {
            if ( in_array( $name, $this->_post_fields, true ) ) {
                return $this->_get_post( $this->comment_post_ID )->$name;
            }
            return false;
        }//373
    }
}else die;