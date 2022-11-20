<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-7-2022
 * Time: 19:55
 */
namespace TP_Core\Libs\Customs;
if(ABSPATH){
    class TP_Customize_Control extends Customize_Base{
        public $settings;
        public $setting = 'default';
        public $capability;
        public $priority = 10;
        public $section = '';
        public $label = '';
        public $description = '';
        public $choices = [];
        public $input_attrs = [];
        public $allow_addition = false;
        public $json = [];
        public $type = 'text';
        public $active_callback = '';
        public function __construct(TP_Customize_Manager $manager, $id, $args = array() ) {
            $keys = array_keys( get_object_vars( $this ) );
            foreach ( $keys as $key ) {
                if ( isset( $args[ $key ] ) ) $this->$key = $args[ $key ];
            }
            $this->manager = $manager;
            $this->id      = $id;
            if ( empty( $this->active_callback ) )
                $this->active_callback = array( $this, 'active_callback' );
            ++self::$_instance_count;
            $this->instance_number = self::$_instance_count;
            if ( ! isset( $this->settings ) ) $this->settings = $id;
            $settings = array();
            if ( is_array( $this->settings ) ) {
                foreach ( $this->settings as $key => $setting )
                    $settings[ $key ] = $this->manager->get_setting( $setting );
            } elseif ( is_string( $this->settings ) ) {
                $this->setting       = $this->manager->get_setting( $this->settings );
                $settings['default'] = $this->setting;
            }
            $this->settings = $settings;
        }//210
        public function enqueue(): void {}
        final public function active() {
            $control = $this;
            $active  = call_user_func( $this->active_callback, $this );
            $active = $this->_apply_filters( 'customize_control_active', $active, $control );
            return $active;
        }//257
        public function active_callback():bool {
            return true;
        }//284
        final public function value( $setting_key = 'default' ) {
            if ( isset( $this->settings[ $setting_key ] ) )
                return $this->settings[ $setting_key ]->value();
            return null;
        }//297
        public function to_json(): void{
            $this->json['settings'] = array();
            foreach ( $this->settings as $key => $setting )
                $this->json['settings'][ $key ] = $setting->id;
            $this->json['type']           = $this->type;
            $this->json['priority']       = $this->priority;
            $this->json['active']         = $this->active();
            $this->json['section']        = $this->section;
            $this->json['content']        = $this->get_content();
            $this->json['label']          = $this->label;
            $this->json['description']    = $this->description;
            $this->json['instanceNumber'] = $this->instance_number;
            if ( 'dropdown-pages' === $this->type )
                $this->json['allow_addition'] = $this->allow_addition;
        }//308
        public function json(): array{
            $this->to_json();
            return $this->json;
        }//335
        final public function check_capabilities(): bool{
            if ( ! empty( $this->capability ) && ! $this->_current_user_can( $this->capability ) )
                return false;
            foreach ( $this->settings as $setting ) {
                if ( ! $setting || ! $setting->check_capabilities() ) return false;
            }
            $section = $this->manager->get_section( $this->section );

            if ($section instanceof TP_Customize_Section && isset( $section ) && ! $section->check_capabilities() ) return false;
            return true;
        }//352
        final public function get_content() {
            ob_start();
            $this->maybe_render();
            return trim( ob_get_clean() );
        }//378
        final public function maybe_render(): void {
            if ( ! $this->check_capabilities() ) return;
            $this->_do_action( 'customize_render_control', $this );
            $this->_do_action( "customize_render_control_{$this->id}", $this );
            $this->_render();
        }//390
        protected function _render(): void{
            $id    = 'customize-control-' . str_replace( array( '[', ']' ), array( '-', '' ), $this->id );
            $class = 'customize-control customize-control-' . $this->type;
            printf( "<li id='%s' class='%s'>", $this->_esc_attr( $id ), $this->_esc_attr( $class ) );
            $this->_render_content();
            echo '</li>';
        }//424
        public function get_link( $setting_key = 'default' ): ?string{
            if ( isset( $this->settings[ $setting_key ] ) && $this->settings[ $setting_key ] instanceof TP_Customize_Setting )
                return "data-customize-setting-link='{$this->_esc_attr( $this->settings[ $setting_key ]->id )}'";
            else return "data-customize-setting-key-link='{$this->_esc_attr( $setting_key )}'";
        }//443
        public function link( $setting_key = 'default' ): void {
            echo $this->get_link( $setting_key );
        }//459
        public function input_attrs(): void{
            foreach ( $this->input_attrs as $attr => $value ) {
                echo "$attr='{$this->_esc_attr( $value )}'";
            }
        }//468
        protected function _get_render_content():string{
            return '';//todo
        }//486
        protected function _render_content():void{}
        final public function get_template():string {
            $script = "<script id='template_customize_ctrl_{$this->_esc_attr( $this->type )}_content'>";
            $script .= $this->_get_content_template();
            $script .= "</script>";
            return $script;
        }//676
        final public function print_template():void{
            echo $this->get_template();
        }
        protected function _get_content_template():string {return '';}
        protected function _content_template():void {}
    }
}else die;