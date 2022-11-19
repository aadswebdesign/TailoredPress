<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 8-5-2022
 * Time: 13:58
 */
declare(strict_types=1);
namespace TP_Core\Traits\AssetsLoaders;
use TP_Core\Libs\AssetsTools\TP_Scripts;
use TP_Core\Traits\Inits\_init_assets;
if(ABSPATH){
    trait _assets_loader_01{
        use _init_assets;
        /**
         * @description Prints the script queue in the HTML head on admin pages.
         * @return array
         */
        protected function _print_head_scripts(): array{
            if ( ! $this->_did_action('tp_print_scripts'))
                $this->_do_action('tp_print_scripts');
            $tp_scripts = $this->_init_scripts();
            $this->_assets_concat_settings();
            $tp_scripts->do_concat = $this->tp_concatenate_scripts;
            $tp_scripts->do_head_items();
            if ( $this->_apply_filters( '__print_head_assets', true ) )
                $this->_print_scripts();
            $tp_scripts->reset();
            return $tp_scripts->done;
        }//1983 from script-loader
        /**
         * @description Prints the scripts that were queued for the footer or too late for the HTML head.
         * @return array
         */
        protected function _print_footer_scripts(): array{
            if(!($this->tp_scripts instanceof TP_Scripts)) return [];
            $this->_assets_concat_settings();
            $this->tp_scripts->do_concat = $this->tp_concatenate_scripts;
            $this->tp_scripts->do_footer_items();
            if ( $this->_apply_filters( '__print_footer_scripts', true ) )
                $this->_print_scripts();
            $this->tp_scripts->reset();
            return $this->tp_scripts->done;
        }//2022
        /**
         * @description Print scripts (internal use only), supports html5 only
         * @note added option to load 'js' modules or any other type
         * @param null $type
         * @return string
         */
        protected function _print_scripts($type = null): string{
            $zip = $this->tp_compress_scripts ? 1 : 0;
            if ( $zip && defined( 'ENFORCE_GZIP' ) && ENFORCE_GZIP ) $zip = 'gzip';
            $concat    = trim( $this->tp_scripts->concat, ', ' );
            $_type = null;
            if(!empty($type)) $_type = " type='{$type}'";
            $type_attr = $_type ?: '';
            if($concat){
                $script = "\n<script{$type_attr}>\n";
                $script .= $this->tp_scripts->print_code;
                $script .= "</script>\n";
                if(!empty($this->tp_scripts->print_code)) echo $script;
                $concat       = str_split( $concat, 128 );
                $concatenated = '';
                foreach ( $concat as $key => $chunk ) /** @noinspection SpellCheckingInspection */
                    $concatenated .= "&load%5Bchunk_{$key}%5D={$chunk}";
                $src = $this->tp_scripts->base_url . "/TP_Admin/load_scripts.php?c={$zip}" . $concatenated . '&ver=' . $this->tp_scripts->default_version;
                echo "<script{$type_attr} src='{$this->_esc_attr( $src )}'></script>\n";
            }
            return '';
        }//2055
        /**
         * @description Prints the script queue in the HTML head on the front end.
         * @return array
         */
        protected function _tp_print_head_scripts(): array{
            if ( ! $this->_did_action( 'tp_print_scripts' ) ) $this->_do_action( 'tp_print_scripts' );
            if ( ! ( $this->tp_scripts instanceof TP_Scripts ) ) return [];
            return $this->_print_head_scripts();
        }//2103
        /**
         * @description Private, for use in *_footer_scripts hooks
         */
        protected function _tp_footer_assets(): void{
            $this->_print_late_styles();
            $this->_print_footer_scripts();
        }//2123
        /**
         * @description Hooks to print the scripts and styles in the footer.
         */
        public function tp_print_footer_assets(): void{
            $this->_do_action('tp_print_footer_assets');
        }//2133
        /**
         * @description Wrapper for do_action( 'tp_enqueue_assets' ).
         */
        public function tp_enqueue_assets(): void{
            $this->_do_action('tp_enqueue_assets');
        }//2150
        /**
         * @description Prints the styles queue in the HTML head on admin pages.
         * @return array
         */
        protected function _print_admin_styles(): array{
            $link_styles = $this->_init_styles();
            $this->_assets_concat_settings();
            $link_styles->do_concat = $this->tp_concatenate_scripts;
            $link_styles->do_items( false );
            if ( $this->_apply_filters( 'print_admin_styles', true ) )
                $this->_print_styles();
            $link_styles->reset();
            return $link_styles->done;
        }//2168
        /**
         * @description Prints the styles that were queued too late for the HTML head.
         * @return array|null
         */
        protected function _print_late_styles(): ?array{
            $link_styles = $this->_init_styles();
            $this->_assets_concat_settings();
            $link_styles->do_concat = $this->tp_concatenate_scripts;
            $link_styles->do_footer_items();
            if ( $this->_apply_filters( 'print_late_styles', true ) )
                $this->_print_styles();
            $link_styles->reset();
            return $link_styles->done;
        }//2202
        /**
         * @description Print styles (internal use only)
         * @param null $rel
         * @param null $media
         */
        protected function _print_styles($rel = null, $media = null): void{
            static $_rel,$_media;
            $this->tp_styles = $this->_init_styles();
            $zip = $this->tp_compress_css ? 1 : 0;
            if ( $zip && defined( 'ENFORCE_GZIP' ) && ENFORCE_GZIP ) $zip = 'gzip';
            $concat    = trim( $this->tp_styles->concat, ', ' );
            if(!empty($rel)) $_rel = " rel='{$rel}'";
            $rel_attr = $_rel ?: " rel='stylesheet'";
            if(!empty($media)) $_media = " media='{$media}'";
            $media_attr = $_media ?: " media='all'";
            if ( $concat ) {
                $dir = $this->tp_styles->text_direction;
                $ver = $this->tp_styles->default_version;
                $concat       = str_split( $concat, 128 );
                $concatenated = '';
                foreach ( $concat as $key => $chunk ) $concatenated .= "&load%5Bchunk_{$key}%5D={$chunk}";
                $href = $this->tp_styles->base_url . "/TP_Admin/load_styles.php?c={$zip}&dir={$dir}" . $concatenated . '&ver=' . $ver;
                echo "<link rel='stylesheet' href='{$this->_esc_attr( $href )}' {$rel_attr} {$media_attr} />\n";
                $style = "<style>\n";
                $style .= $this->tp_styles->print_code;
                $style .= "\n</style>\n";
                if (!empty( $this->tp_styles->print_code )) echo $style;
            }
            if (!empty($this->tp_styles->print_html)) echo $this->tp_styles->print_html;
        }//2236
    }
}else die;