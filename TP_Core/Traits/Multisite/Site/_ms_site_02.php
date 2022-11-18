<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-5-2022
 * Time: 10:39
 */
namespace TP_Core\Traits\Multisite\Site;
use TP_Admin\Traits\AdminSchema\_adm_schema_02;
use TP_Admin\Traits\AdminUpgrade\_adm_upgrade_01;
use TP_Admin\Traits\AdminUpgrade\_adm_upgrade_02;
use TP_Admin\Traits\AdminSchema\_adm_schema_01;
use TP_Core\Traits\Inits\_init_cache;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Traits\Inits\_init_user;
use TP_Core\Traits\Inits\_init_site;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Site;
if(ABSPATH){
    trait _ms_site_02{
        use _init_db, _init_user, _init_site, _init_cache;
        //from admin
        use _adm_schema_01,_adm_schema_02,_adm_upgrade_01,_adm_upgrade_02;
        /**
         * @description Runs the initialization routine for a given site.
         * @param $site_id
         * @param \array[] ...$args
         * @return bool
         */
        protected function _tp_initialize_site( $site_id, array ...$args):bool{
            if(!defined('UPLOAD_BLOGS_DIR')) define('UPLOAD_BLOGS_DIR','todo');//todo
            $tpdb = $this->_init_db();
            if ( empty( $site_id ) )  return (bool)new TP_Error( 'site_empty_id', $this->__( 'Site ID must not be empty.' ) );
            $site = $this->_get_site( $site_id );
            if ( ! $site )  return (bool)new TP_Error( 'site_invalid_id', $this->__( 'Site with the ID does not exist.' ) );
            if ( $this->_tp_is_site_initialized( $site ) )
                return (bool)new TP_Error( 'site_already_initialized', $this->__( 'The site appears to be already initialized.' ) );
            $network = $this->_get_network( $site->network_id );
            if ( ! $network ) $network = $this->_get_network();
            $args = $this->_tp_parse_args( $args,
                ['user_id' => 0,'title' => sprintf( $this->__( 'Site %d' ), $site->id ),'options' => [],'meta' => [],]
            ); /* translators: %d: Site ID. */
            $args = $this->_apply_filters( 'tp_initialize_site_args', $args, $site, $network );
            $orig_installing = $this->_tp_installing();
            if ( ! $orig_installing ) $this->_tp_installing( true );
            $switch = false;
            if ( $this->_get_current_blog_id() !== $site->id ) {
                $switch = true;
                $this->_switch_to_blog( $site->id );
            }
            // todo require_once ABSPATH . 'TP_Admin/includes/upgrade.php';//todo
            $this->_make_db_current_silent( 'blog' );
            $home_scheme    = 'http';
            $siteurl_scheme = 'http';
            if ( ! $this->_is_subdomain_install() ) {
                if ( 'https' === parse_url( $this->_get_home_url( $network->site_id ), PHP_URL_SCHEME ) )
                    $home_scheme = 'https';
                if ( 'https' === parse_url( $this->_get_network_option( $network->id, 'siteurl' ), PHP_URL_SCHEME ) )
                    $siteurl_scheme = 'https';
            }
            $this->_populate_options(
                array_merge(
                    [
                        'home'        => $this->_untrailingslashit( $home_scheme . '://' . $site->domain . $site->path ),
                        'siteurl'     => $this->_untrailingslashit( $siteurl_scheme . '://' . $site->domain . $site->path ),
                        'blogname'    => $this->_tp_unslash( $args['title'] ),
                        'admin_email' => '',
                        'upload_path' => $this->_get_network_option( $network->id, 'ms_files_rewriting' ) ? UPLOAD_BLOGS_DIR . "/{$site->id}/files" : $this->_get_blog_option( $network->site_id, 'upload_path' ),
                        'blog_public' => (int) $site->public,
                        'TPLANG'      => $this->_get_network_option( $network->id, 'TPLANG' ),
                    ],
                    $args['options']
                )
            );
            $this->_clean_blog_cache( $site );
            // Populate the site's roles.
            $this->_populate_roles();
            $this->_init_roles();
            $this->_populate_site_meta( $site->id, $args['meta'] );
            $table_prefix = $tpdb->get_blog_prefix();
            $this->_delete_metadata( 'user', 0, $table_prefix . 'user_level', null, true );   // Delete all.
            $this->_delete_metadata( 'user', 0, $table_prefix . 'capabilities', null, true ); // Delete all.
            $this->_tp_install_defaults( $args['user_id'] );
            // Set the site administrator.
            $this->_add_user_to_blog( $site->id, $args['user_id'], 'administrator' );
            if ( ! $this->_user_can( $args['user_id'], 'manage_network' ) && ! $this->_get_user_meta( $args['user_id'], 'primary_blog', true ) )
                $this->_update_user_meta( $args['user_id'], 'primary_blog', $site->id );
            if ( $switch ) $this->_restore_current_blog();
            $this->_tp_installing( $orig_installing );
            return true;
        }//630
        /**
         * @description Runs the un_initialization routine for a given site.
         * @param $site_id
         * @return bool|TP_Error
         */
        protected function _tp_un_initialize_site( $site_id ){
            $tpdb = $this->_init_db();
            if ( empty( $site_id ) )
                return new TP_Error( 'site_empty_id', $this->__( 'Site ID must not be empty.' ) );
            $site = $this->_get_site( $site_id );
            if ( ! $site )
                return new TP_Error( 'site_invalid_id', $this->__( 'Site with the ID does not exist.' ) );
            if ( ! $this->_tp_is_site_initialized( $site ) )
                return new TP_Error( 'site_already_uninitialized', $this->__( 'The site appears to be already uninitialized.' ) );
            $users = $this->_get_users(['blog_id' => $site->id,'fields' => 'ids',]);
            if ( ! empty( $users ) ) {
                foreach ( $users as $user_id ) $this->_remove_user_from_blog( $user_id, $site->id );
            }
            $switch = false;
            if ( $this->_get_current_blog_id() !== $site->id ) {
                $switch = true;
                $this->_switch_to_blog( $site->id );
            }
            $uploads = $this->_tp_get_upload_dir();
            $tables = $tpdb->tables( 'blog' );
            $drop_tables = $this->_apply_filters( 'tp_mu_drop_tables', $tables, $site->id );
            foreach ( (array) $drop_tables as $table )  $tpdb->query( TP_DROP_TABLE ." IF EXISTS `$table`" );
            $dir     = $this->_apply_filters( 'tp_mu_delete_blog_upload_dir', $uploads['basedir'], $site->id );
            $dir     = rtrim( $dir, DIRECTORY_SEPARATOR );
            $top_dir = $dir;
            $stack   = array( $dir );
            $index   = 0;
            while ( $index < count( $stack ) ) {
                $dir = $stack[ $index ];
                $dh = @opendir( $dir );
                if ( $dh ) {
                    $file = @readdir( $dh );
                    while ( false !== $file ) {
                        if ( '.' === $file || '..' === $file ) {
                            $file = @readdir( $dh );
                            continue;
                        }
                        if ( @is_dir( $dir . DIRECTORY_SEPARATOR . $file ) ) $stack[] = $dir . DIRECTORY_SEPARATOR . $file;
                        elseif ( @is_file( $dir . DIRECTORY_SEPARATOR . $file ) ) @unlink( $dir . DIRECTORY_SEPARATOR . $file );
                        $file = @readdir( $dh );
                    }
                    @closedir( $dh );
                }
                $index++;
            }
            $stack = array_reverse( $stack ); // Last added directories are deepest.
            foreach ( $stack as $dir ) {
                if ( $dir !== $top_dir ) @rmdir( $dir );
            }
            if ( $switch ) $this->_restore_current_blog();
            return true;
        }//761
        /**
         * @description  Checks whether a site is initialized.
         * @param $site_id
         * @return bool
         */
        protected function _tp_is_site_initialized( $site_id ):bool{
            $tpdb = $this->_init_db();
            if ( is_object( $site_id ) ) $site_id = $site_id->blog_id;
            $site_id = (int) $site_id;
            $pre = $this->_apply_filters( 'pre_tp_is_site_initialized', null, $site_id );
            if ( null !== $pre ) return (bool) $pre;
            $switch = false;
            if ( $this->_get_current_blog_id() !== $site_id ) {
                $switch = true;
                $this->_remove_action( 'switch_blog', 'tp_switch_roles_and_user', 1 );
                $this->_switch_to_blog( $site_id );
            }
            $suppress = $tpdb->suppress_errors();
            $result   = (bool) $tpdb->get_results( "DESCRIBE {$tpdb->posts}" );
            $tpdb->suppress_errors( $suppress );
            if ( $switch ) {
                $this->_restore_current_blog();
                $this->_add_action( 'switch_blog', 'tp_switch_roles_and_user', 1, 2 );
            }
            return $result;
        }//883
        /**
         * @description Clean the blog cache
         * @param $blog
         * @return bool
         */
        protected function _clean_blog_cache( $blog ):bool{
            if(!empty($this->__tp_suspend_cache_invalidation)) return false;
            if ( empty( $blog ) ) return false;
            $blog_id = $blog;
            $blog    = $this->_get_site( $blog_id );
            if ( ! $blog ) {
                if ( ! is_numeric( $blog_id ) ) return false;
                $blog = $this->_init_site((object) ['blog_id' => $blog_id,'domain'=> null,'path'=> null,]);
            }
            $blog_id         = $blog->blog_id;
            $domain_path_key = md5( $blog->domain . $blog->path );
            $this->_tp_cache_delete( $blog_id, 'sites' );
            $this->_tp_cache_delete( $blog_id, 'site-details' );
            $this->_tp_cache_delete( $blog_id, 'blog-details' );
            $this->_tp_cache_delete( $blog_id . 'short', 'blog-details' );
            $this->_tp_cache_delete( $domain_path_key, 'blog-lookup' );
            $this->_tp_cache_delete( $domain_path_key, 'blog-id-cache' );
            $this->_tp_cache_delete( $blog_id, 'blog_meta' );
            $this->_do_action( 'clean_site_cache', $blog_id, $blog, $domain_path_key );
            $this->_tp_cache_set( 'last_changed', microtime(), 'sites' );
            return true;
        }//936
        /**
         * @description Adds metadata to a site.
         * @param $site_id
         * @param $meta_key
         * @param $meta_value
         * @param bool $unique
         * @return mixed
         */
        protected function _add_site_meta( $site_id, $meta_key, $meta_value, $unique = false ){
            return $this->_add_metadata( 'blog', $site_id, $meta_key, $meta_value, $unique );
        }//1011
        /**
         * @description Removes metadata matching criteria from a site.
         * @param $site_id
         * @param $meta_key
         * @param string $meta_value
         * @return mixed
         */
        protected function _delete_site_meta( $site_id, $meta_key, $meta_value = '' ){
            return $this->_delete_metadata( 'blog', $site_id, $meta_key, $meta_value );
        }//1031
        /**
         * @description Retrieves metadata for a site.
         * @param $site_id
         * @param string $key
         * @param bool $single
         * @return mixed
         */
        protected function _get_site_meta( $site_id, $key = '', $single = false ){
            return $this->_get_metadata( 'blog', $site_id, $key, $single );
        }//1051
        /**
         * @description Updates metadata for a site.
         * @param $site_id
         * @param $meta_key
         * @param $meta_value
         * @param string $prev_value
         * @return mixed
         */
        protected function _update_site_meta( $site_id, $meta_key, $meta_value, $prev_value = '' ){
            return $this->_update_metadata( 'blog', $site_id, $meta_key, $meta_value, $prev_value );
        }//1075
        /**
         * @description Deletes everything from site meta matching meta key.
         * @param $meta_key
         * @return mixed
         */
        protected function _delete_site_meta_by_key( $meta_key ){
            return $this->_delete_metadata( 'blog', null, $meta_key, '', true );
        }//1087
        /**
         * @description Updates the count of sites for a network based on a changed site.
         * @param $new_site
         * @param TP_Site|null $old_site
         */
        protected function _tp_maybe_update_network_site_counts_on_update( $new_site, TP_Site $old_site = null ):void{
            if ( null === $old_site ) {
                $this->_tp_maybe_update_network_site_counts( $new_site->site_id );
                return;
            }
            if ( $new_site->site_id !== $old_site->site_id ) {
                $this->_tp_maybe_update_network_site_counts( $new_site->site_id );
                $this->_tp_maybe_update_network_site_counts( $old_site->site_id );
            }
        }//1100
    }
}else die;