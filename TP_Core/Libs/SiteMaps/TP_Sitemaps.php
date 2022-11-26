<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-8-2022
 * Time: 22:40
 */
namespace TP_Core\Libs\SiteMaps;
use TP_Core\Libs\Queries\TP_Query;
use TP_Core\Traits\Formats\_formats_10;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\Misc\_rewrite;
use TP_Core\Libs\SiteMaps\Providers\TP_Sitemaps_Posts;
use TP_Core\Libs\SiteMaps\Providers\TP_Sitemaps_Taxonomies;
use TP_Core\Libs\SiteMaps\Providers\TP_Sitemaps_Users;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Inits\_init_queries;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Pluggables\_pluggable_03;
use TP_Core\Traits\Query\_query_01;

if(ABSPATH){
    class TP_Sitemaps extends Sitemaps_Base {
        use _init_queries, _action_01,_option_01,_rewrite;
        use _formats_10,_query_01,_methods_04,_methods_12,_pluggable_03;
        public $index;
        public $registry;
        public $renderer;
        public function __construct(){
            $this->registry = new TP_Sitemaps_Registry();
            $this->renderer = new TP_Sitemaps_Renderer();
            $this->index    = new TP_Sitemaps_Index( $this->registry );
        }//50
        public function init():void{
            $this->register_rewrites();
            $this->_add_action( 'template_redirect',[$this, 'render_sitemaps']);
            if ( ! $this->sitemaps_enabled() ) {return;}
            $this->register_sitemaps();
            $this->_add_filter( 'pre_handle_404',[$this, 'redirect_sitemap_xml'], 10, 2 );
            $this->_add_filter( 'robots_txt',[$this, 'add_robots'], 0, 2 );
        }//64
        public function sitemaps_enabled():bool{
            $is_enabled = (bool) $this->_get_option( 'blog_public' );
            return (bool) $this->_apply_filters( 'tp_sitemaps_enabled', $is_enabled );
        }//88
        public function register_sitemaps():void{
            $providers = ['posts' => new TP_Sitemaps_Posts(),
                'taxonomies' => new TP_Sitemaps_Taxonomies(),
                'users' => new TP_Sitemaps_Users(),];
            /* @var TP_Sitemaps_Provider $provider */
            foreach ( $providers as $name => $provider ) {
                $this->registry->add_provider( $name, $provider );
            }
        }//112
        public function register_rewrites():void{
            // Add rewrite tags.
            $this->_add_rewrite_tag( '%sitemap%', '([^?]+)' );
            $this->_add_rewrite_tag( '%sitemap-subtype%', '([^?]+)' );
            // Register index route.
            $this->_add_rewrite_rule( '^tp-sitemap\.xml$', 'index.php?sitemap=index', 'top' );
            // Register rewrites for the XSL stylesheet.
            $this->_add_rewrite_tag( '%sitemap-stylesheet%', '([^?]+)' );
            $this->_add_rewrite_rule( '^tp-sitemap\.xsl$', 'index.php?sitemap-stylesheet=sitemap', 'top' );
            $this->_add_rewrite_rule( '^tp-sitemap-index\.xsl$', 'index.php?sitemap-stylesheet=index', 'top' );
            // Register routes for providers.
            $this->_add_rewrite_rule('^tp-sitemap-([a-z]+?)-([a-z\d_-]+?)-(\d+?)\.xml$',
                'index.php?sitemap=$matches[1]&sitemap-subtype=$matches[2]&paged=$matches[3]','top');
            $this->_add_rewrite_rule('^tp-sitemap-([a-z]+?)-(\d+?)\.xml$','index.php?sitemap=$matches[1]&paged=$matches[2]','top');
        }//130
        public function render_sitemaps():void{
            $tp_query = $this->_init_query();
            $sitemap         = $this->_sanitize_text_field( $this->_get_query_var( 'sitemap' ) );
            $object_subtype  = $this->_sanitize_text_field( $this->_get_query_var( 'sitemap-subtype' ) );
            $stylesheet_type = $this->_sanitize_text_field( $this->_get_query_var( 'sitemap-stylesheet' ) );
            $paged           = $this->_abs_int( $this->_get_query_var( 'paged' ) );
            // Bail early if this isn't a sitemap or stylesheet route.
            if (!( $sitemap || $stylesheet_type )){return;}
            if ( ! $this->sitemaps_enabled() ) {
                $tp_query->set_404();
                $this->_status_header( 404 );
                return;
            }
            // Render stylesheet if this is stylesheet route.
            if ( $stylesheet_type ) {
                $stylesheet = new TP_Sitemaps_Stylesheet();
                $stylesheet->render_stylesheet( $stylesheet_type );
                exit;
            }
            // Render the index.
            if ( 'index' === $sitemap ) {
                $sitemap_list = $this->index->get_sitemap_list();
                $this->renderer->render_index( $sitemap_list );
                exit;
            }
            $provider = $this->registry->get_provider( $sitemap );
            if ( ! $provider ) {return;}
            if ( empty( $paged ) ) {$paged = 1;}
            $url_list = $provider->get_url_list( $paged, $object_subtype );
            // Force a 404 and bail early if no URLs are present.
            if ( empty( $url_list ) ) {
                $tp_query->set_404();
                $this->_status_header( 404 );
                return;
            }
            $this->renderer->render_sitemap( $url_list );
            exit;
        }//163
        public function redirect_sitemap_xml( $bypass,TP_Query $query ){
            if ( $bypass ){return $bypass;}
            // 'pagename' is for most permalink types, name is for when the %postname% is used as a top-level field.
            if ( 'sitemap-xml' === $query->get( 'pagename' ) || 'sitemap-xml' === $query->get( 'name' )){
                $this->_tp_safe_redirect( $this->index->get_index_url() );
                exit();
            }
            return $bypass;
        }//230
        public function add_robots( $output, $public ){
            if ( $public ) { $output .= "\nSitemap: " . $this->_esc_url( $this->index->get_index_url() ) . "\n";}
            return $output;
        }//256
    }
}else{die;}