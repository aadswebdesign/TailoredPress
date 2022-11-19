<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-3-2022
 * Time: 17:40
 */
namespace TP_Core\Traits\Theme;
use TP_Core\Libs\Customs\TP_Customize_Manager;
use TP_Core\Libs\TP_Theme;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_assets;
if(ABSPATH){
    trait _theme_08 {
        use _init_assets;
        /**
         * @description Registers a theme feature for use in add_theme_support().
         * @param $feature
         * @param array $args
         * @return bool|TP_Error
         */
        protected function _register_theme_feature( $feature, $args = [] ){
            if ( ! is_array( $this->tp_registered_theme_features ) ) {
                $this->tp_registered_theme_features = [];
            }
            $defaults = ['type' => 'boolean','variadic' => false,'description' => '','show_in_rest' => false,];
            $args = $this->_tp_parse_args( $args, $defaults );
            if ( true === $args['show_in_rest'] ) {
                $args['show_in_rest'] = [];
            }
            if ( is_array( $args['show_in_rest'] ) ) {
                $args['show_in_rest'] = $this->_tp_parse_args($args['show_in_rest'], ['schema' => [],'name' => $feature,'prepare_callback' => null,]);
            }
            if ( ! in_array( $args['type'], array( 'string', 'boolean', 'integer', 'number', 'array', 'object' ), true ) ) {
                return new TP_Error('invalid_type',$this->__( 'The feature "type" is not valid JSON Schema type.' ));
            }
            if ( true === $args['variadic'] && 'array' !== $args['type'] ) {
                return new TP_Error('variadic_must_be_array', $this->__( 'When registering a "variadic" theme feature, the "type" must be an "array".' ));
            }
            if ( false !== $args['show_in_rest'] && in_array( $args['type'], array( 'array', 'object' ), true ) ) {
                if ( ! is_array( $args['show_in_rest'] ) || empty( $args['show_in_rest']['schema'] ) ) {
                    return new TP_Error('missing_schema', $this->__( 'When registering an "array" or "object" feature to show in the REST API, the feature\'s schema must also be defined.' ));
                }
                if ( 'array' === $args['type'] && ! isset( $args['show_in_rest']['schema']['items'] ) ) {
                    return new TP_Error('missing_schema_items',$this->__( 'When registering an "array" feature, the feature\'s schema must include the "items" keyword.' ));
                }
                if ( 'object' === $args['type'] && ! isset( $args['show_in_rest']['schema']['properties'] ) ) {
                    return new TP_Error('missing_schema_properties', $this->__( 'When registering an "object" feature, the feature\'s schema must include the "properties" keyword.' ));
                }
            }
            if ( is_array( $args['show_in_rest'] ) ) {
                if ( isset( $args['show_in_rest']['prepare_callback'] ) && ! is_callable( $args['show_in_rest']['prepare_callback'] )) {
                    return new TP_Error('invalid_rest_prepare_callback',sprintf($this->__( 'The "%s" must be a callable function.' ),'prepare_callback'));/* translators: %s: prepare_callback */
                }
                $args['show_in_rest']['schema'] = $this->_tp_parse_args($args['show_in_rest']['schema'],['description' => $args['description'],'type' => $args['type'],'default' => false,]);
                if ( is_bool( $args['show_in_rest']['schema']['default'] ) && ! in_array( 'boolean', (array) $args['show_in_rest']['schema']['type'], true )){
                    $args['show_in_rest']['schema']['type'] = (array) $args['show_in_rest']['schema']['type'];
                    array_unshift( $args['show_in_rest']['schema']['type'], 'boolean' );
                }
                $args['show_in_rest']['schema'] = $this->_rest_default_additional_properties_to_false( $args['show_in_rest']['schema'] );
            }
            $this->tp_registered_theme_features[ $feature ] = $args;
            return true;
        }//3150
        /**
         * @description Gets the list of registered theme features.
         * @return array
         */
        protected function _get_registered_theme_features():array{
            if (!is_array( $this->tp_registered_theme_features )){return [];}
            return $this->tp_registered_theme_features;
        }//3266
        /**
         * @description Gets the registration config for a theme feature.
         * @param $feature
         * @return bool|null
         */
        protected function _get_registered_theme_feature( $feature ):bool{
            if (!is_array( $this->tp_registered_theme_features)){return null;}
            return $this->tp_registered_theme_features[ $feature ] ?? null;
        }//3278
        /**
         * @description Checks an attachment being deleted to see if it's a header or background image.
         * @param $id
         */
        protected function _delete_attachment_theme_mod( $id ):void{
            $attachment_image = $this->_tp_get_attachment_url( $id );
            $header_image     = $this->_get_header_image();
            $background_image = $this->_get_background_image();
            $custom_logo_id   = $this->_get_theme_mod( 'custom_logo' );
            if ( $custom_logo_id && $custom_logo_id === $id ) {
                $this->_remove_theme_mod( 'custom_logo' );
                $this->_remove_theme_mod( 'header_text' );
            }
            if ( $header_image && $header_image === $attachment_image ) {
                $this->_remove_theme_mod( 'header_image' );
                $this->_remove_theme_mod( 'header_image_data' );
            }
            if ( $background_image && $background_image === $attachment_image ){ $this->_remove_theme_mod( 'background_image' );}
        }//3310
        /**
         * @description Checks if a theme has been changed and runs 'after_switch_theme' hook on the next TP load.
         */
        protected function _check_theme_switched():void{
            $stylesheet = $this->_get_option( 'theme_switched' );
            if ( $stylesheet ) {
                $_old_theme = $this->_tp_get_theme( $stylesheet );
                $old_theme = null;
                if($_old_theme instanceof TP_Theme ){
                    $old_theme = $_old_theme;
                }
                if ( $this->_get_option( 'theme_switched_via_customizer' ) ) {
                    $this->_remove_action( 'after_switch_theme', '_tp_menus_changed' );
                    $this->_remove_action( 'after_switch_theme', '_tp_sidebars_changed' );
                    $this->_update_option( 'theme_switched_via_customizer', false );
                }
                if ($old_theme->exists() ) {
                    $this->_do_action( 'after_switch_theme', $old_theme->get_theme( 'Name' ), $old_theme );
                } else {
                    /** This action is documented in wp-includes/theme.php */
                    $this->_do_action( 'after_switch_theme', $stylesheet, $old_theme );
                }
                $this->_flush_rewrite_rules();
                $this->_update_option( 'theme_switched', false );
            }
        }//3338
        /**
         * @description Includes and instantiates the TP_Customize_Manager class.
         */
        protected function _tp_customize_include():void{
            $is_customize_admin_page = ( $this->_is_admin() && 'customize.php' === basename( $_SERVER['PHP_SELF'] ) );
            $should_include = ($is_customize_admin_page || ( isset( $this->tp_customize ) && 'on' === $this->tp_customize )||( ! empty( $_GET['customize_changeset_uuid'] ) || ! empty( $_POST['customize_changeset_uuid'] ) ));
            if (!$should_include){return;}
            $keys = ['changeset_uuid','customize_changeset_uuid','customize_theme','theme','customize_messenger_channel','customize_autosaved',];
            $input_vars = array_merge($this->_tp_array_slice_assoc( $_GET, $keys ),$this->_tp_array_slice_assoc( $_POST, $keys ));
            $theme             = null;
            $autosaved         = null;
            $messenger_channel = null;
            $changeset_uuid = false;
            $branching = false;
            if ( $is_customize_admin_page && isset( $input_vars['changeset_uuid'] ) ) {
                $changeset_uuid = $this->_sanitize_key( $input_vars['changeset_uuid'] );
            } elseif ( ! empty( $input_vars['customize_changeset_uuid'] ) ) {
                $changeset_uuid = $this->_sanitize_key( $input_vars['customize_changeset_uuid'] );
            }
            if ( $is_customize_admin_page && isset( $input_vars['theme'] ) ) {
                $theme = $input_vars['theme'];
            } elseif ( isset( $input_vars['customize_theme'] ) ) {
                $theme = $input_vars['customize_theme'];
            }
            if(!empty($input_vars['customize_autosaved'])){$autosaved = true;}
            if ( isset( $input_vars['customize_messenger_channel'] ) ) {
                $messenger_channel = $this->_sanitize_key( $input_vars['customize_messenger_channel'] );
            }
            $is_customize_save_action = ($this->_tp_doing_async() && isset( $_REQUEST['action']) && 'customize_save' === $this->_tp_unslash( $_REQUEST['action']));
            $settings_previewed       = ! $is_customize_save_action;
            $tp_compact = compact('changeset_uuid','theme','messenger_channel','settings_previewed','autosaved','branching');
            $this->tp_customize = new TP_Customize_Manager($tp_compact);

        }//3390
        /**
         * @description Publishes a snapshot's changes.
         * @param $new_status
         * @param $old_status
         * @param $changeset_post
         */
        protected function _tp_customize_publish_changeset( $new_status, $old_status, $changeset_post ):void{
            $this->tp_customize;
            //$this->tpdb;//todo
            $is_publishing_changeset = ( 'customize_changeset' === $changeset_post->post_type && 'publish' === $new_status && 'publish' !== $old_status);
            if ( ! $is_publishing_changeset ) {return;}
            if ( empty( $this->tp_customize ) ) {
                $this->tp_customize = new TP_Customize_Manager(['changeset_uuid' => $changeset_post->post_name,'settings_previewed' => false,]);
            }
            if (!$this->_did_action( 'customize_register')){
                $this->_remove_action( 'customize_register', array( $this->tp_customize, 'register_controls' ) );
                $this->tp_customize->register_controls();
                $this->_do_action( 'customize_register', $this->tp_customize );
            }
            $this->tp_customize->publish_changeset_values( $changeset_post->ID );
            if ( ! $this->_tp_revisions_enabled( $changeset_post ) ) {
                $this->tp_customize->trash_changeset_post( $changeset_post->ID );
            }
        }//3498
        /**
         * @description Filters changeset post data upon insert to ensure post_name is intact.
         * @param $post_data
         * @param $supplied_post_data
         * @return mixed
         */
        protected function _tp_customize_changeset_filter_insert_post_data( $post_data, $supplied_post_data ){
            if (isset($post_data['post_type']) && 'customize_changeset' === $post_data['post_type'] && empty($post_data['post_name']) && !empty($supplied_post_data['post_name'])) {
                $post_data['post_name'] = $supplied_post_data['post_name'];
            }
            return $post_data;
        }//3573
        /**
         * @description Adds settings for the customize-loader script.
         */
        protected function _tp_customize_loader_settings():void{
            $admin_origin = parse_url( $this->_admin_url() );
            $home_origin  = parse_url( $this->_home_url() );
            $cross_domain = ( strtolower( $admin_origin['host'] ) !== strtolower( $home_origin['host'] ) );
            $browser = ['mobile' => $this->_tp_is_mobile(),'ios'=> $this->_tp_is_mobile() && preg_match( '/iPad|iPod|iPhone/', $_SERVER['HTTP_USER_AGENT'] ),];
            $settings = ['url'=> $this->_esc_url( $this->_admin_url( 'customize.php' ) ),'isCrossDomain' => $cross_domain,'browser'=> $browser,
                'l10n'=> ['saveAlert'=> $this->__( 'The changes you made will be lost if you navigate away from this page.' ), 'mainIframeTitle' => $this->__( 'Customizer' ),],];
            $script = 'const _tpCustomizeLoaderSettings = ' . $this->_tp_json_encode( $settings ) . ';';
            $this->tp_scripts = $this->_init_scripts();
            $data       = $this->tp_scripts->get_data( 'customize-loader', 'data' );
            if($data){$script = "$data\n$script";}
            $this->tp_scripts->add_data( 'customize-loader', 'data', $script );
        }//3589
        /**
         * @description Returns a URL to load the Customizer.
         * @param string $stylesheet
         * @return mixed
         */
        protected function _tp_customize_url( $stylesheet = '' ){
            $url = $this->_admin_url( 'customize.php' );
            if ( $stylesheet ) {$url .= '?theme=' . urlencode( $stylesheet );}
            return $this->_esc_url( $url );
        }//3629
    }
}else die;