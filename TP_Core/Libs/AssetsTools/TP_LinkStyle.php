<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-5-2022
 * Time: 17:46
 */
declare(strict_types=1);
namespace TP_Core\Libs\AssetsTools;

if(ABSPATH){
    class TP_LinkStyle extends TP_Dependencies {
        public const LINK_ELEM = '<link ';
        public const CLOSE_ELEM = ' />';
        public $base_url;
        public $content_url;
        public $default_version;
        public $text_direction = 'ltr';
        public $concat = '';
        public $concat_version = '';
        public $do_concat = false;
        public $print_html = '';
        public $print_code = '';
        public $default_dirs;
        public function __construct() {
            $this->_do_action_ref_array( 'tp_default_styles', array( &$this ) );
        }
        public function add( $handle, ...$data):bool{
            if(!parent::add($handle,$data)) return false;
            $this->registered[ $handle ] = new Dependency($handle, $data['href'], $data['deps'], $data['ver'], $data['rel'], $data['media'], $data['crossorigin'], $data['integrity'], $data['extra_atts']);
        }
        public function do_item( $handle, $group = false ):bool{
            if ( ! parent::do_item( $handle ) ) return false;
            static $media,$rel,$crossorigin,$integrity;
            $obj = $this->registered[ $handle ];
            if ( null === $obj->data['ver'] ) $ver = '';
            else $ver = $obj->data['ver'] ?: $this->default_version;
            if ( isset( $this->args[ $handle ] ) )
                $ver = $ver ? $ver . '&amp;' . $this->args[ $handle ] : $this->args[ $handle ];
            $href = $obj->data['href'];
            $cond_before = '';
            $cond_after  = '';
            $conditional = $obj->extra['conditional'] ?: '';
            if ( $conditional ) {
                $cond_before = "<!--[if {$conditional}]>\n";
                $cond_after  = "<![endif]-->\n";
            }
            $inline_style = $this->print_inline_style( $handle, false );
            if ( $inline_style )
                $inline_style_tag = sprintf( "<style id='%s_inline_css'>\n%s\n</style>\n", $this->_esc_attr( $handle ), $inline_style );
            else $inline_style_tag = '';
            if ( $this->do_concat ){
                $this->do_concat = "$handle,";
                $this->concat_version .= "$handle$ver";
                $this->print_code .= $inline_style;
                return true;
            }
            if(!$href){
                if ( $inline_style_tag ) {
                    if ( $this->do_concat )  $this->print_html .= $inline_style_tag;
                     else echo $inline_style_tag;
                }
                return true;
            }
            $href_setup =  $this->_css_href( $href, $ver, $handle );
            if ( ! $href_setup ) return true;
            if(!empty($obj->data['media']))$media = " media='{$obj->data['media']}'";
            $media_atts = $media ?: " media='all'";
            if(!empty($obj->data['rel'])) $rel = " rel='{$obj->data['rel']}'";
            $rel_atts = $rel ?: " rel='stylesheet'";
            if(!empty($obj->data['crossorigin'])) $crossorigin = " crossorigin='{$obj->data['crossorigin']}'";
            $crossorigin_atts = $crossorigin ?: '';
            if(!empty($obj->data['integrity'])) $integrity = " integrity='{$obj->data['integrity']}'";
            $integrity_atts = $integrity ?: '';
            $atts_set = $rel_atts . $media_atts . $crossorigin_atts . $integrity_atts;
            $tag = sprintf(self::LINK_ELEM . " id='%s_css' href='%s' %s" . self::CLOSE_ELEM."\n",$handle,$href_setup,$atts_set);
            $tag = $this->_apply_filters('style_loader_tag',$tag,$handle,$href_setup,$atts_set);
            static $rtl_href;
            if ( 'rtl' === $this->text_direction && isset( $obj->extra['rtl'] ) && $obj->extra['rtl'] ){
                if ( is_bool( $obj->extra['rtl'] ) || 'replace' === $obj->extra['rtl'] ) {
                    $suffix   = $obj->extra['suffix'] ?: '';
                    $rtl_href = str_replace( "{$suffix}.css", "rtl_{$suffix}.css", $this->_css_href( $href, $ver, "{$handle}_rtl" ) );
                }
            }else $rtl_href = $this->_css_href( $obj->extra['rtl'], $ver, "{$handle}_rtl" );
            $rtl_tag = sprintf(
                self::LINK_ELEM ." id='%s_rtl_css' href='%s' %s" . self::CLOSE_ELEM. "\n",
                $handle,$rtl_href,$atts_set);
            $rtl_tag = $this->_apply_filters( 'style_loader_tag', $rtl_tag, $handle, $rtl_href, $atts_set );
            if ('replace' === $obj->extra['rtl']) $tag = $rtl_tag;
            else $tag .= $rtl_tag;
            if ( $this->do_concat ) {
                $this->print_html .= $cond_before;
                $this->print_html .= $tag;
                if ( $inline_style_tag ) $this->print_html .= $inline_style_tag;
                $this->print_html .= $cond_after;
            } else {
                echo $cond_before;
                echo $tag;
                $this->print_inline_style( $handle );
                echo $cond_after;
            }
            return true;
        }//151
        public function add_inline_style( $handle, $code ){
            if ( ! $code ) return false;
            $after = $this->get_data( $handle, 'after' );
            if ( ! $after ) $after = [];
            $after[] = $code;
            return $this->add_data( $handle, 'after', $after );
        }
        public function print_inline_style( $handle, $echo = true){
            $output = $this->get_data( $handle, 'after' );
            if (empty( $output )) return false;
            $output = implode( "\n", $output );
            if ( ! $echo ) return $output;
            printf(
                "<style id='%s-inline-css'>\n%s\n</style>\n",
                $this->_esc_attr( $handle ),$output);
            return true;
        }
        public function all_deps( $handles, $recursion = false, $group = false ):bool{
            $r = parent::all_deps( $handles, $recursion, $group );
            if ( ! $recursion ) $this->to_do = $this->_apply_filters( 'print_styles_array', $this->to_do );
            return $r;
        }
        protected function _css_href( $href, $ver, $handle ){
            if (!is_bool( $href ) && ! preg_match( '|^(https?:)?//|', $href ) && ! ( $this->content_url && 0 === strpos( $href, $this->content_url ) ) )
                $href = " href='{$this->base_url}{$href}'";
            if (!empty( $ver ) ){
                $href_args = $this->_add_query_arg('ver', $ver, $href);
                $href = $this->_apply_filters('assets_link_loader_href', $href_args, $handle);
            }
            return $this->_esc_url( $href );
        }
        public function in_default_dir( $src): bool{
            if ( ! $this->default_dirs ) return true;
            foreach ( (array) $this->default_dirs as $test ) {
                if ( 0 === strpos( $src, $test ) ) return true;
            }
            return false;
        }
        public function do_footer_items(): array{
            $this->do_items( false, 1 );
            return $this->done;
        }
        public function reset(): void{
            $this->do_concat      = false;
            $this->concat         = '';
            $this->concat_version = '';
            $this->print_html     = '';
        }
    }
}else die;