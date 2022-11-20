<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-7-2022
 * Time: 20:20
 */
namespace TP_Core\Libs\Customs;
if(ABSPATH){
    class TP_Customize_Nav_Menus extends Customize_Base{
        protected $_original_nav_menu_locations;
        public $preview_nav_menu_instance_args = [];
        public function __construct( $manager ){}//44
        public function filter_nonces( $nonces ) {
            $nonces['customize-menus'] = $this->_tp_create_nonce( 'customize-menus' );
            return $nonces;
        }
        public function async_load_available_items(){}//91
        public function load_available_items_query( $type = 'post_type', $object = 'page', $page = 0 ){}//140
        public function async_search_available_items(){}//310
        public function search_available_items_query(array ...$args){}//351
        public function enqueue_scripts(){}//483
        public function filter_dynamic_setting_args( $setting_args, $setting_id ){}//585
        public function filter_dynamic_setting_class( $setting_class, $setting_id, $setting_args ){}//610
        public function customize_register(){}//626
        public function intval_base10( $value ){
            return intval( $value, 10 );
        }//876
        public function available_item_types(){}//888
        public function insert_auto_draft_post( $postarr ){}//946
        public function async_insert_auto_draft_post(){}//990
        public function print_templates(){}//1065
        public function available_items_template(){}//1141
        protected function _print_post_type_container( $available_item_type ){}//1204
        protected function _print_custom_links_available_menu_item(){}//1246
        public function customize_dynamic_partial_args( $partial_args, $partial_id ){}//1298
        public function customize_preview_init(){}//1324
        public function make_auto_draft_status_previewable(){}//1339
        public function sanitize_nav_menus_created_posts( $value ){}//1352
        public function save_nav_menus_created_posts( $setting ){}//1386
        public function filter_tp_nav_menu_args( $args ){}//1426
        public function filter_wp_nav_menu( $nav_menu_content, $args ){}//1494
        public function hash_nav_menu_args( $args ):string{
            return $this->_tp_hash( serialize( $args ) );
        }//1515
        public function customize_preview_enqueue_deps():void {
            $this->tp_enqueue_script( 'customize-preview-nav-menus' ); // Note that we have overridden this.
        }//1524
        public function export_preview_data():void{}//1533
        public function export_partial_rendered_nav_menu_instances( $response ) {
            $response['nav_menu_instance_args'] = $this->preview_nav_menu_instance_args;
            return $response;
        }//1550
        public function render_nav_menu_partial( $partial, $nav_menu_args ) {
            unset( $partial );
            if ( ! isset( $nav_menu_args['args_hmac'] ) ) return false;
            $nav_menu_args_hmac = $nav_menu_args['args_hmac'];
            unset( $nav_menu_args['args_hmac'] );
            ksort( $nav_menu_args );
            if ( ! hash_equals( $this->hash_nav_menu_args( $nav_menu_args ), $nav_menu_args_hmac ) )
                return false;
            ob_start();
            $this->_tp_nav_menu( $nav_menu_args );
            return ob_get_clean();
        }//1566
    }
}else die;