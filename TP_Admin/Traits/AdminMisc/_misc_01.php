<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-8-2022
 * Time: 22:27
 */
namespace TP_Admin\Traits\AdminMisc;
use TP_Core\Libs\TP_Error;
use TP_Core\Traits\Inits\_init_error;
if(ABSPATH){
    trait _misc_01{
        use _init_error;
        /**
         * @description Display the default admin color scheme picker (Used in user-edit.php)
         * @param $user_id
         * @return callable
         */
        protected function _get_admin_color_scheme_picker( $user_id ):callable {
            ksort( $this->tp_adm_css_colors );
            if ( isset( $this->tp_adm_css_colors['fresh'] ) ) {
                $this->tp_adm_css_colors = array_filter(
                    array_merge(['fresh' => '','light' => '','modern' => '',],$this->tp_adm_css_colors));
            }
            $current_color = $this->_get_user_option( 'admin_color', $user_id );
            if ( empty( $current_color ) || ! isset( $this->tp_adm_css_colors[ $current_color ] ) ) {
                $current_color = 'fresh';
            }
            $tp_admin_css_colors = $this->tp_adm_css_colors;
            return static function()use($tp_admin_css_colors,$current_color){
                $html = "<fieldset id='color-picker' class='scheme-list'>";
                $html .= "<legend class='screen-reader-text'><span>{(new self)->__('Admin Color Scheme')}</span></legend>";
                ob_start();
                (new self)->_tp_nonce_field( 'save-color-scheme', 'color-nonce', false );
                $html .= ob_get_clean();
                foreach ( $tp_admin_css_colors as $color => $color_info ){
                    $_color_class = $color === $current_color ? 'selected' : '';
                    $_color_checked = (new self)->_get_checked( $color, $current_color );
                    $_icon_colors = (new self)->_esc_attr( (new self)->_tp_json_encode(['icons' => $color_info->icon_colors]));
                    $html .= "<div class='color-option {$_color_class}'>";
                    $html .= "<input type='radio' name='admin_color' id='admin_color_{(new self)->_esc_attr( $color )}' value='{(new self)->_esc_attr( $color )}' class='tog' {$_color_checked}/>";
                    $html .= "<input type='hidden' class='css-url' value='{(new self)->_esc_url( $color_info->url )}'/>";
                    $html .= "<input type='hidden' class='icon-colors' value='{$_icon_colors}' />";
                    $html .= "<label for='admin_color_{(new self)->_esc_attr( $color )}'>{(new self)->_esc_html( $color_info->name )}</label>";
                    $html .= "<table class='color-palette'><tr>";
                    foreach ( $color_info->colors as $html_color ) {
                        $html .= "<td style='background-color: {(new self)->_esc_attr( $html_color )};'>&nbsp;</td>";
                    }
                    $html .= "</tr></table></div>";
                }
                $html .= "</fieldset>";
                return $html;
            };
        }//925
        /**
         * @return string
         */
        protected function _tp_get_color_scheme_settings():string{
            $color_scheme = $this->_get_user_option( 'admin_color' );
            if ( empty( $this->tp_adm_css_colors[ $color_scheme ])){$color_scheme = 'fresh';}
            if ( ! empty( $this->tp_adm_css_colors[ $color_scheme ]->icon_colors ) ) {
                $icon_colors = $this->tp_adm_css_colors[ $color_scheme ]->icon_colors;
            } elseif ( ! empty( $this->tp_adm_css_colors['fresh']->icon_colors ) ) {
                $icon_colors = $this->tp_adm_css_colors['fresh']->icon_colors;
            } else {$icon_colors = ['base' => '#a7aaad','focus' => '#72aee6','current' => '#fff',];}
            ob_start();
            ?>
            <!--suppress JSUnusedLocalSymbols -->
            <script>let _tpColorScheme = '<?php $this->_tp_json_encode(['icons' => $icon_colors]) ?>'</script>
            <?php
            return ob_get_clean()."\n";
        }//990
        /**
         * @description Displays the viewport meta in the admin.
         * @return string
         */
        protected function _tp_get_admin_viewport_meta():string{
            $viewport_meta = $this->_apply_filters( 'admin_viewport_meta', 'width=device-width,initial-scale=1.0' );
            if ( empty( $viewport_meta ) ) {return null;}
            return "<meta name='viewport' content='{$this->_esc_attr( $viewport_meta )}'/>";
        }//1021
        /**
         * @description Adds viewport meta for mobile in Customizer.
         * @param $viewport_meta
         * @return string
         */
        protected function _customizer_mobile_viewport_meta( $viewport_meta ):string{
            return trim( $viewport_meta, ',' ) . ',minimum-scale=0.5,maximum-scale=1.2';
        }//1048
        /**
         * @description Check lock status for posts displayed on the Posts screen
         * @param $response
         * @param $data
         * @param $screen_id
         * @return mixed
         */
        protected function _tp_check_locked_posts( $response, $data, $screen_id ){
            $checked = [];
            $this->tp_screen_id = $screen_id; //todo. let's see this way
            if ( array_key_exists( 'tp_check_locked_posts', $data ) && is_array( $data['tp_check_locked_posts'] ) ) {
                foreach ( $data['tp_check_locked_posts'] as $key ) {
                    $post_id = $this->_abs_int( substr( $key, 5 ) );
                    if ( ! $post_id ) { continue;}
                    $user_id = $this->_tp_check_post_lock( $post_id );
                    if ( $user_id ) {
                        $user = $this->_get_user_data( $user_id );
                        if ( $user && $this->_current_user_can( 'edit_post', $post_id ) ) {
                            $send = ['text' => sprintf( $this->__( '%s is currently editing' ), $user->display_name ),];
                            /* translators: %s: User's display name. */
                            if ( $this->_get_option( 'show_avatars' ) ) {
                                $send['avatar_src']    = $this->_get_avatar_url( $user->ID, array( 'size' => 18 ) );
                                $send['avatar_src_2x'] = $this->_get_avatar_url( $user->ID, array( 'size' => 36 ) );
                            }
                            $checked[ $key ] = $send;
                        }
                    }
                }
            }
            if ( ! empty( $checked ) ) [ $response['tp_check_locked_posts'] = $checked];
            return $response;
        }//1062
        /**
         * @description Check lock status on the New/Edit Post screen and refresh the lock
         * @param $response
         * @param $data
         * @param $screen_id
         * @return mixed
         */
        protected function _tp_refresh_post_lock( $response, $data, $screen_id){
            if ( array_key_exists( 'tp_refresh_post_lock', $data ) ) {
                $this->tp_screen_id = $screen_id; //todo. let's see this way
                $received = $data['tp_refresh_post_lock'];
                $send     = [];
                $post_id = $this->_abs_int( $received['post_id'] );
                if ( ! $post_id ) {return $response;}
                if ( ! $this->_current_user_can( 'edit_post', $post_id)){return $response;}
                $user_id = $this->_tp_check_post_lock( $post_id );
                $user    = $this->_get_user_data( $user_id );
                if ( $user ) {
                    $error = ['text' => sprintf( $this->__( '%s has taken over and is currently editing.' ), $user->display_name ),];
                    /* translators: %s: User's display name. */
                    if ( $this->_get_option( 'show_avatars' ) ) {
                        $error['avatar_src']    = $this->_get_avatar_url( $user->ID, array( 'size' => 64 ) );
                        $error['avatar_src_2x'] = $this->_get_avatar_url( $user->ID, array( 'size' => 128 ) );
                    }
                    $send['lock_error'] = $error;
                } else {
                    $new_lock = $this->_tp_set_post_lock( $post_id );
                    if ( $new_lock ) { $send['new_lock'] = implode( ':', $new_lock );}
                }
                $response['tp_refresh_post_lock'] = $send;
            }
            return $response;
        }//1109
        /**
         * @description Check nonce expiration on the New/Edit Post screen and refresh if needed
         * @param $response
         * @param $data
         * @param $screen_id
         * @return mixed
         */
        protected function _tp_refresh_post_nonces( $response, $data, $screen_id ){
            if ( array_key_exists( 'tp_refresh_post_nonces', $data ) ) {
                $this->tp_screen_id = $screen_id; //todo. let's see this way
                $received                           = $data['tp_refresh_post_nonces'];
                $response['tp_refresh_post_nonces'] = ['check' => 1];
                $post_id = $this->_abs_int( $received['post_id'] );
                if ( ! $post_id ) {return $response;}
                if ( ! $this->_current_user_can( 'edit_post', $post_id ) ) {return $response;}
                $response['tp_refresh_post_nonces'] = [
                    'replace' => [
                        'get_permalink_nonce'    => $this->_tp_create_nonce( 'tp_get_permalink' ),
                        'sample_permalink_nonce' => $this->_tp_create_nonce( 'tp_sample_permalink' ),
                        'closed_postboxes_nonce' => $this->_tp_create_nonce( 'tp_closed+tp_postboxes' ),
                        '_async_linking_nonce'   => $this->_tp_create_nonce( 'tp_internal_linking' ),
                        '_tpnonce'              => $this->_tp_create_nonce( 'tp_update-post_' . $post_id ),
                    ],
                ];
            }
            return $response;
        }//1160
        /**
         * @description Add the latest Heartbeat and REST-API nonce to the Heartbeat response.
         * @param $response
         * @return mixed
         */
        protected function _tp_refresh_heartbeat_nonces( $response ){
            $response['rest_nonce'] = $this->_tp_create_nonce( 'tp_rest' );
            $response['heartbeat_nonce'] = $this->_tp_create_nonce( 'heartbeat_nonce' );
            return $response;
        }//1196
        /**
         * @description Disable suspension of Heartbeat on the Add/Edit Post screens.
         * @param $settings
         * @return mixed
         */
        protected function _tp_heartbeat_set_suspension( $settings ){
            if ( 'post.php' === $this->tp_pagenow || 'post-new.php' === $this->tp_pagenow ) {
                $settings['suspension'] = 'disable';
            }
            return $settings;
        }//1215 //todo
        /**
         * @description Autosave with heartbeat
         * @param $response
         * @param $data
         * @return mixed
         */
        protected function _heartbeat_autosave( $response, $data ){
            if ( ! empty( $data['tp_autosave'] ) ) {
                $_saved = $this->_tp_autosave( $data['tp_autosave'] );
                $saved = null;
                if($_saved  instanceof TP_Error ){$saved = $_saved;}
                if ( $this->_init_error( $saved ) ) {
                    $response['tp_autosave'] = ['success' => false, 'message' => $saved->get_error_message(),];
                } elseif ($saved === null) {
                    $response['tp_autosave'] = ['success' => false, 'message' => $this->__( 'Error while saving.' ),];
                } else {
                    /* translators: Draft saved date format, see https://www.php.net/manual/datetime.format.php */
                    $draft_saved_date_format = $this->__( 'g:i:s a' );
                    $response['tp_autosave'] = ['success' => true,'message' => sprintf( $this->__( 'Draft saved at %s.' ), $this->_date_i18n( $draft_saved_date_format ) ),];
                    /* translators: %s: Date and time. */
                }
            }
            return $response;
        }//1234
    }
}else{die;}