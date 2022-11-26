<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-8-2022
 * Time: 22:27
 */
namespace TP_Admin\Traits\AdminDashboard;
use TP_Admin\Traits\_adm_list_block;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _adm_dashboard_02{
        /** @note All 'widget(s)' terms are transformed in 'module(s)' */
        use _init_queries;
        use _adm_list_block;
        /**
         * @description Generates Publishing Soon and Recently Published sections.
         * @param array ...$args
         * @return mixed
         */
        protected function _tp_get_dashboard_recent_posts( ...$args ){
            $query_args = ['post_type' => 'post','post_status' => $args['status'],'orderby' => 'date','order' => $args['order'],
                'posts_per_page' => (int) $args['max'],'no_found_rows' => true,'cache_results' => false,
                'perm' => ( 'future' === $args['status'] ) ? 'editable' : 'readable',];
            $query_args = $this->_apply_filters( 'dashboard_recent_posts_query_args', $query_args );
            $posts = $this->_init_query($query_args);
            $today    = $this->_current_time( 'Y-m-d' );
            $_tomorrow = $this->_current_datetime();
            $tomorrow = null;
            if( $_tomorrow instanceof \DateTimeImmutable ){
                $tomorrow = $_tomorrow->modify( '+1 day' )->format( 'Y-m-d' );
            }
            $year     = $this->_current_time( 'Y' );
            $time = $this->_get_the_time( 'U' );
            $output  = "";
            if ($posts->have_posts() ) {
                $output .= "<section id='{$args['id']}' class='dashboard module recent-posts'>";
                $output .= "<h3>{$args['title']}</h3><ul>";
                while ( $posts->have_posts() ) {
                    $posts->the_post();
                    if ( gmdate( 'Y-m-d', $time ) === $today ) {
                        $relative = $this->__( 'Today' );
                    } elseif ( gmdate( 'Y-m-d', $time ) === $tomorrow ) {
                        $relative = $this->__( 'Tomorrow' );
                    } elseif ( gmdate( 'Y', $time ) !== $year ) {
                        /* translators: Date and time format for recent posts on the dashboard, from a different calendar year, see https://www.php.net/manual/datetime.format.php */
                        $relative = $this->_date_i18n($this->__( 'M jS Y' ), $time );
                    } else {
                        /* translators: Date and time format for recent posts on the dashboard, see https://www.php.net/manual/datetime.format.php */
                        $relative =$this->_date_i18n($this->__( 'M jS' ), $time );
                    }
                    $recent_post_link =$this->_current_user_can( 'edit_post',$this->_get_the_ID() ) ? $this->_get_edit_post_link() : $this->_get_permalink();
                    $draft_or_post_title = $this->_draft_or_post_title();
                    $output .= sprintf("<li><span>%1\$s</span><a href='%2\$s' aria-label='%3\$s'>%4\$s</a></li>",
                        sprintf($this->_x( '%1$s, %2$s', 'dashboard' ), $relative, $this->_get_the_time()),
                        $recent_post_link,$this->_esc_attr( sprintf( $this->__( 'Edit &#8220;%s&#8221;' ), $draft_or_post_title )),$draft_or_post_title);
                }
                $output .= "</ul></section>";

            }
            return $output;
        }//961
        /**
         * @description Show Comments section.
         * @param int $total_items
         * @return mixed
         */
        protected function _tp_get_dashboard_recent_comments( $total_items = 5 ){
            $comments = [];
            $comments_query = ['number' => $total_items * 5,'offset' => 0,];
            if ( ! $this->_current_user_can( 'edit_posts' ) ) {
                $comments_query['status'] = 'approve';
            }
            while ( count( $comments ) < $total_items && $possible = $this->_get_comments( $comments_query ) ) {
                if(!is_array( $possible)){break;}
                foreach ( $possible as $comment ) {
                    if ( ! $this->_current_user_can( 'read_post', $comment->comment_post_ID )){continue; }
                    $comments[] = $comment;
                    if ( count( $comments ) === $total_items ) { break 2;}
                }
                $comments_query['offset'] += $comments_query['number'];
                $comments_query['number']  = $total_items * 10;
            }
            $output  = "";
            if (! $comments ) {
                $output .="<section id='latest_comments' class='dashboard module recent-comments'>";
                $output .= "<h3>{$this->__('Recent Comments')}</h3><ul>";
                $output.="<ul id='the_comment_list' data-tp_lists='list:comment'>";
                foreach ( $comments as $comment ) {
                    $output.= $this->_tp_dashboard_get_recent_comments_row( $comment );
                }
                $output.="</ul>";
                if ( $this->_current_user_can( 'edit_posts' ) ) {
                    $output.="<h3 class='screen-reader-text'>{$this->__( 'View more comments' )}</h3>";
                    $this->_get_list_block( 'TP_Comments_List_Block' );//todo ->views()
                }
                $output.= $this->_tp_get_comment_reply( -1, false, 'dashboard');
                $output.= $this->_tp_get_comment_trash_notice();
                $output.="</section>";
            }
            return $output;
        }//1048
        /**
         * @description Calls module control callback.
         * @param bool $module_control_id
         * @return mixed
         */
        protected function _tp_get_dashboard_trigger_module_control( $module_control_id = false ){
            $output  = "";
            $dashboard_control = null;
            $module_control = null;
            if ( is_scalar( $this->tp_dashboard_control_callbacks ) && $module_control_id
                && isset( $this->tp_dashboard_control_callbacks[ $module_control_id ] )
                && is_callable( $this->tp_dashboard_control_callbacks[ $module_control_id ] )
            ) {
                $dashboard_control = $this->tp_dashboard_control_callbacks[ $module_control_id ];
                $module_control = ['id'=> $module_control_id,'callback' => $this->tp_dashboard_control_callbacks[ $module_control_id ],];
                $output .= $dashboard_control($module_control);
            }
            return $output;
        }//1192
        protected function _tp_dashboard_trigger_module_control( $module_control_id = false ):void{
            echo $this->_tp_get_dashboard_trigger_module_control( $module_control_id);
        }//1192
        protected function _tp_get_dashboard_events_news():string{
            $output  = "<div class='dashboard-content event-news'>";
            $output .= "<p>{$this->__('As this is built by a single person.')}</p>";
            $output .= "<p>{$this->__('Events and the like are out of scope!')}</p>";
            $output .= "<p>{$this->__('')}</p>";
            $output .= "</div>";
            return $output;
        }//1269
        protected function _tp_get_community_events_markup():string{
            $output  = "<div class='dashboard-content event-markup'>";
            $output .= "<p>{$this->__('As this is built by a single person.')}</p>";
            $output .= "<p>{$this->__('Events and the like are out of scope!')}</p>";
            $output .= "</div>";
            return $output;
        }//1323
        /**
         * @description Displays file upload quota on dashboard.
         * @return bool|\Closure
         */
        protected function _tp_get_dashboard_quota(){
            if ( ! $this->_is_multisite() || ! $this->_current_user_can( 'upload_files') || $this->_get_site_option( 'upload_space_check_disabled' )){
                return true;
            }
            $quota = $this->_get_space_allowed();
            $used  = $this->_get_space_used();
            if ( $used > $quota ) { $percent_used = '100';}
            else {$percent_used = ( $used / $quota ) * 100;}
            $used_class  = ( $percent_used >= 70 ) ? ' warning' : '';
            $used        = round( $used, 2 );
            $percent_used = number_format( $percent_used );
            $output  = "<section class='dashboard module quota'>";
            $output .= "<h3 class='mu-storage'>{$this->__('Storage Space')}</h3>";
            $output .= "<div class='mu-storage'><ul>";
            $output .= "<li class='storage-count'>";
            $text = sprintf($this->__( '%s MB Space Allowed' ), $this->_number_format_i18n( $quota ));
            $output .= sprintf("<a href='%1\$s'>%2\$s <span class='screen-reader-text'>(%3\$s)</span></a>",
                $this->_esc_url($this->_admin_url('upload.php')),$text,$this->__( 'Manage Uploads' ));
            $output .= "</li>";
            $output .= "<li class='storage-count $used_class'>";
            $text = sprintf($this->__( '%1$s MB (%2$s%%) Space Used' ),$this->_number_format_i18n($used,2),$percent_used);
            $output .= sprintf("<a href='%1\$s' class='mu-sub-link'>%2\$s <span class='screen-reader-text'>(%3\$s)</span></a>",
                $this->_esc_url($this->_admin_url('upload.php')),$text,$this->__('Manage Uploads'));
            $output .= "</li></ul></div></section>";
            return $output;
        }//1599
        /**
         * @description Displays the browser update nag.
         * @return mixed
         */
        protected function _tp_get_dashboard_browser_nag(){
            $notice   = '';
            $response = $this->_tp_check_browser_version();
            if (!$response ) {
                $notice   .= "<section class='dashboard module browser-nag'>";//todo implement ul/li
                if ($response['insecure'] ) {
                    $msg = sprintf($this->__( "It looks like you're using an insecure version of %s. Using an outdated browser makes your computer unsafe. For the best TailoredPress experience, please update your browser."),
                        sprintf("<a href='%s'>%s</a>",$this->_esc_url( $response['update_url'] ), $this->_esc_html( $response['name'] )));
                }else{
                    $msg = sprintf($this->__( "It looks like you're using an old version of %s. For the best TailoredPress experience, please update your browser."),
                        sprintf("<a href='%s'>%s</a>",$this->_esc_url( $response['update_url'] ), $this->_esc_html( $response['name'] )));
                }
                $browser_nag_class = '';
                if ( ! empty( $response['img_src'] ) ) {
                    $img_src = ( $this->_is_ssl() && ! empty( $response['img_src_ssl'] ) ) ? $response['img_src_ssl'] : $response['img_src'];
                    $notice .= "<div class='align-right browser-icon'><img src='{$this->_esc_attr( $img_src )}' /></div>";
                    $browser_nag_class = ' has-browser-icon';
                }
                $notice .= "<p class='browser-update-nag{$browser_nag_class}'>{$msg}</p>";
                $browse_happy = 'https://browsehappy.com/';
                $locale      = $this->_get_user_locale();
                if ( 'en_US' !== $locale ) {
                    $browse_happy = $this->_add_query_arg( 'locale', $locale, $browse_happy );
                }
                $msg_browsehappy = sprintf($this->__("<a href='%1\$s' class='update-browser-link'>Update %2\$s</a> or learn how to <a href='%3\$s' class='browse-happy-link'>browse happy</a>"),
                    $this->_esc_attr( $response['update_url'] ),$this->_esc_html( $response['name'] ),$this->_esc_url( $browse_happy ));/* translators: 1: Browser update URL, 2: Browser name, 3: Browse Happy URL. */
                $notice .= "<p>{$msg_browsehappy}</p>";
                $notice .= "<p class='hide-if-no-js'><a href='' class='dismiss' aria-label='{$this->_esc_attr__( 'Dismiss the browser warning panel' )}'></a></p>";
                $notice .= "</section>";
            }
            return $this->_apply_filters( 'browse-happy-notice', $notice, $response );

        }//1666

    }
}else{die;}