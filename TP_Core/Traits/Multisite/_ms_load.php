<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-5-2022
 * Time: 10:22
 */
namespace TP_Core\Traits\Multisite;
use TP_Core\Libs\TP_Network;
use TP_Core\Libs\TP_Site;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _ms_load{
        use _init_db;
        /**
         * @description  Whether a subdomain configuration is enabled.
         * @return bool
         */
        protected function _is_subdomain_install():bool{
            if ( defined( 'SUBDOMAIN_INSTALL' ) )
                return SUBDOMAIN_INSTALL;
            return ( defined( 'VHOST' ) && 'yes' === VHOST );
        }//18
        //@description Returns array of network plugin files to be included in global scope.
        //protected function _tp_get_active_network_plugins(){return '';}//37
        /**
         * @description Checks status of current blog.
         * @return bool|string
         */
        protected function _ms_site_check(){
            $check = $this->_apply_filters( 'ms_site_check', null );
            if ( null !== $check ){return true;}
            if ( $this->_is_super_admin() ) { return true;}
            $blog = $this->_get_site();
            if ( '1' === $blog->deleted ) {
                if ( file_exists( TP_CONTENT_DIR . '/blog-deleted.php' ) ) { return TP_CONTENT_DIR . '/blog-deleted.php';}
                $this->_tp_die( $this->__( 'This site is no longer available.' ), '', array( 'response' => 410 ) );
            }
            if ( '2' === $blog->deleted ) {
                if ( file_exists( TP_CONTENT_DIR . '/blog-inactive.php')){ return TP_CONTENT_DIR .'/blog-inactive.php';}
                $admin_email = str_replace( '@', ' AT ', $this->_get_site_option( 'admin_email', 'support@' . $this->_get_network()->domain ) );
                $this->_tp_die( sprintf($this->__( 'This site has not been activated yet. If you are having problems activating your site, please contact %s.' ),
                        sprintf( "<a href='mailto:%1\$s'>%1\$s</a>", $admin_email )));/* translators: %s: Admin email link. */
            }
            if ( '1' === $blog->archived || '1' === $blog->spam ) {
                if ( file_exists( TP_CONTENT_DIR . '/blog-suspended.php')){return TP_CONTENT_DIR . '/blog-suspended.php';}
                $this->_tp_die( $this->__( 'This site has been archived or suspended.' ), '', array( 'response' => 410 ) );
            }
            return true;
        }//74
        /**
         * @description Retrieve the closest matching network for a domain and path.
         * @param $domain
         * @param $path
         * @param null $segments
         * @return mixed
         */
        protected function _get_network_by_path( $domain, $path, $segments = null ){
            return TP_Network::get_by_path( $domain, $path, $segments );
        }//141
        /**
         * @description Retrieves the closest matching site object by its domain and path.
         * @param $domain
         * @param $path
         * @param null $segments
         * @return mixed
         */
        protected function _get_site_by_path( $domain, $path, $segments = null ){
            $path_segments = array_filter( explode( '/', trim( $path, '/' ) ) );
            $segments = $this->_apply_filters( 'site_by_path_segments_count', $segments, $domain, $path );
            if ( null !== $segments && count( $path_segments ) > $segments ) {
                $path_segments = array_slice( $path_segments, 0, $segments );
            }
            $paths = [];
            while ( count( $path_segments ) ) {
                $paths[] = '/' . implode( '/', $path_segments ) . '/';
                array_pop( $path_segments );
            }
            $paths[] = '/';
            $pre = $this->_apply_filters( 'pre_get_site_by_path', null, $domain, $path, $segments, $paths );
            if ( null !== $pre ) {
                if ( false !== $pre && ! $pre instanceof TP_Site ) { $pre = new TP_Site( $pre );}
                return $pre;
            }
            $domains = [$domain];
            if (strpos($domain, 'www.') === 0) {$domains[] = substr( $domain, 4 );}
            $args = ['number' => 1,'update_site_meta_cache' => false,];
            if ( count( $domains ) > 1 ) {
                $args['domain__in']               = $domains;
                $args['orderby']['domain_length'] = 'DESC';
            } else {$args['domain'] = array_shift( $domains );}
            if ( count( $paths ) > 1 ) {
                $args['path__in']               = $paths;
                $args['orderby']['path_length'] = 'DESC';
            } else { $args['path'] = array_shift( $paths );}
            $result = $this->_get_sites( $args );
            $site   = array_shift( $result );
            if ( $site ) {return $site;}
            return false;
        }//163
        /**
         * @description Identifies the network and site of a requested domain and path and populates the
         * @description . corresponding network and site global objects as part of the multisite bootstrap process.
         * @param $domain
         * @param $path
         * @param bool $subdomain
         * @return bool|string
         */
        protected function _ms_load_current_site_and_network( $domain, $path, $subdomain = false ){
            if ( defined( 'DOMAIN_CURRENT_SITE' ) && defined( 'PATH_CURRENT_SITE' ) ) {
                $this->tp_current_site         = new \stdClass;
                $this->tp_current_site->id     = defined( 'SITE_ID_CURRENT_SITE' ) ? SITE_ID_CURRENT_SITE : 1;
                $this->tp_current_site->domain = DOMAIN_CURRENT_SITE;
                $this->tp_current_site->path   = PATH_CURRENT_SITE;
                if ( defined( 'BLOG_ID_CURRENT_SITE' ) ) {
                    $this->tp_current_site->blog_id = BLOG_ID_CURRENT_SITE;
                }
                if ( 0 === strcasecmp( $this->tp_current_site->domain, $domain ) && 0 === strcasecmp( $this->tp_current_site->path, $path ) ) {
                    $this->tp_current_blog = $this->_get_site_by_path( $domain, $path );
                } elseif ( '/' !== $this->tp_current_site->path && 0 === strcasecmp( $this->tp_current_site->domain, $domain ) && 0 === stripos( $path, $this->tp_current_site->path ) ) {
                    $this->tp_current_blog = $this->_get_site_by_path( $domain, $path, 1 + count( explode( '/', trim( $this->tp_current_site->path, '/' ) ) ) );
                } else {$this->tp_current_blog = $this->_get_site_by_path( $domain, $path, 1 );}
            } elseif ( ! $subdomain ) {
                $this->tp_current_site = $this->_tp_cache_get( 'current_network', 'site-options' );
                if ( ! $this->tp_current_site ) {
                    $networks = $this->_get_networks( array( 'number' => 2 ) );
                    if ( count( $networks ) === 1 ) {
                        $this->tp_current_site = array_shift( $networks );
                        $this->_tp_cache_add( 'current_network', $this->tp_current_site, 'site-options' );
                    } elseif ( empty( $networks ) ) {return false;}
                }
                if ( empty( $this->tp_current_site ) ) {$this->tp_current_site = TP_Network::get_by_path( $domain, $path, 1 );}
                if (empty( $this->tp_current_site )) {
                    $this->_do_action( 'ms_network_not_found', $domain, $path );
                    return false;
                }
                if ($path === $this->tp_current_site->path) {$this->tp_current_blog = $this->_get_site_by_path( $domain, $path );}
                else {$this->tp_current_blog = $this->_get_site_by_path( $domain, $path, substr_count( $this->tp_current_site->path, '/' ) ); }
            } else {
                $this->tp_current_blog = $this->_get_site_by_path( $domain, $path, 1 );
                if ( $this->tp_current_blog ) {
                    $this->tp_current_site = TP_Network::get_instance( $this->tp_current_blog->site_id ?: 1 );
                } else {$this->tp_current_site = TP_Network::get_by_path( $domain, $path, 1 );}
            }
            if ( $this->tp_current_blog && $this->tp_current_blog->site_id !== $this->tp_current_site->id ) {
                $this->tp_current_site = TP_Network::get_instance( $this->tp_current_blog->site_id );
            }
            if ( empty( $this->tp_current_site ) ) {
                $this->_do_action( 'ms_network_not_found', $domain, $path );
                return false;
            }
            if ( empty( $this->tp_current_blog ) && $this->_tp_installing() ) {
                $this->tp_current_blog          = new \stdClass;
                $this->tp_current_blog->blog_id = 1;
                //$blog_id               = 1;//doing nothing
                $this->tp_current_blog->public  = 1;
            }
            if ( empty( $this->tp_current_blog ) ) {
                $scheme      = $this->_is_ssl() ? 'https' : 'http';
                $destination = "$scheme://{$this->tp_current_site->domain}{$this->tp_current_site->path}";
                $this->_do_action( 'ms_site_not_found', $this->tp_current_site, $domain, $path );
                if ( $subdomain && ! defined( 'TP_NO_BLOG_REDIRECT' ) ) {
                    $destination .= 'tp-signup.php?new=' . str_replace( '.' . $this->tp_current_site->domain, '', $domain );
                } elseif ( $subdomain ) {
                    if ( '%siteurl%' !== TP_NO_BLOG_REDIRECT ){$destination = TP_NO_BLOG_REDIRECT;}
                } elseif ( 0 === strcasecmp( $this->tp_current_site->domain, $domain ) ) {
                    return false;
                }
                return $destination;
            }
            if ( empty( $this->tp_current_site->blog_id ) ) {
                $this->tp_current_site->blog_id = $this->_get_main_site_id( $this->tp_current_site->id );
            }
            return true;
        }//295
        /**
         * @description Displays a failure message.
         * @param $domain
         * @param $path
         */
        protected function _ms_not_installed( $domain, $path ):void{
            $tpdb = $this->tpdb = $this->_init_db();
            if ( ! $this->_is_admin()){$this->_dead_db();}
            $title = $this->__( 'Error establishing a database connection' );
            $not_installed_msg = static function()use($tpdb,$title,$domain, $path){
                (new self)->_tp_load_translations_early();
                $msg="<h1>$title</h1><p>";
                $msg.=(new self)->__('If your site does not display, please contact the owner of this network.');
                $msg.=(new self)->__('If you are the owner of this network please check that MySQL is running properly and all tables are error free.');
                $msg.="</p>";
                $query = $tpdb->prepare( 'SHOW TABLES LIKE %s', $tpdb->esc_like( $tpdb->site ) );
                if ( ! $tpdb->get_var( $query ) ) {
                    $msg.="<p>";
                    ob_start();
                    sprintf((new self)->__('<strong>Database tables are missing.</strong> This means that MySQL is not running, TailoredPress was not installed properly.'),
                        "<code>{$tpdb->site}</code>");/* translators: %s: Table name. */
                    $msg.=ob_get_clean();
                    $msg.="</p>";
                }else{
                    $msg.="<p>";
                    ob_start();
                    sprintf((new self)->__('<strong>Could not find site %1$s.</strong>  Searched for table %2$s in database %3$s. Is that right?'),
                        "<code>". rtrim( $domain . $path, '/' ) . "</code>","<code>{$tpdb->blogs}</code>","<code>" . TP_DB_NAME  ."</code>");
                    $msg.=ob_get_clean();
                    $msg.="</p>";
                }
                $msg.="<p><strong>{(new self)->__('What do I do now?')}</strong>";
                ob_start();
                sprintf(
                    (new self)->__( 'Read this Debugging article <a href="%s" target="_blank">Here</a>. Some of the suggestions there may help you figure out what went wrong.' ),
                    (new self)->__( 'https://wordpress.org/support/article/debugging-a-wordpress-network/' )
                ); /* translators: %s: Documentation URL. */
                $msg.=ob_get_clean();
                $msg.="{(new self)->__('If you&#8217;re still stuck with this message, then check that your database contains the following tables:')}</p><ul>";
                foreach ( $tpdb->tables( 'global' ) as $t => $table ) {
                    if ( 'site_categories' === $t ) {continue;}
                    $msg.="<li>$table</li>";
                }
                $msg.="</ul>";
                echo $msg;
            };
            $this->_tp_die( $not_installed_msg, $title, ['response' => 500] );
        }//462
    }
}else die;

