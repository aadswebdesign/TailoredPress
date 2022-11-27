<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-5-2022
 * Time: 10:08
 */
namespace TP_Admin\Traits\AdminPost;
use TP_Core\Libs\Post\TP_Post;
use TP_Core\Libs\Block\TP_Block_Type_Registry;
use TP_Core\Libs\Users\TP_User;

if(ABSPATH){
    trait _adm_post_04{
        /**
         * @description Sanitizes POST values from a checkbox taxonomy metabox.
         * @param $terms
         * @return array
         */
        protected function _taxonomy_meta_box_sanitize_cb_checkboxes( $terms ):array{ //not used  $taxonomy,
            return array_map( 'intval', $terms );
        }//2084
        /**
         * @description Sanitizes POST values from an input taxonomy metabox.
         * @param $taxonomy
         * @param $terms
         * @return array
         */
        protected function _taxonomy_meta_box_sanitize_cb_input( $taxonomy, $terms ):array{
            if ( ! is_array( $terms ) ) {
                $comma = $this->_x( ',', 'tag delimiter' );
                if ( ',' !== $comma ) { $terms = str_replace( $comma, ',', $terms );}
                $terms = explode( ',', trim( $terms, " \n\t\r\0\x0B," ) );
            }
            $clean_terms = array();
            foreach ( $terms as $term ) {
                if ( empty( $term ) ) { continue;}
                $_term = $this->_get_terms(['taxonomy' => $taxonomy,'name' => $term,'fields' => 'ids','hide_empty' => false,]);
                if ( ! empty( $_term ) ) {$clean_terms[] = (int) $_term[0];}
                else { $clean_terms[] = $term;}
            }
            return $clean_terms;
        }//2097
        /**
         * @description Returns whether the post can be edited in the block editor.
         * @param $post
         * @return bool
         */
        protected function _use_block_editor_for_post( $post ):bool{
            $post = $this->_get_post( $post );
            if ( ! $post ) { return false; }
            if ( isset( $_GET['meta-box-loader'] ) ) {
                $this->_check_admin_referer( 'meta-box-loader', 'meta-box-loader-nonce' );
                return false;
            }
            $use_block_editor = $this->_use_block_editor_for_post_type( $post->post_type );
            /**
             * @param TP_Post $post  The post being checked.
             */
            return $this->_apply_filters( 'use_block_editor_for_post', $use_block_editor, $post );
        }//2146
        /**
         * @description Returns whether a post type is compatible with the block editor.
         * @param $post_type
         * @return bool
         */
        protected function _use_block_editor_for_post_type( $post_type ):bool{
            if ( ! $this->_post_type_exists( $post_type ) ) {return false;}
            if ( ! $this->_post_type_supports( $post_type, 'editor' ) ) { return false;}
            $post_type_object = $this->_get_post_type_object( $post_type );
            if ( $post_type_object && ! $post_type_object->show_in_rest ) { return false;}
            return $this->_apply_filters( 'use_block_editor_for_post_type', true, $post_type );
        }//2183
        /**@description Prepares server-registered blocks for the block editor.
         * @return array
         */
        protected function _get_block_editor_server_block_settings():array{
            $block_registry = TP_Block_Type_Registry::get_instance();
            $blocks         = [];
            $fields_to_pick = ['api_version' => 'apiVersion','title' => 'title','description' => 'description',
                'icon' => 'icon','attributes' => 'attributes','provides_context' => 'providesContext','uses_context' => 'usesContext',
                'supports' => 'supports','category' => 'category','styles' => 'styles','textdomain' => 'textdomain','parent' => 'parent',
                'keywords' => 'keywords','example' => 'example','variations' => 'variations',];
            foreach ( $block_registry->get_all_registered() as $block_name => $block_type ) {
                foreach ( $fields_to_pick as $field => $key ) {
                    if (!isset( $block_type->{ $field })){ continue;}
                    if ( ! isset( $blocks[ $block_name ])){ $blocks[ $block_name ] = [];}
                    $blocks[ $block_name ][ $key ] = $block_type->{ $field };
                }
            }
            return $blocks;
        }//2218
        /**
         * @description Renders the meta boxes forms.
         * @return string
         */
        protected function _get_block_editor_meta_boxes():string{
            $output  = "";
            $_original_meta_boxes = $this->tp_meta_boxes;
            $this->tp_meta_boxes = $this->_apply_filters( 'filter_block_editor_meta_boxes', $this->tp_meta_boxes );
            $locations = ['side', 'normal', 'advanced'];
            $priorities = ['high', 'sorted', 'core', 'default', 'low'];
            $output .= "<div class='meta-forms'><ul><li>";
            $output .= "<form class='metabox-base-form'>{$this->_get_block_editor_meta_box_post_form_hidden_fields( $this->tp_post )}</form>";
            $output .= "</li><li><form id='toggle_custom_fields_form' class='' method='post' action='{$this->_esc_url($this->_admin_url( 'post.php' ))}'>";
            $output .= $this->_tp_get_nonce_field( 'toggle-custom-fields', 'toggle_custom_fields_nonce' );
            $output .= "<input name='action' type='hidden' value='toggle-custom-fields'/>";
            $output .= "</form></li>";
            foreach ( $locations as $location ){
                $_onsubmit = " onsubmit='return false'";
                $output .= "<li><form class='metabox-location-{$this->_esc_attr('$location')}' $_onsubmit ><ul id='post_stuff' class='sidebar-open'>";
                $output .= "<li id='postbox_container_2' class='postbox-container'>";
                $output .= $this->_get_meta_boxes($this->tp_current_screen,$location,$this->tp_post);
                $output .= "</li></ul></form></li>";//
            }
            $meta_boxes_per_location = [];
            foreach ( $locations as $location ) {
                $meta_boxes_per_location[ $location ] = [];
                if ( ! isset( $this->tp_meta_boxes[$this->tp_current_screen->id ][ $location ] ) ) { continue;}
                foreach ( $priorities as $priority ) {
                    if ( ! isset( $this->tp_meta_boxes[ $this->tp_current_screen->id ][ $location ][ $priority ] ) ) {
                        continue;
                    }
                    $meta_boxes = (array) $this->tp_meta_boxes[ $this->tp_current_screen->id ][ $location ][ $priority ];
                    foreach ( $meta_boxes as $meta_box ) {
                        if ( false === $meta_box || ! $meta_box['title'] ) { continue;}
                        $meta_boxes_per_location[ $location ][] = ['id' => $meta_box['id'],'title' => $meta_box['title'],];
                    }
                }
            }
            $script  = 'window._tpLoadBlockEditor.then( function() {';
            $script .= 'tp.data.dispatch( \'core/edit_post\' ).setAvailableMetaBoxesPerLocation(' . $this->_tp_json_encode( $meta_boxes_per_location ) . ')';
            $script .= '});';
            $output .= $this->tp_add_inline_script( 'tp-edit-post', $script );
            if ( $this->tp_script_is( 'tp-edit-post', 'done' ) ) {
                $output .= sprintf("<script id='tp_edit_post_script'>\n %s \n</script>\n", trim( $script ));
            }
            $enable_custom_fields = (bool) $this->_get_user_meta( $this->_get_current_user_id(), 'enable_custom_fields', true );
            if ( $enable_custom_fields ) {
                $script  = "{console.log('todo', 'no jQuery here')}";
                ob_start();
                $this->tp_enqueue_script( 'tp-lists' );
                $output .= ob_get_clean();
                $output .= $this->tp_add_inline_script( 'tp-lists', $script );
            }
            $this->tp_meta_boxes = $_original_meta_boxes;
            $output .= "</li></ul></div>";
            return $output;
        }//2261
        /**
         * @description Renders the hidden form required for the meta boxes form.
         * @param $post
         * @return string
         */
        protected function _get_block_editor_meta_box_post_form_hidden_fields( $post ):string{
            $output  = "";
            $form_extra = '';
            if ( 'auto-draft' === $post->post_status ) {
                $form_extra .= "<input type='hidden' id='auto_draft' name='auto_draft' value='1' />";
            }
            $form_action  = 'edit_post';
            $nonce_action = 'update-post_' . $post->ID;
            $form_extra  .= "<input type='hidden' id='post_ID' name='post_ID' value='" . $this->_esc_attr( $post->ID ) . "' />";
            $referer      = $this->_tp_get_referer();
            $_current_user = $this->_tp_get_current_user();
            $current_user = null;
            if($_current_user instanceof TP_User ){ $current_user = $_current_user;}
            $user_id      = $current_user->ID;
            $classic_output  = $this->_do_action( 'edit_form_after_title', $post );
            $classic_output .= $this->_do_action( 'edit_form_advanced', $post );
            $classic_elements = $this->_tp_html_split( $classic_output );
            // not used $hidden_inputs    = '';
            $output .= $this->_tp_get_nonce_field( $nonce_action );
            foreach ( $classic_elements as $element ) {
                if ( 0 !== strpos( $element, '<input ' ) ) { continue; }
                if ( preg_match( '/\type=[\'"]hidden[\'"]\s/', $element ) ) { $output .= $element;}
            }
            $output .= "<input id='user_id' name='user_ID' type='hidden' value='{(int) $user_id}'/>";
            $output .= "<input id='hidden_action' name='action' type='hidden' value='{$this->_esc_attr($form_action)}'/>";
            $output .= "<input id='original_action' name='original_action' type='hidden' value='{$this->_esc_attr($form_action)}'/>";
            $output .= "<input id='post_type' name='post_type' type='hidden' value='{$this->_esc_attr($post->post_type)}'/>";
            $output .= "<input id='original_post_status' name='original_post_status' type='hidden' value='{$this->_esc_attr($post->post_status)}'/>";
            $_referred = ($referer ? $this->_esc_url( $referer ) : '');
            $output .= "<input id='referred_by' name='referred_by' type='hidden' value='$_referred'/>";
            if ( 'draft' !== $this->_get_post_status( $post ) ) {
                $output .= $this->_tp_original_referer_field( true, 'previous' );
            }
            $output .= $form_extra;
            $output .= $this->_tp_nonce_field( 'meta-box-order', 'meta_box_order_nonce', false );
            $output .= $this->_tp_nonce_field( 'closed-post-boxes', 'closed_post_boxes_nonce', false );
            $output .= $this->_tp_nonce_field( 'sample-perma-link', 'sample_perma_link_nonce', false );
            $output .= $this->_do_action( 'block_editor_meta_box_hidden_fields', $post );
            return $output;
        }//2394
        /**
         * @description Disables block editor for tp_navigation type posts so they can be managed via the UI.
         * @param $value
         * @param $post_type
         * @return bool
         */
        protected function _disable_block_editor_for_navigation_post_type( $value, $post_type ):bool{
            if ( 'tp_navigation' === $post_type ) { return false;}
            return $value;
        }//2470
        /**
         * @description This callback disables the content editor for tp_navigation type posts.
         * @param $post
         */
        protected function _disable_content_editor_for_navigation_post_type( $post ):void{
            $post_type = $this->_get_post_type( $post );
            if ( 'tp_navigation' !== $post_type ) { return;}
            $this->_remove_post_type_support( $post_type, 'editor' );
        }//2489
        /**
         * @description This callback enables content editor for tp_navigation type posts.
         * @param $post
         */
        protected function _enable_content_editor_for_navigation_post_type( $post ):void{
            $post_type = $this->_get_post_type( $post );
            if ( 'tp_navigation' !== $post_type ) { return;}
            $this->_add_post_type_support( $post_type, 'editor' );
        }//2510
    }
}else die;

