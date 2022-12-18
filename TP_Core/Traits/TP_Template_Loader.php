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
use TP_Core\Traits\Load\_load_05;
use TP_Core\Traits\Methods\_methods_04;
use TP_Core\Traits\Methods\_methods_08;
use TP_Core\Traits\Methods\_methods_21;
use TP_Core\Traits\Query\_query_02;
use TP_Core\Traits\Query\_query_03;
use TP_Core\Traits\Query\_query_04;
use TP_Core\Traits\Templates\_template_01;
use TP_Core\Traits\Templates\_template_02;
use TP_Core\Traits\Theme\_theme_01;
use TP_Libs\TP_Trackback;
if(ABSPATH){
    trait TP_Template_Loader{
        use _action_01;
        use _capability_01;
        use _filter_01;
        use _load_05;
        use _methods_04;
        use _methods_08;
        use _methods_21;
        use _query_02;
        use _query_03;
        use _query_04;
        use _template_01,_template_02;
        use _theme_01;
        public $tp_template;
        private function __tpl_construct($args = null):void{
            if (! defined( 'TP_NS_CONTENT' ) ) define('TP_NS_CONTENT','TP_Content\\');
            if (! defined( 'TP_NS_THEMES' ) ) define('TP_NS_THEMES',TP_NS_CONTENT.'Themes\\');
            $this->_args = $args;
            //$this->_add_action( 'do_robots',static function(){return 'is this working?</br>';} );
            echo '__tpl_construct';
        }
        private function __tpl_to_string():string{
            $output  = "";
            if ($this->_tp_using_themes() ){
                $output .= $this->_get_action( 'template_redirect' );
                $output .= "template_redirect</br>";//todo temporary
            }
            if ( 'HEAD' === $_SERVER['REQUEST_METHOD'] && $this->_apply_filters( 'exit_on_http_head', true ) ) {
                echo "</br>REQUEST_METHOD";//todo temporary
                exit;
            }
            if ($this->_is_robots() ) {
                $output .= $this->_get_action( 'do_robots' );
                $output .= "do_robots</br>";//todo temporary
            }elseif ($this->_is_favicon() ) {
                $output .= $this->_get_action( 'do_favicon' );
                $output .= "do_favicon</br>";//todo temporary
            } elseif ( $this->_is_feed() ) {
                $output .= $this->_do_feed();
            }elseif ($this->_is_trackback() ) {
                $output .= new TP_Trackback();
            }
            if (! $this->_tp_using_themes() ) {
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

                foreach ( $tag_templates as $tag => $template_getter ) {
                    if ([$this,$tag]) {
                        $this->tp_template = $template_getter();
                    }
                    if ( $this->tp_template ) {
                        if ( 'is_attachment' === [$this,$tag] ) {
                            $this->_remove_filter( 'the_content', 'prepend_attachment' );
                        }
                        break;
                    }
                }
                if (!$this->tp_template ) {
                    $this->tp_template = $this->_get_index_template();//todo
                }
                $this->tp_template = $this->_apply_filters( 'template_include', $this->tp_template );
                if ( $this->tp_template ) {//todo
                    $output .= $this->tp_template;
                }elseif ( $this->_current_user_can( 'switch_themes' ) ) {
                    $theme = $this->_tp_get_theme();
                    if ( $theme->errors() ) {
                        $output .= $this->_tp_get_die( $theme->errors() );
                    }
                }
            }
            //$output .= $this->tp_template['get_embed'];
            //$output .= $this->tp_template['is_404'];
            //$output .= $this->tp_template['is_search'];
            //$output .= $this->tp_template['is_front_page'];
            //$output .= $this->tp_template['is_home'];
            //$output .= $this->tp_template['is_privacy_policy'];
            //$output .= $this->tp_template['is_post_type_archive'];
            //$output .= $this->tp_template['is_tax'];
            //$output .= $this->tp_template['is_attachment'];
            //$output .= $this->tp_template['is_single'];
            //$output .= $this->tp_template['is_page'];
            //$output .= $this->tp_template['is_singular'];
            //$output .= $this->tp_template['is_category'];
            //$output .= $this->tp_template['is_tag'];
            //$output .= $this->tp_template['is_author'];
            //$output .= $this->tp_template['is_date'];
            //$output .= $this->tp_template['is_archive'];
            $output .= "";
            $output .= "";
            $output .= "";
            $output .= "";
            return $output;
        }
    }
}else{die;}