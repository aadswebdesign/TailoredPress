<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 14-10-2022
 * Time: 19:05
 */
namespace TP_Admin\Libs;
use TP_Admin\Traits\AdminMisc\_misc_02;
use TP_Core\Libs\Post\TP_Post;
use TP_Admin\Traits\_adm_screen;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Constructs\_construct_post;
use TP_Core\Traits\Formats\_formats_03;
use TP_Core\Traits\Formats\_formats_07;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Inits\_init_post;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Methods\_methods_09;
use TP_Core\Traits\Misc\tp_script;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Post\_post_04;
use TP_Core\Traits\Post\_post_05;
use TP_Core\Traits\Templates\_general_template_02;
use TP_Core\Traits\Templates\_link_template_09;
if(ABSPATH){
    final class Adm_Privacy_Policy_Content{
        use _action_01, _capability_01, _formats_03, _formats_07,_formats_08, _general_template_02;
        use _I10n_01, _init_post, _link_template_09, _methods_01, _methods_09, _misc_02, _option_01;
        use _post_01, _post_04,_post_05, _construct_post, _adm_screen, tp_script;
        private static $__policy_content = [];
        private function __construct() {}
        public static function add( $library_name, $policy_text ):void {
            if ( empty( $library_name ) || empty( $policy_text ) ) { return; }
            $data = ['library_name' => $library_name,'policy_text' => $policy_text,];
            if ( ! in_array( $data, self::$__policy_content, true ) ) {
                self::$__policy_content[] = $data;
            }
        }//35
        public static function text_change_check():bool {
            $policy_page_id = (int) (new static)->_get_option( 'tp_page_for_privacy_policy' );
            if ( empty( $policy_page_id ) ) { return false;}
            if ( ! (new static)->_current_user_can( 'edit_post', $policy_page_id ) ) {
                return false;
            }
            $old = (array) (new static)->_get_post_meta( $policy_page_id, '_tp_suggested_privacy_policy_content' );
            if ( empty( $old ) ) { return false;}
            $cached = (new static)->_get_option( '_tp_suggested_policy_text_has_changed' );
            if ( ! (new static)->_did_action( 'admin_init' ) ) { return 'changed' === $cached;}
            $new = self::$__policy_content;
            foreach ( $old as $key => $data ) {
                if ( ! is_array( $data ) || ! empty( $data['removed'] ) ) {
                    unset( $old[ $key ] );
                    continue;
                }
                $old[ $key ] = ['library_name' => $data['library_name'],'policy_text' => $data['policy_text'],];
            }
            sort( $old );
            sort( $new );
            if ( $new !== $old ) {
                (new static)->_add_action( 'admin_notices', array( 'TP_Privacy_Policy_Content', 'policy_text_changed_notice' ) );
                $state = 'changed';
            } else { $state = 'not-changed';}
            if ( $cached !== $state ) {
                (new static)->_update_option( '_tp_suggested_policy_text_has_changed', $state );
            }
            return 'changed' === $state;
        }//55
        public static function get_policy_text_changed_notice():string {
            (new static)->_init_post();
            $screen = (new static)->_get_current_screen()->id;
            if ( 'privacy' !== $screen ) { return;}
            $output  = "<div class='policy-text-updated notice notice-warning is-dismissible'><p>";
            $output .= sprintf((new static)->__("The suggested privacy policy text has changed. Please <a href='%s'>review the guide</a> and update your privacy policy."),(new static)->_esc_url( (new static)->_admin_url( 'privacy_policy_guide.php?tab=policy_guide' )));
            $output .= "</p></div>";
            return $output;
        }//131
        public static function policy_text_changed_notice():void{
            echo self::get_policy_text_changed_notice();
        }
        public static function _policy_page_updated( $post_id ):void {
            $policy_page_id = (int) (new static)->_get_option( 'tp_page_for_privacy_policy' );
            if ( ! $policy_page_id || $policy_page_id !== (int) $post_id ) {
                return;}
            $old          = (array) (new static)->_get_post_meta( $policy_page_id, '_tp_suggested_privacy_policy_content' );
            $done         = [];
            $update_cache = false;
            foreach ( $old as $old_key => $old_data ) {
                if ( ! empty( $old_data['removed'] ) ) {
                    // Remove the old policy text.
                    $update_cache = true;
                    continue;
                }
                if ( ! empty( $old_data['updated'] ) ) {
                    $done[] = ['library_name' => $old_data['library_name'], 'policy_text' => $old_data['policy_text'],'added' => $old_data['updated'],];
                    $update_cache = true;
                } else {$done[] = $old_data;}
            }
            if ( $update_cache ) {
                (new static)->_delete_post_meta( $policy_page_id, '_tp_suggested_privacy_policy_content' );
                // Update the cache.
                foreach ( $done as $data ) {
                    (new static)->_add_post_meta( $policy_page_id, '_tp_suggested_privacy_policy_content', $data );
                }
            }
        }//163
        public static function get_suggested_policy_text():array {
            $policy_page_id = (int) (new static)->_get_option( 'tp_page_for_privacy_policy' );
            $checked        = array();
            $time           = time();
            $update_cache   = false;
            $new            = self::$__policy_content;
            $old            = array();
            if ( $policy_page_id ) {
                $old = (array) (new static)->_get_post_meta( $policy_page_id, '_tp_suggested_privacy_policy_content' );
            }
            foreach ( $new as $new_key => $new_data ) {
                foreach ( $old as $old_key => $old_data ) {
                    $found = false;
                    if ( $new_data['policy_text'] === $old_data['policy_text'] ) {
                        if ( $old_data['library_name'] !== $new_data['library_name'] ) {
                            $old_data['library_name'] = $new_data['library_name'];
                            $update_cache            = true;
                        }
                        if ( ! empty( $old_data['removed'] ) ) {
                            unset( $old_data['removed'] );
                            $old_data['added'] = $time;
                            $update_cache      = true;
                        }
                        $checked[] = $old_data;
                        $found     = true;
                    } elseif ( $new_data['library_name'] === $old_data['library_name'] ) {
                        // The info for the policy was updated.
                        $checked[]    = array(
                            'library_name' => $new_data['library_name'],
                            'policy_text' => $new_data['policy_text'],
                            'updated'     => $time,
                        );
                        $found        = true;
                        $update_cache = true;
                    }
                    if ( $found ) {
                        unset( $new[ $new_key ], $old[ $old_key ] );
                        continue 2;
                    }
                }
            }
            if ( ! empty( $new ) ) {
                foreach ( $new as $new_data ) {
                    if ( ! empty( $new_data['library_name'] ) && ! empty( $new_data['policy_text'] ) ) {
                        $new_data['added'] = $time;
                        $checked[]         = $new_data;
                    }
                }
                $update_cache = true;
            }
            if ( ! empty( $old ) ) {
                foreach ( $old as $old_data ) {
                    if ( ! empty( $old_data['library_name'] ) && ! empty( $old_data['policy_text'] ) ) {
                        $data = array(
                            'library_name' => $old_data['library_name'],
                            'policy_text' => $old_data['policy_text'],
                            'removed'     => $time,
                        );
                        $checked[] = $data;
                    }
                }
                $update_cache = true;
            }
            if ( $update_cache && $policy_page_id ) {
                (new static)->_delete_post_meta( $policy_page_id, '_tp_suggested_privacy_policy_content' );
                foreach ( $checked as $data ) {
                    (new static)->_add_post_meta( $policy_page_id, '_tp_suggested_privacy_policy_content', $data );
                }
            }
            return $checked;
        }//213
        public static function get_notice( $post = null ){
            $output  = "";
            if ( is_null( $post ) ) {$post = (new static)->_init_post();}
            else{$post = (new static)->_get_post( $post );}
            if ( ! ( $post instanceof TP_Post )){ return;}
            if ( ! (new static)->_current_user_can( 'manage_privacy_options' ) ) { return;}
            $current_screen = (new static)->_get_current_screen();
            $policy_page_id = (int) (new static)->_get_option( 'tp_page_for_privacy_policy' );
            if ( 'post' !== $current_screen->base || $policy_page_id !== $post->ID ) {return;}
            $message = (new static)->__( 'Need help putting together your new Privacy Policy page? Check out our guide for recommendations on what content to include, along with policies suggested by your libraries and theme.' );
            $url     = (new static)->_esc_url( (new static)->_admin_url( 'options_privacy.php?tab=policy_guide' ) );
            $label   = (new static)->__( 'View Privacy Policy Guide.' );
            $_screen = (new static)->_get_current_screen();
            if ($_screen instanceof Adm_Screen && $_screen->is_block_editor() ) {
                (new static)->tp_enqueue_script( 'tp-notices' );
                $action = ['url' => $url,'label' => $label,];
                (new static)->tp_add_inline_script(
                    'tp-notices',sprintf('tp.data.dispatch( "core/notices" ).createWarningNotice( "%s", { actions: [ %s ], isDismissible: false } )',
                        $message,(new static)->_tp_json_encode( $action )),'after');
            } else{
                $output .= "<div class='notice notice-warning inline tp-pp-notice'>$message";
                $output .= sprintf("<a href='%s' target='_blank'>%s<span class='screen-reader-text'>%s</span></a>",$url,$label,(new static)->__('(opens in a new tab)'));
                $output .= "</div>";
            }
            return $output;
        }//312
        public static function notice( $post = null ):void{
            echo self::get_notice( $post );
        }//312
        public static function get_privacy_policy_guide():string{
            $content_array = self::get_suggested_policy_text();
            $content       = '';
            $date_format   = (new static)->__( 'F j, Y' );
            $output  = "";
            $badge_class = null;
            $badge_title = null;
            foreach ( $content_array as $section ) {
                $class = '';
                $meta = '';
                $removed = '';
                if (!empty($section['removed'])) {
                    $badge_class = ' red';
                    $date = (new static)->_date_i18n($date_format, $section['removed']);
                    /* translators: %s: Date of library deactivation. */
                    $badge_title = sprintf((new static)->__('Removed %s.'), $date);

                    /* translators: %s: Date of plugin deactivation. */
                    $removed = (new static)->__('You deactivated this library on %s and may no longer need this policy.');
                    $removed = "<div class='notice notice-info inline'><p>{sprintf($removed, $date)}</p></div>";
                } elseif (!empty($section['updated'])) {
                    $badge_class = ' blue';
                    $date = (new static)->_date_i18n($date_format, $section['updated']);
                    $badge_title = sprintf((new static)->__('Updated %s.'), $date);
                }
                $library_name = (new static)->_esc_html($section['plugin_name']);
                $sanitized_policy_name = (new static)->_sanitize_title_with_dashes($library_name);
                $output .= "todo, for later";
                $output .= $content;
                $output .= $class;
                $output .= $meta;
                $output .= $removed;
                $output .= $badge_class;
                $output .= $badge_title;
                $output .= $sanitized_policy_name;
            }
            return $output;
        }//378
        public static function privacy_policy_guide():void{
            echo self::get_privacy_policy_guide();
        }//378
        public static function get_default_content( $description = false, $blocks = true ):string {
            $suggested_text = "<strong class='privacy-policy-tutorial'>{(new static)->__( 'Suggested text:' )}</strong>";
            $strings        = [];
            if ( $description ) {
                $strings[] = "<div class='tp-suggested-text'>";
            }
            $strings[] = "<h2>{(new static)->__( 'Who we are' )}</h2>";
            if ( $description ) {
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this section you should note your site URL, as well as the name of the company, organization, or individual behind it, and some accurate contact information.')}</p>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('The amount of information you may be required to show will vary depending on your local or national business regulations. You may, for example, be required to display a physical address, a registered address, or your company registration number.')}</p>";
            }else{
                $_suggested_string = sprintf( (new static)->__( 'Our website address is: %s.' ), (new static)->_get_bloginfo( 'url', 'display' ) );
                $strings[] = "<p>$suggested_text $_suggested_string</p>";
            }
            if ( $description ) {
                $strings[] = "<h2>{(new static)->__('What personal data we collect and why we collect it')}</h2>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this section you should note what personal data you collect from users and site visitors. This may include personal data, such as name, email address, personal account preferences; transactional data, such as purchase information; and technical data, such as information about cookies.')}</p>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('You should also note any collection and retention of sensitive personal data, such as data concerning health.')}</p>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In addition to listing what personal data you collect, you need to note why you collect it. These explanations must note either the legal basis for your data collection and retention or the active consent the user has given.')}</p>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('Personal data is not just created by a user&#8217;s interactions with your site. Personal data is also generated from technical processes such as contact forms, comments, cookies, analytics, and third party embeds.')}</p>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('By default TailoredPress does not collect any personal data about visitors, and only collects the data shown on the User Profile screen from registered users. However some of your libraries may collect personal data. You should add the relevant information below.')}</p>";
            }
            $strings[] = "<h2>{(new static)->__('Comments')}</h2>";
            if ( $description ) {
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this subsection you should note what information is captured through comments. We have noted the data which WordPress collects by default.')}</p>";
            }else{
                $strings[] = "<p>$suggested_text {(new static)->__( 'When visitors leave comments on the site we collect the data shown in the comments form, and also the visitor&#8217;s IP address and browser user agent string to help spam detection.' )}</p>";
                $strings[] = "<p>{(new static)->__('An anonymized string created from your email address (also called a hash) may be provided to the Gravatar service to see if you are using it. The Gravatar service privacy policy is available here: https://automattic.com/privacy/. After approval of your comment, your profile picture is visible to the public in the context of your comment.')}</p>";
            }
            $strings[] = "<h2>{(new static)->__('Media')}</h2>";
            if ( $description ) {
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this subsection you should note what information may be disclosed by users who can upload media files. All uploaded files are usually publicly accessible.')}</p>";
            }else{
                $strings[] = "<p>$suggested_text {(new static)->__('If you upload images to the website, you should avoid uploading images with embedded location data (EXIF GPS) included. Visitors to the website can download and extract any location data from images on the website.')}</p>";
            }
            if ( $description ) {
                $strings[] = "<h2>{(new static)->__('Contact forms')}</h2>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__(''By default, TailoredPress does not include a contact form. If you use a contact form module, use this subsection to note what personal data is captured when someone submits a contact form, and how long you keep it. For example, you may note that you keep contact form submissions for a certain period for customer service purposes, but you do not use the information submitted through them for marketing purposes.')}</p>";
            }
            $strings[] = "<h2>{(new static)->__('Cookies')}</h2>";
            if ( $description ) {
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this subsection you should list the cookies your web site uses, including those set by your modules, social media, and analytics. We have provided the cookies which TailoredPress installs by default.')}</p>";
            }else{
                $strings[] = "<p>$suggested_text {(new static)->__('If you leave a comment on our site you may opt-in to saving your name, email address and website in cookies. These are for your convenience so that you do not have to fill in your details again when you leave another comment. These cookies will last for one year.')}</p>";
                $strings[] = "<p>{(new static)->__('If you visit our login page, we will set a temporary cookie to determine if your browser accepts cookies. This cookie contains no personal data and is discarded when you close your browser.')}</p>";
                $strings[] = "<p>{(new static)->__('When you log in, we will also set up several cookies to save your login information and your screen display choices. Login cookies last for two days, and screen options cookies last for a year. If you select &quot;Remember Me&quot;, your login will persist for two weeks. If you log out of your account, the login cookies will be removed.')}</p>";
                $strings[] = "<p>{(new static)->__('If you edit or publish an article, an additional cookie will be saved in your browser. This cookie includes no personal data and simply indicates the post ID of the article you just edited. It expires after 1 day.')}</p>";
            }
            if ( ! $description ) {
                $strings[] = "<h2>{(new static)->__('Embedded content from other websites')}</h2>";
                $strings[] = "<p>$suggested_text {(new static)->__('Articles on this site may include embedded content (e.g. videos, images, articles, etc.). Embedded content from other websites behaves in the exact same way as if the visitor has visited the other website.')}</p>";
                $strings[] = "<p>{(new static)->__('These websites may collect data about you, use cookies, embed additional third-party tracking, and monitor your interaction with that embedded content, including tracking your interaction with the embedded content if you have an account and are logged in to that website.')}</p>";
            }
            if ( $description ) {
                $strings[] = "<h2>{(new static)->__('Analytics')}</h2>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this subsection you should note what analytics package you use, how users can opt out of analytics tracking, and a link to your analytics provider&#8217;s privacy policy, if any.')}</p>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('By default TailoredPress does not collect any analytics data. However, many web hosting accounts collect some anonymous analytics data. You may also have installed a TailoredPress module that provides analytics services. In that case, add information from that module here.')}</p>";
            }
            $strings[] = "<h2>{(new static)->__('Who we share your data with')}</h2>";
            if ( $description ) {
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this section you should name and list all third party providers with whom you share site data, including partners, cloud-based services, payment processors, and third party service providers, and note what data you share with them and why. Link to their own privacy policies if possible.')}</p>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('By default TailoredPress does not share any personal data with anyone.')}</p>";
            }else{
                $strings[] = "<p>$suggested_text {(new static)->__('If you request a password reset, your IP address will be included in the reset email.')}</p>";
            }
            $strings[] = "<h2>{(new static)->__('How long we retain your data')}</h2>";
            if ( $description ) {
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this section you should explain how long you retain personal data collected or processed by the web site. While it is your responsibility to come up with the schedule of how long you keep each dataset for and why you keep it, that information does need to be listed here. For example, you may want to say that you keep contact form entries for six months, analytics records for a year, and customer purchase records for ten years.')}</p>";
            }else{
                $strings[] = "<p>$suggested_text {(new static)->__('If you leave a comment, the comment and its metadata are retained indefinitely. This is so we can recognize and approve any follow-up comments automatically instead of holding them in a moderation queue.')}</p>";
                $strings[] = "<p>{(new static)->__('For users that register on our website (if any), we also store the personal information they provide in their user profile. All users can see, edit, or delete their personal information at any time (except they cannot change their username). Website administrators can also see and edit that information.')}</p>";
            }
            $strings[] = "<h2>{(new static)->__('What rights you have over your data')}</h2>";
            if ( $description ) {
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this section you should explain what rights your users have over their data and how they can invoke those rights.')}</p>";
            }else{
                $strings[] = "<p>$suggested_text {(new static)->__('If you have an account on this site, or have left comments, you can request to receive an exported file of the personal data we hold about you, including any data you have provided to us. You can also request that we erase any personal data we hold about you. This does not include any data we are obliged to keep for administrative, legal, or security purposes.')}</p>";
            }
            $strings[] = "<h2>{(new static)->__('Where we send your data')}</h2>";
            if ( $description ) {
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this section you should list all transfers of your site data outside the European Union and describe the means by which that data is safeguarded to European data protection standards. This could include your web hosting, cloud storage, or other third party services.')}</p>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('European data protection law requires data about European residents which is transferred outside the European Union to be safeguarded to the same standards as if the data was in Europe. So in addition to listing where data goes, you should describe how you ensure that these standards are met either by yourself or by your third party providers, whether that is through an agreement such as Privacy Shield, model clauses in your contracts, or binding corporate rules.')}</p>";
            }else{
                $strings[] = "<p>$suggested_text {(new static)->__('Visitor comments may be checked through an automated spam detection service.')}</p>";
            }
            if ( $description ) {
                $strings[] = "<h2>{(new static)->__('Contact information')}</h2>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this section you should provide a contact method for privacy-specific concerns. If you are required to have a Data Protection Officer, list their name and full contact details here as well.')}</p>";
            }
            if ( $description ) {
                $strings[] = "<h2>{(new static)->__('Additional information')}</h2>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('If you use your site for commercial purposes and you engage in more complex collection or processing of personal data, you should note the following information in your privacy policy in addition to the information we have already discussed.')}</p>";
            }
            if ( $description ) {
                $strings[] = "<h2>{(new static)->__('How we protect your data')}</h2>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this section you should explain what measures you have taken to protect your users&#8217; data. This could include technical measures such as encryption; security measures such as two factor authentication; and measures such as staff training in data protection. If you have carried out a Privacy Impact Assessment, you can mention it here too.')}</p>";
            }
            if ( $description ) {
                $strings[] = "<h2>{(new static)->__('What data breach procedures we have in place')}</h2>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('In this section you should explain what procedures you have in place to deal with data breaches, either potential or real, such as internal reporting systems, contact mechanisms, or bug bounties.')}</p>";
            }
            if ( $description ) {
                $strings[] = "<h2>{(new static)->__('What third parties we receive data from')}</h2>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('If your web site receives data about users from third parties, including advertisers, this information must be included within the section of your privacy policy dealing with third party data.')}</p>";
            }
            if ( $description ) {
                $strings[] = "<h2>{(new static)->__('What automated decision making and/or profiling we do with user data')}</h2>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('If your web site provides a service which includes automated decision making - for example, allowing customers to apply for credit, or aggregating their data into an advertising profile - you must note that this is taking place, and include information about how that information is used, what decisions are made with that aggregated data, and what rights users have over decisions made without human intervention.')}</p>";
            }
            if ( $description ) {
                $strings[] = "<h2>{(new static)->__('Industry regulatory disclosure requirements')}</h2>";
                $strings[] = "<p class='privacy-policy-tutorial'>{(new static)->__('If you are a member of a regulated industry, or if you are subject to additional privacy laws, you may be required to disclose that information here.')}</p>";
                $strings[] = "</div>";
            }
            if ( $blocks ) {
                foreach ( $strings as $key => $string ) {
                    if ( 0 === strpos( $string,'<p>')){ $strings[ $key ] = '<!-- tp:paragraph -->' . $string . '<!-- /tp:paragraph -->';}
                    if ( 0 === strpos( $string,'<h2>')){ $strings[ $key ] = '<!-- tp:heading -->' . $string . '<!-- /tp:heading -->';}
                }
            }
            $content = implode( '', $strings );
            return (new static)->_apply_filters_deprecated('tp_get_default_privacy_policy_content', [$content, $strings, $description, $blocks],'0.0.1','tp_add_privacy_policy_content()');
        }//452
        public static function add_suggested_content():void{
            $content = self::get_default_content( false, false );
            (new static)->_tp_add_privacy_policy_content( (new static)->__( 'TailoredPress' ), $content );
        }//696
    }
}else{die;}