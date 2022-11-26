<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 20-8-2022
 * Time: 22:48
 */
namespace TP_Core\Libs\SiteMaps;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Methods\_methods_12;
if(ABSPATH){
    class TP_Sitemaps_Renderer extends Sitemaps_Base {
        use _methods_08,_methods_12;
        protected $_stylesheet = '';
        protected $_stylesheet_index = '';
        public function __construct(){
            $stylesheet_url = $this->get_sitemap_stylesheet_url();
            if($stylesheet_url){$this->_stylesheet = "<?xml-stylesheet type='text/xsl' href='{$this->_esc_url( $stylesheet_url )}' ?>";}
            $stylesheet_index_url = $this->get_sitemap_index_stylesheet_url();
            if ( $stylesheet_index_url ){$this->_stylesheet_index = "<?xml-stylesheet type='text/xsl' href='{$this->_esc_url( $stylesheet_index_url )}' ?>";}
        }//41
        public function get_sitemap_stylesheet_url(){
            $tp_rewrite = $this->_init_rewrite();
            $sitemap_url = $this->_home_url( '/tp-sitemap.xsl' );
            if ( ! $tp_rewrite->using_permalinks() ) {
                $sitemap_url = $this->_home_url( '/?sitemap-stylesheet=sitemap' );
            }
            return $this->_apply_filters( 'tp_sitemaps_stylesheet_url', $sitemap_url );
        }//64
        public function get_sitemap_index_stylesheet_url(){
            $tp_rewrite = $this->_init_rewrite();
            $sitemap_url = $this->_home_url( '/tp-sitemap-index.xsl' );
            if ( ! $tp_rewrite->using_permalinks() ) {
                $sitemap_url = $this->_home_url( '/?sitemap-stylesheet=index' );
            }
            return $this->_apply_filters( 'tp_sitemaps_stylesheet_index_url', $sitemap_url );
        }//95
        public function render_index( $sitemaps ):void{
            header( 'Content-type: application/xml; charset=UTF-8' );
            $this->__check_for_simple_xml_availability();
            $index_xml = $this->get_sitemap_index_xml( $sitemaps );
            if(!empty($index_xml)){echo $index_xml;}
        }//124
        public function get_sitemap_index_xml( $sitemaps ){
            $sitemap_index = new \SimpleXMLElement(
                sprintf('%1$s%2$s%3$s',"<?xml version='1.0' encoding='UTF-8' ?>",$this->_stylesheet_index,
                    "<sitemapindex xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'/>"));
            foreach ( $sitemaps as $entry ) {
                $sitemap = $sitemap_index->addChild( 'sitemap' );
                foreach ( $entry as $name => $value ) {
                    if ( 'loc' === $name ) {
                        $sitemap->addChild( $name, $this->_esc_url( $value ) );
                    } elseif ( 'lastmod' === $name ) {
                        $sitemap->addChild( $name, $this->_esc_xml( $value ) );
                    } else {
                        $this->_doing_it_wrong(__METHOD__, sprintf( $this->__( 'Fields other than %s are not currently supported for the sitemap index.' ),
                                implode( ',',['loc', 'lastmod'])),'0.0.1');/* translators: %s: List of element names. */
                    }
                }
            }
            return $sitemap_index->asXML();
        }//146
        public function render_sitemap( $url_list ):void{
            header( 'Content-type: application/xml; charset=UTF-8' );
            $this->__check_for_simple_xml_availability();
            $sitemap_xml = $this->get_sitemap_xml( $url_list );
            if(!empty($sitemap_xml)){echo $sitemap_xml;}
        }//189
        public function get_sitemap_xml( $url_list ){
            $url_set = new \SimpleXMLElement(sprintf('%1$s%2$s%3$s',
                    "<?xml version='1.0' encoding='UTF-8' ?>",$this->_stylesheet,
                    "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'/>"));
            foreach ( $url_list as $url_item ) {
                $url = $url_set->addChild( 'url' );
                foreach ( $url_item as $name => $value ) {
                    if ( 'loc' === $name ) { $url->addChild( $name, $this->_esc_url( $value ) );
                    } elseif ( in_array( $name, array( 'lastmod', 'changefreq', 'priority' ), true ) ) {
                        $url->addChild( $name, $this->_esc_xml( $value ) );
                    } else {
                        $this->_doing_it_wrong(__METHOD__,sprintf($this->__( 'Fields other than %s are not currently supported for sitemaps.' ),
                                implode( ',', ['loc', 'lastmod', 'changefreq', 'priority'] )),'0.0.1');/* translators: %s: List of element names. */
                    }
                }
            }
            return $url_set->asXML();
        }//211
        private function __check_for_simple_xml_availability():void{
            if ( ! class_exists( 'SimpleXMLElement' ) ) {
                $this->_add_filter('tp_die_handler',static function(){return '_xml_wp_die_handler';});
                $this->_tp_die(sprintf($this->_esc_xml( $this->__( 'Could not generate XML sitemap due to missing %s extension' ) ),
                        'SimpleXML'),/* translators: %s: SimpleXML */
                    $this->_esc_xml( $this->__( 'TailoredPress &rsaquo; Error' ) ),['response' => 501,]); // "Not implemented".
            }
        }//252
    }
}else{die;}