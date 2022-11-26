<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 27-8-2022
 * Time: 22:27
 */
namespace TP_Admin\Traits\AdminDashboard;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Options\_option_03;
if(ABSPATH){
    trait _adm_dashboard_03{
        use _init_error;
        use _option_03;
        /**
         * @description Adds an additional class to the browser nag if the current version is insecure.
         * @param array $classes
         * @return array
         */
        public function tp_get_dashboard_browser_nag_class( $classes ):array{
            $response = $this->_tp_check_browser_version();
            if ( $response && $response['insecure']){$classes[] = 'browser-insecure';}
            return $classes;
        }//1745
        /**
         * @description Checks if the user needs a browser update.
         * @return bool|mixed
         */
        protected function _tp_check_browser_version(){
            if (empty( $_SERVER['HTTP_USER_AGENT'])){ return false;}
            $key = md5( $_SERVER['HTTP_USER_AGENT'] );
            $response = $this->_get_site_transient( 'browser_' . $key );
            if ( false !== $response ) {
                $url     = 'http://api.wordpress.org/core/browse-happy/1.1/';
                $options = ['body' => ['useragent' => $_SERVER['HTTP_USER_AGENT']],
                    'user-agent' => 'TailoredPress/' . TP_VERSION . '; ' . $this->_home_url( '/' )];
                if ( $this->_tp_http_supports(['ssl'])){ $url = $this->_set_url_scheme( $url, 'https' );}
                $response = $this->_tp_remote_post( $url, $options );
                if ( $this->_init_error( $response ) || 200 !== $this->_tp_remote_retrieve_response_code( $response ) ) {
                    return false;
                }
                $response = json_decode( $this->_tp_remote_retrieve_body( $response ), true );
                if ( ! is_array( $response )){return false;}
                $this->_set_site_transient( 'browser_' . $key, $response, WEEK_IN_SECONDS );
            }
            return $response;
        }//1762
        /**
         * @description Displays the PHP update nag.
         * @return string|void
         */
        protected function _tp_get_dashboard_php_nag(){
            $response = $this->_tp_check_php_version();
            if(!$response){ return;}
            if ( isset( $response['is_secure'] ) && ! $response['is_secure'] ) {
                $msg = sprintf( $this->__( 'Your site is running an insecure version of PHP (%s), which should be updated.' ),
                    PHP_VERSION);/* translators: %s: The server PHP version. */
            } else {
                $msg = sprintf($this->__( 'Your site is running an outdated version of PHP (%s), which should be updated.' ),
                    PHP_VERSION);/* translators: %s: The server PHP version. */
            }
            $output = "<section class='dashboard module php-nag'>";
            $output .= "<p>{$msg}</p>";
            $output .= "<h3>{$this->__( 'What is PHP and how does it affect my site?' )}</h3>";
            $output .= "<p>";
            $output .= sprintf($this->__( 'PHP is the programming language used to build and maintain WordPress. Newer versions of PHP are created with increased performance in mind, so you may see a positive effect on your site&#8217;s performance. The minimum recommended version of PHP is %s.' ),$response ? $response['recommended_version'] : '');
            $output .= "</p><p class='button-container'>";
            $output .= sprintf("<a href='%1\$s' class='button button-primary' target='_blank' rel='noopener'>%2\$s <span class='screen-reader-text'>%3\$s</span><span aria-hidden='true' class='dashicons dashicons-external'></span></a>",
                $this->_esc_url( $this->_tp_get_update_php_url() ), $this->__( 'Learn more about updating PHP' ),$this->__( '(opens in a new tab)' ));/* translators: Accessibility text. */
            $output .= "</p>";
            $output .= $this->_tp_get_update_php_annotation();
            $output .= $this->_tp_get_direct_php_update_button();
            $output .= "</section>";
            return $output;

        }//1820
        /**
         * @description Adds an additional class to the PHP nag if the current version is insecure.
         * @param $classes
         * @return string
         */
        protected function _dashboard_get_php_nag_class( $classes ):string{
            $response = $this->_tp_check_php_version();
            if($response && isset($response['is_secure'])&& !$response['is_secure']){$classes[] = 'php-insecure';}
            return $classes;
        }//1879
        /**
         * @description Displays the Site Health Status widget.
         * @return mixed
         */
        protected function _tp_get_dashboard_site_health(){
            $get_issues = $this->_get_transient( 'health-check-site-status-result' );
            $issue_counts = [];
            if ( false !== $get_issues ) { $issue_counts = json_decode( $get_issues, true );}
            if ( ! is_array( $issue_counts ) || ! $issue_counts ) {
                $issue_counts = ['good' => 0,'recommended' => 0,'critical' => 0,];
            }
            $issues_total = $issue_counts['recommended'] + $issue_counts['critical'];
            $output  = "<section class='dashboard module site-health-check'>";
            $output .= "<div class='title-section progress-wrapper loading hide-if-no-js'>";
            $output .= "<div class=site-health-progress>";
            $output .= "<svg role='img' aria-hidden='true' focusable='false' width='100%' height='100%' viewBox='0 0 200 200'>";
            $output .= "<circle r='90' cx='100' cy='100' fill='transparent' stroke-dasharray='565.48' stroke-dashoffset='0'></circle>";
            $output .= "<circle id=\"bar\" r='90' cx='100' cy='100' fill='transparent' stroke-dasharray='565.48' stroke-dashoffset='0'></circle>";
            $output .= "'</svg></div><div class='site-health-progress-label'>";
            if ( false === $get_issues ){$output .= $this->__('No information yet&hellip;');
            }else{$output .= $this->__('Results are still loading&hellip;');}
            $output .= "</div></div><div class='site-health-details'>";
            if ( false === $get_issues ){
                $output .= "<p>";
                $output .= sprintf($this->__('Site health checks will automatically run periodically to gather information about your site. You can also <a href="%s">visit the Site Health screen</a> to gather information about your site now.'),
                    $this->_esc_url($this->_admin_url( 'site-health.php' ) ));
                $output .= "</p>";
            }else{
                $output .= "<p>";
                if ( $issues_total <= 0 ){
                    $output .= $this->__('Great job! Your site currently passes all site health checks.');
                }elseif ( 1 === (int) $issue_counts['critical'] ){
                    $output .= $this->__('Your site has a critical issue that should be addressed as soon as possible to improve its performance and security.');
                }elseif ( $issue_counts['critical'] > 1 ){
                    $output .= $this->__('Your site has critical issues that should be addressed as soon as possible to improve its performance and security.');
                }elseif ( 1 === (int) $issue_counts['recommended'] ){
                    $output .= $this->__('Your site&#8217;s health is looking good, but there is still one thing you can do to improve its performance and security.');
                }else{
                    $output .= $this->__('Your site&#8217;s health is looking good, but there are still some things you can do to improve its performance and security.');
                }
                $output .= "</p>";
            }
            if ( $issues_total > 0 && false !== $get_issues ){
                $output .= "<p>";
                $output .= sprintf($this->_n('Take a look at the <strong>%1$d item</strong> on the <a href="%2$s">Site Health screen</a>.',
                    'Take a look at the <strong>%1$d items</strong> on the <a href="%2$s">Site Health screen</a>.',
                    $issues_total),$issues_total,$this->_esc_url( $this->_admin_url( 'site_health.php' ) ));
                $output .= "</p>";
            }
            $output .= "</div>";

            return $output;
        }//1894
        //todo @description Displays a welcome panel to introduce users to TailoredPress.
        protected function _tp_welcome_panel():string{return 'Todo, is for later';}//1992
    }
}else{die;}

