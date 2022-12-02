<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-5-2022
 * Time: 13:58
 */
namespace TP_Core\Traits\AssetsLoaders;
use TP_Core\Libs\Block\TP_Block_Type_Registry;
use TP_Core\Libs\Block\TP_Block_Styles_Registry;
use TP_Core\Traits\Inits\_init_assets;
//use TP_Core\Traits\Inits\_init_pages;
if(ABSPATH){
    trait _assets_loader_05{
        //use _init_pages;
        use _init_assets;
        /**
         * @description Inject the block editor assets that need to be loaded into the editor's iframe as an inline script.
         * @return string
         */
        protected function _tp_add_i_framed_editor_assets_html():string{
            if ( ! $this->_tp_should_load_block_editor_assets() )return '';
            $script_handles = [];
            $style_handles = ['tp_block_editor','tp_block_library','tp_block_library_theme','tp_edit_blocks',];
            if ( 'widgets.php' === $this->tp_pagenow || 'customize.php' === $this->tp_pagenow ) {//todo not using
                $style_handles[] = 'tp_widgets';
                $style_handles[] = 'tp_edit_widgets';
            }
            $block_type_instances = TP_Block_Type_Registry::get_instance();
            $block_registry = null;
            if($block_type_instances instanceof TP_Block_Styles_Registry ){
                $block_registry = $block_type_instances->get_all_registered();
            }
            foreach($block_registry as $block_type){
                if ( ! empty( $block_type->style ) ) $style_handles[] = $block_type->style;
                if ( ! empty( $block_type->editor_style ) ) $style_handles[] = $block_type->editor_style;
                if ( ! empty( $block_type->script ) ) $script_handles[] = $block_type->script;
            }
            $style_handles = array_unique( $style_handles );
            $this->tp_styles = $this->_init_styles();
            $done = $this->tp_styles->done;
            ob_start();
            $this->tp_styles->done = array( 'tp_reset_editor_styles' );
            $this->tp_styles->do_items( $style_handles );
            $this->tp_styles->done = $done;
            $styles = ob_get_clean();
            $script_handles = array_unique( $script_handles );
            $this->tp_scripts = $this->_init_scripts();
            $done = $this->tp_scripts->done;
            ob_start();
            $this->tp_scripts->done = [];
            $this->tp_scripts->do_items( $script_handles );
            $this->tp_scripts->done = $done;
            $scripts = ob_get_clean();
            $editor_assets = $this->_tp_json_encode(['styles' => $styles,'scripts' => $scripts,]);
            return $editor_assets;
        }//2850
        public function get_i_framed_editor_assets_html():void{
            $editor = $this->_tp_add_i_framed_editor_assets_html();
            $script = "<script>";
            $script .= "window.__editorAssets ={$editor}";
            $script .= "</script>";
            echo $script;
        }
        protected function _tp_scripts_get_suffix($type = ''){
            static $suffixes;
            if ( null === $suffixes ){
                $develop_src = false !== strpos( TP_VERSION, '-src' );
                if ( ! defined( 'TP_SCRIPT_DEBUG' ) ) define( 'TP_SCRIPT_DEBUG', $develop_src );
                $suffix     = TP_SCRIPT_DEBUG ? '' : '.min';
                $dev_suffix = $develop_src ? '' : '.min';
                $suffixes = ['suffix' => $suffix,'dev_suffix' => $dev_suffix,];
            }
            if ( 'dev' === $type )
                return $suffixes['dev_suffix'];
            return $suffixes['suffix'];
        }//581
    }
}else die;