<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-5-2022
 * Time: 19:04
 */
namespace TP_Admin\Traits\AdminMultiSite;
if(ABSPATH){
    trait _adm_multisite_03{
        /**
         * @description Outputs the HTML for a network's "Edit Site" tabular interface.
         * @param array $args
         * @return string
         */
        protected function _get_network_edit_site_nav( $args =[]):string{
            $links = $this->_apply_filters('network_edit_site_nav_links',[
                    'site-info' => ['label' => $this->__( 'Info' ),'url' => 'site_info.php','cap' => 'manage_sites',],
                    'site-users' => ['label' => $this->__( 'Users' ),'url' => 'site_users.php','cap' => 'manage_sites',],
                    'site-themes' => ['label' => $this->__( 'Themes' ),'url' => 'site_themes.php','cap' => 'manage_sites',],
                    'site-settings' => ['label' => $this->__( 'Settings' ),'url' => 'site_settings.php','cap' => 'manage_sites',],]
            );
            $parsed_args = $this->_tp_parse_args($args,['blog_id' => isset( $_GET['blog_id'] ) ? (int) $_GET['blog_id'] : 0,'links' => $links,'selected' => 'site-info',]);
            $screen_links = [];
            foreach ((array) $parsed_args['links'] as $link_id => $link ) {
                if(!$this->_current_user_can( $link['cap'],$parsed_args['blog_id'])){continue;}
                $classes = ['nav-tab'];
                $aria_current = '';
                if ( $parsed_args['selected'] === $link_id || $link['url'] === $GLOBALS['pagenow'] ) {
                    $classes[] = 'nav-tab-active';
                    $aria_current = ' aria-current="page"';
                }
                $esc_classes = implode( ' ', $classes );
                $url = $this->_add_query_arg(['id'=>$parsed_args['blog_id']], $this->_network_admin_url( $link['url'] ) );
                $screen_links[ $link_id ] = "<a href='{$this->_esc_url($url)}' id='{$this->_esc_attr( $link_id )}' class='$esc_classes' $aria_current>{$this->_esc_html($link['label'])}</a>";
            }
            $_screen_links = implode( '', $screen_links );
            return "<nav class='nav-tab-wrapper' aria-label='{$this->_esc_attr__('Secondary menu' )}'>$_screen_links</nav>";
        }//1042
        protected function _network_edit_site_nav( $args =[]):void{
            echo $this->_get_network_edit_site_nav( $args);
        }//1042
        /**
         * @description Returns the arguments for the help tab on the Edit Site screens.
         * @return array
         */
        protected function _get_site_screen_help_tab_args():array{
            $content  = "<p>{$this->__('The menu is for editing information specific to individual sites, particularly if the admin area of a site is unavailable.')}</p>";
            $content .= "<p>{$this->__('<strong>Info</strong> &mdash; The site URL is rarely edited as this can cause the site to not work properly. The Registered date and Last Updated date are displayed. Network admins can mark a site as archived, spam, deleted and mature, to remove from public listings or disable.')}</p>";
            $content .= "<p>{$this->__('<strong>Users</strong> &mdash; This displays the users associated with this site. You can also change their role, reset their password, or remove them from the site. Removing the user from the site does not remove the user from the network.')}</p>";
            $_print_args = sprintf($this->__("<strong>Themes</strong> &mdash; This area shows themes that are not already enabled across the network.Enabling a theme in this menu makes it accessible to this site. It does not activate the theme, but allows it to show in the site&#8217;s Appearance menu. To enable a theme for the entire network, see the <a href='%s'>Network Themes</a> screen."),
                $this->_network_admin_url( 'themes.php' ));
            $content .= "<p>$_print_args</p>";
            $content .= "<p>{$this->__('<strong>Settings</strong> &mdash; This page shows a list of all settings associated with this site. Some are created by WordPress and others are created by plugins you activate. Note that some fields are grayed out and say Serialized Data. You cannot modify these values due to the way the setting is stored in the database.')}</p>";
            return ['id' => 'overview','title' => $this->__( 'Overview' ),'content' => $content, ];
        }//1145
        /**
         * todo @description Returns the content for the help sidebar on the Edit Site screens.
         * @return string
         */
        protected function _get_site_screen_help_sidebar_content():string{return 'todo';}//1169
    }
}else die;