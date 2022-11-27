<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-9-2022
 * Time: 17:19
 */
namespace TP_Admin\Traits\AdminSchema;
use TP_Core\Libs\TP_Theme;
use TP_Admin\Traits\AdminUpgrade\_adm_upgrade_01;
use TP_Admin\Traits\AdminRewrite\_adm_rewrite_01;
use TP_Admin\Traits\AdminRewrite\_adm_rewrite_02;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Rewrite;
use TP_Core\Traits\Cache\_cache_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_formats_06;
use TP_Core\Traits\HTTP\_http_01;
use TP_Core\Traits\HTTP\_http_02;
use TP_Core\Traits\I10n\_I10n_02;
use TP_Core\Traits\I10n\_I10n_04;
use TP_Core\Traits\Inits\_init_error;
use TP_Core\Traits\Media\_media_05;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\Methods\_methods_14;
use TP_Core\Traits\Multisite\_ms_network;
use TP_Core\Traits\Multisite\Site\_ms_site_03;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Misc\_rewrite;
use TP_Core\Traits\User\_user_02;
use TP_Core\Traits\User\_user_03;
if(ABSPATH){
    trait _adm_schema_02{
        use _cache_01, _formats_06, _http_01,_http_02,_I10n_02, _I10n_04;
        use _init_error, _media_05, _methods_14, _methods_01,_filter_01;
        use _ms_network, _ms_site_03, _pluggable_01, _adm_rewrite_02, _adm_rewrite_01,_rewrite;
        use _adm_schema_construct, _adm_upgrade_01, _user_02, _user_03;
        /**
         * @description Install Network.
         */
        protected function _install_network():void{
            if (!defined('TP_INSTALLING_NETWORK')) { define('TP_INSTALLING_NETWORK', true);}
            $this->_db_delta($this->_tp_get_db_schema('global'));
        }//948
        /**
         * @description Populate network settings.
         * @param int $network_id
         * @param string $domain
         * @param string $email
         * @param string $site_name
         * @param string $path
         * @param bool $subdomain_install
         * @return bool
         */
        protected function _populate_network( $network_id = 1, $domain = '', $email = '', $site_name = '', $path = '/', $subdomain_install = false ):bool{
            $this->__schema_construct();
            $this->tpdb = $this->_init_db();
            $errors = new TP_Error();
            if ( '' === $domain ) { $errors->add( 'empty_domain', $this->__( 'You must provide a domain name.' ) );}
            if ( '' === $site_name ) { $errors->add( 'empty_sitename', $this->__( 'You must provide a name for your network of sites.' ) );}
            if ( $this->_is_multisite() ) {
                if ( $this->_get_network( (int) $network_id ) ) { $errors->add( 'site_id_exists', $this->__( 'The network already exists.' ) );}
            }else if ($network_id === $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " id FROM $this->tpdb->site WHERE id = %d", $network_id ) ) ) {
                $errors->add( 'site_id_exists', $this->__( 'The network already exists.' ) );}
            if ( ! $this->_is_email( $email ) ) {$errors->add( 'invalid_email',$this->__( 'You must provide a valid email address.'));}
            if ( $errors->has_errors() ) { return (bool)$errors;}
            if ( 1 === $network_id ) {
                $this->tpdb->insert($this->tpdb->site,['domain' => $domain,'path' => $path,]);
                $network_id = $this->tpdb->insert_id;
            } else { $this->tpdb->insert( $this->tpdb->site,['domain' => $domain, 'path' => $path,'id' => $network_id,]);}
            $this->_populate_network_meta(
                $network_id,['admin_email' => $email,'site_name' => $site_name,'subdomain_install' => $subdomain_install,]
            );
            $site_user = $this->_get_user_data( (int) $this->tpdb->get_var( $this->tpdb->prepare( TP_SELECT . " meta_value FROM $this->tpdb->sitemeta WHERE meta_key = %s AND site_id = %d", 'admin_user_id', $network_id ) ) );
            if ( ! $this->_is_multisite() ) {
                $current_site            = new \stdClass;
                $current_site->domain    = $domain;
                $current_site->path      = $path;
                $current_site->site_name = ucfirst( $domain );
                $this->tpdb->insert($this->tpdb->blogs,
                    ['site_id' => $network_id,'blog_id' => 1,'domain' => $domain,'path' => $path,'registered' => $this->_current_time( 'mysql' ),]
                );
                $current_site->blog_id = $this->tpdb->insert_id;
                $this->_update_user_meta( $site_user->ID, 'source_domain', $domain );
                $this->_update_user_meta( $site_user->ID, 'primary_blog', $current_site->blog_id );
                if ($this->tp_rewrite instanceof TP_Rewrite && $subdomain_install ) {
                    $this->tp_rewrite->set_permalink_structure( '/%year%/%monthnum%/%day%/%postname%/' );
                } else { $this->tp_rewrite->set_permalink_structure( '/blog/%year%/%monthnum%/%day%/%postname%/' );}
                $this->_flush_rewrite_rules();
                if ( ! $subdomain_install ) {return true;}
                $vhost_ok = false;
                $err_str   = '';
                $hostname = substr( md5( time() ), 0, 6 ) . '.' . $domain; // Very random hostname!
                $page     = $this->_tp_remote_get('http://' . $hostname,['timeout' => 5,'httpversion' => '1.1',]);//todo upgrade this to https/httpversion 2.0
                if ($page instanceof TP_Error && $this->_init_error( $page )){ $err_str = $page->get_error_message();}
                elseif ( 200 === $this->_tp_remote_retrieve_response_code( $page ) ) { $vhost_ok = true;}
                if(!$vhost_ok){
                    $msg = "<p><strong>{$this->__('Warning! Wildcard DNS may not be configured correctly!')}</strong></p><p>";
                    $msg .= sprintf($this->__("The installer attempted to contact a random hostname (%s) on your domain."),"<code>{$hostname}</code>");
                    if(!empty($err_str)){ $msg .= sprintf($this->__('This resulted in an error message: %s'),"<code>{$err_str}</code>");}
                    $msg .= "</p><p>";
                    $msg .= sprintf($this->__('To use a subdomain configuration, you must have a wildcard entry in your DNS. This usually means adding a %s hostname record pointing at your web server in your DNS configuration tool.'),"<code>*</code>");
                    $msg .= "</p><p>{$this->__('You can still use your site but any subdomain you create may not be accessible. If you know your DNS is correct, ignore this message.')}</p>";
                    return (string) new TP_Error( 'no_wildcard_dns', $msg );
                }
            }
            return true;
        }//976
        /**
         * @description Creates TailoredPress network meta and sets the default values.
         * @param int $network_id
         * @param \array[] ...$meta
         */
        protected function _populate_network_meta(int $network_id, array ...$meta):void {
            $this->__schema_construct();
            $this->tpdb = $this->_init_db();
            $email             = ! empty( $meta['admin_email'] ) ? $meta['admin_email'] : '';
            $subdomain_install = isset( $meta['subdomain_install'] ) ? (int) $meta['subdomain_install'] : 0;
            $site_user = ! empty( $email ) ? $this->_get_user_by( 'email', $email ) : false;
            if ( false === $site_user ) { $site_user = $this->_tp_get_user_current();}
            if ( empty( $email ) ) { $email = $site_user->user_email;}
            $template       = $this->_get_option( 'template' );
            $stylesheet     = $this->_get_option( 'stylesheet' );
            $allowed_themes = [$stylesheet => true];
            if ( $template !== $stylesheet ) { $allowed_themes[ $template ] = true;}
            if ( TP_DEFAULT_THEME !== $stylesheet && TP_DEFAULT_THEME !== $template ) {
                $allowed_themes[ TP_DEFAULT_THEME ] = true;
            }
            $default_theme = $this->_tp_get_theme( TP_DEFAULT_THEME );
            if ($default_theme instanceof TP_Theme && ! $default_theme->exists() ) {
                $core_default = TP_Theme::get_core_default_theme();
                if ( $core_default instanceof TP_Theme && $core_default ) {
                    $allowed_themes[ $core_default->get_stylesheet() ] = true;
                }
            }
            if ( method_exists([$this,'_clean_network_cache'],$network_id ) ) {
                $this->_clean_network_cache( $network_id );//todo let see this the right way?
            } else { $this->_tp_cache_delete( $network_id, 'networks' );}
            $this->_tp_cache_delete( 'networks_have_paths', 'site-options' );
            if ( ! $this->_is_multisite() ) {
                $site_admins = [$site_user->user_login];
                $users = $this->_get_users(['fields' => ['user_login'],'role' => 'administrator',]);
                if ( $users ) {
                    foreach ( $users as $user ) { $site_admins[] = $user->user_login;}
                    $site_admins = array_unique( $site_admins );
                }
            } else {$site_admins = $this->_get_site_option( 'site_admins' );}
            $_username = 'USERNAME';
            $_sitename = 'SITE_NAME';
            $_password = 'PASSWORD';
            $_blog_url = 'BLOG_URL';
            $welcome_email = static function()use($_username,$_sitename,$_password,$_blog_url){
                $msg  = (new self)->__('Howdy USERNAME').$_username;
                $msg .= (new self)->__(',Your new').$_sitename;
                $msg .= (new self)->__(' site has been successfully set up at: ').$_blog_url;
                $msg .= (new self)->__('You can log in to the administrator account with the following information:');
                $msg .= (new self)->__('Username: ').$_username;
                $msg .= (new self)->__('Password: ').$_password;
                $msg .= (new self)->__('Log in here: ').$_blog_url.'tp_login.php';
                $msg .= (new self)->__('We hope you enjoy your new site. Thanks!');
                $msg .= (new self)->__('--The Team @').$_sitename;
                return $msg; //1213
            };
            $misc_exts  =['jpg','jpeg','png','gif','webp',];// Images.
            $misc_exts .=['mov','avi','mpg','3gp','3g2',];// Video.
            $misc_exts .=['midi','mid',];// Audio.
            $misc_exts .=['pdf','doc','ppt','odt','pptx','docx','pps','ppsx','xls','xlsx','key',];// Miscellaneous.
            $audio_exts       = $this->_tp_get_audio_extensions();
            $video_exts       = $this->_tp_get_video_extensions();
            $upload_file_types = array_unique( array_merge( $misc_exts, $audio_exts, $video_exts ) );
            $site_meta = ['site_name' => $this->__( 'My Network' ),'admin_email' => $email,'admin_user_id' => $site_user->ID,
                'registration' => 'none','upload_file_types' => implode( ' ', $upload_file_types ),'blog_upload_space' => 100,'fileupload_maxk' => 1500,
                'site_admins' => $site_admins,'allowedthemes' => $allowed_themes,'illegal_names' => ['www','web','root','admin','main','invite','administrator','files'],
                'tp_mu_upgrade_site' => TP_DB_VERSION,'welcome_email' => $welcome_email,'first_post' => $this->__( 'Welcome to %s. This is your first post. Edit or delete it, then start writing!' ),
                'siteurl' => $this->_get_option( 'siteurl' ) . '/','add_new_users' => '0','upload_space_check_disabled' => $this->_is_multisite() ? $this->_get_site_option( 'upload_space_check_disabled' ) : '1',
                'subdomain_install' => $subdomain_install,'global_terms_enabled' => $this->_global_terms_enabled() ? '1' : '0',
                'ms_files_rewriting' => $this->_is_multisite() ? $this->_get_site_option( 'ms_files_rewriting' ) : '0',
                'initial_db_version' => $this->_get_option( 'initial_db_version' ),'TP_LANG' => $this->_get_locale(),
            ];
            if ( ! $subdomain_install ) {
                $site_meta['illegal_names'][] = 'blog';
            }
            $site_meta = $this->_tp_parse_args( $meta, $site_meta );
            $site_meta = $this->_apply_filters( 'populate_network_meta', $site_meta, $network_id );
            $insert = '';
            foreach ( $site_meta as $meta_key => $meta_value ) {
                if ( is_array( $meta_value ) ) {$meta_value = serialize( $meta_value );}
                if ( ! empty( $insert ) ) {$insert .= ', ';}
                $insert .= $this->tpdb->prepare( '( %d, %s, %s)', $network_id, $meta_key, $meta_value );
            }
            $this->tpdb->query( TP_INSERT . " INTO $this->tpdb->sitemeta ( site_id, meta_key, meta_value ) VALUES " . $insert ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }//1132
        /**
         * @description Creates TailoredPress site meta and sets the default values.
         * @param int $site_id
         * @param \array[] ...$meta
         */
        protected function _populate_site_meta(int $site_id, array ...$meta):void{
            $this->__schema_construct();
            $this->tpdb = $this->_init_db();
            if (!$this->_is_site_meta_supported()){ return;}
            if ( empty( $meta)){return;}
            $site_meta = $this->_apply_filters( 'populate_site_meta', $meta, $site_id );
            $insert = '';
            foreach ( $site_meta as $meta_key => $meta_value ) {
                if ( is_array( $meta_value ) ) {$meta_value = serialize( $meta_value );}
                if ( ! empty( $insert)){$insert .= ', ';}
                $insert .= $this->tpdb->prepare( '( %d, %s, %s)', $site_id, $meta_key, $meta_value );
            }
            $this->tpdb->query( TP_INSERT . " INTO $this->tpdb->blog_meta ( blog_id, meta_key, meta_value ) VALUES " . $insert );
            $this->_tp_cache_delete( $site_id, 'blog_meta' );
            $this->_tp_cache_set_sites_last_changed();
        }//1313
    }
}else{die;}