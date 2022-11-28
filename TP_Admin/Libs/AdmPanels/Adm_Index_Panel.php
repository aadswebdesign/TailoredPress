<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 22-10-2022
 * Time: 16:22
 */
namespace TP_Admin\Libs\AdmPanels;
use TP_Admin\Admins;
if(ABSPATH){
    class Adm_Index_Panel extends Admins{
        protected $_args;
        private $__screen;
        public function __construct($downstream_args = null ,$args = null){
            parent::__construct();
            $this->adm_header_args = [
                'parent_file' => 'index.php',
                'get_admin_index_head' => [$this,'get_options_index_stuff'],
                'index_title' => 'TailoredPress',
            ];
            $this->adm_header = $this->get_adm_component_class('Adm_Header',$this->adm_header_args);
            $this->adm_footer_args = ['parent_file' => 'index.php',];
            $this->adm_footer = $this->get_adm_component_class('Adm_Footer',$this->adm_footer_args);
            $this->__screen = $this->_get_current_screen();
            $this->__info_setup();
            //var_dump('',$_GET['admin_email_remind_later']);
        }

        private function __info_setup():void{
            $help  = "<p>{$this->__('Welcome to your (Experimental)TailoredPress Dashboard!')}</p>";
            $help .= "<p>{$this->__('The Dashboard is the first place you will come to every time you log into your site. It is where you will find all your TailoredPress tools. If you need help, just click the &#8220;Help&#8221; tab above the screen title.')}</p>";
            $this->__screen->add_help_tab(['id' => 'overview', 'title' => $this->__('Overview'), 'content', $help]);
            $help  = "<p>{$this->__('The left-hand navigation menu provides links to all of the TailoredPress administration screens, with submenu items displayed on hover. You can minimize this menu to a narrow icon strip by clicking on the Collapse Menu arrow at the bottom.')}</p>";
            $help .= "<p>{$this->__('Links in the Toolbar at the top of the screen connect your dashboard and the front end of your site, and provide access to your profile and helpful WordPress information.')}</p>";
            $this->__screen->add_help_tab(['id' => 'help_navigation', 'title' => $this->__('Navigation'), 'content', $help]);
            $help  = "<p>{$this->__('You can use the following controls to arrange your Dashboard screen to suit your workflow. This is true on most other administration screens as well.')}</p>";
            $help .= "<p>{$this->__('<strong>Screen Options</strong> &mdash; Use the Screen Options tab to choose which Dashboard boxes to show.')}</p>";
            $help .= "<p>{$this->__('<strong>Drag and Drop</strong> &mdash; To rearrange the boxes, drag and drop by clicking on the title bar of the selected box and releasing when you see a gray dotted-line rectangle appear in the location you want to place the box.')}</p>";
            $help .= "<p>{$this->__('<strong>Box Controls</strong> &mdash; Click the title bar of the box to expand or collapse it. Some boxes added by plugins may have configurable content, and will show a &#8220;Configure&#8221; link in the title bar if you hover over it.')}</p>";
            $this->__screen->add_help_tab(['id' => 'help_layout', 'title' => $this->__('Layout'), 'content', $help]);
            $help = "<p>{$this->__('The boxes on your Dashboard screen are:')}</p>";
            if ( $this->_current_user_can( 'edit_theme_options' ) ) {
                $help .= "<p>{$this->__('<strong>Welcome</strong> &mdash; Shows links for some of the most common tasks when setting up a new site.')}</p>";
            }
            if ( $this->_current_user_can( 'view_site_health_checks' ) ) {
                $help .= "<p>{$this->__('<strong>Site Health Status</strong> &mdash; Informs you of any potential issues that should be addressed to improve the performance or security of your website.')}</p>";
            }
            if ( $this->_current_user_can( 'edit_posts' ) ) {
                $help .= "<p>{$this->__('\'<strong>At a Glance</strong> &mdash; Displays a summary of the content on your site and identifies which theme and version of TailoredPress you are using.')}</p>";
            }
            $help .= "<p>{$this->__('<strong>Activity</strong> &mdash; Shows the upcoming scheduled posts, recently published posts, and the most recent comments on your posts and allows you to moderate them.')}</p>";
            if ( $this->_is_blog_admin() && $this->_current_user_can( 'edit_posts' ) ) {
                $help .= "<p>{$this->__("<strong>Quick Draft</strong> &mdash; Allows you to create a new post and save it as a draft. Also displays links to the 3 most recent draft posts you've started.")}</p>";
            }
            $help .= "<p>{$this->__('Events and News is out of scope here.')}</p>";
            $this->__screen->add_help_tab(['id' => 'help_content', 'title' => $this->__('Content'), 'content', $help]);
            unset( $help );
            $tp_version = $this->_get_bloginfo( 'version', 'display' );
            $tp_version_text = sprintf($this->__('Version %s'),$tp_version);
            $sidebar  = "<p><strong>{$this->__('For more information:')}</strong></p>";
            $sidebar .= "<p>{$this->__('TODO:')}</p>";
            $sidebar .= "<p>{$this->__('Out of scope for now')}</p>";
            $sidebar .= "<p>$tp_version_text</p>";
            $this->__screen->set_help_sidebar($sidebar);
        }
        private function __welcome_panel():string{
            $output_welcome = "";
            if ( $this->_has_action( 'welcome_panel' ) && $this->_current_user_can( 'edit_theme_options' ) ) {
                $classes = 'welcome-panel';
                $option = (int) $this->_get_user_meta( $this->_get_current_user_id(), 'show_welcome_panel', true );
                $hide = ( 0 === $option || ( 2 === $option && $this->_tp_get_current_user()->user_email !== $this->_get_option( 'admin_email' ) ) );
                if ($hide ){$classes .= ' hidden';}
                $output_welcome .= "<div id='welcome_panel' class='{$this->_esc_attr($classes)}'><ul><li>";
                $output_welcome .= $this->_tp_get_nonce_field( 'welcome-panel-nonce', 'welcome_panel_nonce', false );
                $output_welcome .= "</li><li>";
                $output_welcome .= "<dd><a href='{$this->_esc_url($this->_admin_url('?welcome=0'))}' class='welcome-panel-close' aria-label='{$this->_esc_attr('Dismiss the welcome panel')}'>{$this->__('Dismiss')}</a></dd>";
                $output_welcome .= "</li><li>";
                $output_welcome .= $this->_get_action( 'welcome_panel' );
                $output_welcome .= "</li></ul></div>";
            }
            return $output_welcome;
        }

        private function __to_string():string{
            $output  = $this->adm_header;
            $output .= "<section class='tp-wrap index'>";
            $output .= "<header class='inner-header'><h1>{$this->__('Dashboard')}</h1></header>";
            $output .= "<p class=''>{$this->__('TODO: admin_email stuff')}</p>";
            $output .= $this->__welcome_panel();
            $output .= "</section><!-- tp-wrap -->";
            $output .= $this->adm_footer;
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}