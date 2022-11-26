<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 15-4-2022
 * Time: 20:02
 */
namespace TP_Core\Libs\Users;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Methods\_methods_20;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Load\_load_04;
use TP_Core\Traits\Multisite\Blog\_ms_blog_01;
use TP_Core\Traits\Options\_option_01;
if(ABSPATH){
    class TP_Roles{
        use _init_db, _action_01, _methods_12, _methods_20, _load_04;
        use _ms_blog_01, _option_01;
        private $__role_key;
        private $__tp_user_roles;
        protected $_site_id = 0;
        public $roles;
        public $role_names = array();
        public $role_objects = array();
        public $use_db = true;
        public function __construct( $site_id = null ) {
            $this->use_db = empty( $this->__tp_user_roles );
            $this->for_site( $site_id );
        }
        public function add_role( $role, $display_name,array $capabilities) {
            if ( empty( $role ) || isset( $this->roles[ $role ] ) )
                return null;
            $this->roles[ $role ] = array('name'=> $display_name,'capabilities' => $capabilities,);
            if ( $this->use_db ) $this->_update_option( $this->__role_key, $this->roles );
            $this->role_objects[ $role ] = new TP_Roles( $role, $capabilities );
            $this->role_names[ $role ]   = $display_name;
            return $this->role_objects[ $role ];
        }
        public function remove_role( $role ): void{
            if ( ! isset( $this->role_objects[ $role ] ) ) return;
            unset( $this->role_objects[ $role ], $this->role_names[ $role ], $this->roles[ $role ] );
            if ( $this->use_db ) $this->_update_option( $this->__role_key, $this->roles );
            if ( $this->_get_option( 'default_role' ) === $role )
                $this->_update_option( 'default_role', 'subscriber' );
        }
        public function add_capability( $role, $cap, $grant = true ): void{
            if ( ! isset( $this->roles[ $role ] ) ) return;
            $this->roles[ $role ]['capabilities'][ $cap ] = $grant;
            if ( $this->use_db ) $this->_update_option( $this->__role_key, $this->roles );
        }
        public function remove_capability( $role, $cap ): void{
            if ( ! isset( $this->roles[ $role ] ) ) return;
            unset( $this->roles[ $role ]['capabilities'][ $cap ] );
            if ( $this->use_db ) $this->_update_option( $this->__role_key, $this->roles );
        }//227
        public function get_role( $role ) {
            if ( isset( $this->role_objects[ $role ] ) )
                return $this->role_objects[ $role ];
            else return null;
        }//246
        public function get_names(): array{
            return $this->role_names;
        }//261
        public function is_role( $role ): bool{
            return isset( $this->role_names[ $role ] );
        }//273
        public function init_roles(): void{
            if ( empty( $this->roles ) ) return;
            $this->role_objects = array();
            $this->role_names   = array();
            foreach ( array_keys( $this->roles ) as $role ) {
                $this->role_objects[ $role ] = new TP_Roles( $role, $this->roles[ $role ]['capabilities'] );
                $this->role_names[ $role ]   = $this->roles[ $role ]['name'];
            }
            $this->_do_action( 'tp_roles_init', $this );
        }//282
        public function for_site( $site_id = null ): void{
            if ( ! empty( $site_id ) )
                $this->_site_id = $this->_abs_int( $site_id );
            else $this->_site_id = $this->_get_current_blog_id();
            $this->__role_key = $this->_init_db()->get_blog_prefix( $this->_site_id ) . 'user_roles';
            if ( ! empty( $this->roles ) && ! $this->use_db ) return;
            $this->roles = $this->_get_roles_data();
            $this->init_roles();
        }
        public function get_site_id(): int{
            return $this->_site_id;
        }
        protected function _get_roles_data(){
            if (!empty($this->__tp_user_roles))
                return $this->__tp_user_roles;
            if ( $this->_is_multisite() && $this->_get_current_blog_id() !== $this->_site_id ) {
                $this->_remove_action( 'switch_blog', 'wp_switch_roles_and_user', 1 );
                $roles = $this->_get_blog_option( $this->_site_id, $this->__role_key, array() );
               $this->_add_action( 'switch_blog', 'tp_switch_roles_and_user', 1, 2 );
               return $roles;
            }
            return $this->_get_option( $this->__role_key, array() );
        }
    }
}else die;