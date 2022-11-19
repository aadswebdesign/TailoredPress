<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 13-8-2022
 * Time: 21:18
 */
declare(strict_types=1);
namespace TP_Core\Libs\Block;
use TP_Core\Traits\Compat\_compat_01;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Inits\_init_assets;
use TP_Core\Traits\Misc\tp_script;
use TP_Core\Traits\Methods\_methods_12;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Methods\_methods_20;
if(ABSPATH){
    final class TP_Block_Scripts_Registry implements _block_interface{ //todo testing this brew!
        use _compat_01,_formats_08;
        use _methods_12,_methods_20;
        use _I10n_01;
        use _init_assets;
        use tp_script;
        private $__registered_block_scripts = [];
        private static $__instance;
        public function register($script_handle,$add_block_script):bool {
            if ( ! isset( $script_handle ) || ! is_string( $script_handle ) ) {
                $this->_doing_it_wrong( __METHOD__,$this->__( 'Block script handle must be specified.' ),'0.0.1');
                return false;
            }
            if ( ! isset( $add_block_script ) || ! is_string( $add_block_script ) ) {
                $this->_doing_it_wrong( __METHOD__,$this->__('Block script must be specified,like <code>&lt;script&gt;&lt;/script&gt;</code> and with the needed attributes'),'0.0.1');
                return false;
            }
            $tp_script = $this->_init_scripts();
            $block_script = $tp_script->add($this->_esc_html($add_block_script));
            $this->__registered_block_scripts[$script_handle] = $block_script;
            return true;
        }
        public function unregister($script_handle,$add_block_script=null):bool{
            if ( ! $this->is_registered( $script_handle ) ){
                $this->_doing_it_wrong(__METHOD__, sprintf( $this->__( 'Block "%1$s" does not contain a script named "%2$s".' ), $script_handle ),'0.0.1');
                return false;
            }
            unset($this->__registered_block_scripts[$script_handle]);
            return true;
        }
        public function registered($script_handle,$add_block_script=null):void{
            if ( ! $this->is_registered($script_handle)){ return null;}
            return $this->__registered_block_scripts[$script_handle];
        }
        public function get_all_registered():array {
            return $this->__registered_block_scripts;
        }
        public function get_registered_scripts_for_block($script_handle){
            return $this->__registered_block_scripts[$script_handle] ?? [];
        }
        public function is_registered($script_handle,$add_block_script = null):bool{
            return isset($this->__registered_block_scripts[$script_handle]);
        }
        public static function get_instance(): TP_Block_Scripts_Registry{
            if ( null === self::$__instance ) self::$__instance = new self();
            return self::$__instance;
        }
    }
}else{die;}