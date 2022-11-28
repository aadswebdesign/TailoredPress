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
    class Adm_Options_General_Panel extends Admins{
        private $__tp_site_url_class,$__tp_home_class,$__new_admin_email;
        protected $_args;
        public function __construct($args =null){
            parent::__construct();
            $this->_admin_consts();
            $this->_admin_construct();
            $this->adm_header_args = [
                'parent_file' => 'options_general.php',
                'get_admin_general_head' => [$this,'get_options_general_add_js',],
                'index_title' => 'TailoredPress',
                'panel_title' => 'General Settings',
            ];
            $this->adm_header = $this->get_adm_component_class('Adm_Header',$this->adm_header_args);
            $this->adm_footer =  $this->get_adm_component_class('Adm_Footer');
            $this->adm_panel_title = $this->__( 'General Settings' );
            $this->tp_parent_file = 'options_general.php';//
            $this->adm_timezone_format = $this->_x( 'Y-m-d H:i:s', 'timezone date format' );
        }
        private function __get_options_general_pre():string{
            $output  = "";
            $this->__tp_site_url_class = '';
            $this->__tp_home_class     = '';
            if ( ! $this->_is_multisite() ) {
                if (defined('TP_SITEURL')){$this->__tp_site_url_class = ' disabled';}
                if(defined('TP_HOME')){ $this->__tp_home_class = ' disabled';}
            }
            $this->__new_admin_email = $this->_get_option( 'new_admin_email' );
            if ( ! $this->_current_user_can( 'manage_options' ) ) {
                //$output .= $this->_tp_get_die( $this->__( 'Sorry, you are not allowed to manage options for this site.' ) );
            }
            return $output;
        }
        private function __get_options_general_help(){
            $output_help  = "<p>{$this->__('The fields on this screen determine some of the basics of your site setup.')}</p>";
            $output_help .= "<p>{$this->__('Most themes display the site title at the top of every page, in the title bar of the browser, and as the identifying name for syndicated feeds. The tagline is also displayed by many themes.')}</p>";
            if ( ! $this->_is_multisite() ) {
                $output_help .= "<p>{$this->__('The TailoredPress URL and the Site URL can be the same (example.com) or different; for example, having the TailoredPress core files (example.com/tailored-press) in a subdirectory instead of the root directory.')}</p>";
                $output_help .= "<p>{$this->__('If you want site visitors to be able to register themselves, as opposed to by the site administrator, check the membership box. A default user role can be set for all new users, whether self-registered or registered by the site admin.')}</p>";
            }
            $output_help .= "<p>{$this->__('You can set the language, and the translation files will be automatically downloaded and installed (available if your filesystem is writable).')}</p>";
            $output_help .= "<p>{$this->__('UTC means Coordinated Universal Time.')}</p>";
            $output_help .= "<p>{$this->__('You must click the Save Changes button at the bottom of the screen for new settings to take effect.')}</p>";
            $output  = $this->_get_current_screen()->add_help_tab(['id' => 'overview',
                'title' => $this->__( 'Overview' ),'content' => $output_help,]);
            $output_sidebar  = "<p><strong>{$this->__("TODO: As a tiny developer I don't have those facilities!")}</strong></p>";
            //$output_sidebar .= "<p>{$this->__('')}</p>";
            //$output_sidebar .= "<p>{$this->__('')}</p>";
            $output .= $this->_get_current_screen()->set_help_sidebar($output_sidebar);
            return $output;
        }
        private function __get_language_setup():string{
            $output  = "";
            $languages    = $this->_get_available_languages();
            $translations = $this->_tp_get_available_translations();
            if (defined( 'TP_LANG' ) && 'en_US' !== TP_LANG && '' !== TP_LANG && ! $this->_is_multisite() && ! in_array( TP_LANG, $languages, true ) ) {
                $languages[] = TP_LANG;
            }
            $locale = $this->_get_locale();
            if(!in_array( $locale, $languages, true )){ $locale = '';}
            if(!empty($languages)|| ! empty($translations)){
                $dropdown = ['name' => 'TP_LANG','id' => 'tp_lang','selected' => $locale,'languages' => $languages,'translations' => $translations,
                    'show_available_translations' => $this->_current_user_can( 'install_languages' ) && $this->_tp_can_install_language_pack(),
                ];
                $output .= "<dt><label for='tp_lang'>{$this->__('Site Language')}<span class='dashicons dashicons-translation' aria-hidden='true'></span></label></dt><dd>";
                $output .= $this->_tp_get_dropdown_languages($dropdown);
                $output .= "</dd></li><li>";
            }
            return $output;
        }
        private function __get_timezone_setup():string{
            $current_offset = $this->_get_option('gmt_offset' );
            $check_zone_info = true;
            $tz_string   =  $this->_get_option( 'timezone_string' );
            if (false !== strpos( $tz_string, 'Etc/GMT')){ $tz_string = '';}
            if ( empty( $tz_string ) ) {
                $check_zone_info = false;
                if ( 0 === $current_offset ) {
                    $tz_string = 'UTC+0';
                } elseif ( $current_offset < 0 ) {
                    $tz_string = 'UTC' . $current_offset;
                } else {
                    $tz_string = 'UTC+' . $current_offset;
                }
            }
            $_timezone_1 = sprintf($this->__('Choose either a city in the same timezone as you or a %s (Coordinated Universal Time) time offset.'),'<abbr>UTC</abbr>');
            $_timezone_2 = sprintf($this->__('Universal time is %s.'),"<code>{$this->_date_i18n( $this->adm_timezone_format, false, true )}</code>");
            $_timezone_3 = sprintf($this->__('Local time is %s.'),"<code>{$this->_date_i18n( $this->adm_timezone_format)}</code>");
            $output  = "<dt><label for='timezone_string'>{$this->__('Timezone')}</label></dt>";
            $output .= "<dd><select id='timezone_string' name='timezone_string' aria-describedby='timezone-description'>{$this->_tp_timezone_choice($tz_string, $this->_get_user_locale())}</select></dd>";
            $output .= "</li><li>";
            $output .= "<dd><p id='timezone_description' class='description'>$_timezone_1</p></dd>";
            $output .= "<dd><p class='timezone-info'><span class='utc-time'>$_timezone_2</span>";
            if (! empty( $current_offset ) || $this->_get_option( 'timezone_string' )){
                $output .= "<span class='local-time'>$_timezone_3</span>";
            }
            if ( $check_zone_info && $tz_string ){
                $output .= "</p></dd><dd><p class='timezone-info'><span>";
                $now = new \DateTime( 'now', new \DateTimeZone( $tz_string ) );
                $dst = (bool) $now->format( 'I' );
                if($dst){ $output .= $this->__('This timezone is currently in daylight saving time.');
                }else{$output .= $this->__('This timezone is currently in standard time.');}
                $output .= "</span></p></dd>";
                if ( in_array( $tz_string, timezone_identifiers_list(), true ) ) {
                    $transitions = timezone_transitions_get( timezone_open( $tz_string ), time() );
                    $output .= "<dd><p class='timezone-info'><span>";
                    if ( ! empty( $transitions[1] ) ) {
                        $message = $transitions[1]['isdst'] ? $this->__('Daylight saving time begins on: %s.') : $this->__('Standard time begins on: %s.');
                        $_date = $this->_tp_date("{$this->__('F j, Y')} {$this->__('g:i a')}");
                        $output .= sprintf($message ,"<code>$_date,{$transitions[1]['ts']}</code>");
                    }else{ $output .= $this->__('This timezone does not observe daylight saving time.');}
                    $output .= "</span></p></dd>";
                }
            }
            $output .= "</li><li>";
            return $output;
        }
        private function __get_date_format_setup():string{
            $date_formats = array_unique( $this->_apply_filters( 'date_formats',[$this->__( 'F j, Y' ), 'Y-m-d', 'm/d/Y', 'd/m/Y']) );
            $custom = true;
            $output  = "<dt><h3 class=''>{$this->__('Date Format')}</h3></dt>";
            $output .= "</li><li><fieldset><legend><span class='screen-reader-text'>{$this->__('Date Format')}</span></legend>";
            $output .= "<ul><li>";
            foreach ( $date_formats as $format ){
                $date_format_checked = null;
                if ( $this->_get_option( 'date_format' ) === $format ) {
                    $date_format_checked = 'checked';
                    $custom = false;
                }
                $output .= "<dd><input id='date_format_radio' name='date_format' type='radio' value='{$this->_esc_attr($format)}' $date_format_checked /></dd>\n";
                $output .= "<dt><label for='date_format_radio'><span class='date-time-text format-i18n'>{$this->_date_i18n( $format )}</span><code>{$this->_esc_html( $format )}</code></label></dt>\n";
                $output .= "</li><li>\n";
            }
            $custom_checked = $this->_get_checked( $custom );
            $output .= "<dd><input id='date_format_custom_radio' name='date_format' type='radio' value='{$this->__('\c\u\s\t\o\m')}' $custom_checked /></dd>";
            $output .= "<dt><label for='date_format_custom_radio'><span class='date-time-text date-time-custom-text'>{$this->__('Custom:')}<span class='screen-reader-text'>{$this->__('Enter a custom date format in the following field.')}</span></span></label></dt>";
            $output .= "</li><li>";
            $output .= "<dt><label for='date_format_custom' class='screen-reader-text'>{$this->__('Custom date format:')}</label></dt>";
            $output .= "<dd><input id='date_format_custom' name='date_format_custom' class='small-text' type='text' value='{$this->_esc_attr($this->_get_option( 'date_format' ))}'/></dd>";
            $output .= "</li><li>";
            $output .= "<p><strong>{$this->__('Preview:')}</strong><span class='example'>{$this->_date_i18n( $this->_get_option( 'date_format' ) )}</span></p>";
            $output .= "<span class='spinner'></span>";
            $output .= "</li></ul></fieldset></li><li>";
            return $output;
        }
        private function __get_time_format_setup():string{
            $time_formats = array_unique( $this->_apply_filters( 'time_formats', array( $this->__( 'g:i a' ), 'g:i A', 'H:i' ) ) );
            $custom = true;
            $output  = "<dt><h3 class=''>{$this->__('Time  Format')}</h3></dt>";
            $output .= "</li><li><fieldset><legend><span class='screen-reader-text'>{$this->__('Time  Format')}</span></legend>";
            $output .= "<ul><li>";
            foreach ( $time_formats as $format ) {
                $time_format_checked = null;
                if ( $this->_get_option( 'time_format' ) === $format ) {
                    $time_format_checked = 'checked';
                    $custom = false;
                }
                $output .= "<dd><input id='time_format_checkbox' name='time_format' type='checkbox' value='{$this->_esc_attr($format)}' $time_format_checked/></dd>\n";
                $output .= "<dt><label for='time_format_checkbox'><span class='date-time-text format-i18n'>{$this->_date_i18n( $format )}</span><code>{$this->_esc_html( $format )}</code></label></dt>\n";
                $output .= "</li><li>\n";
            }
            $custom_checked = $this->_get_checked( $custom );
            $output .= "<dd><input id='time_format_custom_radio' name='time_format' type='radio' value='{$this->__('\c\u\s\t\o\m')}' $custom_checked/></dd>";
            $output .= "<dt><label for='time_format_custom_radio'><span class='date-time-text date-time-custom-text'>{$this->__('Custom:')}<span class='screen-reader-text'>{$this->__('Enter a custom time format in the following field.')}</span></span></label></dt>";
            $output .= "</li><li>";
            $output .= "<dt><label for='time_format_custom' class='screen-reader-text'>{$this->__('Custom time format:')}</label></dt>";
            $output .= "<dd><input id='time_format_custom' name='time_format_custom' class='small-text' type='text' value='{$this->_esc_attr($this->_get_option( 'time_format' ))}'/></dd>";
            $output .= "</li><li>";
            $output .= "<p><strong>{$this->__('Preview:')}</strong><span class='example'>{$this->_date_i18n( $this->_get_option( 'time_format' ) )}</span></p>";
            $output .= "<span class='spinner'></span>";
            $output .= "</li><li>";
            $output .= "<p class='date-time-doc'>{$this->__('TODO: date-time-doc')}</p>";
            $output .= "</li></ul></fieldset></li><li>";
            return $output;
        }
        private function __get_start_of_week():string{
            $this->tp_locale = $this->_init_locale();
            $output  = "<dt><label for='start_of_week'>{$this->__('Week Starts On')}</label></dt>";
            $output .= "<dd><select id='start_of_week' name='start_of_week' type='' value='{$this->_esc_attr('')}'>";
            for ( $day_index = 0; $day_index <= 6; $day_index++ ){
                $selected = ( $this->_get_option( 'start_of_week' ) === $day_index ) ? " selected='selected'" : '';
                $_locale = $this->tp_locale->get_weekday( $day_index );
                $output .= "\n<option value='{$this->_esc_attr($day_index)}' $selected >$_locale</option>";
            }
            $output .= "</select></dd></li><li>";
            return $output;
        }
        private function __to_string():string{
            $output  = "";
            $output .= $this->adm_header;
            $output .= $this->__get_options_general_help();
            $output .= $this->__get_options_general_pre();
            $output .= "<section class='tp-wrap options-general'>";
            $output .= "<header class='inner-header'><h1>{$this->__($this->adm_panel_title)}</h1></header>";
            $output .= "<div class='form-wrapper'>";
            $output .= "<form method='post' action='option.php' novalidate><ul class='form-layout' role='presentation'><li>";
            $output .= $this->_get_hidden_input_settings_fields('general');
            $output .= "</li><li>";
            $output .= "<dt><label for='blog_name'>{$this->__('Site Title:')}</label></dt>";
            $output .= "<dd><input id='blog_name' name='blogname' class='regular-text' type='text' value='{$this->_form_option( 'blogname' )}'/></dd>";
            $output .= "</li><li>";
            $output .= "<dt><label for='blog_description'>{$this->__('Tagline:')}</label></dt>";
            $output .= "<dd><input id='blog_description' name='blogdescription' class='regular-text' type='text' value='{$this->_form_option( 'blogdescription' )}' aria-describedby='tagline-description'/></dd>";
            $output .= "<dt><p id='tagline_description' class='description'>{$this->__('In a few words, explain what this site is about.')}</p></dt>";
            $output .= "</li><li>";
            $output .= "<dt><label for='site_url'>{$this->__('TailoredPress Address (URL):')}</label></dt>";
            $output .= "<dd><input id='site_url' name='siteurl' class='regular-text $this->__tp_site_url_class' type='url' value='{$this->_form_option( 'siteurl' )}' {$this->_get_disabled( defined( 'TP_SITEURL' ) )} /></dd>";
            $output .= "</li><li>";
            $output .= "<dt><label for='home'>{$this->__('Site Address (URL):')}</label></dt>";
            $output .= "<dd><input id='home' name='home' type='url' class='regular-text $this->__tp_home_class' value='{$this->_form_option( 'home' )}'/></dd>";
            $output .= "</li><li>";
            if (!defined('TP_HOME')){
                $output .= sprintf($this->__("TODO: Enter the address here if you <a href='%s'>want your site home page to be different from your TailoredPress installation directory</a>."),$this->__('#'));
                $output .= "</li><li>";
            }
            $output .= "<dt><label for='new_admin_email'>{$this->__('Administration Email Address:')}</label></dt>";
            $output .= "<dd><input id='new_admin_email' name='new_admin_email' class='regular-text' type='email' value='{$this->_form_option( 'admin_email' )}' aria-describedby='new-admin-email-description'/></dd>";
            $output .= "</li><li>";
            $output .= "<dt><p id='new_admin_email_description' class='description'>{$this->__('This address is used for admin purposes. If you change this, we will send you an email at your new address to confirm it. <strong>The new address will not become active until confirmed.</strong>')}</p></dt>";
            if ( $this->__new_admin_email && $this->_get_option( 'admin_email' ) !== $this->__new_admin_email ){
                $p1= sprintf($this->__('There is a pending change of the admin email to %s.'),"<code>{$this->_esc_url($this->__new_admin_email)}</code>");
                $p2= sprintf(" <a href='%1\$s'>%2\$s</a>",$this->_esc_url($this->_tp_nonce_url($this->_admin_url( 'options.php?dismiss=new_admin_email' ),"dismiss-{$this->_get_current_blog_id()}-new_admin_email")),$this->__('Cancel'));
                $output .= "<p>$p1</p>";
                $output .= "<p>$p2</p>";
                $output .= "</li><li>";
            }
            if ( ! $this->_is_multisite() ){
                $output .= "<dt><h3 class=''>{$this->__('Membership')}</h3></dt>";
                $output .= "</li><li><fieldset><legend><span class='screen-reader-text'>{$this->__('Membership')}</span></legend>";
                $output .= "<ul><li>";
                $output .= "<dd><input id='users_can_register' name='users_can_register' type='checkbox' value='1' {$this->_get_checked( '1', $this->_get_option( 'users_can_register' ) )}/></dd>";
                $output .= "<dt><label for='users_can_register'>{$this->__('Anyone can register')}</label></dt>";
                $output .= "</li><li>";
                $output .= "<dt><label for='default_role'>{$this->__('')}</label></dt>";
                $output .= "<dd><select id='default_role' name='default_role'>{$this->_tp_get_dropdown_roles( $this->_get_option( 'default_role' ) )}</select></dd>";
                $output .= "</li></ul></fieldset></li><li>";
            }
            $output .= $this->__get_language_setup();
            $output .= $this->__get_timezone_setup();
            $output .= $this->__get_date_format_setup();
            $output .= $this->__get_time_format_setup();
            $output .= $this->__get_start_of_week();
            $output .= "</li><li>{$this->_get_settings_fields( 'general', 'default' )}";
            $output .= "</li><li>{$this->_get_settings_sections( 'general' )}";
            $output .= "</li><li>{$this->_get_submit_button()}";
            $output .= "</li></ul></form></div></section><!-- tp-wrap -->";
            $output .= $this->adm_footer;
            return $output;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else{die;}