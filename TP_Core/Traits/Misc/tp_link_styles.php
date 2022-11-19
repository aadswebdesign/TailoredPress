<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-5-2022
 * Time: 17:02
 */
namespace TP_Core\Traits\Misc;
use TP_Core\Libs\AssetsTools\TP_Styles;
use TP_Core\Traits\Inits\_init_assets;
if(ABSPATH){
    trait tp_link_styles{
        use _assets_base;
        use _init_assets;
        public function tp_print_styles( $handles = false ): array{
            if ( '' === $handles ) $handles = false;
            if ( ! $handles ) $this->_do_action( 'tp_print_link_styles' );
            $this->_tp_scripts_maybe_doing_it_wrong( __FUNCTION__ );
            return $this->_init_styles()->do_items($handles);
        }
        public function tp_add_inline_style( $handle, $data ):bool {
            $this->_tp_scripts_maybe_doing_it_wrong( __FUNCTION__,$handle );
            if ( false !== stripos( $data, '</style>' ) ){
                $this->_doing_it_wrong(
                    __FUNCTION__,
                    /* translators: 1: <style>, 2: wp_add_inline_style() */
                    sprintf($this->__( 'Do not pass %1$s tags to %2$s.' ),
                        "<code>&lt;style&gt;</code>","<code>tp_add_inline_style()</code>" ),'0.0.1');
                $data = trim( preg_replace( '#<style[^>]*>(.*)</style>#is', '$1', $data ) );
            }
            return $this->_init_styles()->add_inline_style( $handle, $data );
        }
        public function tp_register_style( $handle, $href, $deps = [], $ver = false, $rel = null, $media = null, $crossorigin= null, $integrity= null, $extra_atts =null ): bool{
            $this->_tp_scripts_maybe_doing_it_wrong( __FUNCTION__,$handle );
            return $this->_init_styles()->add( $handle, $href, $deps, $ver, $rel, $media, $crossorigin, $integrity, $extra_atts );
        }
        public function tp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $rel = null, $media = null, $crossorigin= null, $integrity= null, $extra_atts =null ): void{
            $this->_tp_scripts_maybe_doing_it_wrong( __FUNCTION__,$handle );
            $link_style = $this->_init_styles();
            if ( $src ) {
                $_handle = explode( '?', $handle );
                $link_style->add( $_handle[0], $src, $deps, $ver, $rel, $media, $crossorigin, $integrity, $extra_atts );
            }
            $link_style->enqueue( $handle );
        }
        public function tp_dequeue_style( $handle ): void{
            $this->_tp_scripts_maybe_doing_it_wrong( __FUNCTION__,$handle );
            $link_style = $this->_init_styles();
            $link_style->dequeue( $handle );
        }
        public function tp_style_is($handle, $list = 'enqueued' ):bool {
            $this->_tp_scripts_maybe_doing_it_wrong( __FUNCTION__,$handle );
            $link_style = $this->_init_styles();
            return (bool) $link_style->query( $handle, $list );
        }
        public function tp_style_add_data($handle, $key, $value ):string {
            return $this->_init_styles()->add_data( $handle, $key, $value );
        }

        /**
         * @param $handle
         * @return mixed
         */
        public function tp_deregister_style( $handle ){
            $this->_tp_scripts_maybe_doing_it_wrong( __FUNCTION__,$handle );
            $link_style = null;
            if($this->_tp_styles instanceof TP_Styles)
                $link_style = $this->_tp_styles;
            return $link_style->remove( $handle );
        }
    }
}else die;