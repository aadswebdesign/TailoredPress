<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 3-5-2022
 * Time: 03:21
 */
declare(strict_types=1);
namespace TP_Core\Libs\AssetsTools;
use TP_Core\Traits\AssetsLoaders\_assets_loader_01;
if(ABSPATH){
    class TP_Scripts extends TP_Dependencies{
        use _assets_loader_01;
        private $__type_attr = '';
        protected $_print_script;
        public $atts = [];
        public $base_url;
        public $concat = '';
        public $concat_version = '';
        public $content_url;
        public $default_dirs;
        public $default_version;
        public $do_concat = false;
        public $ext_handles = '';
        public $ext_version = '';
        public $in_footer = [];
        public $print_code = '';
        public $print_html = '';
        public $types = [];
        //todo temporary added
        public $tp_filter;
        public function __construct() {
            $this->init();
            $this->_add_action( 'init', array( $this, 'init' ), 0 );
        }
        public function init(): void{
            $this->types = [
                'appEcmascript', //=>'application/ecmascript'
                'module', // => 'module'
                'textBabel', // => 'text/babel'
                'textJavascript',//text/javascript
                'textJsx', // => 'text/jsx'
            ];
            switch($this->types){
                case 'appEcmascript':
                    $this->__type_attr = " type='application/ecmascript'";
                    break;
                case('module'):
                    $this->__type_attr = " type='module'";
                    break;
                case('textBabel'):
                    $this->__type_attr = " type='text/babel'";
                    break;
                case('textJavascript'):
                    $this->__type_attr = " type='text/javascript'";
                    break;
                default:
                    $this->__type_attr = null;
            }
            $this->_do_action_ref_array( 'tp_default_scripts', [&$this]);
        }//168
        public function add( $handle, ...$data):bool{
            if(!parent::add($handle,$data)) return false;
            $this->registered[ $handle ] = new Dependency($handle, $data['src'], $data['deps'], $data['ver'], $data['type'], $data['loading_type'], $data['crossorigin'], $data['integrity'], $data['extra_atts']);
        }
        public function print_scripts( $handles = false, $group = false ):array{
            return $this->do_items( $handles, $group );
        }//185
        public function print_extra_script( $handle, $echo = true ){
            $output = $this->get_data( $handle, 'data' );
            if ( ! $output ) return false;
            if ( ! $echo ) return $output;
            $obj = $this->registered[ $handle ];
            $attributes = (array)$obj->data;
            foreach ($attributes as $atts) $this->atts = implode(' ', $this->_esc_attr($atts));
            printf( "<script%s id='%s_js_extra' $this->atts>\n", $this->__type_attr, $this->_esc_attr( $handle ));//todo
            return true;
        }//220
        public function do_item( $handle, $group = false ):bool{
            if (! parent::do_item( $handle ) ) return false;
            if ( 0 === $group && $this->groups[ $handle ] > 0 ){
                $this->in_footer[] = $handle;
                return false;
            }
            if ( false === $group && in_array( $handle, $this->in_footer, true ) )
                $this->in_footer = array_diff( $this->in_footer, (array) $handle );
            $obj = $this->registered[ $handle ];
            if ( null === $obj->data['ver'] ) $ver = '';
            else $ver = $obj->data['ver'];
            if ( isset( $this->args[ $handle ] ) )
                $ver = $ver ? $ver . '&amp;' . $this->args[ $handle ] : $this->args[ $handle ];
            $src = $obj->data['src'];
            $cond_before = '';
            $cond_after  = '';
            $conditional = $obj->extra['conditional'] ?: '';
            if ( $conditional ) {
                $cond_before = "<!--[if {$conditional}]>\n";
                $cond_after  = "<![endif]-->\n";
            }
            $before_handle = $this->print_inline_script( $handle, 'before', false );
            $after_handle  = $this->print_inline_script( $handle, 'after', false );
            $attributes = (array)$obj->data;
            foreach ($attributes as $atts) $this->atts = implode(' ', $this->_esc_attr($atts));
            if ( $before_handle )
                $before_handle = sprintf( "<script%s id='%s_js_before' $this->atts>\n%s\n</script>\n", $this->__type_attr, $this->_esc_attr( $handle ), $before_handle);
            if ( $after_handle )
                $after_handle = sprintf( "<script%s id='%s_js_after' $this->atts>\n%s\n</script>\n", $this->__type_attr, $this->_esc_attr( $handle ), $after_handle);
            if ( $before_handle || $after_handle )
                $inline_script_tag = "{$cond_before}{$handle}{$after_handle}{$cond_after}";
            else $inline_script_tag = '';
            $translations = $this->print_translations( $handle, false );
            if ( $translations )
                $translations = sprintf( "<script%s id='%s_js_translations' $this->atts>\n%s\n</script>\n", $this->__type_attr, $this->_esc_attr( $handle ), $translations );
            if ( $this->do_concat ) {
                $src_e = $this->_apply_filters( 'script_loader_src', $src, $handle );
                if (($before_handle || $after_handle || $translations) && $this->in_default_dir( $src_e )){
                    $this->do_concat = false;
                    $this->_print_scripts();
                    $this->reset();
                }elseif (! $conditional && $this->in_default_dir( $src_e )) {
                    $this->print_code     .= $this->print_extra_script( $handle, false );
                    $this->concat         .= "$handle,";
                    $this->concat_version .= "$handle$ver";
                    return true;
                } else {
                    $this->ext_handles .= "$handle,";
                    $this->ext_version .= "$handle$ver";
                }
            }
            $has_conditional_data = $conditional && $this->get_data( $handle, 'data' );
            if ( $has_conditional_data ) echo $cond_before;
            $this->print_extra_script( $handle );
            if ( $has_conditional_data ) echo $cond_after;
            if ( ! $src ) {
                if ( $inline_script_tag ) {
                    if ( $this->do_concat ) $this->print_html .= $inline_script_tag;
                    else echo $inline_script_tag;
                }
                return true;
            }
            if ( ! preg_match( '|^(https?:)?//|', $src ) && ! ( $this->content_url && 0 === strpos( $src, $this->content_url )))
                $src = $this->base_url . $src;
            if ( ! empty( $ver ) )
                $src = $this->_add_query_arg( 'ver', $ver, $src );
            $src = $this->_esc_url( $this->_apply_filters( 'script_loader_src', $src, $handle ) );
            if ( ! $src ) return true;
            $tag  = $translations . $cond_before . $before_handle;
            $tag .= sprintf( "<script%s src='%s' id='%s_js'></script>\n", $this->__type_attr, $src, $this->_esc_attr( $handle ) );
            $tag .= $after_handle . $cond_after;
            $tag = $this->_apply_filters( 'script_loader_tag', $tag, $handle, $src );
            if ( $this->do_concat ) $this->print_html .= $tag;
            else echo $tag;
            return true;
        }//261
        public function add_inline_script( $handle, $data, $position = 'after' ){
            if (!$data) return false;
            if ('after'!== $position ) $position = 'before';
            $script   = (array) $this->get_data( $handle, $position );
            $script[] = $data;
            return $this->add_data( $handle, $position, $script );
        }
        public function print_inline_script( $handle, $position = 'after', $echo = true ){
            $output = $this->get_data( $handle, $position );
            $obj = $this->registered[ $handle ];
            $attributes = (array)$obj->data;
            foreach ($attributes as $atts) $this->atts = implode(' ', $this->_esc_attr($atts));
            if ( empty($output)) return false;
            $output = trim( implode( "\n", $output ), "\n" );
            if ( $echo ) printf( "<script%s id='%s_js_%s' $this->atts>\n%s\n</script>\n", $this->__type_attr, $this->_esc_attr( $handle ), $this->_esc_attr( $position ), $output );
            return $output;
        }//451
        public function localize( $handle, $object_name,$l10n, $after = null ){
            if ( ! is_array( $l10n ) ) {
                $this->_doing_it_wrong(
                    __METHOD__,
                    sprintf(
                        $this->__( 'The %1$s parameter must be an array. To pass arbitrary data to scripts, use the %2$s function instead.' ),
                        '<code>$l10n</code>','<code>tp_add_inline_script()</code>'
                    ),'0.0.1');
            }
            if ( is_string( $l10n ) ) $l10n = html_entity_decode( $l10n, ENT_QUOTES, 'UTF-8' );
            else{
                foreach ( (array) $l10n as $key => $value ) {
                    if ( ! is_scalar( $value ) ) continue;
                    $l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
                }
            }
            $script = "var $object_name = " . $this->_tp_json_encode( $l10n ) . ';';
            if ( ! empty( $after ) ) $script .= "\n$after;";
            $data = $this->get_data( $handle, 'data' );
            if ( ! empty( $data ) ) $script = "$data\n$script";
            return $this->add_data( $handle, 'data', $script );
        }
        public function set_group( $handle, $recursion, $group = false ):bool{
            if ( isset( $this->registered[ $handle ]->args ) && $this->registered[ $handle ]->args === 1 )
                $grp = 1;
            else $grp = (int) $this->get_data( $handle, 'group' );
            if ( false !== $group && $grp > $group ) $grp = $group;
            return parent::set_group( $handle, $recursion, $grp );
        }
        public function set_translations( $handle, $domain = 'default', $path = null ){
            if ( ! isset( $this->registered[ $handle ] ) )
                return false;
            $obj = $this->registered[ $handle ];
            if ( ! in_array( 'tp-i18n', $obj->data['deps'], true ) )
                $obj->deps[] = 'tp-i18n';
            return $obj->_dependency->set_translations( $domain, $path );
        }
        public function print_translations( $handle, $echo = true ){
            if ( ! isset( $this->registered[ $handle ] ) || empty( $this->registered[ $handle ]->textdomain ) )
                return false;
            $domain = $this->registered[ $handle ]->textdomain;
            $path   = $this->registered[ $handle ]->translations_path;
            $json_translations = $this->_load_script_textdomain( $handle, $domain, $path );
            if ( ! $json_translations )
                $json_translations = '{ "locale_data": { "messages": { "": {} } } }';
            $output = <<<JS
( function( domain, translations ) {
	//noinspection JSUnresolvedVariable
let localeData = translations.locale_data[ domain ] || translations.locale_data.messages;
	localeData[""].domain = domain;
	//noinspection JSUnresolvedVariable ,JSUnresolvedFunction
	tp.i18n.setLocaleData( localeData, domain );
} )( "{$domain}", '{$json_translations}');
JS;
            if ( $echo ) printf( "<script%s id='%s_js_translations'>\n%s\n</script>\n", $this->__type_attr, $this->_esc_attr( $handle ), $output );
            return $output;
        }
        public function all_deps( $handles, $recursion = false, $group = false ):bool{
            $r = parent::all_deps( $handles, $recursion, $group );
            if ( ! $recursion ) $this->to_do = $this->_apply_filters( 'print_scripts_array', $this->to_do );
            return $r;
        }
        public function do_head_items(): array{
            $this->do_items( false, 0 );
            return $this->done;
        }
        public function do_footer_items(): array{
            $this->do_items( false, 1 );
            return $this->done;
        }
        public function in_default_dir( $src): bool{
            if ( ! $this->default_dirs ) return true;
            if ( 0 === strpos( $src, '/' . TP_CORE_ASSETS . '/js/l10n' ) )
                return false;
            foreach ( (array) $this->default_dirs as $test ) {
                if ( 0 === strpos( $src, $test ) ) return true;
            }
            return false;
        }
        public function reset(): void{
            $this->do_concat      = false;
            $this->print_code     = '';
            $this->concat         = '';
            $this->print_html     = '';
            $this->ext_version    = '';
            $this->ext_handles    = '';
        }
    }
}else die;