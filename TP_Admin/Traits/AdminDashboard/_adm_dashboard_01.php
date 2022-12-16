<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-8-2022
 * Time: 22:27
 */
namespace TP_Admin\Traits\AdminDashboard;
use TP_Admin\Libs\Adm_Screen;
use TP_Admin\Libs\AdmUtils\Adm_Site_Health;
if(ABSPATH){
    trait _adm_dashboard_01{
        /** @note All 'widget(s)' terms are transformed in 'module(s)' */
        /**
         * @description Registers dashboard modules.
         */
        protected function _tp_get_dashboard_setup():string{
            $output  = "";
            $this->tp_dashboard_control_callbacks = [];
            $screen = $this->_get_current_screen();
            $response = $this->_tp_check_browser_version();
            if ( $response && $response['upgrade'] ){
                ob_start();
                $this->_add_filter( '_tp_get_dashboard_browser_nag', [$this, '_dashboard_get_browser_nag_class'] );
                $output .= ob_get_clean();
                if ($response['insecure'] ) {
                    $output .= $this->_tp_get_dashboard_module( "dashboard_browser_nag", "<h4>{$this->__( 'You are using an insecure browser!' )}</h4>", $this->_tp_get_dashboard_browser_nag() );
                } else {
                    $output .= $this->_tp_get_dashboard_module("dashboard_browser_nag", "<h4>{$this->__( 'Your browser is out of date!' )}</h4>", $this->_tp_get_dashboard_browser_nag() );
                }
            }
            $response = $this->_tp_check_php_version();
            if ( $response && isset( $response['is_acceptable'] ) && ! $response['is_acceptable'] && $this->_current_user_can( 'update_php')){
                ob_start();
                $this->_add_filter( 'postbox_classes_dashboard_dashboard_php_nag',[$this,'_tp_dashboard_get_php_nag'] );
                $output .= ob_get_clean();
                $output .= $this->_tp_get_dashboard_module("dashboard_php_nag", "<h4>{$this->__( 'PHP Update Recommended' )}</h4>",$this->_tp_get_dashboard_php_nag());
            }
            if ( $this->_current_user_can( 'view_site_health_checks' ) && ! $this->_is_network_admin() ) {
                Adm_Site_Health::get_instance();
                ob_start();
                $this->tp_enqueue_style( 'site-health' );
                $this->tp_enqueue_script( 'site-health' );
                $output .= ob_get_clean();
                $output .= $this->_tp_get_dashboard_module( "dashboard_site_health", "<h4>{$this->__( 'Site Health Status' )}</h4>", $this->_tp_get_dashboard_site_health());
            }
            if ( $this->_is_blog_admin() && $this->_current_user_can( 'edit_posts' ) ) {
                $output .= $this->_tp_get_dashboard_module( "dashboard_right_now", "<h4>{$this->__( 'At a Glance' )}</h4>",$this->_tp_get_dashboard_right_now());
            }
            if ( $this->_is_network_admin() ) {
                $output .= $this->_tp_get_dashboard_module("network_dashboard_right_now", "<h4>{$this->__( 'Right Now' )}</h4>", $this->_tp_get_network_dashboard_right_now() );
            }
            if (! $this->_is_blog_admin() ) {
                $output .= $this->_tp_get_dashboard_module("dashboard_activity", "<h4>{$this->__( 'Activity' )}</h4>", $this->_tp_get_dashboard_site_activity() );
            }
            if ( $this->_is_blog_admin() && $this->_current_user_can( $this->_get_post_type_object( 'post' )->cap->create_posts ) ) {
                $quick_draft_title = sprintf("<span class='hide-if-no-js'>%1\$s</span><span class='hide-if-js'>%2\$s</span>", $this->__( 'Quick Draft' ), $this->__( 'Your Recent Drafts' ) );
                $output .= $this->_tp_get_dashboard_module("dashboard_quick_press", "<h4>{$quick_draft_title}</h4>", $this->_tp_get_dashboard_quick_press() );
            }
            $output .= $this->_tp_get_dashboard_module( "<header class='dashboard-header event-news'><p>{$this->__('Dashboard primary:')} ", "<strong>{$this->__( 'TailoredPress Events and News' )}</strong> </p>", $this->_tp_get_dashboard_events_news() );
            if ($this->_is_network_admin() ) {
                $output .= $this->_get_action( 'tp_network_dashboard_setup' );
                $dashboard_modules = $this->_apply_filters( 'tp_network_dashboard_modules', [] );
            }elseif ($this->_is_user_admin() ) {
                $output .= $this->_get_action( 'tp_user_dashboard_setup' );
                $dashboard_modules = $this->_apply_filters( 'tp_user_dashboard_modules', [] );
            }else {
                $output .= $this->_get_action( 'tp_dashboard_setup' );
                $dashboard_modules = $this->_apply_filters( 'tp_dashboard_modules', [] );
            }
            foreach ( $dashboard_modules as $module_id ) {
                $name = empty( $this->tp_registered_modules[ $module_id ]['all_link'] ) ? $this->tp_registered_modules[ $module_id ]['name'] : $this->tp_registered_modules[ $module_id ]['name'] . " <a href='{$this->tp_registered_modules[$module_id]['all_link']}' class='edit-box open-box'>" . $this->__( 'View all' ) . '</a>';
                $output .= $this->_tp_get_dashboard_module( $module_id, $name, $this->tp_registered_modules[ $module_id ]['callback'], $this->tp_registered_modules_controls[ $module_id ]['callback'] );
            }
            static $output_post = '';
            if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['module_id'] ) ) {
                $this->_check_admin_referer( 'edit_dashboard_module_' . $_POST['module_id'], 'dashboard_module_nonce' );//redirects
                //ob_start(); // Hack - but the same hack wp-admin/widgets.php uses. fixme 'no hacks'
                $output_post .= $this->_tp_get_dashboard_trigger_module_control( $_POST['module_id'] );
                //ob_end_clean();
                $this->_tp_redirect( $this->_remove_query_arg( 'edit' ) );
                exit;
            }
            $output .= $output_post ?: '';
            $output .= $this->_get_action( 'do_meta_boxes', $screen->id, 'normal', '' );
            $output .= $this->_get_action( 'do_meta_boxes', $screen->id, 'side', '' );
            //var_dump('$output: ',$output);
            return $output;
        }//20
        /**
         * @description Adds a new dashboard module.
         * @param $module_id
         * @param $module_name
         * @param $callback
         * @param null $control_callback
         * @param null $callback_args
         * @param string $context
         * @param string $priority
         * @return mixed
         */
        protected function _tp_get_dashboard_module( $module_id, $module_name, $callback, $control_callback = null, $callback_args = null, $context = 'normal', $priority = 'core' ){
            $screen = $this->_get_current_screen();
            $private_callback_args = ['__module_basename' => $module_name];
            if ( is_null( $callback_args ) ) {
                $callback_args = $private_callback_args;
            } elseif ( is_array( $callback_args ) ) { $callback_args = array_merge( $callback_args, $private_callback_args );}
            if ( $control_callback && is_callable( $control_callback ) && $this->_current_user_can( 'edit_dashboard' ) ) {
                $this->tp_dashboard_control_callbacks[ $module_id ] = $control_callback;
                if ( isset( $_GET['edit'] ) && $module_id === $_GET['edit'] ) {
                    @list($url)    = explode( '#', $this->_add_query_arg( 'edit', false ), 2 );
                    $module_name .= "<span class='postbox-title-action'><a href='{$this->_esc_url( $url )}'>{$this->__( 'Cancel' )}</a></span>";
                    $callback     = [$this,'_tp_dashboard_control_callback'];
                } else {
                    @list($url)    = explode( '#', $this->_add_query_arg( 'edit', $module_id ), 2 );
                    $module_name .= "<span class='postbox-title-action'><a href='{$this->_esc_url( "$url#$module_id" )}' class='edit-box open-box'>{$this->__( 'Configure' )}</a></span>";
                }
            }
            $side_modules =['dashboard_quick_press', 'dashboard_primary'];
            if ( in_array( $module_id, $side_modules, true )){$context = 'side';}
            $high_priority_modules = ['dashboard_browser_nag', 'dashboard_php_nag'];
            if ( in_array( $module_id, $high_priority_modules,true)){$priority = 'high';}
            if ( empty( $context)){$context = 'normal';}
            if ( empty( $priority)){$priority = 'core';}
            return $this->_get_meta_box( $module_id, $module_name, $callback, $screen, $context, $priority, $callback_args );
        }//182
        protected function _tp_add_dashboard_module( $module_id, $module_name, $callback, $control_callback = null, $callback_args = null, $context = 'normal', $priority = 'core' ):void{
            $this->_tp_get_dashboard_module( $module_id, $module_name, $callback, $control_callback, $callback_args, $context, $priority);
        }//182
            /**
         * @description Outputs controls for the current dashboard module.
         * @param $dashboard
         * @param $meta_box
         * @return string
         */
        protected function _tp_get_dashboard_control_callback( $dashboard, $meta_box ):string{
            $output = "<section class='dashboard module control'>";
            $output.= "<form method='post' class='control'>";
            $output.= $this->_tp_get_dashboard_trigger_module_control( $meta_box['id']);
            $output.= $this->_tp_get_nonce_field('edit_dashboard_module_' . $meta_box['id'], 'dashboard_module_nonce' );
            $output.= "<input type='hidden' name='module_id' value='{$this->_esc_attr( $meta_box['id'] )}' />";
            $output.= $this->_get_submit_button($this->__( 'Save Changes' ));
            $output.= "</form></section>";
            $output .= $dashboard;
            return $output;
        }//240
        /**
         * @description Displays the dashboard.
         * @return \Closure|string
         */
        protected function _tp_get_dashboard(){
            $_screen = $this->_get_current_screen();
            $screen = null;
            if($_screen instanceof Adm_Screen ){ $screen = $_screen;} //todo might not be needed after $this->_get_current_screen() is set?
            $columns     = '';//$this->_abs_int( $screen->get_columns() );
            $columns_css = '';
            if ( $columns ) {$columns_css = " columns-$columns";}
            $output  = "<div class='dashboard-containers metabox-holder-$columns_css '>";
            $output .= "<div id='pb_container_one' class='postbox-container'>{$this->_get_meta_boxes( $screen->id, 'normal', '' )}</div>";
            $output .= "<div id='pb_container_two' class='postbox-container'>{$this->_get_meta_boxes( $screen->id, 'side', '' )}</div>";
            $output .= "<div id='pb_container_three' class='postbox-container'>{$this->_get_meta_boxes( $screen->id, 'column_3', '' )}</div>";
            $output .= "<div id='pb_container_four' class='postbox-container'>{$this->_get_meta_boxes( $screen->id, 'column_4', '' )}</div>";
            $output .= $this->_tp_get_nonce_field('closed_postboxes', 'closed_postboxes_nonce', false);
            $output .= $this->_tp_get_nonce_field('meta_box_order', 'meta_box_order_nonce', false);
            $output .= "</div>";
            return $output;
        }//254
        /**
         * @description Dashboard module that displays some basic stats about the site.
         * @return mixed
         */
        protected function _tp_get_dashboard_right_now(){
            $output  = "<main class='dashboard main'><ul><li>";
            foreach (['post', 'page'] as $post_type ) {
                $num_posts = $this->_tp_count_posts( $post_type );
                $text = null;
                if ( $num_posts && $num_posts->publish ) {
                    if ( 'post' === $post_type ) {$text = $this->_n( '%s Post', '%s Posts', $num_posts->publish );} /* translators: %s: Number of posts. */
                    else {$text = $this->_n( '%s Page', '%s Pages', $num_posts->publish );}/* translators: %s: Number of pages. */
                    $text = sprintf( $text, $this->_number_format_i18n( $num_posts->publish ) );
                    $post_type_object = $this->_get_post_type_object( $post_type );
                    if ( $post_type_object && $this->_current_user_can( $post_type_object->cap->edit_posts ) ) {
                        $output .= sprintf("<li class='%1\$s-count'><a href='edit.php?post_type=%1\$s'>%2\$s</a></li>", $post_type, $text);
                    }else{
                        $output .= sprintf("<li class='%1\$s-count'><span>%2\$s</span></li>", $post_type, $text);
                    }
                }
            }
            $num_comm = $this->_tp_count_comments();
            if ( $num_comm && ( $num_comm->approved || $num_comm->moderated ) ) {
                $text = sprintf( $this->_n( '%s Comment', '%s Comments', $num_comm->approved ), $this->_number_format_i18n( $num_comm->approved ) );
                $output .= "<li class='comment-count'><a href='edit_comments.php'>{$text}</a></li>";
                $moderated_comments_count_i18n = $this->_number_format_i18n( $num_comm->moderated );
                $text = sprintf( $this->_n( '%s Comment in moderation', '%s Comments in moderation', $num_comm->moderated ), $moderated_comments_count_i18n );
                $moderated = ! $num_comm->moderated ? ' hidden' : '';
                $output .= "<li class='comment-mod-count-$moderated'><a href='edit_comments.php?comment_status=moderated' class='comments-in-moderation-text'>{$text}</a></li>";
            }
            $elements = $this->_apply_filters( 'dashboard_glance_items',[]);
            if($elements){$output .= "<li>". implode( "</li>\n<li>", $elements) ."</li>\n";}
            $output .= "<li>";
            $output .= $this->tp_get_auto_update_message();//todo
            $output .= "</li></ul>";/* ul 1 */
            if ( ! $this->_is_network_admin() && ! $this->_is_user_admin() && $this->_current_user_can( 'manage_options' ) && ! $this->_get_option( 'blog_public' )){
                $title = $this->_apply_filters( 'privacy_on_link_title', '' );
                $content = $this->_apply_filters( 'privacy_on_link_text', $this->__( 'Search engines discouraged' ) );
                $title_attr = '' === $title ? '' : " title='$title'";
                $options_reading = "options_reading.php$title_attr";
                $output .= "<p class='search-engines-info'><a href='$options_reading'>$content</a></p>";
            }
            $output .= "</li><li>";
            $output .= $this->_get_action( 'right_now_end' );
            $output .= "</li><li>";
            $output .= $this->_get_action( 'activity_box_end' );
            $output .= "</li></ul></main>";/* dashboard main */
            return $output;
        }//295
        /**
         * $dashboard_nw_setup
         * @return mixed
         */
        protected function _tp_get_network_dashboard_right_now(){
            $actions = [];
            if ( $this->_current_user_can( 'create_sites' ) ) {
                $actions['create-site'] = "<a href='{$this->_network_admin_url( 'site_new.php' )}'>{$this->__( 'Create a New Site' )}</a>";
            }
            if ( $this->_current_user_can( 'create_users' ) ) {
                $actions['create-user'] = "<a href='{$this->_network_admin_url( 'user_new.php' )}'>{$this->__( 'Create a New User' )}</a>";
            }
            $c_users = $this->_get_user_count();
            $c_blogs = $this->_get_blog_count();
            /* translators: %s: Number of users on the network. */
            $user_text = sprintf( $this->_n( '%s user', '%s users', $c_users ), $this->_number_format_i18n( $c_users ) );
            /* translators: %s: Number of sites on the network. */
            $blog_text = sprintf( $this->_n( '%s site', '%s sites', $c_blogs ), $this->_number_format_i18n( $c_blogs ) );
            $sentence = sprintf( $this->__( 'You have %1$s and %2$s.' ), $blog_text, $user_text );
            $output  = "<main class='dashboard module network'>";
            if ($actions ) {
                $output .= "<div class='actions block'>";
                $output .= "<ul class='sub-sub-sub'>";
                foreach ( $actions as $class => $action ) {
                    $actions[ $class ] = "\t<li class='$class'>$action";
                }
                $output .= implode( " |</li>\n", $actions ) . "</li>\n";
                $output .= "</ul></div";
            }
            $output .= "<div class='block one'><p class='you-have'>{$sentence}</p>";
            $output .= $this->_do_action( 'tp_mu_admin_result'); /** @NOTE methods passed here must be returned*/
            $output .= "</div><div class='block two'>";
            $output .= "<form action='{$this->_esc_url( $this->_network_admin_url( 'users.php' ))}' method='get'>";
            $output .= "<ul><li><dt><label for='search_users' class='screen-reader-text'>{$this->__('Search Users')}</label></dt>";
            $output .= "<dd><input type='search' name='s' value='' size='30' autocomplete='off' id='search_users' /></dd>";
            $output .= "</li><li><dd>{$this->_get_submit_button($this->__( 'Search Users' ),'',false,false,['id' => 'submit_users'])}</dd>";
            $output .= "</li></ul></form></div></div><div class='block three'>";
            $output .= "<form action='{$this->_esc_url( $this->_network_admin_url( 'sites.php' ))}' method='get'>";
            $output .= "<ul><li><dt><label for='search_sites' class='screen-reader-text'>{$this->__('Search Sites')}</label></dt>";
            $output .= "<dd><input type='search' name='s' value='' size='30' autocomplete='off' id='search_sites' /></dd>";
            $output .= "</li><li><dd>{$this->_get_submit_button($this->__( 'Search Sites' ),'',false,false,['id' => 'submit_sites'])}</dd>";
            $output .= "</li></ul></form></div><div class='block four' >";
            $output .= $this->_do_action( 'mu_right_now_end' );/** @NOTE methods passed here must be returned*/
            $output .= $this->_do_action( 'mu_activity_box_end' );/** @NOTE methods passed here must be returned*/
            $output .= "</div></main>";
            return $output;
        }//442
        /**
         * @description The Quick Draft module display and creation of drafts.
         * @param bool $error_msg
         * @return mixed
         */
        protected function _tp_get_dashboard_quick_press( $error_msg = false ){
            if ( ! $this->_current_user_can( 'edit_posts' ) ) {return false;}
            $last_post_id = (int) $this->_get_user_option( 'dashboard_quick_press_last_post_id' );
            if($last_post_id){
                $post = $this->_get_post( $last_post_id );
                if ( empty( $post ) || 'auto-draft' !== $post->post_status ) {
                    $post = $this->_get_default_post_to_edit( 'post', true );
                }else{ $post->post_title = '';}
            }else{
                $post    = $this->_get_default_post_to_edit( 'post', true );
                $user_id = $this->_get_current_user_id();
                if (array_key_exists($this->_get_current_blog_id(), $this->_get_blogs_of_user($user_id))) {
                    $this->_update_user_option( $user_id, 'dashboard_quick_press_last_post_id', (int) $post->ID ); // Save post_ID.
                }
            }
            $post_ID = (int) $post->ID;
            $output  = "<section class='dashboard module quick-press'>";
            $output .= "<div class='block one'><form name='post' id='quick_press' class='initial-form hide-if-no-js' action='{$this->_esc_url( $this->_admin_url( 'post.php' ))}' method='post'>";
            $output .= "<ul>";
            if ( $error_msg ){$output .= "<li>{$error_msg}</li>";}
            $output .= "<li id='title_wrap' class='input-text-wrap'>";
            $output .= "<dt><label for='title'>{$this->_apply_filters( 'enter_title_here', $this->__( 'Title' ), $post )}</label></dt>";
            $output .= "<dd><input type='text' id='title' name='post_title' autocomplete='off'/></dd>";
            $output .= "</li><li id='description_wrap' class='textarea-wrap'>";
            $output .= "<dt><label for='content'>{$this->__('Content')}</label></dt>";
            $output .= "<dd><textarea id='content' class='mce-editor' rows='3' cols='15' name='content' placeholder='{$this->_esc_attr('What\'s on your mind?')}' autocomplete='off'></textarea></dd>";
            $output .= "</li><li class='submit'>";
            $output .= "<input type='hidden' id='quick_post_action' name='action' value='post-quick-draft-save' />";
            $output .= "<input type='hidden' name='post_ID' value='{$post_ID}' />";
            $output .= "<input type='hidden' name='post_type' value='post' />";
            $output .= $this->_tp_get_nonce_field('add_post');
            $output .= $this->_get_submit_button($this->__('Save Draft'), 'primary', 'save', false, ['id' => 'save-post']);
            $output .= "</li></ul></form></div><div class='block two'>";
            $output .= $this->_tp_get_dashboard_recent_drafts();
            $output .= "</div></section>";
            return $output;
        }//527
        /**
         * @description Show recent drafts of the user on the dashboard.
         * @param mixed $drafts
         * @return mixed
         */
        protected function _tp_get_dashboard_recent_drafts($drafts = false ){
            if ( ! $drafts ) {
                $query_args = ['post_type' => 'post','post_status' => 'draft','author' => $this->_get_current_user_id(),
                    'posts_per_page' => 4,'orderby' => 'modified','order' => 'DESC',];
                $query_args = $this->_apply_filters( 'dashboard_recent_drafts_query_args', $query_args );
                $drafts = $this->_get_posts( $query_args );
                if ( ! $drafts ) {return false;}
            }
            $output  = "<section class='dashboard module recent-drafts'><div class='block one'>";
            if (count($drafts) > 3) {
                $output .= sprintf("<p class='view-all'><a href='%s'>%s</a></p>\n",
                    $this->_esc_url($this->_admin_url( 'edit.php?post_status=draft' ) ),$this->__( 'View all drafts' ));
            }
            $output .= "<h3 class='hide-if-no-js'>{$this->__( 'Your Recent Drafts' )}</h3>\n";
            $output .= "</div><div class='block two'><ul>";
            $draft_length = (int) $this->_x( '10', 'draft_length' );
            $drafts = array_slice( $drafts, 0, 3 );
            foreach ( $drafts as $draft ) {
                $url   = $this->_get_edit_post_link( $draft->ID );
                $title = $this->_draft_or_post_title( $draft->ID );
                $output .= "<li>\n";
                $output .= sprintf("<div class='draft-title'><a href='%s' aria-label='%s'>%s</a><time datetime='%s'>%s</time></div>",
                    $this->_esc_url( $url ),$this->_esc_attr( sprintf( $this->__( 'Edit &#8220;%s&#8221;' ), $title ) ),
                    $this->_esc_html( $title ),$this->_get_the_time( 'c', $draft ),$this->_get_the_time($this->__( 'F j, Y' ), $draft));
                $the_content = $this->_tp_trim_words( $draft->post_content, $draft_length );
                if ( $the_content){$output .= "<p>{$the_content}</p>";}
                $output .= "</li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div></section>";
            return $output;
        }//601
        /**
         * @description Outputs a row for the Recent Comments module.
         * @param object|string $comment
         * @param bool $show_date
         * @return mixed
         */
        protected function _tp_dashboard_get_recent_comments_row(&$comment, $show_date = true ){
            //todo
            $this->tp_comment = clone $comment;
            if ( $comment->comment_post_ID > 0 ) {
                $comment_post_title = $this->_draft_or_post_title( $comment->comment_post_ID );
                $comment_post_url   = $this->_get_the_permalink( $comment->comment_post_ID );
                $comment_post_link  = "<a href='$comment_post_url'>$comment_post_title</a>";
            } else {$comment_post_link = '';}
            $actions_string = '';
            if ( $this->_current_user_can( 'edit_comment', $comment->comment_ID ) ) {
                $actions = ['approve'   => '','unapprove' => '','reply' => '','edit' => '','spam' => '','trash' => '','delete' => '','view' => '',];
                $del_nonce     = $this->_esc_html( '_tp_nonce=' . $this->_tp_create_nonce( "delete_comment_$comment->comment_ID" ) );
                $approve_nonce = $this->_esc_html( '_tp_nonce=' . $this->_tp_create_nonce( "approve_comment_$comment->comment_ID" ) );
                $approve_url   = $this->_esc_url( "comment.php?action=approve_comment&p=$comment->comment_post_ID&c=$comment->comment_ID&$approve_nonce" );
                $unapprove_url = $this->_esc_url( "comment.php?action=unapprove_comment&p=$comment->comment_post_ID&c=$comment->comment_ID&$approve_nonce" );
                $spam_url      = $this->_esc_url( "comment.php?action=spam_comment&p=$comment->comment_post_ID&c=$comment->comment_ID&$del_nonce" );
                $trash_url     = $this->_esc_url( "comment.php?action=trash_comment&p=$comment->comment_post_ID&c=$comment->comment_ID&$del_nonce" );
                $delete_url    = $this->_esc_url( "comment.php?action=delete_comment&p=$comment->comment_post_ID&c=$comment->comment_ID&$del_nonce" );
                $actions['approve'] = sprintf("<a href='%s' data-tp_lists='%s' class='vim-a aria-button-if-js' aria-label='%s'>%s</a>",
                    $approve_url,"dim:the_comment_list:comment-{$comment->comment_ID}:unapproved:e7e7d3:e7e7d3:new=approved",
                    $this->_esc_attr__( 'Approve this comment' ),$this->__( 'Approve' ));
                $actions['unapprove'] = sprintf("<a href='%s' data-tp_lists='%s' class='vim-a aria-button-if-js' aria-label='%s'>%s</a>",
                    $unapprove_url,"dim:the-comment-list:comment-{$comment->comment_ID}:unapproved:e7e7d3:e7e7d3:new=unapproved",
                    $this->_esc_attr__( 'Unapprove this comment' ),$this->__( 'Unapprove' ));
                $actions['edit'] = sprintf("<a href='%s' aria-label='%s'>%s</a>","comment.php?action=editcomment&amp;c={$comment->comment_ID}",
                    $this->_esc_attr__( 'Edit this comment' ),$this->__( 'Edit' ));
                $actions['reply'] = sprintf("<button onclick='" . "window.commentReply && commentReply.open(\'%s\',\'%s\');" . "' class='vim-r button-link hide-if-no-js' aria-label='%s'>%s</button>",
                    $comment->comment_ID,$comment->comment_post_ID, $this->_esc_attr__( 'Reply to this comment' ),$this->__( 'Reply' ));
                $actions['spam'] = sprintf("<a href='%s' data-tp_lists='%s' class='vim-s vim-destructive aria-button-if-js' aria-label='%s'>%s</a>",
                    $spam_url,"delete:the-comment-list:comment-{$comment->comment_ID}::spam=1",$this->_esc_attr__( 'Mark this comment as spam' ),$this->_x( 'Spam', 'verb' ));
                if (!EMPTY_TRASH_DAYS ){
                    $actions['delete'] = sprintf("<a href='%s' data-tp_lists='%s' class='vim-s vim-destructive aria-button-if-js' aria-label='%s'>%s</a>",
                        $delete_url,"delete:the-comment-list:comment-{$comment->comment_ID}::trash=1", $this->_esc_attr__( 'Delete this comment permanently'), $this->__( 'Delete Permanently' ));
                }else{
                    $actions['trash'] = sprintf("<a href='%s' data-tp_lists='%s' class='vim-s vim-destructive aria-button-if-js' aria-label='%s'>%s</a>",
                        $trash_url,"delete:the-comment-list:comment-{$comment->comment_ID}::trash=1",$this->_esc_attr__( 'Move this comment to the Trash' ), $this->_x( 'Trash', 'verb' ));
                }
                $actions['view'] = sprintf("<a class='comment-link' href='%s' aria-label='%s'>%s</a>", $this->_esc_url( $this->_get_comment_link( $comment ) ),$this->_esc_attr__( 'View this comment' ),$this->__( 'View' ));
                $actions = $this->_apply_filters( 'comment_row_actions', array_filter( $actions ), $comment );
                $i = 0;
                foreach ( $actions as $action => $link ) {
                    ++$i;
                    if ((('approve' === $action || 'unapprove' === $action) && 2 === $i)|| 1 === $i ){$sep = '';}
                    else {$sep = ' | '; }
                    if ( 'reply' === $action || 'quick-edit' === $action ) { $action .= ' hide-if-no-js';}
                    if ( 'view' === $action && '1' !== $comment->comment_approved ) { $action .= ' hidden';}
                    $actions_string .= "<span class='$action'>$sep$link</span>";
                }
            }
            $_comment_class= $this->_comment_class( array( 'comment-item', $this->_tp_get_comment_status( $comment ) ), $comment );
            $comment_row_class = '';
            $output = "<li id='{$comment->comment_ID}' $_comment_class>";
            if ($this->_get_option( 'show_avatars' ) ) {
                $output .=$this->_get_avatar( $comment, 50, 'mystery' );
                $comment_row_class .= 'has-avatar';
            }
            if ( ! $comment->comment_type || 'comment' === $comment->comment_type ){
                $output .= "<div class='block one comment has-row-actions $comment_row_class'>";
                $output .= "<p class='comment-meta'>";
                if ( $comment_post_link ) {
                    $output .= sprintf($this->__('From %1$s on %2$s %3$s'),
                        "<cite class='comment-author'>{(new self)->_get_comment_author_link( $comment )}</cite>",
                        $comment_post_link,"<span class='approve'>{$this->__( '[Pending]' )}</span>");
                }else{
                    $output .= sprintf($this->__('From %1$s %2$s'),"<cite class='comment-author'>{$this->_get_comment_author_link( $comment )}</cite>","<span class='approve'>{$this->__( '[Pending]' )}</span>");
                }
                $output .= "</p>";
            }else{
                switch ( $comment->comment_type ) {
                    case 'pingback':
                        $type = $this->__( 'Pingback' );
                        break;
                    case 'trackback':
                        $type = $this->__( 'Trackback' );
                        break;
                    default:
                        $type = ucwords( $comment->comment_type );
                }
                $type = $this->_esc_html( $type );
                $output .= "<div class='block two comment has-row-actions'>";
                $output .= "<p class='comment-meta'>";
                if ( $comment_post_link ) {
                    $output .= sprintf($this->_x( '%1$s on %2$s %3$s','dashboard'),"<strong>$type</strong>",$comment_post_link,"<span class='approve'>{$this->__( '[Pending]' )}</span>");
                }else{
                    $output .= sprintf($this->_x( '%1$s on %2$s','dashboard'),"<strong>$type</strong>","<span class='approve'>{$this->__( '[Pending]' )}</span>");
                }
                $output .= "</p><p class='comment-author'>{comment_author_link( $comment )}</p>";
            }
            $output .= "<blockquote><p>{$this->_comment_excerpt( $comment )}</p></blockquote>";
            $_show_date = null;
            if($show_date === true){
                $_show_date = sprintf("<time datetime='%s'>%s</time>",$this->_get_the_time( 'c'),$this->_get_the_time($this->__( 'F j, Y' )));
            }
            if ( $actions_string ){
                $output .= "<p class='row-actions'>{$actions_string}{$_show_date}</p>"; //todo move '$_show_date' this to where it suits best
            }
            $output .= "</div></li>";//block one comment
            return $output;
        }//682
        /**
         * @description Callback function for Activity module.
         * @return string
         */
        protected function _tp_get_dashboard_site_activity():string{
            $output = "<section class='dashboard module activity'>";
            $future_posts = $this->_tp_get_dashboard_recent_posts(['max' => 5,'status' => 'future','order' => 'ASC','title' => $this->__( 'Publishing Soon' ),'id' => 'future-posts',]);
            $recent_posts = $this->_tp_get_dashboard_recent_posts(['max' => 5,'status' => 'publish','order' => 'DESC','title' => $this->__( 'Recently Published' ),'id' => 'published-posts',]);
            $recent_comments = $this->_tp_get_dashboard_recent_comments();
            if ( ! $future_posts && ! $recent_posts && ! $recent_comments ) {
                $output .= "<div class='no-activity'><p>{$this->__( 'No activity yet!' )}</p></div>";
            }else{
                $output .= $future_posts;
                $output .= $recent_posts;
                $output .= $recent_comments;
            }
            $output .= "</section>";
            return $output;
        }//911
    }
}else{die;}