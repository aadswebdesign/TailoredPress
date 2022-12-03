<?php
/**
 * Created by PhpStorm.
 * User: Aad Pouw
 * Date: 2-7-2022
 * Time: 10:43
 */
declare(strict_types=1);
namespace TP_Admin\Libs\AdmRenders;
use TP_Admin\Traits\_adm_screen;
use TP_Core\Traits\Formats\_formats_08;
use TP_Core\Traits\Methods\_methods_01;
use TP_Core\Traits\I10n\_I10n_01;
use TP_Core\Traits\I10n\_I10n_03;
if(ABSPATH){
    class render_screen_layout{
        use _methods_01;
        use _I10n_01;
        use _I10n_03;
        use _formats_08;
        use _adm_screen;
        protected $_html;
        protected $_args;
        public function __construct($args){
            $this->_args['screen_layout_columns'] = $args['screen_layout_columns'];
            $this->_args['num'] = $args['num'];
        }
        private function __to_string():string{
            $this->_html = "<fieldset class='columns-prefers'><legend class='screen-layout'>{$this->__('Layout')}</legend>";
            for ( $i = 1; $i <= $this->_args['num']; ++$i ){
                $this->_html .= "<label class='columns-prefers-{$i}'>";
                $this->_html .= "<input type='radio' name='screen_columns' value='{$this->_esc_attr( $i )}' {$this->_get_checked( $this->_args['screen_layout_columns'], $i )}/>";
                ob_start();
                printf($this->_n( '%s column', '%s columns', $i ), $this->_number_format_i18n( $i ));
                $this->_html .= ob_get_clean();
                $this->_html .= "</label>";
            }
            $this->_html .= "</fieldset>";
            return (string) $this->_html;
        }
        public function __toString(){
            return $this->__to_string();
        }
    }
}else die;