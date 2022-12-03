<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-7-2022
 * Time: 06:32
 */
declare(strict_types=1);
namespace TP_Admin\Libs\AdmRenders;
use TP_Admin\Traits\_adm_screen;
use TP_Core\Traits\Actions\_action_01;
use TP_Core\Traits\AdminConstructs\_adm_construct_screen;
use TP_Core\Traits\Capabilities\_capability_01;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\Options\_option_01;
use TP_Core\Traits\Pluggables\_pluggable_01;
use TP_Core\Traits\User\_user_02;
use TP_Core\Traits\User\_user_03;

if(ABSPATH){
    class render_meta_boxes_preferences{
        use _I10n_01;
        use _option_01;
        use _pluggable_01;
        use _user_02,_user_03;
        use _adm_construct_screen;
        use _adm_screen;
        use _action_01;
        use _capability_01;
        protected $_html;
        protected $_args;
        public function __construct($args){
            $this->_args['meta_id'] = $args['meta_id'];
        }
        private function __to_string(): string{
            $content['p1'] = $this->__('Some screen elements can be shown or hidden by using the checkboxes.');
            $content['p1'] .= $this->__('They can be expanded and collapsed by clicking on their headings, and arranged by dragging their headings or by clicking on the up and down arrows.');
            $this->_html = "<fieldset class='metabox-prefers'><legend>{$this->__('Screen elements')}</legend>";
            $this->_html .= "<p>{$content['p1']}</p>";
            ob_start();
            $this->_meta_box_prefers( $this->tp_screen );
            $this->_html .= ob_get_clean();
            if ( 'dashboard' === $this->_args['meta_id'] && $this->_has_action( 'welcome_panel' ) && $this->_current_user_can( 'edit_theme_options' ) ) {
                if ( isset( $_GET['welcome'] ) ) {
                    $welcome_checked = empty( $_GET['welcome'] ) ? 0 : 1;
                    $this->_update_user_meta( $this->_get_current_user_id(), 'show_welcome_panel', $welcome_checked );
                }else{
                    $welcome_checked = (int) $this->_get_user_meta( $this->_get_current_user_id(), 'show_welcome_panel', true );
                    if ( 2 === $welcome_checked && $this->_tp_get_user_current()->user_email !== $this->_get_option( 'admin_email' ) ) $welcome_checked = false;
                }
                $this->_html .= "<label for='tp_welcome_panel_hide'>";
                $this->_html .= "<input type='checkbox' id='tp_welcome_panel_hide'  {$this->_get_checked( (bool) $welcome_checked, true )}/>";
                $this->_html .= "{$this->__( 'Welcome', 'Welcome panel' )}</label>\n";
            }
            $this->_html .= "</fieldset>";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }


    }
}else die;

