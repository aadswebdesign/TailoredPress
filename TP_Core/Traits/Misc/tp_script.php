<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 4-5-2022
 * Time: 14:49
 */
namespace TP_Core\Traits\Misc;
use TP_Core\Libs\AssetsTools\TP_Scripts;
use TP_Core\Traits\Inits\_init_assets;
//use TP_Core\Traits\Inits\_init_pages;
if(ABSPATH){
    trait tp_script{
        use _assets_base;
        use _init_assets;
        //use _init_pages;
        public function tp_print_scripts( $handles = false ): array{
            $this->_do_action( 'tp_print_scripts' );
            if ( '' === $handles ) $handles = false;
            $this->_tp_scripts_maybe_doing_it_wrong( __FUNCTION__ );
            return $this->_init_scripts()->do_items($handles);
        }
        public function tp_add_inline_script( $handle, $data, $position = 'after' ){
            $this->_tp_scripts_maybe_doing_it_wrong(__FUNCTION__, $handle);
            if(false !== stripos( $data,'</script>')){
                $this->_doing_it_wrong(__FUNCTION__, sprintf(
                    $this->__('Do not pass %1$s tags to %2$s.'),
                    "<code>&lt;script&gt;</code>","<code>tp_add_inline_script()</code>"
                ),'0.0.1');
                $data = trim( preg_replace( '#<script[^>]*>(.*)</script>#is', '$1', $data ) );
            }
            return $this->_init_scripts()->add_inline_script( $handle, $data, $position );
        }
        //todo this has to be checked
        public function tp_register_script( $handle, $src, $deps = [], $ver = false, $type = null, $loading_type = null, $crossorigin = null, $integrity = null, $extra_atts = null, $in_footer = false ): bool{
            $this->_tp_scripts_maybe_doing_it_wrong(__FUNCTION__, $handle);
            $tp_scripts = $this->_init_scripts();
            $registered = $tp_scripts->add( $handle, $src, $deps, $ver, $type, $loading_type, $crossorigin, $integrity, $extra_atts);
            if ( $in_footer ) $tp_scripts->add_data( $handle, 'group', 1 );
            return $registered;
        }
        //todo this has to be checked
        public function tp_enqueue_script( $handle, $src = '', $deps = [], $ver = false, $type = null, $loading_type = null, $crossorigin = null, $integrity = null, $extra_atts = null, $in_footer = false ): void{
            $this->_tp_scripts_maybe_doing_it_wrong(__FUNCTION__, $handle);
            if($type === 'module') {
                $message = "<script>console.log(`If you want to work with 'js' modules? Use 'assets_register_scripts' instead!`)</script>";
                $this->_e($message);
                return;
            }
            $tp_scripts = $this->_init_scripts();
            if ( $src || $in_footer ) {
                $_handle = explode( '?', $handle );
                if ( $src ) $tp_scripts->add( $_handle[0], $src, $deps, $ver, $type, $loading_type, $crossorigin, $integrity, $extra_atts );
                if ( $in_footer ) $tp_scripts->add_data( $_handle[0], 'group', 1 );
            }
        }
        public function tp_dequeue_script( $handle ): void{
            $this->_tp_scripts_maybe_doing_it_wrong(__FUNCTION__, $handle);
            $tp_scripts = $this->_init_scripts();
            $tp_scripts->dequeue( $handle );
        }
        public function tp_script_is( $handle, $list = 'enqueued' ): bool{
            $this->_tp_scripts_maybe_doing_it_wrong(__FUNCTION__, $handle);
            return (bool) $this->_init_scripts()->query( $handle, $list );
        }
        public function tp_script_add_data($handle, $key, $value ): bool{
            return $this->_init_scripts()->add_data( $handle, $key, $value );
        }
        public function tp_localize_script( $handle, $object_name, $l10n ) {
            if ( ! ($this->_tp_scripts instanceof TP_Scripts ) ) {
                $this->_tp_scripts_maybe_doing_it_wrong( __FUNCTION__, $handle );
                return false;
            }
            return $this->_init_scripts()->localize( $handle, $object_name, $l10n );
        }
        public function tp_set_script_translations( $handle, $domain = 'default', $path = null ) {
            if ( ! ($this->_tp_scripts instanceof TP_Scripts ) ) {
                $this->_tp_scripts_maybe_doing_it_wrong( __FUNCTION__, $handle );
                return false;
            }
            return $this->_tp_scripts->set_translations( $handle, $domain, $path );
        }
        public function tp_deregister_script( $handle ): void{
            $this->_tp_scripts_maybe_doing_it_wrong( __FUNCTION__, $handle );
            $current_filter = $this->_current_filter();
            $not_allowed = null;
            if (( 'tp-login.php' === $this->tp_pagenow && 'login_enqueue_scripts' !== $current_filter ) || ( $this->_is_admin() && 'admin_enqueue_scripts' !== $current_filter )){
                $not_allowed = []; //todo
                if ( in_array( $handle, $not_allowed, true ) ) {
                    $this->_doing_it_wrong(
                        __FUNCTION__,
                        sprintf(
                        /* translators: 1: Script name, 2: wp_enqueue_scripts */
                            $this->__( 'Do not deregister the %1$s script in the administration area. To target the front-end theme, use the %2$s hook.' ),
                            "<code>$handle</code>",
                            '<code>tp_enqueue_scripts</code>'
                        ),
                        '0.0.1'
                    );
                    return;
                }
            }
            $this->_init_scripts()->remove($handle);
        }
    }
}else die;