<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 23-6-2022
 * Time: 06:42
 */
namespace TP_Admin\Traits;
use TP_Admin\Libs\Adm_Screen;
use TP_Admin\Traits\AdminInits\_adm_init_screen;
use TP_Admin\Traits\AdminTemplates\_adm_template_04;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Inits\_init_meta;
use TP_Core\Traits\Templates\_general_template_09;
//use TP_Core\Traits\Templates\_template_02;
use TP_Core\Traits\User\_user_02;
if(ABSPATH){
    trait _adm_screen{
        use _user_02;
        use _filter_01;
        use _general_template_09;
        use _adm_init_screen;
        use _init_meta;
        use _adm_template_04;
        /**
         * @description Get the column headers for a screen
         * @param $screen
         * @return mixed
         */
        protected function _get_column_headers(Adm_Screen $screen ){
            static $column_headers = [];
            if ( ! isset( $column_headers[ $screen->id ] ) )
                $column_headers[ $screen->id ] = $this->_apply_filters( "manage_{$screen->id}_columns", array() );
            return $column_headers[ $screen->id ];
        }//17
        /**
         * @description Get a list of hidden columns.
         * @param $screen
         * @return mixed
         */
        protected function _get_hidden_columns(Adm_Screen $screen ){
            $hidden = $this->_get_user_option( 'manage' . $screen->id . 'columns_hidden' );
            $use_defaults = ! is_array( $hidden );
            if ( $use_defaults ) {
                $hidden = [];
                $hidden = $this->_apply_filters( 'default_hidden_columns', $hidden, $screen );
            }
            return $this->_apply_filters( 'hidden_columns', $hidden, $screen, $use_defaults );
        }//51
        /**
         * @description Prints the meta box preferences for screen meta.
         * @param $screen
         */
        protected function _meta_box_prefers(Adm_Screen $screen ): void{
            if ( empty( $this->tp_meta_boxes[ $screen->id ] ) ) return;
            $hidden = $this->_get_hidden_meta_boxes( $screen );
            foreach ( array_keys( $this->tp_meta_boxes[ $screen->id ] ) as $context ) {
                foreach ( array( 'high', 'core', 'default', 'low' ) as $priority ) {
                    if ( ! isset( $this->tp_meta_boxes[ $screen->id ][ $context ][ $priority ] ) )
                        continue;
                    foreach ( $this->tp_meta_boxes[ $screen->id ][ $context ][ $priority ] as $box ) {
                        if ( false === $box || ! $box['title'] ) continue;
                        if ( 'submit_div' === $box['id'] || 'link_submit_div' === $box['id'] ) continue;
                        $box_title = $box['title'];
                        if ( is_array( $box['args'] ) && isset( $box['args']['__box_basename'] ) )
                            $box_title = $box['args']['__box_basename'];
                        $is_hidden = in_array( $box['id'], $hidden, true );
                        printf(
                            "<label for='{%1\$s}_hide'><input class='hide-postbox-tog' name='{%1\$s}_hide' id='{%1\$s}_hide' type='checkbox' value='%1\$s' %2\$s />%3\$s</label>",
                            $this->_esc_attr( $box['id'] ),$this->_get_checked( $is_hidden, false ),$box_title);
                    }
                }
            }
        }//96
        /**
         * @description Gets an array of IDs of hidden meta boxes.
         * @param $screen
         * @return mixed
         */
        protected function _get_hidden_meta_boxes(Adm_Screen $screen ){
            $hidden = $this->_get_user_option( "metabox_hidden_{$screen->id}" );
            $use_defaults = ! is_array( $hidden );
            if ( $use_defaults ) {
                $hidden = [];
                if ( 'post' === $screen->base ) {
                    if ( in_array( $screen->post_type,['post', 'page', 'attachment'], true ) )
                        $hidden = ['slug_div', 'trackbacks_div', 'post_custom', 'post_excerpt', 'comment_status_div', 'comments_div', 'author_div', 'revisions_div'];
                    else $hidden = ['slug_div'];
                }
                $hidden = $this->_apply_filters( 'default_hidden_meta_boxes', $hidden, $screen );
            }
            return $this->_apply_filters( 'hidden_meta_boxes', $hidden, $screen, $use_defaults );
        }//152
        /**
         * @description Register and configure an admin screen option
         * @param $option
         * @param \array[] ...$args
         */
        protected function _add_screen_option( $option,array ...$args): void{
            $this->tp_current_screen = null;
            if( $this->tp_current_screen instanceof Adm_Screen ){
                $this->tp_current_screen = $this->_get_current_screen();
            }
            if ( ! $this->tp_current_screen ) return;
            $this->tp_current_screen->add_screen_option( $option, $args );
        }//205
        /**
         * @description Get the current screen object
         * @return mixed
         */
        protected function _get_current_screen(){
            return $this->tp_current_screen ?? null;
        }//224
        /**
         * @description Set the current screen object
         * @param string $hook_name
         */
        protected function _set_current_screen( $hook_name = '' ): void{
            $get_screen = Adm_Screen::get_screen($hook_name);
            if($get_screen !== null){
                $get_screen->set_screen();
            }
        }//242 //todo might need fixes
    }
}else die;