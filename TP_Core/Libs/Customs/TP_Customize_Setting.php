<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-7-2022
 * Time: 20:20
 */
namespace TP_Core\Libs\Customs;
use TP_Core\Libs\TP_Error;
if(ABSPATH){
    class TP_Customize_Setting extends Customize_Base{
        protected $_id_data = [];
        protected $_is_previewed = false;
        protected static $_aggregated_multi_dimensionals = [];
        protected $_is_multidimensional_aggregated = false;
        protected $_previewed_blog_id;
        protected $_original_value;
        public $type = 'theme_mod';
        public $capability = 'edit_theme_options';
        public $theme_supports = '';
        public $default = '';
        public $transport = 'refresh';
        public $validate_callback = '';
        public $sanitize_callback = '';
        public $sanitize_js_callback = '';
        public $dirty = false;
        public function __construct( $manager, $id,array ...$args){}//177
        final public function id_data():array {
            return $this->_id_data;
        }//231
        protected function _aggregate_multidimensional():void{
            $id_base = $this->_id_data['base'];
            if ( ! isset( self::$_aggregated_multi_dimensionals[ $this->type ] ) )
                self::$_aggregated_multi_dimensionals[ $this->type ] = [];
            if ( ! isset( self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ] ) ) {
                self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ] = array(
                    'previewed_instances'       => [], // Calling preview() will add the $setting to the array.
                    'preview_applied_instances' => [], // Flags for which settings have had their values applied.
                    'root_value'                => $this->_get_root_value( [] ), // Root value for initial state, manipulated by preview and update calls.
                );
            }
            if ( ! empty( $this->_id_data['keys'] ) ) {
                $this->_add_action( "customize_post_value_set_{$this->id}", array( $this, '_clear_aggregated_multidimensional_preview_applied_flag' ), 9 );
                $this->_is_multidimensional_aggregated = true;
            }
        }//243
        public static function reset_aggregated_multi_dimensionals():void {
            self::$_aggregated_multi_dimensionals = [];
        }//271
        public function is_current_blog_previewed():bool {
            if ( ! isset( $this->_previewed_blog_id ) ) {
                return false;
            }
            return ( $this->_get_current_blog_id() === $this->_previewed_blog_id );
        }//290
        public function preview():bool{
            if ( ! isset( $this->_previewed_blog_id ) )
                $this->_previewed_blog_id = $this->_get_current_blog_id();
            if ( $this->_is_previewed ) return true;
            $id_base                 = $this->_id_data['base'];
            $is_multidimensional     = ! empty( $this->_id_data['keys'] );
            $multidimensional_filter = array( $this, '_multidimensional_preview_filter' );
            $undefined     = new \stdClass();
            $needs_preview = ( $undefined !== $this->post_value( $undefined ) );
            $value         = null;
            if ( ! $needs_preview ) {
                if ( $this->_is_multidimensional_aggregated ) {
                    $root  = self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ]['root_value'];
                    $value = $this->_multidimensional_get( $root, $this->_id_data['keys'], $undefined );
                } else {
                    $default       = $this->default;
                    $this->default = $undefined; // Temporarily set default to undefined so we can detect if existing value is set.
                    $value         = $this->value();
                    $this->default = $default;
                }
                $needs_preview = ( $undefined === $value ); // Because the default needs to be supplied.
            }
            if ( ! $needs_preview ) {
                if ( ! $this->_has_action( "customize_post_value_set_{$this->id}", array( $this, 'preview' ) ) )
                    $this->_add_action( "customize_post_value_set_{$this->id}", array( $this, 'preview' ) );
                return false;
            }
            switch ( $this->type ) {
                case 'theme_mod':
                    if ( ! $is_multidimensional )
                        $this->_add_filter( "theme_mod_{$id_base}", array( $this, '_preview_filter' ) );
                    else {
                        if ( empty( self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ]['previewed_instances'] ) )
                            $this->_add_filter( "theme_mod_{$id_base}", $multidimensional_filter );
                        self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ]['previewed_instances'][ $this->id ] = $this;
                    }
                    break;
                case 'option':
                    if ( ! $is_multidimensional )
                        $this->_add_filter( "pre_option_{$id_base}", array( $this, '_preview_filter' ) );
                    else {
                        if ( empty( self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ]['previewed_instances'] ) ) {
                            $this->_add_filter( "option_{$id_base}", $multidimensional_filter );
                            $this->_add_filter( "default_option_{$id_base}", $multidimensional_filter );
                        }
                        self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ]['previewed_instances'][ $this->id ] = $this;
                    }
                    break;
                default:
                    $this->_do_action( "customize_preview_{$this->id}", $this );
                    $this->_do_action( "customize_preview_{$this->type}", $this );
            }
            $this->_is_previewed = true;
            return true;
        }//318
        final public function _clear_aggregated_multidimensional_preview_applied_flag():void {
            unset( self::$_aggregated_multi_dimensionals[ $this->type ][ $this->_id_data['base'] ]['preview_applied_instances'][ $this->id ] );
        }//431
        public function _preview_filter( $original ){
            if ( ! $this->is_current_blog_previewed() ) return $original;
            $undefined  = new \stdClass(); // Symbol hack.
            $post_value = $this->post_value( $undefined );
            if ( $undefined !== $post_value ) $value = $post_value;
            else $value = $this->default;
            return $value;
        }//447
        final protected function _multidimensional_preview_filter( $original ){
            if ( ! $this->is_current_blog_previewed() ) return $original;
            $id_base = $this->_id_data['base'];
            if ( empty( self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ]['previewed_instances'] ) )
                return $original;
            foreach ( self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ]['previewed_instances'] as $previewed_setting ) {
                if ( ! empty( self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ]['preview_applied_instances'][ $previewed_setting->id ] ) )
                    continue;
                //if( $previewed_setting instanceof TP_Customize_Manager );
                $previewed_setting = $this->_init_customize_manager($original);
                $value = $previewed_setting->post_value( $previewed_setting->default );
                $root  = self::$_aggregated_multi_dimensionals[ $previewed_setting->type ][ $id_base ]['root_value'];
                if($previewed_setting instanceof self)
                $root  = $previewed_setting->_multidimensional_replace( $root, $previewed_setting->id_data['keys'], $value );
                self::$_aggregated_multi_dimensionals[ $previewed_setting->type ][ $id_base ]['root_value'] = $root;
                self::$_aggregated_multi_dimensionals[ $previewed_setting->type ][ $id_base ]['preview_applied_instances'][ $previewed_setting->id ] = true;
            }
            return self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ]['root_value'];
        }//479
        final public function save():bool{
            $value = $this->post_value();
            if (! isset( $value ) || ! $this->check_capabilities()) return false;
            $id_base = $this->_id_data['base'];
            $this->_do_action( "customize_save_{$id_base}", $this );
            $this->_update( $value );
            return true;
        }//519
        final public function post_value( $default = null ) {
            if( $this->manager instanceof TP_Customize_Manager){}
            return $this->manager->post_value( $this, $default );
        }//553
        public function sanitize( $value ) {
            return $this->_apply_filters( "customize_sanitize_{$this->id}", $value, $this );
        }//565
        public function validate( $value ) {
            if ( $this->_init_error( $value ) ) return $value;
            if ( is_null( $value ) ) return new TP_Error( 'invalid_value', $this->__( 'Invalid value.' ) );
            $validity = new TP_Error();
            $validity = $this->_apply_filters( "customize_validate_{$this->id}", $validity, $value, $this );
            if ( $this->_init_error( $validity ) && ! $validity->has_errors() ) $validity = true;
            return $validity;
        }//588
        protected function _get_root_value( $default = null ) {
            $id_base = $this->_id_data['base'];
            if ( 'option' === $this->type ) return $this->_get_option( $id_base, $default );
            elseif ( 'theme_mod' === $this->type ) return $this->_get_theme_mod( $id_base, $default );
            else return $default;
        }//627
        protected function _set_root_value( $value ) {
            $id_base = $this->_id_data['base'];
            if ( 'option' === $this->type ) {
                $autoload = true;
                if ( isset( self::$_aggregated_multi_dimensionals[ $this->type ][ $this->_id_data['base'] ]['autoload'] ) )
                    $autoload = self::$_aggregated_multi_dimensionals[ $this->type ][ $this->_id_data['base'] ]['autoload'];
                return $this->_update_option( $id_base, $value, $autoload );
            }
            if ( 'theme_mod' === $this->type ) {
                $this->_set_theme_mod( $id_base, $value );
                return true;
            } else return false;
        }//651
        protected function _update( $value ) {
            $id_base = $this->_id_data['base'];
            if ( 'option' === $this->type || 'theme_mod' === $this->type ) {
                if ( ! $this->_is_multidimensional_aggregated ) {
                    return $this->_set_root_value( $value );
                }
                $root = self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ]['root_value'];
                $root = $this->_multidimensional_replace( $root, $this->_id_data['keys'], $value );
                self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ]['root_value'] = $root;
                return $this->_set_root_value( $root );
            }
            $this->_do_action( "customize_update_{$this->type}", $value, $this );
            return $this->_has_action( "customize_update_{$this->type}" );
        }//680
        public function value() {
            $id_base      = $this->_id_data['base'];
            $is_core_type = ( 'option' === $this->type || 'theme_mod' === $this->type );
            if ( ! $is_core_type && ! $this->_is_multidimensional_aggregated ) {
                if ( $this->_is_previewed ) {
                    $value = $this->post_value( null );
                    if ( null !== $value ) return $value;
                }
                $value = $this->_get_root_value( $this->default );
                $value = $this->_apply_filters( "customize_value_{$id_base}", $value, $this );
            } elseif ( $this->_is_multidimensional_aggregated ) {
                $root_value = self::$_aggregated_multi_dimensionals[ $this->type ][ $id_base ]['root_value'];
                $value      = $this->_multidimensional_get( $root_value, $this->_id_data['keys'], $this->default );
                if ( $this->_is_previewed ) $value = $this->post_value( $value );
            } else $value = $this->_get_root_value( $this->default );
            return $value;
        }//736
        public function js_value() {
            $value = $this->_apply_filters( "customize_sanitize_js_{$this->id}", $this->value(), $this );
            if ( is_string( $value ) )
                return html_entity_decode( $value, ENT_QUOTES, 'UTF-8' );
            return $value;
        }//808
        public function json():array {
            return ['value'=> $this->js_value(),'transport' => $this->transport,
                'dirty' => $this->dirty,'type' => $this->type,];
        }//817
        final public function check_capabilities():bool {
            if ( $this->capability && ! $this->_current_user_can( $this->capability ) )
                return false;
            if ( $this->theme_supports && ! $this->_current_theme_supports( ... (array) $this->theme_supports ) )
                return false;
            return true;
        }
        final protected function _multidimensional( &$root, $keys, $create = false ){
            if ( $create && empty( $root ) ) $root = [];
            if ( ! isset( $root ) || empty( $keys ) ) return false;
            $last = array_pop( $keys );
            $node = &$root;
            foreach ( $keys as $key ) {
                if ( $create && ! isset( $node[ $key ] ) ) $node[ $key ] = [];
                if ( ! is_array( $node ) || ! isset( $node[ $key ] ) ) return false;
                $node = &$node[ $key ];
            }
            if ( $create ) {
                if ( ! is_array( $node ) ) $node = [];
                if ( ! isset( $node[ $last ] ) ) $node[ $last ] = array();
            }
            if ( ! isset( $node[ $last ] ) ) return false;
            return ['root' => &$root,'node' => &$node,'key' => $last,];
        }//855
        final protected function _multidimensional_replace( $root, $keys, $value ){
            if ( ! isset( $value ) ) return $root;
            elseif ( empty( $keys ) ) return $value;
            $result = $this->_multidimensional( $root, $keys, true );
            if ( isset( $result ) ) $result['node'][ $result['key'] ] = $value;
            return $root;
        }//910
        final protected function _multidimensional_get( $root, $keys, $default = null ){
            if ( empty( $keys ) ) return $root ?? $default;
            $result = $this->_multidimensional( $root, $keys );
            return isset( $result ) ? $result['node'][ $result['key'] ] : $default;
        }//936
        final protected function multidimensional_isset( $root, $keys ):bool {
            $result = $this->_multidimensional_get( $root, $keys );
            return isset( $result );
        }//955
    }
}else die;