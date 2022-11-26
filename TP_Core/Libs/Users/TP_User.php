<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-2-2022
 * Time: 21:48
 */
namespace TP_Core\Libs\Users;
use TP_Core\Libs\DB\TP_Db;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Capabilities\_capability_02;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_02;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Meta\_meta_01;
use TP_Core\Traits\Multisite\Blog\_ms_blog_02;
use TP_Core\Traits\User\_user_02;
use TP_Core\Traits\User\_user_03;
if(ABSPATH){
    class TP_User {
        use _action_01, _load_04, _init_db, _cache_01;
        use _capability_01,_capability_02, _filter_01;
        use _formats_02, _methods_12, _meta_01, _ms_blog_02;
        use _user_02, _user_03;
        public static $tp_db;
        public $nickname;
        public $description;
        public $user_description;
        public $first_name;
        public $user_firstname;
        public $last_name;
        public $user_lastname;
        public $user_login;
        public $user_pass;
        public $user_nicename;
        public $user_email;
        public $user_url;
        public $user_registered;
        public $user_activation_key;
        public $user_status;
        public $user_level;
        public $display_name;
        public $spam;
        public $deleted;
        public $locale;
        public $rich_editing;
        public $syntax_highlighting;
        public $use_ssl;
        //-----
        public $all_caps = [];
        public $caps = [];
        public $cap_key;
        public $data;
        public $filter;
        public $ID = 0;
        public $roles = [];
        public $site_id;
        public function __construct( $id = 0, $name = '', $site_id = '' ) {
            self::$tp_db = $this->_init_db();
            if ($id instanceof self) {
                $this->init( $id->data, $site_id );
                return;
            }
            if (is_object((int)$id )) {
                $this->init( $id, $site_id );
                return;
            }
            if ( $id === (string) $id && ! is_numeric($id ) ) {
                $name = $id;
                $id   = 0;
            }
            if ( $id ) $data = self::get_data_by( 'id', $id );
            else $data = static::get_data_by( 'login', $name );
            if ( $data ) $this->init( $data, $site_id );
            else  $this->data = new \stdClass;
        }//123
        public function init( $data, $site_id = '' ): void{
            if ( ! isset( $data->ID ) ) $data->ID = 0;
            $this->data = $data;
            $this->ID   = (int) $data->ID;
            $this->for_site( $site_id );
        }//170
        public static function get_data_by( $field, $value ) {
            //$tpdb = new self::_init_db();
            //$tpdb = new TP_Db();
            $tpdb = self::$tp_db;
            $user = null;
            // 'ID' is an alias of 'id'.
            if ( 'ID' === $field ) $field = 'id';
            if ( 'id' === $field ) {
                if ( ! is_numeric( $value ) ) return false;
                $value = (int) $value;
                if ( $value < 1 ) return false;
            } else $value = trim( $value );
            if ( ! $value ) return false;
            switch ( $field ) {
                case 'id':
                    $user_id  = $value;
                    $db_field = 'ID';
                    break;
                case 'slug':
                    $user_id  = (new static)->_tp_cache_get( $value, 'user_slugs' );
                    $db_field = 'user_nice_name';
                    break;
                case 'email':
                    $user_id  = (new static)->_tp_cache_get( $value, 'user_email' );
                    $db_field = 'user_email';
                    break;
                case 'login':
                    $value    = (new static)->_sanitize_user( $value );
                    $user_id  = (new static)->_tp_cache_get( $value, 'user_logins' );
                    $db_field = 'user_login';
                    break;
                default:
                    return false;
            }
            if ( false !== $user_id ) {
                $user = (new static)->_tp_cache_get( $user_id, 'users' );
                if ( $user ) return $user;
            }
            if($user && $tpdb instanceof TP_Db){
                $user = $tpdb->get_row(
                    $tpdb->prepare(TP_SELECT . " * FROM $tpdb->users WHERE $db_field = %s LIMIT 1", $value)
                );
            }else return false;
            (new static)->_update_user_caches( $user );
            return $user;
        }
        public function __isset( $key ) {
            if ( isset( $this->data->$key ) )return true;
            return $this->_metadata_exists( 'user', $this->ID, $key );
        }
        public function __get( $key ) {
            if ( isset( $this->data->$key ) ) $value = $this->data->$key;
            else $value = $this->_get_user_meta( $this->ID, $key, true );
            if ( $this->filter )
                $value = $this->_sanitize_user_field( $key, $value, $this->ID, $this->filter );
            return $value;
        }
        public function __set( $key, $value ) {
            $this->data->$key = $value;
        }
        public function __unset( $key ) {
            if ( isset( $this->data->$key ) ) unset( $this->data->$key );
        }
        public function exists(): bool{
            return ! empty( $this->ID );
        }
        public function get( $key ){
            return $this->__get($key);
        }
        public function has_property( $key ): bool{
            return $this->__isset( $key );
        }
        public function to_array(): array{
            return get_object_vars( $this->data );
        }
        public function __call( $name, $arguments ) {
            if ( '_init_caps' === $name )
                return $this->_init_caps( ...$arguments );
            return false;
        }
        protected function _init_caps( $cap_key = '' ){
            if ( empty( $cap_key ) )
                $this->cap_key = $this->_init_db()->get_blog_prefix( $this->site_id ) . 'capabilities';
            else $this->cap_key = $cap_key;
            $this->caps = $this->__get_caps_data();
            $this->get_role_caps();
            return $this;
        }
        public function get_role_caps(): array{
            $switch_site = false;
            if ( $this->_is_multisite() && $this->_get_current_blog_id() !== $this->site_id ) {
                $switch_site = true;
                $this->_switch_to_blog( $this->site_id );
            }
            $tp_roles = $this->_init_roles();
            // Filter out caps that are not role names and assign to $this->roles.
            if ( is_array( $this->caps ) )
                $this->roles = array_filter( array_keys( $this->caps ), array( $tp_roles, 'is_role' ) );
            // Build $all caps from role caps, overlay user's $caps.
            $this->all_caps = array();
            foreach ( (array) $this->roles as $role ) {
                $the_role      = $tp_roles->get_role( $role );//todo
                $this->all_caps = array_merge( (array) $this->all_caps, (array) $the_role->capabilities );
            }
            $this->all_caps = array_merge( (array) $this->all_caps, (array) $this->caps );
            if ( $switch_site ) $this->_restore_current_blog();
            return $this->all_caps;
        }
        public function add_role( $role ): void{
            if ( empty( $role ) ) return;
            $this->caps[ $role ] = true;
            $this->_update_user_meta( $this->ID, $this->cap_key, $this->caps );
            $this->get_role_caps();
            $this->update_user_level_from_caps();
            $this->_do_action( 'add_user_role', $this->ID, $role );
        }
        public function remove_role( $role ): void{
            if ( ! in_array( $role, $this->roles, true ) )
                return;
            unset( $this->caps[ $role ] );
            $this->_update_user_meta( $this->ID, $this->cap_key, $this->caps );
            $this->get_role_caps();
            $this->update_user_level_from_caps();
            $this->_do_action( 'remove_user_role', $this->ID, $role );
        }
        public function set_role( $role ): void{
            if (1 === count($this->roles) && current($this->roles) === $role)
                return;
            foreach ((array)$this->roles as $old_role)
                unset($this->caps[$old_role]);
            $old_roles = $this->roles;
            if (!empty($role)){
                $this->caps[$role] = true;
                $this->roles = array($role => true);
            }else $this->roles = false;
            $this->_update_user_meta( $this->ID, $this->cap_key, $this->caps );
            $this->get_role_caps();
            $this->update_user_level_from_caps();
            $this->_do_action( 'set_user_role', $this->ID, $role, $old_roles );
        }
        public function level_reduction( $max, $item ) {
            if ( preg_match( '/^level_(10|\d)$/i', $item, $matches ) ) {
                $level = (int) $matches[1];
                return max( $max, $level );
            } else return $max;
        }
        public function update_user_level_from_caps(): void {
            $this->user_level = array_reduce( array_keys( $this->all_caps ), array( $this, 'level_reduction' ), 0 );
            $this->_update_user_meta( $this->ID, $this->_init_db()->get_blog_prefix() . 'user_level', $this->user_level );
        }//674
        public function add_cap( $cap, $grant = true ): void{
            $this->caps[ $cap ] = $grant;
            $this->_update_user_meta( $this->ID, $this->cap_key, $this->caps );
            $this->get_role_caps();
            $this->update_user_level_from_caps();
        }//684
        public function remove_cap( $cap ): void{
            if ( ! isset( $this->caps[ $cap ] ) ) {
                return;
            }
            unset( $this->caps[ $cap ] );
            $this->_update_user_meta( $this->ID, $this->cap_key, $this->caps );
            $this->get_role_caps();
            $this->update_user_level_from_caps();
        }//698
        public function remove_all_caps(): void{
            $this->caps = array();
            $this->_delete_user_meta( $this->ID, $this->cap_key );
            $this->_delete_user_meta( $this->ID, $this->_init_db()->get_blog_prefix() . 'user_level' );
            $this->get_role_caps();
        }//715
        public function has_cap( $cap, ...$args ): bool{
            $caps = $this->_map_meta_cap( $cap, $this->ID, ...$args );
            if ( $this->_is_multisite() && $this->_is_super_admin( $this->ID ) ) {
                if ( in_array( 'do_not_allow', $caps, true ) ) return false;
                return true;
            }
            $args = array_merge( array( $cap, $this->ID ), $args );
            $capabilities = $this->_apply_filters( 'user_has_cap', $this->all_caps, $caps, $args, $this );
            $capabilities['exist'] = true;
            unset( $capabilities['do_not_allow'] );
            foreach ( (array) $caps as $empty_cap ) {
                if ( empty( $capabilities[ $empty_cap ] ) ) return false;
            }
            return true;
        }//750
        public function translate_level_to_cap( $level ): string{
            return 'level_' . $level;
        }//815
        public function for_site( $site_id = '' ): void{
            if ( ! empty( $site_id ) )
                $this->site_id = $this->_abs_int( $site_id );
            else $this->site_id = $this->_get_current_blog_id();
            $this->cap_key = $this->_init_db()->get_blog_prefix( $this->site_id ) . 'capabilities';
            $this->caps = $this->__get_caps_data();
            $this->get_role_caps();
        }//824
        public function get_site_id() {
            return $this->site_id;
        }//865
        private function __get_caps_data() {
            $caps = $this->_get_user_meta( $this->ID, $this->cap_key, true );
            if ( ! is_array( $caps ) ) return array();
            return $caps;
        }
    }
}else die;