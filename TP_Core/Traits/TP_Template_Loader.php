<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 18-12-2022
 * Time: 04:37
 */
namespace TP_Core\Traits;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\Filters\_filter_01;
use TP_Core\Traits\Formats\_format_post_01;
use TP_Core\Traits\Load\_load_05;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Methods\_methods_21;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\Post\_post_01;
use TP_Core\Traits\Query\_query_01;
use TP_Core\Traits\Query\_query_02;
use TP_Core\Traits\Query\_query_03;
use TP_Core\Traits\Query\_query_04;
use TP_Core\Traits\Templates\_block_template_01;
use TP_Core\Traits\Templates\_template_01;
use TP_Core\Traits\Templates\_template_02;
use TP_Core\Traits\Templates\_template_03;
use TP_Core\Traits\Theme\_theme_01;
use TP_Core\Traits\Theme\_theme_02;
use TP_Core\Traits\Theme\_theme_07;
use TP_Core\Traits\User\_user_03;
use TP_Core\Traits\User\_user_05;
use TP_Libs\Constants;
use TP_Libs\TP_Trackback;
if(ABSPATH){
    trait TP_Template_Loader{
        use _action_01;
        use _block_template_01;
        use _capability_01;
        use Constants;
        use _filter_01,_format_post_01;
        use _load_05;
        use _methods_04;
        use _methods_08;
        use _methods_21;
        use _option_01;
        use _pluggable_01;
        use _post_01;
        use _query_01, _query_02, _query_03, _query_04;
        use _template_01,_template_02,_template_03;
        use _theme_01,_theme_02,_theme_07;
        use _user_03;
        use _user_05;
        public $tp_filter,$tp_template;
        private function __tpl_construct($args = null):void{
            $this->_tp_content_constants();
            $this->_args = $args;
            $this->_add_action('do_favicon',[$this,'theme_loader_stuff']);
        }
        private function __tpl_to_string():string{
            $output  = "";
            if ($this->_tp_using_themes() ){
                $output .= $this->_get_action( 'template_redirect' );
            }
            if ( 'HEAD' === $_SERVER['REQUEST_METHOD'] && $this->_apply_filters( 'exit_on_http_head', true ) ) {
                echo "</br>REQUEST_METHOD";//todo temporary
                exit;
            }
            if ($this->_is_robots() ) {
                $output .= $this->_get_action( 'do_robots' );
            }elseif ($this->_is_favicon() ) {
                $output .= $this->_get_action( 'do_favicon' );
            } elseif ( $this->_is_feed() ) {
                $output .= $this->_do_feed();
            }elseif ($this->_is_trackback() ) {
                $output .= new TP_Trackback();
            }
            if (!$this->_tp_using_themes() ) {//todo removing !
                $tag_templates = [
                    'get_embed'             => [$this,'_get_embed_template'],
                    'is_404'                => [$this,'_get_404_template'],
                    'is_search'             => [$this,'_get_search_template'],
                    'is_front_page'         => [$this,'_get_front_page_template'],
                    'is_home'               => [$this,'_get_home_template'],
                    'is_privacy_policy'     => [$this,'_get_privacy_policy_template'],
                    'is_post_type_archive'  => [$this,'_get_post_type_archive_template'],
                    'is_tax'                => [$this,'_get_taxonomy_template'],
                    'is_attachment'         => [$this,'_get_attachment_template'],
                    'is_single'             => [$this,'_get_single_template'],
                    'is_page'               => [$this,'_get_page_template'],
                    'is_singular'           => [$this,'_get_singular_template'],
                    'is_category'           => [$this,'_get_category_template'],
                    'is_tag'                => [$this,'_get_tag_template'],
                    'is_author'             => [$this,'_get_author_template'],
                    'is_date'               => [$this,'_get_date_template'],
                    'is_archive'            => [$this,'_get_archive_template'],
                ];
                $template = false;
                foreach ( $tag_templates as $tag => $template_getter ) {

                    if ([$this,$tag]) { //original call_user_func( $tag )
                        $template = $template_getter();
                        //var_dump('$template1:', $template);
                    }
                    if ( $template ) {
                        if ( 'is_attachment' === $tag ) {
                            $this->_remove_filter( 'the_content', 'prepend_attachment' );
                        }
                        break;
                    }
                }
                if (! $template ) {
                    $template  = $this->_get_index_template();
                }
                $template = $this->_apply_filters( 'template_include', $template );
                if (! $template ) {//todo
                    $output .= $template;
                }elseif ( $this->_current_user_can( 'switch_themes' ) ) {
                    $theme = $this->_tp_get_theme();
                    if ($theme->errors() ) {
                        $output .= $this->_tp_get_die( $theme->errors() );
                    }
                }
            }
            //$output .= $this->_get_index_template();
            //$output .= "<br>TP_Template_Loader";
            $output .= $template;
            $output .= "";
            return $output;
        }
    }
}else{die;}