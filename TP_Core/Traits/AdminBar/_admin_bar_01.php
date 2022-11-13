<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 19-6-2022
 * Time: 06:46
 */
namespace TP_Core\Traits\AdminBar;
use TP_Core\Libs\TP_Admin_Bar;
use TP_Core\Traits\Inits\_init_adminbar;
use TP_Core\Traits\Inits\_init_custom;
if(ABSPATH){
    trait _admin_bar_01{
        use _init_adminbar,_init_custom;
        /**
         * @description Instantiates the admin bar object and
         * @description . set it up as a global for access elsewhere.
         * @return bool
         */
        protected function _tp_admin_bar_init():bool{
            $this->tp_admin_bar = $this->_init_adminbar();
            if(!$this->_is_admin_bar_showing()){return false;}
            $this->tp_admin_bar->initialize();
            $this->tp_admin_bar->add_menus();
            return $this->_apply_filters( 'tp_admin_bar_class',$this->tp_admin_bar);
        }//23 from admin-bar
        /**
         * @description Renders the admin bar to the page based on the $tp_admin_bar->menu member var.
         * @return bool
         */
        protected function _tp_admin_bar_render():bool{
            $this->tp_admin_bar = $this->_init_adminbar();
            static $rendered = false;
            if($rendered){ return false;}
            if (! is_object( $this->tp_admin_bar ) || ! $this->_is_admin_bar_showing()) {
                return false;
            }
            $this->_do_action_ref_array( 'admin_bar_menu', [&$this->tp_admin_bar]);
            $this->tp_admin_bar->render();
            $this->_do_action( 'tp_after_admin_bar_render' );
            $rendered = true;
        }//74 from admin-bar
        /**
         * @description Adds the TailoredPress logo menu.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_wp_menu(TP_Admin_Bar $tp_admin_bar ):void{
            if ( $this->_current_user_can( 'read' ) ) {
                $about_url = $this->_self_admin_url( 'about.php' );
            } elseif ( $this->_is_multisite() ) {
                $about_url = $this->_get_dashboard_url( $this->_get_current_user_id(), 'about.php' );
            } else {$about_url = false;}
            $tp_admin_bar->add_node(['parent' => 'tp_logo-external','id' => 'notice','title' => $this->__( 'Notice' ),'href' => $about_url,]);
        }//123 from admin-bar
        /**
         * @description Adds the sidebar toggle button.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_sidebar_toggle(TP_Admin_Bar $tp_admin_bar ):void{
            if ( $this->_is_admin() ) {
                $tp_admin_bar->add_node(['id' => 'menu_toggle',
                    'title' => "<span class='ab-icon' aria-hidden='true'></span><span class='screen-reader-text'>{$this->__('Menu')}</span>",
                    'href'  => '#',]);
            }
        }//207 from admin-bar
        /**
         * @description Adds the "My Account" item.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_my_account_item(TP_Admin_Bar $tp_admin_bar ):void{
            $user_id      = $this->_get_current_user_id();
            $_current_user = $this->_tp_get_current_user();
            $current_user = $_current_user ?? null;
            if ( ! $user_id ){ return;}
            if ( $this->_current_user_can( 'read' ) ) {
                $profile_url = $this->_get_edit_profile_url( $user_id );
            } elseif ( $this->_is_multisite() ) {
                $profile_url = $this->_get_dashboard_url( $user_id, 'profile.php' );
            } else {$profile_url = false;}
            $avatar = $this->_get_avatar( $user_id, 26 );
            $howdy = sprintf( $this->__( 'Howdy, %s' ), "<span class='display-name'>{$current_user->display_name}</span>" );
            $class = empty( $avatar ) ? '' : 'with-avatar';
            $tp_admin_bar->add_node(['id' => 'my_account','parent' => 'top_secondary',
                'title' => $howdy . $avatar, 'href' => $profile_url,'meta' => ['class' => $class,],]);
        }//226 from admin-bar
        /**
         * @description Adds the "My Account" submenu items.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_my_account_menu(TP_Admin_Bar $tp_admin_bar ):void{
            $user_id      = $this->_get_current_user_id();
            $_current_user = $this->_tp_get_current_user();
            $current_user = $_current_user ?? null;
            if(!$user_id ){return;}
            if ( $this->_current_user_can( 'read' ) ) {
                $profile_url = $this->_get_edit_profile_url( $user_id );
            } elseif ( $this->_is_multisite() ) {
                $profile_url = $this->_get_dashboard_url( $user_id, 'profile.php' );
            } else {$profile_url = false;}
            $tp_admin_bar->add_group(['parent' => 'my-account','id' => 'user-actions',]);
            $user_info  = $this->_get_avatar( $user_id, 64 );
            $user_info .= "<span class='display-name'>{$current_user->display_name}</span>";
            if ( $current_user->display_name !== $current_user->user_login ) {
                $user_info .= "<span class='username'>{$current_user->user_login}</span>";
            }
            $tp_admin_bar->add_node(['parent' => 'user_actions','id' => 'user_info',
                'title' => $user_info,'href' => $profile_url,'meta' => ['tabindex' => -1,],]);
            if ( false !== $profile_url ) {
                $tp_admin_bar->add_node(['parent' => 'user_actions','id' => 'edit_profile',
                    'title' => $this->__( 'Edit Profile' ),'href' => $profile_url,]);
            }
            $tp_admin_bar->add_node(['parent' => 'user_actions','id' => 'logout',
                'title' => $this->__( 'Log Out' ),'href' => $this->_tp_logout_url(),]);
        }//267 from admin-bar
        /**
         * @description Adds the "Site Name" menu.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_site_menu(TP_Admin_Bar $tp_admin_bar ):void{
            if ( ! $this->_is_user_logged_in()){return;}
            if ( ! $this->_is_user_member_of_blog() && ! $this->_current_user_can('manage_network')){ return;}
            $blogname = $this->_get_bloginfo( 'name' );
            if(!$blogname){ $blogname = preg_replace( '#^(https?://)?(www.)?#', '',$this->_get_home_url());}
            if ( $this->_is_network_admin() ) {
                $blogname = sprintf( $this->__( 'Network Admin: %s' ), $this->_esc_html( $this->_get_network()->site_name ) );
            } elseif ( $this->_is_user_admin() ) {
                $blogname = sprintf( $this->__( 'User Dashboard: %s' ), $this->_esc_html( $this->_get_network()->site_name ) );
            }
            $title = $this->_tp_html_excerpt( $blogname, 40, '&hellip;' );
            $tp_admin_bar->add_node(['id' => 'site_name','title' => $title,
                'href'  => ( $this->_is_admin() || ! $this->_current_user_can( 'read' ) ) ? $this->_home_url( '/' ) : $this->_admin_url(),]);
            if ( $this->_is_admin() ) {
                $tp_admin_bar->add_node(['parent' => 'site_name','id' => 'view-site',
                    'title' => $this->__( 'Visit Site' ),'href' => $this->_home_url( '/' ),]);
                if ( $this->_is_blog_admin() && $this->_is_multisite() && $this->_current_user_can( 'manage_sites' ) ) {
                    $tp_admin_bar->add_node(['parent' => 'site_name','id' => 'edit_site','title' => $this->__( 'Edit Site' ),
                        'href' => $this->_network_admin_url( 'site_info.php?id=' . $this->_get_current_blog_id() ),]);
                }
            } elseif ( $this->_current_user_can( 'read' ) ) {
                $tp_admin_bar->add_node(['parent' => 'site_name','id' => 'dashboard','title' => $this->__( 'Dashboard' ),'href' => $this->_admin_url(),]);
                $this->_tp_admin_bar_appearance_menu( $tp_admin_bar );
            }
        }//337 from admin-bar
        /**
         * @description Adds the "Edit site" link to the Toolbar.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_edit_site_menu(TP_Admin_Bar $tp_admin_bar ):void{
            if ( ! $this->_tp_is_block_theme()){return;}
            if ( ! $this->_current_user_can( 'edit_theme_options' ) || $this->_is_admin() ) { return;}
            $tp_admin_bar->add_node(['id' => 'site_editor', 'title' => $this->__( 'Edit site' ),'href' => $this->_admin_url( 'site_editor.php' ),]);
        }//418 from admin-bar
        /**
         * @description Adds the "Customize" link to the Toolbar.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_customize_menu(TP_Admin_Bar $tp_admin_bar ):void{
            $this->tp_customize = $this->_init_customize_manager();
            if ( $this->_tp_is_block_theme() && ! $this->_has_action( 'customize_register' )){return;}
            if ( ! $this->_current_user_can( 'customize' ) || $this->_is_admin()){return;}
            if ( $this->_is_customize_preview() && $this->tp_customize->changeset_post_id()
                && ! $this->_current_user_can( $this->_get_post_type_object( 'customize_changeset' )->cap->edit_post, $this->tp_customize->changeset_post_id() )
            ) { return;}
            $current_url = ( $this->_is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            if ( $this->_is_customize_preview() && $this->tp_customize->changeset_uuid() ) {
                $current_url = $this->_remove_query_arg( 'customize_changeset_uuid', $current_url );
            }
            $customize_url = $this->_add_query_arg( 'url', urlencode( $current_url ), $this->_tp_customize_url() );
            if ( $this->_is_customize_preview() ) {
                $customize_url = $this->_add_query_arg(['changeset_uuid' => $this->tp_customize->changeset_uuid()], $customize_url );
            }
            $tp_admin_bar->add_node(['id' => 'customize','title' => $this->__( 'Customize' ),
                'href' => $customize_url,'meta' => ['class' => 'hide-if-no-customize',],]);
            $this->_add_action( 'tp_before_admin_bar_render', 'tp_customize_support_script' );
        }//446 from admin-bar
        /**
         * @description Adds the "My Sites/[Site Name]" menu and all sub menus.
         * @param TP_Admin_Bar $tp_admin_bar
         */
        protected function _tp_admin_bar_my_sites_menu(TP_Admin_Bar $tp_admin_bar ):void{
            if ( ! $this->_is_user_logged_in() || ! $this->_is_multisite()){ return; }
            if ( count( $tp_admin_bar->user->blogs ) < 1 && ! $this->_current_user_can( 'manage_network' ) ) {
                return;}
            if ( $tp_admin_bar->user->active_blog ) {
                $my_sites_url = $this->_get_admin_url( $tp_admin_bar->user->active_blog->blog_id, 'my_sites.php' );
            } else { $my_sites_url = $this->_admin_url( 'my_sites.php' );}
            $tp_admin_bar->add_node(['id' => 'my_sites','title' => $this->__( 'My Sites' ),'href' => $my_sites_url,]);
            if ( $this->_current_user_can( 'manage_network' ) ) {
                $tp_admin_bar->add_group(['parent' => 'my_sites','id' => 'my_sites_super_admin',]);
                $tp_admin_bar->add_node(['parent' => 'my_sites_super_admin','id' => 'network_admin','title' => $this->__( 'Network Admin' ),
                    'href' => $this->_network_admin_url(),]);
                $tp_admin_bar->add_node(['parent' => 'network_admin','id' => 'network_admin_d','title' => $this->__( 'Dashboard' ),'href' => $this->_network_admin_url(),]);
                if ( $this->_current_user_can( 'manage_sites' ) ) {
                    $tp_admin_bar->add_node(['parent' => 'network_admin','id' => 'network_admin_s',
                        'title' => $this->__( 'Sites' ),'href' => $this->_network_admin_url( 'sites.php' ),]);
                }
                if ( $this->_current_user_can( 'manage_network_users' ) ) {
                    $tp_admin_bar->add_node(['parent' => 'network_admin','id' => 'network_admin_u',
                        'title' => $this->__( 'Users' ),'href' => $this->_network_admin_url( 'users.php' ),]);
                }
                if ( $this->_current_user_can( 'manage_network_themes' ) ) {
                    $tp_admin_bar->add_node(['parent' => 'network_admin','id' => 'network_admin_t',
                        'title' => $this->__( 'Themes' ),'href' => $this->_network_admin_url( 'themes.php' ),]);
                }
                if ( $this->_current_user_can( 'manage_network_options' ) ) {
                    $tp_admin_bar->add_node(['parent' => 'network_admin','id' => 'network_admin_o',
                        'title' => $this->__( 'Settings' ),'href' => $this->_network_admin_url( 'settings.php' ),]);
                }
            }
            $tp_admin_bar->add_group(['parent' => 'my_sites','id' => 'my_sites_list',
                'meta'   => ['class' => $this->_current_user_can( 'manage_network' ) ? 'ab-sub-secondary' : '',],]);
            foreach ( (array) $tp_admin_bar->user->blogs as $blog ) {
                $this->_switch_to_blog( $blog->userblog_id );
                if ( $this->_has_site_icon() ) {
                    $blank_avatar = sprintf("<img class='blank-avatar' src='%s' srcset='%s 2x' alt='' width='16' height='16' />",
                        $this->_esc_url( $this->_get_site_icon_url( 16 ) ), $this->_esc_url( $this->_get_site_icon_url( 32 ) ));
                } else {
                    $blank_avatar = "<div class='blank-avatar'></div>";
                }
                $blogname = $blog->blogname;
                if (!$blogname ){ $blogname = preg_replace( '#^(https?://)?(www.)?#', '', $this->_get_home_url() );}
                $menu_id = 'blog-' . $blog->userblog_id;
                if ( $this->_current_user_can( 'read' ) ) {
                    $tp_admin_bar->add_node(['parent' => 'my_sites_list','id' => $menu_id,
                        'title' => $blank_avatar . $blogname,'href' => $this->_admin_url(),]);
                    $tp_admin_bar->add_node(['parent' => $menu_id,'id' => $menu_id . '_d',
                        'title' => $this->__( 'Dashboard' ),'href' => $this->_admin_url(),]);
                } else {
                    $tp_admin_bar->add_node(['parent' => 'my_sites_list','id' => $menu_id,
                        'title' => $blank_avatar . $blogname,'href' => $this->_home_url(),]);
                }
                if ( $this->_current_user_can( $this->_get_post_type_object( 'post' )->cap->create_posts ) ) {
                    $tp_admin_bar->add_node(['parent' => $menu_id,'id' => $menu_id . '_n',
                        'title' => $this->_get_post_type_object( 'post' )->labels->new_item,
                        'href' => $this->_admin_url( 'post_new.php' ),]);
                }
                if ( $this->_current_user_can( 'edit_posts' ) ) {
                    $tp_admin_bar->add_node(['parent' => $menu_id,'id' => $menu_id . '_c',
                        'title' => $this->__( 'Manage Comments' ),'href' => $this->_admin_url( 'edit_comments.php' ),]);
                }
                $tp_admin_bar->add_node(['parent' => $menu_id,'id' => $menu_id . '_v',
                    'title' => $this->__( 'Visit Site' ),'href' => $this->_home_url( '/' ),]);
                $this->_restore_current_blog();
            }
        }//496 from admin-bar
    }
}else die;