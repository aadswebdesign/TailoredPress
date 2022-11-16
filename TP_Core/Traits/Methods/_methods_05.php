<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-4-2022
 * Time: 12:45
 */
namespace TP_Core\Traits\Methods;
use TP_Core\Traits\Feed\Components\feed_atom;
use TP_Core\Traits\Feed\Components\feed_atom_comments;
use TP_Core\Traits\Feed\Components\feed_rss;
use TP_Core\Traits\Feed\Components\feed_rss2;
use TP_Core\Traits\Feed\Components\feed_rss2_comments;
use TP_Core\Traits\Inits\_init_db;
if(ABSPATH){
    trait _methods_05{
        use _init_db;
        /**
         * @description Load the RSS 1.0 Feed Template.
         */
        protected function _do_feed_rss():void{
            echo new feed_rss();
        }//1625
        /**
         * @description Load either the RSS2 comment feed or the RSS2 posts feed.
         * @param bool $for_comments
         */
        protected function _do_feed_rss2( $for_comments ):void{
            if ( $for_comments ) echo new feed_rss2_comments();
            else echo new feed_rss2();
        }//1638
        /**
         * @description Load either Atom comment feed or Atom posts feed.
         * @param $for_comments
         */
        protected function _do_feed_atom( $for_comments ):void{
            if ( $for_comments ) new feed_atom_comments();
            else new feed_atom();
        }//1655
        /**
         * @description Displays the default robots.txt file content.
         */
        protected function _do_robots():void{
            header( 'Content-Type: text/plain; charset=utf-8' );
            $this->_do_action( 'do_robots_txt' );
            $output = "User-agent: *\n";
            $public = $this->_get_option( 'blog_public' );
            $site_url = parse_url( $this->_site_url() );
            $path     = ( ! empty( $site_url['path'] ) ) ? $site_url['path'] : '';
            $output  .= "$path/todo/\n";
            $output  .= "Disallow: $path/TP_Admin/\n";
            $output  .= "Allow: $path/TP_Admin/admin-async.php\n";
            echo $this->_apply_filters( 'robots_txt', $output, $public );
        }//1671
        /**
         * @description Display the favicon.ico file content.
         */
        protected function _do_favicon():void{
            $this->_do_action( 'do_favicon_icon' );
            $this->_tp_redirect( $this->_get_site_icon_url( 32, $this->_includes_url( 'images/' ) ) );//todo
            exit;
        }//1705
        /**
         * @description Determines whether TailoredPress is already installed.
         * @return bool
         */
        protected function _is_blog_installed():bool{
            $this->tpdb = $this->_init_db();
            if ( $this->_tp_cache_get( 'is_blog_installed' ) ) return true;
            $suppress = $this->tpdb->suppress_errors();
            if ( ! $this->_tp_installing() )  $all_options = $this->_tp_load_all_options();
            if ( ! isset( $all_options['siteurl'] ) )
                $installed = $this->tpdb->get_var(TP_SELECT . " option_value FROM " . $this->tpdb->options . " WHERE option_name = 'siteurl'" );
            else $installed = $all_options['siteurl'];
            $this->tpdb->suppress_errors($suppress);
            $installed = ! empty( $installed );
            $this->_tp_cache_set( 'is_blog_installed', $installed );
            if ( $installed ) return true;
            if ( defined( 'TP_REPAIRING' ) ) return true;
            $suppress = $this->tpdb->suppress_errors();
            $tp_tables = $this->tpdb->tables();
            foreach ( $tp_tables as $table ) {
                if ( defined( 'CUSTOM_USER_TABLE' ) && CUSTOM_USER_TABLE === $table ) continue;
                if ( defined( 'CUSTOM_USER_META_TABLE' ) && CUSTOM_USER_META_TABLE === $table ) continue;
                $described_table = $this->_init_db()->get_results( "DESCRIBE $table;" );
                if (( ! $described_table && empty( $this->tpdb->last_error ) ) || ( is_array( $described_table ) && 0 === count( $described_table )))
                    continue;
                $this->_tp_load_translations_early();
                $this->tpdb->error = sprintf(
                    $this->__( 'One or more database tables are unavailable. The database may need to be <a href="%s">repaired</a>.' ),
                    'maintenance/repair.php?referrer=is_blog_installed'
                );//todo set the final path to repair_db.php
                $this->_dead_db();
            }
            $this->tpdb->suppress_errors($suppress);
            $this->_tp_cache_set( 'is_blog_installed', false );
            return false;
        }//1736
        /**
         * @description Retrieve URL with nonce added to URL query.
         * @param $action_url
         * @param int $action
         * @param string $name
         * @return mixed
         */
        protected function _tp_nonce_url( $action_url, $action = -1, $name = '_tp_nonce' ){
            $action_url = str_replace( '&amp;', '&', $action_url );
            return $this->_esc_html($this->_add_query_arg($name, $this->_tp_create_nonce($action  ), $action_url ));
        }//1827
        /**
         * @description Retrieve URL with nonce added to URL query.
         * @param int $action
         * @param string $name
         * @param bool $referer
         * @return string
         */
        protected function _tp_get_nonce_field( $action = -1, $name = '_tp_nonce', $referer = true):string{
            $name        = $this->_esc_attr( $name );
            $nonce_field = "<input id='{$name}' name='{$name}' type='hidden' value='{$this->_tp_create_nonce( $action )}'  />";
            if ( $referer ) $nonce_field .= $this->_tp_get_referer_field();
            return $nonce_field;
        }
        protected function _tp_nonce_field( $action = -1, $name = '_tp_nonce', $referer = true):void{
            echo $this->_tp_get_nonce_field( $action, $name, $referer);
        }//1858
        /**
         * @description Retrieve or display referer hidden field for forms.
         * @return string
         */
        protected function _tp_get_referer_field():string{
            return "<input type='hidden' name='_tp_http_referer' value='{$this->_esc_attr( $this->_tp_unslash( $_SERVER['REQUEST_URI'] ) )}'/>";
        }//1884

        /**
         * @description Retrieve or display original referer hidden field for forms.
         * @param bool $echo
         * @param string $jump_back_to
         * @return string
         */
        protected function _tp_original_referer_field( $echo = true, $jump_back_to = 'current' ):string{
            $ref = $this->_tp_get_original_referer();
            if ( ! $ref ) $ref = ( 'previous' === $jump_back_to ) ? $this->_tp_get_referer() : $this->_tp_unslash( $_SERVER['REQUEST_URI'] );
            $orig_referer_field = "<input type='hidden' name='_tp_original_http_referer' value='{$this->_esc_attr( $ref )}' />";
            if ( $echo ) echo $orig_referer_field;
            return $orig_referer_field;
        }//1908
        /**
         * @description Retrieve referer from '_tp_http_referer' or HTTP referer.
         * @return bool
         */
        protected function _tp_get_referer():bool{
            if ( ! function_exists('_tp_validate_redirect') )
                return false;
            $ref = $this->_tp_get_raw_referer();
            if ( $ref && $this->_tp_unslash( $_SERVER['REQUEST_URI'] ) !== $ref && $this->_home_url() . $this->_tp_unslash( $_SERVER['REQUEST_URI'] ) !== $ref )
                return $this->_tp_validate_redirect( $ref, false );
            return false;
        }//1933
    }
}else die;