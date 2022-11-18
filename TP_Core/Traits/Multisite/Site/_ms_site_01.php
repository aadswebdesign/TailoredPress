<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 16-5-2022
 * Time: 10:39
 */
namespace TP_Core\Traits\Multisite\Site;
use TP_Core\Traits\Inits\_init_db;
use TP_Core\Libs\TP_Error;
use TP_Core\Libs\TP_Network;
use TP_Core\Libs\TP_Site;
use TP_Core\Traits\Inits\_init_queries;
if(ABSPATH){
    trait _ms_site_01{
        use _init_db, _init_queries;
        /**
         * todo site_id or network_id
         * @description Inserts a new site into the database.
         * @param array $data
         * @return array|int|TP_Error
         */
        protected function _tp_insert_site( array $data ){
            $this->tpdb = $this->_init_db();
            $now = $this->_current_time( 'mysql', true );
            $defaults = ['domain' => '','path' => '/','network_id' => $this->_get_current_network_id(),
                'registered' => $now,'last_updated' => $now,'public' => 1,'archived' => 0,
                'mature' => 0,'spam' => 0,'deleted' => 0,'lang_id' => 0,];
            $prepared_data = $this->_tp_prepare_site_data( $data, $defaults );
            if ( $this->_init_error( $prepared_data ) ) return $prepared_data;
            if ( false === $this->tpdb->insert( $this->tpdb->blogs, $prepared_data ) )
                return new TP_Error( 'db_insert_error',  $this->__( 'Could not insert site into the database.' ), $this->tpdb->last_error );
            $site_id = (int) $this->tpdb->insert_id;
            $this->_clean_blog_cache( $site_id );
            $new_site =  $this->_get_site( $site_id );
            if ( ! $new_site ) return new TP_Error( 'get_site_error',  $this->__( 'Could not retrieve site data.' ) );
            $this->_do_action( 'tp_insert_site', $new_site );
            $args = array_diff_key( $data, $defaults );
            if ( isset( $args['site_id'] ) ) unset( $args['site_id'] );
            $this->_do_action( 'tp_initialize_site', $new_site, $args );
            if ( $this->_has_action( 'tp_mu_new_blog' ) ) {
                $user_id = ! empty( $args['user_id'] ) ? $args['user_id'] : 0;
                $meta    = ! empty( $args['options'] ) ? $args['options'] : [];
                if ( ! array_key_exists( 'TPLANG', $meta ) ) {
                    $meta['TPLANG'] = $this->_get_network_option( $new_site->site_id, 'TPLANG' );
                }
                $allowed_data_fields = ['public', 'archived', 'mature', 'spam', 'deleted', 'lang_id'];
                $meta                = array_merge( array_intersect_key( $data, array_flip( $allowed_data_fields ) ), $meta );
                $this->_do_action(
                    'tpm_u_new_blog',
                    [ $new_site->site_id, $user_id, $new_site->domain, $new_site->path, $new_site->site_id, $meta],
                    '0.0.1','tp_initialize_site'
                );
            }
            return (int) $new_site->site_id;
        }//44

        /**
         * @description Updates a site in the database.
         * @param $site_id
         * @param mixed ...$data
         * @return array|int|string|TP_Error
         */
        protected function _tp_update_site( $site_id, array ...$data ){
            $this->tpdb = $this->_init_db();
            if ( empty( $site_id ) )
                return new TP_Error( 'site_empty_id', $this->__( 'Site ID must not be empty.' ) );
            $old_site = $this->_get_site( $site_id );
            if ( ! $old_site )
                return new TP_Error( 'site_not_exist', $this->__( 'Site does not exist.' ) );
            $defaults                 = $old_site->to_array();
            $defaults['network_id']   = (int) $defaults['site_id'];
            $defaults['last_updated'] = $this->_current_time( 'mysql', true );
            unset( $defaults['blog_id'], $defaults['site_id'] );
            $data = $this->_tp_prepare_site_data( $data, $defaults, $old_site );
            if ($this->_init_error( $data ) ) return $data;
            if ( false === $this->tpdb->update( $this->tpdb->blogs, $data, array( 'blog_id' => $old_site->site_id ) ) )
                return new TP_Error( 'db_update_error', $this->__( 'Could not update site in the database.' ), $this->tpdb->last_error );
            $this->_clean_blog_cache( $old_site );
            $new_site = $this->_get_site( $old_site->site_id );
            $this->_do_action( 'tp_update_site', $new_site, $old_site );
            return (int) $new_site->site_id;
        }//157
        /**
         * @description Deletes a site from the database.
         * @param $site_id
         * @return string|TP_Error
         */
        protected function _tp_delete_site( $site_id ){
            $this->tpdb = $this->_init_db();
            if ( empty( $site_id ) )
                return new TP_Error( 'site_empty_id', $this->__( 'Site ID must not be empty.' ) );
            $old_site = $this->_get_site( $site_id );
            if ( ! $old_site )
                return new TP_Error( 'site_not_exist', $this->__( 'Site does not exist.' ) );
            $errors = new TP_Error();
            $this->_do_action( 'tp_validate_site_deletion', $errors, $old_site );
            if ( ! empty( $errors->errors ) ) return $errors;
            $this->_do_action( 'tp_un_initialize_site', $old_site );
            if ( $this->_is_site_meta_supported() ) {
                $blog_meta_ids = $this->tpdb->get_col( $this->tpdb->prepare( TP_SELECT . " meta_id FROM $this->tpdb->blog_meta WHERE blog_id = %d ", $old_site->site_id ) );
                foreach ( $blog_meta_ids as $mid ) $this->_delete_metadata_by_mid( 'blog', $mid );
            }
            if ( false === $this->tpdb->delete( $this->tpdb->blogs, array( 'blog_id' => $old_site->site_id ) ) )
                return new TP_Error( 'db_delete_error', $this->__( 'Could not delete site from the database.' ), $this->tpdb->last_error );
            $this->_clean_blog_cache( $old_site );
            $this->_do_action( 'tp_delete_site', $old_site );
            return $old_site;
        }//210
        /**
         * @description Retrieves site data given a site ID or site object.
         * @param null $site
         * @return bool|null|TP_Site
         */
        protected function _get_site( $site = null ){
            if ( empty( $site ) ) $site = $this->_get_current_blog_id();
            if ( $site instanceof TP_Site ) $_site = $site;
            elseif ( is_object( $site ) ) $_site = new TP_Site( $site );
            else $_site = TP_Site::get_instance( $site );
            if ( ! $_site ) return null;
            $_site = $this->_apply_filters( 'get_site', $_site );
            return $_site;
        }//308
        /**
         * @description Adds any sites from the given IDs to the cache that do not already exist in cache.
         * @param $ids
         * @param bool $update_meta_cache
         */
        protected function _prime_site_caches( $ids, $update_meta_cache = true ):void{
            $this->tpdb = $this->_init_db();
            $non_cached_ids = $this->_get_non_cached_ids( $ids, 'sites' );
            if ( ! empty( $non_cached_ids ) ) {
                $fresh_sites = $this->tpdb->get_results( sprintf( TP_SELECT . " * FROM $this->tpdb->blogs WHERE blog_id IN (%s)", implode( ',', array_map( 'intval', $non_cached_ids ) ) ) );
                $this->_update_site_cache( $fresh_sites, $update_meta_cache );
            }
        }//350
        /**
         * @description Updates sites in cache.
         * @param $sites
         * @param bool $update_meta_cache
         */
        protected function _update_site_cache( $sites, $update_meta_cache = true ):void{
            if ( ! $sites ) return;
            $site_ids = [];
            foreach ( $sites as $site ) {
                $site_ids[] = $site->blog_id;
                $this->_tp_cache_add( $site->blog_id, $site, 'sites' );
                $this->_tp_cache_add( $site->blog_id . 'short', $site, 'blog-details' );
            }
            if ( $update_meta_cache ) $this->_update_sitemeta_cache( $site_ids );
        }//370
        /**
         * @description Updates metadata cache for list of site IDs.
         * @param $site_ids
         * @return mixed
         */
        protected function _update_sitemeta_cache( $site_ids ){
            if ( ! $this->_has_filter( 'update_blog_metadata_cache', 'tp_check_site_meta_support_pre_filter' ) )
                $this->_add_filter( 'update_blog_metadata_cache', 'tp_check_site_meta_support_pre_filter' );
            return $this->_update_meta_cache( 'blog', $site_ids );
        }//397
        /**
         * @description Retrieves a list of sites matching requested arguments.
         * @param array ...$args
         * @return array|int
         */
        protected function _get_sites( ...$args){
            $this->tp_query = $this->_init_site_query();
            return $this->tp_query->query_site( $args );
        }//418
        /**
         * @description Prepares site data for insertion or update in the database.
         * @param $data
         * @param $defaults
         * @param null $old_site
         * @return array|TP_Error
         */
        protected function _tp_prepare_site_data( $data, $defaults,$old_site = null ){
            if ( isset( $data['site_id'] ) ) {
                if ( ! empty( $data['site_id'] ) && empty( $data['network_id'] ) )
                    $data['network_id'] = $data['site_id'];
                unset( $data['site_id'] );
            }
            $data = $this->_apply_filters( 'tp_normalize_site_data', $data );
            $allowed_data_fields = array( 'domain', 'path', 'network_id', 'registered', 'last_updated', 'public', 'archived', 'mature', 'spam', 'deleted', 'lang_id' );
            $data                = array_intersect_key( $this->_tp_parse_args( $data, $defaults ), array_flip( $allowed_data_fields ) );
            $errors = new TP_Error();
            $this->_do_action( 'tp_validate_site_data', $errors, $data, $old_site );
            if ( ! empty( $errors->errors ) ) return $errors;
            $data['site_id'] = $data['network_id'];
            unset( $data['network_id'] );
            return $data;
        }//437
        /**
         * @description Normalizes data for a site prior to inserting or updating in the database.
         * @param $data
         * @return mixed
         */
        protected function _tp_normalize_site_data( $data ){
            if ( array_key_exists( 'domain', $data ) ) {
                $data['domain'] = trim( $data['domain'] );
                $data['domain'] = preg_replace( '/\s+/', '', $this->_sanitize_user( $data['domain'], true ) );
                if ( $this->_is_subdomain_install() )
                    $data['domain'] = str_replace( '@', '', $data['domain'] );
            }
            if ( array_key_exists( 'path', $data ) )
                $data['path'] = $this->_trailingslashit( '/' . trim( $data['path'], '/' ) );
            if ( array_key_exists( 'network_id', $data ) )
                $data['network_id'] = (int) $data['network_id'];
            $status_fields = array( 'public', 'archived', 'mature', 'spam', 'deleted' );
            foreach ( $status_fields as $status_field ) {
                if ( array_key_exists( $status_field, $data ) )  $data[ $status_field ] = (int) $data[ $status_field ];
            }
            $date_fields = array( 'registered', 'last_updated' );
            foreach ( $date_fields as $date_field ) {
                if ( ! array_key_exists( $date_field, $data ) ) continue;
                if ( empty( $data[ $date_field ] ) || '0000-00-00 00:00:00' === $data[ $date_field ] )
                    unset( $data[ $date_field ] );
            }
            return $data;
        }//497
        /**
         * @description Validates data for a site prior to inserting or updating in the database.
         * @param TP_Error $errors
         * @param $data
         * @param TP_Network |null $old_site
         */
        protected function _tp_validate_site_data(TP_Error $errors, $data,TP_Network  $old_site = null ): void{
            if ( empty( $data['domain'] ) )
                $errors->add( 'site_empty_domain', $this->__( 'Site domain must not be empty.' ) );
            if ( empty( $data['path'] ) )
                $errors->add( 'site_empty_path', $this->__( 'Site path must not be empty.' ) );
            if ( empty( $data['network_id'] ) )
                $errors->add( 'site_empty_network_id', $this->__( 'Site network ID must be provided.' ) );
            $date_fields = array( 'registered', 'last_updated' );
            foreach ( $date_fields as $date_field ) {
                if ( empty( $data[ $date_field ] ) ) {
                    $errors->add( 'site_empty_' . $date_field, $this->__( 'Both registration and last updated dates must be provided.' ) );
                    break;
                }
                if ( '0000-00-00 00:00:00' !== $data[ $date_field ] ) {
                    $month      = substr( $data[ $date_field ], 5, 2 );
                    $day        = substr( $data[ $date_field ], 8, 2 );
                    $year       = substr( $data[ $date_field ], 0, 4 );
                    $valid_date = $this->_tp_check_date( $month, $day, $year, $data[ $date_field ] );
                    if ( ! $valid_date ) {
                        $errors->add( 'site_invalid_' . $date_field, $this->__( 'Both registration and last updated dates must be valid dates.' ) );
                        break;
                    }
                }
            }
            if ( ! empty( $errors->errors ) ) return;
            // If a new site, or domain/path/network ID have changed, ensure uniqueness.
            if ( ! $old_site || $data['domain'] !== $old_site->domain || $data['path'] !== $old_site->path || $data['network_id'] !== $old_site->network_id) {
                if ( $this->_domain_exists( $data['domain'], $data['path'], $data['network_id'] ) )
                    $errors->add( 'site_taken', $this->__( 'Sorry, that site already exists!' ) );
            }
        }//552

    }
}else die;