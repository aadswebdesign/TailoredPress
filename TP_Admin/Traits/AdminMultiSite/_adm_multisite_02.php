<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-5-2022
 * Time: 19:04
 */
namespace TP_Admin\Traits\AdminMultiSite;
if(ABSPATH){
    trait _adm_multisite_02{
        /**
         * @description Displays an access denied message when a user tries to view a site's dashboard they
         * @description . do not have access to.
         */
        protected function _access_denied_splash():void{
            if ( ! $this->_is_user_logged_in() || $this->_is_network_admin() ) { return; }
            $blogs = $this->_get_blogs_of_user( $this->_get_current_user_id() );
            if ( $this->_tp_list_filter( $blogs, array( 'user_blog_id' => $this->_get_current_blog_id() ) ) ) {
                return;}
            $blog_name = $this->_get_bloginfo( 'name' );
            if ( empty( $blogs ) ) {
                $this->_tp_die( sprintf( $this->__( 'You attempted to access the "%1$s" dashboard, but you do not currently have privileges on this site. If you believe you should be able to access the "%1$s" dashboard, please contact your network administrator.' ),$blog_name), 403);
            }
            $output  = "<div class='access-denied-splash'><ul><li><p>";
            $output .= sprintf($this->__('You attempted to access the "%1$s" dashboard, but you do not currently have privileges on this site. If you believe you should be able to access the "%1$s" dashboard, please contact your network administrator.'),$blog_name);
            $output .= "</p><p>{$this->__('If you reached this screen by accident and meant to visit one of your own sites, here are some shortcuts to help you find your way.')}</p></li><li>";
            $output .= "<h3>{$this->__('Your Sites')}</h3>";
            $output .= "</li><li><ul>";
            foreach ( $blogs as $blog ) {
                $output .= "<li><dt><p>{$blog->blogname}</p></dt>";
                $output .= "<dd><a href='{$this->_esc_url($this->_get_admin_url( $blog->userblog_id ))}'>{$this->__('Visit Dashboard')}</a></dd>";
                $output .= "<span>|</span>";
                $output .= "<dd><a href='{$this->_esc_url($this->_get_home_url( $blog->userblog_id ))}'>{$this->__('View Site')}</a></dd>";
                $output .= "</li>";
            }
            $output .= "</ul></li><li></li><li></li></ul></div>";
            $this->_tp_die( $output, 403 );
        }//575
        /**
         * @description Checks if the current user has permissions to import new users.
         * @param $permission
         * @return bool
         */
        protected function _check_import_new_users( $permission = true):bool{
            if ( ! $this->_current_user_can( 'manage_network_users' ) ) { return false;}
            return $permission;
        }//630 //todo
        /**
         * @description Generates and displays a drop-down of available languages.
         * @param array $lang_files
         * @param string $current
         * @return string
         */
        protected function _mu_get_dropdown_languages( $lang_files = [], $current = '' ):string{
            $flag   = false;
            $output = [];
            foreach ( (array) $lang_files as $val ) {
                $code_lang = basename( $val, '.mo' );
                if ( 'en_US' === $code_lang ) { // American English.
                    $flag = true;
                    $ae = $this->__( 'American English' );
                    $output[ $ae ] = "<option value='{$this->_esc_attr($code_lang )}' {$this->_get_selected( $current, $code_lang)}>$ae</option>";
                } elseif ( 'en_GB' === $code_lang ) { // British English.
                    $flag = true;
                    $be = $this->__( 'British English' );
                    $output[ $be ] = "<option value='{$this->_esc_attr($code_lang )}' {$this->_get_selected( $current, $code_lang)}>$be</option>";
                } else {
                    $translated = $this->_format_code_lang( $code_lang );
                    $output[ $translated ] = "<option value='{$this->_esc_attr($code_lang )}' {$this->_get_selected( $current, $code_lang)}>{$this->_esc_html($translated)}</option>";
                }
            }
            if ( false === $flag ) {
                $output[] = "<option value='{$this->_get_selected( $current, '')}'>{$this->__('English')}</option>";
            }
            uksort( $output, 'strnatcasecmp' );
            $output = $this->_apply_filters( 'mu_dropdown_languages', $output, $lang_files, $current );
            return implode( "\n\t", $output );
        }//647
        /**
         * @description Displays an admin notice to upgrade all sites after a core upgrade.
         * @return string
         */
        public function get_site_admin_notice():string{
            if ( ! $this->_current_user_can( 'upgrade_network' ) ) { return false;}
            if ( 'upgrade.php' === $this->tp_pagenow ) { return true;}//todo could be false?
            $output  = "";
            if ( (int) $this->_get_site_option( 'tp_mu_upgrade_site' ) !== TP_DB_VERSION ) {
                $output .= "<div class='update-nag notice notice-warning inline'>";
                $output .= sprintf($this->__("Thank you for Updating! Please visit the <a href='%s'>Upgrade Network</a> page to update all your sites."),$this->_esc_url( $this->_network_admin_url( 'upgrade.php' ) ));
                $output .= "</div>";
            }
            return $output;
        }//699
        /**
         * @description Avoids a collision between a site slug and a permalink slug.
         * @param $data
         * @return mixed
         */
        protected function _avoid_blog_page_permalink_collision( $data){//not used , $postarr
            if ( $this->_is_subdomain_install()){ return $data;}
            if ('page' !== $data['post_type']){ return $data;}
            if ( ! isset( $data['post_name'] ) || '' === $data['post_name'] ) { return $data; }
            if ( ! $this->_is_main_site() ) { return $data;}
            if ( isset( $data['post_parent'] ) && $data['post_parent'] ) { return $data;}
            $post_name = $data['post_name'];
            $c         = 0;
            while ( $c < 10 && $this->_get_id_from_blogname( $post_name ) ) {
                $post_name .= random_int( 1, 10 );
                $c++;
            }
            if ( $post_name !== $data['post_name'] ) { $data['post_name'] = $post_name;}
            return $data;
        }//731
        /**
         * @description Handles the display of choosing a user's primary site.
         * @return string
         */
        protected function _get_primary_blog():string{
            $all_blogs    = $this->_get_blogs_of_user( $this->_get_current_user_id() );
            $primary_blog = (int) $this->_get_user_meta( $this->_get_current_user_id(), 'primary_blog', true );
            $output  = "<div class='form-block' role='presentation'><ul><li>";
            $output .= "<dt itemscope='row'><label for='primary_blog'>{$this->__('Primary Site')}</label></dt>";
            //<input name='' id='primary_blog' type='' value='{$this->_esc_attr('')}'/>
            $output .= "<dd>";
            if ( count( $all_blogs ) > 1 ) {
                $found = false;
                $output .= "<select id='primary_blog' name='primary_blog'>";
                foreach ( (array) $all_blogs as $blog ) {
                    if ( $blog->userblog_id === $primary_blog ) {$found = true;}
                    $output .= "<option value='{$blog->userblog_id}' {$this->_get_selected( $primary_blog, $blog->userblog_id )}>{$this->_esc_url( $this->_get_home_url( $blog->userblog_id ))}</option>";
                }
                $output .= "</select>";
                if ( ! $found ) {
                    $blog = reset( $all_blogs );
                    $this->_update_user_meta( $this->_get_current_user_id(), 'primary_blog', $blog->userblog_id );
                }
            }elseif( 1 === count( $all_blogs )){
                $blog = reset( $all_blogs );
                $output .= $this->_esc_url($this->_get_home_url( $blog->userblog_id ));
                if ( $blog->userblog_id !== $primary_blog ) { // Set the primary blog again if it's out of sync with blog list.
                    $this->_update_user_meta( $this->_get_current_user_id(), 'primary_blog', $blog->userblog_id );
                }
            }else{ $output .= "N/A";}
            $output .= "</dd></li></ul></div>";
            return $output;
        }//771
        /**
         * @description Whether or not we can edit this network from this page.
         * @param $network_id
         * @return mixed
         */
        protected function _can_edit_network( $network_id ){
            if ( $this->_get_current_network_id() === (int) $network_id ) { $result = true;}
            else { $result = false;}
            return $this->_apply_filters( 'can_edit_network', $result, $network_id );
        }//828
        /**
         * @description Thickbox image paths for Network Admin.
         * @access private
         * @return string
         */
        public function get_thickbox_path_admin_subfolder():string{
            ob_start();
            ?>
            <script id="thickbox_path_admin_subfolder">
                const tpPathToImage = '<?php echo $this->_esc_js( $this->_includes_url( 'js/thickbox/loadingAnimation.gif', 'relative' ) ); ?>';//todo
                console.log('thickbox_path_admin_subfolder', tpPathToImage);
            </script>
            <?php
            return ob_get_clean();
        }//853
        /**
         * @param $users
         * @return string
         */
        protected function _get_confirm_delete_users( $users ):string{
            $current_user = $this->_tp_get_current_user();
            if ( ! is_array( $users ) || empty( $users ) ) {
                return false;
            }

            $output  = "<div class='delete-users-module'>";
            $output .= "<header class='delete-users-header'><ul><li>";
            $output .= "<h1>{$this->__('Users')}</h1>";
            if ( 1 === count( $users ) ){
                $output .= "<p>{$this->__('You have chosen to delete this user from all networks and sites.')}</p>";
            }else{
                $output .= "<p>{$this->__('You have chosen to delete the following users from all networks and sites.')}</p>";
            }
            $output .= "</li></ul></header>";
            $this->_tp_nonce_field( 'ms-users-delete' );
            $site_admins = $this->_get_super_admins();
            $admin_out   = "<option></option>";
            if($current_user !== null){
                $admin_out   = "<option value='{$this->_esc_attr($current_user->ID)}'>{$current_user->user_login}</option>";
            }
            $all_users = (array) $_POST['allusers'];
            $output .= "<div class='form-block'><form method='post' action='users.php?action=do_delete'><ul><li>";
            $output .= "<input name='do_delete' type='hidden' /></li>";
            foreach ( $all_users as $user_id ) {
                if ( '' !== $user_id && '0' !== $user_id ) {
                    $delete_user = $this->_get_user_data( $user_id );
                    if ( ! $this->_current_user_can( 'delete_user', $delete_user->ID ) ) {
                        $this->_tp_die(sprintf($this->__( 'Warning! User %s cannot be deleted.' ),$delete_user->user_login));
                    }
                    if ( in_array( $delete_user->user_login, $site_admins, true ) ) {
                        $this->_tp_die(sprintf($this->__('Warning! User cannot be deleted. The user %s is a network administrator.'),"<em>{$delete_user->user_login}</em>"));
                    }
                    $output .= "<li>";
                    $output .= "<dt itemscope='row'><p>{$delete_user->user_login}</p></dt>";
                    $output .= "<input name='user[]' type='hidden' value='{$this->_esc_attr($user_id)}'/>";
                    $output .= "</li><li>";
                    $blogs = $this->_get_blogs_of_user( $user_id, true );
                    if ( ! empty( $blogs ) ) {
                        $_print_blogs = sprintf($this->__('What should be done with content owned by %s?'),"<em>{$delete_user->user_login}</em>");
                        $output .= "<dd><fieldset><p><legend>$_print_blogs</legend></p>";
                        foreach ( (array) $blogs as $key => $details ) {
                            $blog_users = $this->_get_users(['blog_id' => $details->userblog_id,'fields' => ['ID','user_login'],]);
                            if ( is_array( $blog_users ) && ! empty( $blog_users ) ) {
                                $user_site      = "<a href='{$this->_esc_url( $this->_get_home_url( $details->userblog_id ) )}'>{$details->blogname}</a>";
                                $user_dropdown  = "<dt><label for='re_assign_user' class='screen-reader-text'>{$this->__('Select a user')}</label></dt>";
                                $user_dropdown .= "<dd><select name='blog[$user_id][$key]' id='re_assign_user'>";
                                $user_list      = '';
                                foreach ( $blog_users as $user ) {
                                    if ( ! in_array( (int) $user->ID, $all_users, true ) ) {
                                        $user_list .= "<option value='{$user->ID}'>{$user->user_login}</option>";
                                    }
                                }
                                if ( '' === $user_list ) { $user_list = $admin_out;}
                                $user_dropdown .= "$user_list</select></dd>\n";
                                $output .= "<ul><li>";
                                $output .= sprintf( $this->__( 'Site: %s' ), $user_site );
                                $output .= "</li><li>";
                                $output .= "<dd><input id='delete_option_0' name='delete[$details->userblog_id][$delete_user->ID' type='radio' value='delete' checked/></dd>";
                                $output .= "<dt><label for='delete_option_0'>{$this->__('Delete all content.')}</label></dt>";
                                $output .= "</li><li>";
                                $output .= "<dd><input id='delete_option_1' name='delete[$details->userblog_id][$delete_user->ID' type='radio' value='reassign'/></dd>";
                                $output .= "<dt><label for='delete_option_1'>{$this->__('Attribute all content to:')}</label></dt>$user_dropdown";
                                $output .= "</li></ul>";
                            }
                        }
                        $output .= "</fieldset></dd></li><li>";
                    }else{
                        $output .= "<dt><p>{$this->__('User has no sites or content and will be deleted.')}</p></dt>";
                        $output .= "</li><li>";
                    }
                }
            }
            $output .= $this->_do_action( 'delete_user_form', $current_user, $all_users );
            $output .= "</li><li>";
            if ( 1 === count( $users ) ){
                $output .= "<p>{$this->__('Once you hit &#8220;Confirm Deletion&#8221;, this user will be permanently removed.')}</p>";
            }else{
                $output .= "<p>{$this->__('Once you hit &#8220;Confirm Deletion&#8221;, these users will be permanently removed.')}</p>";
            }
            $output .= "</li><li>";
            $output .= $this->_get_submit_button( $this->__( 'Confirm Deletion' ), 'primary' );
            $output .= "</li></ul></form></div></div>";
            return $output;
        }//864
        /**
         * @description Print JavaScript in the header on the Network Settings screen.
         * @return string
         */
        protected function _get_network_settings_add_js():string{
            return $this->_get_php_console_log('network_settings_add_js', "Todo, as I do not want to use jQuery");
        }//1010
    }
}else die;