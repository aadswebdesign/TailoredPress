<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 26-6-2022
 * Time: 09:00
 */
namespace TP_Admin\Traits\AdminTemplates;
if(ABSPATH){
    trait _adm_template_03{
        /**
         * @description Add a new field to a section of a settings page.
         * @param $id
         * @param $title
         * @param $callback
         * @param $page
         * @param string $section
         * @param array|null $args
         */
        protected function _add_settings_field( $id, $title, $callback, $page, $section = 'default',$args = null):void{
            $this->tp_settings_fields[ $page ][ $section ][ $id ] = ['id' => $id, 'title' => $title,'callback' => $callback,'args' => $args,];
        }//1613
        /**
         * @description Prints out all settings sections added to a particular settings page
         * @param $page
         * @return string
         */
        protected function _get_settings_sections( $page ):string{
            $output  = "";
            if ( ! isset( $this->tp_settings_sections[ $page ] ) ) {return false;}
            foreach ( (array) $this->tp_settings_sections[ $page ] as $section ) {
                if ( $section['title'] ) {$output .= "<h2>{$section['title']}</h2>\n";}
                if ( $section['callback'] ) {$output .= call_user_func( $section['callback'], $section );}
                if (! isset($this->tp_settings_fields[ $page ][ $section['id']])) { continue;}
                $output .= "<ul class='form-setup' role='presentation'>";
                $output .= $this->_get_settings_fields( $page, $section );
                $output .= "</ul>";
            }
            return $output;
        }//1681
        protected function _do_settings_sections( $page ):void{
            echo $this->_get_settings_sections( $page );
        }//1681
        /**
         * @description Print out the settings fields for a particular settings section.
         * @param $page
         * @param $section
         * @return string
         */
        protected function _get_settings_fields( $page, $section ):string{
            if ( ! isset( $this->tp_settings_fields[ $page ][ $section ] ) ) {
                return false;
            }
            $output  = "";
            foreach ( (array) $this->tp_settings_fields[ $page ][ $section ] as $field ) {
                $class = '';
                if ( ! empty( $field['args']['class'] ) ) {
                    $class = " class='{$this->_esc_attr($field['args']['class'])}'";
                }
                $output .= "<li $class>";
                if (!empty($field['args']['label_for'])){
                    $output .= "<dt class='row label'><label for='{$this->_esc_attr( $field['args']['label_for'])}'>{$field['title']}</label></dt>";
                }else{ $output .= "<dt class='row title'>{$field['title']}</dt>"; }
                $output .= "<dd>";
                $output .= call_user_func( $field['callback'], $field['args'] );
                $output .= "</dd></li>";
            }
            return $output;
        }//1720
        protected function _do_settings_fields( $page, $section ):void{
            echo $this->_get_settings_fields( $page, $section);
        }//1720
        /**
         * @description Register a settings error to be displayed to the user.
         * @param $setting
         * @param $code
         * @param $message
         * @param string $type
         */
        protected function _add_settings_error( $setting, $code, $message, $type = 'error' ):void{
            $this->tp_adm_settings_errors = ['setting' => $setting, 'code' => $code, 'message' => $message, 'type' => $type,];
        }//1774
        /**
         * @description Fetch settings errors registered by add_settings_error().
         * @param string $setting
         * @param bool $sanitize
         * @return array
         */
        protected function _get_settings_errors( $setting = '', $sanitize = false ):array{
            if ( $sanitize ) { $this->_sanitize_option( $setting, $this->_get_option( $setting ) );}
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] && $this->_get_transient( 'settings_errors' ) ) {
                $this->tp_adm_settings_errors = array_merge( (array) $this->tp_adm_settings_errors, $this->_get_transient( 'settings_errors' ) );
                $this->_delete_transient( 'settings_errors' );
            }
            if ( empty( $this->tp_adm_settings_errors ) ) { return [];}
            if ( $setting ) {
                $setting_errors = [];
                foreach ( (array) $this->tp_adm_settings_errors as $key => $details ) {
                    if ( $setting === $details['setting'] ) {$setting_errors[] = $this->tp_adm_settings_errors[ $key ];}
                }
                return $setting_errors;
            }
            return $this->tp_adm_settings_errors;
        }//1817
        /**
         * @description Display settings errors registered by add_settings_error().
         * @param string $setting
         * @param bool $sanitize
         * @param bool $hide_on_update
         * @return string
         */
        protected function _settings_errors( $setting = '', $sanitize = false, $hide_on_update = false ):string{
            if ( $hide_on_update && ! empty( $_GET['settings-updated'])){return false;}
            $settings_errors = $this->_get_settings_errors( $setting, $sanitize );
            if ( empty( $settings_errors ) ) {return false;}
            $output = '';
            foreach ((array) $settings_errors as $key => $details ) {
                if ( 'updated' === $details['type'] ) { $details['type'] = 'success';}
                if ( in_array( $details['type'], array( 'error', 'success', 'warning', 'info' ), true ) ) {
                    $details['type'] = 'notice-' . $details['type'];
                }
                $css_id    = sprintf('setting-error-%s', $this->_esc_attr( $details['code'] ));
                $css_class = sprintf( 'notice %s settings-error is-dismissible', $this->_esc_attr( $details['type'] ));
                $output .= "<div id='$css_id' class='$css_class'>";
                $output .= "<p class=''><strong>{$details['message']}</strong></p>";
                $output .= "</div>\n";
            }
            return $output;
        }//1885
        /**
         * @description Outputs the modal window used for,
         * @description . attaching media to posts or pages in the media-listing screen.
         * @param string $found_action
         * @return string
         */
        protected function _get_posts_div( $found_action = '' ):string{
            $output  = "<div id='find_posts' class='find-box' style='display: none;'>";
            $output .= "<header id='find_posts_head' class='find-box-head'>";
            $output .= "<h5>{$this->__('Attach to existing content.')}</h5>";
            $output .= "<dd><button id='find_posts_close' type='button' class='button close'><span class='screen-reader-text'>{$this->__('Close media attachment panel.')}</span></button></dd>";
            $output .= "</header><div class='find-box-inside'><div class='find-box-search'><ul><li>";
            if ( $found_action ) {
                $output .= "<input name='found_action' type='hidden' value='{$this->_esc_attr($found_action)}'/>";
            }
            $output .= "<input name='affected' id='affected' type='hidden' value=''/>";
            $output .= $this->_tp_nonce_field( 'find_posts', '_async_nonce', false );
            $output .= "</li><li>";
            $output .= "<dt><label for='find_posts_input' class='screen-reader-text'>{$this->__('Search')}</label></dt>";
            $output .= "<dd><input name='ps' id='find_posts_input' type='search' value=''/></dd>";
            $output .= "<span class='spinner'></span></li><li>";
            $output .= "<dd><input class='button' id='find_posts_search' type='button' value='{$this->_esc_attr('Search')}'/></dd>";
            $output .= "</li></ul></div><div id='find_posts_response'></div></div>";
            $output .= "<div class='find-box-buttons'>";
            $output .= $this->_get_submit_button( $this->__( 'Select' ), 'primary right', 'find_posts_submit', false );
            $output .= "</div></div>";
            return $output;
        }//1932
        protected function _find_posts_div( $found_action = '' ):void{
            echo $this->_get_posts_div( $found_action);
        }//1932
        /**
         * @description Displays the post password.
         * @return string
         */
        protected function _get_the_post_password():string{
            $output  = "";
            $post = $this->_get_post();
            if ( isset( $post->post_password ) ) {
                $output .= $this->_esc_attr( $post->post_password );
            }
            return $output;
        }//1969
        protected function _the_post_password():void{
            echo $this->_get_the_post_password();
        }//1969
        /**
         * @description Get the post title.
         * @param int $post
         * @return mixed
         */
        protected function _draft_or_post_title( $post = 0 ){
            $title = $this->_get_the_title( $post );
            if ( empty( $title ) ) {
                $title = $this->__( '(no title)' );
            }
            return $this->_esc_html( $title );
        }//1987
        /**
         * @description Displays the search query.
         * @return string
         */
        protected function _get_admin_search_query():string{
            return isset( $_REQUEST['s'] ) ? $this->_esc_attr( $this->_tp_unslash( $_REQUEST['s'] ) ) : '';
        }//2003
        protected function _admin_search_query():void{
            echo $this->_get_admin_search_query();
        }//2003
    }
}else die;