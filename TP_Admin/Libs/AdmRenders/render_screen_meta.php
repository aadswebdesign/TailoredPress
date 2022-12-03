<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 1-7-2022
 * Time: 17:20
 */
declare(strict_types=1);
namespace TP_Admin\Libs\AdmRenders;
use TP_Core\Traits\Formats\_formats_08;
if(ABSPATH){
    class render_screen_meta{
        use _formats_08;
        protected $_html;
        protected $_args;
        public function __construct($args){
            $this->_args['help_sidebar'] = $args['help_sidebar'];
            $this->_args['help_class'] = $args['help_class'];
            $this->_args['help_tabs'] = $args['help_tabs'];
        }
        private function __to_string():string {
            $this->_html = "<div id='contextual_help_wrap' class='{$this->_esc_attr($this->_args['help_class'])}' tabindex='-1' aria-label='{$this->_esc_attr('Contextual Help Tab')}'>";//div1
            $this->_html .= "<div id='contextual_help_back'></div>";//div2
            $this->_html .= "<div id='contextual_help_columns'>";//div3
            $this->_html .= "<div class='contextual-help-tabs'>";//div4
            $this->_html .= "<ul>";//ul1
            $class = " class='active'";
            foreach ( $this->_args['help_tabs'] as $tab ){
                $link_id  = "tab_link_{$tab['id']}";
                $panel_id = "tab_panel_{$tab['id']}";
                $this->_html .= "<li id='{$this->_esc_attr( $link_id )}' {$this->_esc_attr($class)}>";//li1
                $this->_html .= "<a href='#{$this->_esc_attr($panel_id)}' area-controls='{$this->_esc_attr($panel_id)}'>{$this->_esc_html($tab['title'])}</a>";
                $this->_html .= "</li>";//end li1
                $class = '';
            }
            $this->_html .= "</ul>";//end ul1
            $this->_html .= "</div>";//end div4
            if ( $this->_args['help_sidebar'] ){
                $this->_html .= "<div class='contextual-help-sidebar'>";//div5
                $this->_html .= $this->_args['help_sidebar'];
                $this->_html .= "</div>";//end div5
            }
            $this->_html .= "<div class='contextual-help-tabs-wrap'>";//div6
            $classes = 'help-tab-content active';
            foreach ($this->_args['help_tabs'] as $tab ){
                $panel_id = "tab_panel_{$tab['id']}";
                $this->_html .= "<div id='{$this->_esc_attr( $panel_id )}' class='{$this->_esc_attr($classes)}'>";//div7
                $this->_html .= $tab['content'];
                if ( ! empty( $tab['callback'] ) )
                    $this->_html .= call_user_func( $tab['callback'], [$this, $tab] );
                $this->_html .= "</div>";//end div7
                $classes = 'help-tab-content';
            }
            $this->_html .= "</div>";//end div6
            $this->_html .= "</div>";//end div3
            $this->_html .= "</div>";//end div1
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;